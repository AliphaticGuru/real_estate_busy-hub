<?php

namespace App\Dashboard;

use App\Enums\InquiryStatus;
use App\Enums\ListingStatus;
use App\Enums\ListingVerificationStatus;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builds the data for the role-specific dashboard.
 *
 * Queries are scoped here explicitly; policies are not relied on to filter
 * result sets. The Blade view only renders what this class returns.
 */
class DashboardData
{
    /**
     * Return the dashboard payload for the given user, or abort with 403.
     *
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        return match ($user->role?->name) {
            RoleName::SuperAdmin => $this->superAdmin(),
            RoleName::Verifier => $this->verifier(),
            RoleName::Company => $this->company($user),
            RoleName::Professional => $this->professional($user),
            RoleName::Customer => $this->customer($user),
            default => abort(403),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function superAdmin(): array
    {
        $companies = Company::query()->count();
        $listingStatuses = $this->listingCounts(ListingStatus::cases());
        $verificationStatuses = $this->listingCounts(ListingVerificationStatus::cases());
        $inquiryStatuses = $this->inquiryCounts(Inquiry::query());

        $recentAuditLogs = AuditLog::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return $this->payload(
            role: RoleName::SuperAdmin,
            title: __('Super admin dashboard'),
            subtitle: __('Platform-wide counts, verification status and the latest audit activity.'),
            stats: [
                ['label' => __('Companies'), 'value' => $companies],
                ['label' => __('Listings'), 'value' => array_sum($listingStatuses)],
                ['label' => __('New inquiries'), 'value' => $inquiryStatuses[InquiryStatus::New->value]],
            ],
            breakdowns: [
                $this->breakdown(__('Listings by verification status'), $verificationStatuses),
            ],
            recentAuditLogs: $recentAuditLogs,
            links: [
                $this->link(__('Review verification queue'), route('verify.index')),
                $this->link(__('Browse listings'), route('listings.index')),
                $this->link(__('Manage companies')),
                $this->link(__('Manage professionals')),
            ],
            counts: [
                'companies' => $companies,
                'listing_status' => $listingStatuses,
                'verification_status' => $verificationStatuses,
                'inquiry_status' => $inquiryStatuses,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function verifier(): array
    {
        $verificationStatuses = $this->listingCounts(ListingVerificationStatus::cases());
        $awaitingReview = $verificationStatuses[ListingVerificationStatus::Pending->value]
            + $verificationStatuses[ListingVerificationStatus::InReview->value];

        $oldestWaiting = Listing::query()
            ->whereIn('verification_status', [
                ListingVerificationStatus::Pending->value,
                ListingVerificationStatus::InReview->value,
            ])
            ->with('company')
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit(5)
            ->get();

        return $this->payload(
            role: RoleName::Verifier,
            title: __('Verification dashboard'),
            subtitle: __('Listings waiting for review, oldest first.'),
            stats: [
                ['label' => __('Awaiting review'), 'value' => $awaitingReview],
                ['label' => __('Pending'), 'value' => $verificationStatuses[ListingVerificationStatus::Pending->value]],
                ['label' => __('In review'), 'value' => $verificationStatuses[ListingVerificationStatus::InReview->value]],
            ],
            recentListings: $oldestWaiting,
            links: [
                $this->link(__('Open verification queue'), route('verify.index')),
                $this->link(__('Browse listings'), route('listings.index')),
                $this->link(__('Verification history')),
            ],
            counts: [
                'verification_status' => $verificationStatuses,
                'awaiting_review' => $awaitingReview,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function company(User $user): array
    {
        $company = $user->company()->first();
        $companyId = $company?->id;

        $listingStatuses = $this->listingCounts(ListingStatus::cases(), $companyId, true);
        $verificationStatuses = $this->listingCounts(ListingVerificationStatus::cases(), $companyId, true);
        $companyInquiries = $this->companyInquiries($companyId);
        $inquiryStatuses = $this->inquiryCounts(clone $companyInquiries);

        $recentInquiries = $companyInquiries
            ->with('listing')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $links = [
            $this->link(__('Browse listings'), route('listings.index')),
        ];

        if ($company) {
            $links[] = $this->link(__('View public company profile'), route('companies.show', $company->slug));
        } else {
            $links[] = $this->link(__('Complete company profile'));
        }

        $links[] = $this->link(__('Manage listings'));

        return $this->payload(
            role: RoleName::Company,
            title: __('Company dashboard'),
            subtitle: __('Your listings, verification progress and the latest inquiries.'),
            stats: [
                ['label' => __('Own listings'), 'value' => array_sum($listingStatuses)],
                ['label' => __('New inquiries'), 'value' => $inquiryStatuses[InquiryStatus::New->value]],
                ['label' => __('Verified listings'), 'value' => $verificationStatuses[ListingVerificationStatus::Verified->value]],
            ],
            breakdowns: [
                $this->breakdown(__('Listings by status'), $listingStatuses),
                $this->breakdown(__('Listings by verification status'), $verificationStatuses),
                $this->breakdown(__('Inquiries by status'), $inquiryStatuses),
            ],
            recentInquiries: $recentInquiries,
            links: $links,
            counts: [
                'listing_status' => $listingStatuses,
                'verification_status' => $verificationStatuses,
                'inquiry_status' => $inquiryStatuses,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function professional(User $user): array
    {
        $professional = Professional::query()->where('user_id', $user->id)->first();

        $inquiries = $professional
            ? Inquiry::query()->where('professional_id', $professional->id)
            : Inquiry::query()->whereRaw('1 = 0');

        $inquiryStatuses = $this->inquiryCounts(clone $inquiries);

        $recentInquiries = $inquiries
            ->with(['listing', 'professional'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return $this->payload(
            role: RoleName::Professional,
            title: __('Professional dashboard'),
            subtitle: __('Inquiries addressed to your professional profile.'),
            stats: [
                ['label' => __('Total inquiries'), 'value' => array_sum($inquiryStatuses)],
                ['label' => __('New'), 'value' => $inquiryStatuses[InquiryStatus::New->value]],
                ['label' => __('Contacted'), 'value' => $inquiryStatuses[InquiryStatus::Contacted->value]],
            ],
            breakdowns: [
                $this->breakdown(__('Inquiries by status'), $inquiryStatuses),
            ],
            recentInquiries: $recentInquiries,
            links: [
                $this->link(__('Browse listings'), route('listings.index')),
                $this->link(__('Edit professional profile')),
            ],
            counts: [
                'professional_id' => $professional?->id,
                'inquiry_status' => $inquiryStatuses,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function customer(User $user): array
    {
        $inquiries = Inquiry::query()->where('user_id', $user->id);
        $inquiryStatuses = $this->inquiryCounts(clone $inquiries);

        $recentInquiries = $inquiries
            ->with(['listing', 'professional'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return $this->payload(
            role: RoleName::Customer,
            title: __('Your dashboard'),
            subtitle: __('The inquiries you have submitted and their current status.'),
            stats: [
                ['label' => __('Submitted inquiries'), 'value' => array_sum($inquiryStatuses)],
                ['label' => __('New'), 'value' => $inquiryStatuses[InquiryStatus::New->value]],
                ['label' => __('Closed'), 'value' => $inquiryStatuses[InquiryStatus::Closed->value]],
            ],
            breakdowns: [
                $this->breakdown(__('Inquiries by status'), $inquiryStatuses),
            ],
            recentInquiries: $recentInquiries,
            links: [
                $this->link(__('Browse verified listings'), route('listings.index')),
            ],
            counts: [
                'inquiry_status' => $inquiryStatuses,
            ],
        );
    }

    /**
     * @param  array<int, ListingStatus|ListingVerificationStatus>  $cases
     * @return array<string, int>
     */
    private function listingCounts(array $cases, ?int $companyId = null, bool $scoped = false): array
    {
        if ($scoped && $companyId === null) {
            return $this->zeroCounts($cases);
        }

        $column = $cases[0] instanceof ListingStatus ? 'status' : 'verification_status';

        $query = Listing::query()
            ->selectRaw("{$column}, count(*) as aggregate")
            ->groupBy($column);

        if ($scoped) {
            $query->where('company_id', $companyId);
        }

        $counts = $query->pluck('aggregate', $column);

        $result = [];

        foreach ($cases as $case) {
            $result[$case->value] = (int) ($counts[$case->value] ?? 0);
        }

        return $result;
    }

