<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Inquiry: :type', ['type' => Str::headline($inquiry->type->value)])"
            :subtitle="__('Status: :status', ['status' => Str::headline($inquiry->status->value)])" />
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        @if (session('status'))
            <x-alert variant="success" class="mb-6">{{ session('status') }}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert variant="error" class="mb-6">
                {{ __('Please correct the highlighted fields and try again.') }}
            </x-alert>
        @endif

        <x-card class="mb-6">
            <div class="p-6">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Type') }}</dt>
                        <dd class="mt-1">
                            <x-badge :variant="$inquiry->type === 'consultation' ? 'brand' : 'info'">
                                {{ Str::headline($inquiry->type->value) }}
                            </x-badge>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Listing / Professional') }}</dt>
                        <dd class="mt-1">
                            @if ($inquiry->listing)
                                <a href="{{ route('listings.show', $inquiry->listing->slug) }}"
                                    class="text-brand-700 hover:underline">
                                    {{ $inquiry->listing->title }}
                                </a>
                            @elseif ($inquiry->professional)
                                <span class="text-gray-900">
                                    {{ Str::headline($inquiry->professional->type?->value ?? '') }}
                                    @if ($inquiry->professional->registration_number)
                                        ({{ $inquiry->professional->registration_number }})
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-500">{{ __('General inquiry') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Submitted by') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $inquiry->name }}</dd>
                    </div>

                    @if ($isRecipient)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Phone') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $inquiry->phone }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $inquiry->email }}</dd>
                        </div>
                    @endif

                    @if ($inquiry->preferred_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Preferred date') }}</dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $inquiry->preferred_date->format('j F Y') }}
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Message') }}</dt>
                        <dd class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $inquiry->message }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Submitted') }}</dt>
                        <dd class="mt-1 text-gray-500">
                            {{ $inquiry->created_at?->format('j F Y, g:i A') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </x-card>

        @if ($isRecipient)
            <x-card class="mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-4">
                        @foreach ($inquiry->status->allowedTransitions() as $transition)
                            <form method="POST" action="{{ route('inquiries.status.update', $inquiry) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $transition->value }}">

                                <x-button
                                    type="submit"
                                    :variant="$transition === \App\Enums\InquiryStatus::Closed ? 'secondary' : 'primary'"
                                    class="min-h-[44px]"
                                >
                                    @if ($transition === \App\Enums\InquiryStatus::Contacted)
                                        {{ $inquiry->status === \App\Enums\InquiryStatus::Closed ? __('Reopen') : __('Mark as Contacted') }}
                                    @else
                                        {{ $inquiry->status === \App\Enums\InquiryStatus::New ? __('Close inquiry') : __('Mark as Closed') }}
                                    @endif
                                </x-button>
                            </form>
                        @endforeach

                        @if ($whatsappLink)
                            <a href="{{ $whatsappLink }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-md px-4 py-2 bg-green-600 border border-transparent text-white text-sm font-semibold hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.674zm-2.97 12.837c-1.163 0-2.219-.149-3.272-.447-2.326-.67-4.124-2.415-5.212-4.393-.332-.612-.272-1.292.074-1.87.347-.58.94-1.593 1.482-2.167.53-.56 1.224-.855 1.947-.917.723-.062 1.482.163 2.107.558.625.394 1.13.828 1.48 1.213.335.372.52.73.57.987.05.242-.098.486-.333.92-.378.692-.757 1.338-1.04 2.06-.283.719-.447 1.462-.52 2.277-.073.781-.072 1.576-.072 2.403 0 .83.072 1.64.146 2.47.164 2.24 2.46 3.998 5.27 4.59 1.123.227 2.276.227 3.43.074 1.162-.15 2.22-.61 3.17-1.37.868-.688 1.444-1.575 1.727-2.73.282-1.154.148-2.34-.396-3.42-.543-1.08-1.435-2.117-2.604-3.07-.76-.633-1.59-1.266-2.502-1.87-.912-.604-1.893-1.208-2.965-1.81zm0 0"/>
                                </svg>
                                {{ __('Reply on WhatsApp') }}
                            </a>
                        @endif
                    </div>
                </div>
            </x-card>
        @endif

        <a href="{{ route('inquiries.index') }}"
            class="inline-flex min-h-[44px] items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
            {{ __('Back to inquiries') }}
        </a>
    </div>
</x-app-layout>