<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'guest_name' => null,
            'comment' => $this->faker->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a comment by an authenticated user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'guest_name' => null,
        ]);
    }

    /**
     * Create a comment by a guest.
     */
    public function byGuest(?string $guestName = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'guest_name' => $guestName ?? $this->faker->name(),
        ]);
    }

    /**
     * Create a comment on a specific post.
     */
    public function onPost(Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'post_id' => $post->id,
        ]);
    }

    /**
     * Create a comment with long content.
     */
    public function longComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => $this->faker->paragraphs(5, true),
        ]);
    }

    /**
     * Create a comment with short content.
     */
    public function shortComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => $this->faker->sentence(),
        ]);
    }
} 