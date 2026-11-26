<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verification Queue') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($listings->isNotEmpty())
                        <ul class="divide-y divide-gray-200">
                            @foreach ($listings as $listing)
                                @php
                                    $checksByKey = $listing->verificationChecks
                                        ->mapWithKeys(fn ($check) => [$check->check_key?->value => $check])
                                        ->all();
                                @endphp
                                <li class="p-6">
                                    <div class="flex justify-between">
                                        <div>
                                            <a href="{{ route('listings.show', $listing->slug) }}" class="text-lg font-medium text-gray-900 hover:text-brand-600">
                                                {{ $listing->title }}
                                            </a>
                                            <div class="text-sm text-gray-500">
                                                {{ $listing->company?->name ?? config('app.name') }} · {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}
                                            </div>
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                    {{ $listing->verification_status?->value }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <form method="POST" action="{{ route('verify.checks.update', $listing->id) }}">
                                                @csrf
                                                <select name="check_key" class="border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full">
                                                    @foreach (\App\Enums\CheckKey::values() as $checkKey)
                                                        <option value="{{ $checkKey }}" @selected(old('check_key', ($checksByKey[$checkKey] ?? null)?->check_key?->value) === $checkKey)>
                                                            {{ $checkKey }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <select name="status" class="mt-2 border-gray-300 focus:border-brand-500 rounded-md shadow-sm block w-full">
                                                    <option value="">Status</option>
                                                    <option value="passed">Passed</option>
                                                    <option value="failed">Failed</option>
                                                </select>

                                                <button type="submit" class="mt-3 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    {{ __('Update') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-6 text-gray-900">
                            {{ __('No listings awaiting verification.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
