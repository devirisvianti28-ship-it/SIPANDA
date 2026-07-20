@extends('layouts.app')

@section('title', 'Laporan Pengaduan')

@php
    $searchPlaceholder = 'Cari laporan...';
    $userName = 'Administrator';
    $userRole = 'Pusat Bantuan';
@endphp

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .tab-active { color: #1e2a52; border-bottom: 2px solid #1e2a52; font-weight: 700; }
    .tab-inactive { color: #94a3b8; border-bottom: 2px solid transparent; }
    .status-dot { width: 8px; height: 8px; border-radius: 9999px; display: inline-block; }
</style>
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
            <button type="button" @click="selectedJenis = jenis.id; applied = null"
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

    <div class="bg-white rounded-2xl p-6 card-shadow mb-10">

        {{-- ---- Filter: Rekap Harian ---- --}}
        <div x-show="selectedJenis === 'harian'" class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Laporan</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <input type="date" x-model="tanggal" class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
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
            <div class="md:col-span-2">
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

        {{-- ---- Filter: Rekap Bulanan ---- --}}
        <div x-show="selectedJenis === 'bulanan'" class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
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
            <div class="flex gap-3">
                <select x-model="status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="">Semua Status</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Proses">Proses</option>
                    <option value="Pending">Pending</option>
                </select>
                <button type="button" @click="terapkan()"
                        class="bg-navy hover:bg-navy-dark text-white font-semibold text-sm px-5 py-2.5 rounded-lg flex items-center gap-2 card-shadow whitespace-nowrap shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M9 11l2 2 4-4"/></svg>
                    Tampilkan Laporan
                </button>
            </div>
        </div>

        {{-- ---- Filter: Rekap Tahunan ---- --}}
        <div x-show="selectedJenis === 'tahunan'" class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
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

        {{-- Tombol Tampilkan Laporan untuk jenis Harian, Mingguan & Tahunan (bulanan sudah punya sendiri di baris filter) --}}
        <div x-show="selectedJenis === 'harian' || selectedJenis === 'mingguan' || selectedJenis === 'tahunan'" class="flex justify-end mt-5">
            <button type="button" @click="terapkan()"
                    class="bg-navy hover:bg-navy-dark text-white font-semibold text-sm px-6 py-3 rounded-xl flex items-center gap-2 card-shadow">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M9 11l2 2 4-4"/></svg>
                Tampilkan Laporan
            </button>
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

    {{-- ================= STEP 3 : PRATINJAU / TAB LAPORAN ================= --}}
    <div id="pratinjau-section" class="flex items-center justify-between mb-1">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full bg-navy text-white text-sm font-bold flex items-center justify-center shrink-0">3</span>
            <h2 class="text-xl font-bold text-slate-800">Pratinjau Laporan</h2>
        </div>
    </div>
    <p class="text-sm text-slate-500 ml-10 mb-4">Tampilan pratinjau dokumen sebelum diekspor.</p>

    {{-- Placeholder sebelum filter diterapkan --}}
    <div x-show="!applied" class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl py-16 flex flex-col items-center justify-center text-center">
        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><path d="M9 15h6M9 11h6M9 7h2"/>
        </svg>
        <p class="font-semibold text-slate-500">Pratinjau belum tersedia</p>
        <p class="text-sm text-slate-400 mt-1">Pilih jenis laporan &amp; atur filter, lalu klik <span class="font-semibold text-navy">"Tampilkan Laporan"</span> untuk menampilkan pratinjau.</p>
    </div>

    <div x-show="applied" x-cloak class="bg-white rounded-2xl card-shadow overflow-hidden">

        {{-- ---- Tab Navigation ---- --}}
        <div class="flex items-center gap-8 px-6 border-b border-slate-100">
            <template x-for="tab in tabs" :key="tab.id">
                <button type="button" @click="activeTab = tab.id"
                        class="flex items-center gap-2 py-4 text-sm transition"
                        :class="activeTab === tab.id ? 'tab-active' : 'tab-inactive hover:text-slate-600'">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-html="tab.icon"></svg>
                    <span x-text="tab.label"></span>
                </button>
            </template>
        </div>

        <div class="p-6">

            {{-- =================================================== --}}
            {{-- TAB 1 : RINGKASAN --}}
            {{-- =================================================== --}}
            <div x-show="activeTab === 'ringkasan'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Tren Pengaduan Bulanan --}}
                <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl p-5 card-shadow">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-bold text-slate-800">Tren Pengaduan Bulanan</h3>
                        <div class="flex items-center gap-4 text-xs font-semibold text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-navy"></span> Masuk</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Visualisasi volume pengaduan yang masuk sepanjang tahun <span x-text="tahun"></span></p>
                    <svg viewBox="0 0 600 190" class="w-full h-48">
                        <polygon :points="trenAreaPoints" fill="#1e2a52" opacity="0.06"/>
                        <polyline :points="trenMasukPoints" fill="none" stroke="#1e2a52" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline :points="trenSelesaiPoints" fill="none" stroke="#10b981" stroke-width="2.5" stroke-dasharray="5,5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="flex justify-between text-[11px] text-slate-400 mt-1 px-1">
                        <template x-for="m in bulanPendek" :key="m"><span x-text="m"></span></template>
                    </div>
                </div>

                {{-- SAPA AI Insight --}}
                <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 2l2.4 5.8L20 10l-5.6 2.2L12 18l-2.4-5.8L4 10l5.6-2.2z"/></svg>
                        <h3 class="font-bold text-slate-800">SAPA AI Insight</h3>
                    </div>
                    <ul class="space-y-4">
                        <template x-for="(insight, idx) in aiInsights" :key="idx">
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-navy text-white text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5" x-text="String(idx + 1).padStart(2,'0')"></span>
                                <span class="text-sm text-slate-600 leading-snug" x-text="insight"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Aduan per SKPD (Top 5) --}}
                <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl p-5 card-shadow">
                    <h3 class="font-bold text-slate-800 mb-4">Aduan per SKPD (Top 5)</h3>
                    <div class="space-y-4">
                        <template x-for="row in skpdTop5" :key="row.nama">
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="font-medium text-slate-700" x-text="row.nama"></span>
                                    <span class="text-slate-400" x-text="row.jumlah + ' Aduan'"></span>
                                </div>
                                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-navy rounded-full" :style="`width:${(row.jumlah / skpdTop5[0].jumlah) * 100}%`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Distribusi Status --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-5 card-shadow">
                    <h3 class="font-bold text-slate-800 mb-4">Distribusi Status</h3>
                    <div class="relative w-36 h-36 mx-auto mb-4">
                        <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="4"></circle>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#10b981" stroke-width="4"
                                    :stroke-dasharray="`${statusDistribusi[0].persen} ${100 - statusDistribusi[0].persen}`" stroke-linecap="round"></circle>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f59e0b" stroke-width="4"
                                    :stroke-dasharray="`${statusDistribusi[1].persen} ${100 - statusDistribusi[1].persen}`"
                                    :stroke-dashoffset="`${-statusDistribusi[0].persen}`" stroke-linecap="round"></circle>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#ef4444" stroke-width="4"
                                    :stroke-dasharray="`${statusDistribusi[2].persen} ${100 - statusDistribusi[2].persen}`"
                                    :stroke-dashoffset="`${-(statusDistribusi[0].persen + statusDistribusi[1].persen)}`" stroke-linecap="round"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-extrabold text-slate-800" x-text="totalPengaduanFormatted"></span>
                            <span class="text-[10px] text-slate-400 font-semibold tracking-wide">TOTAL</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <template x-for="s in statusDistribusi" :key="s.label">
                            <div class="flex items-center justify-between text-xs">
                                <span class="flex items-center gap-2 font-medium text-slate-600">
                                    <span class="status-dot" :style="`background:${s.warna}`"></span>
                                    <span x-text="s.label"></span>
                                </span>
                                <span class="font-semibold text-slate-500" x-text="s.persen + '%'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- =================================================== --}}
            {{-- TAB 2 : VISUALISASI --}}
            {{-- =================================================== --}}
            <div x-show="activeTab === 'visualisasi'" class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- % Penyelesaian Pengaduan --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-5 card-shadow flex flex-col items-center">
                    <h3 class="font-bold text-slate-800 self-start mb-6">% Penyelesaian Pengaduan</h3>
                    <div class="relative w-40 h-40">
                        <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="4"></circle>
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1e2a52" stroke-width="4"
                                    :stroke-dasharray="`${rekap.persentase} ${100 - rekap.persentase}`" stroke-linecap="round"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-extrabold text-navy" x-text="rekap.persentase + '%'"></span>
                            <span class="text-[11px] text-slate-400 font-semibold">Selesai</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-5">Target KPI: <span class="font-semibold text-slate-500">85%</span> di Akhir Tahun <span x-text="tahun"></span></p>
                </div>

                {{-- Kategori Pengaduan Terbanyak --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-5 card-shadow">
                    <h3 class="font-bold text-slate-800 mb-4">Kategori Pengaduan Terbanyak</h3>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                        <template x-for="k in kategoriTerbanyak" :key="k.nama">
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="font-medium text-slate-600" x-text="k.nama"></span>
                                    <span class="text-slate-400" x-text="k.jumlah"></span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-navy rounded-full" :style="`width:${(k.jumlah / kategoriTerbanyak[0].jumlah) * 100}%`"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Tren Pengaduan (skala lebih besar untuk tab visualisasi) --}}
                <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl p-5 card-shadow">
                    <h3 class="font-bold text-slate-800 mb-4">Tren Pengaduan Bulanan — Masuk vs Selesai</h3>
                    <svg viewBox="0 0 600 220" class="w-full h-56">
                        <polyline :points="trenMasukPoints" fill="none" stroke="#1e2a52" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline :points="trenSelesaiPoints" fill="none" stroke="#10b981" stroke-width="2.5" stroke-dasharray="5,5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            {{-- =================================================== --}}
            {{-- TAB 3 : DATA REKAP --}}
            {{-- =================================================== --}}
            <div x-show="activeTab === 'data-rekap'">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800">Rekapitulasi Data Pengaduan</h3>
                    <span class="text-xs text-slate-400">Menampilkan <span x-text="dataPengaduan.length"></span> dari <span x-text="totalPengaduanFormatted"></span> SKPD</span>
                </div>
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-xs whitespace-nowrap">
                        <thead>
                            <tr class="text-left text-slate-500 bg-slate-50 border-b border-slate-200">
                                <th class="py-3 px-3 font-semibold">Tracking ID</th>
                                <th class="py-3 px-3 font-semibold">Tanggal Laporan Masuk</th>
                                <th class="py-3 px-3 font-semibold">Waktu</th>
                                <th class="py-3 px-3 font-semibold">Nama Pelapor</th>
                                <th class="py-3 px-3 font-semibold">Klasifikasi</th>
                                <th class="py-3 px-3 font-semibold">Tanggapan</th>
                                <th class="py-3 px-3 font-semibold">Keterangan</th>
                                <th class="py-3 px-3 font-semibold">ID Kategori</th>
                                <th class="py-3 px-3 font-semibold">Kategori</th>
                                <th class="py-3 px-3 font-semibold">Judul Laporan</th>
                                <th class="py-3 px-3 font-semibold">Isi Laporan Awal</th>
                                <th class="py-3 px-3 font-semibold">Isi Laporan Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in dataPengaduan" :key="row.tracking">
                                <tr class="border-b border-slate-50 text-slate-600">
                                    <td class="py-3 px-3 font-semibold text-navy" x-text="row.tracking"></td>
                                    <td class="py-3 px-3" x-text="row.tanggal"></td>
                                    <td class="py-3 px-3" x-text="row.waktu"></td>
                                    <td class="py-3 px-3" x-text="row.pelapor"></td>
                                    <td class="py-3 px-3" x-text="row.klasifikasi"></td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-semibold"
                                              :class="row.tanggapan === 'Sudah Ada Tanggapan' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500'"
                                              x-text="row.tanggapan"></span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-semibold"
                                              :class="row.keterangan === 'Selesai' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500'"
                                              x-text="row.keterangan"></span>
                                    </td>
                                    <td class="py-3 px-3" x-text="row.idKategori"></td>
                                    <td class="py-3 px-3" x-text="row.kategori"></td>
                                    <td class="py-3 px-3" x-text="row.judul"></td>
                                    <td class="py-3 px-3 max-w-[160px] truncate" :title="row.isiAwal" x-text="row.isiAwal"></td>
                                    <td class="py-3 px-3 max-w-[160px] truncate" :title="row.isiAkhir" x-text="row.isiAkhir"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- =================================================== --}}
            {{-- TAB 4 : INSIGHT AI --}}
            {{-- =================================================== --}}
            <div x-show="activeTab === 'insight-ai'" class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <template x-for="(insight, idx) in aiInsights" :key="idx">
                    <div class="bg-white border border-slate-100 rounded-2xl p-5 card-shadow">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                            <svg class="w-4 h-4 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 2l2.4 5.8L20 10l-5.6 2.2L12 18l-2.4-5.8L4 10l5.6-2.2z"/></svg>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed" x-text="insight"></p>
                    </div>
                </template>
            </div>

            {{-- =================================================== --}}
            {{-- TAB 5 : REKOMENDASI --}}
            {{-- =================================================== --}}
            <div x-show="activeTab === 'rekomendasi'">
                <h3 class="font-bold text-slate-800 mb-4">Rekomendasi &amp; Tindak Lanjut</h3>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <div class="flex items-center gap-1 px-3 py-2 border-b border-slate-200 bg-slate-50">
                        <button type="button" class="w-7 h-7 rounded hover:bg-slate-200 flex items-center justify-center font-bold text-sm text-slate-600">B</button>
                        <button type="button" class="w-7 h-7 rounded hover:bg-slate-200 flex items-center justify-center italic text-sm text-slate-600">I</button>
                        <span class="w-px h-4 bg-slate-300 mx-1"></span>
                        <button type="button" class="w-7 h-7 rounded hover:bg-slate-200 flex items-center justify-center text-slate-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        </button>
                        <button type="button" class="w-7 h-7 rounded hover:bg-slate-200 flex items-center justify-center text-slate-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="9" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><path d="M13 6h8M13 12h8M13 18h8"/></svg>
                        </button>
                    </div>
                    <textarea x-model="rekomendasiText" rows="6"
                              class="w-full p-4 text-sm text-slate-700 leading-relaxed focus:outline-none resize-none"></textarea>
                </div>
                <div class="flex justify-end mt-5">
                    <button type="button" @click="generateReport()"
                            class="bg-navy hover:bg-navy-dark text-white font-semibold text-sm px-6 py-3 rounded-xl flex items-center gap-2 card-shadow">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 001 1h4"/><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><path d="M9 15h6M9 11h3"/></svg>
                        Generate Report
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= DOKUMEN CETAK (untuk export PDF) ================= --}}
    <div x-show="showPrintDoc" x-cloak class="mt-8">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-xl font-bold text-slate-800">Dokumen Laporan</h2>
            <div class="flex items-center gap-2">
                <button type="button" @click="zoom = Math.min(zoom + 0.1, 1.3)" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4M9 11h4"/></svg>
                </button>
                <button type="button" @click="zoom = Math.max(zoom - 0.1, 0.7)" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4M9 11h4"/></svg>
                </button>
            </div>
        </div>
        <p class="text-sm text-slate-500 mb-4">Dokumen siap cetak / ekspor PDF.</p>

        <div class="bg-slate-100 rounded-2xl p-8 flex justify-center">
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
                    <p><span class="text-slate-500">Periode:</span> <span class="font-semibold text-slate-800 ml-2" x-text="applied?.previewPeriode"></span></p>
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

                <table class="w-full text-sm mb-6">
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

                <div class="mb-8">
                    <p class="text-xs font-bold text-slate-400 tracking-wide mb-2">REKOMENDASI &amp; TINDAK LANJUT</p>
                    <p class="text-sm text-slate-600 whitespace-pre-line" x-text="rekomendasiText"></p>
                </div>

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

