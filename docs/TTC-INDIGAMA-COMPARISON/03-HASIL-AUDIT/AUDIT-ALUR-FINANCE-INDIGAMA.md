# AUDIT ALUR PROSES FINANCE INDIGAMA
**Aplikasi Acuan:** Indigama Desktop Printing Production (Java Swing / MySQL)  
**Paket Sumber:** `Accounting`, `Kasir`, `Master`, `Purchase`, `Report`, `ReportAccounting`  
**Sifat Audit:** READ-ONLY Forensic Inspection  
**Tanggal:** 2 November 2026

---

## 1. Alur Transaksi Kas & Bank (Buku Kas / Bank)

### 1.1. Kas Masuk (`Accounting/TranKasIn.java`) & Bank Masuk (`Accounting/TranBankIn.java`)
* **Fungsi Bisnis:** Mencatat penerimaan uang tunai atau setoran rekening bank di luar penjualan kasir langsung (misalnya pendapatan jasa lain, pendapatan bunga bank, pengembalian kasbon, setoran modal).
* **Alur Input & Validasi:**
  1. Pengguna memilih tanggal transaksi (`jDateChooser`) dan akun kas/bank penampung (`cacckas`).
  2. Sistem menghasilkan nomor bukti otomatis melalui fungsi `LastCode()` (contoh: `GD-KM-26110001` untuk kas masuk, `GD-BM-26110001` untuk bank masuk).
  3. Pengguna menginput satu atau beberapa baris rincian pada tabel UI (`jTable1`): Akun Lawan (`caccll`), Keterangan/Memo, Nominal (`namnt`), dan opsional Kode Customer.
* **Alur Penyimpanan (Simpan Baru):**
  * Java mengeksekusi kueri `INSERT INTO TranKas` (atau `TranKasBank`) per baris tabel dengan kode `ctran = '01'` (Kas Masuk) atau `ctran = '21'` (Bank Masuk).
  * Nilai kolom `id` berstatus `-1` pada baris baru memicu kueri `INSERT`.
* **Alur Edit & Pembaruan:**
  * Pengguna mencari nomor bukti via dialog pencarian. Baris yang sudah ada memiliki nilai `id > 0`.
  * Saat disimpan, baris yang diedit memicu kueri `UPDATE TranKas SET ... WHERE id = '...'`.
* **Alur Pembatalan & Penghapusan:**
  * Eksekusi tombol Delete memicu penghapusan fisik kueri `DELETE FROM TranKas WHERE cnbkt = '...'`.
  * Indigama menggunakan penghapusan permanen (*hard delete*) tanpa kolom `deleted_at`.
* **Pembentukan Jurnal DEBIT / KREDIT:**
  * Dibentuk secara dinamis melalui view `AccAll`:
    * Baris 1: Akun Kas/Bank (`cacckas`) -> **DEBIT** sebesar `namnt` (karena `nilai = namnt`).
    * Baris 2: Akun Lawan (`caccll`) -> **KREDIT** sebesar `namnt` (karena `nilai = namnt * -1`).
* **Format Dokumen Output:**
  * Menggunakan JasperReports `TranKas.jrxml` (Ukuran Kertas Kustom / Struk Continuous Form) berlabel **"BUKTI KAS MASUK"** atau **"BUKTI BANK MASUK"**.

---

### 1.2. Kas Keluar (`Accounting/TranKasOut.java`) & Bank Keluar (`Accounting/TranBankOut.java`)
* **Fungsi Bisnis:** Mencatat pengeluaran uang tunai atau transfer rekening bank untuk pembayaran biaya rutin, retribusi, pembelian perlengkapan tunai, atau transfer dana antar-rekening.
* **Alur Input & Validasi:**
  1. Memilih tanggal, akun kas/bank sumber dana (`cacckas`), dan mengisi akun lawan (`caccll`).
  2. Validasi pencegahan kas minus: Mengecek saldo akun kas/bank berjalan; jika saldo tidak mencukupi, sistem menampilkan dialog peringatan.
  3. Mendukung alokasi ke personal karyawan (`nik`, `nik_cname`) dan unit mesin percetakan (`idmesin`, `kode`).
* **Pembentukan Jurnal DEBIT / KREDIT:**
  * Diproses oleh view `AccAll` dengan kode `ctran = '02'` (Kas Keluar) atau `ctran = '22'` (Bank Keluar):
    * Baris 1: Akun Lawan / Beban (`caccll`) -> **DEBIT** sebesar `namnt` (`nilai = namnt`).
    * Baris 2: Akun Kas/Bank (`cacckas`) -> **KREDIT** sebesar `namnt` (`nilai = namnt * -1`).
* **Format Dokumen Output:**
  * JasperReports `TranKasOut.jrxml` dan `TranBankOut.jrxml` berlabel **"BUKTI KAS KELUAR"** dan **"BUKTI BANK KELUAR"** dengan kolom otorisasi (Dibuat, Diperiksa, Disetujui, Diterima).

---

## 2. Alur Jurnal Umum / Memorial (`Accounting/TranMemorial.java`)

