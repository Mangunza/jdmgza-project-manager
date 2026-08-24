<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('bootstrap.initial_admin');

        if (! $admin['name'] || ! $admin['email'] || ! $admin['password']) {
            return;
        }

        if (User::query()->where('email', $admin['email'])->exists()) {
            return;
        }

        $role = Role::query()->where('slug', 'admin')->firstOrFail();

        $user = User::create([
            'name' => $admin['name'],
            'email' => $admin['email'],
            'password' => $admin['password'],
        ]);

        $user->roles()->sync([$role->id]);
    }
}
