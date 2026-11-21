<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="__('Inquiries')" :subtitle="__('Manage and track all your inquiries.')" />
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <x-card>
            <div class="p-6">
                <form method="GET" class="flex flex-wrap items-end gap-4 mb-6">
                    <div class="flex-1 min-w-[200px]">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Filter by status') }}
                        </label>
                        <select name="status" id="status"
                            class="w-full min-h-[44px] rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}"
                                    @selected($currentStatus === $status->value)>
                                    {{ Str::headline($status->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-button type="submit" class="min-h-[44px]">
                        {{ __('Filter') }}
                    </x-button>

                    @if ($currentStatus)
                        <a href="{{ route('inquiries.index') }}"
                            class="inline-flex min-h-[44px] items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            {{ __('Clear') }}
                        </a>
                    @endif
                </form>

                @if ($inquiries->isEmpty())
                    <x-empty-state :title="__('No inquiries found')"
                        :description="__('No inquiries match your current filter.')" />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('Type') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('Listing / Professional') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('Submitted by') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('Date') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($inquiries as $inquiry)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <x-badge :variant="$inquiry->type === 'consultation' ? 'brand' : 'info'">
                                                {{ Str::headline($inquiry->type->value) }}
                                            </x-badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($inquiry->listing)
                                                <a href="{{ route('listings.show', $inquiry->listing->slug) }}"
                                                    class="text-brand-700 hover:underline">
                                                    {{ $inquiry->listing->title }}
                                                </a>
                                            @elseif ($inquiry->professional)
                                                <span class="text-gray-900">
                                                    {{ Str::headline($inquiry->professional->type?->value ?? '') }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">{{ __('General inquiry') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-900">
                                            {{ $inquiry->name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                                            {{ $inquiry->created_at?->format('j M Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-badge :variant="$inquiry->status->badgeVariant()">
                                                {{ Str::headline($inquiry->status->value) }}
                                            </x-badge>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('inquiries.show', $inquiry) }}"
                                                class="inline-flex min-h-[44px] items-center px-3 py-1.5 text-sm font-medium text-brand-700 hover:text-brand-900">
                                                {{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $inquiries->links() }}
                    </div>
                @endif
            </div>
        </x-card>
    </div>
</x-app-layout>