<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Shared\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create Organizer User
        User::updateOrCreate(
            ['email' => 'organizer@example.com'],
            [
                'name' => 'Event Organizer',
                'email' => 'organizer@example.com',
                'password' => Hash::make('password'),
                'role' => 'organizer',
                'is_active' => true,
            ]
        );

        // Create Scanner User
        User::updateOrCreate(
            ['email' => 'scanner@example.com'],
            [
                'name' => 'Event Scanner',
                'email' => 'scanner@example.com',
                'password' => Hash::make('password'),
                'role' => 'scanner',
                'is_active' => true,
            ]
        );

        $this->command->info('Admin users created successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Organizer: organizer@example.com / password');
        $this->command->info('Scanner: scanner@example.com / password');
    }
}
