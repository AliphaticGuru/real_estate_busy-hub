<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_any_inquiry(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $inquiry = Inquiry::factory()->create();

        $this->assertTrue($superAdmin->can('view', $inquiry));
        $this->assertTrue($superAdmin->can('update', $inquiry));
        $this->assertTrue($superAdmin->can('delete', $inquiry));
    }

    public function test_professional_owner_can_view_and_update_their_inquiry(): void
    {
        $professionalUser = User::factory()->professional()->create();
        $professional = Professional::factory()->create(['user_id' => $professionalUser->id]);
        $inquiry = Inquiry::factory()->create(['professional_id' => $professional->id]);

        $this->assertTrue($professionalUser->can('view', $inquiry));
        $this->assertTrue($professionalUser->can('update', $inquiry));
    }

    public function test_company_staff_can_view_inquiry_of_listing_they_own(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->company()->create();
        $user->company_id = $company->id;
        $user->save();

        $listing = Listing::factory()->create(['company_id' => $company->id]);
        $inquiry = Inquiry::factory()->create(['listing_id' => $listing->id]);

        $this->assertTrue($user->can('view', $inquiry));
    }

    public function test_stranger_cannot_view_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->assertFalse(User::factory()->customer()->create()->can('view', $inquiry));
    }
}
