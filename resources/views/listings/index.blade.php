<x-public-layout :title="__('Listings')">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-text">{{ __('Listings') }}</h1>

    <form method="GET" action="{{ route('listings.index') }}" class="mt-6 mb-8 flex flex-wrap gap-4">
        <div>
            <label class="sr-only" for="property_type">Property type</label>
            <select name="property_type" id="property_type" class="block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand">
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
            <input type="text" name="city" id="city" value="{{ request('city') }}" placeholder="{{ __('City') }}" class="block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand">
        </div>

        <div>
            <label class="sr-only" for="district">District</label>
            <input type="text" name="district" id="district" value="{{ request('district') }}" placeholder="{{ __('District') }}" class="block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand">
        </div>

        <div>
            <label class="sr-only" for="bedrooms">Bedrooms</label>
            <input type="number" name="bedrooms" id="bedrooms" value="{{ request('bedrooms') }}" min="0" class="block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand" placeholder="{{ __('Bedrooms') }}">
        </div>

        <div>
            <label class="sr-only" for="min_price">Minimum price</label>
            <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" min="0" class="block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand" placeholder="{{ __('Min price') }}">
        </div>

        <div>
            <label class="sr-only" for="max_price">Maximum price</label>
            <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" min="0" class="block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand" placeholder="{{ __('Max price') }}">
        </div>

        <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-800 focus:bg-brand-800 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Filter') }}
        </button>

        <a href="{{ route('listings.index') }}" class="inline-flex items-center px-4 py-2 bg-surface border border-border rounded-md font-semibold text-xs text-text uppercase tracking-widest hover:bg-surface-muted focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Clear') }}
        </a>
    </form>

    @if ($listings->isNotEmpty())
        <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg divide-y divide-border">
            @foreach ($listings as $listing)
                <a href="{{ route('listings.show', $listing->slug) }}" class="block p-6 hover:bg-surface-muted">
                    <div class="flex justify-between">
                        <div>
                            <div class="text-lg font-medium text-text">{{ $listing->title }}</div>
                            <div class="text-sm text-text-muted">
                                {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-lg font-semibold text-text">
                                ₦{{ number_format($listing->price / 100) }}
                            </div>
                            <div class="text-sm text-text-muted">
                                {{ $listing->bedrooms }} {{ __('bed') }} · {{ $listing->bathrooms }} {{ __('bath') }}
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-text">
                {{ __('No listings found.') }}
            </div>
        </div>
    @endif
</div>
</x-public-layout>
