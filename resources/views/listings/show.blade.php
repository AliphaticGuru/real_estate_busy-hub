<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $listing->title }} · {{ config('app.name') }}</title>

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
                            <div class="flex justify-between">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">{{ $listing->title }}</h1>
                                    <p class="text-gray-600">
                                        {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900">
                                        ₦{{ number_format($listing->price / 100) }}
                                    </div>
                                </div>
                            </div>

                            <p class="mt-4 text-gray-700">{{ $listing->description }}</p>

                            @if ($listing->images->isNotEmpty())
                                <div class="mt-6">
                                    @foreach ($listing->images as $image)
                                        <img src="{{ $image->path }}" alt="{{ $listing->title }}" class="w-full rounded-lg">
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-6 flex items-center justify-between border-t pt-6">
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('Listed by') }}</p>
                                    <p class="font-medium text-gray-900">{{ $listing->company?->name ?? config('app.name') }}</p>
                                </div>

                                @php
                                    $whatsappNumber = $listing->company?->whatsapp_number ?: '2348000000000';
                                    $whatsappMessage = rawurlencode('Hello! I am interested in this listing: ' . $listing->title . ' — ' . $listing->city . ', ' . $listing->district . '. Price: ₦' . number_format($listing->price / 100) . '.');
                                @endphp
                                <a
                                    href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    {{ __('WhatsApp') }}
                                </a>
                            </div>
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
