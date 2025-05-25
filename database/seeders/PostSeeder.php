<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to distribute posts among them
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found! Please run UserSeeder first.');
            return;
        }

        // Create some featured posts with specific content
        $featuredPosts = [
            [
                'title' => 'Welcome to Our Blog Platform',
                'content' => "Welcome to our amazing blog platform! This is where you can share your thoughts, ideas, and experiences with the world.\n\nOur platform offers:\n- Easy-to-use interface\n- Comment system for engagement\n- User authentication\n- Responsive design\n\nWe're excited to have you here and can't wait to see what you'll share!",
            ],
            [
                'title' => 'Getting Started with Laravel Development',
                'content' => "Laravel is one of the most popular PHP frameworks, and for good reason. It provides an elegant syntax and powerful features that make web development a joy.\n\nKey features of Laravel include:\n- Eloquent ORM for database interactions\n- Blade templating engine\n- Artisan command-line tool\n- Built-in authentication\n- Comprehensive testing tools\n\nWhether you're a beginner or an experienced developer, Laravel has something to offer.",
            ],
            [
                'title' => 'The Future of Web Development',
                'content' => "Web development is constantly evolving, with new technologies and frameworks emerging regularly. In this post, we'll explore some of the trends shaping the future of web development.\n\n1. **Progressive Web Apps (PWAs)**: Bridging the gap between web and mobile apps\n2. **Serverless Architecture**: Reducing infrastructure complexity\n3. **AI Integration**: Making applications smarter and more intuitive\n4. **WebAssembly**: Bringing near-native performance to the web\n\nStaying up-to-date with these trends is crucial for any web developer.",
            ],
            [
                'title' => 'Building Scalable APIs with Laravel',
                'content' => "APIs are the backbone of modern web applications. Laravel provides excellent tools for building robust, scalable APIs.\n\nBest practices for Laravel APIs:\n- Use API Resources for consistent data formatting\n- Implement proper authentication (Sanctum/Passport)\n- Add rate limiting to prevent abuse\n- Use proper HTTP status codes\n- Document your API endpoints\n- Implement comprehensive testing\n\nFollowing these practices will help you build APIs that can grow with your application.",
            ],
            [
                'title' => 'Docker for Laravel Development',
                'content' => "Docker has revolutionized how we develop and deploy applications. For Laravel developers, Docker offers consistent development environments and simplified deployment.\n\nBenefits of using Docker with Laravel:\n- Consistent environment across team members\n- Easy dependency management\n- Simplified deployment process\n- Isolation from host system\n- Easy scaling and orchestration\n\nWhether you're working solo or with a team, Docker can significantly improve your development workflow.",
            ],
        ];

        // Create featured posts
        foreach ($featuredPosts as $index => $postData) {
            Post::create([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
        }

        // Create additional random posts
        $additionalPostsCount = 25;
        
        for ($i = 0; $i < $additionalPostsCount; $i++) {
            Post::factory()
                ->for($users->random())
                ->create([
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now()->subDays(rand(0, 10)),
                ]);
        }

        // Create some posts with longer content
        Post::factory()
            ->count(5)
            ->longContent()
            ->create([
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(rand(1, 45)),
            ]);

        // Create some posts with shorter content
        Post::factory()
            ->count(5)
            ->shortContent()
            ->create([
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(rand(1, 15)),
            ]);

        $totalPosts = Post::count();
        $this->command->info("Created {$totalPosts} posts successfully!");
        $this->command->info('Posts are distributed among ' . $users->count() . ' users');
        $this->command->info('Posts created with dates ranging from 60 days ago to today');
    }
}
