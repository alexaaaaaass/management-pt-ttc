# AUDIT ALUR PROSES FINANCE TTC
**Aplikasi Utama:** TTC Laravel Management System (Laravel 10/11 & Filament v3 PHP)  
**Entitas Bisnis:** PT Teknografi Tri Cawanaska  
**Sifat Audit:** READ-ONLY Forensic Inspection  
**Tanggal:** 2 November 2026

---

## 1. Alur Transaksi Faktur Penjualan (Invoicing) & Penerimaan Pembayaran (AR)

### 1.1. Penerbitan Faktur Penjualan (`InvoiceResource` & `InvoiceService`)
* **Fungsi Bisnis:** Menagih pembayaran kepada pelanggan atas produk etiket/label cetak yang telah diproduksi dan dikirimkan oleh gudang ekspedisi.
* **Alur Input & Validasi:**
  1. Pengguna membuka form pembuatan invoice baru di Filament Admin (`CreateInvoice.php`).
  2. Memilih nomor Surat Jalan (`surat_jalan_id`) yang berstatus telah dikirim.
  3. Sistem reaktif Filament secara otomatis memanggil `InvoiceService::calculateSubtotal()`:
     * Menarik data Sales Order terkait (`salesOrder`).
     * Jika mode `SPK`: `Subtotal = qty_pengiriman * harga_pcs`.
     * Jika mode `SINGLE_MATERIAL` / `MULTI_MATERIAL`: `Subtotal = SUM(qty_kirim * harga_satuan_item)`.
  4. Form otomatis mengisikan Customer, Nomor PO Customer, Tanggal Faktur, dan Tanggal Jatuh Tempo sesuai termin customer.
  5. Pengguna dapat menginput diskon persentase, potongan nominal, ongkos kirim, dan nilai uang muka (DP).
  6. Sistem menghitung Nilai PPN (11%), Grand Total, dan menetapkan `sisa_tagihan = grand_total`.
  7. Nomor invoice diterbitkan otomatis melalui `generateKodeInvoice()`: `000001/INV/1126`.
* **Alur Penyimpanan (Simpan Baru):**
  * `InvoiceObserver::creating()` dieksekusi sebelum simpan untuk memverifikasi akurasi matematis seluruh komponen biaya.
  * Disimpan ke tabel `invoices` dengan status awal `Unpaid`.
* **Alur Edit & Pembatalan:**
  * Pengguna dapat mengubah rincian diskon atau catatan termin selama invoice belum memiliki transaksi pembayaran.
  * *Proteksi Integritas:* `InvoiceObserver::deleting()` secara tegas melarang penghapusan invoice jika telah memiliki rekaman pembayaran di `inv_payments` (`throw new Exception`).
* **Kondisi Jurnal Saat Ini:**
  * **Temuan Kritis:** Pada saat Invoice disimpan berstatus `Unpaid`, **belum ada jurnal akuntansi** yang dicatat ke tabel `coa_movements`. Akun Piutang Usaha (112.001) dan Pendapatan Penjualan (401.001) belum bergerak di buku besar.
* **Format Dokumen Output:**
  * Pratinjau dan unduh langsung via `InvoicePdfController`: Menggunakan template DomPDF `resources/views/pdf/invoice.blade.php` (A5 Landscape) dengan kop resmi, logo PT Teknografi Tri Cawanaska, daftar rekening pembayaran, dan nominal terbilang otomatis.

---

### 1.2. Penerimaan Pembayaran Invoice (`InvPaymentResource` & `InvoicePaymentService`)
* **Fungsi Bisnis:** Mencatat pelunasan tagihan piutang dari pelanggan (tunai kasir atau transfer bank).
* **Alur Input & Validasi:**
  1. Pengguna memilih nomor invoice yang akan dibayar.
  2. Sistem menampilkan sisa tagihan terkini secara reaktif.
  3. Memilih rekening penampung kas/bank (`coa_id`), metode pembayaran, dan menginput nominal bayar.
  4. Validasi batas pembayaran: `InvoicePaymentService` mengunci baris invoice (`lockForUpdate`) dan menolak transaksi jika nominal bayar melebihi `sisa_tagihan`.
* **Alur Penyimpanan & Pembaruan Status Invoice:**
  * Disimpan ke tabel `inv_payments`.
  * `InvoiceService::refreshInvoicePayment()` secara otomatis memperbarui `invoices.total_payment` dan `invoices.sisa_tagihan`.
  * Status invoice diperbarui secara otomatis:
    * Jika `sisa_tagihan == 0`: Berubah menjadi **`Paid`** (Lunas).
    * Jika `sisa_tagihan > 0`: Berubah menjadi **`Partial`** (Sebagian).
