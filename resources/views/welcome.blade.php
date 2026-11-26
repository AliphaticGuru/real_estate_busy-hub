<x-public-layout>
    <x-slot name="head">
        <meta name="description" content="{{ __('Verified property listings, legal due diligence and vetted construction professionals in Abuja, Nigeria. Buy, rent and build without fear of being scammed.') }}">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ config('app.name') }}">
        <meta property="og:description" content="{{ __('Verified property listings, legal due diligence and vetted construction professionals in Abuja, Nigeria.') }}">
        <meta property="og:url" content="{{ url('/') }}">
    </x-slot>

    @php
        $verificationSteps = [
            [
                'label' => __('Title document'),
                'copy' => __('We confirm the property has a valid title document and that it matches the seller.'),
            ],
            [
                'label' => __('Survey plan and ownership proof'),
                'copy' => __('The survey plan is compared with the site, and ownership documents are traced to the current owner.'),
            ],
            [
                'label' => __('Site inspection'),
                'copy' => __('A verifier visits the property on the ground to confirm what is advertised actually exists.'),
            ],
            [
                'label' => __('No encumbrance'),
                'copy' => __('We check for outstanding claims, disputes or loans before a listing is marked verified.'),
            ],
        ];

        $featuredListings = \App\Models\Listing::query()
            ->where('status', \App\Enums\ListingStatus::Published)
            ->where('verification_status', \App\Enums\ListingVerificationStatus::Verified)
            ->with(['images', 'company'])
            ->latest('verified_at')
            ->take(3)
            ->get();
    @endphp

    <!-- Hero -->
    <section class="border-b border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="max-w-3xl">
                <x-badge variant="brand">{{ __('Verified real estate & construction') }}</x-badge>

                <h1 class="mt-4 text-3xl font-bold leading-tight tracking-tight text-gray-900 sm:text-4xl lg:text-5xl">
                    {{ __('Buy, rent and build in Nigeria without fear of being scammed.') }}
                </h1>

                <p class="mt-4 max-w-2xl text-base leading-relaxed text-gray-600 sm:text-lg">
                    {{ __('Every listing is checked by a verifier before it goes live. Work with lawyers, architects, engineers and surveyors who are reviewed before they appear on the hub.') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ route('listings.index') }}"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-brand-600 px-5 py-3 text-sm font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                    >
                        {{ __('Browse verified listings') }}
                    </a>
                    <a
                        href="{{ route('verification') }}"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                    >
                        {{ __('How verification works') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Short intro -->
    <section class="bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <h2 class="text-xl font-bold tracking-tight text-gray-900">{{ __('Our aim') }}</h2>
                <p class="mt-3 text-base leading-relaxed text-gray-600">
                    {{ __('Verifyhaus Afrika exists to make verified real estate and construction in Abuja transparent: verified listings, vetted professionals and published fees, so buyers, renters and builders can act with confidence.') }}
                </p>
            </div>
        </div>
    </section>

    <!-- How verification works -->
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('How verification works') }}</h2>
            <p class="mt-2 text-base leading-relaxed text-gray-600">
                {{ __('Four steps cover the five checks every listing must pass before it can be published.') }}
            </p>
        </div>

        <ol class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($verificationSteps as $index => $step)
                <li class="flex h-full flex-col rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white">{{ $index + 1 }}</span>
                    <h3 class="mt-3 text-base font-semibold text-gray-900">{{ $step['label'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $step['copy'] }}</p>
                </li>
            @endforeach
        </ol>

        <p class="mt-6">
            <a href="{{ route('verification') }}" class="inline-flex min-h-[44px] items-center rounded-md text-sm font-semibold text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                {{ __('Read the full verification process') }}
            </a>
        </p>
    </section>

    <!-- Three service arms -->
    <section class="border-y border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Three ways we help') }}</h2>
                <p class="mt-2 text-base leading-relaxed text-gray-600">
                    {{ __('One hub for verified property, legal checks and construction expertise.') }}
                </p>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                <x-card class="flex h-full flex-col p-6">
                    <x-badge variant="brand">{{ __('Phase 1') }}</x-badge>
                    <h3 class="mt-3 text-lg font-semibold text-gray-900">{{ __('Verified marketplace') }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-gray-600">
                        {{ __('Browse property listings that have passed the verification checklist, with the status of every listing shown clearly.') }}
                    </p>
                    <a href="{{ route('listings.index') }}" class="mt-4 inline-flex min-h-[44px] items-center rounded-md text-sm font-semibold text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        {{ __('Browse listings') }}
                    </a>
                </x-card>

                <x-card class="flex h-full flex-col p-6">
                    <x-badge variant="brand">{{ __('Phase 1') }}</x-badge>
                    <h3 class="mt-3 text-lg font-semibold text-gray-900">{{ __('Legal due diligence') }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-gray-600">
                        {{ __('Work with lawyers and surveyors on title checks, ownership searches and the documents behind a property.') }}
                    </p>
                    <a href="{{ route('fees') }}" class="mt-4 inline-flex min-h-[44px] items-center rounded-md text-sm font-semibold text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        {{ __('See published fees') }}
                    </a>
                </x-card>

                <x-card class="flex h-full flex-col p-6">
                    <x-badge variant="warning">{{ __('Coming soon · Phase 2') }}</x-badge>
                    <h3 class="mt-3 text-lg font-semibold text-gray-900">{{ __('Construction and architectural consultancy') }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-gray-600">
                        {{ __('Planning, architectural design and construction consultancy from vetted professionals. This arm is not open yet.') }}
                    </p>
                    <span class="mt-4 inline-flex min-h-[44px] items-center text-sm font-medium text-gray-500">
                        {{ __('Coming soon') }}
                    </span>
                </x-card>
            </div>
        </div>
    </section>

    <!-- Why trust us -->
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Why trust us') }}</h2>
            <p class="mt-2 text-base leading-relaxed text-gray-600">
                {{ __('Transparency is the product. These are the commitments behind every listing and engagement.') }}
            </p>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-3">
            <x-card class="h-full p-6">
                <h3 class="text-base font-semibold text-gray-900">{{ __('Published fees') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    {{ __('Fees are published before work begins, so there are no surprise charges.') }}
                    <a href="{{ route('fees') }}" class="font-semibold text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        {{ __('View the fees page') }}
                    </a>
                </p>
            </x-card>

            <x-card class="h-full p-6">
                <h3 class="text-base font-semibold text-gray-900">{{ __('Vetted professionals') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    {{ __('Lawyers, architects, engineers and surveyors are reviewed before their profiles appear on the hub.') }}
                </p>
            </x-card>

            <x-card class="h-full p-6">
                <h3 class="text-base font-semibold text-gray-900">{{ __('Complaint handling') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                    {{ __('If a listing or partner does not meet expectations, report it and the case is reviewed.') }}
                </p>
            </x-card>
        </div>
    </section>

    <!-- Featured verified listings -->
    <section class="border-y border-gray-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Featured verified listings') }}</h2>
                    <p class="mt-2 text-base leading-relaxed text-gray-600">
                        {{ __('Published properties that have completed the verification checklist.') }}
                    </p>
                </div>
                <a href="{{ route('listings.index') }}" class="inline-flex min-h-[44px] items-center rounded-md text-sm font-semibold text-brand-700 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    {{ __('View all listings') }}
                </a>
            </div>

            @if ($featuredListings->isNotEmpty())
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredListings as $listing)
                        <x-card class="flex h-full flex-col overflow-hidden">
                            @if ($listing->images->isNotEmpty())
                                <img
                                    src="{{ $listing->images->first()->path }}"
                                    alt="{{ $listing->title }}"
                                    class="h-44 w-full object-cover"
                                    loading="lazy"
                                >
                            @else
                                <div class="flex h-44 items-center justify-center bg-gray-100 text-sm text-gray-500">
                                    {{ __('Photo coming soon') }}
                                </div>
                            @endif

                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-badge variant="success">{{ __('Verified') }}</x-badge>
                                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                        {{ ucfirst($listing->property_type->value) }}
                                    </span>
                                </div>

                                <h3 class="mt-3 text-lg font-semibold text-gray-900">
                                    <a href="{{ route('listings.show', $listing->slug) }}" class="hover:underline focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                        {{ $listing->title }}
                                    </a>
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}
                                </p>

                                <p class="mt-3 text-lg font-bold text-gray-900">
                                    ₦{{ number_format($listing->price / 100) }}
                                </p>

                                @if ($listing->company)
                                    <p class="mt-1 text-sm text-gray-600">{{ $listing->company->name }}</p>
                                @endif
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                <x-empty-state class="mt-8" :title="__('No verified listings yet')">
                    {{ __('Verified listings will appear here as soon as the first property passes review.') }}
                </x-empty-state>
            @endif
        </div>
    </section>

    <!-- Partners -->
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Partners') }}</h2>
            <p class="mt-2 text-base leading-relaxed text-gray-600">
                {{ __('Lawyers, architects, engineers and surveyors will be listed here once onboarding opens. These cards are placeholders.') }}
            </p>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-3">
            @foreach ([__('Lawyer'), __('Architect'), __('Engineer')] as $partnerRole)
                <x-card class="flex h-full flex-col p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Partner profile') }}</h3>
                        <x-badge variant="placeholder">{{ __('Placeholder') }}</x-badge>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        {{ $partnerRole }} · {{ __('Profile details will appear here once this partner is onboarded.') }}
                    </p>
                </x-card>
            @endforeach
        </div>
    </section>
</x-public-layout>
