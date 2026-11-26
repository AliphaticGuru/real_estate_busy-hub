<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <x-theme-script />

        <title>{{ isset($title) && $title !== '' ? $title . ' · ' : '' }}{{ config('app.name') }}</title>

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased font-sans bg-surface-muted text-text">
        <div class="flex min-h-screen flex-col pt-16">
            <x-site-nav />

            <main class="flex flex-1 items-center justify-center px-4 py-10">
                <div class="w-full max-w-md">
                    <div class="mb-6 text-center">
                        <a href="{{ route('listings.index') }}" wire:navigate class="text-lg font-semibold text-text">
                            {{ config('app.name') }}
                        </a>
                        <p class="mt-1 text-sm text-text-muted">
                            {{ __('Find verified real estate & construction partners in Abuja') }}
                        </p>
                    </div>

                    <x-card class="p-6 sm:p-8">
                        {{ $slot }}
                    </x-card>
                </div>
            </main>

            <x-site-footer />
        </div>

        @livewireScripts
    </body>
</html>
