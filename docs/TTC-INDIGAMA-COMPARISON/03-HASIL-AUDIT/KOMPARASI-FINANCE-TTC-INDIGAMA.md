# LAPORAN KOMPARASI SISTEMATIS FINANCE: TTC VS INDIGAMA
**Aplikasi Acuan Akuntansi:** Indigama Desktop Printing System  
**Aplikasi Utama & Sasaran:** TTC Laravel Management System (PT Teknografi Tri Cawanaska)  
**Sifat Audit:** READ-ONLY Forensic Inspection  
**Tanggal:** 2 November 2026

---

## 1. Pendahuluan & Prinsip Perbandingan

Laporan ini menyajikan perbandingan komprehensif antara sistem keuangan TTC Laravel dan Program Desktop Indigama. 

Setiap alur dievaluasi secara baku melalui **9 Parameter Wajib**:
1. Fungsi bisnis.
2. Tabel dan kolom yang digunakan.
3. Alur input, penyimpanan, edit, pembatalan, dan penghapusan.
4. Cara transaksi membentuk jurnal DEBIT/KREDIT.
5. Cara saldo COA dihitung.
6. Cara laporan mengambil data.
7. Format dokumen/PDF yang dihasilkan.
8. Perbedaan TTC dan Indigama.
9. Rekomendasi (Dipertahankan / Diperbaiki / Diadopsi).

---

## 2. Evaluasi Rinci Alur Bisnis Keuangan

---

### ALUR 1: Manajemen Bagan Akun (Chart of Accounts - COA)

1. **Fungsi Bisnis:** Mendefinisikan seluruh rekening buku besar sebagai fondasi pembukuan aktiva, kewajiban, modal, pendapatan, HPP, dan beban perusahaan percetakan.
2. **Tabel & Kolom:**
   * **Indigama:** `maccount` (`id`, `caccnt`, `cdesc`, `fAccount`, `NDebet`, `NCredit`, `cPeriod`, `fstatus`) dan `maccountclass` (`id`, `caccnt_class`, `cname`).
   * **TTC:** `master_coas` (`id`, `kode_akun`, `nama_akun`, `coa_class_id`, `saldo_debit`, `saldo_kredit`, `parent_code`, `level`, `account_type`, `financial_statement`, `cash_flow_type`, `normal_balance`, `posting`) dan `coa_classes` (`id`, `code`, `name`).
3. **Alur Transaksi (Input, Simpan, Edit, Batal, Hapus):**
   * **Indigama:** Form Java `Master/MAccount.java`, input manual kode akun, menyimpan saldo awal langsung menimpa kolom `NDebet` dan `NCredit` periode aktif.
   * **TTC:** Filament `MasterCOAResource.php`, form reaktif dengan validasi keunikan kode akun, mendukung hierarki berjenjang (`parent_code`), serta fasilitas import massal via Excel/CSV.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Indigama:** Nilai `(NDebet - NCredit)` pada `maccount` di-union pada baris ke-0 (`urut = 0`) dalam view `AccAll` sebagai saldo awal pembukuan.
   * **TTC:** Saldo awal tersimpan di `saldo_debit` dan `saldo_kredit` tabel `master_coas`. Belum membentuk jurnal mutasi berpasangan di `coa_movements`.
5. **Kalkulasi Saldo COA:**
   * **Indigama:** `Saldo Bersih = NDebet - NCredit`.
   * **TTC:** `Saldo Bersih = saldo_debit - saldo_kredit`.
6. **Cara Laporan Mengambil Data:**
   * **Indigama:** JasperReports `RSummaryAccount.jrxml`.
   * **TTC:** Filament Table interaktif `MasterCOAResource` dan `CoaLedgerResource`.
7. **Format Dokumen/PDF:**
   * **Indigama:** Cetak iReport teks sederhana.
   * **TTC:** Ekspor tabel Filament (PDF/Excel) modern.
8. **Perbedaan TTC dan Indigama:**
   * TTC memiliki 255 akun terstruktur lengkap dengan relasi Foreign Key ke `coa_classes` dan atribut laporan keuangan (`financial_statement`, `cash_flow_type`). Indigama hanya memiliki 22 akun sederhana tanpa foreign key.
