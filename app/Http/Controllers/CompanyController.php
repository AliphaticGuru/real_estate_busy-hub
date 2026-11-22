<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Models\Company;
use App\Models\Listing;
use App\Models\Professional;
use Illuminate\Contracts\View\View;

class CompanyController extends Controller
{
    /**
     * Public partner (company) profile page with the company's listings.
     */
    public function show(string $slug): View
    {
        $company = Company::query()->where('slug', $slug)->firstOrFail();

        $listings = Listing::query()
            ->where('status', ListingStatus::Published)
            ->where('company_id', $company->id)
            ->with(['images'])
            ->latest()
            ->get();

        $professionals = Professional::query()
            ->where('company_id', $company->id)
            ->whereNotNull('credentials_verified_at')
            ->with('user')
            ->orderBy('type')
            ->orderBy('id')
            ->get();

        return view('companies.show', [
            'company' => $company,
            'listings' => $listings,
            'professionals' => $professionals,
        ]);
    }
}
