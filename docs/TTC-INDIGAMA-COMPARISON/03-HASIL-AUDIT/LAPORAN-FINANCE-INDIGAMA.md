# LAPORAN FORENSIK FINANCE & ACCOUNTING INDIGAMA
**Aplikasi Acuan:** Indigama Desktop Printing Production (Java Swing / MySQL)  
**Tanggal Audit:** 2 November 2026  
**Status Analisis:** Tuntas (Source Code, Skema SQL, JasperReports, View AccAll)

---

## 1. Arsitektur Modul Finance & Accounting Indigama

Modul Keuangan pada Program Desktop Indigama dirancang dengan pendekatan modular berbasis form transaksi di Java Swing dan bermuara pada satu pusat integrasi akuntansi (*accounting aggregation core*) melalui view **`AccAll`**.

Struktur fungsional terbagi dalam 4 pilar utama:
1. **Master Data Keuangan:** Klasifikasi akun (`MAccountClass`), Bagan Akun COA (`MAccount`), Master Biaya Cetak/Mesin (`MCostProduksi`), Master Operasional (`MOperasional`), dan Master Aturan Gaji (`MPayrollDefault`).
2. **Transaksi Kas, Bank, & Memorial:** Form kas masuk/keluar (`TranKasIn`, `TranKasOut`), transfer bank (`TranBankIn`, `TranBankOut`), operasional non-PO (`FormOperasional`, `TranOpOut`), pinjaman karyawan (`TranPinjaman`), dan jurnal memorial/penyesuaian (`TranMemorial`).
3. **Transaksi Pembelian (AP) & Kasir (AR):** Pembayaran tagihan hutang supplier (`TranPayment`), penjualan kasir tunai (`CashierBayar`, `CashierFull`), dan pelunasan piutang bon/kredit pelanggan (`PembayaranBon`).
4. **Mesin Pelaporan Keuangan (Reporting Engine):** Menggunakan JasperReports (`RAnalisaTransaksi.java` dan folder `reports/`) untuk mengompilasi Buku Besar (*General Ledger*), Neraca (*Balance Sheet*), Laba Rugi Perusahaan, serta Laba Rugi per Mesin Percetakan.

---

## 2. Struktur Database Keuangan Indigama

Skema database Indigama (`db_printing`) mengandalkan penamaan tabel berawalan `M` untuk master dan `Tran` / `Form` untuk transaksi.

### 2.1. Tabel Master Keuangan
* **`maccountclass`:**
  * Kolom: `id`, `faccount`, `cdesc`, `nstatus`.
  * Isi data: 6 klasifikasi standar akuntansi:
    * `CL_ASET` (Aktiva/Aset)
    * `CL_LIAB` (Kewajiban/Hutang)
    * `CL_EQTY` (Modal/Ekuitas)
    * `CL_REV` (Pendapatan/Penjualan)
    * `CL_COGS` (Harga Pokok Penjualan - HPP)
    * `CL_EXP` (Beban/Biaya Operasional)
* **`maccount`:**
  * Kolom: `id`, `ccode`, `caccnt`, `cdesc`, `faccount`, `fgroup`, `ndebet`, `ncredit`, `cperiod`.
  * Menyimpan daftar akun buku besar, saldo awal periode (`cperiod`), serta saldo awal debet/kredit.
* **`moperasional` & `mcost`:**
  * Kolom: `id`, `kode`, `nama`, `caccnt`, `fstatus`.
  * Memetakan jenis pengeluaran operasional dan biaya cetak langsung ke nomor akun COA terkait.

### 2.2. Tabel Transaksi Keuangan
* **`trankas` & `trankasbank`:**
  * Kolom utama: `id`, `ctran`, `cnbkt`, `ccode`, `cyear`, `dtran`, `cacckas`, `caccll`, `namnt`, `cnote`, `nik`, `nik_cname`, `cusername`, `kode_customer`, `idmesin`, `kode`.
  * Aturan nilai kolom:
    * `ctran = '01'`: Kas Masuk
    * `ctran = '02'`: Kas Keluar
    * `ctran = '21'`: Bank Masuk
    * `ctran = '22'`: Bank Keluar
    * `cacckas`: Kode Akun Kas / Bank utama.
    * `caccll`: Kode Akun Lawan (*offsetting account*).
    * `namnt`: Nominal transaksi.
