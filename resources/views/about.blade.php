<x-public-layout :title="__('About')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('About') }}</h1>

        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-semibold">{{ __('Draft') }}</p>
            <p class="mt-1">{{ __('Draft: pending review') }}</p>
        </div>

        <section class="mt-8">
            <p class="text-lg leading-relaxed text-gray-700">
                {{ __('Verifyhaus Afrika is a verified real estate and construction hub for Nigeria, starting in Abuja. It runs the Phase 1 scope: a verified property marketplace, vetted partner professionals, consultation and inspection requests, published fees and the verification workflow. Buyers, renters and builders can deal online and offline through verified listings and vetted professionals.') }}
            </p>

            <p class="mt-4 text-base leading-relaxed text-gray-600">
                {{ __('Verified listings on the hub are checked by a verifier before they are featured, and partner professionals such as lawyers, architects and engineers are reviewed before they can be contacted.') }}
            </p>

            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('verification') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-brand-600 px-5 py-3 text-sm font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    {{ __('How verification works') }}
                </a>
                <a href="{{ route('fees') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-semibold uppercase tracking-widest text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    {{ __('Fees') }}
                </a>
            </div>
        </section>
    </div>
</x-public-layout>
