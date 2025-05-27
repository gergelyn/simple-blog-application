<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => 'Post Title',
                    'placeholder' => 'Enter a compelling title for your post',
                    'required' => true,
                    'validation' => [
                        'required' => true,
                        'min_length' => 3,
                        'max_length' => 255,
                    ],
                    'value' => $this->resource ? $this->resource->title : '',
                    'help_text' => 'Title must be between 3 and 255 characters.',
                ],
                'content' => [
                    'type' => 'textarea',
                    'label' => 'Post Content',
                    'placeholder' => 'Write your post content here...',
                    'required' => true,
                    'validation' => [
                        'required' => true,
                        'min_length' => 10,
                        'max_length' => 10000,
                    ],
                    'value' => $this->resource ? $this->resource->content : '',
                    'help_text' => 'Content must be between 10 and 10,000 characters.',
                    'rows' => 10,
                ],
            ],
            'validation_rules' => [
                'title' => [
                    'required' => 'A post title is required.',
                    'min' => 'The title must be at least 3 characters.',
                    'max' => 'The title cannot exceed 255 characters.',
                ],
                'content' => [
                    'required' => 'Post content is required.',
                    'min' => 'The content must be at least 10 characters.',
                    'max' => 'The content cannot exceed 10,000 characters.',
                ],
            ],
            'submit_url' => $this->resource 
                ? route('api.posts.update', $this->resource->id)
                : route('api.posts.store'),
            'method' => $this->resource ? 'PUT' : 'POST',
            'data' => $this->when($this->resource, function () {
                return [
                    'id' => $this->resource->id,
                    'title' => $this->resource->title,
                    'content' => $this->resource->content,
                    'author' => [
                        'id' => $this->resource->user->id,
                        'name' => $this->resource->user->name,
                    ],
                    'created_at' => $this->resource->created_at,
                    'updated_at' => $this->resource->updated_at,
                ];
            }),
        ];
    }
} 