# LAPORAN KOMPARASI FORENSIK FINANCE: TTC VS INDIGAMA
**Workspace:** `C:\xampp\htdocs\TTC-INDIGAMA-COMPARISON`  
**Standar Evaluasi:** Indigama sebagai acuan alur & logika akuntansi; TTC sebagai acuan UI, kebutuhan bisnis, struktur aplikasi, dan identitas korporasi.  
**Tanggal:** 2 November 2026

---

## 1. Matriks Ikhtisar Perbandingan Sistem

| Parameter Komparasi | Indigama Desktop System | TTC Laravel Filament System | Posisi Evaluasi |
| :--- | :--- | :--- | :--- |
| **Paradigma Teknologi** | Desktop Java Swing (NetBeans) | Modern Web Application (Laravel 11 + Filament v3) | **TTC Unggul** (fleksibel, aman, multi-device) |
| **Prinsip Pembukuan** | **Double-Entry Otomatis** via View `AccAll` | **Single-Entry Mutasi Ledger** (`coa_movements`) | **Indigama Unggul** (akuntansi baku & seimbang) |
| **Kelengkapan Master COA** | 22 akun sederhana | **255 akun lengkap industri manufaktur cetak** | **TTC Unggul** (siap untuk audit korporat) |
| **Integritas Database** | 0 Foreign Key (validasi kode Java) | **117 Foreign Key (InnoDB RDBMS level)** | **TTC Unggul** (konsistensi data terjamin) |
| **Integrasi Piutang & Invoice** | Kasir langsung / Bon manual | **Terintegrasi langsung ke Surat Jalan & Sales Order** | **TTC Unggul** (alur manufaktur riil) |
| **Pelaporan Keuangan** | **Laba Rugi, Neraca, Laba Rugi per Mesin (Jasper)** | **Trial Balance (Filament Table)** | **Indigama Unggul** pada jenis laporan manajerial |
| **Desain Dokumen Cetak** | iReport jadul (identitas generic) | **DomPDF A5 Landscape elegan (PT Teknografi Tri Cawanaska)**| **TTC Unggul** (wajib dipertahankan 100%) |

---

## 2. Komparasi Mendalam Fitur demi Fitur (10 Dimensi)

---

### FITUR 1: Manajemen Bagan Akun (Chart of Accounts - COA)

1. **Cara Kerja Indigama:**
   Menggunakan tabel `maccount` (22 akun) yang dikelompokkan ke dalam 6 klasifikasi tabel `maccountclass` (`CL_ASET`, `CL_LIAB`, `CL_EQTY`, `CL_REV`, `CL_COGS`, `CL_EXP`). Saldo awal dicatat langsung pada kolom `ndebet`, `ncredit`, dan `cperiod`.
2. **Cara Kerja TTC:**
   Menggunakan tabel `master_coas` (255 akun) dan `coa_classes`. Memiliki hierarki akun (`parent_code`, `level`), klasifikasi laporan keuangan (`financial_statement`), saldo normal (`normal_balance`), dan tipe arus kas (`cash_flow_type`).
3. **Perbedaan Alur:**
   Indigama memperbarui saldo awal per periode dengan menimpa nilai kolom `cperiod`. TTC memiliki master permanen dengan saldo awal default dan pencatatan mutasi historis di `coa_movements`.
4. **Perbedaan Perhitungan:**
   Indigama: Saldo Awal = `ndebet - ncredit`. TTC: Saldo Awal = `saldo_debit - saldo_kredit`.
5. **Perbedaan Struktur Database:**
   TTC jauh lebih komprehensif (22 kolom di `master_coas` vs 9 kolom di `maccount`), mendukung pengelompokan neraca dan arus kas.
6. **Perbedaan Jurnal dan Saldo:**
   Indigama menyatukan saldo awal ke dalam kueri `AccAll` baris ke-0 (`urut = 0`). TTC membaca saldo awal langsung saat kueri Trial Balance dijalankan.
7. **Perbedaan Laporan:**
   Indigama mencetak daftar akun via JasperReports `RSummaryAccount`. TTC menampilkan daftar akun interaktif via Filament `MasterCOAResource` dan `CoaLedgerResource`.
