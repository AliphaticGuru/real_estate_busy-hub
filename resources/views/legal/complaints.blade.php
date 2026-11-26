<x-public-layout :title="__('Complaints')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-text">{{ __('Complaints') }}</h1>

        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
            <p class="font-semibold">{{ __('Draft') }}</p>
            <p class="mt-1">
                {{ __('This page is a draft and must be reviewed by legal counsel before launch. It does not constitute legal advice yet.') }}
            </p>
        </div>

        <section class="mt-8">
            <h2 class="text-xl font-semibold text-text">{{ __('How to submit a complaint') }}</h2>
            <p class="mt-2 text-text-muted">
                {{ __('This section is a draft placeholder. The complaint process will be published here before launch.') }}
            </p>
        </section>
    </div>
</x-public-layout>
