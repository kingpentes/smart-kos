<x-layouts.app title="Login - SMART KOST">
    <div class="mx-auto flex min-h-[75vh] max-w-md flex-col justify-center gap-6 py-10">
        <div class="text-center">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Selamat Datang Kembali</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Masuk untuk mengelola booking, tagihan, dan listing kos.</p>
        </div>

        <x-ui.card class="border-0 shadow-xl shadow-slate-200/50">
            <x-ui.button href="{{ route('auth.google.redirect') }}" variant="secondary" class="w-full flex items-center justify-center gap-2 border-slate-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                <span class="font-bold">Lanjutkan dengan Google</span>
            </x-ui.button>

            <div class="my-6 flex items-center gap-3 text-xs font-bold uppercase tracking-widest text-slate-300">
                <div class="h-px flex-1 bg-slate-100"></div>
                <span>Atau dengan Email</span>
                <div class="h-px flex-1 bg-slate-100"></div>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="grid gap-5">
                @csrf

                <x-ui.label class="grid gap-1">
                    <span class="text-sm font-semibold text-slate-700">Alamat Email</span>
                    <x-ui.input name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" required />
                    <x-ui.error :messages="$errors->get('email')" />
                </x-ui.label>

                <x-ui.label class="grid gap-1">
                    <span class="text-sm font-semibold text-slate-700">Password</span>
                    <x-ui.input name="password" type="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required />
                    <x-ui.error :messages="$errors->get('password')" />
                </x-ui.label>

                <div class="flex items-center justify-between mt-1">
                    <label class="group flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-600">
                        <input name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                        <span class="group-hover:text-slate-900 transition-colors">Ingat saya</span>
                    </label>
                </div>

                <x-ui.button class="w-full mt-2 bg-slate-900 hover:bg-slate-800 text-white shadow-lg shadow-slate-900/20">Masuk ke Akun</x-ui.button>
            </form>
        </x-ui.card>
        
        <p class="text-center text-sm font-medium text-slate-500">
            Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-700 underline underline-offset-4">Daftar sekarang</a>
        </p>
    </div>
</x-layouts.app>
