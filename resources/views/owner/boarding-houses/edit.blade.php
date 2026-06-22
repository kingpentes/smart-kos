<x-layouts.app title="Edit Listing">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Edit Listing Kos</h1>

        <form method="POST" action="{{ route('owner.listings.submit', $boardingHouse) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="rounded-md bg-[#061C5D] px-4 py-2 text-sm font-semibold text-white">Kirim Verifikasi</button>
        </form>
    </div>

    <form method="POST" action="{{ route('owner.listings.update', $boardingHouse) }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @method('PUT')
        @include('owner.boarding-houses.partials.form')
    </form>
</x-layouts.app>
