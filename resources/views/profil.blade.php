@extends(auth()->user()->hasRole('kepala_dinas') ? 'layouts.app-kepala-dinas' : 'layouts.app')

@section('title', 'Profil')

@section('content')

    <h1 class="text-2xl font-extrabold text-slate-800">Profil</h1>
    <p class="text-slate-500 mt-1 mb-6">Data ini digunakan untuk identitas Anda di dalam sistem.</p>

    <form action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data"
          x-data="{ editMode: false, photoPreview: null }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ==== CARD PROFIL ==== --}}
            <div class="bg-white rounded-2xl card-shadow p-6 flex flex-col items-center text-center">

                <div class="relative w-20 h-20 mb-4">
                    {{-- Foto asli / preview baru --}}
                    <template x-if="photoPreview">
                        <img :src="photoPreview" class="w-20 h-20 rounded-full object-cover">
                    </template>

                    @if($user->foto_profil ?? false)
                        <img x-show="!photoPreview" src="{{ asset('storage/'.$user->foto_profil) }}"
                             class="w-20 h-20 rounded-full object-cover">
                    @else
                        <div x-show="!photoPreview"
                             class="w-20 h-20 rounded-full bg-navy text-white flex items-center justify-center text-2xl font-bold">
                            {{ strtoupper(substr($user->nama_lengkap ?? $userName ?? 'Azizah', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->nama_lengkap ?? $userName ?? 'Azizah')[1] ?? '', 0, 1)) }}
                        </div>
                    @endif

                    {{-- Tombol kamera, SEKARANG SELALU MUNCUL (tidak perlu klik Ubah Profil dulu) --}}
                    <label class="absolute bottom-0 right-0 w-7 h-7 rounded-full bg-navy text-white flex items-center justify-center cursor-pointer border-2 border-white hover:bg-navy-dark">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M4 7h3l2-2h6l2 2h3a1 1 0 011 1v11a1 1 0 01-1 1H4a1 1 0 01-1-1V8a1 1 0 011-1z"/>
                            <circle cx="12" cy="13" r="3.5"/>
                        </svg>
                        <input type="file" name="foto_profil" accept="image/png, image/jpeg" class="hidden"
                               @change="
                                   const file = $event.target.files[0];
                                   if (file) { photoPreview = URL.createObjectURL(file); }
                               ">
                    </label>
                </div>

                <h2 class="font-bold text-slate-800 text-lg">{{ $user->nama_lengkap ?? $userName ?? 'Azizah' }}</h2>
                <p class="text-sm text-slate-400">{{ $user->email ?? 'azizah.admin@diskominfo.garutkab.go.id' }}</p>

                <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-navy bg-brand-softblue px-3 py-1 rounded-full">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"/>
                    </svg>
                    {{ $user->peran ?? $userRole ?? 'Diskominfo Garut' }}
                </span>

                <div class="w-full mt-6 divide-y divide-slate-100 text-left">
                    <div class="py-3 flex items-start gap-3">
                        <svg class="w-4 h-4 text-slate-400 mt-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">BERGABUNG SEJAK</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $user->bergabung_sejak ?? '3 Januari 2024' }}</p>
                        </div>
                    </div>
                    <div class="py-3 flex items-start gap-3">
                        <svg class="w-4 h-4 text-slate-400 mt-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
                        </svg>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">LOGIN TERAKHIR</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $user->login_terakhir ?? 'Hari ini, 08.42' }}</p>
                        </div>
                    </div>
                    <div class="py-3 flex items-start gap-3">
                        <svg class="w-4 h-4 text-navy mt-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">STATUS AKUN</p>
                            <p class="text-sm font-semibold text-navy">{{ $user->status ?? 'Aktif' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==== CARD FORM INFORMASI AKUN ==== --}}
            <div class="bg-white rounded-2xl card-shadow p-6 lg:col-span-2">
                <h2 class="font-bold text-slate-800">Informasi akun</h2>
                <p class="text-sm text-slate-400 mb-6">Data ini digunakan untuk identitas Anda di dalam sistem.</p>

                @error('foto_profil')
                    <p class="text-xs text-red-500 mb-4">{{ $message }}</p>
                @enderror

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1">Nama lengkap</label>
                        <input type="text" name="nama_lengkap"
                            value="{{ old('nama_lengkap', $user->nama_lengkap ?? $userName ?? 'Azizah') }}"
                            :readonly="!editMode"
                            :class="editMode ? 'bg-white text-slate-700 focus:ring-2 focus:ring-navy/30' : 'bg-slate-50 text-slate-500'"
                            class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none">
                        @error('nama_lengkap')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1">NIP</label>
                        <input type="text" name="nip"
                            value="{{ old('nip', $user->nip ?? '') }}"
                            :readonly="!editMode"
                            :class="editMode ? 'bg-white text-slate-700 focus:ring-2 focus:ring-navy/30' : 'bg-slate-50 text-slate-500'"
                            class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none">
                        @error('nip')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1">Email / username</label>
                        <input type="email" name="email"
                            value="{{ old('email', $user->email ?? 'azizah.admin@diskominfo.garutkab.go.id') }}"
                            :readonly="!editMode"
                            :class="editMode ? 'bg-white text-slate-700 focus:ring-2 focus:ring-navy/30' : 'bg-slate-50 text-slate-500'"
                            class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none">
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1">Peran</label>
                        <input type="text" name="peran"
                            value="{{ old('peran', $user->peran ?? $userRole ?? 'Diskominfo Garut') }}"
                            readonly
                            class="w-full border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-500 bg-slate-50 focus:outline-none">
                    </div>
                </div>

                <hr class="my-6 border-slate-100">

                <div class="flex justify-end gap-3">
                    <template x-if="!editMode">
                        <button type="button" @click="editMode = true"
                            class="px-5 py-2.5 rounded-lg bg-navy text-sm font-semibold text-white hover:bg-navy-dark">
                            Ubah Profil
                        </button>
                    </template>

                    <template x-if="editMode">
                        <button type="button" @click="editMode = false; photoPreview = null"
                            class="px-5 py-2.5 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Batalkan
                        </button>
                    </template>
                    <template x-if="editMode">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-navy text-sm font-semibold text-white hover:bg-navy-dark">
                            Perbarui Profil
                        </button>
                    </template>
                </div>
            </div>

        </div>
    </form>

@endsection