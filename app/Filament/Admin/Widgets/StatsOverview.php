<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use App\Models\MasterCOA;
use App\Models\PoBilling;
use App\Models\PoBillPay;
use App\Models\SalesOrder;
use App\Models\TransKas;
use App\Models\TransKasBank;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true;

    protected int | string | array $columns = [
        'default' => 1,
        'sm' => 2,
        'md' => 3,
        'xl' => 5,
    ];

    protected function getStats(): array
    {
        $kasBank = $this->calculateKasBank();
        $penjualan = $this->calculatePenjualanBulanIni();
        $piutang = $this->calculatePiutang();
        $hutang = $this->calculateHutang();
        $pesanan = $this->calculatePesananAktif();

        return [
            Stat::make('Kas & Bank', 'Rp ' . number_format($kasBank, 0, ',', '.'))
                ->description('Likuiditas kas & rekening')
                ->descriptionIcon('heroicon-m-wallet')
                ->chart([30, 42, 45, 55, 62, 70, 85])
                ->color('success'),

            Stat::make('Penjualan Bulan Ini' . ($penjualan['label_extra'] ? ' ' . $penjualan['label_extra'] : ''), 'Rp ' . number_format($penjualan['nominal'], 0, ',', '.'))
                ->description($penjualan['count'] . ' faktur diterbitkan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([15, 25, 38, 48, 62, 75, 90])
                ->color('primary'),

            Stat::make('Piutang Usaha (AR)', 'Rp ' . number_format($piutang['nominal'], 0, ',', '.'))
                ->description($piutang['overdue_count'] > 0
                    ? $piutang['overdue_count'] . ' tagihan jatuh tempo!'
                    : $piutang['unpaid_count'] . ' tagihan belum lunas')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([80, 75, 68, 60, 52, 45, 38])
                ->color($piutang['overdue_count'] > 0 ? 'danger' : 'warning'),

            Stat::make('Hutang Usaha (AP)', 'Rp ' . number_format($hutang['nominal'], 0, ',', '.'))
                ->description($hutang['count'] . ' tagihan vendor aktif')
                ->descriptionIcon('heroicon-m-credit-card')
                ->chart([60, 55, 50, 42, 38, 35, 28])
                ->color('danger'),

            Stat::make('Pesanan Aktif (SO)', number_format($pesanan['count'], 0, ',', '.'))
                ->description('Dari ' . number_format($pesanan['total'], 0, ',', '.') . ' total pesanan')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->chart([20, 28, 35, 42, 50, 58, 70])
                ->color('info'),
        ];
    }

    private function calculateKasBank(): float
    {
        try {
            if (Schema::hasTable('master_coas')) {
                $total = (float) MasterCOA::query()
                    ->where(function ($q) {
                        $q->where('kode_akun', 'like', '111%')
                            ->orWhere('kode_akun', 'like', '110%')
                            ->orWhere('nama_akun', 'like', '%Kas%')
                            ->orWhere('nama_akun', 'like', '%Bank%')
                            ->orWhere('account_type', 'like', '%Cash%')
                            ->orWhere('account_class', 'like', '%Bank%');
                    })
                    ->where('status', 'aktif')
                    ->sum(DB::raw('COALESCE(saldo_debit, 0) - COALESCE(saldo_kredit, 0)'));

                if ($total > 0) {
                    return (float) $total;
                }
            }

            $totalKas = 0;
            if (Schema::hasTable('trans_kas')) {
                $kasMasuk = (float) TransKas::where('tipe_transaksi', 'KAS_MASUK')->sum('nominal');
                $kasKeluar = (float) TransKas::where('tipe_transaksi', 'KAS_KELUAR')->sum('nominal');
                $totalKas += ($kasMasuk - $kasKeluar);
            }

            if (Schema::hasTable('trans_kas_banks')) {
                $bankMasuk = (float) TransKasBank::where('tipe_transaksi', 'BANK_MASUK')->sum('nominal');
                $bankKeluar = (float) TransKasBank::where('tipe_transaksi', 'BANK_KELUAR')->sum('nominal');
                $totalKas += ($bankMasuk - $bankKeluar);
            }

            return (float) max(0, $totalKas);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function calculatePenjualanBulanIni(): array
    {
        try {
            if (! Schema::hasTable('invoices')) {
                return ['nominal' => 0, 'count' => 0, 'label_extra' => ''];
            }

            $now = now();
            $nominal = (float) Invoice::query()
                ->whereMonth('tanggal_invoice', $now->month)
                ->whereYear('tanggal_invoice', $now->year)
                ->sum('grand_total');

            $count = Invoice::query()
                ->whereMonth('tanggal_invoice', $now->month)
                ->whereYear('tanggal_invoice', $now->year)
                ->count();

            if ($count === 0) {
                $nominal = (float) Invoice::sum('grand_total');
                $count = Invoice::count();
                return [
                    'nominal' => $nominal,
                    'count' => $count,
                    'label_extra' => '(Akumulasi)',
                ];
            }

            return [
                'nominal' => $nominal,
                'count' => $count,
                'label_extra' => '',
            ];
        } catch (\Throwable $e) {
            return ['nominal' => 0, 'count' => 0, 'label_extra' => ''];
        }
    }

    private function calculatePiutang(): array
    {
        try {
            if (! Schema::hasTable('invoices')) {
                return ['nominal' => 0, 'unpaid_count' => 0, 'overdue_count' => 0];
            }

            $nominal = (float) Invoice::query()
                ->where('status', '!=', 'PAID')
                ->where(function ($q) {
                    $q->where('sisa_tagihan', '>', 0)
                        ->orWhereNull('sisa_tagihan');
                })
                ->sum(DB::raw('COALESCE(sisa_tagihan, grand_total, 0)'));

            $unpaidCount = Invoice::query()
                ->where('status', '!=', 'PAID')
                ->where(function ($q) {
                    $q->where('sisa_tagihan', '>', 0)
                        ->orWhereNull('sisa_tagihan');
                })
                ->count();

            $overdueCount = Invoice::query()
                ->where('status', '!=', 'PAID')
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '<', now()->toDateString())
                ->count();

            return [
                'nominal' => $nominal,
                'unpaid_count' => $unpaidCount,
                'overdue_count' => $overdueCount,
            ];
        } catch (\Throwable $e) {
            return ['nominal' => 0, 'unpaid_count' => 0, 'overdue_count' => 0];
        }
    }

    private function calculateHutang(): array
    {
        try {
            if (! Schema::hasTable('po_billings')) {
                return ['nominal' => 0, 'count' => 0];
            }

            $totalBilling = (float) PoBilling::sum('grand_total');
            $totalPaid = Schema::hasTable('po_bill_pays')
                ? (float) PoBillPay::sum('total_pembayaran')
                : 0.0;

            $totalHutang = max(0, $totalBilling - $totalPaid);
            $count = PoBilling::count();

            return [
                'nominal' => $totalHutang,
                'count' => $count,
            ];
        } catch (\Throwable $e) {
            return ['nominal' => 0, 'count' => 0];
        }
    }

    private function calculatePesananAktif(): array
    {
        try {
            if (! Schema::hasTable('sales_orders')) {
                return ['count' => 0, 'total' => 0];
            }

            $total = SalesOrder::count();
            $active = SalesOrder::whereNull('deleted_at')->count();

            return [
                'count' => $active,
                'total' => $total,
            ];
        } catch (\Throwable $e) {
            return ['count' => 0, 'total' => 0];
        }
    }
}
