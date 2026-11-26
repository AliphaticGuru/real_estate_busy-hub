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
}
