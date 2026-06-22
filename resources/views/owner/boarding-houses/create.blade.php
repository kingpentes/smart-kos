<x-layouts.app title="Tambah Listing">
    <h1 class="text-2xl font-bold text-slate-900">Tambah Listing Kos</h1>

    <form method="POST" action="{{ route('owner.listings.store') }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @include('owner.boarding-houses.partials.form', ['boardingHouse' => null])
    </form>
</x-layouts.app>
