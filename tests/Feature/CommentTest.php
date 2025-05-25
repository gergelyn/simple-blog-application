<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $anotherUser;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function authenticated_user_can_create_comment()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => 'This is a great post! Thanks for sharing.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Comment created successfully.',
                'data' => [
                    'comment' => $commentData['comment'],
                    'author' => [
                        'name' => $this->user->name,
                        'is_guest' => false,
                        'user_id' => $this->user->id,
                    ],
                    'post_id' => $this->post->id,
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'comment' => $commentData['comment'],
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'guest_name' => null,
        ]);
    }

    #[Test]
    public function guest_can_create_comment_with_guest_name()
    {
        $commentData = [
            'comment' => 'Great post! I really enjoyed reading it.',
            'guest_name' => 'John Doe',
        ];

        $response = $this->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Comment created successfully.',
                'data' => [
                    'comment' => $commentData['comment'],
                    'author' => [
                        'name' => $commentData['guest_name'],
                        'is_guest' => true,
                        'user_id' => null,
                    ],
                    'post_id' => $this->post->id,
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'comment' => $commentData['comment'],
            'post_id' => $this->post->id,
            'user_id' => null,
            'guest_name' => $commentData['guest_name'],
        ]);
    }

    #[Test]
    public function guest_comment_requires_guest_name()
    {
        $commentData = [
            'comment' => 'Great post! I really enjoyed reading it.',
        ];

        $response = $this->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name'])
            ->assertJson([
                'errors' => [
                    'guest_name' => ['A name is required for guest comments.']
                ]
            ]);
    }

    #[Test]
    public function authenticated_user_does_not_need_guest_name()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => 'This is a great post!',
            'guest_name' => 'Should be ignored',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'comment' => $commentData['comment'],
            'user_id' => $this->user->id,
            'guest_name' => null,
        ]);
    }

    #[Test]
    public function comment_creation_requires_comment_text()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson("/api/posts/{$this->post->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    #[Test]
    public function comment_must_be_at_least_3_characters()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => 'Hi',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    #[Test]
    public function comment_cannot_exceed_1000_characters()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => str_repeat('a', 1001),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    #[Test]
    public function comment_creation_returns_404_for_non_existent_post()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => 'This post does not exist.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/posts/999/comments', $commentData);

        $response->assertStatus(404);
    }

    #[Test]
    public function comment_owner_can_delete_their_comment()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment deleted successfully.',
            ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function post_owner_can_delete_any_comment_on_their_post()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->anotherUser->id,
        ]);

        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Comment deleted successfully.',
            ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function user_cannot_delete_another_users_comment_on_another_users_post()
    {
        $anotherPost = Post::factory()->create(['user_id' => $this->anotherUser->id]);
        $comment = Comment::factory()->create([
            'post_id' => $anotherPost->id,
            'user_id' => $this->anotherUser->id,
        ]);

        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function guest_cannot_delete_comments()
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => null,
            'guest_name' => 'Guest User',
        ]);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function comment_deletion_returns_404_for_non_existent_comment()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/comments/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function post_shows_comments_in_response()
    {
        Comment::factory()->count(3)->create(['post_id' => $this->post->id]);

        $response = $this->getJson("/api/posts/{$this->post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'content',
                    'comments' => [
                        '*' => [
                            'id',
                            'comment',
                            'author' => [
                                'name',
                                'is_guest',
                                'user_id',
                            ],
                            'created_at',
                            'created_at_human',
                        ]
                    ],
                    'comments_count',
                ]
            ]);

        $this->assertEquals(3, $response->json('data.comments_count'));
        $this->assertCount(3, $response->json('data.comments'));
    }

    #[Test]
    public function comments_are_ordered_by_latest_first()
    {
        $firstComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'created_at' => now()->subHour(),
        ]);
        
        $secondComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson("/api/posts/{$this->post->id}");

        $comments = $response->json('data.comments');
        $this->assertEquals($secondComment->id, $comments[0]['id']);
        $this->assertEquals($firstComment->id, $comments[1]['id']);
    }

    #[Test]
    public function guest_name_validation_requires_minimum_length()
    {
        $commentData = [
            'comment' => 'Great post!',
            'guest_name' => 'A',
        ];

        $response = $this->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name']);
    }

    #[Test]
    public function guest_name_validation_has_maximum_length()
    {
        $commentData = [
            'comment' => 'Great post!',
            'guest_name' => str_repeat('a', 101),
        ];

        $response = $this->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name']);
    }
} 