<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostService
{
    /**
     * Get all posts with pagination.
     */
    public function getAllPosts(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return Post::with('user:id,name,email')
                ->latest()
                ->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Error fetching posts: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a single post by ID.
     */
    public function getPostById(int $id): Post
    {
        try {
            return Post::with('user:id,name,email')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning("Post not found: {$id}");
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error fetching post: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new post.
     */
    public function createPost(User $user, array $data): Post
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                $post = $user->posts()->create([
                    'title' => $data['title'],
                    'content' => $data['content'],
                ]);

                $post->load('user:id,name,email');

                Log::info("Post created successfully: {$post->id} by user {$user->id}");

                return $post;
            });
        } catch (\Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing post.
     */
    public function updatePost(Post $post, array $data): Post
    {
        try {
            return DB::transaction(function () use ($post, $data) {
                $post->update([
                    'title' => $data['title'] ?? $post->title,
                    'content' => $data['content'] ?? $post->content,
                ]);

                $post->load('user:id,name,email');

                Log::info("Post updated successfully: {$post->id}");

                return $post;
            });
        } catch (\Exception $e) {
            Log::error('Error updating post: ' . $e->getMessage(), [
                'post_id' => $post->id,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Delete a post.
     */
    public function deletePost(Post $post): bool
    {
        try {
            return DB::transaction(function () use ($post) {
                $deleted = $post->delete();

                Log::info("Post deleted successfully: {$post->id}");

                return $deleted;
            });
        } catch (\Exception $e) {
            Log::error('Error deleting post: ' . $e->getMessage(), [
                'post_id' => $post->id,
            ]);
            throw $e;
        }
    }
} 