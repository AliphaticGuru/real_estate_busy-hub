<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="fixed inset-x-0 top-0 z-50 border-b border-gray-100 bg-white shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-6">
            <!-- Brand -->
            <a href="{{ route('dashboard') }}" wire:navigate class="truncate text-lg font-semibold text-gray-900">
                {{ config('app.name') }}
            </a>

            <!-- Navigation Links -->
            <div class="hidden items-center gap-6 text-sm sm:flex" aria-label="{{ __('Primary navigation') }}">
                <a href="{{ route('dashboard') }}" wire:navigate class="{{ request()->routeIs('dashboard') ? 'font-semibold text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ __('Dashboard') }}
                </a>
                <a href="{{ route('listings.index') }}" class="{{ request()->routeIs('listings.*') ? 'font-semibold text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ __('Listings') }}
                </a>
                <a href="{{ route('profile') }}" wire:navigate class="{{ request()->routeIs('profile') ? 'font-semibold text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ __('Profile') }}
                </a>
            </div>
        </div>

        <!-- Settings Dropdown -->
        <div class="hidden sm:flex sm:items-center">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" class="inline-flex min-h-[44px] items-center gap-2 rounded-md border border-transparent px-3 py-2 text-sm leading-4 font-medium text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name" class="max-w-[12rem] truncate"></div>

                        <div class="ms-1">
                            <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-dropdown-link>
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </button>
                </x-slot>
            </x-dropdown>
        </div>

        <!-- Hamburger -->
        <button
            type="button"
            @click="open = !open"
            class="inline-flex h-11 w-11 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:hidden"
            aria-expanded="false"
            x-bind:aria-expanded="open ? 'true' : 'false'"
            aria-controls="authenticated-mobile-menu"
            aria-label="{{ __('Toggle navigation menu') }}"
        >
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path :class="{'hidden': open, 'inline-flex': ! open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                <path :class="{'hidden': ! open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Responsive Navigation Menu -->
    <nav
        id="authenticated-mobile-menu"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute inset-x-0 top-full z-50 border-t border-gray-100 bg-white shadow-lg sm:hidden"
        aria-label="{{ __('Mobile navigation') }}"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 text-sm">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate @click="open = false">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.*')" @click="open = false">
                {{ __('Listings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profile')" :active="request()->routeIs('profile')" wire:navigate @click="open = false">
                {{ __('Profile') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-gray-100 px-4 py-3">
            <div class="truncate text-sm font-medium text-gray-900" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
            <div class="truncate text-sm text-gray-500">{{ auth()->user()->email }}</div>

            <div class="mt-3">
                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </nav>
</nav>
