<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        config()->set('bootstrap.initial_admin', [
            'name' => null,
            'email' => null,
            'password' => null,
        ]);

        parent::tearDown();
    }

    public function test_seed_initializes_essential_roles_permissions_and_relationships(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, Role::query()->count());
        $this->assertSame(17, Permission::query()->count());

        $admin = Role::query()->where('slug', 'admin')->firstOrFail();
        $member = Role::query()->where('slug', 'member')->firstOrFail();

        $this->assertSame(17, $admin->permissions()->count());
        $this->assertTrue($member->hasPermission('dashboard.view'));
        $this->assertTrue($member->hasPermission('projects.view'));
    }

    public function test_seed_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, Role::query()->count());
        $this->assertSame(17, Permission::query()->count());
        $this->assertSame(17, Role::query()
            ->where('slug', 'admin')
            ->firstOrFail()
            ->permissions()
            ->count());
    }

    public function test_seed_creates_configured_initial_administrator_once(): void
    {
        config()->set('bootstrap.initial_admin', [
            'name' => 'Administrador Inicial',
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame(1, User::query()
            ->where('email', 'admin@example.com')
            ->count());
        $this->assertTrue(Hash::check('Password123!', $admin->password));
        $this->assertTrue($admin->hasRole('admin'));
    }
}
