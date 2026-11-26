@props(['variant' => 'menu'])

@php
    $options = [
        ['light', __('Light')],
        ['dark', __('Dark')],
        ['system', __('System')],
    ];

    $compact = in_array($variant, ['menu', 'mobile', 'footer'], true);
@endphp

<div
    x-data="{
        theme: window.theme ? window.theme.get() : 'system',
        choose(value) {
            this.theme = value;

            if (window.theme) {
                window.theme.set(value);
            }
        }
    }"
    x-on:theme-applied.window="theme = $event.detail.theme"
    role="group"
    aria-label="{{ __('Theme') }}"
    class="{{ $compact ? 'inline-flex flex-wrap items-center gap-1 rounded-md border border-border bg-surface p-1' : 'inline-flex flex-wrap gap-2' }}"
>
    @foreach ($options as [$value, $label])
        <button
            type="button"
            aria-pressed="{{ $value === 'system' ? 'true' : 'false' }}"
            x-bind:aria-pressed="theme === '{{ $value }}' ? 'true' : 'false'"
            x-on:click="choose('{{ $value }}')"
            class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md px-3 py-2 text-sm font-medium text-text-muted transition hover:bg-surface-muted hover:text-text focus:outline-none focus:ring-2 focus:ring-brand-500"
        >
            {{ $label }}
        </button>
    @endforeach
</div>