* **Fungsi Bisnis:** Melakukan pencatatan transaksi non-kas: penyusutan aset tetap bulanan, jurnal akrual beban akhir periode, koreksi salah pencatatan akun, dan pemindahan saldo modal/prive.
* **Alur Input & Penyimpanan:**
  1. Pengguna menentukan nomor bukti memorial (`MM/YYMM/[GUDANG]-[NO]`).
  2. Mengisi Akun Kredit (`cacckas`), Akun Debit (`caccll`), Nominal, Keterangan penyesuaian, serta alokasi mesin (`idmesinll`) jika beban mesin.
  3. Kueri `INSERT INTO TranMemorial` dijalankan.
* **Pembentukan Jurnal DEBIT / KREDIT:**
  * Pada view `AccAll`:
    * Akun `caccll` diakui sebagai **DEBIT** (`nilai = namnt`).
    * Akun `cacckas` diakui sebagai **KREDIT** (`nilai = namnt * -1`).
* **Format Dokumen Output:**
  * Pratinjau cetak voucher bukti memorial untuk arsip audit internal pembukuan.

---

## 3. Alur Pembelian & Pembayaran Hutang Supplier (AP)

Alur Pembelian dan Pengakuan Hutang terbagi dalam 3 tahapan Java:

### 3.1. Pemesanan Pembelian (`Purchase/PurchaseOrder.java`)
* Menginput daftar bahan baku (kertas, tinta, plate, foil), kuantiti, harga satuan, dan PPN.
* Disimpan ke tabel `tranpurchaseorder` dan `tranpurchaseorder_detail`.
* *Catatan Akuntansi:* Pada tahap PO, **belum ada jurnal akuntansi** yang terbentuk karena barang belum diterima dan hak kepemilikan belum berpindah.

### 3.2. Penerimaan Tagihan Faktur Supplier (`Purchase/PurchaseOrderBilling.java`)
* **Fungsi Bisnis:** Mengakui hutang dagang resmi setelah Surat Jalan Supplier dan Surat Penerimaan Barang diverifikasi cocok dengan PO.
* **Alur Penyimpanan:**
  * Disimpan ke `tranpurchaseorderbilling` (Header) dan `tranpurchaseorderbillingd` (Detail).
  * Menghitung total nilai tagihan, PPN masukan, dan tanggal jatuh tempo pembayaran (`cdue_date`).
* **Status Transaksi:** Status PO berubah menjadi `Billed`.

### 3.3. Realisasi Pembayaran Tagihan Supplier (`Purchase/PaymentPurchase.java`)
* **Fungsi Bisnis:** Membayar tagihan hutang supplier melalui transfer bank atau kas.
* **Alur Input & Penyimpanan:**
  1. Pengguna memanggil nomor tagihan billing supplier (`cnbkt_bill`) via dialog pencarian.
  2. Mengisi akun kas/bank pengurang dana (`cacckas`), akun hutang yang dilunasi (`caccll`), nomor rekening tujuan vendor (`cbank`, `cbank_rekening`, `cbank_an`), dan nominal pembayaran (`pay` / `namnt`).
  3. Disimpan ke tabel `tranpayment` dan `tranpayment_detail`.
* **Pembentukan Jurnal DEBIT / KREDIT:**
  * Diproses di dalam view `AccAll` (bagian `UNION ALL` ke-7):
    * Akun Hutang Usaha (`caccll`) -> **DEBIT** sebesar `namnt` (`nilai = namnt * 1`).
    * Akun Kas/Bank (`cacckas`) -> **KREDIT** sebesar `namnt` (`nilai = namnt * -1`).
* **Format Dokumen Output:**
  * Laporan Kartu Hutang Supplier `RHutangBiaya.jrxml` dan Rekapitulasi Hutang per Jatuh Tempo `RRekapHutang.jrxml`.

---

## 4. Alur Kasir, Penjualan, & Pelunasan Piutang Bon (AR)

### 4.1. Transaksi Penjualan Kasir (`Kasir/CashierBayar.java` & `CashierFull.java`)
* **Fungsi Bisnis:** Melayani pembayaran pesanan cetak langsung dari pelanggan tunai maupun termin.
* **Alur Eksekusi:**
  1. Menghitung total tagihan order cetak.
  2. Pengguna memilih metode pembayaran dari `mmetodepembayaran` (Tunai Kasir, Transfer Bank, atau Bon Kredit).
  3. Jika dibayar tunai penuh: Disimpan ke `transale` dan `transaleheader`.
  4. Jika pembayaran sebagian / kredit: Selisih pembayaran dicatat ke tabel `transalebon` sebagai tunggakan piutang pelanggan.
* **Pembentukan Jurnal DEBIT / KREDIT:**
  * Pada view `AccAll` (bagian `UNION ALL` ke-8):
    * Pendapatan kasir diakui dari akumulasi pembayaran tunai `bayar` ditambah pelunasan cicilan `bayarbon`.

