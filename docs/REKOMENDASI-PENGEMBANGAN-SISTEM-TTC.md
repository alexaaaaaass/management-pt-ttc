# 📋 Rekomendasi Pengembangan & Penyempurnaan Sistem ERP
## PT Teknografi Tri Cawanaska (TTC - Indigama)

> **Dokumen Analisis & Rekomendasi Fitur Lanjutan**  
> **Tanggal:** 12 November 2026  
> **Status:** Siap Evaluasi & Implementasi Bertahap  
> **Target Sistem:** Filament Admin & Integrasi Operasional Percetakan/Packaging  

---

## 📌 1. Latar Belakang & Ringkasan Eksekutif

Sistem ERP **PT Teknografi Tri Cawanaska** saat ini telah memiliki arsitektur yang solid, mencakup seluruh siklus bisnis mulai dari **Marketing/Penjualan**, **Pengadaan (Purchasing)**, **PPIC & Produksi (Flexo Printing & Finishing)**, **Gudang (Warehouse & Stok)**, **Ekspedisi (Dispatch)**, hingga **Keuangan (Finance & Akuntansi)** dan **HRD/Payroll**.

Format angka desimal dan pemisah ribuan real-time (`currencyMask` dan `quantityMask`) juga telah berhasil diterapkan secara menyeluruh di semua modul.

Namun, agar sistem dapat bertransformasi dari sekadar **pencatatan data administratif** menjadi **sistem operasional terintegrasi otomatis (Action-Driven ERP)**, terdapat **5 porsi kunci** yang sangat direkomendasikan untuk dikembangkan pada fase berikutnya.

---

## 🚀 2. Lima Porsi Kunci yang Perlu Dikembangkan

```
┌────────────────────────────────────────────────────────────────────────┐
│             5 PORSI REKOMENDASI PENGEMBANGAN SISTEM TTC                │
├────────────────────────┬───────────────────────┬───────────────────────┤
│ 1. Otomasi Complaint   │ 2. Tracking SPK       │ 3. Finish Good Auto   │
│    ➜ SPK Rework/Potong │    ➜ Visual Timeline  │    ➜ Auto Stock Out   │
├────────────────────────┼───────────────────────┴───────────────────────┤
│ 4. Cashflow & Aging    │ 5. Executive Dashboard                        │
│    ➜ Alert Piutang     │    ➜ KPI & Grafik Penjualan                   │
└────────────────────────┴───────────────────────────────────────────────┘
```

---

### Porsi 1: Otomasi Tindak Lanjut Berita Acara Complaint (Next-Action 1-Klik)

#### A. Kondisi Saat Ini
Modul **Berita Acara Complaint** sudah memiliki pencatatan kuantitas penolakan (`qty_penolakan`), lampiran bukti foto cacat vs referensi, serta pilihan bentuk solusi (Sortir Ulang, Ganti Barang/Cetak Ulang, Potong Tagihan, Retur Total). Namun, eksekusi tindak lanjutnya masih dilakukan secara terpisah dan manual oleh user.

#### B. Rekomendasi Pengembangan
Menambahkan tombol aksi otomatis berbasis status dan pilihan solusi pada Berita Acara:

1. **Tombol `[Terbitkan SPK Pengganti / Rework]`:**
   - **Kondisi:** Muncul saat solusi dipilih `Cetak Ulang / Ganti Barang Baru`.
   - **Aksi Otomatis:** Sistem menduplikasi data teknis SPK awal (spesifikasi kertas, ukuran potong, warna, mesin) dengan target quantity otomatis diisi sebesar `qty_penolakan`. Nomor SPK baru otomatis bertanda khusus (misal: `SPK/26/04/001-R1`).
2. **Tombol `[Terapkan Potongan ke Invoice]`:**
   - **Kondisi:** Muncul saat solusi dipilih `Potong Tagihan / Credit Note`.
   - **Aksi Otomatis:** Menghitung nilai rupiah barang yang ditolak:
     $$\text{Nilai Potongan} = \text{Qty Penolakan} \times \text{Harga Satuan Item}$$
     Sistem langsung menambahkan nilai tersebut ke kolom `potongan` di Invoice terkait dan memperbarui saldo piutang.
