<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Copy shown while the email address is still unverified.
     */
    public string $verificationStatus = 'Waiting for confirmation...';

    /**
     * Users who are already verified do not need the notice page.
     */
    public function mount(): void
    {
        $this->redirectWhenVerified();
    }

    /**
     * Check the verification state after each poll of the page.
     */
    public function checkVerified(): void
    {
        $this->redirectWhenVerified();
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if ($this->freshUser()?->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        $this->freshUser()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    /**
     * The signed link does the verifying; this only reads the latest state.
     */
    private function redirectWhenVerified(): void
    {
        if (! $this->freshUser()?->hasVerifiedEmail()) {
            return;
        }

        $this->verificationStatus = __('Email verified, redirecting...');

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * The authenticated user re-read from the database.
     */
    private function freshUser()
    {
        return Auth::user()?->fresh();
    }
}; ?>

<div wire:poll.5s.visible="checkVerified" class="space-y-6">
    <x-page-header
        :title="__('Verify your email')"
        :subtitle="__('One more step before you can use your account.')"
    />

    <p class="text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <x-alert variant="success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </x-alert>
    @endif

    <p aria-live="polite" class="text-sm font-medium text-gray-700">
        {{ $verificationStatus }}
    </p>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-button type="button" wire:click="sendVerification" class="w-full sm:w-auto">
            {{ __('Resend Verification Email') }}
        </x-button>

        <button type="submit" wire:click="logout" class="inline-flex min-h-[44px] items-center justify-center rounded-md px-3 text-sm font-medium text-gray-600 underline-offset-4 hover:text-gray-900 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ __('Log Out') }}
        </button>
    </div>
</div>
