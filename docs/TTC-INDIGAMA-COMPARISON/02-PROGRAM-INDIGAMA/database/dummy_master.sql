-- ==============================================================================
-- MIGRASI DATA DUMMY FITUR MASTER PROGRAM INDIGAMA (PRINTING & PRODUCTION)
-- Database: db_printing (MySQL / MariaDB local XAMPP)
-- Dijalankan pada: 2026-10-31
-- Keterangan: Data dummy realistis & saling berelasi untuk semua modul Master.
-- ==============================================================================

USE `db_printing`;

-- 1. MASTER COMPANY (Informasi Perusahaan Default)
INSERT INTO `mcompany` (`id`, `name`, `addr`, `city`, `districts`, `postalcode`, `telp`, `fax`, `mail`, `rek1_jenis`, `rek1_an`, `rek1_no`, `rek2_jenis`, `rek2_an`, `rek2_no`, `ket_inv`)
VALUES (1, 'CV. Indigama Khatulistiwa', 'Jl. Industri Grafika No. 128, Gedangan', 'Sidoarjo', 'Gedangan', '61254', '031-8912345', '031-8912346', 'sales@indigama.co.id', 'BNI', 'CV Indigama Khatulistiwa', '0892345112', 'BCA', 'CV Indigama Khatulistiwa', '0149928310', 'Terima kasih atas kepercayaan Anda bermitra dengan Indigama Printing. Pembayaran tempo maksimal 30 hari kalender.')
ON DUPLICATE KEY UPDATE 
  `name`=VALUES(`name`), `addr`=VALUES(`addr`), `city`=VALUES(`city`), `districts`=VALUES(`districts`),
  `postalcode`=VALUES(`postalcode`), `telp`=VALUES(`telp`), `fax`=VALUES(`fax`), `mail`=VALUES(`mail`),
  `rek1_jenis`=VALUES(`rek1_jenis`), `rek1_an`=VALUES(`rek1_an`), `rek1_no`=VALUES(`rek1_no`),
  `rek2_jenis`=VALUES(`rek2_jenis`), `rek2_an`=VALUES(`rek2_an`), `rek2_no`=VALUES(`rek2_no`),
  `ket_inv`=VALUES(`ket_inv`);

-- 2. MASTER GUDANG (Kode harus 5 Digit sesuai validasi UI)
INSERT INTO `mgudang` (`id`, `ccode`, `cname`, `caddr`, `ccity`, `ckecamatan`, `cdesa`, `cpostalcode`, `ctelp`, `cfax`, `chead`, `chead_acc`, `cnote`, `username`)
VALUES 
(1, 'GD001', 'Gudang Pusat & Bahan Baku', 'Jl. Industri Grafika No. 128', 'Sidoarjo', 'Gedangan', 'Sawotratap', '61254', '031-8912345', '031-8912346', 'Bambang Wijaya', 'Sri Wahyuni', 'Gudang utama penerimaan dan penyimpanan bahan baku plano/roll', 'admin'),
(2, 'GD002', 'Gudang Produk Jadi & Display', 'Jl. Raya Tropodo No. 45', 'Sidoarjo', 'Waru', 'Tropodo', '61256', '031-8667890', '031-8667891', 'Joko Susilo', 'Sri Wahyuni', 'Gudang transit produk jadi siap kirim & display toko', 'admin'),
(3, 'GD003', 'Gudang Logistik & Sparepart', 'Kawasan Pergudangan Sirie Blok C-10', 'Sidoarjo', 'Lingkar Timur', 'Rangkah', '61234', '031-8051234', '031-8051235', 'Agus Hendra', 'Sri Wahyuni', 'Gudang penyimpanan packaging kardus, palet, dan sparepart mesin', 'admin')
ON DUPLICATE KEY UPDATE 
  `ccode`=VALUES(`ccode`), `cname`=VALUES(`cname`), `caddr`=VALUES(`caddr`), `ccity`=VALUES(`ccity`),
  `ckecamatan`=VALUES(`ckecamatan`), `cdesa`=VALUES(`cdesa`), `cpostalcode`=VALUES(`cpostalcode`),
  `ctelp`=VALUES(`ctelp`), `cfax`=VALUES(`cfax`), `chead`=VALUES(`chead`), `chead_acc`=VALUES(`chead_acc`),
  `cnote`=VALUES(`cnote`), `username`=VALUES(`username`);

-- 3. MASTER SKAT / RAK GUDANG
INSERT INTO `mskat` (`id`, `cname`, `tgl`)
VALUES 
(1, 'Rak A - Kertas Sheet & Plano', NOW()),
(2, 'Rak B - Tinta, Toner & Kimia', NOW()),
(3, 'Rak C - Bahan Roll & Banner MMT', NOW()),
(4, 'Rak D - Kemasan & Packaging', NOW()),
(5, 'Rak E - Barang Jadi Siap Kirim', NOW())
ON DUPLICATE KEY UPDATE `cname`=VALUES(`cname`);

