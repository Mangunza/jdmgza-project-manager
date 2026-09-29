<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_user_can_request_a_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'member@example.com',
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo(
            $user,
            ResetPassword::class
        );
    }

    public function test_unknown_email_receives_generic_response(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'unknown@example.com',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'OldPassword123!',
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertTrue(
            password_verify('NewPassword123!', $user->fresh()->password)
        );
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertUnprocessable();
    }
}
