@props([
    'id' => 'confirm-dialog',
    'title' => 'Konfirmasi Tindakan',
    'message' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
])

<div
    x-data="{ show: false, actionUrl: '', method: 'POST' }"
    @open-confirm-modal.window="
        if ($event.detail.id === '{{ $id }}') {
            show = true;
            actionUrl = $event.detail.action || '';
            method = $event.detail.method || 'POST';
        }
    "
    @keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="{{ $id }}-title"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-ink/50 backdrop-blur-xs"
        @click="show = false"
    ></div>

    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
            class="w-full max-w-md p-6 sketch-border sketch-radius-a sketch-shadow bg-surface text-text relative"
            @click.stop
        >
            <h3 id="{{ $id }}-title" class="font-heading text-2xl font-bold mb-2">{{ $title }}</h3>
            <p class="font-body text-text-secondary text-sm mb-6">{{ $message }}</p>

            <form :action="actionUrl" method="POST" class="flex justify-end space-x-3">
                @csrf
                <template x-if="method === 'DELETE'">
                    @method('DELETE')
                </template>
                <template x-if="method === 'PUT'">
                    @method('PUT')
                </template>

                <x-button variant="secondary" @click="show = false" type="button">
                    {{ $cancelText }}
                </x-button>

                <x-button variant="danger" type="submit">
                    {{ $confirmText }}
                </x-button>
            </form>
        </div>
    </div>
</div>
