# AUDIT STRUKTUR DATABASE & SOURCE CODE FINANCE TTC
**Aplikasi Utama:** TTC Laravel Management System (Laravel 10/11 & Filament v3 PHP)  
**Database:** `ttc` (85 Tabel, 995 Kolom, 117 Foreign Key, 241 Indeks, 0 Trigger, 0 Routine)  
**Sifat Audit:** READ-ONLY Forensic Inspection  
**Tanggal:** 2 November 2026

---

## 1. Ikhtisar Arsitektur Database Keuangan TTC

Database `ttc` dirancang dengan standar arsitektur relasional modern (RDBMS MySQL InnoDB) yang menerapkan normalisasi data baku, tipe data presisi tinggi (`bigint`, `decimal(18,2)`), dan penegakan integritas data ketat melalui **117 Foreign Key constraints**.

### Karakteristik Struktural Utama:
1. **Integritas Referensial Ketat (117 FK):** Tidak ada data anak yang dapat eksis tanpa induk yang sah. Relasi antara Invoice, Sales Order, Customer, Surat Jalan, Pembayaran, dan Akun COA dilindungi oleh constraint level database.
2. **Master COA Manufaktur Lengkap (255 Akun):** Bagan akun mencakup seluruh siklus operasional pabrik percetakan (Persediaan Bahan Baku Tinta/Kertas, Barang Dalam Proses, Barang Jadi, Hutang Usaha, Piutang Usaha, Pendapatan Penjualan Etiket/Offset, HPP, hingga Beban Mesin).
3. **Pemisahan Lapisan Mutasi Kas (`coa_movements`):** Pergerakan kas/bank dipisahkan dari tabel master akun dan dicatat ke dalam tabel buku mutasi saldo berjalan.

---

## 2. Bedah Struktur Tabel Master & Buku Besar TTC

### 2.1. Tabel `coa_classes` (`CoaClass.php`)
* **Tujuan Bisnis:** Mengelompokkan nomor akun ke dalam kelompok besar (Aktiva Lancar, Kas & Bank, Aktiva Tetap, Kewajiban, Modal, Pendapatan, HPP, Beban).
* **Jumlah Kolom:** 7 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `karyawan_id` (`bigint(20) unsigned`, FK ke `karyawans.id`, *nullable*)
  * `code` (`varchar(255)`): Kode klasifikasi (misal: `100`, `200`, `300`).
  * `name` (`varchar(255)`): Nama klasifikasi.
  * `status` (`enum('ACTIVE','INACTIVE')`): Status keaktifan.
  * `created_at`, `updated_at` (`timestamp`).

### 2.2. Tabel `master_coas` (`MasterCOA.php`)
* **Tujuan Bisnis:** Master bagan akun resmi perusahaan untuk seluruh transaksi pembukuan.
* **Jumlah Kolom:** 22 kolom.
* **Jumlah Record Terdaftar:** 255 akun aktif.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `coa_class_id` (`bigint(20) unsigned`, FK ke `coa_classes.id`): Relasi klasifikasi.
  * `kode_akun` (`varchar(255)`, Indeks Unik): Nomor akun standar (contoh: `101.001` Kas Utama, `102.001` Bank BCA, `112.001` Piutang Usaha, `211.001` Hutang Dagang, `401.001` Penjualan Label).
  * `nama_akun` (`varchar(255)`): Nama akun buku besar.
  * `saldo_debit` (`bigint(20)`): Saldo awal debit default.
  * `saldo_kredit` (`bigint(20)`): Saldo awal kredit default.
  * `nominal_default` (`bigint(20)`): Nilai patokan saldo awal bersih.
  * `parent_code` (`varchar(255)`): Kode akun induk untuk pembentukan pohon hierarki akun (*account tree*).
  * `level` (`int(11)`): Kedalaman hierarki akun (Level 1 Header, Level 2 Sub-header, Level 3 Akun Transaksi).
  * `account_type` (`varchar(255)`): Jenis akun (Aset, Liabilitas, Ekuitas, Pendapatan, Beban).
  * `account_class` (`varchar(255)`): Pengelompokan detail.
  * `financial_statement` (`varchar(255)`): Penanda laporan keuangan (`NERACA` / `LABA_RUGI`).
  * `cash_flow_type` (`varchar(255)`): Kategori arus kas (`OPERASIONAL`, `INVESTASI`, `PENDANAAN`).
  * `normal_balance` (`enum('DEBIT','KREDIT')`): Posisi saldo normal akun.
  * `posting` (`enum('YES','NO')`): Apakah akun dapat dijurnal langsung atau hanya sebagai header agregasi.
  * `periode`, `gudang`, `keterangan`, `status`, `karyawan_id`, `created_at`, `updated_at`.

