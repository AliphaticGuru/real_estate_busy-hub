<?php

use App\Http\Controllers\ListingController;
use App\Http\Controllers\VerificationController;
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

Route::middleware('auth')->group(function () {
    Route::get('verify', [VerificationController::class, 'index'])->name('verify.index');
    Route::post('verify/listings/{listing}', [VerificationController::class, 'updateCheck'])->name('verify.checks.update');
});

require __DIR__.'/auth.php';
