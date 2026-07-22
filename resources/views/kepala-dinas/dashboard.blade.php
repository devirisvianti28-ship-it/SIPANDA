@extends('layouts.app-kepala-dinas')

@section('title', 'Dashboard Monitoring Pengaduan')

@section('content')

    <h1 class="text-3xl font-extrabold text-navy">Dashboard Monitoring Pengaduan</h1>
    <p class="text-slate-500 mt-1 mb-6">Monitoring Pengaduan Masyarakat Berbasis SP4N-LAPOR!</p>

    {{-- ================= STAT CARDS ================= --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">

        <div class="bg-white rounded-2xl p-4 card-shadow">
            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 16v-4M12 16V8m4 8v-6"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">TOTAL</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-400 mt-1">Pengaduan Masuk</p>
        </div>

        <div class="bg-white rounded-2xl p-4 card-shadow">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">SELESAI</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $stats['selesai'] }}</p>
            <p class="text-xs text-navy font-semibold mt-1">{{ $stats['selesai_persen'] }}% Selesai</p>
        </div>

        <div class="bg-white rounded-2xl p-4 card-shadow">
            <div class="w-9 h-9 rounded-lg bg-sky-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4v5h5M20 20v-5h-5"/><path d="M4 9a8 8 0 0114-5M20 15a8 8 0 01-14 5"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">PROSES</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $stats['proses'] }}</p>
            <p class="text-xs text-slate-400 mt-1">Sudah Ditanggapi, Belum Selesai</p>
        </div>

        <div class="bg-white rounded-2xl p-4 card-shadow">
            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 21h18M4 21V9l8-6 8 6v12M9 21v-6h6v6"/></svg>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 tracking-wide">SKPD</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $stats['skpd'] }}</p>
            <p class="text-xs text-slate-400 mt-1">Instansi Aktif</p>
        </div>

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
                    <a href="{{ route('kepala-dinas.dashboard', ['tahun' => now()->year]) }}"
                       class="px-3 py-1.5 rounded-full transition {{ $tahunAktif === now()->year ? 'bg-navy text-white' : 'text-slate-500 hover:text-navy' }}">
                        Tahun Ini
                    </a>
                    <a href="{{ route('kepala-dinas.dashboard', ['tahun' => now()->year - 1]) }}"
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    // ---- Tren Pengaduan Bulanan (Bar Chart) ----
    const trenCtx = document.getElementById('trenChart');
    new Chart(trenCtx, {
        type: 'bar',
        data: {
            labels: @json($trenBulanan['labels']),
            datasets: [{
                data: @json($trenBulanan['data']),
                backgroundColor: @json($trenBulanan['data']).map((_, i) =>
                    i === {{ $trenBulanan['highlightIndex'] }} ? '#0B3D91' : '#BFDBFE'
                ),
                borderRadius: 6,
                maxBarThickness: 34,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false, grid: { display: false } },
                x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 12, weight: '600' } } }
            }
        }
    });

    // ---- Status Pengaduan (Donut Chart) ----
    const statusCtx = document.getElementById('statusDonut');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json(collect($statusLegend)->pluck('label')),
            datasets: [{
                data: @json(collect($statusLegend)->pluck('persen')),
                backgroundColor: @json(collect($statusLegend)->pluck('color')),
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '72%',
            plugins: { legend: { display: false } }
        }
    });

    // ---- Laju Penyelesaian (Line Chart) ----
    const lajuCtx = document.getElementById('lajuChart');
    new Chart(lajuCtx, {
        type: 'line',
        data: {
            labels: @json($lajuPenyelesaian['labels']),
            datasets: [{
                data: @json($lajuPenyelesaian['data']),
                borderColor: '#3B82F6',
                borderWidth: 3,
                tension: 0.5,
                pointRadius: 0,
                fill: false,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false, grid: { display: false } },
                x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 12, weight: '600' } } }
            }
        }
    });
</script>
@endpush