9. **Rekomendasi:**
   * **DIPERTAHANKAN (100% TTC):** Struktur 255 akun TTC, relasi hierarki, dan antarmuka Filament TTC dipertahankan seutuhnya karena jauh lebih unggul dan siap audit korporat.

---

### ALUR 2: Penerimaan Kas Tunai (Kas Masuk)

1. **Fungsi Bisnis:** Mencatat penerimaan uang kas fisik di luar penjualan kasir langsung (misal: setoran modal tunai, penerimaan bunga, pendapatan non-operasional, pengembalian kasbon).
2. **Tabel & Kolom:**
   * **Indigama:** `trankas` (`id`, `ctran`, `cnbkt`, `dtran`, `cacckas`, `caccll`, `namnt`, `cnote`, `cusername`).
   * **TTC:** `trans_kas` (`id`, `kode_transaksi`, `tipe_transaksi`, `tanggal_transaksi`, `account_kas_id`, `account_bank_id`, `nominal`, `keterangan`, `status`).
3. **Alur Transaksi (Input, Simpan, Edit, Batal, Hapus):**
   * **Indigama:** Form `Accounting/TranKasIn.java`. Menyimpan multi-baris detail dengan tombol Save (`INSERT/UPDATE`). Tombol Delete menghapus baris fisik dari `trankas`.
   * **TTC:** Filament `TransKasResource.php` -> `TreasuryService::recordKas()`. Menggunakan database transaction (`DB::transaction`). Hapus transaksi mengeksekusi `deleteMovementsFor('TRANS_KAS', $id)` dan rekalkulasi saldo berjalan.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Indigama (Double-Entry Seimbang):**
     * **DEBIT:** Akun Kas (`cacckas`) sebesar `namnt`.
     * **KREDIT:** Akun Lawan (`caccll`) sebesar `namnt`.
   * **TTC (Single-Entry Parsial):**
     * **DEBIT:** Akun Kas (`account_kas_id`) di `coa_movements`.
     * **KREDIT:** Hanya dicatat jika `account_bank_id` diisi (transfer kas ke bank). Jika tidak, mutasi kredit tidak terbentuk.
5. **Kalkulasi Saldo COA:**
   * **Indigama:** Agregasi kueri `AccAll` secara dinamis.
   * **TTC:** Saldo berjalan dihitung kronologis melalui `balance_after = balance_before + nominal` pada `coa_movements`.
6. **Cara Laporan Mengambil Data:**
   * **Indigama:** Filter kueri `AccAll` per rentang tanggal.
   * **TTC:** Kueri tabel `coa_movements` yang terhubung ke `master_coas`.
7. **Format Dokumen/PDF:**
   * **Indigama:** Bukti Kas Masuk JasperReports `TranKas.jrxml`.
   * **TTC:** Belum memiliki tombol cetak voucher kas resmi.
8. **Perbedaan TTC dan Indigama:**
   * Indigama mewajibkan Akun Lawan (`caccll`) sehingga jurnal selalu berimbang. TTC memperlakukan `trans_kas` sebagai mutasi tunggal jika tanpa akun bank.
9. **Rekomendasi:**
   * **DIPERBAIKI & DIADOPSI:** Pertahankan UI Filament TTC; tambahkan kolom `account_lawan_id` (wajib diisi) pada form `TransKasResource` dan terapkan pencatatan jurnal berpasangan (D: Kas, K: Akun Lawan) di `TreasuryService::syncKasMovements`. Buatkan template PDF Voucher Kas Masuk resmi PT Teknografi Tri Cawanaska.

---

### ALUR 3: Pengeluaran Kas Tunai (Kas Keluar)

1. **Fungsi Bisnis:** Mencatat pengeluaran uang kas fisik untuk beban rutin, pembelian ATK tunai, dan keperluan harian pabrik.
2. **Tabel & Kolom:**
   * **Indigama:** `trankas` (`ctran = '02'`, `cacckas`, `caccll`, `namnt`, `cnote`, `idmesin`, `nik`).
   * **TTC:** `trans_kas` (`tipe_transaksi = 'KAS_KELUAR'`, `account_kas_id`, `account_bank_id`, `nominal`, `keterangan`).
