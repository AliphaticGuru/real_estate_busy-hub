@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    <h1 class="text-xl font-semibold leading-tight text-gray-900">
        {{ $title ?? $slot }}
    </h1>

    @if ($subtitle)
        <p class="text-sm text-gray-600">{{ $subtitle }}</p>
    @endif
</div>
