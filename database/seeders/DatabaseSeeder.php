<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->command->newLine();

        // Seed users first (required for posts and comments)
        $this->command->info('Seeding users...');
        $this->call(UserSeeder::class);
        $this->command->newLine();

        // Seed posts (requires users)
        $this->command->info('Seeding posts...');
        $this->call(PostSeeder::class);
        $this->command->newLine();

        // Seed comments (requires users and posts)
        $this->command->info('Seeding comments...');
        $this->call(CommentSeeder::class);
        $this->command->newLine();

        $this->command->info('Database seeding completed successfully!');
        $this->command->newLine();
        
        $this->command->info('Sample Data Summary:');
        $this->command->info('- Users: ' . \App\Models\User::count());
        $this->command->info('- Posts: ' . \App\Models\Post::count());
        $this->command->info('- Comments: ' . \App\Models\Comment::count());
        $this->command->newLine();
        
        $this->command->info('Demo Login Credentials:');
        $this->command->info('Email: john@example.com | Password: password');
        $this->command->info('Email: jane@example.com | Password: password');
        $this->command->info('Email: admin@example.com | Password: password');
        $this->command->info('Email: demo@example.com | Password: password');
        $this->command->newLine();
        
        $this->command->info('The Simple Blog Application built for Ominimo\'s Test Assignment is ready to use!');
    }
}
