# LAPORAN VALIDASI SUMBER: TTC VS INDIGAMA
**Tahap 2: Audit Forensik Finance & Accounting**  
**Tanggal Pelaksanaan:** 2 November 2026  
**Workspace:** `C:\xampp\htdocs\TTC-INDIGAMA-COMPARISON`  
**Status Sifat Audit:** READ-ONLY (Tanpa modifikasi source code, migrasi, mutasi data, atau pengubahan database)

---

## 1. Ringkasan Eksekutif Validasi Sumber

Validasi sumber dilakukan sebelum audit komparasi guna memastikan seluruh kode sumber (*source code*), skema database, data transaksi, dan file pendukung berada dalam kondisi lengkap, utuh (*untruncated*), dapat dibaca (*readable*), serta terisolasi secara aman.

Pemeriksaan meliputi dua entitas utama:
1. **TTC Laravel System:** Aplikasi web berbasis Laravel 10/11 & Filament v3 PHP dengan database MySQL `ttc`.
2. **Indigama Desktop System:** Aplikasi desktop berbasis Java Swing / NetBeans dengan database MySQL `db_printing` serta template JasperReports.

---

## 2. Kelengkapan dan Integritas Source Code

### 2.1. Source Code TTC (Laravel & Filament)
* **Lokasi Folder:** `01-TTC-LARAVEL/source-code/`
* **Status Kelengkapan:** **LENGKAP DAN UTUH**
* **Struktur Komponen Finance TTC:**
  * **Filament Resources:** 13 Resource terdeteksi terkait keuangan, yaitu `MasterCOAResource`, `CoaClassResource`, `CoaLedgerResource`, `ReportFinaceResource` (Trial Balance), `InvoiceResource`, `InvPaymentResource`, `PoBillPayResource`, `PaymentEntryGoodResource`, `TransKasResource`, `TransKasBankResource`, `GajiResource`, `MaterialLedgerResource`.
  * **Filament Pages:** 31 file halaman form, edit, view, dan list.
  * **Eloquent Models:** 12 Model keuangan inti (`MasterCOA`, `CoaClass`, `CoaMovement`, `Invoice`, `InvoiceItem`, `InvPayment`, `PoBilling`, `PoBillingItem`, `PoBillPay`, `PoBillPayItem`, `TransKas`, `TransKasBank`, `OperasionalPay`, `Gaji`).
  * **Business Services:** 4 Service inti di namespace `App\Services\Finance` dan `App\Services\HR`:
    * `InvoiceService.php` (275 baris)
    * `InvoicePaymentService.php` (122 baris)
    * `TreasuryService.php` (800 baris)
    * `PayrollService.php` (290 baris)
  * **Observers:** `InvoiceObserver.php` (sinkronisasi nilai kalkulasi subtotal, PPN, dan saldo invoice).
  * **Controllers & Views:** `InvoicePdfController.php`, view cetak DomPDF `pdf.invoice` (A4/A5 landscape bersertifikasi identitas PT Teknografi Tri Cawanaska).

### 2.2. Source Code Indigama (Java Desktop)
* **Lokasi Folder:** `02-PROGRAM-INDIGAMA/source-code/`
* **Status Keterbacaan:** **100% DAPAT DIBACA (READABLE)**
* **Hasil Pengujian Encoding & Parsing:**
  * Seluruh file `.java` diuji menggunakan parser berbasis UTF-8 dan Latin-1 fallback. Seluruh file berhasil diproses tanpa error *corrupted file* atau *I/O block*.
