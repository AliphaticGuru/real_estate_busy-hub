<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ isset($title) && $title !== '' ? $title . ' · ' : '' }}{{ config('app.name') }}</title>

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="bg-white border-b border-gray-100" x-data="{ open: false }">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
                    <a href="{{ route('listings.index') }}" class="text-lg font-semibold text-gray-900">
                        {{ config('app.name') }}
                    </a>

                    <nav class="hidden sm:flex items-center gap-4 text-sm" aria-label="{{ __('Primary navigation') }}">
                        <a href="{{ route('listings.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Listings') }}</a>
                        <a href="{{ route('fees') }}" class="text-gray-600 hover:text-gray-900">{{ __('Fees') }}</a>
                        <a href="{{ route('verification') }}" class="text-gray-600 hover:text-gray-900">{{ __('Verification') }}</a>
                        <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Blog') }}</a>
                    </nav>

                    <button
                        type="button"
                        class="sm:hidden inline-flex h-11 w-11 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        @click="open = !open"
                        aria-expanded="false"
                        x-bind:aria-expanded="open ? 'true' : 'false'"
                        aria-controls="public-mobile-menu"
                        aria-label="{{ __('Toggle navigation menu') }}"
                    >
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

                <nav id="public-mobile-menu" x-show="open" x-cloak class="sm:hidden border-t border-gray-100 bg-white" aria-label="{{ __('Mobile navigation') }}">
                    <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col gap-1 text-sm">
                        <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.*')">{{ __('Listings') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('fees')" :active="request()->routeIs('fees')">{{ __('Fees') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('verification')" :active="request()->routeIs('verification')">{{ __('Verification') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('blog.index')" :active="request()->routeIs('blog.*')">{{ __('Blog') }}</x-responsive-nav-link>
                    </div>
                </nav>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="py-10 text-center text-sm text-gray-500">
                <p>{{ config('app.name') }} · {{ __('Find verified real estate & construction partners in Abuja') }}</p>

                @php
                    $whatsappNumber = preg_replace('/\D+/', '', (string) config('services.whatsapp.number'));
                @endphp

                @if ($whatsappNumber !== '')
                    <p class="mt-4">
                        <a
                            href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode(__('Hello! I would like to speak to a verified partner.')) }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            target="_blank"
                            rel="noopener"
                        >
                            {{ __('Chat on WhatsApp') }}
                        </a>
                    </p>
                @endif
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
