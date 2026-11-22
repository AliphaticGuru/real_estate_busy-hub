<x-public-layout :title="$company->name">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <a href="{{ route('listings.index') }}" class="block mb-6 text-sm text-gray-600 hover:text-gray-900">
        &larr; {{ __('Back to listings') }}
    </a>

    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $company->name }}</h1>
            <p class="text-gray-600">{{ $company->city }}{{ $company->state ? ', ' . $company->state : '' }}</p>
            <p class="mt-4 text-gray-700">{{ $company->description }}</p>

            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-gray-500">{{ __('Email') }}</dt>
                    <dd class="font-medium text-gray-900">{{ $company->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">{{ __('Phone') }}</dt>
                    <dd class="font-medium text-gray-900">{{ $company->phone }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">{{ __('Address') }}</dt>
                    <dd class="font-medium text-gray-900">{{ $company->address }}</dd>
                </div>
            </dl>
        </div>
    </div>

    @if ($professionals->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Verified professionals') }}</h2>
            <ul class="mt-4 divide-y divide-gray-200">
                @foreach ($professionals as $professional)
                    <li class="py-4">
                        <a href="{{ route('professionals.show', $professional->slug) }}" class="block text-lg font-medium text-gray-900 hover:text-indigo-600">
                            {{ $professional->user?->name ?? __('Professional') }}
                        </a>
                        <div class="text-sm text-gray-500">
                            {{ $professional->type?->label() }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($listings->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Listings') }}</h2>
            <ul class="mt-4 divide-y divide-gray-200">
                @foreach ($listings as $listing)
                    <li class="py-4">
                        <a href="{{ route('listings.show', $listing->slug) }}" class="block text-lg font-medium text-gray-900 hover:text-indigo-600">
                            {{ $listing->title }}
                        </a>
                        <div class="text-sm text-gray-500">
                            {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }} ·
                            ₦{{ number_format($listing->price / 100) }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
</x-public-layout>
