<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService
    ) {}

    /**
     * Store a newly created comment.
     */
    public function store(StoreCommentRequest $request, int $postId): JsonResponse
    {
        $post = Post::findOrFail($postId);
        
        $user = $this->getOptionalUser($request);
        
        $comment = $this->commentService->createComment(
            $post,
            $request->validated(),
            $user
        );

        return response()->json([
            'message' => 'Comment created successfully.',
            'data' => new CommentResource($comment),
        ], 201);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = $this->commentService->getCommentById($id);
        
        $this->authorize('delete', $comment);
        
        $this->commentService->deleteComment($comment);

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }

    /**
     * Get authenticated user from Bearer token if present (Sanctum's optional auth).
     */
    private function getOptionalUser(Request $request): ?object
    {
        if ($token = $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }
        
        return null;
    }
} 