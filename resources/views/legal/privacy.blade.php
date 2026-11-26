<x-public-layout :title="__('Privacy policy')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-text">{{ __('Privacy policy') }}</h1>

        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
            <p class="font-semibold">{{ __('Draft') }}</p>
            <p class="mt-1">
                {{ __('This page is a draft and must be reviewed by legal counsel before launch. It does not constitute legal advice and does not describe retention periods yet.') }}
            </p>
        </div>

        <section class="mt-8">
            <h2 class="text-xl font-semibold text-text">{{ __('Information we collect') }}</h2>
            <p class="mt-2 text-text-muted">
                {{ __('This section is a draft placeholder. The exact data collected from visitors and customers will be documented here before launch.') }}
            </p>
        </section>

        <section class="mt-6">
            <h2 class="text-xl font-semibold text-text">{{ __('How we use information') }}</h2>
            <p class="mt-2 text-text-muted">
                {{ __('This section is a draft placeholder. The purposes of processing will be documented here before launch.') }}
            </p>
        </section>
    </div>
</x-public-layout>
