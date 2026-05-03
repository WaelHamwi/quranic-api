<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CategoryController;
// Google OAuth routes for mobile
Route::post('/auth/google/callback', [GoogleAuthController::class, 'handleMobileGoogleCallback']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


Route::get('/test-data', function () {
    return response()->json([
        [
            'id' => 1,
            'title' => 'Sample Item 1',
            'description' => 'This is a test item from the API',
            'timestamp' => now()->toISOString(),
        ],
        [
            'id' => 2,
            'title' => 'Sample Item 2',
            'description' => 'Another test item',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});

Route::post('/test-data', function (Request $request) {
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'timestamp' => 'nullable|string',
    ]);

    return response()->json([
        'id' => rand(100, 999),
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'timestamp' => $validated['timestamp'] ?? now()->toISOString(),
    ], 201);
});

Route::get('/users', function () {
    return response()->json([
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
        ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
    ]);
});

Route::get('/posts', function () {
    return response()->json([
        ['id' => 1, 'title' => 'First Post', 'content' => 'Content of first post'],
        ['id' => 2, 'title' => 'Second Post', 'content' => 'Content of second post'],
    ]);
});
