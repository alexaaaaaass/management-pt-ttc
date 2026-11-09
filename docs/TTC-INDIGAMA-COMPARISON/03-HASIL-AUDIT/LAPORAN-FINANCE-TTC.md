# LAPORAN FORENSIK FINANCE TTC LARAVEL
**Aplikasi Acuan:** TTC Laravel Management System (Laravel 10/11 & Filament v3)  
**Entitas Bisnis:** PT Teknografi Tri Cawanaska  
**Tanggal Audit:** 2 November 2026  
**Status Analisis:** Tuntas (Source Code, Eloquent Models, Services, Observers, Database, PDF Blade)

---

## 1. Arsitektur Modul Finance TTC

Modul Keuangan pada TTC dibangun di atas fondasi **Laravel 10/11** dan panel admin modern **Filament v3**. Modul ini dirancang dengan standar arsitektur web modern yang memisahkan antara antarmuka (*Filament Resources/Pages*), lapisan bisnis (*Service Layer*), orkestrasi *event* (*Model Observers*), dan penyimpanan data (*Eloquent ORM & MySQL*).

### Komponen Utama:
1. **User Interface & Navigation:** Filament Admin Resource dengan tema visual modern, validasi form reaktif, tabel interaktif dengan filter multi-kondisi, modal view, serta aksi cepat (*Quick Actions*).
2. **Business Services (Namespace `App\Services\Finance` & `App\Services\HR`):**
   * `InvoiceService`: Menangani kalkulasi harga subtotal dari Surat Jalan/Sales Order, PPN, potongan, uang muka, dan status invoice.
   * `InvoicePaymentService`: Menangani penerimaan pembayaran piutang invoice dari pelanggan, validasi batas sisa tagihan, dan pembaruan status lunas/sebagian.
   * `TreasuryService`: Sentral manajemen kas, bank, operasional, mutasi buku kas/bank (`coa_movements`), serta kalkulasi saldo berjalan (*running balance*).
   * `PayrollService`: Menangani kalkulasi slip gaji karyawan, lembur, bonus, dan potongan tunjangan.
3. **Identitas & Output Dokumen:** Templat PDF berbasis DomPDF dengan *branding*, kop surat, logo resmi, dan identitas hukum **PT Teknografi Tri Cawanaska**.

---

## 2. Struktur Database & Model Keuangan TTC

Database `ttc` menerapkan standar RDBMS profesional dengan tipe tabel InnoDB, penamaan kolom deskriptif berbahasa Indonesia dan Inggris, serta 117 relasi *Foreign Key* yang menjaga integritas data secara ketat.

### 2.1. Bagan Akun & Mutasi Kas/Bank
* **`coa_classes` (`CoaClass.php`):**
  * Kolom: `id`, `karyawan_id`, `code`, `name`, `status`, `created_at`, `updated_at`.
  * Mengelompokkan jenis akun (Aset Lancar, Kas/Bank, Hutang, Modal, Pendapatan, Beban).
* **`master_coas` (`MasterCOA.php`):**
  * Kolom: `id`, `karyawan_id`, `coa_class_id`, `periode`, `gudang`, `kode_akun`, `nama_akun`, `saldo_debit`, `saldo_kredit`, `nominal_default`, `keterangan`, `status`, `parent_code`, `level`, `account_type`, `account_class`, `financial_statement`, `cash_flow_type`, `posting`, `normal_balance`.
  * Memiliki **255 akun standar industri percetakan label & offset**.
  * Dilengkapi kolom struktur modern: hierarki (`level`, `parent_code`), klasifikasi laporan keuangan (`financial_statement`), tipe arus kas (`cash_flow_type`), dan saldo normal (`normal_balance`).
* **`coa_movements` (`CoaMovement.php`):**
  * Kolom: `id`, `coa_id`, `tanggal`, `reference_type`, `reference_id`, `reference_no`, `movement_type` (DEBIT/KREDIT), `nominal`, `debit`, `kredit`, `balance_before`, `balance_after`, `remarks`, `created_by`, `created_at`, `updated_at`.
  * Berfungsi sebagai buku mutasi saldo kas/bank berjalan (*running ledger*).

### 2.2. Piutang & Faktur Penjualan (Accounts Receivable - AR)
* **`invoices` (`Invoice.php`):**
  * Kolom: `id`, `kode_invoice`, `order_id`, `surat_jalan_id`, `customer_id`, `sales_order_id`, `no_po_customer`, `tanggal_invoice`, `tanggal_jatuh_tempo`, `dibuat_oleh`, `kepada`, `subtotal`, `diskon`, `potongan`, `ppn`, `ongkir`, `uang_muka`, `grand_total`, `total_payment`, `sisa_tagihan`, `status`, `is_printed`, `catatan`, `terms_conditions`, `bank_accounts`, `terbilang_override`.
  * Status: `DRAFT`, `UNPAID`, `PARTIAL`, `PAID`, `CANCELLED`.
  * Terhubung dengan Surat Jalan pengiriman barang nyata dari gudang.
