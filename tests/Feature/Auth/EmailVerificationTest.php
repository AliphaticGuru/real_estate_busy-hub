<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Volt;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response
            ->assertSeeVolt('pages.auth.verify-email')
            ->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_check_verified_does_not_redirect_while_the_user_is_unverified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        Volt::test('pages.auth.verify-email')
            ->call('checkVerified')
            ->assertNoRedirect()
            ->assertSet('verificationStatus', 'Waiting for confirmation...');
    }

    public function test_check_verified_redirects_when_verification_is_confirmed_in_the_database(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $component = Volt::test('pages.auth.verify-email')
            ->call('checkVerified')
            ->assertNoRedirect();

        // Simulate the signed link being opened on another device.
        $user->forceFill(['email_verified_at' => now()])->save();

        $component->call('checkVerified')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_already_verified_users_are_redirected_from_the_notice_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('pages.auth.verify-email')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_verified_user_visiting_the_notice_page_is_redirected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/verify-email')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_verification_page_renders_the_polling_marker_and_status_region(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertOk()
            ->assertSee('wire:poll.5s.visible="checkVerified"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('Waiting for confirmation...');
    }

    public function test_resend_still_sends_a_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        Volt::test('pages.auth.verify-email')
            ->call('sendVerification')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_link_throttling_is_unchanged(): void
    {
        $route = Route::getRoutes()->getByName('verification.verify');

        $this->assertNotNull($route);
        $this->assertContains('throttle:6,1', $route->gatherMiddleware());
    }
}