* **Rincian Paket Terkait Keuangan (Total 46 File Java Relevan):**
  * **Paket `Accounting` (9 file):**
    * `FormOperasional.java` (74,5 KB)
    * `TranBankIn.java` (73,3 KB)
    * `TranBankOut.java` (82,0 KB)
    * `TranKasIn.java` (66,7 KB)
    * `TranKasOut.java` (75,8 KB)
    * `TranMemorial.java` (72,8 KB)
    * `TranOpIn.java` (23,6 KB)
    * `TranOpOut.java` (79,3 KB)
    * `TranPinjaman.java` (57,1 KB)
  * **Paket `Kasir` (6 file):**
    * `CashierBayar.java` (50,8 KB), `CashierFull.java` (91,1 KB), `PembayaranBon.java` (39,6 KB), `PenjualanUtility.java` (62,1 KB), `ReturnPenjualan.java` (32,5 KB), `FormPenawaran.java` (86,8 KB).
  * **Paket `Master` (Master Finance & Operasional - 21 file):**
    * `MAccount.java` (Chart of Accounts / COA - 37,9 KB), `MCompany.java`, `MCostProduksi.java`, `MDept.java`, `MKaryawan.java`, `MOperasional.java`, `MPayment.java`, `MPayrollDefault.java`, `MSupplier.java`, dll.
  * **Paket `ReportAccounting` & `Report` (10 file):**
    * `RAccounting.java` (28,6 KB), `RAnalisaAccounting.java` (29,9 KB), `RAnalisaTransaksi.java` (100,4 KB - pusat kalkulasi Neraca, Laba Rugi, General Ledger), `RMarketingFee.java`, `RPayroll.java`, `RPenjualan.java`.

### 2.3. Template Laporan Keuangan Indigama (JasperReports)
* **Lokasi Folder:** `02-PROGRAM-INDIGAMA/reports/`
* **Total File Laporan:** 168 file (.jrxml dan .jasper).
* **File Terkait Finance/Accounting:** Ditemukan **32 template**, di antaranya:
  * `LabaRugi.jrxml` & `LabaRugi.jasper`
  * `LabaRugiMesin.jrxml` & `LabaRugiMesinD.jrxml` (Laba Rugi per Mesin Cetak)
  * `RNeraca.jrxml` & `RNeraca.jasper` (Neraca Keuangan)
  * `RGeneralLedger.jrxml` & `RGeneralLedger.jasper` (Buku Besar)
  * `RSummaryAccount.jrxml` (Ringkasan Saldo COA)
  * `RHutangBiaya.jrxml` & `RRekapHutang.jrxml` (Kartu & Rekapitulasi Hutang Supplier)
  * `TranKas.jrxml`, `TranKasOut.jrxml`, `TranBankIn.jrxml`, `TranBankOut.jrxml` (Bukti Voucher Kas/Bank)
  * `RPayrollKaryawan.jrxml` (Slip Gaji)

---

## 3. Validasi Integritas File Database SQL

Pengujian dilakukan untuk membuktikan bahwa file database dump tidak terputus di tengah jalan (*truncated*), memiliki penutup sintaks SQL yang valid, dan tidak mengalami korupsi.

| Nama File | Path | Ukuran (Bytes) | Baris Akhir Dump (*Tail Signature*) | Status Integritas |
| :--- | :--- | :--- | :--- | :--- |
| **`ttc.sql`** | `01-TTC-LARAVEL/database/ttc.sql` | 252.318 | `-- Dump completed on 2026-11-02 12:16:35` | **VALID / UTUH** |
| **`db_printing_mysql.sql`** | `02-PROGRAM-INDIGAMA/database/db_printing_mysql.sql` | 144.135 | `-- Dump completed on 2026-10-31 17:15:27` | **VALID / UTUH** |
| **`dummy_master.sql`** | `02-PROGRAM-INDIGAMA/database/dummy_master.sql` | 26.978 | `ON DUPLICATE KEY UPDATE ...;` | **VALID / UTUH** |
| **`db_printing.sql`** | `02-PROGRAM-INDIGAMA/database/db_printing.sql` | 130.606 | `COMMIT; /*!40101 ... */;` | **VALID / UTUH** |
| **`db_printing_mssql.sql`** | `02-PROGRAM-INDIGAMA/database/db_printing_mssql.sql` | 71.075 | `SET IDENTITY_INSERT ... OFF; GO` | **VALID / UTUH** |

