<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicPageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('welcome');
});

// --------------------
// FRONTEND  ROUTES
// --------------------

// Client Login & Register (frontend only)
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

// Dashboard pages (frontend mockup)
Route::view('/dashboard', 'dashboard.index')->name('dashboard');
Route::view('/dashboard/edit-page', 'dashboard.edit-page')->name('edit.page');
Route::view('/dashboard/analytics', 'dashboard.analytics')->name('analytics');

// --------------------
// PUBLIC CLIENT PAGES
// --------------------
Route::get('/{clientSlug}', [PublicPageController::class, 'clientHome'])->name('client.home');
Route::get('/{clientSlug}/{pageSlug}', [PublicPageController::class, 'show'])->name('page.show');

// API endpoints for public pages
Route::get('/api/public/{clientSlug}/pages', [PublicPageController::class, 'clientPages'])->name('client.pages');
Route::get('/api/public/{clientSlug}/{pageSlug}', [PublicPageController::class, 'getPageData'])->name('page.data');
