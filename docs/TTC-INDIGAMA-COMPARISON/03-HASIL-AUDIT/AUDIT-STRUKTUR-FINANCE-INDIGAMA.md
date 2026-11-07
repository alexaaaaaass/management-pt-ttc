# AUDIT STRUKTUR DATABASE FINANCE INDIGAMA
**Aplikasi Acuan:** Indigama Desktop Printing Production (Java Swing / MySQL)  
**Database:** `db_printing` (103 Tabel, 1.494 Kolom, 0 Foreign Key, 104 Indeks, 1 Rutin/Function `getdate`)  
**Sifat Audit:** READ-ONLY Forensic Inspection  
**Tanggal:** 2 November 2026

---

## 1. Ikhtisar Arsitektur Database Keuangan Indigama

Database Indigama (`db_printing`) merupakan sistem basis data relasional bergaya *legacy client-server* yang dirancang untuk mendukung aplikasi desktop Java Swing percetakan. 

### Karakteristik Struktural Utama:
1. **Ketiadaan Foreign Key Fisik (0 FK):** Integritas referensial tidak ditegakkan oleh RDBMS InnoDB, melainkan dibebankan 100% pada logika validasi di dalam kode Java (`ConnectDB.java`, form transaksi, dan dialog pemilihan data).
2. **Skema Kolom Datar & Redundan (*Flat & Denormalized Columns*):** Banyak tabel transaksi yang menyimpan salinan nama relasi secara langsung (misalnya `cacckas` menyimpan kode akun, sekaligus ada kolom `acc_kas` atau `customer` dan `kode_customer`, `nik` dan `nik_cname`).
3. **Agregasi Jurnal Terpusat via View `AccAll`:** Indigama tidak menyimpan baris jurnal di tabel fisik tersendiri, melainkan membentuk jurnal virtual *double-entry* melalui view `AccAll` yang melakukan `UNION ALL` atas seluruh tabel transaksi.

---

## 2. Bedah Struktur Tabel Master Keuangan

### 2.1. Tabel `maccountclass` (Klasifikasi Akun)
* **Tujuan Bisnis:** Mengelompokkan bagan akun ke dalam klasifikasi standar akuntansi keuangan.
* **Jumlah Kolom:** 5 kolom.
* **Jumlah Record Terdaftar:** 6 baris data master.
* **Struktur Kolom Fisik:**
  * `id` (`int(11)`, Primary Key, Auto Increment)
  * `caccnt_class` (`varchar(255)`): Kode klasifikasi akun (`CL_ASET`, `CL_LIAB`, `CL_EQTY`, `CL_REV`, `CL_COGS`, `CL_EXP`).
  * `cname` (`varchar(255)`): Deskripsi/Nama klasifikasi (Aktiva, Kewajiban, Modal, Pendapatan, HPP, Beban).
  * `cusername` (`varchar(255)`): Pengguna pembuat/pembaru.
  * `fstatus` (`int(11)`): Status keaktifan (1 = Aktif).

### 2.2. Tabel `maccount` (Bagan Akun / Chart of Accounts)
* **Tujuan Bisnis:** Master buku besar (Bagan Akun / COA) dan tempat penyimpanan saldo awal periode.
* **Jumlah Kolom:** 18 kolom.
* **Jumlah Record Terdaftar:** 22 baris akun terdaftar.
* **Struktur Kolom Fisik:**
  * `id` (`int(11)`, Primary Key, Auto Increment)
  * `caccnt` (`varchar(255)`): Kode nomor akun unik (misal: `101.001` Kas Toko, `102.001` Kas Bank, `111.001` Piutang Dagang, `201.001` Hutang Dagang).
  * `cdesc` (`varchar(255)`): Nama rekening/akun.
  * `fAccount` (`varchar(255)`): Kunci klasifikasi akun (merujuk ke `caccnt_class` pada `maccountclass`).
  * `ccode` (`varchar(255)`): Kode divisi/gudang/entitas.
  * `cPeriod` (`varchar(255)`): Periode saldo awal (format: `YYYYMM`).
  * `NDebet` (`double`): Nilai saldo awal debit.
  * `NCredit` (`double`): Nilai saldo awal kredit.
  * `NNominal` (`double`): Saldo netto awal (`NDebet - NCredit`).
  * `cnote` (`varchar(255)`): Keterangan saldo awal.
  * `dtran` (`datetime`): Tanggal penetapan saldo.
  * `cusername` (`varchar(255)`): Pembuat akun.
  * `fstatus` (`varchar(255)`): Status keaktifan akun.
  * `gudang`, `supplier`, `cnbkt`, `cnbkt_po`, `csj_vendor`: Kolom tambahan denormalisasi untuk keperluan tracking saldo awal vendor.

