<x-public-layout :title="__('Mission')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-text">{{ __('Mission') }}</h1>

        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
            <p class="font-semibold">{{ __('Draft') }}</p>
            <p class="mt-1">{{ __('Draft: pending review') }}</p>
        </div>

        <section class="mt-8">
            <p class="text-lg leading-relaxed text-text">
                {{ __('To make buying, renting and building property in Nigeria safe and transparent, through verified listings, vetted professionals and published fees.') }}
            </p>
        </section>
    </div>
</x-public-layout>
