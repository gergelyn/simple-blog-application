<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected PostPolicy $policy;
    protected User $user;
    protected User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new PostPolicy();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    }

    #[Test]
    public function any_authenticated_user_can_create_posts()
    {
        $result = $this->policy->create($this->user);

        $this->assertTrue($result);
    }

    #[Test]
    public function unauthenticated_user_cannot_update_posts()
    {
        $post = Post::factory()->create();

        $result = $this->policy->update(null, $post);

        $this->assertFalse($result);
    }

    #[Test]
    public function user_can_update_their_own_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $result = $this->policy->update($this->user, $post);

        $this->assertTrue($result);
    }

    #[Test]
    public function user_cannot_update_another_users_post()
    {
        $post = Post::factory()->create(['user_id' => $this->anotherUser->id]);

        $result = $this->policy->update($this->user, $post);

        $this->assertFalse($result);
    }

    #[Test]
    public function unauthenticated_user_cannot_delete_posts()
    {
        $post = Post::factory()->create();

        $result = $this->policy->delete(null, $post);

        $this->assertFalse($result);
    }

    #[Test]
    public function user_can_delete_their_own_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $result = $this->policy->delete($this->user, $post);

        $this->assertTrue($result);
    }

    #[Test]
    public function user_cannot_delete_another_users_post()
    {
        $post = Post::factory()->create(['user_id' => $this->anotherUser->id]);

        $result = $this->policy->delete($this->user, $post);

        $this->assertFalse($result);
    }

    #[Test]
    public function policy_correctly_identifies_ownership()
    {
        $ownPost = Post::factory()->create(['user_id' => $this->user->id]);
        $otherPost = Post::factory()->create(['user_id' => $this->anotherUser->id]);

        $this->assertTrue($this->policy->update($this->user, $ownPost));
        $this->assertTrue($this->policy->delete($this->user, $ownPost));
        
        $this->assertFalse($this->policy->update($this->user, $otherPost));
        $this->assertFalse($this->policy->delete($this->user, $otherPost));
    }
} 