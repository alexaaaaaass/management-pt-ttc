-- =============================================================================
-- IMPORT BUKU BESAR COA TTC 2026 (DARI EXCEL: FORMAT_BUKU_BESAR_COA_TTC_2026_EDIT.xlsx)
-- Generated at : 2026-11-24 13:45:45
-- Database     : ttc (MySQL / MariaDB)
-- Mode         : APPEND (Opsi 1A - Data existing tetap utuh, prefix BNK26- / KAS26-)
-- Pinjaman     : Opsi 2A - 4 baris Modal Pinjaman Indigama => 2142 (Hutang kepada Pemilik)
-- Antar-Bank   : Opsi 3A - 2 pasang transfer (R193+R195 & R201+R202) digabung
-- Total Tx     : 425 transaksi (417 trans_kas_banks + 8 trans_kas)
-- Ledger Rows  : 850 baris double-entry coa_movements
-- Total Debit  : Rp 32.045.808.823,55
-- Total Kredit : Rp 32.045.808.823,55 (SELISIH: Rp 0,00)
--
-- REKONSILIASI SALDO AKHIR PER REKENING BANK (COCOK 100% DENGAN FOOTER EXCEL R452-R457):
--   1111 Bank BRI - Operasional (BRI TABUNGAN) : Rp 465.022.586,24
--   1114 Bank Lain - Giro       (BRI GIRO)     : Rp 1.856.686,00
--   1112 Bank BCA - Operasional (BCA)          : Rp 58.625.025,87
--   1113 Bank Lain - Efendi     (BRI FENDI)    : Rp 0,00
--   TOTAL 4 REKENING BANK                      : Rp 525.504.298,11
--   1116 Petty Cash (Transfer Bank -> Kas Kecil): Rp 49.000.000,00
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+07:00';

START TRANSACTION;

-- -----------------------------------------------------------------------------
-- BAGIAN 1: INSERT TRANSAKSI (trans_kas_banks, trans_kas) & DOUBLE-ENTRY (coa_movements)
-- -----------------------------------------------------------------------------