</div>

@endsection

@push('scripts')
<script>
    function laporanForm() {
        return {
            // ---------- state umum ----------
            selectedJenis: 'harian',
            activeTab: 'ringkasan',
            zoom: 1,
            applied: null,       // snapshot filter setelah "Tampilkan Laporan" ditekan
            showPrintDoc: false, // tampil setelah klik "Generate Report"

            jenisList: [
                { id: 'harian',   label: 'Rekap Harian',   icon: '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 10h18"/>' },
                { id: 'mingguan', label: 'Rekap Mingguan', icon: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>' },
                { id: 'bulanan',  label: 'Rekap Bulanan',  icon: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>' },
                { id: 'tahunan',  label: 'Rekap Tahunan',  icon: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>' },
            ],

            tabs: [
                { id: 'ringkasan',   label: 'Ringkasan',   icon: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>' },
                { id: 'visualisasi', label: 'Visualisasi', icon: '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>' },
                { id: 'data-rekap',  label: 'Data Rekap',  icon: '<path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><path d="M9 15h6M9 11h6M9 7h2"/>' },
                { id: 'insight-ai',  label: 'Insight AI',  icon: '<path d="M12 2l2.4 5.8L20 10l-5.6 2.2L12 18l-2.4-5.8L4 10l5.6-2.2z"/>' },
                { id: 'rekomendasi',label: 'Rekomendasi',  icon: '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>' },
            ],

            // ---------- filter fields ----------
            tanggal: '{{ now()->format('Y-m-d') }}',
            tanggalMulai: '{{ now()->format('Y-m-d') }}',
            tanggalSelesai: '{{ now()->format('Y-m-d') }}',
            periode: 'Maret',
            tahun: '2024',
            skpd: '',
            status: '',

            // ---------- data ringkasan / visualisasi ----------
            bulanPendek: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'],
            trenMasuk:   [60, 95, 70, 110, 90, 130, 100, 120, 85, 140, 95, 115],
            trenSelesai: [50, 80, 65, 90, 75, 105, 88, 100, 70, 118, 82, 98],

            aiInsights: [
                'Tingkat penyelesaian aduan mencapai 82%, naik 5% dari bulan lalu.',
                'Kategori Infrastruktur mendominasi 42% dari total keluhan masyarakat.',
                'Rating kepuasan pengguna di DISDUKCAPIL stabil di angka 4.9.',
            ],

            skpdTop5: [
                { nama: 'DISDUKCAPIL', jumlah: 342 },
                { nama: 'Dinas Pekerjaan Umum', jumlah: 280 },
                { nama: 'Dinas Kesehatan', jumlah: 195 },
                { nama: 'Satpol PP', jumlah: 150 },
            ],

            kategoriTerbanyak: [
                { nama: 'Jalan & Jembatan', jumlah: 403 },
                { nama: 'Layanan Kesehatan', jumlah: 312 },
                { nama: 'Kependudukan', jumlah: 254 },
                { nama: 'Lingkungan Hidup', jumlah: 189 },
                { nama: 'Keamanan & Ketertiban', jumlah: 142 },
                { nama: 'Lain-lain', jumlah: 119 },
            ],

            statusDistribusi: [
                { label: 'Selesai (82%)', persen: 82, warna: '#10b981' },
                { label: 'Belum Ada Tanggapan (13%)', persen: 13, warna: '#f59e0b' },
                { label: 'Belum Ditindaklanjuti (5%)', persen: 5, warna: '#ef4444' },
            ],

            dataPengaduan: [
                { tracking: 'IPD-9821', tanggal: '24 Okt 2024', waktu: '14:30', pelapor: 'Budi Setiawan', klasifikasi: 'Infrastruktur', tanggapan: 'Sudah Ada Tanggapan', keterangan: 'Selesai', idKategori: 'CAT-001', kategori: 'Infrastruktur', judul: 'Jalan Berlubang di Wanaraja', isiAwal: 'Ada lubang besar di jalan utama.', isiAkhir: 'Lubang telah ditambal.' },
                { tracking: 'SPD-09821', tanggal: '24 Okt 2024', waktu: '14:30', pelapor: 'Budi Setiawan', klasifikasi: 'Infrastruktur', tanggapan: 'Belum Ada Tanggapan', keterangan: 'Belum Selesai', idKategori: 'CAT-001', kategori: 'Infrastruktur', judul: 'Jalan Berlubang di Wanaraja', isiAwal: 'Ada lubang besar di jalan utama.', isiAkhir: '-' },
            ],

            // ---------- rekomendasi (editable) ----------
            rekomendasiText:
`1. Diperlukan koordinasi lintas sektor antara PUPR dan DLH untuk penanganan banjir di area Garut Kota.
2. Penambahan tim lapangan pada akhir pekan untuk merespon aduan darurat infrastruktur.
3. Melakukan sosialisasi penggunaan aplikasi SAPA untuk aduan kesehatan masyarakat.`,

            // ---------- data dari backend (Blade) ----------
            rekap: @json($rekap),

            init() {},

            terapkan() {
                this.applied = {
                    jenis: this.selectedJenis,
                    previewPeriode: this.previewPeriode,
                    skpd: this.skpd,
                    status: this.status,
                };
                this.activeTab = 'ringkasan';
                this.showPrintDoc = false;
                this.$nextTick(() => {
                    document.getElementById('pratinjau-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },

            generateReport() {
                this.showPrintDoc = true;
                this.$nextTick(() => {
                    this.$el.querySelector('[x-show="showPrintDoc"]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },

            get totalPengaduanFormatted() {
                const total = this.rekap?.total_pengaduan ?? 1400;
                return total >= 1000 ? (total / 1000).toFixed(1) + 'K' : total;
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

            // ---------- helper grafik tren (SVG polyline) ----------
            _scalePoints(arr) {
                const w = 600, h = 190, pad = 10;
                const max = Math.max(...this.trenMasuk, ...this.trenSelesai);
                const step = (w - pad * 2) / (arr.length - 1);
                return arr.map((v, i) => {
                    const x = pad + step * i;
                    const y = h - pad - (v / max) * (h - pad * 2);
                    return `${x},${y}`;
                }).join(' ');
            },
            get trenMasukPoints() { return this._scalePoints(this.trenMasuk); },
            get trenSelesaiPoints() { return this._scalePoints(this.trenSelesai); },
            get trenAreaPoints() {
                const line = this.trenMasukPoints;
                return `10,180 ${line} 590,180`;
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