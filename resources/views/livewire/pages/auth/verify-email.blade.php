<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    private const RESEND_LIMIT = 3;

    private const RESEND_DECAY_SECONDS = 600;

    /**
     * Copy shown while the email address is still unverified.
     */
    public string $verificationStatus = 'Waiting for confirmation...';

    /**
     * Feedback for the most recent resend attempt.
     */
    public string $resendMessage = '';

    /**
     * Seconds left before another resend is allowed (zero when not limited).
     */
    public int $resendRetryAfter = 0;

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
     * Send an email verification notification, limited per user.
     */
    public function sendVerification(): void
    {
        $user = $this->freshUser();

        if ($user?->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        $key = 'verify-email-resend:'.Auth::id();

        if (RateLimiter::tooManyAttempts($key, self::RESEND_LIMIT)) {
            $this->resendRetryAfter = RateLimiter::availableIn($key);
            $this->resendMessage = __('Too many verification emails. Please try again in :seconds seconds.', [
                'seconds' => $this->resendRetryAfter,
            ]);

            return;
        }

        RateLimiter::hit($key, self::RESEND_DECAY_SECONDS);

        $user->sendEmailVerificationNotification();

        $this->resendRetryAfter = 0;
        $this->resendMessage = __('Verification link sent.');
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

    @if ($resendMessage !== '')
        <x-alert :variant="$resendRetryAfter > 0 ? 'warning' : 'success'">
            {{ $resendMessage }}
        </x-alert>
    @endif

    <p aria-live="polite" class="text-sm font-medium text-gray-700">
        {{ $verificationStatus }}
    </p>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-button type="button" wire:click="sendVerification" wire:loading.attr="disabled" class="w-full sm:w-auto">
            {{ __('Resend Verification Email') }}
        </x-button>

        <button type="submit" wire:click="logout" class="inline-flex min-h-[44px] items-center justify-center rounded-md px-3 text-sm font-medium text-gray-600 underline-offset-4 hover:text-gray-900 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ __('Log Out') }}
        </button>
    </div>
</div>