3. **Alur Transaksi:**
   * **Indigama:** Form `Accounting/TranKasOut.java`, validasi saldo kas tidak boleh minus.
   * **TTC:** Filament `TransKasResource.php` -> `TreasuryService::recordKas()`.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Indigama (Double-Entry Seimbang):**
     * **DEBIT:** Akun Lawan / Beban (`caccll`) sebesar `namnt`.
     * **KREDIT:** Akun Kas (`cacckas`) sebesar `namnt`.
   * **TTC (Single-Entry Parsial):**
     * **KREDIT:** Akun Kas (`account_kas_id`) di `coa_movements`. Sisi debit beban belum dicatat jika tanpa akun bank lawan.
5. **Kalkulasi Saldo COA:**
   * **Indigama:** Kueri `AccAll` mengurangi saldo kas.
   * **TTC:** `balance_after = balance_before - nominal` pada `coa_movements`.
6. **Cara Laporan Mengambil Data:**
   * **Indigama:** Kueri `AccAll` per akun beban dan kas.
   * **TTC:** Kueri `coa_movements`.
7. **Format Dokumen/PDF:**
   * **Indigama:** JasperReports `TranKasOut.jrxml` (Bukti Kas Keluar).
   * **TTC:** Belum ada cetak voucher kas keluar.
8. **Perbedaan TTC dan Indigama:**
   * Indigama menjamin beban operasional tercatat di akun beban terkait; TTC baru memotong kas tanpa mendebit akun beban jika diinput dari form kas umum.
9. **Rekomendasi:**
   * **DIPERBAIKI:** Terapkan akun lawan wajib di `TransKasResource` TTC dan terbitkan PDF Voucher Kas Keluar resmi PT Teknografi Tri Cawanaska.

---

### ALUR 4: Transaksi Bank Masuk & Bank Keluar (Bank Settlement)

1. **Fungsi Bisnis:** Mencatat mutasi perbankan resmi perusahaan: setoran modal via bank, penarikan tunai, transfer antar-rekening bank, dan pembayaran via transfer.
2. **Tabel & Kolom:**
   * **Indigama:** `trankasbank` (`ctran = '21'/'22'`, `cacckas`, `caccll`, `bank`, `bank_rekening`, `bank_an`, `namnt`, `cnote`).
   * **TTC:** `trans_kas_banks` (`tipe_transaksi`, `account_bank_id`, `account_lawan_id`, `bank`, `no_rekening`, `atas_nama`, `nominal`, `keterangan`).
3. **Alur Transaksi:**
   * **Indigama:** Form `Accounting/TranBankIn.java` dan `TranBankOut.java`.
   * **TTC:** Filament `TransKasBankResource.php` -> `TreasuryService::recordBank()`.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Kedua Sistem SUDAH SAMA-SAMA DOUBLE-ENTRY SEIMBANG:**
     * **Bank Masuk:** DEBIT pada Akun Bank, KREDIT pada Akun Lawan.
     * **Bank Keluar:** KREDIT pada Akun Bank, DEBIT pada Akun Lawan.
5. **Kalkulasi Saldo COA:**
   * Keduanya menghitung saldo berjalan bank secara akurat.
6. **Cara Laporan Mengambil Data:**
   * **Indigama:** Kueri `AccAll`.
   * **TTC:** Kueri `coa_movements` dan tabel `ReportFinaceResource` (Trial Balance).
7. **Format Dokumen/PDF:**
   * **Indigama:** JasperReports `TranBankIn.jrxml` dan `TranBankOut.jrxml`.
   * **TTC:** Belum memiliki tombol cetak bukti mutasi bank.
8. **Perbedaan TTC dan Indigama:**
   * Logika bisnis TTC di `syncBankMovements` sudah sangat matang dan seimbang; perbedaan hanya terletak pada belum adanya template cetak voucher bank di TTC.
9. **Rekomendasi:**
   * **DIPERTAHANKAN:** Logika `TreasuryService::syncBankMovements` TTC sudah sempurna. Cukup tambahkan aksi cetak Bukti Mutasi Bank (Bank Voucher) PDF berlogo TTC.

---

### ALUR 5: Jurnal Umum / Jurnal Memorial (Adjustment & Non-Cash Journal)

