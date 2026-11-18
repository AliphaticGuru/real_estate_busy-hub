<?php

namespace App\Http\Controllers;

use App\Enums\CheckKey;
use App\Enums\CheckStatus;
use App\Enums\ListingVerificationStatus;
use App\Models\Listing;
use App\Models\VerificationCheck;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VerificationController extends Controller
{
    /**
     * Verification queue: listings that are pending or in review.
     */
    public function index(): View
    {
        $user = request()->user();

        if (! $user || (! $user->isSuperAdmin() && ! $user->isVerifier())) {
            abort(403);
        }

        $listings = Listing::query()
            ->whereIn('verification_status', ['pending', 'in_review'])
            ->with(['company', 'images', 'verificationChecks'])
            ->latest()
            ->get();

        return view('verifications.index', ['listings' => $listings]);
    }

    /**
     * Update a single verification check outcome and recompute the listing status.
     */
    public function updateCheck(Request $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->isSuperAdmin() && ! $user->isVerifier())) {
            abort(403);
        }

        $validated = $request->validate([
            'check_key' => ['required', 'string', Rule::in(CheckKey::values())],
            'status' => ['required', 'string', Rule::in(['passed', 'failed'])],
        ]);

        VerificationCheck::updateOrCreate(
            ['listing_id' => $listing->id, 'check_key' => $validated['check_key']],
            [
                'status' => $validated['status'] === 'passed' ? CheckStatus::Passed : CheckStatus::Failed,
                'checked_by' => $user->id,
                'checked_at' => now(),
            ],
        );

        $checks = VerificationCheck::where('listing_id', $listing->id)->get();
        $passed = $checks->filter(fn ($check) => $check->status === CheckStatus::Passed)->count();
        $failed = $checks->filter(fn ($check) => $check->status === CheckStatus::Failed)->count();

        if ($failed > 0) {
            $listing->update([
                'verification_status' => ListingVerificationStatus::Rejected,
                'verified_by' => $user->id,
            ]);
        } elseif ($checks->isNotEmpty() && $passed === $checks->count()) {
            $listing->update([
                'verification_status' => ListingVerificationStatus::Verified,
                'verified_at' => now(),
                'verified_by' => $user->id,
            ]);
        }

        return redirect()
            ->route('verify.index')
            ->with('status', __('Verification update submitted.'));
    }
}
