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

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
                <select
                    name="type"
                    id="type"
                    required
                    class="mt-1 block w-full min-h-[44px] rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                >
                    <option value="consultation" @selected(old('type', 'consultation') === 'consultation')>
                        {{ __('Consultation') }}
                    </option>
                    <option value="inspection" @selected(old('type') === 'inspection')>
                        {{ __('Inspection') }}
                    </option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>

            <div>
                <x-input
                    name="name"
                    type="text"
                    :label="__('Name')"
                    :value="old('name', auth()->user()?->name)"
                    required
                    autocomplete="name"
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input
                    name="phone"
                    type="tel"
                    :label="__('Phone')"
                    :value="old('phone')"
                    required
                    autocomplete="tel"
                />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <x-input
                    name="email"
                    type="email"
                    :label="__('Email')"
                    :value="old('email', auth()->user()?->email)"
                    required
                    autocomplete="email"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input
                    name="preferred_date"
                    type="date"
                    :label="__('Preferred date (optional)')"
                    :value="old('preferred_date')"
                />
                <x-input-error :messages="$errors->get('preferred_date')" class="mt-2" />
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">{{ __('Message') }}</label>
                <textarea
                    name="message"
                    id="message"
                    rows="4"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                >{{ old('message') }}</textarea>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <x-button type="submit">
                {{ __('Submit inquiry') }}
            </x-button>
        </form>
    </div>
</x-public-layout>
