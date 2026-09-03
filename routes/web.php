<?php

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Admin\ModeratorController as AdminModeratorController;
use App\Http\Controllers\Admin\AICenterController as AdminAICenterController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\AnswerController as AdminAnswerController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BadgeController as AdminBadgeController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TagMergeController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\Moderator\DashboardController as ModDashboardController;
use App\Http\Controllers\Moderator\QueueController as ModQueueController;
use App\Http\Controllers\Moderator\ActionController as ModActionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - DiscussHub
|--------------------------------------------------------------------------
*/

// Home & Discussion Feed
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // ── Password Reset via OTP (3-step) ──────────────────────────────
    Route::get('/forgot-password',               [PasswordResetController::class, 'showForgotForm'])->name('password.forgot.form');
    Route::post('/forgot-password',              [PasswordResetController::class, 'sendOtp'])->name('password.forgot.send')->middleware('throttle:5,1');
    Route::get('/verify-otp',                    [PasswordResetController::class, 'showVerifyOtpForm'])->name('password.verify-otp.form');
    Route::post('/verify-otp',                   [PasswordResetController::class, 'verifyOtp'])->name('password.verify-otp.submit')->middleware('throttle:10,1');
    Route::post('/verify-otp/resend',            [PasswordResetController::class, 'resendOtp'])->name('password.verify-otp.resend')->middleware('throttle:3,1');
    Route::get('/reset-password',                [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password',               [PasswordResetController::class, 'resetPassword'])->name('password.reset.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Questions Public & Protected Routes
Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');

Route::middleware(['auth', 'check.suspended'])->group(function () {
    Route::get('/questions/ask', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // Answers
    Route::post('/answers', [AnswerController::class, 'store'])->name('answers.store');
    Route::get('/answers/{answer}/edit', [AnswerController::class, 'edit'])->name('answers.edit');
    Route::put('/answers/{answer}', [AnswerController::class, 'update'])->name('answers.update');
    Route::delete('/answers/{answer}', [AnswerController::class, 'destroy'])->name('answers.destroy');
    Route::post('/answers/{answer}/accept', [AnswerController::class, 'accept'])->name('answers.accept');

    // Likes (Quora-style — hearts only, no downvote)
    Route::post('/like', [\App\Http\Controllers\LikeController::class, 'toggle'])->name('like.toggle');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Content Reporting
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    // AI Generate Answer (web route — uses session auth)
    Route::post('/ai/generate-answer/{id}', [AIController::class, 'generateAnswer'])->name('ai.generate-answer');

    // Follow System
    Route::post('/follow/{user}', [FollowController::class, 'toggle'])->name('follow.toggle');
});

// Question Detail (supports /{id}/{slug?} for SEO friendly URLs)
Route::get('/questions/{id}/{slug?}', [QuestionController::class, 'show'])->name('questions.show');

// Public Taxonomies & Directory
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::get('/tags/{slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/users/{id}', [ProfileController::class, 'showPublic'])->name('users.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Public follow pages (no auth required to view)
Route::get('/users/{id}/followers', [FollowController::class, 'followers'])->name('users.followers');
Route::get('/users/{id}/following', [FollowController::class, 'following'])->name('users.following');

/*
|--------------------------------------------------------------------------
| Moderator Panel Routes
|--------------------------------------------------------------------------
*/
Route::prefix('moderator')->name('moderator.')->middleware(['auth', 'moderator'])->group(function () {
    Route::get('/',                         [ModDashboardController::class, 'index'])->name('dashboard');
    Route::get('/ai-queue',                 [ModQueueController::class, 'aiQueue'])->name('ai-queue');
    Route::get('/report-queue',             [ModQueueController::class, 'reportQueue'])->name('report-queue');
    Route::get('/history',                  [ModQueueController::class, 'history'])->name('history');

    Route::post('/remove-question/{question}', [ModActionController::class, 'removeQuestion'])->name('remove-question');
    Route::post('/remove-answer/{answer}',     [ModActionController::class, 'removeAnswer'])->name('remove-answer');
    Route::post('/warn-user/{user}',           [ModActionController::class, 'warnUser'])->name('warn-user');
    Route::post('/suspend-user/{user}',        [ModActionController::class, 'suspendUser'])->name('suspend-user');
    Route::post('/dismiss-flag/{type}/{id}',   [ModActionController::class, 'dismissFlag'])->name('dismiss-flag');
    Route::post('/dismiss-report/{report}',    [ModActionController::class, 'dismissReport'])->name('dismiss-report');
    Route::post('/escalate/{user}',            [ModActionController::class, 'escalate'])->name('escalate');
});

Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Auth
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/suspend', [AdminUserController::class, 'toggleSuspend'])->name('users.suspend');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Question Management
        Route::get('/questions', [AdminQuestionController::class, 'index'])->name('questions.index');
        Route::get('/questions/{question}', [AdminQuestionController::class, 'show'])->name('questions.show');
        Route::post('/questions/{question}/pin', [AdminQuestionController::class, 'togglePin'])->name('questions.pin');
        Route::delete('/questions/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');

        // Answer Management
        Route::get('/answers', [AdminAnswerController::class, 'index'])->name('answers.index');
        Route::get('/answers/{answer}', [AdminAnswerController::class, 'show'])->name('answers.show');
        Route::delete('/answers/{answer}', [AdminAnswerController::class, 'destroy'])->name('answers.destroy');

        // Category Management
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        // Tag Management & Tag Merging
        Route::get('/tags', [AdminTagController::class, 'index'])->name('tags.index');
        Route::post('/tags', [AdminTagController::class, 'store'])->name('tags.store');
        Route::put('/tags/{tag}', [AdminTagController::class, 'update'])->name('tags.update');
        Route::delete('/tags/{tag}', [AdminTagController::class, 'destroy'])->name('tags.destroy');
        Route::post('/tags/merge', [AdminTagController::class, 'merge'])->name('tags.merge');

        // Moderation Reports Queue
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/{report}/dismiss', [AdminReportController::class, 'dismiss'])->name('reports.dismiss');
        Route::post('/reports/{report}/delete-content', [AdminReportController::class, 'deleteContent'])->name('reports.deleteContent');

        // Badge Management
        Route::get('/badges', [AdminBadgeController::class, 'index'])->name('badges.index');
        Route::post('/badges', [AdminBadgeController::class, 'store'])->name('badges.store');
        Route::put('/badges/{badge}', [AdminBadgeController::class, 'update'])->name('badges.update');
        Route::delete('/badges/{badge}', [AdminBadgeController::class, 'destroy'])->name('badges.destroy');

        // Moderator Management
        Route::get('/moderators', [AdminModeratorController::class, 'index'])->name('moderators.index');
        Route::post('/users/{user}/appoint-moderator', [AdminModeratorController::class, 'appoint'])->name('users.appoint-moderator');
        Route::post('/users/{user}/remove-moderator', [AdminModeratorController::class, 'remove'])->name('users.remove-moderator');

        // Contact Messages
        Route::get('/contact', [AdminContactController::class, 'index'])->name('contact.index');
        Route::get('/contact/{contact}', [AdminContactController::class, 'show'])->name('contact.show');
        Route::post('/contact/{contact}/toggle-read', [AdminContactController::class, 'toggleRead'])->name('contact.toggleRead');
        Route::delete('/contact/{contact}', [AdminContactController::class, 'destroy'])->name('contact.destroy');

        // AI Control Center & Telemetry
        Route::get('/ai-center', [AdminAICenterController::class, 'index'])->name('ai-center.index');
        Route::post('/ai-center/clear-question/{question}', [AdminAICenterController::class, 'clearQuestionFlag'])->name('ai-center.clearQuestionFlag');
        Route::post('/ai-center/clear-answer/{answer}', [AdminAICenterController::class, 'clearAnswerFlag'])->name('ai-center.clearAnswerFlag');

        // Tag Merge AI (Admin)
        Route::get('/api/tags/duplicate-groups', [\App\Http\Controllers\Admin\TagMergeController::class, 'groups'])->name('admin.api.tags.groups');
        Route::post('/api/tags/merge', [\App\Http\Controllers\Admin\TagMergeController::class, 'merge'])->name('admin.api.tags.merge');

        // Reputation Settings
        Route::get('/reputation-settings', [\App\Http\Controllers\Admin\ReputationSettingsController::class, 'index'])->name('reputation-settings.index');
        Route::post('/reputation-settings', [\App\Http\Controllers\Admin\ReputationSettingsController::class, 'update'])->name('reputation-settings.update');

        // Audit Logs & Analytics
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    });
});
