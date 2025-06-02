<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Itian\ItianRegistrationRequestController;
use App\Http\Controllers\CommentController;  

use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/logout', [AuthController::class, 'logout']);

Route::get('jobs', [JobController::class, 'index']);
Route::get('jobs/{job}', [JobController::class, 'show']);
Route::get('jobs-open', [JobController::class, 'openJobs']);
Route::get('jobs-search', [JobController::class, 'search']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('jobs', JobController::class)->except(['index', 'show']);

    // Status management
    Route::patch('jobs/{job}/status', [JobController::class, 'changeStatus']);

    // Admin-only API routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('jobs-statistics', [JobController::class, 'statistics']);
        Route::patch('jobs/bulk-status', [JobController::class, 'bulkUpdateStatus']);
    });

    // Itian submits request
    Route::post('/itian-registration-requests', [ItianRegistrationRequestController::class, 'store']);

    // Admin reviews request
    Route::put('/itian-registration-requests/{id}/review', [ItianRegistrationRequestController::class, 'review']);

    // Admin gets all requests
    Route::get('/itian-registration-requests', [ItianRegistrationRequestController::class, 'index']);


  
    Route::get('posts/{post}/comments', [CommentController::class, 'index']);

    Route::post('posts/{post}/comments', [CommentController::class, 'store']);

    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});
