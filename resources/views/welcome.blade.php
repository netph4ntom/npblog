<x-public-layout title="Home">
    <!-- Hero Section -->
    <section class="py-12 md:py-20 flex flex-col items-center text-center">
        <h1 class="font-heading text-5xl md:text-7xl font-bold tracking-tight text-ink mb-6">
            Catatan, Kode, & <span class="marker-highlight">Eksplorasi</span>
        </h1>
        <p class="font-body text-text-secondary text-lg md:text-xl max-w-reading leading-relaxed mb-8">
            Blog personal minimalis bergaya hand-drawn oleh Dapit. Menulis seputar teknologi, keamanan siber, dan infrastruktur sistem.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-4">
            <x-button as="a" href="{{ url('/articles') }}" variant="primary" size="lg">
                Jelajahi Artikel
            </x-button>
            <x-button as="a" href="{{ url('/about') }}" variant="secondary" size="lg">
                Tentang Penulis
            </x-button>
        </div>
    </section>

    <!-- Component Demo Showcase in Home Preview -->
    <section class="my-12">
        <x-sketch-box variant="a" class="p-6 md:p-8" hover="true">
            <div class="flex items-center justify-between mb-4">
                <span class="font-heading text-3xl font-bold text-ink">Sorotan Terkini</span>
                <x-badge type="published">Aktif</x-badge>
            </div>
            <p class="font-body text-text-secondary mb-6 leading-relaxed">
                Fondasi antarmuka hand-drawn: sudut bergoyang preset CSS, garis tinta 2px, bayangan tegas, dan tipografi ekspresif.
            </p>
            <div class="flex flex-wrap gap-2 items-center">
                <x-chip href="{{ url('/articles?category=technology') }}" active="true">Technology</x-chip>
                <x-chip href="{{ url('/articles?category=cybersecurity') }}">Cybersecurity</x-chip>
                <x-chip href="{{ url('/articles?category=infrastructure') }}">Infrastructure</x-chip>
            </div>
        </x-sketch-box>
    </section>
</x-public-layout>
