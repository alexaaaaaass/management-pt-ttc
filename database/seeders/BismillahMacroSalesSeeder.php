<?php

namespace Database\Seeders;

use App\Models\ApprovalForm;
use App\Models\ApprovalFormItem;
use App\Models\CategoryItem;
use App\Models\CoaMovement;
use App\Models\Customer;
use App\Models\FinishGoodItem;
use App\Models\FinishGoodItemMaterial;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvPayment;
use App\Models\JournalEntry;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use App\Models\MasterDepartemen;
use App\Models\MasterItem;
use App\Models\MasterOperator;
use App\Models\MasterPrinting;
use App\Models\MasterSatuan;
use App\Models\MasterSpk;
use App\Models\MasterSupplier;
use App\Models\MaterialStock;
use App\Models\MetodeBayar;
use App\Models\OperasionalPay;
use App\Models\OrderConfirmation;
use App\Models\OrderConfirmationItem;
use App\Models\Packaging;
use App\Models\PenerimaanBarang;
use App\Models\PenerimaanBarangItem;
use App\Models\Printing;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use App\Models\TransKas;
use App\Models\TransKasBank;
use App\Models\TypeItem;
use App\Models\User;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BismillahMacroSalesSeeder extends Seeder
{
    private function excelDateToString(int|string|null $serial, string $fallback = '2026-01-04'): string
    {
        if (! is_numeric($serial) || (int) $serial < 40000) {
            return $fallback;
        }

        return Carbon::create(1899, 12, 30)->addDays((int) $serial)->toDateString();
    }

    public function run(): void
    {
        $treasury = app(TreasuryService::class);

        /*
        |--------------------------------------------------------------------------
        | 1. BERSIHKAN DATA TRANSAKSI LAMA AGAR 100% MURNI DARI BISMILLAH MACRO.XLSM
        |--------------------------------------------------------------------------
        */
        Schema::disableForeignKeyConstraints();

        $tablesToTruncate = [
            'coa_movements',
            'journal_entry_lines',
            'journal_entries',
            'trans_kas',
            'trans_kas_banks',
            'trans_faktur_items',
            'trans_fakturs',
            'operasional_pays',
            'po_bill_pay_items',
            'po_bill_pays',
            'po_billing_details',
            'po_billing_items',
            'po_billings',
            'payment_entry_goods',
            'penerimaan_barang_items',
            'penerimaan_barangs',
            'purchase_order_items',
            'purchase_orders',
            'purchase_request_items',
            'purchase_requests',
            'master_suppliers',
            'gajis',
            'absensis',
            'lemburs',
            'izins',
            'cutis',
            'peng_pinjamans',
            'pemb_pinjamans',
            'bonus_karyawans',
            'pot_tunjangans',
            'hari_liburs',
            'external_return_items',
            'external_returns',
            'internal_return_items',
            'internal_returns',
            'subcount_out_items',
            'subcount_outs',
            'subcount_in_items',
            'subcount_ins',
            'berita_acara_items',
            'berita_acaras',
            'error_productions',
            'blokir',
            'material_movements',
            'inv_payments',
            'invoice_items',
            'invoices',
            'surat_jalan_items',
            'surat_jalans',
            'packagings',
            'master_finishings',
            'printings',
            'pracetak_processes',
            'diemakings',
            'imr_printing_items',
            'imr_printings',
            'imr_finish_items',
            'imr_finishes',
            'master_spk',
            'sales_order_items',
            'sales_orders',
            'order_confirmation_items',
            'order_confirmations',
            'approval_form_items',
            'approval_forms',
            'finish_good_item_materials',
            'finish_good_items',
            'customers',
            'master_printings',
        ];

        foreach ($tablesToTruncate as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // Bersihkan kemungkinan duplikat kode_akun pada master_coas
        if (Schema::hasTable('master_coas')) {
            $seenCodes = [];
            foreach (MasterCOA::orderBy('id')->get() as $coaRow) {
                $normCode = trim((string) $coaRow->kode_akun);
                if (isset($seenCodes[$normCode])) {
                    $coaRow->delete();
                } else {
                    $seenCodes[$normCode] = true;
                }
            }
            MasterCOA::query()->update([
                'saldo_debit'  => 0,
                'saldo_kredit' => 0,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. SEED 10 CUSTOMER DARI SHEET MASTER (C001 s/d C010)
        |--------------------------------------------------------------------------
        */
        $macroCustomers = [
            ['kode' => 'C001', 'nama' => 'PT. NANO KENKOU INDONESIA',    'pic' => 'P HERI',   'group' => 'TOBACCO',   'alamat' => 'Kawasan Industri Rungkut, Surabaya, Jawa Timur'],
            ['kode' => 'C002', 'nama' => 'PT. CATUR WIRATAMA TOBACCO',   'pic' => 'P HERI',   'group' => 'TOBACCO',   'alamat' => 'Jl. Raya Malang - Pandaan KM 28, Pasuruan, Jawa Timur'],
            ['kode' => 'C003', 'nama' => 'CV. AZKARA TOBACCO',           'pic' => 'P HERI',   'group' => 'TOBACCO',   'alamat' => 'Jl. Mayor Kusmanto, Kudus, Jawa Tengah'],
            ['kode' => 'C004', 'nama' => 'PR. JOYO BARU',                'pic' => 'P HERI',   'group' => 'PR',        'alamat' => 'Jl. Raya Gondanglegi, Malang, Jawa Timur'],
            ['kode' => 'C005', 'nama' => 'PR. JUMALI',                   'pic' => 'P HERI',   'group' => 'PR',        'alamat' => 'Jl. Raya Pakisaji, Malang, Jawa Timur'],
            ['kode' => 'C006', 'nama' => 'P. ADI',                       'pic' => 'P. ADI',   'group' => 'MITRA',     'alamat' => 'Jl. Raya Bululawang, Malang, Jawa Timur'],
            ['kode' => 'C007', 'nama' => 'P. FAISAL',                    'pic' => 'P. FAISAL','group' => 'MITRA',     'alamat' => 'Jl. Raya Tanggulangin, Sidoarjo, Jawa Timur'],
            ['kode' => 'C008', 'nama' => 'PR. PINANG JAYA',              'pic' => 'P HERI',   'group' => 'PR',        'alamat' => 'Jl. Raya Tajinan, Malang, Jawa Timur'],
            ['kode' => 'C009', 'nama' => 'Juan',                         'pic' => 'Juan',     'group' => 'PERSONAL',  'alamat' => 'Surabaya, Jawa Timur'],
            ['kode' => 'C010', 'nama' => 'PT RUKUN GLOBAL INDOJAYA',     'pic' => 'P HERI',   'group' => 'CORPORATE', 'alamat' => 'Kawasan Industri Jababeka, Bekasi, Jawa Barat'],
        ];

        $validCodes = array_column($macroCustomers, 'kode');
        Customer::whereNotIn('kode_customer', $validCodes)->delete();

        $customerMap = [];
        foreach ($macroCustomers as $mc) {
            $cust = Customer::updateOrCreate(
                ['kode_customer' => $mc['kode']],
                [
                    'nama_customer'  => $mc['nama'],
                    'nama_pic'       => $mc['pic'],
                    'kode_group'     => $mc['group'],
                    'nama_group'     => $mc['group'],
                    'alamat_lengkap' => $mc['alamat'],
                ]
            );
            $customerMap[$mc['kode']] = $cust;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. MASTER PENDUKUNG (SATUAN, TYPE ITEM, BAHAN BAKU & STOK BOM, MESIN CETAK)
        |--------------------------------------------------------------------------
        */
        $satuanPcs = MasterSatuan::firstOrCreate(
            ['kode_satuan' => 'PCS'],
            ['nama_satuan' => 'Pieces (Pcs)']
        );
        $satuanLbr = MasterSatuan::firstOrCreate(
            ['kode_satuan' => 'LBR'],
            ['nama_satuan' => 'Lembar / Sheet']
        );

        $catBahan = CategoryItem::firstOrCreate(
            ['kode_item_category' => 'RAW'],
            ['nama_category' => 'Bahan Baku Cetak']
        );
        $deptPrinting = MasterDepartemen::firstOrCreate(
            ['kode' => 'PRT'],
            ['nama_departemen' => 'Printing & Offset', 'deskripsi' => 'Departemen Cetak', 'status' => 1]
        );

        $typeMap = [];
        foreach (['Etiket', 'Inner', 'Label', 'Ambri', 'Sloop', 'Grenjeng', 'Opp Pack', 'Opp Sloop', 'Cylinder', 'CTP', 'Kertas'] as $komp) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $komp), 0, 5));
            $typeMap[$komp] = TypeItem::firstOrCreate(
                ['kode_type_item' => $code],
                ['nama_type_item' => $komp, 'category_item_id' => $catBahan->id]
            );
        }

        $rawSubstrates = [
            'Ivory'    => ['kode' => 'MAT-IVORY',    'nama' => 'Kertas Ivory 250gr Plano'],
            'Metalize' => ['kode' => 'MAT-METALIZE', 'nama' => 'Kertas Metalize Silver 250gr'],
            'Artpaper' => ['kode' => 'MAT-ARTPAPER', 'nama' => 'Kertas Artpaper 150gr'],
            'Ambri'    => ['kode' => 'MAT-AMBRI',    'nama' => 'Kertas Ambri Cigaret 32gr'],
            'Grenjeng' => ['kode' => 'MAT-GRENJENG', 'nama' => 'Foil Grenjeng Emboss Silver/Gold'],
            'Opp'      => ['kode' => 'MAT-OPP',      'nama' => 'Plastik OPP Seal Film 20 Micron'],
            'CTP'      => ['kode' => 'MAT-CTP',      'nama' => 'Plat Aluminium CTP Offset'],
            'Cylinder' => ['kode' => 'MAT-CYLINDER', 'nama' => 'Cylinder Rotogravure / Flexo'],
            'Kertas'   => ['kode' => 'MAT-KERTAS',   'nama' => 'Kertas Produksi Cetak Khusus'],
            'Tinta'    => ['kode' => 'MAT-TINTA',    'nama' => 'Tinta Offset CMYK, UV Varnish & Lem'],
        ];

        $rawMaterialMap = [];
        foreach ($rawSubstrates as $key => $info) {
            $mItem = MasterItem::updateOrCreate(
                ['kode_master_item' => $info['kode']],
                [
                    'nama_master_item' => $info['nama'],
                    'min_stock'        => 100,
                    'min_order'        => 100,
                    'qty'              => 5000000,
                    'satuan_id'        => $satuanLbr->id,
                    'category_item_id' => $catBahan->id,
                    'type_item_id'     => $typeMap['Kertas']->id,
                ]
            );
            MaterialStock::updateOrCreate(
                ['item_id' => $mItem->id],
                ['on_hand' => 10000000, 'allocation' => 0]
            );
            $rawMaterialMap[$key] = $mItem;
        }

        // Mesin Cetak hanya 3 unit utama (bersihkan duplikat bila ada)
        MasterPrinting::whereNotIn('nama_mesin', ['SM 74', 'FLEXO', 'GTO 52'])->delete();
        $mesinSm74 = MasterPrinting::updateOrCreate(
            ['nama_mesin' => 'SM 74'],
            ['jenis_mesin' => 'PRINTING', 'kapasitas' => 100, 'proses' => 'OFFSET 4 WARNA', 'status' => 1]
        );
        $mesinFlexo = MasterPrinting::updateOrCreate(
            ['nama_mesin' => 'FLEXO'],
            ['jenis_mesin' => 'PRINTING', 'kapasitas' => 100, 'proses' => 'FLEXO & ROLL', 'status' => 1]
        );
        $mesinGto52 = MasterPrinting::updateOrCreate(
            ['nama_mesin' => 'GTO 52'],
            ['jenis_mesin' => 'PRINTING', 'kapasitas' => 80, 'proses' => 'OFFSET KOMERSIAL', 'status' => 1]
        );

        $operatorId = MasterOperator::firstOrCreate(
            ['nama_operator' => 'Budi Santoso (Kepala Cetak)']
        )->id;

        $karyawanId = Karyawan::value('id') ?? 1;
        $userId = User::value('id') ?? 1;

        $metodeTransfer = MetodeBayar::firstOrCreate(
            ['kode_pembayaran' => 'TRF'],
            ['metode_pembayaran' => 'Transfer Bank (BCA / BRI)']
        );
        $metodeTunai = MetodeBayar::firstOrCreate(
            ['kode_pembayaran' => 'CASH'],
            ['metode_pembayaran' => 'Cash on Hand / Tunai']
        );

        Schema::enableForeignKeyConstraints();

        /*
        |--------------------------------------------------------------------------
        | 4. DATA LENGKAP 19 ORDER HEADER, 47 ORDER DETAIL, 17 FINANCE & 6 COMMISSION
        |    (100% SESUAI BISMILLAH MACRO.XLSM)
        |--------------------------------------------------------------------------
        */
        $orderHeaders = [
            ['order_id' => 'OR-26-I-C001-001',   'date' => 46026, 'cust' => 'C001', 'summary' => 'ETIKET KENKOU',     'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-II-C004-001',  'date' => 46058, 'cust' => 'C004', 'summary' => 'INNER JB',          'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-II-C005-001',  'date' => 46062, 'cust' => 'C005', 'summary' => 'HLP PDW 88',        'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-II-C003-001',  'date' => 46069, 'cust' => 'C003', 'summary' => 'ETIKET FLORENT',    'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-III-C008-001', 'date' => 46095, 'cust' => 'C008', 'summary' => 'PINANG MAS',        'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-III-C002-001', 'date' => 46097, 'cust' => 'C002', 'summary' => 'AMBRI DAFI',        'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-IV-C005-002',  'date' => 46127, 'cust' => 'C005', 'summary' => 'SKM BLUE SEA',      'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-IV-C002-002',  'date' => 46129, 'cust' => 'C002', 'summary' => 'SKT DAFI',          'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'ON PROCESS', 'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-IV-C005-003',  'date' => 46135, 'cust' => 'C005', 'summary' => 'SKT PDW 88',        'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'N', 'po' => 'N', 'prod' => 'ON PROCESS', 'deliv' => 'PENDING',   'lead' => 14],
            ['order_id' => 'OR-26-V-C006-001',   'date' => 46162, 'cust' => 'C006', 'summary' => 'Crust',             'pic' => 'P. ADI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-V-C007-001',   'date' => 46162, 'cust' => 'C007', 'summary' => 'Apple',             'pic' => 'P. FAISAL','conf'=> 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-VI-C002-003',  'date' => 46190, 'cust' => 'C002', 'summary' => 'AMBRI DAFI',        'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-VI-C002-004',  'date' => 46200, 'cust' => 'C002', 'summary' => 'SKT DAFI 16',       'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-VI-C006-002',  'date' => 46198, 'cust' => 'C006', 'summary' => 'BARBARA',           'pic' => 'P. ADI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'ON PROCESS', 'deliv' => 'SHIPPED',   'lead' => 14],
            ['order_id' => 'OR-26-VII-C005-004', 'date' => 46212, 'cust' => 'C005', 'summary' => 'ETIKET VULLARISO',  'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'ON PROCESS', 'deliv' => 'SHIPPED',   'lead' => 14],
            ['order_id' => 'OR-26-VII-C006-003', 'date' => 46224, 'cust' => 'C006', 'summary' => 'BARBARA',           'pic' => 'P. ADI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'FINISHED',   'deliv' => 'DELIVERED', 'lead' => 14],
            ['order_id' => 'OR-26-X-C010-001',   'date' => 46302, 'cust' => 'C010', 'summary' => 'Artha',             'pic' => 'P HERI', 'conf' => 'Y', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'ON PROCESS', 'deliv' => 'SHIPPED',   'lead' => 14],
            ['order_id' => 'OR-26-X-C002-005',   'date' => 46308, 'cust' => 'C002', 'summary' => 'BarbaRoza',         'pic' => 'P HERI', 'conf' => 'N', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'UNFINISHED', 'deliv' => 'PENDING',   'lead' => 14],
            ['order_id' => 'OR-26-X-C002-006',   'date' => 46318, 'cust' => 'C002', 'summary' => 'Dafi Black SKT 16', 'pic' => 'P HERI', 'conf' => 'N', 'appr' => 'Y', 'po' => 'Y', 'prod' => 'UNFINISHED', 'deliv' => 'PENDING',   'lead' => 14],
        ];

        $orderDetails = [
            // 1. OR-26-I-C001-001 (Total: 46,196,120)
            ['order_id' => 'OR-26-I-C001-001', 'item' => 'KENKOU', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '10', 'bahan' => 'Metalize', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 350, 'total' => 17500000, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-I-C001-001', 'item' => 'KENKOU', 'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '10', 'bahan' => 'Ivory',    'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 80,  'total' => 4000000,  'urut' => 2, 'ket' => ''],
            ['order_id' => 'OR-26-I-C001-001', 'item' => 'KENKOU', 'jenis' => 'SKT', 'komponen' => 'Label',  'isi' => '10', 'bahan' => 'Ivory',    'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 40360, 'actual_qty' => 40360, 'harga' => 50,  'total' => 2018000,  'urut' => 3, 'ket' => ''],
            ['order_id' => 'OR-26-I-C001-001', 'item' => 'KENKOU', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '12', 'bahan' => 'Metalize', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 42574, 'actual_qty' => 42574, 'harga' => 380, 'total' => 16178120, 'urut' => 4, 'ket' => ''],
            ['order_id' => 'OR-26-I-C001-001', 'item' => 'KENKOU', 'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '12', 'bahan' => 'Ivory',    'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 80,  'total' => 4000000,  'urut' => 5, 'ket' => ''],
            ['order_id' => 'OR-26-I-C001-001', 'item' => 'KENKOU', 'jenis' => 'SKT', 'komponen' => 'Label',  'isi' => '12', 'bahan' => 'Ivory',    'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 50,  'total' => 2500000,  'urut' => 6, 'ket' => ''],

            // 2. OR-26-II-C003-001 (Total: 39,111,750)
            ['order_id' => 'OR-26-II-C003-001', 'item' => 'Florent Merah', 'jenis' => 'HLP', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Emboss, Deboss, Cutting', 'order_qty' => 47500, 'actual_qty' => 47500, 'harga' => 333,  'total' => 15817500, 'urut' => 1, 'ket' => 'Kesepakatan Tertentu'],
            ['order_id' => 'OR-26-II-C003-001', 'item' => 'Florent Biru',  'jenis' => 'HLP', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Emboss, Deboss, Cutting', 'order_qty' => 50750, 'actual_qty' => 50750, 'harga' => 185,  'total' => 9388750,  'urut' => 2, 'ket' => 'Kesepakatan Tertentu'],
            ['order_id' => 'OR-26-II-C003-001', 'item' => 'Florent Merah', 'jenis' => 'HLP', 'komponen' => 'Sloop',  'isi' => '10', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Cutting',                 'order_qty' => 5000,  'actual_qty' => 5000,  'harga' => 1370, 'total' => 6850000,  'urut' => 3, 'ket' => 'Kesepakatan Tertentu'],
            ['order_id' => 'OR-26-II-C003-001', 'item' => 'Florent Biru',  'jenis' => 'HLP', 'komponen' => 'Sloop',  'isi' => '10', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Cutting',                 'order_qty' => 5150,  'actual_qty' => 5150,  'harga' => 1370, 'total' => 7055500,  'urut' => 4, 'ket' => 'Kesepakatan Tertentu'],

            // 3. OR-26-II-C004-001 (Total: 6,887,500)
            ['order_id' => 'OR-26-II-C004-001', 'item' => 'INNER JB', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '12', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Lem, Cutting', 'order_qty' => 47500, 'actual_qty' => 47500, 'harga' => 145, 'total' => 6887500, 'urut' => 1, 'ket' => ''],

            // 4. OR-26-II-C005-001 (Detail: 21,907,400 / Finance Revenue: 13,090,000)
            ['order_id' => 'OR-26-II-C005-001', 'item' => 'PDW 88', 'jenis' => 'HLP', 'komponen' => 'Etiket', 'isi' => '12', 'bahan' => 'Metalize', 'finishing' => 'Spot UV, Emboss, Cutting', 'order_qty' => 49460, 'actual_qty' => 49460, 'harga' => 370, 'total' => 18300200, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-II-C005-001', 'item' => 'PDW 88', 'jenis' => 'HLP', 'komponen' => 'Inner',  'isi' => '12', 'bahan' => 'Ivory',    'finishing' => 'Spot UV, Cutting',         'order_qty' => 50100, 'actual_qty' => 50100, 'harga' => 72,  'total' => 3607200,  'urut' => 2, 'ket' => ''],

            // 5. OR-26-III-C002-001 (Total: 13,090,000)
            ['order_id' => 'OR-26-III-C002-001', 'item' => 'Dafi Exclusive Hijau', 'jenis' => 'Ambri', 'komponen' => 'Ambri', 'isi' => '1', 'bahan' => 'Ambri', 'finishing' => 'Cutting', 'order_qty' => 495000, 'actual_qty' => 568000, 'harga' => 10, 'total' => 5680000, 'urut' => 1, 'ket' => 'Kesepakatan Tertentu'],
            ['order_id' => 'OR-26-III-C002-001', 'item' => 'Dafi Rempah Padang',   'jenis' => 'Ambri', 'komponen' => 'Ambri', 'isi' => '1', 'bahan' => 'Ambri', 'finishing' => 'Cutting', 'order_qty' => 495000, 'actual_qty' => 570000, 'harga' => 13, 'total' => 7410000, 'urut' => 2, 'ket' => 'Kesepakatan Tertentu'],

            // 6. OR-26-III-C008-001 (Total: 7,130,000)
            ['order_id' => 'OR-26-III-C008-001', 'item' => 'Pinang Mas', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Lem, Cutting', 'order_qty' => 30000, 'actual_qty' => 23000, 'harga' => 310, 'total' => 7130000, 'urut' => 1, 'ket' => 'Kesepakatan Tertentu'],

            // 7. OR-26-IV-C002-002 (Total: 15,289,345)
            ['order_id' => 'OR-26-IV-C002-002', 'item' => 'DAFI 16',       'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 32755, 'harga' => 373, 'total' => 12217615, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-IV-C002-002', 'item' => 'INNER DAFI 16', 'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Cutting',              'order_qty' => 50000, 'actual_qty' => 53890, 'harga' => 57,  'total' => 3071730,  'urut' => 2, 'ket' => ''],
            ['order_id' => 'OR-26-IV-C002-002', 'item' => 'DAFI 20',       'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 0,     'harga' => 373, 'total' => 0,        'urut' => 3, 'ket' => 'Belum Produksi'],
            ['order_id' => 'OR-26-IV-C002-002', 'item' => 'INNER DAFI 20', 'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '20', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Cutting',              'order_qty' => 50000, 'actual_qty' => 0,     'harga' => 57,  'total' => 0,        'urut' => 4, 'ket' => 'Belum Produksi'],

            // 8. OR-26-IV-C005-002 (Total: 17,895,600)
            ['order_id' => 'OR-26-IV-C005-002', 'item' => 'Blue Sea', 'jenis' => 'SKM', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Metalize', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 41425, 'harga' => 432, 'total' => 17895600, 'urut' => 1, 'ket' => ''],

            // 9. OR-26-IV-C005-003 (Total: 38,635,200)
            ['order_id' => 'OR-26-IV-C005-003', 'item' => 'PDW88 Sliding Luar',  'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Metalize', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 49850, 'harga' => 432, 'total' => 21535200, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-IV-C005-003', 'item' => 'PDW88 Sliding Dalam', 'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '20', 'bahan' => 'Ivory',    'finishing' => 'Spot UV, Cutting',              'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 342, 'total' => 17100000, 'urut' => 2, 'ket' => ''],

            // 10. OR-26-V-C006-001 (Total: 33,939,200)
            ['order_id' => 'OR-26-V-C006-001', 'item' => 'Crust', 'jenis' => 'SKM', 'komponen' => 'Etiket',   'isi' => '20', 'bahan' => 'Metalize', 'finishing' => 'Hotprint, Spot UV, Cutting', 'order_qty' => 70000, 'actual_qty' => 70000, 'harga' => 470,   'total' => 32900000, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-V-C006-001', 'item' => 'Crust', 'jenis' => 'SKM', 'komponen' => 'Grenjeng', 'isi' => '1',  'bahan' => 'Grenjeng', 'finishing' => 'Cutting',                    'order_qty' => 10,    'actual_qty' => 10,    'harga' => 56400, 'total' => 564000,   'urut' => 2, 'ket' => ''],
            ['order_id' => 'OR-26-V-C006-001', 'item' => 'Crust', 'jenis' => 'SKM', 'komponen' => 'Inner',    'isi' => '1',  'bahan' => 'Ivory',    'finishing' => 'Cutting',                    'order_qty' => 16.5,  'actual_qty' => 16.5,  'harga' => 28800, 'total' => 475200,   'urut' => 3, 'ket' => ''],

            // 11. OR-26-V-C007-001 (Total: 37,607,150)
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple Material', 'jenis' => 'SKM',    'komponen' => 'Grenjeng',  'isi' => '1',  'bahan' => 'Grenjeng', 'finishing' => 'Cutting',                          'order_qty' => 55,    'actual_qty' => 55,    'harga' => 57500,   'total' => 3162500,  'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple material', 'jenis' => 'SKM',    'komponen' => 'Inner',     'isi' => '1',  'bahan' => 'Ivory',    'finishing' => 'Cutting',                          'order_qty' => 53.6,  'actual_qty' => 53.6,  'harga' => 23000,   'total' => 1232800,  'urut' => 2, 'ket' => ''],
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple material', 'jenis' => 'SKM',    'komponen' => 'Opp Pack',  'isi' => '1',  'bahan' => 'Opp',      'finishing' => 'Cutting',                          'order_qty' => 43,    'actual_qty' => 43,    'harga' => 37950,   'total' => 1631850,  'urut' => 3, 'ket' => ''],
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple material', 'jenis' => 'Sloop',  'komponen' => 'Opp Sloop', 'isi' => '1',  'bahan' => 'Opp',      'finishing' => 'Cutting',                          'order_qty' => 10,    'actual_qty' => 10,    'harga' => 69000,   'total' => 690000,   'urut' => 4, 'ket' => ''],
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple Material', 'jenis' => 'SKM',    'komponen' => 'Etiket',    'isi' => '20', 'bahan' => 'Metalize', 'finishing' => 'Hologram, Spot UV, Emboss, Cutting','order_qty' => 36000, 'actual_qty' => 36000, 'harga' => 345,     'total' => 12420000, 'urut' => 5, 'ket' => ''],
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple Material', 'jenis' => 'SKM',    'komponen' => 'Cylinder',  'isi' => '1',  'bahan' => 'Cylinder', 'finishing' => '-',                                'order_qty' => 1,     'actual_qty' => 1,     'harga' => 6350000, 'total' => 6350000,  'urut' => 6, 'ket' => ''],
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple Material', 'jenis' => 'SKM',    'komponen' => 'CTP',       'isi' => '1',  'bahan' => 'CTP',      'finishing' => '-',                                'order_qty' => 19,    'actual_qty' => 19,    'harga' => 580000,  'total' => 11020000, 'urut' => 7, 'ket' => ''],
            ['order_id' => 'OR-26-V-C007-001', 'item' => 'Apple Material', 'jenis' => 'Kertas', 'komponen' => 'Kertas',    'isi' => '1',  'bahan' => 'Kertas',   'finishing' => '-',                                'order_qty' => 2,     'actual_qty' => 2,     'harga' => 550000,  'total' => 1100000,  'urut' => 8, 'ket' => ''],

            // 12. OR-26-VI-C002-003 (Total: 5,863,000)
            ['order_id' => 'OR-26-VI-C002-003', 'item' => 'Ambri Exclusive Hijau', 'jenis' => 'Ambri', 'komponen' => 'Ambri', 'isi' => '1', 'bahan' => 'Ambri', 'finishing' => 'Cutting', 'order_qty' => 495000, 'actual_qty' => 533000, 'harga' => 11, 'total' => 5863000, 'urut' => 1, 'ket' => ''],

            // 13. OR-26-VI-C002-004 (Total: 11,192,541)
            ['order_id' => 'OR-26-VI-C002-004', 'item' => 'DAFI 16', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 30000, 'actual_qty' => 24792, 'harga' => 373, 'total' => 9247416, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-VI-C002-004', 'item' => 'DAFI 16', 'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Cutting',              'order_qty' => 30000, 'actual_qty' => 34125, 'harga' => 57,  'total' => 1945125, 'urut' => 2, 'ket' => ''],

            // 14. OR-26-VI-C006-002 (Total: 13,360,000)
            ['order_id' => 'OR-26-VI-C006-002', 'item' => 'BARBARA', 'jenis' => 'SKM', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Cutting', 'order_qty' => 51700, 'actual_qty' => 31700, 'harga' => 400, 'total' => 12680000, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-VI-C006-002', 'item' => 'BARBARA', 'jenis' => 'SKM', 'komponen' => 'Etiket', 'isi' => '21', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Cutting', 'order_qty' => 51700, 'actual_qty' => 1700,  'harga' => 400, 'total' => 680000,   'urut' => 2, 'ket' => ''],

            // 15. OR-26-VII-C005-004 (Total: 37,606,860)
            ['order_id' => 'OR-26-VII-C005-004', 'item' => 'Etiket Vullariso', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Metalize', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 47810, 'harga' => 432, 'total' => 20653920, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-VII-C005-004', 'item' => 'Inner Vullariso',  'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '20', 'bahan' => 'Ivory',    'finishing' => 'Spot UV, Cutting',              'order_qty' => 50000, 'actual_qty' => 49570, 'harga' => 342, 'total' => 16952940, 'urut' => 2, 'ket' => ''],

            // 16. OR-26-VII-C006-003 (Total: 46,154,000)
            ['order_id' => 'OR-26-VII-C006-003', 'item' => 'barbara Metalize', 'jenis' => 'SKM', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Metalize', 'finishing' => 'Spot UV, Cutting', 'order_qty' => 70000, 'actual_qty' => 98200, 'harga' => 470, 'total' => 46154000, 'urut' => 1, 'ket' => ''],

            // 17. OR-26-X-C010-001 (Total: 345,000,000)
            ['order_id' => 'OR-26-X-C010-001', 'item' => 'Artha 16',      'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Lem, Cutting', 'order_qty' => 500000, 'actual_qty' => 500000, 'harga' => 310, 'total' => 155000000, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-X-C010-001', 'item' => 'Artha Sena 20', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '20', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Lem, Cutting', 'order_qty' => 500000, 'actual_qty' => 500000, 'harga' => 380, 'total' => 190000000, 'urut' => 2, 'ket' => ''],

            // 18. OR-26-X-C002-005 (NEW ORDER - Total: 29,000,000)
            ['order_id' => 'OR-26-X-C002-005', 'item' => 'Barbaroza', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '12', 'bahan' => 'Ivory', 'finishing' => 'Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 580, 'total' => 29000000, 'urut' => 1, 'ket' => ''],

            // 19. OR-26-X-C002-006 (NEW ORDER - Total: 21,500,000)
            ['order_id' => 'OR-26-X-C002-006', 'item' => 'Dafi Black SKT 16', 'jenis' => 'SKT', 'komponen' => 'Etiket', 'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Spot UV, Emboss, Lem, Cutting', 'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 373, 'total' => 18650000, 'urut' => 1, 'ket' => ''],
            ['order_id' => 'OR-26-X-C002-006', 'item' => 'Dafi Black SKT 16', 'jenis' => 'SKT', 'komponen' => 'Inner',  'isi' => '16', 'bahan' => 'Ivory', 'finishing' => 'Lem, Cutting',                  'order_qty' => 50000, 'actual_qty' => 50000, 'harga' => 57,  'total' => 2850000,  'urut' => 2, 'ket' => ''],
        ];

        // 17 Finance Rows (Total Revenue = Rp 728.048.266)
        $financeMap = [
            'OR-26-I-C001-001'   => ['revenue' => 46196120,  'cost' => 39266702,    'profit' => 6929418,    'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 46196120, 'pel_date' => 46050, 'status' => 'PAID',    'paid' => 46196120, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-II-C004-001'  => ['revenue' => 6887500,   'cost' => 5854375,     'profit' => 1033125,    'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 6887500,  'pel_date' => 46081, 'status' => 'PAID',    'paid' => 6887500,  'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-II-C005-001'  => ['revenue' => 13090000,  'cost' => 11126500,    'profit' => 1963500,    'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 13090000, 'pel_date' => 46085, 'status' => 'PAID',    'paid' => 17895600, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => 'Pelunasan penuh sesuai Finance Macro'],
            'OR-26-II-C003-001'  => ['revenue' => 39111750,  'cost' => 33244987.5,  'profit' => 5866762.5,  'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 39111750, 'pel_date' => 46093, 'status' => 'PAID',    'paid' => 39111750, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-III-C008-001' => ['revenue' => 7130000,   'cost' => 6060500,     'profit' => 1069500,    'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 7130000,  'pel_date' => 46120, 'status' => 'PAID',    'paid' => 13090000, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-III-C002-001' => ['revenue' => 13090000,  'cost' => 11126500,    'profit' => 1963500,    'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 13090000, 'pel_date' => 46168, 'status' => 'PAID',    'paid' => 17900000, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-IV-C005-002'  => ['revenue' => 17895600,  'cost' => 15211260,    'profit' => 2684340,    'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 17895600, 'pel_date' => 46155, 'status' => 'PAID',    'paid' => 17900000, 'out' => 0,         'bank' => 'BRI TTC', 'notes' => ''],
            'OR-26-IV-C002-002'  => ['revenue' => 15289345,  'cost' => 12995943.25, 'profit' => 2293401.75, 'dp' => 10000000, 'dp_date' => 46138, 'term2' => 0,        't2_date' => null,  'pelunasan' => 5289345,  'pel_date' => 46186, 'status' => 'PAID',    'paid' => 15289345, 'out' => 0,         'bank' => 'BRI TTC', 'notes' => ''],
            'OR-26-IV-C005-003'  => ['revenue' => 38635200,  'cost' => 32839920,    'profit' => 5795280,    'dp' => 19317600, 'dp_date' => 46238, 'term2' => 0,        't2_date' => null,  'pelunasan' => 19317600, 'pel_date' => 46241, 'status' => 'PAID',    'paid' => 38635200, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-V-C006-001'   => ['revenue' => 33939200,  'cost' => 28848320,    'profit' => 5090880,    'dp' => 10000000, 'dp_date' => 46162, 'term2' => 0,        't2_date' => null,  'pelunasan' => 0,        'pel_date' => null,  'status' => 'PARTIAL', 'paid' => 10000000, 'out' => 23939200,  'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-V-C007-001'   => ['revenue' => 37607150,  'cost' => 31966077.5,  'profit' => 5641072.5,  'dp' => 4650000,  'dp_date' => 46162, 'term2' => 0,        't2_date' => null,  'pelunasan' => 27500000, 'pel_date' => 46176, 'status' => 'PARTIAL', 'paid' => 32150000, 'out' => 5457150,   'bank' => 'BCA TTC', 'notes' => '(30%) Kompensasi'],
            'OR-26-VI-C002-003'  => ['revenue' => 5863000,   'cost' => 4983550,     'profit' => 879450,     'dp' => 0,        'dp_date' => 46211, 'term2' => 0,        't2_date' => null,  'pelunasan' => 5863000,  'pel_date' => 46211, 'status' => 'PAID',    'paid' => 5863000,  'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-VI-C002-004'  => ['revenue' => 11192541,  'cost' => 9513659.85,  'profit' => 1678881.15, 'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 11192541, 'pel_date' => 46262, 'status' => 'PAID',    'paid' => 11192541, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-VI-C006-002'  => ['revenue' => 13360000,  'cost' => 11356000,    'profit' => 2004000,    'dp' => 2000000,  'dp_date' => 46198, 'term2' => 0,        't2_date' => null,  'pelunasan' => 7000000,  'pel_date' => 46210, 'status' => 'PARTIAL', 'paid' => 9000000,  'out' => 4360000,   'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-VII-C005-004' => ['revenue' => 37606860,  'cost' => 31965831,    'profit' => 5641029,    'dp' => 18803430, 'dp_date' => 46273, 'term2' => 0,        't2_date' => null,  'pelunasan' => 18803430, 'pel_date' => 46323, 'status' => 'PAID',    'paid' => 37606860, 'out' => 0,         'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-VII-C006-003' => ['revenue' => 46154000,  'cost' => 39230900,    'profit' => 6923100,    'dp' => 10000000, 'dp_date' => 46238, 'term2' => 10000000, 't2_date' => 46242, 'pelunasan' => 13000000, 'pel_date' => 46274, 'status' => 'PARTIAL', 'paid' => 33000000, 'out' => 13154000,  'bank' => 'BCA TTC', 'notes' => ''],
            'OR-26-X-C010-001'   => ['revenue' => 345000000, 'cost' => 293250000,   'profit' => 51750000,   'dp' => 0,        'dp_date' => null,  'term2' => 0,        't2_date' => null,  'pelunasan' => 0,        'pel_date' => null,  'status' => 'UNPAID',  'paid' => 0,        'out' => 345000000, 'bank' => 'BCA TTC', 'notes' => ''],
        ];

        // 6 Commission Rows dari Sheet Commission & DOC_Comm (COM-26-...)
        $commissions = [
            ['no' => 'COM-26-IV-C002-002',  'order_id' => 'OR-26-IV-C002-002',  'cust' => 'PT. CATUR WIRATAMA TOBACCO', 'summary' => 'SKT DAFI',          'date' => 46129, 'revenue' => 15289345, 'qty' => 86645,  'adj_type' => 'PER UNIT', 'adj_unit' => 10, 'total_adj' => 866450, 'rev_af_adj' => 14422895, 'mkt_comm' => 288457.90, 'net_rev' => 14134437.10],
            ['no' => 'COM-26-VI-C002-003',  'order_id' => 'OR-26-VI-C002-003',  'cust' => 'PT. CATUR WIRATAMA TOBACCO', 'summary' => 'AMBRI DAFI',        'date' => 46190, 'revenue' => 5863000,  'qty' => 533000, 'adj_type' => 'PER UNIT', 'adj_unit' => 1,  'total_adj' => 533000, 'rev_af_adj' => 5330000,  'mkt_comm' => 106600.00, 'net_rev' => 5223400.00],
            ['no' => 'COM-26-IV-C005-003',  'order_id' => 'OR-26-IV-C005-003',  'cust' => 'PR. JUMALI',                 'summary' => 'SKT PDW 88',        'date' => 46135, 'revenue' => 38635200, 'qty' => 99850,  'adj_type' => 'NONE',     'adj_unit' => 0,  'total_adj' => 0,      'rev_af_adj' => 38635200, 'mkt_comm' => 772704.00, 'net_rev' => 37862496.00],
            ['no' => 'COM-26-VI-C002-004',  'order_id' => 'OR-26-VI-C002-004',  'cust' => 'PT. CATUR WIRATAMA TOBACCO', 'summary' => 'SKT DAFI 16',       'date' => 46200, 'revenue' => 11192541, 'qty' => 58917,  'adj_type' => 'PER UNIT', 'adj_unit' => 10, 'total_adj' => 589170, 'rev_af_adj' => 10603371, 'mkt_comm' => 212067.42, 'net_rev' => 10391303.58],
            ['no' => 'COM-26-VI-C006-002',  'order_id' => 'OR-26-VI-C006-002',  'cust' => 'P. ADI',                     'summary' => 'BARBARA',           'date' => 46198, 'revenue' => 13360000, 'qty' => 33400,  'adj_type' => 'NONE',     'adj_unit' => 0,  'total_adj' => 0,      'rev_af_adj' => 13360000, 'mkt_comm' => 267200.00, 'net_rev' => 13092800.00],
            ['no' => 'COM-26-VII-C005-004', 'order_id' => 'OR-26-VII-C005-004', 'cust' => 'PR. JUMALI',                 'summary' => 'ETIKET VULLARISO',  'date' => 46212, 'revenue' => 37606860, 'qty' => 97380,  'adj_type' => 'NONE',     'adj_unit' => 0,  'total_adj' => 0,      'rev_af_adj' => 37606860, 'mkt_comm' => 752137.20, 'net_rev' => 36854722.80],
        ];

        /*
        |--------------------------------------------------------------------------
        | 5. SIAPKAN MASTER COA & SALDO AWAL NERACA (100% SEIMBANG: DEBIT == KREDIT)
        |--------------------------------------------------------------------------
        */
        $coaByCode = fn (string $code): ?MasterCOA => MasterCOA::where('kode_akun', $code)
            ->orWhere('kode_akun', 'like', $code . '%')
            ->orderByRaw('CASE WHEN kode_akun = ? THEN 0 ELSE 1 END', [$code])
            ->first();

        $coaBri         = $coaByCode('1111'); // Bank BRI - Operasional (BRI TTC)
        $coaBca         = $coaByCode('1112'); // Bank BCA - Operasional (BCA TTC)
        $coaPetty       = $coaByCode('1116'); // Petty Cash (Cash on Hand)
        $coaPiutang     = $coaByCode('1121'); // Piutang - Barang Jadi IDR
        $coaPersBaku    = $coaByCode('1141'); // Persediaan Bahan Baku
        $coaPersWip     = $coaByCode('1142'); // Persediaan Barang Setengah Jadi
        $coaPersJadi    = $coaByCode('1143'); // Persediaan Barang Jadi
        $coaPersSpare   = $coaByCode('1144'); // Persediaan Alat & Sparepart
        $coaPpnMasuk    = $coaByCode('1171'); // PPN Masukan
        $coaTanah       = $coaByCode('1211');
        $coaAkumBangun  = $coaByCode('1212');
        $coaMesin       = $coaByCode('1221');
        $coaAkumMesin   = $coaByCode('1222');
        $coaKendaraan   = $coaByCode('1231');
        $coaAkumKend    = $coaByCode('1232');
        $coaFurniture   = $coaByCode('1241');
        $coaAkumFurn    = $coaByCode('1242');
        $coaUtangBaku   = $coaByCode('2111');
        $coaUtangPpn    = $coaByCode('2121');
        $coaUtangPph21  = $coaByCode('2122');
        $coaUtangGaji   = $coaByCode('2131');
        $coaUtangUtil   = $coaByCode('2132');
        $coaUangMuka    = $coaByCode('2151');
        $coaUtangBankJp = $coaByCode('2210');
        $coaUtangLease  = $coaByCode('2220');
        $coaModal       = $coaByCode('3100');
        $coaLabaDitahan = $coaByCode('3200');
        $coaPenjJadi    = $coaByCode('4101');
        $coaHppBahan    = $coaByCode('5101');
        $coaBebanSales  = $coaByCode('6101');

        DB::transaction(function () use (
            $treasury,
            $customerMap,
            $orderHeaders,
            $orderDetails,
            $financeMap,
            $commissions,
            $satuanPcs,
            $typeMap,
            $rawMaterialMap,
            $deptPrinting,
            $mesinSm74,
            $mesinFlexo,
            $mesinGto52,
            $operatorId,
            $karyawanId,
            $userId,
            $metodeTransfer,
            $metodeTunai,
            $coaBri,
            $coaBca,
            $coaPetty,
            $coaPiutang,
            $coaPersBaku,
            $coaPersWip,
            $coaPersJadi,
            $coaPersSpare,
            $coaPpnMasuk,
            $coaTanah,
            $coaAkumBangun,
            $coaMesin,
            $coaAkumMesin,
            $coaKendaraan,
            $coaAkumKend,
            $coaFurniture,
            $coaAkumFurn,
            $coaUtangBaku,
            $coaUtangPpn,
            $coaUtangPph21,
            $coaUtangGaji,
            $coaUtangUtil,
            $coaUangMuka,
            $coaUtangBankJp,
            $coaUtangLease,
            $coaModal,
            $coaLabaDitahan,
            $coaPenjJadi,
            $coaHppBahan,
            $coaBebanSales
        ) {
            MasterCOA::query()->update([
                'saldo_debit'  => 0,
                'saldo_kredit' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 6. LOOP 19 ORDER HEADER & 47 ORDER DETAIL:
            |    - FinishGoodItem + BOM (FinishGoodItemMaterial)
            |    - ApprovalForm + ApprovalFormItem
            |    - OrderConfirmation + OrderConfirmationItem
            |    - SalesOrder + MasterSpk + Printing + Packaging + SuratJalan
            |    - Invoice + InvoiceItem + InvPayment + Jurnal HPP 85%
            |--------------------------------------------------------------------------
            */
            $detailsByOrder = collect($orderDetails)->groupBy('order_id');
            $fgCounter = 1;

            foreach ($orderHeaders as $idx => $oh) {
                $orderId = $oh['order_id'];
                $suffix = substr($orderId, 3); // 26-I-C001-001
                $afNo = 'AF-' . $suffix;
                $scNo = 'SC-' . $suffix;
                $spkNo = 'SPK-' . $suffix;
                $sjNo = 'SJ-' . $suffix;
                $invNo = 'INV-' . $suffix;

                $orderDate = $this->excelDateToString($oh['date']);
                $customer = $customerMap[$oh['cust']];
                $items = $detailsByOrder->get($orderId, collect());
                $fin = $financeMap[$orderId] ?? null;

                // 6A. Buat ApprovalForm
                $afStatus = ($oh['appr'] === 'Y' || $oh['conf'] === 'Y') ? 'approved' : 'draft';
                $af = ApprovalForm::create([
                    'order_id'            => $orderId,
                    'form_no'             => $afNo,
                    'customer_id'         => $customer->id,
                    'tanggal'             => $orderDate,
                    'lead_time'           => $oh['lead'] . ' Hari Kerja',
                    'catatan_produksi'    => 'Order Summary: ' . $oh['summary'] . ' | PIC Marketing: ' . $oh['pic'],
                    'status'              => $afStatus,
                    'disetujui_marketing' => $oh['pic'],
                    'disetujui_produksi'  => 'PPIC TTC',
                    'created_by'          => $userId,
                ]);

                // 6B. Buat OrderConfirmation
                $ocStatus = $fin ? 'approved' : ($oh['conf'] === 'Y' ? 'approved' : 'sent');
                $oc = OrderConfirmation::create([
                    'approval_form_id'   => $af->id,
                    'order_id'           => $orderId,
                    'doc_no'             => $scNo,
                    'customer_id'        => $customer->id,
                    'tanggal'            => $orderDate,
                    'lead_time'          => $oh['lead'] . ' Hari Kerja',
                    'subtotal'           => 0,
                    'diskon'             => 0,
                    'grand_total'        => 0,
                    'terms_conditions'   => OrderConfirmation::defaultTermsConditions(),
                    'catatan'            => 'Order Summary: ' . $oh['summary'] . ($fin && $fin['notes'] ? ' (' . $fin['notes'] . ')' : ''),
                    'status'             => $ocStatus,
                    'disetujui_customer' => $customer->nama_pic ?: $customer->nama_customer,
                    'diketahui_ttc'      => $oh['pic'],
                    'created_by'         => $userId,
                ]);

                $primaryFg = null;
                $createdOcItems = [];

                foreach ($items as $d) {
                    $bahanKey = trim((string) ($d['bahan'] ?: 'Kertas'));
                    $rawSubstrate = $rawMaterialMap[$bahanKey] ?? $rawMaterialMap['Ivory'];
                    $rawTinta = $rawMaterialMap['Tinta'];
                    $typeObj = $typeMap[$d['komponen']] ?? $typeMap['Etiket'];

                    $fgName = trim($d['item'] . ' - ' . $d['komponen'] . ' (' . $d['jenis'] . ' ' . $d['isi'] . ')');
                    $fgCode = 'FG-' . $oh['cust'] . '-' . str_pad((string) $fgCounter++, 3, '0', STR_PAD_LEFT);

                    $fg = FinishGoodItem::where('customer_id', $customer->id)
                        ->where('nama_barang', $fgName)
                        ->first();

                    if (! $fg) {
                        $fg = FinishGoodItem::create([
                            'customer_id'          => $customer->id,
                            'type_item_id'         => $typeObj->id,
                            'satuan_id'            => $satuanPcs->id,
                            'kode_material_produk' => $fgCode,
                            'pc_number'            => 'PC-' . $fgCode,
                            'nama_barang'          => $fgName,
                            'deskripsi'            => $d['item'] . ' | Jenis: ' . $d['jenis'] . ' | Komponen: ' . $d['komponen'] . ' | Isi: ' . $d['isi'],
                            'spesifikasi_kertas'   => $bahanKey,
                            'jenis'                => $d['jenis'],
                            'komponen'             => $d['komponen'],
                            'isi'                  => (string) $d['isi'],
                            'warna'                => '4 Warna (CMYK) + Spot',
                            'finishing'            => $d['finishing'] ?: '-',
                            'gramasi'              => $bahanKey === 'Ambri' ? '32' : '250',
                            'up_satu'              => 8,
                            'ukuran_cetak'         => '65 x 100 cm',
                            'ukuran_potong'        => '65 x 50 cm',
                            'kertas_items'         => [
                                [
                                    'nama_kertas'    => $bahanKey,
                                    'bagian_kemasan' => $d['komponen'],
                                    'gramasi'        => $bahanKey === 'Ambri' ? '32' : '250',
                                    'tebal_kertas'   => '0.35',
                                    'up'             => '8',
                                    'ukuran_cetak'   => '65 x 100 cm',
                                    'keterangan'     => $d['finishing'] ?: '-',
                                ],
                            ],
                        ]);

                        // Buat Resep BOM agar Kesiapan BOM = BOM LENGKAP
                        FinishGoodItemMaterial::create([
                            'finish_good_item_id' => $fg->id,
                            'master_item_id'      => $rawSubstrate->id,
                            'departemen_id'       => $deptPrinting->id,
                            'qty'                 => 0.125,
                            'waste'               => 2.0,
                            'keterangan'          => 'Bahan Utama ' . $bahanKey,
                        ]);
                        FinishGoodItemMaterial::create([
                            'finish_good_item_id' => $fg->id,
                            'master_item_id'      => $rawTinta->id,
                            'departemen_id'       => $deptPrinting->id,
                            'qty'                 => 0.02,
                            'waste'               => 1.0,
                            'keterangan'          => 'Tinta Cetak & Finishing (' . ($d['finishing'] ?: 'Standard') . ')',
                        ]);
                    }

                    if (! $primaryFg) {
                        $primaryFg = $fg;
                    }

                    // Qty efektif: gunakan actual_qty bila > 0 agar qty * harga == Total (R) di Bismillah MACRO.xlsm
                    $effectiveQty = (float) ($d['actual_qty'] > 0 ? $d['actual_qty'] : $d['order_qty']);
                    // Khusus OR-26-IV-C002-002 item 3 & 4 (DAFI 20 & INNER DAFI 20) yang di Excel Total = 0 (belum diproduksi)
                    $ocQty = ((float) $d['total'] <= 0 && (float) $d['actual_qty'] <= 0) ? 0.0 : $effectiveQty;
                    $hargaUnit = (float) $d['harga'];
                    $hppUnit = round($hargaUnit * 0.85, 2);
                    $lineTotal = round($ocQty * $hargaUnit, 2);

                    $afItem = ApprovalFormItem::create([
                        'approval_form_id'    => $af->id,
                        'finish_good_item_id' => $fg->id,
                        'item_name'           => $d['item'],
                        'jenis'               => $d['jenis'],
                        'komponen'            => $d['komponen'],
                        'isi'                 => (string) $d['isi'],
                        'kertas'              => $bahanKey,
                        'warna'               => '4 Warna (CMYK)',
                        'finishing'           => $d['finishing'] ?: '-',
                        'qty'                 => (float) $d['order_qty'],
                        'satuan'              => 'PCS',
                        'cek_desain'          => true,
                        'cek_plate'           => true,
                        'cek_produksi'        => true,
                        'cek_qc'              => true,
                    ]);

                    $ocItem = OrderConfirmationItem::create([
                        'order_confirmation_id'  => $oc->id,
                        'approval_form_item_id'  => $afItem->id,
                        'finish_good_item_id'    => $fg->id,
                        'item_name'              => $d['item'],
                        'jenis'                  => $d['jenis'],
                        'komponen'               => $d['komponen'],
                        'isi'                    => (string) $d['isi'],
                        'kertas'                 => $bahanKey,
                        'warna'                  => '4 Warna (CMYK)',
                        'finishing'              => $d['finishing'] ?: '-',
                        'qty'                    => $ocQty,
                        'harga'                  => $hargaUnit,
                        'total'                  => $lineTotal,
                        'satuan'                 => 'PCS',
                        'hpp_per_pcs'            => $hppUnit,
                        'harga_jual_indikatif'   => $hargaUnit,
                        'biaya_bahan'            => round($lineTotal * 0.70, 2),
                        'biaya_proses'           => round($lineTotal * 0.15, 2),
                        'total_biaya_order'      => round($lineTotal * 0.85, 2),
                        'kebutuhan_kertas_kg'    => round($ocQty * 0.015, 2),
                        'kebutuhan_kertas_meter' => round($ocQty * 0.08, 2),
                    ]);

                    $createdOcItems[] = [
                        'detail' => $d,
                        'fg'     => $fg,
                        'ocItem' => $ocItem,
                    ];
                }

                // Recalculate OC totals; jika di sheet Finance ada nilai Revenue spesifik (mis. OR-26-II-C005-001 = 13.090.000), sesuaikan diskon/grand_total
                $oc->recalculateTotals();
                $oc->refresh();
                if ($fin && abs((float) $oc->grand_total - (float) $fin['revenue']) > 1) {
                    $diff = max(0, (float) $oc->subtotal - (float) $fin['revenue']);
                    $oc->updateQuietly([
                        'diskon'      => $diff,
                        'grand_total' => (float) $fin['revenue'],
                    ]);
                }

                // 6C. Buat SalesOrder (no_sales_order = OrderID dari Bismillah MACRO.xlsm)
                $totalActualQty = (float) collect($createdOcItems)->sum(fn ($x) => (float) $x['ocItem']->qty);
                if ($totalActualQty <= 0) {
                    $totalActualQty = (float) collect($items)->sum('order_qty');
                }
                $targetRevenue = $fin ? (float) $fin['revenue'] : (float) $oc->grand_total;
                $effectiveSoHarga = $totalActualQty > 0 ? round($targetRevenue / $totalActualQty, 4) : 0;

                $firstDetail = $items->first();
                $so = SalesOrder::create([
                    'order_confirmation_id' => $oc->id,
                    'itemable_type'         => FinishGoodItem::class,
                    'itemable_id'           => $primaryFg?->id,
                    'kode_material'         => $primaryFg?->kode_material_produk ?? ('FG-' . $oh['cust']),
                    'product_name'          => $oh['summary'],
                    'customer_id'           => $customer->id,
                    'no_sales_order'        => $orderId,
                    'no_po_customer'        => 'PO-' . $suffix,
                    'qty'                   => $totalActualQty,
                    'harga_pcs'             => $effectiveSoHarga,
                    'harga_kirim'           => 0,
                    'mata_uang'             => 'IDR',
                    'syarat_pembayaran'     => '14 Hari',
                    'tanggal_po'            => $orderDate,
                    'klaim_kertas'          => 'not_claim',
                    'dipesan_via'           => 'whatsapp',
                    'tipe_pesanan'          => 'REPEAT ORDER',
                    'toleransi_pengiriman'  => 5,
                    'lead_time'             => $oh['lead'] . ' Hari Kerja',
                    'diskon'                => 0,
                    'jenis'                 => $firstDetail['jenis'] ?? 'SKT',
                    'komponen'              => $firstDetail['komponen'] ?? 'Etiket',
                    'isi'                   => (string) ($firstDetail['isi'] ?? '12'),
                    'kertas'                => $firstDetail['bahan'] ?? 'Ivory',
                    'warna'                 => '4 Warna (CMYK)',
                    'finishing'             => $firstDetail['finishing'] ?? '-',
                    'catatan'               => 'Order Summary: ' . $oh['summary'] . ' (' . $orderId . ') | PIC: ' . $oh['pic'],
                    'catatan_colour_range'  => 'Sesuai Proof Approval ' . $afNo,
                    'order_type'            => 'SPK',
                ]);

                $oc->updateQuietly(['sales_order_id' => $so->id]);

                // 6D. Buat MasterSpk (SPK-26-...)
                $spkStatus = match ($oh['prod']) {
                    'FINISHED', 'DELIVERED' => 'FINISH',
                    default                 => 'ON PROCESS',
                };

                $spk = MasterSpk::create([
                    'no_spk'                   => $spkNo,
                    'kode_ik'                  => str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
                    'sales_order_id'           => $so->id,
                    'production_plan'          => 'Produksi ' . $oh['summary'] . ' - ' . $customer->nama_customer,
                    'status'                   => $spkStatus,
                    'tanggal_po'               => $orderDate,
                    'tanggal_estimasi_selesai' => Carbon::parse($orderDate)->addDays((int) $oh['lead'])->toDateString(),
                    'up_satu'                  => 8,
                    'ukuran_potong'            => '65 x 50 cm',
                    'ukuran_cetak'             => '65 x 100 cm',
                    'spesifikasi_kertas'       => $firstDetail['bahan'] ?? 'Ivory',
                ]);

                // 6E. Untuk 17 Order yang masuk ke Finance Sheet, buat Printing, Packaging, Surat Jalan, Invoice & Payment
                if ($fin) {
                    $detailSum = (float) collect($createdOcItems)->sum(fn ($x) => (float) $x['ocItem']->total);
                    $ratio = ($detailSum > 0 && abs($detailSum - (float) $fin['revenue']) > 1)
                        ? ((float) $fin['revenue'] / $detailSum)
                        : 1.0;

                    foreach ($createdOcItems as $ci) {
                        $d = $ci['detail'];
                        $ocIt = $ci['ocItem'];
                        $qtyGood = (float) $ocIt->qty;
                        if ($qtyGood <= 0) {
                            continue;
                        }

                        // Jika total order disesuaikan di sheet Finance (OR-26-II-C005-001), sesuaikan harga OC item agar qty * harga == Finance Revenue
                        if (abs($ratio - 1.0) > 0.0001) {
                            $adjHarga = round((float) $ocIt->harga * $ratio, 4);
                            $adjHpp = round($adjHarga * 0.85, 4);
                            $ocIt->updateQuietly([
                                'harga'       => $adjHarga,
                                'hpp_per_pcs' => $adjHpp,
                                'total'       => round($qtyGood * $adjHarga, 2),
                            ]);
                        } else {
                            // Pastikan presisi hpp_per_pcs = 85% dari harga
                            $ocIt->updateQuietly([
                                'hpp_per_pcs' => round((float) $ocIt->harga * 0.85, 4),
                            ]);
                        }

                        // Pilih Mesin Cetak sesuai jenis bahan/komponen agar ke-3 mesin (SM 74, FLEXO, GTO 52) terisi proporsional
                        $komp = strtolower((string) $d['komponen']);
                        $jenis = strtolower((string) $d['jenis']);
                        if (in_array($komp, ['ambri', 'grenjeng', 'opp pack', 'opp sloop', 'label', 'cylinder'], true)) {
                            $targetMesin = $mesinFlexo;
                            $prosesPrt = 'color-printing';
                        } elseif (in_array($komp, ['sloop', 'ctp', 'kertas'], true) || $jenis === 'hlp') {
                            $targetMesin = $mesinGto52;
                            $prosesPrt = 'color-printing';
                        } else {
                            $targetMesin = $mesinSm74;
                            $prosesPrt = stripos((string) $d['finishing'], 'Spot UV') !== false ? 'spot-uv' : 'color-printing';
                        }

                        $wasteQty = max(0, (int) round($qtyGood * 0.005)); // Waste 0.5%

                        Printing::create([
                            'spk_id'          => $spk->id,
                            'mesin_id'        => $targetMesin->id,
                            'operator_id'     => $operatorId,
                            'tanggal_entri'   => Carbon::parse($orderDate)->addDays(3)->toDateString(),
                            'proses_printing' => $prosesPrt,
                            'tahap_printing'  => 'proses_cetak',
                            'hasil_baik'      => (int) round($qtyGood),
                            'hasil_rusak'     => $wasteQty,
                            'semi_waste'      => 0,
                            'keterangan_spk'  => sprintf(
                                '#ITEM:%d %s - %s (%s %s) [%s]',
                                $ocIt->id,
                                $d['item'],
                                $d['komponen'],
                                $d['jenis'],
                                $d['isi'],
                                $orderId
                            ),
                        ]);
                    }

                    // Buat Packaging (grand_total = totalActualQty)
                    $intTotalQty = max(1, (int) round($totalActualQty));
                    $pkg = new Packaging([
                        'spk_id'               => $spk->id,
                        'satuan_transfer'      => 'Box',
                        'jenis_transfer'       => 'barang_hasil_baik',
                        'tgl_transfer'         => Carbon::parse($orderDate)->addDays(7)->toDateString(),
                        'jumlah_satuan_penuh'  => 1,
                        'qty_per_satuan_penuh' => $intTotalQty,
                        'jumlah_satuan_sisa'   => 0,
                        'qty_per_satuan_sisa'  => 0,
                    ]);
                    $pkg->kode_packaging = 'PKG-' . $suffix;
                    $pkg->save();

                    // Buat Surat Jalan (SJ-26-...)
                    $sjDate = Carbon::parse($orderDate)->addDays(10)->toDateString();
                    $sj = SuratJalan::create([
                        'kode_surat_jalan'    => $sjNo,
                        'sales_order_id'      => $so->id,
                        'spk_id'              => $spk->id,
                        'qty_pengiriman'      => $totalActualQty,
                        'tanggal_surat_jalan' => $sjDate,
                        'alamat_tujuan'       => $customer->alamat_lengkap ?: 'Gudang Customer',
                        'transportasi'        => 'Armada Box TTC',
                        'no_polisi'           => 'N 8821 TTC',
                        'driver'              => 'Pak Slamet',
                        'pengirim'            => 'Logistik TTC',
                        'keterangan'          => 'Pengiriman Order ' . $orderId . ' (' . $oh['summary'] . ')',
                    ]);

                    // Buat Invoice (INV-26-...) sesuai Finance Sheet Bismillah MACRO.xlsm
                    $revAmount = (float) $fin['revenue'];
                    $paidInSheet = (float) $fin['paid'];
                    $outInSheet = (float) $fin['out'];
                    $effectivePaid = max(0.0, round($revAmount - $outInSheet, 2));

                    $invStatus = match ($fin['status']) {
                        'PAID'    => 'Paid',
                        'PARTIAL' => 'Partial',
                        default   => 'Unpaid',
                    };

                    $inv = Invoice::create([
                        'kode_invoice'        => $invNo,
                        'order_id'            => $orderId,
                        'surat_jalan_id'      => $sj->id,
                        'customer_id'         => $customer->id,
                        'sales_order_id'      => $so->id,
                        'no_po_customer'      => 'PO-' . $suffix,
                        'tanggal_invoice'     => $orderDate,
                        'tanggal_jatuh_tempo' => Carbon::parse($orderDate)->addDays(14)->toDateString(),
                        'subtotal'            => $revAmount,
                        'diskon'              => 0,
                        'ppn'                 => 0,
                        'ongkir'              => 0,
                        'uang_muka'           => 0,
                        'grand_total'         => $revAmount,
                        'total_payment'       => $effectivePaid,
                        'sisa_tagihan'        => $outInSheet,
                        'status'              => $invStatus,
                        'is_printed'          => true,
                        'catatan'             => $fin['notes'] ?: ('Tagihan Order ' . $orderId . ' - ' . $oh['summary']),
                        'dibuat_oleh'         => 'Finance TTC',
                        'kepada'              => $customer->nama_customer,
                    ]);

                    // Buat InvoiceItem sesuai rincian Order_Detail
                    foreach ($createdOcItems as $ci) {
                        $d = $ci['detail'];
                        $ocIt = $ci['ocItem'];
                        if ((float) $ocIt->qty <= 0) {
                            continue;
                        }
                        InvoiceItem::create([
                            'invoice_id' => $inv->id,
                            'item'       => $d['item'],
                            'jenis'      => $d['jenis'],
                            'komponen'   => $d['komponen'],
                            'kertas'     => $d['bahan'] ?: '-',
                            'finishing'  => $d['finishing'] ?: '-',
                            'qty'        => (float) $ocIt->qty,
                            'harga'      => (float) $ocIt->harga,
                            'total'      => (float) $ocIt->total,
                            'keterangan' => $d['ket'] ?: '',
                        ]);
                    }

                    // Sync jurnal penjualan Invoice (Debit Piutang 1121 / Kredit Penjualan 4101)
                    $treasury->syncInvoice($inv);

                    // Sync Jurnal HPP 85% (Debit HPP Bahan Baku 5101 / Kredit Utang Produksi 2111)
                    $hppAmount = round((float) $fin['cost'], 2);
                    $coaKreditHpp = $coaUtangBaku ?? $coaPersBaku;
                    if ($hppAmount > 0 && $coaHppBahan && $coaKreditHpp) {
                        $treasury->recordJournalMemorial([
                            'kode_jurnal'  => 'JRN-HPP-' . $suffix,
                            'tanggal'      => $orderDate,
                            'periode'      => substr($orderDate, 0, 7),
                            'gudang'       => 'TTC',
                            'jenis_jurnal' => 'MEMORIAL',
                            'no_referensi' => $spkNo,
                            'karyawan_id'  => $karyawanId,
                            'status'       => 'POSTED',
                            'keterangan'   => 'Beban HPP Produksi (85%) Order ' . $orderId . ' - ' . $oh['summary'],
                            'lines'        => [
                                [
                                    'coa_id'     => $coaHppBahan->id,
                                    'keterangan' => 'HPP Bahan & Produksi Cetak (85%) ' . $orderId . ' (' . $oh['summary'] . ')',
                                    'debit'      => $hppAmount,
                                    'kredit'     => 0,
                                ],
                                [
                                    'coa_id'     => $coaKreditHpp->id,
                                    'keterangan' => 'Beban Pokok Produksi Cetak SPK ' . $spkNo,
                                    'debit'      => 0,
                                    'kredit'     => $hppAmount,
                                ],
                            ],
                        ]);
                    }

                    // Buat InvPayment untuk DP, Term 2, dan Pelunasan sesuai Finance Sheet
                    $bankCoa = ($fin['bank'] === 'BRI TTC') ? ($coaBri ?? $coaBca) : ($coaBca ?? $coaBri);
                    $paymentSlices = [];

                    if ((float) $fin['dp'] > 0) {
                        $paymentSlices[] = [
                            'nominal' => (float) $fin['dp'],
                            'tanggal' => $this->excelDateToString($fin['dp_date'], $orderDate),
                            'ket'     => 'Pembayaran DP Order ' . $orderId . ' (' . $oh['summary'] . ') via ' . $fin['bank'],
                        ];
                    }
                    if ((float) $fin['term2'] > 0) {
                        $paymentSlices[] = [
                            'nominal' => (float) $fin['term2'],
                            'tanggal' => $this->excelDateToString($fin['t2_date'], Carbon::parse($orderDate)->addDays(7)->toDateString()),
                            'ket'     => 'Pembayaran Termin 2 Order ' . $orderId . ' (' . $oh['summary'] . ') via ' . $fin['bank'],
                        ];
                    }
                    $remPelunasan = max(0.0, round($effectivePaid - (float) $fin['dp'] - (float) $fin['term2'], 2));
                    if ($remPelunasan > 0) {
                        $paymentSlices[] = [
                            'nominal' => $remPelunasan,
                            'tanggal' => $this->excelDateToString($fin['pel_date'], Carbon::parse($orderDate)->addDays(14)->toDateString()),
                            'ket'     => 'Pelunasan Invoice ' . $invNo . ' (' . $oh['summary'] . ')' . ($paidInSheet > $revAmount ? ' [Total Diterima di Excel: Rp ' . number_format($paidInSheet, 0, ',', '.') . ']' : ''),
                        ];
                    }

                    foreach ($paymentSlices as $ps) {
                        $ip = InvPayment::create([
                            'invoice_id'      => $inv->id,
                            'metode_bayar_id' => $metodeTransfer->id,
                            'coa_id'          => $bankCoa?->id,
                            'karyawan_id'     => $karyawanId,
                            'tanggal_bayar'   => $ps['tanggal'],
                            'nominal_bayar'   => $ps['nominal'],
                            'keterangan'      => $ps['ket'],
                            'status'          => 'SUCCESS',
                        ]);
                        $treasury->syncInvPayment($ip);
                    }

                    // Pastikan status & sisa_tagihan invoice persis sama dengan Finance Sheet
                    $inv->updateQuietly([
                        'total_payment' => $effectivePaid,
                        'sisa_tagihan'  => $outInSheet,
                        'status'        => $invStatus,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 7. SEED 6 TRANSAKSI KOMISI MARKETING DARI SHEET COMMISSION
            |--------------------------------------------------------------------------
            */
            foreach ($commissions as $com) {
                $comDate = $this->excelDateToString($com['date']);
                $treasury->recordOperasional([
                    'kode_transaksi'    => $com['no'],
                    'gudang'            => 'TTC',
                    'periode'           => substr($comDate, 0, 7),
                    'tanggal_transaksi' => $comDate,
                    'account_kas_id'    => $coaBca?->id,
                    'account_beban_id'  => $coaBebanSales?->id,
                    'karyawan_id'       => $karyawanId,
                    'nominal'           => round((float) $com['mkt_comm'], 2),
                    'nopol'             => null,
                    'odometer'          => null,
                    'mesin'             => null,
                    'jenis'             => 'KOMISI MARKETING',
                    'kode'              => $com['order_id'],
                    'keterangan'        => sprintf(
                        'Komisi Marketing (2%%) Order %s - %s (%s) | Rev After Adj: Rp %s',
                        $com['order_id'],
                        $com['summary'],
                        $com['cust'],
                        number_format((float) $com['rev_af_adj'], 0, ',', '.')
                    ),
                    'status'            => 'ACTIVE',
                ]);
            }
        });
    }
}