8. **Fitur TTC yang Harus Dipertahankan:**
   Struktur 255 akun TTC, relasi hierarki (`parent_code`), klasifikasi arus kas, serta antarmuka import COA via Excel/CSV.
9. **Fitur Indigama yang Layak Diadopsi:**
   Penetapan penanda klasifikasi baku 6 pilar (`ASET`, `LIABILITAS`, `EKUITAS`, `PENDAPATAN`, `HPP`, `BEBAN`) yang tegas untuk mempermudah kalkulasi Laba Rugi dan Neraca otomatis.
10. **Rekomendasi Implementasi:**
    Pertahankan master COA TTC 255 akun; pastikan kolom `account_class` dan `financial_statement` pada seluruh 255 akun terpetakan ke klasifikasi akuntansi baku.

---

### FITUR 2: Transaksi Kas Masuk & Kas Keluar (Petty Cash & Cash Drawer)

1. **Cara Kerja Indigama:**
   Form `TranKasIn` dan `TranKasOut` mencatat transaksi ke tabel `trankas`. Mewajibkan pengisian `cacckas` (Akun Kas) dan `caccll` (Akun Lawan / Beban / Pendapatan / Pelanggan). Mendukung multi-baris detail untuk 1 nomor bukti kas.
2. **Cara Kerja TTC:**
   Resource `TransKasResource` dan service `TreasuryService::recordKas()` mencatat transaksi ke `trans_kas`. Kolom akun terdiri dari `account_kas_id` dan opsional `account_bank_id`.
3. **Perbedaan Alur:**
   Indigama mengharuskan setiap pengeluaran kas menentukan akun lawan beban/pendapatan. TTC saat ini memperlakukan `TransKas` sebagai pencatatan mutasi kas tunggal kecuali jika akun bank diisi (transfer kas ke bank).
4. **Perbedaan Perhitungan:**
   Indigama menghitung dua sisi berimbang (Kas bertambah/berkurang dan Lawan bertambah/berkurang). TTC hanya menambah/mengurangi saldo berjalan pada akun kas terpilih.
5. **Perbedaan Struktur Database:**
   Indigama: `cacckas` dan `caccll` berdampingan dalam satu baris tabel `trankas`. TTC: `account_kas_id` dan `account_bank_id`.
6. **Perbedaan Jurnal dan Saldo:**
   Indigama: Kas Masuk menjurnal (D: Kas, K: Akun Lawan). Kas Keluar menjurnal (D: Akun Lawan, K: Kas). TTC: Kas Masuk hanya mencatat D: Kas di `coa_movements` jika tidak ada akun bank lawan.
7. **Perbedaan Laporan:**
   Indigama mencetak voucher tanda terima/pengeluaran kas via `TranKas.jasper`. TTC belum memiliki cetak voucher kas resmi.
8. **Fitur TTC yang Harus Dipertahankan:**
   Antarmuka Filament yang bersih, autonumber `TRK-Ym-0001`, dan audit trail `created_by`.
9. **Fitur Indigama yang Layak Diadopsi:**
   Kewajiban menentukan Akun Lawan (`account_lawan_id`) dan dukungan multi-akun detail untuk pengeluaran kas operasional.
10. **Rekomendasi Implementasi:**
    Tambahkan kolom `account_lawan_id` (wajib diisi) pada form `TransKasResource`. Pada saat simpan, buat dua baris mutasi berpasangan di `coa_movements` (Debit Kas & Kredit Lawan atau sebaliknya). Tambahkan tombol cetak bukti pengeluaran/penerimaan kas (Voucher Kas TTC).

---

### FITUR 3: Transaksi Bank Masuk & Bank Keluar (Bank Settlement & Transfer)

1. **Cara Kerja Indigama:**
   Form `TranBankIn` dan `TranBankOut` mencatat transaksi ke `trankasbank` dengan penanda kode `ctran = '21'` (Masuk) dan `ctran = '22'` (Keluar). Memasukkan rincian bank tujuan, nomor rekening, dan akun lawan.
2. **Cara Kerja TTC:**
   Resource `TransKasBankResource` dan `TreasuryService::recordBank()` mencatat ke `trans_kas_banks`. Memiliki kolom `account_bank_id` dan `account_lawan_id` lengkap.
3. **Perbedaan Alur:**
   Alur bisnis kedua sistem sudah sangat selaras! Keduanya telah mengenali konsep akun bank dan akun lawan.
