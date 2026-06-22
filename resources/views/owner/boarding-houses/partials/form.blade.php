@php
    $selectedFacilities = collect(old('facilities', $boardingHouse?->facilities->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
    $rules = old('rules', $boardingHouse?->rules->map(fn ($rule) => ['key' => $rule->key, 'value' => $rule->value])->all() ?? []);
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

<div class="grid gap-6 rounded-md border border-slate-200 bg-white p-6">
    <div class="grid gap-4 md:grid-cols-2">
        <label class="grid gap-1 text-sm">
            <span>Nama kos</span>
            <input name="name" value="{{ old('name', $boardingHouse?->name) }}" required class="rounded-md border border-slate-300 px-3 py-2">
            @error('name') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Tipe kos</span>
            <select name="type" required class="rounded-md border border-slate-300 px-3 py-2">
                <option value="male" @selected(old('type', $boardingHouse?->type?->value) === 'male')>Putra</option>
                <option value="female" @selected(old('type', $boardingHouse?->type?->value) === 'female')>Putri</option>
                <option value="mixed" @selected(old('type', $boardingHouse?->type?->value) === 'mixed')>Campur</option>
            </select>
            @error('type') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>
    </div>

    <label class="grid gap-1 text-sm">
        <span>Deskripsi</span>
        <textarea name="description" rows="4" required class="rounded-md border border-slate-300 px-3 py-2">{{ old('description', $boardingHouse?->description) }}</textarea>
        @error('description') <span class="text-red-600">{{ $message }}</span> @enderror
    </label>

    <label class="grid gap-1 text-sm">
        <span>Alamat</span>
        <textarea name="address" rows="3" required class="rounded-md border border-slate-300 px-3 py-2">{{ old('address', $boardingHouse?->address) }}</textarea>
        @error('address') <span class="text-red-600">{{ $message }}</span> @enderror
    </label>

    <div class="grid gap-4 md:grid-cols-2">
        <label class="grid gap-1 text-sm">
            <span>Kota</span>
            <input name="city" value="{{ old('city', $boardingHouse?->city) }}" required class="rounded-md border border-slate-300 px-3 py-2">
            @error('city') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Kecamatan</span>
            <input name="district" value="{{ old('district', $boardingHouse?->district) }}" class="rounded-md border border-slate-300 px-3 py-2">
            @error('district') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <label class="grid gap-1 text-sm">
            <span>Harga bulanan</span>
            <input name="price_monthly" type="number" value="{{ old('price_monthly', $boardingHouse?->price_monthly) }}" required class="rounded-md border border-slate-300 px-3 py-2">
            @error('price_monthly') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Deposit</span>
            <input name="deposit_amount" type="number" value="{{ old('deposit_amount', $boardingHouse?->deposit_amount ?? 0) }}" class="rounded-md border border-slate-300 px-3 py-2">
            @error('deposit_amount') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Latitude</span>
            <input name="latitude" id="latitudeInput" type="number" step="0.0000001" value="{{ old('latitude', $boardingHouse?->latitude) }}" class="rounded-md border border-slate-300 px-3 py-2">
            @error('latitude') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-sm">
            <span>Longitude</span>
            <input name="longitude" id="longitudeInput" type="number" step="0.0000001" value="{{ old('longitude', $boardingHouse?->longitude) }}" class="rounded-md border border-slate-300 px-3 py-2">
            @error('longitude') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>
    </div>

    <!-- Map Container -->
    <div class="grid gap-1 text-sm">
        <span>Tentukan Lokasi di Peta</span>
        <div id="mapPicker" class="h-[300px] w-full rounded-md border border-slate-300 z-10"></div>
        <span class="text-xs text-slate-500">Klik atau geser peta untuk menentukan titik koordinat kos.</span>
    </div>

    @if (! $boardingHouse)
        <label class="grid gap-1 text-sm">
            <span>Jumlah kamar awal</span>
            <input name="room_count" type="number" min="1" max="200" value="{{ old('room_count', 5) }}" required class="rounded-md border border-slate-300 px-3 py-2">
            @error('room_count') <span class="text-red-600">{{ $message }}</span> @enderror
        </label>
    @endif

    <div class="grid gap-2 text-sm">
        <span>Fasilitas</span>
        <div class="grid gap-2 md:grid-cols-3">
            @foreach ($facilities as $facility)
                <label class="flex items-center gap-2">
                    <input name="facilities[]" type="checkbox" value="{{ $facility->id }}" @checked(in_array((string) $facility->id, $selectedFacilities, true))>
                    <span>{{ $facility->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="grid gap-3 text-sm">
        <span>Aturan kos</span>
        @for ($index = 0; $index < 3; $index++)
            <div class="grid gap-3 md:grid-cols-[220px_1fr]">
                <input name="rules[{{ $index }}][key]" value="{{ $rules[$index]['key'] ?? '' }}" placeholder="Judul aturan" class="rounded-md border border-slate-300 px-3 py-2">
                <input name="rules[{{ $index }}][value]" value="{{ $rules[$index]['value'] ?? '' }}" placeholder="Isi aturan" class="rounded-md border border-slate-300 px-3 py-2">
            </div>
        @endfor
    </div>

    <label class="grid gap-1 text-sm">
        <span>Foto kos</span>
        <input name="photos[]" type="file" multiple accept="image/*" class="rounded-md border border-slate-300 px-3 py-2">
        @error('photos.*') <span class="text-red-600">{{ $message }}</span> @enderror
    </label>

    <div>
        <button type="submit" class="rounded-md bg-[#2563EB] px-4 py-2 font-semibold text-white">Simpan Listing</button>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const latInput = document.getElementById('latitudeInput');
            const lngInput = document.getElementById('longitudeInput');
            
            // Default center: Indonesia or existing coordinates
            let initialLat = latInput.value ? parseFloat(latInput.value) : -0.789275;
            let initialLng = lngInput.value ? parseFloat(lngInput.value) : 113.921327;
            let initialZoom = latInput.value ? 16 : 5;

            const map = L.map('mapPicker').setView([initialLat, initialLng], initialZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let marker = null;
            
            if (latInput.value && lngInput.value) {
                marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);
            }

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);
            }

            function setMarker(lat, lng) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], {draggable: true}).addTo(map);
                    marker.on('dragend', function (event) {
                        const position = marker.getLatLng();
                        updateInputs(position.lat, position.lng);
                    });
                }
            }

            map.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });

            if (marker) {
                marker.on('dragend', function (event) {
                    const position = marker.getLatLng();
                    updateInputs(position.lat, position.lng);
                });
            }

            // Sync manual input back to map
            function syncMapFromInput() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    setMarker(lat, lng);
                    map.setView([lat, lng], 16);
                }
            }

            latInput.addEventListener('change', syncMapFromInput);
            lngInput.addEventListener('change', syncMapFromInput);
        });
    </script>
@endpush
