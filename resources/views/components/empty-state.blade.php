@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center']) }}>
    @if ($title)
        <p class="text-base font-semibold text-gray-900">{{ $title }}</p>
    @endif

    <div class="mt-1 text-sm text-gray-600">
        {{ $slot }}
    </div>
</div>