4. **Perbedaan Perhitungan:**
   Kedua sistem menghitung mutasi debet/kredit secara akurat.
5. **Perbedaan Struktur Database:**
   TTC memiliki struktur tabel lebih rapi (`trans_kas_banks`) dengan foreign key ke `master_coas`.
6. **Perbedaan Jurnal dan Saldo:**
   TTC di `TreasuryService::syncBankMovements` sudah menerapkan jurnal berpasangan di `coa_movements`:
   * Bank Masuk: D: Bank, K: Akun Lawan.
   * Bank Keluar: K: Bank, D: Akun Lawan.
7. **Perbedaan Laporan:**
   Indigama mencetak voucher bank via `TranBankIn.jasper`. TTC belum menyediakan cetak voucher bukti transfer/setoran bank.
8. **Fitur TTC yang Harus Dipertahankan:**
   Struktur `TransKasBankResource` dan logika `syncBankMovements` yang sudah rapi dan seimbang.
9. **Fitur Indigama yang Layak Diadopsi:**
   Tombol cetak Voucher Bukti Penerimaan/Pengeluaran Bank (*Bank Receipt / Payment Voucher*).
10. **Rekomendasi Implementasi:**
    Pertahankan logika TTC yang sudah sempurna; buatkan template DomPDF voucher bank bersurat resmi PT Teknografi Tri Cawanaska.

---

### FITUR 4: Jurnal Umum / Jurnal Memorial (Adjustment & Closing)

1. **Cara Kerja Indigama:**
   Form `TranMemorial.java` mencatat jurnal umum non-kas ke tabel `tranmemorial` (penyusutan aset, penyesuaian persediaan, jurnal akrual, pemindahan saldo).
2. **Cara Kerja TTC:**
   TTC belum memiliki form khusus untuk Jurnal Memorial/Umum non-kas. Penyesuaian saat ini harus melalui form kas/bank buatan.
3. **Perbedaan Alur:**
   Indigama memisahkan tegas antara transaksi kas/bank dan jurnal memorial. TTC belum memiliki jalur khusus jurnal non-kas.
4. **Perbedaan Perhitungan:**
   Indigama: Nilai debit dan kredit dapat dialokasikan per baris akun.
5. **Perbedaan Struktur Database:**
   Indigama memiliki tabel `tranmemorial`. TTC belum memiliki tabel `journal_entries` / `trans_memorials`.
6. **Perbedaan Jurnal dan Saldo:**
   Di Indigama, `tranmemorial` di-union langsung ke `AccAll`. Di TTC, belum ada mekanisme jurnal memorial ke `coa_movements`.
7. **Perbedaan Laporan:**
   Indigama dapat mencetak bukti jurnal memorial. TTC belum memilikinya.
8. **Fitur TTC yang Harus Dipertahankan:**
   Filament architecture standar dan permission role.
9. **Fitur Indigama yang Layak Diadopsi:**
   Fasilitas Jurnal Memorial untuk kebutuhan koreksi akun, depresiasi aktiva tetap, dan tutup buku bulanan.
10. **Rekomendasi Implementasi:**
    Buat Resource baru `JurnalMemorialResource` di Filament TTC yang memungkinkan akuntan menginput baris Debit dan Kredit berimbang secara manual untuk transaksi non-kas.

---

### FITUR 5: Faktur Penjualan & Piutang Usaha (Invoicing & Accounts Receivable)

1. **Cara Kerja Indigama:**
   Modul `Kasir` mencatat transaksi penjualan ke `transale` dan penjualan tempo/bon ke `transalebon`. Pembayaran cicilan dicatat di `transalebonpay`.
2. **Cara Kerja TTC:**
   Modul `InvoiceResource` bekerja terintegrasi dengan alur manufaktur sesungguhnya:
   `Sales Order -> SPK -> Surat Jalan -> Invoice -> InvPayment`.
   Nilai subtotal, diskon, PPN 11%, ongkir, uang muka, dan sisa tagihan dihitung otomatis.
3. **Perbedaan Alur:**
   Indigama berbasis ritel/kasir percetakan cepat. TTC berbasis manufaktur industri (*b2b industrial label printing*) dengan referensi Surat Jalan dan PO Customer resmi.