### 2.3. Tabel `coa_movements` (`CoaMovement.php`)
* **Tujuan Bisnis:** Mencatat setiap pergerakan mutasi kas dan bank secara kronologis dengan rekam jejak saldo sebelum dan sesudah mutasi.
* **Jumlah Kolom:** 16 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `coa_id` (`bigint(20) unsigned`, FK ke `master_coas.id`): Akun yang mengalami mutasi.
  * `tanggal` (`date`): Tanggal efektif pembukuan.
  * `reference_type` (`varchar(255)`): Asal modul pemicu (`TRANS_KAS`, `TRANS_BANK`, `OPERASIONAL_PAY`, `INV_PAYMENT`, `PO_BILL_PAY`, `PAYROLL`).
  * `reference_id` (`bigint(20) unsigned`): ID primer dokumen sumber.
  * `reference_no` (`varchar(255)`): Nomor bukti transaksi resmi (kode invoice, no voucher, kode slip).
  * `movement_type` (`enum('DEBIT','KREDIT')`): Tipe mutasi penambah/pengurang.
  * `nominal` (`decimal(18,2)`): Nilai transaksi bersih.
  * `debit` (`decimal(18,2)`): Nominal jika mutasi debit, 0 jika kredit.
  * `kredit` (`decimal(18,2)`): Nominal jika mutasi kredit, 0 jika debit.
  * `balance_before` (`decimal(18,2)`): Saldo akun sebelum transaksi dieksekusi.
  * `balance_after` (`decimal(18,2)`): Saldo akun setelah transaksi dieksekusi.
  * `remarks` (`text`): Keterangan / memo transaksi.
  * `created_by` (`bigint(20) unsigned`, FK ke `users.id`): User penanggung jawab.
  * `created_at`, `updated_at` (`timestamp`).

---

## 3. Bedah Struktur Tabel Piutang & Penjualan (AR)

### 3.1. Tabel `invoices` (`Invoice.php`)
* **Tujuan Bisnis:** Faktur penagihan resmi penjualan manufaktur label/cetak kepada customer.
* **Jumlah Kolom:** 27 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `kode_invoice` (`varchar(255)`, Unique): Format penomoran otomatis `000001/INV/mY`.
  * `surat_jalan_id` (`bigint(20) unsigned`, FK ke `surat_jalans.id`): Menjamin invoice dibuat atas pengiriman barang riil.
  * `sales_order_id` (`bigint(20) unsigned`, FK ke `sales_orders.id`): Referensi pesanan penjualan.
  * `customer_id` (`bigint(20) unsigned`, FK ke `customers.id`): Identitas pelanggan tertagih.
  * `no_po_customer` (`varchar(255)`): Nomor Purchase Order dari pihak customer.
  * `tanggal_invoice` (`date`) & `tanggal_jatuh_tempo` (`date`).
  * `subtotal` (`bigint(20)`): Total kotor pesanan sebelum pajak dan diskon.
  * `diskon` (`decimal(5,2)`): Persentase diskon.
  * `potongan` (`double`): Nilai potongan rupiah nominal.
  * `ppn` (`decimal(5,2)`): Nilai persentase PPN (default 11%).
  * `ongkir` (`bigint(20)`): Ongkos pengiriman barang ke lokasi customer.
  * `uang_muka` (`bigint(20)`): Uang muka / DP yang sudah dibayar di awal.
  * `grand_total` (`bigint(20)`): Nilai akhir faktur tagihan: `(Subtotal - Diskon - Potongan) + PPN + Ongkir - Uang Muka`.
  * `total_payment` (`decimal(18,2)`): Akumulasi pembayaran yang telah diterima.
  * `sisa_tagihan` (`bigint(20)`): Sisa kewajiban pelanggan yang belum dibayar.
  * `status` (`enum('Draft','Unpaid','Partial','Paid','Cancelled')`).
  * `is_printed` (`tinyint(1)`): Penanda audit cetak fisik.
  * `terms_conditions`, `bank_accounts`, `catatan`, `terbilang_override`.

### 3.2. Tabel `invoice_items` (`InvoiceItem.php`)
* **Tujuan Bisnis:** Rincian barang yang tertagih di dalam invoice.
* **Jumlah Kolom:** 13 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK), `invoice_id` (`bigint(20) unsigned`, FK ke `invoices.id`).
  * `item`, `jenis`, `komponen`, `kertas`, `finishing`, `qty`, `harga`, `total`, `keterangan`.

