<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/posts', [PostController::class, 'index'])->name('api.posts.index');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('api.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('api.posts.store');
    Route::get('/posts/{id}/edit', [PostController::class, 'edit'])->name('api.posts.edit');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('api.posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('api.posts.destroy');
    
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('api.comments.destroy');
});

Route::get('/posts/{id}', [PostController::class, 'show'])->name('api.posts.show');
Route::post('/posts/{id}/comments', [CommentController::class, 'store'])->name('api.posts.comments.store');

require __DIR__.'/auth.php';