<?php

namespace Tests\Feature;

use Tests\TestCase;

class StaticContentTest extends TestCase
{
    public function test_guests_can_view_the_fees_page(): void
    {
        $this->get(route('fees'))
            ->assertOk()
            ->assertSee('Fees')
            ->assertSee(config('app.name'));
    }

    public function test_guests_can_view_the_verification_process_page(): void
    {
        $this->get(route('verification'))
            ->assertOk()
            ->assertSee('How verification works')
            ->assertSee(config('app.name'));
    }

    public function test_guests_can_view_the_blog_index(): void
    {
        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Blog')
            ->assertSee(config('app.name'));
    }

    public function test_guests_can_view_a_blog_post(): void
    {
        $this->get(route('blog.show', 'grand-villa-gwarinpa-buying-guide'))
            ->assertOk()
            ->assertSee('Buying a Grand Villa in Gwarinpa')
            ->assertSee(config('app.name'));
    }

    public function test_guests_get_a_404_for_an_unknown_blog_post(): void
    {
        $this->get(route('blog.show', 'not-a-real-post'))->assertNotFound();
    }

    public function test_public_footer_shows_the_whatsapp_number_as_digits_only(): void
    {
        config()->set('services.whatsapp.number', '+234 800 000 0000');

        $this->get(route('fees'))
            ->assertOk()
            ->assertSee('https://wa.me/2348000000000', false);
    }

    public function test_public_footer_hides_whatsapp_when_the_number_is_empty(): void
    {
        config()->set('services.whatsapp.number', null);

        $this->get(route('fees'))
            ->assertOk()
            ->assertDontSee('wa.me', false);
    }
}
