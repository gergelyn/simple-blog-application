<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        if (!$user) {
            return false;
        }

        if ($comment->user_id && $comment->user_id === $user->id) {
            return true;
        }

        if ($comment->post->user_id === $user->id) {
            return true;
        }

        return false;
    }
} 