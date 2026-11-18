<?php

namespace App\Http\Controllers;

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
        if ($listing->status?->value !== 'published') {
            abort(404);
        }

        return view('inquiries.create', ['listing' => $listing]);
    }

    /**
     * Store a consultation / inspection request for a listing.
     */
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        if ($listing->status?->value !== 'published') {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['consultation', 'inspection'])],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'message' => ['required', 'string'],
            'preferred_date' => ['nullable', 'date'],
        ]);

        $inquiry = new Inquiry($validated);
$inquiry->user_id = $request->user()?->id;
$inquiry->listing_id = $listing->id;
$inquiry->save();

        return redirect()
            ->route('listings.show', $listing->slug)
            ->with('status', __('Thank you! Your inquiry has been sent.'));
    }
}
