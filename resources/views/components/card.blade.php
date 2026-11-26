@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'border border-border bg-surface shadow-sm',
        'muted' => 'border border-border bg-surface-muted',
    ];

    $classes = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl {$classes}"]) }}>
    {{ $slot }}
</div>
