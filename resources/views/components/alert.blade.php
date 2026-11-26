@props(['variant' => 'status'])

@php
    $variants = [
        'status' => 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-800 dark:bg-brand-950 dark:text-brand-200',
        'success' => 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200',
        'error' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200',
        'info' => 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-800 dark:bg-sky-950 dark:text-sky-200',
    ];

    $classes = $variants[$variant] ?? $variants['status'];
@endphp

<div role="alert" {{ $attributes->merge(['class' => "rounded-md border px-4 py-3 text-sm {$classes}"]) }}>
    {{ $slot }}
</div>