3. **Otomasi Karantina ke Menu `Blokir`:**
   - **Kondisi:** Saat Berita Acara disetujui (ACC).
   - **Aksi Otomatis:** Sistem otomatis mencatat log karantina ke tabel `blokir` dengan `qty_blokir = qty_penolakan` sehingga barang reject tidak tercampur ke stok siap kirim.

---

### Porsi 2: Tracking Progress Produksi SPK Visual (Timeline & Kanban)

#### A. Kondisi Saat Ini
Rangkaian tahapan produksi terbagi ke beberapa sub-modul terpisah:
* *Pra Cetak (Plate / CTP)*
* *Printing (Cetak Flexo / Offset)*
* *Finishing (Laminasi, Hotprint, Die-Cut/Pond, Lem)*
* *Packaging (Sortir & Packing Box)*

Jika tim Marketing atau Customer menanyakan *"Pesanan kami sekarang sudah sampai tahap apa?"*, staf harus membuka masing-masing menu untuk memeriksa status fisik barang.

#### B. Rekomendasi Pengembangan
1. **Visual Stage Progress Bar di Detail SPK & Sales Order:**
   Menampilkan bar tahapan produksi interaktif:
   $$\text{Order ACC} \xrightarrow{100\%} \text{Pra Cetak} \xrightarrow{100\%} \text{Cetak Flexo} \xrightarrow{60\%} \text{Finishing} \xrightarrow{0\%} \text{Packaging} \xrightarrow{0\%} \text{Siap Kirim}$$
2. **Live Counter Per Tahapan:**
   Menampilkan ringkasan jumlah yang sudah selesai per tahap (contoh: *Pra-Cetak: Selesai | Printing: 50.000 / 50.000 lembar | Pond Finishing: 20.000 / 50.000 lembar*).
3. **Status Peringatan Keterlambatan (Lead Time Alert):**
   Memberikan badge merah jika SPK telah melewati tanggal `tanggal_estimasi_selesai` namun status belum `FINISH`.

---

### Porsi 3: Otomasi Pemotongan Stok Barang Jadi (Finish Good Stock) pada Surat Jalan

#### A. Kondisi Saat Ini
* Bahan baku (Raw Material) sudah memiliki mutasi stok otomatis saat pengeluaran bahan (`IssueMaterial`).
* Proses Packaging sudah menghasilkan rincian barang jadi (`Packagings`).
* Namun saat diterbitkan **Surat Jalan**, pemotongan kartu stok barang jadi (`finish_good_stocks` & `finish_good_movements`) belum otomatis tersinkronisasi penuh.

#### B. Rekomendasi Pengembangan
1. **Auto Stock Out pada Surat Jalan:**
   Saat Surat Jalan dibuat atau berstatus *Terkirim/Dikirim*:
   * Sistem otomatis memotong stok di tabel `finish_good_stocks`.
   * Mencatat mutasi keluar di `finish_good_movements`:
     $$\text{Movement Type} = \text{OUT}, \quad \text{Source} = \text{SURAT\_JALAN}, \quad \text{Qty} = \text{qty\_pengiriman}$$
2. **Validasi Anti-Minus:**
   Mencegah penerbitan Surat Jalan jika fisik stok barang jadi di gudang kurang dari jumlah yang hendak dikirim (kecuali diizinkan otorisasi khusus Supervisor).
3. **Auto Stock In saat Retur Barang:**
   Jika customer mengembalikan barang (Retur/Berita Acara), stok barang jadi otomatis ditambahkan kembali ke gudang sebagai stok sortir/karantina.

---

### Porsi 4: Monitoring Jatuh Tempo & Umur Piutang (Cashflow & Aging Invoices)

