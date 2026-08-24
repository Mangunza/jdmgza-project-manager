<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Johnny Mangunza',
                'email' => 'johnny@test.com',
                'password' => '12345678',
                'role_slug' => 'admin',
            ],
            [
                'name' => 'Project Manager',
                'email' => 'manager@test.com',
                'password' => '12345678',
                'role_slug' => 'manager',
            ],
            [
                'name' => 'Project Member',
                'email' => 'member@test.com',
                'password' => '12345678',
                'role_slug' => 'member',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('slug', $userData['role_slug'])->first();

            if (! $role) {
                $this->command->warn(
                    "Role '{$userData['role_slug']}' não encontrada. O utilizador foi ignorado."
                );

                continue;
            }

            $user = User::firstOrCreate(
                [
                    'email' => $userData['email'],
                ],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ]
            );

            $user->roles()->sync([
                $role->id,
            ]);
        }
    }
}