### 2.3. Tabel `mmetodepembayaran` & `mpayment` (Metode & Termin Pembayaran)
* **`mmetodepembayaran` (7 kolom, 5 record):**
  * `id` (`int(11)`, PK), `kode` (`varchar(255)`), `nama` (`varchar(255)`), `caccnt` (`varchar(255)` - akun kas/bank penampung), `rekening`, `an`, `fstatus`.
  * Memetakan rekening bank (misal: BCA, Mandiri, Kas Tunai) langsung ke nomor akun COA penampung dana.
* **`mpayment` (6 kolom, 6 record):**
  * `id` (`int(11)`, PK), `cpay_code` (`varchar(255)`), `cdesc` (`varchar(255)`), `ntime` (`int(11)` - tenor hari/termin), `fstatus`, `cusername`.
  * Menyimpan termin pembayaran tagihan (Cash, COD, 7 Hari, 14 Hari, 30 Hari, 45 Hari).

---

## 3. Bedah Struktur Tabel Transaksi Kas, Bank, & Operasional

### 3.1. Tabel `trankas` (Buku Kas Masuk & Kas Keluar)
* **Tujuan Bisnis:** Mencatat transaksi kas fisik (tunai) yang masuk ke kasir atau keluar dari brankas kecil/operasional.
* **Jumlah Kolom:** 22 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`int(11)`, Primary Key, Auto Increment)
  * `ctran` (`varchar(255)`): Kode tipe mutasi (`01` = Kas Masuk, `02` = Kas Keluar).
  * `cnbkt` (`varchar(255)`): Nomor bukti kas (format: `[GUDANG]-KM-[YYMM][NO]` atau `[GUDANG]-KK-[YYMM][NO]`).
  * `ccode` (`varchar(255)`): Kode cabang/gudang.
  * `cyear` (`varchar(255)`): Tahun transaksi (4 digit).
  * `dtran` (`datetime`): Tanggal dan jam transaksi.
  * `cacckas` (`varchar(255)`): Kode akun kas fisik (sumber dana pada kas keluar / penampung pada kas masuk).
  * `caccll` (`varchar(255)`): Kode akun lawan (*offsetting account* debet/kredit).
  * `namnt` (`double`): Nominal transaksi kas.
  * `cnote` (`varchar(255)`): Uraian keperluan / memo transaksi.
  * `cusername` (`varchar(255)`): User pembuat transaksi.
  * `kode_customer` (`varchar(50)`): Kode customer (opsional, jika terkait pelunasan langsung).
  * `nik` & `nik_cname`: Identitas karyawan (jika pengeluaran kas ditujukan kepada personal karyawan).
  * `idmesin` (`int(11)`): ID mesin percetakan (jika biaya dialokasikan ke mesin cetak tertentu).
  * `acc_kas`, `acc_bank`, `customer`, `mesin`, `gudang`, `kode`, `lastno`: Kolom pendukung GUI & autonumber.

### 3.2. Tabel `trankasbank` (Buku Bank Masuk & Bank Keluar)
* **Tujuan Bisnis:** Mencatat mutasi rekening koran bank perusahaan (setoran, penerimaan transfer, pembayaran transfer, penarikan tunai).
* **Jumlah Kolom:** 30 kolom.
* **Struktur Kolom Fisik:**
  * Kolom identik dengan `trankas` ditambah kolom spesifik perbankan:
    * `ctran`: `21` = Bank Masuk, `22` = Bank Keluar.
    * `bank` (`varchar(255)`): Nama institusi bank (BCA, Mandiri, BRI, BNI).
    * `bank_rekening` (`varchar(255)`): Nomor rekening bank.
    * `bank_an` (`varchar(255)`): Nama pemilik rekening / atas nama.
    * `cacckas`: Kode akun buku besar bank penampung/pengirim.
    * `caccll`: Kode akun lawan penyeimbang transaksi.

