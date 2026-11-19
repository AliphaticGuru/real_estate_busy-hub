@props(['variant' => 'neutral'])

@php
    $variants = [
        'neutral' => 'bg-gray-100 text-gray-700 ring-gray-200',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-200',
        'success' => 'bg-green-50 text-green-700 ring-green-200',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'danger' => 'bg-red-50 text-red-700 ring-red-200',
        'placeholder' => 'border border-dashed border-amber-300 bg-amber-50 text-amber-800',
    ];

    $classes = $variants[$variant] ?? $variants['neutral'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide ring-1 ring-inset {$classes}"]) }}>
    {{ $slot }}
</span>
