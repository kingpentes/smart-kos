<x-layouts.dashboard title="Buat Tagihan Tambahan">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Buat Tagihan Tambahan</h1>
            <p class="mt-1 text-sm text-slate-500">Kirim tagihan kustom seperti WiFi, Listrik, atau denda ke penyewa.</p>
        </div>
        <x-ui.button href="{{ route('owner.invoices.index') }}" variant="secondary">Kembali</x-ui.button>
    </div>

    <div class="max-w-3xl">
        <x-ui.card>
            <form method="POST" action="{{ route('owner.invoices.store') }}" class="space-y-6">
                @csrf

                <div>
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Target Tagihan</h2>
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                            <input type="radio" name="target_type" value="all" class="peer sr-only" onchange="toggleTarget('all')" {{ old('target_type') === 'all' ? 'checked' : '' }}>
                            <div class="flex flex-1">
                                <div class="flex flex-col">
                                    <span class="block text-sm font-medium text-slate-900">Semua Penghuni Kos</span>
                                    <span class="mt-1 flex items-center text-sm text-slate-500">Kirim ke semua penyewa aktif di satu kos.</span>
                                </div>
                            </div>
                            <div class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-blue-500" aria-hidden="true"></div>
                        </label>

                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none">
                            <input type="radio" name="target_type" value="specific" class="peer sr-only" onchange="toggleTarget('specific')" {{ old('target_type', 'specific') === 'specific' ? 'checked' : '' }}>
                            <div class="flex flex-1">
                                <div class="flex flex-col">
                                    <span class="block text-sm font-medium text-slate-900">Penyewa Spesifik</span>
                                    <span class="mt-1 flex items-center text-sm text-slate-500">Pilih penyewa tertentu untuk dikirimi tagihan.</span>
                                </div>
                            </div>
                            <div class="pointer-events-none absolute -inset-px rounded-lg border-2 border-transparent peer-checked:border-blue-500" aria-hidden="true"></div>
                        </label>
                    </div>
                    @error('target_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="target-all-section" class="hidden">
                    <x-ui.label for="boarding_house_id">Pilih Kos (Semua Penyewa Aktif)</x-ui.label>
                    <x-ui.select name="boarding_house_id" id="boarding_house_id" class="mt-1 w-full">
                        <option value="">-- Pilih Kos --</option>
                        @foreach($boardingHouses as $boardingHouse)
                            <option value="{{ $boardingHouse->id }}" {{ old('boarding_house_id') == $boardingHouse->id ? 'selected' : '' }}>{{ $boardingHouse->name }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('boarding_house_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="target-specific-section">
                    <x-ui.label for="lease_id">Pilih Penyewa</x-ui.label>
                    <x-ui.select name="lease_id" id="lease_id" class="mt-1 w-full">
                        <option value="">-- Pilih Penyewa --</option>
                        @foreach($leases as $lease)
                            <option value="{{ $lease->id }}" {{ old('lease_id') == $lease->id ? 'selected' : '' }}>
                                {{ $lease->tenant->name }} - {{ $lease->boardingHouse->name }} (Kamar {{ $lease->room->room_number }})
                            </option>
                        @endforeach
                    </x-ui.select>
                    @error('lease_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <hr class="border-slate-100">

                <div>
                    <h2 class="text-base font-semibold text-slate-900 mb-4">Detail Tagihan</h2>
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-ui.label for="title">Judul Tagihan</x-ui.label>
                            <x-ui.input type="text" name="title" id="title" class="mt-1 w-full" placeholder="Misal: Tagihan WiFi Bulan Ini" value="{{ old('title') }}" required />
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="amount">Nominal (Rp)</x-ui.label>
                            <x-ui.input type="number" name="amount" id="amount" class="mt-1 w-full" placeholder="50000" min="1" value="{{ old('amount') }}" required />
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="due_date">Batas Pembayaran (Jatuh Tempo)</x-ui.label>
                            <x-ui.input type="date" name="due_date" id="due_date" class="mt-1 w-full" value="{{ old('due_date') }}" required />
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.label for="description">Keterangan (Opsional)</x-ui.label>
                            <x-ui.textarea name="description" id="description" rows="3" class="mt-1 w-full">{{ old('description') }}</x-ui.textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <x-ui.button type="submit">Kirim Tagihan</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    <script>
        function toggleTarget(type) {
            const allSection = document.getElementById('target-all-section');
            const specificSection = document.getElementById('target-specific-section');
            
            if (type === 'all') {
                allSection.classList.remove('hidden');
                specificSection.classList.add('hidden');
            } else {
                allSection.classList.add('hidden');
                specificSection.classList.remove('hidden');
            }
        }

        // Initialize state on load
        document.addEventListener('DOMContentLoaded', () => {
            const selected = document.querySelector('input[name="target_type"]:checked')?.value || 'specific';
            toggleTarget(selected);
        });
    </script>
</x-layouts.dashboard>
