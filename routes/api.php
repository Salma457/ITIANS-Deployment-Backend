<?php

// use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employer\JobController;
use Illuminate\Support\Facades\Route;


    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);


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
    });
