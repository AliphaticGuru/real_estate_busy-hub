<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_renders_at_the_bottom_of_the_page_with_the_copyright_last(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString('<footer', $content);
        $this->assertStringContainsString('min-h-screen flex flex-col', $content);
        $this->assertStringContainsString('mt-auto', $content);
        $this->assertStringContainsString('All rights reserved', $content);

        $footerPosition = strpos($content, '<footer');
        $copyrightPosition = strpos($content, 'All rights reserved');

        $this->assertGreaterThan($footerPosition, $copyrightPosition);
    }

    public function test_footer_is_shown_on_short_pages_and_stacks_on_small_screens(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('<footer', false)
            ->assertSee('min-h-screen', false)
            ->assertSee('mt-auto', false)
            ->assertSee('flex flex-wrap', false);

        $this->get('/register')
            ->assertOk()
            ->assertSee('<footer', false)
            ->assertSee('mt-auto', false);
    }

    public function test_footer_links_to_the_new_public_info_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('about'), false)
            ->assertSee(route('mission'), false)
            ->assertSee(route('vision'), false)
            ->assertSee(route('services'), false)
            ->assertSee(route('contact'), false)
            ->assertSee(route('feedback'), false);
    }
}
