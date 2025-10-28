<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicPageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Public client pages
Route::get('/{clientSlug}', [PublicPageController::class, 'clientHome'])
    ->name('client.home');

Route::get('/{clientSlug}/{pageSlug}', [PublicPageController::class, 'show'])
    ->name('page.show');

// API endpoints for public pages
Route::get('/api/public/{clientSlug}/pages', [PublicPageController::class, 'clientPages'])
    ->name('client.pages');

Route::get('/api/public/{clientSlug}/{pageSlug}', [PublicPageController::class, 'getPageData'])
    ->name('page.data');