4. **Perbedaan Perhitungan:**
   TTC jauh lebih superior dalam perhitungan pajak (PPN 11%), potongan harga, uang muka, dan rekonsiliasi sisa tagihan parsial.
5. **Perbedaan Struktur Database:**
   TTC memiliki tabel `invoices`, `invoice_items`, `inv_payments` dengan relasi lengkap ke `customers` dan `surat_jalans`.
6. **Perbedaan Jurnal dan Saldo:**
   * **Celah Akuntansi TTC:** Saat Invoice diterbitkan, TTC belum membuat jurnal pengakuan piutang (D: Piutang Usaha, K: Pendapatan Penjualan, K: Hutang PPN). Saat pembayaran invoice diterima (`postInvPayment`), TTC baru mendebit Kas/Bank tetapi **lupa mengkredit Piutang Usaha**.
7. **Perbedaan Laporan:**
   TTC memiliki Invoice PDF A5/A4 Landscape yang sangat profesional dan elegan.
8. **Fitur TTC yang Harus Dipertahankan:**
   Seluruh arsitektur Invoice TTC, relasi Surat Jalan, kalkulasi PPN/DP, dan template cetak PDF PT Teknografi Tri Cawanaska.
9. **Fitur Indigama yang Layak Diadopsi:**
   Logika akuntansi pengakuan piutang dan pelunasan piutang di buku besar.
10. **Rekomendasi Implementasi:**
    Lengkapi posting jurnal pada alur Invoice TTC:
    * **Saat Invoice Final/Terbit:** Jurnal otomatis (Debit: Piutang Usaha [112.001], Kredit: Pendapatan Penjualan [401.001], Kredit: PPN Keluaran [214.001]).
    * **Saat Pembayaran Diterima (`InvPayment`):** Jurnal otomatis (Debit: Kas/Bank [101.xxx], Kredit: Piutang Usaha [112.001]).

---

### FITUR 6: Pembelian Bahan & Pembayaran Hutang Supplier (Accounts Payable - AP)

1. **Cara Kerja Indigama:**
   Mencatat tagihan PO di `tranpurchaseorderbilling` dan pelunasan pembayaran hutang supplier di `tranpayment` / `tranpayment_detail`.
2. **Cara Kerja TTC:**
   Alur pembelian terstruktur:
   `Purchase Request -> Purchase Order -> Penerimaan Barang -> PO Billing -> PO Bill Pay`.
   `TreasuryService::postPoBillPay()` memotong saldo kas/bank (`KREDIT`) dan mendebit akun hutang (`DEBIT`).
3. **Perbedaan Alur:**
   Alur procurement TTC jauh lebih tertib dan sesuai tata kelola korporat dibanding Indigama.
4. **Perbedaan Perhitungan:**
   Keduanya menghitung saldo hutang dan alokasi pembayaran per item tagihan.
5. **Perbedaan Struktur Database:**
   TTC memiliki `po_billings` dan `po_bill_pays` dengan foreign key ke `master_suppliers` dan `master_coas`.
6. **Perbedaan Jurnal dan Saldo:**
   Pada TTC, jika `item->coa_debit_id` diisi, sistem sudah membuat jurnal berimbang (Kredit Kas/Bank dan Debit Hutang Supplier).
7. **Perbedaan Laporan:**
   Indigama memiliki laporan kartu hutang supplier (`RHutangBiaya.jrxml`). TTC memiliki tabel pemantauan status billing di Filament.
8. **Fitur TTC yang Harus Dipertahankan:**
   Seluruh rantai proses procurement TTC dari PR hingga PO Bill Pay.
9. **Fitur Indigama yang Layak Diadopsi:**
   Laporan Kartu Hutang per Supplier (menampilkan riwayat saldo awal hutang, penambahan hutang dari PO, dan riwayat pembayaran).
10. **Rekomendasi Implementasi:**
    Pastikan `coa_debit_id` (Akun Hutang Usaha - 211.001) terisi secara *default* otomatis saat pembuatan pembayaran tagihan supplier sehingga jurnal selalu berpasangan sempurna. Buatkan view laporan Kartu Hutang Vendor di Filament.

---

### FITUR 7: Beban Operasional Non-PO (Operational Expenses)

