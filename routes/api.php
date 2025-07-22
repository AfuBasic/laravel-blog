<?php

use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/token', function () {
    // echo User::first()->createToken('read-only')->plainTextToken;
});

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->group(function () {
    Route::get('/categories', [PostCategoryController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'getPostBySlug']);
    Route::get('/posts', [PostController::class, 'index']);
});
