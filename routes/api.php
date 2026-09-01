<?php

use App\Http\Controllers\AIController;
use App\Http\Controllers\Admin\TagMergeController as AdminTagMergeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TagMergeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for DiscussHub
|--------------------------------------------------------------------------
*/

// AI Endpoints
Route::post('/ai/check-duplicate', [AIController::class, 'checkDuplicate'])->name('api.ai.check-duplicate');
Route::post('/ai/suggest-tags', [AIController::class, 'suggestTags'])->name('api.ai.suggest-tags');
Route::get('/ai/summarize/{id}', [AIController::class, 'summarize'])->name('api.ai.summarize');
Route::post('/ai/quality-check', [AIController::class, 'evaluateQuality'])->name('api.ai.quality-check');
Route::post('/ai/generate-answer/{id}', [AIController::class, 'generateAnswer'])->name('api.ai.generate-answer');

// Related & Taxonomy
Route::get('/questions/related/{id}', [QuestionController::class, 'related'])->name('api.questions.related');
Route::get('/tags/search', [TagController::class, 'search'])->name('api.tags.search');

// Tag & Category Deduplication (public — no auth required)
Route::post('/tags/check-duplicate', [TagMergeController::class, 'checkTag'])->name('api.tags.check-duplicate');
Route::post('/categories/check-similar', [TagMergeController::class, 'checkCategory'])->name('api.categories.check-similar');

// Realtime helpers
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');
});
