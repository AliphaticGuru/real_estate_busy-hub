<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSee(config('app.name'))
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);

        $this->assertAuthenticated();
    }

    public function test_weak_passwords_are_rejected_at_registration(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'weak@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertHasErrors('password');
    }

    public function test_new_users_are_registered_with_the_customer_role(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'customer@example.com')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->call('register');

        $user = User::where('email', 'customer@example.com')->firstOrFail();

        $this->assertTrue($user->isCustomer());
        $this->assertSame(RoleName::Customer, $user->role->name);
    }

    public function test_newly_registered_user_is_redirected_to_the_verification_notice(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'newcustomer@example.com')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->call('register');

        $user = User::where('email', 'newcustomer@example.com')->firstOrFail();

        $this->assertAuthenticated();
        $this->assertFalse($user->hasVerifiedEmail());

        $this->get('/dashboard')->assertRedirect(route('verification.notice', absolute: false));

        $this->get('/profile')->assertRedirect(route('verification.notice', absolute: false));

        $this->get('/inquiries')->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_newly_registered_user_reaches_the_customer_dashboard_after_verifying(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'verifiedcustomer@example.com')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->call('register');

        $user = User::where('email', 'verifiedcustomer@example.com')->firstOrFail();
        $user->markEmailAsVerified();

        $this->actingAs($user);

        $this->get('/dashboard')->assertOk();

        $this->assertAuthenticated();
    }
}
