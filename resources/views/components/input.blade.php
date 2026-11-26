@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
])

@php
    $fieldId = $id ?? $name;
    $inputClasses = 'mt-1 block w-full min-h-[44px] rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand';
@endphp

@if ($label)
    <label @if ($fieldId) for="{{ $fieldId }}" @endif class="block text-sm font-medium text-text">
        {{ $label }}
    </label>
@endif

<input
    type="{{ $type }}"
    @if ($name) name="{{ $name }}" @endif
    @if ($fieldId) id="{{ $fieldId }}" @endif
    {{ $attributes->merge(['class' => $inputClasses]) }}
/>