-- 4. MASTER DEPARTEMEN
INSERT INTO `mdepartemen` (`id`, `id_dept`, `cdept`, `cusername`)
VALUES 
(1, 'DP01', 'PRODUKSI & CETAK', 'admin'),
(2, 'DP02', 'GUDANG & LOGISTIK', 'admin'),
(3, 'DP03', 'FINANCE & ACCOUNTING', 'admin'),
(4, 'DP04', 'MARKETING & SALES', 'admin'),
(5, 'DP05', 'HRD & UMUM', 'admin'),
(6, 'DP06', 'DESAIN & PRE-PRESS', 'admin')
ON DUPLICATE KEY UPDATE `id_dept`=VALUES(`id_dept`), `cdept`=VALUES(`cdept`), `cusername`=VALUES(`cusername`);

-- 5. MASTER JABATAN
INSERT INTO `mjabatan` (`id`, `id_jab`, `cjabatan`, `id_dept`, `username`, `ctunj`)
VALUES 
(1, 'J001', 'Manager Produksi', 'DP01', 'admin', 1500000),
(2, 'J002', 'Operator Cetak Offset', 'DP01', 'admin', 600000),
(3, 'J003', 'Operator Finishing & Pond', 'DP01', 'admin', 450000),
(4, 'J004', 'Kepala Gudang & Logistik', 'DP02', 'admin', 750000),
(5, 'J005', 'Staff Finance & Accounting', 'DP03', 'admin', 650000),
(6, 'J006', 'Account Executive / Sales', 'DP04', 'admin', 800000),
(7, 'J007', 'Staff HRD & Personalia', 'DP05', 'admin', 600000),
(8, 'J008', 'Graphic Designer & CTP', 'DP06', 'admin', 700000)
ON DUPLICATE KEY UPDATE `id_jab`=VALUES(`id_jab`), `cjabatan`=VALUES(`cjabatan`), `id_dept`=VALUES(`id_dept`), `username`=VALUES(`username`), `ctunj`=VALUES(`ctunj`);

-- 6. MASTER KARYAWAN
INSERT INTO `mkaryawan` (`id`, `ServerDate`, `ccode`, `nik`, `cid_finger`, `cname`, `cktp`, `ctgl_masuk`, `ctgl_lahir`, `creligy`, `cgander`, `cmarried`, `caddr`, `ccity`, `ckecamatan`, `cdesa`, `cpostalcode`, `ctelp`, `cjabatan`, `cpayrol`, `csalary`, `ctunjjab`, `ctunjkomp`, `ctunjintensif`, `cbank`, `cbank_an`, `cbank_number`, `cbpjs_name`, `cbpjs_kesehatan`, `cbpjs_ketenagakerjaan`, `cbpjs_cabang`, `cbpjs_tgl`, `cnote`, `cnpwp_name`, `cnpwp_no`, `clembur`, `cborongan`, `cusername`)
VALUES 
(1, NOW(), 'GD001', '230110001', '101', 'Budi Santoso, S.T.', '3515011005850001', '2023-01-10', '1985-05-10', 'Islam', 'Laki - Laki', 'Menikah', 'Perum Puri Surya Jaya Blok B-12', 'Sidoarjo', 'Gedangan', 'Puri', '61254', '081234567890', 'J001', 'MONTH', '6500000', 1500000, 500000, 500000, 'BCA', 'Budi Santoso', '0142234123', 'Budi Santoso', '000123456789', '98765432100', 'Sidoarjo', '2023-02-01', 'Karyawan Tetap - Manager Produksi', 'Budi Santoso', '85.123.456.7-603.000', 'LMB01', '0', 'admin'),
(2, NOW(), 'GD001', '230315002', '102', 'Ahmad Fauzi', '3515022008900002', '2023-03-15', '1990-08-20', 'Islam', 'Laki - Laki', 'Menikah', 'Desa Sawotratap RT 03 RW 02', 'Sidoarjo', 'Gedangan', 'Sawotratap', '61254', '081234567891', 'J002', 'MONTH', '4800000', 600000, 300000, 300000, 'BCA', 'Ahmad Fauzi', '0142234124', 'Ahmad Fauzi', '000123456790', '98765432101', 'Sidoarjo', '2023-04-01', 'Operator Mesin Offset Utama', 'Ahmad Fauzi', '85.123.456.7-603.001', 'LMB01', '0', 'admin'),
(3, NOW(), 'GD001', '230601003', '103', 'Siti Rahmawati', '3515031512950003', '2023-06-01', '1995-12-15', 'Islam', 'Perempuan', 'Belum Menikah', 'Jl. Kolonel Sugiono No. 25', 'Sidoarjo', 'Waru', 'Kureksari', '61256', '081234567892', 'J003', 'MONTH', '4200000', 450000, 250000, 200000, 'BRI', 'Siti Rahmawati', '002101988123', 'Siti Rahmawati', '000123456791', '98765432102', 'Sidoarjo', '2023-07-01', 'Operator Finishing & Quality Control', 'Siti Rahmawati', '85.123.456.7-603.002', 'LMB01', '1', 'admin'),
(4, NOW(), 'GD001', '230201004', '104', 'Bambang Wijaya', '3515041004880004', '2023-02-01', '1988-04-10', 'Islam', 'Laki - Laki', 'Menikah', 'Dusun Rangkah Kidul No. 08', 'Sidoarjo', 'Sidoarjo', 'Rangkah', '61234', '081234567893', 'J004', 'MONTH', '5000000', 750000, 300000, 400000, 'Mandiri', 'Bambang Wijaya', '142001889234', 'Bambang Wijaya', '000123456792', '98765432103', 'Sidoarjo', '2023-03-01', 'Kepala Gudang Bahan Baku & Finished Goods', 'Bambang Wijaya', '85.123.456.7-603.003', 'LMB01', '0', 'admin'),
(5, NOW(), 'GD002', '230510005', '105', 'Dewi Lestari, S.Ak.', '3515052207960005', '2023-05-10', '1996-07-22', 'Islam', 'Perempuan', 'Belum Menikah', 'Jl. Letjen Sutoyo No. 18', 'Sidoarjo', 'Waru', 'Medaeng', '61256', '081234567894', 'J005', 'MONTH', '4700000', 650000, 300000, 300000, 'BCA', 'Dewi Lestari', '0142234125', 'Dewi Lestari', '000123456793', '98765432104', 'Sidoarjo', '2023-06-01', 'Staff Finance, Billing & Pajak', 'Dewi Lestari', '85.123.456.7-603.004', 'LMB01', '0', 'admin'),
(6, NOW(), 'GD002', '230820006', '106', 'Rian Pratama', '3515061803940006', '2023-08-20', '1994-03-18', 'Islam', 'Laki - Laki', 'Menikah', 'Taman Candiloka Blok F-05', 'Sidoarjo', 'Candi', 'Ngasinan', '61271', '081234567895', 'J006', 'MONTH', '4500000', 800000, 300000, 500000, 'BCA', 'Rian Pratama', '0142234126', 'Rian Pratama', '000123456794', '98765432105', 'Sidoarjo', '2023-09-01', 'Account Executive Korporat & B2B', 'Rian Pratama', '85.123.456.7-603.005', 'LMB01', '0', 'admin'),
(7, NOW(), 'GD001', '230412007', '107', 'Dian Anggraini, S.Ds.', '3515072811970007', '2023-04-12', '1997-11-28', 'Islam', 'Perempuan', 'Belum Menikah', 'Jl. Jenggolo II No. 14', 'Sidoarjo', 'Buduran', 'Banjarkemantren', '61252', '081234567896', 'J008', 'MONTH', '4900000', 700000, 300000, 400000, 'BCA', 'Dian Anggraini', '0142234127', 'Dian Anggraini', '000123456795', '98765432106', 'Sidoarjo', '2023-05-01', 'Desain Grafis Pre-Press & Separasi Warna', 'Dian Anggraini', '85.123.456.7-603.006', 'LMB01', '0', 'admin')
ON DUPLICATE KEY UPDATE 
  `ccode`=VALUES(`ccode`), `nik`=VALUES(`nik`), `cname`=VALUES(`cname`), `cjabatan`=VALUES(`cjabatan`),
  `csalary`=VALUES(`csalary`), `ctunjjab`=VALUES(`ctunjjab`), `ctelp`=VALUES(`ctelp`), `cusername`=VALUES(`cusername`);

