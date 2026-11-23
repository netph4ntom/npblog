<x-public-layout title="500 — Terjadi Kesalahan">
    <div class="min-h-[60vh] flex items-center justify-center py-12">
        <x-sketch-box variant="b" class="max-w-lg w-full p-8 sm:p-12 text-center bg-surface space-y-6">
            <div class="font-heading text-8xl sm:text-9xl font-bold text-danger leading-none">
                500
            </div>

            <h1 class="font-heading text-3xl sm:text-4xl font-bold text-ink">
                Terjadi Kesalahan Server
            </h1>

            <p class="font-body text-text-secondary text-lg leading-relaxed">
                Mohon maaf, ada sedikit kendala teknis pada sistem kami. Kami sedang menanganinya secepat mungkin.
            </p>

            <div class="pt-4 flex items-center justify-center gap-4">
                <x-button href="{{ route('home') }}" variant="primary">
                    Kembali ke Beranda
                </x-button>
            </div>
        </x-sketch-box>
    </div>
</x-public-layout>
