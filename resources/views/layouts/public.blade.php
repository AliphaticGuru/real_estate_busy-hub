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
            <x-site-nav />

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
