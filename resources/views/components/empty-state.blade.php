@props([
    'title' => 'Belum ada data',
    'message' => null,
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

@php
    $bodyText = $description ?? $message ?? 'Tidak ada data untuk ditampilkan saat ini.';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-6 sm:p-8 text-center sketch-border border-dashed sketch-radius-c bg-surface/50 text-text my-6']) }}>
    <svg class="w-16 h-16 stroke-text-secondary mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
    </svg>
    <h3 class="font-heading text-2xl font-bold mb-1">{{ $title }}</h3>
    <p class="font-body text-text-secondary text-sm max-w-sm">{{ $bodyText }}</p>
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @elseif($actionLabel && $actionUrl)
        <div class="mt-5">
            <x-button href="{{ $actionUrl }}" variant="secondary">
                {{ $actionLabel }}
            </x-button>
        </div>
    @endif
</div>