1. **Fungsi Bisnis:** Menampung transaksi akuntansi non-kas murni: penyusutan aktiva tetap pabrik, jurnal penyesuaian akhir tahun, akrual beban gaji/listrik yang belum dibayar, serta koreksi akun.
2. **Tabel & Kolom:**
   * **Indigama:** `tranmemorial` (`id`, `ctran`, `cnbkt`, `dtran`, `cacckas`, `caccll`, `namnt`, `cnote`).
   * **TTC:** **BELUM TERSEDIA** (Belum ada tabel `trans_memorials` atau `journal_entries`).
3. **Alur Transaksi:**
   * **Indigama:** Form Java `Accounting/TranMemorial.java`. Menginput akun debit, akun kredit, memo, dan nominal.
   * **TTC:** Saat ini akuntan terpaksa melakukan trik manual melalui form kas/bank atau belum dapat membukukan jurnal non-kas murni.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Indigama:** DEBIT pada `caccll` dan KREDIT pada `cacckas` di `AccAll`.
   * **TTC:** Belum ada mekanisme pencatatan.
5. **Kalkulasi Saldo COA:**
   * Indigama memperbarui saldo akun debit dan kredit secara berpasangan.
6. **Cara Laporan Mengambil Data:**
   * Indigama langsung menyerapnya ke Neraca dan Laba Rugi via `AccAll`.
7. **Format Dokumen/PDF:**
   * Indigama memiliki cetak bukti memorial.
8. **Perbedaan TTC dan Indigama:**
   * Ketiadaan modul Jurnal Memorial merupakan celah akuntansi paling nyata pada sistem TTC saat ini.
9. **Rekomendasi:**
   * **DIADOPSI & DIBUATKAN BARU DI TTC:** Buat Resource baru di Filament `JurnalMemorialResource` dengan tabel pendukung `journal_entries` dan `journal_entry_lines`. Hubungkan langsung untuk mencatat dua baris mutasi berimbang di `coa_movements`.

---

### ALUR 6: Faktur Penjualan (Invoicing) & Pengakuan Piutang Usaha (AR)

1. **Fungsi Bisnis:** Menerbitkan faktur tagihan resmi atas barang etiket/label yang telah dikirimkan ke pelanggan dan mengakui timbulnya Piutang Usaha (*Accounts Receivable*).
2. **Tabel & Kolom:**
   * **Indigama:** Mengandalkan kasir `transale` dan kartu piutang bon `transalebon`.
   * **TTC:** `invoices` (`id`, `kode_invoice`, `surat_jalan_id`, `sales_order_id`, `customer_id`, `subtotal`, `diskon`, `potongan`, `ppn`, `ongkir`, `uang_muka`, `grand_total`, `sisa_tagihan`, `status`), `invoice_items`.
3. **Alur Transaksi:**
   * **Indigama:** Kasir langsung mencatat penjualan di form Java.
   * **TTC:** Alur B2B Manufaktur riil: Dibuat dari Surat Jalan pengiriman fisik, menghitung PPN 11%, potongan harga, uang muka, divalidasi oleh `InvoiceObserver`, diterbitkan dengan status `Unpaid`.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Indigama:** Penjualan diakui saat kas diterima atau bon dicatat.
   * **TTC (Celah Akuntansi Saat Ini):** Saat Invoice terbit, **belum ada jurnal otomatis** ke `coa_movements`. Seharusnya mencatat:
     * **DEBIT:** Piutang Usaha (112.001) sebesar `grand_total`.
     * **KREDIT:** Pendapatan Penjualan (401.001) sebesar `subtotal - diskon - potongan`.
     * **KREDIT:** Hutang PPN Keluaran (214.001) sebesar nilai PPN.
5. **Kalkulasi Saldo COA:**
   * Di TTC, akun Piutang Usaha (112.001) belum bergerak saat faktur diterbitkan.
6. **Cara Laporan Mengambil Data:**
   * TTC memantau tagihan dari tabel `invoices` via Filament `InvoiceResource`, namun belum terhubung ke Neraca Saldo.