> [!NOTE]
> Semua file SQL terbukti memiliki *footer* penutup yang sempurna sesuai format standar MySQL dump dan T-SQL MSSQL. Tidak ditemukan indikasi file corrupt atau transfer yang terhenti.

---

## 4. Statistik Perbandingan Struktur Database

Pemeriksaan forensik struktur skema dilakukan melalui kueri metadata `information_schema` pada MySQL lokal:

| Parameter Metrik | Database TTC (`ttc`) | Database Indigama (`db_printing`) | Catatan Analisis Arsitektur |
| :--- | :---: | :---: | :--- |
| **Total Tabel** | **85** | **103** | Indigama memiliki tabel detail lebih terfragmentasi untuk tiap sub-proses produksi dan kasir. |
| **Total Kolom** | **995** | **1.494** | Rata-rata kolom per tabel Indigama lebih banyak karena penamaan *legacy flat column*. |
| **Relasi Foreign Key (FK)** | **117** | **0** | **Temuan Kritis:** TTC menerapkan referensial integritas ketat (InnoDB FK constraint). Indigama mengandalkan relasi di level logika aplikasi Java tanpa *foreign key constraint* database. |
| **Jumlah Index** | **241** | **104** | Index TTC mencakup PK dan relasi FK, sedangkan Indigama dominan pada Primary Key dan beberapa index kode. |
| **Trigger Database** | **0** | **0** | Kedua sistem tidak menggunakan trigger database (seluruh logika ada di level aplikasi/service). |
| **Stored Routines** | **0** | **1** (`getdate`) | Logika akuntansi Indigama pada versi MySQL dialihkan ke view dinamis dan kueri Java, sedangkan pada MSSQL sebelumnya menggunakan stored procedure `LabaRugi`. |

---

## 5. Jumlah Record Tabel Finance & Accounting

Pemeriksaan jumlah baris data fisik (`COUNT(*)`) pada tabel-tabel keuangan di kedua sistem:

### 5.1. Tabel Keuangan TTC (`ttc`)
| Nama Tabel | Deskripsi Fungsi | Jumlah Baris (Record) |
| :--- | :--- | :---: |
| `master_coas` | Master Bagan Akun (Chart of Accounts) | **255** |
| `coa_classes` | Klasifikasi/Kategori Akun COA | **1** |
| `coa_movements` | Ledger Mutasi Saldo Kas/Bank Berjalan | **2** |
| `invoices` | Faktur Tagihan Penjualan (AR) | **3** |
| `invoice_items` | Rincian Item Penjualan per Invoice | **0** |
| `inv_payments` | Riwayat Pembayaran Tagihan Invoice | **2** |
| `po_billings` | Tagihan Pembelian Vendor/Supplier (AP) | **2** |
| `po_billing_items` | Rincian Tagihan Pembelian Vendor | **3** |
| `po_bill_pays` | Header Pembayaran Tagihan Supplier | **1** |
| `po_bill_pay_items` | Alokasi Pembayaran Tagihan Supplier | **1** |
| `trans_kas` | Transaksi Penerimaan/Pengeluaran Kas | **6** |
| `trans_kas_banks` | Transaksi Penerimaan/Pengeluaran Bank | **2** |
| `trans_fakturs` | Faktur Pajak / Dokumen Faktur Pendukung | **2** |
| `trans_faktur_items` | Rincian Faktur Pendukung | **3** |
| `operasional_pays` | Pembayaran Beban Operasional Non-PO | **2** |
| `pemb_pinjamans` | Pembayaran/Cicilan Pinjaman Karyawan | **0** |
| `peng_pinjamans` | Pengajuan Pinjaman Karyawan | **0** |
| `gajis` | Rekapitulasi Slip Gaji Karyawan (Payroll) | **0** |
| `bonus_karyawans` | Data Bonus Insentif Karyawan | **0** |
| `pot_tunjangans` | Data Potongan Tunjangan Karyawan | **0** |
| `lemburs` | Data Transaksi Lembur Karyawan | **1** |

