<x-public-layout :title="__('Too many requests')">
    <div class="max-w-xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl font-bold text-text">{{ __('Too many requests') }}</h1>
        <p class="mt-4 text-text-muted">
            {{ __('You have sent several inquiries in a short time. Please wait a minute and try again.') }}
        </p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('listings.index') }}"
            class="mt-8 inline-flex min-h-[44px] items-center justify-center rounded-md border border-transparent bg-brand-700 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-800">
            {{ __('Go back') }}
        </a>
    </div>
</x-public-layout>
