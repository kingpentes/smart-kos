<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'SMART KOST') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @stack('styles')
    </head>
    <body class="min-h-screen bg-[#F6FBFF] text-slate-900 antialiased">
        <x-navbar />

        @if (session('status'))
            <div class="mx-auto mt-4 max-w-6xl px-4">
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main class="mx-auto max-w-6xl px-4 py-6 md:py-8">
            {{ $slot }}
        </main>

        @stack('scripts')
    </body>
</html>