### 5.2. Tabel Keuangan Indigama (`db_printing`)
| Nama Tabel | Deskripsi Fungsi | Jumlah Baris (Record) |
| :--- | :--- | :---: |
| `maccountclass` | Master Klasifikasi Akun (Aktiva, Pasiva, Modal, dll) | **6** |
| `maccount` | Master Chart of Accounts (COA) | **22** |
| `mcost` | Master Alokasi Biaya Mesin / Biaya Cetak | **4** |
| `moperasional` | Master Kategori Pengeluaran Operasional | **6** |
| `mpayment` | Master Syarat & Termin Pembayaran | **6** |
| `mmetodepembayaran` | Master Pilihan Rekening / Metode Pembayaran | **5** |
| `mpayroll` | Master Aturan & Tarif Kompensasi Penggajian | **2** |
| `tranmemorial` | Jurnal Umum / Jurnal Memorial | **0** |
| `trankas` | Transaksi Buku Kas Masuk/Keluar | **0** |
| `trankasbank` | Transaksi Buku Bank Masuk/Keluar | **0** |
| `trankaspinjaman` | Transaksi Kas Pinjaman/Kasbon Karyawan | **0** |
| `tranopout` | Transaksi Kas Operasional Keluar | **0** |
| `formoperasional` | Pengajuan Biaya Operasional (Header) | **0** |
| `formoperasionald` | Pengajuan Biaya Operasional (Detail) | **0** |
| `transale` | Transaksi Penjualan / Kasir (Detail) | **0** |
| `transaleheader` | Transaksi Penjualan / Kasir (Header) | **0** |
| `transalebon` | Transaksi Penjualan Kredit / Piutang Bon | **0** |
| `transalebonpay` | Riwayat Pembayaran Cicilan Piutang Bon | **0** |
| `tranpayment` | Header Pembayaran Hutang Supplier | **0** |
| `tranpayment_detail` | Rincian Pembayaran Hutang Supplier | **0** |
| `tranpayroll` | Header Penggajian Periode (Payroll) | **0** |
| `tranpayrolld` | Rincian Slip Gaji Karyawan | **0** |

---

## 6. Verifikasi Kepatuhan Keamanan Kredensial

* **File `.env`:** File `.env` pada proyek TTC Laravel (`01-TTC-LARAVEL/source-code/.env`) diperiksa keberadaannya tanpa menampilkan string sensitif (seperti `DB_PASSWORD`, `APP_KEY`, atau `MAIL_PASSWORD`) ke dalam laporan atau terminal.
* **Isolasi Proyek Asli:** Proyek asli di direktori aslinya sama sekali tidak dibuka atau disentuh. Operasi pembacaan dibatasi secara ketat hanya pada ruang kerja `C:\xampp\htdocs\TTC-INDIGAMA-COMPARISON`.

---

## 7. Catatan Kendala & Temuan Awal Validasi

1. **TTC Memiliki Master COA Lengkap (255 akun) tetapi Transaksi Baru Dimulai:** TTC telah mengimpor bagan akun standar PSAK manufaktur percetakan secara ekstensif, dengan 2 mutasi saldo berjalan awal.
2. **Indigama Berisi Master COA Sederhana (22 akun) & Database Transaksi Kosong di Dump:** Tabel transaksi akuntansi di file dump `db_printing_mysql.sql` berstatus kosong (0 baris), namun seluruh logika bisnis, rumus pergerakan uang, struktur kueri jurnal, dan relasi transaksi tersimpan secara eksplisit dan utuh di dalam kode sumber Java (`TranKasIn`, `TranKasOut`, `TranBankIn`, `TranMemorial`, `Login.java` alter view `AccAll`, dan `RAnalisaTransaksi.java`). Hal ini memberikan referensi desain murni (*blueprint*) alur akuntansi tanpa noise data kotor.
3. **Ketiadaan Foreign Key di Indigama:** Ketiadaan FK di Indigama menegaskan bahwa aturan validasi dan integritas data dibebankan sepenuhnya pada kode Java Swing. Sebaliknya, TTC memiliki 117 Foreign Key yang menjamin integritas relasional di level RDBMS InnoDB.

