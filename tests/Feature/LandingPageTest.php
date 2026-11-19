<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_with_brand_heading_and_calls_to_action(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee(config('app.name'))
            ->assertSee('Buy, rent and build in Nigeria without fear of being scammed.')
            ->assertSee('href="'.route('listings.index').'"', false)
            ->assertSee('href="'.route('verification').'"', false)
            ->assertSee('property="og:site_name"', false)
            ->assertSee('name="description"', false);

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_featured_section_only_shows_verified_published_listings(): void
    {
        $company = Company::factory()->create(['name' => 'Verified Homes Ltd']);

        $featured = Listing::factory()->for($company)->published()->verified()->create([
            'title' => 'Featured Verified Villa',
        ]);

        ListingImage::factory()->for($featured)->create([
            'path' => 'listings/featured-villa.jpg',
        ]);

        Listing::factory()->for($company)->published()->create([
            'title' => 'Unverified Published Duplex',
        ]);

        Listing::factory()->for($company)->verified()->create([
            'title' => 'Draft Verified Land',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Featured Verified Villa')
            ->assertSee('Verified Homes Ltd')
            ->assertSee('listings/featured-villa.jpg', false)
            ->assertDontSee('Unverified Published Duplex')
            ->assertDontSee('Draft Verified Land');
    }

    public function test_featured_section_shows_an_empty_state_without_verified_listings(): void
    {
        Listing::factory()->published()->create([
            'title' => 'Pending Review Apartment',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('No verified listings yet')
            ->assertDontSee('Pending Review Apartment');
    }
}
