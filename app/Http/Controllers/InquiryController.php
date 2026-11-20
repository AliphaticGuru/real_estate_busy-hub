<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Models\Inquiry;
use App\Models\Listing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InquiryController extends Controller
{
    /**
     * Public consultation / inspection request form for a listing.
     */
    public function create(Listing $listing): View
    {
        $this->ensurePublished($listing);

        return view('inquiries.create', ['listing' => $listing]);
    }

    /**
     * Store a consultation / inspection request for a listing.
     */
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $this->ensurePublished($listing);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['consultation', 'inspection'])],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'message' => ['required', 'string'],
            'preferred_date' => ['nullable', 'date'],
        ]);

        $inquiry = new Inquiry($validated);
        $inquiry->listing_id = $listing->id;
        $inquiry->user_id = $request->user()?->id;
        $inquiry->save();

        return redirect()
            ->route('listings.show', $listing->slug)
            ->with('status', __('Thank you! Your inquiry has been sent.'));
    }

    /**
     * Draft, archived and other non-public listings are not inquiry targets.
     */
    private function ensurePublished(Listing $listing): void
    {
        if ($listing->status !== ListingStatus::Published) {
            abort(404);
        }
    }
}
