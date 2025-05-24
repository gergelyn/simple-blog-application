<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Display a listing of all posts.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->get('per_page', 10), 50); // Max 50 per page
            $posts = $this->postService->getAllPosts($perPage);

            return response()->json(new PostCollection($posts));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch posts.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            Gate::authorize('create', Post::class);

            $post = $this->postService->createPost(
                Auth::user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Post created successfully!',
                'data' => new PostResource($post)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified post.
     */
    public function show($id): JsonResponse
    {
        try {
            $post = $this->postService->getPostById((int) $id);

            return response()->json([
                'data' => new PostResource($post)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {

    }

    /**
     * Update the specified post.
     */
    public function update(UpdatePostRequest $request, $id): JsonResponse
    {
        try {
            $post = $this->postService->getPostById((int) $id);
            
            Gate::authorize('update', $post);

            $updatedPost = $this->postService->updatePost(
                $post,
                $request->validated()
            );

            return response()->json([
                'message' => 'Post updated successfully!',
                'data' => new PostResource($updatedPost)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found.'
            ], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'You can only edit your own posts.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $post = $this->postService->getPostById((int) $id);
            
            Gate::authorize('delete', $post);

            $this->postService->deletePost($post);

            return response()->json([
                'message' => 'Post deleted successfully!'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found.'
            ], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'You can only delete your own posts.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete post.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
} 