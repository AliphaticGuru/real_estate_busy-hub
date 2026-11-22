<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ isset($title) && $title !== '' ? $title . ' · ' : '' }}{{ config('app.name') }}</title>

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-900">
        <div class="flex min-h-screen flex-col">
            <main class="flex flex-1 items-center justify-center px-4 py-10">
                <div class="w-full max-w-md">
                    <div class="mb-6 text-center">
                        <a href="{{ route('listings.index') }}" wire:navigate class="text-lg font-semibold text-gray-900">
                            {{ config('app.name') }}
                        </a>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Find verified real estate & construction partners in Abuja') }}
                        </p>
                    </div>

                    <x-card class="p-6 sm:p-8">
                        {{ $slot }}
                    </x-card>
                </div>
            </main>

            <footer class="py-10 text-center text-sm text-gray-500">
                <p>{{ config('app.name') }} · {{ __('Find verified real estate & construction partners in Abuja') }}</p>
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
