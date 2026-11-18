<?php

namespace Tests\Feature;

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_a_published_listing(): void
    {
        $listing = Listing::factory()->published()->create([
            'title' => 'Grand Villa in Gwarinpa',
        ]);

        $this->get(route('listings.show', $listing->slug))
            ->assertOk()
            ->assertSee('Grand Villa in Gwarinpa')
            ->assertSee(config('app.name'))
            ->assertSee('wa.me');
    }

    public function test_guests_cannot_view_a_draft_listing(): void
    {
        $listing = Listing::factory()->create([
            'title' => 'Office Suite for Rent',
        ]);

        $this->get(route('listings.show', $listing->slug))->assertNotFound();
    }

    public function test_listing_index_lists_only_published_listings(): void
    {
        Listing::factory()->published()->create([
            'title' => 'Grand Villa in Gwarinpa',
        ]);

        Listing::factory()->create([
            'title' => 'Office Suite for Rent',
        ]);

        $this->get(route('listings.index'))
            ->assertOk()
            ->assertSee('Grand Villa in Gwarinpa')
            ->assertSee(config('app.name'))
            ->assertDontSee('Office Suite for Rent');
    }

    public function test_listing_index_can_filter_by_property_type(): void
    {
        Listing::factory()->published()->create([
            'title' => 'Villa House for Families',
            'property_type' => 'house',
        ]);

        Listing::factory()->published()->create([
            'title' => 'Luxury Villa',
            'property_type' => 'villa',
        ]);

        $this->get(route('listings.index', ['property_type' => 'house']))
            ->assertOk()
            ->assertSee('Villa House for Families')
            ->assertDontSee('Luxury Villa');
    }

    public function test_listing_index_can_filter_by_city(): void
    {
        Listing::factory()->published()->create([
            'title' => 'Abuja City House',
            'city' => 'Abuja',
        ]);

        Listing::factory()->published()->create([
            'title' => 'Lagos City House',
            'city' => 'Lagos',
        ]);

        $this->get(route('listings.index', ['city' => 'Abuja']))
            ->assertOk()
            ->assertSee('Abuja City House')
            ->assertDontSee('Lagos City House');
    }

    public function test_listing_index_has_mobile_navigation_and_footer_whatsapp(): void
    {
        config()->set('services.whatsapp.number', '+234 800 000 0000');

        $response = $this->get(route('listings.index'))
            ->assertOk()
            ->assertSee('<footer', false)
            ->assertSee('aria-label="Toggle navigation menu"', false)
            ->assertSee('x-data="{ open: false }"', false)
            ->assertSee('hidden sm:flex', false)
            ->assertSee('sm:hidden', false);

        $content = $response->getContent();
        $footerStart = strpos($content, '<footer');

        $this->assertNotFalse($footerStart);
        $this->assertStringContainsString('https://wa.me/2348000000000', substr($content, $footerStart));
    }
}
