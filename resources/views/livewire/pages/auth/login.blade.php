<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <x-page-header
        :title="__('Log in')"
        :subtitle="__('Welcome back. Access your dashboard, listings and requests.')"
    />

    @if (session('status'))
        <x-alert variant="success">{{ session('status') }}</x-alert>
    @endif

    <form wire:submit="login" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input
                wire:model="form.email"
                id="email"
                name="email"
                type="email"
                :label="__('Email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input
                wire:model="form.password"
                id="password"
                name="password"
                type="password"
                :label="__('Password')"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember" class="inline-flex min-h-[44px] items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-medium text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <x-button type="submit" class="w-full">
            {{ __('Log in') }}
        </x-button>
    </form>

    <p class="text-center text-sm text-gray-600">
        {{ __('New here?') }}
        <a href="{{ route('register') }}" wire:navigate class="font-medium text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ __('Create an account') }}
        </a>
    </p>
</div>
