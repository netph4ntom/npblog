@props([
    'type' => 'published',
])

@php
$typeClasses = match($type) {
    'draft' => 'bg-warning-bg text-warning-text border-warning-text',
    'danger' => 'bg-danger/10 text-danger border-danger',
    'warning' => 'bg-warning-bg text-warning-text border-warning-text',
    default => 'bg-accent/20 text-accent-dark dark:text-accent border-accent-dark dark:border-accent',
};

$classes = "inline-flex items-center px-2.5 py-1 font-ui text-xs font-semibold rounded-md border $typeClasses";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
