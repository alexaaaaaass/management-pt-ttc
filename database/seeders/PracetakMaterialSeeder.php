<?php

namespace Database\Seeders;

use App\Models\MasterMaterialPlate;
use App\Models\MasterSatuan;
use App\Models\MasterSupplier;
use App\Models\MesinPraCetak;
use App\Models\OperatorPraCetak;
use App\Models\PracetakPenerimaan;
use App\Models\User;
use App\Services\Pracetak\PracetakStockService;
use Illuminate\Database\Seeder;

class PracetakMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->orderBy('id')->value('id');

        $satuanSheet = MasterSatuan::where('nama_satuan', 'like', '%Sheet%')
            ->orWhere('nama_satuan', 'like', '%Lembar%')
            ->first() ?? MasterSatuan::first();

        $supplier = MasterSupplier::query()->orderBy('id')->first();

        // 1. Master Mesin & Operator Pra Cetak (idempotent)
        MesinPraCetak::firstOrCreate(
            ['nama_mesin' => 'Esko CDI Spark Flexo CTP'],
            [
                'jenis_mesin'     => 'Digital Flexo Imager',
                'kapasitas'       => '20 Plate / Hari',
                'proses'          => 'ctp',
                'tarif_per_plate' => 200000,
                'status'          => 'aktif',
            ]
        );
        MesinPraCetak::firstOrCreate(
            ['nama_mesin' => 'Kodak Trendsetter Thermal CTP'],
            [
                'jenis_mesin'     => 'Thermal CTP Offset / UV',
                'kapasitas'       => '40 Plate / Hari',
                'proses'          => 'plate_making',
                'tarif_per_plate' => 120000,
                'status'          => 'aktif',
            ]
        );

        OperatorPraCetak::firstOrCreate(['nama_operator' => 'Alan']);
        OperatorPraCetak::firstOrCreate(['nama_operator' => 'Rizal']);

        // 2. Katalog Master Material Plate Mentah (5 spesifikasi umum Flexo & CTP)
        $catalog = [
            [
                'kode_material'    => 'MPL-0001',
                'nama_material'    => 'Polymer Flexo Digital 1.14 mm (Etiket / Label)',
                'brand'            => 'DuPont Cyrel',
                'tipe_plate'       => 'Photopolymer Digital Flexo',
                'ketebalan_mm'     => 1.140,
                'panjang_sheet_mm' => 900.00,
                'lebar_sheet_mm'   => 1200.00,
                'harga_beli'       => 1450000.00,
                'catatan'          => 'Standar cetak etiket kemasan rokok & label flexo Zonten.',
            ],
            [
                'kode_material'    => 'MPL-0002',
                'nama_material'    => 'Polymer Flexo Digital 1.70 mm (Ivory / Karton)',
                'brand'            => 'Flint Group nyloflex',
                'tipe_plate'       => 'Photopolymer Digital Flexo',
                'ketebalan_mm'     => 1.700,
                'panjang_sheet_mm' => 900.00,
                'lebar_sheet_mm'   => 1200.00,
                'harga_beli'       => 1750000.00,
                'catatan'          => 'Cocok untuk kertas tebal, ivory 230–310 gsm, dan metalised.',
            ],
            [
                'kode_material'    => 'MPL-0003',
                'nama_material'    => 'Polymer Flexo Water-Wash 1.14 mm (Fine Screen)',
                'brand'            => 'Toyobo Cosmolight',
                'tipe_plate'       => 'Water-Washable Photopolymer',
                'ketebalan_mm'     => 1.140,
                'panjang_sheet_mm' => 762.00,
                'lebar_sheet_mm'   => 1016.00,
                'harga_beli'       => 1280000.00,
                'catatan'          => 'Cuci air ramah lingkungan untuk detail raster halus.',
            ],
            [
                'kode_material'    => 'MPL-0004',
                'nama_material'    => 'Plate Thermal CTP Aluminium 0.30 mm (Spot UV / Offset)',
                'brand'            => 'Kodak Sonora XTRA',
                'tipe_plate'       => 'Thermal CTP Aluminium',
                'ketebalan_mm'     => 0.300,
                'panjang_sheet_mm' => 790.00,
                'lebar_sheet_mm'   => 1030.00,
                'harga_beli'       => 165000.00,
                'catatan'          => 'Untuk cetak offset plano & blok Spot UV / Varnish.',
            ],
            [
                'kode_material'    => 'MPL-0005',
                'nama_material'    => 'Polymer Flexo Medium 1.14 mm (Web Zonten)',
                'brand'            => 'Asahi AFP-DSE',
                'tipe_plate'       => 'Digital Flexo Plate',
                'ketebalan_mm'     => 1.140,
                'panjang_sheet_mm' => 600.00,
                'lebar_sheet_mm'   => 900.00,
                'harga_beli'       => 850000.00,
                'catatan'          => 'Ukuran sheet sedang yang hemat potongan untuk mesin Zonten.',
            ],
        ];

        $plates = [];
        foreach ($catalog as $row) {
            $plates[$row['kode_material']] = MasterMaterialPlate::firstOrCreate(
                ['kode_material' => $row['kode_material']],
                array_merge($row, [
                    'satuan_id'   => $satuanSheet?->id,
                    'supplier_id' => $supplier?->id,
                    'status'      => 'aktif',
                ])
            );
        }

        $stockService = app(PracetakStockService::class);

        // 3. Transaksi Penerimaan #1 (Posted — Masuk Stok & Ledger IN)
        $p1 = PracetakPenerimaan::where('nomor_penerimaan', 'PRE-2026-0001')->first();
        if (! $p1) {
            $p1 = PracetakPenerimaan::create([
                'nomor_penerimaan'    => 'PRE-2026-0001',
                'tanggal_terima'      => now()->subDays(14)->toDateString(),
                'supplier_id'         => $supplier?->id,
                'no_surat_jalan'      => 'SJ-PLT-2611-001',
                'no_invoice_supplier' => 'INV-SUP-2611-001',
                'status'              => 'draft',
                'catatan'             => 'Pembelian stok awal material plate Flexo (Batch 1)',
                'created_by'          => $userId,
            ]);

            $p1->items()->createMany([
                [
                    'master_material_plate_id' => $plates['MPL-0001']->id,
                    'qty_sheet'                => 10,
                    'harga_satuan'             => 1450000,
                    'catatan'                  => 'Stok awal DuPont Cyrel 1.14mm',
                ],
                [
                    'master_material_plate_id' => $plates['MPL-0002']->id,
                    'qty_sheet'                => 8,
                    'harga_satuan'             => 1750000,
                    'catatan'                  => 'Stok awal Flint nyloflex 1.70mm',
                ],
                [
                    'master_material_plate_id' => $plates['MPL-0005']->id,
                    'qty_sheet'                => 12,
                    'harga_satuan'             => 850000,
                    'catatan'                  => 'Stok awal Asahi Medium Web Zonten',
                ],
            ]);

            $stockService->postPenerimaan($p1, $userId);
        }

        // 4. Transaksi Penerimaan #2 (Posted — Repeat pembelian dengan penyesuaian harga untuk demo Weighted Average Cost)
        $p2 = PracetakPenerimaan::where('nomor_penerimaan', 'PRE-2026-0002')->first();
        if (! $p2) {
            $p2 = PracetakPenerimaan::create([
                'nomor_penerimaan'    => 'PRE-2026-0002',
                'tanggal_terima'      => now()->subDays(5)->toDateString(),
                'supplier_id'         => $supplier?->id,
                'no_surat_jalan'      => 'SJ-PLT-2611-008',
                'no_invoice_supplier' => 'INV-SUP-2611-008',
                'status'              => 'draft',
                'catatan'             => 'Restock Polymer Flexo & Plate CTP Aluminium (Batch 2)',
                'created_by'          => $userId,
            ]);

            $p2->items()->createMany([
                [
                    'master_material_plate_id' => $plates['MPL-0001']->id,
                    'qty_sheet'                => 4,
                    'harga_satuan'             => 1500000,
                    'catatan'                  => 'Restock harga baru (cek rata-rata tertimbang)',
                ],
                [
                    'master_material_plate_id' => $plates['MPL-0003']->id,
                    'qty_sheet'                => 6,
                    'harga_satuan'             => 1280000,
                    'catatan'                  => 'Toyobo Water-Wash untuk job detail halus',
                ],
                [
                    'master_material_plate_id' => $plates['MPL-0004']->id,
                    'qty_sheet'                => 25,
                    'harga_satuan'             => 165000,
                    'catatan'                  => 'Plate CTP Aluminium untuk Spot UV / Offset',
                ],
            ]);

            $stockService->postPenerimaan($p2, $userId);
        }

        // 5. Transaksi Penerimaan #3 (Draft — Siap dicoba klik tombol "Posting" oleh user)
        $p3 = PracetakPenerimaan::where('nomor_penerimaan', 'PRE-2026-0003')->first();
        if (! $p3) {
            $p3 = PracetakPenerimaan::create([
                'nomor_penerimaan'    => 'PRE-2026-0003',
                'tanggal_terima'      => now()->toDateString(),
                'supplier_id'         => $supplier?->id,
                'no_surat_jalan'      => 'SJ-PLT-2611-015',
                'no_invoice_supplier' => 'INV-SUP-2611-015',
                'status'              => 'draft',
                'total_qty'           => 5,
                'total_nominal'       => 4300000,
                'catatan'             => 'Draft kedatangan baru — klik Posting untuk memasukkan ke saldo stok',
                'created_by'          => $userId,
            ]);

            $p3->items()->create([
                'master_material_plate_id' => $plates['MPL-0005']->id,
                'qty_sheet'                => 5,
                'harga_satuan'             => 860000,
                'catatan'                  => 'Siap dicek fisik & di-Posting',
            ]);
        }
    }
}
