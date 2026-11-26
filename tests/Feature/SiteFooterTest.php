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

        $footer = $this->footerHtml($content);

        $footerPosition = strpos($content, '<footer');
        $copyrightPosition = strpos($content, 'All rights reserved');

        $this->assertGreaterThan($footerPosition, $copyrightPosition);

        // After the copyright line only closing tags remain before </footer>.
        $tail = substr($footer, strpos($footer, 'All rights reserved.') + strlen('All rights reserved.'));

        $this->assertMatchesRegularExpression('/^\s*(?:<\/p>\s*)?<\/footer>\s*$/', $tail);
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

    public function test_footer_groups_info_links_under_company_and_support(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('aria-label="Company"', false)
            ->assertSee('aria-label="Support"', false);

        $footer = $this->footerHtml($response->getContent());

        // The links inside each group are stacked (block) below xl and inline from xl up.
        $this->assertSame(
            2,
            substr_count($footer, 'flex flex-col items-center gap-2 xl:flex-row xl:gap-4'),
        );

        // The Company group renders before the Support group.
        $companyPosition = strpos($footer, 'aria-label="Company"');
        $supportPosition = strpos($footer, 'aria-label="Support"');

        $this->assertLessThan($supportPosition, $companyPosition);

        $company = substr($footer, $companyPosition, $supportPosition - $companyPosition);
        $support = substr($footer, $supportPosition);

        $this->assertLinksInOrder($company, ['Mission', 'Vision', 'Blog', 'Services', 'Partners']);
        $this->assertLinksInOrder($support, ['About', 'Contact', 'Feedback']);
    }

    /**
     * @param  list<string>  $labels
     */
    private function assertLinksInOrder(string $group, array $labels): void
    {
        $previous = -1;

        foreach ($labels as $label) {
            $position = strpos($group, '>'.$label.'</a>');

            $this->assertNotFalse($position, "The {$label} link is missing from its footer group.");
            $this->assertGreaterThan($previous, $position, "The {$label} link is out of order.");
            $previous = $position;
        }
    }

    private function footerHtml(string $content): string
    {
        $start = strpos($content, '<footer');
        $end = strpos($content, '</footer>', $start);

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($content, $start, $end - $start + strlen('</footer>'));
    }
}
