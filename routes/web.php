<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\Admin\{
    AuthController as AdminAuthController,
    DashboardController,
    UserController as AdminUserController,
    ProjectController as AdminProjectController,
    ContactController as AdminContactController,
    CallHistoryController as AdminCallHistoryController,
    AttendanceController as AdminAttendanceController,
    OfferController as AdminOfferController,
    ReportController as AdminReportController,
    MasterController,
    SettingController as AdminSettingController,
    ForgotPasswordController,
    SalaryController as AdminSalaryController,
    HolidayController as AdminHolidayController
};

/*
|--------------------------------------------------------------------------
| Public Routes & Authentication
|--------------------------------------------------------------------------
*/
Route::get('/', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::get('/login', [AdminAuthController::class, 'showLoginForm']);
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
Route::match(['get', 'post'], '/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Google Play Store Account Deletion URL & Direct GET/POST Deletion Handler (Preserved)
Route::match(['get', 'post'], 'delete-account/{id?}', [AccountDeletionController::class, 'deleteAccount'])->name('delete-account');

// Legacy Superadmin Export (Preserved)
Route::get('superadmin/users-export', [ExcelExportController::class, 'usersExport']);

/*
|--------------------------------------------------------------------------
| Admin & Superadmin Panel Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'superadminoragent'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Password
    Route::get('/profile', [AdminAuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminAuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/password/update', [AdminAuthController::class, 'updatePassword'])->name('password.update');

    // Users Management (CRUD)
    Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
    Route::post('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
    Route::resource('users', AdminUserController::class);

    // Projects Management (CRUD)
    Route::get('/projects/maps', [AdminProjectController::class, 'mapsHub'])->name('projects.maps');
    Route::post('/projects/quick-map-update', [AdminProjectController::class, 'quickMapUpdate'])->name('projects.quick-map-update');
    Route::post('/projects/{project}/toggle-status', [AdminProjectController::class, 'toggleStatus'])->name('projects.toggle');
    Route::post('/projects/{project}/update-map', [AdminProjectController::class, 'updateMap'])->name('projects.update-map');
    Route::post('/projects/{project}/remove-map', [AdminProjectController::class, 'removeMap'])->name('projects.remove-map');
    Route::resource('projects', AdminProjectController::class);

    // Leads / Contacts Management (CRUD)
    Route::post('/leads/{lead}/assign', [AdminContactController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{lead}/call-history', [AdminContactController::class, 'addCallHistory'])->name('leads.add-call-history');
    Route::resource('leads', AdminContactController::class);

    // Call Histories & Site Visits
    Route::get('/call-histories', [AdminCallHistoryController::class, 'index'])->name('call-histories.index');
    Route::delete('/call-histories/{callHistory}', [AdminCallHistoryController::class, 'destroy'])->name('call-histories.destroy');

    // Attendance Management
    Route::get('/attendances/monthly', [AdminAttendanceController::class, 'monthly'])->name('attendances.monthly');
    Route::resource('attendances', AdminAttendanceController::class)->only(['index', 'create', 'store', 'destroy']);

    // Staff Salary & Payroll Calculation
    Route::get('/salary', [AdminSalaryController::class, 'index'])->name('salary.index');
    Route::post('/salary/weekly-off', [AdminSalaryController::class, 'updateWeeklyOff'])->name('salary.weekly-off');
    Route::get('/salary/payslip/{user}', [AdminSalaryController::class, 'payslip'])->name('salary.payslip');
    Route::post('/users/{user}/salary', [AdminUserController::class, 'updateSalary'])->name('users.update-salary');

    // Offers & Promotions
    Route::resource('offers', AdminOfferController::class)->except(['show']);

    // Reports Hub
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminReportController::class, 'index'])->name('index');
        Route::get('/leads', [AdminReportController::class, 'leads'])->name('leads');
        Route::get('/agent-performance', [AdminReportController::class, 'agentPerformance'])->name('agent-performance');
        Route::get('/attendance', [AdminReportController::class, 'attendance'])->name('attendance');
        Route::get('/site-visits', [AdminReportController::class, 'siteVisits'])->name('site-visits');
        Route::get('/projects', [AdminReportController::class, 'projects'])->name('projects');
        Route::get('/user-growth', [AdminReportController::class, 'userGrowth'])->name('user-growth');
    });

    /*
    |--------------------------------------------------------------------------
    | Superadmin Only Management
    |--------------------------------------------------------------------------
    */
    Route::middleware('superadmin')->group(function () {
        // Masters: States
        Route::get('/masters/states', [MasterController::class, 'statesIndex'])->name('masters.states.index');
        Route::post('/masters/states', [MasterController::class, 'stateStore'])->name('masters.states.store');
        Route::put('/masters/states/{state}', [MasterController::class, 'stateUpdate'])->name('masters.states.update');
        Route::delete('/masters/states/{state}', [MasterController::class, 'stateDestroy'])->name('masters.states.destroy');
        Route::post('/masters/states/{state}/toggle', [MasterController::class, 'stateToggle'])->name('masters.states.toggle');

        // Masters: Cities
        Route::get('/masters/cities', [MasterController::class, 'citiesIndex'])->name('masters.cities.index');
        Route::post('/masters/cities', [MasterController::class, 'cityStore'])->name('masters.cities.store');
        Route::put('/masters/cities/{city}', [MasterController::class, 'cityUpdate'])->name('masters.cities.update');
        Route::delete('/masters/cities/{city}', [MasterController::class, 'cityDestroy'])->name('masters.cities.destroy');
        Route::post('/masters/cities/{city}/toggle', [MasterController::class, 'cityToggle'])->name('masters.cities.toggle');

        // Masters: Designations
        Route::get('/masters/designations', [MasterController::class, 'designationsIndex'])->name('masters.designations.index');
        Route::post('/masters/designations', [MasterController::class, 'designationStore'])->name('masters.designations.store');
        Route::put('/masters/designations/{designation}', [MasterController::class, 'designationUpdate'])->name('masters.designations.update');
        Route::delete('/masters/designations/{designation}', [MasterController::class, 'designationDestroy'])->name('masters.designations.destroy');
        Route::post('/masters/designations/{designation}/toggle', [MasterController::class, 'designationToggle'])->name('masters.designations.toggle');

        // Masters: Holidays
        Route::get('/masters/holidays', [AdminHolidayController::class, 'index'])->name('masters.holidays.index');
        Route::post('/masters/holidays', [AdminHolidayController::class, 'store'])->name('masters.holidays.store');
        Route::put('/masters/holidays/{holiday}', [AdminHolidayController::class, 'update'])->name('masters.holidays.update');
        Route::delete('/masters/holidays/{holiday}', [AdminHolidayController::class, 'destroy'])->name('masters.holidays.destroy');
        Route::post('/masters/holidays/{holiday}/toggle', [AdminHolidayController::class, 'toggleStatus'])->name('masters.holidays.toggle');

        // App Settings
        Route::get('/settings', [AdminSettingController::class, 'settingsIndex'])->name('settings.index');
        Route::post('/settings', [AdminSettingController::class, 'settingsUpdate'])->name('settings.update');

        // FAQs
        Route::get('/faqs', [AdminSettingController::class, 'faqsIndex'])->name('faqs.index');
        Route::post('/faqs', [AdminSettingController::class, 'faqStore'])->name('faqs.store');
        Route::put('/faqs/{faq}', [AdminSettingController::class, 'faqUpdate'])->name('faqs.update');
        Route::delete('/faqs/{faq}', [AdminSettingController::class, 'faqDestroy'])->name('faqs.destroy');
        Route::post('/faqs/{faq}/toggle', [AdminSettingController::class, 'faqToggle'])->name('faqs.toggle');

        // Forgot Password Requests
        Route::get('/forgot-passwords', [ForgotPasswordController::class, 'index'])->name('forgot-passwords.index');
        Route::post('/forgot-passwords/{forgotPassword}/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot-passwords.reset');
        Route::post('/forgot-passwords/{forgotPassword}/reject', [ForgotPasswordController::class, 'reject'])->name('forgot-passwords.reject');
    });
});
