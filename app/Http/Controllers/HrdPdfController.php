<?php

namespace App\Http\Controllers;

use App\Models\Gaji;
use App\Models\PayrollBorongan;
use App\Models\PengPinjaman;
use App\Services\HR\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HrdPdfController extends Controller
{
    protected function preparePdfRuntime(): void
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');
    }

    protected function getLogoBase64(): string
    {
        $path = public_path('images/Logo PT Teknografi.png');
        if (file_exists($path)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        return '';
    }

    public function previewSlipGaji(Gaji $gaji)
    {
        $this->preparePdfRuntime();
        $gaji->load(['karyawan.departemen', 'accountKas']);
        $pdf = Pdf::loadView('pdf.slip-gaji', [
            'logoBase64' => $this->getLogoBase64(),
            'slips'      => collect([$gaji]),
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Slip-Gaji-' . str_replace('/', '-', $gaji->kode_slip) . '.pdf');
    }

    public function downloadSlipGaji(Gaji $gaji)
    {
        $this->preparePdfRuntime();
        $gaji->load(['karyawan.departemen', 'accountKas']);
        $pdf = Pdf::loadView('pdf.slip-gaji', [
            'logoBase64' => $this->getLogoBase64(),
            'slips'      => collect([$gaji]),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Slip-Gaji-' . str_replace('/', '-', $gaji->kode_slip) . '.pdf');
    }

    public function batchSlipGaji(Request $request)
    {
        $this->preparePdfRuntime();
        $periode = $request->query('periode', now()->format('Y-m'));
        $slips = Gaji::with(['karyawan.departemen', 'accountKas'])
            ->where('periode', $periode)
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('pdf.slip-gaji', [
            'logoBase64' => $this->getLogoBase64(),
            'slips'      => $slips,
            'periode'    => $periode,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("Slip-Gaji-Massal-{$periode}.pdf");
    }

    public function previewRekapPayroll(Request $request)
    {
        $this->preparePdfRuntime();
        $periode = $request->query('periode', now()->format('Y-m'));
        $tipe = $request->query('tipe', '');

        $rows = Gaji::with(['karyawan.departemen', 'accountKas'])
            ->where('periode', $periode)
            ->when(filled($tipe), fn ($q) => $q->where('tipe_payroll', $tipe))
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('pdf.rekap-payroll', [
            'logoBase64' => $this->getLogoBase64(),
            'periode'    => $periode,
            'tipe'       => $tipe ?: 'Semua Skema',
            'rows'       => $rows,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream("Rekap-Payroll-{$periode}.pdf");
    }

    public function previewPayrollBorongan(PayrollBorongan $payrollBorongan)
    {
        $this->preparePdfRuntime();
        $payrollBorongan->load(['items.karyawan', 'accountKas']);
        $pdf = Pdf::loadView('pdf.payroll-borongan', [
            'logoBase64' => $this->getLogoBase64(),
            'borongan'   => $payrollBorongan,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('Payroll-Borongan-' . str_replace('/', '-', $payrollBorongan->kode_bukti) . '.pdf');
    }

    public function downloadPayrollBorongan(PayrollBorongan $payrollBorongan)
    {
        $this->preparePdfRuntime();
        $payrollBorongan->load(['items.karyawan', 'accountKas']);
        $pdf = Pdf::loadView('pdf.payroll-borongan', [
            'logoBase64' => $this->getLogoBase64(),
            'borongan'   => $payrollBorongan,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('Payroll-Borongan-' . str_replace('/', '-', $payrollBorongan->kode_bukti) . '.pdf');
    }

    public function previewPinjaman(PengPinjaman $pengPinjaman)
    {
        $this->preparePdfRuntime();
        $pengPinjaman->load(['karyawan.departemen', 'pembayaran']);
        $pdf = Pdf::loadView('pdf.pinjaman-karyawan', [
            'logoBase64' => $this->getLogoBase64(),
            'mode'       => 'single',
            'pinjaman'   => $pengPinjaman,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Form-Pinjaman-' . str_replace('/', '-', $pengPinjaman->nomor_bukti) . '.pdf');
    }

    public function previewRekapPinjaman()
    {
        $this->preparePdfRuntime();
        $rows = PengPinjaman::with(['karyawan.departemen', 'pembayaran'])->orderByDesc('id')->get();
        $pdf = Pdf::loadView('pdf.pinjaman-karyawan', [
            'logoBase64' => $this->getLogoBase64(),
            'mode'       => 'rekap',
            'rows'       => $rows,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('Rekap-Pinjaman-Karyawan.pdf');
    }

    public function previewRekapAbsensi(Request $request)
    {
        $this->preparePdfRuntime();
        $tahun = (int) $request->query('tahun', now()->year);
        $bulan = (int) $request->query('bulan', now()->month);
        $deptId = filled($request->query('departemen_id')) ? (int) $request->query('departemen_id') : null;
        $tipe = filled($request->query('tipe_gaji')) ? (string) $request->query('tipe_gaji') : null;

        $recap = app(AttendanceService::class)->getMonthlyRecap($tahun, $bulan, $deptId, $tipe);
        $pdf = Pdf::loadView('pdf.rekap-absensi', [
            'logoBase64' => $this->getLogoBase64(),
            'recap'      => $recap,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream("Rekap-Absensi-{$recap['periode']}.pdf");
    }

    public function downloadPayrollTemplate()
    {
        $service = app(\App\Services\HR\PayrollExcelImportService::class);
        $spreadsheet = $service->generateTemplateSpreadsheet();
        $fileName = 'Template-Impor-Payroll-TTC.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadBoronganTemplate()
    {
        $service = app(\App\Services\HR\PayrollExcelImportService::class);
        $spreadsheet = $service->generateBoronganTemplateSpreadsheet();
        $fileName = 'Template-Impor-Borongan-TTC.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportKaryawanExcel(Request $request)
    {
        $templateOnly = (bool) $request->boolean('template', false);
        $service = app(\App\Services\HR\KaryawanExcelService::class);
        $spreadsheet = $service->exportSpreadsheet($templateOnly);
        $fileName = $templateOnly
            ? 'Template-Impor-Karyawan-TTC.xlsx'
            : 'Data-Karyawan-TTC-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
