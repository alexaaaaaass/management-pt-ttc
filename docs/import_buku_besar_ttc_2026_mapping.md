# Rekap Pemetaan Lengkap 425 Transaksi — Impor Buku Besar COA TTC 2026

* **File SQL Siap Eksekusi:** `01-TTC-LARAVEL/database/import_buku_besar_ttc_2026.sql` (551,2 KB — 1.278 statement SQL atomik dalam `START TRANSACTION; ... COMMIT;`)
* **Mode:** Append (`Opsi 1A`) — data existing tidak disentuh, transaksi impor memakai kode unik `BNK26-0001..0417` & `KAS26-0001..0008`.
* **Pinjaman Modal Indigama (`Opsi 2A`):** 4 baris TUNAI (`Rp 5.114.869.194`) $\rightarrow$ `2142 - Hutang kepada Pemilik`.
* **Transfer Antar-Bank (`Opsi 3A`):** `R193+R195` (`Rp 2.500.000`) & `R201+R202` (`Rp 140.000.000`) digabung menjadi 2 transaksi transfer antar-rekening.
* **Status Database Saat Ini (`Opsi 4A`):** **BELUM dieksekusi ke database live** (hanya diuji lewat *dry-run* `ROLLBACK` pada MariaDB `10.4.32` dengan hasil 0 error & 100% balance).

## 1. Rekonsiliasi Saldo Akhir per Rekening Bank (vs Footer Excel R452–R457)

| Akun COA TTC | Nama di Excel | Masuk (Debit) | Keluar (Kredit) | Saldo Akhir Batch | Footer Excel | Status |
| :--- | :--- | ---: | ---: | ---: | ---: | :---: |
| `1111` Bank BRI - Operasional | Bank - BRI TABUNGAN | Rp 9.411.995.321,00 | Rp 8.946.972.734,76 | **Rp 465.022.586,24** | Rp 465.022.586,24 | EXACT MATCH |
| `1114` Bank Lain - Giro | Bank - BRI GIRO | Rp 1.021.466.387,00 | Rp 1.019.609.701,00 | **Rp 1.856.686,00** | Rp 1.856.686,00 | EXACT MATCH |
| `1112` Bank BCA - Operasional | Bank - BCA | Rp 603.175.658,83 | Rp 544.550.632,96 | **Rp 58.625.025,87** | Rp 58.625.025,87 | EXACT MATCH |
| `1113` Bank Lain - Efendi | Bank - BRI FENDI | Rp 205.400.000,00 | Rp 205.400.000,00 | **Rp 0,00** | Rp 0,00 | EXACT MATCH |
| **TOTAL 4 REKENING BANK** | | **Rp 11.242.037.366,83** | **Rp 10.716.533.068,72** | **Rp 525.504.298,11** | **Rp 525.504.298,11** | **EXACT MATCH** |
| `1116` Petty Cash | TUNAI + Transfer Petty Cash | Rp 5.163.869.194,00 | Rp 5.114.869.194,00 | **Rp 49.000.000,00** | *(14x isi kas kecil)* | VALID |

## 2. Ringkasan Pemetaan per Akun Lawan (37 COA)

| Akun Lawan COA | Jml Tx | Total Debit (Rp) | Total Kredit (Rp) |
| :--- | :---: | ---: | ---: |
| `1111` Bank BRI - Operasional *(transfer antar-bank R201+R202)* | 1 | 140.000.000,00 | 0,00 |
| `1112` Bank BCA - Operasional *(transfer antar-bank R193+R195)* | 1 | 2.500.000,00 | 0,00 |
| `1116` Petty Cash *(pengisian kas kecil dari bank)* | 14 | 49.000.000,00 | 0,00 |
| `1131` Piutang Karyawan *(kasbon & pelunasan)* | 4 | 6.500.000,00 | 300.000,00 |
| `1211` Tanah & Bangunan *(cicilan gudang TTC B8-B9)* | 8 | 1.487.510.003,00 | 0,00 |
| `1221` Mesin & Peralatan *(Mesin Zonten, PLN/Trafo/Kubikel/LVMDP, AC, Printer)* | 18 | 12.492.411.750,76 | 0,00 |
| `1231` Kendaraan *(Mobil & UTJ Kendaraan TTC)* | 3 | 604.073.239,00 | 0,00 |
| `1241` Furniture & Fixtures *(Kursi, Meja, Lemari Kantor)* | 1 | 34.082.882,00 | 0,00 |
| `1251` Bangunan Dalam Pembangunan *(Mes TTC, Nogosari, material cor/bangunan)* | 51 | 436.287.606,00 | 500.000,00 |
| `1261` Renovasi & Leasehold Improvement *(Partisi, Kaca Film, Pengecatan)* | 8 | 93.786.000,00 | 0,00 |
| `2111` Utang Usaha - Bahan Baku IDR *(Hutang Bahan Baku R039)* | 1 | 136.664.000,00 | 0,00 |
| `2133` Biaya Lainnya Terutang *(Bayar Hutang External)* | 1 | 250.000,00 | 0,00 |
| `2142` Hutang kepada Pemilik *(4x Modal Pinjaman Indigama)* | 4 | 0,00 | 5.114.869.194,00 |
| `3100` Modal Awal Pemilik *(14x Setoran Modal ke PT Teknografi / Fendi)* | 14 | 0,00 | 10.847.173.239,00 |
| `3500` Prive / Pengambilan Pemilik *(Pengembalian sisa saldo Fendi)* | 1 | 43.443,00 | 0,00 |
| `4101` Penjualan Barang Jadi - IDR *(Etiket, Inner, Ambri, Cylinder, CTP)* | 21 | 0,00 | 225.660.036,00 |
| `4510` Pendapatan Bunga *(Interest on Account / Bunga BCA & BRI)* | 14 | 0,00 | 2.684.873,83 |
| `4540` Pendapatan Lain-lain *(Penjualan Tebu & Retur Toko Anyar)* | 4 | 0,00 | 14.750.000,00 |
| `5101` Pemakaian Bahan Baku - Produksi *(Pembelian Kertas Ivory)* | 1 | 2.874.555,00 | 0,00 |
| `5308` Biaya Subcont Produksi *(Jasa Cetak/Produksi/Lem/Plong/Pisau/Klis/Spot UV & 2 reversal)* | 18 | 84.815.493,00 | 6.557.218,00 |
| `6104` Biaya Pengiriman Barang *(Ongkir Pisau & Expedisi Nogosari)* | 2 | 2.922.000,00 | 0,00 |
| `6108` Komisi Marketing / Referral *(Komisi & Fee Marketing + 1 refund)* | 10 | 6.057.240,00 | 412.000,00 |
| `6203` Listrik & Air Kantor *(Token Listrik & PDAM)* | 10 | 1.184.900,00 | 0,00 |
| `6205` Biaya Profesional *(Pembuatan & Perubahan Akta PT)* | 4 | 18.630.000,00 | 0,00 |
| `6207` Telepon & Internet *(Wifi Telkom / TLKM)* | 6 | 7.161.694,00 | 0,00 |
| `6209` Bahan Bakar dan Etoll Kendaraan *(Bensin, E-Toll, Transport)* | 5 | 1.146.000,00 | 0,00 |
| `6212` Iuran & Keanggotaan *(Iuran Keamanan & Kebersihan TTC)* | 2 | 20.064.000,00 | 0,00 |
| `6214` Biaya Administrasi Bank *(Admin transfer, BDC, SMS, Key, Cek, Pajak Bunga)* | 152 | 1.664.523,96 | 0,00 |
| `6215` Perawatan Gedung/Bangunan *(Reparasi Air TTC)* | 1 | 282.000,00 | 0,00 |
| `6216` Perlengkapan dan Furniture *(Peralatan Rumah Tangga Kantor)* | 3 | 2.384.950,00 | 0,00 |
| `6301` Gaji Karyawan Umum | 13 | 127.977.565,00 | 0,00 |
| `6302` Tunjangan Karyawan *(THR 2026)* | 1 | 11.553.000,00 | 0,00 |
| `6305` Training & Pengembangan *(Uang Saku Training & Magang)* | 5 | 4.950.000,00 | 0,00 |
| `6402` Retribusi & Perizinan *(NIB, Domisili, Perizinan + 1 refund)* | 4 | 3.200.000,00 | 1.500.000,00 |
| `6502` Kontribusi BPJS Ketenagakerjaan | 3 | 2.625.852,00 | 0,00 |
| `6503` Program Kesejahteraan Karyawan *(Seragam, Kaos Polo, Paspor)* | 5 | 4.085.000,00 | 0,00 |
| `6610` Biaya Miscellaneous *(Reimburse umum, makan tamu China, tiket pesawat)* | 11 | 44.714.566,00 | 0,00 |

## 3. Daftar Lengkap Seluruh 425 Transaksi Terpetakan

