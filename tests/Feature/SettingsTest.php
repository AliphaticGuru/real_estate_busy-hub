<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_is_displayed_for_authenticated_verified_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee('Appearance')
            ->assertSee('Notifications')
            ->assertSee('Account')
            ->assertSee('Email me when I receive an inquiry');
    }

    public function test_guests_cannot_open_settings(): void
    {
        $this->get(route('settings'))->assertRedirect(route('login'));
    }

    public function test_only_the_authenticated_users_preference_is_updated(): void
    {
        $user = User::factory()->create(['notify_inquiries' => true]);
        $other = User::factory()->create(['notify_inquiries' => true]);

        $this->actingAs($user);

        Volt::test('settings.update-notifications')
            ->set('notify_inquiries', false)
            ->call('updateNotifications')
            ->assertHasNoErrors();

        $this->assertFalse($user->refresh()->notify_inquiries);
        $this->assertTrue($other->refresh()->notify_inquiries);
    }

    public function test_invalid_notification_value_is_rejected(): void
    {
        $user = User::factory()->create(['notify_inquiries' => true]);

        $this->actingAs($user);

        Volt::test('settings.update-notifications')
            ->set('notify_inquiries', 'maybe')
            ->call('updateNotifications')
            ->assertHasErrors('notify_inquiries');

        $this->assertTrue($user->refresh()->notify_inquiries);
    }
}
