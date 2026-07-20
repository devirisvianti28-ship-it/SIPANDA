@extends('layouts.app')

@section('title', 'Kelola Banner Homepage')

@php
    $searchPlaceholder = 'Cari nomor pengaduan atau pelapor...';
    $userName = 'Admin Garut';
    $userRole = 'Super Admin';
@endphp

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')

<div x-data="{
        showModal: {{ $errors->any() ? 'true' : 'false' }},
        deleteModal: { open: false, id: null, judul: '', loading: false },
        toast: { show: false, message: '' },
        showToast(message) {
            this.toast.message = message;
            this.toast.show = true;
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => { this.toast.show = false; }, 3000);
        },
        openDeleteModal(id, judul) {
            this.deleteModal.id = id;
            this.deleteModal.judul = judul;
            this.deleteModal.open = true;
        },
        async confirmDelete() {
            if (this.deleteModal.loading) return;
            this.deleteModal.loading = true;
            const targetId = this.deleteModal.id;
            const targetJudul = this.deleteModal.judul;
            try {
                await callBannerApi(`/kelola-banner/${targetId}`, 'DELETE');
                document.querySelector(`tr[data-banner-id='${targetId}']`)?.remove();
                this.deleteModal.open = false;
                this.showToast(`Banner '${targetJudul}' berhasil dihapus.`);
            } catch (e) {
                alert('Gagal menghapus banner.');
            } finally {
                this.deleteModal.loading = false;
            }
        }
    }">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-navy">Kelola Banner Homepage</h1>
            <p class="text-slate-500 mt-1">Kelola gambar yang akan ditampilkan pada banner utama halaman depan SAPA GARUT.</p>
        </div>
        <button type="button" @click="showModal = true"
                class="bg-navy hover:bg-navy-dark text-white font-semibold text-sm px-5 py-3 rounded-xl flex items-center gap-2 card-shadow shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Banner
        </button>
    </div>

    {{-- ================= FLASH MESSAGE ================= --}}
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl mb-5">
            {{ session('success') }}
        </div>
    @endif

    {{-- ================= TABEL BANNER ================= --}}
    <div class="bg-white rounded-2xl card-shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-400 tracking-wide border-b border-slate-100">
                        <th class="px-6 py-3">NO</th>
                        <th class="px-3 py-3">PREVIEW</th>
                        <th class="px-3 py-3">STATUS</th>
                        <th class="px-3 py-3 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody id="banner-table-body">
                    @forelse($banners as $i => $banner)
                    <tr class="border-b border-slate-50 last:border-0" data-banner-id="{{ $banner->id }}">
                        <td class="px-6 py-4 text-slate-500">{{ $i + 1 }}</td>
                        <td class="px-3 py-4">
                            <img src="{{ $banner->gambar_url }}"
                                 onerror="this.onerror=null;this.src='https://placehold.co/96x64/DBEAFE/1D4ED8?text=Banner';"
                                 class="w-20 h-14 object-cover rounded-lg border border-slate-100">
                        </td>
                        <td class="px-3 py-4">
                            <button type="button"
                                    class="banner-toggle-status flex items-center gap-2"
                                    data-id="{{ $banner->id }}">
                                <span class="toggle-track relative inline-flex h-5 w-9 items-center rounded-full transition {{ $banner->status ? 'bg-navy' : 'bg-slate-200' }}">
                                    <span class="toggle-dot inline-block h-3.5 w-3.5 transform rounded-full bg-white transition {{ $banner->status ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                </span>
                                <span class="toggle-label text-xs font-semibold {{ $banner->status ? 'text-navy' : 'text-slate-400' }}">
                                    {{ $banner->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </button>
                        </td>
                        <td class="px-3 py-4 text-center">
                            <button type="button" class="text-red-400 hover:text-red-600"
                                    @click="openDeleteModal('{{ $banner->id }}', '{{ addslashes($banner->judul) }}')"
                                    title="Hapus banner">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6h16z"/></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            Belum ada banner. Klik <span class="font-semibold text-navy">"Tambah Banner"</span> untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= MODAL TAMBAH BANNER ================= --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 bg-slate-900/50 z-40 flex items-center justify-center p-4"
         @keydown.escape.window="showModal = false">
        <div @click.outside="showModal = false" class="bg-white rounded-2xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-800">Tambah Banner Baru</h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('kelola-banner.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                @if ($errors->any())
                <div class="bg-red-50 text-red-600 text-sm font-semibold px-4 py-3 rounded-xl">
                    {{ $errors->first() }}
                </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gambar Banner</label>
                    <input type="file" name="gambar" accept="image/png,image/jpeg,image/webp" required
                           onchange="document.getElementById('preview-upload').src = URL.createObjectURL(this.files[0]); document.getElementById('preview-upload').classList.remove('hidden')"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:bg-blue-50 file:text-navy file:text-xs file:font-semibold">
                    <p class="text-xs text-slate-400 mt-1">Format JPG/PNG/WEBP, maksimal 2MB.</p>
                    <img id="preview-upload" class="hidden mt-3 w-full h-32 object-cover rounded-lg border border-slate-100">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-navy hover:bg-navy-dark">Simpan Banner</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL KONFIRMASI HAPUS ================= --}}
    <div x-show="deleteModal.open" x-cloak
         class="fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="!deleteModal.loading && (deleteModal.open = false)">
        <div @click.outside="!deleteModal.loading && (deleteModal.open = false)" class="bg-white rounded-2xl w-full max-w-sm p-6 text-center">
            <div class="mx-auto mb-4 w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
                <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6h16z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1.5">Hapus Banner?</h3>
            <p class="text-sm text-slate-500 mb-6">
                Yakin mau hapus banner <span class="font-semibold text-slate-700" x-text="deleteModal.judul"></span>? Tindakan ini tidak bisa dibatalkan.
            </p>
            <div class="flex justify-center gap-3">
                <button type="button" @click="deleteModal.open = false" :disabled="deleteModal.loading"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Batal
                </button>
                <button type="button" @click="confirmDelete()" :disabled="deleteModal.loading"
                        :class="deleteModal.loading ? 'pointer-events-none' : ''"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-500 hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg x-show="deleteModal.loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="deleteModal.loading ? 'Menghapus...' : 'Ya, Hapus'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ================= TOAST SUKSES ================= --}}
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-[60] bg-emerald-600 text-white text-sm font-semibold px-5 py-3.5 rounded-xl card-shadow flex items-center gap-2.5">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span x-text="toast.message"></span>
    </div>

</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    async function callBannerApi(url, method = 'POST', body = null) {
        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: body ? JSON.stringify(body) : null,
        });
        if (!res.ok) throw new Error('Request gagal');
        // Sebagian endpoint (mis. destroy() yang return 204/no body) tidak mengirim JSON.
        // Cek dulu biar res.json() tidak error walau request-nya sebetulnya sukses.
        const text = await res.text();
        return text ? JSON.parse(text) : null;
    }

    // ---- Toggle Status Aktif/Nonaktif (optimistic: UI update dulu, baru kirim ke server) ----
    document.querySelectorAll('.banner-toggle-status').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const track = btn.querySelector('.toggle-track');
            const dot = btn.querySelector('.toggle-dot');
            const label = btn.querySelector('.toggle-label');

            // Simpan kondisi lama, buat jaga-jaga kalau request gagal
            const wasActive = track.classList.contains('bg-navy');
            const nowActive = !wasActive;

            // ---- Update tampilan LANGSUNG, tanpa nunggu server ----
            applyToggleUi(track, dot, label, nowActive);
            btn.disabled = true;

            try {
                const result = await callBannerApi(`/kelola-banner/${id}/toggle-status`, 'PATCH');
                // Samakan dengan hasil asli dari server (jaga-jaga kalau beda)
                applyToggleUi(track, dot, label, result.status);
            } catch (e) {
                // Gagal -> balikin ke kondisi semula
                applyToggleUi(track, dot, label, wasActive);
                alert('Gagal mengubah status banner.');
            } finally {
                btn.disabled = false;
            }
        });
    });

    function applyToggleUi(track, dot, label, isActive) {
        if (isActive) {
            track.classList.remove('bg-slate-200'); track.classList.add('bg-navy');
            dot.classList.remove('translate-x-1'); dot.classList.add('translate-x-5');
            label.textContent = 'Aktif'; label.classList.remove('text-slate-400'); label.classList.add('text-navy');
        } else {
            track.classList.remove('bg-navy'); track.classList.add('bg-slate-200');
            dot.classList.remove('translate-x-5'); dot.classList.add('translate-x-1');
            label.textContent = 'Nonaktif'; label.classList.remove('text-navy'); label.classList.add('text-slate-400');
        }
    }

    // ---- Hapus Banner: sekarang ditangani oleh modal konfirmasi Alpine (lihat confirmDelete() di x-data) ----
</script>
@endpush