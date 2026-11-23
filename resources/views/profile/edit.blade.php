<x-admin-layout header="Profile">
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Header -->
        <div class="pb-4 border-b border-border">
            <h1 class="font-heading text-4xl font-bold text-ink">Profil Pengguna</h1>
            <p class="font-ui text-base text-text-secondary">Perbarui identitas akun administrator dan kata sandi Anda.</p>
        </div>

        <!-- 1. Profile Information Form -->
        <x-sketch-box variant="a" class="p-6 bg-surface">
            @include('profile.partials.update-profile-information-form')
        </x-sketch-box>

        <!-- 2. Update Password Form -->
        <x-sketch-box variant="b" class="p-6 bg-surface">
            @include('profile.partials.update-password-form')
        </x-sketch-box>
    </div>
</x-admin-layout>