* **`tranmemorial`:**
  * Kolom utama: `id`, `ctran`, `cnbkt`, `ccode`, `cyear`, `dtran`, `cacckas`, `caccll`, `namnt`, `cnote`, `cusername`, `kode_customer`, `kode_customerll`, `idmesinll`.
  * Digunakan untuk jurnal umum, penyusutan aset, penyesuaian akhir bulan, dan alokasi biaya non-kas.
* **`tranopout` & `formoperasional`:**
  * Kolom utama: `id`, `cnbkt`, `dtran`, `cacckas`, `caccll`, `namnt`, `cnote`.
  * Digunakan untuk mencatat beban operasional yang diajukan departemen/karyawan.
* **`tranpayment` & `tranpayment_detail`:**
  * Kolom utama: `id`, `cnbkt`, `cnbkt_PO`, `dtran`, `kode_supplier`, `cacckas`, `caccll`, `namnt`, `cbank`, `cbank_rekening`, `cbank_an`.
  * Mencatat pelunasan hutang atas Purchase Order (PO) bahan/material.
* **`transale`, `transalebon`, `transalebonpay`:**
  * Kolom utama: `cnbkt`, `dtran`, `caccnt`, `bayar`, `cicilan`.
  * Mencatat penerimaan kas penjualan dan pembayaran cicilan piutang customer.

---

## 3. Bedah Forensik View `AccAll`: Jantung Akuntansi Indigama

Temuan terpenting dalam audit forensik Indigama adalah keberadaan konstruksi view SQL **`AccAll`** yang didefinisikan secara dinamis di `Main/Login.java` (baris 589–729).

View `AccAll` berfungsi sebagai **General Ledger Unifier** yang mengonversi seluruh transaksi bisnis menjadi jurnal berpasangan (double-entry ledger) secara otomatis:

