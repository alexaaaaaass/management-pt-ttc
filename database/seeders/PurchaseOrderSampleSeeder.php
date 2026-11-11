<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterSupplier;
use App\Models\MasterDepartemen;
use App\Models\MasterItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;

class PurchaseOrderSampleSeeder extends Seeder
{
    public function run(): void
    {
        $sup2 = MasterSupplier::firstOrCreate(
            ['kode_supplier' => 'SUP-002'],
            [
                'nama_supplier'  => 'PT. Siegwerk Indonesia',
                'jenis_supplier' => 'Tinta & Kimia',
                'alamat'         => 'Kawasan Industri Jatake, Tangerang',
                'keterangan'     => 'Supplier tinta flexo UV dan vernis',
            ]
        );

        $sup3 = MasterSupplier::firstOrCreate(
            ['kode_supplier' => 'SUP-003'],
            [
                'nama_supplier'  => 'CV. Multi Grafika Mandiri',
                'jenis_supplier' => 'Subkontrak Pisau Pond',
                'alamat'         => 'Jl. Daan Mogot Km. 19, Jakarta Barat',
                'keterangan'     => 'Jasa hot stamping & pisau pond',
            ]
        );

        $dept = MasterDepartemen::first();
        $items = MasterItem::all();
        $item2 = $items[1] ?? $items[0];
        $item3 = $items[2] ?? $items[0];

        $pr2 = PurchaseRequest::firstOrCreate(
            ['nomor_pr' => 'PR/TTC/2026/11/002'],
            [
                'departemen_id' => $dept->id,
                'tanggal_pr'    => Carbon::now()->subDays(3)->toDateString(),
                'status'        => 'otorisasi',
            ]
        );

        PurchaseRequestItem::firstOrCreate(
            ['purchase_request_id' => $pr2->id, 'item_id' => $item2->id],
            [
                'qty'     => 30,
                'satuan'  => 'TUB',
                'eta'     => Carbon::now()->addDays(7)->toDateString(),
                'catatan' => 'Restock tinta flexo UV Cyan',
            ]
        );

        $po2 = PurchaseOrder::firstOrCreate(
            ['no_po' => 'PO-UGRMS-20261110-002'],
            [
                'purchase_request_id' => $pr2->id,
                'supplier_id'         => $sup2->id,
                'po_date'             => Carbon::now()->subDays(1)->toDateString(),
                'eta'                 => Carbon::now()->addDays(6)->toDateString(),
                'currency'            => 'IDR',
                'ppn'                 => 11.00,
                'ongkir'              => 0.00,
                'dp'                  => 500000.00,
                'po_type'             => 'reguler',
                'status'              => 'Partial',
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po2->id, 'item_id' => $item2->id],
            [
                'qty_pr'       => 30,
                'qty_po'       => 30,
                'qty_konversi' => 1,
                'price'        => 175000.00,
                'discount'     => 5.00,
                'total'        => 4987500.00,
                'satuan'       => 'TUB',
                'eta'          => Carbon::now()->addDays(6)->toDateString(),
                'catatan'      => 'Tinta Flexo UV Cyan',
                'qty_received' => 15,
            ]
        );

        $po3 = PurchaseOrder::firstOrCreate(
            ['no_po' => 'PO-UGRMS-20261111-003'],
            [
                'purchase_request_id' => $pr2->id,
                'supplier_id'         => $sup3->id,
                'po_date'             => Carbon::now()->toDateString(),
                'eta'                 => Carbon::now()->addDays(3)->toDateString(),
                'currency'            => 'IDR',
                'ppn'                 => 0.00,
                'ongkir'              => 50000.00,
                'dp'                  => 0.00,
                'po_type'             => 'subcount',
                'status'              => 'Completed',
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po3->id, 'item_id' => $item3->id],
            [
                'qty_pr'       => 5000,
                'qty_po'       => 5000,
                'qty_konversi' => 1,
                'price'        => 350.00,
                'discount'     => 0.00,
                'total'        => 1750000.00,
                'satuan'       => 'PCS',
                'eta'          => Carbon::now()->addDays(3)->toDateString(),
                'catatan'      => 'Jasa Subcount Hot Stamping Foil Emas',
                'qty_received' => 5000,
            ]
        );
    }
}
