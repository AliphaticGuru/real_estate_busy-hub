@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-border bg-surface px-6 py-10 text-center']) }}>
    @if ($title)
        <p class="text-base font-semibold text-text">{{ $title }}</p>
    @endif

    <div class="mt-1 text-sm text-text-muted">
        {{ $slot }}
    </div>
</div>
