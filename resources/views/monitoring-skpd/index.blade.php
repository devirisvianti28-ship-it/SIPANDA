@extends(auth()->user()->hasRole('kepala_dinas') ? 'layouts.app-kepala-dinas' : 'layouts.app')

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

    {{-- ================= FILTER BAR (auto-submit, tanpa tombol Terapkan) ================= --}}
    <form method="GET" id="filterForm" class="bg-white rounded-2xl p-4 card-shadow mb-6">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tahun</label>
                <select name="tahun" onchange="document.getElementById('filterForm').submit()"
                        class="bg-slate-50 border border-slate-200 rounded-full text-sm font-medium text-slate-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    @foreach($tahunList ?? [] as $t)
                        <option value="{{ $t }}" @selected((int) request('tahun', now()->year) === (int) $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Periode</label>
                <select name="bulan" onchange="document.getElementById('filterForm').submit()"
                        class="bg-slate-50 border border-slate-200 rounded-full text-sm font-medium text-slate-600 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option value="" @selected(! request('bulan'))>Semua Bulan</option>
                    @foreach($bulanList ?? [] as $key => $label)
                        <option value="{{ $key }}" @selected((string) request('bulan') === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Cari Nama SKPD</label>
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full text-sm font-medium text-slate-600 px-4 py-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                    </svg>
                    {{-- Input teks di-debounce (jeda 500ms setelah berhenti ngetik) supaya
                         halaman gak reload setiap huruf, tapi tetap otomatis submit --}}
                    <input type="text" name="nama_skpd" value="{{ request('nama_skpd') }}" placeholder="Contoh: Dinas Kesehatan"
                           id="inputNamaSkpd"
                           class="w-full bg-transparent focus:outline-none text-slate-600 placeholder:text-slate-400">
                </div>
            </div>
        </div>

        {{-- ============ Chip filter aktif — "Reset Semua" tetap ada ============ --}}
        @if(request('tahun') || request('bulan') || request('nama_skpd'))
        <div class="flex flex-wrap items-center gap-2 mt-4">
            <span class="text-xs font-semibold text-slate-400">Filter Aktif:</span>

            @if(request('tahun'))
                <a href="{{ request()->fullUrlWithQuery(['tahun' => null]) }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-navy bg-blue-50 px-3 py-1 rounded-full hover:bg-blue-100">
                    Tahun: {{ request('tahun') }} <span class="text-navy/60">&times;</span>
                </a>
            @endif

            @if(request('bulan'))
                <a href="{{ request()->fullUrlWithQuery(['bulan' => null]) }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-navy bg-blue-50 px-3 py-1 rounded-full hover:bg-blue-100">
                    Bulan: {{ $bulanList[request('bulan')] ?? request('bulan') }} <span class="text-navy/60">&times;</span>
                </a>
            @endif

            @if(request('nama_skpd'))
                <a href="{{ request()->fullUrlWithQuery(['nama_skpd' => null]) }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-navy bg-blue-50 px-3 py-1 rounded-full hover:bg-blue-100">
                    SKPD: "{{ request('nama_skpd') }}" <span class="text-navy/60">&times;</span>
                </a>
            @endif

            <div class="ml-auto">
                <a href="{{ url()->current() }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Reset Semua</a>
            </div>
        </div>
        @endif
    </form>

    {{-- ================= TREN & TOP PERFORMERS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <div class="lg:col-span-2 bg-white rounded-2xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-800">Tren Penyelesaian</h2>
                <div class="flex items-center gap-1 bg-slate-100 rounded-full p-1">
                    <button type="button" id="btnTrenBulan"
                            class="text-xs font-semibold px-4 py-1.5 rounded-full bg-navy text-white transition-colors">
                        Per Bulan
                    </button>
                    <button type="button" id="btnTrenTahun"
                            class="text-xs font-semibold px-4 py-1.5 rounded-full text-slate-500 transition-colors">
                        Per Tahun
                    </button>
                </div>
            </div>
            <canvas id="trenSkpdChart" height="110"></canvas>
        </div>

        <div class="bg-white rounded-2xl p-6 card-shadow flex flex-col">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Top Performers</h2>
            <div class="space-y-3">
                @forelse($topPerformers as $p)
                <div class="flex items-center justify-between {{ $p['rank'] === 1 ? 'bg-blue-50' : 'bg-slate-50' }} rounded-xl px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold text-white {{ $p['rank'] === 1 ? 'bg-navy' : 'bg-slate-400' }}">{{ $p['rank'] }}</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $p['nama'] }}</span>
                    </div>
                    <span class="text-sm font-bold text-navy">{{ $p['persen'] }}%</span>
                </div>
                @empty
                <p class="text-sm text-slate-400">Belum ada data untuk periode ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= DAFTAR KINERJA SKPD ================= --}}
    <div class="bg-white rounded-2xl card-shadow mb-6 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-3.5">
            <h2 class="text-lg font-bold text-slate-800">Daftar Kinerja SKPD</h2>
            <span class="text-sm text-slate-400">{{ $kinerjaSkpd->count() }} SKPD ditemukan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="text-left text-[11px] font-semibold text-slate-400 tracking-wide border-y border-slate-100 bg-slate-50">
                        <th class="px-2.5 py-1.5">NAMA SKPD</th>
                        <th class="px-1.5 py-1.5">TOTAL</th>
                        <th class="px-1.5 py-1.5">SELESAI</th>
                        <th class="px-1.5 py-1.5 whitespace-normal max-w-[85px] leading-tight">BELUM ADA TANGGAPAN</th>
                        <th class="px-1.5 py-1.5">DITINDAKLANJUTI</th>
                        <th class="px-1.5 py-1.5">PENYELESAIAN</th>
                        <th class="px-1.5 py-1.5">STATUS</th>
                        <th class="px-1.5 py-1.5">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kinerjaSkpd as $skpd)
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
                        <td class="px-2.5 py-2 font-semibold text-slate-800 max-w-[170px] truncate" title="{{ $skpd['nama'] }}">{{ $skpd['nama'] }}</td>
                        <td class="px-1.5 py-2 font-bold text-slate-800">{{ $skpd['total'] }}</td>
                        <td class="px-1.5 py-2 font-bold text-navy">{{ $skpd['selesai'] }}</td>
                        <td class="px-1.5 py-2 text-slate-500">{{ $skpd['belum_ada_tanggapan'] ?? $skpd['proses'] ?? 0 }}</td>
                        <td class="px-1.5 py-2 font-semibold text-red-500">{{ $skpd['ditindaklanjuti'] ?? $skpd['menunggu'] ?? 0 }}</td>
                        <td class="px-1.5 py-2 min-w-[110px]">
                            <div class="flex items-center text-[11px] text-slate-500 mb-0.5">
                                <span>{{ $skpd['persen'] }}%</span>
                            </div>
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $barColor }} rounded-full" style="width: {{ $skpd['persen'] }}%"></div>
                            </div>
                        </td>
                        <td class="px-1.5 py-2">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase {{ $badgeClasses }} px-2 py-0.5 rounded-full whitespace-nowrap">
                                {{ $skpd['status'] }}
                            </span>
                        </td>
                        <td class="px-1.5 py-2 whitespace-nowrap">
                            <a href="{{ route('data-pengaduan', array_filter(['skpd' => $skpd['nama'], 'tahun' => request('tahun')])) }}"
                               class="font-semibold text-navy">Lihat Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                            Tidak ada SKPD yang cocok dengan filter ini.
                        </td>
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
    const dataBulanan = @json($trenPenyelesaian['bulanan']);
    const dataTahunan = @json($trenPenyelesaian['tahunan']);

    const trenSkpdCtx = document.getElementById('trenSkpdChart');
    const trenChart = new Chart(trenSkpdCtx, {
        type: 'line',
        data: {
            labels: dataBulanan.labels,
            datasets: [{
                data: dataBulanan.data,
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

    const btnBulan = document.getElementById('btnTrenBulan');
    const btnTahun = document.getElementById('btnTrenTahun');

    function setTrenMode(mode) {
        const isBulan = mode === 'bulan';
        const dataset = isBulan ? dataBulanan : dataTahunan;

        trenChart.data.labels = dataset.labels;
        trenChart.data.datasets[0].data = dataset.data;
        trenChart.update();

        btnBulan.classList.toggle('bg-navy', isBulan);
        btnBulan.classList.toggle('text-white', isBulan);
        btnBulan.classList.toggle('text-slate-500', !isBulan);

        btnTahun.classList.toggle('bg-navy', !isBulan);
        btnTahun.classList.toggle('text-white', !isBulan);
        btnTahun.classList.toggle('text-slate-500', isBulan);
    }

    btnBulan.addEventListener('click', () => setTrenMode('bulan'));
    btnTahun.addEventListener('click', () => setTrenMode('tahun'));

    // ============ AUTO-SUBMIT UNTUK INPUT PENCARIAN NAMA SKPD ============
    // Select tahun/bulan langsung submit lewat onchange di HTML-nya.
    // Untuk input teks, kita tunggu user berhenti ngetik dulu (debounce 500ms)
    // baru submit form, biar gak reload halaman tiap 1 huruf.
    const inputNamaSkpd = document.getElementById('inputNamaSkpd');
    let debounceTimer;
    inputNamaSkpd.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
</script>
@endpush