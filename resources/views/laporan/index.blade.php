@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div x-data="{
        jenis: '{{ request('jenis', 'bulanan') }}',
        activeSection: '{{ $activeTab ?? 'ringkasan' }}',
        charts: {},

        // Isi manual rekomendasi & tindak lanjut. SEKARANG bentuknya 1 string
        // bebas (bukan object 4 poin lagi) — tiap baris (Enter) = 1 poin,
        // jumlahnya bebas. Ini yang dikirim ke Word lewat form 'Generate
        // Report' di bawah — BUKAN auto-generate dari data lagi.
        rekomendasi: '',

        harian: {
            tanggal: '{{ request('harian_tanggal', now()->format('Y-m-d')) }}',
            skpd: '{{ request('harian_skpd', 'Semua Instansi / SKPD') }}',
            status: '{{ request('harian_status', 'Semua Status') }}'
        },
        mingguan: {
            mulai: '{{ request('mingguan_mulai', now()->startOfWeek()->format('Y-m-d')) }}',
            selesai: '{{ request('mingguan_selesai', now()->endOfWeek()->format('Y-m-d')) }}',
            skpd: '{{ request('mingguan_skpd', 'Semua Instansi / SKPD') }}',
            status: '{{ request('mingguan_status', 'Semua Status') }}'
        },
        bulanan: {
            periode: '{{ request('bulanan_periode', $bulanList[now()->month - 1] ?? 'Januari') }}',
            tahun: '{{ request('bulanan_tahun', now()->year) }}',
            skpd: '{{ request('bulanan_skpd', 'Semua SKPD') }}',
            status: '{{ request('bulanan_status', 'Semua Status') }}'
        },
        tahunan: {
            tahun: '{{ request('tahunan_tahun', now()->year) }}',
            skpd: '{{ request('tahunan_skpd', 'Semua SKPD') }}',
            status: '{{ request('tahunan_status', 'Semua Status') }}'
        },

        formatTanggal(iso) {
            if (!iso) return '-';
            const d = new Date(iso + 'T00:00:00');
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

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
        },

        scrollToSection(key, updateHash = true) {
            this.activeSection = key;
            const el = document.getElementById('section-' + key);
            if (el) {
                const offset = 90; // biar nggak ketutup nav yang sticky
                const top = el.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top, behavior: 'smooth' });
            }
            if (updateHash) {
                history.pushState(null, '', '#' + key);
            }
        },

        initScrollSpy() {
            const validSections = ['ringkasan', 'visualisasi', 'rekap', 'rekomendasi'];
            const hash = window.location.hash.replace('#', '');

            this.$nextTick(() => this.renderCharts());

            if (validSections.includes(hash)) {
                this.$nextTick(() => setTimeout(() => this.scrollToSection(hash, false), 50));
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.activeSection = entry.target.id.replace('section-', '');
                    }
                });
            }, { rootMargin: '-100px 0px -60% 0px', threshold: 0 });

            validSections.forEach((key) => {
                const el = document.getElementById('section-' + key);
                if (el) observer.observe(el);
            });
        },

        renderCharts() {
            if (this.charts.tren) return;

            const dataGrafik = {{ \Illuminate\Support\Js::from($dataGrafik) }};

            const trenCtx = document.getElementById('trenChart').getContext('2d');
            const gradMasuk = trenCtx.createLinearGradient(0, 0, 0, 220);
            gradMasuk.addColorStop(0, 'rgba(11,61,145,0.25)');
            gradMasuk.addColorStop(1, 'rgba(11,61,145,0.02)');

            this.charts.tren = new Chart(trenCtx, {
                type: 'line',
                data: {
                    labels: dataGrafik.bulan_label,
                    datasets: [
                        {
                            label: 'Masuk',
                            data: dataGrafik.tren_masuk,
                            borderColor: '#0B3D91',
                            backgroundColor: gradMasuk,
                            fill: true,
                            tension: 0.45,
                            pointRadius: 0,
                            borderWidth: 2.5,
                        },
                        {
                            label: 'Selesai',
                            data: dataGrafik.tren_selesai,
                            borderColor: '#10B981',
                            backgroundColor: 'transparent',
                            borderDash: [4, 4],
                            fill: false,
                            tension: 0.45,
                            pointRadius: 0,
                            borderWidth: 2,
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { display: false, grid: { display: false } },
                        x: { grid: { display: false }, ticks: { color: '#94A3B8', font: { size: 11 } } }
                    }
                }
            });

            const barValueLabels = {
                id: 'barValueLabels',
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    chart.data.datasets.forEach((dataset, i) => {
                        chart.getDatasetMeta(i).data.forEach((bar, index) => {
                            const value = dataset.data[index];
                            ctx.save();
                            ctx.fillStyle = '#334155';
                            ctx.font = 'bold 11px sans-serif';
                            ctx.textAlign = 'center';
                            ctx.fillText(value, bar.x, bar.y - 8);
                            ctx.restore();
                        });
                    });
                }
            };

            const wrapLabel = (text, maxCharsPerLine = 14) => {
                const words = String(text).split(' ');
                const lines = [];
                let current = '';
                words.forEach((word) => {
                    const candidate = current ? current + ' ' + word : word;
                    if (candidate.length > maxCharsPerLine && current) {
                        lines.push(current);
                        current = word;
                    } else {
                        current = candidate;
                    }
                });
                if (current) lines.push(current);
                return lines;
            };

            new Chart(document.getElementById('skpdBarChart'), {
                type: 'bar',
                data: {
                    labels: dataGrafik.skpd_labels.map((l) => wrapLabel(l)),
                    datasets: [{
                        data: dataGrafik.skpd_data,
                        backgroundColor: ['#0B3D91', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD'],
                        borderRadius: 6,
                        maxBarThickness: 34,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false } },
                    layout: { padding: { top: 20 } },
                    scales: {
                        y: { display: false, grid: { display: false } },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#94A3B8',
                                font: { size: 10, weight: '600' },
                                maxRotation: 0,
                                minRotation: 0,
                                autoSkip: false,
                            }
                        }
                    }
                },
                plugins: [barValueLabels]
            });

            new Chart(document.getElementById('statusDonut'), {
                type: 'doughnut',
                data: {
                    labels: dataGrafik.status_labels,
                    datasets: [{
                        data: dataGrafik.status_data,
                        backgroundColor: ['#10B981', '#EF4444'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    animation: false,
                    cutout: '72%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });

            new Chart(document.getElementById('kategoriBarChart'), {
                type: 'bar',
                data: {
                    labels: dataGrafik.kategori_labels,
                    datasets: [{
                        data: dataGrafik.kategori_data,
                        backgroundColor: '#2563EB',
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    animation: false,
                    barPercentage: 0.8,
                    categoryPercentage: 0.8,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    layout: { padding: { right: 34 } },
                    scales: {
                        x: { display: false, grid: { display: false } },
                        y: { grid: { display: false }, ticks: { color: '#334155', font: { size: 11 } } }
                    }
                },
                plugins: [{
                    id: 'barValueLabelsH',
                    afterDatasetsDraw(chart) {
                        const { ctx } = chart;
                        chart.getDatasetMeta(0).data.forEach((bar, i) => {
                            const value = chart.data.datasets[0].data[i];
                            ctx.save();
                            ctx.fillStyle = '#0B3D91';
                            ctx.font = 'bold 11px sans-serif';
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(value, bar.x + 6, bar.y);
                            ctx.restore();
                        });
                    }
                }]
            });
        }
    }"
    x-init="initScrollSpy()">

    <h1 class="text-2xl font-extrabold text-slate-800">Laporan Pengaduan</h1>
    <p class="text-slate-500 mt-1 mb-6">Ikuti langkah-langkah di bawah untuk membuat laporan yang diinginkan.</p>

    {{-- ============ STEP 1: JENIS LAPORAN ============ --}}
    <div class="bg-white rounded-2xl card-shadow p-6 mb-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-5 h-5 rounded-full bg-navy text-white text-xs font-bold flex items-center justify-center">1</span>
            <h2 class="font-bold text-slate-800">Jenis Laporan</h2>
        </div>
        <p class="text-sm text-slate-400 mb-5 ml-7">Pilih jenis laporan yang ingin dibuat.</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
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
                    class="relative border rounded-xl p-5 flex flex-col items-center justify-center gap-2.5 text-center transition min-h-[110px]"
                    :class="jenis === '{{ $key }}' ? 'border-navy bg-brand-softblue/40 ring-1 ring-navy' : 'border-slate-200 hover:border-slate-300'">

                    <template x-if="jenis === '{{ $key }}'">
                        <span class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full bg-navy text-white flex items-center justify-center">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                    </template>

                    <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                         :class="jenis === '{{ $key }}' ? 'bg-navy text-white' : 'bg-slate-100 text-slate-500'">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            @switch($item['icon'])
                                @case('calendar-day')
                                    <rect x="4" y="5" width="16" height="16" rx="2" stroke-width="1.6"/>
                                    <path d="M4 9.5h16M8 5v-1.5M16 5v-1.5" stroke-linecap="round"/>
                                    <circle cx="12" cy="14.5" r="1.6" fill="currentColor" stroke="none"/>
                                    @break
                                @case('calendar-week')
                                    <rect x="4" y="5" width="16" height="16" rx="2" stroke-width="1.6"/>
                                    <path d="M4 9.5h16M8 5v-1.5M16 5v-1.5" stroke-linecap="round"/>
                                    <rect x="7.5" y="12.5" width="2.6" height="2.6" fill="currentColor" stroke="none"/>
                                    <rect x="10.7" y="12.5" width="2.6" height="2.6" fill="currentColor" stroke="none"/>
                                    <rect x="13.9" y="12.5" width="2.6" height="2.6" fill="currentColor" stroke="none"/>
                                    @break
                                @case('grid')
                                    <rect x="4" y="4" width="7" height="7" rx="1.2" stroke-width="1.6"/>
                                    <rect x="13" y="4" width="7" height="7" rx="1.2" fill="currentColor" stroke="none"/>
                                    <rect x="4" y="13" width="7" height="7" rx="1.2" fill="currentColor" stroke="none"/>
                                    <rect x="13" y="13" width="7" height="7" rx="1.2" stroke-width="1.6"/>
                                    @break
                                @default
                                    <rect x="4" y="5" width="16" height="16" rx="2" stroke-width="1.6"/>
                                    <path d="M4 9.5h16M8 5v-1.5M16 5v-1.5" stroke-linecap="round"/>
                                    <path d="M7 15l1.8-2.4 1.8 1.8 1.8-3 1.8 2.4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4"/>
                            @endswitch
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">{{ $item['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ============ STEP 2: FILTER DATA ============ --}}
    <form method="GET" action="{{ url()->current() }}" class="bg-white rounded-2xl card-shadow p-6 mb-6">
        <input type="hidden" name="jenis" :value="jenis">
        <input type="hidden" name="tab" :value="activeSection">

        <div class="flex items-center gap-2 mb-1">
            <span class="w-5 h-5 rounded-full bg-navy text-white text-xs font-bold flex items-center justify-center">2</span>
            <h2 class="font-bold text-slate-800">Filter Data</h2>
        </div>
        <p class="text-sm text-slate-400 mb-5 ml-7">Sesuaikan data yang akan ditampilkan pada laporan.</p>

        {{-- --- HARIAN --- --}}
        <div x-show="jenis === 'harian'" x-cloak class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Laporan</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    <input type="date" name="harian_tanggal" x-model="harian.tanggal"
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">SKPD</label>
                <select name="harian_skpd" x-model="harian.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Instansi / SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select name="harian_status" x-model="harian.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="submit"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        {{-- --- MINGGUAN --- --}}
        <div x-show="jenis === 'mingguan'" x-cloak class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Mulai</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    <input type="date" name="mingguan_mulai" x-model="mingguan.mulai"
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Selesai</label>
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    <input type="date" name="mingguan_selesai" x-model="mingguan.selesai"
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                </div>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">SKPD</label>
                <select name="mingguan_skpd" x-model="mingguan.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Instansi / SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select name="mingguan_status" x-model="mingguan.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="submit"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        {{-- --- BULANAN --- --}}
        <div x-show="jenis === 'bulanan'" x-cloak class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[130px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Periode</label>
                <select name="bulanan_periode" x-model="bulanan.periode" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
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
            <div class="flex-1 min-w-[110px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
                <select name="bulanan_tahun" x-model="bulanan.tahun" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>2024</option>
                    <option>2025</option>
                    <option>2026</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">SKPD</label>
                <select name="bulanan_skpd" x-model="bulanan.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select name="bulanan_status" x-model="bulanan.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="submit"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        {{-- --- TAHUNAN --- --}}
        <div x-show="jenis === 'tahunan'" x-cloak class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[110px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
                <select name="tahunan_tahun" x-model="tahunan.tahun" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>2024</option>
                    <option>2025</option>
                    <option>2026</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">SKPD</label>
                <select name="tahunan_skpd" x-model="tahunan.skpd" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua SKPD</option>
                    <option>DISDUKCAPIL</option>
                    <option>Dinas Pekerjaan Umum</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status Pengaduan</label>
                <select name="tahunan_status" x-model="tahunan.status" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30">
                    <option>Semua Status</option>
                    <option>Selesai</option>
                    <option>Belum Selesai</option>
                </select>
            </div>
            <button type="submit"
                class="flex items-center justify-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-4 py-2.5 hover:bg-navy-dark whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>
                </svg>
                Tampilkan Laporan
            </button>
        </div>

        <div class="flex items-center gap-2 mt-4 text-xs flex-wrap">
            <span class="font-semibold text-slate-500">FILTER AKTIF:</span>
            <template x-for="f in filters()" :key="f.key">
                <span class="inline-flex items-center gap-1 bg-brand-softblue text-navy px-2.5 py-1 rounded-full font-medium">
                    <span x-text="f.label"></span>
                    <button type="button" @click="removeFilter(f.key)" class="hover:text-navy-dark">&times;</button>
                </span>
            </template>
        </div>
    </form>

    {{-- ============ NAV SECTION ============ --}}
    <div class="border-b border-slate-200 mb-6 sticky top-0 z-20 bg-slate-50/95 backdrop-blur">
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
                <a href="#{{ $key }}" @click.prevent="scrollToSection('{{ $key }}')"
                    class="pb-3 -mb-px border-b-2 transition cursor-pointer"
                    :class="activeSection === '{{ $key }}' ? 'border-navy text-navy font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700'">
                    {{ $t['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ============ TAB: RINGKASAN ============ --}}
    <section id="section-ringkasan" class="space-y-6 scroll-mt-24">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl card-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm text-slate-500">Total pengaduan</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M4 19h16M4 15l4-5 3 3 5-7 4 5"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-extrabold text-slate-900">{{ number_format($rekap['total_pengaduan'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Periode {{ $periodeLabel ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-2xl card-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm text-slate-500">Selesai</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-extrabold text-slate-900">{{ number_format($rekap['selesai'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-500 mt-1 font-semibold">{{ $rekap['persentase'] ?? 0 }}% dari total</p>
            </div>

            <div class="bg-white rounded-2xl card-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm text-slate-500">Belum Selesai</span>
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 9v4m0 4h.01M10.29 3.86l-8.13 14A2 2 0 004 21h16a2 2 0 001.84-3.14l-8.13-14a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-extrabold text-slate-900">{{ number_format(($rekap['total_pengaduan'] ?? 0) - ($rekap['selesai'] ?? 0), 0, ',', '.') }}</p>
                <p class="text-xs text-red-500 mt-1 font-semibold">{{ 100 - ($rekap['persentase'] ?? 0) }}% dari total</p>
            </div>
        </div>
    </section>

    {{-- ============ TAB: VISUALISASI ============ --}}
    <section id="section-visualisasi" class="space-y-6 scroll-mt-24 mt-6">
        <div class="bg-white rounded-2xl card-shadow p-6">
            <div class="flex items-center justify-between mb-1">
                <div>
                    <h3 class="font-bold text-slate-800">Tren Pengaduan Bulanan</h3>
                    <p class="text-xs text-slate-400">Visualisasi volume pengaduan yang masuk, periode {{ $periodeLabel ?? '-' }}</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-navy"></span> Masuk</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Selesai</span>
                </div>
            </div>
            <div class="mt-4" style="height: 220px;">
                <canvas id="trenChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl card-shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800">Aduan per SKPD (Top 5)</h3>
                        <p class="text-xs text-slate-400">Volume pengaduan tertinggi, {{ $periodeLabel ?? '-' }}</p>
                    </div>
                </div>
                <div style="height: 260px;">
                    <canvas id="skpdBarChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl card-shadow p-6">
                <h3 class="font-bold text-slate-800 mb-4">Distribusi Status</h3>
                <div class="flex items-center gap-6">
                    <div class="relative w-32 h-32 shrink-0">
                        <canvas id="statusDonut"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-extrabold text-slate-800">{{ number_format($dataGrafik['status_total'] ?? 0, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-400 font-semibold tracking-wide">TOTAL</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        @php
                            $warnaStatus = ['bg-emerald-500', 'bg-red-500'];
                        @endphp
                        @forelse(($dataGrafik['status_labels'] ?? []) as $i => $label)
                            <p class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $warnaStatus[$i] ?? 'bg-slate-400' }}"></span>
                                {{ $label }} ({{ $dataGrafik['status_persen'][$i] ?? 0 }}%)
                            </p>
                        @empty
                            <p class="text-slate-400">Belum ada data untuk periode ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-6">
            <h3 class="font-bold text-slate-800 mb-4">Kategori Pengaduan Terbanyak</h3>
            <div style="height: {{ max(200, count($dataGrafik['kategori_labels'] ?? []) * 36) }}px;">
                <canvas id="kategoriBarChart"></canvas>
            </div>
        </div>
    </section>

    {{-- ============ TAB: DATA REKAP ============ --}}
    <section id="section-rekap" class="space-y-6 scroll-mt-24 mt-6">
        <div class="bg-white rounded-2xl card-shadow overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5">
                <h3 class="font-bold text-slate-800">Rekapitulasi Data Pengaduan</h3>
                <span class="text-sm text-slate-400">
                    Menampilkan {{ isset($rekapData) ? $rekapData->count() : 0 }}
                    dari {{ isset($rekapData) && method_exists($rekapData, 'total') ? number_format($rekapData->total(), 0, ',', '.') : (isset($rekapData) ? $rekapData->count() : 0) }} data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="text-left text-[11px] font-semibold text-slate-500 bg-slate-50 whitespace-nowrap">
                            <th class="border border-slate-200 px-2 py-1.5">No.</th>
                            <th class="border border-slate-200 px-2 py-1.5">Tracking ID</th>
                            <th class="border border-slate-200 px-2 py-1.5">Tanggal Laporan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Waktu Laporan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Nama Pelapor</th>
                            <th class="border border-slate-200 px-2 py-1.5">Tanggapan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Status Penyelesaian</th>
                            <th class="border border-slate-200 px-2 py-1.5">Keterangan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Klasifikasi Laporan</th>
                            <th class="border border-slate-200 px-2 py-1.5">ID Kategori</th>
                            <th class="border border-slate-200 px-2 py-1.5">Kategori</th>
                            <th class="border border-slate-200 px-2 py-1.5">Judul Laporan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Isi Laporan Awal</th>
                            <th class="border border-slate-200 px-2 py-1.5">Isi Laporan Akhir</th>
                            <th class="border border-slate-200 px-2 py-1.5">Tipe Laporan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Sumber Laporan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Instansi Induk</th>
                            <th class="border border-slate-200 px-2 py-1.5">ID Instansi Terdisposisi</th>
                            <th class="border border-slate-200 px-2 py-1.5">Instansi Terdisposisi (SKPD)</th>
                            <th class="border border-slate-200 px-2 py-1.5">Status Laporan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Alasan Tunda/Arsip</th>
                            <th class="border border-slate-200 px-2 py-1.5">Provinsi</th>
                            <th class="border border-slate-200 px-2 py-1.5">Kabupaten</th>
                            <th class="border border-slate-200 px-2 py-1.5">Kecamatan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Kelurahan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Nomor SK</th>
                            <th class="border border-slate-200 px-2 py-1.5">Url SK</th>
                            <th class="border border-slate-200 px-2 py-1.5">Url Dokumen Laporan Tahunan</th>
                            <th class="border border-slate-200 px-2 py-1.5">Kanal Aduan Setwapres</th>
                            <th class="border border-slate-200 px-2 py-1.5">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($rekapData ?? []) as $i => $row)
                        <tr class="hover:bg-slate-50/60 align-top">
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">
                                {{ (method_exists($rekapData, 'firstItem') ? $rekapData->firstItem() : 1) + $i }}
                            </td>
                            <td class="border border-slate-200 px-2 py-1.5 whitespace-nowrap"><span class="font-bold text-navy">{{ $row->tracking_id }}</span></td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">
                                {{ $row->tanggal ? $row->tanggal->format('d M Y') : '-' }}
                            </td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->waktu ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 font-semibold text-slate-700 max-w-[140px] truncate" title="{{ $row->pelapor }}">{{ $row->pelapor ?? '-' }}</td>

                            <td class="border border-slate-200 px-2 py-1.5 whitespace-nowrap">
                                <span class="inline-flex text-[11px] font-semibold rounded-full px-2.5 py-1 min-w-[128px] justify-center
                                    {{ $row->status === 'Selesai' ? 'bg-blue-50 text-navy' : 'bg-red-50 text-red-500' }}">
                                    {{ $row->status === 'Selesai' ? 'Sudah Ada Tanggapan' : 'Belum Ada Tanggapan' }}
                                </span>
                            </td>

                            <td class="border border-slate-200 px-2 py-1.5 whitespace-nowrap">
                                <span class="inline-flex text-[11px] font-semibold rounded-full px-2.5 py-1 min-w-[110px] justify-center
                                    {{ $row->status === 'Selesai' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500' }}">
                                    {{ $row->status ?? 'Belum Selesai' }}
                                </span>
                            </td>

                            <td class="border border-slate-200 px-2 py-1.5 text-slate-600 max-w-[150px] truncate" title="{{ $row->keterangan }}">{{ $row->keterangan ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-600 max-w-[130px] truncate" title="{{ $row->klasifikasi }}">{{ $row->klasifikasi ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->id_kategori ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-600 max-w-[130px] truncate" title="{{ $row->kategori }}">{{ $row->kategori ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-600 max-w-[170px] truncate" title="{{ $row->judul }}">{{ $row->judul ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 max-w-[180px] truncate" title="{{ $row->isi_awal }}">{{ $row->isi_awal ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 max-w-[180px] truncate" title="{{ $row->isi_akhir }}">{{ $row->isi_akhir ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->tipe_laporan ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->sumber_laporan ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 max-w-[130px] truncate" title="{{ $row->instansi_induk }}">{{ $row->instansi_induk ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->id_instansi_terdisposisi ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-600 max-w-[150px] truncate" title="{{ $row->skpd }}">{{ $row->skpd ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->status_laporan_raw ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 max-w-[130px] truncate" title="{{ $row->alasan_tunda_arsip }}">{{ $row->alasan_tunda_arsip ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->provinsi ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->kabupaten ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->kecamatan ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->kelurahan ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->nomor_sk ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">
                                @if($row->url_sk)
                                    <a href="{{ $row->url_sk }}" target="_blank" rel="noopener" class="text-navy underline">Lihat SK</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">
                                @if($row->url_dokumen_laporan_tahunan)
                                    <a href="{{ $row->url_dokumen_laporan_tahunan }}" target="_blank" rel="noopener" class="text-navy underline">Lihat Dokumen</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->laporan_setwapres ?? '-' }}</td>
                            <td class="border border-slate-200 px-2 py-1.5 text-slate-500 whitespace-nowrap">{{ $row->rating ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="29" class="border border-slate-200 px-6 py-10 text-center text-slate-400">
                                Belum ada data untuk filter laporan yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($rekapData) && method_exists($rekapData, 'links'))
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $rekapData->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- ============ TAB: REKOMENDASI (SEKARANG 1 TEXTAREA BEBAS) ============ --}}
    <section id="section-rekomendasi" class="space-y-6 scroll-mt-24 mt-6">
        <div class="bg-white rounded-2xl card-shadow p-6">
            <h3 class="font-bold text-slate-800 mb-1">Rekomendasi & Tindak Lanjut</h3>
            <p class="text-sm text-slate-400 mb-3">
                Isi manual — tulis satu poin per baris (tekan Enter untuk poin baru). Jumlah poin bebas,
                akan otomatis dinomori di dokumen Word saat "Generate Report".
            </p>

            <textarea rows="6"
                class="w-full border border-slate-200 rounded-xl p-4 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30 resize-y"
                placeholder="Contoh:&#10;Tingkatkan koordinasi dengan SKPD terkait penanganan aduan infrastruktur jalan.&#10;Percepat proses disposisi laporan yang masih berstatus Diarsipkan.&#10;..."
                x-model="rekomendasi"></textarea>

            {{--
                =====================================================================
                Tombol "Generate Report" -> GET ke laporan.generate, bawa SEMUA
                filter aktif (jenis + isian harian/mingguan/bulanan/tahunan) DAN
                isi rekomendasi manual (1 field "rekomendasi", tiap baris = 1
                poin, jumlah bebas). Backend (LaporanController::generateWord)
                pakai filter yang sama persis kayak yang dipakai buat nampilin
                data di tab Ringkasan/Visualisasi/Rekap, dan poin rekomendasi
                persis apa yang diketik user di sini (lihat
                isiRekomendasiManual()) — bukan auto-generate lagi.
                =====================================================================
            --}}
            <form method="GET" action="{{ route('laporan.generate') }}" class="flex justify-end mt-4">
                <input type="hidden" name="jenis" :value="jenis">

                <input type="hidden" name="harian_tanggal" :value="harian.tanggal">
                <input type="hidden" name="harian_skpd" :value="harian.skpd">
                <input type="hidden" name="harian_status" :value="harian.status">

                <input type="hidden" name="mingguan_mulai" :value="mingguan.mulai">
                <input type="hidden" name="mingguan_selesai" :value="mingguan.selesai">
                <input type="hidden" name="mingguan_skpd" :value="mingguan.skpd">
                <input type="hidden" name="mingguan_status" :value="mingguan.status">

                <input type="hidden" name="bulanan_periode" :value="bulanan.periode">
                <input type="hidden" name="bulanan_tahun" :value="bulanan.tahun">
                <input type="hidden" name="bulanan_skpd" :value="bulanan.skpd">
                <input type="hidden" name="bulanan_status" :value="bulanan.status">

                <input type="hidden" name="tahunan_tahun" :value="tahunan.tahun">
                <input type="hidden" name="tahunan_skpd" :value="tahunan.skpd">
                <input type="hidden" name="tahunan_status" :value="tahunan.status">

                <input type="hidden" name="rekomendasi" :value="rekomendasi">

                <button type="submit" class="flex items-center gap-2 bg-navy text-white text-sm font-semibold rounded-lg px-5 py-2.5 hover:bg-navy-dark">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/><path d="M9 12h6M9 16h6M9 8h2"/>
                    </svg>
                    Generate Report
                </button>
            </form>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endpush