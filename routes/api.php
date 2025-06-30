<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employer\EmployerJobController;
use App\Http\Controllers\Itian\ItianRegistrationRequestController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\Api\ItianProfileController;
use App\Http\Controllers\Api\EmployerProfileController;
use App\Http\Controllers\Api\ItianSkillProjectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostReactionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicJobController;
use App\Http\Controllers\RagController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\TestimonialController;
// ------------------- Public Routes -------------------
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('jobs', [EmployerJobController::class, 'index']);
Route::get('jobs/{job}', [EmployerJobController::class, 'show']);
Route::get('posts/{post}/comments', [CommentController::class, 'index']);
Route::get('posts/{post}/reactions/details', [PostReactionController::class, 'getReactionDetails']);
Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
Route::get('public-profile/{username}', [ItianProfileController::class, 'showPublic']);
Route::get('email/verify/{id}', [EmailVerificationController::class, 'verify'])->name('verification.verify')->middleware('signed');
Route::get('public/jobs/{id}', [PublicJobController::class, 'show']);
Route::apiResource('posts', PostController::class);

// ------------------- Authenticated Routes -------------------
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('logout', [AuthController::class, 'logout']);

    // Itian Profile
    Route::get('itian-profile/{user_id}', [ItianProfileController::class, 'showProfileByUserId']);
    Route::post('itian-profile', [ItianProfileController::class, 'store']);
    Route::get('itian-profile', [ItianProfileController::class, 'show']);
    Route::post('itian-profiles/{user_id}/update', [ItianProfileController::class, 'update']);
    Route::put('itian-profile', [ItianProfileController::class, 'update']);
    Route::delete('itian-profile', [ItianProfileController::class, 'destroy']);
    Route::get('itian-profile/{user}', [ItianProfileController::class, 'publicShow']);

    // Employer Profile
    Route::get('employer-public-profile/{id}', [EmployerProfileController::class, 'showPublicProfileById']);
    Route::get('employer-profile', [EmployerProfileController::class, 'show']);
    Route::post('employer-profile', [EmployerProfileController::class, 'store']);
    Route::post('employer-profiles/{user_id}/update', [EmployerProfileController::class, 'update']);
    Route::delete('employer-profile', [EmployerProfileController::class, 'destroy']);

    // Posts
    Route::apiResource('posts', PostController::class);
    Route::get('myposts', [PostController::class, 'myPosts']);

    // Post Reactions
    Route::post('posts/{post}/react', [PostReactionController::class, 'react']);
    Route::delete('posts/{post}/reaction', [PostReactionController::class, 'removeReaction']);
    Route::get('posts/{post}/reactions', [PostReactionController::class, 'getReactions']);

    // Comments
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
    Route::put('replies/{id}', [CommentController::class, 'updateReply']);
    Route::delete('replies/{id}', [CommentController::class, 'destroyReply']);

    // Skills
    Route::post('skills', [ItianSkillProjectController::class, 'storeSkill']);
    Route::put('skills/{id}', [ItianSkillProjectController::class, 'updateSkill']);
    Route::delete('skills/{id}', [ItianSkillProjectController::class, 'deleteSkill']);
    Route::get('skills', [ItianSkillProjectController::class, 'listSkills']);
    Route::get('skills/profile/{itian_profile_id}', [ItianSkillProjectController::class, 'showSkillsByProfile']);

    // Projects
    Route::post('projects', [ItianSkillProjectController::class, 'storeProject']);
    Route::put('projects/{id}', [ItianSkillProjectController::class, 'updateProject']);
    Route::delete('projects/{id}', [ItianSkillProjectController::class, 'deleteProject']);
    Route::get('projects', [ItianSkillProjectController::class, 'listProjects']);
    Route::get('projects/profile/{itian_profile_id}', [ItianSkillProjectController::class, 'showProjectsByProfile']);

    // Jobs
    Route::apiResource('jobs', EmployerJobController::class)->except(['index', 'show']);
    Route::get('employer/jobs', [EmployerJobController::class, 'employerJobs']);
    Route::patch('jobs/{job}/status', [EmployerJobController::class, 'updateStatus']);
    Route::get('jobs-statistics', [EmployerJobController::class, 'statistics']);
    Route::get('jobs-trashed', [EmployerJobController::class, 'trashed']);
    Route::post('jobs/{id}/restore', [EmployerJobController::class, 'restore']);
    Route::delete('jobs/{id}/force-delete', [EmployerJobController::class, 'forceDelete']);

    // Job Applications
    Route::post('job-application', [JobApplicationController::class, 'store']);
    Route::get('job-application/single/{id}', [JobApplicationController::class, 'show']);
    Route::get('job/{job}/applications', [JobApplicationController::class, 'getJobApplications']);
    Route::get('employer/job-application', [JobApplicationController::class, 'getEmployerAllJobApplications']);
    Route::get('itian/job-application', [JobApplicationController::class, 'index']);
    Route::get('check-application/{job_id}', [JobApplicationController::class, 'checkIfApplied']);
    Route::put('job-application/{id}', [JobApplicationController::class, 'update']);
    Route::patch('job-application/{id}', [JobApplicationController::class, 'update']);
    Route::patch('job-application/{id}/status', [JobApplicationController::class, 'updateStatus']);
    Route::delete('job-application/{id}', [JobApplicationController::class, 'destroy']);
   Route::get('my-applications', [JobApplicationController::class, 'getMyApplications']);

    // Itian Registration Requests
    Route::post('itian-registration-requests', [ItianRegistrationRequestController::class, 'store']);
    Route::get('itian-registration-requests', [ItianRegistrationRequestController::class, 'index']);

    // Reports
   Route::get('/reports', [ReportController::class, 'index']); // For admin to view all reports with filters/pagination
    Route::post('/reports', [ReportController::class, 'store']); // For users to create reports
    Route::get('/reports/{id}', [ReportController::class, 'show']); // To view a single report
    Route::patch('/reports/{id}/status', [ReportController::class, 'updateStatus']); // For admin to update report status
    Route::delete('/reports/{id}', [ReportController::class, 'destroy']); // For admin to delete reports (or user to delete their own if logic allows)
    Route::get('/my-reports', [ReportController::class, 'myReports']); // New route for user to view their own reports

    // Notifications
    Route::get('/my-notifications', [NotificationController::class, 'index']);
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAllNotifications']);

    // Chat
    Route::prefix('mychat')->group(function () {
        Route::post('chat/auth', [CustomChatController::class, 'pusherAuth']);
        Route::post('idInfo', [CustomChatController::class, 'idFetchData']);
        Route::post('sendMessage', [CustomChatController::class, 'send']);
        Route::post('fetchMessages', [CustomChatController::class, 'fetch']);
        Route::get('download/{fileName}', [CustomChatController::class, 'download']);
        Route::post('makeSeen', [CustomChatController::class, 'seen']);
        Route::get('getContacts', [CustomChatController::class, 'getContacts']);
        Route::post('star', [CustomChatController::class, 'favorite']);
        Route::post('favorites', [CustomChatController::class, 'getFavorites']);
        Route::get('search', [CustomChatController::class, 'search']);
        Route::post('shared', [CustomChatController::class, 'sharedPhotos']);
        Route::post('deleteConversation', [CustomChatController::class, 'deleteConversation']);
        Route::post('updateSettings', [CustomChatController::class, 'updateSettings']);
        Route::post('setActiveStatus', [CustomChatController::class, 'setActiveStatus']);
        Route::post('updateMessage', [CustomChatController::class, 'updateMessage']);

    });
        Route::get('user', [UserManagementController::class, 'getUserData']);

    // Admin Routes
    Route::middleware('admin')->group(function () {
        Route::get('users', [UserManagementController::class, 'allUsers']);
        Route::get('users/unapproved-employers', [UserManagementController::class, 'getUnApprovedEmployers']);
        Route::post('users/{id}/approve-employer', [UserManagementController::class, 'approveEmployer']);
        Route::post('users/{id}/reject-employer', [UserManagementController::class, 'rejectEmployer']);
        Route::delete('users/{id}', [UserManagementController::class, 'deleteUser']);

        Route::put('itian-registration-requests/{id}/review', [ItianRegistrationRequestController::class, 'review']);
        Route::get('itian-registration-requests/{id}', [ItianRegistrationRequestController::class, 'show']);

        Route::get('/admin/job-pricing', [AdminController::class, 'showPricing']);
        Route::post('/set-job-price', [AdminController::class, 'updatePricing']);
        Route::get('/job-price', [AdminController::class, 'getLatestPrice']);
       
     Route::get('/admin/employers', [AdminController::class, 'listEmployers']);
     Route::post('/admin/send-round-ended-email', [AdminController::class, 'sendRoundEndedEmail']);

    Route::get('/admin/testimonials', [TestimonialController::class, 'adminIndex']);
    Route::patch('/admin/testimonials/{testimonial}/status', [TestimonialController::class, 'updateStatus']);
    Route::delete('/admin/testimonials/{testimonial}', [TestimonialController::class, 'destroy']);
    });

   Route::get('/employer-list', [\App\Http\Controllers\Admin\EmailController::class, 'getEmployers']);

    // Payments
    Route::post('/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/has-unused-payment', [PaymentController::class, 'hasUnusedPayment']);

    Route::post('/testimonials', [TestimonialController::class, 'store']);



});
// RAG
Route::prefix('rag')->group(function () {
        Route::get('/embed/posts', [RagController::class, 'embedPosts']);
        Route::get('/embed/jobs', [RagController::class, 'embedJobs']);
        Route::get('/search', [RagController::class, 'search']);
        Route::get('/ask', [RagController::class, 'ask']);
});
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    Route::post('/stripe/webhook', [PaymentController::class, 'handleStripeWebhook']);
