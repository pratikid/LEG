<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TimelineEventController;
use App\Http\Controllers\TimelineReportController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\TimelinePreferencesController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\IndividualController;

Route::get('/', function () {
    return view('auth.login');
});

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register')
    ->middleware('guest');

Route::post('/register', [RegisterController::class, 'register'])
    ->name('register')
    ->middleware('guest');

// Dashboard Route
Route::get('/dashboard', function () {
    // $totalMembers = \App\Models\Individual::count();
    $totalMembers = 0;
    $generations = 6; // TODO: Replace with actual calculation if available
    // $totalPhotos = \App\Models\TimelineEvent::count(); // Placeholder for photos
    $totalPhotos = 0;
    $activities = \App\Models\ActivityLog::with('user')
        ->where('user_id', Auth::id())
        ->orderByDesc('created_at')
        ->limit(3)
        ->get();
    // $userTrees = \App\Models\Tree::where('user_id', auth()->id())->limit(3)->get();
    $userTrees = [];
    // $recentIndividuals = \App\Models\Individual::orderByDesc('created_at')->limit(3)->get();
    $recentIndividuals = [];
    return view('dashboard', compact('totalMembers', 'generations', 'totalPhotos', 'activities', 'userTrees', 'recentIndividuals'));
})->middleware(['auth'])->name('dashboard');

// Timeline Routes
Route::resource('timeline', TimelineEventController::class)
    ->middleware(['auth'])
    ->except(['show']); // Show route will be public

// Public timeline view
Route::get('/timeline/{timelineEvent}', [TimelineEventController::class, 'show'])
    ->name('timeline.show');

// Timeline Report Routes
Route::prefix('timeline-reports')->middleware(['auth'])->group(function () {
    Route::get('/generate', [TimelineReportController::class, 'generateTimelineReport'])
        ->name('timeline.reports.generate');
    Route::get('/event/{timelineEvent}', [TimelineReportController::class, 'generateEventReport'])
        ->name('timeline.reports.event');
    Route::get('/type/{type}', [TimelineReportController::class, 'generateTypeReport'])
        ->name('timeline.reports.type');
});

// Tutorial Routes
Route::prefix('tutorials')->middleware(['auth'])->group(function () {
    Route::post('/mark-completed', [TutorialController::class, 'markAsCompleted'])
        ->name('tutorials.mark-completed');
    Route::post('/reset', [TutorialController::class, 'resetTutorials'])
        ->name('tutorials.reset');
});

// Timeline Preferences Routes
Route::middleware(['auth'])->group(function () {
    Route::put('/timeline/preferences', [TimelinePreferencesController::class, 'update'])->name('timeline.preferences.update');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{log}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth');

Route::resource('trees', TreeController::class)->middleware(['auth']);
Route::post('trees/import', [TreeController::class, 'handleImport'])->name('trees.import')->middleware(['auth']);

Route::resource('groups', GroupController::class)->middleware(['auth']);
Route::resource('individuals', IndividualController::class)->middleware(['auth']);