-- 7. MASTER SATUAN PRODUK & BAHAN
INSERT INTO `msatuan` (`id`, `cname`, `namasatuan`, `big_value`, `small_value`, `id_turunan`)
VALUES 
(1, 'PCS', 'Pieces / Lembar Satuan', 1, 1, 0),
(2, 'RIM', 'Rim (500 Lembar)', 1, 500, 1),
(3, 'BOX', 'Box / Dus Kemasan', 1, 100, 1),
(4, 'PACK', 'Pack / Bungkus', 1, 10, 1),
(5, 'KG', 'Kilogram (1000 Gram)', 1, 1000, 0),
(6, 'METER', 'Meter Lari / Persegi', 1, 1, 0),
(7, 'ROLL', 'Roll / Gulungan 50m', 1, 50, 6),
(8, 'BUKU', 'Buku / Eksemplar', 1, 1, 0)
ON DUPLICATE KEY UPDATE `cname`=VALUES(`cname`), `namasatuan`=VALUES(`namasatuan`), `big_value`=VALUES(`big_value`), `small_value`=VALUES(`small_value`);

-- 8. MASTER GROUP / KATEGORI PRODUK & BAHAN
INSERT INTO `mgroup` (`id`, `cgroup`, `cket`, `cusername`)
VALUES 
(1, 'KERTAS SHEET', 'Bahan baku kertas lembaran plano & potong', 'admin'),
(2, 'TINTA & KIMIA', 'Tinta offset, toner & chemical percetakan', 'admin'),
(3, 'BAHAN ROLL', 'Bahan flexi, banner mmt, sticker roll', 'admin'),
(4, 'PACKAGING & BINDING', 'Plastik laminasi, lem binding, spiral kawat, dus', 'admin'),
(5, 'PRODUK CETAK OFFSET', 'Produk jadi cetak offset massal (brosur, nota, buku)', 'admin'),
(6, 'PRODUK DIGITAL PRINT', 'Produk jadi cetak digital cepat (kartu nama, banner, stiker)', 'admin'),
(7, 'PRODUK PACKAGING', 'Produk jadi box kemasan makanan, obat & produk', 'admin')
ON DUPLICATE KEY UPDATE `cgroup`=VALUES(`cgroup`), `cket`=VALUES(`cket`), `cusername`=VALUES(`cusername`);