### 3.3. Tabel `tranmemorial` (Jurnal Umum Non-Kas)
* **Tujuan Bisnis:** Mencatat transaksi non-kas: penyesuaian akhir bulan, koreksi pembukuan, depresiasi aktiva tetap, pemindahan saldo antar-akun, dan alokasi split sale order.
* **Jumlah Kolom:** 22 kolom.
* **Struktur Kolom Fisik:**
  * `id` (`int(11)`, Primary Key, Auto Increment)
  * `ctran` (`varchar(255)`): Kode transaksi (default `M1` atau `MM`).
  * `cnbkt` (`varchar(255)`): Nomor bukti memorial (`MM/YYMM/[GUDANG]-[NO]`).
  * `dtran` (`datetime`): Tanggal transaksi.
  * `cacckas` (`varchar(255)`): Akun pertama (akan dicatat sebagai Kredit pada `AccAll`).
  * `caccll` (`varchar(255)`): Akun kedua (akan dicatat sebagai Debit pada `AccAll`).
  * `namnt` (`double`): Nilai transaksi penyesuaian.
  * `cnote` (`varchar(255)`): Keterangan rinci jurnal.
  * `kode_customer` & `kode_customerll`: Relasi multi-pihak jika melibatkan dua entitas pelanggan/rekanan.
  * `idmesin` & `idmesinll`: Alokasi beban antar-mesin percetakan.

### 3.4. Tabel `tranopout` (Kas Operasional Keluar)
* **Tujuan Bisnis:** Khusus mencatat pengeluaran operasional lapangan (bensin kendaraan operasional, servis rutin, konsumsi, retribusi).
* **Jumlah Kolom:** 24 kolom.
* **Struktur Kolom Fisik:**
  * Memiliki kolom operasional spesifik: `cjenis`, `cjenis_name` (Kategori Operasional), `ckendaraan` (Nomor Polisi Armada), `codometer` (Angka Odometer/KM Kendaraan saat pengisian bensin).
  * Kolom akuntansi: `cacckas` (Akun Kas sumber dana), `caccll` (Akun Beban Operasional), `namnt` (Nominal).

---

## 4. Bedah Struktur Tabel Pembelian & Hutang Supplier (AP)

### 4.1. Tabel `tranpurchaseorder` (PO Header & Detail Flat)
* **Tujuan Bisnis:** Mencatat kesepakatan pesanan pembelian bahan baku cetak kepada vendor.
* **Jumlah Kolom:** 33 kolom.
* **Struktur Kolom Fisik:**
  * `cnbkt` (`varchar(255)`): Nomor Purchase Order.
  * `dtran` (`datetime`): Tanggal PO.
  * `cdue_date` (`datetime`): Estimasi tanggal jatuh tempo pembayaran / kedatangan bahan.
  * `kode_supplier`: Identitas vendor supplier.
  * `cproduct`, `cgroup`, `nqty`, `nprice`, `ntotal`, `ppn`: Detail pemesanan bahan baku cetak.
  * `fstatus`: Status PO (`Open`, `Approved`, `Billed`, `Closed`).

### 4.2. Tabel `tranpurchaseorderbilling` & `tranpurchaseorderbillingd` (Tagihan Faktur Supplier)
* **Tujuan Bisnis:** Mengakui kewajiban hutang dagang (*Accounts Payable*) saat faktur tagihan dari supplier diterima setelah penerimaan barang riil.
* **Struktur Kolom Fisik:**
  * **Header (`tranpurchaseorderbilling` - 24 kolom):** `id`, `cnbkt` (No Billing), `cnbkt_PO` (Referensi PO), `dtran` (Tgl Tagihan), `cdue_date` (Jatuh Tempo), `kode_supplier`, `totalnilai`, `ppn`, `fstatus`.
  * **Detail (`tranpurchaseorderbillingd` - 18 kolom):** `id`, `cnbkt`, `cproduct`, `nqty`, `nprice`, `ntotal`, `ppn`.

