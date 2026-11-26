<x-public-layout :title="__('Fees')">
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-surface shadow rounded-lg p-8 max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold text-text mb-6">Fees</h2>
        <p class="text-text-muted leading-relaxed">
            {{ __('Transparent fees for verification, professional consultancy and inspections. Final amounts are agreed directly with the partner before work begins.') }}
        </p>

        <div class="mt-6 grid gap-6">
            <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-text">{{ __('Listing verification fee') }}</h2>
                    <p class="mt-2 text-text">
                        {{ __('Covers the full verification workflow for a single listing: title document, survey plan, ownership proof, site inspection and no-encumbrance checks. The fee is payable by the buyer once the verified listing is confirmed.') }}
                    </p>
                    <p class="mt-4 text-2xl font-bold text-text">₦25,000</p>
                </div>
            </div>

            <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-text">{{ __('Professional consultancy fee') }}</h2>
                    <p class="mt-2 text-text">
                        {{ __('Consultation or legal due diligence with a lawyer, architect, engineer or surveyor. Professional fees are quoted per engagement and agreed before the consultation starts.') }}
                    </p>
                    <p class="mt-4 text-2xl font-bold text-text">From ₦75,000</p>
                </div>
            </div>

            <div class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-text">{{ __('Site inspection fee') }}</h2>
                    <p class="mt-2 text-text">
                        {{ __('On-site inspection for inspection requests. Travel and logistics are included in the quoted amount.') }}
                    </p>
                    <p class="mt-4 text-2xl font-bold text-text">From ₦50,000</p>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-surface overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-text">{{ __('Payment and next steps') }}</h2>
                <p class="mt-2 text-text">
                    {{ __('Fees are settled directly between the customer and the partner. Once payment is confirmed, the partner proceeds with the verification workflow described on the verification page.') }}
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