```sql
ALTER VIEW AccAll AS

-- 1. Saldo Awal Akun COA
SELECT id, 0 AS urut, ccode, '-1' AS ctran, '000000' AS cnbkt, 
       DATEADD(d, -1, RIGHT(cperiod, 2) + '/1/' + LEFT(cperiod, 4)) AS dtran,
       caccnt, (ndebet - ncredit) AS nilai, '' AS caccll, '' AS nik, 
       'Saldo Awal Akun ' + cdesc AS cnote, '' AS kode_customer
FROM maccount

UNION ALL 

-- 2. Kas Masuk (ctran = '01'): Kas bertambah (Debit), Lawan berkurang/bertambah (Kredit)
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, cacckas AS caccnt, namnt AS nilai, caccll AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankas WHERE ctran = '01'
UNION ALL
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, caccll AS caccnt, namnt * -1 AS nilai, cacckas AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankas WHERE ctran = '01'

UNION ALL

-- 3. Kas Keluar (ctran = '02'): Kas berkurang (Kredit), Lawan bertambah (Debit)
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, cacckas AS caccnt, namnt * -1 AS nilai, caccll AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankas WHERE ctran = '02'
UNION ALL
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, caccll AS caccnt, namnt AS nilai, cacckas AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankas WHERE ctran = '02'

UNION ALL

-- 4. Bank Masuk & Keluar (ctran = '21' & '22')
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, cacckas AS caccnt, namnt AS nilai, caccll AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankasbank WHERE ctran = '21'
UNION ALL
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, caccll AS caccnt, namnt * -1 AS nilai, cacckas AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankasbank WHERE ctran = '21'
UNION ALL
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, cacckas AS caccnt, namnt * -1 AS nilai, caccll AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankasbank WHERE ctran = '22'
UNION ALL
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, caccll AS caccnt, namnt AS nilai, caccll AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM trankasbank WHERE ctran = '22'

UNION ALL

-- 5. Beban Operasional Keluar (ctran = 'O2')
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, cacckas AS caccnt, namnt * -1 AS nilai, caccll AS caccll, nik, cnote, '' FROM tranopout WHERE ctran = 'O2'
UNION ALL
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, caccll AS caccnt, namnt AS nilai, cacckas AS caccll, nik, cnote, '' FROM tranopout WHERE ctran = 'O2'

UNION ALL

-- 6. Jurnal Memorial
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, cacckas AS caccnt, namnt * -1 AS nilai, caccll AS caccll, nik, cnote, ISNULL(kode_customer,'') FROM tranmemorial
UNION ALL
SELECT id, 1 AS urut, ccode, ctran, cnbkt, dtran, caccll AS caccnt, namnt AS nilai, cacckas AS caccll, nik, cnote, ISNULL(kode_customerll,'') FROM tranmemorial

UNION ALL

-- 7. Pembayaran Hutang Supplier (PO Payment)
SELECT b.id, 1 AS urut, b.ccode, '' AS ctran, b.cnbkt, b.dtran, b.cacckas AS caccnt, b.namnt * -1 AS nilai, b.caccll AS caccll, '' AS nik, 
       'PEMBAYARAN PO KEPADA ' + c.namasupplier AS cnote, '' AS kode_customer
FROM tranpayment a 
JOIN tranpayment_detail b ON a.id = b.id_cnbkt AND a.cnbkt = b.cnbkt
JOIN (SELECT a.cnbkt, b.kode_supplier, b.cname AS namasupplier FROM tranpurchaseorder_detail a JOIN msupplier b ON a.kode_supplier=b.kode_supplier GROUP BY a.cnbkt, b.kode_supplier, b.cname) c ON a.cnbkt_PO = c.cnbkt

UNION ALL

-- 8. Penjualan Kasir & Pelunasan Bon Piutang
SELECT 1 AS id, 1 AS urut, a.ccode, ctran, a.cnbkt, a.dtran, a.caccnt, (a.bayar + ISNULL(bayarbon, 0)) AS nilai, '' AS caccll, '' AS nik, 'SALES ORDER' AS cnote, '' AS kode_customer
FROM (SELECT DISTINCT ctran, ccode, cnbkt, dtran, caccnt, bayar FROM transale WHERE LEFT(cnbkt,1) <> 'R') a
LEFT JOIN (SELECT cnbkt, ccode, SUM(cicilan) AS bayarbon FROM transalebonpay GROUP BY cnbkt, ccode) b ON a.ccode = b.ccode AND a.cnbkt = b.cnbkt

UNION ALL

-- 9. Beban Payroll Gaji Karyawan
SELECT b.id, 1 AS urut, a.ccode, 'HR' AS ctran, a.cnbkt, a.dtran, a.cacckas, b.totalakhir * -1 AS nilai, a.caccll, b.nik, 
       'Payroll ' + a.cperiode + ' - ' + c.cname AS cnote, '' AS kode_customer 
FROM tranpayroll a 
JOIN tranpayrolld b ON a.ccode = b.ccode AND a.cnbkt = b.cnbkt
JOIN mkaryawan c ON b.nik = c.nik;
```

### Keunggulan Arsitektur `AccAll`:
1. **Garansi Keseimbangan (Self-Balancing):** Setiap transaksi kas/bank secara otomatis memproduksi baris positif (`nilai = namnt`) dan baris negatif (`nilai = namnt * -1`), menjamin total `SUM(nilai) = 0`.
2. **Kueri Buku Besar Sederhana:** Untuk melihat mutasi akun kas `101.001`:
   ```sql
   SELECT dtran, cnbkt, cnote, nilai, caccll 
   FROM AccAll 
   WHERE caccnt = '101.001' AND dtran BETWEEN '2026-01-01' AND '2026-01-31' 
   ORDER BY dtran, urut, id;
   ```
3. **Kueri Neraca & Laba Rugi Instan:**
   * **Neraca:** Mengambil agregat saldo akun `CL_ASET`, `CL_LIAB`, dan `CL_EQTY` hingga tanggal laporan.
   * **Laba Rugi:** Mengambil mutasi periode atas akun `CL_REV`, `CL_COGS`, dan `CL_EXP`.

---

## 4. Mekanisme Alur Transaksi Java Indigama

