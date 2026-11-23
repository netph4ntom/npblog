@props([
    'title',
    'value',
    'variant' => 'a',
    'description' => null,
])

<x-sketch-box :variant="$variant" {{ $attributes->merge(['class' => 'p-6 bg-surface flex flex-col justify-between']) }}>
    <div class="flex items-center justify-between">
        <span class="font-ui text-base font-semibold text-text-secondary uppercase tracking-wider">{{ $title }}</span>
        @if(isset($icon))
            <div class="text-accent-dark">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-4">
        <span class="font-heading text-5xl font-bold text-ink leading-none">{{ $value }}</span>
        @if($description)
            <p class="font-ui text-sm text-text-secondary mt-1">{{ $description }}</p>
        @endif
    </div>
</x-sketch-box>

