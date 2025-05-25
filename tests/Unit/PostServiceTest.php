<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PostService $postService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->postService = new PostService();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_can_create_a_post()
    {
        $postData = [
            'title' => 'Test Post Title',
            'content' => 'This is the content of the test post.',
        ];

        $post = $this->postService->createPost($this->user, $postData);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals($postData['title'], $post->title);
        $this->assertEquals($postData['content'], $post->content);
        $this->assertEquals($this->user->id, $post->user_id);
        $this->assertTrue($post->relationLoaded('user'));

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'content' => $postData['content'],
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_can_get_all_posts_with_pagination()
    {
        Post::factory()->count(15)->create();

        $posts = $this->postService->getAllPosts(5);

        $this->assertEquals(5, $posts->count());
        $this->assertEquals(15, $posts->total());
        $this->assertEquals(3, $posts->lastPage());
    }

    #[Test]
    public function it_can_get_post_by_id()
    {
        $post = Post::factory()->create();

        $retrievedPost = $this->postService->getPostById($post->id);

        $this->assertEquals($post->id, $retrievedPost->id);
        $this->assertEquals($post->title, $retrievedPost->title);
        $this->assertEquals($post->content, $retrievedPost->content);
        $this->assertTrue($retrievedPost->relationLoaded('user'));
        $this->assertTrue($retrievedPost->relationLoaded('comments'));
    }

    #[Test]
    public function it_throws_exception_for_non_existent_post()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->postService->getPostById(999);
    }

    #[Test]
    public function it_can_update_a_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        
        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'This is the updated content.',
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        $this->assertEquals($updateData['title'], $updatedPost->title);
        $this->assertEquals($updateData['content'], $updatedPost->content);
        $this->assertEquals($this->user->id, $updatedPost->user_id);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
            'content' => $updateData['content'],
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_can_partially_update_a_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Title',
            'content' => 'Original content',
        ]);

        $updateData = [
            'title' => 'Updated Title Only',
        ];

        $updatedPost = $this->postService->updatePost($post, $updateData);

        $this->assertEquals($updateData['title'], $updatedPost->title);
        $this->assertEquals('Original content', $updatedPost->content); // Should remain unchanged

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
            'content' => 'Original content',
        ]);
    }

    #[Test]
    public function it_can_delete_a_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $postId = $post->id;

        $result = $this->postService->deletePost($post);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('posts', [
            'id' => $postId,
        ]);
    }

    #[Test]
    public function it_uses_default_per_page_when_not_specified()
    {
        Post::factory()->count(20)->create();

        $posts = $this->postService->getAllPosts();

        $this->assertEquals(10, $posts->perPage()); // Default should be 10
    }

    #[Test]
    public function it_loads_user_relationship_when_getting_posts()
    {
        $posts = Post::factory()->count(3)->create();

        $paginatedPosts = $this->postService->getAllPosts(10);

        foreach ($paginatedPosts as $post) {
            $this->assertTrue($post->relationLoaded('user'));
            $this->assertNotNull($post->user);
        }
    }

    #[Test]
    public function it_loads_comments_and_counts_when_getting_single_post()
    {
        $post = Post::factory()->create();

        $retrievedPost = $this->postService->getPostById($post->id);

        $this->assertTrue($retrievedPost->relationLoaded('comments'));
        $this->assertTrue(isset($retrievedPost->comments_count));
    }

    #[Test]
    public function it_orders_posts_by_latest_first()
    {
        $firstPost = Post::factory()->create(['created_at' => now()->subDays(2)]);
        $secondPost = Post::factory()->create(['created_at' => now()->subDay()]);
        $thirdPost = Post::factory()->create(['created_at' => now()]);

        $posts = $this->postService->getAllPosts(10);

        $this->assertEquals($thirdPost->id, $posts->first()->id);
        $this->assertEquals($firstPost->id, $posts->last()->id);
    }
} 