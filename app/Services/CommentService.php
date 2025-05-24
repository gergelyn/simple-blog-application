<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentService
{
    /**
     * Create a comment on a post.
     */
    public function createComment(Post $post, array $data, ?User $user = null): Comment
    {
        try {
            return DB::transaction(function () use ($post, $data, $user) {
                $commentData = [
                    'post_id' => $post->id,
                    'comment' => $data['comment'],
                ];

                if ($user) {
                    $commentData['user_id'] = $user->id;
                } else {
                    $commentData['guest_name'] = $data['guest_name'];
                }

                $comment = Comment::create($commentData);
                $comment->load(['user', 'post']);

                Log::info("Comment created successfully: {$comment->id} on post {$post->id}", [
                    'user_id' => $user?->id,
                    'is_guest' => !$user,
                ]);

                return $comment;
            });
        } catch (\Exception $e) {
            Log::error('Error creating comment: ' . $e->getMessage(), [
                'post_id' => $post->id,
                'user_id' => $user?->id,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Get a comment by ID.
     */
    public function getCommentById(int $id): Comment
    {
        try {
            return Comment::with(['user', 'post'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning("Comment not found: {$id}");
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error fetching comment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool
    {
        try {
            return DB::transaction(function () use ($comment) {
                $deleted = $comment->delete();

                Log::info("Comment deleted successfully: {$comment->id}");

                return $deleted;
            });
        } catch (\Exception $e) {
            Log::error('Error deleting comment: ' . $e->getMessage(), [
                'comment_id' => $comment->id,
            ]);
            throw $e;
        }
    }
} 