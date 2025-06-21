<?php

//use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employer\EmployerJobController;
use App\Http\Controllers\Itian\ItianRegistrationRequestController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItianProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\EmployerProfileController;
use App\Http\Controllers\Api\ItianSkillProjectController;
use App\Http\Controllers\PostReactionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CustomChatController;
use App\Http\Controllers\CommentController;



Route::middleware('auth:sanctum')->group(function () {
    // Post reactions
    Route::post('/posts/{post}/react', [PostReactionController::class, 'react']);
    Route::delete('/posts/{post}/reaction', [PostReactionController::class, 'removeReaction']);
    Route::get('/posts/{post}/reactions', [PostReactionController::class, 'getReactions']);

    // Skills
    Route::post('/skills', [ItianSkillProjectController::class, 'storeSkill']);
    Route::put('/skills/{id}', [ItianSkillProjectController::class, 'updateSkill']);
    Route::delete('/skills/{id}', [ItianSkillProjectController::class, 'deleteSkill']);
    Route::get('/skills', [ItianSkillProjectController::class, 'listSkills']);
    Route::get('/skills/profile/{itian_profile_id}', [ItianSkillProjectController::class, 'showSkillsByProfile']);

    // Projects
    Route::post('/projects', [ItianSkillProjectController::class, 'storeProject']);
    Route::put('/projects/{id}', [ItianSkillProjectController::class, 'updateProject']);
    Route::delete('/projects/{id}', [ItianSkillProjectController::class, 'deleteProject']);
    Route::get('/projects', [ItianSkillProjectController::class, 'listProjects']);
    Route::get('/projects/profile/{itian_profile_id}', [ItianSkillProjectController::class, 'showProjectsByProfile']);

    // Itian profile
    Route::post('/itian-profile', [ItianProfileController::class, 'store']);
    Route::get('/itian-profile', [ItianProfileController::class, 'show']);
    Route::put('/itian-profile', [ItianProfileController::class, 'update']);
    Route::delete('/itian-profile', [ItianProfileController::class, 'destroy']);
    Route::get('/itian-profile/{user}', [ItianProfileController::class, 'publicShow']);

    // Employer profile
    Route::post('/employer-profile', [EmployerProfileController::class, 'store']);
    Route::get('/employer-profile', [EmployerProfileController::class, 'show']);
    Route::put('/employer-profile', [EmployerProfileController::class, 'update']);
    Route::delete('/employer-profile', [EmployerProfileController::class, 'destroy']);
    Route::get('/employer-profile/{user}', [EmployerProfileController::class, 'publicShow']);

    // Chat
    Route::prefix('mychat')->group(function () {
        Route::post('/chat/auth', [CustomChatController::class, 'pusherAuth']);
        Route::post('/idInfo', [CustomChatController::class, 'idFetchData']);
        Route::post('/sendMessage', [CustomChatController::class, 'send']);
        Route::post('/fetchMessages', [CustomChatController::class, 'fetch']);
        Route::get('/download/{fileName}', [CustomChatController::class, 'download']);
        Route::post('/makeSeen', [CustomChatController::class, 'seen']);
        Route::get('/getContacts', [CustomChatController::class, 'getContacts']);
        Route::post('/star', [CustomChatController::class, 'favorite']);
        Route::post('/favorites', [CustomChatController::class, 'getFavorites']);
        Route::get('/search', [CustomChatController::class, 'search']);
        Route::post('/shared', [CustomChatController::class, 'sharedPhotos']);
        Route::post('/deleteConversation', [CustomChatController::class, 'deleteConversation']);
        Route::post('/updateSettings', [CustomChatController::class, 'updateSettings']);
        Route::post('/setActiveStatus', [CustomChatController::class, 'setActiveStatus']);
    });

    // Posts
    Route::apiResource('posts', PostController::class);
    Route::get('/myposts', [PostController::class, 'myPosts']);

    // Comments
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    // Job applications
    Route::post('job-application', [JobApplicationController::class, 'store']);
    Route::get('job-application/single/{id}', [JobApplicationController::class, 'show']);
    Route::get('job/{job}/applications', [JobApplicationController::class, 'getJobApplications']);
    Route::get('employer/job-application', [JobApplicationController::class, 'getEmployerAllJobApplications']);
    Route::get('itian/job-application', [JobApplicationController::class, 'index']);
    Route::get('check-application/{job_id}', [JobApplicationController::class, 'checkIfApplied']);
    Route::put('job-application/{id}', [JobApplicationController::class, 'update']);
    Route::patch('job-application/{id}', [JobApplicationController::class, 'update']);
    Route::put('job-application/{id}/status', [JobApplicationController::class, 'updateStatus']);
    Route::delete('job-application/{id}', [JobApplicationController::class, 'destroy']);

    // Itian registration requests
    Route::post('/itian-registration-requests', [ItianRegistrationRequestController::class, 'store']);
    Route::put('/itian-registration-requests/{id}/review', [ItianRegistrationRequestController::class, 'review'])->middleware('admin');
    Route::get('/itian-registration-requests/{id}', [ItianRegistrationRequestController::class, 'show'])->middleware('admin');
    Route::get('/itian-registration-requests', [ItianRegistrationRequestController::class, 'index']);

    // Employer registration requests (add similar routes if needed)
});

// Public routes
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
    Route::get('jobs-trashed', [EmployerJobController::class, 'trashed']);
    Route::post('jobs/{id}/restore', [EmployerJobController::class, 'restore']);
    Route::delete('jobs/{id}/force-delete', [EmployerJobController::class, 'forceDelete']);
});

// Comments (public)
Route::get('posts/{post}/comments', [CommentController::class, 'index']);

// Password reset routes
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'allUsers']);
    Route::get('/users/unapproved-employers', [UserManagementController::class, 'getUnApprovedEmployers']);
    Route::post('/users/{id}/approve-employer', [UserManagementController::class, 'approveEmployer']);
    Route::post('/users/{id}/reject-employer', [UserManagementController::class, 'rejectEmployer']);
});

// Post reactions details
Route::get('/posts/{post}/reactions/details', [PostReactionController::class, 'getReactionDetails']);
