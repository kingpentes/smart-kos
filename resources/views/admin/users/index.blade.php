<x-layouts.app title="Kelola Pengguna">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Pengguna</h1>
        <a href="{{ route('admin.dashboard') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-700 border border-slate-300">Kembali</a>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200">
            <p class="text-sm text-green-700">{{ session('status') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <div class="mb-6 flex gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-4 items-center w-full max-w-lg">
            <select name="role" class="rounded-md border-slate-300 shadow-sm focus:border-[#061C5D] focus:ring-[#061C5D] text-sm">
                <option value="">Semua Peran</option>
                <option value="tenant" {{ request('role') === 'tenant' ? 'selected' : '' }}>Penyewa</option>
                <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Pemilik</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <select name="status" class="rounded-md border-slate-300 shadow-sm focus:border-[#061C5D] focus:ring-[#061C5D] text-sm">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <button type="submit" class="rounded-md bg-[#061C5D] px-4 py-2 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Reset</a>
        </form>
    </div>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-900">
                <tr>
                    <th class="px-4 py-3 font-semibold">Nama</th>
                    <th class="px-4 py-3 font-semibold">Email</th>
                    <th class="px-4 py-3 font-semibold">Peran</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Tanggal Daftar</th>
                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                {{ $user->role->value === 'admin' ? 'bg-purple-100 text-purple-700' : ($user->role->value === 'owner' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ ucfirst($user->role->value) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                {{ $user->status->value === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($user->status->value) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->status->value === 'active' ? 'menangguhkan' : 'mengaktifkan' }} akun ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-sm font-semibold {{ $user->status->value === 'active' ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $user->status->value === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400 text-sm">You</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            Tidak ada pengguna yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-layouts.app>
