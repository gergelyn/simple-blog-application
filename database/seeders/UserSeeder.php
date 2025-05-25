<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin/demo users with known credentials
        $usersDatas = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($usersDatas as $userData) {
            User::create($userData);
        }

        // Create additional random users
        User::factory()
            ->count(15)
            ->create();

        $this->command->info('Created ' . User::count() . ' users successfully!');
        $this->command->info('Demo users created with email/password:');
        $this->command->info('- john@example.com / password');
        $this->command->info('- jane@example.com / password');
        $this->command->info('- admin@example.com / password');
        $this->command->info('- demo@example.com / password');
    }
}