### 4.2. Pelunasan Piutang Bon Pelanggan (`Kasir/PembayaranBon.java`)
* **Fungsi Bisnis:** Menerima pembayaran cicilan atas faktur penjualan kredit masa lalu.
* **Alur Otomatisasi Jurnal (`autoSaveMemorial`):**
  * Ketika kasir menyimpan pembayaran bon, fungsi `saveBayar()` mencatat cicilan ke `transalebonpay`.
  * Secara bersamaan, fungsi `autoSaveMemorial()` dieksekusi secara otomatis:
    ```java
    HashMap<String,String> listObj = new HashMap<String,String>();
    listObj.put("ctran", "M1");
    listObj.put("cnbkt", buktimemo);
    listObj.put("cacckas", "103.101"); // Akun Kas/Bank Penampung
    listObj.put("caccll", taccll.getText()); // Akun Piutang Customer
    listObj.put("namnt", nominalbayar.getText());
    listObj.put("cnote", "SPLIT SALE ORDER PELUNASAN CUSTOMER (" + id_member.getText() + ") INV NO " + id_jual.getText());
    sql = con.insertValue("TranMemorial", listObj);
    con.queryUpdate(sql);
    ```
  * *Hasil Jurnal:*
    * **DEBIT:** Kas/Bank (`103.101` / akun kasir terpilih) sebesar nominal cicilan.
    * **KREDIT:** Piutang Usaha (`caccll`) sebesar nominal cicilan.
* **Format Dokumen Output:**
  * Kwitansi Pembayaran Piutang Pelanggan dan Laporan Rekap Bon `rekapbon.jrxml`.

---

## 5. Cara Saldo COA Dihitung di Indigama

Indigama **tidak menyimpan saldo akhir permanen** pada tabel master `maccount`. Seluruh saldo akun dihitung secara dinamis dan *on-the-fly* dari view `AccAll`:

### 5.1. Rumus Saldo Berjalan Buku Besar (General Ledger)
```sql
SELECT ccode, caccnt, dtran, cnbkt, cnote, caccll,
       CASE SIGN(nilai) WHEN 1 THEN nilai ELSE 0 END AS Debet,
       ABS(CASE SIGN(nilai) WHEN 1 THEN 0 ELSE nilai END) AS Kredit
FROM AccAll
WHERE caccnt = '101.001' AND ccode = 'GD' AND dtran BETWEEN '2026-01-01' AND '2026-01-31'
ORDER BY dtran ASC, urut ASC, id ASC;
```
* Baris dengan `urut = 0` adalah Saldo Awal Akun (`NDebet - NCredit` dari `maccount`).
* Baris dengan `urut = 1` adalah mutasi transaksi berurutan waktu.
* Saldo Berjalan Akun Aset = `Saldo Sebelumnya + Debet - Kredit`.
* Saldo Berjalan Akun Kewajiban/Modal = `Saldo Sebelumnya + Kredit - Debet`.

### 5.2. Rumus Laporan Neraca Saldo (Trial Balance)
```sql
SELECT a.caccnt, b.cdesc, b.fAccount,
       SUM(CASE WHEN a.dtran < '2026-01-01' THEN a.nilai ELSE 0 END) AS SaldoAwal,
       SUM(CASE WHEN a.dtran >= '2026-01-01' AND a.nilai > 0 THEN a.nilai ELSE 0 END) AS MutasiDebet,
       SUM(CASE WHEN a.dtran >= '2026-01-01' AND a.nilai < 0 THEN ABS(a.nilai) ELSE 0 END) AS MutasiKredit,
       SUM(a.nilai) AS SaldoAkhir
FROM AccAll a
JOIN MAccount b ON a.caccnt = b.caccnt
WHERE a.ccode = 'GD' AND a.dtran <= '2026-01-31'
GROUP BY a.caccnt, b.cdesc, b.fAccount
ORDER BY a.caccnt;
```

---

## 6. Cara Laporan Mengambil Data & Menghasilkan Dokumen

Pemeriksaan pada `Report/RAnalisaTransaksi.java`:

### 6.1. Pipeline Pembuatan Laporan JasperReports di Java:
1. Java mengeksekusi kueri agregasi SQL terhadap `AccAll` dan menampungnya ke dalam objek `ResultSet`.
2. Data `ResultSet` dibaca baris demi baris dan dimasukkan ke dalam `DefaultTableModel` GUI Swing (`tabel.getModel()`).
3. Model tabel tersebut dibungkus menjadi `JRDataSource` menggunakan:
   ```java
   JRDataSource dataSource = new JRTableModelDataSource(tabel.getModel());
   ```
4. Mengisi parameter laporan (Nama Perusahaan, Alamat, Telepon, Periode Tanggal, Gudang) ke dalam `HashMap param`.
5. Mengompilasi dan mengeksekusi pengisian laporan:
   ```java
   JasperReport JReport = JasperCompileManager.compileReport("report/LabaRugi.jrxml");
   JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
   JasperViewer.viewReport(JPrint, false);
   ```
6. Jendela pratinjau (*JasperViewer*) muncul di layar desktop dan pengguna dapat mencetak ke printer fisik atau mengekspor ke format PDF/XLS.

