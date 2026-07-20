@extends('layouts.app')

@section('title', 'Laporan Pengaduan')

@php
    $searchPlaceholder = 'Cari laporan...';
    $userName = 'Administrator';
    $userRole = 'Pusat Bantuan';
@endphp

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')

<div x-data="laporanForm()" x-init="init()">

    <h1 class="text-3xl font-extrabold text-slate-900">Laporan Pengaduan</h1>
    <p class="text-slate-500 mt-1 mb-8">Ikuti langkah-langkah di bawah untuk membuat laporan yang diinginkan.</p>

    {{-- ================= STEP 1 : JENIS LAPORAN ================= --}}
    <div class="flex items-center gap-3 mb-1">
        <span class="w-7 h-7 rounded-full bg-navy text-white text-sm font-bold flex items-center justify-center shrink-0">1</span>
        <h2 class="text-xl font-bold text-slate-800">Jenis Laporan</h2>
    </div>
    <p class="text-sm text-slate-500 ml-10 mb-4">Pilih jenis laporan yang ingin dibuat.</p>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <template x-for="jenis in jenisList" :key="jenis.id">
            <button type="button" @click="selectedJenis = jenis.id"
                    class="relative bg-white rounded-2xl p-6 flex flex-col items-center gap-3 border-2 card-shadow transition"
                    :class="selectedJenis === jenis.id ? 'border-navy' : 'border-transparent hover:border-slate-200'">
                <svg x-show="selectedJenis === jenis.id" class="w-5 h-5 text-navy absolute top-3 right-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.3 2.3L16 9.5"/>
                </svg>
                <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                     :class="selectedJenis === jenis.id ? 'bg-navy' : 'bg-emerald-50'">
                    <svg class="w-6 h-6" :class="selectedJenis === jenis.id ? 'text-white' : 'text-emerald-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-html="jenis.icon"></svg>
                </div>
                <span class="font-bold text-slate-800" x-text="jenis.label"></span>
            </button>
        </template>
    </div>

    {{-- ================= STEP 2 : FILTER DATA ================= --}}
    <div class="flex items-center gap-3 mb-1">
        <span class="w-7 h-7 rounded-full bg-navy text-white text-sm font-bold flex items-center justify-center shrink-0">2</span>
        <h2 class="text-xl font-bold text-slate-800">Filter Data</h2>
    </div>
    <p class="text-sm text-slate-500 ml-10 mb-4">Sesuaikan data yang akan ditampilkan pada laporan.</p>

    <div class="bg-white rounded-2xl p-6 card-shadow mb-4">

        {{-- ---- Filter: Rekap Harian ---- --}}
        <div x-show="selectedJenis === 'harian'" class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Laporan</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <input type="date" x-model="tanggal" class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Filter SKPD</label>
                <select x-model="skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua Instansi / SKPD</option>
                    @foreach($daftarSkpd as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status Pengaduan</label>
                <select x-model="status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua Status</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Proses">Proses</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
        </div>

        {{-- ---- Filter: Rekap Mingguan ---- --}}
        <div x-show="selectedJenis === 'mingguan'" class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <input type="date" x-model="tanggalMulai" class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <input type="date" x-model="tanggalSelesai" class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Filter SKPD</label>
                <select x-model="skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua Instansi / SKPD</option>
                    @foreach($daftarSkpd as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status Pengaduan</label>
                <select x-model="status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua Status</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Proses">Proses</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
        </div>

        {{-- ---- Filter: Rekap Bulanan ---- --}}
        <div x-show="selectedJenis === 'bulanan'" class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Periode</label>
                <select x-model="periode" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    @foreach($bulanList as $bulan)
                        <option value="{{ $bulan }}" @selected($bulan === 'Maret')>{{ $bulan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun</label>
                <select x-model="tahun" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    @foreach($tahunList as $th)
                        <option value="{{ $th }}" @selected($th === 2024)>{{ $th }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">SKPD</label>
                <select x-model="skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua SKPD</option>
                    @foreach($daftarSkpd as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status Pengaduan</label>
                <select x-model="status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua Status</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Proses">Proses</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
        </div>

        {{-- ---- Filter: Rekap Tahunan ---- --}}
        <div x-show="selectedJenis === 'tahunan'" class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun</label>
                <select x-model="tahun" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    @foreach($tahunList as $th)
                        <option value="{{ $th }}" @selected($th === 2024)>{{ $th }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">SKPD</label>
                <select x-model="skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua SKPD</option>
                    @foreach($daftarSkpd as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status Pengaduan</label>
                <select x-model="status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua Status</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Proses">Proses</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
        </div>

        {{-- ---- Filter Aktif (chips, otomatis mengikuti jenis & isian) ---- --}}
        <div class="flex flex-wrap items-center gap-2 mt-6 pt-5 border-t border-slate-100">
            <span class="text-xs font-bold text-slate-400 tracking-wide">FILTER AKTIF:</span>
            <template x-for="chip in activeChips" :key="chip">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-50 text-navy px-3 py-1.5 rounded-full">
                    <span x-text="chip"></span>
                    <svg class="w-3 h-3 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </span>
            </template>
        </div>
    </div>

    <div class="flex justify-end items-center gap-3 mb-10">
        <button type="button" @click="terapkan()"
                class="bg-navy hover:bg-navy-dark text-white font-semibold text-sm px-6 py-3 rounded-xl flex items-center gap-2 card-shadow">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
            Terapkan Filter
        </button>
        <button type="button" :disabled="!applied" :class="applied ? '' : 'opacity-40 cursor-not-allowed'"
                class="border border-navy text-navy font-semibold text-sm px-5 py-3 rounded-xl flex items-center gap-2 bg-white hover:bg-blue-50">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 001 1h4"/><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><path d="M9 15h6M9 11h3"/></svg>
            Export PDF
        </button>
    </div>

    {{-- ================= STEP 3 : PRATINJAU LAPORAN ================= --}}
    <div id="pratinjau-section" class="flex items-center justify-between mb-1">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full bg-navy text-white text-sm font-bold flex items-center justify-center shrink-0">3</span>
            <h2 class="text-xl font-bold text-slate-800">Pratinjau Laporan</h2>
        </div>
        <div class="flex items-center gap-2" x-show="applied">
            <button type="button" @click="zoom = Math.min(zoom + 0.1, 1.3)" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4M9 11h4"/></svg>
            </button>
            <button type="button" @click="zoom = Math.max(zoom - 0.1, 0.7)" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4M9 11h4M11 9"/></svg>
            </button>
        </div>
    </div>
    <p class="text-sm text-slate-500 ml-10 mb-4">Tampilan pratinjau dokumen sebelum diekspor.</p>

    {{-- Placeholder sebelum filter diterapkan --}}
    <div x-show="!applied" class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl py-16 flex flex-col items-center justify-center text-center">
        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><path d="M9 15h6M9 11h6M9 7h2"/>
        </svg>
        <p class="font-semibold text-slate-500">Pratinjau belum tersedia</p>
        <p class="text-sm text-slate-400 mt-1">Pilih jenis laporan &amp; atur filter, lalu klik <span class="font-semibold text-navy">"Terapkan Filter"</span> untuk menampilkan pratinjau.</p>
    </div>

    <div x-show="applied" x-cloak class="bg-slate-100 rounded-2xl p-8 flex justify-center">
        <div class="bg-white rounded-lg shadow-lg p-10 w-full max-w-3xl origin-top transition-transform" :style="`transform: scale(${zoom})`">

            {{-- Kop Surat --}}
            <div class="flex items-start gap-4 pb-4 border-b-4 border-navy">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/></svg>
                </div>
                <div class="text-center flex-1">
                    <p class="font-extrabold text-slate-900 tracking-wide">{{ $instansi['nama_pemerintah'] }}</p>
                    <p class="font-semibold text-slate-700">{{ $instansi['nama_dinas'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $instansi['alamat'] }}</p>
                </div>
            </div>

            <h3 class="text-center font-extrabold text-lg text-slate-900 mt-6 mb-6 tracking-wide">LAPORAN REKAPITULASI PENGADUAN</h3>

            <div class="grid grid-cols-2 gap-y-1 text-sm mb-6">
                <p><span class="text-slate-500">Bulan:</span> <span class="font-semibold text-slate-800 ml-2" x-text="applied?.previewPeriode"></span></p>
                <p><span class="text-slate-500">Unit Kerja:</span> <span class="font-semibold text-slate-800 ml-2" x-text="applied?.skpd || 'Semua SKPD'"></span></p>
                <p><span class="text-slate-500">Dicetak:</span> <span class="font-semibold text-slate-800 ml-2">{{ now()->translatedFormat('d F Y') }}</span></p>
                <p><span class="text-slate-500">Status:</span> <span class="font-semibold text-slate-800 ml-2" x-text="applied?.status || 'Semua Status'"></span></p>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-50 rounded-xl text-center py-4">
                    <p class="text-[11px] font-semibold text-slate-400 tracking-wide">TOTAL PENGADUAN</p>
                    <p class="text-2xl font-extrabold text-navy mt-1">{{ number_format($rekap['total_pengaduan']) }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl text-center py-4">
                    <p class="text-[11px] font-semibold text-slate-400 tracking-wide">SELESAI</p>
                    <p class="text-2xl font-extrabold text-navy mt-1">{{ number_format($rekap['selesai']) }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl text-center py-4">
                    <p class="text-[11px] font-semibold text-slate-400 tracking-wide">PROSENTASE</p>
                    <p class="text-2xl font-extrabold text-navy mt-1">{{ $rekap['persentase'] }}%</p>
                </div>
            </div>

            <div class="border border-dashed border-slate-200 rounded-xl py-10 flex flex-col items-center justify-center mb-6">
                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                <p class="text-xs text-slate-400 mt-2">Visualisasi Tren Pengaduan Bulanan</p>
            </div>

            <table class="w-full text-sm mb-8">
                <thead>
                    <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                        <th class="py-2 font-semibold">No.</th>
                        <th class="py-2 font-semibold">SKPD / Instansi</th>
                        <th class="py-2 font-semibold text-right">Masuk</th>
                        <th class="py-2 font-semibold text-right">Proses</th>
                        <th class="py-2 font-semibold text-right">Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rincianSkpd as $row)
                    <tr class="border-b border-slate-50">
                        <td class="py-2.5">{{ $row['no'] }}</td>
                        <td class="py-2.5 font-medium text-slate-700">{{ $row['skpd'] }}</td>
                        <td class="py-2.5 text-right">{{ $row['masuk'] }}</td>
                        <td class="py-2.5 text-right">{{ $row['proses'] }}</td>
                        <td class="py-2.5 text-right">{{ $row['selesai'] }}</td>
                    </tr>
                    @endforeach
                    <tr class="font-bold text-slate-800">
                        <td class="py-2.5" colspan="2">TOTAL</td>
                        <td class="py-2.5 text-right">{{ $totalRincian['masuk'] }}</td>
                        <td class="py-2.5 text-right">{{ $totalRincian['proses'] }}</td>
                        <td class="py-2.5 text-right">{{ $totalRincian['selesai'] }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end">
                <div class="text-sm text-center">
                    <p class="text-slate-600">Garut, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="text-slate-600 mb-10">Kepala Dinas Kominfo,</p>
                    <p class="font-bold text-slate-800 underline">{{ $instansi['kepala_dinas'] }}</p>
                    <p class="text-slate-500">NIP. {{ $instansi['nip'] }}</p>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    function laporanForm() {
        return {
            selectedJenis: 'harian',
            zoom: 1,
            applied: null, // snapshot filter yang sudah ditekan "Terapkan Filter" — null berarti preview belum tampil
            jenisList: [
                { id: 'harian',   label: 'Rekap Harian',   icon: '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 10h18"/>' },
                { id: 'mingguan', label: 'Rekap Mingguan', icon: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>' },
                { id: 'bulanan',  label: 'Rekap Bulanan',  icon: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>' },
                { id: 'tahunan',  label: 'Rekap Tahunan',  icon: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>' },
            ],
            // filter fields
            tanggal: '{{ now()->format('Y-m-d') }}',
            tanggalMulai: '{{ now()->format('Y-m-d') }}',
            tanggalSelesai: '{{ now()->format('Y-m-d') }}',
            periode: 'Maret',
            tahun: '2024',
            skpd: '',
            status: '',

            init() {},

            // Dipanggil saat tombol "Terapkan Filter" diklik.
            // Preview (Step 3) hanya berubah/tampil setelah ini dipanggil,
            // bukan otomatis live saat user masih mengubah-ubah filter.
            terapkan() {
                this.applied = {
                    jenis: this.selectedJenis,
                    previewPeriode: this.previewPeriode,
                    skpd: this.skpd,
                    status: this.status,
                };
                // scroll halus ke bagian pratinjau setelah diterapkan
                this.$nextTick(() => {
                    document.getElementById('pratinjau-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },

            get activeChips() {
                const chips = [];
                if (this.selectedJenis === 'harian') {
                    chips.push('Tanggal Laporan: ' + this.formatTanggal(this.tanggal));
                } else if (this.selectedJenis === 'mingguan') {
                    chips.push('Periode: ' + this.formatTanggal(this.tanggalMulai) + ' - ' + this.formatTanggal(this.tanggalSelesai));
                } else if (this.selectedJenis === 'bulanan') {
                    chips.push('Periode: ' + this.periode);
                    chips.push('Tahun: ' + this.tahun);
                } else if (this.selectedJenis === 'tahunan') {
                    chips.push('Tahun: ' + this.tahun);
                }
                if (this.skpd) chips.push('SKPD: ' + this.skpd);
                if (this.status) chips.push('Status: ' + this.status);
                return chips;
            },

            get previewPeriode() {
                if (this.selectedJenis === 'harian') return this.formatTanggal(this.tanggal);
                if (this.selectedJenis === 'mingguan') return this.formatTanggal(this.tanggalMulai) + ' - ' + this.formatTanggal(this.tanggalSelesai);
                if (this.selectedJenis === 'bulanan') return this.periode + ' ' + this.tahun;
                if (this.selectedJenis === 'tahunan') return 'Tahun ' + this.tahun;
                return '-';
            },

            formatTanggal(value) {
                if (!value) return '-';
                const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                const d = new Date(value);
                return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
            },
        }
    }
</script>
@endpush