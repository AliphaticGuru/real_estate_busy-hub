<x-public-layout :title="__('Feedback')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Feedback') }}</h1>

        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-semibold">{{ __('Draft') }}</p>
            <p class="mt-1">{{ __('Draft: pending review') }}</p>
        </div>

        <section class="mt-8">
            <p class="text-base leading-relaxed text-gray-600">
                {{ __('Feedback about the hub, a listing or a professional can be sent to the same contact details used for the contact page. A feedback form will be added later.') }}
            </p>

            @php
                $email = config('services.contact.email');
                $whatsapp = config('services.whatsapp.number');
            @endphp

            @if ($email)
                <p class="mt-4">
                    <a href="mailto:{{ $email }}" class="text-brand-700 hover:underline">{{ __('Send us an email') }}: {{ $email }}</a>
                </p>
            @endif

            @if ($whatsapp)
                <p class="mt-2">
                    <a href="https://wa.me/{{ $whatsapp }}?text={{ urlencode(__('Hello Verifyhaus Afrika, I would like to share feedback.')) }}" class="text-brand-700 hover:underline">{{ __('Chat on WhatsApp') }}</a>
                </p>
            @endif

            @if (! $email && ! $whatsapp)
                <p class="mt-4 text-gray-600">
                    {{ __('Contact details coming soon') }}
                </p>
            @endif
        </section>
    </div>
</x-public-layout>
