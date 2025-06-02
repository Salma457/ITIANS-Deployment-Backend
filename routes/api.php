<?php

// use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employer\EmployerJobController;
use App\Http\Controllers\Itian\ItianRegistrationRequestController;
use Illuminate\Support\Facades\Route;


    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->get('/logout', [AuthController::class, 'logout']);


    Route::get('jobs', [EmployerJobController::class, 'index']);
    Route::get('jobs/{job}', [EmployerJobController::class, 'show']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('jobs', EmployerJobController::class)->except(['index', 'show']);
        
        Route::get('employer/jobs', [EmployerJobController::class, 'employerJobs']);
        
        Route::patch('jobs/{job}/status', [EmployerJobController::class, 'updateStatus']);
        
        Route::get('jobs-statistics', [EmployerJobController::class, 'statistics']);

    });

    Route::middleware('auth:sanctum')->group(function () {
        // Itian submits request
        Route::post('/itian-registration-requests', [ItianRegistrationRequestController::class, 'store']);

        // Admin reviews request
        Route::put('/itian-registration-requests/{id}/review', [ItianRegistrationRequestController::class, 'review']);

        // Admin gets all requests
        Route::get('/itian-registration-requests', [ItianRegistrationRequestController::class, 'index']);
    });
