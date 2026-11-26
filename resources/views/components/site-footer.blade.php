<footer class="mt-auto py-10 text-center text-sm text-gray-500">
    <p class="text-gray-600">
        {{ config('app.name') }} · {{ __('Find verified real estate & construction partners in Abuja') }}
    </p>

    <div class="mt-8 flex flex-row flex-wrap items-center justify-center gap-8">
        <nav class="flex flex-col items-center gap-2" aria-label="{{ __('Company') }}">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">{{ __('Company') }}</p>
            <div class="flex flex-col items-center gap-2 sm:flex-row sm:gap-4">
                <a href="{{ route('mission') }}" class="text-brand-700 hover:underline">{{ __('Mission') }}</a>
                <a href="{{ route('vision') }}" class="text-brand-700 hover:underline">{{ __('Vision') }}</a>
                <a href="{{ route('blog.index') }}" class="text-brand-700 hover:underline">{{ __('Blog') }}</a>
                <a href="{{ route('services') }}" class="text-brand-700 hover:underline">{{ __('Services') }}</a>
                <a href="{{ route('professionals.index') }}" class="text-brand-700 hover:underline">{{ __('Partners') }}</a>
            </div>
        </nav>

        <nav class="flex flex-col items-center gap-2" aria-label="{{ __('Support') }}">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">{{ __('Support') }}</p>
            <div class="flex flex-col items-center gap-2 sm:flex-row sm:gap-4">
                <a href="{{ route('about') }}" class="text-brand-700 hover:underline">{{ __('About') }}</a>
                <a href="{{ route('contact') }}" class="text-brand-700 hover:underline">{{ __('Contact') }}</a>
                <a href="{{ route('feedback') }}" class="text-brand-700 hover:underline">{{ __('Feedback') }}</a>
            </div>
        </nav>
    </div>

    <nav class="mt-8 flex flex-wrap items-center justify-center gap-x-4 gap-y-2" aria-label="{{ __('Legal') }}">
        <a href="{{ route('privacy.index') }}" class="text-brand-70000 hover:underline">{{ __('Privacy') }}</a>
        <a href="{{ route('terms.index') }}" class="text-brand-70000 hover:underline">{{ __('Terms') }}</a>
        <a href="{{ route('complaints.index') }}" class="text-brand-70000 hover:underline">{{ __('Complaints') }}</a>
    </nav>

    <p class="mt-6 text-gray-400">
        {{ __('Copyright') }} {{ now()->year }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
    </p>
</footer>
