<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertDatabaseHas('role_user', [
            'user_id' => $response->json('user.id'),
            'role_id' => Role::query()
                ->where('slug', 'member')
                ->valueOrFail('id'),
        ]);
    }

    public function test_public_registration_ignores_privileged_role_input(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_slugs' => ['admin'],
        ]);

        $response->assertCreated();

        $user = User::query()
            ->where('email', 'test@example.com')
            ->firstOrFail();

        $this->assertTrue($user->hasRole('member'));
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_user_can_login(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertUnprocessable();
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $register->assertCreated();

        $token = $register->json('token');

        $response = $this
            ->withToken($token)
            ->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'user',
            ]);
    }
}
