<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->name('verify-otp');
Route::post('/resend-otp', [AuthController::class, 'resendOTP'])->name('resend-otp');
Route::post('/login', [AuthController::class, 'login'])->name('login')->name('login');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');

Route::middleware('auth:api')->group(function() {
    //AuthController
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'delete']);
    //PostController
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/post/{id}', [PostController::class, 'show']);
    Route::post('/create-post', [PostController::class, 'create']);
    Route::post('/update-post/{id}', [PostController::class, 'update']); // or put/patch
    Route::delete('/delete-post/{id}', [PostController::class, 'destroy']);
    Route::get('/my-post', [PostController::class, 'myPosts']);

    //Likes
    Route::post('/like-post/{id}', [LikeController::class, 'toggle']);
    Route::get('/total-like-post/{id}', [LikeController::class, 'total']);

    //Comments
    Route::post('/create-comment/{id}', [CommentController::class, 'create']);
    Route::get('/view-comment/{id}', [CommentController::class, 'show']);
    Route::post('update-comment/{id}', [CommentController::class, 'update']);
    Route::delete('delete-comment/{id}', [CommentController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
});