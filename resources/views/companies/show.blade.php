<x-public-layout :title="$company->name">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <a href="{{ route('listings.index') }}" class="block mb-6 text-sm text-text-muted hover:text-text">
        &larr; {{ __('Back to listings') }}
    </a>

    <div class="mt-6 bg-surface overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-text">{{ $company->name }}</h1>
            <p class="text-text-muted">{{ $company->city }}{{ $company->state ? ', ' . $company->state : '' }}</p>
            <p class="mt-4 text-text">{{ $company->description }}</p>

            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-text-muted">{{ __('Email') }}</dt>
                    <dd class="font-medium text-text">{{ $company->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-text-muted">{{ __('Phone') }}</dt>
                    <dd class="font-medium text-text">{{ $company->phone }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-text-muted">{{ __('Address') }}</dt>
                    <dd class="font-medium text-text">{{ $company->address }}</dd>
                </div>
            </dl>
        </div>
    </div>

    @if ($professionals->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-lg font-semibold text-text">{{ __('Verified professionals') }}</h2>
            <ul class="mt-4 divide-y divide-border">
                @foreach ($professionals as $professional)
                    <li class="py-4">
                        <a href="{{ route('professionals.show', $professional->slug) }}" class="block text-lg font-medium text-text hover:text-brand">
                            {{ $professional->user?->name ?? __('Professional') }}
                        </a>
                        <div class="text-sm text-text-muted">
                            {{ $professional->type?->label() }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($listings->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-lg font-semibold text-text">{{ __('Listings') }}</h2>
            <ul class="mt-4 divide-y divide-border">
                @foreach ($listings as $listing)
                    <li class="py-4">
                        <a href="{{ route('listings.show', $listing->slug) }}" class="block text-lg font-medium text-text hover:text-brand">
                            {{ $listing->title }}
                        </a>
                        <div class="text-sm text-text-muted">
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
