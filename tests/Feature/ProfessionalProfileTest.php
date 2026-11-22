<?php

namespace Tests\Feature;

use App\Enums\ProfessionalType;
use App\Models\Company;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_lists_only_verified_professionals(): void
    {
        $this->professional('Ada Okafor', ProfessionalType::Lawyer);
        $this->professional('Tunde Bello', ProfessionalType::Architect);
        $this->professional('Ngozi Eze', ProfessionalType::Engineer, verified: false);

        $this->get(route('professionals.index'))
            ->assertOk()
            ->assertSee('Ada Okafor')
            ->assertSee('Tunde Bello')
            ->assertSee('Verified')
            ->assertDontSee('Ngozi Eze');
    }

    public function test_directory_filters_by_professional_type(): void
    {
        $this->professional('Ada Okafor', ProfessionalType::Lawyer);
        $this->professional('Tunde Bello', ProfessionalType::Architect);

        $this->get(route('professionals.index', ['type' => 'lawyer']))
            ->assertOk()
            ->assertSee('Ada Okafor')
            ->assertDontSee('Tunde Bello');
    }

    public function test_unknown_professional_type_filter_returns_everyone_verified(): void
    {
        $this->professional('Ada Okafor', ProfessionalType::Lawyer);
        $this->professional('Tunde Bello', ProfessionalType::Architect);

        $this->get(route('professionals.index', ['type' => 'astronaut']))
            ->assertOk()
            ->assertSee('Ada Okafor')
            ->assertSee('Tunde Bello');
    }

    public function test_unverified_professional_profile_is_not_found(): void
    {
        $professional = $this->professional('Ngozi Eze', ProfessionalType::Engineer, verified: false);

        $this->get(route('professionals.show', $professional->slug))->assertNotFound();
    }

    public function test_professional_urls_never_expose_numeric_ids(): void
    {
        $professional = $this->professional('Ada Okafor', ProfessionalType::Lawyer);

        $this->get('/professionals/'.$professional->id)->assertNotFound();
    }

    public function test_profile_shows_verified_details_and_the_consultation_button(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Realty', 'slug' => 'acme-realty']);

        $professional = $this->professional('Ada Okafor', ProfessionalType::Lawyer, attributes: [
            'company_id' => $company->id,
            'registration_body' => 'Nigerian Bar Association',
            'registration_number' => 'NBA/ABJ/2011/0042',
            'bio' => 'Property and construction lawyer in Abuja.',
        ]);

        $this->get(route('professionals.show', $professional->slug))
            ->assertOk()
            ->assertSee('Ada Okafor')
            ->assertSee('Lawyer')
            ->assertSee('Verified credentials')
            ->assertSee('Nigerian Bar Association')
            ->assertSee('NBA/ABJ/2011/0042')
            ->assertSee('Property and construction lawyer in Abuja.')
            ->assertSee('Acme Realty')
            ->assertSee(route('companies.show', 'acme-realty'), false)
            ->assertSee('Request consultation')
            ->assertSee(route('professionals.inquiries.create', $professional->slug), false)
            ->assertSee('min-h-[44px]', false);
    }

    public function test_profile_never_renders_the_professionals_private_contact_details(): void
    {
        $professional = $this->professional('Ada Okafor', ProfessionalType::Lawyer);

        $this->get(route('professionals.show', $professional->slug))
            ->assertOk()
            ->assertDontSee($professional->user->email)
            ->assertDontSee('mailto:', false)
            ->assertDontSee('tel:', false);
    }

    public function test_company_profile_lists_only_its_verified_professionals(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Realty', 'slug' => 'acme-realty']);

        $verified = $this->professional('Ada Okafor', ProfessionalType::Lawyer, attributes: [
            'company_id' => $company->id,
        ]);

        $this->professional('Tunde Bello', ProfessionalType::Architect, attributes: [
            'company_id' => $company->id,
        ]);

        $unverified = $this->professional('Ngozi Eze', ProfessionalType::Engineer, verified: false, attributes: [
            'company_id' => $company->id,
        ]);

        $this->get(route('companies.show', $company->slug))
            ->assertOk()
            ->assertSee('Verified professionals')
            ->assertSee('Ada Okafor')
            ->assertSee('Tunde Bello')
            ->assertSee(route('professionals.show', $verified->slug), false)
            ->assertDontSee($unverified->user->name);
    }

    public function test_public_navigation_links_to_partners(): void
    {
        $this->get(route('listings.index'))
            ->assertOk()
            ->assertSee('Partners')
            ->assertSee(route('professionals.index'), false);
    }

    public function test_demo_seeder_creates_verified_and_unverified_professionals(): void
    {
        $this->seed();

        $companyId = Company::where('slug', 'acme-realty')->value('id');

        $this->assertSame(3, Professional::count());
        $this->assertSame(2, Professional::whereNotNull('credentials_verified_at')->count());

        $this->assertDatabaseHas('professionals', [
            'slug' => 'ada-okafor',
            'type' => ProfessionalType::Lawyer->value,
            'company_id' => $companyId,
        ]);

        $this->assertDatabaseHas('professionals', [
            'slug' => 'tunde-bello',
            'type' => ProfessionalType::Architect->value,
            'company_id' => null,
        ]);

        $this->assertDatabaseHas('professionals', [
            'slug' => 'ngozi-eze',
            'type' => ProfessionalType::Engineer->value,
            'credentials_verified_at' => null,
        ]);

        $this->assertSame(3, Professional::query()->distinct()->count('slug'));

        $this->get(route('professionals.index'))
            ->assertOk()
            ->assertSee('Ada Okafor')
            ->assertSee('Tunde Bello')
            ->assertDontSee('Ngozi Eze');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function professional(
        string $name,
        ProfessionalType $type,
        bool $verified = true,
        array $attributes = [],
    ): Professional {
        $user = User::factory()->professional()->create(['name' => $name]);

        $factory = Professional::factory()->state($attributes + [
            'user_id' => $user->id,
            'type' => $type->value,
        ]);

        if ($verified) {
            $factory = $factory->verified();
        }

        return $factory->create();
    }
}
