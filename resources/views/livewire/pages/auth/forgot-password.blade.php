<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="space-y-6">
    <x-page-header
        :title="__('Forgot password')"
        :subtitle="__('Enter your email and we will send you a reset link.')"
    />

    <!-- Session Status -->
    @if (session('status'))
        <x-alert variant="success">{{ session('status') }}</x-alert>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input
                wire:model="email"
                id="email"
                name="email"
                type="email"
                :label="__('Email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-button type="submit" class="w-full">
            {{ __('Email Password Reset Link') }}
        </x-button>
    </form>

    <p class="text-center text-sm text-gray-600">
        <a href="{{ route('login') }}" wire:navigate class="font-medium text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ __('Back to log in') }}
        </a>
    </p>
</div>
