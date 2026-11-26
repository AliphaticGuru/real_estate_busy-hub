<nav x-data="{ open: false }" @click.outside="open = false" class="fixed inset-x-0 top-0 z-50 border-b border-gray-100 bg-white shadow-sm">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-3 sm:gap-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-6">
            <!-- Brand -->
            <a href="{{ url('/') }}" class="truncate text-lg font-semibold text-gray-900">
                {{ config('app.name') }}
            </a>

            <!-- Navigation Links -->
            <div class="hidden items-center gap-4 text-sm sm:flex" aria-label="{{ __('Primary navigation') }}">
                
                @auth
                    @if (auth()->user()->hasVerifiedEmail())
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('dashboard') ? 'font-semibold text-gray-900' : '' }}">
                            {{ __('Dashboard') }}
                        </a>

                        @if (auth()->user()->hasRole(\App\Enums\RoleName::SuperAdmin, \App\Enums\RoleName::Company, \App\Enums\RoleName::Professional, \App\Enums\RoleName::Customer))
                            <a href="{{ route('inquiries.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('inquiries.*') ? 'font-semibold text-gray-900' : '' }}">
                                {{ __('Inquiries') }}
                            </a>
                        @endif
                    @endif
                @endauth

                <a href="{{ route('listings.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('listings.*') ? 'font-semibold text-gray-900' : '' }}">
                    {{ __('Listings') }}
                </a>
                <a href="{{ route('professionals.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('professionals.*') ? 'font-semibold text-gray-900' : '' }}">
                    {{ __('Partners') }}
                </a>
                <a href="{{ route('fees') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('fees') ? 'font-semibold text-gray-900' : '' }}">
                    {{ __('Fees') }}
                </a>
                <a href="{{ route('verification') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('verification') ? 'font-semibold text-gray-900' : '' }}">
                    {{ __('Verification') }}
                </a>
                <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('blog.*') ? 'font-semibold text-gray-900' : '' }}">
                    {{ __('Blog') }}
                </a>

            </div>
        </div>

        @auth
            <!-- Avatar menu for authenticated users -->
            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex min-h-[44px] items-center gap-2 rounded-md border border-transparent px-3 py-2 text-sm leading-4 font-medium text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <x-avatar :user="auth()->user()" size="md" />

                            <div class="ms-1">
                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="truncate text-sm font-medium text-gray-900" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                        <div class="truncate text-sm text-gray-500">{{ auth()->user()->email }}</div>

                        <div class="mt-3">
                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            {{-- Theme toggle slot: arrives with the later U2 phase. --}}
                            <div class="px-4 py-2 text-sm text-gray-500">
                                {{ __('Theme toggle (coming soon)') }}
                            </div>

                            <livewire:layout.logout />
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>
        @else
            <!-- Guest actions -->
            <div class="flex items-center gap-2 flex-nowrap">
                <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {{ __('Log in') }}
                </a>
                <a href="{{ route('register') }}" class="inline-flex min-h-[44px] items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {{ __('Register') }}
                </a>
            </div>
        @endauth

        <!-- Hamburger -->
        <button
            type="button"
            @click="open = !open"
            class="inline-flex h-11 w-11 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:hidden"
            aria-expanded="false"
            x-bind:aria-expanded="open ? 'true' : 'false'"
            aria-controls="site-mobile-menu"
            aria-label="{{ __('Toggle navigation menu') }}"
        >
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
    </div>

    <!-- Responsive Navigation Menu -->
    <nav
        id="site-mobile-menu"
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
        <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 text-sm">
            <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.*')" @click="open = false">
                {{ __('Listings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('professionals.index')" :active="request()->routeIs('professionals.*')" @click="open = false">
                {{ __('Partners') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('fees')" :active="request()->routeIs('fees')" @click="open = false">
                {{ __('Fees') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('verification')" :active="request()->routeIs('verification')" @click="open = false">
                {{ __('Verification') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('blog.index')" :active="request()->routeIs('blog.*')" @click="open = false">
                {{ __('Blog') }}
            </x-responsive-nav-link>

            @auth
                @if (auth()->user()->hasVerifiedEmail())
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" @click="open = false">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>

                    @if (auth()->user()->hasRole(\App\Enums\RoleName::SuperAdmin, \App\Enums\RoleName::Company, \App\Enums\RoleName::Professional, \App\Enums\RoleName::Customer))
                        <x-responsive-nav-link :href="route('inquiries.index')" :active="request()->routeIs('inquiries.*')" @click="open = false">
                            {{ __('Inquiries') }}
                        </x-responsive-nav-link>
                    @endif
                @endif

                @if (auth()->check())
                    <livewire:layout.logout />
                @endif
            @endauth
        </div>
    </nav>
</nav>
