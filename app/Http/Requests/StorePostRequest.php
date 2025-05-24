<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'content' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A post title is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'content.required' => 'Post content is required.',
            'content.min' => 'The content must be at least 10 characters.',
            'content.max' => 'The content cannot exceed 10,000 characters.',
        ];
    }
} 