<x-public-layout :title="__('Access denied')">
    <div class="max-w-xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-bold text-text">{{ __('Access denied') }}</h1>
        <p class="mt-4 text-text-muted">
            {{ __('You do not have permission to access this page.') }}
        </p>
        <a href="{{ route('listings.index') }}"
            class="mt-8 inline-flex min-h-[44px] items-center justify-center rounded-md border border-transparent bg-brand-700 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-800">
            {{ __('Go back to listings') }}
        </a>
    </div>
</x-public-layout>
