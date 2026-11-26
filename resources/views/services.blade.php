<x-public-layout :title="__('Services')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Services') }}</h1>

        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-semibold">{{ __('Draft') }}</p>
            <p class="mt-1">{{ __('Draft: pending review') }}</p>
        </div>

        <section class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('Verified property marketplace') }}</h2>
            <p class="mt-2 text-gray-600 leading-relaxed">
                {{ __('Verified listings with a full offline and online due diligence trail: title document, survey plan, ownership proof, site inspection and no-encumbrance checks before a listing goes live.') }}
            </p>
        </section>

        <section class="mt-6">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('Legal due diligence') }}</h2>
            <p class="mt-2 text-gray-600 leading-relaxed">
                {{ __('Consultation and legal due diligence with partner lawyers, architects, engineers and surveyors for buyers, renters and builders. Consultation requests and inspection requests are submitted through the hub.') }}
            </p>
        </section>

        <section class="mt-6">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('Construction consultancy') }}</h2>
            <p class="mt-2 text-gray-600 leading-relaxed">
                {{ __('Construction consultancy support for buyers and builders planning renovations or new builds. Coming soon, Phase 2.') }}
            </p>
        </section>
    </div>
</x-public-layout>
