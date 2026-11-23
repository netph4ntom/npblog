<x-public-layout title="404 — Halaman Tidak Ditemukan">
    <div class="min-h-[60vh] flex items-center justify-center py-12">
        <x-sketch-box variant="a" class="max-w-lg w-full p-8 sm:p-12 text-center bg-surface space-y-6">
            <div class="font-heading text-8xl sm:text-9xl font-bold text-accent leading-none">
                404
            </div>

            <h1 class="font-heading text-3xl sm:text-4xl font-bold text-ink">
                Halaman Tidak Ditemukan
            </h1>

            <p class="font-body text-text-secondary text-lg leading-relaxed">
                Sepertinya tautan yang kamu tuju telah dipindahkan, dihapus, atau tersesat di balik lembaran sketsa.
            </p>

            <div class="pt-4 flex items-center justify-center gap-4">
                <x-button href="{{ route('home') }}" variant="primary">
                    Kembali ke Beranda
                </x-button>
                <x-button href="{{ route('articles.index') }}" variant="secondary">
                    Jelajahi Artikel
                </x-button>
            </div>
        </x-sketch-box>
    </div>
</x-public-layout>
