@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-border bg-surface text-text shadow-sm placeholder:text-text-muted focus:border-brand focus:ring-brand']) }}>