-- 9. MASTER PRODUK (BAHAN BAKU & PRODUK JADI)
INSERT INTO `mproduk` (`id`, `ccode`, `cgroup`, `CDProduct`, `cproduct`, `cnameProduct`, `cdesc`, `cunit`, `NQty`, `NPrPurc`, `NPrSell`, `ncogs`, `cwarningstock`, `Fstatus`, `username`, `nunit_bigvalue`, `nunit_smallvalue`)
VALUES 
-- Bahan Baku (CDProduct = 'M')
(1, 'GD001', 'KERTAS SHEET', 'M', 'MAT-AC260', 'Art Carton 260 GSM Plano 65x100', 'Bahan kartu nama, cover buku, dus', 'RIM', 85, 320000, 0, 320000, '10', '1', 'admin', '1', '500'),
(2, 'GD001', 'KERTAS SHEET', 'M', 'MAT-AP150', 'Art Paper 150 GSM Plano 65x100', 'Bahan brosur, flyer, isi majalah', 'RIM', 120, 210000, 0, 210000, '15', '1', 'admin', '1', '500'),
(3, 'GD001', 'KERTAS SHEET', 'M', 'MAT-HVS80', 'Kertas HVS 80 GSM Plano 65x90', 'Bahan kop surat, nota, buku teks', 'RIM', 150, 145000, 0, 145000, '20', '1', 'admin', '1', '500'),
(4, 'GD001', 'KERTAS SHEET', 'M', 'MAT-DUP310', 'Kertas Duplex 310 GSM Plano 79x109', 'Bahan box kemasan makanan & snack', 'RIM', 60, 285000, 0, 285000, '10', '1', 'admin', '1', '500'),
(5, 'GD001', 'BAHAN ROLL', 'M', 'MAT-FLX280', 'Flexi Frontlite Outdoor 280 GSM Lebar 3.2m', 'Bahan spanduk, banner outdoor', 'ROLL', 18, 650000, 0, 650000, '5', '1', 'admin', '1', '50'),
(6, 'GD001', 'BAHAN ROLL', 'M', 'MAT-VIN-GLOSS', 'Sticker Vinyl Glossy Lebar 1.27m', 'Bahan stiker label botol, kemasan', 'ROLL', 22, 520000, 0, 520000, '5', '1', 'admin', '1', '50'),
(7, 'GD001', 'TINTA & KIMIA', 'M', 'MAT-TNT-CYAN', 'Tinta Cetak Offset Toyo Ink Cyan 1 Kg', 'Tinta biru proses 4-warna', 'KG', 30, 110000, 0, 110000, '5', '1', 'admin', '1', '1'),
(8, 'GD001', 'TINTA & KIMIA', 'M', 'MAT-TNT-MAG', 'Tinta Cetak Offset Toyo Ink Magenta 1 Kg', 'Tinta merah proses 4-warna', 'KG', 30, 110000, 0, 110000, '5', '1', 'admin', '1', '1'),
(9, 'GD001', 'TINTA & KIMIA', 'M', 'MAT-TNT-YEL', 'Tinta Cetak Offset Toyo Ink Yellow 1 Kg', 'Tinta kuning proses 4-warna', 'KG', 30, 110000, 0, 110000, '5', '1', 'admin', '1', '1'),
(10, 'GD001', 'TINTA & KIMIA', 'M', 'MAT-TNT-BLK', 'Tinta Cetak Offset Toyo Ink Black 1 Kg', 'Tinta hitam proses 4-warna', 'KG', 40, 95000, 0, 95000, '5', '1', 'admin', '1', '1'),
(11, 'GD001', 'PACKAGING & BINDING', 'M', 'MAT-LAM-DOFF', 'Plastik Thermal Laminating Doff 30 Micron', 'Bahan laminasi doff elegan', 'ROLL', 15, 240000, 0, 240000, '3', '1', 'admin', '1', '1'),
(12, 'GD001', 'PACKAGING & BINDING', 'M', 'MAT-LEM-HOT', 'Lem Panas Glue Binding Pellet 1 Kg', 'Lem jilid buku perekat kuat', 'KG', 50, 48000, 0, 48000, '10', '1', 'admin', '1', '1'),