1. **Cara Kerja Indigama:**
   Menggunakan `FormOperasional` dan `TranOpOut` dengan mapping kategori master operasional (`moperasional`) ke nomor akun beban.
2. **Cara Kerja TTC:**
   Menggunakan `OperasionalPay` dan `TreasuryService::recordOperasional()` yang langsung menghubungkan `account_kas_id` (sumber dana) dengan `account_beban_id` (alokasi biaya).
3. **Perbedaan Alur:**
   TTC lebih cepat dan ringkas; Indigama memiliki alur pengajuan form terlebih dahulu baru pencairan kas.
4. **Perbedaan Perhitungan:**
   Sama-sama menghitung pengeluaran kas terhadap akun beban.
5. **Perbedaan Struktur Database:**
   TTC mengaitkan operasional langsung dengan `departemen_id` dan `account_beban_id`.
6. **Perbedaan Jurnal dan Saldo:**
   TTC di `syncOperasionalMovements` sudah menerapkan jurnal berpasangan (Kredit: Kas, Debit: Beban Operasional).
7. **Perbedaan Laporan:**
   Indigama mencetak bukti form operasional. TTC menampilkan list tabel Filament.
8. **Fitur TTC yang Harus Dipertahankan:**
   Model `OperasionalPay` TTC dan relasi ke departemen.
9. **Fitur Indigama yang Layak Diadopsi:**
   Cetak Bukti Pengeluaran Kas Operasional (Voucher Kas Keluar Operasional).
10. **Rekomendasi Implementasi:**
    Pertahankan logika TTC; tambahkan template cetak bukti pengeluaran kas operasional dengan kop surat resmi TTC.

---

### FITUR 8: Pinjaman & Kasbon Karyawan (Employee Loans & Advances)

1. **Cara Kerja Indigama:**
   Form `TranPinjaman.java` mencatat pengeluaran kasbon karyawan ke tabel `trankaspinjaman` dan memotong cicilan saat proses payroll bulanan via `tranpayroll`.
2. **Cara Kerja TTC:**
   TTC memiliki tabel `peng_pinjamans` (pengajuan) dan `pemb_pinjamans` (pembayaran/cicilan) serta terhubung dengan `PayrollService` melalui pemotongan pinjaman di tabel `gajis.potongan_pinjaman`.
3. **Perbedaan Alur:**
   Keduanya memiliki konsep yang sama: pencairan kasbon di awal, lalu cicilan dipotong otomatis pada slip gaji bulanan.
4. **Perbedaan Perhitungan:**
   Perhitungan saldo sisa pinjaman: `Total Pinjaman - Total Cicilan Terbayar`.
5. **Perbedaan Struktur Database:**
   TTC memiliki pemisahan tabel yang jelas antara pengajuan pinjaman dan histori cicilan.
6. **Perbedaan Jurnal dan Saldo:**
   * Di Indigama, pencairan pinjaman menjurnal: Debit Piutang Karyawan / Kasbon, Kredit Kas. Saat cicilan dipotong gaji: Kredit Piutang Karyawan.
   * Di TTC, integrasi jurnal kasbon ke `coa_movements` belum aktif secara otomatis.
7. **Perbedaan Laporan:**
   Indigama memiliki cetak bukti pinjaman `FormPinjaman.jasper`.
8. **Fitur TTC yang Harus Dipertahankan:**
   Tabel `peng_pinjamans` dan `pemb_pinjamans` di database TTC.
9. **Fitur Indigama yang Layak Diadopsi:**
   Posting jurnal otomatis saat kasbon dicairkan (D: Piutang Karyawan [114.001], K: Kas [101.001]) dan saat gaji dipotong (K: Piutang Karyawan [114.001]).
10. **Rekomendasi Implementasi:**
    Hubungkan aksi pencairan pinjaman dan pemotongan gaji di `PayrollService` ke `TreasuryService::postMovement` secara otomatis.

---

### FITUR 9: Penggajian & Integrasi Akuntansi (Payroll Integration)

1. **Cara Kerja Indigama:**
   Modul `TranPayroll` menghitung gaji pokok, lembur, bonus, dan potongan, lalu menjurnal pengeluaran kas total di `AccAll` (Kredit: Kas, Debit: Beban Gaji).
