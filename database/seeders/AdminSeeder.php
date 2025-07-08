<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the main admin from the original database
        Admin::create([
            'id' => '2024JUS371',
            'adminname' => 'JUSTO',
            'adminpassword' => Hash::make('12345'), // Using a simple password for demo
        ]);

        // Create additional admin for testing
        Admin::create([
            'id' => '2024ADM001',
            'adminname' => 'System Admin',
            'adminpassword' => Hash::make('admin123'),
        ]);
    }
}
