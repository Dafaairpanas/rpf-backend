<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Super Admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'division' => 'IT',
                'role_id' => $adminRole?->id,
            ]
        );
    }
}
