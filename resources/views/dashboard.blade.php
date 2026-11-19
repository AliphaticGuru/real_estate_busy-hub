@use('App\Enums\RoleName')
@use('Illuminate\Support\Str')

@php
    $badgeVariant = fn (?string $status): string => match ($status) {
        'verified', 'published', 'closed' => 'success',
        'in_review', 'contacted' => 'warning',
        'rejected' => 'danger',
        default => 'neutral',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$data['title']" :subtitle="$data['subtitle']" />
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            {{-- Headline counts --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($data['stats'] as $stat)
                    <x-card class="p-5">
                        <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900">{{ $stat['value'] }}</p>
                    </x-card>
                @endforeach
            </div>

            {{-- Quick actions --}}
            @if (! empty($data['links']))
                <x-card class="p-5">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('Quick actions') }}</h2>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        @foreach ($data['links'] as $link)
                            @if ($link['enabled'])
                                <a
                                    href="{{ $link['url'] }}"
                                    class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                                >
                                    {{ $link['label'] }}
                                </a>
                            @else
                                <span
                                    aria-disabled="true"
                                    class="inline-flex min-h-[44px] cursor-not-allowed items-center justify-center gap-2 rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-500"
                                >
                                    {{ $link['label'] }}
                                    <x-badge variant="placeholder">{{ __('Coming soon') }}</x-badge>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </x-card>
            @endif

            {{-- Status breakdowns --}}
            @foreach ($data['breakdowns'] as $breakdown)
                <x-card class="p-5">
                    <h2 class="text-base font-semibold text-gray-900">{{ $breakdown['title'] }}</h2>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($breakdown['items'] as $item)
                            <div class="rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-sm text-gray-500">{{ $item['label'] }}</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $item['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-card>
            @endforeach

            {{-- Verifier: oldest listings waiting for review --}}
            @if ($data['role'] === RoleName::Verifier)
                <x-card class="overflow-hidden">
                    <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-base font-semibold text-gray-900">{{ __('Oldest waiting listings') }}</h2>
                        <a href="{{ route('verify.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">
                            {{ __('Open verification queue') }}
                        </a>
                    </div>

                    @if ($data['recentListings']->isEmpty())
                        <div class="p-5">
                            <x-empty-state :title="__('Nothing waiting')">
                                {{ __('No listings are pending or in review right now.') }}
                            </x-empty-state>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($data['recentListings'] as $listing)
                                <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <a href="{{ route('listings.show', $listing->slug) }}" class="text-sm font-semibold text-gray-900 hover:text-brand-700">
                                            {{ $listing->title }}
                                        </a>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $listing->company?->name ?? config('app.name') }}
                                            · {{ $listing->created_at?->format('j M Y') }}
                                        </p>
                                    </div>

                                    <x-badge :variant="$badgeVariant($listing->verification_status?->value)">
                                        {{ Str::headline($listing->verification_status?->value ?? '') }}
                                    </x-badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            @endif

            {{-- Company, professional and customer inquiries --}}
            @if (in_array($data['role'], [RoleName::Company, RoleName::Professional, RoleName::Customer], true))
                <x-card class="overflow-hidden">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">{{ __('Recent inquiries') }}</h2>
                    </div>

                    @if ($data['recentInquiries']->isEmpty())
                        <div class="p-5">
                            <x-empty-state :title="__('No inquiries yet')">
                                {{ __('Inquiries will appear here as soon as they are submitted.') }}
                            </x-empty-state>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($data['recentInquiries'] as $inquiry)
                                <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $inquiry->name }}
                                            <span class="font-normal text-gray-500">
                                                · {{ Str::headline($inquiry->type?->value ?? '') }}
                                            </span>
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            @if ($inquiry->listing)
                                                <a href="{{ route('listings.show', $inquiry->listing->slug) }}" class="hover:text-brand-700">
                                                    {{ $inquiry->listing->title }}
                                                </a>
                                            @elseif ($inquiry->professional)
                                                {{ Str::headline($inquiry->professional->type?->value ?? '') }}
                                            @else
                                                {{ __('General inquiry') }}
                                            @endif
                                            · {{ $inquiry->created_at?->format('j M Y') }}
                                        </p>
                                    </div>

                                    <x-badge :variant="$badgeVariant($inquiry->status?->value)">
                                        {{ Str::headline($inquiry->status?->value ?? '') }}
                                    </x-badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            @endif

            {{-- Super admin: recent audit activity --}}
            @if ($data['role'] === RoleName::SuperAdmin)
                <x-card class="overflow-hidden">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h2 class="text-base font-semibold text-gray-900">{{ __('Recent audit activity') }}</h2>
                    </div>

                    @if ($data['recentAuditLogs']->isEmpty())
                        <div class="p-5">
                            <x-empty-state :title="__('No audit entries yet')">
                                {{ __('Verification and listing changes will be recorded here.') }}
                            </x-empty-state>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($data['recentAuditLogs'] as $log)
                                <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ Str::headline(str_replace('.', ' ', $log->action)) }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $log->user?->name ?? __('System') }}
                                            · {{ $log->created_at?->format('j M Y H:i') }}
                                        </p>
                                    </div>

                                    @if (! empty($log->new_values['verification_status']))
                                        <x-badge :variant="$badgeVariant($log->new_values['verification_status'])">
                                            {{ Str::headline($log->new_values['verification_status']) }}
                                        </x-badge>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>
