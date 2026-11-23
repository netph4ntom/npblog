@props([
    'variant' => 'a',
    'as' => 'div',
    'hover' => false,
])

@php
$radiusClass = match($variant) {
    'b' => 'sketch-radius-b',
    'c' => 'sketch-radius-c',
    default => 'sketch-radius-a',
};

$classes = 'sketch-border bg-surface text-text sketch-shadow ' . $radiusClass . ($hover ? ' sketch-shadow-hover' : '');
@endphp

<{{ $as }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $as }}>
