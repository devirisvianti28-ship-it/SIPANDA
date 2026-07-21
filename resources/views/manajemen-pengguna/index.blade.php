{{-- resources/views/manajemen-pengguna/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div
    x-data="{
        modalOpen: false,
        editing: null,
        confirmDelete: null,
        openCreate() { this.editing = null; this.modalOpen = true; },
        openEdit(user) { this.editing = user; this.modalOpen = true; }
    }"
>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium" style="background:#E7F7EE;color:#0F9D58;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium bg-red-50 text-red-600">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium bg-red-50 text-red-600">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola daftar pengguna sistem, peran, dan status akses mereka.</p>
        </div>
        <button type="button" @click="openCreate()"
            class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-white text-sm font-semibold shadow-sm hover:opacity-95"
            style="background:#1D4ED8;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m18-9v6m3-3h-6M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Tambah Pengguna
        </button>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('manajemen-pengguna.index') }}" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau NIP ..."
                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <span class="text-sm text-gray-500">Filter Role:</span>
            <select name="peran"
                class="px-3.5 py-2 rounded-lg border border-gray-200 text-sm font-medium bg-white focus:outline-none focus:ring-2 focus:ring-blue-200"
                onchange="this.form.submit()">
                <option value="semua" @selected(request('peran', 'semua') == 'semua')>Semua Role</option>
                <option value="pengelola" @selected(request('peran') == 'pengelola')>Pengelola</option>
                <option value="kepala_dinas" @selected(request('peran') == 'kepala_dinas')>Kepala Dinas</option>
            </select>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wide text-gray-400 bg-gray-50">
                    <th class="px-6 py-3.5 font-semibold">Nama</th>
                    <th class="px-6 py-3.5 font-semibold">NIP</th>
                    <th class="px-6 py-3.5 font-semibold">Email</th>
                    <th class="px-6 py-3.5 font-semibold">Role</th>
                    <th class="px-6 py-3.5 font-semibold">Status</th>
                    <th class="px-6 py-3.5 font-semibold">Terakhir Login</th>
                    <th class="px-6 py-3.5 font-semibold">Dibuat</th>
                    <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50/60 align-top">
                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $user->nip ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @php
                            $roleLabel = ['master_admin' => 'Master Admin', 'pengelola' => 'Pengelola', 'kepala_dinas' => 'Kepala Dinas'][$user->peran] ?? $user->peran;
                            $roleClass = match($user->peran) {
                                'master_admin' => 'bg-gray-100 text-gray-600',
                                'pengelola'    => 'bg-blue-50 text-blue-700',
                                'kepala_dinas' => 'bg-green-50 text-green-700',
                                default        => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleClass }}">{{ $roleLabel }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusClass = $user->status == 'aktif' ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400';
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ optional($user->last_login_at)->diffForHumans() ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ optional($user->created_at)->format('d M Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click='openEdit(@json($user))'
                                class="text-gray-400 hover:text-blue-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.4-9.4a2 2 0 112.8 2.8L11 15l-4 1 1-4 9.6-9.6z"/></svg>
                            </button>
                            <button type="button" @click="confirmDelete = { id: {{ $user->id }}, name: @js($user->name) }"
                                class="text-gray-400 hover:text-red-600" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.87 12.14A2 2 0 0116.14 21H7.86a2 2 0 01-1.99-1.86L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">Belum ada data pengguna.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
            </p>
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Tambah / Edit --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(11,27,51,.55);" @click.self="modalOpen=false">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden" @click.stop :key="editing ? editing.id : 'new'">
            <form :action="editing ? '{{ url('manajemen-pengguna') }}/' + editing.id : '{{ route('manajemen-pengguna.store') }}'" method="POST">
                @csrf
                <template x-if="editing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <div>
                        <h2 class="font-bold text-lg" x-text="editing ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"></h2>
                        <p class="text-xs text-gray-500 mt-0.5">Lengkapi data akun sesuai identitas kepegawaian.</p>
                    </div>
                    <button type="button" @click="modalOpen=false" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500">&times;</button>
                </div>

                <div class="px-6 py-5 space-y-4 max-h-[65vh] overflow-y-auto">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">NIP</label>
                        <input type="text" name="nip" :value="editing ? editing.nip : ''" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" :value="editing ? editing.name : ''" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Peran</label>
                        <select name="peran" :value="editing ? editing.peran : ''" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="">Pilih Peran</option>
                            <option value="pengelola">Pengelola</option>
                            <option value="kepala_dinas">Kepala Dinas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Email <span class="normal-case font-normal text-gray-400">(opsional)</span>
                        </label>
                        <input type="email" name="email" :value="editing ? editing.email : ''"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                                Password <span x-show="editing" class="normal-case font-normal text-gray-400">(kosongkan jika tak diubah)</span>
                            </label>
                            <input type="password" name="password" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Konfirmasi</label>
                            <input type="password" name="password_confirmation" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="status" value="1" :checked="editing ? editing.status == 'aktif' : true" id="statusAktif"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600">
                        <label for="statusAktif" class="text-sm">Aktifkan akun</label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <button type="button" @click="modalOpen=false" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-semibold hover:bg-white">Batalkan</button>
                    <button type="submit" class="px-4 py-2.5 rounded-lg text-white text-sm font-semibold hover:opacity-95" style="background:#1D4ED8;">
                        <span x-text="editing ? 'Simpan Perubahan' : 'Simpan Pengguna'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(11,27,51,.55);" @click.self="confirmDelete=null">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl p-6" @click.stop>
            <div class="w-11 h-11 rounded-full bg-red-50 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <h3 class="font-bold text-lg mb-1.5">Hapus pengguna ini?</h3>
            <p class="text-sm text-gray-500 mb-6">
                Akun <span x-text="confirmDelete ? confirmDelete.name : ''" class="font-semibold text-gray-900"></span>
                akan dihapus permanen dan tidak bisa login kembali ke sistem.
            </p>
            <form :action="confirmDelete ? '{{ url('manajemen-pengguna') }}/' + confirmDelete.id : '#'" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex items-center gap-3">
                    <button type="button" @click="confirmDelete=null" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-semibold hover:bg-gray-50">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection