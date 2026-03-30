@props([
    'horizontal' => false,
    'hidden' => false,
])

<div
    {{ $attributes->class([
        $horizontal ? 'w-full h-px' : 'self-stretch w-px',
        'bg-neutral-800/10 dark:bg-white/10' => !$hidden,
    ]) }}
    data-slot="separator"
></div>