### 3.3. Tabel `inv_payments` (`InvPayment.php`)
* **Tujuan Bisnis:** Mencatat transaksi penerimaan pembayaran piutang invoice dari pelanggan.
* **Jumlah Kolom:** 11 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `invoice_id` (`bigint(20) unsigned`, FK ke `invoices.id`).
  * `coa_id` (`bigint(20) unsigned`, FK ke `master_coas.id`): Rekening kas atau bank penerima pembayaran.
  * `metode_bayar_id` (`bigint(20) unsigned`, FK ke `metode_bayars.id`).
  * `karyawan_id` (`bigint(20) unsigned`, FK ke `karyawans.id`): Kasir/staf penerima.
  * `tanggal_bayar` (`date`): Tanggal dana masuk.
  * `nominal_bayar` (`bigint(20)`): Jumlah uang yang dibayarkan.
  * `status` (`varchar(100)`): Status pembayaran (`SUCCESS`, `PENDING`).
  * `keterangan` (`text`).

---

## 4. Bedah Struktur Tabel Pembelian & Tagihan Supplier (AP)

### 4.1. Tabel `po_billings` & `po_billing_items` (`PoBilling.php` & `PoBillingItem.php`)
* **Tujuan Bisnis:** Mengelola tagihan faktur pembelian bahan baku dari supplier/vendor atas dasar Purchase Order dan Penerimaan Barang.
* **Struktur Kolom Fisik Header (`po_billings` - 18 kolom):**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `kode_tagihan` (`varchar(255)`, Unique): Format kode billing tagihan supplier.
  * `penerimaan_barang_id` (`bigint(20) unsigned`, FK): Bukti fisik barang sudah masuk gudang pabrik.
  * `purchase_order_id` (`bigint(20) unsigned`, FK): Referensi PO disetujui.
  * `supplier_id` (`bigint(20) unsigned`, FK ke `master_suppliers.id`).
  * `invoice_vendor` (`varchar(255)`): Nomor faktur asli yang dicetak oleh supplier.
  * `tanggal_transaksi` (`date`) & `tanggal_jatuh_tempo` (`date`).
  * `total_barang`, `ppn`, `ongkir`, `dp`, `grand_total` (`decimal(18,2)`).

### 4.2. Tabel `po_bill_pays` & `po_bill_pay_items` (`PoBillPay.php` & `PoBillPayItem.php`)
* **Tujuan Bisnis:** Mencatat realisasi pembayaran tagihan supplier melalui kas atau rekening bank perusahaan.
* **Struktur Kolom Fisik:**
  * **Header (`po_bill_pays` - 12 kolom):**
    * `id` (`bigint(20) unsigned`, PK), `kode_pembayaran` (`varchar(255)`), `po_billing_id` (`bigint(20) unsigned`, FK), `tanggal_pembayaran` (`date`), `total_tagihan`, `total_pembayaran`.
  * **Detail Items (`po_bill_pay_items` - 13 kolom):**
    * `id` (`bigint(20) unsigned`, PK), `po_bill_pay_id` (`bigint(20) unsigned`, FK).
    * `coa_debit_id` (`bigint(20) unsigned`, FK ke `master_coas.id`): Akun Hutang Usaha yang dilunasi.
    * `coa_kredit_id` (`bigint(20) unsigned`, FK ke `master_coas.id`): Akun Kas/Bank pengeluaran dana.
    * `nominal_pembayaran` (`decimal(18,2)`), `nama_bank`, `no_rekening`, `atas_nama`, `memo`.

### 4.3. Tabel `trans_fakturs` & `trans_faktur_items` (`TransFaktur.php` & `TransFakturItem.php`)
* **Tujuan Bisnis:** Mencatat rekonsiliasi faktur pajak dan faktur komersial atas Purchase Order yang terbit.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK), `purchase_order_id` (`bigint(20) unsigned`, FK), `no_invoice`, `no_faktur`, `tanggal_transaksi`, `subtotal`, `ppn`, `grand_total`.

---

## 5. Bedah Struktur Tabel Kas, Bank, & Operasional (Treasury)

### 5.1. Tabel `trans_kas` (`TransKas.php`)
* **Tujuan Bisnis:** Mencatat transaksi buku kas tunai (Kas Masuk dan Kas Keluar).
* **Jumlah Kolom:** 15 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `kode_transaksi` (`varchar(100)`, Unique): Format autonumber `TRK-Ym-0001`.
  * `tipe_transaksi` (`enum('KAS_MASUK','KAS_KELUAR')`).
  * `tanggal_transaksi` (`date`), `periode` (`varchar(50)`), `gudang`.
  * `account_kas_id` (`bigint(20) unsigned`, FK ke `master_coas.id`): Akun Kas utama.
  * `account_bank_id` (`bigint(20) unsigned`, FK ke `master_coas.id`, *nullable*): Akun Bank lawan transfer.
  * `nominal` (`bigint(20)`), `keterangan` (`text`), `status` (`enum('ACTIVE','INACTIVE')`).

