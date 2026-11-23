@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'sketch-border sketch-radius-a bg-surface text-text placeholder:text-text-secondary font-body focus:border-ink focus:ring-0 min-h-[44px] px-3 py-2 shadow-sm']) }}>
