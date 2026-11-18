<x-public-layout :title="__('How verification works')">
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg p-8 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('How verification works') }}</h1>
        <p class="mt-2 text-gray-600">
            {{ __('Every listing on our platform is verified before it goes live. Here is the end-to-end workflow, from submission to decision.') }}
        </p>

        <ol class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-200">
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-gray-900 text-white text-sm font-semibold">1</span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Submission') }}</h2>
                        <p class="mt-2 text-gray-700">{{ __('The company submits a listing with ownership details. The listing starts in the pending state.') }}</p>
                    </div>
                </div>
            </li>
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-gray-900 text-white text-sm font-semibold">2</span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Under review') }}</h2>
                        <p class="mt-2 text-gray-700">{{ __('A verifier moves the listing into the in_review state and starts the checklist.') }}</p>
                    </div>
                </div>
            </li>
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-gray-900 text-white text-sm font-semibold">3</span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Checklist') }}</h2>
                        <p class="mt-2 text-gray-700">{{ __('The verifier works through the five checks: title document, survey plan, ownership proof, site inspection and no encumbrance.') }}</p>
                        <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach (\App\Enums\CheckKey::values() as $checkKey)
                                <li class="text-sm text-gray-600">{{ $checkKey }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </li>
            <li class="p-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center size-8 rounded-full bg-gray-900 text-white text-sm font-semibold">4</span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Decision') }}</h2>
                        <p class="mt-2 text-gray-700">{{ __('All checks passed marks the listing as verified. Any failed check marks it as rejected and records who reviewed it and when.') }}</p>
                    </div>
                </div>
            </li>
        </ol>

        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('What happens next') }}</h2>
                <p class="mt-2 text-gray-700">
                    {{ __('Verified listings are published to customers immediately. Listings that fail review stay hidden until the required checks are resolved and re-submitted.') }}
                </p>
                <p class="mt-4">
                    <a href="{{ route('listings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Browse verified listings') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
</x-public-layout>