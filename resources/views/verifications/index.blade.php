<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-text leading-tight">
            {{ __('Verification Queue') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($listings->isNotEmpty())
                        <ul class="divide-y divide-border">
                            @foreach ($listings as $listing)
                                @php
                                    $checksByKey = $listing->verificationChecks
                                        ->mapWithKeys(fn ($check) => [$check->check_key?->value => $check])
                                        ->all();
                                @endphp
                                <li class="p-6">
                                    <div class="flex justify-between">
                                        <div>
                                            <a href="{{ route('listings.show', $listing->slug) }}" class="text-lg font-medium text-text hover:text-brand">
                                                {{ $listing->title }}
                                            </a>
                                            <div class="text-sm text-text-muted">
                                                {{ $listing->company?->name ?? config('app.name') }} · {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}
                                            </div>
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-muted text-text">
                                                    {{ $listing->verification_status?->value }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <form method="POST" action="{{ route('verify.checks.update', $listing->id) }}">
                                                @csrf
                                                <select name="check_key" class="block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand">
                                                    @foreach (\App\Enums\CheckKey::values() as $checkKey)
                                                        <option value="{{ $checkKey }}" @selected(old('check_key', ($checksByKey[$checkKey] ?? null)?->check_key?->value) === $checkKey)>
                                                            {{ $checkKey }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <select name="status" class="mt-2 block w-full rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand">
                                                    <option value="">Status</option>
                                                    <option value="passed">Passed</option>
                                                    <option value="failed">Failed</option>
                                                </select>

                                                <button type="submit" class="mt-3 inline-flex items-center px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-800 focus:bg-brand-800 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150">
                                                    {{ __('Update') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-6 text-text">
                            {{ __('No listings awaiting verification.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