* **Alur Posting Kas/Bank ke Treasury:**
  * `TreasuryService::postInvPayment()` dieksekusi secara otomatis:
    * Mencatat satu baris mutasi `DEBIT` pada akun kas/bank terpilih (`coa_id`) di tabel `coa_movements` sebesar nominal pembayaran.
  * *Temuan Celah Akuntansi:* Mutasi lawan **`KREDIT` pada akun Piutang Usaha (112.001) belum dicatat** di `coa_movements`.

---

## 2. Alur Transaksi Kas, Bank, & Operasional (Treasury)

### 2.1. Transaksi Kas Tunai (`TransKasResource` & `TreasuryService`)
* **Fungsi Bisnis:** Mengelola penerimaan dan pengeluaran fisik uang tunai di pabrik/kantor.
* **Alur Input & Penyimpanan:**
  1. Pengguna memilih tipe transaksi (`KAS_MASUK` / `KAS_KELUAR`).
  2. Mengisi akun kas (`account_kas_id`), tanggal, nominal, dan keterangan.
  3. Nomor transaksi terbit otomatis: `TRK-2611-0001`.
  4. `TreasuryService::recordKas()` membungkus operasi dalam transaksi database atomik (`DB::transaction`).
* **Alur Pembentukan Mutasi (`syncKasMovements`):**
  * Hapus mutasi lama dengan tipe `TRANS_KAS` pada ID ini.
  * Jika `KAS_MASUK`:
    * DEBIT pada `account_kas_id` di `coa_movements`.
    * Jika `account_bank_id` diisi dan berbeda akun: KREDIT pada `account_bank_id`.
  * Jika `KAS_KELUAR`:
    * KREDIT pada `account_kas_id` di `coa_movements`.
    * Jika `account_bank_id` diisi dan berbeda akun: DEBIT pada `account_bank_id`.
  * *Catatan:* Jika transaksi adalah pengeluaran beban tunai tanpa rekening bank, sistem baru mencatat satu sisi KREDIT kas tanpa mencatat DEBIT akun beban lawan.

---

### 2.2. Transaksi Bank (`TransKasBankResource` & `TreasuryService`)
* **Fungsi Bisnis:** Mengelola transaksi mutasi rekening bank resmi perusahaan (BCA, Mandiri, dll).
* **Alur Input & Penyimpanan:**
  1. Memilih tipe (`BANK_MASUK` / `BANK_KELUAR`).
  2. Memilih Akun Bank (`account_bank_id`) dan Akun Lawan (`account_lawan_id`).
  3. Nomor transaksi terbit otomatis: `BANK-00001`.
* **Alur Pembentukan Jurnal Berpasangan (`syncBankMovements`):**
  * **Alur pada TransKasBank sudah beroperasi Double-Entry seimbang!**
    * `BANK_MASUK`: **DEBIT** pada `account_bank_id` dan **KREDIT** pada `account_lawan_id`.
    * `BANK_KELUAR`: **KREDIT** pada `account_bank_id` dan **DEBIT** pada `account_lawan_id`.
  * Setiap baris mutasi langsung memperbarui saldo berjalan masing-masing akun di `coa_movements`.

---

### 2.3. Biaya Operasional Non-PO (`OperasionalPayResource` & `TreasuryService`)
* **Fungsi Bisnis:** Pembayaran biaya langsung non-pembelian barang dagangan (listrik, BBM armada, perbaikan kecil).
* **Alur Input & Penyimpanan:**
  1. Memilih Akun Kas/Bank sumber pembayaran (`account_kas_id`).
  2. Memilih Akun Beban Operasional tujuan pembebanan (`account_beban_id`).
  3. Nomor bukti terbit otomatis: `OPR-00001`.
* **Alur Pembentukan Jurnal Berpasangan (`syncOperasionalMovements`):**
  * **Sudah beroperasi Double-Entry seimbang:**
    * **KREDIT** pada `account_kas_id` (Kas/Bank berkurang).
    * **DEBIT** pada `account_beban_id` (Beban Operasional bertambah).

---

## 3. Alur Pembelian & Pembayaran Hutang Supplier (AP)

