<x-public-layout :title="__('Listings')">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Listings') }}</h1>

    <form method="GET" action="{{ route('listings.index') }}" class="mt-6 mb-8 flex flex-wrap gap-4">
        <div>
            <label class="sr-only" for="property_type">Property type</label>
            <select name="property_type" id="property_type" class="border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full">
                <option value="">All types</option>
                <option value="house">House</option>
                <option value="apartment">Apartment</option>
                <option value="duplex">Duplex</option>
                <option value="villa">Villa</option>
                <option value="townhouse">Townhouse</option>
                <option value="land">Land</option>
                <option value="office">Office</option>
            </select>
        </div>

        <div>
            <label class="sr-only" for="city">City</label>
            <input type="text" name="city" id="city" value="{{ request('city') }}" placeholder="{{ __('City') }}" class="border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full">
        </div>

        <div>
            <label class="sr-only" for="district">District</label>
            <input type="text" name="district" id="district" value="{{ request('district') }}" placeholder="{{ __('District') }}" class="border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full">
        </div>

        <div>
            <label class="sr-only" for="bedrooms">Bedrooms</label>
            <input type="number" name="bedrooms" id="bedrooms" value="{{ request('bedrooms') }}" min="0" class="border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full" placeholder="{{ __('Bedrooms') }}">
        </div>

        <div>
            <label class="sr-only" for="min_price">Minimum price</label>
            <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" min="0" class="border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full" placeholder="{{ __('Min price') }}">
        </div>

        <div>
            <label class="sr-only" for="max_price">Maximum price</label>
            <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" min="0" class="border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full" placeholder="{{ __('Max price') }}">
        </div>

        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Filter') }}
        </button>

        <a href="{{ route('listings.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Clear') }}
        </a>
    </form>

    @if ($listings->isNotEmpty())
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-200">
            @foreach ($listings as $listing)
                <a href="{{ route('listings.show', $listing->slug) }}" class="block p-6 hover:bg-gray-50">
                    <div class="flex justify-between">
                        <div>
                            <div class="text-lg font-medium text-gray-900">{{ $listing->title }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-lg font-semibold text-gray-900">
                                ₦{{ number_format($listing->price / 100) }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $listing->bedrooms }} {{ __('bed') }} · {{ $listing->bathrooms }} {{ __('bath') }}
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                {{ __('No listings found.') }}
            </div>
        </div>
    @endif
</div>
</x-public-layout>
