<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\IndividualController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Neo4jRelationshipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TimelineEventController;
use App\Http\Controllers\TimelinePreferencesController;
use App\Http\Controllers\TimelineReportController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\TutorialController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->name('register')
    ->middleware('guest');

Route::post('/register', [RegisterController::class, 'register'])
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
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/timeline/preferences', [TimelinePreferencesController::class, 'update'])->name('timeline.preferences.update');
    Route::post('/relationships/parent-child', [Neo4jRelationshipController::class, 'addParentChild'])->name('relationships.parent-child');
    Route::post('/relationships/spouse', [Neo4jRelationshipController::class, 'addSpouse'])->name('relationships.spouse');
    Route::get('/relationships/{id}/children', [Neo4jRelationshipController::class, 'getChildren'])->name('relationships.children');
    Route::get('/relationships/{id}/parents', [Neo4jRelationshipController::class, 'getParents'])->name('relationships.parents');
    Route::get('/relationships/{id}/spouses', [Neo4jRelationshipController::class, 'getSpouses'])->name('relationships.spouses');
    // Advanced Neo4j relationship queries
    Route::get('/relationships/{id}/ancestors', [Neo4jRelationshipController::class, 'getAncestors'])->name('relationships.ancestors');
    Route::get('/relationships/{id}/descendants', [Neo4jRelationshipController::class, 'getDescendants'])->name('relationships.descendants');
    Route::get('/relationships/{id}/siblings', [Neo4jRelationshipController::class, 'getSiblings'])->name('relationships.siblings');
    Route::get('/relationships/{fromId}/shortest-path/{toId}', [Neo4jRelationshipController::class, 'getShortestPath'])->name('relationships.shortest-path');
    Route::post('/relationships/sibling', [Neo4jRelationshipController::class, 'addSibling'])->name('relationships.sibling');
    Route::delete('/relationships/parent-child', [Neo4jRelationshipController::class, 'removeParentChild'])->name('relationships.remove-parent-child');
    Route::delete('/relationships/spouse', [Neo4jRelationshipController::class, 'removeSpouse'])->name('relationships.remove-spouse');
    Route::delete('/relationships/sibling', [Neo4jRelationshipController::class, 'removeSibling'])->name('relationships.remove-sibling');
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

Route::get('/individuals/timeline', [IndividualController::class, 'timeline'])->name('individuals.timeline')->middleware(['auth']);

// Community
Route::get('/community/directory', [CommunityController::class, 'directory'])->name('community.directory')->middleware(['auth']);
Route::get('/community/my-groups', [CommunityController::class, 'myGroups'])->name('community.my-groups')->middleware(['auth']);
Route::get('/community/forums', [CommunityController::class, 'forums'])->name('community.forums')->middleware(['auth']);

// Tools
Route::get('/tools/templates', [ToolsController::class, 'templates'])->name('tools.templates')->middleware(['auth']);
Route::get('/tools/export', [ToolsController::class, 'export'])->name('tools.export')->middleware(['auth']);
Route::get('/tools/reports', [ToolsController::class, 'reports'])->name('tools.reports')->middleware(['auth']);

// Events
Route::resource('events', EventController::class)->middleware(['auth']);
Route::get('/events/calendar', [EventController::class, 'calendar'])->name('events.calendar')->middleware(['auth']);

// Media
Route::resource('media', MediaController::class)->middleware(['auth']);

// Stories
Route::resource('stories', StoryController::class)->middleware(['auth']);

// Sources
Route::resource('sources', SourceController::class)->middleware(['auth']);

// Help
Route::get('/help/user-guide', [HelpController::class, 'userGuide'])->name('help.user-guide');
Route::get('/help/tutorials', [HelpController::class, 'tutorials'])->name('help.tutorials');
Route::get('/help/support', [HelpController::class, 'support'])->name('help.support');

// Profile
Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings')->middleware(['auth']);
Route::get('/profile/preferences', [ProfileController::class, 'preferences'])->name('profile.preferences')->middleware(['auth']);

// Admin
Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users')->middleware(['auth', 'admin']);
Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs')->middleware(['auth', 'admin']);
Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings')->middleware(['auth', 'admin']);
Route::get('/admin/notifications', [AdminController::class, 'notifications'])->name('admin.notifications')->middleware(['auth', 'admin']);

Route::get('/search', function () {
    return view('search.index');
})->name('search')->middleware(['auth']);
