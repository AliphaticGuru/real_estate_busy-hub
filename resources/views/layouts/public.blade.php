<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <x-theme-script />

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

            <x-site-footer />
        </div>

        @livewireScripts
    </body>
</html>