    /**
     * @param  array<int, ListingStatus|ListingVerificationStatus|InquiryStatus>  $cases
     * @return array<string, int>
     */
    private function zeroCounts(array $cases): array
    {
        return collect($cases)
            ->mapWithKeys(fn ($case) => [$case->value => 0])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function inquiryCounts(Builder $query): array
    {
        $counts = $query
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $result = [];

        foreach (InquiryStatus::cases() as $case) {
            $result[$case->value] = (int) ($counts[$case->value] ?? 0);
        }

        return $result;
    }

    /**
     * Inquiries for a company, resolved through the listing's company_id.
     */
    private function companyInquiries(?int $companyId): Builder
    {
        if ($companyId === null) {
            return Inquiry::query()->whereRaw('1 = 0');
        }

        return Inquiry::query()
            ->whereHas('listing', fn (Builder $listing) => $listing->where('company_id', $companyId));
    }

    /**
     * @param  array<string, int>  $counts
     * @return array{title: string, items: array<int, array{label: string, value: int}>}
     */
    private function breakdown(string $title, array $counts): array
    {
        return [
            'title' => $title,
            'items' => collect($counts)
                ->map(fn (int $value, string $key) => [
                    'label' => Str::headline($key),
                    'value' => $value,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{label: string, url: string|null, enabled: bool}
     */
    private function link(string $label, ?string $url = null): array
    {
        return [
            'label' => $label,
            'url' => $url,
            'enabled' => $url !== null,
        ];
    }

    /**
     * @param  array<int, array{label: string, value: int}>  $stats
     * @param  array<int, array{title: string, items: array<int, array{label: string, value: int}>}>  $breakdowns
     * @param  Collection<int, mixed>|null  $recentListings
     * @param  Collection<int, mixed>|null  $recentInquiries
     * @param  Collection<int, mixed>|null  $recentAuditLogs
     * @param  array<int, array{label: string, url: string|null, enabled: bool}>  $links
     * @param  array<string, mixed>  $counts
     * @return array<string, mixed>
     */
    private function payload(
        RoleName $role,
        string $title,
        string $subtitle,
        array $stats,
        array $breakdowns = [],
        ?Collection $recentListings = null,
        ?Collection $recentInquiries = null,
        ?Collection $recentAuditLogs = null,
        array $links = [],
        array $counts = [],
    ): array {
        return [
            'role' => $role,
            'title' => $title,
            'subtitle' => $subtitle,
            'stats' => $stats,
            'breakdowns' => $breakdowns,
            'recentListings' => $recentListings ?? collect(),
            'recentInquiries' => $recentInquiries ?? collect(),
            'recentAuditLogs' => $recentAuditLogs ?? collect(),
            'links' => $links,
            'counts' => $counts,
        ];
    }
}
