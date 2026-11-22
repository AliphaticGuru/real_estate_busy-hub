<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\SitemapController;
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
    ->middleware(['auth', 'verified'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::get('verify', [VerificationController::class, 'index'])->name('verify.index');
    Route::post('verify/listings/{listing}', [VerificationController::class, 'updateCheck'])->name('verify.checks.update');
});

Route::get('companies/{slug}', [CompanyController::class, 'show'])->name('companies.show');

Route::get('professionals', [ProfessionalController::class, 'index'])->name('professionals.index');
Route::get('professionals/{professional:slug}', [ProfessionalController::class, 'show'])->name('professionals.show');
Route::get('professionals/{professional:slug}/inquiries/create', [InquiryController::class, 'createForProfessional'])
    ->name('professionals.inquiries.create');
Route::post('professionals/{professional:slug}/inquiries', [InquiryController::class, 'storeForProfessional'])
    ->middleware('throttle:5,1')
    ->name('professionals.inquiries.store');

Route::get('listings/{listing:slug}/inquiries/create', [InquiryController::class, 'create'])->name('inquiries.create');
Route::post('listings/{listing:slug}/inquiries', [InquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('inquiries.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
    Route::patch('inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiries.status.update');
});

Route::get('robots.txt', function () {
    $disallowed = ['dashboard', 'inquiries', 'profile', 'verify', 'login', 'register'];

    $lines = ['User-agent: *'];

    foreach ($disallowed as $path) {
        $lines[] = 'Disallow: /'.$path;
    }

    $lines[] = '';
    $lines[] = 'Sitemap: '.url('sitemap.xml');

    return response(implode(PHP_EOL, $lines).PHP_EOL, 200, ['Content-Type' => 'text/plain']);
})->name('robots.txt');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.xml');

require __DIR__.'/auth.php';