7. **Format Dokumen/PDF:**
   * **TTC SANGAT SUPERIOR:** Memiliki Invoice PDF resmi A5 Landscape dengan logo PT Teknografi Tri Cawanaska, rincian item, nomor PO customer, syarat pembayaran, dan tanda tangan otorisasi.
8. **Perbedaan TTC dan Indigama:**
   * Alur bisnis invoicing TTC jauh lebih profesional dan modern untuk industri percetakan b2b, namun perlu disempurnakan pada sisi penjurnalan piutang otomatis.
9. **Rekomendasi:**
   * **DIPERTAHANKAN & DILENGKAPI JURNALNYA:** Pertahankan 100% alur UI, database, dan Invoice PDF TTC. Tambahkan hook otomatis saat status Invoice menjadi `Unpaid` untuk mendebit Piutang Usaha (112.001) dan mengkredit Pendapatan Penjualan (401.001) serta PPN Keluaran (214.001).

---

### ALUR 7: Penerimaan Pembayaran Tagihan Invoice Customer

1. **Fungsi Bisnis:** Mencatat masuknya uang pembayaran dari pelanggan untuk melunasi tagihan invoice dan mengkredit piutang pelanggan.
2. **Tabel & Kolom:**
   * **Indigama:** `transalebonpay` (`cnbkt`, `dtran`, `cicilan`, `caccnt`) dan `tranmemorial`.
   * **TTC:** `inv_payments` (`invoice_id`, `coa_id`, `tanggal_bayar`, `nominal_bayar`, `status`), `invoices`.
3. **Alur Transaksi:**
   * **Indigama:** Kasir `PembayaranBon.java` -> mencatat cicilan -> memanggil fungsi `autoSaveMemorial()` untuk menjurnal otomatis ke `TranMemorial`.
   * **TTC:** Filament `InvPaymentResource.php` -> `InvoicePaymentService::recordPayment()` -> mengunci row invoice -> mengupdate sisa tagihan -> memanggil `TreasuryService::postInvPayment()`.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Indigama (Double-Entry Otomatis):**
     * DEBIT: Kas/Bank (`103.101`).
     * KREDIT: Piutang Pelanggan (`taccll`).
   * **TTC (Single-Entry Parsial Saat Ini):**
     * DEBIT: Kas/Bank (`inv_payments.coa_id`) di `coa_movements`.
     * *Temuan:* Sisi KREDIT pada Piutang Usaha (112.001) belum dicatat.
5. **Kalkulasi Saldo COA:**
   * Indigama: Piutang berkurang seketika di `AccAll`.
   * TTC: Saldo kas/bank bertambah, namun saldo piutang di buku besar belum berkurang.
6. **Cara Laporan Mengambil Data:**
   * Indigama: Laporan Rekap Bon. TTC: Laporan status pembayaran invoice di Filament.
7. **Format Dokumen/PDF:**
   * Indigama: Struk bon. TTC: Riwayat pembayaran pada lembar invoice.
8. **Perbedaan TTC dan Indigama:**
   * Indigama di `PembayaranBon.java` memiliki otomatisasi penjurnalan piutang (`autoSaveMemorial`) yang sangat rapi. TTC baru mencatat penambahan uang di kasir.
9. **Rekomendasi:**
   * **DIPERBAIKI DENGAN MENGADOPSI LOGIKA INDIGAMA:** Pada `TreasuryService::postInvPayment()`, tambahkan satu baris mutasi otomatis: **KREDIT pada Akun Piutang Usaha (112.001)** sehingga saldo kas/bank bertambah dan saldo piutang usaha berkurang secara berimbang.

---

### ALUR 8: Tagihan Pembelian Vendor (PO Billing) & Pembayaran Hutang (AP)

1. **Fungsi Bisnis:** Mengakui kewajiban hutang atas kedatangan bahan baku percetakan dari supplier, serta mencatat pelunasan hutang saat jatuh tempo.
2. **Tabel & Kolom:**
   * **Indigama:** `tranpurchaseorderbilling` / `tranpurchaseorderbillingd` dan `tranpayment` / `tranpayment_detail`.
   * **TTC:** `po_billings` / `po_billing_items` dan `po_bill_pays` / `po_bill_pay_items`.
