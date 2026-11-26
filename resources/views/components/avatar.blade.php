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

    $backgroundColor = \App\Services\AvatarPalette::colorFor($user);
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
