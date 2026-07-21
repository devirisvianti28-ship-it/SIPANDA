@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div x-data="{
        jenis: 'bulanan',
        tab: 'ringkasan',
        filters: [
            { key: 'periode', label: 'Periode: Maret' },
            { key: 'tahun', label: 'Tahun: 2024' },
        ]
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

<<<<<<< Updated upstream
        <div class="bg-white rounded-2xl p-4 card-shadow">
            <div class="w-9 h-9 rounded-lg bg-sky-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M4 9a8 8 0 0114-5M20 15a8 8 0 01-14 5"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">PROSES</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $stats['proses'] }}</p>
            <p class="text-xs text-slate-400 mt-1">Sudah Ditanggapi, Belum Selesai</p>
        </div>
=======
            @foreach($jenisLaporan as $key => $item)
                <button type="button" @click="jenis = '{{ $key }}'"
                    class="relative border rounded-xl p-4 flex flex-col items-center justify-center gap-2 text-center transition"
                    :class="jenis === '{{ $key }}' ? 'border-navy bg-brand-softblue/40 ring-1 ring-navy' : 'border-slate-200 hover:border-slate-300'">
>>>>>>> Stashed changes

                    <template x-if="jenis === '{{ $key }}'">
                        <span class="absolute top-2 right-2 w-4 h-4 rounded-full bg-navy text-white flex items-center justify-center">
                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    </template>