### 5.2. Tabel `trans_kas_banks` (`TransKasBank.php`)
* **Tujuan Bisnis:** Mencatat mutasi perbankan resmi (Bank Masuk dan Bank Keluar).
* **Jumlah Kolom:** 17 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK, Auto Increment)
  * `kode_transaksi` (`varchar(100)`, Unique): Format autonumber `BANK-00001`.
  * `tipe_transaksi` (`enum('BANK_MASUK','BANK_KELUAR')`).
  * `account_bank_id` (`bigint(20) unsigned`, FK ke `master_coas.id`): Akun Bank penerima/pengirim.
  * `account_lawan_id` (`bigint(20) unsigned`, FK ke `master_coas.id`): Akun Lawan penyeimbang.
  * `bank` (`varchar(100)`), `atas_nama` (`varchar(100)`), `no_rekening` (`varchar(100)`).
  * `nominal` (`bigint(20)`), `keterangan` (`text`), `status` (`enum('ACTIVE','INACTIVE')`).

### 5.3. Tabel `operasional_pays` (`OperasionalPay.php`)
* **Tujuan Bisnis:** Pembayaran pengeluaran biaya operasional pabrik dan kantor non-PO (listrik, bensin, konsumsi lembur, sparepart kecil).
* **Jumlah Kolom:** 18 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`bigint(20) unsigned`, PK), `kode_transaksi` (`OPR-00001`), `tanggal_transaksi` (`date`).
  * `account_kas_id` (`bigint(20) unsigned`, FK): Akun Kas/Bank sumber dana pembayaran.
  * `account_beban_id` (`bigint(20) unsigned`, FK): Akun Beban Operasional tujuan pembebanan biaya.
  * `nopol`, `odometer`, `mesin`, `jenis`, `kode`, `nominal`, `keterangan`.

---

## 6. Analisis Lapisan Service & Observers Keuangan TTC

| Service / Observer File | Baris Kode | Tanggung Jawab Teknis |
| :--- | :---: | :--- |
| **`InvoiceService.php`** | 275 baris | Menghitung subtotal dari item Surat Jalan dan Sales Order, memproses PPN, potongan harga, DP, sisa tagihan, dan menerbitkan kode faktur `000001/INV/mY`. |
| **`InvoicePaymentService.php`** | 122 baris | Menerima data pembayaran, memvalidasi agar tidak melebihi sisa tagihan, mengunci baris (*lockForUpdate*), memperbarui status invoice, dan meneruskan ke `TreasuryService::postInvPayment`. |
| **`TreasuryService.php`** | 800 baris | Mengelola mutasi saldo kas/bank, autonumber bukti transaksi, sinkronisasi mutasi `coa_movements`, dan rekalkulasi saldo berjalan (*recalculate running balance*). |
| **`PoBillingService.php`** | 165 baris | Mengelola tagihan vendor atas kedatangan barang Purchase Order. |
| **`PoBillPayService.php`** | 148 baris | Menangani proses pelunasan tagihan supplier dan pencatatan akun debit-kredit pembayaran. |
| **`InvoiceObserver.php`** | 42 baris | Menjamin konsistensi nilai finansial invoice pada saat *event* `creating` dan `updating`, serta mencegah penghapusan jika invoice sudah memiliki pembayaran. |

---

## 7. Evaluasi Struktural & Kesimpulan Audit

| Parameter Evaluasi | Temuan Fakta TTC Laravel | Implikasi Rekayasa Sistem |
| :--- | :--- | :--- |
| **Integritas Relasional** | **117 Foreign Key** aktif melindungi integritas data. | Mencegah anomali data yatim piatu dan menjamin kepatuhan audit sistem database tingkat tinggi. |
| **Kesiapan Master Akun** | Master COA **255 akun** sangat siap untuk pembukuan manufaktur skala menengah-besar. | Tidak perlu membuat master akun baru; seluruh akun standar PSAK percetakan sudah tersedia. |
| **Celah Jurnal Teridentifikasi** | Tabel `coa_movements` saat ini baru berfungsi sebagai **buku kas/bank berjalan**, bukan buku besar umum berimbang penuh (*Full Double-Entry General Ledger*). | Penerbitan Invoice dan Payroll belum mencatat sisi lawan nominal di `coa_movements`. Celah ini akan diselesaikan dengan pembentukan modul jurnal otomatis terpadu. |