### 3.1. Tagihan Faktur Vendor (`PoBillingResource` & `PoBillingService`)
* **Fungsi Bisnis:** Mengakui tagihan faktur dari vendor supplier bahan baku setelah barang diterima di pabrik.
* **Alur Eksekusi:**
  1. Memilih nomor Penerimaan Barang (`penerimaan_barang_id`) yang sah.
  2. Menarik rincian item, kuantiti, dan harga dari Purchase Order.
  3. Menginput nomor faktur vendor (`invoice_vendor`), tanggal tagihan, dan tanggal jatuh tempo.
  4. Kode tagihan terbit otomatis: `BILL-00001`.
  5. Disimpan ke `po_billings` dan `po_billing_items`.

### 3.2. Realisasi Pembayaran Tagihan Supplier (`PoBillPayResource` & `PoBillPayService`)
* **Fungsi Bisnis:** Membayar tagihan hutang supplier melalui transfer bank atau kas.
* **Alur Eksekusi & Jurnal (`TreasuryService::postPoBillPay`):**
  1. Memilih tagihan `po_billing_id`.
  2. Menentukan akun kas/bank pengeluaran (`coa_kredit_id`) dan akun hutang yang dilunasi (`coa_debit_id`).
  3. Sistem mencatat:
     * **KREDIT** pada `coa_kredit_id` di `coa_movements` (Kas/Bank berkurang).
     * **DEBIT** pada `coa_debit_id` di `coa_movements` (Hutang Dagang berkurang).

---

## 4. Cara Saldo COA Dihitung di TTC

TTC menggunakan metode **Pencatatan Berantai Saldo Berjalan (*Running Balance Ledger*)** yang tersimpan pada tabel `coa_movements`:

### 4.1. Rumus Saldo Terkini (`getCurrentBalance`)
```php
public function getCurrentBalance(int $coaId): float
{
    $last = CoaMovement::where('coa_id', $coaId)
        ->orderByDesc('tanggal')
        ->orderByDesc('id')
        ->first();

    if ($last) {
        return (float) $last->balance_after;
    }

    $coa = MasterCOA::find($coaId);
    return $coa ? ((float) $coa->saldo_debit - (float) $coa->saldo_kredit) : 0.0;
}
```

### 4.2. Algoritma Hitung Ulang Saldo Berantai (`recalculateRunningBalances`)
Jika terjadi penghapusan atau koreksi transaksi di masa lalu, TTC melakukan rekalkulasi berantai dari baris awal mutasi akun tersebut:
```php
$runningBalance = (float) $coa->saldo_debit - (float) $coa->saldo_kredit;

$movements = CoaMovement::where('coa_id', $coaId)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

foreach ($movements as $m) {
    $before = $runningBalance;
    if ($m->movement_type === 'DEBIT') {
        $runningBalance += (float) $m->nominal;
    } else {
        $runningBalance -= (float) $m->nominal;
    }

    $m->update([
        'balance_before' => round($before, 2),
        'balance_after'  => round($runningBalance, 2),
    ]);
}
```

---

## 5. Cara Laporan Mengambil Data & Menghasilkan Dokumen

### 5.1. Laporan Neraca Saldo (`ReportFinaceResource` / Trial Balance)
* Berbasis tabel Filament interaktif dengan kueri Eloquent terhadap model `MasterCOA`:
  * Mengambil saldo awal: `saldo_debit - saldo_kredit` dari `master_coas` ditambah pergerakan mutasi sebelum tanggal filter.
  * Menghitung total mutasi debit periode: `SUM(debit)` dari `coa_movements`.
  * Menghitung total mutasi kredit periode: `SUM(kredit)` dari `coa_movements`.
  * Menghitung saldo akhir: `Saldo Awal + Total Debit - Total Kredit`.
* Dilengkapi filter rentang tanggal dan pencarian cepat kode/nama akun.

### 5.2. Format Dokumen Invoice PDF Resmi (DomPDF)
* Dijalankan melalui `InvoicePdfController::preview()` dan `download()`.
* Merender view Blade `resources/views/pdf/invoice.blade.php`:
  * Logo resmi `public/images/Logo PT Teknografi.png` di-encode Base64.
  * Standar kertas: A5 Landscape / A4 Landscape.
  * Menampilkan kop surat formal, nomor surat jalan rujukan, nomor PO customer, rincian tabel barang pesanan, ringkasan subtotal, diskon, PPN 11%, DP, sisa tagihan, rekening resmi pembayaran, dan teks terbilang rupiah.

