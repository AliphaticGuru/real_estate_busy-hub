@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'border border-gray-100 bg-white shadow-sm',
        'muted' => 'border border-gray-200 bg-gray-50',
    ];

    $classes = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl {$classes}"]) }}>
    {{ $slot }}
</div>