-- Produk Jadi (CDProduct = 'P')
(13, 'GD002', 'PRODUK CETAK OFFSET', 'P', 'PRD-BRO-A4', 'Brosur Promosi A4 Art Paper 150gr', 'Brosur A4 2 sisi full color minimal 1 Rim', 'RIM', 25, 180000, 350000, 180000, '5', '1', 'admin', '1', '500'),
(14, 'GD002', 'PRODUK DIGITAL PRINT', 'P', 'PRD-KRT-NAMA', 'Kartu Nama Premium 2 Sisi + Box + Doff', 'Kartu nama AC 260gr cetak bolak balik', 'BOX', 150, 22000, 45000, 22000, '20', '1', 'admin', '1', '100'),
(15, 'GD002', 'PRODUK DIGITAL PRINT', 'P', 'PRD-BNR-FLX', 'Banner Spanduk Flexi 280gr Mata Ayam', 'Banner outdoor cetak tajam siap pasang', 'METER', 200, 12000, 25000, 12000, '30', '1', 'admin', '1', '1'),
(16, 'GD002', 'PRODUK DIGITAL PRINT', 'P', 'PRD-STK-VYN', 'Sticker Vinyl Glossy Kiss Cut A3+', 'Stiker kemasan tahan air & tidak mudah robek', 'PCS', 500, 4500, 10000, 4500, '50', '1', 'admin', '1', '1'),
(17, 'GD002', 'PRODUK CETAK OFFSET', 'P', 'PRD-KAL-MEJA', 'Kalender Meja Standar Spiral Kawat 13 Lbr', 'Kalender meja custom dudukan linen hardcover', 'PCS', 120, 16000, 35000, 16000, '25', '1', 'admin', '1', '1'),
(18, 'GD002', 'PRODUK CETAK OFFSET', 'P', 'PRD-NOT-3R', 'Buku Nota NCR 3 Rangkap (50 Set)', 'Nota custom nomorator + perforasi rapi', 'BUKU', 80, 8500, 18000, 8500, '15', '1', 'admin', '1', '1'),
(19, 'GD002', 'PRODUK PACKAGING', 'P', 'PRD-BOX-SNACK', 'Dus Kemasan Snack Duplex 310gr Full Color', 'Dus kue & catering die cut kancing otomatis', 'PCS', 3500, 1200, 2500, 1200, '500', '1', 'admin', '1', '1')
ON DUPLICATE KEY UPDATE 
  `ccode`=VALUES(`ccode`), `cgroup`=VALUES(`cgroup`), `CDProduct`=VALUES(`CDProduct`),
  `cproduct`=VALUES(`cproduct`), `cnameProduct`=VALUES(`cnameProduct`), `cdesc`=VALUES(`cdesc`),
  `cunit`=VALUES(`cunit`), `NQty`=VALUES(`NQty`), `NPrPurc`=VALUES(`NPrPurc`), `NPrSell`=VALUES(`NPrSell`),
  `ncogs`=VALUES(`ncogs`), `cwarningstock`=VALUES(`cwarningstock`), `Fstatus`=VALUES(`Fstatus`);

-- 10. MASTER SUPPLIER
INSERT INTO `msupplier` (`id`, `kode_supplier`, `cname`, `addr`, `cjenis`, `ctelp`, `cs`, `bank`, `bank_owner`, `bank_number`, `username`)
VALUES 
(1, 'S001', 'PT. Surya Kertas Nusantara', 'Kawasan Industri Rungkut Megah Raya Blok M-08 Surabaya', 'Kertas Sheet & Plano', '031-8712345', 'Heru Purnomo', 'BCA', 'PT Surya Kertas Nusantara', '0148892123', 'admin'),
(2, 'S002', 'CV. Tinta Warna Mandiri', 'Jl. Letjen Sutoyo No. 89 Waru, Sidoarjo', 'Tinta Offset & Toner', '031-8945678', 'David Chandra', 'Mandiri', 'CV Tinta Warna Mandiri', '142001988231', 'admin'),
(3, 'S003', 'PT. Duta Grafika Media', 'Jl. Ngagel Tama No. 15 Surabaya', 'Bahan Roll & Digital Print', '031-5678912', 'Maya Kusuma', 'BCA', 'PT Duta Grafika Media', '0147721890', 'admin'),
(4, 'S004', 'UD. Mitra Lem & Packaging', 'Jl. Raya Tropodo Wetan No. 34 Sidoarjo', 'Lem, Lakban & Packaging', '031-8664321', 'Haryono Santoso', 'BRI', 'Haryono Santoso', '0021019876543', 'admin')
ON DUPLICATE KEY UPDATE 
  `kode_supplier`=VALUES(`kode_supplier`), `cname`=VALUES(`cname`), `addr`=VALUES(`addr`),
  `cjenis`=VALUES(`cjenis`), `ctelp`=VALUES(`ctelp`), `cs`=VALUES(`cs`), `bank`=VALUES(`bank`),
  `bank_owner`=VALUES(`bank_owner`), `bank_number`=VALUES(`bank_number`), `username`=VALUES(`username`);

-- 11. MASTER CUSTOMER / PELANGGAN
INSERT INTO `mcustomer` (`id`, `kode_customer`, `cname`, `addr`, `ctelp`, `username`)
VALUES 
(1, 'CM001', 'PT. Indofood Sukses Makmur Tbk', 'Jl. Raya Rungkut Industri No. 10 Surabaya', '031-8432100', 'admin'),
(2, 'CM002', 'CV. Berkah Abadi Bakery', 'Jl. Pahlawan No. 45 Sidoarjo', '031-8923456', 'admin'),
(3, 'CM003', 'Universitas Negeri Surabaya (UNESA)', 'Jl. Ketintang Surabaya', '031-8280009', 'admin'),
(4, 'CM004', 'Klinik Pratama Sehat Sejahtera', 'Jl. Gajah Mada No. 88 Sidoarjo', '031-8967812', 'admin'),
(5, 'CM005', 'Toko Busana Muslim Amanah', 'Pasar Atom Mall Lt. 2 Tahap IV No. 20 Surabaya', '031-3554422', 'admin'),
(6, 'CM006', 'Agensi Kreatif Digital Nusantara', 'Ruko Graha Family Blok B-12 Surabaya', '031-7345678', 'admin')
ON DUPLICATE KEY UPDATE 
  `kode_customer`=VALUES(`kode_customer`), `cname`=VALUES(`cname`), `addr`=VALUES(`addr`),
  `ctelp`=VALUES(`ctelp`), `username`=VALUES(`username`);

