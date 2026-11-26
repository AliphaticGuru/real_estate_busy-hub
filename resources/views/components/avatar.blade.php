@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-base',
        'lg' => 'h-14 w-14 text-lg',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $initial = $user->name
        ? strtoupper(substr($user->name, 0, 1))
        : strtoupper(substr($user->email, 0, 1));

    $label = $user->name ?: $user->email;

    $palette = [
        '#1E3A8A',
        '#7C2D12',
        '#14532D',
        '#78350F',
        '#831843',
        '#4C1D95',
        '#0F172A',
        '#7F1D1D',
        '#155E75',
        '#1E293B',
    ];
    $index = hexdec(hash('crc32b', (string) $user->id)) % count($palette);
    $backgroundColor = $palette[$index];
@endphp

<span
    {{ $attributes->merge(['class' => "avatar inline-flex items-center justify-center rounded-full text-white font-semibold select-none {$sizeClass}"]) }}
    style="background-color: {{ $backgroundColor }};"
    role="img"
    aria-label="{{ $label }}"
>
    <span aria-hidden="true" class="flex h-full w-full items-center justify-center">
        {{ $initial }}
    </span>
</span>
