@extends('layouts.app')

@section('title', 'Data Pengaduan')

@php
    $searchPlaceholder = 'Cari data...';
    $userName = 'Admin Garut';
    $userRole = 'Super Admin';
@endphp

@section('content')
<div x-data="{ showImportModal: false }">

    {{-- ================= BREADCRUMB ================= --}}
    <p class="text-sm text-slate-400 mb-2">Pages / <span class="text-slate-600 font-medium">Data Pengaduan</span></p>

    {{-- ================= TOMBOL KEMBALI (muncul kalau datang dari filter SKPD) ================= --}}
    @if(request('skpd'))
        <a href="{{ route('monitoring-skpd') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-navy mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Monitoring SKPD
        </a>
    @endif

    {{-- ================= HEADER ================= --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Kelola Data Pengaduan</h1>
            <p class="text-slate-500 mt-1">Monitoring dan tindak lanjut aspirasi masyarakat Kabupaten Garut.</p>
        </div>

        <button type="button" @click="showImportModal = true"
                class="bg-navy hover:bg-navy-dark text-white font-semibold text-sm px-5 py-3 rounded-xl flex items-center gap-2 card-shadow shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Pengaduan
        </button>
    </div>

    {{-- ================= INFO FILTER SKPD AKTIF ================= --}}
    @if(request('skpd'))
        <div class="mb-4 flex items-center gap-2 text-sm text-slate-600 bg-blue-50 rounded-xl px-4 py-3">
            Menampilkan pengaduan untuk SKPD:
            <span class="font-bold text-navy">{{ request('skpd') }}</span>
            <a href="{{ route('data-pengaduan') }}" class="ml-auto text-red-500 font-semibold hover:underline">Hapus Filter</a>
        </div>
    @endif

    {{-- ================= FLASH MESSAGE ================= --}}
    @if (session('success'))
        <div class="mb-4 rounded-xl bg-green-50 text-green-700 text-sm px-4 py-3">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="mb-4 rounded-xl bg-yellow-50 text-yellow-700 text-sm px-4 py-3">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    {{-- ================= KARTU STATISTIK ================= --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 card-shadow flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 5h16M4 5a2 2 0 00-2 2v9a2 2 0 002 2h11l3 3v-3h1a2 2 0 002-2V7a2 2 0 00-2-2H4z"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 tracking-wide">TOTAL {{ $tahun }}</p>
                <p class="text-2xl font-extrabold text-slate-800">{{ number_format($stats['total'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 card-shadow flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 tracking-wide">SELESAI</p>
                <p class="text-2xl font-extrabold text-slate-800">{{ number_format($stats['selesai'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 card-shadow flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 tracking-wide">BELUM SELESAI</p>
                <p class="text-2xl font-extrabold text-slate-800">{{ number_format($stats['belum_selesai'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 card-shadow flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 tracking-wide">BELUM ADA TANGGAPAN</p>
                <p class="text-2xl font-extrabold text-slate-800">{{ number_format($stats['belum_tanggapan'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- ================= TABS + SEARCH + FILTER TAHUN ================= --}}
    <form method="GET" class="bg-white rounded-2xl p-3 card-shadow mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @php
                $tabs = [
                    'semua' => 'Semua Data',
                    'selesai' => 'Selesai',
                    'belum_selesai' => 'Belum Selesai',
                    'belum_tanggapan' => 'Belum Ada Tanggapan',
                ];
            @endphp
            @foreach($tabs as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['filter' => $key]) }}"
                   class="text-sm font-semibold px-4 py-2 rounded-xl transition
                       {{ $filter === $key ? 'bg-navy text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="filter" value="{{ $filter }}">
            @if(request('skpd'))
                <input type="hidden" name="skpd" value="{{ request('skpd') }}">
            @endif

            {{-- ============ DROPDOWN FILTER TAHUN ============ --}}
            {{-- Default nampilin tahun berjalan. Ganti tahun -> auto submit form -> data ganti ke tahun itu. --}}
            <select name="tahun" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-semibold text-slate-600
                           focus:outline-none focus:ring-2 focus:ring-navy/30 cursor-pointer">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ (int) $tahun === (int) $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>

            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/>
                </svg>
                <input type="text" name="tracking_id" value="{{ request('tracking_id') }}" placeholder="Cari Tracking ID atau Nama..."
                       class="bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-navy/30">
            </div>
        </div>
    </form>

    {{-- ================= TABEL ================= --}}
    {{--
        id + data-next-page-url dipakai sama script infinite scroll di bawah.
        nextPageUrl() otomatis null kalau ini halaman terakhir, jadi script tau kapan berhenti.
        Parameter "tahun" ikut kebawa otomatis di nextPageUrl() karena paginate()->withQueryString().
    --}}
    <div id="pengaduan-table-card" class="bg-white rounded-2xl card-shadow overflow-hidden"
         data-next-page-url="{{ $pengaduan->nextPageUrl() }}">
        <div class="flex items-center justify-between px-6 py-5">
            <h2 class="font-bold text-slate-800">Daftar Pengaduan</h2>
            <span class="text-sm text-slate-400">Menampilkan <span id="pengaduan-shown-count">{{ $pengaduan->count() }}</span> dari {{ number_format($totalData, 0, ',', '.') }} data tahun {{ $tahun }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 border-y border-slate-100 whitespace-nowrap">
                        <th class="px-6 py-3">No.</th>
                        <th class="px-3 py-3">Tracking ID</th>
                        <th class="px-3 py-3">Tanggal</th>
                        <th class="px-3 py-3">Waktu</th>
                        <th class="px-3 py-3">Nama Pelapor</th>
                        <th class="px-3 py-3 min-w-[180px]">Tanggapan</th>
                        <th class="px-3 py-3 min-w-[180px]">Status Penyelesaian</th>
                        <th class="px-3 py-3 min-w-[200px]">Keterangan</th>
                        <th class="px-3 py-3">Klasifikasi Laporan</th>
                        <th class="px-3 py-3">ID Kategori</th>
                        <th class="px-3 py-3">Kategori</th>
                        <th class="px-3 py-3 min-w-[200px]">Judul Laporan</th>
                        <th class="px-3 py-3 min-w-[220px]">Isi Laporan Awal</th>
                        <th class="px-3 py-3 min-w-[220px]">Isi Laporan Akhir</th>
                        <th class="px-3 py-3">Tipe Laporan</th>
                        <th class="px-3 py-3">Sumber Laporan</th>
                        <th class="px-3 py-3">Instansi Induk</th>
                        <th class="px-3 py-3">ID Instansi Terdisposisi</th>
                        <th class="px-3 py-3">Instansi Terdisposisi (SKPD)</th>
                        <th class="px-3 py-3">Status Laporan</th>
                        <th class="px-3 py-3">Alasan Tunda/Arsip</th>
                        <th class="px-3 py-3">Provinsi</th>
                        <th class="px-3 py-3">Kabupaten</th>
                        <th class="px-3 py-3">Kecamatan</th>
                        <th class="px-3 py-3">Kelurahan</th>
                        <th class="px-3 py-3">Nomor SK</th>
                        <th class="px-3 py-3">Url SK</th>
                        <th class="px-3 py-3">Url Dokumen Laporan Tahunan</th>
                        <th class="px-3 py-3">Kanal Aduan Setwapres</th>
                        <th class="px-3 py-3">Rating</th>
                    </tr>
                </thead>
                <tbody id="pengaduan-tbody">
                    @forelse($pengaduan as $i => $row)
                    <tr class="js-pengaduan-row border-b border-slate-50 last:border-0 align-top cursor-pointer hover:bg-slate-50/60 transition"
                        data-row-id="{{ $row->id }}"
                        data-tracking-id="{{ $row->tracking_id }}"
                        data-tanggal="{{ $row->tanggal ? $row->tanggal->format('d M Y') : '-' }}"
                        data-waktu="{{ $row->waktu ?? '-' }}"
                        data-pelapor="{{ $row->pelapor ?? '-' }}"
                        data-sudah-ditanggapi="{{ $row->sudah_ditanggapi ? '1' : '0' }}"
                        data-status="{{ $row->status ?? 'Belum Selesai' }}"
                        data-keterangan="{{ $row->keterangan ?? '-' }}"
                        data-klasifikasi="{{ $row->klasifikasi ?? '-' }}"
                        data-id-kategori="{{ $row->id_kategori ?? '-' }}"
                        data-kategori="{{ $row->kategori ?? '-' }}"
                        data-judul="{{ $row->judul ?? '-' }}"
                        data-isi-awal="{{ $row->isi_awal ?? '-' }}"
                        data-isi-akhir="{{ $row->isi_akhir ?? '-' }}"
                        data-tipe-laporan="{{ $row->tipe_laporan ?? '-' }}"
                        data-sumber-laporan="{{ $row->sumber_laporan ?? '-' }}"
                        data-instansi-induk="{{ $row->instansi_induk ?? '-' }}"
                        data-id-instansi-terdisposisi="{{ $row->id_instansi_terdisposisi ?? '-' }}"
                        data-skpd="{{ $row->skpd ?? '-' }}"
                        data-status-laporan-raw="{{ $row->status_laporan_raw ?? '-' }}"
                        data-alasan-tunda-arsip="{{ $row->alasan_tunda_arsip ?? '-' }}"
                        data-provinsi="{{ $row->provinsi ?? '-' }}"
                        data-kabupaten="{{ $row->kabupaten ?? '-' }}"
                        data-kecamatan="{{ $row->kecamatan ?? '-' }}"
                        data-kelurahan="{{ $row->kelurahan ?? '-' }}"
                        data-nomor-sk="{{ $row->nomor_sk ?? '-' }}"
                        data-url-sk="{{ $row->url_sk ?? '' }}"
                        data-url-dokumen="{{ $row->url_dokumen_laporan_tahunan ?? '' }}"
                        data-laporan-setwapres="{{ $row->laporan_setwapres ?? '-' }}"
                        data-rating="{{ $row->rating ?? '-' }}">
                        <td class="px-6 py-5 text-slate-500 js-row-number">{{ $pengaduan->firstItem() + $i }}</td>
                        <td class="px-3 py-5 whitespace-nowrap"><span class="font-bold text-navy">{{ $row->tracking_id }}</span></td>
                        <td class="px-3 py-5 text-slate-500 whitespace-nowrap">
                            {{ $row->tanggal ? $row->tanggal->format('d M Y') : '-' }}
                        </td>
                        <td class="px-3 py-5 text-slate-500 whitespace-nowrap">{{ $row->waktu ?? '-' }}</td>
                        <td class="px-3 py-5 font-semibold text-slate-700 whitespace-nowrap">{{ $row->pelapor ?? '-' }}</td>

                        <td class="px-3 py-5">
                            <select class="js-update-tanggapan text-xs font-semibold rounded-full px-3 py-1.5 border-0 cursor-pointer
                                {{ $row->sudah_ditanggapi ? 'bg-blue-50 text-navy' : 'bg-red-50 text-red-500' }}"
                                data-id="{{ $row->id }}" data-field="sudah_ditanggapi">
                                <option value="1" @selected($row->sudah_ditanggapi)>Sudah Ada Tanggapan</option>
                                <option value="0" @selected(!$row->sudah_ditanggapi)>Belum Ada Tanggapan</option>
                            </select>
                        </td>

                        <td class="px-3 py-5">
                            <select class="js-update-tanggapan text-xs font-semibold rounded-full px-3 py-1.5 border-0 cursor-pointer
                                {{ $row->status === 'Selesai' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500' }}"
                                data-id="{{ $row->id }}" data-field="status">
                                <option value="Selesai" @selected($row->status === 'Selesai')>Selesai</option>
                                <option value="Belum Selesai" @selected($row->status !== 'Selesai')>Belum Selesai</option>
                            </select>
                        </td>

                        <td class="px-3 py-5">
                            <input type="text" class="js-update-keterangan w-full text-xs border border-slate-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-navy/30"
                                data-id="{{ $row->id }}" value="{{ $row->keterangan }}" placeholder="Tulis keterangan...">
                        </td>

                        <td class="px-3 py-5 text-slate-600">{{ $row->klasifikasi ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->id_kategori ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-600">{{ $row->kategori ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-600">{{ $row->judul ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">
                            <span title="{{ $row->isi_awal }}">{{ Str::limit($row->isi_awal, 80) }}</span>
                        </td>
                        <td class="px-3 py-5 text-slate-500">
                            <span title="{{ $row->isi_akhir }}">{{ Str::limit($row->isi_akhir, 80) }}</span>
                        </td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->tipe_laporan ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->sumber_laporan ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->instansi_induk ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->id_instansi_terdisposisi ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-600">{{ $row->skpd ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->status_laporan_raw ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">
                            <span title="{{ $row->alasan_tunda_arsip }}">{{ Str::limit($row->alasan_tunda_arsip, 40) }}</span>
                        </td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->provinsi ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->kabupaten ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->kecamatan ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->kelurahan ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->nomor_sk ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500 whitespace-nowrap">
                            @if($row->url_sk)
                                <a href="{{ $row->url_sk }}" target="_blank" rel="noopener" class="text-navy underline">Lihat SK</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-5 text-slate-500 whitespace-nowrap">
                            @if($row->url_dokumen_laporan_tahunan)
                                <a href="{{ $row->url_dokumen_laporan_tahunan }}" target="_blank" rel="noopener" class="text-navy underline">Lihat Dokumen</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->laporan_setwapres ?? '-' }}</td>
                        <td class="px-3 py-5 text-slate-500">{{ $row->rating ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="30" class="px-6 py-10 text-center text-slate-400">
                            Belum ada data untuk tahun {{ $tahun }}. Klik "Tambah Pengaduan" buat upload file Excel, atau ganti filter tahun di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= INFINITE SCROLL SENTINEL ================= --}}
        {{-- Sentinel ini yang dipantau IntersectionObserver. Begitu kelihatan di viewport
             (user scroll sampai bawah), script fetch halaman berikutnya otomatis. --}}
        <div id="pengaduan-scroll-sentinel" class="px-6 py-6 flex items-center justify-center">
            <div id="pengaduan-loading-indicator" class="hidden items-center gap-2 text-sm text-slate-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memuat data...
            </div>
            <div id="pengaduan-end-indicator" class="hidden text-sm text-slate-400">
                Semua data sudah ditampilkan.
            </div>
        </div>
    </div>

    {{-- ================= MODAL TAMBAH / IMPORT DATA ================= --}}
    <div x-show="showImportModal" x-cloak
         class="fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showImportModal = false">
        <div @click.outside="showImportModal = false"
             class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">

            <div class="flex items-center justify-between mb-1">
                <h3 class="text-xl font-extrabold text-slate-900">Tambah / Import Data Pengaduan</h3>
                <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 mb-5">Unggah Excel, Word, dan/atau PDF sekaligus dalam satu proses import.</p>

            {{-- ================= FORM GABUNGAN — SATU SUBMIT UNTUK SEMUA FILE ================= --}}
            <form method="POST" action="{{ route('pengaduan.import') }}" enctype="multipart/form-data"
                  x-data="{ excelName: '', wordName: '', pdfName: '' }">
                @csrf

                {{-- SLOT EXCEL (wajib, data utama) --}}
                <div class="mb-4">
                    <p class="text-xs font-bold text-slate-500 mb-2">1. Excel — Data Utama <span class="text-red-500">*wajib</span></p>
                    <label for="file_excel_modal"
                           class="border-2 border-dashed border-slate-200 rounded-2xl py-6 flex items-center gap-4 px-5 cursor-pointer hover:border-navy/40 transition">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700 truncate" x-text="excelName || 'Tarik & Lepas atau klik untuk pilih file'"></p>
                            <p class="text-xs text-slate-400 mt-0.5">.xls / .xlsx</p>
                        </div>
                        <input id="file_excel_modal" type="file" name="file_excel" accept=".xlsx,.xls" required class="hidden"
                               @change="excelName = $event.target.files[0]?.name">
                    </label>
                </div>

                {{-- SLOT WORD (opsional, tanggapan) --}}
                <div class="mb-4">
                    <p class="text-xs font-bold text-slate-500 mb-2">2. Word — Tanggapan <span class="text-slate-400">(opsional)</span></p>
                    <label for="file_word_modal"
                           class="border-2 border-dashed border-slate-200 rounded-2xl py-6 flex items-center gap-4 px-5 cursor-pointer hover:border-navy/40 transition">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700 truncate" x-text="wordName || 'Tarik & Lepas atau klik untuk pilih file'"></p>
                            <p class="text-xs text-slate-400 mt-0.5">.docx — di-join lewat Tracking ID</p>
                        </div>
                        <input id="file_word_modal" type="file" name="file_word" accept=".docx" class="hidden"
                               @change="wordName = $event.target.files[0]?.name">
                    </label>
                </div>

                {{-- SLOT PDF (opsional, alternatif Word) --}}
                <div class="mb-5">
                    <p class="text-xs font-bold text-slate-500 mb-2">3. PDF — Alternatif Word <span class="text-slate-400">(opsional)</span></p>
                    <label for="file_pdf_modal"
                           class="border-2 border-dashed border-slate-200 rounded-2xl py-6 flex items-center gap-4 px-5 cursor-pointer hover:border-navy/40 transition">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700 truncate" x-text="pdfName || 'Tarik & Lepas atau klik untuk pilih file'"></p>
                            <p class="text-xs text-slate-400 mt-0.5">.pdf — dipakai kalau rekap tanggapan dalam bentuk PDF</p>
                        </div>
                        <input id="file_pdf_modal" type="file" name="file_pdf" accept=".pdf" class="hidden"
                               @change="pdfName = $event.target.files[0]?.name">
                    </label>
                </div>

                <button type="submit" class="w-full bg-navy hover:bg-navy-dark text-white text-sm font-semibold py-3 rounded-xl">
                    Import Data
                </button>
            </form>

            <div class="bg-blue-50 rounded-xl p-4 mt-4">
                <p class="text-xs font-bold text-navy mb-2">Panduan Import</p>
                <ul class="text-xs text-slate-600 space-y-1 list-disc list-inside">
                    <li>Pastikan format kolom sesuai dengan template yang disediakan.</li>
                    <li>Excel wajib diisi; Word dan/atau PDF opsional dan boleh diunggah bersamaan.</li>
                    <li>Semua file diproses dalam satu kali submit, di-join otomatis lewat Tracking ID.</li>
                    <li>Data dengan Tracking ID yang sama akan otomatis ter-update, bukan dobel.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ================= PANEL DETAIL PENGADUAN (nempel di kanan, muncul saat baris tabel diklik) ================= --}}
    <div id="pengaduan-detail-modal" class="hidden fixed inset-0 bg-slate-900/40 z-50">
        <div id="pengaduan-detail-panel"
             class="absolute top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl overflow-y-auto
                    transform translate-x-full transition-transform duration-300 ease-out">

            {{-- Header --}}
            <div class="flex items-start justify-between px-6 pt-6 pb-4 sticky top-0 bg-white border-b border-slate-100 z-10">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Detail Pengaduan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tracking ID: <span id="detail-tracking-id" class="font-bold text-navy"></span></p>
                </div>
                <button type="button" id="pengaduan-detail-close" class="text-slate-400 hover:text-slate-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">
                {{-- Data Pelapor --}}
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-wide mb-2">DATA PELAPOR</p>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-slate-400">Nama</p>
                            <p id="detail-pelapor" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Tanggal / Waktu</p>
                            <p class="font-semibold text-slate-700"><span id="detail-tanggal"></span> · <span id="detail-waktu"></span></p>
                        </div>
                    </div>
                </div>

                {{-- Isi Aduan --}}
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-wide mb-2">ISI ADUAN</p>
                    <p id="detail-judul" class="font-bold text-slate-800 mb-1"></p>
                    <p class="text-xs text-slate-400 mt-2">Laporan Awal</p>
                    <p id="detail-isi-awal" class="text-sm text-slate-600 whitespace-pre-line"></p>
                    <div id="detail-isi-akhir-wrap">
                        <p class="text-xs text-slate-400 mt-3">Laporan Akhir</p>
                        <p id="detail-isi-akhir" class="text-sm text-slate-600 whitespace-pre-line"></p>
                    </div>
                </div>

                {{-- Informasi Tambahan --}}
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-wide mb-2">INFORMASI TAMBAHAN</p>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-slate-400">Klasifikasi</p>
                            <p id="detail-klasifikasi" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Kategori</p>
                            <p id="detail-kategori" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Tipe Laporan</p>
                            <p id="detail-tipe-laporan" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Sumber Laporan</p>
                            <p id="detail-sumber-laporan" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Instansi Induk</p>
                            <p id="detail-instansi-induk" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Instansi Terdisposisi (SKPD)</p>
                            <p id="detail-skpd" class="font-semibold text-slate-700"></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-slate-400">Lokasi</p>
                            <p id="detail-lokasi" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Nomor SK</p>
                            <p id="detail-nomor-sk" class="font-semibold text-slate-700"></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Rating</p>
                            <p id="detail-rating" class="font-semibold text-slate-700"></p>
                        </div>
                    </div>
                    <div class="flex gap-4 mt-3">
                        <a id="detail-url-sk" href="#" target="_blank" rel="noopener" class="hidden text-sm text-navy underline font-semibold">Lihat SK</a>
                        <a id="detail-url-dokumen" href="#" target="_blank" rel="noopener" class="hidden text-sm text-navy underline font-semibold">Lihat Dokumen</a>
                    </div>
                </div>

                {{-- Status & Tindak Lanjut --}}
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-wide mb-2">STATUS &amp; TINDAK LANJUT</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span id="detail-tanggapan-badge" class="text-xs font-semibold rounded-full px-3 py-1.5"></span>
                        <span id="detail-status-badge" class="text-xs font-semibold rounded-full px-3 py-1.5"></span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Keterangan</p>
                        <p id="detail-keterangan" class="text-sm text-slate-600"></p>
                    </div>
                    <div id="detail-alasan-wrap" class="mt-2">
                        <p class="text-xs text-slate-400">Alasan Tunda/Arsip</p>
                        <p id="detail-alasan" class="text-sm text-slate-600"></p>
                    </div>
                </div>
            </div>

            <div class="px-6 pb-6">
                <button type="button" id="pengaduan-detail-close-btn"
                        class="w-full bg-navy hover:bg-navy-dark text-white text-sm font-semibold py-3 rounded-xl">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>

    {{-- SCRIPT UNTUK UPDATE INLINE VIA AJAX + INFINITE SCROLL --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function kirimUpdate(id, payload, onSuccess) {
            fetch(`/pengaduan/${id}/update-tanggapan`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && onSuccess) onSuccess(data);
            })
            .catch(() => alert('Gagal menyimpan perubahan, coba lagi.'));
        }

        function bindRowEvents(scope) {
            scope.querySelectorAll('.js-update-tanggapan').forEach(function (select) {
                if (select.dataset.bound) return;
                select.dataset.bound = '1';

                select.addEventListener('change', function () {
                    const id = this.dataset.id;
                    const field = this.dataset.field;
                    const row = this.closest('tr');
                    const keteranganInput = row.querySelector('.js-update-keterangan');

                    const payload = {
                        sudah_ditanggapi: row.querySelector('[data-field="sudah_ditanggapi"]').value,
                        status: row.querySelector('[data-field="status"]').value,
                        keterangan: keteranganInput ? keteranganInput.value : null,
                    };

                    kirimUpdate(id, payload, () => {
                        this.classList.remove('bg-blue-50', 'text-navy', 'bg-red-50', 'text-red-500', 'bg-green-50', 'text-green-600');
                        if (field === 'sudah_ditanggapi') {
                            this.classList.add(...(this.value === '1' ? ['bg-blue-50', 'text-navy'] : ['bg-red-50', 'text-red-500']));
                        } else {
                            this.classList.add(...(this.value === 'Selesai' ? ['bg-green-50', 'text-green-600'] : ['bg-red-50', 'text-red-500']));
                        }
                    });
                });
            });

            scope.querySelectorAll('.js-update-keterangan').forEach(function (input) {
                if (input.dataset.bound) return;
                input.dataset.bound = '1';

                input.addEventListener('blur', function () {
                    const id = this.dataset.id;
                    const row = this.closest('tr');

                    const payload = {
                        sudah_ditanggapi: row.querySelector('[data-field="sudah_ditanggapi"]').value,
                        status: row.querySelector('[data-field="status"]').value,
                        keterangan: this.value,
                    };

                    kirimUpdate(id, payload);
                });
            });
        }

        bindRowEvents(document);

        const detailModal = document.getElementById('pengaduan-detail-modal');
        const detailPanel = document.getElementById('pengaduan-detail-panel');

        function openDetailModal(row) {
            const d = row.dataset;

            document.getElementById('detail-tracking-id').textContent = d.trackingId || '-';
            document.getElementById('detail-pelapor').textContent = d.pelapor || '-';
            document.getElementById('detail-tanggal').textContent = d.tanggal || '-';
            document.getElementById('detail-waktu').textContent = d.waktu || '-';
            document.getElementById('detail-judul').textContent = d.judul || '-';
            document.getElementById('detail-isi-awal').textContent = d.isiAwal || '-';

            const isiAkhirWrap = document.getElementById('detail-isi-akhir-wrap');
            if (d.isiAkhir && d.isiAkhir !== '-' && d.isiAkhir.trim() !== '') {
                document.getElementById('detail-isi-akhir').textContent = d.isiAkhir;
                isiAkhirWrap.classList.remove('hidden');
            } else {
                isiAkhirWrap.classList.add('hidden');
            }

            document.getElementById('detail-klasifikasi').textContent = d.klasifikasi || '-';
            document.getElementById('detail-kategori').textContent = d.kategori || '-';
            document.getElementById('detail-tipe-laporan').textContent = d.tipeLaporan || '-';
            document.getElementById('detail-sumber-laporan').textContent = d.sumberLaporan || '-';
            document.getElementById('detail-instansi-induk').textContent = d.instansiInduk || '-';
            document.getElementById('detail-skpd').textContent = d.skpd || '-';
            document.getElementById('detail-nomor-sk').textContent = d.nomorSk || '-';
            document.getElementById('detail-rating').textContent = d.rating || '-';

            const lokasiParts = [d.kelurahan, d.kecamatan, d.kabupaten, d.provinsi]
                .filter(v => v && v !== '-' && v.trim() !== '');
            document.getElementById('detail-lokasi').textContent = lokasiParts.length ? lokasiParts.join(', ') : '-';

            const skLink = document.getElementById('detail-url-sk');
            if (d.urlSk) { skLink.href = d.urlSk; skLink.classList.remove('hidden'); } else { skLink.classList.add('hidden'); }

            const dokLink = document.getElementById('detail-url-dokumen');
            if (d.urlDokumen) { dokLink.href = d.urlDokumen; dokLink.classList.remove('hidden'); } else { dokLink.classList.add('hidden'); }

            const tanggapanBadge = document.getElementById('detail-tanggapan-badge');
            const sudahDitanggapi = d.sudahDitanggapi === '1';
            tanggapanBadge.textContent = sudahDitanggapi ? 'Sudah Ada Tanggapan' : 'Belum Ada Tanggapan';
            tanggapanBadge.className = 'text-xs font-semibold rounded-full px-3 py-1.5 ' +
                (sudahDitanggapi ? 'bg-blue-50 text-navy' : 'bg-red-50 text-red-500');

            const statusBadge = document.getElementById('detail-status-badge');
            const selesai = d.status === 'Selesai';
            statusBadge.textContent = selesai ? 'Selesai' : 'Belum Selesai';
            statusBadge.className = 'text-xs font-semibold rounded-full px-3 py-1.5 ' +
                (selesai ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500');

            document.getElementById('detail-keterangan').textContent = (d.keterangan && d.keterangan.trim() !== '') ? d.keterangan : '-';

            const alasanWrap = document.getElementById('detail-alasan-wrap');
            if (d.alasanTundaArsip && d.alasanTundaArsip !== '-' && d.alasanTundaArsip.trim() !== '') {
                document.getElementById('detail-alasan').textContent = d.alasanTundaArsip;
                alasanWrap.classList.remove('hidden');
            } else {
                alasanWrap.classList.add('hidden');
            }

            detailModal.classList.remove('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    detailPanel.classList.remove('translate-x-full');
                });
            });
        }

        function closeDetailModal() {
            detailPanel.classList.add('translate-x-full');
            setTimeout(() => {
                detailModal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('pengaduan-detail-close')?.addEventListener('click', closeDetailModal);
        document.getElementById('pengaduan-detail-close-btn')?.addEventListener('click', closeDetailModal);
        detailModal?.addEventListener('click', function (e) {
            if (!detailPanel.contains(e.target)) closeDetailModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDetailModal();
        });

        function bindDetailClick(scope) {
            scope.querySelectorAll('.js-pengaduan-row').forEach(function (row) {
                if (row.dataset.detailBound) return;
                row.dataset.detailBound = '1';

                row.addEventListener('click', function (e) {
                    if (e.target.closest('select, input, a, button')) return;
                    openDetailModal(this);
                });
            });
        }

        bindDetailClick(document);

        const card = document.getElementById('pengaduan-table-card');
        const tbody = document.getElementById('pengaduan-tbody');
        const sentinel = document.getElementById('pengaduan-scroll-sentinel');
        const loadingIndicator = document.getElementById('pengaduan-loading-indicator');
        const endIndicator = document.getElementById('pengaduan-end-indicator');
        const shownCountEl = document.getElementById('pengaduan-shown-count');

        let nextPageUrl = card?.dataset.nextPageUrl || null;
        let isLoading = false;
        let rowCounter = tbody.querySelectorAll('tr[data-row-id]').length;

        function loadNextPage() {
            if (! nextPageUrl || isLoading) return;
            isLoading = true;
            loadingIndicator?.classList.remove('hidden');
            loadingIndicator?.classList.add('flex');

            fetch(nextPageUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newRows = doc.querySelectorAll('#pengaduan-tbody tr[data-row-id]');
                    newRows.forEach(row => {
                        rowCounter++;
                        const numberCell = row.querySelector('.js-row-number');
                        if (numberCell) numberCell.textContent = rowCounter;
                        tbody.appendChild(row);
                    });

                    bindRowEvents(tbody);
                    bindDetailClick(tbody);

                    if (shownCountEl) shownCountEl.textContent = rowCounter;

                    const nextCard = doc.getElementById('pengaduan-table-card');
                    nextPageUrl = nextCard?.dataset.nextPageUrl || null;

                    if (! nextPageUrl) {
                        endIndicator?.classList.remove('hidden');
                        observer.disconnect();
                    }
                })
                .catch(() => {})
                .finally(() => {
                    isLoading = false;
                    loadingIndicator?.classList.add('hidden');
                    loadingIndicator?.classList.remove('flex');
                });
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) loadNextPage();
            });
        }, { rootMargin: '200px' });

        if (sentinel && nextPageUrl) {
            observer.observe(sentinel);
        } else if (sentinel && ! nextPageUrl) {
            endIndicator?.classList.remove('hidden');
        }
    });
    </script>
</div>
@endsection