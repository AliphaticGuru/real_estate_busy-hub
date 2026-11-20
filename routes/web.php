<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
Route::get('listings/{listing:slug}', [ListingController::class, 'show'])->name('listings.show');

Route::view('fees', 'fees')->name('fees');
Route::view('verification', 'verification')->name('verification');

Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::get('verify', [VerificationController::class, 'index'])->name('verify.index');
    Route::post('verify/listings/{listing}', [VerificationController::class, 'updateCheck'])->name('verify.checks.update');
});

Route::get('companies/{slug}', [CompanyController::class, 'show'])->name('companies.show');
Route::get('listings/{listing:slug}/inquiries/create', [InquiryController::class, 'create'])->name('inquiries.create');
Route::post('listings/{listing:slug}/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');

require __DIR__.'/auth.php';
