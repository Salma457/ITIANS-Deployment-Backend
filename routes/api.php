<?php

//use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employer\EmployerJobController;
use App\Http\Controllers\Itian\ItianRegistrationRequestController;
use App\Http\Controllers\JobApplicationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItianProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\EmployerProfileController;
use App\Http\Controllers\Api\ItianSkillProjectController;
use App\Http\Controllers\ReportController;


Route::middleware('auth:sanctum')->group(function () {

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

});
use App\Http\Controllers\CustomChatController;
use App\Http\Controllers\CommentController; 


    Route::get('/public-profile/{username}', [ItianProfileController::class, 'showPublic']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::put('/itian-profile', [ItianProfileController::class, 'update']);
    Route::post('/itian-profiles/{user_id}/update', [ItianProfileController::class, 'update']);


    // Route::post('/profile/update', [ProfileApiController::class, 'update'])->name('profile.update');

    Route::post('/itian-profile', [ItianProfileController::class, 'store']);
    Route::get('/itian-profile', [ItianProfileController::class, 'show']);
    // Route::match(['POST', 'PUT'], '/itian-profile', [ItianProfileController::class, 'update']);
    // Route::put('/itian-profile', [ItianProfileController::class, 'update']); // تم التعديل هنا
    Route::delete('/itian-profile', [ItianProfileController::class, 'destroy']);
});


// Route::middleware('auth:sanctum')->match(['put', 'post'], '/itian-profile', [ItianProfileController::class, 'update']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/employer-profile', [EmployerProfileController::class, 'store']);
    // Route::get('/employer-profile', [EmployerProfileController::class, 'show']);
    // Route::put('/employer-profile', [EmployerProfileController::class, 'update']);
    // Route::delete('/employer-profile', [EmployerProfileController::class, 'destroy']);
   
    // Route::get('/employer-profile/{user}', [EmployerProfileController::class, 'publicShow']);
    Route::post('/employer-profile', [EmployerProfileController::class, 'store']); // إنشاء ملف شخصي لصاحب العمل
    Route::get('/employer-profile', [EmployerProfileController::class, 'show']); // عرض ملف شخصي لصاحب العمل الموثق
    // ملاحظة: لرفع الملفات (مثل اللوجو) مع طلب PUT، غالبًا ما نستخدم POST مع حقل _method = PUT
    Route::post('/employer-profile/update', [EmployerProfileController::class, 'update']); // تحديث ملف شخصي لصاحب العمل
    Route::delete('/employer-profile', [EmployerProfileController::class, 'destroy']); // حذف ملف شخصي لصاحب العمل
});


    Route::get('/employer-public-profile/{username}', [EmployerProfileController::class, 'showPublic']);



Route::middleware('auth:sanctum')->prefix('mychat')->group(function () {
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


//posts
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', App\Http\Controllers\PostController::class);
});
Route::middleware('auth:sanctum')->get('/myposts', [PostController::class, 'myPosts']);
//comments
// anyone can view comments
Route::get('posts/{post}/comments', [CommentController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
});




// Job applications
Route::middleware('auth:sanctum')->group(function () {
    Route::post('job-application', [JobApplicationController::class,'store']);
    Route::get('job-application/{id}', [JobApplicationController::class,'show']);
    Route::get('job-application/{job_id}', [JobApplicationController::class, 'getJobApplications']);
    Route::get('/employer/job-application/', [JobApplicationController::class, 'getEmployerAllJobApplications']);
    Route::get('/itian/job-application/', [JobApplicationController::class, 'index']);
    Route::put('job-application/{id}', [JobApplicationController::class, 'updateStatus']);
    Route::delete('job-application/{id}', [JobApplicationController::class, 'destroy']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::delete('/reports/{id}', [ReportController::class, 'destroy']);

    Route::put('/reports/{id}/status', [ReportController::class, 'updateStatus']);
});