### 4.1. Pembuatan Kode Bukti (Autonumber)
Indigama menggunakan helper autonumber terstandarisasi berbasis kombinasi kode gudang/perusahaan + tipe transaksi + bulan/tahun + nomor urut:
* Format Kas Masuk: `[GUDANG]-KM-[YYMM][0001]`
* Format Kas Keluar: `[GUDANG]-KK-[YYMM][0001]`
* Format Bank Masuk: `[GUDANG]-BM-[YYMM][0001]`
* Format Bank Keluar: `[GUDANG]-BK-[YYMM][0001]`
* Format Memorial: `[GUDANG]-MM-[YYMM][0001]`

### 4.2. Validasi & Eksekusi Penyimpanan
Dalam `TranKasIn.java`, `TranKasOut.java`, dan `TranMemorial.java`:
1. Validasi saldo akun kas/bank asal (mencegah kas minus untuk transaksi kas keluar).
2. Pilihan akun lawan wajib diisi dari tabel `maccount`.
3. Dukungan multi-baris detail: Satu nomor bukti kas dapat memiliki beberapa rincian akun lawan yang berbeda (misalnya: 1 bukti pengeluaran kas membayar beban listrik, ATK, dan konsumsi sekaligus).
4. Tombol cetak langsung membuka *Jasper Viewer* dengan template `TranKas.jasper` atau `TranBankIn.jasper`.

---

## 5. Laporan Keuangan Indigama (Report Engine)

Diperiksa pada file `Report/RAnalisaTransaksi.java`:

### 5.1. Laporan Neraca (`RNeraca.jrxml`)
Dihitung dengan kueri:
```sql
SELECT a.ccode, 
       CASE UPPER(faccount) WHEN 'CL_ASET' THEN 'AKTIVA' ELSE 'PASIVA' END AS GroupAcc, 
       a.caccnt, cdesc,
       CASE SIGN(SUM(a.nilai)) WHEN 1 THEN SUM(a.nilai) ELSE 0 END AS Debet, 
       ABS(CASE SIGN(SUM(a.nilai)) WHEN 1 THEN 0 ELSE SUM(a.nilai) END) AS Kredit,
       SUM(a.nilai) AS saldo 
FROM AccAll a 
JOIN MAccount b ON a.caccnt = b.caccnt
WHERE a.ccode = ? AND a.dtran <= ?
GROUP BY a.ccode, a.caccnt, cdesc, faccount 
ORDER BY GroupAcc;
```

### 5.2. Laporan Laba Rugi (`LabaRugi.jrxml`)
Mengagregasikan pendapatan penjualan, HPP (bahan baku, biaya cetak, tenaga kerja borongan), dan beban operasional pada rentang tanggal transaksi.

### 5.3. Laporan Laba Rugi Mesin (`LabaRugiMesin.jrxml` & `LabaRugiMesinD.jrxml`)
Fitur unggulan spesifik industri percetakan:
* Menghubungkan nomor mesin cetak (`idmesin`) pada saat pengerjaan SPK dengan pemakaian material tinta/kertas, upah operator/teknisi mesin, dan biaya perawatan mesin.
* Memberikan laporan profitabilitas per unit mesin cetak.

### 5.4. Laporan Kartu & Rekap Hutang Supplier (`RHutangBiaya.jrxml`)
* Menampilkan saldo hutang awal kepada supplier, penambahan hutang dari penerimaan PO, dan pengurangan hutang dari pembayaran kas/bank (`tranpayment`).

---

## 6. Kesimpulan Forensik Modul Finance Indigama

1. **Kekuatan Utama Indigama:**
   * Menerapkan prinsip akuntansi **Double-Entry murni** melalui integrasi view `AccAll`.
   * Memiliki keterkaitan organik antara Kas, Bank, PO Supplier, Kasir/AR, Payroll, dan Biaya Mesin.
   * Laporan Laba Rugi per Mesin sangat relevan dengan operasional percetakan.
2. **Kelemahan Indigama:**
   * Antarmuka jadul (*legacy desktop* Java Swing), tidak responsif dan terikat pada OS desktop.
   * Tidak memiliki relasi FK di database MySQL (risiko inkonsistensi data jika ada kueri liar).
   * Format cetak voucher kas dan laporan kaku berbasis iReport tanpa dukungan format web modern.

