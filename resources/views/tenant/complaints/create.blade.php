<x-layouts.app title="Ajukan Keluhan">
    <h1 class="text-2xl font-bold text-slate-900">Ajukan Keluhan</h1>

    <form method="POST" action="{{ route('tenant.complaints.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-5 rounded-md border border-slate-200 bg-white p-6">
        @csrf

        <label class="grid gap-1 text-sm">
            <span>Sewa aktif</span>
            <select name="lease_id" required class="rounded-md border border-slate-300 px-3 py-2">
                @foreach ($leases as $lease)
                    <option value="{{ $lease->id }}" @selected((int) old('lease_id') === $lease->id)>
                        {{ $lease->boardingHouse->name }} - Kamar {{ $lease->room->room_number }}
                    </option>
                @endforeach
            </select>
            @error('lease_id') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Kategori</span>
            <select name="category" required class="rounded-md border border-slate-300 px-3 py-2">
                <option value="fasilitas_rusak" @selected(old('category') === 'fasilitas_rusak')>Fasilitas rusak</option>
                <option value="kebersihan" @selected(old('category') === 'kebersihan')>Kebersihan</option>
                <option value="keamanan" @selected(old('category') === 'keamanan')>Keamanan</option>
                <option value="lainnya" @selected(old('category') === 'lainnya')>Lainnya</option>
            </select>
            @error('category') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Deskripsi</span>
            <textarea name="description" rows="5" required class="rounded-md border border-slate-300 px-3 py-2">{{ old('description') }}</textarea>
            @error('description') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Foto pendukung</span>
            <input name="photos[]" type="file" multiple accept="image/*" class="rounded-md border border-slate-300 px-3 py-2">
            @error('photos.*') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        @if ($leases->isEmpty())
            <div class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">Tidak ada sewa aktif untuk mengajukan keluhan.</div>
        @else
            <button type="submit" class="rounded-md bg-[#2563EB] px-4 py-2 font-semibold text-white">Kirim Keluhan</button>
        @endif
    </form>
</x-layouts.app>
