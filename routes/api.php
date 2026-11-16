<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\Api\{
    FetchController,
    AuthController,
    UserController,
    ProjectController,
    AttendanceController,
    ContactController,
    OfferController,
};

// Fetch Data
Route::controller(FetchController::class)->group(function () {
    Route::get('states', 'states');
    Route::get('cities', 'cities');
    Route::get('designations', 'designations');
    Route::get('terms-and-conditions', 'termsAndConditions');
    Route::get('privacy-policy', 'privacyPolicy');
    Route::get('faqs', 'faqs');
    Route::get('user-agreement', 'userAgreement');
    Route::get('app-info', 'appInfo');
    Route::get('active-agents', 'activeAgents');
    Route::get('active-projects', 'activeProjects');
});

Route::post('register', [AuthController::class, 'register']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('login', [AuthController::class, 'login']);

// Public Google Play Store Account Deletion API Endpoint
Route::match(['get', 'post'], 'delete-account/{id?}', [AccountDeletionController::class, 'deleteAccountApi']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('projects',[ProjectController::class, 'index']);

    Route::controller(AuthController::class)->group(function () {
        Route::get('dashboard','dashborad');
        Route::post('profile-update','profileUpdate');
        Route::get('profile', 'profile');
        Route::post('change-password', 'changePassword');
        Route::get('logout', 'logout');
        Route::post('delete-account-request', 'deleteAccountRequest');
        Route::post('user-delete-request', 'deleteAccountRequest');
        Route::get('delete-account-status', 'accountDeletionStatus');
        Route::post('cancel-delete-account', 'cancelAccountDeletion');
    });
    
    // Super Admin Routes
    Route::middleware('superadmin')->group(function () {
        Route::get('forgot-password-requests',[AuthController::class, 'forgotPasswordRequests']);
        Route::post('forgot-password-update',[AuthController::class, 'forgotPasswordUpdate']);
        Route::controller(UserController::class)->group(function () {
            Route::get('agents', 'agents');
            Route::post('agents-status-update', 'agentsStatusUpdate');
            Route::get('active-agent-wise-sub-agents', 'activeAgentWiseSubAgents');
            Route::get('staffs-show', 'staffsShow');
        });
        Route::controller(ProjectController::class)->group(function () {
            Route::post('projects', 'store');
            Route::post('projects-update', 'projectsUpdate');
        });
        Route::get('contacts-active-leads', [ContactController::class, 'activeLeads']);
        Route::post('offers', [OfferController::class, 'store']);
        Route::post('offers-update', [OfferController::class, 'update']);
    });
        
    // Superadmin or Admin Routes
    Route::middleware('superadminoragent')->group(function () {
        Route::get('active-sub-agents', [UserController::class, 'activeSubAgents']);
        Route::post('theme-store-update', [UserController::class, 'themeStoreUpdate']);
        Route::post('lead-assigns', [UserController::class, 'leadassignStore']);
        Route::post('staffs-status-update', [UserController::class, 'staffsStatusUpdate']);
        Route::get('attendances', [AttendanceController::class, 'index']);

        Route::get('contacts-booking-done', [ContactController::class, 'bookingDone']);
        Route::get('contacts-agent-visits', [ContactController::class, 'agentVisit']);
        Route::get('contacts-agent-team-visits', [ContactController::class, 'agentTeamVisit']);
    });

    // Superadmin or Admin or Staff Routes
    Route::middleware('superadminoragentorsubagent')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('staffs', 'staffs');
        });
        Route::post('attendances', [AttendanceController::class, 'store']);

        Route::get('contacts', [ContactController::class, 'index']);
        Route::post('contacts', [ContactController::class, 'store']);
        Route::get('contacts-show', [ContactController::class, 'show']);
        Route::get('lead-assigns', [ContactController::class, 'leadAssigns']);
        Route::post('contacts-update', [ContactController::class, 'update']);

        Route::get('offers', [OfferController::class, 'index']);
    });
});