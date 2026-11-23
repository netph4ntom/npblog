<x-admin-layout header="Dashboard">
    <div class="space-y-6">
        <x-sketch-box variant="a" class="p-6">
            <h3 class="font-heading text-3xl font-bold text-ink mb-2">Selamat datang, {{ Auth::user()->name ?? 'Dapit' }}!</h3>
            <p class="font-body text-text-secondary leading-relaxed">
                Anda berada di pusat kendali admin NPblog. Dari sini Anda dapat mengelola artikel, kategori, media, dan pengaturan blog.
            </p>
        </x-sketch-box>
    </div>
</x-admin-layout>
