@use('Illuminate\Support\Str')

<x-public-layout :title="__('Partners')">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Verified professionals') }}</h1>
        <p class="mt-2 text-gray-600">
            {{ __('Lawyers, architects, engineers and other professionals whose credentials we have checked.') }}
        </p>

        <form method="GET" action="{{ route('professionals.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="type" class="block text-sm font-medium text-gray-700">{{ __('Specialism') }}</label>
                <select
                    name="type"
                    id="type"
                    class="mt-1 block w-full min-h-[44px] rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                >
                    <option value="">{{ __('All professionals') }}</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected($currentType === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <x-button type="submit">{{ __('Filter') }}</x-button>

            @if ($currentType)
                <a
                    href="{{ route('professionals.index') }}"
                    class="inline-flex min-h-[44px] items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    {{ __('Clear') }}
                </a>
            @endif
        </form>

        @if ($professionals->isEmpty())
            <x-empty-state class="mt-6" :title="__('No professionals found')">
                {{ __('No verified professionals match your current filter.') }}
            </x-empty-state>
        @else
            <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($professionals as $professional)
                    <li class="flex flex-col bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="flex-1 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    <a href="{{ route('professionals.show', $professional->slug) }}" class="hover:text-brand-700">
                                        {{ $professional->user?->name ?? __('Professional') }}
                                    </a>
                                </h2>
                                <x-badge variant="success">{{ __('Verified') }}</x-badge>
                            </div>

                            <p class="mt-1 text-sm text-gray-600">{{ $professional->type?->label() }}</p>

                            @if ($professional->company)
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ __('Company') }}:
                                    <a href="{{ route('companies.show', $professional->company->slug) }}" class="text-brand-700 hover:underline">
                                        {{ $professional->company->name }}
                                    </a>
                                </p>
                            @endif

                            @if ($professional->bio)
                                <p class="mt-3 text-sm text-gray-600">{{ Str::limit($professional->bio, 140) }}</p>
                            @endif
                        </div>

                        <div class="border-t border-gray-100 px-5 py-3">
                            <a
                                href="{{ route('professionals.show', $professional->slug) }}"
                                class="inline-flex min-h-[44px] items-center text-sm font-semibold text-brand-700 hover:text-brand-900"
                            >
                                {{ __('View profile') }}
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-public-layout>
