@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div x-data="{
        jenis: 'bulanan',
        tab: 'ringkasan',

        // state filter per jenis laporan
        harian: { tanggal: '2026-03-15', skpd: 'Semua Instansi / SKPD', status: 'Semua Status' },
        mingguan: { mulai: '2026-03-15', selesai: '2026-03-15', skpd: 'Semua Instansi / SKPD', status: 'Semua Status' },
        bulanan: { periode: 'Maret', tahun: '2024', skpd: 'Semua SKPD', status: 'Semua Status' },
        tahunan: { tahun: '2024', skpd: 'Semua SKPD', status: 'Semua Status' },

        formatTanggal(iso) {
            if (!iso) return '-';
            const d = new Date(iso + 'T00:00:00');
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        // tag filter aktif, menyesuaikan jenis laporan yang sedang dipilih
        filters() {
            if (this.jenis === 'harian') {
                return [
                    { key: 'tanggal', label: 'Tanggal Laporan: ' + this.formatTanggal(this.harian.tanggal) },
                ];
            }
            if (this.jenis === 'mingguan') {
                return [
                    { key: 'rentang', label: 'Periode: ' + this.formatTanggal(this.mingguan.mulai) + ' - ' + this.formatTanggal(this.mingguan.selesai) },
                ];
            }
            if (this.jenis === 'bulanan') {
                return [
                    { key: 'periode', label: 'Periode: ' + this.bulanan.periode },
                    { key: 'tahun', label: 'Tahun: ' + this.bulanan.tahun },
                ];
            }
            // tahunan
            return [
                { key: 'tahun', label: 'Tahun: ' + this.tahunan.tahun },
            ];
        },

        removeFilter(key) {
            if (this.jenis === 'harian' && key === 'tanggal') this.harian.tanggal = '';
            if (this.jenis === 'mingguan' && key === 'rentang') { this.mingguan.mulai = ''; this.mingguan.selesai = ''; }
            if (this.jenis === 'bulanan' && key === 'periode') this.bulanan.periode = '';
            if (this.jenis === 'bulanan' && key === 'tahun') this.bulanan.tahun = '';
            if (this.jenis === 'tahunan' && key === 'tahun') this.tahunan.tahun = '';
        }
    }">

    <h1 class="text-2xl font-extrabold text-slate-800">Laporan Pengaduan</h1>
    <p class="text-slate-500 mt-1 mb-6">Ikuti langkah-langkah di bawah untuk membuat laporan yang diinginkan.</p>

    {{-- ============ STEP 1: JENIS LAPORAN ============ --}}
    <div class="bg-white rounded-2xl card-shadow p-6 mb-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-5 h-5 rounded-full bg-navy text-white text-xs font-bold flex items-center justify-center">1</span>
            <h2 class="font-bold text-slate-800">Jenis Laporan</h2>
        </div>
        <p class="text-sm text-slate-400 mb-5 ml-7">Pilih jenis laporan yang ingin dibuat.</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-2xl">
            @php
                $jenisLaporan = [
                    'harian'   => ['label' => 'Rekap Harian',   'icon' => 'calendar-day'],
                    'mingguan' => ['label' => 'Rekap Mingguan', 'icon' => 'calendar-week'],
                    'bulanan'  => ['label' => 'Rekap Bulanan',  'icon' => 'grid'],
                    'tahunan'  => ['label' => 'Rekap Tahunan',  'icon' => 'calendar'],
                ];
            @endphp

            @foreach($jenisLaporan as $key => $item)
                <button type="button" @click="jenis = '{{ $key }}'"
                    class="relative border rounded-xl p-4 flex flex-col items-center justify-center gap-2 text-center transition"
                    :class="jenis === '{{ $key }}' ? 'border-navy bg-brand-softblue/40 ring-1 ring-navy' : 'border-slate-200 hover:border-slate-300'">

                    <template x-if="jenis === '{{ $key }}'">
                        <span class="absolute top-2 right-2 w-4 h-4 rounded-full bg-navy text-white flex items-center justify-center">
                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    </template>

                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                         :class="jenis === '{{ $key }}' ? 'bg-navy text-white' : 'bg-slate-100 text-slate-500'">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">{{ $item['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ============ STEP 2: FILTER DATA (dinamis per jenis laporan) ============ --}}
    <div class="bg-white rounded-2xl card-shadow p-6 mb-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-5 h-5 rounded-full bg-navy text-white text-xs font-bold flex items-center justify-center">2</span>
            <h2 class="font-bold text-slate-800">Filter Data</h2>
        </div>
        <p class="text-sm text-slate-400 mb-5 ml-7">Sesuaikan data yang akan ditampilkan pada laporan.</p>

        {{-- --- HARIAN: Tanggal Laporan, SKPD, Status --- --}}
        <div x-show="jenis === 'harian'" x-cloak class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Laporan</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    <input type="date" x-model="harian.tanggal"
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Filter SKPD</label>
                <select x-model="harian.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Instansi / SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select x-model="harian.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="button"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        {{-- --- MINGGUAN: Tanggal Mulai, Tanggal Selesai, SKPD, Status --- --}}
        <div x-show="jenis === 'mingguan'" x-cloak class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Mulai</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    <input type="date" x-model="mingguan.mulai"
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Selesai</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    <input type="date" x-model="mingguan.selesai"
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Filter SKPD</label>
                <select x-model="mingguan.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Instansi / SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select x-model="mingguan.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="button"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        {{-- --- BULANAN: Periode, Tahun, SKPD, Status --- --}}
        <div x-show="jenis === 'bulanan'" x-cloak class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Periode</label>
                <select x-model="bulanan.periode" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Januari</option>
                    <option>Februari</option>
                    <option>Maret</option>
                    <option>April</option>
                    <option>Mei</option>
                    <option>Juni</option>
                    <option>Juli</option>
                    <option>Agustus</option>
                    <option>September</option>
                    <option>Oktober</option>
                    <option>November</option>
                    <option>Desember</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
                <select x-model="bulanan.tahun" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>2024</option>
                    <option>2025</option>
                    <option>2026</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">SKPD</label>
                <select x-model="bulanan.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select x-model="bulanan.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="button"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        {{-- --- TAHUNAN: Tahun, SKPD, Status --- --}}
        <div x-show="jenis === 'tahunan'" x-cloak class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
                <select x-model="tahunan.tahun" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>2024</option>
                    <option>2025</option>
                    <option>2026</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">SKPD</label>
                <select x-model="tahunan.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select x-model="tahunan.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="button"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        {{-- Filter aktif: dihitung ulang otomatis sesuai jenis laporan yang aktif --}}
        <div class="flex items-center gap-2 mt-4 text-xs flex-wrap">
            <span class="font-semibold text-slate-500">FILTER AKTIF:</span>
            <template x-for="f in filters()" :key="f.key">
                <span class="inline-flex items-center gap-1 bg-brand-softblue text-navy px-2.5 py-1 rounded-full font-medium">
                    <span x-text="f.label"></span>
                    <button type="button" @click="removeFilter(f.key)" class="hover:text-navy-dark">&times;</button>
                </span>
            </template>
        </div>
    </div>

    {{-- ============ TABS ============ --}}
    <div class="border-b border-slate-200 mb-6">
        <nav class="flex gap-6 text-sm font-medium">
            @php
                $tabs = [
                    'ringkasan'   => ['label' => 'Ringkasan'],
                    'visualisasi' => ['label' => 'Visualisasi'],
                    'rekap'       => ['label' => 'Data Rekap'],
                    'rekomendasi' => ['label' => 'Rekomendasi'],
                ];
            @endphp
            @foreach($tabs as $key => $t)
                <button type="button" @click="tab = '{{ $key }}'"
                    class="pb-3 -mb-px border-b-2 transition"
                    :class="tab === '{{ $key }}' ? 'border-navy text-navy font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700'">
                    {{ $t['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ============ TAB: RINGKASAN ============ --}}
    <div x-show="tab === 'ringkasan'" x-cloak class="space-y-6">

        {{-- Tren Pengaduan Bulanan --}}
        <div class="bg-white rounded-2xl card-shadow p-6">
            <div class="flex items-center justify-between mb-1">
                <div>
                    <h3 class="font-bold text-slate-800">Tren Pengaduan Bulanan</h3>
                    <p class="text-xs text-slate-400">Visualisasi volume pengaduan yang masuk sepanjang tahun 2026</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-navy"></span> Masuk</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Selesai</span>
                </div>
            </div>
            <div class="mt-4">
                <canvas id="trenChart" height="70"></canvas>
            </div>
        </div>

        {{-- Aduan per SKPD + Distribusi Status --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl card-shadow p-6">
                <h3 class="font-bold text-slate-800 mb-4">Aduan per SKPD (Top 5)</h3>
                <div class="space-y-4">
                    @foreach([
                        ['DISDUKCAPIL', 342, 100],
                        ['Dinas Pekerjaan Umum', 280, 82],
                        ['Dinas Kesehatan', 195, 57],
                        ['Satpol PP', 150, 44],
                    ] as [$nama, $jumlah, $persen])
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-slate-700">{{ $nama }}</span>
                                <span class="text-slate-400">{{ $jumlah }} Aduan</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-navy h-2 rounded-full" style="width: {{ $persen }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl card-shadow p-6">
                <h3 class="font-bold text-slate-800 mb-4">Distribusi Status</h3>
                <div class="flex items-center gap-6">
                    <div class="relative w-32 h-32 shrink-0">
                        <canvas id="statusDonut"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-extrabold text-slate-800">1.4K</span>
                            <span class="text-[10px] text-slate-400 font-semibold tracking-wide">TOTAL</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai (82%)</p>
                        <p class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Belum Ada Tanggapan (13%)</p>
                        <p class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Belum Ditindaklanjuti (5%)</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- % Penyelesaian + Kategori Terbanyak --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl card-shadow p-6 flex flex-col items-center text-center">
                <h3 class="font-bold text-slate-800 self-start mb-6">% Penyelesaian Pengaduan</h3>
                <div class="relative w-36 h-36">
                    <canvas id="progressDonut"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-extrabold text-navy">82%</span>
                        <span class="text-xs text-slate-400">Selesai</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-4">Target KPI: 85% di Akhir Tahun 2024</p>
            </div>

            <div class="bg-white rounded-2xl card-shadow p-6 lg:col-span-2">
                <h3 class="font-bold text-slate-800 mb-5">Kategori Pengaduan Terbanyak</h3>
                <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                    @foreach([
                        ['Jalan & Jembatan', 423, 100],
                        ['Layanan Kesehatan', 312, 74],
                        ['Kependudukan', 256, 60],
                        ['Lingkungan Hidup', 189, 45],
                        ['Keamanan & Ketertiban', 142, 34],
                        ['Lain-lain', 110, 26],
                    ] as [$nama, $jumlah, $persen])
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">{{ $nama }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 relative">
                                <div class="bg-navy/80 h-2 rounded-full" style="width: {{ $persen }}%"></div>
                                <span class="absolute right-0 -top-5 text-xs text-slate-500">{{ $jumlah }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Rekapitulasi Data Pengaduan --}}
        <div class="bg-white rounded-2xl card-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">Rekapitulasi Data Pengaduan</h3>
                <span class="text-xs text-slate-400">Menampilkan 5 dari 42 SKPD</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs whitespace-nowrap">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100">
                            <th class="text-left font-semibold px-3 py-2">Tracking ID</th>
                            <th class="text-left font-semibold px-3 py-2">Tanggal Masuk</th>
                            <th class="text-left font-semibold px-3 py-2">Nama Pelapor</th>
                            <th class="text-left font-semibold px-3 py-2">Klasifikasi</th>
                            <th class="text-left font-semibold px-3 py-2">Tanggapan</th>
                            <th class="text-left font-semibold px-3 py-2">Kategori</th>
                            <th class="text-left font-semibold px-3 py-2">Judul Laporan</th>
                            <th class="text-left font-semibold px-3 py-2">Instansi</th>
                            <th class="text-left font-semibold px-3 py-2">Status</th>
                            <th class="text-left font-semibold px-3 py-2">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach([
                            ['SPD-09821', '24 Okt 2024', 'Budi Setiawan', 'Infrastruktur', 'Sudah Ada Tanggapan', 'emerald', 'CAT-001', 'Jalan Berlubang di Wanaraj', 'PUPR', 'Selesai', 'emerald', '4.8/5'],
                            ['SPD-09821', '24 Okt 2024', 'Budi Setiawan', 'Infrastruktur', 'Belum Ada Tanggapan', 'red', 'CAT-001', 'Jalan Berlubang di Wanaraj', 'PUPR', 'Belum Selesai', 'red', '4.8/5'],
                        ] as [$track, $tgl, $nama, $klas, $tanggap, $tanggapColor, $kat, $judul, $instansi, $status, $statusColor, $rating])
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-3 font-semibold text-navy">{{ $track }}</td>
                                <td class="px-3 py-3 text-slate-600">{{ $tgl }}</td>
                                <td class="px-3 py-3 text-slate-600">{{ $nama }}</td>
                                <td class="px-3 py-3 text-slate-600">{{ $klas }}</td>
                                <td class="px-3 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-{{ $tanggapColor }}-50 text-{{ $tanggapColor }}-600">{{ $tanggap }}</span>
                                </td>
                                <td class="px-3 py-3 text-slate-600">{{ $kat }}</td>
                                <td class="px-3 py-3 text-slate-600">{{ $judul }}</td>
                                <td class="px-3 py-3 text-slate-600">{{ $instansi }}</td>
                                <td class="px-3 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-{{ $statusColor }}-50 text-{{ $statusColor }}-600">{{ $status }}</span>
                                </td>
                                <td class="px-3 py-3 font-semibold text-amber-500">{{ $rating }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Rekomendasi & Tindak Lanjut --}}
        <div class="bg-white rounded-2xl card-shadow p-6">
            <h3 class="font-bold text-slate-800 mb-3">Rekomendasi & Tindak Lanjut</h3>
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="flex items-center gap-3 px-3 py-2 border-b border-slate-200 bg-slate-50 text-slate-500">
                    <button type="button" class="font-bold text-sm hover:text-navy">B</button>
                    <button type="button" class="italic text-sm hover:text-navy">I</button>
                    <span class="w-px h-4 bg-slate-200"></span>
                    <button type="button" class="hover:text-navy">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    </button>
                    <button type="button" class="hover:text-navy">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h1M7 6h13M4 12h1M7 12h13M4 18h1M7 18h13"/></svg>
                    </button>
                    <button type="button" class="hover:text-navy">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13.5 6.5l4 4L9 19H5v-4z"/></svg>
                    </button>
                </div>
                <textarea rows="5" class="w-full px-4 py-3 text-sm text-slate-700 focus:outline-none resize-none"
                    placeholder="Tulis rekomendasi dan tindak lanjut di sini...">1. Diperlukan koordinasi lintas sektor antara PUPR dan DLH untuk penanganan banjir di area Garut Kota.
2. Penambahan tim lapangan pada akhir pekan untuk merespon aduan darurat infrastruktur.
3. Melakukan sosialisasi penggunaan aplikasi SAPA untuk aduan kesehatan masyarakat.</textarea>
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" class="flex items-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-5 py-2.5 hover:bg-navy-dark">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/><path d="M9 12h6M9 16h6M9 8h2"/>
                    </svg>
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    {{-- ============ TAB LAIN (placeholder) ============ --}}
    <div x-show="tab === 'visualisasi'" x-cloak class="bg-white rounded-2xl card-shadow p-10 text-center text-slate-400 text-sm">
        Konten tab Visualisasi.
    </div>
    <div x-show="tab === 'rekap'" x-cloak class="bg-white rounded-2xl card-shadow p-10 text-center text-slate-400 text-sm">
        Konten tab Data Rekap.
    </div>
    <div x-show="tab === 'rekomendasi'" x-cloak class="bg-white rounded-2xl card-shadow p-10 text-center text-slate-400 text-sm">
        Konten tab Rekomendasi.
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tren Pengaduan Bulanan (line chart)
    new Chart(document.getElementById('trenChart'), {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'],
            datasets: [
                {
                    label: 'Masuk',
                    data: [40, 55, 70, 90, 120, 150, 130, 110, 95, 80, 70, 60],
                    borderColor: '#0B3D91',
                    backgroundColor: 'rgba(11,61,145,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2,
                },
                {
                    label: 'Selesai',
                    data: [30, 45, 60, 75, 100, 130, 115, 95, 80, 68, 60, 50],
                    borderColor: '#34D399',
                    backgroundColor: 'transparent',
                    borderDash: [4, 4],
                    fill: false,
                    tension: 0.4,
                    pointRadius: 0,
                    borderWidth: 2,
                }
            ]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 11 } } }
            }
        }
    });

    // Distribusi Status (donut)
    new Chart(document.getElementById('statusDonut'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [82, 13, 5],
                backgroundColor: ['#10B981', '#FBBF24', '#EF4444'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '75%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });

    // % Penyelesaian (donut progress)
    new Chart(document.getElementById('progressDonut'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [82, 18],
                backgroundColor: ['#0B3D91', '#E2E8F0'],
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '78%',
            circumference: 360,
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });
});

</script>
@endpush