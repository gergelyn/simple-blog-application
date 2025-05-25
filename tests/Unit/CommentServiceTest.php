<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentService $commentService;
    protected User $user;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->commentService = new CommentService();
        $this->user = User::factory()->create();
        $this->post = Post::factory()->create();
    }

    #[Test]
    public function it_can_create_authenticated_user_comment()
    {
        $commentData = [
            'comment' => 'This is a test comment from authenticated user.',
        ];

        $comment = $this->commentService->createComment($this->post, $commentData, $this->user);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals($commentData['comment'], $comment->comment);
        $this->assertEquals($this->post->id, $comment->post_id);
        $this->assertEquals($this->user->id, $comment->user_id);
        $this->assertNull($comment->guest_name);
        $this->assertTrue($comment->relationLoaded('user'));
        $this->assertTrue($comment->relationLoaded('post'));

        $this->assertDatabaseHas('comments', [
            'comment' => $commentData['comment'],
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'guest_name' => null,
        ]);
    }

    #[Test]
    public function it_can_create_guest_comment()
    {
        $commentData = [
            'comment' => 'This is a test comment from guest.',
            'guest_name' => 'John Doe',
        ];

        $comment = $this->commentService->createComment($this->post, $commentData, null);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals($commentData['comment'], $comment->comment);
        $this->assertEquals($this->post->id, $comment->post_id);
        $this->assertNull($comment->user_id);
        $this->assertEquals($commentData['guest_name'], $comment->guest_name);

        $this->assertDatabaseHas('comments', [
            'comment' => $commentData['comment'],
            'post_id' => $this->post->id,
            'user_id' => null,
            'guest_name' => $commentData['guest_name'],
        ]);
    }

    #[Test]
    public function it_can_get_comment_by_id()
    {
        $comment = Comment::factory()->create();

        $retrievedComment = $this->commentService->getCommentById($comment->id);

        $this->assertEquals($comment->id, $retrievedComment->id);
        $this->assertEquals($comment->comment, $retrievedComment->comment);
        $this->assertTrue($retrievedComment->relationLoaded('user'));
        $this->assertTrue($retrievedComment->relationLoaded('post'));
    }

    #[Test]
    public function it_throws_exception_for_non_existent_comment()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->commentService->getCommentById(999);
    }

    #[Test]
    public function it_can_delete_comment()
    {
        $comment = Comment::factory()->create();
        $commentId = $comment->id;

        $result = $this->commentService->deleteComment($comment);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('comments', [
            'id' => $commentId,
        ]);
    }

    #[Test]
    public function comment_has_correct_author_name_for_authenticated_user()
    {
        $commentData = [
            'comment' => 'Test comment',
        ];

        $comment = $this->commentService->createComment($this->post, $commentData, $this->user);

        $this->assertEquals($this->user->name, $comment->author_name);
        $this->assertFalse($comment->isGuestComment());
        $this->assertTrue($comment->isUserComment());
    }

    #[Test]
    public function comment_has_correct_author_name_for_guest()
    {
        $guestName = 'Jane Doe';
        $commentData = [
            'comment' => 'Test guest comment',
            'guest_name' => $guestName,
        ];

        $comment = $this->commentService->createComment($this->post, $commentData, null);

        $this->assertEquals($guestName, $comment->author_name);
        $this->assertTrue($comment->isGuestComment());
        $this->assertFalse($comment->isUserComment());
    }

    #[Test]
    public function anonymous_guest_comment_gets_anonymous_name()
    {
        $commentData = [
            'comment' => 'Test anonymous comment',
        ];

        $comment = Comment::factory()->create([
            'user_id' => null,
            'guest_name' => null,
            'comment' => $commentData['comment'],
        ]);

        $this->assertEquals('Anonymous', $comment->author_name);
        $this->assertTrue($comment->isGuestComment());
    }
} 