3. **Alur Transaksi:**
   * **Indigama:** `Purchase/PurchaseOrderBilling.java` dan `Purchase/PaymentPurchase.java`.
   * **TTC:** `PoBillingResource.php` dan `PoBillPayResource.php` yang dijalankan melalui `PoBillPayService` dan `TreasuryService::postPoBillPay()`.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Saat Pembayaran Tagihan Supplier:**
     * **Indigama:** DEBIT pada Hutang Dagang (`caccll`), KREDIT pada Kas/Bank (`cacckas`).
     * **TTC:** DEBIT pada `item->coa_debit_id` (Hutang Dagang), KREDIT pada `item->coa_kredit_id` (Kas/Bank).
5. **Kalkulasi Saldo COA:**
   * Keduanya sudah mendukung pengurangan saldo kas/bank dan pengurangan saldo hutang.
6. **Cara Laporan Mengambil Data:**
   * Indigama: JasperReports `RHutangBiaya.jrxml`. TTC: Monitoring status tagihan PO di Filament Table.
7. **Format Dokumen/PDF:**
   * Indigama: Kartu Hutang. TTC: Lembar PO Billing.
8. **Perbedaan TTC dan Indigama:**
   * TTC memiliki alur data pengadaan (*procurement*) yang jauh lebih lengkap (PR -> PO -> Penerimaan Barang -> Billing -> Bill Pay). Pada Indigama alurnya lebih sederhana.
9. **Rekomendasi:**
   * **DIPERTAHANKAN & DIPERBAIKI SEDIKIT:** Pertahankan alur PO Billing TTC. Pastikan `coa_debit_id` terisi otomatis dengan akun Hutang Usaha (211.001) agar staf tidak perlu memilih akun hutang secara manual. Buatkan halaman laporan Kartu Hutang Supplier di Filament TTC.

---

### ALUR 9: Beban Operasional Non-PO (OperasionalPay)

1. **Fungsi Bisnis:** Membayar beban rutin operasional pabrik dan kantor (listrik, BBM, konsumsi, pemeliharaan gedung).
2. **Tabel & Kolom:**
   * **Indigama:** `tranopout` (`cnbkt`, `cacckas`, `caccll`, `namnt`, `cjenis`, `ckendaraan`, `codometer`).
   * **TTC:** `operasional_pays` (`kode_transaksi`, `account_kas_id`, `account_beban_id`, `nominal`, `nopol`, `odometer`, `mesin`, `jenis`).
3. **Alur Transaksi:**
   * **Indigama:** Form Java `Accounting/TranOpOut.java`.
   * **TTC:** Filament `OperasionalPayResource.php` -> `TreasuryService::recordOperasional()`.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **KEDUA SISTEM SUDAH DOUBLE-ENTRY SEIMBANG:**
     * **DEBIT:** Akun Beban Operasional (`account_beban_id` / `caccll`).
     * **KREDIT:** Akun Kas/Bank (`account_kas_id` / `cacckas`).
5. **Kalkulasi Saldo COA:**
   * Keduanya memotong kas dan menambah akumulasi beban operasional pada buku besar.
6. **Cara Laporan Mengambil Data:**
   * Indigama: Masuk ke Laba Rugi via `AccAll`. TTC: Masuk ke Trial Balance via `coa_movements`.
7. **Format Dokumen/PDF:**
   * Indigama: Form operasional. TTC: List Filament.
8. **Perbedaan TTC dan Indigama:**
   * Keduanya memiliki kemiripan struktur data yang sangat tinggi (termasuk kolom armada `nopol` dan `odometer`). Logika TTC sudah sangat sempurna.
9. **Rekomendasi:**
   * **DIPERTAHANKAN:** Struktur dan alur `OperasionalPay` TTC sudah sempurna. Cukup tambahkan tombol cetak Bukti Kas Keluar Operasional PDF berlogo TTC.

---

### ALUR 10: Penggajian Karyawan & Integrasi Akuntansi (Payroll)

1. **Fungsi Bisnis:** Menghitung hak kompensasi tenaga kerja pabrik dan staf, memproses potongan kasbon, dan menjurnal beban gaji.
2. **Tabel & Kolom:**
   * **Indigama:** `tranpayroll` dan `tranpayrolld`.
   * **TTC:** `gajis`, `karyawans`, `lemburs`, `bonus_karyawans`, `pot_tunjangans`, `peng_pinjamans`.