#### A. Kondisi Saat Ini
Modul Keuangan (Invoices, Kas/Bank, dan Jurnal Memorial) sudah berjalan dengan baik. Namun penagihan piutang customer masih bergantung pada pengecekan tanggal satu per satu secara manual.

#### B. Rekomendasi Pengembangan
1. **Laporan Aging Piutang (Umur Piutang Otomatis):**
   Membuat laporan pengelompokan piutang customer:
   * **Belum Jatuh Tempo (Current)**
   * **Menunggak 1 – 30 Hari**
   * **Menunggak 31 – 60 Hari**
   * **Menunggak > 60 Hari (Perhatian Khusus / Macet)**
2. **Indikator Warna (Badge Due Date) di Tabel Invoice:**
   * 🟢 **Hijau:** Jatuh tempo masih lama (> 7 hari).
   * 🟡 **Kuning:** Jatuh tempo mendekat ($\le 3$ hari).
   * 🔴 **Merah Berkedip:** Telah melewati tanggal jatuh tempo (Overdue).
3. **Kredit Limit & Peringatan Order Customer:**
   Jika customer memiliki invoice macet yang melewati batas toleransi, sistem menampilkan peringatan saat sales membuat **Sales Order** baru untuk customer tersebut.

---

### Porsi 5: Executive & Operational Dashboard Beranda

#### A. Kondisi Saat Ini
Halaman awal (Dashboard Admin) saat ini hanya memuat 4 kotak statistik angka dasar (`Total Sales Order`, `Total Purchase Order`, `Pengajuan PR`, `Total PR`).

#### B. Rekomendasi Pengembangan
Menyediakan dashboard interaktif sesuai peran pengguna:

1. **Widget Manajemen / Pemilik:**
   * **Grafik Tren Omset Penjualan Bulanan (Target vs Realisasi).**
   * **Total Piutang Belum Tertagih & Total Hutang Supplier Jatuh Tempo.**
   * **Total Laba Bersih Berjalan.**
2. **Widget Operasional Pabrik & Gudang:**
   * **SPK On-Process Hari Ini:** Jumlah SPK yang aktif di mesin cetak dan finishing.
   * **Bahan Baku Kritis:** Daftar master item yang stok fisiknya berada di bawah `min_stock`.
   * **Jadwal Pengiriman Hari Ini:** Daftar Surat Jalan yang harus diberangkatkan hari ini.

---

## 🗓️ 3. Rencana Tahapan Implementasi (Roadmap)

| Fase | Fokus Pengembangan | Estimasi Dampak |
| :--- | :--- | :--- |
| **Fase 1** | **Otomasi Berita Acara Complaint**<br>• Tombol buat SPK Rework otomatis<br>• Tombol potong tagihan Invoice otomatis | Langsung menyelesaikan alur penanganan barang retur/komplain tanpa kerja dobel. |
| **Fase 2** | **Otomasi Stok Barang Jadi (Finish Good)**<br>• Mutasi OUT otomatis saat Surat Jalan terbit<br>• Sinkronisasi stok siap kirim | Mencegah selisih stok gudang barang jadi dan stok fiktif. |
| **Fase 3** | **Visual Tracking Produksi SPK**<br>• Progress Bar per tahap produksi di SPK<br>• Alert keterlambatan mesin/order | Meningkatkan transparansi produksi bagi tim Sales & Manajemen. |
| **Fase 4** | **Financial Aging & Executive Dashboard**<br>• Monitoring umur piutang (Aging Receivables)<br>• Grafik performa penjualan dan stok menipis di Beranda | Mempercepat perputaran kas masuk (cashflow) dan memudahkan monitoring owner. |

---

## 💡 4. Kesimpulan

Dengan menerapkan rekomendasi ini secara bertahap, sistem **TTC Indigama** akan semakin matang, tidak hanya sebagai buku catatan digital, tetapi sebagai **mesin operasional pintar** yang mencegah kesalahan input manusia, mempercepat penanganan keluhan pelanggan, dan memberikan visibilitas penuh terhadap proses produksi maupun keuangan pabrik.

