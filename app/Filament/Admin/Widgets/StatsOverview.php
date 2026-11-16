<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use App\Models\MasterCOA;
use App\Models\PoBilling;
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
                ->color('success'),

            Stat::make('Penjualan Bulan Ini' . ($penjualan['label_extra'] ? ' ' . $penjualan['label_extra'] : ''), 'Rp ' . number_format($penjualan['nominal'], 0, ',', '.'))
                ->description($penjualan['count'] . ' faktur diterbitkan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Piutang Usaha (AR)', 'Rp ' . number_format($piutang['nominal'], 0, ',', '.'))
                ->description($piutang['overdue_count'] > 0
                    ? $piutang['overdue_count'] . ' tagihan jatuh tempo!'
                    : $piutang['unpaid_count'] . ' tagihan belum lunas')
                ->descriptionIcon('heroicon-m-clock')
                ->color($piutang['overdue_count'] > 0 ? 'danger' : 'warning'),

            Stat::make('Hutang Usaha (AP)', 'Rp ' . number_format($hutang['nominal'], 0, ',', '.'))
                ->description($hutang['count'] . ' tagihan vendor aktif')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('danger'),

            Stat::make('Pesanan Aktif (SO)', number_format($pesanan['count'], 0, ',', '.'))
                ->description('Dari ' . number_format($pesanan['total'], 0, ',', '.') . ' total pesanan')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info'),
        ];
    }

    private function calculateKasBank(): float
    {
        try {
            if (Schema::hasTable('master_coas')) {
                $coas = MasterCOA::query()
                    ->where(function ($q) {
                        $q->where('kode_akun', 'like', '111%')
                            ->orWhere('kode_akun', 'like', '110%')
                            ->orWhere('nama_akun', 'like', '%Kas%')
                            ->orWhere('nama_akun', 'like', '%Bank%')
                            ->orWhere('account_type', 'like', '%Cash%')
                            ->orWhere('account_class', 'like', '%Bank%');
                    })
                    ->where('status', 'aktif')
                    ->get();

                $total = 0;
                foreach ($coas as $coa) {
                    $total += $coa->saldo_berjalan;
                }

                if ($total > 0) {
                    return (float) $total;
                }
            }

            $totalKas = 0;
            if (Schema::hasTable('trans_kas')) {
                $kasMasuk = TransKas::where('tipe_transaksi', 'KAS_MASUK')->sum('nominal');
                $kasKeluar = TransKas::where('tipe_transaksi', 'KAS_KELUAR')->sum('nominal');
                $totalKas += ($kasMasuk - $kasKeluar);
            }

            if (Schema::hasTable('trans_kas_banks')) {
                $bankMasuk = TransKasBank::where('tipe_transaksi', 'BANK_MASUK')->sum('nominal');
                $bankKeluar = TransKasBank::where('tipe_transaksi', 'BANK_KELUAR')->sum('nominal');
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
            $query = Invoice::query()
                ->whereMonth('tanggal_invoice', $now->month)
                ->whereYear('tanggal_invoice', $now->year);

            $nominal = (float) $query->sum('grand_total');
            $count = $query->count();

            if ($count === 0) {
                $allQuery = Invoice::query();
                $nominal = (float) $allQuery->sum('grand_total');
                $count = $allQuery->count();
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

            $unpaid = Invoice::query()
                ->where('status', '!=', 'PAID')
                ->where(function ($q) {
                    $q->where('sisa_tagihan', '>', 0)
                        ->orWhereNull('sisa_tagihan');
                });

            $nominal = (float) $unpaid->sum(DB::raw('COALESCE(sisa_tagihan, grand_total, 0)'));
            $unpaidCount = $unpaid->count();

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

            $billings = PoBilling::with('payments')->get();
            $totalHutang = 0;
            $unpaidCount = 0;

            foreach ($billings as $bill) {
                $sisa = $bill->sisa_tagihan;
                if ($sisa > 0) {
                    $totalHutang += $sisa;
                    $unpaidCount++;
                }
            }

            return [
                'nominal' => $totalHutang,
                'count' => $unpaidCount,
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

            $activeQuery = SalesOrder::query();
            if (Schema::hasTable('surat_jalans')) {
                $activeQuery->whereRaw("((SELECT COALESCE(SUM(qty_pengiriman), 0) FROM surat_jalans WHERE surat_jalans.sales_order_id = sales_orders.id) < COALESCE(sales_orders.qty, 0))");
            }

            $active = $activeQuery->count();

            return [
                'count' => $active > 0 ? $active : $total,
                'total' => $total,
            ];
        } catch (\Throwable $e) {
            return ['count' => 0, 'total' => 0];
        }
    }
}
