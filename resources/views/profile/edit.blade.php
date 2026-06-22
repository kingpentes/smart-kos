<x-layouts.dashboard title="Pengaturan Profil">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Pengaturan Profil</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui informasi akun dan kata sandi Anda.</p>
    </div>

    <div class="max-w-3xl grid gap-6">
        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-900 mb-4 border-b border-slate-100 pb-3">Informasi Pribadi</h2>
            
            <form method="POST" action="{{ route('profile.update') }}" class="grid gap-4">
                @csrf
                @method('PUT')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <x-ui.label for="name">Nama Lengkap</x-ui.label>
                        <x-ui.input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" />
                        <x-ui.error field="name" />
                    </div>

                    <div class="grid gap-2">
                        <x-ui.label for="phone_number">Nomor WhatsApp</x-ui.label>
                        <x-ui.input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number', $user->phone_number) }}" autocomplete="tel" placeholder="08123456789" />
                        <x-ui.error field="phone_number" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <x-ui.label for="email">Email</x-ui.label>
                    <x-ui.input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                    <x-ui.error field="email" />
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Ubah Kata Sandi <span class="text-slate-500 font-normal">(opsional)</span></h3>
                    
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <x-ui.label for="current_password">Kata Sandi Saat Ini</x-ui.label>
                            <x-ui.input id="current_password" name="current_password" type="password" autocomplete="current-password" />
                            <x-ui.error field="current_password" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <x-ui.label for="password">Kata Sandi Baru</x-ui.label>
                                <x-ui.input id="password" name="password" type="password" autocomplete="new-password" />
                                <x-ui.error field="password" />
                            </div>

                            <div class="grid gap-2">
                                <x-ui.label for="password_confirmation">Konfirmasi Kata Sandi Baru</x-ui.label>
                                <x-ui.input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.dashboard>
