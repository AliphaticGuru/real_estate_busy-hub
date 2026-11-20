<x-public-layout :title="$listing->title">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <a href="{{ route('listings.index') }}" class="block mb-6 text-sm text-gray-600 hover:text-gray-900">
        &larr; {{ __('Back to listings') }}
    </a>

    @if (session('status'))
        <x-alert variant="success" class="mb-6">{{ session('status') }}</x-alert>
    @endif

    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $listing->title }}</h1>
                    <p class="text-gray-600">
                        {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}
                    </p>
                </div>

                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900">
                        ₦{{ number_format($listing->price / 100) }}
                    </div>
                </div>
            </div>

            <p class="mt-4 text-gray-700">{{ $listing->description }}</p>

            @if ($listing->images->isNotEmpty())
                <div class="mt-6">
                    @foreach ($listing->images as $image)
                        <img src="{{ $image->path }}" alt="{{ $listing->title }}" class="w-full rounded-lg">
                    @endforeach
                </div>
            @endif

            <div class="mt-6 border-t pt-6">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Listed by') }}</p>
                    <p class="font-medium text-gray-900">{{ $listing->company?->name ?? config('app.name') }}</p>
                </div>

                @php
                    $whatsappNumber = preg_replace('/\D+/', '', (string) ($listing->company?->whatsapp_number ?: config('services.whatsapp.number')));
                    $whatsappMessage = rawurlencode('Hello! I am interested in this listing: ' . $listing->title . ' — ' . $listing->city . ', ' . $listing->district . '. Price: ₦' . number_format($listing->price / 100) . '.');
                @endphp

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <x-button
                        :href="route('inquiries.create', $listing->slug)"
                        variant="primary"
                        class="grow"
                        aria-label="{{ __('Request an inspection or consultation') }}"
                    >
                        {{ __('Inquire') }}
                    </x-button>

                    @if ($whatsappNumber !== '')
                        <a
                            href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                            class="inline-flex grow min-h-[44px] items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            target="_blank"
                            rel="noopener"
                        >
                            {{ __('WhatsApp') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-public-layout>
