<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_layouts_include_the_head_theme_script(): void
    {
        foreach (glob(resource_path('views/layouts/*.blade.php')) as $layout) {
            $this->assertStringContainsString(
                '<x-theme-script',
                (string) file_get_contents($layout),
                "Layout {$layout} must include the head theme script.",
            );
        }

        foreach (['/', '/login'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-theme-script', false)
                ->assertSee("var key = 'theme';", false)
                ->assertSee('livewire:navigated', false);
        }

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('data-theme-script', false)
            ->assertSee("var key = 'theme';", false);
    }

    public function test_theme_control_renders_three_options_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Light')
            ->assertSee('Dark')
            ->assertSee('System')
            ->assertSee('aria-label="Theme"', false)
            ->assertSee('aria-pressed', false);
    }

    public function test_theme_control_renders_in_settings_and_the_avatar_menu(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('aria-label="Theme"', false)
            ->assertSee('Light')
            ->assertSee('Dark')
            ->assertSee('System');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee(route('settings'), false)
            ->assertSee('aria-label="Theme"', false)
            ->assertSee('Light')
            ->assertSee('Dark')
            ->assertSee('System');
    }

    public function test_theme_tokens_and_dark_variants_are_defined(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('@custom-variant dark (&:where(.dark, .dark *));', $css);
        $this->assertStringContainsString('--color-surface:', $css);
        $this->assertStringContainsString('--color-surface-muted:', $css);
        $this->assertStringContainsString('--color-text:', $css);
        $this->assertStringContainsString('--color-text-muted:', $css);
        $this->assertStringContainsString('--color-border:', $css);
        $this->assertStringContainsString('--color-brand:', $css);
        $this->assertStringContainsString('.dark {', $css);

        foreach ([
            'components/badge.blade.php',
            'components/alert.blade.php',
            'components/input-error.blade.php',
            'components/dropdown.blade.php',
        ] as $view) {
            $path = resource_path('views/'.$view);
            $this->assertStringContainsString('dark:', (string) file_get_contents($path), "{$view} must have a dark variant.");
        }

        foreach ([
            'components/input.blade.php',
            'components/text-input.blade.php',
            'components/card.blade.php',
            'components/empty-state.blade.php',
        ] as $view) {
            $path = resource_path('views/'.$view);
            $this->assertStringContainsString('bg-surface', (string) file_get_contents($path), "{$view} must use semantic surfaces.");
        }
    }

    public function test_views_do_not_use_hardcoded_white_or_gray_utility_classes(): void
    {
        foreach (glob(resource_path('views/*.blade.php')) as $view) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:bg-white|text-gray-[0-9]+|border-gray-[0-9]+)\b/',
                (string) file_get_contents($view),
                "{$view} must use semantic colour tokens.",
            );
        }
    }
}