<<<<<<< Updated upstream
        <div class="bg-white rounded-2xl p-4 card-shadow">
            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 109-9M3 4v5h5"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">IMPORT</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $stats['import_tanggal'] }}</p>
            <p class="text-xs text-slate-400 mt-1">Pukul {{ $stats['import_jam'] }} WIB</p>
        </div>
    </div>

    {{-- ================= TREN & STATUS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="font-bold text-slate-800">Tren Pengaduan Bulanan</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Statistik pengaduan masuk tahun {{ $tahunAktif }}</p>
                </div>
                {{-- ============ TOGGLE TAHUN INI / TAHUN LALU ============ --}}
                <div class="flex bg-slate-100 rounded-full p-1 text-xs font-semibold">
                    <a href="{{ route('dashboard', ['tahun' => now()->year]) }}"
                       class="px-3 py-1.5 rounded-full transition {{ $tahunAktif === now()->year ? 'bg-navy text-white' : 'text-slate-500 hover:text-navy' }}">
                        Tahun Ini
                    </a>
                    <a href="{{ route('dashboard', ['tahun' => now()->year - 1]) }}"
                       class="px-3 py-1.5 rounded-full transition {{ $tahunAktif === (now()->year - 1) ? 'bg-navy text-white' : 'text-slate-500 hover:text-navy' }}">
                        Tahun Lalu
                    </a>
                </div>
            </div>
            <canvas id="trenChart" height="110"></canvas>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow flex flex-col">
            <h2 class="font-bold text-slate-800 mb-4">Status Pengaduan</h2>
            {{-- Hanya 2 kategori: Selesai / Belum Selesai — persis sama seperti kolom
                 `status` di tabel pengaduan (Data Pengaduan & Monitoring SKPD juga
                 pakai 2 nilai ini, bukan "Pending"/"Diproses"). --}}
            <div class="relative flex-1 flex items-center justify-center">
                <canvas id="statusDonut" width="200" height="200"></canvas>
                <div class="absolute text-center">
                    <p class="text-xl font-extrabold text-slate-800">{{ $stats['total_singkat'] }}</p>
                    <p class="text-xs text-slate-400">Data Total</p>
                </div>
            </div>
            <div class="space-y-2.5 mt-4">
                @foreach($statusLegend as $item)
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $item['color'] }}"></span>
                        {{ $item['label'] }}
                    </span>
                    <span class="font-semibold text-slate-800">{{ $item['persen'] }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ================= TOP 5 SKPD & LAJU PENYELESAIAN ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <div class="bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-slate-800">Top 5 SKPD Terbanyak</h2>
                <a href="{{ route('monitoring-skpd', ['tahun' => $tahunAktif]) }}" class="text-sm font-semibold text-navy">Lihat Semua</a>
            </div>
            <div class="space-y-5">
                @forelse($topSkpd as $skpd)
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-sm font-semibold text-slate-700">{{ $skpd['nama'] }}</p>
                        <p class="text-sm font-bold text-slate-800">{{ $skpd['jumlah'] }}</p>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-navy rounded-full" style="width: {{ $skpd['persen'] }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400">Belum ada data SKPD untuk tahun {{ $tahunAktif }}.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow flex flex-col">
            <h2 class="font-bold text-slate-800 mb-4">Laju Penyelesaian</h2>
            <canvas id="lajuChart" class="flex-1" height="140"></canvas>
            <p class="text-sm font-semibold {{ $lajuKenaikan >= 0 ? 'text-navy' : 'text-red-500' }} mt-4 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8M21 7v6h-6"/></svg>
                {{ $lajuKenaikan >= 0 ? '+' : '' }}{{ $lajuKenaikan }}% Penyelesaian {{ $lajuKenaikan >= 0 ? 'lebih cepat' : 'lebih lambat' }} minggu ini
            </p>
        </div>
    </div>

    {{-- ================= AKTIVITAS TERBARU ================= --}}
    <div class="bg-white rounded-2xl card-shadow mb-6 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5">
            <h2 class="font-bold text-slate-800">Aktivitas Terbaru</h2>
            <a href="{{ route('data-pengaduan') }}" class="text-sm font-semibold text-navy">Lihat Semua Data</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase text-slate-400 border-y border-slate-100">
                        <th class="px-6 py-3 font-semibold">Tracking ID</th>
                        <th class="px-3 py-3 font-semibold">Tanggal</th>
                        <th class="px-3 py-3 font-semibold">Pelapor</th>
                        <th class="px-3 py-3 font-semibold">Judul Pengaduan</th>
                        <th class="px-3 py-3 font-semibold">SKPD Terkait</th>
                        <th class="px-3 py-3 font-semibold">Tanggapan</th>
                        <th class="px-3 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aktivitas as $row)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="px-6 py-4">
                            <a href="{{ route('data-pengaduan', ['tracking_id' => $row['tracking_id']]) }}" class="font-semibold text-navy">{{ $row['tracking_id'] }}</a>
                        </td>
                        <td class="px-3 py-4 text-slate-500">{{ $row['tanggal'] }}</td>
                        <td class="px-3 py-4 font-semibold text-slate-700">{{ $row['pelapor'] }}</td>
                        <td class="px-3 py-4 text-slate-600">{{ $row['judul'] }}</td>
                        <td class="px-3 py-4 text-slate-600">{{ $row['skpd'] }}</td>
                        <td class="px-3 py-4">
                            @if($row['sudah_ditanggapi'])
                                <span class="inline-block text-xs font-semibold bg-blue-50 text-navy px-3 py-1 rounded-full">Sudah Ada Tanggapan</span>
                            @else
                                <span class="inline-block text-xs font-semibold bg-red-50 text-red-500 px-3 py-1 rounded-full">Belum Ada Tanggapan</span>
                            @endif
                        </td>
                        <td class="px-3 py-4">
                            {{-- Render LANGSUNG nilai kolom `status` dari database.
                                 Nilainya cuma "Selesai" atau "Belum Selesai" —
                                 sama persis kayak di halaman Data Pengaduan,
                                 tidak dikarang jadi "Pending" di sini. --}}
                            <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full
                                {{ $row['status'] === 'Selesai' ? 'bg-blue-100 text-navy' : 'bg-red-100 text-red-500' }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada aktivitas pengaduan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
=======
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

    {{-- ============ STEP 2: FILTER DATA ============ --}}
    <div class="bg-white rounded-2xl card-shadow p-6 mb-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-5 h-5 rounded-full bg-navy text-white text-xs font-bold flex items-center justify-center">2</span>
            <h2 class="font-bold text-slate-800">Filter Data</h2>
        </div>
        <p class="text-sm text-slate-400 mb-5 ml-7">Sesuaikan data yang akan ditampilkan pada laporan.</p>

        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Periode</label>
                <select class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Maret</option>
                    <option>April</option>
                    <option>Mei</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
                <select class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>2024</option>
                    <option>2025</option>
                    <option>2026</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">SKPD</label>
                <select class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
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

        <div class="flex items-center gap-2 mt-4 text-xs">
            <span class="font-semibold text-slate-500">FILTER AKTIF:</span>
            <template x-for="f in filters" :key="f.key">
                <span class="inline-flex items-center gap-1 bg-brand-softblue text-navy px-2.5 py-1 rounded-full font-medium">
                    <span x-text="f.label"></span>
                    <button type="button" @click="filters = filters.filter(x => x.key !== f.key)" class="hover:text-navy-dark">&times;</button>
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
            <div class="mt-4 relative h-64">
                <canvas id="trenChart"></canvas>
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
>>>>>>> Stashed changes
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
            responsive: true,
            maintainAspectRatio: false,
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
            responsive: true,
            maintainAspectRatio: false,
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
            responsive: true,
            maintainAspectRatio: false,
            cutout: '78%',
            circumference: 360,
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });
});
</script>
@endpush