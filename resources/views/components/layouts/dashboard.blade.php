<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'SMART KOST') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-900 antialiased flex flex-col h-screen overflow-hidden selection:bg-indigo-100 selection:text-indigo-900" x-data="{ mobileMenuOpen: false, profileDropdownOpen: false }">
        
        <x-navbar />

        <!-- Main View -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-6 lg:p-8">
            @if (session('status'))
                <div class="max-w-7xl mx-auto mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('status') }}
                </div>
            @endif
            
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
