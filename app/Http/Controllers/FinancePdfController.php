<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\OperasionalPay;
use App\Models\TransKas;
use App\Models\TransKasBank;
use App\Services\Finance\FinancialReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FinancePdfController extends Controller
{
    protected function getLogoBase64(): string
    {
        $logoPath = public_path('images/Logo PT Teknografi.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('images/logo.png');
        }

        if (file_exists($logoPath)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        return '';
    }

    protected function terbilang(float $nominal): string
    {
        if ($nominal <= 0) {
            return 'Nol Rupiah';
        }
        $intPart = floor($nominal);
        $fraction = (int) round(($nominal - $intPart) * 100);
        $hasil = trim(Invoice::penyebut($intPart)) . ' Rupiah';
        if ($fraction > 0) {
            $hasil .= ' ' . trim(Invoice::penyebut($fraction)) . ' Sen';
        }
        return preg_replace('/\s+/', ' ', $hasil);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. VOUCHER KAS (KAS MASUK / KAS KELUAR)
    |--------------------------------------------------------------------------
    */

    protected function buildKasPdf(TransKas $transKas)
    {
        $transKas->loadMissing(['accountKas', 'accountBank', 'karyawan', 'customer']);
        $isMasuk = $transKas->tipe_transaksi === 'KAS_MASUK';

        $lines = [];
        if ($isMasuk) {
            if ($transKas->accountKas) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKas->accountKas->kode_akun),
                    'nama_akun'  => $transKas->accountKas->nama_akun,
                    'keterangan' => $transKas->keterangan ?: 'Penerimaan Kas Tunai',
                    'debit'      => (float) $transKas->nominal,
                    'kredit'     => 0.0,
                ];
            }
            if ($transKas->accountBank) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKas->accountBank->kode_akun),
                    'nama_akun'  => $transKas->accountBank->nama_akun,
                    'keterangan' => 'Akun Lawan / Sumber Dana',
                    'debit'      => 0.0,
                    'kredit'     => (float) $transKas->nominal,
                ];
            }
        } else {
            if ($transKas->accountBank) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKas->accountBank->kode_akun),
                    'nama_akun'  => $transKas->accountBank->nama_akun,
                    'keterangan' => $transKas->keterangan ?: 'Pengeluaran Kas Tunai',
                    'debit'      => (float) $transKas->nominal,
                    'kredit'     => 0.0,
                ];
            }
            if ($transKas->accountKas) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKas->accountKas->kode_akun),
                    'nama_akun'  => $transKas->accountKas->nama_akun,
                    'keterangan' => 'Rekening Kas Pembayar',
                    'debit'      => 0.0,
                    'kredit'     => (float) $transKas->nominal,
                ];
            }
        }

        return Pdf::loadView('pdf.voucher-finance', [
            'logoBase64'     => $this->getLogoBase64(),
            'judul'          => $isMasuk ? 'BUKTI KAS MASUK (BKM)' : 'BUKTI KAS KELUAR (BKK)',
            'subjudul'       => 'VOUCHER TRANSAKSI KAS TUNAI',
            'no_bukti'       => $transKas->kode_transaksi,
            'tanggal'        => $transKas->tanggal_transaksi ? $transKas->tanggal_transaksi->format('d/m/Y') : now()->format('d/m/Y'),
            'periode'        => $transKas->periode ?? '-',
            'gudang'         => $transKas->gudang ?? 'TTC',
            'pihak_terkait'  => $transKas->customer?->nama_customer ?? ($transKas->karyawan?->nama ?? '-'),
            'label_pihak'    => $isMasuk ? 'Diterima Dari' : 'Dibayarkan Kepada',
            'keterangan'     => $transKas->keterangan ?? '-',
            'nominal'        => (float) $transKas->nominal,
            'terbilang'      => $this->terbilang((float) $transKas->nominal),
            'lines'          => $lines,
            'pic_nama'       => $transKas->karyawan?->nama ?? (auth()->user()?->name ?? 'Admin Finance'),
        ])->setPaper('A4', 'portrait');
    }

    public function previewVoucherKas(TransKas $transKas)
    {
        return $this->buildKasPdf($transKas)->stream('Voucher_Kas_' . $transKas->kode_transaksi . '.pdf');
    }

    public function downloadVoucherKas(TransKas $transKas)
    {
        return $this->buildKasPdf($transKas)->download('Voucher_Kas_' . $transKas->kode_transaksi . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | 2. VOUCHER BANK (BANK MASUK / BANK KELUAR)
    |--------------------------------------------------------------------------
    */

    protected function buildBankPdf(TransKasBank $transKasBank)
    {
        $transKasBank->loadMissing(['accountBank', 'accountLawan', 'customer']);
        $isMasuk = $transKasBank->tipe_transaksi === 'BANK_MASUK';

        $lines = [];
        if ($isMasuk) {
            if ($transKasBank->accountBank) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKasBank->accountBank->kode_akun),
                    'nama_akun'  => $transKasBank->accountBank->nama_akun,
                    'keterangan' => $transKasBank->keterangan ?: 'Penerimaan Bank',
                    'debit'      => (float) $transKasBank->nominal,
                    'kredit'     => 0.0,
                ];
            }
            if ($transKasBank->accountLawan) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKasBank->accountLawan->kode_akun),
                    'nama_akun'  => $transKasBank->accountLawan->nama_akun,
                    'keterangan' => 'Akun Lawan Penerimaan Bank',
                    'debit'      => 0.0,
                    'kredit'     => (float) $transKasBank->nominal,
                ];
            }
        } else {
            if ($transKasBank->accountLawan) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKasBank->accountLawan->kode_akun),
                    'nama_akun'  => $transKasBank->accountLawan->nama_akun,
                    'keterangan' => $transKasBank->keterangan ?: 'Pengeluaran Bank',
                    'debit'      => (float) $transKasBank->nominal,
                    'kredit'     => 0.0,
                ];
            }
            if ($transKasBank->accountBank) {
                $lines[] = [
                    'kode_akun'  => trim((string) $transKasBank->accountBank->kode_akun),
                    'nama_akun'  => $transKasBank->accountBank->nama_akun,
                    'keterangan' => 'Rekening Bank Pembayar',
                    'debit'      => 0.0,
                    'kredit'     => (float) $transKasBank->nominal,
                ];
            }
        }

        $infoBank = trim(($transKasBank->bank ?? '') . ' ' . ($transKasBank->no_rekening ?? '') . ' ' . ($transKasBank->atas_nama ? 'a/n ' . $transKasBank->atas_nama : ''));

        return Pdf::loadView('pdf.voucher-finance', [
            'logoBase64'     => $this->getLogoBase64(),
            'judul'          => $isMasuk ? 'BUKTI BANK MASUK (BBM)' : 'BUKTI BANK KELUAR (BBK)',
            'subjudul'       => 'VOUCHER TRANSAKSI REKENING BANK',
            'no_bukti'       => $transKasBank->kode_transaksi,
            'tanggal'        => $transKasBank->tanggal_transaksi ? $transKasBank->tanggal_transaksi->format('d/m/Y') : now()->format('d/m/Y'),
            'periode'        => $transKasBank->periode ?? '-',
            'gudang'         => $transKasBank->gudang ?? 'TTC',
            'pihak_terkait'  => $transKasBank->customer?->nama_customer ?: ($infoBank ?: '-'),
            'label_pihak'    => $isMasuk ? 'Diterima Dari' : 'Ditujukan Kepada',
            'keterangan'     => $transKasBank->keterangan ?? '-',
            'nominal'        => (float) $transKasBank->nominal,
            'terbilang'      => $this->terbilang((float) $transKasBank->nominal),
            'lines'          => $lines,
            'pic_nama'       => auth()->user()?->name ?? 'Admin Finance',
        ])->setPaper('A4', 'portrait');
    }

    public function previewVoucherBank(TransKasBank $transKasBank)
    {
        return $this->buildBankPdf($transKasBank)->stream('Voucher_Bank_' . $transKasBank->kode_transaksi . '.pdf');
    }

    public function downloadVoucherBank(TransKasBank $transKasBank)
    {
        return $this->buildBankPdf($transKasBank)->download('Voucher_Bank_' . $transKasBank->kode_transaksi . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | 3. VOUCHER PENGELUARAN OPERASIONAL & COMMISSION REQUEST (DOC_Comm)
    |--------------------------------------------------------------------------
    */

    protected function getMacroCommissionCatalog(): array
    {
        return [
            'OR-26-IV-C002-002' => [
                'order_id'       => 'OR-26-IV-C002-002',
                'commission_no'  => 'COM-26-IV-C002-002',
                'order_date'     => '17 Apr 2026',
                'request_date'   => '1 Oct 2026',
                'customer'       => 'PT. CATUR WIRATAMA TOBACCO',
                'order_summary'  => 'SKT DAFI',
                'revenue'        => 15289345,
                'revenue_comm'   => 866450,
                'tax_comm'       => 0,
                'referral_comm'  => 866450,
                'rev_after_adj'  => 14422895,
                'sales_comm'     => 288458,
                'total_comm'     => 288458,
            ],
            'OR-26-VI-C002-003' => [
                'order_id'       => 'OR-26-VI-C002-003',
                'commission_no'  => 'COM-26-VI-C002-003',
                'order_date'     => '17 Jun 2026',
                'request_date'   => '1 Oct 2026',
                'customer'       => 'PT. CATUR WIRATAMA TOBACCO',
                'order_summary'  => 'AMBRI DAFI',
                'revenue'        => 5863000,
                'revenue_comm'   => 533000,
                'tax_comm'       => 0,
                'referral_comm'  => 533000,
                'rev_after_adj'  => 5330000,
                'sales_comm'     => 106600,
                'total_comm'     => 106600,
            ],
            'OR-26-IV-C005-003' => [
                'order_id'       => 'OR-26-IV-C005-003',
                'commission_no'  => 'COM-26-IV-C005-003',
                'order_date'     => '23 Apr 2026',
                'request_date'   => '1 Oct 2026',
                'customer'       => 'PR. JUMALI',
                'order_summary'  => 'SKT PDW 88',
                'revenue'        => 38635200,
                'revenue_comm'   => 0,
                'tax_comm'       => 0,
                'referral_comm'  => 0,
                'rev_after_adj'  => 38635200,
                'sales_comm'     => 772704,
                'total_comm'     => 772704,
            ],
            'OR-26-VI-C002-004' => [
                'order_id'       => 'OR-26-VI-C002-004',
                'commission_no'  => 'COM-26-VI-C002-004',
                'order_date'     => '27 Jun 2026',
                'request_date'   => '1 Oct 2026',
                'customer'       => 'PT. CATUR WIRATAMA TOBACCO',
                'order_summary'  => 'SKT DAFI 16',
                'revenue'        => 11192541,
                'revenue_comm'   => 589170,
                'tax_comm'       => 0,
                'referral_comm'  => 589170,
                'rev_after_adj'  => 10603371,
                'sales_comm'     => 212067,
                'total_comm'     => 212067,
            ],
            'OR-26-VI-C006-002' => [
                'order_id'       => 'OR-26-VI-C006-002',
                'commission_no'  => 'COM-26-VI-C006-002',
                'order_date'     => '25 Jun 2026',
                'request_date'   => '1 Oct 2026',
                'customer'       => 'P. ADI',
                'order_summary'  => 'BARBARA',
                'revenue'        => 13360000,
                'revenue_comm'   => 0,
                'tax_comm'       => 0,
                'referral_comm'  => 0,
                'rev_after_adj'  => 13360000,
                'sales_comm'     => 267200,
                'total_comm'     => 267200,
            ],
            'OR-26-VII-C005-004' => [
                'order_id'       => 'OR-26-VII-C005-004',
                'commission_no'  => 'COM-26-VII-C005-004',
                'order_date'     => '9 Jul 2026',
                'request_date'   => '1 Oct 2026',
                'customer'       => 'PR. JUMALI',
                'order_summary'  => 'ETIKET VULLARISO',
                'revenue'        => 37606860,
                'revenue_comm'   => 0,
                'tax_comm'       => 0,
                'referral_comm'  => 0,
                'rev_after_adj'  => 37606860,
                'sales_comm'     => 752137,
                'total_comm'     => 752137,
            ],
        ];
    }

    protected function buildCommissionRequestPdf(string $orderId, ?OperasionalPay $operasionalPay = null)
    {
        $catalog = $this->getMacroCommissionCatalog();
        $normalizedOrderId = str_starts_with($orderId, 'COM-')
            ? 'OR-' . substr($orderId, 4)
            : $orderId;

        if (isset($catalog[$normalizedOrderId])) {
            $data = $catalog[$normalizedOrderId];
        } else {
            $inv = Invoice::with('customer')->where('order_id', $normalizedOrderId)->first();
            $af = \App\Models\ApprovalForm::where('order_id', $normalizedOrderId)->first();
            $rev = (float) ($inv?->grand_total ?? 0);
            $salesComm = (float) ($operasionalPay?->nominal ?? round($rev * 0.02));
            if ($rev <= 0 && $salesComm > 0) {
                $rev = round($salesComm / 0.02);
            }
            $commNo = str_starts_with($normalizedOrderId, 'OR-')
                ? 'COM-' . substr($normalizedOrderId, 3)
                : ($operasionalPay?->kode_transaksi ?: 'COM-' . $normalizedOrderId);

            $data = [
                'order_id'       => $normalizedOrderId,
                'commission_no'  => $commNo,
                'order_date'     => $inv?->tanggal_invoice ? \Carbon\Carbon::parse($inv->tanggal_invoice)->format('j M Y') : ($operasionalPay?->tanggal_transaksi ? $operasionalPay->tanggal_transaksi->format('j M Y') : '1 Oct 2026'),
                'request_date'   => '1 Oct 2026',
                'customer'       => $inv?->customer?->nama_customer ?? ($af?->customer ?? '-'),
                'order_summary'  => $af?->catatan ?? ($operasionalPay?->keterangan ?? '-'),
                'revenue'        => $rev,
                'revenue_comm'   => 0,
                'tax_comm'       => 0,
                'referral_comm'  => 0,
                'rev_after_adj'  => $rev,
                'sales_comm'     => $salesComm,
                'total_comm'     => $salesComm,
            ];
        }

        $data['logoBase64'] = $this->getLogoBase64();

        return Pdf::loadView('pdf.commission-request', $data)->setPaper('A4', 'portrait');
    }

    public function previewCommissionRequest(string $orderId)
    {
        $commNo = str_starts_with($orderId, 'OR-') ? 'COM-' . substr($orderId, 3) : $orderId;
        return $this->buildCommissionRequestPdf($orderId)->stream('Commission_Request_' . $commNo . '.pdf');
    }

    public function downloadCommissionRequest(string $orderId)
    {
        $commNo = str_starts_with($orderId, 'OR-') ? 'COM-' . substr($orderId, 3) : $orderId;
        return $this->buildCommissionRequestPdf($orderId)->download('Commission_Request_' . $commNo . '.pdf');
    }

    protected function buildOperasionalPdf(OperasionalPay $operasionalPay)
    {
        if (
            str_contains(strtoupper((string) $operasionalPay->jenis), 'KOMISI')
            || str_starts_with(strtoupper((string) $operasionalPay->kode_transaksi), 'COM-')
            || str_starts_with(strtoupper((string) $operasionalPay->kode), 'OR-')
        ) {
            $orderKey = $operasionalPay->kode ?: $operasionalPay->kode_transaksi;
            return $this->buildCommissionRequestPdf((string) $orderKey, $operasionalPay);
        }

        $operasionalPay->loadMissing(['accountKas', 'accountBeban', 'karyawan']);

        $extraInfo = array_filter([
            $operasionalPay->mesin ? "Mesin: {$operasionalPay->mesin}" : null,
            $operasionalPay->nopol ? "Nopol: {$operasionalPay->nopol}" : null,
            $operasionalPay->odometer ? "KM: {$operasionalPay->odometer}" : null,
            $operasionalPay->jenis ? "Jenis: {$operasionalPay->jenis}" : null,
        ]);
        $ketLengkap = trim(($operasionalPay->keterangan ?? '') . (! empty($extraInfo) ? ' [' . implode(' | ', $extraInfo) . ']' : ''));

        $lines = [];
        if ($operasionalPay->accountBeban) {
            $lines[] = [
                'kode_akun'  => trim((string) $operasionalPay->accountBeban->kode_akun),
                'nama_akun'  => $operasionalPay->accountBeban->nama_akun,
                'keterangan' => $ketLengkap ?: 'Beban Operasional',
                'debit'      => (float) $operasionalPay->nominal,
                'kredit'     => 0.0,
            ];
        }
        if ($operasionalPay->accountKas) {
            $lines[] = [
                'kode_akun'  => trim((string) $operasionalPay->accountKas->kode_akun),
                'nama_akun'  => $operasionalPay->accountKas->nama_akun,
                'keterangan' => 'Kas/Bank Pembayar Operasional',
                'debit'      => 0.0,
                'kredit'     => (float) $operasionalPay->nominal,
            ];
        }

        return Pdf::loadView('pdf.voucher-finance', [
            'logoBase64'     => $this->getLogoBase64(),
            'judul'          => 'BUKTI PENGELUARAN OPERASIONAL',
            'subjudul'       => 'VOUCHER BIAYA OPERASIONAL PABRIK & KANTOR',
            'no_bukti'       => $operasionalPay->kode_transaksi,
            'tanggal'        => $operasionalPay->tanggal_transaksi ? $operasionalPay->tanggal_transaksi->format('d/m/Y') : now()->format('d/m/Y'),
            'periode'        => $operasionalPay->periode ?? '-',
            'gudang'         => $operasionalPay->gudang ?? 'TTC',
            'pihak_terkait'  => $operasionalPay->karyawan?->nama ?? '-',
            'label_pihak'    => 'PIC / Pemohon',
            'keterangan'     => $ketLengkap ?: '-',
            'nominal'        => (float) $operasionalPay->nominal,
            'terbilang'      => $this->terbilang((float) $operasionalPay->nominal),
            'lines'          => $lines,
            'pic_nama'       => $operasionalPay->karyawan?->nama ?? (auth()->user()?->name ?? 'Admin Finance'),
        ])->setPaper('A4', 'portrait');
    }

    public function previewVoucherOperasional(OperasionalPay $operasionalPay)
    {
        return $this->buildOperasionalPdf($operasionalPay)->stream('Voucher_Operasional_' . $operasionalPay->kode_transaksi . '.pdf');
    }

    public function downloadVoucherOperasional(OperasionalPay $operasionalPay)
    {
        return $this->buildOperasionalPdf($operasionalPay)->download('Voucher_Operasional_' . $operasionalPay->kode_transaksi . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | 4. BUKTI JURNAL MEMORIAL / PENYESUAIAN
    |--------------------------------------------------------------------------
    */

    protected function buildMemorialPdf(JournalEntry $journalEntry)
    {
        $journalEntry->loadMissing(['lines.coa', 'karyawan', 'creator']);

        $lines = $journalEntry->lines->map(fn ($line) => [
            'kode_akun'  => trim((string) ($line->coa?->kode_akun ?? '-')),
            'nama_akun'  => $line->coa?->nama_akun ?? '-',
            'keterangan' => $line->keterangan ?: ($journalEntry->keterangan ?? '-'),
            'debit'      => (float) $line->debit,
            'kredit'     => (float) $line->kredit,
        ])->toArray();

        return Pdf::loadView('pdf.voucher-finance', [
            'logoBase64'     => $this->getLogoBase64(),
            'judul'          => 'BUKTI JURNAL MEMORIAL (' . strtoupper($journalEntry->jenis_jurnal) . ')',
            'subjudul'       => 'DOKUMEN PEMBUKUAN DOUBLE-ENTRY (GENERAL JOURNAL VOUCHER)',
            'no_bukti'       => $journalEntry->kode_jurnal,
            'tanggal'        => $journalEntry->tanggal ? $journalEntry->tanggal->format('d/m/Y') : now()->format('d/m/Y'),
            'periode'        => $journalEntry->periode ?? '-',
            'gudang'         => $journalEntry->gudang ?? 'TTC',
            'pihak_terkait'  => $journalEntry->no_referensi ?: ($journalEntry->karyawan?->nama ?? '-'),
            'label_pihak'    => 'No. Referensi / PIC',
            'keterangan'     => $journalEntry->keterangan ?? '-',
            'nominal'        => (float) $journalEntry->total_debit,
            'terbilang'      => $this->terbilang((float) $journalEntry->total_debit),
            'lines'          => $lines,
            'pic_nama'       => $journalEntry->karyawan?->nama ?? ($journalEntry->creator?->name ?? 'Accounting'),
        ])->setPaper('A4', 'portrait');
    }

    public function previewVoucherMemorial(JournalEntry $journalEntry)
    {
        return $this->buildMemorialPdf($journalEntry)->stream('Bukti_Memorial_' . $journalEntry->kode_jurnal . '.pdf');
    }

    public function downloadVoucherMemorial(JournalEntry $journalEntry)
    {
        return $this->buildMemorialPdf($journalEntry)->download('Bukti_Memorial_' . $journalEntry->kode_jurnal . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | 5. LAPORAN LABA RUGI, LABA RUGI MESIN & NERACA (PDF)
    |--------------------------------------------------------------------------
    */

    public function previewLabaRugi(Request $request, FinancialReportService $service)
    {
        $data = $service->getIncomeStatement($request->query('start_date'), $request->query('end_date'));
        $pdf = Pdf::loadView('pdf.laporan-laba-rugi', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Laba_Rugi_' . $data['start_date'] . '_' . $data['end_date'] . '.pdf');
    }

    public function downloadLabaRugi(Request $request, FinancialReportService $service)
    {
        $data = $service->getIncomeStatement($request->query('start_date'), $request->query('end_date'));
        $pdf = Pdf::loadView('pdf.laporan-laba-rugi', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Laba_Rugi_' . $data['start_date'] . '_' . $data['end_date'] . '.pdf');
    }

    protected function filterMachineData(array $data, ?string $mesin): array
    {
        if (! $mesin) {
            return $data;
        }

        $filtered = array_values(array_filter(
            $data['machines'],
            fn (array $m) => strcasecmp((string) ($m['nama_mesin'] ?? ''), $mesin) === 0
                || strcasecmp((string) ($m['kode_mesin'] ?? ''), $mesin) === 0
        ));

        $grandPendapatan = array_sum(array_column($filtered, 'pendapatan'));
        $grandBahan      = array_sum(array_column($filtered, 'biaya_bahan'));
        $grandOp         = array_sum(array_column($filtered, 'biaya_operasional'));
        $grandTotalBiaya = $grandBahan + $grandOp;
        $grandLaba       = $grandPendapatan - $grandTotalBiaya;

        $data['machines']                = $filtered;
        $data['grand_spk_count']         = array_sum(array_column($filtered, 'jumlah_spk'));
        $data['grand_hasil_baik']        = array_sum(array_column($filtered, 'hasil_baik'));
        $data['grand_hasil_rusak']       = array_sum(array_column($filtered, 'hasil_rusak'));
        $data['grand_pendapatan']        = $grandPendapatan;
        $data['grand_biaya_bahan']       = $grandBahan;
        $data['grand_biaya_operasional'] = $grandOp;
        $data['grand_total_biaya']       = $grandTotalBiaya;
        $data['grand_laba']              = $grandLaba;
        $data['grand_margin_persen']     = $grandPendapatan > 0
            ? round(($grandLaba / $grandPendapatan) * 100, 2)
            : 0.0;

        return $data;
    }

    public function previewLabaRugiMesin(Request $request, FinancialReportService $service)
    {
        $data = $this->filterMachineData(
            $service->getMachineProfitabilityReport($request->query('start_date'), $request->query('end_date')),
            $request->query('mesin')
        );
        $pdf = Pdf::loadView('pdf.laporan-laba-rugi-mesin', [
            'logoBase64'    => $this->getLogoBase64(),
            'data'          => $data,
            'selectedMesin' => $request->query('mesin'),
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('Laporan_Laba_Rugi_Mesin_' . $data['start_date'] . '_' . $data['end_date'] . '.pdf');
    }

    public function downloadLabaRugiMesin(Request $request, FinancialReportService $service)
    {
        $data = $this->filterMachineData(
            $service->getMachineProfitabilityReport($request->query('start_date'), $request->query('end_date')),
            $request->query('mesin')
        );
        $pdf = Pdf::loadView('pdf.laporan-laba-rugi-mesin', [
            'logoBase64'    => $this->getLogoBase64(),
            'data'          => $data,
            'selectedMesin' => $request->query('mesin'),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('Laporan_Laba_Rugi_Mesin_' . $data['start_date'] . '_' . $data['end_date'] . '.pdf');
    }

    public function previewNeraca(Request $request, FinancialReportService $service)
    {
        $data = $service->getBalanceSheet($request->query('as_of_date'));
        $pdf = Pdf::loadView('pdf.laporan-neraca', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Neraca_' . $data['as_of_date'] . '.pdf');
    }

    public function downloadNeraca(Request $request, FinancialReportService $service)
    {
        $data = $service->getBalanceSheet($request->query('as_of_date'));
        $pdf = Pdf::loadView('pdf.laporan-neraca', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Neraca_' . $data['as_of_date'] . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAL LEDGER (BUKU BESAR & JURNAL UMUM)
    |--------------------------------------------------------------------------
    */

    public function previewGeneralLedger(Request $request, FinancialReportService $service)
    {
        $data = $service->getGeneralLedgerReport(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('coa_id'),
            $request->query('ref_type')
        );

        $pdf = Pdf::loadView('pdf.general-ledger', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('General_Ledger_' . $data['start_date'] . '_' . $data['end_date'] . '.pdf');
    }

    public function downloadGeneralLedger(Request $request, FinancialReportService $service)
    {
        $data = $service->getGeneralLedgerReport(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('coa_id'),
            $request->query('ref_type')
        );

        $pdf = Pdf::loadView('pdf.general-ledger', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('General_Ledger_' . $data['start_date'] . '_' . $data['end_date'] . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | TRIAL BALANCE (NERACA SALDO)
    |--------------------------------------------------------------------------
    */

    public function previewTrialBalance(Request $request, FinancialReportService $service)
    {
        $group = $request->query('group');
        $onlyActive = (bool) $request->query('only_active', true);
        $data = $service->getTrialBalanceReport($group, $onlyActive);

        $pdf = Pdf::loadView('pdf.trial-balance', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'landscape');

        return $pdf->stream('Trial_Balance_' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadTrialBalance(Request $request, FinancialReportService $service)
    {
        $group = $request->query('group');
        $onlyActive = (bool) $request->query('only_active', true);
        $data = $service->getTrialBalanceReport($group, $onlyActive);

        $pdf = Pdf::loadView('pdf.trial-balance', [
            'logoBase64' => $this->getLogoBase64(),
            'data'       => $data,
        ])->setPaper('A4', 'landscape');

        return $pdf->download('Trial_Balance_' . now()->format('Y-m-d') . '.pdf');
    }
}


