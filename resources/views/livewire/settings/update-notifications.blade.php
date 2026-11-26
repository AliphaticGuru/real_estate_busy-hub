<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public $notify_inquiries = true;

    /**
     * Load the current preference for the authenticated user.
     */
    public function mount(): void
    {
        $this->notify_inquiries = Auth::user()->notify_inquiries;
    }

    /**
     * Persist only the authenticated user's preference.
     */
    public function updateNotifications(): void
    {
        $validated = $this->validate([
            'notify_inquiries' => ['boolean'],
        ]);

        Auth::user()->update([
            'notify_inquiries' => $validated['notify_inquiries'],
        ]);

        $this->dispatch('notifications-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-semibold text-text">{{ __('Notifications') }}</h2>
        <p class="mt-1 text-sm text-text-muted">
            {{ __('Control which emails you receive from the hub.') }}
        </p>
    </header>

    <form wire:submit="updateNotifications" class="mt-6 space-y-6">
        <div>
            <label class="flex items-start gap-3 text-sm text-text">
                <input
                    type="checkbox"
                    wire:model="notify_inquiries"
                    class="mt-0.5 h-5 w-5 rounded border-border text-brand shadow-sm focus:ring-brand"
                />
                <span>{{ __('Email me when I receive an inquiry') }}</span>
            </label>

            <x-input-error class="mt-2" :messages="$errors->get('notify_inquiries')" />
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-button>{{ __('Save') }}</x-button>

            <x-action-message class="me-3" on="notifications-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
