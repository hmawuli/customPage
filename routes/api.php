<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PageVersionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Clients
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{id}/statistics', [ClientController::class, 'statistics']);

    // Pages
    Route::apiResource('pages', PageController::class)->except(['update', 'destroy']);
    // Restrict update and destroy to page owner
    Route::put('pages/{page}', [PageController::class, 'update'])->middleware('ensure.client.owns.page');
    Route::delete('pages/{page}', [PageController::class, 'destroy'])->middleware('ensure.client.owns.page');
    Route::post('pages/{id}/publish', [PageController::class, 'publish'])->middleware('ensure.client.owns.page');
    Route::post('pages/{id}/unpublish', [PageController::class, 'unpublish'])->middleware('ensure.client.owns.page');
    Route::post('pages/upload-image', [PageController::class, 'uploadImage']);
    Route::delete('pages/delete-image', [PageController::class, 'deleteImage']);

    // Themes
    Route::apiResource('themes', ThemeController::class);
    Route::get('themes-popular', [ThemeController::class, 'popular']);
    Route::post('themes/{id}/preview', [ThemeController::class, 'preview']);

    // Analytics
    Route::get('analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('analytics/page/{pageId}', [AnalyticsController::class, 'pageAnalytics']);
    Route::get('analytics/trends', [AnalyticsController::class, 'viewsTrend']);
    Route::get('analytics/top-pages', [AnalyticsController::class, 'topPages']);
    Route::get('analytics/visitors', [AnalyticsController::class, 'visitorAnalytics']);
    Route::get('analytics/realtime', [AnalyticsController::class, 'realtime']);
    Route::get('analytics/export', [AnalyticsController::class, 'export']);

    // Page Versions
    Route::get('pages/{pageId}/versions', [PageVersionController::class, 'index']);
    Route::get('pages/{pageId}/versions/{versionId}', [PageVersionController::class, 'show']);
    Route::post('pages/{pageId}/versions/{versionId}/restore', [PageVersionController::class, 'restore']);
    Route::post('pages/{pageId}/versions/compare', [PageVersionController::class, 'compare']);
    Route::delete('pages/{pageId}/versions/{versionId}', [PageVersionController::class, 'destroy']);
});
