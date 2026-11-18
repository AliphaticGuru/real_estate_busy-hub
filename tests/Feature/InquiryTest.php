<?php

namespace Tests\Feature;

use App\Models\Listing;
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
