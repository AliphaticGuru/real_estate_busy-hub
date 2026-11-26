@props(['variant' => 'nav'])

<div
    x-data="{
        theme: window.theme ? window.theme.get() : 'system',
        labels: { light: '{{ __('Light') }}', dark: '{{ __('Dark') }}', system: '{{ __('System') }}' },
        next: { light: 'dark', dark: 'system', system: 'light' },
        get label() {
            return this.labels[this.theme] || this.labels.system;
        },
        cycle() {
            this.theme = this.next[this.theme] || 'system';

            if (window.theme) {
                window.theme.set(this.theme);
            }
        }
    }"
    x-on:theme-applied.window="theme = $event.detail.theme"
    @click.stop
    class="inline-flex items-center {{ $variant === 'settings' ? 'gap-2' : '' }}"
>
    <button
        type="button"
        data-theme-button
        x-bind:aria-label="'{{ __('Theme') }}: ' + label + '. ' + '{{ __('Activate to change') }}'"
        aria-label="{{ __('Theme') }}: {{ __('System') }}. {{ __('Activate to change') }}"
        x-on:click="cycle()"
        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md border border-border bg-surface text-text-muted transition hover:bg-surface-muted hover:text-text focus:outline-none focus:ring-2 focus:ring-brand-500"
    >
        <span x-show="theme === 'light'" x-cloak class="relative flex h-5 w-5 items-center justify-center" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                <circle cx="12" cy="12" r="9" fill="#ffffff" stroke="#6b7280" stroke-width="2" />
            </svg>
        </span>

        <span x-show="theme === 'dark'" x-cloak class="relative flex h-5 w-5 items-center justify-center" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                <circle cx="12" cy="12" r="9" fill="#000000" stroke="#6b7280" stroke-width="2" />
            </svg>
        </span>

        <span x-show="theme === 'system'" x-cloak class="relative flex h-5 w-5 items-center justify-center" aria-hidden="true">
            <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                <circle cx="12" cy="12" r="9" fill="#ffffff" stroke="#6b7280" stroke-width="2" />
                <path d="M12 3a9 9 0 0 1 0 18z" fill="#000000" />
            </svg>
        </span>
    </button>

    @if ($variant === 'settings')
        <span class="text-sm text-text" x-text="label">{{ __('System') }}</span>
    @endif
</div>
