<?php

namespace Tests\Feature;

use App\Enums\CompanyStatus;
use App\Models\Company;
use App\Models\Listing;
use App\Models\Professional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_the_sitemap_xml_document(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<urlset', false);
    }

    public function test_sitemap_contains_static_public_pages_and_published_verified_content(): void
    {
        $listing = Listing::factory()->published()->verified()->create();
        $draftListing = Listing::factory()->create();

        $company = Company::factory()->create(['status' => CompanyStatus::Active->value]);
        $pendingCompany = Company::factory()->create();

        $verifiedProfessional = Professional::factory()->verified()->create();
        $unverifiedProfessional = Professional::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();

        foreach ([
            url('/'),
            url('/listings'),
            url('/fees'),
            url('/verification'),
            url('/blog'),
            url('/professionals'),
            url('/blog/grand-villa-gwarinpa-buying-guide'),
            url('/listings/'.$listing->slug),
            url('/companies/'.$company->slug),
            url('/professionals/'.$verifiedProfessional->slug),
        ] as $url) {
            $response->assertSee($url, false);
        }

        $response->assertDontSee($draftListing->slug);
        $response->assertDontSee($pendingCompany->slug);
        $response->assertDontSee($unverifiedProfessional->slug);
    }
}
