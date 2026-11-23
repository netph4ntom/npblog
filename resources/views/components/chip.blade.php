@props([
    'href' => null,
    'active' => false,
])

@php
$baseClasses = 'inline-flex items-center px-3.5 py-1.5 font-ui text-sm transition-all sketch-border sketch-shadow-sm min-h-[44px] ' .
    ($active
        ? 'bg-accent text-on-accent border-ink font-bold'
        : 'bg-surface text-text hover:bg-bg border-ink');
$radius = 'rounded-full';
$classes = "$baseClasses $radius";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </span>
@endif