-- 12. MASTER MESIN
INSERT INTO `mmesin` (`id`, `ccode`, `cname`, `cgroup`, `cproduct`)
VALUES 
(1, 'MSN01', 'Mesin Offset Heidelberg Speedmaster SM 52 4-Warna', 'PRODUKSI CETAK', 'GD001'),
(2, 'MSN02', 'Mesin Digital Production Konica Minolta AccurioPress C4070', 'PRODUKSI DIGITAL', 'GD001'),
(3, 'MSN03', 'Mesin Large Format Indoor Roland TrueVIS SG3-540', 'LARGE FORMAT', 'GD001'),
(4, 'MSN04', 'Mesin Outdoor Flora Polar 320m Konica 512i', 'LARGE FORMAT', 'GD001'),
(5, 'MSN05', 'Mesin Potong Kertas Otomatis Polar Mohr 115 High Speed', 'FINISHING', 'GD001'),
(6, 'MSN06', 'Mesin Laminasi Thermal Doff & Glossy Automatic YDFM-720', 'FINISHING', 'GD001'),
(7, 'MSN07', 'Mesin Lem Panas Jilid Buku / Perfect Binding Horizon BQ-270', 'FINISHING', 'GD001')
ON DUPLICATE KEY UPDATE `ccode`=VALUES(`ccode`), `cname`=VALUES(`cname`), `cgroup`=VALUES(`cgroup`), `cproduct`=VALUES(`cproduct`);

-- 13. MASTER ACCOUNT CLASS / KLASIFIKASI AKUN
INSERT INTO `maccountclass` (`id`, `caccnt_class`, `cname`, `fstatus`, `cusername`)
VALUES 
(1, '1', 'AKTIVA / HARTA', 1, 'admin'),
(2, '2', 'KEWAJIBAN / HUTANG', 1, 'admin'),
(3, '3', 'MODAL / EKUITAS', 1, 'admin'),
(4, '4', 'PENDAPATAN / REVENUE', 1, 'admin'),
(5, '5', 'HARGA POKOK PENJUALAN (HPP)', 1, 'admin'),
(6, '6', 'BEBAN OPERASIONAL', 1, 'admin')
ON DUPLICATE KEY UPDATE `caccnt_class`=VALUES(`caccnt_class`), `cname`=VALUES(`cname`), `fstatus`=VALUES(`fstatus`), `cusername`=VALUES(`cusername`);

-- 14. MASTER COA / CHART OF ACCOUNTS
INSERT INTO `maccount` (`id`, `caccnt`, `cdesc`, `ccode`, `fAccount`, `cPeriod`, `NDebet`, `NCredit`, `NNominal`, `fstatus`, `cusername`, `cnote`)
VALUES 
(1, '1101', 'Kas Kasir / Kas Tunai', 'GD001', '1', '2026', 15000000, 0, 15000000, '1', 'admin', 'Kas operasional harian kasir & penerimaan tunai'),
(2, '1102', 'Bank Central Asia (BCA)', 'GD001', '1', '2026', 85000000, 0, 85000000, '1', 'admin', 'Rekening penerimaan pelanggan & giro masuk'),
(3, '1103', 'Bank Mandiri', 'GD001', '1', '2026', 45000000, 0, 45000000, '1', 'admin', 'Rekening pembayaran supplier & operasional'),
(4, '1104', 'Piutang Usaha Pelanggan', 'GD001', '1', '2026', 28500000, 0, 28500000, '1', 'admin', 'Piutang cetak PO & tagihan invoice tempo'),
(5, '1105', 'Persediaan Bahan Baku (Raw Material)', 'GD001', '1', '2026', 35000000, 0, 35000000, '1', 'admin', 'Nilai stok fisik kertas, tinta, dan bahan roll'),
(6, '1106', 'Persediaan Barang Jadi (Finished Goods)', 'GD001', '1', '2026', 18000000, 0, 18000000, '1', 'admin', 'Nilai persediaan barang cetak siap kirim'),
(7, '1201', 'Aktiva Tetap - Mesin Percetakan', 'GD001', '1', '2026', 450000000, 0, 450000000, '1', 'admin', 'Inventaris mesin offset, digital press & finishing'),
(8, '1202', 'Akumulasi Penyusutan Mesin', 'GD001', '1', '2026', 0, 45000000, -45000000, '1', 'admin', 'Akumulasi depresiasi aktiva tetap mesin'),
(9, '2101', 'Hutang Usaha Supplier Bahan', 'GD001', '2', '2026', 0, 22000000, 22000000, '1', 'admin', 'Hutang pembelian kertas & tinta tempo 30 hari'),
(10, '2102', 'Hutang Gaji & Tunjangan Karyawan', 'GD001', '2', '2026', 0, 15000000, 15000000, '1', 'admin', 'Alokasi payroll gaji akhir bulan'),
(11, '3101', 'Modal Pemilik / Disetor', 'GD001', '3', '2026', 0, 500000000, 500000000, '1', 'admin', 'Modal awal pendirian percetakan'),
(12, '3201', 'Laba Ditahan', 'GD001', '3', '2026', 0, 94500000, 94500000, '1', 'admin', 'Akumulasi laba bersih periode sebelumnya'),
(13, '4101', 'Pendapatan Penjualan Cetak Offset', 'GD001', '4', '2026', 0, 0, 0, '1', 'admin', 'Omset dari pesanan cetak brosur, majalah, nota'),
(14, '4102', 'Pendapatan Penjualan Digital Printing', 'GD001', '4', '2026', 0, 0, 0, '1', 'admin', 'Omset dari cetak stiker, banner, kartu nama'),
(15, '4103', 'Pendapatan Jasa Finishing & Desain', 'GD001', '4', '2026', 0, 0, 0, '1', 'admin', 'Pendapatan biaya desain, setting, dan jilid'),
(16, '5101', 'HPP - Pemakaian Bahan Kertas', 'GD001', '5', '2026', 0, 0, 0, '1', 'admin', 'Harga pokok kertas yang terpakai pada SPK'),
(17, '5102', 'HPP - Pemakaian Tinta & Kimia', 'GD001', '5', '2026', 0, 0, 0, '1', 'admin', 'Harga pokok tinta & bahan kimia cetak'),
(18, '5103', 'HPP - Biaya Tenaga Kerja Langsung', 'GD001', '5', '2026', 0, 0, 0, '1', 'admin', 'Upah borongan & lembur operator produksi'),
(19, '6101', 'Beban Gaji Karyawan & Staff', 'GD001', '6', '2026', 0, 0, 0, '1', 'admin', 'Gaji pokok & tunjangan staff bulanan'),
(20, '6102', 'Beban Listrik, Air & Workshop', 'GD001', '6', '2026', 0, 0, 0, '1', 'admin', 'Biaya daya listrik industri & utilitas'),
(21, '6103', 'Beban Pemeliharaan & Sparepart Mesin', 'GD001', '6', '2026', 0, 0, 0, '1', 'admin', 'Oli, karet roll, service berkala mesin'),
(22, '6104', 'Beban Pemasaran & Promosi', 'GD001', '6', '2026', 0, 0, 0, '1', 'admin', 'Iklan, sample produk, dan fee marketing')
ON DUPLICATE KEY UPDATE 
  `caccnt`=VALUES(`caccnt`), `cdesc`=VALUES(`cdesc`), `ccode`=VALUES(`ccode`),
  `fAccount`=VALUES(`fAccount`), `cPeriod`=VALUES(`cPeriod`), `NDebet`=VALUES(`NDebet`),
  `NCredit`=VALUES(`NCredit`), `NNominal`=VALUES(`NNominal`), `fstatus`=VALUES(`fstatus`),
  `cusername`=VALUES(`cusername`), `cnote`=VALUES(`cnote`);

