<x-public-layout :title="__('Request consultation')">
    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('professionals.show', $professional->slug) }}" class="block mb-6 text-sm text-gray-600 hover:text-gray-900">
            &larr; {{ __('Back to profile') }}
        </a>

        <h1 class="text-2xl font-bold text-gray-900">{{ __('Request consultation') }}</h1>
        <p class="mt-2 text-gray-600">
            {{ __('Professional') }}:
            <a href="{{ route('professionals.show', $professional->slug) }}" class="text-brand-700 hover:underline">
                {{ $professional->user?->name ?? __('Professional') }}
            </a>
        </p>

        @if ($errors->any())
            <x-alert variant="error" class="mt-6">
                {{ __('Please correct the highlighted fields and try again.') }}
            </x-alert>
        @endif

        <form method="POST" action="{{ route('professionals.inquiries.store', $professional->slug) }}" class="mt-6 space-y-5">
            @csrf

            @include('inquiries.partials.fields', ['showType' => false])

            <x-button type="submit">
                {{ __('Submit inquiry') }}
            </x-button>
        </form>

        <p class="mt-6 text-sm text-gray-600">
            {{ __('By submitting this form you agree to the processing of your details as described in our') }}
            <a href="{{ route('privacy.index') }}" class="text-brand-700 hover:underline">{{ __('privacy policy') }}</a>.
        </p>
    </div>
</x-public-layout>
