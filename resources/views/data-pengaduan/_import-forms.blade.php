{{-- Tempel snippet ini di atas tabel "Data Pengaduan" yang sudah ada --}}

@if (session('success'))
    <div class="mb-4 rounded-xl bg-green-50 text-green-700 text-sm px-4 py-3">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="mb-4 rounded-xl bg-yellow-50 text-yellow-700 text-sm px-4 py-3">{{ session('warning') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Upload Excel: data mentah dari LAPOR.go.id --}}
    <form method="POST" action="{{ route('pengaduan.import.excel') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl p-4 card-shadow">
        @csrf
        <label class="block text-[11px] font-bold text-slate-400 tracking-wide mb-2">1. UPLOAD EXCEL (DATA UTAMA)</label>
        <input type="file" name="file_excel" accept=".xlsx,.xls" required
               class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2">
        <button type="submit" class="mt-3 w-full bg-navy text-white text-sm font-semibold py-2.5 rounded-lg">
            Import Excel
        </button>
    </form>

    {{-- Upload Word: rekap manual Tanggapan & Keterangan, di-join pakai Tracking ID --}}
    <form method="POST" action="{{ route('pengaduan.import.word') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl p-4 card-shadow">
        @csrf
        <label class="block text-[11px] font-bold text-slate-400 tracking-wide mb-2">2. UPLOAD WORD (TANGGAPAN &amp; KETERANGAN)</label>
        <input type="file" name="file_word" accept=".docx" required
               class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2">
        <button type="submit" class="mt-3 w-full border border-navy text-navy text-sm font-semibold py-2.5 rounded-lg bg-white">
            Import Word
        </button>
    </form>

    {{-- Upload PDF: alternatif kalau rekapnya dalam bentuk PDF, bukan Word --}}
    <form method="POST" action="{{ route('pengaduan.import.pdf') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl p-4 card-shadow">
        @csrf
        <label class="block text-[11px] font-bold text-slate-400 tracking-wide mb-2">3. UPLOAD PDF (ALTERNATIF WORD)</label>
        <input type="file" name="file_pdf" accept=".pdf" required
               class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2">
        <button type="submit" class="mt-3 w-full border border-navy text-navy text-sm font-semibold py-2.5 rounded-lg bg-white">
            Import PDF
        </button>
    </form>

</div>