* **`invoice_items` (`InvoiceItem.php`):**
  * Kolom: `id`, `invoice_id`, `surat_jalan_item_id`, `nama_item`, `spesifikasi`, `qty`, `satuan`, `harga_satuan`, `subtotal`, `keterangan`.
* **`inv_payments` (`InvPayment.php`):**
  * Kolom: `id`, `invoice_id`, `coa_id` (Akun Kas/Bank penerima), `kode_pembayaran`, `tanggal_bayar`, `nominal_bayar`, `metode_bayar`, `referensi_bukti`, `keterangan`, `status`, `created_by`.

### 2.3. Hutang & Pembelian Bahan (Accounts Payable - AP)
* **`po_billings` & `po_billing_items`:**
  * Tagihan faktur dari vendor/supplier atas penerimaan barang Purchase Order (PO).
* **`po_bill_pays` & `po_bill_pay_items` (`PoBillPay.php` & `PoBillPayItem.php`):**
  * Realisasi pembayaran tagihan supplier melalui kas/bank dengan referensi akun debet (Hutang) dan kredit (Kas/Bank).

### 2.4. Kas, Bank, & Operasional (Treasury)
* **`trans_kas` (`TransKas.php`):**
  * Kolom: `id`, `kode_transaksi`, `tanggal_transaksi`, `tipe_transaksi` (KAS_MASUK / KAS_KELUAR), `nominal`, `account_kas_id`, `account_bank_id`, `kategori`, `keterangan`, `status`, `created_by`.
* **`trans_kas_banks` (`TransKasBank.php`):**
  * Kolom: `id`, `kode_transaksi`, `tanggal_transaksi`, `tipe_transaksi` (BANK_MASUK / BANK_KELUAR), `nominal`, `account_bank_id`, `account_lawan_id`, `no_rekening`, `nama_bank`, `atas_nama`, `keterangan`, `status`, `created_by`.
* **`operasional_pays` (`OperasionalPay.php`):**
  * Kolom: `id`, `kode_transaksi`, `tanggal_transaksi`, `nominal`, `account_kas_id`, `account_beban_id`, `departemen_id`, `keterangan`, `status`, `created_by`.

### 2.5. Penggajian (Payroll)
* **`gajis` (`Gaji.php`):**
  * Kolom: `id`, `karyawan_id`, `account_kas_id`, `kode_slip`, `periode`, `gaji_pokok`, `tunjangan_jabatan`, `tunjangan_kompetensi`, `tunjangan_intensif`, `total_lembur`, `total_bonus`, `potongan_tunjangan`, `potongan_pinjaman`, `gaji_bersih`, `tanggal_pembayaran`, `status` (`Draft`, `Paid`), `metode_pembayaran`.

---

## 3. Bedah Forensik Proses Posting, Perhitungan Saldo, & Pembatalan

Analisis mendalam pada `app/Services/Finance/TreasuryService.php` mengungkap mekanisme kerja pembukuan TTC saat ini:

### 3.1. Mekanisme Mutasi Kas & Saldo Berjalan (`CoaMovement`)
TTC mencatat pergerakan dana di tabel `coa_movements` dengan prinsip saldo berjalan (*running balance*):
1. **Pengecekan Saldo Terkini:**
   * Diambil dari baris terakhir `CoaMovement` berdasarkan akun `coa_id`.
   * Jika belum ada mutasi, saldo awal diambil dari `master_coas.saldo_debit - master_coas.saldo_kredit`.
2. **Kalkulasi Saldo Baru:**
   * Jika mutasi `DEBIT`: `balance_after = current_balance + nominal`.
   * Jika mutasi `KREDIT`: `balance_after = current_balance - nominal`.
3. **Penyimpanan Baris Mutasi:**
   * Menyimpan `balance_before` dan `balance_after` untuk menjaga jejak audit runut waktu (*chronological audit trail*).
4. **Hitung Ulang Saldo Berjalan (`recalculateRunningBalances`):**
   * Mengurutkan seluruh mutasi berdasarkan `tanggal ASC, id ASC` dan memperbarui saldo berantai dari awal hingga akhir.

### 3.2. Proses Posting Per Transaksi
* **Transaksi Kas (`syncKasMovements`):**
  * `KAS_MASUK`: Menghasilkan mutasi DEBIT pada `account_kas_id`. Jika `account_bank_id` diisi, menghasilkan mutasi KREDIT pada `account_bank_id`.
  * `KAS_KELUAR`: Menghasilkan mutasi KREDIT pada `account_kas_id`. Jika `account_bank_id` diisi, menghasilkan mutasi DEBIT pada `account_bank_id`.
  * *Temuan:* Jika `account_bank_id` kosong (kas murni tanpa transfer antar kas), transaksi ini hanya mencatat 1 baris mutasi tunggal (*single entry*).
* **Transaksi Bank (`syncBankMovements`):**
  * `BANK_MASUK`: DEBIT pada `account_bank_id`, KREDIT pada `account_lawan_id`.
  * `BANK_KELUAR`: KREDIT pada `account_bank_id`, DEBIT pada `account_lawan_id`.
  * Menerapkan pencatatan berpasangan lengkap jika `account_lawan_id` ditentukan.
