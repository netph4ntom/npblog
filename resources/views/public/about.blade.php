<x-public-layout title="Tentang">
    <div class="max-w-[740px] mx-auto py-8 sm:py-12 space-y-10">
        <!-- Header -->
        <header class="text-center space-y-4">
            <div class="w-28 h-28 mx-auto rounded-full border-2 border-ink flex items-center justify-center bg-accent/20 text-ink font-heading text-5xl font-bold">
                {{ substr($authorName, 0, 1) }}
            </div>
            <h1 class="font-heading text-4xl sm:text-5xl font-bold text-ink">{{ $authorName }}</h1>
            <p class="font-ui text-xl text-text-secondary max-w-xl mx-auto">{{ $authorBio }}</p>
        </header>

        <!-- About Content -->
        <div class="space-y-6 bg-surface p-8 sm:p-10 border-2 border-ink sketch-radius-b sketch-shadow font-body text-lg leading-relaxed text-text">
            <h2 class="font-heading text-3xl font-bold text-ink mb-4">Tentang Blog Ini</h2>
            <p class="whitespace-pre-line">{{ $aboutContent }}</p>
        </div>

        <!-- Hand-drawn Social & Contact Box -->
        <x-sketch-box variant="a" class="p-8 bg-surface space-y-4">
            <h3 class="font-heading text-2xl font-bold text-ink">Terhubung & Diskusi</h3>
            <p class="font-ui text-text-secondary text-base">
                Tertarik bertukar pikiran mengenai rekayasa perangkat lunak, sistem terdistribusi, atau arsitektur minimalis? Silakan hubungi saya melalui kanal di bawah.
            </p>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <x-button href="mailto:admin@npblog.local" variant="primary">
                    Email Saya
                </x-button>
                <x-button href="https://github.com" target="_blank" rel="noopener noreferrer" variant="secondary">
                    GitHub
                </x-button>
            </div>
        </x-sketch-box>
    </div>
</x-public-layout>
