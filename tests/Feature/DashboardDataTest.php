<?php

namespace Tests\Feature;

use App\Dashboard\DashboardData;
use App\Enums\InquiryStatus;
use App\Enums\ListingVerificationStatus;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DashboardDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function dataFor(User $user): array
    {
        return app(DashboardData::class)->for($user);
    }

    public function test_super_admin_payload_contains_platform_counts_and_five_latest_audit_entries(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $company = Company::factory()->create();
        Company::factory()->create();

        $pending = Listing::factory()->count(2)->create([
            'company_id' => $company->id,
            'verification_status' => ListingVerificationStatus::Pending->value,
        ]);
        $inReview = Listing::factory()->create([
            'company_id' => $company->id,
            'verification_status' => ListingVerificationStatus::InReview->value,
        ]);
        Listing::factory()->verified()->create(['company_id' => $company->id]);
        Listing::factory()->rejected()->create(['company_id' => $company->id]);

        Inquiry::factory()->count(2)->create([
            'listing_id' => $pending->first()->id,
            'status' => InquiryStatus::New->value,
        ]);
        Inquiry::factory()->create([
            'listing_id' => $inReview->id,
            'status' => InquiryStatus::Contacted->value,
        ]);

        $logs = collect(range(1, 6))->map(fn (int $index) => AuditLog::factory()->create([
            'action' => "audit-{$index}",
            'created_at' => now()->subMinutes(10 - $index),
        ]));

        $data = $this->dataFor($admin);

        $this->assertSame(RoleName::SuperAdmin, $data['role']);
        $this->assertSame(2, $data['counts']['companies']);
        $this->assertSame([
            'draft' => 5,
            'published' => 0,
            'archived' => 0,
        ], $data['counts']['listing_status']);
        $this->assertSame([
            'pending' => 2,
            'in_review' => 1,
            'verified' => 1,
            'rejected' => 1,
        ], $data['counts']['verification_status']);
        $this->assertSame(2, $data['counts']['inquiry_status']['new']);
        $this->assertSame(1, $data['counts']['inquiry_status']['contacted']);
        $this->assertSame(0, $data['counts']['inquiry_status']['closed']);

        $this->assertCount(5, $data['recentAuditLogs']);
        $this->assertSame($logs->last()->id, $data['recentAuditLogs']->first()->id);
        $this->assertFalse($data['recentAuditLogs']->contains('id', $logs->first()->id));

        $this->assertSame(__('Listings by verification status'), $data['breakdowns'][0]['title']);

        $links = collect($data['links'])->keyBy('label');
        $this->assertTrue($links[__('Review verification queue')]['enabled']);
        $this->assertFalse($links[__('Manage companies')]['enabled']);
        $this->assertNull($links[__('Manage companies')]['url']);
    }

    public function test_verifier_payload_counts_waiting_listings_and_returns_the_five_oldest(): void
    {
        $verifier = User::factory()->verifier()->create();

        $waiting = collect(range(1, 6))->map(fn (int $index) => Listing::factory()->create([
            'title' => "Waiting {$index}",
            'verification_status' => $index % 2 === 0
                ? ListingVerificationStatus::InReview->value
                : ListingVerificationStatus::Pending->value,
            'created_at' => now()->subMinutes(10 - $index),
        ]));

        Listing::factory()->verified()->create(['title' => 'Already verified']);

        $data = $this->dataFor($verifier);

        $this->assertSame(RoleName::Verifier, $data['role']);
        $this->assertSame(6, $data['counts']['awaiting_review']);
        $this->assertSame(3, $data['counts']['verification_status']['pending']);
        $this->assertSame(3, $data['counts']['verification_status']['in_review']);
        $this->assertSame(1, $data['counts']['verification_status']['verified']);

        $this->assertCount(5, $data['recentListings']);
        $this->assertSame(
            $waiting->take(5)->pluck('id')->all(),
            $data['recentListings']->pluck('id')->all(),
        );

        $links = collect($data['links'])->keyBy('label');
        $this->assertSame(route('verify.index'), $links[__('Open verification queue')]['url']);
    }

    public function test_company_payload_is_scoped_to_the_users_company(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $user = User::factory()->company()->create();
        $user->forceFill(['company_id' => $ownCompany->id])->save();

        $ownPublished = Listing::factory()->published()->verified()->create([
            'company_id' => $ownCompany->id,
        ]);
        Listing::factory()->create([
            'company_id' => $ownCompany->id,
            'verification_status' => ListingVerificationStatus::Pending->value,
        ]);

        $otherPublished = Listing::factory()->published()->verified()->create([
            'company_id' => $otherCompany->id,
        ]);
        Listing::factory()->published()->verified()->create([
            'company_id' => $otherCompany->id,
        ]);

        $ownNew = Inquiry::factory()->create([
            'listing_id' => $ownPublished->id,
            'status' => InquiryStatus::New->value,
        ]);
        $ownClosed = Inquiry::factory()->create([
            'listing_id' => $ownPublished->id,
            'status' => InquiryStatus::Closed->value,
        ]);
        $otherNew = Inquiry::factory()->create([
            'listing_id' => $otherPublished->id,
            'status' => InquiryStatus::New->value,
        ]);
        Inquiry::factory()->create([
            'listing_id' => null,
            'status' => InquiryStatus::New->value,
        ]);

        $data = $this->dataFor($user);

        $this->assertSame(RoleName::Company, $data['role']);
        $this->assertSame([
            'draft' => 1,
            'published' => 1,
            'archived' => 0,
        ], $data['counts']['listing_status']);
        $this->assertSame([
            'pending' => 1,
            'in_review' => 0,
            'verified' => 1,
            'rejected' => 0,
        ], $data['counts']['verification_status']);
        $this->assertSame([
            'new' => 1,
            'contacted' => 0,
            'closed' => 1,
        ], $data['counts']['inquiry_status']);

        $this->assertSame(
            [$ownNew->id, $ownClosed->id],
            $data['recentInquiries']->pluck('id')->sort()->values()->all(),
        );
        $this->assertFalse($data['recentInquiries']->contains('id', $otherNew->id));

        $links = collect($data['links'])->keyBy('label');
        $this->assertSame(
            route('companies.show', $ownCompany->slug),
            $links[__('View public company profile')]['url'],
        );
        $this->assertFalse($links[__('Manage listings')]['enabled']);
    }

    public function test_company_payload_without_a_company_returns_zero_counts(): void
    {
        $user = User::factory()->company()->create();

        Company::factory()->count(2)->create();
        Listing::factory()->count(3)->verified()->create();
        Inquiry::factory()->count(2)->create(['status' => InquiryStatus::New->value]);

        $data = $this->dataFor($user);

        $this->assertSame(0, array_sum($data['counts']['listing_status']));
        $this->assertSame(0, array_sum($data['counts']['verification_status']));
        $this->assertSame(0, array_sum($data['counts']['inquiry_status']));
        $this->assertCount(0, $data['recentInquiries']);
    }

    public function test_professional_payload_is_scoped_to_their_own_inquiries(): void
    {
        $user = User::factory()->professional()->create();
        $professional = Professional::factory()->create(['user_id' => $user->id]);
        $otherProfessional = Professional::factory()->create();

        $ownNew = collect(range(1, 2))->map(fn () => Inquiry::factory()->create([
            'professional_id' => $professional->id,
            'status' => InquiryStatus::New->value,
        ]));
        Inquiry::factory()->create([
            'professional_id' => $professional->id,
            'status' => InquiryStatus::Contacted->value,
        ]);
        $otherInquiry = Inquiry::factory()->create([
            'professional_id' => $otherProfessional->id,
            'status' => InquiryStatus::New->value,
        ]);

        $data = $this->dataFor($user);

        $this->assertSame(RoleName::Professional, $data['role']);
        $this->assertSame($professional->id, $data['counts']['professional_id']);
        $this->assertSame([
            'new' => 2,
            'contacted' => 1,
            'closed' => 0,
        ], $data['counts']['inquiry_status']);
        $this->assertCount(3, $data['recentInquiries']);
        $this->assertFalse($data['recentInquiries']->contains('id', $otherInquiry->id));
        $this->assertTrue($data['recentInquiries']->pluck('id')->intersect($ownNew->pluck('id'))->isNotEmpty());
    }

    public function test_customer_payload_is_scoped_to_their_own_inquiries(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        $own = collect(range(1, 2))->map(fn () => Inquiry::factory()->create([
            'user_id' => $customer->id,
            'status' => InquiryStatus::New->value,
        ]));
        Inquiry::factory()->create([
            'user_id' => $customer->id,
            'status' => InquiryStatus::Closed->value,
        ]);
        $otherInquiry = Inquiry::factory()->create([
            'user_id' => $otherCustomer->id,
            'status' => InquiryStatus::New->value,
        ]);
        Inquiry::factory()->create([
            'user_id' => null,
            'status' => InquiryStatus::New->value,
        ]);

        $data = $this->dataFor($customer);

        $this->assertSame(RoleName::Customer, $data['role']);
        $this->assertSame([
            'new' => 2,
            'contacted' => 0,
            'closed' => 1,
        ], $data['counts']['inquiry_status']);
        $this->assertCount(3, $data['recentInquiries']);
        $this->assertFalse($data['recentInquiries']->contains('id', $otherInquiry->id));
        $this->assertTrue($data['recentInquiries']->pluck('id')->intersect($own->pluck('id'))->isNotEmpty());

        $links = collect($data['links'])->keyBy('label');
        $this->assertSame(route('listings.index'), $links[__('Browse verified listings')]['url']);
    }

    public function test_user_without_a_role_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role_id' => null])->save();

        try {
            $this->dataFor($user->fresh());
            $this->fail('Expected a 403 response for a user without a role.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
