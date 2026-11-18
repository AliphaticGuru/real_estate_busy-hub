<?php

use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
Route::get('listings/{slug}', [ListingController::class, 'show'])->name('listings.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
