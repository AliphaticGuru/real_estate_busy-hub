<x-public-layout :title="__('Request inspection or consultation')">
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @if (session('status'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <h1 class="text-2xl font-bold text-gray-900">{{ __('Request inspection or consultation') }}</h1>
    <p class="mt-2 text-gray-600">
        {{ __('Listing') }}: <a href="{{ route('listings.show', $listing->slug) }}" class="text-indigo-600">{{ $listing->title }}</a>
    </p>

    <form method="POST" action="{{ route('inquiries.store', $listing->id) }}" class="mt-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="type">{{ __('Type') }}</label>
            <select name="type" id="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                <option value="consultation">{{ __('Consultation') }}</option>
                <option value="inspection">{{ __('Inspection') }}</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="name">{{ __('Name') }}</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="phone">{{ __('Phone') }}</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="email">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700" for="preferred_date">{{ __('Preferred date') }}</label>
            <input type="date" name="preferred_date" id="preferred_date" value="{{ old('preferred_date') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700" for="message">{{ __('Message') }}</label>
            <textarea name="message" id="message" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Submit inquiry') }}
        </button>
    </form>
</div>
</x-public-layout>
