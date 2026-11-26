<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_renders_the_uppercase_initial_from_the_users_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $html = $this->renderAvatar($user);

        $this->assertStringContainsString('J', $html);
        $this->assertStringContainsString('aria-label="John Doe"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function test_avatar_falls_back_to_the_email_initial_when_the_name_is_empty(): void
    {
        $user = User::factory()->create(['name' => '', 'email' => 'john.doe@busyhub.test']);

        $html = $this->renderAvatar($user);

        $this->assertStringContainsString('J', $html);
        $this->assertStringContainsString('aria-label="john.doe@busyhub.test"', $html);
    }

    public function test_avatar_uses_the_same_color_for_the_same_user_across_renders(): void
    {
        $user = User::factory()->create();

        $this->assertSame(
            $this->backgroundColor($this->renderAvatar($user)),
            $this->backgroundColor($this->renderAvatar($user)),
        );
    }

    public function test_avatar_uses_a_deterministic_color_from_the_shared_palette_per_user(): void
    {
        $palette = config('avatar.palette');
        $colors = [];

        foreach (User::factory()->count(50)->create() as $user) {
            $color = $this->backgroundColor($this->renderAvatar($user));

            $this->assertContains($color, $palette);
            $colors[$color] = true;
        }

        $this->assertGreaterThan(
            1,
            count($colors),
            'Avatars for 50 users must use more than one colour from the palette.',
        );
    }

    public function test_avatar_palette_colors_keep_a_minimum_contrast_of_4_5_to_white(): void
    {
        $palette = config('avatar.palette');

        $this->assertNotEmpty($palette);

        foreach ($palette as $color) {
            $this->assertGreaterThanOrEqual(
                4.5,
                $this->contrastRatioAgainstWhite($color),
                "Avatar background colour {$color} must keep a contrast ratio of at least 4.5:1 against white.",
            );
        }
    }

    private function renderAvatar(User $user): string
    {
        /** @var string $html */
        $html = view('components.avatar', ['user' => $user, 'size' => 'md'])->render();

        return $html;
    }

    private function backgroundColor(string $html): string
    {
        preg_match('/background-color:\s*([^;]+);/', $html, $matches);

        return trim($matches[1] ?? '');
    }

    private function contrastRatioAgainstWhite(string $hex): float
    {
        return 1.05 / ($this->relativeLuminance($hex) + 0.05);
    }

    private function relativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');

        $channels = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];

        $linear = array_map(
            function ($channel) {
                $value = $channel / 255;

                return $value <= 0.03928 ? $value / 12.92 : pow(($value + 0.055) / 1.055, 2.4);
            },
            $channels,
        );

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }
}
