<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Professional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_renders(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy policy')
            ->assertSee('Draft');
    }

    public function test_terms_of_service_page_renders(): void
    {
        $this->get('/terms')
            ->assertOk()
            ->assertSee('Terms of service')
            ->assertSee('Draft');
    }

    public function test_complaints_page_renders(): void
    {
        $this->get('/complaints')
            ->assertOk()
            ->assertSee('Complaints')
            ->assertSee('Draft');
    }

    public function test_legal_pages_are_linked_in_the_footer(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/privacy')
            ->assertSee('/terms')
            ->assertSee('/complaints');
    }

    public function test_listing_inquiry_form_contains_the_consent_line(): void
    {
        $listing = Listing::factory()->published()->create();

        $this->get(route('inquiries.create', $listing->slug))
            ->assertOk()
            ->assertSee('privacy policy');
    }

    public function test_professional_inquiry_form_contains_the_consent_line(): void
    {
        $professional = Professional::factory()->verified()->create();

        $this->get(route('professionals.inquiries.create', $professional->slug))
            ->assertOk()
            ->assertSee('privacy policy');
    }
}
