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

    public function test_theme_button_renders_once_with_three_cycle_states_for_guests(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('data-theme-button', false)
            ->assertSee('Theme: System.', false)
            ->assertSee("next: { light: 'dark', dark: 'system', system: 'light' }", false);

        $this->assertSame(
            1,
            substr_count((string) $response->getContent(), 'data-theme-button'),
            'The theme button belongs in the nav bar only, not the footer or mobile menu.',
        );
    }

    public function test_theme_button_renders_in_settings_and_the_nav_bar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('settings'))
            ->assertOk()
            ->assertSee('data-theme-button', false)
            ->assertSee('Appearance');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee(route('settings'), false)
            ->assertSee('data-theme-button', false);
    }

    public function test_theme_script_guards_every_local_storage_access(): void
    {
        $script = (string) file_get_contents(resource_path('views/components/theme-script.blade.php'));

        $this->assertSame(2, substr_count($script, 'try {'), 'Both the read and the write must be guarded.');
        $this->assertStringContainsString('window.localStorage.getItem(key)', $script);
        $this->assertStringContainsString('window.localStorage.setItem(key, next)', $script);
        $this->assertStringContainsString("return 'system';", $script);

        foreach (['/', '/login'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('try {', false)
                ->assertSee('catch (error)', false)
                ->assertSee('window.localStorage', false);
        }

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('catch (error)', false);
    }

    public function test_settings_profile_and_theme_controls_have_brand_focus_rings(): void
    {
        foreach ([
            'components/input.blade.php',
            'components/button.blade.php',
            'components/theme-toggle.blade.php',
            'livewire/settings/update-notifications.blade.php',
            'livewire/profile/update-profile-information-form.blade.php',
        ] as $view) {
            $this->assertStringContainsString(
                'focus:ring-brand',
                (string) file_get_contents(resource_path('views/'.$view)),
                "{$view} must use a brand-coloured focus ring.",
            );
        }
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
