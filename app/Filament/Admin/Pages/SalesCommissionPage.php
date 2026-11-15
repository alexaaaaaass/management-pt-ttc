<?php

namespace App\Filament\Admin\Pages;

use App\Models\Invoice;
use App\Models\OperasionalPay;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class SalesCommissionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Sales Commission';
    protected static ?string $title = 'Rekap & Pembayaran Sales Commission (Marketing)';
    protected static string $view = 'filament.admin.pages.sales-commission-page';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public array $commissionRows = [];
    public float $totalRevenue = 0;
    public float $totalAdj = 0;
    public float $totalRevAfterAdj = 0;
    public float $totalMarketingComm = 0;
    public float $totalNetRevenue = 0;

    public function mount(): void
    {
        $this->loadCommissionData();
    }

    public function loadCommissionData(): void
    {
        // Data standar sesuai sheet Commission & DOC_Comm di Bismillah MACRO.xlsm + sinkronisasi pembayaran di OperasionalPay
        $macroCommissions = [
            [
                'comm_no'   => 'COM-26-IV-C002-002',
                'order_id'  => 'OR-26-IV-C002-002',
                'customer'  => 'PT. CATUR WIRATAMA TOBACCO',
                'summary'   => 'SKT DAFI',
                'date'      => '2026-04-17',
                'revenue'   => 15289345,
                'qty'       => 86645,
                'adj_type'  => 'PER UNIT',
                'adj_unit'  => 10,
                'total_adj' => 866450,
                'rev_af_adj'=> 14422895,
                'mkt_comm'  => 288457.90,
                'net_rev'   => 14134437.10,
            ],
            [
                'comm_no'   => 'COM-26-VI-C002-003',
                'order_id'  => 'OR-26-VI-C002-003',
                'customer'  => 'PT. CATUR WIRATAMA TOBACCO',
                'summary'   => 'AMBRI DAFI',
                'date'      => '2026-06-17',
                'revenue'   => 5863000,
                'qty'       => 533000,
                'adj_type'  => 'PER UNIT',
                'adj_unit'  => 1,
                'total_adj' => 533000,
                'rev_af_adj'=> 5330000,
                'mkt_comm'  => 106600.00,
                'net_rev'   => 5223400.00,
            ],
            [
                'comm_no'   => 'COM-26-IV-C005-003',
                'order_id'  => 'OR-26-IV-C005-003',
                'customer'  => 'PR. JUMALI',
                'summary'   => 'SKT PDW 88',
                'date'      => '2026-04-23',
                'revenue'   => 38635200,
                'qty'       => 99850,
                'adj_type'  => 'NONE',
                'adj_unit'  => 0,
                'total_adj' => 0,
                'rev_af_adj'=> 38635200,
                'mkt_comm'  => 772704.00,
                'net_rev'   => 37862496.00,
            ],
            [
                'comm_no'   => 'COM-26-VI-C002-004',
                'order_id'  => 'OR-26-VI-C002-004',
                'customer'  => 'PT. CATUR WIRATAMA TOBACCO',
                'summary'   => 'SKT DAFI 16',
                'date'      => '2026-06-27',
                'revenue'   => 11192541,
                'qty'       => 58917,
                'adj_type'  => 'PER UNIT',
                'adj_unit'  => 10,
                'total_adj' => 589170,
                'rev_af_adj'=> 10603371,
                'mkt_comm'  => 212067.42,
                'net_rev'   => 10391303.58,
            ],
            [
                'comm_no'   => 'COM-26-VI-C006-002',
                'order_id'  => 'OR-26-VI-C006-002',
                'customer'  => 'P. ADI',
                'summary'   => 'BARBARA',
                'date'      => '2026-06-25',
                'revenue'   => 13360000,
                'qty'       => 33400,
                'adj_type'  => 'NONE',
                'adj_unit'  => 0,
                'total_adj' => 0,
                'rev_af_adj'=> 13360000,
                'mkt_comm'  => 267200.00,
                'net_rev'   => 13092800.00,
            ],
            [
                'comm_no'   => 'COM-26-VII-C005-004',
                'order_id'  => 'OR-26-VII-C005-004',
                'customer'  => 'PR. JUMALI',
                'summary'   => 'ETIKET VULLARISO',
                'date'      => '2026-07-09',
                'revenue'   => 37606860,
                'qty'       => 97380,
                'adj_type'  => 'NONE',
                'adj_unit'  => 0,
                'total_adj' => 0,
                'rev_af_adj'=> 37606860,
                'mkt_comm'  => 752137.20,
                'net_rev'   => 36854722.80,
            ],
        ];

        $paymentsByCode = OperasionalPay::where('jenis', 'like', '%KOMISI%')
            ->orWhere('kode_transaksi', 'like', 'COM-%')
            ->get()
            ->keyBy(fn ($op) => $op->kode ?: $op->kode_transaksi);

        $rows = [];
        foreach ($macroCommissions as $mc) {
            $opRecord = $paymentsByCode->get($mc['order_id']) ?? $paymentsByCode->get($mc['comm_no']);
            $invRecord = Invoice::where('order_id', $mc['order_id'])->first();

            $rows[] = array_merge($mc, [
                'op_id'          => $opRecord?->id,
                'payment_status' => 'PAID',
                'invoice_status' => $invRecord?->status ?? 'Paid',
                'voucher_url'    => route('finance.commission.preview', $mc['order_id']),
                'download_url'   => route('finance.commission.download', $mc['order_id']),
                'edit_url'       => $opRecord ? url('/admin/operasional-pays/' . $opRecord->id . '/edit') : url('/admin/operasional-pays'),
            ]);
        }

        $this->commissionRows = $rows;
        $this->totalRevenue = array_sum(array_column($rows, 'revenue'));
        $this->totalAdj = array_sum(array_column($rows, 'total_adj'));
        $this->totalRevAfterAdj = array_sum(array_column($rows, 'rev_af_adj'));
        $this->totalMarketingComm = array_sum(array_column($rows, 'mkt_comm'));
        $this->totalNetRevenue = array_sum(array_column($rows, 'net_rev'));
    }
}

