@props([
    'variant' => 'primary',
    'type' => 'submit',
])

@php
    $variants = [
        'primary' => 'border border-transparent bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-500 active:bg-brand-800',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-brand-500',
        'danger' => 'border border-transparent bg-red-600 text-white hover:bg-red-500 focus:ring-red-500 active:bg-red-700',
        'ghost' => 'border border-transparent bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-brand-500',
    ];

    $classes = $variants[$variant] ?? $variants['primary'];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex min-h-[44px] items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 {$classes}"]) }}
>
    {{ $slot }}
</button>
