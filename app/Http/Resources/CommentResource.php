<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'author' => [
                'name' => $this->author_name,
                'is_guest' => $this->isGuestComment(),
                'user_id' => $this->user_id,
            ],
            'post_id' => $this->post_id,
            'created_at' => $this->created_at->toISOString(),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
} 