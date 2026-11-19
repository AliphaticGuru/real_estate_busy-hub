@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
])

@php
    $fieldId = $id ?? $name;
    $inputClasses = 'mt-1 block w-full min-h-[44px] rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

@if ($label)
    <label @if ($fieldId) for="{{ $fieldId }}" @endif class="block text-sm font-medium text-gray-700">
        {{ $label }}
    </label>
@endif

<input
    type="{{ $type }}"
    @if ($name) name="{{ $name }}" @endif
    @if ($fieldId) id="{{ $fieldId }}" @endif
    {{ $attributes->merge(['class' => $inputClasses]) }}
/>