3. **Alur Transaksi:**
   * **Indigama:** Form Java `Payroll/`.
   * **TTC:** Filament `GajiResource.php` -> `PayrollService::calculateEmployeePayroll()` menghitung gaji pokok, lembur sesuai rumus Depnaker `(1/173 * Gaji Pokok)`, bonus, dan potongan pinjaman.
4. **Pembentukan Jurnal DEBIT/KREDIT:**
   * **Indigama:** Mencatat Kredit pada Kas dan Debit pada Beban Gaji di `AccAll`.
   * **TTC (Celah Akuntansi Saat Ini):** `TreasuryService::postPayroll()` baru mencatat KREDIT pada akun kas pembayaran gaji. Belum mencatat DEBIT pada Beban Gaji & Upah (501.001 untuk produksi / 601.001 untuk non-produksi).
5. **Kalkulasi Saldo COA:**
   * Indigama: Beban gaji terakumulasi di Laba Rugi. TTC: Kas berkurang, namun beban gaji belum muncul di Neraca Saldo.
6. **Cara Laporan Mengambil Data:**
   * Indigama: JasperReports `RPayrollKaryawan.jrxml`. TTC: Modal pratinjau slip gaji `slip-gaji-modal.blade.php`.
7. **Format Dokumen/PDF:**
   * TTC memiliki format slip gaji modern yang rapi.
8. **Perbedaan TTC dan Indigama:**
   * Formulasi payroll TTC jauh lebih canggih dan sesuai standar hukum ketenagakerjaan Indonesia.
9. **Rekomendasi:**
   * **DIPERTAHANKAN & DILENGKAPI JURNALNYA:** Pertahankan seluruh formula `PayrollService` TTC. Tambahkan pencatatan mutasi DEBIT ke akun Beban Gaji (501.001/601.001) di `TreasuryService::postPayroll`.

---

### ALUR 11: Buku Besar & Neraca Saldo (General Ledger & Trial Balance)

1. **Fungsi Bisnis:** Menyajikan ringkasan pergerakan saldo seluruh akun untuk audit, verifikasi keseimbangan pembukuan, dan dasar penyusunan laporan keuangan.
2. **Tabel & Kolom:**
   * **Indigama:** View `AccAll` dan tabel `maccount`.
   * **TTC:** Tabel fisik `coa_movements` dan `master_coas`.
3. **Alur Transaksi & Penyajian:**
   * **Indigama:** Java mengeksekusi kueri agregat terhadap view `AccAll`.
   * **TTC:** Filament `ReportFinaceResource.php` (Trial Balance) dan `CoaLedgerResource.php` (General Ledger).
4. **Pembentukan Keseimbangan Jurnal:**
   * **Indigama:** Selalu seimbang (`Total Debit == Total Kredit`) karena view `AccAll` secara matematis memproduksi baris simetris.
   * **TTC Saat Ini:** Belum seimbang karena beberapa transaksi (seperti Invoice dan Gaji) baru mencatat mutasi satu sisi (*single-entry*).
5. **Kalkulasi Saldo COA:**
   * Indigama: Dihitung saat kueri dijalankan. TTC: Saldo berjalan sudah tersimpan di kolom `balance_after` tabel `coa_movements`.
6. **Cara Laporan Mengambil Data:**
   * Indigama: Model tabel Java. TTC: Kueri Eloquent Filament dengan summarizer kolom `Sum::make('debit')` dan `Sum::make('kredit')`.
7. **Format Dokumen/PDF:**
   * Indigama: Jasper `RGeneralLedger.jrxml`. TTC: Tabel Filament interaktif.
8. **Perbedaan TTC dan Indigama:**
   * Tabel fisik `coa_movements` TTC jauh lebih cepat dan terindeks dengan baik dibanding view union Indigama yang berat. TTC hanya butuh penyeimbangan jurnal dua sisi.
9. **Rekomendasi:**
   * **DIPERTAHANKAN:** Pertahankan arsitektur tabel fisik `coa_movements` TTC. Setelah seluruh hook jurnal berpasangan diterapkan, Trial Balance TTC akan otomatis seimbang sempurna 100%.

