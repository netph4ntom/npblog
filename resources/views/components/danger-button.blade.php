<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-danger text-white font-ui text-base font-normal min-h-[44px] sketch-border sketch-shadow sketch-radius-b sketch-btn-press hover:opacity-90 transition-all focus-visible:outline-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
