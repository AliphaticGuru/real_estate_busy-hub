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

    public function test_guests_can_view_the_about_page(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('About')
            ->assertSee(config('app.name'))
            ->assertSee('Draft: pending review');
    }

    public function test_guests_can_view_the_mission_page(): void
    {
        $this->get(route('mission'))
            ->assertOk()
            ->assertSee('Mission')
            ->assertSee('safe and transparent')
            ->assertSee(config('app.name'))
            ->assertSee('Draft: pending review');
    }

    public function test_guests_can_view_the_vision_page(): void
    {
        $this->get(route('vision'))
            ->assertOk()
            ->assertSee('Vision')
            ->assertSee('To be written by the founders')
            ->assertSee(config('app.name'))
            ->assertSee('Draft: pending review');
    }

    public function test_guests_can_view_the_services_page(): void
    {
        $this->get(route('services'))
            ->assertOk()
            ->assertSee('Services')
            ->assertSee('Coming soon, Phase 2')
            ->assertSee(config('app.name'))
            ->assertSee('Draft: pending review');
    }

    public function test_guests_can_view_the_contact_page(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Contact')
            ->assertSee(config('app.name'))
            ->assertSee('Draft: pending review')
            ->assertSee('Contact details coming soon');
    }

    public function test_guests_can_view_the_feedback_page(): void
    {
        $this->get(route('feedback'))
            ->assertOk()
            ->assertSee('Feedback')
            ->assertSee(config('app.name'))
            ->assertSee('Draft: pending review');
    }

    public function test_contact_page_links_to_whatsapp_with_a_digits_only_number(): void
    {
        config(['services.whatsapp.number' => '+234 800 000 0001']);

        $message = __('Hello Verifyhaus Afrika, I would like to know more about a verified listing or professional.');

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('https://wa.me/2348000000001?text='.rawurlencode($message), false)
            ->assertDontSee('Contact details coming soon');
    }

    public function test_feedback_page_links_to_whatsapp_with_a_digits_only_number(): void
    {
        config(['services.whatsapp.number' => '+234 800 000 0001']);

        $message = __('Hello Verifyhaus Afrika, I would like to share feedback.');

        $this->get(route('feedback'))
            ->assertOk()
            ->assertSee('https://wa.me/2348000000001?text='.rawurlencode($message), false)
            ->assertDontSee('Contact details coming soon');
    }

    public function test_contact_and_feedback_hide_the_whatsapp_link_when_no_number_is_set(): void
    {
        config([
            'services.whatsapp.number' => null,
            'services.contact.email' => null,
        ]);

        foreach ([route('contact'), route('feedback')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('wa.me', false)
                ->assertSee('Contact details coming soon');
        }
    }
}
