<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can create posts.
     */
    public function create(User $user): bool
    {
        // Only authenticated users can create posts
        return true;
    }

    /**
     * Determine whether the user can update the post.
     */
    public function update(?User $user, Post $post): bool
    {
        // Unauthenticated users cannot update posts
        if (!$user) {
            return false;
        }

        // Only the post owner can update
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(?User $user, Post $post): bool
    {
        // Unauthenticated users cannot delete posts
        if (!$user) {
            return false;
        }

        // Only the post owner can delete
        return $user->id === $post->user_id;
    }
} 