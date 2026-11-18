<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_submit_a_consultation_inquiry_for_a_published_listing(): void
    {
        $listing = Listing::factory()->published()->create();

        $this->post(route('inquiries.store', $listing->id), [
            'type' => 'consultation',
            'name' => 'John Doe',
            'phone' => '+2348000000000',
            'email' => 'john@example.com',
            'message' => 'I would like a consultation.',
            'preferred_date' => '2026-12-01',
        ])
            ->assertRedirect(route('listings.show', $listing->slug));

        $this->assertDatabaseHas('inquiries', [
            'listing_id' => $listing->id,
            'type' => 'consultation',
            'name' => 'John Doe',
            'status' => 'new',
            'user_id' => null,
        ]);
    }

    public function test_guests_can_submit_an_inspection_inquiry_for_a_published_listing(): void
    {
        $listing = Listing::factory()->published()->create();

        $this->post(route('inquiries.store', $listing->id), [
            'type' => 'inspection',
            'name' => 'Jane Doe',
            'phone' => '+2348000000001',
            'email' => 'jane@example.com',
            'message' => 'I would like an inspection.',
        ])
            ->assertRedirect(route('listings.show', $listing->slug));

        $this->assertDatabaseHas('inquiries', [
            'listing_id' => $listing->id,
            'type' => 'inspection',
            'user_id' => null,
        ]);
    }

    public function test_authenticated_user_inquiry_stores_user_id(): void
    {
        $user = new User(['name' => 'Test User', 'email' => 'test@test.com']);
        $user->password = 'password';
        $user->save();

        $listing = Listing::factory()->published()->create();

        $this->actingAs($user)
            ->post(route('inquiries.store', $listing->id), [
                'type' => 'consultation',
                'name' => 'John Doe',
                'phone' => '+2348000000000',
                'email' => 'john@example.com',
                'message' => 'I would like a consultation.',
                'preferred_date' => '2026-12-01',
            ])
            ->assertRedirect(route('listings.show', $listing->slug));

        $this->assertDatabaseHas('inquiries', [
            'listing_id' => $listing->id,
            'type' => 'consultation',
            'name' => 'John Doe',
            'user_id' => $user->id,
        ]);
    }

    public function test_customer_cannot_see_another_customer_inquiries(): void
    {
        $userA = new User(['name' => 'User A', 'email' => 'a@test.com']);
        $userA->password = 'password';
        $userA->save();

        $userB = new User(['name' => 'User B', 'email' => 'b@test.com']);
        $userB->password = 'password';
        $userB->save();

        $listing = Listing::factory()->published()->create();

        // User A submits an inquiry
        $this->actingAs($userA)
            ->post(route('inquiries.store', $listing->id), [
                'type' => 'consultation',
                'name' => 'User A',
                'phone' => '+2348000000005',
                'email' => 'a@example.com',
                'message' => 'Inquiry from user A.',
            ]);

        // User B should NOT see user A's inquiry
        $this->actingAs($userB)
            ->get(route('listings.show', $listing->slug))
            ->assertDontSee('User A');

        // More direct check: User B's inquiry query should not include user A's inquiry
        $bInquiries = Inquiry::where('user_id', $userB->id)->get();
        $this->assertCount(0, $bInquiries);

        // User A's inquiry should only be visible to user A
        $aInquiries = Inquiry::where('user_id', $userA->id)->get();
        $this->assertCount(1, $aInquiries);
    }

    public function test_company_user_cannot_view_another_company_inquiries(): void
    {
        // Create companies first
        $company = \App\Models\Company::firstOrCreate(
            ['slug' => 'company-a'],
            ['name' => 'Company A', 'slug' => 'company-a', 'description' => 'Company A']
        );
        $companyB = \App\Models\Company::firstOrCreate(
            ['slug' => 'company-b'],
            ['name' => 'Company B', 'slug' => 'company-b', 'description' => 'Company B']
        );

        $companyA = new User(['name' => 'Company A', 'email' => 'a@company.com']);
        $companyA->password = 'password';
        $companyA->company_id = $company->id;
        $companyA->save();

        $companyBUser = new User(['name' => 'Company B', 'email' => 'b@company.com']);
        $companyBUser->password = 'password';
        $companyBUser->company_id = $companyB->id;
        $companyBUser->save();

        $listingA = Listing::factory()->published()->create(['company_id' => $company->id]);
        $listingB = Listing::factory()->published()->create(['company_id' => $companyB->id]);

        // Company A submits an inquiry for their listing
        $this->actingAs($companyA)
            ->post(route('inquiries.store', $listingA->id), [
                'type' => 'consultation',
                'name' => 'Company A Rep',
                'phone' => '+2348000000010',
                'email' => 'rep@companya.com',
                'message' => 'Inquiry from company A.',
            ]);

        // Company B should NOT see Company A's inquiry
        $this->actingAs($companyBUser)
            ->get(route('listings.show', $listingA->slug))
            ->assertDontSee('Company A Rep');

        // Company B's inquiry query for their own listing should be empty
        $bInquiries = Inquiry::where('user_id', $companyBUser->id)->get();
        $this->assertCount(0, $bInquiries);

        // Company A's inquiry should only be visible to Company A
        $aInquiries = Inquiry::where('user_id', $companyA->id)->get();
        $this->assertCount(1, $aInquiries);
    }

    public function test_professional_cannot_view_another_professional_inquiries(): void
    {
        // Use Professional factory to create professionals with required fields
        $profA = \App\Models\Professional::factory()->create();
        $profB = \App\Models\Professional::factory()->create();

        $listing = Listing::factory()->published()->create();

        // Create a professional inquiry for profA
        $inquiry = Inquiry::create([
            'listing_id' => $listing->id,
            'type' => 'consultation',
            'name' => 'Prof A',
            'phone' => '+2348000000010',
            'email' => 'pa@test.com',
            'message' => 'Inquiry from prof A.',
            'professional_id' => $profA->id,
            'user_id' => null,
            'status' => 'new',
        ]);

        // Prof B should NOT see prof A's inquiry (use profB's user for actingAs)
        $this->actingAs($profB->user)
            ->get(route('listings.show', $listing->slug))
            ->assertDontSee('Prof A');

        // Direct check: prof B's inquiries query
        $profBInquiries = Inquiry::where('professional_id', $profB->id)->get();
        $this->assertCount(0, $profBInquiries);

        // Prof A's inquiry should only be visible to prof A
        $profAInquiries = Inquiry::where('professional_id', $profA->id)->get();
        $this->assertCount(1, $profAInquiries);
    }

    public function test_customer_can_view_own_inquiry_but_cannot_update_status(): void
    {
        $user = new User(['name' => 'Customer', 'email' => 'c@test.com']);
        $user->password = 'password';
        $user->save();

        $listing = Listing::factory()->published()->create();

        $this->actingAs($user)
            ->post(route('inquiries.store', $listing->id), [
                'type' => 'consultation',
                'name' => 'Customer',
                'phone' => '+2348000000011',
                'email' => 'c@test.com',
                'message' => 'Customer inquiry.',
            ]);

        // Customer can view their own inquiry
        $this->actingAs($user)
            ->get(route('listings.show', $listing->slug))
            ->assertSee('Customer');

        // Customer cannot update the inquiry status (only super_admin can)
        $inquiry = Inquiry::where('user_id', $user->id)->first();
        $this->actingAs($user)
            ->patch(route('inquiries.update', $inquiry->id), [
                'status' => 'resolved',
            ])
            ->assertStatus(403); // Forbidden
    }

    public function test_verifier_denied_from_viewing_inquiries(): void
    {
        $verifier = new User(['name' => 'Verifier', 'email' => 'v@test.com']);
        $verifier->password = 'password';
        $verifier->save();

        $listing = Listing::factory()->published()->create();

        $this->actingAs($verifier)
            ->get(route('listings.show', $listing->slug))
            ->assertSee('Verifiers cannot view inquiries on this page');
    }

    public function test_posted_user_id_is_ignored(): void
    {
        $user = new User(['name' => 'Test User', 'email' => 'test@test.com']);
        $user->password = 'password';
        $user->save();

        $listing = Listing::factory()->published()->create();

        // Post with user_id in the form data - should be ignored
        $this->actingAs($user)
            ->post(route('inquiries.store', $listing->id), [
                'type' => 'consultation',
                'name' => 'John Doe',
                'phone' => '+2348000000000',
                'email' => 'john@example.com',
                'message' => 'I would like a consultation.',
                'preferred_date' => '2026-12-01',
                'user_id' => 999, // This should be ignored
            ])
            ->assertRedirect(route('listings.show', $listing->slug));

        // The inquiry should have user_id set to the authenticated user's id, not 999
        $this->assertDatabaseHas('inquiries', [
            'listing_id' => $listing->id,
            'type' => 'consultation',
            'name' => 'John Doe',
            'user_id' => $user->id, // Should be the authenticated user's id, not 999
        ]);
    }

    public function test_inquiry_requires_a_message(): void
    {
        $listing = Listing::factory()->published()->create();

        $this->from(route('listings.show', $listing->slug))
            ->post(route('inquiries.store', $listing->id), [
                'type' => 'consultation',
                'name' => 'John Doe',
                'phone' => '+2348000000000',
                'email' => 'john@example.com',
                'message' => '',
            ])
            ->assertSessionHasErrors('message');
    }

    public function test_inquiry_is_not_created_when_validation_fails(): void
    {
        $listing = Listing::factory()->published()->create();

        $this->post(route('inquiries.store', $listing->id), [
            'type' => 'consultation',
            'name' => 'John Doe',
            'phone' => '+2348000000000',
            'email' => 'john@example.com',
            'message' => '',
        ]);

        $this->assertDatabaseCount('inquiries', 0);
    }
}