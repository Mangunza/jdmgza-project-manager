<?php

namespace Tests\Feature;

use App\Models\RecoveryContactVerification;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RecoveryContactApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function authenticatedUser(): array
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Recovery User',
            'email' => 'recovery@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated();

        return [
            User::query()
                ->where('email', 'recovery@example.com')
                ->firstOrFail(),
            $response->json('token'),
        ];
    }

    public function test_unauthenticated_user_cannot_update_recovery_contacts(): void
    {
        $response = $this->putJson('/api/auth/recovery-contacts', [
            'recovery_email' => 'recovery@example.com',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_set_recovery_email(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $response = $this
            ->withToken($token)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_email' => 'Recovery.Email@Example.com',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.recovery_email',
                'recovery.email@example.com'
            );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'recovery_email' => 'recovery.email@example.com',
        ]);
    }

    public function test_authenticated_user_can_set_recovery_phone(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $response = $this
            ->withToken($token)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_phone' => '+244 923 456 789',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.recovery_phone',
                '+244 923 456 789'
            );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'recovery_phone' => '+244 923 456 789',
        ]);
    }

    public function test_recovery_email_must_be_unique(): void
    {
        [$firstUser] = $this->authenticatedUser();

        $firstUser->update([
            'recovery_email' => 'used@example.com',
        ]);

        $secondResponse = $this->postJson('/api/auth/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $secondResponse->assertCreated();

        $secondToken = $secondResponse->json('token');

        $response = $this
            ->withToken($secondToken)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_email' => 'used@example.com',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['recovery_email']);
    }

    public function test_recovery_phone_must_be_unique(): void
    {
        [$firstUser] = $this->authenticatedUser();

        $firstUser->update([
            'recovery_phone' => '+244923456789',
        ]);

        $secondResponse = $this->postJson('/api/auth/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $secondResponse->assertCreated();

        $secondToken = $secondResponse->json('token');

        $response = $this
            ->withToken($secondToken)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_phone' => '+244923456789',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['recovery_phone']);
    }

    public function test_changing_recovery_email_clears_previous_verification(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'old@example.com',
            'recovery_email_verified_at' => Carbon::now(),
        ]);

        $response = $this
            ->withToken($token)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_email' => 'new@example.com',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'recovery_email' => 'new@example.com',
            'recovery_email_verified_at' => null,
        ]);
    }

    public function test_changing_recovery_phone_clears_previous_verification(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_phone' => '+244923456789',
            'recovery_phone_verified_at' => Carbon::now(),
        ]);

        $response = $this
            ->withToken($token)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_phone' => '+244923456788',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'recovery_phone' => '+244923456788',
            'recovery_phone_verified_at' => null,
        ]);
    }

    public function test_changing_recovery_email_invalidates_pending_email_verification(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'old@example.com',
        ]);

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination_hash' => hash('sha256', 'old@example.com'),
            'code_hash' => hash('sha256', '123456'),
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts' => 0,
        ]);

        $response = $this
            ->withToken($token)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_email' => 'new@example.com',
            ]);

        $response->assertOk();

        $this->assertDatabaseMissing('recovery_contact_verifications', [
            'user_id' => $user->id,
            'channel' => 'email',
            'verified_at' => null,
        ]);
    }

    public function test_changing_recovery_phone_invalidates_pending_sms_verification(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_phone' => '+244923456789',
        ]);

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'sms',
            'destination_hash' => hash('sha256', '+244923456789'),
            'code_hash' => hash('sha256', '123456'),
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts' => 0,
        ]);

        $response = $this
            ->withToken($token)
            ->putJson('/api/auth/recovery-contacts', [
                'recovery_phone' => '+244923456788',
            ]);

        $response->assertOk();

        $this->assertDatabaseMissing('recovery_contact_verifications', [
            'user_id' => $user->id,
            'channel' => 'sms',
            'verified_at' => null,
        ]);
    }
    public function test_unauthenticated_user_cannot_request_recovery_contact_verification(): void
    {
        $response = $this->postJson(
            '/api/auth/recovery-contacts/verification',
            [
                'channel' => 'email',
            ]
        );

        $response->assertUnauthorized();
    }
    public function test_authenticated_user_can_request_email_recovery_verification(): void
    {
        Mail::fake();

        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'Recovery.Email@Example.com',
        ]);

        $before = Carbon::now();

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification', [
                'channel' => 'email',
            ]);

        $after = Carbon::now();

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Código de verificação enviado com sucesso.',
            ]);

        $verification = RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->latest('id')
            ->first();

        $this->assertNotNull($verification);

        $this->assertSame(
            hash('sha256', 'recovery.email@example.com'),
            $verification->destination_hash
        );

        $this->assertNotSame(
            'recovery.email@example.com',
            $verification->code_hash
        );

        $this->assertSame(0, $verification->attempts);
        $this->assertNull($verification->verified_at);

        $this->assertTrue(
            $verification->expires_at->between(
                $before->copy()->addMinutes(9),
                $after->copy()->addMinutes(11)
            )
        );

    }
    public function test_authenticated_user_cannot_request_sms_recovery_verification_when_sms_is_not_configured(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_phone' => '+244923456789',
        ]);

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification', [
                'channel' => 'sms',
            ]);

        $response
            ->assertStatus(500)
            ->assertJsonFragment([
                'message' => 'O envio por SMS ainda não está configurado.',
            ]);
    }

    public function test_unauthenticated_user_cannot_verify_recovery_contact(): void
    {
        $response = $this->postJson(
            '/api/auth/recovery-contacts/verification/verify',
            [
                'channel' => 'email',
                'code' => '123456',
            ]
        );

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_verify_recovery_email_with_correct_code(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'recovery@example.com',
        ]);

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination_hash' => hash('sha256', 'recovery@example.com'),
            'code_hash' => Hash::make('123456'),
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts' => 0,
            'verified_at' => null,
        ]);

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification/verify', [
                'channel' => 'email',
                'code' => '123456',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Contacto de recuperação verificado com sucesso.',
            ]);

        $user->refresh();

        $this->assertNotNull($user->recovery_email_verified_at);

        $verification = RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->latest('id')
            ->first();

        $this->assertNotNull($verification);
        $this->assertNotNull($verification->verified_at);
        $this->assertSame(1, $verification->attempts);
    }

    public function test_authenticated_user_cannot_verify_recovery_email_with_incorrect_code(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'recovery@example.com',
        ]);

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination_hash' => hash('sha256', 'recovery@example.com'),
            'code_hash' => Hash::make('123456'),
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts' => 0,
            'verified_at' => null,
        ]);

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification/verify', [
                'channel' => 'email',
                'code' => '654321',
            ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Código de verificação inválido ou expirado.',
            ]);

        $user->refresh();

        $this->assertNull($user->recovery_email_verified_at);

        $verification = RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->latest('id')
            ->first();

        $this->assertNotNull($verification);
        $this->assertNull($verification->verified_at);
        $this->assertSame(1, $verification->attempts);
    }

    public function test_authenticated_user_cannot_verify_recovery_email_with_expired_code(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'recovery@example.com',
        ]);

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination_hash' => hash('sha256', 'recovery@example.com'),
            'code_hash' => Hash::make('123456'),
            'expires_at' => Carbon::now()->subMinute(),
            'attempts' => 0,
            'verified_at' => null,
        ]);

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification/verify', [
                'channel' => 'email',
                'code' => '123456',
            ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Código de verificação inválido ou expirado.',
            ]);

        $user->refresh();

        $this->assertNull($user->recovery_email_verified_at);

        $verification = RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->latest('id')
            ->first();

        $this->assertNotNull($verification);
        $this->assertNull($verification->verified_at);
        $this->assertSame(0, $verification->attempts);
    }

    public function test_authenticated_user_cannot_verify_recovery_email_after_maximum_attempts(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'recovery@example.com',
        ]);

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination_hash' => hash('sha256', 'recovery@example.com'),
            'code_hash' => Hash::make('123456'),
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts' => 5,
            'verified_at' => null,
        ]);

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification/verify', [
                'channel' => 'email',
                'code' => '123456',
            ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Código de verificação inválido ou expirado.',
            ]);

        $user->refresh();

        $this->assertNull($user->recovery_email_verified_at);

        $verification = RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->latest('id')
            ->first();

        $this->assertNotNull($verification);
        $this->assertNull($verification->verified_at);
        $this->assertSame(5, $verification->attempts);
    }

    public function test_authenticated_user_cannot_verify_recovery_email_when_destination_hash_does_not_match(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'recovery@example.com',
        ]);

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination_hash' => hash('sha256', 'another@example.com'),
            'code_hash' => Hash::make('123456'),
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts' => 0,
            'verified_at' => null,
        ]);

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification/verify', [
                'channel' => 'email',
                'code' => '123456',
            ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Código de verificação inválido ou expirado.',
            ]);

        $user->refresh();

        $this->assertNull($user->recovery_email_verified_at);

        $verification = RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->latest('id')
            ->first();

        $this->assertNotNull($verification);
        $this->assertNull($verification->verified_at);
        $this->assertSame(0, $verification->attempts);
    }

    public function test_authenticated_user_cannot_verify_recovery_email_with_already_verified_challenge(): void
    {
        [$user, $token] = $this->authenticatedUser();

        $user->update([
            'recovery_email' => 'recovery@example.com',
        ]);

        $verifiedAt = Carbon::now()->subMinute()->startOfSecond();

        RecoveryContactVerification::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination_hash' => hash('sha256', 'recovery@example.com'),
            'code_hash' => Hash::make('123456'),
            'expires_at' => Carbon::now()->addMinutes(10),
            'attempts' => 1,
            'verified_at' => $verifiedAt,
        ]);

        $response = $this
            ->withToken($token)
            ->postJson('/api/auth/recovery-contacts/verification/verify', [
                'channel' => 'email',
                'code' => '123456',
            ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Código de verificação inválido ou expirado.',
            ]);

        $user->refresh();

        $this->assertNull($user->recovery_email_verified_at);

        $verification = RecoveryContactVerification::query()
            ->where('user_id', $user->id)
            ->where('channel', 'email')
            ->latest('id')
            ->first();

        $this->assertNotNull($verification);
        $this->assertNotNull($verification->verified_at);
        $this->assertTrue(
            $verification->verified_at->equalTo($verifiedAt)
        );
        $this->assertSame(1, $verification->attempts);
    }
}