2. **Cara Kerja TTC:**
   `GajiResource` dan `PayrollService` menghitung gaji pokok, tunjangan jabatan, tunjangan kompetensi, tunjangan intensif, kalkulasi lembur standar Depnaker `(1/173 * Gaji Pokok)`, bonus, dan potongan pinjaman/tunjangan.
3. **Perbedaan Alur:**
   Rumus kalkulasi lembur dan tunjangan TTC jauh lebih modern, akurat, dan sesuai regulasi ketenagakerjaan Indonesia.
4. **Perbedaan Perhitungan:**
   TTC memiliki komponen penggajian yang lebih rinci dan tertata rapi.
5. **Perbedaan Struktur Database:**
   TTC menggunakan tabel `gajis`, `karyawans`, `lemburs`, `bonus_karyawans`, `pot_tunjangans`.
6. **Perbedaan Jurnal dan Saldo:**
   Saat gaji dibayar (`status = 'Paid'`), TTC di `TreasuryService::postPayroll` mencatat Kredit pada Kas pembayaran, namun belum mendebit Akun Beban Gaji & Upah (501.001/601.001).
7. **Perbedaan Laporan:**
   TTC memiliki komponen modal pratinjau slip gaji `slip-gaji-modal.blade.php`.
8. **Fitur TTC yang Harus Dipertahankan:**
   Seluruh formula perhitungan lembur, bonus, dan tunjangan pada `PayrollService` TTC.
9. **Fitur Indigama yang Layak Diadopsi:**
   Pencatatan jurnal beban gaji lengkap (Debit: Beban Gaji Karyawan, Kredit: Kas/Bank).
10. **Rekomendasi Implementasi:**
    Tambahkan satu baris mutasi `DEBIT` ke akun Beban Gaji (501.001 untuk produksi / 601.001 untuk non-produksi) di dalam fungsi `TreasuryService::postPayroll`.

---

### FITUR 10: Buku Besar & Neraca Saldo (General Ledger & Trial Balance)

1. **Cara Kerja Indigama:**
   Mengambil data mutasi akun dari view `AccAll`. Kueri langsung mengagregasikan debet, kredit, dan saldo berjalan akun per periode.
2. **Cara Kerja TTC:**
   Resource `ReportFinaceResource` menampilkan tabel Trial Balance yang menghitung Saldo Awal, Total Debit, Total Kredit, dan Saldo Akhir berdasarkan kueri tabel `coa_movements`.
3. **Perbedaan Alur:**
   Indigama menggabungkan seluruh transaksi melalui view UNION. TTC mengandalkan tabel fisik `coa_movements` yang diisi oleh hooks event.
4. **Perbedaan Perhitungan:**
   Keduanya menghasilkan ringkasan pergerakan saldo akun yang sama.
5. **Perbedaan Struktur Database:**
   Indigama: View virtual `AccAll`. TTC: Tabel fisik `coa_movements` (kinerja kueri lebih cepat karena memiliki index).
6. **Perbedaan Jurnal dan Saldo:**
   Karena TTC saat ini masih beroperasi parsial (beberapa transaksi belum mencatat akun lawan), Neraca Saldo TTC saat ini belum seimbang (*unbalanced trial balance*). Setelah seluruh jurnal berpasangan diterapkan, Neraca Saldo TTC akan seimbang sempurna (Total Debet = Total Kredit).
7. **Perbedaan Laporan:**
   TTC memiliki tampilan tabel Trial Balance Filament yang sangat interaktif dan responsif.
8. **Fitur TTC yang Harus Dipertahankan:**
   Antarmuka `ReportFinaceResource` dan tabel fisik `coa_movements`.
9. **Fitur Indigama yang Layak Diadopsi:**
   Laporan Buku Besar Detail (*General Ledger Detail*) per akun yang menampilkan histori mutasi tanggal per tanggal dengan lawan akun dan nomor bukti.
10. **Rekomendasi Implementasi:**
    Kembangkan `CoaLedgerResource` di Filament TTC menjadi Buku Besar Detail yang menampilkan histori mutasi per akun dengan filter tanggal dan tombol ekspor PDF/Excel.

---

### FITUR 11: Laporan Laba Rugi Perusahaan & Laba Rugi per Mesin Percetakan

