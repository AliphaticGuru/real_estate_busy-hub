<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_a_companys_public_profile(): void
    {
        $company = Company::factory()->create([
            'name' => 'Acme Realty',
            'slug' => 'acme-realty',
        ]);

        Listing::factory()->published()->create([
            'company_id' => $company->id,
            'title' => 'Grand Villa in Gwarinpa',
        ]);

        $this->get(route('companies.show', $company->slug))
            ->assertOk()
            ->assertSee('Acme Realty')
            ->assertSee('Grand Villa in Gwarinpa');
    }

    public function test_guests_get_a_404_for_an_unknown_company(): void
    {
        $this->get(route('companies.show', 'does-not-exist'))->assertNotFound();
    }
}