-- 15. MASTER COST PRODUKSI
INSERT INTO `mcost` (`id`, `kode`, `cost`, `cket`)
VALUES 
(1, 'C001', 'Biaya Listrik Mesin Cetak Offset', 'Dihitung per jam jalan mesin cetak (Rp 75.000/jam)'),
(2, 'C002', 'Biaya Pembuatan Plat CTP Aluminium', 'Output plat 4 warna CMYK ukuran 52 (Rp 35.000/plat)'),
(3, 'C003', 'Biaya Pisau Pond / Die-Cut Custom', 'Pembuatan pisau pon bentuk dus & packaging kemasan'),
(4, 'C004', 'Biaya Bahan Packing & Pengiriman', 'Kardus packing, lakban, shrink wrap & strapping band')
ON DUPLICATE KEY UPDATE `kode`=VALUES(`kode`), `cost`=VALUES(`cost`), `cket`=VALUES(`cket`);

-- 16. MASTER STEP / TAHAPAN PRODUKSI
INSERT INTO `mstepproduction` (`id`, `ccode`, `kode`, `cname`, `cket`, `idmesin`, `cfinal_process`, `cusername`, `urut`)
VALUES 
(1, 'GD001', 'STP01', '1. Desain Pre-Press & Output CTP', 'Pemeriksaan file, separasi warna & burning plat CTP', '1', 0, 'admin', '1'),
(2, 'GD001', 'STP02', '2. Persiapan Bahan Kertas & Potong Awal', 'Potong bahan plano menjadi ukuran siap masuk mesin cetak', '5', 0, 'admin', '2'),
(3, 'GD001', 'STP03', '3. Proses Cetak Offset / Digital', 'Pencetakan bahan sesuai SPK & approval sample warna', '1', 0, 'admin', '3'),
(4, 'GD001', 'STP04', '4. Proses Laminasi Thermal (Doff/Glossy)', 'Pelapisan plastik thermal pelindung cetakan', '6', 0, 'admin', '4'),
(5, 'GD001', 'STP05', '5. Finishing Potong Sisir / Lem Jilid', 'Pemotongan ukuran jadi atau pengeleman jilid buku', '7', 0, 'admin', '5'),
(6, 'GD001', 'STP06', '6. Quality Control & Packing Pengiriman', 'Pemeriksaan kualitas akhir dan pengemasan ke kardus', '0', 1, 'admin', '6')
ON DUPLICATE KEY UPDATE 
  `ccode`=VALUES(`ccode`), `kode`=VALUES(`kode`), `cname`=VALUES(`cname`),
  `cket`=VALUES(`cket`), `idmesin`=VALUES(`idmesin`), `cfinal_process`=VALUES(`cfinal_process`),
  `cusername`=VALUES(`cusername`), `urut`=VALUES(`urut`);

