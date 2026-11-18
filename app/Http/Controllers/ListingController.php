<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    /**
     * Public listing directory with optional filters.
     */
    public function index(Request $request): View
    {
        $listings = Listing::query()
            ->where('status', ListingStatus::Published)
            ->with(['company', 'images'])
            ->when($request->filled('property_type'), fn ($query) => $query->where('property_type', $request->input('property_type')))
            ->when($request->filled('city'), fn ($query) => $query->where('city', $request->input('city')))
            ->when($request->filled('district'), fn ($query) => $query->where('district', $request->input('district')))
            ->when($request->filled('bedrooms'), fn ($query) => $query->where('bedrooms', '>=', (int) $request->input('bedrooms')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', (int) $request->input('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', (int) $request->input('max_price')))
            ->latest()
            ->get();

        return view('listings.index', ['listings' => $listings]);
    }

    /**
     * Public listing detail page.
     */
    public function show(string $slug): View
    {
        $listing = Listing::query()
            ->where('status', ListingStatus::Published)
            ->with(['company', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('listings.show', ['listing' => $listing]);
    }
}
