<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Sanctum\PersonalAccessToken;

class StoreCommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'comment' => [
                'required',
                'string',
                'min:3',
                'max:1000',
            ],
        ];

        $hasValidToken = $this->hasValidBearerToken();
        
        if (!$hasValidToken) {
            $rules['guest_name'] = [
                'required',
                'string',
                'min:2',
                'max:100',
            ];
        }

        return $rules;
    }

    /**
     * Check if request has a valid Bearer token.
     */
    private function hasValidBearerToken(): bool
    {
        if ($token = $this->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($token);
            return $accessToken && $accessToken->tokenable;
        }
        
        return false;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'comment.required' => 'A comment is required.',
            'comment.min' => 'The comment must be at least 3 characters.',
            'comment.max' => 'The comment cannot exceed 1,000 characters.',
            'guest_name.required' => 'A name is required for guest comments.',
            'guest_name.min' => 'The name must be at least 2 characters.',
            'guest_name.max' => 'The name cannot exceed 100 characters.',
        ];
    }
} 