<x-public-layout :title="__('Request inspection or consultation')">
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('listings.show', $listing->slug) }}" class="block mb-6 text-sm text-gray-600 hover:text-gray-900">
            &larr; {{ __('Back to listing') }}
        </a>

        <h1 class="text-2xl font-bold text-gray-900">{{ __('Request inspection or consultation') }}</h1>
        <p class="mt-2 text-gray-600">
            {{ __('Listing') }}:
            <a href="{{ route('listings.show', $listing->slug) }}" class="text-brand-700 hover:underline">
                {{ $listing->title }}
            </a>
        </p>

        @if ($errors->any())
            <x-alert variant="error" class="mt-6">
                {{ __('Please correct the highlighted fields and try again.') }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('inquiries.store', $listing->slug) }}" class="mt-6 space-y-5">
            @csrf

            @include('inquiries.partials.fields', ['showType' => true])

            <x-button type="submit">
                {{ __('Submit inquiry') }}
            </x-button>
        </form>
    </div>
</x-public-layout>