### 4.3. Tabel `tranpayment` & `tranpayment_detail` (Pembayaran Tagihan Supplier)
* **Tujuan Bisnis:** Mencatat realisasi pembayaran pelunasan tagihan supplier dari rekening kas atau bank perusahaan.
* **Struktur Kolom Fisik:**
  * **Header (`tranpayment` - 45 kolom):**
    * `id`, `cnbkt` (No Bukti Pembayaran Hutang), `cnbkt_PO` (Referensi PO), `cnbkt_bill` (Referensi Billing Tagihan), `dtran`, `kode_supplier`, `namnt`, `pay` (Total Dibayar), `status` (`PAID`).
  * **Detail (`tranpayment_detail` - 36 kolom):**
    * `id`, `id_cnbkt`, `cnbkt`, `cacckas` (Akun Kas/Bank pengurang dana), `caccll` (Akun Hutang Dagang yang dilunasi), `namnt` (Jumlah yang dialokasikan), `cbank`, `cbank_rekening`, `cbank_an` (Rekening tujuan transfer vendor).

---

## 5. Bedah Struktur Tabel Kasir, Penjualan, & Piutang Bon (AR)

### 5.1. Tabel `transale` & `transaleheader` (Transaksi Penjualan Kasir)
* **Tujuan Bisnis:** Mencatat transaksi penjualan produk percetakan tunai maupun kredit di kasir.
* **Struktur Kolom Fisik:**
  * `cnbkt` (`varchar(255)`): Nomor Faktur Penjualan / Struk Kasir.
  * `dtran` (`datetime`): Tanggal penjualan.
  * `caccnt` (`varchar(255)`): Kode akun kas penampung uang penjualan.
  * `total` (`double`): Total nilai faktur.
  * `bayar` (`double`): Jumlah uang tunai yang langsung dibayar oleh pelanggan saat transaksi.
  * `sisa` (`double`): Sisa pembayaran yang belum lunas (menjadi piutang bon).

### 5.2. Tabel `transalebon` & `transalebonpay` (Piutang Bon & Cicilan Pelanggan)
* **Tujuan Bisnis:** Mengelola penagihan piutang pelanggan yang belum lunas dan mencatat riwayat cicilan pelunasan bon.
* **Struktur Kolom Fisik:**
  * **`transalebon`:** Menyimpan histori saldo piutang tertunggak per faktur penjualan pelanggan.
  * **`transalebonpay` (11 kolom):** `id`, `cnbkt` (No Faktur Penjualan), `dtran` (Tanggal Bayar), `cicilan` (Nominal Pelunasan), `caccnt` (Akun Kas/Bank Penerima), `cusername`.

---

## 6. Evaluasi Struktural & Kesimpulan Audit

| Parameter Evaluasi | Temuan Fakta Database Indigama | Implikasi Rekayasa Sistem |
| :--- | :--- | :--- |
| **Integritas Referensial** | **0 Foreign Key** di seluruh tabel database. | Rawan terhadap data yatim piatu (*orphaned records*) jika proses delete di aplikasi tidak menangani relasi secara kaskade (*manual cascade*). |
| **Normalisasi Data** | Denormalisasi tinggi (nama supplier, nama mesin, nama akun diduplikasi ke dalam tabel transaksi). | Mempermudah kueri pelaporan tanpa join berlebih, namun memboroskan memori disk dan rawan inkonsistensi nama jika master diubah. |
| **Penjurnalan Terpadu** | Menggunakan view dinamis `AccAll` yang menyatukan 9 sub-kueri transaksi. | **Sangat cerdas dan elegan secara logika bisnis akuntansi**, namun dapat menimbulkan beban kueri berat jika data transaksi mencapai ratusan ribu baris tanpa indeks komposit yang tepat. |

