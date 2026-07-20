<?php

namespace App\Http\Controllers;

use App\Services\PengaduanExcelImporter;
use App\Services\PengaduanWordImporter;
use App\Services\PengaduanPdfImporter;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PengaduanImportController extends Controller
{
    /**
     * Import gabungan: Excel (wajib) + Word (opsional) + PDF (opsional) dalam satu submit.
     * Urutan proses: Excel dulu (bikin/update baris data utama by Tracking ID),
     * baru Word/PDF kalau file-nya dikirim (update kolom tanggapan & keterangan).
     * Ini yang dipanggil oleh modal import gabungan di halaman Data Pengaduan.
     */
    public function import(
        Request $request,
        PengaduanExcelImporter $excelImporter,
        PengaduanWordImporter $wordImporter,
        PengaduanPdfImporter $pdfImporter
    ): RedirectResponse {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
            'file_word'  => 'nullable|file|mimes:docx',
            'file_pdf'   => 'nullable|file|mimes:pdf',
        ]);

        $messages = [];
        $hasWarning = false;

        // 1. Excel dulu — ini yang bikin/update baris data utama.
        try {
            $excelSummary = $excelImporter->import($request->file('file_excel')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'Import Excel gagal: ' . $e->getMessage());
        }

        $messages[] = sprintf(
            'Excel: %d data baru, %d ter-update, %d dilewati.',
            $excelSummary['inserted'],
            $excelSummary['updated'],
            $excelSummary['skipped']
        );

        // 2. Word — cuma diproses kalau ada filenya.
        if ($request->hasFile('file_word')) {
            try {
                $wordSummary = $wordImporter->import($request->file('file_word')->getRealPath());
            } catch (\Throwable $e) {
                return back()->with('error', implode(' ', $messages) . ' Tapi import Word gagal: ' . $e->getMessage());
            }

            $messages[] = sprintf(
                'Word: %d tanggapan/keterangan ter-update, %d dilewati.',
                $wordSummary['updated'],
                $wordSummary['skipped']
            );

            if (! empty($wordSummary['not_found'])) {
                $hasWarning = true;
                $messages[] = 'Tracking ID dari Word yang tidak ditemukan di Excel: '
                    . implode(', ', array_unique($wordSummary['not_found'])) . '.';
            }
        }

        // 3. PDF — cuma diproses kalau ada filenya.
        if ($request->hasFile('file_pdf')) {
            try {
                $pdfSummary = $pdfImporter->import($request->file('file_pdf')->getRealPath());
            } catch (\Throwable $e) {
                return back()->with('error', implode(' ', $messages) . ' Tapi import PDF gagal: ' . $e->getMessage());
            }

            $messages[] = sprintf(
                'PDF: %d tanggapan/keterangan ter-update, %d dilewati.',
                $pdfSummary['updated'],
                $pdfSummary['skipped']
            );

            if (! empty($pdfSummary['not_found'])) {
                $hasWarning = true;
                $messages[] = 'Tracking ID dari PDF yang tidak ditemukan di Excel: '
                    . implode(', ', array_unique($pdfSummary['not_found'])) . '.';
            }
        }

        $finalMessage = implode(' ', $messages);

        return $hasWarning
            ? back()->with('warning', $finalMessage)
            : back()->with('success', $finalMessage);
    }

    public function importExcel(Request $request, PengaduanExcelImporter $importer): RedirectResponse
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('file_excel')->getRealPath();

        try {
            $summary = $importer->import($path);
        } catch (\Throwable $e) {
            return back()->with('error', 'Import Excel gagal: ' . $e->getMessage());
        }

        return back()->with('success', sprintf(
            'Excel berhasil diproses. %d data baru, %d data ter-update, %d baris dilewati.',
            $summary['inserted'],
            $summary['updated'],
            $summary['skipped']
        ));
    }

    public function importWord(Request $request, PengaduanWordImporter $importer): RedirectResponse
    {
        $request->validate([
            'file_word' => 'required|file|mimes:docx',
        ]);

        $path = $request->file('file_word')->getRealPath();

        try {
            $summary = $importer->import($path);
        } catch (\Throwable $e) {
            return back()->with('error', 'Import Word gagal: ' . $e->getMessage());
        }

        $message = sprintf(
            'Word berhasil diproses. %d tanggapan/keterangan ter-update, %d baris dilewati.',
            $summary['updated'],
            $summary['skipped']
        );

        if (! empty($summary['not_found'])) {
            $message .= ' Tracking ID berikut ada di Word tapi TIDAK ditemukan di data Excel: '
                . implode(', ', array_unique($summary['not_found']))
                . '. Cek lagi, mungkin Excel belum di-import atau ID-nya beda.';

            return back()->with('warning', $message);
        }

        return back()->with('success', $message);
    }

    public function importPdf(Request $request, PengaduanPdfImporter $importer): RedirectResponse
    {
        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf',
        ]);

        $path = $request->file('file_pdf')->getRealPath();

        try {
            $summary = $importer->import($path);
        } catch (\Throwable $e) {
            return back()->with('error', 'Import PDF gagal: ' . $e->getMessage());
        }

        $message = sprintf(
            'PDF berhasil diproses. %d tanggapan/keterangan ter-update, %d baris dilewati.',
            $summary['updated'],
            $summary['skipped']
        );

        if (! empty($summary['not_found'])) {
            $message .= ' Tracking ID berikut ada di PDF tapi TIDAK ditemukan di data Excel: '
                . implode(', ', array_unique($summary['not_found']))
                . '.';

            return back()->with('warning', $message);
        }

        return back()->with('success', $message);
    }
}