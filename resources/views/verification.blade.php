<x-public-layout :title="__('How verification works')">
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-surface shadow rounded-lg p-8 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-text mb-6">{{ __('How verification works') }}</h1>
        <p class="mt-2 text-text-muted">
            {{ __('Every listing on our platform is verified before it goes live. Here is the end-to-end workflow, from submission to decision.') }}
        </p>

        <ol class="mt-6 bg-surface overflow-hidden shadow-sm sm:rounded-lg divide-y divide-border">
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-brand-700 text-white text-sm font-semibold">1</span>
                    <div>
                        <h2 class="text-lg font-semibold text-text">{{ __('Submission') }}</h2>
                        <p class="mt-2 text-text">{{ __('The company submits a listing with ownership details. The listing starts in the pending state.') }}</p>
                    </div>
                </div>
            </li>
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-brand-700 text-white text-sm font-semibold">2</span>
                    <div>
                        <h2 class="text-lg font-semibold text-text">{{ __('Under review') }}</h2>
                        <p class="mt-2 text-text">{{ __('A verifier moves the listing into the in_review state and starts the checklist.') }}</p>
                    </div>
                </div>
            </li>
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-brand-700 text-white text-sm font-semibold">3</span>
                    <div>
                        <h2 class="text-lg font-semibold text-text">{{ __('Checklist') }}</h2>
                        <p class="mt-2 text-text">{{ __('The verifier works through the five checks: title document, survey plan, ownership proof, site inspection and no encumbrance.') }}</p>
                        <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach (\App\Enums\CheckKey::values() as $checkKey)
                                <li class="text-sm text-text-muted">{{ $checkKey }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </li>
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-brand-700 text-white text-sm font-semibold">4</span>
                    <div>
                        <h2 class="text-lg font-semibold text-text">{{ __('Decision') }}</h2>
                        <p class="mt-2 text-text">{{ __('All checks passed marks the listing as verified. Any failed check marks it as rejected and records who reviewed it and when.') }}</p>
                    </div>
                </div>
            </li>
        </ol>

        <div class="mt-6 bg-surface overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-text">{{ __('What happens next') }}</h2>
                <p class="mt-2 text-text">
                    {{ __('Verified listings are published to customers immediately. Listings that fail review stay hidden until the required checks are resolved and re-submitted.') }}
                </p>
                <p class="mt-4">
                    <a href="{{ route('listings.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-800 focus:bg-brand-800 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Browse verified listings') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
</x-public-layout>