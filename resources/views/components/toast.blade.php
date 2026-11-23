<div
    x-data="{
        messages: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.messages.push({ id, message, type });
            setTimeout(() => { this.remove(id); }, 4000);
        },
        remove(id) {
            this.messages = this.messages.filter(m => m.id !== id);
        }
    }"
    @toast.window="add($event.detail.message, $event.detail.type)"
    x-init="
        @if(session('success')) add('{{ session('success') }}', 'success'); @endif
        @if(session('error')) add('{{ session('error') }}', 'error'); @endif
        @if(session('status')) add('{{ session('status') }}', 'info'); @endif
    "
    class="fixed bottom-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none max-w-sm w-full"
    role="status"
    aria-live="polite"
>
    <template x-for="item in messages" :key="item.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="pointer-events-auto p-4 sketch-border sketch-radius-b sketch-shadow bg-surface text-text flex items-start justify-between space-x-3"
            :class="{
                'border-accent': item.type === 'success',
                'border-danger': item.type === 'error',
                'border-ink': item.type === 'info'
            }"
        >
            <div class="flex items-center space-x-2">
                <span x-text="item.message" class="font-body text-sm font-medium"></span>
            </div>
            <button
                type="button"
                @click="remove(item.id)"
                class="text-text-secondary hover:text-text p-1 min-h-[30px]"
                aria-label="Tutup notifikasi"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
