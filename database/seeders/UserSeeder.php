<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        User::create([
            'username' => 'admin',
            'email' => 'admin@myattendance.com',
            'password' => Hash::make('Admin@123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'password_changed_at' => now(),
        ]);

        // Create HR Manager user
        User::create([
            'username' => 'hr_manager',
            'email' => 'hr@myattendance.com',
            'password' => Hash::make('HR@123'),
            'role' => User::ROLE_HR_MANAGER,
            'is_active' => true,
            'password_changed_at' => now(),
        ]);

        // Create Supervisor user
        User::create([
            'username' => 'supervisor',
            'email' => 'supervisor@myattendance.com',
            'password' => Hash::make('Super@123'),
            'role' => User::ROLE_SUPERVISOR,
            'is_active' => true,
            'password_changed_at' => now(),
        ]);

        // Create additional test users
        User::create([
            'username' => 'justo_admin',
            'email' => 'justo@myattendance.com',
            'password' => Hash::make('Justo@123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'password_changed_at' => now(),
        ]);
    }
}
