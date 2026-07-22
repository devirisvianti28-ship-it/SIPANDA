@extends('layouts.app')

@section('title', 'Arsip Laporan')

@section('content')
<div class="max-w-6xl">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800">Laporan Pengaduan</h1>
        <p class="text-slate-500 text-sm mt-1">Ikuti langkah-langkah di bawah untuk membuat laporan yang diinginkan.</p>
    </div>

    {{-- Total Arsip --}}
    <div class="bg-white rounded-2xl border border-slate-200 card-shadow p-6 flex items-start justify-between mb-6">
        <div>
            <p class="text-sm text-slate-500 font-medium mb-1.5">Total Arsip Laporan</p>
            <p class="text-3xl md:text-4xl font-extrabold text-slate-800">{{ number_format($totalArsip ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center text-navy shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M8 10l-3 3 3 3M16 10l3 3-3 3"/>
            </svg>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('laporan.pengaduan') }}"
          class="bg-white rounded-2xl border border-slate-200 card-shadow p-5 md:p-6 flex flex-wrap items-end gap-4 mb-6">
        <div class="flex flex-col gap-2 flex-1 min-w-[180px]">
            <label for="tanggal_dibuat" class="text-xs font-medium text-slate-500">Tanggal dibuat</label>
            <input type="date" id="tanggal_dibuat" name="tanggal_dibuat"
                   value="{{ request('tanggal_dibuat') }}"
                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30 bg-white">
        </div>

        <div class="flex flex-col gap-2 flex-1 min-w-[180px]">
            <label for="jenis" class="text-xs font-medium text-slate-500">Jenis laporan</label>
            <select id="jenis" name="jenis"
                    class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-navy/30 bg-white">
                <option value="">Semua jenis</option>
                <option value="harian" {{ request('jenis') == 'harian' ? 'selected' : '' }}>Harian</option>
                <option value="mingguan" {{ request('jenis') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                <option value="bulanan" {{ request('jenis') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                <option value="tahunan" {{ request('jenis') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
            </select>
        </div>

        <button type="submit"
                class="inline-flex items-center gap-2 bg-navy hover:bg-navy-dark text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            Tampilkan laporan
        </button>
    </form>

    {{-- Arsip laporan --}}
    <div class="bg-white rounded-2xl border border-slate-200 card-shadow overflow-hidden">
        <div class="px-6 pt-5 pb-4">
            <h2 class="font-bold text-slate-800">Arsip laporan</h2>
            <p class="text-sm text-slate-500 mt-0.5">Menampilkan {{ $laporans->count() ?? 0 }} laporan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-y border-slate-200">
                        <th class="text-left text-[11px] font-semibold tracking-wider text-slate-400 uppercase px-6 py-3 whitespace-nowrap">Nama Laporan</th>
                        <th class="text-left text-[11px] font-semibold tracking-wider text-slate-400 uppercase px-3 py-3 whitespace-nowrap">Jenis</th>
                        <th class="text-left text-[11px] font-semibold tracking-wider text-slate-400 uppercase px-3 py-3 whitespace-nowrap">Periode</th>
                        <th class="text-left text-[11px] font-semibold tracking-wider text-slate-400 uppercase px-3 py-3 whitespace-nowrap">Dibuat</th>
                        <th class="text-left text-[11px] font-semibold tracking-wider text-slate-400 uppercase px-3 py-3 whitespace-nowrap">Format</th>
                        <th class="text-left text-[11px] font-semibold tracking-wider text-slate-400 uppercase px-3 py-3 whitespace-nowrap">Ukuran</th>
                        <th class="text-right text-[11px] font-semibold tracking-wider text-slate-400 uppercase px-6 py-3 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($laporans as $laporan)
                    <tr class="border-b border-slate-100 last:border-none hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-sm text-slate-800">{{ $laporan['nama'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $laporan['skpd'] }}</p>
                        </td>
                        <td class="px-3 py-4">
                            <span class="inline-block bg-slate-100 text-slate-600 text-xs font-medium px-3 py-1 rounded-full">{{ $laporan['jenis'] }}</span>
                        </td>
                        <td class="px-3 py-4 text-sm text-slate-500 whitespace-nowrap">{{ $laporan['periode'] }}</td>
                        <td class="px-3 py-4 text-sm text-slate-500 whitespace-nowrap">{{ $laporan['dibuat'] }}</td>
                        <td class="px-3 py-4">
                            <span class="inline-block bg-red-50 text-red-500 text-xs font-bold px-2.5 py-1 rounded-md">{{ $laporan['format'] }}</span>
                        </td>
                        <td class="px-3 py-4 text-sm text-slate-500 whitespace-nowrap">{{ $laporan['ukuran'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ $laporan['url_lihat'] ?? '#' }}" title="Lihat"
                                   class="text-slate-400 hover:text-navy hover:bg-blue-50 p-1.5 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                <a href="{{ $laporan['url_unduh'] ?? '#' }}" title="Unduh"
                                   class="text-slate-400 hover:text-navy hover:bg-blue-50 p-1.5 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-400 text-sm py-10">
                            Belum ada laporan untuk filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

</div>
@endsection