<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    }

    #[Test]
    public function it_can_list_all_posts_without_authentication()
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'author' => [
                            'id',
                            'name',
                        ],
                        'created_at',
                        'updated_at',
                        'created_at_human',
                        'updated_at_human',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function it_can_show_a_single_post_without_authentication()
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'content',
                    'author' => [
                        'id',
                        'name',
                    ],
                    'comments',
                    'comments_count',
                    'created_at',
                    'updated_at',
                    'created_at_human',
                    'updated_at_human',
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => $post->content,
                ]
            ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_post()
    {
        $response = $this->getJson('/api/posts/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Post not found.'
            ]);
    }

    #[Test]
    public function authenticated_user_can_create_post()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $postData = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/posts', $postData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Post created successfully!',
                'data' => [
                    'title' => $postData['title'],
                    'content' => $postData['content'],
                    'author' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'content' => $postData['content'],
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function guest_cannot_create_post()
    {
        $postData = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(401);
    }

    #[Test]
    public function post_creation_requires_title()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $postData = [
            'content' => $this->faker->paragraphs(3, true),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/posts', $postData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function post_creation_requires_content()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $postData = [
            'title' => $this->faker->sentence(),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/posts', $postData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function post_owner_can_update_their_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content for the post.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Post updated successfully!',
                'data' => [
                    'id' => $post->id,
                    'title' => $updateData['title'],
                    'content' => $updateData['content'],
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
            'content' => $updateData['content'],
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function user_cannot_update_another_users_post()
    {
        $post = Post::factory()->create(['user_id' => $this->anotherUser->id]);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Trying to update another user\'s post',
            'content' => 'This should not be allowed.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_update_post()
    {
        $post = Post::factory()->create();

        $updateData = [
            'title' => 'Guest trying to update post',
            'content' => 'This should not be allowed.',
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(401);
    }

    #[Test]
    public function post_owner_can_delete_their_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Post deleted successfully!',
            ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    #[Test]
    public function user_cannot_delete_another_users_post()
    {
        $post = Post::factory()->create(['user_id' => $this->anotherUser->id]);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }

    #[Test]
    public function guest_cannot_delete_post()
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(401);
    }

    #[Test]
    public function it_returns_404_when_trying_to_update_non_existent_post()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'This post does not exist',
            'content' => 'So this should return 404.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson('/api/posts/999', $updateData);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_trying_to_delete_non_existent_post()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/posts/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_paginate_posts()
    {
        Post::factory()->count(25)->create();

        $response = $this->getJson('/api/posts?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'total_pages',
                    'has_more_pages',
                ],
            ]);

        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(10, $response->json('meta.per_page'));
        $this->assertEquals(25, $response->json('meta.total'));
        $this->assertEquals(3, $response->json('meta.total_pages'));
    }

    #[Test]
    public function it_limits_pagination_to_maximum_50_per_page()
    {
        Post::factory()->count(100)->create();

        $response = $this->getJson('/api/posts?per_page=100');

        $response->assertStatus(200);
        $this->assertEquals(50, $response->json('meta.per_page'));
        $this->assertEquals(100, $response->json('meta.total'));
        $this->assertEquals(50, $response->json('meta.count'));
    }

    #[Test]
    public function post_update_allows_partial_updates()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $originalTitle = $post->title;
        $newContent = 'This is updated content only.';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson("/api/posts/{$post->id}", [
            'content' => $newContent,
        ]);

        $response->assertStatus(200);

        $post->refresh();
        $this->assertEquals($originalTitle, $post->title);
        $this->assertEquals($newContent, $post->content);
    }
} 