| Kode | Excel Row | Tanggal | Tipe | Akun Utama (Kas/Bank) | Akun Lawan (Target COA) | Nominal (Rp) | Keterangan |
| :--- | :---: | :---: | :--- | :--- | :--- | ---: | :--- |
| `KAS26-0001` | R004 | 2025-12-23 | KAS_MASUK | `1116` Petty Cash | `2142` Hutang kepada Pemilik | 1.920.786.312,00 | [Excel R4 / MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 10% |
| `KAS26-0002` | R005 | 2026-01-22 | KAS_KELUAR | `1116` Petty Cash | `1221` Mesin & Peralatan - Harga Perolehan | 1.920.786.312,00 | [Excel R5 / PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 10% |
| `BNK26-0001` | R006 | 2026-01-24 | BANK_MASUK | `1113` Bank Lain - Efendi | `3100` Modal Awal Pemilik | 5.100.000,00 | [Excel R6 / MODAL] SETORAN MODAL KE REKENING FENDI |
| `BNK26-0002` | R007 | 2026-01-25 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 750,00 | [Excel R7 / BIAYA ADMIN] BIAYA NOTIFIKASI SMS |
| `KAS26-0003` | R008 | 2026-01-30 | KAS_MASUK | `1116` Petty Cash | `2142` Hutang kepada Pemilik | 3.000.000.000,00 | [Excel R8 / MODAL] MODAL PINJAMAN INDIGAMA - BAYAR ZONTEN 20% |
| `KAS26-0004` | R009 | 2026-01-30 | KAS_KELUAR | `1116` Petty Cash | `1221` Mesin & Peralatan - Harga Perolehan | 3.000.000.000,00 | [Excel R9 / PEMBAYARAN] PEMBAYARAN MESIN ZONTEN 20% |
| `BNK26-0003` | R010 | 2026-01-30 | BANK_MASUK | `1113` Bank Lain - Efendi | `3100` Modal Awal Pemilik | 200.000.000,00 | [Excel R10 / MODAL] SETORAN MODAL KE REKENING FENDI |
| `BNK26-0004` | R011 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `5308` Biaya Subcont Produksi | 917.500,00 | [Excel R11 / PEMBAYARAN] JASA PRODUKSI ETIKET JB |
| `BNK26-0005` | R012 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R12 / BIAYA ADMIN] ADMIN BANK JASA PRODUKSI |
| `BNK26-0006` | R013 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `5308` Biaya Subcont Produksi | 822.000,00 | [Excel R13 / PEMBELIAN] PISAU INNER KENKO |
| `BNK26-0007` | R014 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R14 / BIAYA ADMIN] ADMIN BANK PISAU INNER KENKOU |
| `BNK26-0008` | R015 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6104` Biaya Pengiriman Barang | 22.000,00 | [Excel R15 / PEMBAYARAN] ONGKIR PISAU |
| `BNK26-0009` | R016 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R16 / BIAYA ADMIN] ADMIN BANK ONGKIR PISAU |
| `BNK26-0010` | R017 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `1251` Bangunan Dalam Pembangunan | 6.119.000,00 | [Excel R17 / PEMBELIAN] PEMBANGUNAN NOGOSARI |
| `BNK26-0011` | R018 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R18 / BIAYA ADMIN] PEMBANGUNAN NOGOSARI |
| `BNK26-0012` | R019 | 2026-01-31 | BANK_KELUAR | `1113` Bank Lain - Efendi | `1131` Piutang Karyawan | 1.000.000,00 | [Excel R19 / PEMBAYARAN] BON P.HARIYANTO |
| `BNK26-0013` | R022 | 2026-02-02 | BANK_KELUAR | `1113` Bank Lain - Efendi | `5308` Biaya Subcont Produksi | 3.900.000,00 | [Excel R22 / PEMBAYARAN] ETIKET MARBOL |
| `BNK26-0014` | R023 | 2026-02-02 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R23 / BIAYA ADMIN] ETIKET MARBOL |
| `BNK26-0015` | R024 | 2026-02-02 | BANK_KELUAR | `1113` Bank Lain - Efendi | `5308` Biaya Subcont Produksi | 13.485.000,00 | [Excel R24 / PEMBAYARAN] PELUNASAN CTP MARBOL |
| `BNK26-0016` | R025 | 2026-02-02 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 6.500,00 | [Excel R25 / BIAYA ADMIN] ADMIN BANK PELUNASAN CTP MARBOL |
| `BNK26-0017` | R026 | 2026-02-02 | BANK_KELUAR | `1113` Bank Lain - Efendi | `5308` Biaya Subcont Produksi | 2.372.469,00 | [Excel R26 / PEMBAYARAN] JASA LEM |
| `BNK26-0018` | R027 | 2026-02-02 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 6.500,00 | [Excel R27 / BIAYA ADMIN] ADMIN BANK JASA LEM |
| `BNK26-0019` | R028 | 2026-02-02 | BANK_KELUAR | `1113` Bank Lain - Efendi | `1116` Petty Cash | 3.000.000,00 | [Excel R28 / PETTYCASH] SETORAN MODAL PETYCASH YAUL |
| `BNK26-0020` | R029 | 2026-02-03 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R29 / BIAYA ADMIN] SETORAN MODAL PETYCASH YAUL |
| `BNK26-0021` | R030 | 2026-02-04 | BANK_MASUK | `1114` Bank Lain - Giro | `3100` Modal Awal Pemilik | 1.000.000.000,00 | [Excel R30 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0022` | R031 | 2026-02-05 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6209` Bahan Bakar dan Etoll Kendaraan | 250.000,00 | [Excel R31 / OPERASIONAL] BENSIN PERTALITE |
| `BNK26-0023` | R032 | 2026-02-05 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R32 / BIAYA ADMIN] BENSIN PERTALITE |
| `BNK26-0024` | R033 | 2026-02-05 | BANK_KELUAR | `1113` Bank Lain - Efendi | `5308` Biaya Subcont Produksi | 11.948.000,00 | [Excel R33 / PEMBAYARAN] JASA CETAK ET KENKO |
| `BNK26-0025` | R034 | 2026-02-05 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R34 / BIAYA ADMIN] JASA CETAK ET KENKO |
| `BNK26-0026` | R035 | 2026-02-05 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6610` Biaya Miscellaneous | 500.000,00 | [Excel R35 / PEMBAYARAN] REIMBUERSE OPERASIONAL |
| `BNK26-0027` | R036 | 2026-02-05 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R36 / BIAYA ADMIN] REIMBUERSE OPERASIONAL |
| `BNK26-0028` | R037 | 2026-02-05 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 1.500,00 | [Excel R37 / BIAYA ADMIN] SMS NOTIFIKASI |
| `BNK26-0029` | R038 | 2026-02-05 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 750,00 | [Excel R38 / BIAYA ADMIN] SMS NOTIFIKASI SEJUMLAH 1 NOTIFIKASI |
| `BNK26-0030` | R039 | 2026-02-06 | BANK_KELUAR | `1113` Bank Lain - Efendi | `2111` Utang Usaha - Bahan Baku IDR | 136.664.000,00 | [Excel R39 / PEMBELIAN] HUTANG BAHAN BAKU |
| `BNK26-0031` | R040 | 2026-02-06 | BANK_MASUK | `1113` Bank Lain - Efendi | `1131` Piutang Karyawan | 30.000,00 | [Excel R40 / PENERIMAAN] BAYAR  UTANG |
| `BNK26-0032` | R041 | 2026-02-06 | BANK_MASUK | `1113` Bank Lain - Efendi | `1131` Piutang Karyawan | 270.000,00 | [Excel R41 / PENERIMAAN] BAYAR  UTANG |
| `BNK26-0033` | R042 | 2026-02-06 | BANK_KELUAR | `1113` Bank Lain - Efendi | `2133` Biaya Lainnya Terutang | 250.000,00 | [Excel R42 / PEMBAYARAN] BAYAR HUTANG EXTERNAL |
| `BNK26-0034` | R043 | 2026-02-06 | BANK_KELUAR | `1113` Bank Lain - Efendi | `5308` Biaya Subcont Produksi | 24.014.088,00 | [Excel R43 / PEMBAYARAN] ET MARBOL |
| `BNK26-0035` | R044 | 2026-02-06 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R44 / BIAYA ADMIN] ADMIN BANK ET MARBOL |
| `BNK26-0036` | R045 | 2026-02-06 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 275.000,00 | [Excel R45 / PEMBAYARAN] CEK PT.TTC |
| `BNK26-0037` | R046 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `1211` Tanah & Bangunan - Harga Perolehan | 168.435.715,00 | [Excel R46 / PEMBAYARAN] CICILAN GUDANG K/ 1 |
| `BNK26-0038` | R047 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R47 / PEMBAYARAN] CICILAN GUDANG K/ 1 |
| `BNK26-0039` | R048 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `1211` Tanah & Bangunan - Harga Perolehan | 188.435.715,00 | [Excel R48 / PEMBAYARAN] CICILAN GUDANG K/ 2 |
| `BNK26-0040` | R049 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R49 / PEMBAYARAN] CICILAN GUDANG K/ 2 |
| `BNK26-0041` | R050 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `1211` Tanah & Bangunan - Harga Perolehan | 188.435.715,00 | [Excel R50 / PEMBAYARAN] CICILAN GUDANG K/ 3 |
| `BNK26-0042` | R051 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R51 / PEMBAYARAN] CICILAN GUDANG K/ 3 |
| `BNK26-0043` | R052 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `1251` Bangunan Dalam Pembangunan | 3.047.500,00 | [Excel R52 / PEMBAYARAN] PEMBANGUNAN NOGOSARI |
| `BNK26-0044` | R053 | 2026-02-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R53 / PEMBAYARAN] PEMBANGUNAN NOGOSARI |
| `BNK26-0045` | R054 | 2026-02-09 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 2.250,00 | [Excel R54 / BIAYA ADMIN] BIAYA SMS NOTIFIKASI |
| `BNK26-0046` | R055 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 1.000.000,00 | [Excel R55 / PETTYCASH] TARIK TUNAI - PETTY CASH YAUL |
| `BNK26-0047` | R056 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 1.000.000,00 | [Excel R56 / PETTYCASH] TARIK TUNAI - PETTY CASH YAUL |
| `BNK26-0048` | R057 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 2.000.000,00 | [Excel R57 / PETTYCASH] TARIK TUNAI - PETTY CASH YAUL |
| `BNK26-0049` | R058 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 2.000.000,00 | [Excel R58 / PETTYCASH] TARIK TUNAI - PETTY CASH YAUL |
| `BNK26-0050` | R059 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 2.000.000,00 | [Excel R59 / PETTYCASH] TARIK TUNAI - PETTY CASH YAUL |
| `BNK26-0051` | R060 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 500,00 | [Excel R60 / BIAYA ADMIN] FEE INFO MUTASI ATM |
| `BNK26-0052` | R061 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 2.000.000,00 | [Excel R61 / PETTYCASH] TARIK TUNAI - PETTY CASH YAUL |
| `BNK26-0053` | R062 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 707.000,00 | [Excel R62 / PEMBAYARAN] JASA PLONG |
| `BNK26-0054` | R063 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 6.500,00 | [Excel R63 / PEMBAYARAN] JASA PLONG |
| `BNK26-0055` | R064 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `6610` Biaya Miscellaneous | 745.000,00 | [Excel R64 / PEMBAYARAN] REIMBURSE SHERLY |
| `BNK26-0056` | R065 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 6.500,00 | [Excel R65 / BIAYA ADMIN] ADMIN REIMBURSE SHER;Y |
| `BNK26-0057` | R066 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 3.000.000,00 | [Excel R66 / PETTYCASH] PETTY CASH YAUL |
| `BNK26-0058` | R067 | 2026-02-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 6.500,00 | [Excel R67 / PETTYCASH] PETTY CASH YAUL |
| `BNK26-0059` | R068 | 2026-02-12 | BANK_MASUK | `1114` Bank Lain - Giro | `4540` Pendapatan Lain-lain | 9.550.000,00 | [Excel R68 / PENJUALAN] UANG MASUK - PENJUALAN TEBU |
| `BNK26-0060` | R069 | 2026-02-12 | BANK_MASUK | `1114` Bank Lain - Giro | `4540` Pendapatan Lain-lain | 4.800.000,00 | [Excel R69 / PENJUALAN] UANG MASUK - PENJUALAN TEBU |
| `BNK26-0061` | R070 | 2026-02-12 | BANK_MASUK | `1114` Bank Lain - Giro | `4540` Pendapatan Lain-lain | 100.000,00 | [Excel R70 / PENJUALAN] UANG MASUK - PENJUALAN TEBU |
| `BNK26-0062` | R071 | 2026-02-13 | BANK_KELUAR | `1113` Bank Lain - Efendi | `3500` Prive / Pengambilan Pemilik | 43.443,00 | [Excel R71 / PEMBAYARAN] PENGEMBALIAN SISA SALDO |
| `BNK26-0063` | R072 | 2026-02-13 | BANK_KELUAR | `1113` Bank Lain - Efendi | `6214` Biaya Administrasi Bank | 50.000,00 | [Excel R72 / BIAYA ADMIN] UANG TETAP |
| `BNK26-0064` | R073 | 2026-02-14 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 1.000.000,00 | [Excel R73 / PETTYCASH] PETTY CASH YAUL |
| `BNK26-0065` | R074 | 2026-02-14 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R74 / PETTYCASH] PETTY CASH YAUL |
| `BNK26-0066` | R075 | 2026-02-14 | BANK_KELUAR | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 1.920.000,00 | [Excel R75 / PEMBAYARAN] KLIS EMBOS PDW 88 |
| `BNK26-0067` | R076 | 2026-02-14 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R76 / PEMBAYARAN] KLIS EMBOS PDW 88 |
| `BNK26-0068` | R077 | 2026-02-17 | BANK_KELUAR | `1114` Bank Lain - Giro | `1251` Bangunan Dalam Pembangunan | 850.000,00 | [Excel R77 / PEMBAYARAN] JASA DESIGN NOGOSARI |
| `BNK26-0069` | R078 | 2026-02-17 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R78 / PEMBAYARAN] JASA DESIGN NOGOSARI |
| `BNK26-0070` | R079 | 2026-02-17 | BANK_KELUAR | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 1.788.000,00 | [Excel R79 / PEMBAYARAN] PISAU ET PDW 88 |
| `BNK26-0071` | R080 | 2026-02-18 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 7.000.000,00 | [Excel R80 / PETTYCASH] PETTY CASH JUAN |
| `BNK26-0072` | R081 | 2026-02-18 | BANK_KELUAR | `1114` Bank Lain - Giro | `6610` Biaya Miscellaneous | 6.367.136,00 | [Excel R81 / PEMBAYARAN] REIMBURSE MBAK NARNI |
| `BNK26-0073` | R082 | 2026-02-18 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R82 / PEMBAYARAN] REIMBURSE MBAK NARNI |
| `BNK26-0074` | R083 | 2026-02-19 | BANK_KELUAR | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 5.661.968,00 | [Excel R83 / PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD |
| `BNK26-0075` | R084 | 2026-02-19 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R84 / BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD |
| `BNK26-0076` | R085 | 2026-02-19 | BANK_KELUAR | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 5.661.968,00 | [Excel R85 / PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD |
| `BNK26-0077` | R086 | 2026-02-19 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R86 / BIAYA ADMIN] SPOT UV & HOT PRINT ET JB BOLD |
| `BNK26-0078` | R087 | 2026-02-19 | BANK_KELUAR | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 895.250,00 | [Excel R87 / PEMBAYARAN] JASA PISAU PLONG INNER PDW 88 |
| `BNK26-0079` | R088 | 2026-02-19 | BANK_KELUAR | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 895.250,00 | [Excel R88 / PEMBAYARAN] JASA PISAU PLONG INNER PDW 88 |
| `BNK26-0080` | R089 | 2026-02-20 | BANK_MASUK | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 895.250,00 | [Excel R89 / PEMBAYARAN] JASA PISAU PLONG INNER PDW 88 |
| `BNK26-0081` | R090 | 2026-02-20 | BANK_MASUK | `1114` Bank Lain - Giro | `5308` Biaya Subcont Produksi | 5.661.968,00 | [Excel R90 / PEMBAYARAN] SPOT UV & HOT PRINT ET JB BOLD |
| `BNK26-0082` | R091 | 2026-02-20 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 4.000.000,00 | [Excel R91 / PETTYCASH] PETTY CASH YAUL |
| `BNK26-0083` | R092 | 2026-02-20 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R92 / PETTYCASH] PETTY CASH YAUL |
| `BNK26-0084` | R093 | 2026-02-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `1251` Bangunan Dalam Pembangunan | 7.914.000,00 | [Excel R93 / PEMBELIAN] PEMBANGUNAN NOGOSARI |
| `BNK26-0085` | R094 | 2026-02-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R94 / PEMBELIAN] PEMBANGUNAN NOGOSARI |
| `BNK26-0086` | R095 | 2026-02-25 | BANK_MASUK | `1114` Bank Lain - Giro | `4510` Pendapatan Bunga | 175.051,00 | [Excel R95 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0087` | R096 | 2026-02-25 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 35.010,00 | [Excel R96 / BIAYA ADMIN] TAX |
| `BNK26-0088` | R097 | 2026-02-26 | BANK_KELUAR | `1114` Bank Lain - Giro | `6104` Biaya Pengiriman Barang | 2.900.000,00 | [Excel R97 / PEMBELIAN] EXPEDISI NOGOSARI |
| `BNK26-0089` | R098 | 2026-02-26 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R98 / BIAYA ADMIN] EXPEDISI NOGOSARI |
| `BNK26-0090` | R099 | 2026-02-26 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 7.000.000,00 | [Excel R99 / PETTYCASH] PETTYCASH JUAN |
| `BNK26-0091` | R100 | 2026-02-27 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 10.953.700,00 | [Excel R100 / PENJUALAN] ETIKET PDW 88 SKM 12 |
| `BNK26-0092` | R101 | 2026-02-27 | BANK_KELUAR | `1114` Bank Lain - Giro | `6610` Biaya Miscellaneous | 2.095.756,00 | [Excel R101 / PEMBAYARAN] REIMBURSE SHERLY |
| `BNK26-0093` | R102 | 2026-02-27 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R102 / PEMBAYARAN] REIMBURSE SHERLY |
| `BNK26-0094` | R103 | 2026-02-28 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 24.000.000,00 | [Excel R103 / PENJUALAN] ETIKET FLORENT |
| `BNK26-0095` | R104 | 2026-02-28 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 6.890.000,00 | [Excel R104 / PENJUALAN] INNER JB |
| `BNK26-0096` | R105 | 2026-02-28 | BANK_KELUAR | `1114` Bank Lain - Giro | `6301` Gaji Karyawan Umum | 13.476.400,00 | [Excel R105 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0097` | R108 | 2026-03-04 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 4.000.000,00 | [Excel R108 / PETTYCASH] PETTYCASH YAUL |
| `BNK26-0098` | R109 | 2026-03-04 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R109 / PETTYCASH] PETTYCASH YAUL |
| `BNK26-0099` | R110 | 2026-03-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `6402` Retribusi & Perizinan | 1.500.000,00 | [Excel R110 / PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA |
| `BNK26-0100` | R111 | 2026-03-07 | BANK_KELUAR | `1114` Bank Lain - Giro | `6402` Retribusi & Perizinan | 1.500.000,00 | [Excel R111 / PEMBAYARAN] BIAYA PENGURUSAN NIB EXTRA |
| `BNK26-0101` | R112 | 2026-03-07 | BANK_MASUK | `1111` Bank BRI - Operasional | `6402` Retribusi & Perizinan | 1.500.000,00 | [Excel R112 / PENERIMAAN] REFUND |
| `KAS26-0005` | R113 | 2026-03-08 | KAS_MASUK | `1116` Petty Cash | `2142` Hutang kepada Pemilik | 34.082.882,00 | [Excel R113 / MODAL] MODAL PINJAMAN PEMBELIAN KURSI, MEJA KANTOR |
| `KAS26-0006` | R114 | 2026-03-08 | KAS_KELUAR | `1116` Petty Cash | `1241` Furniture & Fixtures - Harga Perolehan | 34.082.882,00 | [Excel R114 / PEMBAYARAN] PEMBELIAN KURSI, MEJA DAN LEMARI KANTOR |
| `BNK26-0102` | R115 | 2026-03-13 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 4.650.000,00 | [Excel R115 / PENJUALAN] ETIKET PINANG MAS SKT 16 |
| `BNK26-0103` | R116 | 2026-03-13 | BANK_KELUAR | `1114` Bank Lain - Giro | `1221` Mesin & Peralatan - Harga Perolehan | 152.500.000,00 | [Excel R116 / PEMBAYARAN] DP INSTALASI LISTRIK |
| `BNK26-0104` | R117 | 2026-03-13 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R117 / PEMBAYARAN] DP INSTALASI LISTRIK |
| `BNK26-0105` | R118 | 2026-03-14 | BANK_KELUAR | `1114` Bank Lain - Giro | `6610` Biaya Miscellaneous | 100.000,00 | [Excel R118 / PEMBAYARAN] REMBUERSE PAK HERI |
| `BNK26-0106` | R119 | 2026-03-14 | BANK_KELUAR | `1114` Bank Lain - Giro | `6302` Tunjangan Karyawan | 11.553.000,00 | [Excel R119 / PEMBAYARAN] TUNJANGAN H R 2026 |
| `BNK26-0107` | R120 | 2026-03-16 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 5.692.500,00 | [Excel R120 / PENJUALAN] AMBRI DAFI |
| `BNK26-0108` | R121 | 2026-03-20 | BANK_MASUK | `1111` Bank BRI - Operasional | `4510` Pendapatan Bunga | 17.152,00 | [Excel R121 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0109` | R122 | 2026-03-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 3.430,00 | [Excel R122 / BIAYA ADMIN] TAX |
| `BNK26-0110` | R123 | 2026-03-25 | BANK_MASUK | `1114` Bank Lain - Giro | `4510` Pendapatan Bunga | 118.383,00 | [Excel R123 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0111` | R124 | 2026-03-25 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 23.677,00 | [Excel R124 / BIAYA ADMIN] TAX |
| `BNK26-0112` | R125 | 2026-03-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 13.508.495,00 | [Excel R125 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0113` | R128 | 2026-04-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6205` Biaya Profesional | 8.130.000,00 | [Excel R128 / PEMBAYARAN] PEMBUATAN AKTA |
| `BNK26-0114` | R129 | 2026-04-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R129 / BIAYA ADMIN] ADMIN BANK |
| `BNK26-0115` | R130 | 2026-04-05 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6610` Biaya Miscellaneous | 2.865.500,00 | [Excel R130 / PEMBAYARAN] REIMBURSE |
| `BNK26-0116` | R131 | 2026-04-05 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R131 / BIAYA ADMIN] ADMIN BANK |
| `BNK26-0117` | R132 | 2026-04-07 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 10.000.000,00 | [Excel R132 / PENJUALAN] ETIKET APPLE |
| `BNK26-0118` | R133 | 2026-04-14 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 7.397.500,00 | [Excel R133 / PENJUALAN] AMBRI DAFI |
| `BNK26-0119` | R134 | 2026-04-15 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 1.350.000,00 | [Excel R134 / PENJUALAN] ETIKET BARBARA |
| `BNK26-0120` | R135 | 2026-04-15 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 20.000.000,00 | [Excel R135 / PENJUALAN] ETIKET BARBARA |
| `BNK26-0121` | R136 | 2026-04-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 10.000,00 | [Excel R136 / BIAYA ADMIN] CEK REKENING |
| `BNK26-0122` | R137 | 2026-04-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 1.250.000,00 | [Excel R137 / PEMBAYARAN] REIUMBURSE DP KACA FILM 50% |
| `BNK26-0123` | R138 | 2026-04-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R138 / BIAYA ADMIN] REIUMBURSE DP KACA FILM 50% |
| `BNK26-0124` | R139 | 2026-04-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 1.250.000,00 | [Excel R139 / PEMBAYARAN] PELUNASAN KACA FILM |
| `BNK26-0125` | R140 | 2026-04-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `6108` Komisi Marketing / Referral | 1.138.000,00 | [Excel R140 / PEMBAYARAN] KOMISI PAK GUNG |
| `BNK26-0126` | R141 | 2026-04-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `6108` Komisi Marketing / Referral | 239.040,00 | [Excel R141 / PEMBAYARAN] KOMISI PAK HERI |
| `BNK26-0127` | R142 | 2026-04-20 | BANK_MASUK | `1111` Bank BRI - Operasional | `4510` Pendapatan Bunga | 28.415,00 | [Excel R142 / PENERIMAAN] INTERSENT ON ACCOUNT |
| `BNK26-0128` | R143 | 2026-04-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 5.683,00 | [Excel R143 / BIAYA ADMIN] TAX |
| `BNK26-0129` | R144 | 2026-04-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `6216` Perlengkapan dan Furniture | 1.160.000,00 | [Excel R144 / PEMBELIAN] REIMBURSE PERALATAN RUMAH TANGGA |
| `BNK26-0130` | R145 | 2026-04-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R145 / BIAYA ADMIN] REIMBURSE PERALATAN RUMAH TANGGA |
| `BNK26-0131` | R146 | 2026-04-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `6207` Telepon & Internet | 664.194,00 | [Excel R146 / OPERASIONAL] TLKM |
| `BNK26-0132` | R147 | 2026-04-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.800,00 | [Excel R147 / BIAYA ADMIN] TLKM |
| `BNK26-0133` | R148 | 2026-04-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `6212` Iuran & Keanggotaan | 10.032.000,00 | [Excel R148 / PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN TTC |
| `BNK26-0134` | R149 | 2026-04-21 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R149 / BIAYA ADMIN] IURAN KEAMANAN DAN KEBERSIHAN TTC |
| `BNK26-0135` | R150 | 2026-04-22 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 10.000.000,00 | [Excel R150 / PENJUALAN] ETIKET DAFI SKT 16 |
| `KAS26-0007` | R151 | 2026-04-22 | KAS_MASUK | `1116` Petty Cash | `2142` Hutang kepada Pemilik | 160.000.000,00 | [Excel R151 / MODAL] MODAL PINJAMAN INDIGAMA |
| `KAS26-0008` | R152 | 2026-04-22 | KAS_KELUAR | `1116` Petty Cash | `1221` Mesin & Peralatan - Harga Perolehan | 160.000.000,00 | [Excel R152 / PEMBAYARAN] LISTRIK - PEMBAYARAN DP TRAFO DAN CUBICLE |
| `BNK26-0136` | R153 | 2026-04-25 | BANK_MASUK | `1114` Bank Lain - Giro | `4510` Pendapatan Bunga | 91.027,00 | [Excel R153 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0137` | R154 | 2026-04-25 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 18.205,00 | [Excel R154 / BIAYA ADMIN] TAX |
| `BNK26-0138` | R155 | 2026-04-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 15.903.856,00 | [Excel R155 / PEMBELIAN] MATERIAL KELENGKAPAN PT |
| `BNK26-0139` | R156 | 2026-04-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R156 / BIAYA ADMIN] ADMIN BANK |
| `BNK26-0140` | R157 | 2026-04-27 | BANK_KELUAR | `1114` Bank Lain - Giro | `6212` Iuran & Keanggotaan | 10.032.000,00 | [Excel R157 / PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN |
| `BNK26-0141` | R158 | 2026-04-27 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R158 / PEMBAYARAN] IURAN KEAMANAN DAN KEBERSIHAN |
| `BNK26-0142` | R159 | 2026-04-27 | BANK_KELUAR | `1114` Bank Lain - Giro | `6203` Listrik & Air Kantor | 103.000,00 | [Excel R159 / OPERASIONAL] REIMBURSE TOKEN LISTRIK |
| `BNK26-0143` | R160 | 2026-04-27 | BANK_KELUAR | `1114` Bank Lain - Giro | `6209` Bahan Bakar dan Etoll Kendaraan | 370.000,00 | [Excel R160 / OPERASIONAL] REIMBURSE E TOL DAN KONUMSI |
| `BNK26-0144` | R161 | 2026-04-27 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R161 / OPERASIONAL] REIMBURSE E TOL DAN KONUMSI |
| `BNK26-0145` | R162 | 2026-04-28 | BANK_KELUAR | `1114` Bank Lain - Giro | `6216` Perlengkapan dan Furniture | 116.000,00 | [Excel R162 / PEMBELIAN] PERALATAN RUMAH TANGGA |
| `BNK26-0146` | R163 | 2026-04-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 13.608.490,00 | [Excel R163 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0147` | R164 | 2026-04-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 2.280.000,00 | [Excel R164 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0148` | R165 | 2026-04-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R165 / BIAYA ADMIN] ADMIN BANK |
| `BNK26-0149` | R166 | 2026-04-30 | BANK_KELUAR | `1114` Bank Lain - Giro | `6209` Bahan Bakar dan Etoll Kendaraan | 200.000,00 | [Excel R166 / OPERASIONAL] REIMBURSE E TOL |
| `BNK26-0150` | R167 | 2026-04-30 | BANK_KELUAR | `1114` Bank Lain - Giro | `6209` Bahan Bakar dan Etoll Kendaraan | 126.000,00 | [Excel R167 / OPERASIONAL] REIMBURSE UANG TRANSPORT |
| `BNK26-0151` | R168 | 2026-04-30 | BANK_KELUAR | `1114` Bank Lain - Giro | `6216` Perlengkapan dan Furniture | 1.108.950,00 | [Excel R168 / PEMBELIAN] REIMBUERSE PERALATAN RUMAH TANGGA |
| `BNK26-0152` | R169 | 2026-04-30 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R169 / BIAYA ADMIN] ADMIN BANK REIMBUERSE PERALATAN RUMAH TANGGA |
| `BNK26-0153` | R170 | 2026-04-30 | BANK_KELUAR | `1114` Bank Lain - Giro | `6209` Bahan Bakar dan Etoll Kendaraan | 200.000,00 | [Excel R170 / OPERASIONAL] REIMBUERSE E TOL |
| `BNK26-0154` | R171 | 2026-04-30 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R171 / BIAYA ADMIN] ADMIN BANK REIMBUERSE E TOL |
| `BNK26-0155` | R174 | 2026-05-05 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 12.766.000,00 | [Excel R174 / PENJUALAN] CYLINDER ET BARLBARA |
| `BNK26-0156` | R175 | 2026-05-08 | BANK_KELUAR | `1114` Bank Lain - Giro | `6207` Telepon & Internet | 1.254.300,00 | [Excel R175 / OPERASIONAL] TAGIHAN WIFI TELKOM |
| `BNK26-0157` | R176 | 2026-05-08 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.800,00 | [Excel R176 / BIAYA ADMIN] ADMIN BANK TELOKM |
| `BNK26-0158` | R177 | 2026-05-09 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 5.850.000,00 | [Excel R177 / PENJUALAN] CTP BARLBARA |
| `BNK26-0159` | R178 | 2026-05-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6203` Listrik & Air Kantor | 100.000,00 | [Excel R178 / PEMBELIAN] TOKEN LISTRIK |
| `BNK26-0160` | R179 | 2026-05-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 3.000,00 | [Excel R179 / BIAYA ADMIN] ADMIN TOKEN LISTRIK |
| `BNK26-0161` | R180 | 2026-05-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `6203` Listrik & Air Kantor | 102.000,00 | [Excel R180 / OPERASIONAL] PEMBAYARAN PDAM |
| `BNK26-0162` | R181 | 2026-05-13 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6503` Program Kesejahteraan Karyawan | 650.000,00 | [Excel R181 / PEMBAYARAN] PEMBUATAN PASPOR KARYAWAN |
| `BNK26-0163` | R182 | 2026-05-13 | BANK_KELUAR | `1114` Bank Lain - Giro | `1116` Petty Cash | 10.000.000,00 | [Excel R182 / PETTYCASH] PETYCASH INTERNAL INAM |
| `BNK26-0164` | R183 | 2026-05-13 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R183 / BIAYA ADMIN] ADMIN BANK PETYCASH INTERNAL |
| `BNK26-0165` | R184 | 2026-05-13 | BANK_KELUAR | `1114` Bank Lain - Giro | `6205` Biaya Profesional | 4.500.000,00 | [Excel R184 / PEMBAYARAN] PEMBUATAN AKTA PT |
| `BNK26-0166` | R185 | 2026-05-14 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 50.096.250,00 | [Excel R185 / PEMBELIAN] LISTRIK - DP MATERIAL PENDUKUNG TRAVO SUTM |
| `BNK26-0167` | R186 | 2026-05-14 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R186 / PEMBELIAN] ADMIN BANK DP MATERIAL PENDUKUNG TRAVO SUTM |
| `BNK26-0168` | R187 | 2026-05-15 | BANK_KELUAR | `1114` Bank Lain - Giro | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 2.100.000,00 | [Excel R187 / PEMBAYARAN] JASA PENGECATAN KANTOR |
| `BNK26-0169` | R188 | 2026-05-15 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R188 / BIAYA ADMIN] ADMIN BANK JASA PENGECATAN KANTOR |
| `BNK26-0170` | R189 | 2026-05-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 1.500.000,00 | [Excel R189 / PEMBAYARAN] DP DESIGN RUANGAN PARTISI |
| `BNK26-0171` | R190 | 2026-05-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R190 / BIAYA ADMIN] ADMIN BANK DP DESIGN RUANGAN |
| `BNK26-0172` | R191 | 2026-05-20 | BANK_MASUK | `1111` Bank BRI - Operasional | `4510` Pendapatan Bunga | 29.702,00 | [Excel R191 / PENERIMAAN] INTERSET ON ACCOUNT |
| `BNK26-0173` | R192 | 2026-05-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 5.940,00 | [Excel R192 / BIAYA ADMIN] TAX |
| `BNK26-0174` | R193 | 2026-05-20 | BANK_KELUAR | `1114` Bank Lain - Giro | `1112` Bank BCA - Operasional | 2.500.000,00 | [Excel R193 / PEMBAYARAN] BUKA REKENING BCA (Transfer Antar-Bank BRI Giro -> BCA, gabung R193 & R195) |
| `BNK26-0175` | R194 | 2026-05-20 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R194 / BIAYA ADMIN] ADMIN BANK BUKA REKENING |
| `BNK26-0176` | R196 | 2026-05-20 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 50.000,00 | [Excel R196 / PEMBELIAN] POT BY KEY |
| `BNK26-0177` | R197 | 2026-05-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 2.600.000,00 | [Excel R197 / PEMBELIAN] PRINTER EPSON L3251 |
| `BNK26-0178` | R198 | 2026-05-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R198 / BIAYA ADMIN] ADMIN BANK PRINTER EPSON L3251 |
| `BNK26-0179` | R199 | 2026-05-25 | BANK_MASUK | `1114` Bank Lain - Giro | `4510` Pendapatan Bunga | 74.708,00 | [Excel R199 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0180` | R200 | 2026-05-25 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 14.942,00 | [Excel R200 / BIAYA ADMIN] TAX |
| `BNK26-0181` | R201 | 2026-05-26 | BANK_KELUAR | `1114` Bank Lain - Giro | `1111` Bank BRI - Operasional | 140.000.000,00 | [Excel R201 / PEMBAYARAN] PEMINDAHAN DANA (Transfer Antar-Bank BRI Giro -> BRI Tabungan, gabung R201 & R202) |
| `BNK26-0182` | R203 | 2026-05-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 133.404.000,00 | [Excel R203 / PEMBELIAN] LISTRIK - DP MATERIAL LVMDP |
| `BNK26-0183` | R204 | 2026-05-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R204 / BIAYA ADMIN] ADMIN BANK DP MATERIAL LVMDP |
| `BNK26-0184` | R205 | 2026-05-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 11.714.000,00 | [Excel R205 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0185` | R206 | 2026-05-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 3.496.000,00 | [Excel R206 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0186` | R207 | 2026-05-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R207 / BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN |
| `BNK26-0187` | R208 | 2026-05-31 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 30.000,00 | [Excel R208 / BIAYA ADMIN] ADMIN BANK BCA |
| `BNK26-0188` | R211 | 2026-06-01 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 30.000,00 | [Excel R211 / BIAYA ADMIN] ADMIN BANK BCA |
| `BNK26-0189` | R212 | 2026-06-03 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 27.500.000,00 | [Excel R212 / PENJUALAN] ETIKET APPLE |
| `BNK26-0190` | R213 | 2026-06-05 | BANK_MASUK | `1112` Bank BCA - Operasional | `4101` Penjualan Barang Jadi - IDR | 10.953.700,00 | [Excel R213 / PENJUALAN] PELUNASAN ETIKET DAN INNER PDW 88 |
| `BNK26-0191` | R214 | 2026-06-05 | BANK_MASUK | `1112` Bank BCA - Operasional | `4101` Penjualan Barang Jadi - IDR | 15.111.750,00 | [Excel R214 / PENJUALAN] PELUNASAN ETIKET FLORENT |
| `BNK26-0192` | R215 | 2026-06-06 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 30.000,00 | [Excel R215 / PEMBELIAN] BIAYA KARTU BDC |
| `BNK26-0193` | R216 | 2026-06-06 | BANK_MASUK | `1112` Bank BCA - Operasional | `4101` Penjualan Barang Jadi - IDR | 17.900.000,00 | [Excel R216 / PENJUALAN] PELUNASAN ET BLUESEA |
| `BNK26-0194` | R217 | 2026-06-09 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 1.300.000.000,00 | [Excel R217 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0195` | R218 | 2026-06-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1211` Tanah & Bangunan - Harga Perolehan | 188.462.858,00 | [Excel R218 / PEMBAYARAN] CICILAN GUDANG K/5 |
| `BNK26-0196` | R219 | 2026-06-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R219 / BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/5 |
| `BNK26-0197` | R220 | 2026-06-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1211` Tanah & Bangunan - Harga Perolehan | 188.435.000,00 | [Excel R220 / PEMBAYARAN] CICILAN GUDANG K/6 |
| `BNK26-0198` | R221 | 2026-06-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R221 / BIAYA ADMIN] ADMIN BANK CICILAN GUDANG K/6 |
| `BNK26-0199` | R222 | 2026-06-11 | BANK_KELUAR | `1114` Bank Lain - Giro | `6108` Komisi Marketing / Referral | 357.912,00 | [Excel R222 / PEMBAYARAN] FEE PAK HERI |
| `BNK26-0200` | R223 | 2026-06-12 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6503` Program Kesejahteraan Karyawan | 650.000,00 | [Excel R223 / PEMBAYARAN] PEMBUATAN PASPOR |
| `BNK26-0201` | R224 | 2026-06-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `1251` Bangunan Dalam Pembangunan | 1.125.000,00 | [Excel R224 / PEMBELIAN] MATERIAL KAYU |
| `BNK26-0202` | R225 | 2026-06-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R225 / BIAYA ADMIN] BIAYA ADMIN MATERIAL KAYU |
| `BNK26-0203` | R226 | 2026-06-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 500,00 | [Excel R226 / BIAYA ADMIN] BIAYA SMS NOTIFIKASI |
| `BNK26-0204` | R227 | 2026-06-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 33.600.000,00 | [Excel R227 / PEMBAYARAN] PEMBAYARAN AWAL PEMBANGUNAN MES TTC |
| `BNK26-0205` | R228 | 2026-06-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R228 / BIAYA ADMIN] ADMIN BANK PEMBAYARAN AWAL PEMBANGUNAN MES TTC |
| `BNK26-0206` | R229 | 2026-06-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 22.068.000,00 | [Excel R229 / PEMBELIAN] MATERIAL QB |
| `BNK26-0207` | R230 | 2026-06-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R230 / BIAYA ADMIN] ADMIN BANK MATERIAL QB |
| `BNK26-0208` | R231 | 2026-06-17 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 152.500.000,00 | [Excel R231 / PEMBAYARAN] PELUNASAN PROYEK LISTRIK |
| `BNK26-0209` | R232 | 2026-06-17 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R232 / BIAYA ADMIN] ADMIN BANK PELUNASAN PROYEK LISTRIK |
| `BNK26-0210` | R233 | 2026-06-17 | BANK_KELUAR | `1114` Bank Lain - Giro | `1251` Bangunan Dalam Pembangunan | 16.698.750,00 | [Excel R233 / PEMBELIAN] PELUNASAN MATERIAL |
| `BNK26-0211` | R234 | 2026-06-17 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R234 / BIAYA ADMIN] ADMIN BANK PELUNASAN MATERIAL |
| `BNK26-0212` | R235 | 2026-06-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 160.000.000,00 | [Excel R235 / PEMBELIAN] PELUNASAN PEMBELIAN KUBIKEL |
| `BNK26-0213` | R236 | 2026-06-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R236 / BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBELIAN KUBIKEL |
| `BNK26-0214` | R237 | 2026-06-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 44.468.000,00 | [Excel R237 / PEMBAYARAN] PELUNASAN PEMBUATAN PANEL |
| `BNK26-0215` | R238 | 2026-06-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R238 / BIAYA ADMIN] ADMIN BANK PELUNASAN PEMBUATAN PANEL |
| `BNK26-0216` | R239 | 2026-06-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6503` Program Kesejahteraan Karyawan | 650.000,00 | [Excel R239 / PEMBAYARAN] PEMBUATAN PASPOR |
| `BNK26-0217` | R240 | 2026-06-20 | BANK_MASUK | `1111` Bank BRI - Operasional | `4510` Pendapatan Bunga | 412.986,00 | [Excel R240 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0218` | R241 | 2026-06-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 82.597,00 | [Excel R241 / BIAYA ADMIN] TAX |
| `BNK26-0219` | R242 | 2026-06-22 | BANK_KELUAR | `1114` Bank Lain - Giro | `6207` Telepon & Internet | 1.310.800,00 | [Excel R242 / OPERASIONAL] TAGIHAN WIFI TELKOM |
| `BNK26-0220` | R243 | 2026-06-22 | BANK_KELUAR | `1114` Bank Lain - Giro | `6214` Biaya Administrasi Bank | 2.800,00 | [Excel R243 / BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM |
| `BNK26-0221` | R244 | 2026-06-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 18.750.000,00 | [Excel R244 / PEMBELIAN] DP 50 PERSEN 5 UNIT AC |
| `BNK26-0222` | R245 | 2026-06-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R245 / BIAYA ADMIN] ADMIN BANK DP 50 PERSEN 5 UNIT AC |
| `BNK26-0223` | R246 | 2026-06-22 | BANK_KELUAR | `1114` Bank Lain - Giro | `6203` Listrik & Air Kantor | 111.850,00 | [Excel R246 / OPERASIONAL] PEMBAYARAN PDAM |
| `BNK26-0224` | R247 | 2026-06-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 7.940.000,00 | [Excel R247 / PEMBELIAN] MATERIAL PEMBANGUNAN |
| `BNK26-0225` | R248 | 2026-06-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R248 / BIAYA ADMIN] ADMIN BANK MATERIAL PEMBANGUNAN |
| `BNK26-0226` | R249 | 2026-06-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 3.156.000,00 | [Excel R249 / PEMBAYARAN] PELUNASAN DESIGN PARTISI |
| `BNK26-0227` | R250 | 2026-06-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R250 / BIAYA ADMIN] ADMIN BANK PELUNASAN DESIGN |
| `BNK26-0228` | R251 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 1.595.250,00 | [Excel R251 / PEMBELIAN] PEMBAYARAN HARDISK |
| `BNK26-0229` | R252 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6108` Komisi Marketing / Referral | 412.000,00 | [Excel R252 / PEMBAYARAN] FEE MARKETING |
| `BNK26-0230` | R253 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R253 / BIAYA ADMIN] ADMIN BANK FEE MARKETING |
| `BNK26-0231` | R254 | 2026-06-25 | BANK_MASUK | `1111` Bank BRI - Operasional | `6108` Komisi Marketing / Referral | 412.000,00 | [Excel R254 / PENERIMAAN] REFUND FEE MARKETING |
| `BNK26-0232` | R255 | 2026-06-25 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 5.289.345,00 | [Excel R255 / PENJUALAN] UANG MASUK PELUNASAN ET DAVI & INNER |
| `BNK26-0233` | R256 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6108` Komisi Marketing / Referral | 300.000,00 | [Excel R256 / PEMBAYARAN] FEE MARKETING SUNARNI |
| `BNK26-0234` | R257 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R257 / BIAYA ADMIN] ADMIN BANK FEE MARKETING SUNARNI |
| `BNK26-0235` | R258 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `5308` Biaya Subcont Produksi | 2.000.000,00 | [Excel R258 / PEMBAYARAN] DP ETIKET BARLBARA |
| `BNK26-0236` | R259 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R259 / BIAYA ADMIN] ADMIN BANK UTJ |
| `BNK26-0237` | R260 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6108` Komisi Marketing / Referral | 603.143,00 | [Excel R260 / PEMBAYARAN] FEE MARKETING PAK ANDI PRABOWO |
| `BNK26-0238` | R261 | 2026-06-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R261 / BIAYA ADMIN] ADMIN BANK FEE MARKETING PAK ANDI PRABOWO |
| `BNK26-0239` | R262 | 2026-06-25 | BANK_MASUK | `1112` Bank BCA - Operasional | `4101` Penjualan Barang Jadi - IDR | 5.300.000,00 | [Excel R262 / PENJUALAN] DP ETIKET BARLBARA |
| `BNK26-0240` | R263 | 2026-06-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6108` Komisi Marketing / Referral | 412.000,00 | [Excel R263 / PEMBAYARAN] FEE MARKETING |
| `BNK26-0241` | R264 | 2026-06-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R264 / BIAYA ADMIN] ADMIN BANK FEE MARKETING |
| `BNK26-0242` | R265 | 2026-06-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 7.579.000,00 | [Excel R265 / PEMBELIAN] MATERIAL MESS TTC |
| `BNK26-0243` | R266 | 2026-06-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 7.795.000,00 | [Excel R266 / PEMBELIAN] MATERIAL MESS TTC |
| `BNK26-0244` | R267 | 2026-06-27 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 100.800.000,00 | [Excel R267 / PEMBAYARAN] BIAYA TERMIN  1 MES TTC |
| `BNK26-0245` | R268 | 2026-06-27 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R268 / BIAYA ADMIN] ADMIN BANK BIAYA TERMIN  1 MES TTC |
| `BNK26-0246` | R269 | 2026-06-27 | BANK_KELUAR | `1111` Bank BRI - Operasional | `5308` Biaya Subcont Produksi | 7.827.000,00 | [Excel R269 / PEMBAYARAN] ETIKET DAFI SKT 16 |
| `BNK26-0247` | R270 | 2026-06-27 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R270 / BIAYA ADMIN] ADMIN BANK UTJ |
| `BNK26-0248` | R271 | 2026-06-27 | BANK_MASUK | `1112` Bank BCA - Operasional | `4101` Penjualan Barang Jadi - IDR | 7.000.000,00 | [Excel R271 / PENJUALAN] PELUNASAN ET BARLBARA |
| `BNK26-0249` | R272 | 2026-06-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 2.875.000,00 | [Excel R272 / PEMBAYARAN] PEMBELIAN MATERIAL MES TTC |
| `BNK26-0250` | R273 | 2026-06-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `5101` Pemakaian Bahan Baku - Produksi | 2.874.555,00 | [Excel R273 / PEMBELIAN] PEMBELIAN KERTAS IVORY |
| `BNK26-0251` | R274 | 2026-06-30 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 12.694.000,00 | [Excel R274 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0252` | R277 | 2026-07-01 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 30.000,00 | [Excel R277 / BIAYA ADMIN] ADMIN BANK BCA |
| `BNK26-0253` | R278 | 2026-07-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 1.005.000,00 | [Excel R278 / PEMBELIAN] MATERIAL MESS TTC |
| `BNK26-0254` | R279 | 2026-07-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R279 / BIAYA ADMIN] BIAYA ADMIN Q8A & SENAR |
| `BNK26-0255` | R280 | 2026-07-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 960.000,00 | [Excel R280 / PEMBELIAN] MATERIAL MESS TTC |
| `BNK26-0256` | R281 | 2026-07-01 | BANK_KELUAR | `1114` Bank Lain - Giro | `1131` Piutang Karyawan | 5.500.000,00 | [Excel R281 / PEMBAYARAN] KASBON KARYAWAN |
| `BNK26-0257` | R282 | 2026-07-02 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 3.496.000,00 | [Excel R282 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0258` | R283 | 2026-07-02 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R283 / BIAYA ADMIN] ADMIN BANK GAJI KARYAWAN |
| `BNK26-0259` | R284 | 2026-07-02 | BANK_KELUAR | `1114` Bank Lain - Giro | `6502` Kontribusi BPJS Ketenagakerjaan | 398.548,00 | [Excel R284 / PEMBAYARAN] BAYAR BPJS KETENAGA KERJA |
| `BNK26-0260` | R285 | 2026-07-04 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 40.000,00 | [Excel R285 / BIAYA ADMIN] BIAYA ADMIN BDC |
| `BNK26-0261` | R286 | 2026-07-04 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 3.050.000,00 | [Excel R286 / PEMBAYARAN] MATERIAL PASIR DAN SEMEN |
| `BNK26-0262` | R287 | 2026-07-04 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R287 / BIAYA ADMIN] ADMIN BANK MATERIAL PASIR DAN SEMEN |
| `BNK26-0263` | R288 | 2026-07-06 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 5.975.000,00 | [Excel R288 / PEMBAYARAN] BAMBU & PAKU & KAYU |
| `BNK26-0264` | R289 | 2026-07-08 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 5.863.000,00 | [Excel R289 / PENJUALAN] AMBRI DAFI |
| `BNK26-0265` | R290 | 2026-07-10 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 19.194.000,00 | [Excel R290 / PEMBELIAN] MATERIAL MESS TTC |
| `BNK26-0266` | R291 | 2026-07-10 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1211` Tanah & Bangunan - Harga Perolehan | 188.435.000,00 | [Excel R291 / PEMBAYARAN] CICILAN GUDAN K/6 |
| `BNK26-0267` | R292 | 2026-07-10 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R292 / BIAYA ADMIN] ADMIN BANK CICILAN GUDAN K/6 |
| `BNK26-0268` | R293 | 2026-07-13 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6503` Program Kesejahteraan Karyawan | 850.000,00 | [Excel R293 / PEMBAYARAN] DP KAOS POLO DAN KEMEJA |
| `BNK26-0269` | R294 | 2026-07-13 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R294 / BIAYA ADMIN] ADMIN BANK DP KAOS POLO |
| `BNK26-0270` | R295 | 2026-07-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 3.684.000,00 | [Excel R295 / PEMBELIAN] MATERIAL MES TTC |
| `BNK26-0271` | R296 | 2026-07-16 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 750.000.000,00 | [Excel R296 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0272` | R297 | 2026-07-16 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 200.000.000,00 | [Excel R297 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0273` | R298 | 2026-07-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 6.950.000,00 | [Excel R298 / PEMBELIAN] MATERIAL MES TTC |
| `BNK26-0274` | R299 | 2026-07-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6108` Komisi Marketing / Referral | 1.793.908,00 | [Excel R299 / PEMBAYARAN] FEE MARKETING |
| `BNK26-0275` | R300 | 2026-07-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 475.090.000,00 | [Excel R300 / PEMBAYARAN] RESGISTER PLN |
| `BNK26-0276` | R301 | 2026-07-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 3.000,00 | [Excel R301 / BIAYA ADMIN] ADMIN BANK |
| `BNK26-0277` | R302 | 2026-07-20 | BANK_MASUK | `1111` Bank BRI - Operasional | `4510` Pendapatan Bunga | 833.462,00 | [Excel R302 / PENERIMAAN] INTEREST OF ACCOUNT |
| `BNK26-0278` | R303 | 2026-07-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 166.692,00 | [Excel R303 / BIAYA ADMIN] TAX |
| `BNK26-0279` | R304 | 2026-07-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6207` Telepon & Internet | 1.310.800,00 | [Excel R304 / PEMBAYARAN] TAGIHAN WIFI TELKOM |
| `BNK26-0280` | R305 | 2026-07-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.800,00 | [Excel R305 / BIAYA ADMIN] ADMIN BANK TAGIHAN WIFI TELKOM |
| `BNK26-0281` | R306 | 2026-07-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 79.200.000,00 | [Excel R306 / PEMBAYARAN] DP JASA PEKERJAAN RUANG PARTISI |
| `BNK26-0282` | R307 | 2026-07-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R307 / BIAYA ADMIN] ADMIN BANK DP PEKERJAAN RUANG PARTISI |
| `BNK26-0283` | R308 | 2026-07-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6610` Biaya Miscellaneous | 1.275.350,00 | [Excel R308 / PEMBAYARAN] REIMBUERSE MAKAN SIANG DENGAN TAMU CHINA |
| `BNK26-0284` | R309 | 2026-07-21 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 50.000,00 | [Excel R309 / PEMBELIAN] POT BY KEY BCA |
| `BNK26-0285` | R310 | 2026-07-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 3.740.000,00 | [Excel R310 / PEMBELIAN] MATERIAL BESI RUANG PARTISI TTC |
| `BNK26-0286` | R311 | 2026-07-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R311 / BIAYA ADMIN] ADMIN BANK MATERIAL BESI RUANG PARTISI TTC |
| `BNK26-0287` | R312 | 2026-07-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6205` Biaya Profesional | 1.800.000,00 | [Excel R312 / PEMBAYARAN] DP 30% AKTA PERUBAHAN PT |
| `BNK26-0288` | R313 | 2026-07-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R313 / BIAYA ADMIN] ADMIN BANK DP 30% AKTA PERUBAHAN PT |
| `BNK26-0289` | R314 | 2026-07-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6610` Biaya Miscellaneous | 17.198.864,00 | [Excel R314 / PEMBAYARAN] REIMBUERSE TIKET PESAWAT KE CHINA |
| `BNK26-0290` | R315 | 2026-07-23 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6610` Biaya Miscellaneous | 36.960,00 | [Excel R315 / PEMBAYARAN] REIMBUERSE JUAN |
| `BNK26-0291` | R316 | 2026-07-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 22.175.000,00 | [Excel R316 / PEMBELIAN] PEMBELIAN BAMBU DAN BESI |
| `BNK26-0292` | R317 | 2026-07-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6503` Program Kesejahteraan Karyawan | 1.285.000,00 | [Excel R317 / PEMBAYARAN] SERAGAM TTC |
| `BNK26-0293` | R318 | 2026-07-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R318 / BIAYA ADMIN] ADMIN BANK SERAGAM TTC |
| `BNK26-0294` | R319 | 2026-07-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1231` Kendaraan - Harga Perolehan | 10.000.000,00 | [Excel R319 / PEMBAYARAN] UTJ PEMBELIAN MOBIL |
| `BNK26-0295` | R320 | 2026-07-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R320 / BIAYA ADMIN] ADMIN BANK UTJ |
| `BNK26-0296` | R321 | 2026-07-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1261` Renovasi & Leasehold Improvement - Harga Perolehan | 1.590.000,00 | [Excel R321 / PEMBELIAN] MATERIAL PARTISI GUDANG |
| `BNK26-0297` | R322 | 2026-07-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R322 / BIAYA ADMIN] ADMIN DP PEMBELIAN ANGKUR DAN PLAT |
| `BNK26-0298` | R323 | 2026-07-29 | BANK_MASUK | `1112` Bank BCA - Operasional | `4540` Pendapatan Lain-lain | 300.000,00 | [Excel R323 / PENERIMAAN] RETUR PEMBELIAN TOKO ANYAR |
| `BNK26-0299` | R324 | 2026-07-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 7.000.000,00 | [Excel R324 / PEMBELIAN] MATERIAL KAYU MES TTC |
| `BNK26-0300` | R325 | 2026-07-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6305` Training & Pengembangan | 1.500.000,00 | [Excel R325 / PEMBAYARAN] UANG SAKU TRAINING |
| `BNK26-0301` | R326 | 2026-07-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6305` Training & Pengembangan | 1.000.000,00 | [Excel R326 / PEMBAYARAN] UANG SAKU TRAINING |
| `BNK26-0302` | R327 | 2026-07-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6610` Biaya Miscellaneous | 1.948.000,00 | [Excel R327 / PEMBAYARAN] REIMBURSE PAK HERI |
| `BNK26-0303` | R328 | 2026-07-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6305` Training & Pengembangan | 1.000.000,00 | [Excel R328 / PEMBAYARAN] UANG SAKU TRAINING |
| `BNK26-0304` | R329 | 2026-07-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6305` Training & Pengembangan | 1.000.000,00 | [Excel R329 / PEMBAYARAN] UANG SAKU TRAINING |
| `BNK26-0305` | R330 | 2026-07-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 15.000.000,00 | [Excel R330 / PEMBAYARAN] DP MATERIAL KABEL NYY, SKUN KABEL & VINYL |
| `BNK26-0306` | R331 | 2026-07-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 17.482.260,00 | [Excel R331 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0307` | R332 | 2026-07-31 | BANK_MASUK | `1112` Bank BCA - Operasional | `4510` Pendapatan Bunga | 12.436,20 | [Excel R332 / PENERIMAAN] BUNGA |
| `BNK26-0308` | R333 | 2026-07-31 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 2.487,24 | [Excel R333 / BIAYA ADMIN] PAJAK BUNGA |
| `BNK26-0309` | R336 | 2026-08-01 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 40.000,00 | [Excel R336 / BIAYA ADMIN] BIAYA ADMIN BDC |
| `BNK26-0310` | R337 | 2026-08-01 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 30.000,00 | [Excel R337 / BIAYA ADMIN] BIAYA ADMIN |
| `BNK26-0311` | R338 | 2026-08-03 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 20.861.400,00 | [Excel R338 / PEMBELIAN] MATERIAL KABEL NYY, SKUN KABEL & VINYL |
| `BNK26-0312` | R339 | 2026-08-03 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6203` Listrik & Air Kantor | 104.000,00 | [Excel R339 / PEMBELIAN] TOKEN LISTRIK |
| `BNK26-0313` | R340 | 2026-08-06 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 2.300.000,00 | [Excel R340 / PEMBELIAN] PEMBELIAN BAMBU MES TTC |
| `BNK26-0314` | R341 | 2026-08-06 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R341 / BIAYA ADMIN] ADMIN PEMBELIAN BAMBU MES TTC |
| `BNK26-0315` | R342 | 2026-08-08 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 1.395.000,00 | [Excel R342 / PEMBELIAN] PEMBELIAN MATERIAL  PAKU,BENANG,WD,KAPUR, & BENDRAT |
| `BNK26-0316` | R343 | 2026-08-10 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6205` Biaya Profesional | 4.200.000,00 | [Excel R343 / PEMBAYARAN] PELUNASAN AKTA PERUBAHAN AD PT |
| `BNK26-0317` | R344 | 2026-08-10 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R344 / BIAYA ADMIN] ADMIN PELUNASAN AKTA PERUBAHAN AD PT |
| `BNK26-0318` | R345 | 2026-08-11 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 19.920.000,00 | [Excel R345 / PEMBELIAN] PEMBELIAN BONDEK GALVANIS |
| `BNK26-0319` | R346 | 2026-08-11 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R346 / BIAYA ADMIN] ADMIN PEMBELIAN BONDEK GALVANIS |
| `BNK26-0320` | R347 | 2026-08-13 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6305` Training & Pengembangan | 450.000,00 | [Excel R347 / PEMBAYARAN] UANG MAKAN DAN TRANSPORT ANK MAGANG |
| `BNK26-0321` | R348 | 2026-08-13 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 3.500.000,00 | [Excel R348 / PEMBELIAN] PEMBELIAN MATERIAL KAYU 4x6 |
| `BNK26-0322` | R349 | 2026-08-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 11.625.000,00 | [Excel R349 / PEMBELIAN] MATERIAL WIREMESH DAN PENAMBAHAN TRIPLEK |
| `BNK26-0323` | R350 | 2026-08-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 12.599.500,00 | [Excel R350 / PEMBELIAN] MATERIAL PAKU, BESI, BAMBU |
| `BNK26-0324` | R351 | 2026-08-19 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6610` Biaya Miscellaneous | 11.582.000,00 | [Excel R351 / PEMBAYARAN] TIKET PESAWAT |
| `BNK26-0325` | R352 | 2026-08-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1211` Tanah & Bangunan - Harga Perolehan | 188.435.000,00 | [Excel R352 / PEMBAYARAN] CICILAN GUDANG K/7 |
| `BNK26-0326` | R353 | 2026-08-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R353 / BIAYA ADMIN] ADMIN CICILAN GUDAN K/7 |
| `BNK26-0327` | R354 | 2026-08-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 24.400.000,00 | [Excel R354 / PEMBAYARAN] JASA JAYA MIX UNTUK COR MES TTC |
| `BNK26-0328` | R355 | 2026-08-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 2.000.000,00 | [Excel R355 / PEMBAYARAN] PERIZINAN PEMBANGUNAN DAN OPRASIONAL |
| `BNK26-0329` | R356 | 2026-08-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R356 / BIAYA ADMIN] ADMIN PERIZINAN PEMBANGUNAN DAN OPRASIONAL |
| `BNK26-0330` | R357 | 2026-08-20 | BANK_MASUK | `1111` Bank BRI - Operasional | `4510` Pendapatan Bunga | 528.223,00 | [Excel R357 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0331` | R358 | 2026-08-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 105.645,00 | [Excel R358 / BIAYA ADMIN] TAX |
| `BNK26-0332` | R359 | 2026-08-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6502` Kontribusi BPJS Ketenagakerjaan | 1.113.652,00 | [Excel R359 / PEMBAYARAN] PEMBAYARAN BPJS KETENAGA KERJAAN |
| `BNK26-0333` | R360 | 2026-08-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1231` Kendaraan - Harga Perolehan | 50.000.000,00 | [Excel R360 / PEMBELIAN] UTJ KENDARAAN TTC |
| `BNK26-0334` | R361 | 2026-08-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R361 / BIAYA ADMIN] ADMIN UTJ KENDARAAN |
| `BNK26-0335` | R362 | 2026-08-24 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6203` Listrik & Air Kantor | 241.200,00 | [Excel R362 / OPERASIONAL] PEMBAYARAN PDAM |
| `BNK26-0336` | R363 | 2026-08-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6207` Telepon & Internet | 1.310.800,00 | [Excel R363 / OPERASIONAL] TLKM |
| `BNK26-0337` | R364 | 2026-08-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.800,00 | [Excel R364 / BIAYA ADMIN] ADMIN TLKM |
| `BNK26-0338` | R365 | 2026-08-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6203` Listrik & Air Kantor | 100.000,00 | [Excel R365 / OPERASIONAL] TOKEN LISTRIK |
| `BNK26-0339` | R366 | 2026-08-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 3.000,00 | [Excel R366 / BIAYA ADMIN] ADMIN TOKEN LISTRIK |
| `BNK26-0340` | R367 | 2026-08-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 85.000,00 | [Excel R367 / PEMBELIAN] PEMBELIAN PAKU |
| `BNK26-0341` | R368 | 2026-08-26 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 505.000,00 | [Excel R368 / PEMBELIAN] PEMBELIAN SOLASI LISTRIK, KUNCI BOR 13,MATA BOR BESI 10,KABEL 2x1,5 |
| `BNK26-0342` | R369 | 2026-08-26 | BANK_MASUK | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 500.000,00 | [Excel R369 / PENERIMAAN] SISA UANG KONTRIBUSI PEMBANGUNAN |
| `BNK26-0343` | R370 | 2026-08-27 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 4.200.500,00 | [Excel R370 / PEMBELIAN] DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT |
| `BNK26-0344` | R371 | 2026-08-27 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R371 / PEMBELIAN] ADMIN DP MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT |
| `BNK26-0345` | R372 | 2026-08-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 6.120.000,00 | [Excel R372 / PEMBELIAN] DP PEMBELIAN HEBEL 18 KUBIK |
| `BNK26-0346` | R373 | 2026-08-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R373 / PEMBELIAN] ADMIN DP PEMBELIAN HEBEL 18 KUBIK |
| `BNK26-0347` | R374 | 2026-08-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 4.200.500,00 | [Excel R374 / PEMBELIAN] PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT |
| `BNK26-0348` | R375 | 2026-08-28 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R375 / BIAYA ADMIN] ADMIN PELUNASAN MATERIAL BESI, BATA RINGAN, KAWAT BENDRAT |
| `BNK26-0349` | R376 | 2026-08-28 | BANK_MASUK | `1111` Bank BRI - Operasional | `4101` Penjualan Barang Jadi - IDR | 11.192.541,00 | [Excel R376 / PENJUALAN] PENJUALAN ET. DAN INNER DAFI |
| `BNK26-0350` | R377 | 2026-08-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 6.120.000,00 | [Excel R377 / PEMBELIAN] PELUNASAN  PEMBELIAN HEBEL 18 KUBIK |
| `BNK26-0351` | R378 | 2026-08-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R378 / PEMBELIAN] ADMIN PELUNASAN PEMBELIAN HEBEL 18 KUBIK |
| `BNK26-0352` | R379 | 2026-08-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 110.000,00 | [Excel R379 / PEMBELIAN] PEMBELIAN KAWAT BENDRAT 1KG 5x |
| `BNK26-0353` | R380 | 2026-08-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R380 / PEMBELIAN] ADMIN PEMBELIAN KAWAT BENDRAT 1KG 5x |
| `BNK26-0354` | R381 | 2026-08-31 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 17.856.960,00 | [Excel R381 / PEMBAYARAN] GAJI KARYAWAN |
| `BNK26-0355` | R382 | 2026-08-31 | BANK_MASUK | `1112` Bank BCA - Operasional | `4510` Pendapatan Bunga | 12.473,31 | [Excel R382 / PENERIMAAN] BUNGA |
| `BNK26-0356` | R383 | 2026-08-31 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 2.494,66 | [Excel R383 / BIAYA ADMIN] PAJAK BUNGA |
| `BNK26-0357` | R386 | 2026-09-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 2.000.000,00 | [Excel R386 / PEMBAYARAN] GAJI 1 KARYAWAN |
| `BNK26-0358` | R387 | 2026-09-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R387 / BIAYA ADMIN] ADMIN |
| `BNK26-0359` | R388 | 2026-09-02 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 865.000,00 | [Excel R388 / PEMBELIAN] PEMBELIAN MATERIAL MES TTC LT 2 |
| `BNK26-0360` | R389 | 2026-09-02 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R389 / BIAYA ADMIN] ADMIN |
| `BNK26-0361` | R390 | 2026-09-02 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 30.000,00 | [Excel R390 / BIAYA ADMIN] BIAYA ADMIN |
| `BNK26-0362` | R391 | 2026-09-05 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 40.000,00 | [Excel R391 / BIAYA ADMIN] BIAYA ADMIN BDC |
| `BNK26-0363` | R392 | 2026-09-07 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 200.000,00 | [Excel R392 / OPERASIONAL] JASA MENURUNKAN BESI DAN TRIPEK MES TTC 4 ORANG |
| `BNK26-0364` | R393 | 2026-09-07 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R393 / BIAYA ADMIN] ADMIN |
| `BNK26-0365` | R394 | 2026-09-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 3.386.000,00 | [Excel R394 / PEMBELIAN] MATERIAL PROSES TAHP BLK BACK DN TING TLNG COR 3 N |
| `BNK26-0366` | R395 | 2026-09-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R395 / BIAYA ADMIN] ADMIN |
| `BNK26-0367` | R396 | 2026-09-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 6.803.000,00 | [Excel R396 / PEMBELIAN] PENAMBAHAN MATERIAL UTK MES TTC |
| `BNK26-0368` | R397 | 2026-09-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R397 / BIAYA ADMIN] ADMIN |
| `BNK26-0369` | R398 | 2026-09-09 | BANK_MASUK | `1112` Bank BCA - Operasional | `3100` Modal Awal Pemilik | 544.073.239,00 | [Excel R398 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0370` | R399 | 2026-09-09 | BANK_KELUAR | `1112` Bank BCA - Operasional | `1231` Kendaraan - Harga Perolehan | 544.073.239,00 | [Excel R399 / PEMBELIAN] PEMBAYARAN MOBIL |
| `BNK26-0371` | R400 | 2026-09-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6402` Retribusi & Perizinan | 200.000,00 | [Excel R400 / PEMBAYARAN] PENGURUSAN SURAT KETERANGAN DOMISILI PT |
| `BNK26-0372` | R401 | 2026-09-11 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R401 / BIAYA ADMIN] ADMIN |
| `BNK26-0373` | R402 | 2026-09-09 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 1.800.000,00 | [Excel R402 / PEMBELIAN] MATERIAL PASIR 1 RIT TRUCK UNTUK COR LT 2 MES TTC |
| `BNK26-0374` | R403 | 2026-09-15 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 1.111.000,00 | [Excel R403 / PEMBELIAN] STOCK MTRL MES TTC UTK PROSES COR 1 MINGGU KDPN |
| `BNK26-0375` | R404 | 2026-09-15 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R404 / BIAYA ADMIN] ADMIN |
| `BNK26-0376` | R405 | 2026-09-15 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6502` Kontribusi BPJS Ketenagakerjaan | 1.113.652,00 | [Excel R405 / PEMBAYARAN] BPJS TK PU |
| `BNK26-0377` | R406 | 2026-09-15 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 2.450.000.000,00 | [Excel R406 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0378` | R407 | 2026-09-15 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 550.000.000,00 | [Excel R407 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0379` | R408 | 2026-09-15 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6203` Listrik & Air Kantor | 100.000,00 | [Excel R408 / PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC |
| `BNK26-0380` | R409 | 2026-09-15 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 3.000,00 | [Excel R409 / BIAYA ADMIN] ADMIN |
| `BNK26-0381` | R410 | 2026-09-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 3.000.886.601,50 | [Excel R410 / PEMBAYARAN] PEMBAYARAN MESIN ZONTEN |
| `BNK26-0382` | R411 | 2026-09-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 10.730.000,00 | [Excel R411 / PEMBELIAN] PEMBELIAN WIREMESH DAN BONDEK UNTUK MES TTC |
| `BNK26-0383` | R412 | 2026-09-16 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R412 / BIAYA ADMIN] ADMIN |
| `BNK26-0384` | R413 | 2026-09-16 | BANK_KELUAR | `1114` Bank Lain - Giro | `1251` Bangunan Dalam Pembangunan | 1.800.000,00 | [Excel R413 / PEMBELIAN] PEMBELIAN PASIR UNTUK PROSES COR MES TTC |
| `BNK26-0385` | R414 | 2026-09-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 727.500,00 | [Excel R414 / PEMBELIAN] REIMBUERSE SEMEN MERAH PUTIH 15 SAK KAV 24 |
| `BNK26-0386` | R415 | 2026-09-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R415 / BIAYA ADMIN] ADMIN |
| `BNK26-0387` | R416 | 2026-09-18 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 3.600.000,00 | [Excel R416 / PEMBELIAN] PASIR LMJNG 2 RIT TRUCK UTK PROSES COR ATAP MES TTC |
| `BNK26-0388` | R417 | 2026-09-20 | BANK_MASUK | `1111` Bank BRI - Operasional | `4510` Pendapatan Bunga | 338.795,00 | [Excel R417 / PENERIMAAN] INTEREST ON ACCOUNT |
| `BNK26-0389` | R418 | 2026-09-20 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 67.759,00 | [Excel R418 / BIAYA ADMIN] TAX |
| `BNK26-0390` | R419 | 2026-09-21 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 1.000,00 | [Excel R419 / BIAYA ADMIN] BIAYA SMS NOTIFIKASI |
| `BNK26-0391` | R420 | 2026-09-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 1.351.500,00 | [Excel R420 / PEMBELIAN] PEMBELIAN MATERIAL UNTUK COR RING BALOK LT2 MES TTC |
| `BNK26-0392` | R421 | 2026-09-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R421 / BIAYA ADMIN] ADMIN |
| `BNK26-0393` | R422 | 2026-09-22 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 848.000.000,00 | [Excel R422 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0394` | R423 | 2026-09-22 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 2.500.000.000,00 | [Excel R423 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0395` | R424 | 2026-09-22 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 3.151.947.602,40 | [Excel R424 / PEMBAYARAN] PEMBAYARAN MESIN ZONTEN |
| `BNK26-0396` | R425 | 2026-09-24 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6207` Telepon & Internet | 1.310.800,00 | [Excel R425 / PEMBAYARAN] PEMBAYARAN TAGIHAN WIFI GUDANG |
| `BNK26-0397` | R426 | 2026-09-24 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.800,00 | [Excel R426 / BIAYA ADMIN] ADMIN |
| `BNK26-0398` | R427 | 2026-09-24 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1251` Bangunan Dalam Pembangunan | 525.000,00 | [Excel R427 / PEMBELIAN] PEMBELIAN LEM SEMEN UNTUK MES TTC |
| `BNK26-0399` | R428 | 2026-09-24 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R428 / BIAYA ADMIN] ADMIN |
| `BNK26-0400` | R429 | 2026-09-24 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6203` Listrik & Air Kantor | 122.850,00 | [Excel R429 / PEMBAYARAN] PEMBAYARAN PDAM GUDANG TTC |
| `BNK26-0401` | R430 | 2026-09-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1221` Mesin & Peralatan - Harga Perolehan | 31.926.334,86 | [Excel R430 / PEMBAYARAN] PEMBAYARAN MESIN ZONTEN |
| `BNK26-0402` | R431 | 2026-09-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 5.000,00 | [Excel R431 / BIAYA ADMIN] ADMIN |
| `BNK26-0403` | R432 | 2026-09-25 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 1.000,00 | [Excel R432 / BIAYA ADMIN] BIAYA SMS NOTIFIKASI |
| `BNK26-0404` | R433 | 2026-09-29 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 200.000.000,00 | [Excel R433 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0405` | R434 | 2026-09-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `1211` Tanah & Bangunan - Harga Perolehan | 188.435.000,00 | [Excel R434 / PEMBAYARAN] CICILAN GUDANG TTC BLOK B8-B9 KE 8 |
| `BNK26-0406` | R435 | 2026-09-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 2.500,00 | [Excel R435 / BIAYA ADMIN] ADMIN |
| `BNK26-0407` | R436 | 2026-09-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6215` Perawatan Gedung/Bangunan | 282.000,00 | [Excel R436 / PEMBELIAN] REIMBURSE BIAYA REPARASI AIR TTC |
| `BNK26-0408` | R437 | 2026-09-29 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6108` Komisi Marketing / Referral | 801.237,00 | [Excel R437 / PEMBAYARAN] COMMISSION ET DAFI 16 |
| `BNK26-0409` | R438 | 2026-09-30 | BANK_MASUK | `1112` Bank BCA - Operasional | `4510` Pendapatan Bunga | 12.060,32 | [Excel R438 / PENERIMAAN] BUNGA |
| `BNK26-0410` | R439 | 2026-09-30 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 2.412,06 | [Excel R439 / BIAYA ADMIN] PAJAK BUNGA |
| `BNK26-0411` | R442 | 2026-10-01 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 14.364.960,00 | [Excel R442 / PEMBAYARAN] GAJI 6 KARYAWAN |
| `BNK26-0412` | R443 | 2026-10-02 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 200.000.000,00 | [Excel R443 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0413` | R444 | 2026-10-02 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6301` Gaji Karyawan Umum | 2.000.000,00 | [Excel R444 / PEMBAYARAN] GAJI 1 KARYAWAN |
| `BNK26-0414` | R445 | 2026-10-03 | BANK_MASUK | `1111` Bank BRI - Operasional | `3100` Modal Awal Pemilik | 100.000.000,00 | [Excel R445 / MODAL] SETORAN MODAL KE PT TEKNOGRAFI |
| `BNK26-0415` | R446 | 2026-10-05 | BANK_KELUAR | `1112` Bank BCA - Operasional | `6214` Biaya Administrasi Bank | 70.000,00 | [Excel R446 / BIAYA ADMIN] ADMIN BDC |
| `BNK26-0416` | R447 | 2026-10-07 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6203` Listrik & Air Kantor | 100.000,00 | [Excel R447 / PEMBELIAN] PEMBELIAN TOKEN LISTRIK GUDANG TTC |
| `BNK26-0417` | R448 | 2026-10-07 | BANK_KELUAR | `1111` Bank BRI - Operasional | `6214` Biaya Administrasi Bank | 3.000,00 | [Excel R448 / BIAYA ADMIN] ADMIN BANK PEMBELIAN TOKEN LISTRIK |