-- 17. MASTER ERROR / REJECT PRODUKSI
INSERT INTO `merrorproduction` (`id`, `kode`, `cname`)
VALUES 
(1, 'ERR01', 'Kertas Nyangkut / Paper Jam'),
(2, 'ERR02', 'Tinta Belang / Ketebalan Warna Tidak Merata'),
(3, 'ERR03', 'Register Warna Meleset / Berbayang'),
(4, 'ERR04', 'Salah Presisi Potong / Ukuran Tidak Sesuai'),
(5, 'ERR05', 'Laminasi Berkerut / Ada Gelembung Udara'),
(6, 'ERR06', 'Lem Jilid Kurang Melekat / Halaman Lepas')
ON DUPLICATE KEY UPDATE `kode`=VALUES(`kode`), `cname`=VALUES(`cname`);

-- 18. MASTER PAYMENT (TERMIN PEMBAYARAN)
INSERT INTO `mpayment` (`id`, `id_pay`, `cdesc`)
VALUES 
(1, 'CASH', 'Pembayaran Tunai Kasir'),
(2, 'TRANSFER-BCA', 'Transfer Rekening Bank BCA'),
(3, 'TRANSFER-MANDIRI', 'Transfer Rekening Bank Mandiri'),
(4, 'QRIS', 'Pembayaran QRIS Statis / Dinamis'),
(5, 'GIRO', 'Pembayaran Bilyet Giro Tempo'),
(6, 'TEMPO', 'Kredit Piutang Dagang 30 Hari')
ON DUPLICATE KEY UPDATE `id_pay`=VALUES(`id_pay`), `cdesc`=VALUES(`cdesc`);

-- 19. MASTER METODE PEMBAYARAN KASIR
INSERT INTO `mmetodepembayaran` (`id`, `KodeBayar`, `MetodeBayar`)
VALUES 
(1, '01', 'TUNAI / CASH'),
(2, '02', 'TRANSFER BCA'),
(3, '03', 'TRANSFER MANDIRI'),
(4, '04', 'QRIS'),
(5, '05', 'BON / KREDIT')
ON DUPLICATE KEY UPDATE `KodeBayar`=VALUES(`KodeBayar`), `MetodeBayar`=VALUES(`MetodeBayar`);

-- 20. MASTER OPERASIONAL
INSERT INTO `moperasional` (`id`, `id_op`, `cdesc`, `nstatus`, `username`)
VALUES 
(1, 'OP01', 'Tagihan Listrik PLN Workshop & Kantor', 1, 'admin'),
(2, 'OP02', 'Biaya Akses Internet Fiber & Telepon Kantor', 1, 'admin'),
(3, 'OP03', 'Uang Makan & Konsumsi Lembur Karyawan', 1, 'admin'),
(4, 'OP04', 'BBM & Operasional Armada Mobil Box Pengiriman', 1, 'admin'),
(5, 'OP05', 'Pembelian ATK, Nota & Perlengkapan Kantor', 1, 'admin'),
(6, 'OP06', 'Biaya Servis Rutin & Penggantian Oli Mesin', 1, 'admin')
ON DUPLICATE KEY UPDATE `id_op`=VALUES(`id_op`), `cdesc`=VALUES(`cdesc`), `nstatus`=VALUES(`nstatus`), `username`=VALUES(`username`);

-- 21. MASTER CUTI & IZIN
INSERT INTO `mcuti` (`id`, `cidc`, `cdesc`)
VALUES 
(1, 'CT001', 'Cuti Tahunan (12 Hari Kerja)'),
(2, 'CT002', 'Cuti Khusus Pernikahan (3 Hari)'),
(3, 'CT003', 'Cuti Bersalin / Melahirkan (3 Bulan)'),
(4, 'CK019', 'Ijin Sakit (Surat Dokter)'),
(5, 'CK020', 'Ijin Terlambat Masuk Kantor'),
(6, 'CK021', 'Ijin Pulang Cepat / Urusan Mendesak'),
(7, 'CK022', 'Ijin Urusan Keluarga / Kedukaan')
ON DUPLICATE KEY UPDATE `cidc`=VALUES(`cidc`), `cdesc`=VALUES(`cdesc`);

-- 22. MASTER PAYROLL / ATURAN LEMBUR
INSERT INTO `mpayroll` (`id`, `kode`, `cdesc`, `key1`, `key2`, `key3`, `value1`, `value2`, `value3`, `flag`)
VALUES 
(1, 'LMB01', 'Lembur Hari Kerja Biasa', 'Start Menit', 'Kelipatan Menit', 'Nominal', 60, 60, 25000, 'LEMBUR'),
(2, 'LMB02', 'Lembur Hari Libur / Weekend', 'Start Menit', 'Kelipatan Menit', 'Nominal', 60, 60, 40000, 'LEMBUR')
ON DUPLICATE KEY UPDATE `kode`=VALUES(`kode`), `cdesc`=VALUES(`cdesc`), `key1`=VALUES(`key1`), `key2`=VALUES(`key2`), `key3`=VALUES(`key3`), `value1`=VALUES(`value1`), `value2`=VALUES(`value2`), `value3`=VALUES(`value3`), `flag`=VALUES(`flag`);

