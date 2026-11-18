<!-- input.blade.php -->
<label {{ $attrs }} class="block mb-2">
    {{ $label }}
</label>
<input type="{{ $type ?? 'text' }}" {{ $attrs }} class="input" />