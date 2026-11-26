<x-public-layout :title="__('Contact')">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-text">{{ __('Contact') }}</h1>

        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
            <p class="font-semibold">{{ __('Draft') }}</p>
            <p class="mt-1">{{ __('Draft: pending review') }}</p>
        </div>

        <section class="mt-8">
            <p class="text-base leading-relaxed text-text-muted">
                {{ __('Questions about a listing, a consultation request or an inspection can be sent to the team before a partner reply arrives.') }}
            </p>

            @php
                $email = config('services.contact.email');
                $whatsapp = config('services.whatsapp.number');
                $whatsappLink = $whatsapp
                    ? \App\Services\WhatsApp::link($whatsapp, __('Hello Verifyhaus Afrika, I would like to know more about a verified listing or professional.'))
                    : null;
            @endphp

            @if ($email)
                <p class="mt-6">
                    <span class="font-semibold text-text">{{ __('Email') }}: </span>
                    <a href="mailto:{{ $email }}" class="text-brand hover:underline">{{ $email }}</a>
                </p>
            @endif

            @if ($whatsappLink)
                <p class="mt-2">
                    <span class="font-semibold text-text">{{ __('WhatsApp') }}: </span>
                    <a href="{{ $whatsappLink }}" class="text-brand hover:underline">{{ __('Chat on WhatsApp') }}</a>
                </p>
            @endif

            @if (! $email && ! $whatsapp)
                <p class="mt-6 text-text-muted">
                    {{ __('Contact details coming soon') }}
                </p>
            @endif
        </section>
    </div>
</x-public-layout>
