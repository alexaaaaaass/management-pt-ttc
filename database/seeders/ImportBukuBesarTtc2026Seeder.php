<?php

namespace Database\Seeders;

use App\Models\CoaMovement;
use App\Models\MasterCOA;
use App\Models\TransKas;
use App\Models\TransKasBank;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportBukuBesarTtc2026Seeder extends Seeder
{
    /**
     * Pemetaan permanen 39 COA 4-digit dari file SQL Juan -> skema Indigama xxx.xxx (berbasis kode_akun, tanpa hardcoded ID).
     */
    public const COA_CODE_MAP = [
        // Kas & Bank
        '1111' => '101.003', // Bank BRI - Operasional (BRI TABUNGAN) -> KAS BESAR (BANK BRI)
        '1112' => '101.002', // Bank BCA - Operasional (BCA)          -> KAS BESAR (BANK BCA)
        '1113' => '101.104', // Bank Lain - Efendi (BRI FENDI)        -> REKENING OPERASIONAL (BANK BRI FENDI)
        '1114' => '101.103', // Bank Lain - Giro (BRI GIRO)           -> REKENING PT (BANK BRI GIRO)
        '1116' => '101.001', // Petty Cash                            -> KAS KECIL

        // Piutang & Aset Tetap
        '1131' => '101.204', // Piutang / Kasbon Karyawan             -> PIUTANG / KASBON KARYAWAN
        '1211' => '102.201', // Tanah & Bangunan (Cicilan Gudang)     -> NILAI BUKU GEDUNG KANTOR
        '1221' => '102.301', // Mesin & Peralatan (Zonten/Listrik)    -> NILAI BUKU MESIN PABRIK
        '1231' => '102.401', // Kendaraan                             -> NILAI BUKU KENDARAAN
        '1241' => '102.302', // Furniture & Fixtures                  -> NILAI BUKU INVENTARIS KANTOR
        '1251' => '103.301', // Bangunan Dalam Pembangunan            -> AKTIVA TETAP DALAM PROSES
        '1261' => '102.205', // Renovasi & Leasehold Improvement      -> RENOVASI & LEASEHOLD IMPROVEMENT

        // Kewajiban & Ekuitas
        '2111' => '201.103', // Utang Usaha - Bahan Baku IDR          -> HUTANG PEMBELIAN BAHAN BAKU
        '2133' => '201.404', // Biaya Lainnya Terutang                -> HUTANG BIAYA LAIN
        '2142' => '202.202', // Hutang kepada Pemilik (Indigama)      -> HUTANG KEPADA PEMILIK (PINJAMAN MODAL)
        '3100' => '301.001', // Modal Awal Pemilik                    -> MODAL SAHAM
        '3500' => '301.201', // Prive / Pengambilan Pemilik           -> PRIVE / PENGAMBILAN PEMILIK

        // Pendapatan & HPP
        '4101' => '401.001', // Penjualan Barang Jadi - IDR           -> PENJUALAN PRODUK
        '4510' => '403.401', // Pendapatan Bunga                      -> PENDAPATAN BUNGA BANK
        '4540' => '403.402', // Pendapatan Lain-lain                  -> PENDAPATAN LAIN-LAIN
        '5101' => '502.001', // Pemakaian Bahan Baku - Produksi       -> BIAYA BAHAN BAKU KERTAS
        '5308' => '502.302', // Biaya Subcont Produksi                -> BIAYA SUBCONT PRODUKSI

        // Beban Penjualan, Operasional, & Tenaga Kerja
        '6104' => '602.204', // Biaya Pengiriman Barang               -> BIAYA KIRIM BARANG
        '6108' => '601.002', // Komisi Marketing / Referral           -> KOMISI PENJUALAN
        '6203' => '602.301', // Listrik & Air Kantor                  -> BIAYA LISTRIK
        '6205' => '602.503', // Biaya Profesional (Akta PT)           -> BIAYA PROFESIONAL & LEGAL (AKTA)
        '6207' => '602.304', // Telepon & Internet                    -> BIAYA INTERNET
        '6209' => '602.201', // Bahan Bakar dan Etoll Kendaraan       -> BIAYA BBM
        '6212' => '602.504', // Iuran & Keanggotaan                   -> IURAN KEAMANAN & KEBERSIHAN
        '6214' => '603.101', // Biaya Administrasi Bank               -> BEBAN ADMIN BANK
        '6215' => '602.216', // Perawatan Gedung/Bangunan             -> BIAYA PERAWATAN GEDUNG & BANGUNAN
        '6216' => '602.214', // Perlengkapan dan Furniture            -> BIAYA RUMAH TANGGA KANTOR
        '6301' => '602.001', // Gaji Karyawan Umum                    -> GAJI KARYAWAN KONTRAK
        '6302' => '602.005', // Tunjangan Karyawan (THR 2026)         -> TUNJANGAN & THR KARYAWAN
        '6305' => '602.006', // Training & Pengembangan               -> TRAINING & PENGEMBANGAN KARYAWAN
        '6402' => '602.505', // Retribusi & Perizinan                 -> RETRIBUSI & PERIZINAN
        '6502' => '602.007', // Kontribusi BPJS Ketenagakerjaan       -> BPJS KETENAGAKERJAAN
        '6503' => '602.008', // Program Kesejahteraan Karyawan        -> PROGRAM KESEJAHTERAAN KARYAWAN
        '6610' => '602.210', // Biaya Miscellaneous / Reimburse       -> BIAYA OPERASIONAL
    ];

    /**
     * Pemetaan ID lama di file SQL Juan -> kode 4-digit SQL (diverifikasi langsung dari komentar double-entry).
     */
    public const LEGACY_ID_TO_SQL_CODE = [
        309 => '1111',
        310 => '1112',
        311 => '1113',
        312 => '1114',
        314 => '1116',
        329 => '1131',
        349 => '1211',
        355 => '1221',
        361 => '1231',
        367 => '1241',
        373 => '1251',
        378 => '1261',
        395 => '2111',
        415 => '2133',
        422 => '2142',
        439 => '3100',
        443 => '3500',
        452 => '4101',
        467 => '4510',
        470 => '4540',
        477 => '5101',
        492 => '5308',
        504 => '6104',
        508 => '6108',
        512 => '6203',
        514 => '6205',
        516 => '6207',
        518 => '6209',
        521 => '6212',
        523 => '6214',
        524 => '6215',
        525 => '6216',
        528 => '6301',
        529 => '6302',
        532 => '6305',
        535 => '6402',
        538 => '6502',
        539 => '6503',
        542 => '6610',
    ];

    /**
     * Opsi 1 (Anti Double-Counting): 12 transaksi penjualan (4101) di SQL yang sudah tercatat persis
     * pada inv_payments existing (total Rp 143.836.636) -> dilewati agar saldo bank & omzet tidak dobel.
     */
    public const SKIPPED_DUPLICATE_SALES_CODES = [
        'BNK26-0094' => 'Overlap PAY#4 (INV-26-II-C003-001 ETIKET FLORENT — bagian 1/2 dari Rp 39.111.750)',
        'BNK26-0095' => 'Overlap PAY#2 (INV-26-II-C004-001 INNER JB — 2026-02-28)',
        'BNK26-0107' => 'Overlap PAY#6 (INV-26-III-C002-001 AMBRI DAFI — bagian 1/2 dari Rp 13.090.000)',
        'BNK26-0118' => 'Overlap PAY#6 (INV-26-III-C002-001 AMBRI DAFI — bagian 2/2 dari Rp 13.090.000)',
        'BNK26-0135' => 'Overlap PAY#8 (INV-26-IV-C002-002 SKT DAFI — DP Rp 10.000.000)',
        'BNK26-0189' => 'Overlap PAY#14 (INV-26-V-C007-001 Apple — 2026-06-03 Rp 27.500.000)',
        'BNK26-0191' => 'Overlap PAY#4 (INV-26-II-C003-001 ETIKET FLORENT — bagian 2/2 dari Rp 39.111.750)',
        'BNK26-0193' => 'Overlap PAY#7 (INV-26-IV-C005-002 SKM BLUE SEA — Rp 17.900.000)',
        'BNK26-0232' => 'Overlap PAY#9 (INV-26-IV-C002-002 SKT DAFI — Pelunasan Rp 5.289.345)',
        'BNK26-0248' => 'Overlap PAY#18 (INV-26-VI-C006-002 BARBARA — Pelunasan Rp 7.000.000)',
        'BNK26-0264' => 'Overlap PAY#15 (INV-26-VI-C002-003 AMBRI DAFI — 2026-07-08 Rp 5.863.000)',
        'BNK26-0349' => 'Overlap PAY#16 (INV-26-VI-C002-004 SKT DAFI 16 — 2026-08-28 Rp 11.192.541)',
    ];

    public function run(): void
    {
        $summary = $this->executeImport(skipDuplicateSales: true);

        if ($this->command) {
            $this->command->info(sprintf(
                'Import Buku Besar TTC 2026 selesai: %d Bank + %d Kas = %d transaksi (%d ledger rows), dilewati: %d duplikat inv_payments, %d sudah ada.',
                $summary['inserted_bank'],
                $summary['inserted_kas'],
                $summary['inserted_bank'] + $summary['inserted_kas'],
                $summary['inserted_movements'],
                $summary['skipped_overlap'],
                $summary['skipped_existing'],
            ));
        }
    }

    /**
     * Eksekusi migrasi idempoten berbasis kode_akun (bukan hardcoded ID).
     */
    public function executeImport(bool $skipDuplicateSales = true, ?string $sqlFilePath = null): array
    {
        // 1. Pastikan 17 akun COA tambahan Indigama sudah tersedia
        (new TtcBukuBesarCoaExtensionSeeder())->run();

        // 2. Resolusi seluruh target kode_akun -> master_coas.id live
        $liveCoas = MasterCOA::whereIn('kode_akun', array_values(self::COA_CODE_MAP))
            ->get()
            ->keyBy('kode_akun');

        $legacyIdToLiveCoaId = [];
        foreach (self::LEGACY_ID_TO_SQL_CODE as $legacyId => $sqlCode) {
            $targetCode = self::COA_CODE_MAP[$sqlCode] ?? null;
            if (! $targetCode || ! $liveCoas->has($targetCode)) {
                throw new RuntimeException("Target COA '{$targetCode}' (dari kode SQL '{$sqlCode}') tidak ditemukan di master_coas.");
            }
            $legacyIdToLiveCoaId[$legacyId] = (int) $liveCoas->get($targetCode)->id;
        }

        // 3. Parse file SQL sumber
        $sourcePath = $sqlFilePath ?: database_path('import_buku_besar_ttc_2026.sql');
        if (! is_file($sourcePath)) {
            throw new RuntimeException("File SQL sumber tidak ditemukan: {$sourcePath}");
        }

        $transactions = $this->parseSourceSql($sourcePath);
        $createdByUserId = User::orderBy('id')->value('id');

        $insertedBank = 0;
        $insertedKas = 0;
        $insertedMovements = 0;
        $skippedOverlap = 0;
        $skippedExisting = 0;
        $affectedCoaIds = [];

        DB::transaction(function () use (
            $transactions,
            $legacyIdToLiveCoaId,
            $skipDuplicateSales,
            $createdByUserId,
            &$insertedBank,
            &$insertedKas,
            &$insertedMovements,
            &$skippedOverlap,
            &$skippedExisting,
            &$affectedCoaIds
        ) {
            foreach ($transactions as $tx) {
                $kode = $tx['kode_transaksi'];

                // Lewati 12 penjualan overlap dengan inv_payments existing (Opsi 1)
                if ($skipDuplicateSales && array_key_exists($kode, self::SKIPPED_DUPLICATE_SALES_CODES)) {
                    $skippedOverlap++;
                    continue;
                }

                // Idempoten: jangan dobel jika kode_transaksi sudah masuk sebelumnya
                if ($tx['table'] === 'trans_kas_banks') {
                    if (TransKasBank::where('kode_transaksi', $kode)->exists()) {
                        $skippedExisting++;
                        continue;
                    }

                    $bankCoaId = $this->resolveLegacyId((int) $tx['row']['account_bank_id'], $legacyIdToLiveCoaId);
                    $lawanCoaId = $this->resolveLegacyId((int) $tx['row']['account_lawan_id'], $legacyIdToLiveCoaId);

                    $refId = DB::table('trans_kas_banks')->insertGetId([
                        'kode_transaksi'    => $kode,
                        'tipe_transaksi'    => $tx['row']['tipe_transaksi'],
                        'gudang'            => $tx['row']['gudang'],
                        'tanggal_transaksi' => $tx['row']['tanggal_transaksi'],
                        'periode'           => $tx['row']['periode'],
                        'account_bank_id'   => $bankCoaId,
                        'account_lawan_id'  => $lawanCoaId,
                        'nominal'           => round((float) $tx['row']['nominal'], 2),
                        'customer_id'       => null,
                        'bank'              => $tx['row']['bank'],
                        'atas_nama'         => null,
                        'no_rekening'       => null,
                        'keterangan'        => $tx['row']['keterangan'],
                        'status'            => $tx['row']['status'] ?: 'ACTIVE',
                        'created_at'        => $tx['row']['created_at'],
                        'updated_at'        => $tx['row']['updated_at'],
                    ]);

                    $insertedBank++;
                } else {
                    if (TransKas::where('kode_transaksi', $kode)->exists()) {
                        $skippedExisting++;
                        continue;
                    }

                    $lawanCoaId = $this->resolveLegacyId((int) $tx['row']['account_bank_id'], $legacyIdToLiveCoaId);
                    $kasCoaId = $this->resolveLegacyId((int) $tx['row']['account_kas_id'], $legacyIdToLiveCoaId);

                    $refId = DB::table('trans_kas')->insertGetId([
                        'kode_transaksi'    => $kode,
                        'tipe_transaksi'    => $tx['row']['tipe_transaksi'],
                        'gudang'            => $tx['row']['gudang'],
                        'tanggal_transaksi' => $tx['row']['tanggal_transaksi'],
                        'periode'           => $tx['row']['periode'],
                        'karyawan_id'       => null,
                        'account_bank_id'   => $lawanCoaId,
                        'account_kas_id'    => $kasCoaId,
                        'customer_id'       => null,
                        'nominal'           => round((float) $tx['row']['nominal'], 2),
                        'status'            => $tx['row']['status'] ?: 'ACTIVE',
                        'keterangan'        => $tx['row']['keterangan'],
                        'created_at'        => $tx['row']['created_at'],
                        'updated_at'        => $tx['row']['updated_at'],
                    ]);

                    $insertedKas++;
                }

                // Insert 2 baris double-entry coa_movements yang sudah diremap
                $movBatch = [];
                foreach ($tx['movements'] as $mov) {
                    $liveCoaId = $this->resolveLegacyId((int) $mov['coa_id'], $legacyIdToLiveCoaId);
                    $affectedCoaIds[$liveCoaId] = true;

                    $movBatch[] = [
                        'coa_id'         => $liveCoaId,
                        'tanggal'        => $mov['tanggal'],
                        'reference_type' => $mov['reference_type'],
                        'reference_id'   => $refId,
                        'reference_no'   => $mov['reference_no'],
                        'movement_type'  => $mov['movement_type'],
                        'nominal'        => round((float) $mov['nominal'], 2),
                        'debit'          => round((float) $mov['debit'], 2),
                        'kredit'         => round((float) $mov['kredit'], 2),
                        'balance_before' => 0.00,
                        'balance_after'  => 0.00,
                        'remarks'        => $mov['remarks'],
                        'created_by'     => $createdByUserId,
                        'created_at'     => $mov['created_at'],
                        'updated_at'     => $mov['updated_at'],
                    ];
                }

                DB::table('coa_movements')->insert($movBatch);
                $insertedMovements += count($movBatch);
            }

            // 4. Hitung ulang saldo berjalan (balance_before & balance_after) kronologis pada seluruh COA terdampak
            $coaIdsList = array_keys($affectedCoaIds);
            if (! empty($coaIdsList)) {
                $placeholders = implode(',', array_fill(0, count($coaIdsList), '?'));

                DB::statement("
                    UPDATE `coa_movements` cm
                    JOIN (
                        SELECT
                            m.id,
                            ROUND(
                                (COALESCE(c.saldo_debit, 0) - COALESCE(c.saldo_kredit, 0))
                                + COALESCE(SUM(m.debit - m.kredit) OVER (
                                    PARTITION BY m.coa_id
                                    ORDER BY m.tanggal ASC, m.id ASC
                                    ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING
                                ), 0),
                                2
                            ) AS calc_before,
                            ROUND(
                                (COALESCE(c.saldo_debit, 0) - COALESCE(c.saldo_kredit, 0))
                                + SUM(m.debit - m.kredit) OVER (
                                    PARTITION BY m.coa_id
                                    ORDER BY m.tanggal ASC, m.id ASC
                                    ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                                ),
                                2
                            ) AS calc_after
                        FROM `coa_movements` m
                        JOIN `master_coas` c ON c.id = m.coa_id
                        WHERE m.coa_id IN ({$placeholders})
                    ) calc ON calc.id = cm.id
                    SET cm.balance_before = calc.calc_before,
                        cm.balance_after  = calc.calc_after
                ", $coaIdsList);
            }
        });

        return [
            'inserted_bank'      => $insertedBank,
            'inserted_kas'       => $insertedKas,
            'inserted_movements' => $insertedMovements,
            'skipped_overlap'    => $skippedOverlap,
            'skipped_existing'   => $skippedExisting,
            'affected_coas'      => count($affectedCoaIds),
        ];
    }

    /**
     * Hasilkan file SQL standalone hasil remapping berbasis kode_akun (tanpa hardcoded ID) untuk dokumentasi/backup.
     */
    public function generateRemappedSqlFile(?string $outputPath = null, bool $skipDuplicateSales = true): string
    {
        $sourcePath = database_path('import_buku_besar_ttc_2026.sql');
        $targetPath = $outputPath ?: database_path('import_buku_besar_ttc_2026_indigama_remapped.sql');
        $transactions = $this->parseSourceSql($sourcePath);

        $out = [];
        $out[] = '-- =============================================================================';
        $out[] = '-- IMPORT BUKU BESAR COA TTC 2026 — REMAPPED KE SKEMA INDIGAMA (xxx.xxx)';
        $out[] = '-- Mode: Opsi A (Remapped via kode_akun, tanpa hardcoded coa_id) + Opsi 1 (Skip 12 penjualan overlap inv_payments)';
        $out[] = '-- Prasyarat: jalankan migration alter_nominal_to_decimal + TtcBukuBesarCoaExtensionSeeder terlebih dahulu.';
        $out[] = '-- =============================================================================';
        $out[] = '';
        $out[] = 'SET NAMES utf8mb4;';
        $out[] = "SET time_zone = '+07:00';";
        $out[] = '';
        $out[] = 'START TRANSACTION;';
        $out[] = '';
        $out[] = '-- Resolusi dinamis coa_id dari master_coas.kode_akun (ZERO hardcoded ID)';

        $varByLegacyId = [];
        foreach (self::LEGACY_ID_TO_SQL_CODE as $legacyId => $sqlCode) {
            $targetCode = self::COA_CODE_MAP[$sqlCode];
            $varName = '@coa_' . str_replace('.', '_', $targetCode);
            $varByLegacyId[$legacyId] = $varName;
            $out[] = "SET {$varName} = (SELECT id FROM `master_coas` WHERE `kode_akun` = '{$targetCode}' LIMIT 1);";
        }
        $out[] = 'SET @import_user_id = (SELECT id FROM `users` ORDER BY id ASC LIMIT 1);';
        $out[] = '';

        $esc = static fn (?string $val): string => $val === null ? 'NULL' : "'" . str_replace(["\\", "'"], ["\\\\", "''"], $val) . "'";
        $usedVars = [];

        foreach ($transactions as $tx) {
            $kode = $tx['kode_transaksi'];
            if ($skipDuplicateSales && array_key_exists($kode, self::SKIPPED_DUPLICATE_SALES_CODES)) {
                $reason = self::SKIPPED_DUPLICATE_SALES_CODES[$kode];
                $out[] = "-- [SKIPPED OPSI 1] {$kode}: {$reason}";
                continue;
            }

            $out[] = $tx['comment'];
            if ($tx['table'] === 'trans_kas_banks') {
                $vBank = $varByLegacyId[(int) $tx['row']['account_bank_id']];
                $vLawan = $varByLegacyId[(int) $tx['row']['account_lawan_id']];
                $usedVars[$vBank] = true;
                $usedVars[$vLawan] = true;
                $r = $tx['row'];
                $out[] = sprintf(
                    "INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES (%s, %s, %s, %s, %s, %s, %s, %.2f, NULL, %s, NULL, NULL, %s, %s, %s, %s);",
                    $esc($kode), $esc($r['tipe_transaksi']), $esc($r['gudang']), $esc($r['tanggal_transaksi']), $esc($r['periode']),
                    $vBank, $vLawan, (float) $r['nominal'], $esc($r['bank']), $esc($r['keterangan']), $esc($r['status']), $esc($r['created_at']), $esc($r['updated_at'])
                );
            } else {
                $vLawan = $varByLegacyId[(int) $tx['row']['account_bank_id']];
                $vKas = $varByLegacyId[(int) $tx['row']['account_kas_id']];
                $usedVars[$vLawan] = true;
                $usedVars[$vKas] = true;
                $r = $tx['row'];
                $out[] = sprintf(
                    "INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES (%s, %s, %s, %s, %s, NULL, %s, %s, NULL, %.2f, %s, %s, %s, %s);",
                    $esc($kode), $esc($r['tipe_transaksi']), $esc($r['gudang']), $esc($r['tanggal_transaksi']), $esc($r['periode']),
                    $vLawan, $vKas, (float) $r['nominal'], $esc($r['status']), $esc($r['keterangan']), $esc($r['created_at']), $esc($r['updated_at'])
                );
            }

            $out[] = 'SET @ref_id = LAST_INSERT_ID();';
            $out[] = 'INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES';

            $movLines = [];
            foreach ($tx['movements'] as $mov) {
                $vCoa = $varByLegacyId[(int) $mov['coa_id']];
                $usedVars[$vCoa] = true;
                $movLines[] = sprintf(
                    "(%s, %s, %s, @ref_id, %s, %s, %.2f, %.2f, %.2f, 0.00, 0.00, %s, @import_user_id, %s, %s)",
                    $vCoa, $esc($mov['tanggal']), $esc($mov['reference_type']), $esc($mov['reference_no']), $esc($mov['movement_type']),
                    (float) $mov['nominal'], (float) $mov['debit'], (float) $mov['kredit'], $esc($mov['remarks']), $esc($mov['created_at']), $esc($mov['updated_at'])
                );
            }
            $out[] = implode(",\n", $movLines) . ';';
            $out[] = '';
        }

        $varList = implode(', ', array_keys($usedVars));
        $out[] = '-- Hitung ulang saldo berjalan (balance_before & balance_after) kronologis';
        $out[] = "UPDATE `coa_movements` cm";
        $out[] = "JOIN (";
        $out[] = "    SELECT m.id,";
        $out[] = "        ROUND((COALESCE(c.saldo_debit, 0) - COALESCE(c.saldo_kredit, 0)) + COALESCE(SUM(m.debit - m.kredit) OVER (PARTITION BY m.coa_id ORDER BY m.tanggal ASC, m.id ASC ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING), 0), 2) AS calc_before,";
        $out[] = "        ROUND((COALESCE(c.saldo_debit, 0) - COALESCE(c.saldo_kredit, 0)) + SUM(m.debit - m.kredit) OVER (PARTITION BY m.coa_id ORDER BY m.tanggal ASC, m.id ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW), 2) AS calc_after";
        $out[] = "    FROM `coa_movements` m";
        $out[] = "    JOIN `master_coas` c ON c.id = m.coa_id";
        $out[] = "    WHERE m.coa_id IN ({$varList})";
        $out[] = ") calc ON calc.id = cm.id";
        $out[] = "SET cm.balance_before = calc.calc_before, cm.balance_after = calc.calc_after;";
        $out[] = '';
        $out[] = 'COMMIT;';

        file_put_contents($targetPath, implode("\n", $out) . "\n");

        return $targetPath;
    }

    protected function resolveLegacyId(int $legacyId, array $legacyIdToLiveCoaId): int
    {
        if (! isset($legacyIdToLiveCoaId[$legacyId])) {
            throw new RuntimeException("Legacy COA ID {$legacyId} tidak dikenali dalam peta remapping.");
        }

        return $legacyIdToLiveCoaId[$legacyId];
    }

    /**
     * Parse 425 blok transaksi + 850 baris double-entry dari file SQL sumber.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function parseSourceSql(string $path): array
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
        $transactions = [];
        $current = null;
        $pendingComment = '';

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '-- #')) {
                $pendingComment = $line;
                continue;
            }

            if (str_starts_with($line, '--')) {
                continue;
            }

            if (preg_match('/^INSERT INTO `(trans_kas|trans_kas_banks)` \(([^)]+)\) VALUES \((.+)\);$/', $line, $m)) {
                if ($current !== null) {
                    $transactions[] = $current;
                }

                $cols = array_map(static fn ($c) => trim($c, " `"), explode(',', $m[2]));
                $vals = array_map(static fn ($v) => trim($v), str_getcsv($m[3], ',', "'", "\\"));
                $row = array_combine($cols, $vals);

                $current = [
                    'table'          => $m[1],
                    'kode_transaksi' => $row['kode_transaksi'],
                    'comment'        => $pendingComment,
                    'row'            => $row,
                    'movements'      => [],
                ];
                continue;
            }

            if (
                $current !== null
                && preg_match('/^\((\d+),\s*\'([^\']+)\',\s*\'([^\']+)\',\s*@ref_id,\s*\'([^\']+)\',\s*\'([^\']+)\',\s*([\d.]+),\s*([\d.]+),\s*([\d.]+),\s*([\d.]+),\s*([\d.]+),\s*\'(.*)\',\s*(\d+|NULL),\s*\'([^\']+)\',\s*\'([^\']+)\'\)[,;]$/', $line, $m)
            ) {
                $current['movements'][] = [
                    'coa_id'         => (int) $m[1],
                    'tanggal'        => $m[2],
                    'reference_type' => $m[3],
                    'reference_no'   => $m[4],
                    'movement_type'  => $m[5],
                    'nominal'        => (float) $m[6],
                    'debit'          => (float) $m[7],
                    'kredit'         => (float) $m[8],
                    'remarks'        => str_replace("''", "'", $m[11]),
                    'created_at'     => $m[13],
                    'updated_at'     => $m[14],
                ];
            }
        }

        if ($current !== null) {
            $transactions[] = $current;
        }

        if (count($transactions) !== 425) {
            throw new RuntimeException('Expected 425 transactions from SQL source, parsed ' . count($transactions));
        }

        return $transactions;
    }
}
