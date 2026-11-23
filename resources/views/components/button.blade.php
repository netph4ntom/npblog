@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
    'type' => 'button',
])

@php
$baseClasses = 'inline-flex items-center justify-center font-ui font-normal transition-all sketch-border sketch-shadow sketch-radius-b sketch-btn-press focus-visible:outline-none disabled:opacity-50 disabled:pointer-events-none';

$sizeClasses = match($size) {
    'sm' => 'px-3 py-1.5 text-sm min-h-[38px]',
    'lg' => 'px-6 py-3 text-lg min-h-[48px]',
    default => 'px-4 py-2 text-base min-h-[44px]',
};

$variantClasses = match($variant) {
    'secondary' => 'bg-surface text-text hover:bg-bg',
    'danger' => 'bg-danger text-white hover:opacity-90',
    'ghost' => 'bg-transparent text-text hover:bg-surface border-transparent shadow-none',
    default => 'bg-accent-dark text-on-accent hover:opacity-95',
};

$classes = "$baseClasses $sizeClasses $variantClasses";
@endphp

@if($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

