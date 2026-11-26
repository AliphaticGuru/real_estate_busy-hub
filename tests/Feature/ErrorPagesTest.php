<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_page_renders_the_404_error_view(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Find verified real estate');
    }

    public function test_forbidden_page_renders_the_403_error_view(): void
    {
        $user = User::factory()->verifier()->create();

        $this->actingAs($user)
            ->get(route('inquiries.index'))
            ->assertForbidden()
            ->assertSee('Access denied');
    }

    public function test_expired_page_renders_the_419_error_view(): void
    {
        $this->app['router']->post('/test/error/419', fn () => abort(419));

        $this->post('/test/error/419')
            ->assertStatus(419)
            ->assertSee('Page expired');
    }

    public function test_server_error_page_renders_the_500_error_view(): void
    {
        $this->app['router']->get('/test/error/500', fn () => abort(500));

        $this->get('/test/error/500')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertDontSee('Find verified real estate');
    }

    public function test_service_unavailable_page_renders_the_503_error_view(): void
    {
        $this->app['router']->get('/test/error/503', fn () => abort(503));

        $this->get('/test/error/503')
            ->assertStatus(503)
            ->assertSee('Service unavailable')
            ->assertDontSee('Find verified real estate');
    }

    public function test_server_error_pages_follow_the_system_colour_scheme(): void
    {
        foreach ([500, 503] as $status) {
            $this->app['router']->get("/test/error/{$status}", fn () => abort($status));

            $this->get("/test/error/{$status}")
                ->assertStatus($status)
                ->assertSee('prefers-color-scheme: dark', false)
                ->assertSee('color-scheme: dark', false)
                ->assertDontSee('#4f46e5', false);
        }
    }

    public function test_client_error_pages_render_within_the_public_layout(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Find verified real estate');
    }

    public function test_server_error_pages_render_standalone_html_without_the_public_layout(): void
    {
        $this->app['router']->get('/test/error/500', fn () => abort(500));
        $this->app['router']->get('/test/error/503', fn () => abort(503));

        $this->get('/test/error/500')
            ->assertStatus(500)
            ->assertDontSee('Find verified real estate');

        $this->get('/test/error/503')
            ->assertStatus(503)
            ->assertDontSee('Find verified real estate');
    }

    public function test_authenticated_pages_render_the_noindex_meta(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_guest_pages_render_the_noindex_meta(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }
}