* **Operasional Pay (`syncOperasionalMovements`):**
  * KREDIT pada `account_kas_id` (Kas berkurang).
  * DEBIT pada `account_beban_id` (Beban operasional bertambah).
* **Pembayaran Invoice Customer (`postInvPayment`):**
  * DEBIT pada `inv_payments.coa_id` (Kas/Bank bertambah).
  * *Temuan Kritis:* **Tidak mencatat KREDIT pada Akun Piutang Usaha (112.001)** di `coa_movements`.
* **Pembayaran Tagihan Supplier (`postPoBillPay`):**
  * KREDIT pada `item->coa_kredit_id` (Kas/Bank berkurang).
  * DEBIT pada `item->coa_debit_id` (Hutang Dagang berkurang) jika dipilih manual.
* **Penggajian (`postPayroll`):**
  * KREDIT pada `account_kas_id` (Kas pembayaran gaji berkurang).
  * *Temuan:* Tidak mencatat DEBIT pada Beban Gaji & Upah (501.001/601.001).

### 3.3. Mekanisme Pembatalan / Hapus Transaksi (`deleteMovementsFor`)
* Menghapus seluruh rekaman `CoaMovement` yang memiliki `reference_type` dan `reference_id` bersangkutan.
* Memanggil fungsi `recalculateRunningBalances($coaId)` untuk setiap akun yang terdampak guna memulihkan integritas saldo berjalan secara otomatis.

---

## 4. Evaluasi Format Cetak, Invoice PDF, & Identitas Perusahaan

Pemeriksaan pada `app/Http/Controllers/InvoicePdfController.php` dan `resources/views/pdf/invoice.blade.php`:

### 4.1. Parameter Desain & Identitas Perusahaan
* **Identitas Korporasi:**
  * Nama Resmi: **PT Teknografi Tri Cawanaska**
  * Logo: `public/images/Logo PT Teknografi.png` (di-encode Base64 untuk rendering DomPDF cepat tanpa dependensi URL eksternal).
  * Desain Kop Surat: Header simetris dengan alamat pabrik/kantor, kontak resmi, dan nomor faktur formal.
* **Layout Dokumen:**
  * Format Kertas: **A5 Landscape** (default) / A4 Landscape dengan garis ganda (*double-line separation*).
  * Warna Aksen: Deep Blue (`#31558f`) pada header tabel item dan latar selang-seling baris (`#dce3f3`).
* **Fitur Finansial Lengkap pada Invoice:**
  * Subtotal, Diskon Item, Potongan Khusus, PPN (11%), Biaya Kirim (Ongkir), dan Uang Muka (DP).
  * Nilai Sisa Tagihan (*Net Due Amount*).
  * Kotak Pilihan Rekening Pembayaran Resmi Perusahaan (*Bank Accounts Box*).
  * Syarat & Ketentuan Pembayaran (*Terms & Conditions*).
  * Terbilang Otomatis Bahasa Indonesia dengan kemampuan *override* manual jika dibutuhkan.
  * Tanda tangan otorisasi dan penerima (*Authorized Signatures*).

---

## 5. Analisis Fitur Laporan Keuangan TTC Saat Ini

* **`ReportFinaceResource.php` (Trial Balance):**
  * Menggunakan antarmuka tabel Filament interaktif dengan model dasar `MasterCOA`.
  * Dilengkapi filter rentang tanggal (`tanggal_mulai` dan `tanggal_akhir`).
  * Menghitung Saldo Awal, Total Mutasi Debet, Total Mutasi Kredit, dan Saldo Akhir akun.
* **Keterbatasan Pelaporan TTC Saat Ini:**
  * Belum memiliki halaman tersendiri untuk **Laporan Laba Rugi (Income Statement)** formal.
  * Belum memiliki halaman tersendiri untuk **Laporan Neraca (Balance Sheet)** formal.
  * Belum memiliki **Laporan Laba Rugi per Mesin Cetak** seperti pada Indigama.

---

## 6. Kesimpulan Forensik Modul Finance TTC

1. **Keunggulan Mutlak TTC:**
   * Antarmuka web Filament v3 modern, responsif, dan mudah digunakan di desktop maupun tablet.
   * Master COA manufaktur percetakan sangat lengkap (255 akun terstruktur).
   * Alur invoicing terintegrasi langsung dengan Surat Jalan dan Sales Order riil.
   * Format cetak Invoice PDF elegan, rapi, dan memancarkan identitas profesional PT Teknografi Tri Cawanaska.
   * Integritas data kuat di level database (117 Foreign Key).
2. **Kekurangan yang Perlu Disempurnakan (Accounting Gap):**
   * Model pencatatan masih didominasi **Single-Entry Kas/Bank Mutasi**. Penerbitan Invoice belum menjurnal piutang/penjualan, dan pembayaran invoice belum mengkredit piutang di buku besar.
   * Laporan keuangan komprehensif (Laba Rugi, Neraca, Laba Rugi Mesin) belum tersedia.

