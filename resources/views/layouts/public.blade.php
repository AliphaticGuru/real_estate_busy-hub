<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Livewire Styles -->
        @livewireStyles

        <!-- Alpine.js -->
        <script defer src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-gray-50 min-h-screen">
        <!-- Mobile Menu (visible on pages without Livewire component) -->
        <div class="md:hidden fixed top-0 left-0 right-0 z-50 bg-white shadow-md transform transition-transform duration-300 ease-out" id="mobileMenu">
            <div class="flex flex-col p-4">
                <button class="absolute right-4 top-4 text-gray-500 hover:text-gray-900" @click="mobileMenuOpen = false">×</button>
                <x-application-logo class="w-16 h-16 mx-auto mb-4 fill-current text-gray-500" />
                <nav class="space-y-4">
                    <x-resp-nav-link href="{{ route('listings.index') }}" class="text-lg">Listings</x-resp-nav-link>
                    <x-resp-nav-link href="{{ route('about') }}" class="text-lg">About</x-resp-nav-link>
                    @guest
                        <x-resp-nav-link href="{{ route('login') }}" class="text-lg">Login</x-resp-nav-link>
                        <x-resp-nav-link href="{{ route('register') }}" class="text-lg">Register</x-resp-nav-link>
                    @else
                        <x-resp-nav-link href="{{ route('listings.index') }}" class="text-lg">My Inquiries</x-resp-nav-link>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="button" class="text-gray-500 hover:text-gray-900" @click="event.preventDefault(); this.form.submit()">Logout</button>
                        </form>
                    @endguest
                </nav>
            </div>
        </div>

        <script>
            // Mobile menu Alpine.js
            document.addEventListener('alpine:init', () => {
                Alpine.data('mobileMenu', () => ({
                    mobileMenuOpen: false,
                    openMobileMenu() {
                        this.mobileMenuOpen = true;
                    },
                    closeMobileMenu() {
                        this.mobileMenuOpen = false;
                    }
                }));
            });

            // Close mobile menu on link click
            document.querySelectorAll('#mobileMenu a').forEach(link => {
                link.addEventListener('click', () => {
                    document.getElementById('mobileMenu').classList.add('translate-x-full');
                });
            });
        </script>

        <!-- Main Content -->
        <div class="container mx-auto py-8 px-4 max-w-4xl">
            @livewireStyles
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="bg-white shadow-talbot border-t border-gray-200 py-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-500">© {{ date('Y') }} Busy Hub. All rights reserved.</p>
                    <div class="flex gap-6">
                        <a href="https://wa.me/+2348000000001" class="text-gray-500 hover:text-green-600" target="_blank" rel="noopener">
                            WhatsApp
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-600">Terms</a>
                        <a href="#" class="text-gray-500 hover:text-gray-600">Privacy</a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>