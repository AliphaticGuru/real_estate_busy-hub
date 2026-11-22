<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ isset($title) && $title !== '' ? $title . ' · ' : '' }}{{ config('app.name') }}</title>

        {{ $head ?? '' }}

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col pt-16">
            <header
                x-data="{
                    open: false,
                    scrolled: false,
                    autoHide: false,
                    idle: false,
                    timer: null,
                    init() {
                        this.autoHide = localStorage.getItem('public-nav-autohide') === 'true';
                        this.scrolled = window.scrollY > 0;

                        if (this.autoHide && this.scrolled) {
                            this.scheduleHide();
                        }
                    },
                    onScroll() {
                        this.scrolled = window.scrollY > 0;
                        this.idle = false;
                        clearTimeout(this.timer);

                        if (this.autoHide && this.scrolled) {
                            this.scheduleHide();
                        }
                    },
                    scheduleHide() {
                        clearTimeout(this.timer);
                        this.timer = setTimeout(() => {
                            if (this.autoHide && this.scrolled) {
                                this.idle = true;
                                this.open = false;
                            }
                        }, 2250);
                    },
                    toggleAutoHide() {
                        this.autoHide = !this.autoHide;
                        this.idle = false;
                        localStorage.setItem('public-nav-autohide', this.autoHide ? 'true' : 'false');
                        clearTimeout(this.timer);

                        if (this.autoHide && this.scrolled) {
                            this.scheduleHide();
                        }
                    }
                }"
                @scroll.window.passive="onScroll()"
                :class="(autoHide && idle && scrolled) ? '-translate-y-full opacity-0 pointer-events-none' : 'translate-y-0 opacity-100'"
                class="fixed inset-x-0 top-0 z-50 bg-white border-b border-gray-100 shadow-sm transition-all duration-300 ease-in-out"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
                    <a href="{{ route('listings.index') }}" class="text-lg font-semibold text-gray-900">
                        {{ config('app.name') }}
                    </a>

                    <div class="flex items-center gap-4">
                        <nav class="hidden sm:flex items-center gap-4 text-sm" aria-label="{{ __('Primary navigation') }}">
                            <a href="{{ route('listings.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Listings') }}</a>
                            <a href="{{ route('professionals.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Partners') }}</a>
                            <a href="{{ route('fees') }}" class="text-gray-600 hover:text-gray-900">{{ __('Fees') }}</a>
                            <a href="{{ route('verification') }}" class="text-gray-600 hover:text-gray-900">{{ __('Verification') }}</a>
                            <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Blog') }}</a>
                        </nav>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                @click="toggleAutoHide()"
                                aria-pressed="false"
                                x-bind:aria-pressed="autoHide ? 'true' : 'false'"
                                aria-label="{{ __('Toggle auto-hide navigation') }}"
                                title="{{ __('Toggle auto-hide navigation') }}"
                            >
                                <svg class="h-5 w-5" :class="autoHide ? 'text-indigo-600' : 'text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </button>

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
                    </div>
                </div>

                <nav
                    id="public-mobile-menu"
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="sm:hidden absolute inset-x-0 top-full z-50 border-t border-gray-100 bg-white shadow-lg"
                    aria-label="{{ __('Mobile navigation') }}"
                >
                    <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col gap-1 text-sm">
                        <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.*')" @click="open = false">{{ __('Listings') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('professionals.index')" :active="request()->routeIs('professionals.*')" @click="open = false">{{ __('Partners') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('fees')" :active="request()->routeIs('fees')" @click="open = false">{{ __('Fees') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('verification')" :active="request()->routeIs('verification')" @click="open = false">{{ __('Verification') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('blog.index')" :active="request()->routeIs('blog.*')" @click="open = false">{{ __('Blog') }}</x-responsive-nav-link>
                    </div>
                </nav>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="py-10 text-center text-sm text-gray-500">
                <p>{{ config('app.name') }} · {{ __('Find verified real estate & construction partners in Abuja') }}</p>
                <p class="mt-2">
                    <a href="{{ route('professionals.index') }}" class="text-brand-700 hover:underline">{{ __('Partners') }}</a>
                </p>
                <nav class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-2" aria-label="{{ __('Legal') }}">
                    <a href="{{ route('privacy.index') }}" class="text-brand-700 hover:underline">{{ __('Privacy') }}</a>
                    <a href="{{ route('terms.index') }}" class="text-brand-700 hover:underline">{{ __('Terms') }}</a>
                    <a href="{{ route('complaints.index') }}" class="text-brand-700 hover:underline">{{ __('Complaints') }}</a>
                </nav>
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
