<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Pengaduan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class LaporanController extends Controller
{
    private array $bulanList = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /**
     * Tampilkan halaman Laporan Pengaduan (generate laporan Harian/Mingguan/Bulanan/Tahunan).
     */
    public function index(Request $request)
    {
        $daftarSkpd = ['Dinas Kesehatan', 'Dinas Pendidikan', 'Dinas Pekerjaan Umum', 'Dinas Sosial', 'Dishub'];
        $tahunList = range(now()->year, now()->year - 4);

        $instansi = [
            'nama_pemerintah' => 'PEMERINTAH KABUPATEN GARUT',
            'nama_dinas'      => 'Dinas Komunikasi dan Informatika',
            'alamat'          => 'Jalan Pembangunan No. 181, Sukagalih, Kec. Tarogong Kidul, Kab. Garut',
            'kepala_dinas'    => 'H. Margiyanto, S.H., M.Si.',
            'nip'             => '19690101 199003 1 001',
        ];

        $validTabs = ['ringkasan', 'visualisasi', 'rekap', 'rekomendasi'];
        $activeTab = $request->input('tab', 'ringkasan');
        if (! in_array($activeTab, $validTabs, true)) {
            $activeTab = 'ringkasan';
        }

        $query = Pengaduan::query();
        [$jenis, $periodeLabel] = $this->terapkanFilter($query, $request);

        // ================= REKAP RINGKASAN =================
        $totalPengaduan = (clone $query)->count();
        $totalSelesai   = (clone $query)->where('status', 'Selesai')->count();
        $persentase     = $totalPengaduan > 0 ? round(($totalSelesai / $totalPengaduan) * 100, 1) : 0;

        $rekap = [
            'total_pengaduan' => $totalPengaduan,
            'selesai'         => $totalSelesai,
            'persentase'      => $persentase,
        ];

        // ================= RINCIAN PER SKPD =================
        $rincianSkpd = (clone $query)
            ->select('skpd', DB::raw('COUNT(*) as masuk'))
            ->selectRaw("SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai")
            ->selectRaw("SUM(CASE WHEN status != 'Selesai' THEN 1 ELSE 0 END) as proses")
            ->whereNotNull('skpd')
            ->groupBy('skpd')
            ->orderByDesc('masuk')
            ->get()
            ->map(fn ($row, $i) => [
                'no'      => $i + 1,
                'skpd'    => $row->skpd,
                'masuk'   => (int) $row->masuk,
                'proses'  => (int) $row->proses,
                'selesai' => (int) $row->selesai,
            ])
            ->values()
            ->all();

        $totalRincian = [
            'masuk'   => collect($rincianSkpd)->sum('masuk'),
            'proses'  => collect($rincianSkpd)->sum('proses'),
            'selesai' => collect($rincianSkpd)->sum('selesai'),
        ];

        // ================= DATA REKAP (TABEL DETAIL) =================
        $rekapData = (clone $query)
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->withQueryString();

        // ================= DATA GRAFIK (dipakai bareng web & Word) =================
        $rowsUntukGrafik = (clone $query)->get();
        $dataGrafik = $this->hitungDataGrafik($rowsUntukGrafik);

        return view('laporan.index', compact(
            'daftarSkpd',
            'tahunList',
            'instansi',
            'rekap',
            'rincianSkpd',
            'totalRincian',
            'rekapData',
            'jenis',
            'activeTab',
            'dataGrafik'
        ) + ['bulanList' => $this->bulanList, 'periodeLabel' => $periodeLabel]);
    }

    /**
     * Tampilkan halaman Arsip Laporan (daftar semua laporan Word yang
     * sudah pernah di-generate lewat generateWord(), diambil dari tabel
     * `laporan`).
     * GET /laporan/pengaduan (route: laporan.pengaduan)
     */
    public function arsip(Request $request)
    {
        $query = Laporan::query();

        if (Auth::check() && \Schema::hasColumn('laporan', 'user_id')) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('tanggal_dibuat')) {
            $query->whereDate('tanggal_dibuat', $request->input('tanggal_dibuat'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        $totalArsip = (clone $query)->count();

        $laporans = $query->orderByDesc('tanggal_dibuat')
            ->get()
            ->map(function (Laporan $l) {
                return [
                    'nama'       => $l->nama,
                    'skpd'       => $l->skpd ?? '-',
                    'jenis'      => ucfirst($l->jenis),
                    'periode'    => $l->periode,
                    'dibuat'     => $l->tanggal_dibuat ? $l->tanggal_dibuat->translatedFormat('d M Y') : '-',
                    'format'     => $l->format ?? 'DOCX',
                    'ukuran'     => $this->formatUkuran($l->ukuran_bytes),
                    'url_lihat'  => $l->path_file ? asset('storage/' . $l->path_file) : '#',
                    'url_unduh'  => $l->path_file ? asset('storage/' . $l->path_file) : '#',
                ];
            });

        return view('laporan.pengaduan', compact('laporans', 'totalArsip'));
    }

    /**
     * Endpoint tombol "Generate Report" -> download file Word DAN
     * menyimpan record ke tabel `laporan` supaya muncul di halaman Arsip.
     * GET /laporan/generate
     */
    public function generateWord(Request $request)
    {
        $query = Pengaduan::query();
        [$jenis, $periodeLabel] = $this->terapkanFilter($query, $request);

        $rows = $query->orderBy('tanggal')->get();

        $ringkasan = $this->hitungRingkasan($rows, $periodeLabel);

        $templatePath = resource_path('templates/laporan_template.docx');
        $template = new TemplateProcessor($templatePath);

        $template->setValue('nama_penyusun', $this->esc(Auth::user()->name ?? '-'));
        $template->setValue('nip', $this->esc(Auth::user()->nip ?? '-'));
        $template->setValue('tanggal_laporan', $this->esc(now()->translatedFormat('d F Y')));
        $template->setValue('periode', $this->esc($ringkasan['periode']));
        $template->setValue('total_pengaduan', $this->esc($ringkasan['total']));
        $template->setValue('total_selesai', $this->esc($ringkasan['selesai']));
        $template->setValue('total_proses', $this->esc($ringkasan['proses']));
        $template->setValue('persentase', $this->esc($ringkasan['persentase']));
        $template->setValue('bulan_tertinggi', $this->esc($ringkasan['bulan_tertinggi']));
        $template->setValue('jumlah_tertinggi', $this->esc($ringkasan['jumlah_tertinggi']));

        $this->isiGrafik($template, $rows);
        $this->isiRekomendasiManual($template, $request);
        $this->isiTabelLaporan($template, $rows);
        $this->isiTabelDisposisi($template, $rows);

        $namaLaporan = 'Laporan_' . ucfirst($jenis) . '_' . str_replace(' ', '_', $ringkasan['periode']);
        $filename = $namaLaporan . '_' . now()->format('YmdHis') . '.docx';

        $relativePath = 'laporan/' . $filename;
        $fullPath = storage_path('app/public/' . $relativePath);

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $template->saveAs($fullPath);

        $skpdDipakai = $request->input($jenis . '_skpd');
        if (! $skpdDipakai || str_starts_with($skpdDipakai, 'Semua')) {
            $skpdDipakai = 'Semua SKPD';
        }

        Laporan::create([
            'nama'           => $namaLaporan,
            'skpd'           => $skpdDipakai,
            'jenis'          => $jenis,
            'periode'        => $ringkasan['periode'],
            'tanggal_dibuat' => now(),
            'format'         => 'DOCX',
            'path_file'      => $relativePath,
            'ukuran_bytes'   => filesize($fullPath),
            'user_id'        => Auth::id(),
        ]);

        return response()->download($fullPath, $filename);
    }

    private function formatUkuran(?int $bytes): string
    {
        if (! $bytes) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function esc($value): string
    {
        $value = (string) ($value ?? '-');
        if ($value === '') {
            $value = '-';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function terapkanFilter(Builder $query, Request $request): array
    {
        $jenis = $request->input('jenis', 'bulanan');
        $skpdFilter = null;
        $statusFilter = null;
        $periodeLabel = '-';

        switch ($jenis) {
            case 'harian':
                $tanggal = $request->input('harian_tanggal', now()->format('Y-m-d'));
                $query->whereDate('tanggal', $tanggal);
                $skpdFilter = $request->input('harian_skpd');
                $statusFilter = $request->input('harian_status');
                $periodeLabel = Carbon::parse($tanggal)->translatedFormat('d F Y');
                break;

            case 'mingguan':
                $mulai = $request->input('mingguan_mulai', now()->startOfWeek()->format('Y-m-d'));
                $selesai = $request->input('mingguan_selesai', now()->endOfWeek()->format('Y-m-d'));
                $query->whereBetween('tanggal', [$mulai, $selesai]);
                $skpdFilter = $request->input('mingguan_skpd');
                $statusFilter = $request->input('mingguan_status');
                $periodeLabel = Carbon::parse($mulai)->translatedFormat('d F Y') . ' - ' . Carbon::parse($selesai)->translatedFormat('d F Y');
                break;

            case 'tahunan':
                $tahun = $request->input('tahunan_tahun', now()->year);
                $query->whereYear('tanggal', $tahun);
                $skpdFilter = $request->input('tahunan_skpd');
                $statusFilter = $request->input('tahunan_status');
                $periodeLabel = (string) $tahun;
                break;

            case 'bulanan':
            default:
                $jenis = 'bulanan';
                $periode = $request->input('bulanan_periode', $this->bulanList[now()->month - 1]);
                $tahun = $request->input('bulanan_tahun', now()->year);
                $bulanKe = array_search($periode, $this->bulanList);
                $bulanKe = $bulanKe !== false ? $bulanKe + 1 : now()->month;

                $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulanKe);
                $skpdFilter = $request->input('bulanan_skpd');
                $statusFilter = $request->input('bulanan_status');
                $periodeLabel = "{$periode} {$tahun}";
                break;
        }

        if ($skpdFilter && ! str_starts_with($skpdFilter, 'Semua')) {
            $query->where('skpd', $skpdFilter);
        }

        if ($statusFilter && ! str_starts_with($statusFilter, 'Semua')) {
            $query->where('status', $statusFilter);
        }

        return [$jenis, $periodeLabel];
    }

    private function hitungRingkasan($rows, string $periode): array
    {
        $total = $rows->count();
        $selesai = $rows->where('status', 'Selesai')->count();
        $proses = $total - $selesai;
        $persentase = $total > 0 ? round(($selesai / $total) * 100) : 0;

        $perBulan = $rows->groupBy(fn ($r) => Carbon::parse($r->tanggal)->translatedFormat('F'));
        $tertinggi = $perBulan->map->count()->sortDesc();

        return [
            'periode' => $periode,
            'total' => $total,
            'selesai' => $selesai,
            'proses' => $proses,
            'persentase' => $persentase,
            'bulan_tertinggi' => $tertinggi->keys()->first() ?? '-',
            'jumlah_tertinggi' => $tertinggi->first() ?? 0,
        ];
    }

    private function hitungDataGrafik($rows): array
    {
        $bulanLabelSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $masuk = array_fill(0, 12, 0);
        $selesaiArr = array_fill(0, 12, 0);
        foreach ($rows as $r) {
            $bulanIdx = Carbon::parse($r->tanggal)->month - 1;
            $masuk[$bulanIdx]++;
            if ($r->status === 'Selesai') {
                $selesaiArr[$bulanIdx]++;
            }
        }

        $perSkpd = $rows
            ->map(fn ($r) => ($r->skpd === null || trim((string) $r->skpd) === '') ? 'Belum Ada SKPD' : $r->skpd)
            ->countBy()
            ->sortDesc()
            ->take(5);

        $perKategori = $rows
            ->map(fn ($r) => ($r->kategori === null || trim((string) $r->kategori) === '') ? 'Tanpa Kategori' : $r->kategori)
            ->countBy()
            ->sortDesc();

        $totalStatus = $rows->count();
        $totalSelesai = $rows->where('status', 'Selesai')->count();
        $totalBelumSelesai = $totalStatus - $totalSelesai;

        $statusCount = collect([
            'Selesai'       => $totalSelesai,
            'Belum Selesai' => $totalBelumSelesai,
        ]);

        $statusPersen = $statusCount->map(
            fn ($jumlah) => $totalStatus > 0 ? round(($jumlah / $totalStatus) * 100) : 0
        );

        return [
            'bulan_label'      => $bulanLabelSingkat,
            'tren_masuk'       => $masuk,
            'tren_selesai'     => $selesaiArr,
            'skpd_labels'      => $perSkpd->keys()->values()->all(),
            'skpd_data'        => $perSkpd->values()->all(),
            'status_labels'    => $statusCount->keys()->values()->all(),
            'status_data'      => $statusCount->values()->all(),
            'status_persen'    => $statusPersen->values()->all(),
            'status_total'     => $totalStatus,
            'kategori_labels'  => $perKategori->keys()->values()->all(),
            'kategori_data'    => $perKategori->values()->all(),
        ];
    }

    private function warnaKategori(int $jumlah): array
    {
        $palet = ['#0B3D91', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE'];

        $warna = [];
        for ($i = 0; $i < $jumlah; $i++) {
            $warna[] = $palet[$i % count($palet)];
        }

        return $warna;
    }

    private function isiRekomendasiManual(TemplateProcessor $template, Request $request): void
    {
        $raw = (string) $request->input('rekomendasi', '');

        $poin = collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values();

        if ($poin->isEmpty()) {
            $poin = collect(['Tidak ada catatan khusus untuk periode berjalan.']);
        }

        $template->cloneRow('rekomendasi_no', $poin->count());

        foreach ($poin as $i => $isi) {
            $n = $i + 1;
            $template->setValue("rekomendasi_no#{$n}", $this->esc((string) $n));
            $template->setValue("rekomendasi_isi#{$n}", $this->esc($isi));
        }
    }

    private function isiGrafik(TemplateProcessor $template, $rows): void
    {
        $data = $this->hitungDataGrafik($rows);

        $template->setValue('status_total', $this->esc($data['status_total']));

        // ---- Tren Pengaduan Bulanan ----
        $this->downloadChartImage($template, 'gambar_tren', [
            'type' => 'line',
            'data' => [
                'labels' => $data['bulan_label'],
                'datasets' => [
                    [
                        'label' => 'Masuk',
                        'data' => $data['tren_masuk'],
                        'borderColor' => '#0B3D91',
                        'backgroundColor' => 'rgba(11,61,145,0.15)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointRadius' => 0,
                        'borderWidth' => 2.5,
                    ],
                    [
                        'label' => 'Selesai',
                        'data' => $data['tren_selesai'],
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'transparent',
                        'borderDash' => [4, 4],
                        'fill' => false,
                        'tension' => 0.4,
                        'pointRadius' => 0,
                        'borderWidth' => 2,
                    ],
                ],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'top'],
                    'datalabels' => ['display' => false],
                ],
                'scales' => [
                    'y' => ['display' => false, 'grid' => ['display' => false]],
                    'x' => ['grid' => ['display' => false]],
                ],
            ],
        ]);

        // ---- Aduan per SKPD (Top 5) ----
        // FIX: (1) angka pada bar tertinggi kepotong di tepi atas canvas
        // karena tidak ada ruang ekstra dan datalabel tidak di-clamp;
        // (2) label SKPD panjang di-rotate otomatis dengan sudut acak dan
        // tinggi canvas terlalu kecil, sehingga tabrakan dengan konten
        // Word di bawah gambar. Sekarang: suggestedMax dilebarkan 25% dari
        // nilai maksimum, clamp:true dipasang, rotasi label dipaksa 40°
        // (bukan auto), dan tinggi gambar dinaikkan dari 320 -> 420.
        $maxSkpd = ! empty($data['skpd_data']) ? max($data['skpd_data']) : 1;

        $this->downloadChartImage($template, 'gambar_skpd', [
            'type' => 'bar',
            'data' => [
                'labels' => $data['skpd_labels'],
                'datasets' => [[
                    'label' => 'Jumlah Aduan',
                    'data' => $data['skpd_data'],
                    'backgroundColor' => ['#0B3D91', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD'],
                    'borderRadius' => 6,
                ]],
            ],
            'options' => [
                'layout' => [
                    'padding' => ['top' => 30, 'bottom' => 10],
                ],
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'anchor' => 'end',
                        'align' => 'top',
                        'clamp' => true,
                        'color' => '#334155',
                        'font' => ['weight' => 'bold', 'size' => 11],
                    ],
                ],
                'scales' => [
                    'y' => [
                        'display' => false,
                        'grid' => ['display' => false],
                        'suggestedMax' => $maxSkpd * 1.25,
                    ],
                    'x' => [
                        'grid' => ['display' => false],
                        'ticks' => [
                            'font' => ['size' => 9],
                            'maxRotation' => 40,
                            'minRotation' => 40,
                        ],
                    ],
                ],
            ],
        ], 420);

        // ---- Distribusi Status (donut) ----
        $this->downloadChartImage($template, 'gambar_status', [
            'type' => 'doughnut',
            'data' => [
                'labels' => $data['status_labels'],
                'datasets' => [[
                    'data' => $data['status_data'],
                    'backgroundColor' => ['#10B981', '#EF4444'],
                    'borderWidth' => 0,
                ]],
            ],
            'options' => [
                'cutout' => '72%',
                'plugins' => [
                    'legend' => ['display' => true, 'position' => 'bottom'],
                    'datalabels' => ['display' => false],
                ],
            ],
        ]);

        // ---- Kategori Pengaduan Terbanyak (SEMUA kategori, indexAxis 'y') ----
        // FIX: angka pada bar terpanjang kepotong di tepi kanan canvas
        // karena skala X tidak diberi ruang ekstra dan datalabel tidak
        // di-clamp. Sekarang: suggestedMax dilebarkan 20% dari nilai
        // maksimum data, clamp:true dipasang, dan padding kanan ditambah.
        $jumlahKategori = max(count($data['kategori_labels']), 1);
        $maxKategori = ! empty($data['kategori_data']) ? max($data['kategori_data']) : 1;

        $this->downloadChartImage($template, 'gambar_kategori', [
            'type' => 'bar',
            'data' => [
                'labels' => $data['kategori_labels'],
                'datasets' => [[
                    'label' => 'Jumlah',
                    'data' => $data['kategori_data'],
                    'backgroundColor' => $this->warnaKategori($jumlahKategori),
                    'borderRadius' => 6,
                ]],
            ],
            'options' => [
                'indexAxis' => 'y',
                'layout' => [
                    'padding' => ['right' => 50],
                ],
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'anchor' => 'end',
                        'align' => 'right',
                        'clamp' => true,
                        'color' => '#0B3D91',
                        'font' => ['weight' => 'bold', 'size' => 11],
                    ],
                ],
                'scales' => [
                    'x' => [
                        'display' => false,
                        'grid' => ['display' => false],
                        'suggestedMax' => $maxKategori * 1.2,
                    ],
                    'y' => ['grid' => ['display' => false]],
                ],
            ],
        ], max(260, $jumlahKategori * 45));
    }

    private function downloadChartImage(TemplateProcessor $template, string $placeholder, array $chartConfig, int $height = 320): void
    {
        $url = 'https://quickchart.io/chart?width=600&height=' . $height . '&backgroundColor=white&version=4&c='
            . urlencode(json_encode($chartConfig));

        $tempPath = storage_path("app/tmp_{$placeholder}.png");

        $imageData = null;
        $lastError = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FAILONERROR    => true,
            ]);
            $result   = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($result !== false && $httpCode === 200) {
                $imageData = $result;
                break;
            }

            $lastError = $error ?: "HTTP {$httpCode}";
        }

        if ($imageData === null) {
            Log::error("Gagal mengambil chart '{$placeholder}' dari QuickChart", [
                'error' => $lastError,
                'url'   => $url,
            ]);

            throw new \RuntimeException(
                "Gagal membuat grafik '{$placeholder}' (QuickChart tidak dapat diakses: {$lastError}). "
                . 'Silakan coba generate ulang laporan beberapa saat lagi.'
            );
        }

        if (file_put_contents($tempPath, $imageData) === false) {
            Log::error("Gagal menyimpan file gambar chart '{$placeholder}'", [
                'path' => $tempPath,
            ]);

            throw new \RuntimeException(
                "Gagal menyimpan file gambar chart '{$placeholder}' ke {$tempPath}. "
                . 'Pastikan folder storage/app bisa ditulis (writable).'
            );
        }

        $tinggiGambar = (int) round($height * (260 / 320));

        $template->setImageValue($placeholder, [
            'path' => $tempPath,
            'width' => 500,
            'height' => $tinggiGambar,
            'ratio' => false,
        ]);
    }

    private function isiTabelLaporan(TemplateProcessor $template, $rows): void
    {
        if ($rows->isEmpty()) {
            $template->cloneRow('tracking_id', 1);
            $template->setValue('tracking_id#1', '-');
            return;
        }

        $template->cloneRow('tracking_id', $rows->count());

        foreach ($rows->values() as $i => $row) {
            $n = $i + 1;
            $template->setValue("tracking_id#{$n}", $this->esc($row->tracking_id ?? '-'));
            $template->setValue("tgl_laporan#{$n}", $this->esc($row->tanggal ? Carbon::parse($row->tanggal)->format('d/m/Y') : '-'));
            $template->setValue("waktu_laporan#{$n}", $this->esc($row->waktu ?? '-'));
            $template->setValue("nama_pelapor#{$n}", $this->esc($row->pelapor ?? '-'));
            $template->setValue("klasifikasi#{$n}", $this->esc($row->klasifikasi ?? '-'));
            $template->setValue("id_kategori#{$n}", $this->esc($row->id_kategori ?? '-'));
            $template->setValue("kategori#{$n}", $this->esc($row->kategori ?? '-'));
            $template->setValue("judul#{$n}", $this->esc($row->judul ?? '-'));
            $template->setValue("isi_awal#{$n}", $this->esc($row->isi_awal ?? '-'));
            $template->setValue("isi_akhir#{$n}", $this->esc($row->isi_akhir ?? '-'));
            $template->setValue("tipe_laporan#{$n}", $this->esc($row->tipe_laporan ?? '-'));
            $template->setValue("sumber_laporan#{$n}", $this->esc($row->sumber_laporan ?? '-'));
            $template->setValue("tanggapan#{$n}", $this->esc($row->status === 'Selesai' ? 'Sudah ada tanggapan' : 'Belum ada tanggapan'));
            $template->setValue("keterangan#{$n}", $this->esc($row->keterangan ?? '-'));
        }
    }

    private function isiTabelDisposisi(TemplateProcessor $template, $rows): void
    {
        if ($rows->isEmpty()) {
            $template->cloneRow('d_tracking_id', 1);
            $template->setValue('d_tracking_id#1', '-');
            return;
        }

        $template->cloneRow('d_tracking_id', $rows->count());

        foreach ($rows->values() as $i => $row) {
            $n = $i + 1;
            $template->setValue("d_tracking_id#{$n}", $this->esc($row->tracking_id ?? '-'));
            $template->setValue("instansi_induk#{$n}", $this->esc($row->instansi_induk ?? '-'));
            $template->setValue("id_instansi#{$n}", $this->esc($row->id_instansi_terdisposisi ?? '-'));
            $template->setValue("instansi_terdisposisi#{$n}", $this->esc($row->skpd ?? '-'));
            $template->setValue("status_laporan#{$n}", $this->esc($row->status_laporan_raw ?? $row->status ?? '-'));
            $template->setValue("alasan_tunda#{$n}", $this->esc($row->alasan_tunda_arsip ?? '-'));
            $template->setValue("provinsi#{$n}", $this->esc($row->provinsi ?? '-'));
            $template->setValue("kabupaten#{$n}", $this->esc($row->kabupaten ?? '-'));
            $template->setValue("kecamatan#{$n}", $this->esc($row->kecamatan ?? '-'));
            $template->setValue("kelurahan#{$n}", $this->esc($row->kelurahan ?? '-'));
            $template->setValue("nomor_sk#{$n}", $this->esc($row->nomor_sk ?? '-'));
            $template->setValue("url_sk#{$n}", $this->esc($row->url_sk ?? '-'));
            $template->setValue("url_dok#{$n}", $this->esc($row->url_dokumen_laporan_tahunan ?? '-'));
            $template->setValue("kanal_setwapres#{$n}", $this->esc($row->laporan_setwapres ?? '-'));
            $template->setValue("rating#{$n}", $this->esc($row->rating ?? '-'));
        }
    }
}