<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_any_listing(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $listing = Listing::factory()->create();

        $this->assertTrue($superAdmin->can('update', $listing));
    }

    public function test_company_user_can_update_their_own_companies_listing(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company()->create();
        $user->company_id = $company->id;
        $user->save();

        $listing = Listing::factory()->create(['company_id' => $company->id]);

        $this->assertTrue($user->can('update', $listing));
    }

    public function test_company_user_cannot_update_another_companies_listing(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $userA = User::factory()->company()->create();
        $userA->company_id = $companyA->id;
        $userA->save();

        $listingB = Listing::factory()->create(['company_id' => $companyB->id]);

        $this->assertFalse($userA->can('update', $listingB));
        $this->assertFalse($userA->can('delete', $listingB));
    }

    public function test_company_user_cannot_delete_another_companies_listing(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $userA = User::factory()->company()->create();
        $userA->company_id = $companyA->id;
        $userA->save();

        $listingB = Listing::factory()->create(['company_id' => $companyB->id]);

        $this->assertFalse($userA->can('delete', $listingB));
    }

    public function test_customers_can_only_view_published_listings(): void
    {
        $customer = User::factory()->customer()->create();
        $published = Listing::factory()->published()->create();
        $draft = Listing::factory()->create();

        $this->assertTrue($customer->can('view', $published));
        $this->assertFalse($customer->can('view', $draft));
    }

    public function test_only_verifier_or_super_admin_can_change_verification_status(): void
    {
        $verifier = User::factory()->verifier()->create();
        $listing = Listing::factory()->create();

        $this->assertTrue($verifier->can('updateVerificationStatus', $listing));

        $customer = User::factory()->customer()->create();
        $this->assertFalse($customer->can('updateVerificationStatus', $listing));
    }

    public function test_verifier_or_super_admin_can_change_verification_status(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $listing = Listing::factory()->create();

        $this->assertTrue($superAdmin->can('updateVerificationStatus', $listing));
    }
}
