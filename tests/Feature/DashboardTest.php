<?php

namespace Tests\Feature;

use App\Enums\InquiryStatus;
use App\Enums\ListingVerificationStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_a_user_without_a_valid_role_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role_id' => null])->save();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_super_admin_sees_platform_counts_and_recent_audit_activity(): void
    {
        $admin = User::factory()->superAdmin()->create();

        Company::factory()->count(2)->create();
        Listing::factory()->count(3)->create([
            'verification_status' => ListingVerificationStatus::Pending->value,
        ]);
        Inquiry::factory()->count(2)->create(['status' => InquiryStatus::New->value]);
        AuditLog::factory()->create(['action' => 'listing.flagged.for.review']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Super admin dashboard')
            ->assertSee('Companies')
            ->assertSee('Listings by verification status')
            ->assertSee('Listing Flagged For Review')
            ->assertSee('Manage companies')
            ->assertSee('Coming soon')
            ->assertSee(route('verify.index'), false);
    }

    public function test_verifier_sees_waiting_listings_and_the_queue_link(): void
    {
        $verifier = User::factory()->verifier()->create();

        Listing::factory()->create([
            'title' => 'Waiting Villa',
            'verification_status' => ListingVerificationStatus::Pending->value,
        ]);
        Listing::factory()->verified()->create([
            'title' => 'Already Verified Villa',
        ]);

        $this->actingAs($verifier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Verification dashboard')
            ->assertSee('Waiting Villa')
            ->assertDontSee('Already Verified Villa')
            ->assertDontSee('Nothing waiting')
            ->assertSee(route('verify.index'), false);
    }

    public function test_verifier_sees_an_empty_state_when_nothing_is_waiting(): void
    {
        $verifier = User::factory()->verifier()->create();
        Listing::factory()->verified()->create();

        $this->actingAs($verifier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Nothing waiting')
            ->assertSee('No listings are pending or in review right now.');
    }

    public function test_company_user_only_sees_their_own_company_data(): void
    {
        $ownCompany = Company::factory()->create(['name' => 'Alpha Homes']);
        $otherCompany = Company::factory()->create(['name' => 'Beta Estates']);

        $user = User::factory()->company()->create();
        $user->forceFill(['company_id' => $ownCompany->id])->save();

        $ownListing = Listing::factory()->published()->verified()->create([
            'company_id' => $ownCompany->id,
            'title' => 'Alpha Own Listing',
        ]);
        $otherListing = Listing::factory()->published()->verified()->create([
            'company_id' => $otherCompany->id,
            'title' => 'Beta Secret Listing',
        ]);

        Inquiry::factory()->create([
            'listing_id' => $ownListing->id,
            'name' => 'Own Buyer',
        ]);
        Inquiry::factory()->create([
            'listing_id' => $otherListing->id,
            'name' => 'Foreign Buyer',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Company dashboard')
            ->assertSee('Own Buyer')
            ->assertSee('Alpha Own Listing')
            ->assertSee(route('companies.show', $ownCompany->slug), false)
            ->assertDontSee('Foreign Buyer')
            ->assertDontSee('Beta Secret Listing')
            ->assertDontSee(route('companies.show', $otherCompany->slug), false);
    }

    public function test_company_user_without_a_company_sees_empty_states(): void
    {
        $user = User::factory()->company()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Company dashboard')
            ->assertSee('Complete company profile')
            ->assertSee('No inquiries yet');
    }

    public function test_professional_only_sees_their_own_inquiries(): void
    {
        $user = User::factory()->professional()->create();
        $professional = Professional::factory()->create(['user_id' => $user->id]);
        $otherProfessional = Professional::factory()->create();

        $ownListing = Listing::factory()->create(['title' => 'Own Client Listing']);
        $otherListing = Listing::factory()->create(['title' => 'Other Client Listing']);

        Inquiry::factory()->create([
            'professional_id' => $professional->id,
            'listing_id' => $ownListing->id,
            'name' => 'Own Client',
        ]);
        Inquiry::factory()->create([
            'professional_id' => $otherProfessional->id,
            'listing_id' => $otherListing->id,
            'name' => 'Other Client',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Professional dashboard')
            ->assertSee('Own Client')
            ->assertSee('Own Client Listing')
            ->assertDontSee('Other Client')
            ->assertDontSee('Other Client Listing');
    }

    public function test_customer_only_sees_their_own_inquiries_and_a_browse_link(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        $ownListing = Listing::factory()->create(['title' => 'Customer Own Listing']);
        $otherListing = Listing::factory()->create(['title' => 'Customer Other Listing']);

        Inquiry::factory()->create([
            'user_id' => $customer->id,
            'listing_id' => $ownListing->id,
            'name' => 'My Own Inquiry',
            'status' => InquiryStatus::New->value,
        ]);
        Inquiry::factory()->create([
            'user_id' => $otherCustomer->id,
            'listing_id' => $otherListing->id,
            'name' => 'Someone Elses Inquiry',
            'status' => InquiryStatus::New->value,
        ]);

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Your dashboard')
            ->assertSee('My Own Inquiry')
            ->assertSee('Customer Own Listing')
            ->assertSee(route('listings.index'), false)
            ->assertDontSee('Someone Elses Inquiry')
            ->assertDontSee('Customer Other Listing');
    }

    public function test_customer_with_no_inquiries_sees_an_empty_state(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No inquiries yet')
            ->assertSee('Browse verified listings');
    }
}
