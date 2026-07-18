<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Splash Screen
Route::get('/', [LoginController::class, 'splash'])->name('splash');
// Language Switcher
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard (shared)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Agenda & Jadwal, Tugas Global, Diskusi Global
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
    Route::get('/my-tasks', [AgendaController::class, 'tasks'])->name('agenda.tasks');
    Route::get('/my-discussions', [AgendaController::class, 'discussions'])->name('agenda.discussions');

    // Classes Routes
    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}', [ClassController::class, 'show'])->name('classes.show');
    Route::put('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');
    Route::post('/classes/join', [ClassController::class, 'join'])->name('classes.join');
    Route::get('/join/{kode}', [ClassController::class, 'joinLink'])->name('classes.join.link');
    Route::post('/classes/{class}/leave', [ClassController::class, 'leave'])->name('classes.leave');
    Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');
    Route::post('/classes/{class}/add-admin', [ClassController::class, 'addAdmin'])->name('classes.add-admin');

    // Tasks Routes
    Route::post('/classes/{class}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Submissions Routes
    Route::post('/tasks/{task}/submit', [SubmissionController::class, 'submit'])->name('submissions.submit');
    Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::post('/submissions/{submission}/comments', [\App\Http\Controllers\PrivateCommentController::class, 'store'])->name('submissions.comments.store');

    // Schedules Routes
    Route::post('/classes/{class}/schedules', [ScheduleController::class, 'store'])->name('schedules.store');

    // Discussions Routes
    Route::post('/classes/{class}/discussions', [DiscussionController::class, 'store'])->name('discussions.store');
    Route::get('/discussions/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');
    Route::post('/discussions/{discussion}/reply', [DiscussionController::class, 'reply'])->name('discussions.reply');

    // Notifications Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Analytics Routes
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Reports (Export)
    Route::get('/classes/{class}/reports/excel', [ReportController::class, 'exportCsv'])->name('reports.excel');
    Route::get('/classes/{class}/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');

    // Settings Routes
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings/account', [SettingController::class, 'deleteAccount'])->name('settings.delete');

    // Theme sync (AJAX)
    Route::post('/api/theme-update', [SettingController::class, 'updateTheme'])->name('theme.update');
});
