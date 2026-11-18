<?php

namespace Tests\Feature;

use App\Enums\CheckKey;
use App\Models\Company;
use App\Models\Listing;
use App\Models\User;
use App\Models\VerificationCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_belongs_to_a_company(): void
    {
        $company = Company::factory()->create();
        $listing = Listing::factory()->create(['company_id' => $company->id]);

        $this->assertTrue($listing->company->is($company));
    }

    public function test_listing_belongs_to_its_creator(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($listing->createdBy->is($user));
    }

    public function test_listing_has_many_images(): void
    {
        $listing = Listing::factory()->hasImages(2)->create();

        $this->assertCount(2, $listing->images);
    }

    public function test_listing_has_many_verification_checks(): void
    {
        $listing = Listing::factory()->create();

        VerificationCheck::factory()->create([
            'listing_id' => $listing->id,
            'check_key' => CheckKey::TitleDocument->value,
        ]);
        VerificationCheck::factory()->create([
            'listing_id' => $listing->id,
            'check_key' => CheckKey::SurveyPlan->value,
        ]);

        $this->assertCount(2, $listing->verificationChecks);
    }

    public function test_listing_has_many_inquiries(): void
    {
        $listing = Listing::factory()->hasInquiries(2)->create();

        $this->assertCount(2, $listing->inquiries);
    }

    public function test_listing_has_many_audit_logs(): void
    {
        $listing = Listing::factory()->create();

        $listing->auditLogs()->create([
            'user_id' => $listing->createdBy->id,
            'action' => 'listing.created',
            'old_values' => null,
            'new_values' => ['listing' => ['title' => $listing->title]],
            'ip_address' => null,
        ]);

        $this->assertCount(1, $listing->auditLogs);
    }
}
