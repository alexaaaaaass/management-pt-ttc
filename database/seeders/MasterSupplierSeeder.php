<?php

namespace Database\Seeders;

use App\Models\MasterMaterialPlate;
use App\Models\MasterSupplier;
use Illuminate\Database\Seeder;

class MasterSupplierSeeder extends Seeder
{
    /**
     * Seed data Master Supplier untuk kebutuhan pengadaan bahan baku percetakan,
     * material plate pra cetak, tinta/varnish, foil/laminasi, consumables, dan subcount.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'kode_supplier'  => 'SUP-PLT-001',
                'nama_supplier'  => 'PT DuPont Grafika Nusantara',
                'jenis_supplier' => 'Material Plate & Prepress',
                'alamat'         => 'Kawasan Industri Jababeka II Blok CC-12, Cikarang, Bekasi, Jawa Barat',
                'keterangan'     => 'Distributor resmi photopolymer flexo plate DuPont Cyrel (1.14 mm & 1.70 mm) dan material prepress digital. TOP 30 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-PLT-002',
                'nama_supplier'  => 'PT Flint Imaging Indonesia',
                'jenis_supplier' => 'Material Plate & Prepress',
                'alamat'         => 'Jl. Raya Rungkut Industri III No. 48, Surabaya, Jawa Timur',
                'keterangan'     => 'Pemasok plate photopolymer Flint Group nyloflex, cairan washout solvent, dan mounting tape flexo. TOP 30 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-PLT-003',
                'nama_supplier'  => 'PT Toyobo & Asahi Prepress Jaya',
                'jenis_supplier' => 'Material Plate & Prepress',
                'alamat'         => 'Pergudangan Margomulyo Permai Blok G-19, Surabaya, Jawa Timur',
                'keterangan'     => 'Distributor plate water-wash Toyobo Cosmolight & digital flexo Asahi AFP-DSE untuk mesin web Zonten. TOP 30 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-PLT-004',
                'nama_supplier'  => 'PT Kodak Sonora Grafika Mandiri',
                'jenis_supplier' => 'Material Plate & Prepress',
                'alamat'         => 'Jl. Raya Waru Km. 12, Sidoarjo, Jawa Timur',
                'keterangan'     => 'Distributor plate thermal CTP aluminium Kodak Sonora XTRA (0.30 mm) untuk cetak offset plano & blok Spot UV. TOP 14 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-KRT-001',
                'nama_supplier'  => 'PT Sinar Kertas Kemasan Tbk',
                'jenis_supplier' => 'Kertas & Karton',
                'alamat'         => 'Jl. Raya Mojokerto - Surabaya Km. 42, Mojokerto, Jawa Timur',
                'keterangan'     => 'Pemasok utama kertas Ivory 210–350 gsm, Art Paper, Art Carton, dan Duplex roll/plano. TOP 30 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-KRT-002',
                'nama_supplier'  => 'PT Pakerin & Kartonindo Sejahtera',
                'jenis_supplier' => 'Kertas & Karton',
                'alamat'         => 'Jl. Raya Bangun Pungging No. 88, Mojokerto, Jawa Timur',
                'keterangan'     => 'Pemasok alternatif kertas karton, duplex, metalized paper, dan stiker label (mendukung split PO multi-supplier). TOP 30 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-TNT-001',
                'nama_supplier'  => 'PT DIC Tinta & Coating Indonesia',
                'jenis_supplier' => 'Tinta, Varnish & Coating',
                'alamat'         => 'Kawasan Industri SIER Jl. Berbek Industri IV No. 22, Sidoarjo, Jawa Timur',
                'keterangan'     => 'Pemasok tinta cetak offset CMYK, tinta Pantone khusus, tinta flexo UV/water-based, UV Varnish & OPV. TOP 30 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-FLM-001',
                'nama_supplier'  => 'PT Kurz Foil & Laminasi Prima',
                'jenis_supplier' => 'Foil Stamping, Laminasi & Lem',
                'alamat'         => 'Komplek Pergudangan Tambak Sawah Blok B-07, Waru, Sidoarjo, Jawa Timur',
                'keterangan'     => 'Pemasok hot stamping foil (gold/silver/hologram), film laminasi glossy/doff, serta lem PVA & hotmelt kemasan. TOP 30 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-CNS-001',
                'nama_supplier'  => 'CV Mitra Consumables & Cylinder',
                'jenis_supplier' => 'Consumables & Sparepart Cetak',
                'alamat'         => 'Jl. Raya Singosari No. 115, Singosari, Malang, Jawa Timur',
                'keterangan'     => 'Pemasok cylinder flexo/rotogravure, blanket offset, pisau pond/die-cut, lakban, dan karton box pengiriman. TOP 14 hari.',
            ],
            [
                'kode_supplier'  => 'SUP-SUB-001',
                'nama_supplier'  => 'CV Karya Makloon Finishing Malang',
                'jenis_supplier' => 'Subcount / Jasa Makloon',
                'alamat'         => 'Jl. Perusahaan Raya No. 64, Karanglo, Singosari, Malang, Jawa Timur',
                'keterangan'     => 'Rekanan subcount untuk pekerjaan finishing eksternal: laminating, emboss/deboss, hot print, dan die-cut. TOP 14 hari.',
            ],
        ];

        $seededByCode = [];
        foreach ($suppliers as $row) {
            $seededByCode[$row['kode_supplier']] = MasterSupplier::updateOrCreate(
                ['kode_supplier' => $row['kode_supplier']],
                $row
            );
        }

        // Hubungkan supplier rekomendasi default pada katalog MasterMaterialPlate yang belum punya supplier
        $plateSupplierMap = [
            'MPL-0001' => 'SUP-PLT-001', // DuPont Cyrel
            'MPL-0002' => 'SUP-PLT-002', // Flint Group nyloflex
            'MPL-0003' => 'SUP-PLT-003', // Toyobo Cosmolight
            'MPL-0004' => 'SUP-PLT-004', // Kodak Sonora XTRA
            'MPL-0005' => 'SUP-PLT-003', // Asahi AFP-DSE
        ];

        foreach ($plateSupplierMap as $plateCode => $supplierCode) {
            $supplier = $seededByCode[$supplierCode] ?? null;
            if (! $supplier) {
                continue;
            }

            MasterMaterialPlate::where('kode_material', $plateCode)
                ->whereNull('supplier_id')
                ->update(['supplier_id' => $supplier->id]);
        }
    }
}
