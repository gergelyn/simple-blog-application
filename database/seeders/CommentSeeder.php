<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $users = User::all();

        if ($posts->isEmpty()) {
            $this->command->error('No posts found! Please run PostSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('No users found! Please run UserSeeder first.');
            return;
        }

        // Sample realistic comments for different types of posts
        $sampleComments = [
            // Positive comments
            "Great post! This really helped me understand the topic better.",
            "Thanks for sharing this valuable information. Very insightful!",
            "Excellent explanation! I'll definitely try this approach.",
            "This is exactly what I was looking for. Much appreciated!",
            "Well written and easy to follow. Keep up the great work!",
            "Love this! Can't wait to implement these ideas.",
            "Fantastic article! You've covered all the important points.",
            "This post is a game-changer. Thank you for the detailed explanation.",
            
            // Questions and engagement
            "Could you elaborate more on the third point? I'm a bit confused.",
            "Have you tried this approach in production? What were the results?",
            "What would you recommend for beginners who are just starting out?",
            "Do you have any resources for further reading on this topic?",
            "How does this compare to the traditional approach?",
            "Is there a specific tool you'd recommend for this?",
            "What are the potential drawbacks of this method?",
            
            // Technical comments
            "I've been using this technique for months and it works great!",
            "One thing to note is that this might not work in older versions.",
            "You might want to add error handling for edge cases.",
            "I found that adding caching significantly improves performance.",
            "Consider using dependency injection for better testability.",
            "This approach scales well but watch out for memory usage.",
            
            // General engagement
            "Looking forward to more posts like this!",
            "Your blog has become my go-to resource for learning.",
            "Please write more about this topic!",
            "I shared this with my team - they loved it too!",
            "Bookmarked for future reference. Thanks!",
            "This deserves more visibility. Great work!",
        ];

        $guestNames = [
            'Alex Johnson', 'Sarah Wilson', 'Mike Chen', 'Emily Davis',
            'David Brown', 'Lisa Garcia', 'Tom Anderson', 'Maria Rodriguez',
            'James Taylor', 'Anna Thompson', 'Chris Lee', 'Jessica White',
            'Ryan Martinez', 'Amanda Clark', 'Kevin Lewis', 'Rachel Green',
            'Daniel Hall', 'Nicole Young', 'Brandon King', 'Stephanie Wright'
        ];

        $totalComments = 0;
        $userComments = 0;
        $guestComments = 0;

        // Add comments to each post
        foreach ($posts as $post) {
            // Random number of comments per post (0-8)
            $commentCount = rand(0, 8);
            
            for ($i = 0; $i < $commentCount; $i++) {
                $isGuestComment = rand(1, 100) <= 30; // 30% chance of guest comment
                
                $commentData = [
                    'post_id' => $post->id,
                    'comment' => $sampleComments[array_rand($sampleComments)],
                    'created_at' => $post->created_at->addDays(rand(0, 10))->addHours(rand(1, 23)),
                ];

                if ($isGuestComment) {
                    // Create guest comment
                    Comment::create([
                        ...$commentData,
                        'user_id' => null,
                        'guest_name' => $guestNames[array_rand($guestNames)],
                    ]);
                    $guestComments++;
                } else {
                    // Create user comment
                    Comment::create([
                        ...$commentData,
                        'user_id' => $users->random()->id,
                        'guest_name' => null,
                    ]);
                    $userComments++;
                }
                
                $totalComments++;
            }
        }

        // Add some additional random comments using factories
        $additionalUserComments = 20;
        $additionalGuestComments = 10;

        // Create additional user comments
        for ($i = 0; $i < $additionalUserComments; $i++) {
            Comment::factory()
                ->byUser($users->random())
                ->onPost($posts->random())
                ->create([
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            $userComments++;
            $totalComments++;
        }

        // Create additional guest comments
        for ($i = 0; $i < $additionalGuestComments; $i++) {
            Comment::factory()
                ->byGuest($guestNames[array_rand($guestNames)])
                ->onPost($posts->random())
                ->create([
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            $guestComments++;
            $totalComments++;
        }

        // Create some long comments
        Comment::factory()
            ->count(5)
            ->longComment()
            ->create([
                'post_id' => $posts->random()->id,
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(rand(1, 20)),
            ]);
        $totalComments += 5;
        $userComments += 5;

        // Create some short comments
        Comment::factory()
            ->count(8)
            ->shortComment()
            ->create([
                'post_id' => $posts->random()->id,
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(rand(1, 15)),
            ]);
        $totalComments += 8;
        $userComments += 8;

        $this->command->info("Created {$totalComments} comments successfully!");
        $this->command->info("- User comments: {$userComments}");
        $this->command->info("- Guest comments: {$guestComments}");
        $this->command->info("Comments distributed across {$posts->count()} posts");
        $this->command->info("Average comments per post: " . round($totalComments / $posts->count(), 1));
    }
}
