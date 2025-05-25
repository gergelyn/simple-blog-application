<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected CommentPolicy $policy;
    protected User $user;
    protected User $anotherUser;
    protected User $postOwner;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new CommentPolicy();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        $this->postOwner = User::factory()->create();
    }

    #[Test]
    public function unauthenticated_user_cannot_delete_comments()
    {
        $comment = Comment::factory()->create();

        $result = $this->policy->delete(null, $comment);

        $this->assertFalse($result);
    }

    #[Test]
    public function comment_owner_can_delete_their_own_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->user->id]);

        $result = $this->policy->delete($this->user, $comment);

        $this->assertTrue($result);
    }

    #[Test]
    public function user_cannot_delete_another_users_comment()
    {
        $comment = Comment::factory()->create(['user_id' => $this->anotherUser->id]);

        $result = $this->policy->delete($this->user, $comment);

        $this->assertFalse($result);
    }

    #[Test]
    public function post_owner_can_delete_any_comment_on_their_post()
    {
        $post = Post::factory()->create(['user_id' => $this->postOwner->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $this->anotherUser->id,
        ]);

        $result = $this->policy->delete($this->postOwner, $comment);

        $this->assertTrue($result);
    }

    #[Test]
    public function user_cannot_delete_guest_comment_on_another_users_post()
    {
        $post = Post::factory()->create(['user_id' => $this->postOwner->id]);
        $guestComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => null,
            'guest_name' => 'Guest User',
        ]);

        $result = $this->policy->delete($this->user, $guestComment);

        $this->assertFalse($result);
    }

    #[Test]
    public function post_owner_can_delete_guest_comments_on_their_post()
    {
        $post = Post::factory()->create(['user_id' => $this->postOwner->id]);
        $guestComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => null,
            'guest_name' => 'Guest User',
        ]);

        $result = $this->policy->delete($this->postOwner, $guestComment);

        $this->assertTrue($result);
    }

    #[Test]
    public function policy_correctly_handles_complex_ownership_scenarios()
    {
        $userPost = Post::factory()->create(['user_id' => $this->user->id]);
        $anotherUserPost = Post::factory()->create(['user_id' => $this->anotherUser->id]);

        $userCommentOnOwnPost = Comment::factory()->create([
            'post_id' => $userPost->id,
            'user_id' => $this->user->id,
        ]);

        $userCommentOnOtherPost = Comment::factory()->create([
            'post_id' => $anotherUserPost->id,
            'user_id' => $this->user->id,
        ]);

        $otherUserCommentOnUserPost = Comment::factory()->create([
            'post_id' => $userPost->id,
            'user_id' => $this->anotherUser->id,
        ]);

        // User can delete their own comment on their own post
        $this->assertTrue($this->policy->delete($this->user, $userCommentOnOwnPost));

        // User can delete their own comment on another user's post
        $this->assertTrue($this->policy->delete($this->user, $userCommentOnOtherPost));

        // User can delete another user's comment on their own post (as post owner)
        $this->assertTrue($this->policy->delete($this->user, $otherUserCommentOnUserPost));

        // Another user cannot delete user's comment on user's post
        $this->assertFalse($this->policy->delete($this->anotherUser, $userCommentOnOwnPost));
    }
} 