@extends('layouts.app')

@section('title', 'Monitoring SKPD')

@php
    $searchPlaceholder = 'Cari SKPD atau data pengaduan...';
    $userName = 'Administrator';
    $userRole = 'Super Admin';
@endphp

@section('content')

    <h1 class="text-3xl font-extrabold text-slate-900">Monitoring SKPD</h1>
    <p class="text-slate-500 mt-1 mb-6">Pantau kinerja penanganan pengaduan oleh setiap Satuan Kerja Perangkat Daerah.</p>

    {{-- ================= STAT CARDS ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

        <div class="bg-white rounded-2xl p-5 card-shadow">
            <div class="flex items-center justify-between mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M3 21h18M4 21V9l8-6 8 6v12M9 21v-6h6v6"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full">{{ $stats['total_skpd_note'] }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 tracking-wide">TOTAL SKPD</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total_skpd'] }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 card-shadow">
            <div class="flex items-center justify-between mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.3 2.3L16 9.5"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-navy bg-blue-50 px-2.5 py-1 rounded-full">{{ $stats['skpd_aktif_note'] }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 tracking-wide">SKPD AKTIF</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['skpd_aktif'] }}</p>
        </div>
    </div>

    {{-- ================= FILTER BAR ================= --}}
    <form method="GET" class="bg-white rounded-2xl p-4 card-shadow mb-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tahun</label>
                <select name="tahun" class="bg-slate-50 border border-slate-200 rounded-full text-sm font-medium text-slate-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    @foreach($tahunList ?? [] as $t)
                        <option value="{{ $t }}" @selected(request('tahun', now()->year) == $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Periode</label>
                <select name="bulan" class="bg-slate-50 border border-slate-200 rounded-full text-sm font-medium text-slate-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    @foreach($bulanList ?? [] as $key => $label)
                        <option value="{{ $key }}" @selected(request('bulan') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Cari Tracking ID / Pelapor</label>
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full text-sm font-medium text-slate-600 px-4 py-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                    </svg>
                    <input type="text" name="tracking" value="{{ request('tracking') }}" placeholder="ID: SPG-2026-..."
                           class="w-full bg-transparent focus:outline-none text-slate-600 placeholder:text-slate-400">
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-4">
            <span class="text-xs font-semibold text-slate-400">Filter Aktif:</span>

            @if(request('tahun'))
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-navy bg-blue-50 px-3 py-1 rounded-full">
                    Tahun: {{ request('tahun') }} <span class="text-navy/60">&times;</span>
                </span>
            @endif

            @if(request('bulan'))
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-navy bg-blue-50 px-3 py-1 rounded-full">
                    Bulan: {{ $bulanList[request('bulan')] ?? request('bulan') }} <span class="text-navy/60">&times;</span>
                </span>
            @endif

            <div class="ml-auto flex items-center gap-4">
                <a href="{{ url()->current() }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Reset</a>
                <button type="submit" class="bg-navy hover:bg-navy-dark text-white text-sm font-semibold px-5 py-2 rounded-full">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    {{-- ================= DAFTAR KINERJA SKPD ================= --}}
    <div class="bg-white rounded-2xl card-shadow mb-6 overflow-hidden">
        <div class="px-6 py-5">
            <h2 class="text-lg font-bold text-slate-800">Daftar Kinerja SKPD</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-400 tracking-wide border-y border-slate-100">
                        <th class="px-6 py-3">NAMA SKPD</th>
                        <th class="px-3 py-3">TOTAL</th>
                        <th class="px-3 py-3">SELESAI</th>
                        <th class="px-3 py-3">BELUM ADA TANGGAPAN</th>
                        <th class="px-3 py-3">DITINDAKLANJUTI</th>
                        <th class="px-3 py-3">PENYELESAIAN</th>
                        <th class="px-3 py-3">STATUS</th>
                        <th class="px-3 py-3">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kinerjaSkpd as $skpd)
                    @php
                        $barColor = $skpd['persen'] < 60 ? 'bg-red-500' : 'bg-navy';
                        $badgeClasses = match($skpd['status_color']) {
                            'hijau', 'green'   => 'bg-emerald-50 text-emerald-500',
                            'biru', 'blue'     => 'bg-blue-50 text-navy',
                            'merah', 'red'     => 'bg-red-50 text-red-500',
                            'kuning', 'yellow', 'amber' => 'bg-amber-50 text-amber-600',
                            default            => 'bg-slate-100 text-slate-500',
                        };
                    @endphp
                    <tr class="border-b border-slate-50 last:border-0 align-top">
                        <td class="px-6 py-5 font-semibold text-slate-800 max-w-[180px]">{{ $skpd['nama'] }}</td>
                        <td class="px-3 py-5 font-bold text-slate-800">{{ $skpd['total'] }}</td>
                        <td class="px-3 py-5 font-bold text-navy">{{ $skpd['selesai'] }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $skpd['belum_ada_tanggapan'] ?? $skpd['proses'] ?? 0 }}</td>
                        <td class="px-3 py-5 font-semibold text-red-500">{{ $skpd['ditindaklanjuti'] ?? $skpd['menunggu'] ?? 0 }}</td>
                        <td class="px-3 py-5 min-w-[150px]">
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                                <span>{{ $skpd['persen'] }}%</span>
                                <span>Target: {{ $skpd['target'] }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $barColor }} rounded-full" style="width: {{ $skpd['persen'] }}%"></div>
                            </div>
                        </td>
                        <td class="px-3 py-5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase {{ $badgeClasses }} px-3 py-1.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $skpd['status'] }}
                            </span>
                        </td>
                        <td class="px-3 py-5">
                            <a href="{{ route('data-pengaduan', ['skpd' => $skpd['nama']]) }}" class="font-semibold text-navy">Lihat Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= TREN & TOP PERFORMERS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-800">Tren Penyelesaian 7 Hari Terakhir</h2>
                <button class="flex items-center gap-2 text-xs font-semibold text-slate-500 border border-slate-200 rounded-full px-4 py-1.5">
                    Per Minggu
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </button>
            </div>
            <canvas id="trenSkpdChart" height="110"></canvas>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow flex flex-col">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Top Performers</h2>
            <div class="space-y-3">
                @foreach($topPerformers as $p)
                <div class="flex items-center justify-between {{ $p['rank'] === 1 ? 'bg-blue-50' : 'bg-slate-50' }} rounded-xl px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold text-white {{ $p['rank'] === 1 ? 'bg-navy' : 'bg-slate-400' }}">{{ $p['rank'] }}</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $p['nama'] }}</span>
                    </div>
                    <span class="text-sm font-bold text-navy">{{ $p['persen'] }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const trenSkpdCtx = document.getElementById('trenSkpdChart');
    new Chart(trenSkpdCtx, {
        type: 'line',
        data: {
            labels: @json($trenPenyelesaian['labels']),
            datasets: [{
                data: @json($trenPenyelesaian['data']),
                borderColor: '#1D4ED8',
                backgroundColor: 'rgba(29,78,216,0.08)',
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 0,
                fill: true,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false, grid: { display: false } },
                x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 11, weight: '600' } } }
            }
        }
    });
</script>
@endpush