<x-public-layout :title="$professional->user?->name ?? __('Professional')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('professionals.index') }}" class="block mb-6 text-sm text-text-muted hover:text-text">
            &larr; {{ __('Back to partners') }}
        </a>

        @if (session('status'))
            <x-alert variant="success" class="mb-6">{{ session('status') }}</x-alert>
        @endif

        <div class="mt-6 bg-surface overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-text">{{ $professional->user?->name ?? __('Professional') }}</h1>
                        <p class="text-text-muted">{{ $professional->type?->label() }}</p>
                    </div>

                    <x-badge variant="success">{{ __('Verified credentials') }}</x-badge>
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-text-muted">{{ __('Registration body') }}</dt>
                        <dd class="font-medium text-text">{{ $professional->registration_body ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-text-muted">{{ __('Registration number') }}</dt>
                        <dd class="font-medium text-text">{{ $professional->registration_number ?: '—' }}</dd>
                    </div>

                    @if ($professional->company)
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-text-muted">{{ __('Company') }}</dt>
                            <dd class="font-medium text-text">
                                <a href="{{ route('companies.show', $professional->company->slug) }}" class="text-brand hover:underline">
                                    {{ $professional->company->name }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>

                @if ($professional->bio)
                    <div class="mt-6 border-t border-border pt-6">
                        <h2 class="text-lg font-semibold text-text">{{ __('About') }}</h2>
                        <p class="mt-2 whitespace-pre-line text-text">{{ $professional->bio }}</p>
                    </div>
                @endif
            </div>
        </div>

        @php
            $whatsappNumber = preg_replace('/\D+/', '', (string) config('services.whatsapp.number'));
            $whatsappMessage = rawurlencode(
                'Hello! I would like to consult ' .
                ($professional->user?->name ?? 'a professional') .
                ' (' . ($professional->type?->label() ?? 'professional') . ') on ' .
                config('app.name') . '.'
            );
        @endphp

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-button
                :href="route('professionals.inquiries.create', $professional->slug)"
                variant="primary"
                class="grow"
                aria-label="{{ __('Request a consultation') }}"
            >
                {{ __('Request consultation') }}
            </x-button>

            @if ($whatsappNumber !== '')
                <a
                    href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                    class="inline-flex grow min-h-[44px] items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md dark:bg-green-700 font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 dark:hover:bg-green-600 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    target="_blank"
                    rel="noopener"
                >
                    {{ __('WhatsApp') }}
                </a>
            @endif
        </div>
    </div>
</x-public-layout>
