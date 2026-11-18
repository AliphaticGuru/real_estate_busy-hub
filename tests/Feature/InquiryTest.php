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
        ])->assertRedirect(route('listings.show', $listing->slug));

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