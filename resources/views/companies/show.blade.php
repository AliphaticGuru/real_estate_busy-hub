<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $company->name }} · {{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400;600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-900">
        <div class="min-h-screen">
            <header class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <a href="{{ route('listings.index') }}" class="text-lg font-semibold text-gray-900">
                        {{ config('app.name') }}
                    </a>
                    <a href="{{ route('listings.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        {{ __('All listings') }}
                    </a>
                </div>
            </header>

            <main class="py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('listings.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        &larr; {{ __('Back to listings') }}
                    </a>

                    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $company->name }}</h1>
                            <p class="text-gray-600">{{ $company->city }}{{ $company->state ? ', ' . $company->state : '' }}</p>
                            <p class="mt-4 text-gray-700">{{ $company->description }}</p>

                            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                        <dt class="text-sm text-gray-500">{{ __('Email') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $company->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Phone') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $company->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">{{ __('Address') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $company->address }}</dd>
                    </div>
                </dl>

                            @if ($listings->isNotEmpty())
                                <div class="mt-8">
                                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Listings') }}</h2>
                                    <ul class="mt-4 divide-y divide-gray-200">
                                        @foreach ($listings as $listing)
                                            <li class="py-4">
                                                <a href="{{ route('listings.show', $listing->slug) }}" class="block text-lg font-medium text-gray-900 hover:text-indigo-600">
                                                    {{ $listing->title }}
                                                </a>
                                                <div class="text-sm text-gray-500">
                                                    {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }} ·
                                                    ₦{{ number_format($listing->price / 100) }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-16 text-center text-sm text-gray-500">
                {{ config('app.name') }} · {{ __('Find verified real estate & construction partners in Abuja') }}
            </footer>
        </div>
    </body>
</html>
