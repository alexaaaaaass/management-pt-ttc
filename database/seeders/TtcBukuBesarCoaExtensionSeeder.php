<?php

namespace Database\Seeders;

use App\Models\CoaClass;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use Illuminate\Database\Seeder;

class TtcBukuBesarCoaExtensionSeeder extends Seeder
{
    public function run(): void
    {
        $classByPrefix = [
            '1' => CoaClass::where('code', '1')->orWhere('code', 'CL_ASET')->value('id') ?? CoaClass::orderBy('id')->value('id'),
            '2' => CoaClass::where('code', '2')->orWhere('code', 'CL_HUTANG')->value('id') ?? CoaClass::orderBy('id')->value('id'),
            '3' => CoaClass::where('code', '3')->orWhere('code', 'CL_MODAL')->value('id') ?? CoaClass::orderBy('id')->value('id'),
            '4' => CoaClass::where('code', '4')->orWhere('code', 'CL_PENDAPATAN')->value('id') ?? CoaClass::orderBy('id')->value('id'),
            '5' => CoaClass::where('code', '5')->orWhere('code', 'CL_HPP')->value('id') ?? CoaClass::orderBy('id')->value('id'),
            '6' => CoaClass::where('code', '6')->orWhere('code', 'CL_BEBAN')->value('id') ?? CoaClass::orderBy('id')->value('id'),
        ];

        $karyawanId = Karyawan::orderBy('id')->value('id');
        $periode = now()->format('Y-m');

        $newAccounts = [
            // Bank & Aset Lancar
            [
                'kode_akun'           => '101.103',
                'nama_akun'           => 'REKENING PT (BANK BRI GIRO)',
                'parent'              => '101',
                'account_type'        => 'Asset',
                'account_class'       => 'Current - Bank',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '101.104',
                'nama_akun'           => 'REKENING OPERASIONAL (BANK BRI FENDI)',
                'parent'              => '101',
                'account_type'        => 'Asset',
                'account_class'       => 'Current - Bank',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '101.204',
                'nama_akun'           => 'PIUTANG / KASBON KARYAWAN',
                'parent'              => '101',
                'account_type'        => 'Asset',
                'account_class'       => 'Current - Employee Receivable',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ],

            // Aset Tetap
            [
                'kode_akun'           => '102.205',
                'nama_akun'           => 'RENOVASI & LEASEHOLD IMPROVEMENT',
                'parent'              => '102',
                'account_type'        => 'Asset',
                'account_class'       => 'Fixed Asset - Building',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ],

            // Kewajiban
            [
                'kode_akun'           => '202.202',
                'nama_akun'           => 'HUTANG KEPADA PEMILIK (PINJAMAN MODAL)',
                'parent'              => '202',
                'account_type'        => 'Liability',
                'account_class'       => 'Long Term Liability',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Credit',
            ],

            // Ekuitas
            [
                'kode_akun'           => '301.201',
                'nama_akun'           => 'PRIVE / PENGAMBILAN PEMILIK',
                'parent'              => '301',
                'account_type'        => 'Equity',
                'account_class'       => 'Equity',
                'financial_statement' => 'Balance Sheet',
                'normal_balance'      => 'Debit',
            ],

            // Pendapatan Lain-lain
            [
                'kode_akun'           => '403.401',
                'nama_akun'           => 'PENDAPATAN BUNGA BANK',
                'parent'              => '403',
                'account_type'        => 'Revenue',
                'account_class'       => 'Other Income',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Credit',
            ],
            [
                'kode_akun'           => '403.402',
                'nama_akun'           => 'PENDAPATAN LAIN-LAIN',
                'parent'              => '403',
                'account_type'        => 'Revenue',
                'account_class'       => 'Other Income',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Credit',
            ],

            // HPP / Produksi
            [
                'kode_akun'           => '502.302',
                'nama_akun'           => 'BIAYA SUBCONT PRODUKSI',
                'parent'              => '502',
                'account_type'        => 'Expense',
                'account_class'       => 'Cost of Goods Sold - Subcontract',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],

            // Beban Tenaga Kerja & Kesejahteraan
            [
                'kode_akun'           => '602.005',
                'nama_akun'           => 'TUNJANGAN & THR KARYAWAN',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - Payroll',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '602.006',
                'nama_akun'           => 'TRAINING & PENGEMBANGAN KARYAWAN',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - Payroll',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '602.007',
                'nama_akun'           => 'BPJS KETENAGAKERJAAN',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - Payroll',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '602.008',
                'nama_akun'           => 'PROGRAM KESEJAHTERAAN KARYAWAN',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - Payroll',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],

            // Beban Pemeliharaan, Legal, Iuran & Perizinan
            [
                'kode_akun'           => '602.216',
                'nama_akun'           => 'BIAYA PERAWATAN GEDUNG & BANGUNAN',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - Maintenance',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '602.503',
                'nama_akun'           => 'BIAYA PROFESIONAL & LEGAL (AKTA)',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - General Admin',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '602.504',
                'nama_akun'           => 'IURAN KEAMANAN & KEBERSIHAN',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - General Admin',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],
            [
                'kode_akun'           => '602.505',
                'nama_akun'           => 'RETRIBUSI & PERIZINAN',
                'parent'              => '602',
                'account_type'        => 'Expense',
                'account_class'       => 'Operating Expense - Tax & License',
                'financial_statement' => 'Income Statement',
                'normal_balance'      => 'Debit',
            ],
        ];

        foreach ($newAccounts as $acc) {
            $prefix = substr($acc['kode_akun'], 0, 1);

            MasterCOA::firstOrCreate(
                ['kode_akun' => $acc['kode_akun']],
                [
                    'nama_akun'           => $acc['nama_akun'],
                    'coa_class_id'        => $classByPrefix[$prefix] ?? 1,
                    'karyawan_id'         => $karyawanId ?? 1,
                    'parent_code'         => $acc['parent'],
                    'account_type'        => $acc['account_type'],
                    'account_class'       => $acc['account_class'],
                    'financial_statement' => $acc['financial_statement'],
                    'cash_flow_type'      => in_array($acc['account_class'], ['Current - Cash', 'Current - Bank'], true) ? 'Operating' : '-',
                    'normal_balance'      => $acc['normal_balance'],
                    'periode'             => '2026',
                    'gudang'              => 'TTC',
                    'saldo_debit'         => 0.00,
                    'saldo_kredit'        => 0.00,
                    'nominal_default'     => 0.00,
                    'keterangan'          => 'Ekstensi COA Buku Besar TTC 2026',
                    'status'              => 'aktif',
                    'level'               => 3,
                    'posting'             => 'Yes',
                ]
            );
        }
    }
}
