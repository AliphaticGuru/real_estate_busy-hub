<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_login_and_register_in_the_unified_navigation_and_no_app_links(): void
    {
        $this->get('/listings')
            ->assertOk()
            ->assertSee('aria-label="Primary navigation"', false)
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false)
            ->assertDontSee(route('dashboard'), false)
            ->assertDontSee(route('inquiries.index'), false);
    }

    public function test_authenticated_verified_customer_sees_dashboard_and_inquiries_in_the_unified_navigation(): void
    {
        $user = User::factory()->forRole(RoleName::Customer)->create();

        $this->actingAs($user);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('How verification works', false)
            ->assertSee('Inquiries')
            ->assertSee($user->email);
    }

    public function test_unverified_users_do_not_get_the_dashboard_link_in_the_unified_navigation(): void
    {
        $user = User::factory()->forRole(RoleName::Customer)->unverified()->create();

        $this->actingAs($user);

        $this->get('/listings')
            ->assertOk()
            ->assertDontSee('Dashboard');
    }

    public function test_verifiers_do_not_get_the_inquiries_link_in_the_unified_navigation(): void
    {
        $user = User::factory()->forRole(RoleName::Verifier)->create();

        $this->actingAs($user);

        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Inquiries');
    }

    public function test_the_unified_navigation_renders_on_public_auth_and_app_layouts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('How verification works', false);

        $this->get('/login')
            ->assertOk()
            ->assertSee('How verification works', false);

        $user = User::factory()->forRole(RoleName::Customer)->create();

        $this->actingAs($user);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('How verification works', false);
    }
}