---

### ALUR 12: Laporan Laba Rugi Perusahaan & Laba Rugi per Mesin Percetakan

1. **Fungsi Bisnis:** Mengukur kinerja profitabilitas bisnis perusahaan, serta menganalisis efisiensi dan laba kotor yang dihasilkan oleh setiap unit mesin percetakan.
2. **Tabel & Kolom:**
   * **Indigama:** View `AccAll`, `maccount`, `idmesin`, JasperReports `LabaRugi.jrxml` dan `LabaRugiMesin.jrxml`.
   * **TTC:** Tabel `master_coas` (kolom `financial_statement = 'LABA_RUGI'`), `coa_movements`, dan data mesin pada tabel `printings`. Halaman laporan khusus di Filament: **BELUM TERSEDIA**.
3. **Alur Pengolahan Data:**
   * **Indigama:** Kueri Java mengagregasikan pendapatan penjualan dikurangi HPP dan beban operasional pada rentang tanggal filter.
   * **TTC:** Saat ini belum ada halaman laporan Laba Rugi di menu Report Filament.
4. **Pembentukan Angka Laporan:**
   * `Laba Bersih = Pendapatan Penjualan - (HPP Bahan + HPP Tenaga Kerja + Beban Operasional Pabrik + Beban Umum/Admin)`.
5. **Laba Rugi Mesin Cetak (Fitur Unggulan Indigama):**
   * Mengaitkan pendapatan SPK pesanan cetak dengan mesin yang mengerjakan, lalu dikurangi biaya kertas, tinta, listrik, dan upah operator mesin tersebut.
6. **Format Dokumen/PDF:**
   * Indigama: JasperReports `LabaRugi.jrxml` dan `LabaRugiMesin.jrxml`.
7. **Perbedaan TTC dan Indigama:**
   * Indigama sudah memiliki laporan Laba Rugi Perusahaan dan Laba Rugi Mesin; TTC saat ini baru memiliki Trial Balance.
8. **Rekomendasi:**
   * **DIADOPSI DARI INDIGAMA & DIIMPLEMENTASIKAN DI FILAMENT TTC:**
     * Buat `ReportLabaRugiResource` di Filament TTC untuk menyajikan Laba Rugi Perusahaan multi-step.
     * Buat `ReportLabaRugiMesinResource` di Filament TTC untuk menyajikan profitabilitas mesin cetak (Zonten Flexo, Heidelberg Offset, Die-cutting) bersurat resmi PT Teknografi Tri Cawanaska.

---

### ALUR 13: Laporan Neraca Keuangan (Balance Sheet)

1. **Fungsi Bisnis:** Menyajikan posisi kekayaan (Aset/Aktiva), kewajiban hutang, dan permodalan ekuitas perusahaan pada tanggal tertentu.
2. **Tabel & Kolom:**
   * **Indigama:** View `AccAll`, `maccount` (klasifikasi `CL_ASET`, `CL_LIAB`, `CL_EQTY`), JasperReports `RNeraca.jrxml`.
   * **TTC:** Tabel `master_coas` (kolom `financial_statement = 'NERACA'`), `coa_movements`. Halaman laporan di Filament: **BELUM TERSEDIA**.
3. **Alur Pengolahan Data:**
   * Mengambil saldo kumulatif akun riil dari awal pendirian hingga tanggal *cut-off* laporan.
4. **Persamaan Neraca Baku:**
   * `Total Aset = Total Kewajiban + Total Ekuitas + Laba Periode Berjalan`.
5. **Format Dokumen/PDF:**
   * Indigama: JasperReports `RNeraca.jrxml`.
6. **Perbedaan TTC dan Indigama:**
   * Indigama menyajikan neraca skontro; TTC belum memiliki halaman laporan Neraca formal di panel admin.
7. **Rekomendasi:**
   * **DIADOPSI & DIIMPLEMENTASIKAN DI FILAMENT TTC:** Buat `ReportNeracaResource` di Filament TTC dengan tata letak Stafel/Skontro modern, indikator keseimbangan (*balance check indicator*), dan ekspor PDF resmi PT Teknografi Tri Cawanaska.

