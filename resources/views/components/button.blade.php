@props([
    'variant' => 'primary',
    'type' => 'submit',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'border border-transparent bg-brand-700 text-white hover:bg-brand-800 focus:ring-brand active:bg-brand-800',
        'secondary' => 'border border-border bg-surface text-text shadow-sm hover:bg-surface-muted focus:ring-brand',
        'danger' => 'border border-transparent bg-red-600 text-white hover:bg-red-500 focus:ring-red-500 active:bg-red-700',
        'ghost' => 'border border-transparent bg-transparent text-text hover:bg-surface-muted focus:ring-brand',
    ];

    $classes = $variants[$variant] ?? $variants['primary'];
    $base = "inline-flex min-h-[44px] items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 {$classes}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base]) }}>
        {{ $slot }}
    </button>
@endif