1. **Cara Kerja Indigama:**
   * **Laba Rugi Umum:** Menghitung Pendapatan (`CL_REV`) dikurangi HPP (`CL_COGS`) dan Beban (`CL_EXP`) dari data `AccAll`.
   * **Laba Rugi Mesin (`LabaRugiMesin.jrxml`):** Mengelompokkan pendapatan cetak SPK per nomor mesin cetak (`idmesin`) dan mengurangkan pemakaian bahan serta biaya operasional mesin terkait.
2. **Cara Kerja TTC:**
   TTC belum memiliki halaman antarmuka Laporan Laba Rugi di menu Report Filament.
3. **Perbedaan Alur:**
   Indigama sudah memiliki fitur kalkulasi laba rugi bulanan dan profitabilitas mesin. TTC saat ini baru memiliki Trial Balance.
4. **Perbedaan Perhitungan:**
   Rumus Indigama: `Laba Bersih = Pendapatan Penjualan - (HPP Bahan + HPP Tenaga Kerja + Beban Operasional)`.
5. **Perbedaan Struktur Database:**
   TTC sebenarnya sudah memiliki data mesin dan SPK di tabel `printings`, `imr_printing`, dan `finish_good_items`, sehingga data biaya mesin sangat siap untuk diolah.
6. **Perbedaan Jurnal dan Saldo:**
   Laba Rugi mengambil akumulasi mutasi akun nominal (Akun Kepala 4, 5, 6, 8, 9).
7. **Perbedaan Laporan:**
   Indigama menggunakan JasperReports `LabaRugi.jrxml` dan `LabaRugiMesin.jrxml`.
8. **Fitur TTC yang Harus Dipertahankan:**
   Seluruh arsitektur Filament Report grup.
9. **Fitur Indigama yang Layak Diadopsi:**
   Logika Laporan Laba Rugi Perusahaan dan Laporan Laba Rugi per Mesin Cetak.
10. **Rekomendasi Implementasi:**
    Buat dua halaman laporan baru di Filament TTC:
    * `ReportLabaRugiResource`: Laporan Laba Rugi Perusahaan komprehensif.
    * `ReportLabaRugiMesinResource`: Laporan Profitabilitas per Mesin Percetakan (analisis pendapatan SPK mesin vs biaya material/tinta & upah operator).

---

### FITUR 12: Laporan Neraca Keuangan (Balance Sheet)

1. **Cara Kerja Indigama:**
   Mengagregasikan saldo akhir akun riil (`CL_ASET` sebagai Aktiva, `CL_LIAB` dan `CL_EQTY` ditambah Laba Tahun Berjalan sebagai Pasiva) per tanggal cut-off.
2. **Cara Kerja TTC:**
   TTC belum memiliki halaman laporan Neraca Keuangan resmi di panel Filament.
3. **Perbedaan Alur:**
   Indigama menyajikan neraca skontro/stafel via JasperReports `RNeraca.jrxml`. TTC baru menyajikan neraca saldo di Trial Balance.
4. **Perbedaan Perhitungan:**
   Rumus Neraca Baku: `Total Aset = Total Kewajiban + Total Ekuitas + Laba Periode Berjalan`.
5. **Perbedaan Struktur Database:**
   TTC sudah memiliki kolom `financial_statement` dan `account_type` di tabel `master_coas` yang membedakan akun Neraca (*Balance Sheet*) dan akun Laba Rugi (*Income Statement*).
6. **Perbedaan Jurnal dan Saldo:**
   Neraca mengambil saldo kumulatif akun riil dari awal hingga tanggal laporan.
7. **Perbedaan Laporan:**
   Indigama mencetak PDF Neraca via Jasper.
8. **Fitur TTC yang Harus Dipertahankan:**
   Klasifikasi kolom `financial_statement` pada `master_coas`.
9. **Fitur Indigama yang Layak Diadopsi:**
   Struktur pengelompokan Aktiva (Lancar & Tetap) dan Pasiva (Kewajiban Jangka Pendek/Panjang & Modal).
10. **Rekomendasi Implementasi:**
    Buat halaman `ReportNeracaResource` di Filament TTC yang menyajikan posisi aset dan kewajiban/ekuitas perusahaan per tanggal tertentu dengan tombol ekspor PDF resmi PT Teknografi Tri Cawanaska.

