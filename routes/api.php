<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function() {
    //PostController
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/post/{id}', [PostController::class, 'show']);
    Route::post('/create-post', [PostController::class, 'create']);
    Route::post('/update-post/{id}', [PostController::class, 'update']); // or put/patch
    Route::delete('/delete-post/{id}', [PostController::class, 'destroy']);
    Route::get('/my-post', [PostController::class, 'myPosts']);

    //Likes
    Route::post('/like-post/{id}', [LikeController::class, 'toggle']);
    Route::get('total-like-post/{id}', [LikeController::class, 'total']);

    Route::post('/logout', [AuthController::class, 'logout']);
});