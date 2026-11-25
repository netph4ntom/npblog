<x-admin-layout header="Tambah Kategori">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-border">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Tambah Kategori</h1>
                <p class="font-ui text-base text-text-secondary">Buat kelompok topik baru untuk artikel blog.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink min-h-[44px] inline-flex items-center self-start sm:self-auto">
                &larr; Kembali
            </a>
        </div>

        <x-sketch-box variant="a" class="p-6 bg-surface">
            <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" value="Nama Kategori" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        class="w-full"
                        placeholder="Contoh: Arsitektur Software"
                        value="{{ old('name') }}"
                        required
                        autofocus
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Deskripsi (opsional)" />
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="sketch-border sketch-radius-a bg-surface text-text font-body p-3 w-full focus:border-ink focus:ring-0"
                        placeholder="Penjelasan singkat mengenai kategori ini..."
                    >{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-border">
                    <a href="{{ route('admin.categories.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink px-4 py-2 min-h-[44px] inline-flex items-center">
                        Batal
                    </a>
                    <x-button type="submit" variant="primary">
                        Simpan Kategori
                    </x-button>
                </div>
            </form>
        </x-sketch-box>
    </div>
</x-admin-layout>

