<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Fees') }} · {{ config('app.name') }}</title>

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
                    <nav class="flex items-center gap-4 text-sm">
                        <a href="{{ route('listings.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Listings') }}</a>
                        <a href="{{ route('fees') }}" class="text-gray-600 hover:text-gray-900">{{ __('Fees') }}</a>
                        <a href="{{ route('verification') }}" class="text-gray-600 hover:text-gray-900">{{ __('Verification') }}</a>
                        <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Blog') }}</a>
                    </nav>
                </div>
            </header>

            <main class="py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('Fees') }}</h1>
                    <p class="mt-2 text-gray-600">
                        {{ __('Transparent fees for verification, professional consultancy and inspections. Final amounts are agreed directly with the partner before work begins.') }}
                    </p>

                    <div class="mt-6 grid gap-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('Listing verification fee') }}</h2>
                                <p class="mt-2 text-gray-700">
                                    {{ __('Covers the full verification workflow for a single listing: title document, survey plan, ownership proof, site inspection and no-encumbrance checks. The fee is payable by the buyer once the verified listing is confirmed.') }}
                                </p>
                                <p class="mt-4 text-2xl font-bold text-gray-900">₦25,000</p>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('Professional consultancy fee') }}</h2>
                                <p class="mt-2 text-gray-700">
                                    {{ __('Consultation or legal due diligence with a lawyer, architect, engineer or surveyor. Professional fees are quoted per engagement and agreed before the consultation starts.') }}
                                </p>
                                <p class="mt-4 text-2xl font-bold text-gray-900">From ₦75,000</p>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('Site inspection fee') }}</h2>
                                <p class="mt-2 text-gray-700">
                                    {{ __('On-site inspection for inspection requests. Travel and logistics are included in the quoted amount.') }}
                                </p>
                                <p class="mt-4 text-2xl font-bold text-gray-900">From ₦50,000</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('Payment and next steps') }}</h2>
                            <p class="mt-2 text-gray-700">
                                {{ __('Fees are settled directly between the customer and the partner. Once payment is confirmed, the partner proceeds with the verification workflow described on the verification page.') }}
                            </p>
                            <p class="mt-4">
                                <a href="{{ route('listings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Browse verified listings') }}
                                </a>
                            </p>
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