-- #KAS26-0001 | Excel R004 | 2025-12-23 | 1116 (D) -> 2142 (K) (Pinjaman Modal Indigama (Opsi 2A))
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0001', 'KAS_MASUK', 'TTC', '2025-12-23', 'December 2025', NULL, 422, 314, NULL, 1920786312.00, 'ACTIVE', '[Excel R4 | MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 10%', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2025-12-23', 'TRANS_KAS', @ref_id, 'KAS26-0001', 'DEBIT', 1920786312.00, 1920786312.00, 0.00, 0.00, 0.00, '[Excel R4 | MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 10%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(422, '2025-12-23', 'TRANS_KAS', @ref_id, 'KAS26-0001', 'KREDIT', 1920786312.00, 0.00, 1920786312.00, 0.00, 0.00, 'Lawan penerimaan [Excel R4 | MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 10%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #KAS26-0002 | Excel R005 | 2026-01-22 | 1221 (D) -> 1116 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0002', 'KAS_KELUAR', 'TTC', '2026-01-22', 'January 2026', NULL, 355, 314, NULL, 1920786312.00, 'ACTIVE', '[Excel R5 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 10%', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-01-22', 'TRANS_KAS', @ref_id, 'KAS26-0002', 'DEBIT', 1920786312.00, 1920786312.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R5 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 10%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(314, '2026-01-22', 'TRANS_KAS', @ref_id, 'KAS26-0002', 'KREDIT', 1920786312.00, 0.00, 1920786312.00, 0.00, 0.00, '[Excel R5 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 10%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0001 | Excel R006 | 2026-01-24 | 1113 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0001', 'BANK_MASUK', 'TTC', '2026-01-24', 'January 2026', 311, 439, 5100000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R6 | MODAL] SETORAN MODAL KE REKENING FENDI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(311, '2026-01-24', 'TRANS_BANK', @ref_id, 'BNK26-0001', 'DEBIT', 5100000.00, 5100000.00, 0.00, 0.00, 0.00, '[Excel R6 | MODAL] SETORAN MODAL KE REKENING FENDI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-01-24', 'TRANS_BANK', @ref_id, 'BNK26-0001', 'KREDIT', 5100000.00, 0.00, 5100000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R6 | MODAL] SETORAN MODAL KE REKENING FENDI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0002 | Excel R007 | 2026-01-25 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0002', 'BANK_KELUAR', 'TTC', '2026-01-25', 'January 2026', 311, 523, 750.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R7 | BIAYA ADMIN] BIAYA NOTIFIKASI SMS', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-01-25', 'TRANS_BANK', @ref_id, 'BNK26-0002', 'DEBIT', 750.00, 750.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R7 | BIAYA ADMIN] BIAYA NOTIFIKASI SMS', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-25', 'TRANS_BANK', @ref_id, 'BNK26-0002', 'KREDIT', 750.00, 0.00, 750.00, 0.00, 0.00, '[Excel R7 | BIAYA ADMIN] BIAYA NOTIFIKASI SMS', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #KAS26-0003 | Excel R008 | 2026-01-30 | 1116 (D) -> 2142 (K) (Pinjaman Modal Indigama (Opsi 2A))
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0003', 'KAS_MASUK', 'TTC', '2026-01-30', 'January 2026', NULL, 422, 314, NULL, 3000000000.00, 'ACTIVE', '[Excel R8 | MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 20%', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-01-30', 'TRANS_KAS', @ref_id, 'KAS26-0003', 'DEBIT', 3000000000.00, 3000000000.00, 0.00, 0.00, 0.00, '[Excel R8 | MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 20%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(422, '2026-01-30', 'TRANS_KAS', @ref_id, 'KAS26-0003', 'KREDIT', 3000000000.00, 0.00, 3000000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R8 | MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 20%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #KAS26-0004 | Excel R009 | 2026-01-30 | 1221 (D) -> 1116 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0004', 'KAS_KELUAR', 'TTC', '2026-01-30', 'January 2026', NULL, 355, 314, NULL, 3000000000.00, 'ACTIVE', '[Excel R9 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 20%', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-01-30', 'TRANS_KAS', @ref_id, 'KAS26-0004', 'DEBIT', 3000000000.00, 3000000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R9 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 20%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(314, '2026-01-30', 'TRANS_KAS', @ref_id, 'KAS26-0004', 'KREDIT', 3000000000.00, 0.00, 3000000000.00, 0.00, 0.00, '[Excel R9 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 20%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0003 | Excel R010 | 2026-01-30 | 1113 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0003', 'BANK_MASUK', 'TTC', '2026-01-30', 'January 2026', 311, 439, 200000000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R10 | MODAL] SETORAN MODAL KE REKENING FENDI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(311, '2026-01-30', 'TRANS_BANK', @ref_id, 'BNK26-0003', 'DEBIT', 200000000.00, 200000000.00, 0.00, 0.00, 0.00, '[Excel R10 | MODAL] SETORAN MODAL KE REKENING FENDI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-01-30', 'TRANS_BANK', @ref_id, 'BNK26-0003', 'KREDIT', 200000000.00, 0.00, 200000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R10 | MODAL] SETORAN MODAL KE REKENING FENDI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0004 | Excel R011 | 2026-01-31 | 5308 (D) -> 1113 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0004', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 492, 917500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R11 | PEMBAYARAN] JASA PRODUKSI ETIKET JB', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0004', 'DEBIT', 917500.00, 917500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R11 | PEMBAYARAN] JASA PRODUKSI ETIKET JB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0004', 'KREDIT', 917500.00, 0.00, 917500.00, 0.00, 0.00, '[Excel R11 | PEMBAYARAN] JASA PRODUKSI ETIKET JB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0005 | Excel R012 | 2026-01-31 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0005', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R12 | BIAYA ADMIN] ADMIN BANK JASA PRODUKSI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0005', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R12 | BIAYA ADMIN] ADMIN BANK JASA PRODUKSI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0005', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R12 | BIAYA ADMIN] ADMIN BANK JASA PRODUKSI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0006 | Excel R013 | 2026-01-31 | 5308 (D) -> 1113 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0006', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 492, 822000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R13 | PEMBELIAN] PISAU INNER KENKO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0006', 'DEBIT', 822000.00, 822000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R13 | PEMBELIAN] PISAU INNER KENKO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0006', 'KREDIT', 822000.00, 0.00, 822000.00, 0.00, 0.00, '[Excel R13 | PEMBELIAN] PISAU INNER KENKO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0007 | Excel R014 | 2026-01-31 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0007', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R14 | BIAYA ADMIN] ADMIN BANK PISAU INNER KENKOU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0007', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R14 | BIAYA ADMIN] ADMIN BANK PISAU INNER KENKOU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0007', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R14 | BIAYA ADMIN] ADMIN BANK PISAU INNER KENKOU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0008 | Excel R015 | 2026-01-31 | 6104 (D) -> 1113 (K) (Biaya Pengiriman / Ekspedisi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0008', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 504, 22000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R15 | PEMBAYARAN] ONGKIR PISAU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(504, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0008', 'DEBIT', 22000.00, 22000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R15 | PEMBAYARAN] ONGKIR PISAU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0008', 'KREDIT', 22000.00, 0.00, 22000.00, 0.00, 0.00, '[Excel R15 | PEMBAYARAN] ONGKIR PISAU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0009 | Excel R016 | 2026-01-31 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0009', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R16 | BIAYA ADMIN] ADMIN BANK ONGKIR PISAU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0009', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R16 | BIAYA ADMIN] ADMIN BANK ONGKIR PISAU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0009', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R16 | BIAYA ADMIN] ADMIN BANK ONGKIR PISAU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0010 | Excel R017 | 2026-01-31 | 1251 (D) -> 1113 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0010', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 373, 6119000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R17 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0010', 'DEBIT', 6119000.00, 6119000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R17 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0010', 'KREDIT', 6119000.00, 0.00, 6119000.00, 0.00, 0.00, '[Excel R17 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0011 | Excel R018 | 2026-01-31 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0011', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R18 | BIAYA ADMIN] PEMBANGUNAN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0011', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R18 | BIAYA ADMIN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0011', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R18 | BIAYA ADMIN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0012 | Excel R019 | 2026-01-31 | 1131 (D) -> 1113 (K) (Piutang / Kasbon Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0012', 'BANK_KELUAR', 'TTC', '2026-01-31', 'January 2026', 311, 329, 1000000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R19 | PEMBAYARAN] BON P.HARIYANTO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(329, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0012', 'DEBIT', 1000000.00, 1000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R19 | PEMBAYARAN] BON P.HARIYANTO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-01-31', 'TRANS_BANK', @ref_id, 'BNK26-0012', 'KREDIT', 1000000.00, 0.00, 1000000.00, 0.00, 0.00, '[Excel R19 | PEMBAYARAN] BON P.HARIYANTO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0013 | Excel R022 | 2026-02-02 | 5308 (D) -> 1113 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0013', 'BANK_KELUAR', 'TTC', '2026-02-02', 'February 2026', 311, 492, 3900000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R22 | PEMBAYARAN] ETIKET MARBOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0013', 'DEBIT', 3900000.00, 3900000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R22 | PEMBAYARAN] ETIKET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0013', 'KREDIT', 3900000.00, 0.00, 3900000.00, 0.00, 0.00, '[Excel R22 | PEMBAYARAN] ETIKET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0014 | Excel R023 | 2026-02-02 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0014', 'BANK_KELUAR', 'TTC', '2026-02-02', 'February 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R23 | BIAYA ADMIN] ETIKET MARBOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0014', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R23 | BIAYA ADMIN] ETIKET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0014', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R23 | BIAYA ADMIN] ETIKET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0015 | Excel R024 | 2026-02-02 | 5308 (D) -> 1113 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0015', 'BANK_KELUAR', 'TTC', '2026-02-02', 'February 2026', 311, 492, 13485000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R24 | PEMBAYARAN] PELUNASAN CTP MARBOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0015', 'DEBIT', 13485000.00, 13485000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R24 | PEMBAYARAN] PELUNASAN CTP MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0015', 'KREDIT', 13485000.00, 0.00, 13485000.00, 0.00, 0.00, '[Excel R24 | PEMBAYARAN] PELUNASAN CTP MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0016 | Excel R025 | 2026-02-02 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0016', 'BANK_KELUAR', 'TTC', '2026-02-02', 'February 2026', 311, 523, 6500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R25 | BIAYA ADMIN] ADMIN BANK PELUNASAN CTP MARBOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0016', 'DEBIT', 6500.00, 6500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R25 | BIAYA ADMIN] ADMIN BANK PELUNASAN CTP MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0016', 'KREDIT', 6500.00, 0.00, 6500.00, 0.00, 0.00, '[Excel R25 | BIAYA ADMIN] ADMIN BANK PELUNASAN CTP MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0017 | Excel R026 | 2026-02-02 | 5308 (D) -> 1113 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0017', 'BANK_KELUAR', 'TTC', '2026-02-02', 'February 2026', 311, 492, 2372469.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R26 | PEMBAYARAN] JASA LEM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0017', 'DEBIT', 2372469.00, 2372469.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R26 | PEMBAYARAN] JASA LEM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0017', 'KREDIT', 2372469.00, 0.00, 2372469.00, 0.00, 0.00, '[Excel R26 | PEMBAYARAN] JASA LEM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0018 | Excel R027 | 2026-02-02 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0018', 'BANK_KELUAR', 'TTC', '2026-02-02', 'February 2026', 311, 523, 6500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R27 | BIAYA ADMIN] ADMIN BANK JASA LEM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0018', 'DEBIT', 6500.00, 6500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R27 | BIAYA ADMIN] ADMIN BANK JASA LEM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0018', 'KREDIT', 6500.00, 0.00, 6500.00, 0.00, 0.00, '[Excel R27 | BIAYA ADMIN] ADMIN BANK JASA LEM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0019 | Excel R028 | 2026-02-02 | 1116 (D) -> 1113 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0019', 'BANK_KELUAR', 'TTC', '2026-02-02', 'February 2026', 311, 314, 3000000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R28 | PETTYCASH] SETORAN MODAL PETYCASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0019', 'DEBIT', 3000000.00, 3000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R28 | PETTYCASH] SETORAN MODAL PETYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-02', 'TRANS_BANK', @ref_id, 'BNK26-0019', 'KREDIT', 3000000.00, 0.00, 3000000.00, 0.00, 0.00, '[Excel R28 | PETTYCASH] SETORAN MODAL PETYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0020 | Excel R029 | 2026-02-03 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0020', 'BANK_KELUAR', 'TTC', '2026-02-03', 'February 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R29 | BIAYA ADMIN] SETORAN MODAL PETYCASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-03', 'TRANS_BANK', @ref_id, 'BNK26-0020', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R29 | BIAYA ADMIN] SETORAN MODAL PETYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-03', 'TRANS_BANK', @ref_id, 'BNK26-0020', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R29 | BIAYA ADMIN] SETORAN MODAL PETYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0021 | Excel R030 | 2026-02-04 | 1114 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0021', 'BANK_MASUK', 'TTC', '2026-02-04', 'February 2026', 312, 439, 1000000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R30 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-02-04', 'TRANS_BANK', @ref_id, 'BNK26-0021', 'DEBIT', 1000000000.00, 1000000000.00, 0.00, 0.00, 0.00, '[Excel R30 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-02-04', 'TRANS_BANK', @ref_id, 'BNK26-0021', 'KREDIT', 1000000000.00, 0.00, 1000000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R30 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0022 | Excel R031 | 2026-02-05 | 6209 (D) -> 1113 (K) (Bahan Bakar, E-Toll & Transportasi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0022', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 311, 518, 250000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R31 | OPERASIONAL] BENSIN PERTALITE', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(518, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0022', 'DEBIT', 250000.00, 250000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R31 | OPERASIONAL] BENSIN PERTALITE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0022', 'KREDIT', 250000.00, 0.00, 250000.00, 0.00, 0.00, '[Excel R31 | OPERASIONAL] BENSIN PERTALITE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0023 | Excel R032 | 2026-02-05 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0023', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R32 | BIAYA ADMIN] BENSIN PERTALITE', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0023', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R32 | BIAYA ADMIN] BENSIN PERTALITE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0023', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R32 | BIAYA ADMIN] BENSIN PERTALITE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0024 | Excel R033 | 2026-02-05 | 5308 (D) -> 1113 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0024', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 311, 492, 11948000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R33 | PEMBAYARAN] JASA CETAK ET KENKO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0024', 'DEBIT', 11948000.00, 11948000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R33 | PEMBAYARAN] JASA CETAK ET KENKO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0024', 'KREDIT', 11948000.00, 0.00, 11948000.00, 0.00, 0.00, '[Excel R33 | PEMBAYARAN] JASA CETAK ET KENKO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0025 | Excel R034 | 2026-02-05 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0025', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R34 | BIAYA ADMIN] JASA CETAK ET KENKO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0025', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R34 | BIAYA ADMIN] JASA CETAK ET KENKO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0025', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R34 | BIAYA ADMIN] JASA CETAK ET KENKO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0026 | Excel R035 | 2026-02-05 | 6610 (D) -> 1113 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0026', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 311, 542, 500000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R35 | PEMBAYARAN] REIMBUERSE OPERASIONAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0026', 'DEBIT', 500000.00, 500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R35 | PEMBAYARAN] REIMBUERSE OPERASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0026', 'KREDIT', 500000.00, 0.00, 500000.00, 0.00, 0.00, '[Excel R35 | PEMBAYARAN] REIMBUERSE OPERASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0027 | Excel R036 | 2026-02-05 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0027', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R36 | BIAYA ADMIN] REIMBUERSE OPERASIONAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0027', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R36 | BIAYA ADMIN] REIMBUERSE OPERASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0027', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R36 | BIAYA ADMIN] REIMBUERSE OPERASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0028 | Excel R037 | 2026-02-05 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0028', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 311, 523, 1500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R37 | BIAYA ADMIN] SMS NOTIFIKASI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0028', 'DEBIT', 1500.00, 1500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R37 | BIAYA ADMIN] SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0028', 'KREDIT', 1500.00, 0.00, 1500.00, 0.00, 0.00, '[Excel R37 | BIAYA ADMIN] SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0029 | Excel R038 | 2026-02-05 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0029', 'BANK_KELUAR', 'TTC', '2026-02-05', 'February 2026', 312, 523, 750.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R38 | BIAYA ADMIN] SMS NOTIFIKASI SEJUMLAH 1 NOTIFIKASI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0029', 'DEBIT', 750.00, 750.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R38 | BIAYA ADMIN] SMS NOTIFIKASI SEJUMLAH 1 NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-05', 'TRANS_BANK', @ref_id, 'BNK26-0029', 'KREDIT', 750.00, 0.00, 750.00, 0.00, 0.00, '[Excel R38 | BIAYA ADMIN] SMS NOTIFIKASI SEJUMLAH 1 NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0030 | Excel R039 | 2026-02-06 | 2111 (D) -> 1113 (K) (Pelunasan Utang Usaha Bahan Baku)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0030', 'BANK_KELUAR', 'TTC', '2026-02-06', 'February 2026', 311, 395, 136664000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R39 | PEMBELIAN] HUTANG BAHAN BAKU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(395, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0030', 'DEBIT', 136664000.00, 136664000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R39 | PEMBELIAN] HUTANG BAHAN BAKU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0030', 'KREDIT', 136664000.00, 0.00, 136664000.00, 0.00, 0.00, '[Excel R39 | PEMBELIAN] HUTANG BAHAN BAKU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0031 | Excel R040 | 2026-02-06 | 1113 (D) -> 1131 (K) (Pelunasan Piutang / Kasbon)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0031', 'BANK_MASUK', 'TTC', '2026-02-06', 'February 2026', 311, 329, 30000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R40 | PENERIMAAN] BAYAR  UTANG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(311, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0031', 'DEBIT', 30000.00, 30000.00, 0.00, 0.00, 0.00, '[Excel R40 | PENERIMAAN] BAYAR  UTANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(329, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0031', 'KREDIT', 30000.00, 0.00, 30000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R40 | PENERIMAAN] BAYAR  UTANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0032 | Excel R041 | 2026-02-06 | 1113 (D) -> 1131 (K) (Pelunasan Piutang / Kasbon)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0032', 'BANK_MASUK', 'TTC', '2026-02-06', 'February 2026', 311, 329, 270000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R41 | PENERIMAAN] BAYAR  UTANG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(311, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0032', 'DEBIT', 270000.00, 270000.00, 0.00, 0.00, 0.00, '[Excel R41 | PENERIMAAN] BAYAR  UTANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(329, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0032', 'KREDIT', 270000.00, 0.00, 270000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R41 | PENERIMAAN] BAYAR  UTANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0033 | Excel R042 | 2026-02-06 | 2133 (D) -> 1113 (K) (Pelunasan Biaya / Utang External)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0033', 'BANK_KELUAR', 'TTC', '2026-02-06', 'February 2026', 311, 415, 250000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R42 | PEMBAYARAN] BAYAR HUTANG EXTERNAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(415, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0033', 'DEBIT', 250000.00, 250000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R42 | PEMBAYARAN] BAYAR HUTANG EXTERNAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0033', 'KREDIT', 250000.00, 0.00, 250000.00, 0.00, 0.00, '[Excel R42 | PEMBAYARAN] BAYAR HUTANG EXTERNAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0034 | Excel R043 | 2026-02-06 | 5308 (D) -> 1113 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0034', 'BANK_KELUAR', 'TTC', '2026-02-06', 'February 2026', 311, 492, 24014088.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R43 | PEMBAYARAN] ET MARBOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0034', 'DEBIT', 24014088.00, 24014088.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R43 | PEMBAYARAN] ET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0034', 'KREDIT', 24014088.00, 0.00, 24014088.00, 0.00, 0.00, '[Excel R43 | PEMBAYARAN] ET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0035 | Excel R044 | 2026-02-06 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0035', 'BANK_KELUAR', 'TTC', '2026-02-06', 'February 2026', 311, 523, 2500.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R44 | BIAYA ADMIN] ADMIN BANK ET MARBOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0035', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R44 | BIAYA ADMIN] ADMIN BANK ET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0035', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R44 | BIAYA ADMIN] ADMIN BANK ET MARBOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0036 | Excel R045 | 2026-02-06 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0036', 'BANK_KELUAR', 'TTC', '2026-02-06', 'February 2026', 312, 523, 275000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R45 | PEMBAYARAN] CEK PT.TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0036', 'DEBIT', 275000.00, 275000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R45 | PEMBAYARAN] CEK PT.TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-06', 'TRANS_BANK', @ref_id, 'BNK26-0036', 'KREDIT', 275000.00, 0.00, 275000.00, 0.00, 0.00, '[Excel R45 | PEMBAYARAN] CEK PT.TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0037 | Excel R046 | 2026-02-07 | 1211 (D) -> 1114 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0037', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 349, 168435715.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R46 | PEMBAYARAN] CICILAN GUDANG K/ 1', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0037', 'DEBIT', 168435715.00, 168435715.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R46 | PEMBAYARAN] CICILAN GUDANG K/ 1', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0037', 'KREDIT', 168435715.00, 0.00, 168435715.00, 0.00, 0.00, '[Excel R46 | PEMBAYARAN] CICILAN GUDANG K/ 1', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0038 | Excel R047 | 2026-02-07 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0038', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R47 | PEMBAYARAN] CICILAN GUDANG K/ 1', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0038', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R47 | PEMBAYARAN] CICILAN GUDANG K/ 1', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0038', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R47 | PEMBAYARAN] CICILAN GUDANG K/ 1', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0039 | Excel R048 | 2026-02-07 | 1211 (D) -> 1114 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0039', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 349, 188435715.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R48 | PEMBAYARAN] CICILAN GUDANG K/ 2', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0039', 'DEBIT', 188435715.00, 188435715.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R48 | PEMBAYARAN] CICILAN GUDANG K/ 2', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0039', 'KREDIT', 188435715.00, 0.00, 188435715.00, 0.00, 0.00, '[Excel R48 | PEMBAYARAN] CICILAN GUDANG K/ 2', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0040 | Excel R049 | 2026-02-07 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0040', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R49 | PEMBAYARAN] CICILAN GUDANG K/ 2', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0040', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R49 | PEMBAYARAN] CICILAN GUDANG K/ 2', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0040', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R49 | PEMBAYARAN] CICILAN GUDANG K/ 2', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0041 | Excel R050 | 2026-02-07 | 1211 (D) -> 1114 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0041', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 349, 188435715.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R50 | PEMBAYARAN] CICILAN GUDANG K/ 3', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0041', 'DEBIT', 188435715.00, 188435715.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R50 | PEMBAYARAN] CICILAN GUDANG K/ 3', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0041', 'KREDIT', 188435715.00, 0.00, 188435715.00, 0.00, 0.00, '[Excel R50 | PEMBAYARAN] CICILAN GUDANG K/ 3', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0042 | Excel R051 | 2026-02-07 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0042', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R51 | PEMBAYARAN] CICILAN GUDANG K/ 3', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0042', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R51 | PEMBAYARAN] CICILAN GUDANG K/ 3', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0042', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R51 | PEMBAYARAN] CICILAN GUDANG K/ 3', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0043 | Excel R052 | 2026-02-07 | 1251 (D) -> 1114 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0043', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 373, 3047500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R52 | PEMBAYARAN] PEMBANGUNAN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0043', 'DEBIT', 3047500.00, 3047500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R52 | PEMBAYARAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0043', 'KREDIT', 3047500.00, 0.00, 3047500.00, 0.00, 0.00, '[Excel R52 | PEMBAYARAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0044 | Excel R053 | 2026-02-07 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0044', 'BANK_KELUAR', 'TTC', '2026-02-07', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R53 | PEMBAYARAN] PEMBANGUNAN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0044', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R53 | PEMBAYARAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-07', 'TRANS_BANK', @ref_id, 'BNK26-0044', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R53 | PEMBAYARAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0045 | Excel R054 | 2026-02-09 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0045', 'BANK_KELUAR', 'TTC', '2026-02-09', 'February 2026', 311, 523, 2250.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R54 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-09', 'TRANS_BANK', @ref_id, 'BNK26-0045', 'DEBIT', 2250.00, 2250.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R54 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-09', 'TRANS_BANK', @ref_id, 'BNK26-0045', 'KREDIT', 2250.00, 0.00, 2250.00, 0.00, 0.00, '[Excel R54 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0046 | Excel R055 | 2026-02-11 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0046', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 314, 1000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R55 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0046', 'DEBIT', 1000000.00, 1000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R55 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0046', 'KREDIT', 1000000.00, 0.00, 1000000.00, 0.00, 0.00, '[Excel R55 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0047 | Excel R056 | 2026-02-11 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0047', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 314, 1000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R56 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0047', 'DEBIT', 1000000.00, 1000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R56 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0047', 'KREDIT', 1000000.00, 0.00, 1000000.00, 0.00, 0.00, '[Excel R56 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0048 | Excel R057 | 2026-02-11 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0048', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 314, 2000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R57 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0048', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R57 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0048', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R57 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0049 | Excel R058 | 2026-02-11 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0049', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 314, 2000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R58 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0049', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R58 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0049', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R58 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0050 | Excel R059 | 2026-02-11 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0050', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 314, 2000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R59 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0050', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R59 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0050', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R59 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0051 | Excel R060 | 2026-02-11 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0051', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 523, 500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R60 | BIAYA ADMIN] FEE INFO MUTASI ATM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0051', 'DEBIT', 500.00, 500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R60 | BIAYA ADMIN] FEE INFO MUTASI ATM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0051', 'KREDIT', 500.00, 0.00, 500.00, 0.00, 0.00, '[Excel R60 | BIAYA ADMIN] FEE INFO MUTASI ATM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0052 | Excel R061 | 2026-02-11 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0052', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 314, 2000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R61 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0052', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R61 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0052', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R61 | PETTYCASH] TARIK TUNAI - PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0053 | Excel R062 | 2026-02-11 | 5308 (D) -> 1114 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0053', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 492, 707000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R62 | PEMBAYARAN] JASA PLONG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0053', 'DEBIT', 707000.00, 707000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R62 | PEMBAYARAN] JASA PLONG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0053', 'KREDIT', 707000.00, 0.00, 707000.00, 0.00, 0.00, '[Excel R62 | PEMBAYARAN] JASA PLONG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0054 | Excel R063 | 2026-02-11 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0054', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 523, 6500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R63 | PEMBAYARAN] JASA PLONG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0054', 'DEBIT', 6500.00, 6500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R63 | PEMBAYARAN] JASA PLONG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0054', 'KREDIT', 6500.00, 0.00, 6500.00, 0.00, 0.00, '[Excel R63 | PEMBAYARAN] JASA PLONG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0055 | Excel R064 | 2026-02-11 | 6610 (D) -> 1114 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0055', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 542, 745000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R64 | PEMBAYARAN] REIMBURSE SHERLY', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0055', 'DEBIT', 745000.00, 745000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R64 | PEMBAYARAN] REIMBURSE SHERLY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0055', 'KREDIT', 745000.00, 0.00, 745000.00, 0.00, 0.00, '[Excel R64 | PEMBAYARAN] REIMBURSE SHERLY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0056 | Excel R065 | 2026-02-11 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0056', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 523, 6500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R65 | BIAYA ADMIN] ADMIN REIMBURSE SHER;Y', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0056', 'DEBIT', 6500.00, 6500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R65 | BIAYA ADMIN] ADMIN REIMBURSE SHER;Y', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0056', 'KREDIT', 6500.00, 0.00, 6500.00, 0.00, 0.00, '[Excel R65 | BIAYA ADMIN] ADMIN REIMBURSE SHER;Y', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0057 | Excel R066 | 2026-02-11 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0057', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 314, 3000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R66 | PETTYCASH] PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0057', 'DEBIT', 3000000.00, 3000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R66 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0057', 'KREDIT', 3000000.00, 0.00, 3000000.00, 0.00, 0.00, '[Excel R66 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0058 | Excel R067 | 2026-02-11 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0058', 'BANK_KELUAR', 'TTC', '2026-02-11', 'February 2026', 312, 523, 6500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R67 | PETTYCASH] PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0058', 'DEBIT', 6500.00, 6500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R67 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-11', 'TRANS_BANK', @ref_id, 'BNK26-0058', 'KREDIT', 6500.00, 0.00, 6500.00, 0.00, 0.00, '[Excel R67 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0059 | Excel R068 | 2026-02-12 | 1114 (D) -> 4540 (K) (Pendapatan Lain-lain (Penjualan Tebu))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0059', 'BANK_MASUK', 'TTC', '2026-02-12', 'February 2026', 312, 470, 9550000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R68 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-02-12', 'TRANS_BANK', @ref_id, 'BNK26-0059', 'DEBIT', 9550000.00, 9550000.00, 0.00, 0.00, 0.00, '[Excel R68 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(470, '2026-02-12', 'TRANS_BANK', @ref_id, 'BNK26-0059', 'KREDIT', 9550000.00, 0.00, 9550000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R68 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0060 | Excel R069 | 2026-02-12 | 1114 (D) -> 4540 (K) (Pendapatan Lain-lain (Penjualan Tebu))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0060', 'BANK_MASUK', 'TTC', '2026-02-12', 'February 2026', 312, 470, 4800000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R69 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-02-12', 'TRANS_BANK', @ref_id, 'BNK26-0060', 'DEBIT', 4800000.00, 4800000.00, 0.00, 0.00, 0.00, '[Excel R69 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(470, '2026-02-12', 'TRANS_BANK', @ref_id, 'BNK26-0060', 'KREDIT', 4800000.00, 0.00, 4800000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R69 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0061 | Excel R070 | 2026-02-12 | 1114 (D) -> 4540 (K) (Pendapatan Lain-lain (Penjualan Tebu))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0061', 'BANK_MASUK', 'TTC', '2026-02-12', 'February 2026', 312, 470, 100000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R70 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-02-12', 'TRANS_BANK', @ref_id, 'BNK26-0061', 'DEBIT', 100000.00, 100000.00, 0.00, 0.00, 0.00, '[Excel R70 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(470, '2026-02-12', 'TRANS_BANK', @ref_id, 'BNK26-0061', 'KREDIT', 100000.00, 0.00, 100000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R70 | PENJUALAN] UANG MASUK - PENJUALAN TEBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0062 | Excel R071 | 2026-02-13 | 3500 (D) -> 1113 (K) (Pengembalian Sisa Saldo Rekening Fendi (Prive))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0062', 'BANK_KELUAR', 'TTC', '2026-02-13', 'February 2026', 311, 443, 43443.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R71 | PEMBAYARAN] PENGEMBALIAN SISA SALDO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(443, '2026-02-13', 'TRANS_BANK', @ref_id, 'BNK26-0062', 'DEBIT', 43443.00, 43443.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R71 | PEMBAYARAN] PENGEMBALIAN SISA SALDO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-13', 'TRANS_BANK', @ref_id, 'BNK26-0062', 'KREDIT', 43443.00, 0.00, 43443.00, 0.00, 0.00, '[Excel R71 | PEMBAYARAN] PENGEMBALIAN SISA SALDO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0063 | Excel R072 | 2026-02-13 | 6214 (D) -> 1113 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0063', 'BANK_KELUAR', 'TTC', '2026-02-13', 'February 2026', 311, 523, 50000.00, NULL, 'Bank - BRI FENDI', NULL, NULL, '[Excel R72 | BIAYA ADMIN] UANG TETAP', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-13', 'TRANS_BANK', @ref_id, 'BNK26-0063', 'DEBIT', 50000.00, 50000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R72 | BIAYA ADMIN] UANG TETAP', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(311, '2026-02-13', 'TRANS_BANK', @ref_id, 'BNK26-0063', 'KREDIT', 50000.00, 0.00, 50000.00, 0.00, 0.00, '[Excel R72 | BIAYA ADMIN] UANG TETAP', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0064 | Excel R073 | 2026-02-14 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0064', 'BANK_KELUAR', 'TTC', '2026-02-14', 'February 2026', 312, 314, 1000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R73 | PETTYCASH] PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0064', 'DEBIT', 1000000.00, 1000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R73 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0064', 'KREDIT', 1000000.00, 0.00, 1000000.00, 0.00, 0.00, '[Excel R73 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0065 | Excel R074 | 2026-02-14 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0065', 'BANK_KELUAR', 'TTC', '2026-02-14', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R74 | PETTYCASH] PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0065', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R74 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0065', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R74 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0066 | Excel R075 | 2026-02-14 | 5308 (D) -> 1114 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0066', 'BANK_KELUAR', 'TTC', '2026-02-14', 'February 2026', 312, 492, 1920000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R75 | PEMBAYARAN] KLIS EMBOS PDW 88', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0066', 'DEBIT', 1920000.00, 1920000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R75 | PEMBAYARAN] KLIS EMBOS PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0066', 'KREDIT', 1920000.00, 0.00, 1920000.00, 0.00, 0.00, '[Excel R75 | PEMBAYARAN] KLIS EMBOS PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0067 | Excel R076 | 2026-02-14 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0067', 'BANK_KELUAR', 'TTC', '2026-02-14', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R76 | PEMBAYARAN] KLIS EMBOS PDW 88', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0067', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R76 | PEMBAYARAN] KLIS EMBOS PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-14', 'TRANS_BANK', @ref_id, 'BNK26-0067', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R76 | PEMBAYARAN] KLIS EMBOS PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0068 | Excel R077 | 2026-02-17 | 1251 (D) -> 1114 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0068', 'BANK_KELUAR', 'TTC', '2026-02-17', 'February 2026', 312, 373, 850000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R77 | PEMBAYARAN] JASA DESIGN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-02-17', 'TRANS_BANK', @ref_id, 'BNK26-0068', 'DEBIT', 850000.00, 850000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R77 | PEMBAYARAN] JASA DESIGN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-17', 'TRANS_BANK', @ref_id, 'BNK26-0068', 'KREDIT', 850000.00, 0.00, 850000.00, 0.00, 0.00, '[Excel R77 | PEMBAYARAN] JASA DESIGN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0069 | Excel R078 | 2026-02-17 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0069', 'BANK_KELUAR', 'TTC', '2026-02-17', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R78 | PEMBAYARAN] JASA DESIGN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-17', 'TRANS_BANK', @ref_id, 'BNK26-0069', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R78 | PEMBAYARAN] JASA DESIGN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-17', 'TRANS_BANK', @ref_id, 'BNK26-0069', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R78 | PEMBAYARAN] JASA DESIGN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0070 | Excel R079 | 2026-02-17 | 5308 (D) -> 1114 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0070', 'BANK_KELUAR', 'TTC', '2026-02-17', 'February 2026', 312, 492, 1788000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R79 | PEMBAYARAN] PISAU ET PDW 88', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-17', 'TRANS_BANK', @ref_id, 'BNK26-0070', 'DEBIT', 1788000.00, 1788000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R79 | PEMBAYARAN] PISAU ET PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-17', 'TRANS_BANK', @ref_id, 'BNK26-0070', 'KREDIT', 1788000.00, 0.00, 1788000.00, 0.00, 0.00, '[Excel R79 | PEMBAYARAN] PISAU ET PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0071 | Excel R080 | 2026-02-18 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0071', 'BANK_KELUAR', 'TTC', '2026-02-18', 'February 2026', 312, 314, 7000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R80 | PETTYCASH] PETTY CASH JUAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-18', 'TRANS_BANK', @ref_id, 'BNK26-0071', 'DEBIT', 7000000.00, 7000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R80 | PETTYCASH] PETTY CASH JUAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-18', 'TRANS_BANK', @ref_id, 'BNK26-0071', 'KREDIT', 7000000.00, 0.00, 7000000.00, 0.00, 0.00, '[Excel R80 | PETTYCASH] PETTY CASH JUAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0072 | Excel R081 | 2026-02-18 | 6610 (D) -> 1114 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0072', 'BANK_KELUAR', 'TTC', '2026-02-18', 'February 2026', 312, 542, 6367136.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R81 | PEMBAYARAN] REIMBURSE MBAK NARNI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-02-18', 'TRANS_BANK', @ref_id, 'BNK26-0072', 'DEBIT', 6367136.00, 6367136.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R81 | PEMBAYARAN] REIMBURSE MBAK NARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-18', 'TRANS_BANK', @ref_id, 'BNK26-0072', 'KREDIT', 6367136.00, 0.00, 6367136.00, 0.00, 0.00, '[Excel R81 | PEMBAYARAN] REIMBURSE MBAK NARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0073 | Excel R082 | 2026-02-18 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0073', 'BANK_KELUAR', 'TTC', '2026-02-18', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R82 | PEMBAYARAN] REIMBURSE MBAK NARNI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-18', 'TRANS_BANK', @ref_id, 'BNK26-0073', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R82 | PEMBAYARAN] REIMBURSE MBAK NARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-18', 'TRANS_BANK', @ref_id, 'BNK26-0073', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R82 | PEMBAYARAN] REIMBURSE MBAK NARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0074 | Excel R083 | 2026-02-19 | 5308 (D) -> 1114 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0074', 'BANK_KELUAR', 'TTC', '2026-02-19', 'February 2026', 312, 492, 5661968.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R83 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0074', 'DEBIT', 5661968.00, 5661968.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R83 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0074', 'KREDIT', 5661968.00, 0.00, 5661968.00, 0.00, 0.00, '[Excel R83 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0075 | Excel R084 | 2026-02-19 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0075', 'BANK_KELUAR', 'TTC', '2026-02-19', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R84 | BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0075', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R84 | BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0075', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R84 | BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0076 | Excel R085 | 2026-02-19 | 5308 (D) -> 1114 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0076', 'BANK_KELUAR', 'TTC', '2026-02-19', 'February 2026', 312, 492, 5661968.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R85 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0076', 'DEBIT', 5661968.00, 5661968.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R85 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0076', 'KREDIT', 5661968.00, 0.00, 5661968.00, 0.00, 0.00, '[Excel R85 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0077 | Excel R086 | 2026-02-19 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0077', 'BANK_KELUAR', 'TTC', '2026-02-19', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R86 | BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0077', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R86 | BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0077', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R86 | BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0078 | Excel R087 | 2026-02-19 | 5308 (D) -> 1114 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0078', 'BANK_KELUAR', 'TTC', '2026-02-19', 'February 2026', 312, 492, 895250.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R87 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0078', 'DEBIT', 895250.00, 895250.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R87 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0078', 'KREDIT', 895250.00, 0.00, 895250.00, 0.00, 0.00, '[Excel R87 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0079 | Excel R088 | 2026-02-19 | 5308 (D) -> 1114 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0079', 'BANK_KELUAR', 'TTC', '2026-02-19', 'February 2026', 312, 492, 895250.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R88 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0079', 'DEBIT', 895250.00, 895250.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R88 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-19', 'TRANS_BANK', @ref_id, 'BNK26-0079', 'KREDIT', 895250.00, 0.00, 895250.00, 0.00, 0.00, '[Excel R88 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0080 | Excel R089 | 2026-02-20 | 1114 (D) -> 5308 (K) (Reversal / Refund Biaya Subcont Produksi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0080', 'BANK_MASUK', 'TTC', '2026-02-20', 'February 2026', 312, 492, 895250.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R89 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0080', 'DEBIT', 895250.00, 895250.00, 0.00, 0.00, 0.00, '[Excel R89 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(492, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0080', 'KREDIT', 895250.00, 0.00, 895250.00, 0.00, 0.00, 'Lawan penerimaan [Excel R89 | PEMBAYARAN] JASA PISAU PLONG INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0081 | Excel R090 | 2026-02-20 | 1114 (D) -> 5308 (K) (Reversal / Refund Biaya Subcont Produksi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0081', 'BANK_MASUK', 'TTC', '2026-02-20', 'February 2026', 312, 492, 5661968.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R90 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0081', 'DEBIT', 5661968.00, 5661968.00, 0.00, 0.00, 0.00, '[Excel R90 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(492, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0081', 'KREDIT', 5661968.00, 0.00, 5661968.00, 0.00, 0.00, 'Lawan penerimaan [Excel R90 | PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0082 | Excel R091 | 2026-02-20 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0082', 'BANK_KELUAR', 'TTC', '2026-02-20', 'February 2026', 312, 314, 4000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R91 | PETTYCASH] PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0082', 'DEBIT', 4000000.00, 4000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R91 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0082', 'KREDIT', 4000000.00, 0.00, 4000000.00, 0.00, 0.00, '[Excel R91 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0083 | Excel R092 | 2026-02-20 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0083', 'BANK_KELUAR', 'TTC', '2026-02-20', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R92 | PETTYCASH] PETTY CASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0083', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R92 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-20', 'TRANS_BANK', @ref_id, 'BNK26-0083', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R92 | PETTYCASH] PETTY CASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0084 | Excel R093 | 2026-02-21 | 1251 (D) -> 1114 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0084', 'BANK_KELUAR', 'TTC', '2026-02-21', 'February 2026', 312, 373, 7914000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R93 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-02-21', 'TRANS_BANK', @ref_id, 'BNK26-0084', 'DEBIT', 7914000.00, 7914000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R93 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-21', 'TRANS_BANK', @ref_id, 'BNK26-0084', 'KREDIT', 7914000.00, 0.00, 7914000.00, 0.00, 0.00, '[Excel R93 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0085 | Excel R094 | 2026-02-21 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0085', 'BANK_KELUAR', 'TTC', '2026-02-21', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R94 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-21', 'TRANS_BANK', @ref_id, 'BNK26-0085', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R94 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-21', 'TRANS_BANK', @ref_id, 'BNK26-0085', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R94 | PEMBELIAN] PEMBANGUNAN NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0086 | Excel R095 | 2026-02-25 | 1114 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0086', 'BANK_MASUK', 'TTC', '2026-02-25', 'February 2026', 312, 467, 175051.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R95 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-02-25', 'TRANS_BANK', @ref_id, 'BNK26-0086', 'DEBIT', 175051.00, 175051.00, 0.00, 0.00, 0.00, '[Excel R95 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-02-25', 'TRANS_BANK', @ref_id, 'BNK26-0086', 'KREDIT', 175051.00, 0.00, 175051.00, 0.00, 0.00, 'Lawan penerimaan [Excel R95 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0087 | Excel R096 | 2026-02-25 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0087', 'BANK_KELUAR', 'TTC', '2026-02-25', 'February 2026', 312, 523, 35010.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R96 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-25', 'TRANS_BANK', @ref_id, 'BNK26-0087', 'DEBIT', 35010.00, 35010.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R96 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-25', 'TRANS_BANK', @ref_id, 'BNK26-0087', 'KREDIT', 35010.00, 0.00, 35010.00, 0.00, 0.00, '[Excel R96 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0088 | Excel R097 | 2026-02-26 | 6104 (D) -> 1114 (K) (Biaya Pengiriman / Ekspedisi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0088', 'BANK_KELUAR', 'TTC', '2026-02-26', 'February 2026', 312, 504, 2900000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R97 | PEMBELIAN] EXPEDISI NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(504, '2026-02-26', 'TRANS_BANK', @ref_id, 'BNK26-0088', 'DEBIT', 2900000.00, 2900000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R97 | PEMBELIAN] EXPEDISI NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-26', 'TRANS_BANK', @ref_id, 'BNK26-0088', 'KREDIT', 2900000.00, 0.00, 2900000.00, 0.00, 0.00, '[Excel R97 | PEMBELIAN] EXPEDISI NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0089 | Excel R098 | 2026-02-26 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0089', 'BANK_KELUAR', 'TTC', '2026-02-26', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R98 | BIAYA ADMIN] EXPEDISI NOGOSARI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-26', 'TRANS_BANK', @ref_id, 'BNK26-0089', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R98 | BIAYA ADMIN] EXPEDISI NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-26', 'TRANS_BANK', @ref_id, 'BNK26-0089', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R98 | BIAYA ADMIN] EXPEDISI NOGOSARI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0090 | Excel R099 | 2026-02-26 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0090', 'BANK_KELUAR', 'TTC', '2026-02-26', 'February 2026', 312, 314, 7000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R99 | PETTYCASH] PETTYCASH JUAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-02-26', 'TRANS_BANK', @ref_id, 'BNK26-0090', 'DEBIT', 7000000.00, 7000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R99 | PETTYCASH] PETTYCASH JUAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-26', 'TRANS_BANK', @ref_id, 'BNK26-0090', 'KREDIT', 7000000.00, 0.00, 7000000.00, 0.00, 0.00, '[Excel R99 | PETTYCASH] PETTYCASH JUAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0091 | Excel R100 | 2026-02-27 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0091', 'BANK_MASUK', 'TTC', '2026-02-27', 'February 2026', 309, 452, 10953700.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R100 | PENJUALAN] ETIKET PDW 88 SKM 12', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-02-27', 'TRANS_BANK', @ref_id, 'BNK26-0091', 'DEBIT', 10953700.00, 10953700.00, 0.00, 0.00, 0.00, '[Excel R100 | PENJUALAN] ETIKET PDW 88 SKM 12', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-02-27', 'TRANS_BANK', @ref_id, 'BNK26-0091', 'KREDIT', 10953700.00, 0.00, 10953700.00, 0.00, 0.00, 'Lawan penerimaan [Excel R100 | PENJUALAN] ETIKET PDW 88 SKM 12', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0092 | Excel R101 | 2026-02-27 | 6610 (D) -> 1114 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0092', 'BANK_KELUAR', 'TTC', '2026-02-27', 'February 2026', 312, 542, 2095756.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R101 | PEMBAYARAN] REIMBURSE SHERLY', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-02-27', 'TRANS_BANK', @ref_id, 'BNK26-0092', 'DEBIT', 2095756.00, 2095756.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R101 | PEMBAYARAN] REIMBURSE SHERLY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-27', 'TRANS_BANK', @ref_id, 'BNK26-0092', 'KREDIT', 2095756.00, 0.00, 2095756.00, 0.00, 0.00, '[Excel R101 | PEMBAYARAN] REIMBURSE SHERLY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0093 | Excel R102 | 2026-02-27 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0093', 'BANK_KELUAR', 'TTC', '2026-02-27', 'February 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R102 | PEMBAYARAN] REIMBURSE SHERLY', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-02-27', 'TRANS_BANK', @ref_id, 'BNK26-0093', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R102 | PEMBAYARAN] REIMBURSE SHERLY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-27', 'TRANS_BANK', @ref_id, 'BNK26-0093', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R102 | PEMBAYARAN] REIMBURSE SHERLY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0094 | Excel R103 | 2026-02-28 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0094', 'BANK_MASUK', 'TTC', '2026-02-28', 'February 2026', 309, 452, 24000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R103 | PENJUALAN] ETIKET FLORENT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-02-28', 'TRANS_BANK', @ref_id, 'BNK26-0094', 'DEBIT', 24000000.00, 24000000.00, 0.00, 0.00, 0.00, '[Excel R103 | PENJUALAN] ETIKET FLORENT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-02-28', 'TRANS_BANK', @ref_id, 'BNK26-0094', 'KREDIT', 24000000.00, 0.00, 24000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R103 | PENJUALAN] ETIKET FLORENT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0095 | Excel R104 | 2026-02-28 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0095', 'BANK_MASUK', 'TTC', '2026-02-28', 'February 2026', 309, 452, 6890000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R104 | PENJUALAN] INNER JB', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-02-28', 'TRANS_BANK', @ref_id, 'BNK26-0095', 'DEBIT', 6890000.00, 6890000.00, 0.00, 0.00, 0.00, '[Excel R104 | PENJUALAN] INNER JB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-02-28', 'TRANS_BANK', @ref_id, 'BNK26-0095', 'KREDIT', 6890000.00, 0.00, 6890000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R104 | PENJUALAN] INNER JB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0096 | Excel R105 | 2026-02-28 | 6301 (D) -> 1114 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0096', 'BANK_KELUAR', 'TTC', '2026-02-28', 'February 2026', 312, 528, 13476400.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R105 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-02-28', 'TRANS_BANK', @ref_id, 'BNK26-0096', 'DEBIT', 13476400.00, 13476400.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R105 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-02-28', 'TRANS_BANK', @ref_id, 'BNK26-0096', 'KREDIT', 13476400.00, 0.00, 13476400.00, 0.00, 0.00, '[Excel R105 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0097 | Excel R108 | 2026-03-04 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0097', 'BANK_KELUAR', 'TTC', '2026-03-04', 'March 2026', 312, 314, 4000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R108 | PETTYCASH] PETTYCASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-03-04', 'TRANS_BANK', @ref_id, 'BNK26-0097', 'DEBIT', 4000000.00, 4000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R108 | PETTYCASH] PETTYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-04', 'TRANS_BANK', @ref_id, 'BNK26-0097', 'KREDIT', 4000000.00, 0.00, 4000000.00, 0.00, 0.00, '[Excel R108 | PETTYCASH] PETTYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0098 | Excel R109 | 2026-03-04 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0098', 'BANK_KELUAR', 'TTC', '2026-03-04', 'March 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R109 | PETTYCASH] PETTYCASH YAUL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-03-04', 'TRANS_BANK', @ref_id, 'BNK26-0098', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R109 | PETTYCASH] PETTYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-04', 'TRANS_BANK', @ref_id, 'BNK26-0098', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R109 | PETTYCASH] PETTYCASH YAUL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0099 | Excel R110 | 2026-03-07 | 6402 (D) -> 1114 (K) (Retribusi & Perizinan (NIB / Domisili / Izin))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0099', 'BANK_KELUAR', 'TTC', '2026-03-07', 'March 2026', 312, 535, 1500000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R110 | PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(535, '2026-03-07', 'TRANS_BANK', @ref_id, 'BNK26-0099', 'DEBIT', 1500000.00, 1500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R110 | PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-07', 'TRANS_BANK', @ref_id, 'BNK26-0099', 'KREDIT', 1500000.00, 0.00, 1500000.00, 0.00, 0.00, '[Excel R110 | PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0100 | Excel R111 | 2026-03-07 | 6402 (D) -> 1114 (K) (Retribusi & Perizinan (NIB / Domisili / Izin))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0100', 'BANK_KELUAR', 'TTC', '2026-03-07', 'March 2026', 312, 535, 1500000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R111 | PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(535, '2026-03-07', 'TRANS_BANK', @ref_id, 'BNK26-0100', 'DEBIT', 1500000.00, 1500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R111 | PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-07', 'TRANS_BANK', @ref_id, 'BNK26-0100', 'KREDIT', 1500000.00, 0.00, 1500000.00, 0.00, 0.00, '[Excel R111 | PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0101 | Excel R112 | 2026-03-07 | 1111 (D) -> 6402 (K) (Refund Biaya Pengurusan NIB Extra)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0101', 'BANK_MASUK', 'TTC', '2026-03-07', 'March 2026', 309, 535, 1500000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R112 | PENERIMAAN] REFUND', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-03-07', 'TRANS_BANK', @ref_id, 'BNK26-0101', 'DEBIT', 1500000.00, 1500000.00, 0.00, 0.00, 0.00, '[Excel R112 | PENERIMAAN] REFUND', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(535, '2026-03-07', 'TRANS_BANK', @ref_id, 'BNK26-0101', 'KREDIT', 1500000.00, 0.00, 1500000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R112 | PENERIMAAN] REFUND', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #KAS26-0005 | Excel R113 | 2026-03-08 | 1116 (D) -> 2142 (K) (Pinjaman Modal Indigama (Opsi 2A))
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0005', 'KAS_MASUK', 'TTC', '2026-03-08', 'March 2026', NULL, 422, 314, NULL, 34082882.00, 'ACTIVE', '[Excel R113 | MODAL] MODAL PINJAMAN PEMBELIAN KURSI, MEJA KANTOR', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-03-08', 'TRANS_KAS', @ref_id, 'KAS26-0005', 'DEBIT', 34082882.00, 34082882.00, 0.00, 0.00, 0.00, '[Excel R113 | MODAL] MODAL PINJAMAN PEMBELIAN KURSI, MEJA KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(422, '2026-03-08', 'TRANS_KAS', @ref_id, 'KAS26-0005', 'KREDIT', 34082882.00, 0.00, 34082882.00, 0.00, 0.00, 'Lawan penerimaan [Excel R113 | MODAL] MODAL PINJAMAN PEMBELIAN KURSI, MEJA KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #KAS26-0006 | Excel R114 | 2026-03-08 | 1241 (D) -> 1116 (K) (Aset Tetap - Furniture & Fixtures Kantor)
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0006', 'KAS_KELUAR', 'TTC', '2026-03-08', 'March 2026', NULL, 367, 314, NULL, 34082882.00, 'ACTIVE', '[Excel R114 | PEMBAYARAN] PEMBELIAN KURSI, MEJA DAN LEMARI KANTOR', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(367, '2026-03-08', 'TRANS_KAS', @ref_id, 'KAS26-0006', 'DEBIT', 34082882.00, 34082882.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R114 | PEMBAYARAN] PEMBELIAN KURSI, MEJA DAN LEMARI KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(314, '2026-03-08', 'TRANS_KAS', @ref_id, 'KAS26-0006', 'KREDIT', 34082882.00, 0.00, 34082882.00, 0.00, 0.00, '[Excel R114 | PEMBAYARAN] PEMBELIAN KURSI, MEJA DAN LEMARI KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0102 | Excel R115 | 2026-03-13 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0102', 'BANK_MASUK', 'TTC', '2026-03-13', 'March 2026', 309, 452, 4650000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R115 | PENJUALAN] ETIKET PINANG MAS SKT 16', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-03-13', 'TRANS_BANK', @ref_id, 'BNK26-0102', 'DEBIT', 4650000.00, 4650000.00, 0.00, 0.00, 0.00, '[Excel R115 | PENJUALAN] ETIKET PINANG MAS SKT 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-03-13', 'TRANS_BANK', @ref_id, 'BNK26-0102', 'KREDIT', 4650000.00, 0.00, 4650000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R115 | PENJUALAN] ETIKET PINANG MAS SKT 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0103 | Excel R116 | 2026-03-13 | 1221 (D) -> 1114 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0103', 'BANK_KELUAR', 'TTC', '2026-03-13', 'March 2026', 312, 355, 152500000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R116 | PEMBAYARAN] DP INSTALASI LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-03-13', 'TRANS_BANK', @ref_id, 'BNK26-0103', 'DEBIT', 152500000.00, 152500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R116 | PEMBAYARAN] DP INSTALASI LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-13', 'TRANS_BANK', @ref_id, 'BNK26-0103', 'KREDIT', 152500000.00, 0.00, 152500000.00, 0.00, 0.00, '[Excel R116 | PEMBAYARAN] DP INSTALASI LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0104 | Excel R117 | 2026-03-13 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0104', 'BANK_KELUAR', 'TTC', '2026-03-13', 'March 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R117 | PEMBAYARAN] DP INSTALASI LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-03-13', 'TRANS_BANK', @ref_id, 'BNK26-0104', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R117 | PEMBAYARAN] DP INSTALASI LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-13', 'TRANS_BANK', @ref_id, 'BNK26-0104', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R117 | PEMBAYARAN] DP INSTALASI LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0105 | Excel R118 | 2026-03-14 | 6610 (D) -> 1114 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0105', 'BANK_KELUAR', 'TTC', '2026-03-14', 'March 2026', 312, 542, 100000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R118 | PEMBAYARAN] REMBUERSE PAK HERI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-03-14', 'TRANS_BANK', @ref_id, 'BNK26-0105', 'DEBIT', 100000.00, 100000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R118 | PEMBAYARAN] REMBUERSE PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-14', 'TRANS_BANK', @ref_id, 'BNK26-0105', 'KREDIT', 100000.00, 0.00, 100000.00, 0.00, 0.00, '[Excel R118 | PEMBAYARAN] REMBUERSE PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0106 | Excel R119 | 2026-03-14 | 6302 (D) -> 1114 (K) (Tunjangan Hari Raya (THR) Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0106', 'BANK_KELUAR', 'TTC', '2026-03-14', 'March 2026', 312, 529, 11553000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R119 | PEMBAYARAN] TUNJANGAN H R 2026', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(529, '2026-03-14', 'TRANS_BANK', @ref_id, 'BNK26-0106', 'DEBIT', 11553000.00, 11553000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R119 | PEMBAYARAN] TUNJANGAN H R 2026', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-14', 'TRANS_BANK', @ref_id, 'BNK26-0106', 'KREDIT', 11553000.00, 0.00, 11553000.00, 0.00, 0.00, '[Excel R119 | PEMBAYARAN] TUNJANGAN H R 2026', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0107 | Excel R120 | 2026-03-16 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0107', 'BANK_MASUK', 'TTC', '2026-03-16', 'March 2026', 309, 452, 5692500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R120 | PENJUALAN] AMBRI DAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-03-16', 'TRANS_BANK', @ref_id, 'BNK26-0107', 'DEBIT', 5692500.00, 5692500.00, 0.00, 0.00, 0.00, '[Excel R120 | PENJUALAN] AMBRI DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-03-16', 'TRANS_BANK', @ref_id, 'BNK26-0107', 'KREDIT', 5692500.00, 0.00, 5692500.00, 0.00, 0.00, 'Lawan penerimaan [Excel R120 | PENJUALAN] AMBRI DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0108 | Excel R121 | 2026-03-20 | 1111 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0108', 'BANK_MASUK', 'TTC', '2026-03-20', 'March 2026', 309, 467, 17152.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R121 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-03-20', 'TRANS_BANK', @ref_id, 'BNK26-0108', 'DEBIT', 17152.00, 17152.00, 0.00, 0.00, 0.00, '[Excel R121 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-03-20', 'TRANS_BANK', @ref_id, 'BNK26-0108', 'KREDIT', 17152.00, 0.00, 17152.00, 0.00, 0.00, 'Lawan penerimaan [Excel R121 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0109 | Excel R122 | 2026-03-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0109', 'BANK_KELUAR', 'TTC', '2026-03-20', 'March 2026', 309, 523, 3430.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R122 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-03-20', 'TRANS_BANK', @ref_id, 'BNK26-0109', 'DEBIT', 3430.00, 3430.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R122 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-03-20', 'TRANS_BANK', @ref_id, 'BNK26-0109', 'KREDIT', 3430.00, 0.00, 3430.00, 0.00, 0.00, '[Excel R122 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0110 | Excel R123 | 2026-03-25 | 1114 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0110', 'BANK_MASUK', 'TTC', '2026-03-25', 'March 2026', 312, 467, 118383.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R123 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-03-25', 'TRANS_BANK', @ref_id, 'BNK26-0110', 'DEBIT', 118383.00, 118383.00, 0.00, 0.00, 0.00, '[Excel R123 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-03-25', 'TRANS_BANK', @ref_id, 'BNK26-0110', 'KREDIT', 118383.00, 0.00, 118383.00, 0.00, 0.00, 'Lawan penerimaan [Excel R123 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0111 | Excel R124 | 2026-03-25 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0111', 'BANK_KELUAR', 'TTC', '2026-03-25', 'March 2026', 312, 523, 23677.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R124 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-03-25', 'TRANS_BANK', @ref_id, 'BNK26-0111', 'DEBIT', 23677.00, 23677.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R124 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-03-25', 'TRANS_BANK', @ref_id, 'BNK26-0111', 'KREDIT', 23677.00, 0.00, 23677.00, 0.00, 0.00, '[Excel R124 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0112 | Excel R125 | 2026-03-31 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0112', 'BANK_KELUAR', 'TTC', '2026-03-31', 'March 2026', 309, 528, 13508495.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R125 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-03-31', 'TRANS_BANK', @ref_id, 'BNK26-0112', 'DEBIT', 13508495.00, 13508495.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R125 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-03-31', 'TRANS_BANK', @ref_id, 'BNK26-0112', 'KREDIT', 13508495.00, 0.00, 13508495.00, 0.00, 0.00, '[Excel R125 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0113 | Excel R128 | 2026-04-01 | 6205 (D) -> 1111 (K) (Biaya Profesional / Pembuatan Akta Notaris)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0113', 'BANK_KELUAR', 'TTC', '2026-04-01', 'April 2026', 309, 514, 8130000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R128 | PEMBAYARAN] PEMBUATAN AKTA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(514, '2026-04-01', 'TRANS_BANK', @ref_id, 'BNK26-0113', 'DEBIT', 8130000.00, 8130000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R128 | PEMBAYARAN] PEMBUATAN AKTA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-01', 'TRANS_BANK', @ref_id, 'BNK26-0113', 'KREDIT', 8130000.00, 0.00, 8130000.00, 0.00, 0.00, '[Excel R128 | PEMBAYARAN] PEMBUATAN AKTA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0114 | Excel R129 | 2026-04-01 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0114', 'BANK_KELUAR', 'TTC', '2026-04-01', 'April 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R129 | BIAYA ADMIN] ADMIN BANK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-01', 'TRANS_BANK', @ref_id, 'BNK26-0114', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R129 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-01', 'TRANS_BANK', @ref_id, 'BNK26-0114', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R129 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0115 | Excel R130 | 2026-04-05 | 6610 (D) -> 1111 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0115', 'BANK_KELUAR', 'TTC', '2026-04-05', 'April 2026', 309, 542, 2865500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R130 | PEMBAYARAN] REIMBURSE', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-04-05', 'TRANS_BANK', @ref_id, 'BNK26-0115', 'DEBIT', 2865500.00, 2865500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R130 | PEMBAYARAN] REIMBURSE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-05', 'TRANS_BANK', @ref_id, 'BNK26-0115', 'KREDIT', 2865500.00, 0.00, 2865500.00, 0.00, 0.00, '[Excel R130 | PEMBAYARAN] REIMBURSE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0116 | Excel R131 | 2026-04-05 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0116', 'BANK_KELUAR', 'TTC', '2026-04-05', 'April 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R131 | BIAYA ADMIN] ADMIN BANK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-05', 'TRANS_BANK', @ref_id, 'BNK26-0116', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R131 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-05', 'TRANS_BANK', @ref_id, 'BNK26-0116', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R131 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0117 | Excel R132 | 2026-04-07 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0117', 'BANK_MASUK', 'TTC', '2026-04-07', 'April 2026', 309, 452, 10000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R132 | PENJUALAN] ETIKET APPLE', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-04-07', 'TRANS_BANK', @ref_id, 'BNK26-0117', 'DEBIT', 10000000.00, 10000000.00, 0.00, 0.00, 0.00, '[Excel R132 | PENJUALAN] ETIKET APPLE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-04-07', 'TRANS_BANK', @ref_id, 'BNK26-0117', 'KREDIT', 10000000.00, 0.00, 10000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R132 | PENJUALAN] ETIKET APPLE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0118 | Excel R133 | 2026-04-14 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0118', 'BANK_MASUK', 'TTC', '2026-04-14', 'April 2026', 309, 452, 7397500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R133 | PENJUALAN] AMBRI DAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-04-14', 'TRANS_BANK', @ref_id, 'BNK26-0118', 'DEBIT', 7397500.00, 7397500.00, 0.00, 0.00, 0.00, '[Excel R133 | PENJUALAN] AMBRI DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-04-14', 'TRANS_BANK', @ref_id, 'BNK26-0118', 'KREDIT', 7397500.00, 0.00, 7397500.00, 0.00, 0.00, 'Lawan penerimaan [Excel R133 | PENJUALAN] AMBRI DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0119 | Excel R134 | 2026-04-15 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0119', 'BANK_MASUK', 'TTC', '2026-04-15', 'April 2026', 309, 452, 1350000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R134 | PENJUALAN] ETIKET BARBARA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-04-15', 'TRANS_BANK', @ref_id, 'BNK26-0119', 'DEBIT', 1350000.00, 1350000.00, 0.00, 0.00, 0.00, '[Excel R134 | PENJUALAN] ETIKET BARBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-04-15', 'TRANS_BANK', @ref_id, 'BNK26-0119', 'KREDIT', 1350000.00, 0.00, 1350000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R134 | PENJUALAN] ETIKET BARBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0120 | Excel R135 | 2026-04-15 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0120', 'BANK_MASUK', 'TTC', '2026-04-15', 'April 2026', 309, 452, 20000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R135 | PENJUALAN] ETIKET BARBARA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-04-15', 'TRANS_BANK', @ref_id, 'BNK26-0120', 'DEBIT', 20000000.00, 20000000.00, 0.00, 0.00, 0.00, '[Excel R135 | PENJUALAN] ETIKET BARBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-04-15', 'TRANS_BANK', @ref_id, 'BNK26-0120', 'KREDIT', 20000000.00, 0.00, 20000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R135 | PENJUALAN] ETIKET BARBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0121 | Excel R136 | 2026-04-16 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0121', 'BANK_KELUAR', 'TTC', '2026-04-16', 'April 2026', 312, 523, 10000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R136 | BIAYA ADMIN] CEK REKENING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0121', 'DEBIT', 10000.00, 10000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R136 | BIAYA ADMIN] CEK REKENING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0121', 'KREDIT', 10000.00, 0.00, 10000.00, 0.00, 0.00, '[Excel R136 | BIAYA ADMIN] CEK REKENING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0122 | Excel R137 | 2026-04-16 | 1261 (D) -> 1114 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0122', 'BANK_KELUAR', 'TTC', '2026-04-16', 'April 2026', 312, 378, 1250000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R137 | PEMBAYARAN] REIUMBURSE DP KACA FILM 50%', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0122', 'DEBIT', 1250000.00, 1250000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R137 | PEMBAYARAN] REIUMBURSE DP KACA FILM 50%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0122', 'KREDIT', 1250000.00, 0.00, 1250000.00, 0.00, 0.00, '[Excel R137 | PEMBAYARAN] REIUMBURSE DP KACA FILM 50%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0123 | Excel R138 | 2026-04-16 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0123', 'BANK_KELUAR', 'TTC', '2026-04-16', 'April 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R138 | BIAYA ADMIN] REIUMBURSE DP KACA FILM 50%', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0123', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R138 | BIAYA ADMIN] REIUMBURSE DP KACA FILM 50%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0123', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R138 | BIAYA ADMIN] REIUMBURSE DP KACA FILM 50%', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0124 | Excel R139 | 2026-04-16 | 1261 (D) -> 1114 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0124', 'BANK_KELUAR', 'TTC', '2026-04-16', 'April 2026', 312, 378, 1250000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R139 | PEMBAYARAN] PELUNASAN KACA FILM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0124', 'DEBIT', 1250000.00, 1250000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R139 | PEMBAYARAN] PELUNASAN KACA FILM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0124', 'KREDIT', 1250000.00, 0.00, 1250000.00, 0.00, 0.00, '[Excel R139 | PEMBAYARAN] PELUNASAN KACA FILM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0125 | Excel R140 | 2026-04-16 | 6108 (D) -> 1114 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0125', 'BANK_KELUAR', 'TTC', '2026-04-16', 'April 2026', 312, 508, 1138000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R140 | PEMBAYARAN] KOMISI PAK GUNG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0125', 'DEBIT', 1138000.00, 1138000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R140 | PEMBAYARAN] KOMISI PAK GUNG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0125', 'KREDIT', 1138000.00, 0.00, 1138000.00, 0.00, 0.00, '[Excel R140 | PEMBAYARAN] KOMISI PAK GUNG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0126 | Excel R141 | 2026-04-16 | 6108 (D) -> 1114 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0126', 'BANK_KELUAR', 'TTC', '2026-04-16', 'April 2026', 312, 508, 239040.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R141 | PEMBAYARAN] KOMISI PAK HERI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0126', 'DEBIT', 239040.00, 239040.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R141 | PEMBAYARAN] KOMISI PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-16', 'TRANS_BANK', @ref_id, 'BNK26-0126', 'KREDIT', 239040.00, 0.00, 239040.00, 0.00, 0.00, '[Excel R141 | PEMBAYARAN] KOMISI PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0127 | Excel R142 | 2026-04-20 | 1111 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0127', 'BANK_MASUK', 'TTC', '2026-04-20', 'April 2026', 309, 467, 28415.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R142 | PENERIMAAN] INTERSENT ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-04-20', 'TRANS_BANK', @ref_id, 'BNK26-0127', 'DEBIT', 28415.00, 28415.00, 0.00, 0.00, 0.00, '[Excel R142 | PENERIMAAN] INTERSENT ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-04-20', 'TRANS_BANK', @ref_id, 'BNK26-0127', 'KREDIT', 28415.00, 0.00, 28415.00, 0.00, 0.00, 'Lawan penerimaan [Excel R142 | PENERIMAAN] INTERSENT ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0128 | Excel R143 | 2026-04-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0128', 'BANK_KELUAR', 'TTC', '2026-04-20', 'April 2026', 309, 523, 5683.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R143 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-20', 'TRANS_BANK', @ref_id, 'BNK26-0128', 'DEBIT', 5683.00, 5683.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R143 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-20', 'TRANS_BANK', @ref_id, 'BNK26-0128', 'KREDIT', 5683.00, 0.00, 5683.00, 0.00, 0.00, '[Excel R143 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0129 | Excel R144 | 2026-04-21 | 6216 (D) -> 1114 (K) (Perlengkapan & Peralatan Rumah Tangga Kantor)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0129', 'BANK_KELUAR', 'TTC', '2026-04-21', 'April 2026', 312, 525, 1160000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R144 | PEMBELIAN] REIMBURSE PERALATAN RUMAH TANGGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(525, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0129', 'DEBIT', 1160000.00, 1160000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R144 | PEMBELIAN] REIMBURSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0129', 'KREDIT', 1160000.00, 0.00, 1160000.00, 0.00, 0.00, '[Excel R144 | PEMBELIAN] REIMBURSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0130 | Excel R145 | 2026-04-21 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0130', 'BANK_KELUAR', 'TTC', '2026-04-21', 'April 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R145 | BIAYA ADMIN] REIMBURSE PERALATAN RUMAH TANGGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0130', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R145 | BIAYA ADMIN] REIMBURSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0130', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R145 | BIAYA ADMIN] REIMBURSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0131 | Excel R146 | 2026-04-21 | 6207 (D) -> 1114 (K) (Telepon & Internet)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0131', 'BANK_KELUAR', 'TTC', '2026-04-21', 'April 2026', 312, 516, 664194.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R146 | OPERASIONAL] TLKM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(516, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0131', 'DEBIT', 664194.00, 664194.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R146 | OPERASIONAL] TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0131', 'KREDIT', 664194.00, 0.00, 664194.00, 0.00, 0.00, '[Excel R146 | OPERASIONAL] TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0132 | Excel R147 | 2026-04-21 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0132', 'BANK_KELUAR', 'TTC', '2026-04-21', 'April 2026', 312, 523, 2800.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R147 | BIAYA ADMIN] TLKM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0132', 'DEBIT', 2800.00, 2800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R147 | BIAYA ADMIN] TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0132', 'KREDIT', 2800.00, 0.00, 2800.00, 0.00, 0.00, '[Excel R147 | BIAYA ADMIN] TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0133 | Excel R148 | 2026-04-21 | 6212 (D) -> 1114 (K) (Iuran Keamanan & Kebersihan Kawasan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0133', 'BANK_KELUAR', 'TTC', '2026-04-21', 'April 2026', 312, 521, 10032000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R148 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(521, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0133', 'DEBIT', 10032000.00, 10032000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R148 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0133', 'KREDIT', 10032000.00, 0.00, 10032000.00, 0.00, 0.00, '[Excel R148 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0134 | Excel R149 | 2026-04-21 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0134', 'BANK_KELUAR', 'TTC', '2026-04-21', 'April 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R149 | BIAYA ADMIN] IURAN KEAMANAN DAN KEBERSIHAN TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0134', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R149 | BIAYA ADMIN] IURAN KEAMANAN DAN KEBERSIHAN TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-21', 'TRANS_BANK', @ref_id, 'BNK26-0134', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R149 | BIAYA ADMIN] IURAN KEAMANAN DAN KEBERSIHAN TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0135 | Excel R150 | 2026-04-22 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0135', 'BANK_MASUK', 'TTC', '2026-04-22', 'April 2026', 309, 452, 10000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R150 | PENJUALAN] ETIKET DAFI SKT 16', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-04-22', 'TRANS_BANK', @ref_id, 'BNK26-0135', 'DEBIT', 10000000.00, 10000000.00, 0.00, 0.00, 0.00, '[Excel R150 | PENJUALAN] ETIKET DAFI SKT 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-04-22', 'TRANS_BANK', @ref_id, 'BNK26-0135', 'KREDIT', 10000000.00, 0.00, 10000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R150 | PENJUALAN] ETIKET DAFI SKT 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #KAS26-0007 | Excel R151 | 2026-04-22 | 1116 (D) -> 2142 (K) (Pinjaman Modal Indigama (Opsi 2A))
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0007', 'KAS_MASUK', 'TTC', '2026-04-22', 'April 2026', NULL, 422, 314, NULL, 160000000.00, 'ACTIVE', '[Excel R151 | MODAL] MODAL PINJAMAN INDIGAMA', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-04-22', 'TRANS_KAS', @ref_id, 'KAS26-0007', 'DEBIT', 160000000.00, 160000000.00, 0.00, 0.00, 0.00, '[Excel R151 | MODAL] MODAL PINJAMAN INDIGAMA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(422, '2026-04-22', 'TRANS_KAS', @ref_id, 'KAS26-0007', 'KREDIT', 160000000.00, 0.00, 160000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R151 | MODAL] MODAL PINJAMAN INDIGAMA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #KAS26-0008 | Excel R152 | 2026-04-22 | 1221 (D) -> 1116 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `karyawan_id`, `account_bank_id`, `account_kas_id`, `customer_id`, `nominal`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES ('KAS26-0008', 'KAS_KELUAR', 'TTC', '2026-04-22', 'April 2026', NULL, 355, 314, NULL, 160000000.00, 'ACTIVE', '[Excel R152 | PEMBAYARAN] LISTRIK - PEMBAYARAN DP TRAFO DAN CUBICLE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-04-22', 'TRANS_KAS', @ref_id, 'KAS26-0008', 'DEBIT', 160000000.00, 160000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R152 | PEMBAYARAN] LISTRIK - PEMBAYARAN DP TRAFO DAN CUBICLE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(314, '2026-04-22', 'TRANS_KAS', @ref_id, 'KAS26-0008', 'KREDIT', 160000000.00, 0.00, 160000000.00, 0.00, 0.00, '[Excel R152 | PEMBAYARAN] LISTRIK - PEMBAYARAN DP TRAFO DAN CUBICLE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0136 | Excel R153 | 2026-04-25 | 1114 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0136', 'BANK_MASUK', 'TTC', '2026-04-25', 'April 2026', 312, 467, 91027.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R153 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-04-25', 'TRANS_BANK', @ref_id, 'BNK26-0136', 'DEBIT', 91027.00, 91027.00, 0.00, 0.00, 0.00, '[Excel R153 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-04-25', 'TRANS_BANK', @ref_id, 'BNK26-0136', 'KREDIT', 91027.00, 0.00, 91027.00, 0.00, 0.00, 'Lawan penerimaan [Excel R153 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0137 | Excel R154 | 2026-04-25 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0137', 'BANK_KELUAR', 'TTC', '2026-04-25', 'April 2026', 312, 523, 18205.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R154 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-25', 'TRANS_BANK', @ref_id, 'BNK26-0137', 'DEBIT', 18205.00, 18205.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R154 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-25', 'TRANS_BANK', @ref_id, 'BNK26-0137', 'KREDIT', 18205.00, 0.00, 18205.00, 0.00, 0.00, '[Excel R154 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0138 | Excel R155 | 2026-04-26 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0138', 'BANK_KELUAR', 'TTC', '2026-04-26', 'April 2026', 309, 373, 15903856.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R155 | PEMBELIAN] MATERIAL KELENGKAPAN PT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-04-26', 'TRANS_BANK', @ref_id, 'BNK26-0138', 'DEBIT', 15903856.00, 15903856.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R155 | PEMBELIAN] MATERIAL KELENGKAPAN PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-26', 'TRANS_BANK', @ref_id, 'BNK26-0138', 'KREDIT', 15903856.00, 0.00, 15903856.00, 0.00, 0.00, '[Excel R155 | PEMBELIAN] MATERIAL KELENGKAPAN PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0139 | Excel R156 | 2026-04-26 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0139', 'BANK_KELUAR', 'TTC', '2026-04-26', 'April 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R156 | BIAYA ADMIN] ADMIN BANK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-26', 'TRANS_BANK', @ref_id, 'BNK26-0139', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R156 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-26', 'TRANS_BANK', @ref_id, 'BNK26-0139', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R156 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0140 | Excel R157 | 2026-04-27 | 6212 (D) -> 1114 (K) (Iuran Keamanan & Kebersihan Kawasan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0140', 'BANK_KELUAR', 'TTC', '2026-04-27', 'April 2026', 312, 521, 10032000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R157 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(521, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0140', 'DEBIT', 10032000.00, 10032000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R157 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0140', 'KREDIT', 10032000.00, 0.00, 10032000.00, 0.00, 0.00, '[Excel R157 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0141 | Excel R158 | 2026-04-27 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0141', 'BANK_KELUAR', 'TTC', '2026-04-27', 'April 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R158 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0141', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R158 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0141', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R158 | PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0142 | Excel R159 | 2026-04-27 | 6203 (D) -> 1114 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0142', 'BANK_KELUAR', 'TTC', '2026-04-27', 'April 2026', 312, 512, 103000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R159 | OPERASIONAL] REIMBURSE TOKEN LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0142', 'DEBIT', 103000.00, 103000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R159 | OPERASIONAL] REIMBURSE TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0142', 'KREDIT', 103000.00, 0.00, 103000.00, 0.00, 0.00, '[Excel R159 | OPERASIONAL] REIMBURSE TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0143 | Excel R160 | 2026-04-27 | 6209 (D) -> 1114 (K) (Bahan Bakar, E-Toll & Transportasi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0143', 'BANK_KELUAR', 'TTC', '2026-04-27', 'April 2026', 312, 518, 370000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R160 | OPERASIONAL] REIMBURSE E TOL DAN KONUMSI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(518, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0143', 'DEBIT', 370000.00, 370000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R160 | OPERASIONAL] REIMBURSE E TOL DAN KONUMSI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0143', 'KREDIT', 370000.00, 0.00, 370000.00, 0.00, 0.00, '[Excel R160 | OPERASIONAL] REIMBURSE E TOL DAN KONUMSI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0144 | Excel R161 | 2026-04-27 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0144', 'BANK_KELUAR', 'TTC', '2026-04-27', 'April 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R161 | OPERASIONAL] REIMBURSE E TOL DAN KONUMSI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0144', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R161 | OPERASIONAL] REIMBURSE E TOL DAN KONUMSI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-27', 'TRANS_BANK', @ref_id, 'BNK26-0144', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R161 | OPERASIONAL] REIMBURSE E TOL DAN KONUMSI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0145 | Excel R162 | 2026-04-28 | 6216 (D) -> 1114 (K) (Perlengkapan & Peralatan Rumah Tangga Kantor)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0145', 'BANK_KELUAR', 'TTC', '2026-04-28', 'April 2026', 312, 525, 116000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R162 | PEMBELIAN] PERALATAN RUMAH TANGGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(525, '2026-04-28', 'TRANS_BANK', @ref_id, 'BNK26-0145', 'DEBIT', 116000.00, 116000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R162 | PEMBELIAN] PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-28', 'TRANS_BANK', @ref_id, 'BNK26-0145', 'KREDIT', 116000.00, 0.00, 116000.00, 0.00, 0.00, '[Excel R162 | PEMBELIAN] PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0146 | Excel R163 | 2026-04-30 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0146', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 309, 528, 13608490.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R163 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0146', 'DEBIT', 13608490.00, 13608490.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R163 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0146', 'KREDIT', 13608490.00, 0.00, 13608490.00, 0.00, 0.00, '[Excel R163 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0147 | Excel R164 | 2026-04-30 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0147', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 309, 528, 2280000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R164 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0147', 'DEBIT', 2280000.00, 2280000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R164 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0147', 'KREDIT', 2280000.00, 0.00, 2280000.00, 0.00, 0.00, '[Excel R164 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0148 | Excel R165 | 2026-04-30 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0148', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R165 | BIAYA ADMIN] ADMIN BANK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0148', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R165 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0148', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R165 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0149 | Excel R166 | 2026-04-30 | 6209 (D) -> 1114 (K) (Bahan Bakar, E-Toll & Transportasi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0149', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 312, 518, 200000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R166 | OPERASIONAL] REIMBURSE E TOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(518, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0149', 'DEBIT', 200000.00, 200000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R166 | OPERASIONAL] REIMBURSE E TOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0149', 'KREDIT', 200000.00, 0.00, 200000.00, 0.00, 0.00, '[Excel R166 | OPERASIONAL] REIMBURSE E TOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0150 | Excel R167 | 2026-04-30 | 6209 (D) -> 1114 (K) (Bahan Bakar, E-Toll & Transportasi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0150', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 312, 518, 126000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R167 | OPERASIONAL] REIMBURSE UANG TRANSPORT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(518, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0150', 'DEBIT', 126000.00, 126000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R167 | OPERASIONAL] REIMBURSE UANG TRANSPORT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0150', 'KREDIT', 126000.00, 0.00, 126000.00, 0.00, 0.00, '[Excel R167 | OPERASIONAL] REIMBURSE UANG TRANSPORT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0151 | Excel R168 | 2026-04-30 | 6216 (D) -> 1114 (K) (Perlengkapan & Peralatan Rumah Tangga Kantor)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0151', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 312, 525, 1108950.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R168 | PEMBELIAN] REIMBUERSE PERALATAN RUMAH TANGGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(525, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0151', 'DEBIT', 1108950.00, 1108950.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R168 | PEMBELIAN] REIMBUERSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0151', 'KREDIT', 1108950.00, 0.00, 1108950.00, 0.00, 0.00, '[Excel R168 | PEMBELIAN] REIMBUERSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0152 | Excel R169 | 2026-04-30 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0152', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R169 | BIAYA ADMIN] ADMIN BANK REIMBUERSE PERALATAN RUMAH TANGGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0152', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R169 | BIAYA ADMIN] ADMIN BANK REIMBUERSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0152', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R169 | BIAYA ADMIN] ADMIN BANK REIMBUERSE PERALATAN RUMAH TANGGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0153 | Excel R170 | 2026-04-30 | 6209 (D) -> 1114 (K) (Bahan Bakar, E-Toll & Transportasi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0153', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 312, 518, 200000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R170 | OPERASIONAL] REIMBUERSE E TOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(518, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0153', 'DEBIT', 200000.00, 200000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R170 | OPERASIONAL] REIMBUERSE E TOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0153', 'KREDIT', 200000.00, 0.00, 200000.00, 0.00, 0.00, '[Excel R170 | OPERASIONAL] REIMBUERSE E TOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0154 | Excel R171 | 2026-04-30 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0154', 'BANK_KELUAR', 'TTC', '2026-04-30', 'April 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R171 | BIAYA ADMIN] ADMIN BANK REIMBUERSE E TOL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0154', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R171 | BIAYA ADMIN] ADMIN BANK REIMBUERSE E TOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-04-30', 'TRANS_BANK', @ref_id, 'BNK26-0154', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R171 | BIAYA ADMIN] ADMIN BANK REIMBUERSE E TOL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0155 | Excel R174 | 2026-05-05 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0155', 'BANK_MASUK', 'TTC', '2026-05-05', 'May 2026', 309, 452, 12766000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R174 | PENJUALAN] CYLINDER ET BARLBARA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-05-05', 'TRANS_BANK', @ref_id, 'BNK26-0155', 'DEBIT', 12766000.00, 12766000.00, 0.00, 0.00, 0.00, '[Excel R174 | PENJUALAN] CYLINDER ET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-05-05', 'TRANS_BANK', @ref_id, 'BNK26-0155', 'KREDIT', 12766000.00, 0.00, 12766000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R174 | PENJUALAN] CYLINDER ET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0156 | Excel R175 | 2026-05-08 | 6207 (D) -> 1114 (K) (Telepon & Internet)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0156', 'BANK_KELUAR', 'TTC', '2026-05-08', 'May 2026', 312, 516, 1254300.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R175 | OPERASIONAL] TAGIHAN WIFI TELKOM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(516, '2026-05-08', 'TRANS_BANK', @ref_id, 'BNK26-0156', 'DEBIT', 1254300.00, 1254300.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R175 | OPERASIONAL] TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-08', 'TRANS_BANK', @ref_id, 'BNK26-0156', 'KREDIT', 1254300.00, 0.00, 1254300.00, 0.00, 0.00, '[Excel R175 | OPERASIONAL] TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0157 | Excel R176 | 2026-05-08 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0157', 'BANK_KELUAR', 'TTC', '2026-05-08', 'May 2026', 312, 523, 2800.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R176 | BIAYA ADMIN] ADMIN BANK TELOKM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-08', 'TRANS_BANK', @ref_id, 'BNK26-0157', 'DEBIT', 2800.00, 2800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R176 | BIAYA ADMIN] ADMIN BANK TELOKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-08', 'TRANS_BANK', @ref_id, 'BNK26-0157', 'KREDIT', 2800.00, 0.00, 2800.00, 0.00, 0.00, '[Excel R176 | BIAYA ADMIN] ADMIN BANK TELOKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0158 | Excel R177 | 2026-05-09 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0158', 'BANK_MASUK', 'TTC', '2026-05-09', 'May 2026', 309, 452, 5850000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R177 | PENJUALAN] CTP BARLBARA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-05-09', 'TRANS_BANK', @ref_id, 'BNK26-0158', 'DEBIT', 5850000.00, 5850000.00, 0.00, 0.00, 0.00, '[Excel R177 | PENJUALAN] CTP BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-05-09', 'TRANS_BANK', @ref_id, 'BNK26-0158', 'KREDIT', 5850000.00, 0.00, 5850000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R177 | PENJUALAN] CTP BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0159 | Excel R178 | 2026-05-09 | 6203 (D) -> 1111 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0159', 'BANK_KELUAR', 'TTC', '2026-05-09', 'May 2026', 309, 512, 100000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R178 | PEMBELIAN] TOKEN LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-05-09', 'TRANS_BANK', @ref_id, 'BNK26-0159', 'DEBIT', 100000.00, 100000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R178 | PEMBELIAN] TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-09', 'TRANS_BANK', @ref_id, 'BNK26-0159', 'KREDIT', 100000.00, 0.00, 100000.00, 0.00, 0.00, '[Excel R178 | PEMBELIAN] TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0160 | Excel R179 | 2026-05-09 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0160', 'BANK_KELUAR', 'TTC', '2026-05-09', 'May 2026', 309, 523, 3000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R179 | BIAYA ADMIN] ADMIN TOKEN LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-09', 'TRANS_BANK', @ref_id, 'BNK26-0160', 'DEBIT', 3000.00, 3000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R179 | BIAYA ADMIN] ADMIN TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-09', 'TRANS_BANK', @ref_id, 'BNK26-0160', 'KREDIT', 3000.00, 0.00, 3000.00, 0.00, 0.00, '[Excel R179 | BIAYA ADMIN] ADMIN TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0161 | Excel R180 | 2026-05-11 | 6203 (D) -> 1114 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0161', 'BANK_KELUAR', 'TTC', '2026-05-11', 'May 2026', 312, 512, 102000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R180 | OPERASIONAL] PEMBAYARAN PDAM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-05-11', 'TRANS_BANK', @ref_id, 'BNK26-0161', 'DEBIT', 102000.00, 102000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R180 | OPERASIONAL] PEMBAYARAN PDAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-11', 'TRANS_BANK', @ref_id, 'BNK26-0161', 'KREDIT', 102000.00, 0.00, 102000.00, 0.00, 0.00, '[Excel R180 | OPERASIONAL] PEMBAYARAN PDAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0162 | Excel R181 | 2026-05-13 | 6503 (D) -> 1111 (K) (Program Kesejahteraan / Seragam / Paspor Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0162', 'BANK_KELUAR', 'TTC', '2026-05-13', 'May 2026', 309, 539, 650000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R181 | PEMBAYARAN] PEMBUATAN PASPOR KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(539, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0162', 'DEBIT', 650000.00, 650000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R181 | PEMBAYARAN] PEMBUATAN PASPOR KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0162', 'KREDIT', 650000.00, 0.00, 650000.00, 0.00, 0.00, '[Excel R181 | PEMBAYARAN] PEMBUATAN PASPOR KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0163 | Excel R182 | 2026-05-13 | 1116 (D) -> 1114 (K) (Pengisian / Transfer ke Petty Cash)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0163', 'BANK_KELUAR', 'TTC', '2026-05-13', 'May 2026', 312, 314, 10000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R182 | PETTYCASH] PETYCASH INTERNAL INAM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(314, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0163', 'DEBIT', 10000000.00, 10000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R182 | PETTYCASH] PETYCASH INTERNAL INAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0163', 'KREDIT', 10000000.00, 0.00, 10000000.00, 0.00, 0.00, '[Excel R182 | PETTYCASH] PETYCASH INTERNAL INAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0164 | Excel R183 | 2026-05-13 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0164', 'BANK_KELUAR', 'TTC', '2026-05-13', 'May 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R183 | BIAYA ADMIN] ADMIN BANK PETYCASH INTERNAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0164', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R183 | BIAYA ADMIN] ADMIN BANK PETYCASH INTERNAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0164', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R183 | BIAYA ADMIN] ADMIN BANK PETYCASH INTERNAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0165 | Excel R184 | 2026-05-13 | 6205 (D) -> 1114 (K) (Biaya Profesional / Pembuatan Akta Notaris)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0165', 'BANK_KELUAR', 'TTC', '2026-05-13', 'May 2026', 312, 514, 4500000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R184 | PEMBAYARAN] PEMBUATAN AKTA PT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(514, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0165', 'DEBIT', 4500000.00, 4500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R184 | PEMBAYARAN] PEMBUATAN AKTA PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-13', 'TRANS_BANK', @ref_id, 'BNK26-0165', 'KREDIT', 4500000.00, 0.00, 4500000.00, 0.00, 0.00, '[Excel R184 | PEMBAYARAN] PEMBUATAN AKTA PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0166 | Excel R185 | 2026-05-14 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0166', 'BANK_KELUAR', 'TTC', '2026-05-14', 'May 2026', 309, 355, 50096250.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R185 | PEMBELIAN] LISTRIK - DP MATERIAL PENDUKUNG TRAVO SUTM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-05-14', 'TRANS_BANK', @ref_id, 'BNK26-0166', 'DEBIT', 50096250.00, 50096250.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R185 | PEMBELIAN] LISTRIK - DP MATERIAL PENDUKUNG TRAVO SUTM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-14', 'TRANS_BANK', @ref_id, 'BNK26-0166', 'KREDIT', 50096250.00, 0.00, 50096250.00, 0.00, 0.00, '[Excel R185 | PEMBELIAN] LISTRIK - DP MATERIAL PENDUKUNG TRAVO SUTM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0167 | Excel R186 | 2026-05-14 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0167', 'BANK_KELUAR', 'TTC', '2026-05-14', 'May 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R186 | PEMBELIAN] ADMIN BANK DP MATERIAL PENDUKUNG TRAVO SUTM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-14', 'TRANS_BANK', @ref_id, 'BNK26-0167', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R186 | PEMBELIAN] ADMIN BANK DP MATERIAL PENDUKUNG TRAVO SUTM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-14', 'TRANS_BANK', @ref_id, 'BNK26-0167', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R186 | PEMBELIAN] ADMIN BANK DP MATERIAL PENDUKUNG TRAVO SUTM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0168 | Excel R187 | 2026-05-15 | 1261 (D) -> 1114 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0168', 'BANK_KELUAR', 'TTC', '2026-05-15', 'May 2026', 312, 378, 2100000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R187 | PEMBAYARAN] JASA PENGECATAN KANTOR', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-05-15', 'TRANS_BANK', @ref_id, 'BNK26-0168', 'DEBIT', 2100000.00, 2100000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R187 | PEMBAYARAN] JASA PENGECATAN KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-15', 'TRANS_BANK', @ref_id, 'BNK26-0168', 'KREDIT', 2100000.00, 0.00, 2100000.00, 0.00, 0.00, '[Excel R187 | PEMBAYARAN] JASA PENGECATAN KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0169 | Excel R188 | 2026-05-15 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0169', 'BANK_KELUAR', 'TTC', '2026-05-15', 'May 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R188 | BIAYA ADMIN] ADMIN BANK JASA PENGECATAN KANTOR', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-15', 'TRANS_BANK', @ref_id, 'BNK26-0169', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R188 | BIAYA ADMIN] ADMIN BANK JASA PENGECATAN KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-15', 'TRANS_BANK', @ref_id, 'BNK26-0169', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R188 | BIAYA ADMIN] ADMIN BANK JASA PENGECATAN KANTOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0170 | Excel R189 | 2026-05-18 | 1261 (D) -> 1111 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0170', 'BANK_KELUAR', 'TTC', '2026-05-18', 'May 2026', 309, 378, 1500000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R189 | PEMBAYARAN] DP DESIGN RUANGAN PARTISI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-05-18', 'TRANS_BANK', @ref_id, 'BNK26-0170', 'DEBIT', 1500000.00, 1500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R189 | PEMBAYARAN] DP DESIGN RUANGAN PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-18', 'TRANS_BANK', @ref_id, 'BNK26-0170', 'KREDIT', 1500000.00, 0.00, 1500000.00, 0.00, 0.00, '[Excel R189 | PEMBAYARAN] DP DESIGN RUANGAN PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0171 | Excel R190 | 2026-05-18 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0171', 'BANK_KELUAR', 'TTC', '2026-05-18', 'May 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R190 | BIAYA ADMIN] ADMIN BANK DP DESIGN RUANGAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-18', 'TRANS_BANK', @ref_id, 'BNK26-0171', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R190 | BIAYA ADMIN] ADMIN BANK DP DESIGN RUANGAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-18', 'TRANS_BANK', @ref_id, 'BNK26-0171', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R190 | BIAYA ADMIN] ADMIN BANK DP DESIGN RUANGAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0172 | Excel R191 | 2026-05-20 | 1111 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0172', 'BANK_MASUK', 'TTC', '2026-05-20', 'May 2026', 309, 467, 29702.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R191 | PENERIMAAN] INTERSET ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0172', 'DEBIT', 29702.00, 29702.00, 0.00, 0.00, 0.00, '[Excel R191 | PENERIMAAN] INTERSET ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0172', 'KREDIT', 29702.00, 0.00, 29702.00, 0.00, 0.00, 'Lawan penerimaan [Excel R191 | PENERIMAAN] INTERSET ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0173 | Excel R192 | 2026-05-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0173', 'BANK_KELUAR', 'TTC', '2026-05-20', 'May 2026', 309, 523, 5940.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R192 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0173', 'DEBIT', 5940.00, 5940.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R192 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0173', 'KREDIT', 5940.00, 0.00, 5940.00, 0.00, 0.00, '[Excel R192 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0174 | Excel R193 | 2026-05-20 | 1112 (D) -> 1114 (K) (Transfer Antar-Bank (BRI Giro -> BCA, gabung R193+R195))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0174', 'BANK_KELUAR', 'TTC', '2026-05-20', 'May 2026', 312, 310, 2500000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R193 | PEMBAYARAN] BUKA REKENING BCA (Transfer Antar-Bank BRI Giro -> BCA, gabung R193 & R195)', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0174', 'DEBIT', 2500000.00, 2500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R193 | PEMBAYARAN] BUKA REKENING BCA (Transfer Antar-Bank BRI Giro -> BCA, gabung R193 & R195)', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0174', 'KREDIT', 2500000.00, 0.00, 2500000.00, 0.00, 0.00, '[Excel R193 | PEMBAYARAN] BUKA REKENING BCA (Transfer Antar-Bank BRI Giro -> BCA, gabung R193 & R195)', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0175 | Excel R194 | 2026-05-20 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0175', 'BANK_KELUAR', 'TTC', '2026-05-20', 'May 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R194 | BIAYA ADMIN] ADMIN BANK BUKA REKENING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0175', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R194 | BIAYA ADMIN] ADMIN BANK BUKA REKENING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0175', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R194 | BIAYA ADMIN] ADMIN BANK BUKA REKENING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0176 | Excel R196 | 2026-05-20 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0176', 'BANK_KELUAR', 'TTC', '2026-05-20', 'May 2026', 310, 523, 50000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R196 | PEMBELIAN] POT BY KEY', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0176', 'DEBIT', 50000.00, 50000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R196 | PEMBELIAN] POT BY KEY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-05-20', 'TRANS_BANK', @ref_id, 'BNK26-0176', 'KREDIT', 50000.00, 0.00, 50000.00, 0.00, 0.00, '[Excel R196 | PEMBELIAN] POT BY KEY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0177 | Excel R197 | 2026-05-21 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0177', 'BANK_KELUAR', 'TTC', '2026-05-21', 'May 2026', 309, 355, 2600000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R197 | PEMBELIAN] PRINTER EPSON L3251', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-05-21', 'TRANS_BANK', @ref_id, 'BNK26-0177', 'DEBIT', 2600000.00, 2600000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R197 | PEMBELIAN] PRINTER EPSON L3251', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-21', 'TRANS_BANK', @ref_id, 'BNK26-0177', 'KREDIT', 2600000.00, 0.00, 2600000.00, 0.00, 0.00, '[Excel R197 | PEMBELIAN] PRINTER EPSON L3251', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0178 | Excel R198 | 2026-05-21 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0178', 'BANK_KELUAR', 'TTC', '2026-05-21', 'May 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R198 | BIAYA ADMIN] ADMIN BANK PRINTER EPSON L3251', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-21', 'TRANS_BANK', @ref_id, 'BNK26-0178', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R198 | BIAYA ADMIN] ADMIN BANK PRINTER EPSON L3251', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-21', 'TRANS_BANK', @ref_id, 'BNK26-0178', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R198 | BIAYA ADMIN] ADMIN BANK PRINTER EPSON L3251', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0179 | Excel R199 | 2026-05-25 | 1114 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0179', 'BANK_MASUK', 'TTC', '2026-05-25', 'May 2026', 312, 467, 74708.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R199 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(312, '2026-05-25', 'TRANS_BANK', @ref_id, 'BNK26-0179', 'DEBIT', 74708.00, 74708.00, 0.00, 0.00, 0.00, '[Excel R199 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-05-25', 'TRANS_BANK', @ref_id, 'BNK26-0179', 'KREDIT', 74708.00, 0.00, 74708.00, 0.00, 0.00, 'Lawan penerimaan [Excel R199 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0180 | Excel R200 | 2026-05-25 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0180', 'BANK_KELUAR', 'TTC', '2026-05-25', 'May 2026', 312, 523, 14942.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R200 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-25', 'TRANS_BANK', @ref_id, 'BNK26-0180', 'DEBIT', 14942.00, 14942.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R200 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-25', 'TRANS_BANK', @ref_id, 'BNK26-0180', 'KREDIT', 14942.00, 0.00, 14942.00, 0.00, 0.00, '[Excel R200 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0181 | Excel R201 | 2026-05-26 | 1111 (D) -> 1114 (K) (Transfer Antar-Bank (BRI Giro -> BRI Tabungan, gabung R201+R202))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0181', 'BANK_KELUAR', 'TTC', '2026-05-26', 'May 2026', 312, 309, 140000000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R201 | PEMBAYARAN] PEMINDAHAN DANA (Transfer Antar-Bank BRI Giro -> BRI Tabungan, gabung R201 & R202)', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-05-26', 'TRANS_BANK', @ref_id, 'BNK26-0181', 'DEBIT', 140000000.00, 140000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R201 | PEMBAYARAN] PEMINDAHAN DANA (Transfer Antar-Bank BRI Giro -> BRI Tabungan, gabung R201 & R202)', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-05-26', 'TRANS_BANK', @ref_id, 'BNK26-0181', 'KREDIT', 140000000.00, 0.00, 140000000.00, 0.00, 0.00, '[Excel R201 | PEMBAYARAN] PEMINDAHAN DANA (Transfer Antar-Bank BRI Giro -> BRI Tabungan, gabung R201 & R202)', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0182 | Excel R203 | 2026-05-26 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0182', 'BANK_KELUAR', 'TTC', '2026-05-26', 'May 2026', 309, 355, 133404000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R203 | PEMBELIAN] LISTRIK - DP MATERIAL LVMDP', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-05-26', 'TRANS_BANK', @ref_id, 'BNK26-0182', 'DEBIT', 133404000.00, 133404000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R203 | PEMBELIAN] LISTRIK - DP MATERIAL LVMDP', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-26', 'TRANS_BANK', @ref_id, 'BNK26-0182', 'KREDIT', 133404000.00, 0.00, 133404000.00, 0.00, 0.00, '[Excel R203 | PEMBELIAN] LISTRIK - DP MATERIAL LVMDP', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0183 | Excel R204 | 2026-05-26 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0183', 'BANK_KELUAR', 'TTC', '2026-05-26', 'May 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R204 | BIAYA ADMIN] ADMIN BANK DP MATERIAL LVMDP', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-26', 'TRANS_BANK', @ref_id, 'BNK26-0183', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R204 | BIAYA ADMIN] ADMIN BANK DP MATERIAL LVMDP', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-26', 'TRANS_BANK', @ref_id, 'BNK26-0183', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R204 | BIAYA ADMIN] ADMIN BANK DP MATERIAL LVMDP', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0184 | Excel R205 | 2026-05-30 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0184', 'BANK_KELUAR', 'TTC', '2026-05-30', 'May 2026', 309, 528, 11714000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R205 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-05-30', 'TRANS_BANK', @ref_id, 'BNK26-0184', 'DEBIT', 11714000.00, 11714000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R205 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-30', 'TRANS_BANK', @ref_id, 'BNK26-0184', 'KREDIT', 11714000.00, 0.00, 11714000.00, 0.00, 0.00, '[Excel R205 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0185 | Excel R206 | 2026-05-30 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0185', 'BANK_KELUAR', 'TTC', '2026-05-30', 'May 2026', 309, 528, 3496000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R206 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-05-30', 'TRANS_BANK', @ref_id, 'BNK26-0185', 'DEBIT', 3496000.00, 3496000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R206 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-30', 'TRANS_BANK', @ref_id, 'BNK26-0185', 'KREDIT', 3496000.00, 0.00, 3496000.00, 0.00, 0.00, '[Excel R206 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0186 | Excel R207 | 2026-05-30 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0186', 'BANK_KELUAR', 'TTC', '2026-05-30', 'May 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R207 | BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-30', 'TRANS_BANK', @ref_id, 'BNK26-0186', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R207 | BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-05-30', 'TRANS_BANK', @ref_id, 'BNK26-0186', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R207 | BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0187 | Excel R208 | 2026-05-31 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0187', 'BANK_KELUAR', 'TTC', '2026-05-31', 'May 2026', 310, 523, 30000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R208 | BIAYA ADMIN] ADMIN BANK BCA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-05-31', 'TRANS_BANK', @ref_id, 'BNK26-0187', 'DEBIT', 30000.00, 30000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R208 | BIAYA ADMIN] ADMIN BANK BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-05-31', 'TRANS_BANK', @ref_id, 'BNK26-0187', 'KREDIT', 30000.00, 0.00, 30000.00, 0.00, 0.00, '[Excel R208 | BIAYA ADMIN] ADMIN BANK BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0188 | Excel R211 | 2026-06-01 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0188', 'BANK_KELUAR', 'TTC', '2026-06-01', 'June 2026', 310, 523, 30000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R211 | BIAYA ADMIN] ADMIN BANK BCA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-01', 'TRANS_BANK', @ref_id, 'BNK26-0188', 'DEBIT', 30000.00, 30000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R211 | BIAYA ADMIN] ADMIN BANK BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-06-01', 'TRANS_BANK', @ref_id, 'BNK26-0188', 'KREDIT', 30000.00, 0.00, 30000.00, 0.00, 0.00, '[Excel R211 | BIAYA ADMIN] ADMIN BANK BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0189 | Excel R212 | 2026-06-03 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0189', 'BANK_MASUK', 'TTC', '2026-06-03', 'June 2026', 309, 452, 27500000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R212 | PENJUALAN] ETIKET APPLE', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-06-03', 'TRANS_BANK', @ref_id, 'BNK26-0189', 'DEBIT', 27500000.00, 27500000.00, 0.00, 0.00, 0.00, '[Excel R212 | PENJUALAN] ETIKET APPLE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-06-03', 'TRANS_BANK', @ref_id, 'BNK26-0189', 'KREDIT', 27500000.00, 0.00, 27500000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R212 | PENJUALAN] ETIKET APPLE', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0190 | Excel R213 | 2026-06-05 | 1112 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0190', 'BANK_MASUK', 'TTC', '2026-06-05', 'June 2026', 310, 452, 10953700.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R213 | PENJUALAN] PELUNASAN ETIKET DAN INNER PDW 88', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-06-05', 'TRANS_BANK', @ref_id, 'BNK26-0190', 'DEBIT', 10953700.00, 10953700.00, 0.00, 0.00, 0.00, '[Excel R213 | PENJUALAN] PELUNASAN ETIKET DAN INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-06-05', 'TRANS_BANK', @ref_id, 'BNK26-0190', 'KREDIT', 10953700.00, 0.00, 10953700.00, 0.00, 0.00, 'Lawan penerimaan [Excel R213 | PENJUALAN] PELUNASAN ETIKET DAN INNER PDW 88', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0191 | Excel R214 | 2026-06-05 | 1112 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0191', 'BANK_MASUK', 'TTC', '2026-06-05', 'June 2026', 310, 452, 15111750.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R214 | PENJUALAN] PELUNASAN ETIKET FLORENT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-06-05', 'TRANS_BANK', @ref_id, 'BNK26-0191', 'DEBIT', 15111750.00, 15111750.00, 0.00, 0.00, 0.00, '[Excel R214 | PENJUALAN] PELUNASAN ETIKET FLORENT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-06-05', 'TRANS_BANK', @ref_id, 'BNK26-0191', 'KREDIT', 15111750.00, 0.00, 15111750.00, 0.00, 0.00, 'Lawan penerimaan [Excel R214 | PENJUALAN] PELUNASAN ETIKET FLORENT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0192 | Excel R215 | 2026-06-06 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0192', 'BANK_KELUAR', 'TTC', '2026-06-06', 'June 2026', 310, 523, 30000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R215 | PEMBELIAN] BIAYA KARTU BDC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-06', 'TRANS_BANK', @ref_id, 'BNK26-0192', 'DEBIT', 30000.00, 30000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R215 | PEMBELIAN] BIAYA KARTU BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-06-06', 'TRANS_BANK', @ref_id, 'BNK26-0192', 'KREDIT', 30000.00, 0.00, 30000.00, 0.00, 0.00, '[Excel R215 | PEMBELIAN] BIAYA KARTU BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0193 | Excel R216 | 2026-06-06 | 1112 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0193', 'BANK_MASUK', 'TTC', '2026-06-06', 'June 2026', 310, 452, 17900000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R216 | PENJUALAN] PELUNASAN ET BLUESEA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-06-06', 'TRANS_BANK', @ref_id, 'BNK26-0193', 'DEBIT', 17900000.00, 17900000.00, 0.00, 0.00, 0.00, '[Excel R216 | PENJUALAN] PELUNASAN ET BLUESEA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-06-06', 'TRANS_BANK', @ref_id, 'BNK26-0193', 'KREDIT', 17900000.00, 0.00, 17900000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R216 | PENJUALAN] PELUNASAN ET BLUESEA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0194 | Excel R217 | 2026-06-09 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0194', 'BANK_MASUK', 'TTC', '2026-06-09', 'June 2026', 309, 439, 1300000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R217 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0194', 'DEBIT', 1300000000.00, 1300000000.00, 0.00, 0.00, 0.00, '[Excel R217 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0194', 'KREDIT', 1300000000.00, 0.00, 1300000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R217 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0195 | Excel R218 | 2026-06-09 | 1211 (D) -> 1111 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0195', 'BANK_KELUAR', 'TTC', '2026-06-09', 'June 2026', 309, 349, 188462858.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R218 | PEMBAYARAN] CICILAN GUDANG K/5', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0195', 'DEBIT', 188462858.00, 188462858.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R218 | PEMBAYARAN] CICILAN GUDANG K/5', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0195', 'KREDIT', 188462858.00, 0.00, 188462858.00, 0.00, 0.00, '[Excel R218 | PEMBAYARAN] CICILAN GUDANG K/5', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0196 | Excel R219 | 2026-06-09 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0196', 'BANK_KELUAR', 'TTC', '2026-06-09', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R219 | BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/5', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0196', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R219 | BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/5', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0196', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R219 | BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/5', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0197 | Excel R220 | 2026-06-09 | 1211 (D) -> 1111 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0197', 'BANK_KELUAR', 'TTC', '2026-06-09', 'June 2026', 309, 349, 188435000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R220 | PEMBAYARAN] CICILAN GUDANG K/6', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0197', 'DEBIT', 188435000.00, 188435000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R220 | PEMBAYARAN] CICILAN GUDANG K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0197', 'KREDIT', 188435000.00, 0.00, 188435000.00, 0.00, 0.00, '[Excel R220 | PEMBAYARAN] CICILAN GUDANG K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0198 | Excel R221 | 2026-06-09 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0198', 'BANK_KELUAR', 'TTC', '2026-06-09', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R221 | BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/6', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0198', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R221 | BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-09', 'TRANS_BANK', @ref_id, 'BNK26-0198', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R221 | BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0199 | Excel R222 | 2026-06-11 | 6108 (D) -> 1114 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0199', 'BANK_KELUAR', 'TTC', '2026-06-11', 'June 2026', 312, 508, 357912.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R222 | PEMBAYARAN] FEE PAK HERI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-06-11', 'TRANS_BANK', @ref_id, 'BNK26-0199', 'DEBIT', 357912.00, 357912.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R222 | PEMBAYARAN] FEE PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-11', 'TRANS_BANK', @ref_id, 'BNK26-0199', 'KREDIT', 357912.00, 0.00, 357912.00, 0.00, 0.00, '[Excel R222 | PEMBAYARAN] FEE PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0200 | Excel R223 | 2026-06-12 | 6503 (D) -> 1111 (K) (Program Kesejahteraan / Seragam / Paspor Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0200', 'BANK_KELUAR', 'TTC', '2026-06-12', 'June 2026', 309, 539, 650000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R223 | PEMBAYARAN] PEMBUATAN PASPOR', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(539, '2026-06-12', 'TRANS_BANK', @ref_id, 'BNK26-0200', 'DEBIT', 650000.00, 650000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R223 | PEMBAYARAN] PEMBUATAN PASPOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-12', 'TRANS_BANK', @ref_id, 'BNK26-0200', 'KREDIT', 650000.00, 0.00, 650000.00, 0.00, 0.00, '[Excel R223 | PEMBAYARAN] PEMBUATAN PASPOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0201 | Excel R224 | 2026-06-16 | 1251 (D) -> 1114 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0201', 'BANK_KELUAR', 'TTC', '2026-06-16', 'June 2026', 312, 373, 1125000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R224 | PEMBELIAN] MATERIAL KAYU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0201', 'DEBIT', 1125000.00, 1125000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R224 | PEMBELIAN] MATERIAL KAYU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0201', 'KREDIT', 1125000.00, 0.00, 1125000.00, 0.00, 0.00, '[Excel R224 | PEMBELIAN] MATERIAL KAYU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0202 | Excel R225 | 2026-06-16 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0202', 'BANK_KELUAR', 'TTC', '2026-06-16', 'June 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R225 | BIAYA ADMIN] BIAYA ADMIN MATERIAL KAYU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0202', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R225 | BIAYA ADMIN] BIAYA ADMIN MATERIAL KAYU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0202', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R225 | BIAYA ADMIN] BIAYA ADMIN MATERIAL KAYU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0203 | Excel R226 | 2026-06-16 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0203', 'BANK_KELUAR', 'TTC', '2026-06-16', 'June 2026', 309, 523, 500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R226 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0203', 'DEBIT', 500.00, 500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R226 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0203', 'KREDIT', 500.00, 0.00, 500.00, 0.00, 0.00, '[Excel R226 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0204 | Excel R227 | 2026-06-16 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0204', 'BANK_KELUAR', 'TTC', '2026-06-16', 'June 2026', 309, 373, 33600000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R227 | PEMBAYARAN] PEMBAYARAN AWAL PEMBANGUNAN MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0204', 'DEBIT', 33600000.00, 33600000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R227 | PEMBAYARAN] PEMBAYARAN AWAL PEMBANGUNAN MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0204', 'KREDIT', 33600000.00, 0.00, 33600000.00, 0.00, 0.00, '[Excel R227 | PEMBAYARAN] PEMBAYARAN AWAL PEMBANGUNAN MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0205 | Excel R228 | 2026-06-16 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0205', 'BANK_KELUAR', 'TTC', '2026-06-16', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R228 | BIAYA ADMIN] ADMIN BANK PEMBAYARAN AWAL PEMBANGUNAN MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0205', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R228 | BIAYA ADMIN] ADMIN BANK PEMBAYARAN AWAL PEMBANGUNAN MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0205', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R228 | BIAYA ADMIN] ADMIN BANK PEMBAYARAN AWAL PEMBANGUNAN MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0206 | Excel R229 | 2026-06-16 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0206', 'BANK_KELUAR', 'TTC', '2026-06-16', 'June 2026', 309, 373, 22068000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R229 | PEMBELIAN] MATERIAL QB', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0206', 'DEBIT', 22068000.00, 22068000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R229 | PEMBELIAN] MATERIAL QB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0206', 'KREDIT', 22068000.00, 0.00, 22068000.00, 0.00, 0.00, '[Excel R229 | PEMBELIAN] MATERIAL QB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0207 | Excel R230 | 2026-06-16 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0207', 'BANK_KELUAR', 'TTC', '2026-06-16', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R230 | BIAYA ADMIN] ADMIN BANK MATERIAL QB', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0207', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R230 | BIAYA ADMIN] ADMIN BANK MATERIAL QB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-16', 'TRANS_BANK', @ref_id, 'BNK26-0207', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R230 | BIAYA ADMIN] ADMIN BANK MATERIAL QB', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0208 | Excel R231 | 2026-06-17 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0208', 'BANK_KELUAR', 'TTC', '2026-06-17', 'June 2026', 309, 355, 152500000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R231 | PEMBAYARAN] PELUNASAN PROYEK LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0208', 'DEBIT', 152500000.00, 152500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R231 | PEMBAYARAN] PELUNASAN PROYEK LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0208', 'KREDIT', 152500000.00, 0.00, 152500000.00, 0.00, 0.00, '[Excel R231 | PEMBAYARAN] PELUNASAN PROYEK LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0209 | Excel R232 | 2026-06-17 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0209', 'BANK_KELUAR', 'TTC', '2026-06-17', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R232 | BIAYA ADMIN] ADMIN BANK PELUNASAN PROYEK LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0209', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R232 | BIAYA ADMIN] ADMIN BANK PELUNASAN PROYEK LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0209', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R232 | BIAYA ADMIN] ADMIN BANK PELUNASAN PROYEK LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0210 | Excel R233 | 2026-06-17 | 1251 (D) -> 1114 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0210', 'BANK_KELUAR', 'TTC', '2026-06-17', 'June 2026', 312, 373, 16698750.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R233 | PEMBELIAN] PELUNASAN MATERIAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0210', 'DEBIT', 16698750.00, 16698750.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R233 | PEMBELIAN] PELUNASAN MATERIAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0210', 'KREDIT', 16698750.00, 0.00, 16698750.00, 0.00, 0.00, '[Excel R233 | PEMBELIAN] PELUNASAN MATERIAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0211 | Excel R234 | 2026-06-17 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0211', 'BANK_KELUAR', 'TTC', '2026-06-17', 'June 2026', 312, 523, 2500.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R234 | BIAYA ADMIN] ADMIN BANK PELUNASAN MATERIAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0211', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R234 | BIAYA ADMIN] ADMIN BANK PELUNASAN MATERIAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-17', 'TRANS_BANK', @ref_id, 'BNK26-0211', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R234 | BIAYA ADMIN] ADMIN BANK PELUNASAN MATERIAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0212 | Excel R235 | 2026-06-18 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0212', 'BANK_KELUAR', 'TTC', '2026-06-18', 'June 2026', 309, 355, 160000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R235 | PEMBELIAN] PELUNASAN PEMBELIAN KUBIKEL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0212', 'DEBIT', 160000000.00, 160000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R235 | PEMBELIAN] PELUNASAN PEMBELIAN KUBIKEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0212', 'KREDIT', 160000000.00, 0.00, 160000000.00, 0.00, 0.00, '[Excel R235 | PEMBELIAN] PELUNASAN PEMBELIAN KUBIKEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0213 | Excel R236 | 2026-06-18 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0213', 'BANK_KELUAR', 'TTC', '2026-06-18', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R236 | BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBELIAN KUBIKEL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0213', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R236 | BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBELIAN KUBIKEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0213', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R236 | BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBELIAN KUBIKEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0214 | Excel R237 | 2026-06-18 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0214', 'BANK_KELUAR', 'TTC', '2026-06-18', 'June 2026', 309, 355, 44468000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R237 | PEMBAYARAN] PELUNASAN PEMBUATAN PANEL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0214', 'DEBIT', 44468000.00, 44468000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R237 | PEMBAYARAN] PELUNASAN PEMBUATAN PANEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0214', 'KREDIT', 44468000.00, 0.00, 44468000.00, 0.00, 0.00, '[Excel R237 | PEMBAYARAN] PELUNASAN PEMBUATAN PANEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0215 | Excel R238 | 2026-06-18 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0215', 'BANK_KELUAR', 'TTC', '2026-06-18', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R238 | BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBUATAN PANEL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0215', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R238 | BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBUATAN PANEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0215', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R238 | BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBUATAN PANEL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0216 | Excel R239 | 2026-06-18 | 6503 (D) -> 1111 (K) (Program Kesejahteraan / Seragam / Paspor Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0216', 'BANK_KELUAR', 'TTC', '2026-06-18', 'June 2026', 309, 539, 650000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R239 | PEMBAYARAN] PEMBUATAN PASPOR', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(539, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0216', 'DEBIT', 650000.00, 650000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R239 | PEMBAYARAN] PEMBUATAN PASPOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-18', 'TRANS_BANK', @ref_id, 'BNK26-0216', 'KREDIT', 650000.00, 0.00, 650000.00, 0.00, 0.00, '[Excel R239 | PEMBAYARAN] PEMBUATAN PASPOR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0217 | Excel R240 | 2026-06-20 | 1111 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0217', 'BANK_MASUK', 'TTC', '2026-06-20', 'June 2026', 309, 467, 412986.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R240 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-06-20', 'TRANS_BANK', @ref_id, 'BNK26-0217', 'DEBIT', 412986.00, 412986.00, 0.00, 0.00, 0.00, '[Excel R240 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-06-20', 'TRANS_BANK', @ref_id, 'BNK26-0217', 'KREDIT', 412986.00, 0.00, 412986.00, 0.00, 0.00, 'Lawan penerimaan [Excel R240 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0218 | Excel R241 | 2026-06-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0218', 'BANK_KELUAR', 'TTC', '2026-06-20', 'June 2026', 309, 523, 82597.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R241 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-20', 'TRANS_BANK', @ref_id, 'BNK26-0218', 'DEBIT', 82597.00, 82597.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R241 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-20', 'TRANS_BANK', @ref_id, 'BNK26-0218', 'KREDIT', 82597.00, 0.00, 82597.00, 0.00, 0.00, '[Excel R241 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0219 | Excel R242 | 2026-06-22 | 6207 (D) -> 1114 (K) (Telepon & Internet)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0219', 'BANK_KELUAR', 'TTC', '2026-06-22', 'June 2026', 312, 516, 1310800.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R242 | OPERASIONAL] TAGIHAN WIFI TELKOM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(516, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0219', 'DEBIT', 1310800.00, 1310800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R242 | OPERASIONAL] TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0219', 'KREDIT', 1310800.00, 0.00, 1310800.00, 0.00, 0.00, '[Excel R242 | OPERASIONAL] TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0220 | Excel R243 | 2026-06-22 | 6214 (D) -> 1114 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0220', 'BANK_KELUAR', 'TTC', '2026-06-22', 'June 2026', 312, 523, 2800.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R243 | BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0220', 'DEBIT', 2800.00, 2800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R243 | BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0220', 'KREDIT', 2800.00, 0.00, 2800.00, 0.00, 0.00, '[Excel R243 | BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0221 | Excel R244 | 2026-06-22 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0221', 'BANK_KELUAR', 'TTC', '2026-06-22', 'June 2026', 309, 355, 18750000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R244 | PEMBELIAN] DP 50 PERSEN 5 UNIT AC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0221', 'DEBIT', 18750000.00, 18750000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R244 | PEMBELIAN] DP 50 PERSEN 5 UNIT AC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0221', 'KREDIT', 18750000.00, 0.00, 18750000.00, 0.00, 0.00, '[Excel R244 | PEMBELIAN] DP 50 PERSEN 5 UNIT AC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0222 | Excel R245 | 2026-06-22 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0222', 'BANK_KELUAR', 'TTC', '2026-06-22', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R245 | BIAYA ADMIN] ADMIN BANK DP 50 PERSEN 5 UNIT AC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0222', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R245 | BIAYA ADMIN] ADMIN BANK DP 50 PERSEN 5 UNIT AC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0222', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R245 | BIAYA ADMIN] ADMIN BANK DP 50 PERSEN 5 UNIT AC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0223 | Excel R246 | 2026-06-22 | 6203 (D) -> 1114 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0223', 'BANK_KELUAR', 'TTC', '2026-06-22', 'June 2026', 312, 512, 111850.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R246 | OPERASIONAL] PEMBAYARAN PDAM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0223', 'DEBIT', 111850.00, 111850.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R246 | OPERASIONAL] PEMBAYARAN PDAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-06-22', 'TRANS_BANK', @ref_id, 'BNK26-0223', 'KREDIT', 111850.00, 0.00, 111850.00, 0.00, 0.00, '[Excel R246 | OPERASIONAL] PEMBAYARAN PDAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0224 | Excel R247 | 2026-06-23 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0224', 'BANK_KELUAR', 'TTC', '2026-06-23', 'June 2026', 309, 373, 7940000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R247 | PEMBELIAN] MATERIAL PEMBANGUNAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0224', 'DEBIT', 7940000.00, 7940000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R247 | PEMBELIAN] MATERIAL PEMBANGUNAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0224', 'KREDIT', 7940000.00, 0.00, 7940000.00, 0.00, 0.00, '[Excel R247 | PEMBELIAN] MATERIAL PEMBANGUNAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0225 | Excel R248 | 2026-06-23 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0225', 'BANK_KELUAR', 'TTC', '2026-06-23', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R248 | BIAYA ADMIN] ADMIN BANK MATERIAL PEMBANGUNAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0225', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R248 | BIAYA ADMIN] ADMIN BANK MATERIAL PEMBANGUNAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0225', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R248 | BIAYA ADMIN] ADMIN BANK MATERIAL PEMBANGUNAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0226 | Excel R249 | 2026-06-23 | 1261 (D) -> 1111 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0226', 'BANK_KELUAR', 'TTC', '2026-06-23', 'June 2026', 309, 378, 3156000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R249 | PEMBAYARAN] PELUNASAN DESIGN PARTISI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0226', 'DEBIT', 3156000.00, 3156000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R249 | PEMBAYARAN] PELUNASAN DESIGN PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0226', 'KREDIT', 3156000.00, 0.00, 3156000.00, 0.00, 0.00, '[Excel R249 | PEMBAYARAN] PELUNASAN DESIGN PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0227 | Excel R250 | 2026-06-23 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0227', 'BANK_KELUAR', 'TTC', '2026-06-23', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R250 | BIAYA ADMIN] ADMIN BANK PELUNASAN DESIGN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0227', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R250 | BIAYA ADMIN] ADMIN BANK PELUNASAN DESIGN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-23', 'TRANS_BANK', @ref_id, 'BNK26-0227', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R250 | BIAYA ADMIN] ADMIN BANK PELUNASAN DESIGN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0228 | Excel R251 | 2026-06-25 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0228', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 355, 1595250.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R251 | PEMBELIAN] PEMBAYARAN HARDISK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0228', 'DEBIT', 1595250.00, 1595250.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R251 | PEMBELIAN] PEMBAYARAN HARDISK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0228', 'KREDIT', 1595250.00, 0.00, 1595250.00, 0.00, 0.00, '[Excel R251 | PEMBELIAN] PEMBAYARAN HARDISK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0229 | Excel R252 | 2026-06-25 | 6108 (D) -> 1111 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0229', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 508, 412000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R252 | PEMBAYARAN] FEE MARKETING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0229', 'DEBIT', 412000.00, 412000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R252 | PEMBAYARAN] FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0229', 'KREDIT', 412000.00, 0.00, 412000.00, 0.00, 0.00, '[Excel R252 | PEMBAYARAN] FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0230 | Excel R253 | 2026-06-25 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0230', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R253 | BIAYA ADMIN] ADMIN BANK FEE MARKETING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0230', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R253 | BIAYA ADMIN] ADMIN BANK FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0230', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R253 | BIAYA ADMIN] ADMIN BANK FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0231 | Excel R254 | 2026-06-25 | 1111 (D) -> 6108 (K) (Refund / Koreksi Komisi Marketing)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0231', 'BANK_MASUK', 'TTC', '2026-06-25', 'June 2026', 309, 508, 412000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R254 | PENERIMAAN] REFUND FEE MARKETING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0231', 'DEBIT', 412000.00, 412000.00, 0.00, 0.00, 0.00, '[Excel R254 | PENERIMAAN] REFUND FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(508, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0231', 'KREDIT', 412000.00, 0.00, 412000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R254 | PENERIMAAN] REFUND FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0232 | Excel R255 | 2026-06-25 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0232', 'BANK_MASUK', 'TTC', '2026-06-25', 'June 2026', 309, 452, 5289345.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R255 | PENJUALAN] UANG MASUK PELUNASAN ET DAVI & INNER', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0232', 'DEBIT', 5289345.00, 5289345.00, 0.00, 0.00, 0.00, '[Excel R255 | PENJUALAN] UANG MASUK PELUNASAN ET DAVI & INNER', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0232', 'KREDIT', 5289345.00, 0.00, 5289345.00, 0.00, 0.00, 'Lawan penerimaan [Excel R255 | PENJUALAN] UANG MASUK PELUNASAN ET DAVI & INNER', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0233 | Excel R256 | 2026-06-25 | 6108 (D) -> 1111 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0233', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 508, 300000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R256 | PEMBAYARAN] FEE MARKETING SUNARNI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0233', 'DEBIT', 300000.00, 300000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R256 | PEMBAYARAN] FEE MARKETING SUNARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0233', 'KREDIT', 300000.00, 0.00, 300000.00, 0.00, 0.00, '[Excel R256 | PEMBAYARAN] FEE MARKETING SUNARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0234 | Excel R257 | 2026-06-25 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0234', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R257 | BIAYA ADMIN] ADMIN BANK FEE MARKETING SUNARNI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0234', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R257 | BIAYA ADMIN] ADMIN BANK FEE MARKETING SUNARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0234', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R257 | BIAYA ADMIN] ADMIN BANK FEE MARKETING SUNARNI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0235 | Excel R258 | 2026-06-25 | 5308 (D) -> 1111 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0235', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 492, 2000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R258 | PEMBAYARAN] DP ETIKET BARLBARA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0235', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R258 | PEMBAYARAN] DP ETIKET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0235', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R258 | PEMBAYARAN] DP ETIKET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0236 | Excel R259 | 2026-06-25 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0236', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R259 | BIAYA ADMIN] ADMIN BANK UTJ', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0236', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R259 | BIAYA ADMIN] ADMIN BANK UTJ', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0236', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R259 | BIAYA ADMIN] ADMIN BANK UTJ', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0237 | Excel R260 | 2026-06-25 | 6108 (D) -> 1111 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0237', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 508, 603143.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R260 | PEMBAYARAN] FEE MARKETING PAK ANDI PRABOWO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0237', 'DEBIT', 603143.00, 603143.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R260 | PEMBAYARAN] FEE MARKETING PAK ANDI PRABOWO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0237', 'KREDIT', 603143.00, 0.00, 603143.00, 0.00, 0.00, '[Excel R260 | PEMBAYARAN] FEE MARKETING PAK ANDI PRABOWO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0238 | Excel R261 | 2026-06-25 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0238', 'BANK_KELUAR', 'TTC', '2026-06-25', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R261 | BIAYA ADMIN] ADMIN BANK FEE MARKETING PAK ANDI PRABOWO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0238', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R261 | BIAYA ADMIN] ADMIN BANK FEE MARKETING PAK ANDI PRABOWO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0238', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R261 | BIAYA ADMIN] ADMIN BANK FEE MARKETING PAK ANDI PRABOWO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0239 | Excel R262 | 2026-06-25 | 1112 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0239', 'BANK_MASUK', 'TTC', '2026-06-25', 'June 2026', 310, 452, 5300000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R262 | PENJUALAN] DP ETIKET BARLBARA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0239', 'DEBIT', 5300000.00, 5300000.00, 0.00, 0.00, 0.00, '[Excel R262 | PENJUALAN] DP ETIKET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-06-25', 'TRANS_BANK', @ref_id, 'BNK26-0239', 'KREDIT', 5300000.00, 0.00, 5300000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R262 | PENJUALAN] DP ETIKET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0240 | Excel R263 | 2026-06-26 | 6108 (D) -> 1111 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0240', 'BANK_KELUAR', 'TTC', '2026-06-26', 'June 2026', 309, 508, 412000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R263 | PEMBAYARAN] FEE MARKETING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0240', 'DEBIT', 412000.00, 412000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R263 | PEMBAYARAN] FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0240', 'KREDIT', 412000.00, 0.00, 412000.00, 0.00, 0.00, '[Excel R263 | PEMBAYARAN] FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0241 | Excel R264 | 2026-06-26 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0241', 'BANK_KELUAR', 'TTC', '2026-06-26', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R264 | BIAYA ADMIN] ADMIN BANK FEE MARKETING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0241', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R264 | BIAYA ADMIN] ADMIN BANK FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0241', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R264 | BIAYA ADMIN] ADMIN BANK FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0242 | Excel R265 | 2026-06-26 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0242', 'BANK_KELUAR', 'TTC', '2026-06-26', 'June 2026', 309, 373, 7579000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R265 | PEMBELIAN] MATERIAL MESS TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0242', 'DEBIT', 7579000.00, 7579000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R265 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0242', 'KREDIT', 7579000.00, 0.00, 7579000.00, 0.00, 0.00, '[Excel R265 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0243 | Excel R266 | 2026-06-26 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0243', 'BANK_KELUAR', 'TTC', '2026-06-26', 'June 2026', 309, 373, 7795000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R266 | PEMBELIAN] MATERIAL MESS TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0243', 'DEBIT', 7795000.00, 7795000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R266 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-26', 'TRANS_BANK', @ref_id, 'BNK26-0243', 'KREDIT', 7795000.00, 0.00, 7795000.00, 0.00, 0.00, '[Excel R266 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0244 | Excel R267 | 2026-06-27 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0244', 'BANK_KELUAR', 'TTC', '2026-06-27', 'June 2026', 309, 373, 100800000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R267 | PEMBAYARAN] BIAYA TERMIN  1 MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0244', 'DEBIT', 100800000.00, 100800000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R267 | PEMBAYARAN] BIAYA TERMIN  1 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0244', 'KREDIT', 100800000.00, 0.00, 100800000.00, 0.00, 0.00, '[Excel R267 | PEMBAYARAN] BIAYA TERMIN  1 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0245 | Excel R268 | 2026-06-27 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0245', 'BANK_KELUAR', 'TTC', '2026-06-27', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R268 | BIAYA ADMIN] ADMIN BANK BIAYA TERMIN  1 MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0245', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R268 | BIAYA ADMIN] ADMIN BANK BIAYA TERMIN  1 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0245', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R268 | BIAYA ADMIN] ADMIN BANK BIAYA TERMIN  1 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0246 | Excel R269 | 2026-06-27 | 5308 (D) -> 1111 (K) (Biaya Subcont / Jasa Produksi / Pisau / Klis)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0246', 'BANK_KELUAR', 'TTC', '2026-06-27', 'June 2026', 309, 492, 7827000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R269 | PEMBAYARAN] ETIKET DAFI SKT 16', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(492, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0246', 'DEBIT', 7827000.00, 7827000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R269 | PEMBAYARAN] ETIKET DAFI SKT 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0246', 'KREDIT', 7827000.00, 0.00, 7827000.00, 0.00, 0.00, '[Excel R269 | PEMBAYARAN] ETIKET DAFI SKT 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0247 | Excel R270 | 2026-06-27 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0247', 'BANK_KELUAR', 'TTC', '2026-06-27', 'June 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R270 | BIAYA ADMIN] ADMIN BANK UTJ', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0247', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R270 | BIAYA ADMIN] ADMIN BANK UTJ', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0247', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R270 | BIAYA ADMIN] ADMIN BANK UTJ', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0248 | Excel R271 | 2026-06-27 | 1112 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0248', 'BANK_MASUK', 'TTC', '2026-06-27', 'June 2026', 310, 452, 7000000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R271 | PENJUALAN] PELUNASAN ET BARLBARA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0248', 'DEBIT', 7000000.00, 7000000.00, 0.00, 0.00, 0.00, '[Excel R271 | PENJUALAN] PELUNASAN ET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-06-27', 'TRANS_BANK', @ref_id, 'BNK26-0248', 'KREDIT', 7000000.00, 0.00, 7000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R271 | PENJUALAN] PELUNASAN ET BARLBARA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0249 | Excel R272 | 2026-06-30 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0249', 'BANK_KELUAR', 'TTC', '2026-06-30', 'June 2026', 309, 373, 2875000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R272 | PEMBAYARAN] PEMBELIAN MATERIAL MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-06-30', 'TRANS_BANK', @ref_id, 'BNK26-0249', 'DEBIT', 2875000.00, 2875000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R272 | PEMBAYARAN] PEMBELIAN MATERIAL MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-30', 'TRANS_BANK', @ref_id, 'BNK26-0249', 'KREDIT', 2875000.00, 0.00, 2875000.00, 0.00, 0.00, '[Excel R272 | PEMBAYARAN] PEMBELIAN MATERIAL MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0250 | Excel R273 | 2026-06-30 | 5101 (D) -> 1111 (K) (Pembelian Bahan Baku Kertas Produksi)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0250', 'BANK_KELUAR', 'TTC', '2026-06-30', 'June 2026', 309, 477, 2874555.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R273 | PEMBELIAN] PEMBELIAN KERTAS IVORY', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(477, '2026-06-30', 'TRANS_BANK', @ref_id, 'BNK26-0250', 'DEBIT', 2874555.00, 2874555.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R273 | PEMBELIAN] PEMBELIAN KERTAS IVORY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-30', 'TRANS_BANK', @ref_id, 'BNK26-0250', 'KREDIT', 2874555.00, 0.00, 2874555.00, 0.00, 0.00, '[Excel R273 | PEMBELIAN] PEMBELIAN KERTAS IVORY', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0251 | Excel R274 | 2026-06-30 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0251', 'BANK_KELUAR', 'TTC', '2026-06-30', 'June 2026', 309, 528, 12694000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R274 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-06-30', 'TRANS_BANK', @ref_id, 'BNK26-0251', 'DEBIT', 12694000.00, 12694000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R274 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-06-30', 'TRANS_BANK', @ref_id, 'BNK26-0251', 'KREDIT', 12694000.00, 0.00, 12694000.00, 0.00, 0.00, '[Excel R274 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0252 | Excel R277 | 2026-07-01 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0252', 'BANK_KELUAR', 'TTC', '2026-07-01', 'July 2026', 310, 523, 30000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R277 | BIAYA ADMIN] ADMIN BANK BCA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0252', 'DEBIT', 30000.00, 30000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R277 | BIAYA ADMIN] ADMIN BANK BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0252', 'KREDIT', 30000.00, 0.00, 30000.00, 0.00, 0.00, '[Excel R277 | BIAYA ADMIN] ADMIN BANK BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0253 | Excel R278 | 2026-07-01 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0253', 'BANK_KELUAR', 'TTC', '2026-07-01', 'July 2026', 309, 373, 1005000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R278 | PEMBELIAN] MATERIAL MESS TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0253', 'DEBIT', 1005000.00, 1005000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R278 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0253', 'KREDIT', 1005000.00, 0.00, 1005000.00, 0.00, 0.00, '[Excel R278 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0254 | Excel R279 | 2026-07-01 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0254', 'BANK_KELUAR', 'TTC', '2026-07-01', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R279 | BIAYA ADMIN] BIAYA ADMIN Q8A & SENAR', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0254', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R279 | BIAYA ADMIN] BIAYA ADMIN Q8A & SENAR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0254', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R279 | BIAYA ADMIN] BIAYA ADMIN Q8A & SENAR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0255 | Excel R280 | 2026-07-01 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0255', 'BANK_KELUAR', 'TTC', '2026-07-01', 'July 2026', 309, 373, 960000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R280 | PEMBELIAN] MATERIAL MESS TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0255', 'DEBIT', 960000.00, 960000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R280 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0255', 'KREDIT', 960000.00, 0.00, 960000.00, 0.00, 0.00, '[Excel R280 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0256 | Excel R281 | 2026-07-01 | 1131 (D) -> 1114 (K) (Piutang / Kasbon Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0256', 'BANK_KELUAR', 'TTC', '2026-07-01', 'July 2026', 312, 329, 5500000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R281 | PEMBAYARAN] KASBON KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(329, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0256', 'DEBIT', 5500000.00, 5500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R281 | PEMBAYARAN] KASBON KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-07-01', 'TRANS_BANK', @ref_id, 'BNK26-0256', 'KREDIT', 5500000.00, 0.00, 5500000.00, 0.00, 0.00, '[Excel R281 | PEMBAYARAN] KASBON KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0257 | Excel R282 | 2026-07-02 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0257', 'BANK_KELUAR', 'TTC', '2026-07-02', 'July 2026', 309, 528, 3496000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R282 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-07-02', 'TRANS_BANK', @ref_id, 'BNK26-0257', 'DEBIT', 3496000.00, 3496000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R282 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-02', 'TRANS_BANK', @ref_id, 'BNK26-0257', 'KREDIT', 3496000.00, 0.00, 3496000.00, 0.00, 0.00, '[Excel R282 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0258 | Excel R283 | 2026-07-02 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0258', 'BANK_KELUAR', 'TTC', '2026-07-02', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R283 | BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-02', 'TRANS_BANK', @ref_id, 'BNK26-0258', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R283 | BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-02', 'TRANS_BANK', @ref_id, 'BNK26-0258', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R283 | BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0259 | Excel R284 | 2026-07-02 | 6502 (D) -> 1114 (K) (BPJS Ketenagakerjaan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0259', 'BANK_KELUAR', 'TTC', '2026-07-02', 'July 2026', 312, 538, 398548.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R284 | PEMBAYARAN] BAYAR BPJS KETENAGA KERJA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(538, '2026-07-02', 'TRANS_BANK', @ref_id, 'BNK26-0259', 'DEBIT', 398548.00, 398548.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R284 | PEMBAYARAN] BAYAR BPJS KETENAGA KERJA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-07-02', 'TRANS_BANK', @ref_id, 'BNK26-0259', 'KREDIT', 398548.00, 0.00, 398548.00, 0.00, 0.00, '[Excel R284 | PEMBAYARAN] BAYAR BPJS KETENAGA KERJA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0260 | Excel R285 | 2026-07-04 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0260', 'BANK_KELUAR', 'TTC', '2026-07-04', 'July 2026', 310, 523, 40000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R285 | BIAYA ADMIN] BIAYA ADMIN BDC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-04', 'TRANS_BANK', @ref_id, 'BNK26-0260', 'DEBIT', 40000.00, 40000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R285 | BIAYA ADMIN] BIAYA ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-07-04', 'TRANS_BANK', @ref_id, 'BNK26-0260', 'KREDIT', 40000.00, 0.00, 40000.00, 0.00, 0.00, '[Excel R285 | BIAYA ADMIN] BIAYA ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0261 | Excel R286 | 2026-07-04 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0261', 'BANK_KELUAR', 'TTC', '2026-07-04', 'July 2026', 309, 373, 3050000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R286 | PEMBAYARAN] MATERIAL PASIR DAN SEMEN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-04', 'TRANS_BANK', @ref_id, 'BNK26-0261', 'DEBIT', 3050000.00, 3050000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R286 | PEMBAYARAN] MATERIAL PASIR DAN SEMEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-04', 'TRANS_BANK', @ref_id, 'BNK26-0261', 'KREDIT', 3050000.00, 0.00, 3050000.00, 0.00, 0.00, '[Excel R286 | PEMBAYARAN] MATERIAL PASIR DAN SEMEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0262 | Excel R287 | 2026-07-04 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0262', 'BANK_KELUAR', 'TTC', '2026-07-04', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R287 | BIAYA ADMIN] ADMIN BANK MATERIAL PASIR DAN SEMEN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-04', 'TRANS_BANK', @ref_id, 'BNK26-0262', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R287 | BIAYA ADMIN] ADMIN BANK MATERIAL PASIR DAN SEMEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-04', 'TRANS_BANK', @ref_id, 'BNK26-0262', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R287 | BIAYA ADMIN] ADMIN BANK MATERIAL PASIR DAN SEMEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0263 | Excel R288 | 2026-07-06 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0263', 'BANK_KELUAR', 'TTC', '2026-07-06', 'July 2026', 309, 373, 5975000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R288 | PEMBAYARAN] BAMBU & PAKU & KAYU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-06', 'TRANS_BANK', @ref_id, 'BNK26-0263', 'DEBIT', 5975000.00, 5975000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R288 | PEMBAYARAN] BAMBU & PAKU & KAYU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-06', 'TRANS_BANK', @ref_id, 'BNK26-0263', 'KREDIT', 5975000.00, 0.00, 5975000.00, 0.00, 0.00, '[Excel R288 | PEMBAYARAN] BAMBU & PAKU & KAYU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0264 | Excel R289 | 2026-07-08 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0264', 'BANK_MASUK', 'TTC', '2026-07-08', 'July 2026', 309, 452, 5863000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R289 | PENJUALAN] AMBRI DAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-07-08', 'TRANS_BANK', @ref_id, 'BNK26-0264', 'DEBIT', 5863000.00, 5863000.00, 0.00, 0.00, 0.00, '[Excel R289 | PENJUALAN] AMBRI DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-07-08', 'TRANS_BANK', @ref_id, 'BNK26-0264', 'KREDIT', 5863000.00, 0.00, 5863000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R289 | PENJUALAN] AMBRI DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0265 | Excel R290 | 2026-07-10 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0265', 'BANK_KELUAR', 'TTC', '2026-07-10', 'July 2026', 309, 373, 19194000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R290 | PEMBELIAN] MATERIAL MESS TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-10', 'TRANS_BANK', @ref_id, 'BNK26-0265', 'DEBIT', 19194000.00, 19194000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R290 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-10', 'TRANS_BANK', @ref_id, 'BNK26-0265', 'KREDIT', 19194000.00, 0.00, 19194000.00, 0.00, 0.00, '[Excel R290 | PEMBELIAN] MATERIAL MESS TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0266 | Excel R291 | 2026-07-10 | 1211 (D) -> 1111 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0266', 'BANK_KELUAR', 'TTC', '2026-07-10', 'July 2026', 309, 349, 188435000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R291 | PEMBAYARAN] CICILAN GUDAN K/6', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-07-10', 'TRANS_BANK', @ref_id, 'BNK26-0266', 'DEBIT', 188435000.00, 188435000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R291 | PEMBAYARAN] CICILAN GUDAN K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-10', 'TRANS_BANK', @ref_id, 'BNK26-0266', 'KREDIT', 188435000.00, 0.00, 188435000.00, 0.00, 0.00, '[Excel R291 | PEMBAYARAN] CICILAN GUDAN K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0267 | Excel R292 | 2026-07-10 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0267', 'BANK_KELUAR', 'TTC', '2026-07-10', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R292 | BIAYA ADMIN] ADMIN BANK CICILAN GUDAN K/6', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-10', 'TRANS_BANK', @ref_id, 'BNK26-0267', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R292 | BIAYA ADMIN] ADMIN BANK CICILAN GUDAN K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-10', 'TRANS_BANK', @ref_id, 'BNK26-0267', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R292 | BIAYA ADMIN] ADMIN BANK CICILAN GUDAN K/6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0268 | Excel R293 | 2026-07-13 | 6503 (D) -> 1111 (K) (Program Kesejahteraan / Seragam / Paspor Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0268', 'BANK_KELUAR', 'TTC', '2026-07-13', 'July 2026', 309, 539, 850000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R293 | PEMBAYARAN] DP KAOS POLO DAN KEMEJA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(539, '2026-07-13', 'TRANS_BANK', @ref_id, 'BNK26-0268', 'DEBIT', 850000.00, 850000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R293 | PEMBAYARAN] DP KAOS POLO DAN KEMEJA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-13', 'TRANS_BANK', @ref_id, 'BNK26-0268', 'KREDIT', 850000.00, 0.00, 850000.00, 0.00, 0.00, '[Excel R293 | PEMBAYARAN] DP KAOS POLO DAN KEMEJA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0269 | Excel R294 | 2026-07-13 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0269', 'BANK_KELUAR', 'TTC', '2026-07-13', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R294 | BIAYA ADMIN] ADMIN BANK DP KAOS POLO', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-13', 'TRANS_BANK', @ref_id, 'BNK26-0269', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R294 | BIAYA ADMIN] ADMIN BANK DP KAOS POLO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-13', 'TRANS_BANK', @ref_id, 'BNK26-0269', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R294 | BIAYA ADMIN] ADMIN BANK DP KAOS POLO', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0270 | Excel R295 | 2026-07-16 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0270', 'BANK_KELUAR', 'TTC', '2026-07-16', 'July 2026', 309, 373, 3684000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R295 | PEMBELIAN] MATERIAL MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-16', 'TRANS_BANK', @ref_id, 'BNK26-0270', 'DEBIT', 3684000.00, 3684000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R295 | PEMBELIAN] MATERIAL MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-16', 'TRANS_BANK', @ref_id, 'BNK26-0270', 'KREDIT', 3684000.00, 0.00, 3684000.00, 0.00, 0.00, '[Excel R295 | PEMBELIAN] MATERIAL MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0271 | Excel R296 | 2026-07-16 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0271', 'BANK_MASUK', 'TTC', '2026-07-16', 'July 2026', 309, 439, 750000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R296 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-07-16', 'TRANS_BANK', @ref_id, 'BNK26-0271', 'DEBIT', 750000000.00, 750000000.00, 0.00, 0.00, 0.00, '[Excel R296 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-07-16', 'TRANS_BANK', @ref_id, 'BNK26-0271', 'KREDIT', 750000000.00, 0.00, 750000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R296 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0272 | Excel R297 | 2026-07-16 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0272', 'BANK_MASUK', 'TTC', '2026-07-16', 'July 2026', 309, 439, 200000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R297 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-07-16', 'TRANS_BANK', @ref_id, 'BNK26-0272', 'DEBIT', 200000000.00, 200000000.00, 0.00, 0.00, 0.00, '[Excel R297 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-07-16', 'TRANS_BANK', @ref_id, 'BNK26-0272', 'KREDIT', 200000000.00, 0.00, 200000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R297 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0273 | Excel R298 | 2026-07-20 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0273', 'BANK_KELUAR', 'TTC', '2026-07-20', 'July 2026', 309, 373, 6950000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R298 | PEMBELIAN] MATERIAL MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0273', 'DEBIT', 6950000.00, 6950000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R298 | PEMBELIAN] MATERIAL MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0273', 'KREDIT', 6950000.00, 0.00, 6950000.00, 0.00, 0.00, '[Excel R298 | PEMBELIAN] MATERIAL MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0274 | Excel R299 | 2026-07-20 | 6108 (D) -> 1111 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0274', 'BANK_KELUAR', 'TTC', '2026-07-20', 'July 2026', 309, 508, 1793908.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R299 | PEMBAYARAN] FEE MARKETING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0274', 'DEBIT', 1793908.00, 1793908.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R299 | PEMBAYARAN] FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0274', 'KREDIT', 1793908.00, 0.00, 1793908.00, 0.00, 0.00, '[Excel R299 | PEMBAYARAN] FEE MARKETING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0275 | Excel R300 | 2026-07-20 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0275', 'BANK_KELUAR', 'TTC', '2026-07-20', 'July 2026', 309, 355, 475090000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R300 | PEMBAYARAN] RESGISTER PLN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0275', 'DEBIT', 475090000.00, 475090000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R300 | PEMBAYARAN] RESGISTER PLN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0275', 'KREDIT', 475090000.00, 0.00, 475090000.00, 0.00, 0.00, '[Excel R300 | PEMBAYARAN] RESGISTER PLN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0276 | Excel R301 | 2026-07-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0276', 'BANK_KELUAR', 'TTC', '2026-07-20', 'July 2026', 309, 523, 3000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R301 | BIAYA ADMIN] ADMIN BANK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0276', 'DEBIT', 3000.00, 3000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R301 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0276', 'KREDIT', 3000.00, 0.00, 3000.00, 0.00, 0.00, '[Excel R301 | BIAYA ADMIN] ADMIN BANK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0277 | Excel R302 | 2026-07-20 | 1111 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0277', 'BANK_MASUK', 'TTC', '2026-07-20', 'July 2026', 309, 467, 833462.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R302 | PENERIMAAN] INTEREST OF ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0277', 'DEBIT', 833462.00, 833462.00, 0.00, 0.00, 0.00, '[Excel R302 | PENERIMAAN] INTEREST OF ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0277', 'KREDIT', 833462.00, 0.00, 833462.00, 0.00, 0.00, 'Lawan penerimaan [Excel R302 | PENERIMAAN] INTEREST OF ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0278 | Excel R303 | 2026-07-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0278', 'BANK_KELUAR', 'TTC', '2026-07-20', 'July 2026', 309, 523, 166692.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R303 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0278', 'DEBIT', 166692.00, 166692.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R303 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-20', 'TRANS_BANK', @ref_id, 'BNK26-0278', 'KREDIT', 166692.00, 0.00, 166692.00, 0.00, 0.00, '[Excel R303 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0279 | Excel R304 | 2026-07-21 | 6207 (D) -> 1111 (K) (Telepon & Internet)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0279', 'BANK_KELUAR', 'TTC', '2026-07-21', 'July 2026', 309, 516, 1310800.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R304 | PEMBAYARAN] TAGIHAN WIFI TELKOM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(516, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0279', 'DEBIT', 1310800.00, 1310800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R304 | PEMBAYARAN] TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0279', 'KREDIT', 1310800.00, 0.00, 1310800.00, 0.00, 0.00, '[Excel R304 | PEMBAYARAN] TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0280 | Excel R305 | 2026-07-21 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0280', 'BANK_KELUAR', 'TTC', '2026-07-21', 'July 2026', 309, 523, 2800.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R305 | BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0280', 'DEBIT', 2800.00, 2800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R305 | BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0280', 'KREDIT', 2800.00, 0.00, 2800.00, 0.00, 0.00, '[Excel R305 | BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0281 | Excel R306 | 2026-07-21 | 1261 (D) -> 1111 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0281', 'BANK_KELUAR', 'TTC', '2026-07-21', 'July 2026', 309, 378, 79200000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R306 | PEMBAYARAN] DP JASA PEKERJAAN RUANG PARTISI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0281', 'DEBIT', 79200000.00, 79200000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R306 | PEMBAYARAN] DP JASA PEKERJAAN RUANG PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0281', 'KREDIT', 79200000.00, 0.00, 79200000.00, 0.00, 0.00, '[Excel R306 | PEMBAYARAN] DP JASA PEKERJAAN RUANG PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0282 | Excel R307 | 2026-07-21 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0282', 'BANK_KELUAR', 'TTC', '2026-07-21', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R307 | BIAYA ADMIN] ADMIN BANK DP PEKERJAAN RUANG PARTISI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0282', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R307 | BIAYA ADMIN] ADMIN BANK DP PEKERJAAN RUANG PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0282', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R307 | BIAYA ADMIN] ADMIN BANK DP PEKERJAAN RUANG PARTISI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0283 | Excel R308 | 2026-07-21 | 6610 (D) -> 1111 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0283', 'BANK_KELUAR', 'TTC', '2026-07-21', 'July 2026', 309, 542, 1275350.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R308 | PEMBAYARAN] REIMBUERSE MAKAN SIANG DENGAN TAMU CHINA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0283', 'DEBIT', 1275350.00, 1275350.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R308 | PEMBAYARAN] REIMBUERSE MAKAN SIANG DENGAN TAMU CHINA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0283', 'KREDIT', 1275350.00, 0.00, 1275350.00, 0.00, 0.00, '[Excel R308 | PEMBAYARAN] REIMBUERSE MAKAN SIANG DENGAN TAMU CHINA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0284 | Excel R309 | 2026-07-21 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0284', 'BANK_KELUAR', 'TTC', '2026-07-21', 'July 2026', 310, 523, 50000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R309 | PEMBELIAN] POT BY KEY BCA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0284', 'DEBIT', 50000.00, 50000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R309 | PEMBELIAN] POT BY KEY BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-07-21', 'TRANS_BANK', @ref_id, 'BNK26-0284', 'KREDIT', 50000.00, 0.00, 50000.00, 0.00, 0.00, '[Excel R309 | PEMBELIAN] POT BY KEY BCA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0285 | Excel R310 | 2026-07-22 | 1261 (D) -> 1111 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0285', 'BANK_KELUAR', 'TTC', '2026-07-22', 'July 2026', 309, 378, 3740000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R310 | PEMBELIAN] MATERIAL BESI RUANG PARTISI TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-07-22', 'TRANS_BANK', @ref_id, 'BNK26-0285', 'DEBIT', 3740000.00, 3740000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R310 | PEMBELIAN] MATERIAL BESI RUANG PARTISI TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-22', 'TRANS_BANK', @ref_id, 'BNK26-0285', 'KREDIT', 3740000.00, 0.00, 3740000.00, 0.00, 0.00, '[Excel R310 | PEMBELIAN] MATERIAL BESI RUANG PARTISI TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0286 | Excel R311 | 2026-07-22 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0286', 'BANK_KELUAR', 'TTC', '2026-07-22', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R311 | BIAYA ADMIN] ADMIN BANK MATERIAL BESI RUANG PARTISI TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-22', 'TRANS_BANK', @ref_id, 'BNK26-0286', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R311 | BIAYA ADMIN] ADMIN BANK MATERIAL BESI RUANG PARTISI TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-22', 'TRANS_BANK', @ref_id, 'BNK26-0286', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R311 | BIAYA ADMIN] ADMIN BANK MATERIAL BESI RUANG PARTISI TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0287 | Excel R312 | 2026-07-23 | 6205 (D) -> 1111 (K) (Biaya Profesional / Pembuatan Akta Notaris)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0287', 'BANK_KELUAR', 'TTC', '2026-07-23', 'July 2026', 309, 514, 1800000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R312 | PEMBAYARAN] DP 30% AKTA PERUBAHAN PT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(514, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0287', 'DEBIT', 1800000.00, 1800000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R312 | PEMBAYARAN] DP 30% AKTA PERUBAHAN PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0287', 'KREDIT', 1800000.00, 0.00, 1800000.00, 0.00, 0.00, '[Excel R312 | PEMBAYARAN] DP 30% AKTA PERUBAHAN PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0288 | Excel R313 | 2026-07-23 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0288', 'BANK_KELUAR', 'TTC', '2026-07-23', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R313 | BIAYA ADMIN] ADMIN BANK DP 30% AKTA PERUBAHAN PT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0288', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R313 | BIAYA ADMIN] ADMIN BANK DP 30% AKTA PERUBAHAN PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0288', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R313 | BIAYA ADMIN] ADMIN BANK DP 30% AKTA PERUBAHAN PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0289 | Excel R314 | 2026-07-23 | 6610 (D) -> 1111 (K) (Perjalanan Dinas (Tiket Pesawat))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0289', 'BANK_KELUAR', 'TTC', '2026-07-23', 'July 2026', 309, 542, 17198864.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R314 | PEMBAYARAN] REIMBUERSE TIKET PESAWAT KE CHINA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0289', 'DEBIT', 17198864.00, 17198864.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R314 | PEMBAYARAN] REIMBUERSE TIKET PESAWAT KE CHINA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0289', 'KREDIT', 17198864.00, 0.00, 17198864.00, 0.00, 0.00, '[Excel R314 | PEMBAYARAN] REIMBUERSE TIKET PESAWAT KE CHINA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0290 | Excel R315 | 2026-07-23 | 6610 (D) -> 1111 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0290', 'BANK_KELUAR', 'TTC', '2026-07-23', 'July 2026', 309, 542, 36960.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R315 | PEMBAYARAN] REIMBUERSE JUAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0290', 'DEBIT', 36960.00, 36960.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R315 | PEMBAYARAN] REIMBUERSE JUAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-23', 'TRANS_BANK', @ref_id, 'BNK26-0290', 'KREDIT', 36960.00, 0.00, 36960.00, 0.00, 0.00, '[Excel R315 | PEMBAYARAN] REIMBUERSE JUAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0291 | Excel R316 | 2026-07-25 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0291', 'BANK_KELUAR', 'TTC', '2026-07-25', 'July 2026', 309, 373, 22175000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R316 | PEMBELIAN] PEMBELIAN BAMBU DAN BESI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-25', 'TRANS_BANK', @ref_id, 'BNK26-0291', 'DEBIT', 22175000.00, 22175000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R316 | PEMBELIAN] PEMBELIAN BAMBU DAN BESI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-25', 'TRANS_BANK', @ref_id, 'BNK26-0291', 'KREDIT', 22175000.00, 0.00, 22175000.00, 0.00, 0.00, '[Excel R316 | PEMBELIAN] PEMBELIAN BAMBU DAN BESI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0292 | Excel R317 | 2026-07-25 | 6503 (D) -> 1111 (K) (Program Kesejahteraan / Seragam / Paspor Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0292', 'BANK_KELUAR', 'TTC', '2026-07-25', 'July 2026', 309, 539, 1285000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R317 | PEMBAYARAN] SERAGAM TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(539, '2026-07-25', 'TRANS_BANK', @ref_id, 'BNK26-0292', 'DEBIT', 1285000.00, 1285000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R317 | PEMBAYARAN] SERAGAM TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-25', 'TRANS_BANK', @ref_id, 'BNK26-0292', 'KREDIT', 1285000.00, 0.00, 1285000.00, 0.00, 0.00, '[Excel R317 | PEMBAYARAN] SERAGAM TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0293 | Excel R318 | 2026-07-25 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0293', 'BANK_KELUAR', 'TTC', '2026-07-25', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R318 | BIAYA ADMIN] ADMIN BANK SERAGAM TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-25', 'TRANS_BANK', @ref_id, 'BNK26-0293', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R318 | BIAYA ADMIN] ADMIN BANK SERAGAM TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-25', 'TRANS_BANK', @ref_id, 'BNK26-0293', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R318 | BIAYA ADMIN] ADMIN BANK SERAGAM TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0294 | Excel R319 | 2026-07-28 | 1231 (D) -> 1111 (K) (Aset Tetap - Pembelian / UTJ Kendaraan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0294', 'BANK_KELUAR', 'TTC', '2026-07-28', 'July 2026', 309, 361, 10000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R319 | PEMBAYARAN] UTJ PEMBELIAN MOBIL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(361, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0294', 'DEBIT', 10000000.00, 10000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R319 | PEMBAYARAN] UTJ PEMBELIAN MOBIL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0294', 'KREDIT', 10000000.00, 0.00, 10000000.00, 0.00, 0.00, '[Excel R319 | PEMBAYARAN] UTJ PEMBELIAN MOBIL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0295 | Excel R320 | 2026-07-28 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0295', 'BANK_KELUAR', 'TTC', '2026-07-28', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R320 | BIAYA ADMIN] ADMIN BANK UTJ', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0295', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R320 | BIAYA ADMIN] ADMIN BANK UTJ', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0295', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R320 | BIAYA ADMIN] ADMIN BANK UTJ', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0296 | Excel R321 | 2026-07-28 | 1261 (D) -> 1111 (K) (Aset Tetap - Renovasi, Partisi & Leasehold Improvement)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0296', 'BANK_KELUAR', 'TTC', '2026-07-28', 'July 2026', 309, 378, 1590000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R321 | PEMBELIAN] MATERIAL PARTISI GUDANG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(378, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0296', 'DEBIT', 1590000.00, 1590000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R321 | PEMBELIAN] MATERIAL PARTISI GUDANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0296', 'KREDIT', 1590000.00, 0.00, 1590000.00, 0.00, 0.00, '[Excel R321 | PEMBELIAN] MATERIAL PARTISI GUDANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0297 | Excel R322 | 2026-07-28 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0297', 'BANK_KELUAR', 'TTC', '2026-07-28', 'July 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R322 | BIAYA ADMIN] ADMIN DP PEMBELIAN ANGKUR DAN PLAT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0297', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R322 | BIAYA ADMIN] ADMIN DP PEMBELIAN ANGKUR DAN PLAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-28', 'TRANS_BANK', @ref_id, 'BNK26-0297', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R322 | BIAYA ADMIN] ADMIN DP PEMBELIAN ANGKUR DAN PLAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0298 | Excel R323 | 2026-07-29 | 1112 (D) -> 4540 (K) (Retur Pembelian / Pendapatan Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0298', 'BANK_MASUK', 'TTC', '2026-07-29', 'July 2026', 310, 470, 300000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R323 | PENERIMAAN] RETUR PEMBELIAN TOKO ANYAR', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0298', 'DEBIT', 300000.00, 300000.00, 0.00, 0.00, 0.00, '[Excel R323 | PENERIMAAN] RETUR PEMBELIAN TOKO ANYAR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(470, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0298', 'KREDIT', 300000.00, 0.00, 300000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R323 | PENERIMAAN] RETUR PEMBELIAN TOKO ANYAR', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0299 | Excel R324 | 2026-07-29 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0299', 'BANK_KELUAR', 'TTC', '2026-07-29', 'July 2026', 309, 373, 7000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R324 | PEMBELIAN] MATERIAL KAYU MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0299', 'DEBIT', 7000000.00, 7000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R324 | PEMBELIAN] MATERIAL KAYU MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0299', 'KREDIT', 7000000.00, 0.00, 7000000.00, 0.00, 0.00, '[Excel R324 | PEMBELIAN] MATERIAL KAYU MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0300 | Excel R325 | 2026-07-29 | 6305 (D) -> 1111 (K) (Training & Pengembangan / Magang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0300', 'BANK_KELUAR', 'TTC', '2026-07-29', 'July 2026', 309, 532, 1500000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R325 | PEMBAYARAN] UANG SAKU TRAINING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(532, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0300', 'DEBIT', 1500000.00, 1500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R325 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0300', 'KREDIT', 1500000.00, 0.00, 1500000.00, 0.00, 0.00, '[Excel R325 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0301 | Excel R326 | 2026-07-29 | 6305 (D) -> 1111 (K) (Training & Pengembangan / Magang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0301', 'BANK_KELUAR', 'TTC', '2026-07-29', 'July 2026', 309, 532, 1000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R326 | PEMBAYARAN] UANG SAKU TRAINING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(532, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0301', 'DEBIT', 1000000.00, 1000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R326 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0301', 'KREDIT', 1000000.00, 0.00, 1000000.00, 0.00, 0.00, '[Excel R326 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0302 | Excel R327 | 2026-07-29 | 6610 (D) -> 1111 (K) (Biaya Operasional / Reimburse Lain-lain)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0302', 'BANK_KELUAR', 'TTC', '2026-07-29', 'July 2026', 309, 542, 1948000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R327 | PEMBAYARAN] REIMBURSE PAK HERI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0302', 'DEBIT', 1948000.00, 1948000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R327 | PEMBAYARAN] REIMBURSE PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0302', 'KREDIT', 1948000.00, 0.00, 1948000.00, 0.00, 0.00, '[Excel R327 | PEMBAYARAN] REIMBURSE PAK HERI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0303 | Excel R328 | 2026-07-29 | 6305 (D) -> 1111 (K) (Training & Pengembangan / Magang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0303', 'BANK_KELUAR', 'TTC', '2026-07-29', 'July 2026', 309, 532, 1000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R328 | PEMBAYARAN] UANG SAKU TRAINING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(532, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0303', 'DEBIT', 1000000.00, 1000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R328 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0303', 'KREDIT', 1000000.00, 0.00, 1000000.00, 0.00, 0.00, '[Excel R328 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0304 | Excel R329 | 2026-07-29 | 6305 (D) -> 1111 (K) (Training & Pengembangan / Magang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0304', 'BANK_KELUAR', 'TTC', '2026-07-29', 'July 2026', 309, 532, 1000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R329 | PEMBAYARAN] UANG SAKU TRAINING', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(532, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0304', 'DEBIT', 1000000.00, 1000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R329 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-29', 'TRANS_BANK', @ref_id, 'BNK26-0304', 'KREDIT', 1000000.00, 0.00, 1000000.00, 0.00, 0.00, '[Excel R329 | PEMBAYARAN] UANG SAKU TRAINING', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0305 | Excel R330 | 2026-07-31 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0305', 'BANK_KELUAR', 'TTC', '2026-07-31', 'July 2026', 309, 355, 15000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R330 | PEMBAYARAN] DP MATERIAL KABEL NYY, SKUN KABEL & VINYL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0305', 'DEBIT', 15000000.00, 15000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R330 | PEMBAYARAN] DP MATERIAL KABEL NYY, SKUN KABEL & VINYL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0305', 'KREDIT', 15000000.00, 0.00, 15000000.00, 0.00, 0.00, '[Excel R330 | PEMBAYARAN] DP MATERIAL KABEL NYY, SKUN KABEL & VINYL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0306 | Excel R331 | 2026-07-31 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0306', 'BANK_KELUAR', 'TTC', '2026-07-31', 'July 2026', 309, 528, 17482260.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R331 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0306', 'DEBIT', 17482260.00, 17482260.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R331 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0306', 'KREDIT', 17482260.00, 0.00, 17482260.00, 0.00, 0.00, '[Excel R331 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0307 | Excel R332 | 2026-07-31 | 1112 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0307', 'BANK_MASUK', 'TTC', '2026-07-31', 'July 2026', 310, 467, 12436.20, NULL, 'Bank - BCA', NULL, NULL, '[Excel R332 | PENERIMAAN] BUNGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0307', 'DEBIT', 12436.20, 12436.20, 0.00, 0.00, 0.00, '[Excel R332 | PENERIMAAN] BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0307', 'KREDIT', 12436.20, 0.00, 12436.20, 0.00, 0.00, 'Lawan penerimaan [Excel R332 | PENERIMAAN] BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0308 | Excel R333 | 2026-07-31 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0308', 'BANK_KELUAR', 'TTC', '2026-07-31', 'July 2026', 310, 523, 2487.24, NULL, 'Bank - BCA', NULL, NULL, '[Excel R333 | BIAYA ADMIN] PAJAK BUNGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0308', 'DEBIT', 2487.24, 2487.24, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R333 | BIAYA ADMIN] PAJAK BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-07-31', 'TRANS_BANK', @ref_id, 'BNK26-0308', 'KREDIT', 2487.24, 0.00, 2487.24, 0.00, 0.00, '[Excel R333 | BIAYA ADMIN] PAJAK BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0309 | Excel R336 | 2026-08-01 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0309', 'BANK_KELUAR', 'TTC', '2026-08-01', 'August 2026', 310, 523, 40000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R336 | BIAYA ADMIN] BIAYA ADMIN BDC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-01', 'TRANS_BANK', @ref_id, 'BNK26-0309', 'DEBIT', 40000.00, 40000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R336 | BIAYA ADMIN] BIAYA ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-08-01', 'TRANS_BANK', @ref_id, 'BNK26-0309', 'KREDIT', 40000.00, 0.00, 40000.00, 0.00, 0.00, '[Excel R336 | BIAYA ADMIN] BIAYA ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0310 | Excel R337 | 2026-08-01 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0310', 'BANK_KELUAR', 'TTC', '2026-08-01', 'August 2026', 310, 523, 30000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R337 | BIAYA ADMIN] BIAYA ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-01', 'TRANS_BANK', @ref_id, 'BNK26-0310', 'DEBIT', 30000.00, 30000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R337 | BIAYA ADMIN] BIAYA ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-08-01', 'TRANS_BANK', @ref_id, 'BNK26-0310', 'KREDIT', 30000.00, 0.00, 30000.00, 0.00, 0.00, '[Excel R337 | BIAYA ADMIN] BIAYA ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0311 | Excel R338 | 2026-08-03 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0311', 'BANK_KELUAR', 'TTC', '2026-08-03', 'August 2026', 309, 355, 20861400.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R338 | PEMBELIAN] MATERIAL KABEL NYY, SKUN KABEL & VINYL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-08-03', 'TRANS_BANK', @ref_id, 'BNK26-0311', 'DEBIT', 20861400.00, 20861400.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R338 | PEMBELIAN] MATERIAL KABEL NYY, SKUN KABEL & VINYL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-03', 'TRANS_BANK', @ref_id, 'BNK26-0311', 'KREDIT', 20861400.00, 0.00, 20861400.00, 0.00, 0.00, '[Excel R338 | PEMBELIAN] MATERIAL KABEL NYY, SKUN KABEL & VINYL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0312 | Excel R339 | 2026-08-03 | 6203 (D) -> 1111 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0312', 'BANK_KELUAR', 'TTC', '2026-08-03', 'August 2026', 309, 512, 104000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R339 | PEMBELIAN] TOKEN LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-08-03', 'TRANS_BANK', @ref_id, 'BNK26-0312', 'DEBIT', 104000.00, 104000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R339 | PEMBELIAN] TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-03', 'TRANS_BANK', @ref_id, 'BNK26-0312', 'KREDIT', 104000.00, 0.00, 104000.00, 0.00, 0.00, '[Excel R339 | PEMBELIAN] TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0313 | Excel R340 | 2026-08-06 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0313', 'BANK_KELUAR', 'TTC', '2026-08-06', 'August 2026', 309, 373, 2300000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R340 | PEMBELIAN] PEMBELIAN BAMBU MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-06', 'TRANS_BANK', @ref_id, 'BNK26-0313', 'DEBIT', 2300000.00, 2300000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R340 | PEMBELIAN] PEMBELIAN BAMBU MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-06', 'TRANS_BANK', @ref_id, 'BNK26-0313', 'KREDIT', 2300000.00, 0.00, 2300000.00, 0.00, 0.00, '[Excel R340 | PEMBELIAN] PEMBELIAN BAMBU MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0314 | Excel R341 | 2026-08-06 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0314', 'BANK_KELUAR', 'TTC', '2026-08-06', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R341 | BIAYA ADMIN] ADMIN PEMBELIAN BAMBU MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-06', 'TRANS_BANK', @ref_id, 'BNK26-0314', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R341 | BIAYA ADMIN] ADMIN PEMBELIAN BAMBU MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-06', 'TRANS_BANK', @ref_id, 'BNK26-0314', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R341 | BIAYA ADMIN] ADMIN PEMBELIAN BAMBU MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0315 | Excel R342 | 2026-08-08 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0315', 'BANK_KELUAR', 'TTC', '2026-08-08', 'August 2026', 309, 373, 1395000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R342 | PEMBELIAN] PEMBELIAN MATERIAL  PAKU,BENANG,WD,KAPUR, & BENDRAT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-08', 'TRANS_BANK', @ref_id, 'BNK26-0315', 'DEBIT', 1395000.00, 1395000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R342 | PEMBELIAN] PEMBELIAN MATERIAL  PAKU,BENANG,WD,KAPUR, & BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-08', 'TRANS_BANK', @ref_id, 'BNK26-0315', 'KREDIT', 1395000.00, 0.00, 1395000.00, 0.00, 0.00, '[Excel R342 | PEMBELIAN] PEMBELIAN MATERIAL  PAKU,BENANG,WD,KAPUR, & BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0316 | Excel R343 | 2026-08-10 | 6205 (D) -> 1111 (K) (Biaya Profesional / Pembuatan Akta Notaris)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0316', 'BANK_KELUAR', 'TTC', '2026-08-10', 'August 2026', 309, 514, 4200000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R343 | PEMBAYARAN] PELUNASAN AKTA PERUBAHAN AD PT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(514, '2026-08-10', 'TRANS_BANK', @ref_id, 'BNK26-0316', 'DEBIT', 4200000.00, 4200000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R343 | PEMBAYARAN] PELUNASAN AKTA PERUBAHAN AD PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-10', 'TRANS_BANK', @ref_id, 'BNK26-0316', 'KREDIT', 4200000.00, 0.00, 4200000.00, 0.00, 0.00, '[Excel R343 | PEMBAYARAN] PELUNASAN AKTA PERUBAHAN AD PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0317 | Excel R344 | 2026-08-10 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0317', 'BANK_KELUAR', 'TTC', '2026-08-10', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R344 | BIAYA ADMIN] ADMIN PELUNASAN AKTA PERUBAHAN AD PT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-10', 'TRANS_BANK', @ref_id, 'BNK26-0317', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R344 | BIAYA ADMIN] ADMIN PELUNASAN AKTA PERUBAHAN AD PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-10', 'TRANS_BANK', @ref_id, 'BNK26-0317', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R344 | BIAYA ADMIN] ADMIN PELUNASAN AKTA PERUBAHAN AD PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0318 | Excel R345 | 2026-08-11 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0318', 'BANK_KELUAR', 'TTC', '2026-08-11', 'August 2026', 309, 373, 19920000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R345 | PEMBELIAN] PEMBELIAN BONDEK GALVANIS', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-11', 'TRANS_BANK', @ref_id, 'BNK26-0318', 'DEBIT', 19920000.00, 19920000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R345 | PEMBELIAN] PEMBELIAN BONDEK GALVANIS', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-11', 'TRANS_BANK', @ref_id, 'BNK26-0318', 'KREDIT', 19920000.00, 0.00, 19920000.00, 0.00, 0.00, '[Excel R345 | PEMBELIAN] PEMBELIAN BONDEK GALVANIS', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0319 | Excel R346 | 2026-08-11 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0319', 'BANK_KELUAR', 'TTC', '2026-08-11', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R346 | BIAYA ADMIN] ADMIN PEMBELIAN BONDEK GALVANIS', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-11', 'TRANS_BANK', @ref_id, 'BNK26-0319', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R346 | BIAYA ADMIN] ADMIN PEMBELIAN BONDEK GALVANIS', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-11', 'TRANS_BANK', @ref_id, 'BNK26-0319', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R346 | BIAYA ADMIN] ADMIN PEMBELIAN BONDEK GALVANIS', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0320 | Excel R347 | 2026-08-13 | 6305 (D) -> 1111 (K) (Training & Pengembangan / Magang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0320', 'BANK_KELUAR', 'TTC', '2026-08-13', 'August 2026', 309, 532, 450000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R347 | PEMBAYARAN] UANG MAKAN DAN TRANSPORT ANK MAGANG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(532, '2026-08-13', 'TRANS_BANK', @ref_id, 'BNK26-0320', 'DEBIT', 450000.00, 450000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R347 | PEMBAYARAN] UANG MAKAN DAN TRANSPORT ANK MAGANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-13', 'TRANS_BANK', @ref_id, 'BNK26-0320', 'KREDIT', 450000.00, 0.00, 450000.00, 0.00, 0.00, '[Excel R347 | PEMBAYARAN] UANG MAKAN DAN TRANSPORT ANK MAGANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0321 | Excel R348 | 2026-08-13 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0321', 'BANK_KELUAR', 'TTC', '2026-08-13', 'August 2026', 309, 373, 3500000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R348 | PEMBELIAN] PEMBELIAN MATERIAL KAYU 4x6', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-13', 'TRANS_BANK', @ref_id, 'BNK26-0321', 'DEBIT', 3500000.00, 3500000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R348 | PEMBELIAN] PEMBELIAN MATERIAL KAYU 4x6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-13', 'TRANS_BANK', @ref_id, 'BNK26-0321', 'KREDIT', 3500000.00, 0.00, 3500000.00, 0.00, 0.00, '[Excel R348 | PEMBELIAN] PEMBELIAN MATERIAL KAYU 4x6', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0322 | Excel R349 | 2026-08-18 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0322', 'BANK_KELUAR', 'TTC', '2026-08-18', 'August 2026', 309, 373, 11625000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R349 | PEMBELIAN] MATERIAL WIREMESH DAN PENAMBAHAN TRIPLEK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-18', 'TRANS_BANK', @ref_id, 'BNK26-0322', 'DEBIT', 11625000.00, 11625000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R349 | PEMBELIAN] MATERIAL WIREMESH DAN PENAMBAHAN TRIPLEK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-18', 'TRANS_BANK', @ref_id, 'BNK26-0322', 'KREDIT', 11625000.00, 0.00, 11625000.00, 0.00, 0.00, '[Excel R349 | PEMBELIAN] MATERIAL WIREMESH DAN PENAMBAHAN TRIPLEK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0323 | Excel R350 | 2026-08-18 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0323', 'BANK_KELUAR', 'TTC', '2026-08-18', 'August 2026', 309, 373, 12599500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R350 | PEMBELIAN] MATERIAL PAKU, BESI, BAMBU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-18', 'TRANS_BANK', @ref_id, 'BNK26-0323', 'DEBIT', 12599500.00, 12599500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R350 | PEMBELIAN] MATERIAL PAKU, BESI, BAMBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-18', 'TRANS_BANK', @ref_id, 'BNK26-0323', 'KREDIT', 12599500.00, 0.00, 12599500.00, 0.00, 0.00, '[Excel R350 | PEMBELIAN] MATERIAL PAKU, BESI, BAMBU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0324 | Excel R351 | 2026-08-19 | 6610 (D) -> 1111 (K) (Perjalanan Dinas (Tiket Pesawat))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0324', 'BANK_KELUAR', 'TTC', '2026-08-19', 'August 2026', 309, 542, 11582000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R351 | PEMBAYARAN] TIKET PESAWAT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(542, '2026-08-19', 'TRANS_BANK', @ref_id, 'BNK26-0324', 'DEBIT', 11582000.00, 11582000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R351 | PEMBAYARAN] TIKET PESAWAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-19', 'TRANS_BANK', @ref_id, 'BNK26-0324', 'KREDIT', 11582000.00, 0.00, 11582000.00, 0.00, 0.00, '[Excel R351 | PEMBAYARAN] TIKET PESAWAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0325 | Excel R352 | 2026-08-20 | 1211 (D) -> 1111 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0325', 'BANK_KELUAR', 'TTC', '2026-08-20', 'August 2026', 309, 349, 188435000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R352 | PEMBAYARAN] CICILAN GUDANG K/7', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0325', 'DEBIT', 188435000.00, 188435000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R352 | PEMBAYARAN] CICILAN GUDANG K/7', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0325', 'KREDIT', 188435000.00, 0.00, 188435000.00, 0.00, 0.00, '[Excel R352 | PEMBAYARAN] CICILAN GUDANG K/7', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0326 | Excel R353 | 2026-08-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0326', 'BANK_KELUAR', 'TTC', '2026-08-20', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R353 | BIAYA ADMIN] ADMIN CICILAN GUDAN K/7', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0326', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R353 | BIAYA ADMIN] ADMIN CICILAN GUDAN K/7', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0326', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R353 | BIAYA ADMIN] ADMIN CICILAN GUDAN K/7', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0327 | Excel R354 | 2026-08-20 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0327', 'BANK_KELUAR', 'TTC', '2026-08-20', 'August 2026', 309, 373, 24400000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R354 | PEMBAYARAN] JASA JAYA MIX UNTUK COR MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0327', 'DEBIT', 24400000.00, 24400000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R354 | PEMBAYARAN] JASA JAYA MIX UNTUK COR MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0327', 'KREDIT', 24400000.00, 0.00, 24400000.00, 0.00, 0.00, '[Excel R354 | PEMBAYARAN] JASA JAYA MIX UNTUK COR MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0328 | Excel R355 | 2026-08-20 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0328', 'BANK_KELUAR', 'TTC', '2026-08-20', 'August 2026', 309, 373, 2000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R355 | PEMBAYARAN] PERIZINAN PEMBANGUNAN DAN OPRASIONAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0328', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R355 | PEMBAYARAN] PERIZINAN PEMBANGUNAN DAN OPRASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0328', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R355 | PEMBAYARAN] PERIZINAN PEMBANGUNAN DAN OPRASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0329 | Excel R356 | 2026-08-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0329', 'BANK_KELUAR', 'TTC', '2026-08-20', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R356 | BIAYA ADMIN] ADMIN PERIZINAN PEMBANGUNAN DAN OPRASIONAL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0329', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R356 | BIAYA ADMIN] ADMIN PERIZINAN PEMBANGUNAN DAN OPRASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0329', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R356 | BIAYA ADMIN] ADMIN PERIZINAN PEMBANGUNAN DAN OPRASIONAL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0330 | Excel R357 | 2026-08-20 | 1111 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0330', 'BANK_MASUK', 'TTC', '2026-08-20', 'August 2026', 309, 467, 528223.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R357 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0330', 'DEBIT', 528223.00, 528223.00, 0.00, 0.00, 0.00, '[Excel R357 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0330', 'KREDIT', 528223.00, 0.00, 528223.00, 0.00, 0.00, 'Lawan penerimaan [Excel R357 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0331 | Excel R358 | 2026-08-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0331', 'BANK_KELUAR', 'TTC', '2026-08-20', 'August 2026', 309, 523, 105645.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R358 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0331', 'DEBIT', 105645.00, 105645.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R358 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-20', 'TRANS_BANK', @ref_id, 'BNK26-0331', 'KREDIT', 105645.00, 0.00, 105645.00, 0.00, 0.00, '[Excel R358 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0332 | Excel R359 | 2026-08-22 | 6502 (D) -> 1111 (K) (BPJS Ketenagakerjaan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0332', 'BANK_KELUAR', 'TTC', '2026-08-22', 'August 2026', 309, 538, 1113652.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R359 | PEMBAYARAN] PEMBAYARAN BPJS KETENAGA KERJAAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(538, '2026-08-22', 'TRANS_BANK', @ref_id, 'BNK26-0332', 'DEBIT', 1113652.00, 1113652.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R359 | PEMBAYARAN] PEMBAYARAN BPJS KETENAGA KERJAAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-22', 'TRANS_BANK', @ref_id, 'BNK26-0332', 'KREDIT', 1113652.00, 0.00, 1113652.00, 0.00, 0.00, '[Excel R359 | PEMBAYARAN] PEMBAYARAN BPJS KETENAGA KERJAAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0333 | Excel R360 | 2026-08-22 | 1231 (D) -> 1111 (K) (Aset Tetap - Pembelian / UTJ Kendaraan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0333', 'BANK_KELUAR', 'TTC', '2026-08-22', 'August 2026', 309, 361, 50000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R360 | PEMBELIAN] UTJ KENDARAAN TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(361, '2026-08-22', 'TRANS_BANK', @ref_id, 'BNK26-0333', 'DEBIT', 50000000.00, 50000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R360 | PEMBELIAN] UTJ KENDARAAN TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-22', 'TRANS_BANK', @ref_id, 'BNK26-0333', 'KREDIT', 50000000.00, 0.00, 50000000.00, 0.00, 0.00, '[Excel R360 | PEMBELIAN] UTJ KENDARAAN TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0334 | Excel R361 | 2026-08-22 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0334', 'BANK_KELUAR', 'TTC', '2026-08-22', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R361 | BIAYA ADMIN] ADMIN UTJ KENDARAAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-22', 'TRANS_BANK', @ref_id, 'BNK26-0334', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R361 | BIAYA ADMIN] ADMIN UTJ KENDARAAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-22', 'TRANS_BANK', @ref_id, 'BNK26-0334', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R361 | BIAYA ADMIN] ADMIN UTJ KENDARAAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0335 | Excel R362 | 2026-08-24 | 6203 (D) -> 1111 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0335', 'BANK_KELUAR', 'TTC', '2026-08-24', 'August 2026', 309, 512, 241200.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R362 | OPERASIONAL] PEMBAYARAN PDAM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-08-24', 'TRANS_BANK', @ref_id, 'BNK26-0335', 'DEBIT', 241200.00, 241200.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R362 | OPERASIONAL] PEMBAYARAN PDAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-24', 'TRANS_BANK', @ref_id, 'BNK26-0335', 'KREDIT', 241200.00, 0.00, 241200.00, 0.00, 0.00, '[Excel R362 | OPERASIONAL] PEMBAYARAN PDAM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0336 | Excel R363 | 2026-08-26 | 6207 (D) -> 1111 (K) (Telepon & Internet)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0336', 'BANK_KELUAR', 'TTC', '2026-08-26', 'August 2026', 309, 516, 1310800.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R363 | OPERASIONAL] TLKM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(516, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0336', 'DEBIT', 1310800.00, 1310800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R363 | OPERASIONAL] TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0336', 'KREDIT', 1310800.00, 0.00, 1310800.00, 0.00, 0.00, '[Excel R363 | OPERASIONAL] TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0337 | Excel R364 | 2026-08-26 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0337', 'BANK_KELUAR', 'TTC', '2026-08-26', 'August 2026', 309, 523, 2800.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R364 | BIAYA ADMIN] ADMIN TLKM', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0337', 'DEBIT', 2800.00, 2800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R364 | BIAYA ADMIN] ADMIN TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0337', 'KREDIT', 2800.00, 0.00, 2800.00, 0.00, 0.00, '[Excel R364 | BIAYA ADMIN] ADMIN TLKM', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0338 | Excel R365 | 2026-08-26 | 6203 (D) -> 1111 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0338', 'BANK_KELUAR', 'TTC', '2026-08-26', 'August 2026', 309, 512, 100000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R365 | OPERASIONAL] TOKEN LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0338', 'DEBIT', 100000.00, 100000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R365 | OPERASIONAL] TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0338', 'KREDIT', 100000.00, 0.00, 100000.00, 0.00, 0.00, '[Excel R365 | OPERASIONAL] TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0339 | Excel R366 | 2026-08-26 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0339', 'BANK_KELUAR', 'TTC', '2026-08-26', 'August 2026', 309, 523, 3000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R366 | BIAYA ADMIN] ADMIN TOKEN LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0339', 'DEBIT', 3000.00, 3000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R366 | BIAYA ADMIN] ADMIN TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0339', 'KREDIT', 3000.00, 0.00, 3000.00, 0.00, 0.00, '[Excel R366 | BIAYA ADMIN] ADMIN TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0340 | Excel R367 | 2026-08-26 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0340', 'BANK_KELUAR', 'TTC', '2026-08-26', 'August 2026', 309, 373, 85000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R367 | PEMBELIAN] PEMBELIAN PAKU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0340', 'DEBIT', 85000.00, 85000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R367 | PEMBELIAN] PEMBELIAN PAKU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0340', 'KREDIT', 85000.00, 0.00, 85000.00, 0.00, 0.00, '[Excel R367 | PEMBELIAN] PEMBELIAN PAKU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0341 | Excel R368 | 2026-08-26 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0341', 'BANK_KELUAR', 'TTC', '2026-08-26', 'August 2026', 309, 373, 505000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R368 | PEMBELIAN] PEMBELIAN SOLASI LISTRIK, KUNCI BOR 13,MATA BOR BESI 10,KABEL 2x1,5', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0341', 'DEBIT', 505000.00, 505000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R368 | PEMBELIAN] PEMBELIAN SOLASI LISTRIK, KUNCI BOR 13,MATA BOR BESI 10,KABEL 2x1,5', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0341', 'KREDIT', 505000.00, 0.00, 505000.00, 0.00, 0.00, '[Excel R368 | PEMBELIAN] PEMBELIAN SOLASI LISTRIK, KUNCI BOR 13,MATA BOR BESI 10,KABEL 2x1,5', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0342 | Excel R369 | 2026-08-26 | 1111 (D) -> 1251 (K) (Pengembalian Sisa Dana Pembangunan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0342', 'BANK_MASUK', 'TTC', '2026-08-26', 'August 2026', 309, 373, 500000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R369 | PENERIMAAN] SISA UANG KONTRIBUSI PEMBANGUNAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0342', 'DEBIT', 500000.00, 500000.00, 0.00, 0.00, 0.00, '[Excel R369 | PENERIMAAN] SISA UANG KONTRIBUSI PEMBANGUNAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(373, '2026-08-26', 'TRANS_BANK', @ref_id, 'BNK26-0342', 'KREDIT', 500000.00, 0.00, 500000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R369 | PENERIMAAN] SISA UANG KONTRIBUSI PEMBANGUNAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0343 | Excel R370 | 2026-08-27 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0343', 'BANK_KELUAR', 'TTC', '2026-08-27', 'August 2026', 309, 373, 4200500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R370 | PEMBELIAN] DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-27', 'TRANS_BANK', @ref_id, 'BNK26-0343', 'DEBIT', 4200500.00, 4200500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R370 | PEMBELIAN] DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-27', 'TRANS_BANK', @ref_id, 'BNK26-0343', 'KREDIT', 4200500.00, 0.00, 4200500.00, 0.00, 0.00, '[Excel R370 | PEMBELIAN] DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0344 | Excel R371 | 2026-08-27 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0344', 'BANK_KELUAR', 'TTC', '2026-08-27', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R371 | PEMBELIAN] ADMIN DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-27', 'TRANS_BANK', @ref_id, 'BNK26-0344', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R371 | PEMBELIAN] ADMIN DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-27', 'TRANS_BANK', @ref_id, 'BNK26-0344', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R371 | PEMBELIAN] ADMIN DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0345 | Excel R372 | 2026-08-28 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0345', 'BANK_KELUAR', 'TTC', '2026-08-28', 'August 2026', 309, 373, 6120000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R372 | PEMBELIAN] DP PEMBELIAN HEBEL 18 KUBIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0345', 'DEBIT', 6120000.00, 6120000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R372 | PEMBELIAN] DP PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0345', 'KREDIT', 6120000.00, 0.00, 6120000.00, 0.00, 0.00, '[Excel R372 | PEMBELIAN] DP PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0346 | Excel R373 | 2026-08-28 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0346', 'BANK_KELUAR', 'TTC', '2026-08-28', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R373 | PEMBELIAN] ADMIN DP PEMBELIAN HEBEL 18 KUBIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0346', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R373 | PEMBELIAN] ADMIN DP PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0346', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R373 | PEMBELIAN] ADMIN DP PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0347 | Excel R374 | 2026-08-28 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0347', 'BANK_KELUAR', 'TTC', '2026-08-28', 'August 2026', 309, 373, 4200500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R374 | PEMBELIAN] PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0347', 'DEBIT', 4200500.00, 4200500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R374 | PEMBELIAN] PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0347', 'KREDIT', 4200500.00, 0.00, 4200500.00, 0.00, 0.00, '[Excel R374 | PEMBELIAN] PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0348 | Excel R375 | 2026-08-28 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0348', 'BANK_KELUAR', 'TTC', '2026-08-28', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R375 | BIAYA ADMIN] ADMIN PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0348', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R375 | BIAYA ADMIN] ADMIN PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0348', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R375 | BIAYA ADMIN] ADMIN PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0349 | Excel R376 | 2026-08-28 | 1111 (D) -> 4101 (K) (Penjualan Barang Jadi / Percetakan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0349', 'BANK_MASUK', 'TTC', '2026-08-28', 'August 2026', 309, 452, 11192541.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R376 | PENJUALAN] PENJUALAN ET. DAN INNER DAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0349', 'DEBIT', 11192541.00, 11192541.00, 0.00, 0.00, 0.00, '[Excel R376 | PENJUALAN] PENJUALAN ET. DAN INNER DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(452, '2026-08-28', 'TRANS_BANK', @ref_id, 'BNK26-0349', 'KREDIT', 11192541.00, 0.00, 11192541.00, 0.00, 0.00, 'Lawan penerimaan [Excel R376 | PENJUALAN] PENJUALAN ET. DAN INNER DAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0350 | Excel R377 | 2026-08-31 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0350', 'BANK_KELUAR', 'TTC', '2026-08-31', 'August 2026', 309, 373, 6120000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R377 | PEMBELIAN] PELUNASAN  PEMBELIAN HEBEL 18 KUBIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0350', 'DEBIT', 6120000.00, 6120000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R377 | PEMBELIAN] PELUNASAN  PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0350', 'KREDIT', 6120000.00, 0.00, 6120000.00, 0.00, 0.00, '[Excel R377 | PEMBELIAN] PELUNASAN  PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0351 | Excel R378 | 2026-08-31 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0351', 'BANK_KELUAR', 'TTC', '2026-08-31', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R378 | PEMBELIAN] ADMIN PELUNASAN PEMBELIAN HEBEL 18 KUBIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0351', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R378 | PEMBELIAN] ADMIN PELUNASAN PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0351', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R378 | PEMBELIAN] ADMIN PELUNASAN PEMBELIAN HEBEL 18 KUBIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0352 | Excel R379 | 2026-08-31 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0352', 'BANK_KELUAR', 'TTC', '2026-08-31', 'August 2026', 309, 373, 110000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R379 | PEMBELIAN] PEMBELIAN KAWAT BENDRAT 1KG 5x', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0352', 'DEBIT', 110000.00, 110000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R379 | PEMBELIAN] PEMBELIAN KAWAT BENDRAT 1KG 5x', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0352', 'KREDIT', 110000.00, 0.00, 110000.00, 0.00, 0.00, '[Excel R379 | PEMBELIAN] PEMBELIAN KAWAT BENDRAT 1KG 5x', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0353 | Excel R380 | 2026-08-31 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0353', 'BANK_KELUAR', 'TTC', '2026-08-31', 'August 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R380 | PEMBELIAN] ADMIN PEMBELIAN KAWAT BENDRAT 1KG 5x', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0353', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R380 | PEMBELIAN] ADMIN PEMBELIAN KAWAT BENDRAT 1KG 5x', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0353', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R380 | PEMBELIAN] ADMIN PEMBELIAN KAWAT BENDRAT 1KG 5x', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0354 | Excel R381 | 2026-08-31 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0354', 'BANK_KELUAR', 'TTC', '2026-08-31', 'August 2026', 309, 528, 17856960.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R381 | PEMBAYARAN] GAJI KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0354', 'DEBIT', 17856960.00, 17856960.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R381 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0354', 'KREDIT', 17856960.00, 0.00, 17856960.00, 0.00, 0.00, '[Excel R381 | PEMBAYARAN] GAJI KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0355 | Excel R382 | 2026-08-31 | 1112 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0355', 'BANK_MASUK', 'TTC', '2026-08-31', 'August 2026', 310, 467, 12473.31, NULL, 'Bank - BCA', NULL, NULL, '[Excel R382 | PENERIMAAN] BUNGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0355', 'DEBIT', 12473.31, 12473.31, 0.00, 0.00, 0.00, '[Excel R382 | PENERIMAAN] BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0355', 'KREDIT', 12473.31, 0.00, 12473.31, 0.00, 0.00, 'Lawan penerimaan [Excel R382 | PENERIMAAN] BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0356 | Excel R383 | 2026-08-31 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0356', 'BANK_KELUAR', 'TTC', '2026-08-31', 'August 2026', 310, 523, 2494.66, NULL, 'Bank - BCA', NULL, NULL, '[Excel R383 | BIAYA ADMIN] PAJAK BUNGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0356', 'DEBIT', 2494.66, 2494.66, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R383 | BIAYA ADMIN] PAJAK BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-08-31', 'TRANS_BANK', @ref_id, 'BNK26-0356', 'KREDIT', 2494.66, 0.00, 2494.66, 0.00, 0.00, '[Excel R383 | BIAYA ADMIN] PAJAK BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0357 | Excel R386 | 2026-09-01 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0357', 'BANK_KELUAR', 'TTC', '2026-09-01', 'September 2026', 309, 528, 2000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R386 | PEMBAYARAN] GAJI 1 KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-09-01', 'TRANS_BANK', @ref_id, 'BNK26-0357', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R386 | PEMBAYARAN] GAJI 1 KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-01', 'TRANS_BANK', @ref_id, 'BNK26-0357', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R386 | PEMBAYARAN] GAJI 1 KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0358 | Excel R387 | 2026-09-01 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0358', 'BANK_KELUAR', 'TTC', '2026-09-01', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R387 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-01', 'TRANS_BANK', @ref_id, 'BNK26-0358', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R387 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-01', 'TRANS_BANK', @ref_id, 'BNK26-0358', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R387 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0359 | Excel R388 | 2026-09-02 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0359', 'BANK_KELUAR', 'TTC', '2026-09-02', 'September 2026', 309, 373, 865000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R388 | PEMBELIAN] PEMBELIAN MATERIAL MES TTC LT 2', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-02', 'TRANS_BANK', @ref_id, 'BNK26-0359', 'DEBIT', 865000.00, 865000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R388 | PEMBELIAN] PEMBELIAN MATERIAL MES TTC LT 2', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-02', 'TRANS_BANK', @ref_id, 'BNK26-0359', 'KREDIT', 865000.00, 0.00, 865000.00, 0.00, 0.00, '[Excel R388 | PEMBELIAN] PEMBELIAN MATERIAL MES TTC LT 2', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0360 | Excel R389 | 2026-09-02 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0360', 'BANK_KELUAR', 'TTC', '2026-09-02', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R389 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-02', 'TRANS_BANK', @ref_id, 'BNK26-0360', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R389 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-02', 'TRANS_BANK', @ref_id, 'BNK26-0360', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R389 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0361 | Excel R390 | 2026-09-02 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0361', 'BANK_KELUAR', 'TTC', '2026-09-02', 'September 2026', 310, 523, 30000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R390 | BIAYA ADMIN] BIAYA ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-02', 'TRANS_BANK', @ref_id, 'BNK26-0361', 'DEBIT', 30000.00, 30000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R390 | BIAYA ADMIN] BIAYA ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-09-02', 'TRANS_BANK', @ref_id, 'BNK26-0361', 'KREDIT', 30000.00, 0.00, 30000.00, 0.00, 0.00, '[Excel R390 | BIAYA ADMIN] BIAYA ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0362 | Excel R391 | 2026-09-05 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0362', 'BANK_KELUAR', 'TTC', '2026-09-05', 'September 2026', 310, 523, 40000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R391 | BIAYA ADMIN] BIAYA ADMIN BDC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-05', 'TRANS_BANK', @ref_id, 'BNK26-0362', 'DEBIT', 40000.00, 40000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R391 | BIAYA ADMIN] BIAYA ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-09-05', 'TRANS_BANK', @ref_id, 'BNK26-0362', 'KREDIT', 40000.00, 0.00, 40000.00, 0.00, 0.00, '[Excel R391 | BIAYA ADMIN] BIAYA ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0363 | Excel R392 | 2026-09-07 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0363', 'BANK_KELUAR', 'TTC', '2026-09-07', 'September 2026', 309, 373, 200000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R392 | OPERASIONAL] JASA MENURUNKAN BESI DAN TRIPEK MES TTC 4 ORANG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-07', 'TRANS_BANK', @ref_id, 'BNK26-0363', 'DEBIT', 200000.00, 200000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R392 | OPERASIONAL] JASA MENURUNKAN BESI DAN TRIPEK MES TTC 4 ORANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-07', 'TRANS_BANK', @ref_id, 'BNK26-0363', 'KREDIT', 200000.00, 0.00, 200000.00, 0.00, 0.00, '[Excel R392 | OPERASIONAL] JASA MENURUNKAN BESI DAN TRIPEK MES TTC 4 ORANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0364 | Excel R393 | 2026-09-07 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0364', 'BANK_KELUAR', 'TTC', '2026-09-07', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R393 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-07', 'TRANS_BANK', @ref_id, 'BNK26-0364', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R393 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-07', 'TRANS_BANK', @ref_id, 'BNK26-0364', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R393 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0365 | Excel R394 | 2026-09-09 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0365', 'BANK_KELUAR', 'TTC', '2026-09-09', 'September 2026', 309, 373, 3386000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R394 | PEMBELIAN] MATERIAL PROSES TAHP BLK BACK DN TING TLNG COR 3 N', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0365', 'DEBIT', 3386000.00, 3386000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R394 | PEMBELIAN] MATERIAL PROSES TAHP BLK BACK DN TING TLNG COR 3 N', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0365', 'KREDIT', 3386000.00, 0.00, 3386000.00, 0.00, 0.00, '[Excel R394 | PEMBELIAN] MATERIAL PROSES TAHP BLK BACK DN TING TLNG COR 3 N', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0366 | Excel R395 | 2026-09-09 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0366', 'BANK_KELUAR', 'TTC', '2026-09-09', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R395 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0366', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R395 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0366', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R395 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0367 | Excel R396 | 2026-09-09 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0367', 'BANK_KELUAR', 'TTC', '2026-09-09', 'September 2026', 309, 373, 6803000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R396 | PEMBELIAN] PENAMBAHAN MATERIAL UTK MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0367', 'DEBIT', 6803000.00, 6803000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R396 | PEMBELIAN] PENAMBAHAN MATERIAL UTK MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0367', 'KREDIT', 6803000.00, 0.00, 6803000.00, 0.00, 0.00, '[Excel R396 | PEMBELIAN] PENAMBAHAN MATERIAL UTK MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0368 | Excel R397 | 2026-09-09 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0368', 'BANK_KELUAR', 'TTC', '2026-09-09', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R397 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0368', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R397 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0368', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R397 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0369 | Excel R398 | 2026-09-09 | 1112 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0369', 'BANK_MASUK', 'TTC', '2026-09-09', 'September 2026', 310, 439, 544073239.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R398 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0369', 'DEBIT', 544073239.00, 544073239.00, 0.00, 0.00, 0.00, '[Excel R398 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0369', 'KREDIT', 544073239.00, 0.00, 544073239.00, 0.00, 0.00, 'Lawan penerimaan [Excel R398 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0370 | Excel R399 | 2026-09-09 | 1231 (D) -> 1112 (K) (Aset Tetap - Pembelian / UTJ Kendaraan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0370', 'BANK_KELUAR', 'TTC', '2026-09-09', 'September 2026', 310, 361, 544073239.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R399 | PEMBELIAN] PEMBAYARAN MOBIL', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(361, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0370', 'DEBIT', 544073239.00, 544073239.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R399 | PEMBELIAN] PEMBAYARAN MOBIL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0370', 'KREDIT', 544073239.00, 0.00, 544073239.00, 0.00, 0.00, '[Excel R399 | PEMBELIAN] PEMBAYARAN MOBIL', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0371 | Excel R400 | 2026-09-09 | 6402 (D) -> 1111 (K) (Retribusi & Perizinan (NIB / Domisili / Izin))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0371', 'BANK_KELUAR', 'TTC', '2026-09-09', 'September 2026', 309, 535, 200000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R400 | PEMBAYARAN] PENGURUSAN SURAT KETERANGAN DOMISILI PT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(535, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0371', 'DEBIT', 200000.00, 200000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R400 | PEMBAYARAN] PENGURUSAN SURAT KETERANGAN DOMISILI PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0371', 'KREDIT', 200000.00, 0.00, 200000.00, 0.00, 0.00, '[Excel R400 | PEMBAYARAN] PENGURUSAN SURAT KETERANGAN DOMISILI PT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0372 | Excel R401 | 2026-09-11 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0372', 'BANK_KELUAR', 'TTC', '2026-09-11', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R401 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-11', 'TRANS_BANK', @ref_id, 'BNK26-0372', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R401 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-11', 'TRANS_BANK', @ref_id, 'BNK26-0372', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R401 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0373 | Excel R402 | 2026-09-09 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0373', 'BANK_KELUAR', 'TTC', '2026-09-09', 'September 2026', 309, 373, 1800000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R402 | PEMBELIAN] MATERIAL PASIR 1 RIT TRUCK UNTUK COR LT 2 MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0373', 'DEBIT', 1800000.00, 1800000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R402 | PEMBELIAN] MATERIAL PASIR 1 RIT TRUCK UNTUK COR LT 2 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-09', 'TRANS_BANK', @ref_id, 'BNK26-0373', 'KREDIT', 1800000.00, 0.00, 1800000.00, 0.00, 0.00, '[Excel R402 | PEMBELIAN] MATERIAL PASIR 1 RIT TRUCK UNTUK COR LT 2 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0374 | Excel R403 | 2026-09-15 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0374', 'BANK_KELUAR', 'TTC', '2026-09-15', 'September 2026', 309, 373, 1111000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R403 | PEMBELIAN] STOCK MTRL MES TTC UTK PROSES COR 1 MINGGU KDPN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0374', 'DEBIT', 1111000.00, 1111000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R403 | PEMBELIAN] STOCK MTRL MES TTC UTK PROSES COR 1 MINGGU KDPN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0374', 'KREDIT', 1111000.00, 0.00, 1111000.00, 0.00, 0.00, '[Excel R403 | PEMBELIAN] STOCK MTRL MES TTC UTK PROSES COR 1 MINGGU KDPN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0375 | Excel R404 | 2026-09-15 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0375', 'BANK_KELUAR', 'TTC', '2026-09-15', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R404 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0375', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R404 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0375', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R404 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0376 | Excel R405 | 2026-09-15 | 6502 (D) -> 1111 (K) (BPJS Ketenagakerjaan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0376', 'BANK_KELUAR', 'TTC', '2026-09-15', 'September 2026', 309, 538, 1113652.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R405 | PEMBAYARAN] BPJS TK PU', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(538, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0376', 'DEBIT', 1113652.00, 1113652.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R405 | PEMBAYARAN] BPJS TK PU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0376', 'KREDIT', 1113652.00, 0.00, 1113652.00, 0.00, 0.00, '[Excel R405 | PEMBAYARAN] BPJS TK PU', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0377 | Excel R406 | 2026-09-15 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0377', 'BANK_MASUK', 'TTC', '2026-09-15', 'September 2026', 309, 439, 2450000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R406 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0377', 'DEBIT', 2450000000.00, 2450000000.00, 0.00, 0.00, 0.00, '[Excel R406 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0377', 'KREDIT', 2450000000.00, 0.00, 2450000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R406 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0378 | Excel R407 | 2026-09-15 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0378', 'BANK_MASUK', 'TTC', '2026-09-15', 'September 2026', 309, 439, 550000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R407 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0378', 'DEBIT', 550000000.00, 550000000.00, 0.00, 0.00, 0.00, '[Excel R407 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0378', 'KREDIT', 550000000.00, 0.00, 550000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R407 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0379 | Excel R408 | 2026-09-15 | 6203 (D) -> 1111 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0379', 'BANK_KELUAR', 'TTC', '2026-09-15', 'September 2026', 309, 512, 100000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R408 | PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0379', 'DEBIT', 100000.00, 100000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R408 | PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0379', 'KREDIT', 100000.00, 0.00, 100000.00, 0.00, 0.00, '[Excel R408 | PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0380 | Excel R409 | 2026-09-15 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0380', 'BANK_KELUAR', 'TTC', '2026-09-15', 'September 2026', 309, 523, 3000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R409 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0380', 'DEBIT', 3000.00, 3000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R409 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-15', 'TRANS_BANK', @ref_id, 'BNK26-0380', 'KREDIT', 3000.00, 0.00, 3000.00, 0.00, 0.00, '[Excel R409 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0381 | Excel R410 | 2026-09-16 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0381', 'BANK_KELUAR', 'TTC', '2026-09-16', 'September 2026', 309, 355, 3000886601.50, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R410 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0381', 'DEBIT', 3000886601.50, 3000886601.50, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R410 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0381', 'KREDIT', 3000886601.50, 0.00, 3000886601.50, 0.00, 0.00, '[Excel R410 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0382 | Excel R411 | 2026-09-16 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0382', 'BANK_KELUAR', 'TTC', '2026-09-16', 'September 2026', 309, 373, 10730000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R411 | PEMBELIAN] PEMBELIAN WIREMESH DAN BONDEK UNTUK MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0382', 'DEBIT', 10730000.00, 10730000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R411 | PEMBELIAN] PEMBELIAN WIREMESH DAN BONDEK UNTUK MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0382', 'KREDIT', 10730000.00, 0.00, 10730000.00, 0.00, 0.00, '[Excel R411 | PEMBELIAN] PEMBELIAN WIREMESH DAN BONDEK UNTUK MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0383 | Excel R412 | 2026-09-16 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0383', 'BANK_KELUAR', 'TTC', '2026-09-16', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R412 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0383', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R412 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0383', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R412 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0384 | Excel R413 | 2026-09-16 | 1251 (D) -> 1114 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0384', 'BANK_KELUAR', 'TTC', '2026-09-16', 'September 2026', 312, 373, 1800000.00, NULL, 'Bank - BRI GIRO', NULL, NULL, '[Excel R413 | PEMBELIAN] PEMBELIAN PASIR UNTUK PROSES COR MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0384', 'DEBIT', 1800000.00, 1800000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R413 | PEMBELIAN] PEMBELIAN PASIR UNTUK PROSES COR MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(312, '2026-09-16', 'TRANS_BANK', @ref_id, 'BNK26-0384', 'KREDIT', 1800000.00, 0.00, 1800000.00, 0.00, 0.00, '[Excel R413 | PEMBELIAN] PEMBELIAN PASIR UNTUK PROSES COR MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0385 | Excel R414 | 2026-09-18 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0385', 'BANK_KELUAR', 'TTC', '2026-09-18', 'September 2026', 309, 373, 727500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R414 | PEMBELIAN] REIMBUERSE SEMEN MERAH PUTIH 15 SAK KAV 24', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-18', 'TRANS_BANK', @ref_id, 'BNK26-0385', 'DEBIT', 727500.00, 727500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R414 | PEMBELIAN] REIMBUERSE SEMEN MERAH PUTIH 15 SAK KAV 24', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-18', 'TRANS_BANK', @ref_id, 'BNK26-0385', 'KREDIT', 727500.00, 0.00, 727500.00, 0.00, 0.00, '[Excel R414 | PEMBELIAN] REIMBUERSE SEMEN MERAH PUTIH 15 SAK KAV 24', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0386 | Excel R415 | 2026-09-18 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0386', 'BANK_KELUAR', 'TTC', '2026-09-18', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R415 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-18', 'TRANS_BANK', @ref_id, 'BNK26-0386', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R415 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-18', 'TRANS_BANK', @ref_id, 'BNK26-0386', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R415 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0387 | Excel R416 | 2026-09-18 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0387', 'BANK_KELUAR', 'TTC', '2026-09-18', 'September 2026', 309, 373, 3600000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R416 | PEMBELIAN] PASIR LMJNG 2 RIT TRUCK UTK PROSES COR ATAP MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-18', 'TRANS_BANK', @ref_id, 'BNK26-0387', 'DEBIT', 3600000.00, 3600000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R416 | PEMBELIAN] PASIR LMJNG 2 RIT TRUCK UTK PROSES COR ATAP MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-18', 'TRANS_BANK', @ref_id, 'BNK26-0387', 'KREDIT', 3600000.00, 0.00, 3600000.00, 0.00, 0.00, '[Excel R416 | PEMBELIAN] PASIR LMJNG 2 RIT TRUCK UTK PROSES COR ATAP MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0388 | Excel R417 | 2026-09-20 | 1111 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0388', 'BANK_MASUK', 'TTC', '2026-09-20', 'September 2026', 309, 467, 338795.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R417 | PENERIMAAN] INTEREST ON ACCOUNT', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-09-20', 'TRANS_BANK', @ref_id, 'BNK26-0388', 'DEBIT', 338795.00, 338795.00, 0.00, 0.00, 0.00, '[Excel R417 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-09-20', 'TRANS_BANK', @ref_id, 'BNK26-0388', 'KREDIT', 338795.00, 0.00, 338795.00, 0.00, 0.00, 'Lawan penerimaan [Excel R417 | PENERIMAAN] INTEREST ON ACCOUNT', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0389 | Excel R418 | 2026-09-20 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0389', 'BANK_KELUAR', 'TTC', '2026-09-20', 'September 2026', 309, 523, 67759.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R418 | BIAYA ADMIN] TAX', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-20', 'TRANS_BANK', @ref_id, 'BNK26-0389', 'DEBIT', 67759.00, 67759.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R418 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-20', 'TRANS_BANK', @ref_id, 'BNK26-0389', 'KREDIT', 67759.00, 0.00, 67759.00, 0.00, 0.00, '[Excel R418 | BIAYA ADMIN] TAX', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0390 | Excel R419 | 2026-09-21 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0390', 'BANK_KELUAR', 'TTC', '2026-09-21', 'September 2026', 309, 523, 1000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R419 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-21', 'TRANS_BANK', @ref_id, 'BNK26-0390', 'DEBIT', 1000.00, 1000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R419 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-21', 'TRANS_BANK', @ref_id, 'BNK26-0390', 'KREDIT', 1000.00, 0.00, 1000.00, 0.00, 0.00, '[Excel R419 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0391 | Excel R420 | 2026-09-22 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0391', 'BANK_KELUAR', 'TTC', '2026-09-22', 'September 2026', 309, 373, 1351500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R420 | PEMBELIAN] PEMBELIAN MATERIAL UNTUK COR RING BALOK LT2 MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0391', 'DEBIT', 1351500.00, 1351500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R420 | PEMBELIAN] PEMBELIAN MATERIAL UNTUK COR RING BALOK LT2 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0391', 'KREDIT', 1351500.00, 0.00, 1351500.00, 0.00, 0.00, '[Excel R420 | PEMBELIAN] PEMBELIAN MATERIAL UNTUK COR RING BALOK LT2 MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0392 | Excel R421 | 2026-09-22 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0392', 'BANK_KELUAR', 'TTC', '2026-09-22', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R421 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0392', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R421 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0392', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R421 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0393 | Excel R422 | 2026-09-22 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0393', 'BANK_MASUK', 'TTC', '2026-09-22', 'September 2026', 309, 439, 848000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R422 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0393', 'DEBIT', 848000000.00, 848000000.00, 0.00, 0.00, 0.00, '[Excel R422 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0393', 'KREDIT', 848000000.00, 0.00, 848000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R422 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0394 | Excel R423 | 2026-09-22 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0394', 'BANK_MASUK', 'TTC', '2026-09-22', 'September 2026', 309, 439, 2500000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R423 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0394', 'DEBIT', 2500000000.00, 2500000000.00, 0.00, 0.00, 0.00, '[Excel R423 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0394', 'KREDIT', 2500000000.00, 0.00, 2500000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R423 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0395 | Excel R424 | 2026-09-22 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0395', 'BANK_KELUAR', 'TTC', '2026-09-22', 'September 2026', 309, 355, 3151947602.40, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R424 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0395', 'DEBIT', 3151947602.40, 3151947602.40, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R424 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-22', 'TRANS_BANK', @ref_id, 'BNK26-0395', 'KREDIT', 3151947602.40, 0.00, 3151947602.40, 0.00, 0.00, '[Excel R424 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0396 | Excel R425 | 2026-09-24 | 6207 (D) -> 1111 (K) (Telepon & Internet)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0396', 'BANK_KELUAR', 'TTC', '2026-09-24', 'September 2026', 309, 516, 1310800.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R425 | PEMBAYARAN] PEMBAYARAN TAGIHAN WIFI GUDANG', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(516, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0396', 'DEBIT', 1310800.00, 1310800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R425 | PEMBAYARAN] PEMBAYARAN TAGIHAN WIFI GUDANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0396', 'KREDIT', 1310800.00, 0.00, 1310800.00, 0.00, 0.00, '[Excel R425 | PEMBAYARAN] PEMBAYARAN TAGIHAN WIFI GUDANG', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0397 | Excel R426 | 2026-09-24 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0397', 'BANK_KELUAR', 'TTC', '2026-09-24', 'September 2026', 309, 523, 2800.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R426 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0397', 'DEBIT', 2800.00, 2800.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R426 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0397', 'KREDIT', 2800.00, 0.00, 2800.00, 0.00, 0.00, '[Excel R426 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0398 | Excel R427 | 2026-09-24 | 1251 (D) -> 1111 (K) (Aset Tetap - Bangunan Dalam Pembangunan (Mes TTC / Nogosari))
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0398', 'BANK_KELUAR', 'TTC', '2026-09-24', 'September 2026', 309, 373, 525000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R427 | PEMBELIAN] PEMBELIAN LEM SEMEN UNTUK MES TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(373, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0398', 'DEBIT', 525000.00, 525000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R427 | PEMBELIAN] PEMBELIAN LEM SEMEN UNTUK MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0398', 'KREDIT', 525000.00, 0.00, 525000.00, 0.00, 0.00, '[Excel R427 | PEMBELIAN] PEMBELIAN LEM SEMEN UNTUK MES TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0399 | Excel R428 | 2026-09-24 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0399', 'BANK_KELUAR', 'TTC', '2026-09-24', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R428 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0399', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R428 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0399', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R428 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0400 | Excel R429 | 2026-09-24 | 6203 (D) -> 1111 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0400', 'BANK_KELUAR', 'TTC', '2026-09-24', 'September 2026', 309, 512, 122850.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R429 | PEMBAYARAN] PEMBAYARAN PDAM GUDANG TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0400', 'DEBIT', 122850.00, 122850.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R429 | PEMBAYARAN] PEMBAYARAN PDAM GUDANG TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-24', 'TRANS_BANK', @ref_id, 'BNK26-0400', 'KREDIT', 122850.00, 0.00, 122850.00, 0.00, 0.00, '[Excel R429 | PEMBAYARAN] PEMBAYARAN PDAM GUDANG TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0401 | Excel R430 | 2026-09-25 | 1221 (D) -> 1111 (K) (Aset Tetap - Mesin, Listrik Industri & Peralatan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0401', 'BANK_KELUAR', 'TTC', '2026-09-25', 'September 2026', 309, 355, 31926334.86, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R430 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(355, '2026-09-25', 'TRANS_BANK', @ref_id, 'BNK26-0401', 'DEBIT', 31926334.86, 31926334.86, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R430 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-25', 'TRANS_BANK', @ref_id, 'BNK26-0401', 'KREDIT', 31926334.86, 0.00, 31926334.86, 0.00, 0.00, '[Excel R430 | PEMBAYARAN] PEMBAYARAN MESIN ZONTEN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0402 | Excel R431 | 2026-09-25 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0402', 'BANK_KELUAR', 'TTC', '2026-09-25', 'September 2026', 309, 523, 5000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R431 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-25', 'TRANS_BANK', @ref_id, 'BNK26-0402', 'DEBIT', 5000.00, 5000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R431 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-25', 'TRANS_BANK', @ref_id, 'BNK26-0402', 'KREDIT', 5000.00, 0.00, 5000.00, 0.00, 0.00, '[Excel R431 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0403 | Excel R432 | 2026-09-25 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0403', 'BANK_KELUAR', 'TTC', '2026-09-25', 'September 2026', 309, 523, 1000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R432 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-25', 'TRANS_BANK', @ref_id, 'BNK26-0403', 'DEBIT', 1000.00, 1000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R432 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-25', 'TRANS_BANK', @ref_id, 'BNK26-0403', 'KREDIT', 1000.00, 0.00, 1000.00, 0.00, 0.00, '[Excel R432 | BIAYA ADMIN] BIAYA SMS NOTIFIKASI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0404 | Excel R433 | 2026-09-29 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0404', 'BANK_MASUK', 'TTC', '2026-09-29', 'September 2026', 309, 439, 200000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R433 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0404', 'DEBIT', 200000000.00, 200000000.00, 0.00, 0.00, 0.00, '[Excel R433 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0404', 'KREDIT', 200000000.00, 0.00, 200000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R433 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0405 | Excel R434 | 2026-09-29 | 1211 (D) -> 1111 (K) (Aset Tetap - Cicilan Gudang TTC Blok B8-B9)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0405', 'BANK_KELUAR', 'TTC', '2026-09-29', 'September 2026', 309, 349, 188435000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R434 | PEMBAYARAN] CICILAN GUDANG TTC BLOK B8-B9 KE 8', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(349, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0405', 'DEBIT', 188435000.00, 188435000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R434 | PEMBAYARAN] CICILAN GUDANG TTC BLOK B8-B9 KE 8', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0405', 'KREDIT', 188435000.00, 0.00, 188435000.00, 0.00, 0.00, '[Excel R434 | PEMBAYARAN] CICILAN GUDANG TTC BLOK B8-B9 KE 8', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0406 | Excel R435 | 2026-09-29 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0406', 'BANK_KELUAR', 'TTC', '2026-09-29', 'September 2026', 309, 523, 2500.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R435 | BIAYA ADMIN] ADMIN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0406', 'DEBIT', 2500.00, 2500.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R435 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0406', 'KREDIT', 2500.00, 0.00, 2500.00, 0.00, 0.00, '[Excel R435 | BIAYA ADMIN] ADMIN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0407 | Excel R436 | 2026-09-29 | 6215 (D) -> 1111 (K) (Perawatan Gedung / Reparasi Air)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0407', 'BANK_KELUAR', 'TTC', '2026-09-29', 'September 2026', 309, 524, 282000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R436 | PEMBELIAN] REIMBURSE BIAYA REPARASI AIR TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(524, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0407', 'DEBIT', 282000.00, 282000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R436 | PEMBELIAN] REIMBURSE BIAYA REPARASI AIR TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0407', 'KREDIT', 282000.00, 0.00, 282000.00, 0.00, 0.00, '[Excel R436 | PEMBELIAN] REIMBURSE BIAYA REPARASI AIR TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0408 | Excel R437 | 2026-09-29 | 6108 (D) -> 1111 (K) (Komisi Marketing / Referral)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0408', 'BANK_KELUAR', 'TTC', '2026-09-29', 'September 2026', 309, 508, 801237.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R437 | PEMBAYARAN] COMMISSION ET DAFI 16', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(508, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0408', 'DEBIT', 801237.00, 801237.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R437 | PEMBAYARAN] COMMISSION ET DAFI 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-09-29', 'TRANS_BANK', @ref_id, 'BNK26-0408', 'KREDIT', 801237.00, 0.00, 801237.00, 0.00, 0.00, '[Excel R437 | PEMBAYARAN] COMMISSION ET DAFI 16', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0409 | Excel R438 | 2026-09-30 | 1112 (D) -> 4510 (K) (Pendapatan Bunga Bank)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0409', 'BANK_MASUK', 'TTC', '2026-09-30', 'September 2026', 310, 467, 12060.32, NULL, 'Bank - BCA', NULL, NULL, '[Excel R438 | PENERIMAAN] BUNGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(310, '2026-09-30', 'TRANS_BANK', @ref_id, 'BNK26-0409', 'DEBIT', 12060.32, 12060.32, 0.00, 0.00, 0.00, '[Excel R438 | PENERIMAAN] BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(467, '2026-09-30', 'TRANS_BANK', @ref_id, 'BNK26-0409', 'KREDIT', 12060.32, 0.00, 12060.32, 0.00, 0.00, 'Lawan penerimaan [Excel R438 | PENERIMAAN] BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0410 | Excel R439 | 2026-09-30 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0410', 'BANK_KELUAR', 'TTC', '2026-09-30', 'September 2026', 310, 523, 2412.06, NULL, 'Bank - BCA', NULL, NULL, '[Excel R439 | BIAYA ADMIN] PAJAK BUNGA', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-09-30', 'TRANS_BANK', @ref_id, 'BNK26-0410', 'DEBIT', 2412.06, 2412.06, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R439 | BIAYA ADMIN] PAJAK BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-09-30', 'TRANS_BANK', @ref_id, 'BNK26-0410', 'KREDIT', 2412.06, 0.00, 2412.06, 0.00, 0.00, '[Excel R439 | BIAYA ADMIN] PAJAK BUNGA', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0411 | Excel R442 | 2026-10-01 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0411', 'BANK_KELUAR', 'TTC', '2026-10-01', 'October 2026', 309, 528, 14364960.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R442 | PEMBAYARAN] GAJI 6 KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-10-01', 'TRANS_BANK', @ref_id, 'BNK26-0411', 'DEBIT', 14364960.00, 14364960.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R442 | PEMBAYARAN] GAJI 6 KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-10-01', 'TRANS_BANK', @ref_id, 'BNK26-0411', 'KREDIT', 14364960.00, 0.00, 14364960.00, 0.00, 0.00, '[Excel R442 | PEMBAYARAN] GAJI 6 KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0412 | Excel R443 | 2026-10-02 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0412', 'BANK_MASUK', 'TTC', '2026-10-02', 'October 2026', 309, 439, 200000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R443 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-10-02', 'TRANS_BANK', @ref_id, 'BNK26-0412', 'DEBIT', 200000000.00, 200000000.00, 0.00, 0.00, 0.00, '[Excel R443 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-10-02', 'TRANS_BANK', @ref_id, 'BNK26-0412', 'KREDIT', 200000000.00, 0.00, 200000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R443 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0413 | Excel R444 | 2026-10-02 | 6301 (D) -> 1111 (K) (Gaji Karyawan)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0413', 'BANK_KELUAR', 'TTC', '2026-10-02', 'October 2026', 309, 528, 2000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R444 | PEMBAYARAN] GAJI 1 KARYAWAN', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(528, '2026-10-02', 'TRANS_BANK', @ref_id, 'BNK26-0413', 'DEBIT', 2000000.00, 2000000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R444 | PEMBAYARAN] GAJI 1 KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-10-02', 'TRANS_BANK', @ref_id, 'BNK26-0413', 'KREDIT', 2000000.00, 0.00, 2000000.00, 0.00, 0.00, '[Excel R444 | PEMBAYARAN] GAJI 1 KARYAWAN', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0414 | Excel R445 | 2026-10-03 | 1111 (D) -> 3100 (K) (Setoran Modal Pemilik)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0414', 'BANK_MASUK', 'TTC', '2026-10-03', 'October 2026', 309, 439, 100000000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R445 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(309, '2026-10-03', 'TRANS_BANK', @ref_id, 'BNK26-0414', 'DEBIT', 100000000.00, 100000000.00, 0.00, 0.00, 0.00, '[Excel R445 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(439, '2026-10-03', 'TRANS_BANK', @ref_id, 'BNK26-0414', 'KREDIT', 100000000.00, 0.00, 100000000.00, 0.00, 0.00, 'Lawan penerimaan [Excel R445 | MODAL] SETORAN MODAL KE PT TEKNOGRAFI', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0415 | Excel R446 | 2026-10-05 | 6214 (D) -> 1112 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0415', 'BANK_KELUAR', 'TTC', '2026-10-05', 'October 2026', 310, 523, 70000.00, NULL, 'Bank - BCA', NULL, NULL, '[Excel R446 | BIAYA ADMIN] ADMIN BDC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-10-05', 'TRANS_BANK', @ref_id, 'BNK26-0415', 'DEBIT', 70000.00, 70000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R446 | BIAYA ADMIN] ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(310, '2026-10-05', 'TRANS_BANK', @ref_id, 'BNK26-0415', 'KREDIT', 70000.00, 0.00, 70000.00, 0.00, 0.00, '[Excel R446 | BIAYA ADMIN] ADMIN BDC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0416 | Excel R447 | 2026-10-07 | 6203 (D) -> 1111 (K) (Listrik & Air Kantor/Gudang)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0416', 'BANK_KELUAR', 'TTC', '2026-10-07', 'October 2026', 309, 512, 100000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R447 | PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(512, '2026-10-07', 'TRANS_BANK', @ref_id, 'BNK26-0416', 'DEBIT', 100000.00, 100000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R447 | PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-10-07', 'TRANS_BANK', @ref_id, 'BNK26-0416', 'KREDIT', 100000.00, 0.00, 100000.00, 0.00, 0.00, '[Excel R447 | PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- #BNK26-0417 | Excel R448 | 2026-10-07 | 6214 (D) -> 1111 (K) (Biaya Administrasi Bank / Pajak Bunga)
INSERT INTO `trans_kas_banks` (`kode_transaksi`, `tipe_transaksi`, `gudang`, `tanggal_transaksi`, `periode`, `account_bank_id`, `account_lawan_id`, `nominal`, `customer_id`, `bank`, `atas_nama`, `no_rekening`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES ('BNK26-0417', 'BANK_KELUAR', 'TTC', '2026-10-07', 'October 2026', 309, 523, 3000.00, NULL, 'Bank - BRI TABUNGAN', NULL, NULL, '[Excel R448 | BIAYA ADMIN] ADMIN BANK PEMBELIAN TOKEN LISTRIK', 'ACTIVE', '2026-11-24 13:45:45', '2026-11-24 13:45:45');
SET @ref_id = LAST_INSERT_ID();
INSERT INTO `coa_movements` (`coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type`, `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(523, '2026-10-07', 'TRANS_BANK', @ref_id, 'BNK26-0417', 'DEBIT', 3000.00, 3000.00, 0.00, 0.00, 0.00, 'Lawan pengeluaran [Excel R448 | BIAYA ADMIN] ADMIN BANK PEMBELIAN TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45'),
(309, '2026-10-07', 'TRANS_BANK', @ref_id, 'BNK26-0417', 'KREDIT', 3000.00, 0.00, 3000.00, 0.00, 0.00, '[Excel R448 | BIAYA ADMIN] ADMIN BANK PEMBELIAN TOKEN LISTRIK', 1, '2026-11-24 13:45:45', '2026-11-24 13:45:45');

-- -----------------------------------------------------------------------------
-- BAGIAN 2: HITUNG ULANG SALDO BERJALAN (balance_before & balance_after)
-- Sinkron kronologis untuk seluruh akun COA terdampak (termasuk data existing)
-- Kompatibel dengan MariaDB 10.2+ & MySQL 8.0+ (Window Function)
-- -----------------------------------------------------------------------------
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
    WHERE m.coa_id IN (314, 422, 355, 311, 439, 523, 492, 504, 373, 329, 312, 518, 542, 395, 415, 349, 470, 443, 467, 309, 452, 528, 535, 367, 529, 514, 378, 508, 525, 516, 521, 512, 539, 310, 477, 538, 361, 532, 524)
) calc ON calc.id = cm.id
SET cm.balance_before = calc.calc_before,
    cm.balance_after  = calc.calc_after;

COMMIT;

-- -----------------------------------------------------------------------------
-- BAGIAN 3: QUERY VERIFIKASI (OPSIONAL — JALANKAN SETELAH COMMIT UNTUK CEK)
-- -----------------------------------------------------------------------------
-- 1) Cek keseimbangan Total Debit vs Total Kredit batch impor:
-- SELECT
--     COUNT(*) AS total_baris_ledger,
--     SUM(debit) AS total_debit,
--     SUM(kredit) AS total_kredit,
--     ROUND(SUM(debit) - SUM(kredit), 2) AS selisih
-- FROM coa_movements
-- WHERE reference_no LIKE 'BNK26-%' OR reference_no LIKE 'KAS26-%';
--
-- 2) Cek rekonsiliasi saldo akhir 4 rekening bank vs footer Excel (R452-R457):
-- SELECT
--     mc.kode_akun, mc.nama_akun,
--     SUM(cm.debit) AS total_masuk_debit,
--     SUM(cm.kredit) AS total_keluar_kredit,
--     ROUND(SUM(cm.debit) - SUM(cm.kredit), 2) AS saldo_akhir_excel
-- FROM coa_movements cm
-- JOIN master_coas mc ON mc.id = cm.coa_id
-- WHERE (cm.reference_no LIKE 'BNK26-%' OR cm.reference_no LIKE 'KAS26-%')
--   AND mc.kode_akun IN ('1111','1112','1113','1114','1116')
-- GROUP BY mc.kode_akun, mc.nama_akun
-- ORDER BY mc.kode_akun;
--
-- 3) Rollback manual batch impor ini jika suatu saat perlu dibatalkan:
-- DELETE FROM coa_movements WHERE reference_no LIKE 'BNK26-%' OR reference_no LIKE 'KAS26-%';
-- DELETE FROM trans_kas_banks WHERE kode_transaksi LIKE 'BNK26-%';
-- DELETE FROM trans_kas WHERE kode_transaksi LIKE 'KAS26-%';
