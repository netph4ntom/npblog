<x-admin-layout header="Edit Kategori">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-border">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Edit Kategori</h1>
                <p class="font-ui text-base text-text-secondary">Perbarui kategori &ldquo;{{ $category->name }}&rdquo;.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink">
                &larr; Kembali
            </a>
        </div>

        <x-sketch-box variant="b" class="p-6 bg-surface">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <x-input-label for="name" value="Nama Kategori" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        class="w-full"
                        value="{{ old('name', $category->name) }}"
                        required
                        autofocus
                    />
                    <p class="font-mono text-xs text-text-secondary mt-1">Slug saat ini: /{{ $category->slug }}</p>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <!-- Description -->
                <div>
                    <x-input-label for="description" value="Deskripsi (opsional)" />
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="sketch-border sketch-radius-a bg-surface text-text font-body p-3 w-full"
                    >{{ old('description', $category->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-border">
                    <a href="{{ route('admin.categories.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink px-4 py-2">
                        Batal
                    </a>
                    <x-button type="submit" variant="primary">
                        Perbarui Kategori
                    </x-button>
                </div>
            </form>
        </x-sketch-box>
    </div>
</x-admin-layout>

