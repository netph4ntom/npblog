<x-public-layout title="Tentang">
    <div class="max-w-[740px] mx-auto py-8 sm:py-12 space-y-10">
        <!-- Header -->
        <header class="text-center space-y-4">
            @if($profileImage !== '')
                <img
                    src="{{ $profileImage }}"
                    alt="{{ $authorName }}"
                    class="w-28 h-28 mx-auto rounded-full border-2 border-ink object-cover sketch-shadow-sm"
                />
            @else
                <div class="w-28 h-28 mx-auto rounded-full border-2 border-ink flex items-center justify-center bg-accent/20 text-ink font-heading text-5xl font-bold">
                    {{ substr($authorName, 0, 1) }}
                </div>
            @endif
            <h1 class="font-heading text-4xl sm:text-5xl font-bold text-ink">{{ $authorName }}</h1>
            <p class="font-ui text-xl text-text-secondary max-w-xl mx-auto">{{ $authorBio }}</p>
        </header>

        <!-- About Content -->
        <div class="bg-surface p-8 sm:p-10 border-2 border-ink sketch-radius-b sketch-shadow">
            <div class="article-body font-body text-lg leading-relaxed text-text">
                {!! $aboutHtml !!}
            </div>
        </div>

        <!-- Hand-drawn Social & Contact Box -->
        @if($contactEmail !== '' || !empty($socialLinks))
            <x-sketch-box variant="a" class="p-8 bg-surface space-y-4">
                <h3 class="font-heading text-2xl font-bold text-ink">Terhubung & Diskusi</h3>
                <p class="font-ui text-text-secondary text-base">
                    Tertarik bertukar pikiran mengenai rekayasa perangkat lunak, sistem terdistribusi, atau arsitektur minimalis? Silakan hubungi saya melalui kanal di bawah.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @if($contactEmail !== '')
                        <x-button href="mailto:{{ $contactEmail }}" variant="primary">
                            Email Saya
                        </x-button>
                    @endif
                    @foreach($socialLinks as $social)
                        <x-button href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" variant="secondary">
                            {{ $social['label'] }}
                        </x-button>
                    @endforeach
                </div>
            </x-sketch-box>
        @endif
    </div>
</x-public-layout>
