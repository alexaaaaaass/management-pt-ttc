-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ttc
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `absensis`
--

DROP TABLE IF EXISTS `absensis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `karyawan_id` bigint(20) unsigned DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `pin` varchar(50) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `departemen` varchar(100) DEFAULT NULL,
  `kantor` varchar(100) DEFAULT NULL,
  `tanggal_scan` datetime DEFAULT NULL,
  `sn` varchar(100) DEFAULT NULL,
  `mesin` varchar(100) DEFAULT NULL,
  `workcode` varchar(50) DEFAULT NULL,
  `io` enum('Masuk','Keluar') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  CONSTRAINT `absensis_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensis`
--

LOCK TABLES `absensis` WRITE;
/*!40000 ALTER TABLE `absensis` DISABLE KEYS */;
INSERT INTO `absensis` VALUES (1,1,'12345','12345','kepsek','Human Resource','freferferfer','2026-04-14 11:23:05','123','1','1','Masuk','2026-04-27 21:23:25','2026-04-27 21:23:25');
/*!40000 ALTER TABLE `absensis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_form_items`
--

DROP TABLE IF EXISTS `approval_form_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_form_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approval_form_id` bigint(20) unsigned NOT NULL,
  `finish_good_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `komponen` varchar(100) DEFAULT NULL,
  `isi` varchar(50) DEFAULT NULL,
  `kertas` varchar(255) DEFAULT NULL,
  `warna` varchar(100) DEFAULT NULL,
  `finishing` text DEFAULT NULL,
  `qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `satuan` varchar(50) NOT NULL DEFAULT 'Pcs',
  `cek_desain` tinyint(1) NOT NULL DEFAULT 0,
  `cek_plate` tinyint(1) NOT NULL DEFAULT 0,
  `cek_produksi` tinyint(1) NOT NULL DEFAULT 0,
  `cek_qc` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_form_items_approval_form_id_foreign` (`approval_form_id`),
  KEY `approval_form_items_finish_good_item_id_foreign` (`finish_good_item_id`),
  CONSTRAINT `approval_form_items_approval_form_id_foreign` FOREIGN KEY (`approval_form_id`) REFERENCES `approval_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_form_items_finish_good_item_id_foreign` FOREIGN KEY (`finish_good_item_id`) REFERENCES `finish_good_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_form_items`
--

LOCK TABLES `approval_form_items` WRITE;
/*!40000 ALTER TABLE `approval_form_items` DISABLE KEYS */;
INSERT INTO `approval_form_items` VALUES (4,3,4,'ETIKET DJANDA BOLD 20 SKM','SKM','FILTER','26','Metalized','8','Spot UV, Cutting',0.00,'Pcs',1,1,1,1,'2026-10-17 20:31:56','2026-10-17 20:31:56'),(5,4,13,'ETIKET DJARUM SUPER','SKM','ETIKET','20','KERTAS IVORY ROLLL','6','Spot UV, emboss, lem, cutting',10000.00,'Pcs',1,1,1,1,'2026-10-18 00:24:06','2026-10-18 00:24:06'),(6,5,24,'ETIKET WALI SONGO','SKM','Etiket','20','Metalized','8','Spot UV, Emboss, Lem',100.00,'Pcs',1,1,1,1,'2026-10-18 06:41:51','2026-10-18 06:44:25'),(7,6,25,'Dafi ','SKT','ETIKET','16','METALIZE','4','embos,lem,cutting',50.00,'Pcs',0,0,0,0,'2026-10-20 06:41:12','2026-10-20 06:42:01');
/*!40000 ALTER TABLE `approval_form_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_forms`
--

DROP TABLE IF EXISTS `approval_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_forms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(100) DEFAULT NULL,
  `form_no` varchar(100) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `lead_time` varchar(100) DEFAULT NULL,
  `catatan_produksi` text DEFAULT NULL,
  `file_design` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'draft',
  `disetujui_marketing` varchar(150) DEFAULT NULL,
  `disetujui_produksi` varchar(150) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_forms_form_no_unique` (`form_no`),
  KEY `approval_forms_customer_id_foreign` (`customer_id`),
  KEY `approval_forms_created_by_foreign` (`created_by`),
  KEY `approval_forms_order_id_index` (`order_id`),
  CONSTRAINT `approval_forms_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_forms_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_forms`
--

LOCK TABLES `approval_forms` WRITE;
/*!40000 ALTER TABLE `approval_forms` DISABLE KEYS */;
INSERT INTO `approval_forms` VALUES (3,'OR-26-X-	CS001-001','AF-26-X-	CS001-001',1,'2026-10-18','1 bulan','Perhatikan warna cetak\nFinishing harus Presisi\nQC Sebelum pengemasan','approval-forms/designs/01M56H1TBD18A06Z7NZGBTJXGQ.JPG','draft','Coba Coba','Coba coba',1,'2026-10-17 20:31:56','2026-10-17 20:31:56'),(4,'OR-26-X-CS003-002','AF-26-X-CS003-002',3,'2026-10-18','2 minggu','Perhatikan warna cetak\nFinishing harus Presisi\nQC Sebelum pengemasan','approval-forms/designs/01M56YAY3T7DBNHZ8CDYW83TF9.jpeg','converted_to_oc','Budi Santoso','Juan ',1,'2026-10-18 00:24:06','2026-10-18 01:05:04'),(5,'OR-26-X-CS003-003','AF-26-X-CS003-003',3,'2026-10-18',NULL,'Perhatikan warna cetak\nFinishing harus Presisi\nQC Sebelum pengemasan','approval-forms/designs/01M57KYKRYD7Y0ME33Y0T5JYPH.JPG','draft',NULL,NULL,1,'2026-10-18 06:41:51','2026-10-18 06:41:51'),(6,'OR-26-X-001-004','AF-26-X-001-004',4,'2026-10-20','21 ','Perhatikan warna cetak\nFinishing harus Presisi\nQC Sebelum pengemasan',NULL,'draft',NULL,NULL,1,'2026-10-20 06:41:12','2026-10-20 06:41:12');
/*!40000 ALTER TABLE `approval_forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `berita_acara_items`
--

DROP TABLE IF EXISTS `berita_acara_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `berita_acara_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `berita_acara_id` bigint(20) unsigned NOT NULL,
  `item_product` varchar(255) DEFAULT NULL,
  `qty_kedatangan` decimal(15,2) DEFAULT NULL,
  `qty_penerimaan` decimal(15,2) DEFAULT NULL,
  `qty_penolakan` decimal(15,2) DEFAULT NULL,
  `catatan_penerimaan` text DEFAULT NULL,
  `foto_referensi` varchar(255) DEFAULT NULL,
  `foto_fisik` varchar(255) DEFAULT NULL,
  `urutan` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `berita_acara_items_berita_acara_id_foreign` (`berita_acara_id`),
  CONSTRAINT `berita_acara_items_berita_acara_id_foreign` FOREIGN KEY (`berita_acara_id`) REFERENCES `berita_acaras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `berita_acara_items`
--

LOCK TABLES `berita_acara_items` WRITE;
/*!40000 ALTER TABLE `berita_acara_items` DISABLE KEYS */;
INSERT INTO `berita_acara_items` VALUES (1,1,'kardus',5.00,5.00,NULL,'barang rusak\n',NULL,'berita-acara/fisik/01M5C44ZQQZ5ZSG7QMWNBYGFN5.png',1,'2026-10-20 00:41:55','2026-10-20 00:41:55'),(2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'2026-10-20 00:42:39','2026-10-20 00:42:39'),(3,3,'KARDUS',20.00,20.00,5.00,'BARANG RUSAK ',NULL,'berita-acara/fisik/01M5CZGFZ5M6TKAR34QRNS3PV4.png',1,'2026-10-20 08:40:03','2026-10-20 08:40:03');
/*!40000 ALTER TABLE `berita_acara_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `berita_acaras`
--

DROP TABLE IF EXISTS `berita_acaras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `berita_acaras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_ba` varchar(255) NOT NULL,
  `tipe` enum('rapat','complaint_produksi') NOT NULL DEFAULT 'complaint_produksi',
  `tanggal` date NOT NULL,
  `no_surat_jalan_invoice` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `surat_jalan_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `item_product` varchar(255) DEFAULT NULL,
  `foto_bukti` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`foto_bukti`)),
  `kronologi` text DEFAULT NULL,
  `analisa_produksi` text DEFAULT NULL,
  `tindakan_solusi` text DEFAULT NULL,
  `peserta` text DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `ttd_qc` varchar(255) DEFAULT NULL,
  `ttd_customer` varchar(255) DEFAULT NULL,
  `status` enum('draft','proses','selesai') NOT NULL DEFAULT 'draft',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `berita_acaras_no_ba_unique` (`no_ba`),
  KEY `berita_acaras_customer_id_foreign` (`customer_id`),
  KEY `berita_acaras_surat_jalan_id_foreign` (`surat_jalan_id`),
  KEY `berita_acaras_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `berita_acaras_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `berita_acaras_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `berita_acaras_surat_jalan_id_foreign` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `berita_acaras`
--

LOCK TABLES `berita_acaras` WRITE;
/*!40000 ALTER TABLE `berita_acaras` DISABLE KEYS */;
INSERT INTO `berita_acaras` VALUES (1,'BA/COMP/2026/001','complaint_produksi','2026-10-20','SJ-20260719-001 / 000001/INV/072026',1,19,11,'KARDUS 485 X 268 X 335 MM','[\"berita-acara\\/01M5C34GNJYFZHXP9YRC3H7ATR.png\"]','kardus rusak saat di terima',NULL,'potong harga jual','juan','pembuatan Berita Acara','potong sesuai dengan harga per pcs nya\n','ACC',NULL,'draft','2026-10-20 00:44:10','2026-10-20 00:24:11','2026-10-20 00:44:10'),(2,'BA/COMP/2026/01','complaint_produksi','2026-10-20',NULL,2,NULL,NULL,NULL,'[]',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'draft',NULL,'2026-10-20 00:27:18','2026-10-20 00:27:18'),(3,'BA/COMP/2026/002','complaint_produksi','2026-10-20','SJ-20260719-001 / 000001/INV/072026',1,19,11,'KARDUS 485 X 268 X 335 MM','[\"berita-acara\\/01M5CZGFZ1SH4DRYGS90C535Z3.png\"]','BARANG RUSAK ',NULL,'MENGURANGI HARGA PADA INV','PT CREATIVE MEDIA',NULL,'ACC',NULL,NULL,'draft',NULL,'2026-10-20 08:40:03','2026-10-20 08:40:03');
/*!40000 ALTER TABLE `berita_acaras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bonus_karyawans`
--

DROP TABLE IF EXISTS `bonus_karyawans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bonus_karyawans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_gudang` varchar(50) DEFAULT 'UGRMS',
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `tanggal_bonus` date NOT NULL,
  `periode` varchar(20) DEFAULT NULL,
  `nilai_bonus` decimal(18,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonus_karyawans_karyawan_id_foreign` (`karyawan_id`),
  CONSTRAINT `bonus_karyawans_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bonus_karyawans`
--

LOCK TABLES `bonus_karyawans` WRITE;
/*!40000 ALTER TABLE `bonus_karyawans` DISABLE KEYS */;
/*!40000 ALTER TABLE `bonus_karyawans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_items`
--

DROP TABLE IF EXISTS `category_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_item_category` varchar(255) NOT NULL,
  `nama_category` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_items_kode_item_category_unique` (`kode_item_category`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_items`
--

LOCK TABLES `category_items` WRITE;
/*!40000 ALTER TABLE `category_items` DISABLE KEYS */;
INSERT INTO `category_items` VALUES (1,'INVT','INVENTARIS KANTOR','2026-03-06 16:19:40','2026-03-06 16:19:55'),(2,'MI','MATERIAL PRODUCTION','2026-03-06 16:20:13','2026-03-06 16:20:13'),(3,'JS','JASA','2026-03-06 16:20:20','2026-03-06 16:20:20'),(4,'MSN','MESIN','2026-03-06 16:20:26','2026-03-06 16:20:26'),(5,'FG','FINISH GOODS','2026-04-28 01:49:30','2026-04-28 01:49:30'),(6,'CH','CHEMICAL','2026-05-09 05:30:50','2026-05-09 05:30:50');
/*!40000 ALTER TABLE `category_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coa_classes`
--

DROP TABLE IF EXISTS `coa_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coa_classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_coa_classes_karyawan` (`karyawan_id`),
  CONSTRAINT `fk_coa_classes_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coa_classes`
--

LOCK TABLES `coa_classes` WRITE;
/*!40000 ALTER TABLE `coa_classes` DISABLE KEYS */;
INSERT INTO `coa_classes` VALUES (1,1,'CL_ASET','ASET PT','aktif','2026-04-24 00:05:23','2026-04-24 00:05:23');
/*!40000 ALTER TABLE `coa_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coa_movements`
--

DROP TABLE IF EXISTS `coa_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coa_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coa_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `reference_type` varchar(50) NOT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `movement_type` varchar(30) NOT NULL,
  `nominal` decimal(18,2) NOT NULL DEFAULT 0.00,
  `debit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `kredit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `balance_before` decimal(18,2) NOT NULL DEFAULT 0.00,
  `balance_after` decimal(18,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coa_movements_created_by_foreign` (`created_by`),
  KEY `coa_movements_coa_id_tanggal_id_index` (`coa_id`,`tanggal`,`id`),
  KEY `coa_movements_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  CONSTRAINT `coa_movements_coa_id_foreign` FOREIGN KEY (`coa_id`) REFERENCES `master_coas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coa_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coa_movements`
--

LOCK TABLES `coa_movements` WRITE;
/*!40000 ALTER TABLE `coa_movements` DISABLE KEYS */;
INSERT INTO `coa_movements` VALUES (49,1,'2026-10-20','INV_PAYMENT',13,'000001/INV/072026','DEBIT',280000.00,280000.00,0.00,5000.00,285000.00,'Penerimaan Pembayaran Customer Invoice 000001/INV/072026',1,'2026-10-20 08:35:04','2026-10-20 08:35:04'),(50,505,'2026-10-31','INV_PAYMENT',14,'000003/INV/102026','DEBIT',50000324.00,50000324.00,0.00,0.00,50000324.00,'Penerimaan Pembayaran Customer Invoice 000003/INV/102026',1,'2026-10-31 01:01:42','2026-10-31 01:01:42');
/*!40000 ALTER TABLE `coa_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_customer` varchar(255) NOT NULL,
  `nama_customer` varchar(255) NOT NULL,
  `alamat_lengkap` text NOT NULL,
  `alamat_kedua` text DEFAULT NULL,
  `alamat_ketiga` text DEFAULT NULL,
  `kode_group` varchar(255) NOT NULL,
  `nama_group` varchar(255) NOT NULL,
  `nama_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_kode_customer_unique` (`kode_customer`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'	CS001','PT Creative Media Surabaya','Jln Nginden Intan',NULL,NULL,'CM','CM',NULL,'2026-03-05 09:38:58','2026-03-05 09:38:58'),(2,'C005','PR. JUMALI','Kudus, Jawa Tengah',NULL,NULL,'GRP-01','Rokok Group','Pak Jumali','2026-10-15 02:33:52','2026-10-15 02:33:52'),(3,'CS003','Farhan Abdillah','Sidoarjo',NULL,NULL,'CS003','Jejak Group','Juan','2026-10-18 00:22:08','2026-10-18 00:22:08'),(4,'001','Catur Wiratama Tobacco ','jl singosari',NULL,NULL,'01','ppn','pak andreas','2026-10-20 06:35:10','2026-10-20 06:35:10');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cutis`
--

DROP TABLE IF EXISTS `cutis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cutis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jumlah_hari` int(11) NOT NULL DEFAULT 1,
  `jenis_cuti` varchar(100) NOT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cutis_karyawan` (`karyawan_id`),
  CONSTRAINT `fk_cutis_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cutis`
--

LOCK TABLES `cutis` WRITE;
/*!40000 ALTER TABLE `cutis` DISABLE KEYS */;
INSERT INTO `cutis` VALUES (1,1,'2026-06-05','2026-06-10',6,'Cuti Menikah','cuti/01KTB893X60GK8DKMNNCKDYNVH.png','nikah dong','2026-06-04 23:42:10','2026-06-04 23:42:10');
/*!40000 ALTER TABLE `cutis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diemakings`
--

DROP TABLE IF EXISTS `diemakings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diemakings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `spk_id` bigint(20) unsigned NOT NULL,
  `mesin_id` bigint(20) unsigned NOT NULL,
  `operator_id` bigint(20) unsigned NOT NULL,
  `tanggal_entri` date NOT NULL,
  `proses_diemaking` enum('UV Varnish','Hot Print','UV Spot','UV Holo','Embos','Cutting') NOT NULL,
  `tahap_diemaking` enum('Proses Die Making 1','Proses Die Making 2') NOT NULL,
  `hasil_baik` int(11) NOT NULL DEFAULT 0,
  `hasil_rusak` int(11) NOT NULL DEFAULT 0,
  `semi_waste` int(11) NOT NULL DEFAULT 0,
  `note_waste` enum('CETAK KOTOR','CETAK LUNTUR','CETAK BINTIK') DEFAULT NULL,
  `keterangan_spk` enum('Reguler','Subcount') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_diemaking_spk` (`spk_id`),
  KEY `fk_diemaking_mesin` (`mesin_id`),
  KEY `fk_diemaking_operator` (`operator_id`),
  CONSTRAINT `fk_diemaking_mesin` FOREIGN KEY (`mesin_id`) REFERENCES `mesin_die_makings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_diemaking_operator` FOREIGN KEY (`operator_id`) REFERENCES `operator_diemaking` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_diemaking_spk` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diemakings`
--

LOCK TABLES `diemakings` WRITE;
/*!40000 ALTER TABLE `diemakings` DISABLE KEYS */;
/*!40000 ALTER TABLE `diemakings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `error_productions`
--

DROP TABLE IF EXISTS `error_productions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `error_productions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_error` varchar(255) NOT NULL,
  `nama_error` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_error` (`kode_error`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `error_productions`
--

LOCK TABLES `error_productions` WRITE;
/*!40000 ALTER TABLE `error_productions` DISABLE KEYS */;
INSERT INTO `error_productions` VALUES (1,'ERROR001','CETAK TIDAK TERCETAK / LOLOS CETAK',NULL,NULL),(2,'ERROR002','CETAK KOTOR / NODA',NULL,NULL),(3,'ERROR003','CETAK BOCOR / BANJIR',NULL,NULL),(4,'ERROR004','CETAK LUNTUR',NULL,NULL),(5,'ERROR005','CETAK TIDAK REGISTER / MBAYANG',NULL,NULL),(6,'ERROR006','CETAK WARNA MUDA',NULL,NULL),(7,'ERROR007','CETAK WARNA TEBAL',NULL,NULL),(8,'ERROR008','CETAK WARNA BELANG',NULL,NULL),(9,'ERROR009','CETAK MLOI',NULL,NULL),(10,'ERROR010','CETAK BINTIK',NULL,NULL);
/*!40000 ALTER TABLE `error_productions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_return_items`
--

DROP TABLE IF EXISTS `external_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `external_return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `external_return_id` bigint(20) unsigned NOT NULL,
  `penerimaan_barang_item_id` bigint(20) unsigned NOT NULL,
  `qty_return` decimal(15,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `external_return_items_external_return_id_foreign` (`external_return_id`),
  KEY `external_return_items_penerimaan_barang_item_id_foreign` (`penerimaan_barang_item_id`),
  CONSTRAINT `external_return_items_external_return_id_foreign` FOREIGN KEY (`external_return_id`) REFERENCES `external_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `external_return_items_penerimaan_barang_item_id_foreign` FOREIGN KEY (`penerimaan_barang_item_id`) REFERENCES `penerimaan_barang_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_return_items`
--

LOCK TABLES `external_return_items` WRITE;
/*!40000 ALTER TABLE `external_return_items` DISABLE KEYS */;
INSERT INTO `external_return_items` VALUES (1,1,18,10.00,NULL,'2026-07-08 16:34:12','2026-07-08 16:34:12');
/*!40000 ALTER TABLE `external_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_returns`
--

DROP TABLE IF EXISTS `external_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `external_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_return` varchar(255) NOT NULL,
  `penerimaan_barang_id` bigint(20) unsigned NOT NULL,
  `tanggal_return` date NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('draft','approved','cancel') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_returns_no_return_unique` (`no_return`),
  KEY `external_returns_penerimaan_barang_id_foreign` (`penerimaan_barang_id`),
  KEY `external_returns_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `external_returns_penerimaan_barang_id_foreign` FOREIGN KEY (`penerimaan_barang_id`) REFERENCES `penerimaan_barangs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `external_returns_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `master_suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_returns`
--

LOCK TABLES `external_returns` WRITE;
/*!40000 ALTER TABLE `external_returns` DISABLE KEYS */;
INSERT INTO `external_returns` VALUES (1,'RET-20260708-001',25,'2026-07-08',1,NULL,'cancel','2026-07-08 16:34:12','2026-07-08 18:50:47');
/*!40000 ALTER TABLE `external_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finish_good_item_materials`
--

DROP TABLE IF EXISTS `finish_good_item_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `finish_good_item_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `finish_good_item_id` bigint(20) unsigned NOT NULL,
  `master_item_id` bigint(20) unsigned DEFAULT NULL,
  `departemen_id` bigint(20) unsigned DEFAULT NULL,
  `qty` double DEFAULT 0,
  `waste` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_fgim_master_item` (`master_item_id`),
  KEY `fk_fgim_departemen` (`departemen_id`),
  KEY `fk_fgim_fgi` (`finish_good_item_id`),
  CONSTRAINT `fk_fgim_departemen` FOREIGN KEY (`departemen_id`) REFERENCES `master_departemens` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fgim_master_item` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finish_good_item_materials`
--

LOCK TABLES `finish_good_item_materials` WRITE;
/*!40000 ALTER TABLE `finish_good_item_materials` DISABLE KEYS */;
INSERT INTO `finish_good_item_materials` VALUES (1,1,2,1,1,0.0000,'blablabla','2026-03-19 09:10:54','2026-03-19 09:10:54'),(9,6,1,1,1,1.0000,NULL,'2026-04-15 01:49:20','2026-04-15 01:49:20'),(10,4,3,2,NULL,3.0000,'-','2026-04-24 20:01:05','2026-10-17 20:53:02'),(11,4,2,2,NULL,5.0000,'-','2026-04-24 20:01:05','2026-10-17 20:53:02'),(12,8,5,4,1,0.0000,NULL,'2026-04-28 02:00:03','2026-04-28 02:00:03'),(13,9,1,2,5,0.0000,'-','2026-05-09 05:34:52','2026-05-09 08:03:02'),(16,9,4,2,1,0.0000,'-','2026-05-09 05:34:52','2026-05-09 05:34:52'),(17,10,11,2,1,0.0000,NULL,'2026-05-25 19:40:55','2026-05-25 19:40:55'),(18,10,12,2,0,0.0000,'TINTA','2026-05-25 19:40:55','2026-05-25 19:40:55'),(19,10,13,2,0,0.0000,'TINTA MAGENTA','2026-05-25 19:40:55','2026-05-25 19:40:55'),(20,10,14,2,0,0.0000,NULL,'2026-05-25 19:40:55','2026-05-25 19:40:55'),(21,10,15,2,0,0.0000,NULL,'2026-05-25 19:40:55','2026-05-25 19:40:55'),(22,10,16,2,0,0.0000,'TINTA METALIZED WHITE','2026-05-25 19:40:55','2026-05-25 19:40:55'),(23,10,17,2,0,0.0000,'SPAREGUM','2026-05-25 19:40:55','2026-05-25 19:40:55'),(24,10,18,2,0,0.0000,NULL,'2026-05-25 19:40:55','2026-05-25 19:40:55'),(25,10,19,2,0,0.0000,'FOUNTAIN','2026-05-25 19:40:55','2026-05-25 19:40:55'),(26,10,6,2,0,0.0000,NULL,'2026-05-25 19:40:55','2026-05-25 19:40:55'),(27,10,20,2,0,0.0000,'PLATE CLEANER','2026-05-25 19:40:55','2026-05-25 19:40:55'),(28,10,21,2,1,0.0000,NULL,'2026-05-25 19:40:55','2026-05-25 19:40:55'),(29,9,6,2,0.5,0.0000,NULL,'2026-05-29 03:38:54','2026-05-29 03:38:54'),(30,11,1,2,1,0.0000,NULL,'2026-05-29 03:40:59','2026-05-29 03:40:59'),(31,11,6,2,0.05,0.0000,NULL,'2026-05-29 03:40:59','2026-05-29 03:40:59'),(32,11,15,2,0.05,0.0000,NULL,'2026-05-29 03:40:59','2026-05-29 03:40:59'),(33,11,12,2,0.03,0.0000,NULL,'2026-05-29 03:40:59','2026-05-29 03:40:59'),(34,12,7,2,2,0.0000,NULL,'2026-06-10 06:29:59','2026-06-10 06:29:59'),(35,12,10,2,2,0.0000,NULL,'2026-06-10 06:29:59','2026-06-10 06:29:59'),(36,12,19,2,1,0.0000,NULL,'2026-06-10 06:29:59','2026-06-10 06:29:59'),(37,12,19,2,1,0.0000,NULL,'2026-06-10 06:29:59','2026-06-10 06:29:59'),(38,13,22,2,NULL,0.0000,'1 METER KERTAS IVORY ROLL','2026-06-17 20:41:10','2026-10-18 01:45:20'),(39,13,6,2,NULL,0.0000,'0.05 ALKOHOL','2026-06-17 20:41:10','2026-10-18 01:45:20'),(40,13,15,2,NULL,0.0000,'TINTA METALIZED 0.005 LITER','2026-06-17 20:41:10','2026-10-18 01:45:20'),(41,14,22,2,0.3333,0.0000,NULL,'2026-06-18 00:28:01','2026-06-18 00:28:01'),(42,14,6,2,0.0015,0.0000,NULL,'2026-06-18 00:28:01','2026-06-18 00:28:01'),(43,14,7,2,0.5,0.0000,NULL,'2026-06-18 00:28:01','2026-06-18 00:28:01'),(44,15,22,2,0.3536,0.0000,'PANJANG KERTAS 0.3536','2026-06-18 01:25:46','2026-06-18 01:25:46'),(45,15,16,2,0.0005,0.0000,'JUMLAH TINTA 0.0005 LITER','2026-06-18 01:25:46','2026-06-18 01:25:46'),(47,17,18,1,1,0.0000,NULL,'2026-07-11 00:54:16','2026-07-11 00:54:16'),(48,17,21,2,3,0.0000,NULL,'2026-07-11 00:54:52','2026-07-11 00:54:52'),(49,17,1,NULL,5,0.0000,NULL,'2026-07-11 00:54:52','2026-07-11 00:54:52'),(54,22,1,1,1.5,0.0500,'Kertas utama etiket','2026-10-15 02:46:32','2026-10-15 02:46:32'),(57,4,19,2,0,0.0000,NULL,'2026-10-17 20:53:02','2026-10-17 20:53:02'),(58,4,4,5,0,0.0000,NULL,'2026-10-17 20:53:02','2026-10-17 20:53:02'),(59,4,6,NULL,0,0.0000,NULL,'2026-10-17 20:53:02','2026-10-17 20:53:02'),(60,13,17,2,NULL,0.0000,NULL,'2026-10-18 00:47:38','2026-10-18 01:45:20'),(61,25,6,2,NULL,3.0000,'minta 1 jirigen tinta','2026-10-20 06:46:49','2026-10-20 06:46:49'),(62,25,18,2,20,1.0000,NULL,'2026-10-20 06:46:49','2026-10-20 06:46:49');
/*!40000 ALTER TABLE `finish_good_item_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finish_good_items`
--

DROP TABLE IF EXISTS `finish_good_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `finish_good_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `type_item_id` bigint(20) unsigned DEFAULT NULL,
  `satuan_id` bigint(20) unsigned DEFAULT NULL,
  `kode_material_produk` varchar(255) DEFAULT NULL,
  `kode_barcode` varchar(255) DEFAULT NULL,
  `pc_number` varchar(255) DEFAULT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `komponen` varchar(100) DEFAULT NULL,
  `isi` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `spesifikasi_kertas` text DEFAULT NULL,
  `kertas_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`kertas_items`)),
  `gramasi` varchar(100) DEFAULT NULL,
  `tebal_kertas` varchar(100) DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `finishing` varchar(255) DEFAULT NULL,
  `up_satu` varchar(255) DEFAULT NULL,
  `up_dua` varchar(255) DEFAULT NULL,
  `up_tiga` varchar(255) DEFAULT NULL,
  `ukuran_potong` varchar(255) DEFAULT NULL,
  `ukuran_cetak` varchar(255) DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `berat_kotor` varchar(20) DEFAULT NULL,
  `berat_bersih` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_fgi_customer` (`customer_id`),
  KEY `fk_fgi_type_item` (`type_item_id`),
  KEY `fk_fgi_satuan` (`satuan_id`),
  CONSTRAINT `fk_fgi_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fgi_satuan` FOREIGN KEY (`satuan_id`) REFERENCES `master_satuans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fgi_type_item` FOREIGN KEY (`type_item_id`) REFERENCES `type_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finish_good_items`
--

LOCK TABLES `finish_good_items` WRITE;
/*!40000 ALTER TABLE `finish_good_items` DISABLE KEYS */;
INSERT INTO `finish_good_items` VALUES (4,1,1,2,'KI-123','987654321','20','ETIKET DJANDA BOLD 20 SKM','SKM','ETIKET','20','ETIKET DJANDA BOLD 20 SKM','IVORY 230 GR 53 X 68.5 230 GSM [ETIKET] 230 GSM [ETIKET]','[{\"nama_kertas\":\"IVORY 230 GR 53 X 68.5 230 GSM [ETIKET]\",\"bagian_kemasan\":\"ETIKET\",\"gramasi\":\"230\",\"tebal_kertas\":\"0.35\",\"up\":\"9\",\"ukuran_cetak\":\"65 x 100 cm\",\"keterangan\":null}]','230','0.35','6','Spot UV, Cutting','9','14','14','1','65 x 100 cm',12.00,6.00,12.00,'10 kg','20 kg','2026-03-24 16:37:09','2026-10-23 01:27:38',NULL),(5,1,2,6,'JS-ikn','12345678','87654321','Kertas karton',NULL,NULL,NULL,'uefguweyfguwef','dasdsda',NULL,NULL,NULL,NULL,NULL,'1','2','3','1','1',2.00,1.00,20.00,'10','20 kg','2026-04-12 22:01:26','2026-10-15 18:55:52','2026-10-15 18:55:52'),(6,1,1,2,'KI-123','12345678','87654321','ETIKET DJANDA BOLD 20 SKM',NULL,NULL,NULL,'dededede','1',NULL,NULL,NULL,NULL,NULL,'1','1','1','1','1',1.00,1.00,1.00,'10 kg','20 kg','2026-04-15 01:49:20','2026-04-15 01:49:20',NULL),(8,1,7,1,'P-01','12345678','87654321',' PLAT PRINTING',NULL,NULL,NULL,'palt prinitng kebutuhan cetak','745 X 605',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-28 02:00:03','2026-04-28 02:00:03',NULL),(9,1,2,1,'JS-123','987654321','20','SERTIFIKAT SISWA',NULL,NULL,NULL,'SERTIFIKAT SISWA','	KERTAS IVORY 230 GR 53 X 68.5',NULL,NULL,NULL,NULL,NULL,'15','10','13','1','1',30.00,15.00,15.00,'1000 Gr','1000 Gr','2026-05-09 05:34:52','2026-05-09 05:34:52',NULL),(10,1,8,8,'ET-001','987654321','20','ETIKET RASTEL BLACK GOLD REG 20 SKM (MTLZ)',NULL,NULL,NULL,'ETIKET RASTEL BLACK GOLD REG 20 SKM (MTLZ)','METALIZED 53 X 70',NULL,NULL,NULL,NULL,NULL,'14',NULL,NULL,'1','1',NULL,NULL,NULL,'0.50','0.30','2026-05-25 19:40:55','2026-05-25 19:40:55',NULL),(11,1,2,1,'JS-123','987654321','20','SERTIFIKAT SISWA COBA COBA HEHE',NULL,NULL,NULL,'SERTIFIKAT SISWA COBA COBA HEHE','SERTIFIKAT SISWA COBA COBA HEHE',NULL,NULL,NULL,NULL,NULL,'1','1','1','1','1',30.00,14.00,NULL,NULL,NULL,'2026-05-29 03:40:59','2026-05-29 03:40:59',NULL),(12,1,8,1,'ET-345',NULL,NULL,'ETIKET RASTEL SILVER',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'14',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-10 06:29:59','2026-06-10 06:29:59',NULL),(13,1,8,8,'ET-001','987654321','20','ETIKET DJARUM SUPER','SKM','ETIKET','20','ETIKET DJARUM SUPER','KERTAS METALIZED [ETIKET]','[{\"nama_kertas\":\"KERTAS METALIZED\",\"bagian_kemasan\":\"ETIKET\",\"gramasi\":null,\"tebal_kertas\":\"0.05\",\"up\":\"12\",\"ukuran_cetak\":null,\"keterangan\":null}]',NULL,'0.05','6','Spot UV, Cutting','12',NULL,NULL,NULL,NULL,10.00,8.00,15.00,NULL,NULL,'2026-06-17 20:41:10','2026-10-18 00:47:38',NULL),(14,1,8,8,'ET-345','987654321','20','ETIKET MARLBORO LONG',NULL,NULL,NULL,'ETIKEL MARLBORO LONG','KERTAS IVORY ROLL',NULL,'10','4',NULL,NULL,'1',NULL,NULL,NULL,NULL,0.33,0.15,NULL,NULL,NULL,'2026-06-18 00:28:01','2026-06-18 00:28:01',NULL),(15,1,8,8,'ET-456','987654321','20','ETIKET GUDANG GARAM FILTER',NULL,NULL,NULL,'ETIKET GUDANG GARAM FILTER','ETIKET GUDANG GARAM FILTER',NULL,NULL,'4',NULL,NULL,'1',NULL,NULL,NULL,'1',0.35,0.16,NULL,NULL,NULL,'2026-06-18 01:25:45','2026-06-18 01:25:45',NULL),(16,1,4,8,'BP-123','987654321','20','PLAT CETAK POLYMER (COBA COBA)',NULL,NULL,NULL,'PLAT CETAK POLYMER (COBA COBA)','PLAT CETAK POLYMER (COBA COBA)',NULL,'50',NULL,NULL,NULL,'1',NULL,NULL,NULL,'1',1.00,1.00,NULL,'1000 Gr',NULL,'2026-06-26 05:23:39','2026-10-15 18:56:15','2026-10-15 18:56:15'),(17,1,8,8,'ET-909','987654321','20','COBA COBA MATERIAL STOCK',NULL,NULL,NULL,'COBA COBA MATERIAL STOCK','COBA COBA MATERIAL STOCK',NULL,'COBA COBA MATERIAL STOCK',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-11 00:54:16','2026-07-11 00:54:16',NULL),(22,2,8,8,'FG-C005-PDW88SLI-967C',NULL,NULL,'PDW88 Sliding Luar','SKT','Etiket','20','Dibuat otomatis dari Approval Form No: AF-26-X-C005-001','Metalize',NULL,NULL,NULL,'5','Spot UV, Emboss, Lem, Cutting',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-15 02:46:32','2026-10-15 02:46:32',NULL),(23,1,8,8,'FG-	CS001-WALIJAGA-0FA7',NULL,NULL,'Wali Jagat','SKT','Etiket','12','Dibuat otomatis dari Approval Form No: AF-26-X-	CS001-002','Metalized',NULL,NULL,NULL,'6','Spot UV, Embos, Debos',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-15 05:50:05','2026-10-15 05:50:05',NULL),(24,3,8,8,'FG-CS003-ETIKETWA-FAD9',NULL,NULL,'ETIKET WALI SONGO','SKM','Etiket','20','Dibuat otomatis dari Approval Form No: AF-26-X-CS003-003','Metalized [Etiket]',NULL,NULL,NULL,'8','Spot UV, Emboss, Lem',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 06:44:25','2026-10-18 06:44:25',NULL),(25,4,8,8,'FG-001-DAFI-2DB5',NULL,NULL,'Dafi ','SKT','ETIKET','16','Dibuat otomatis dari Approval Form No: AF-26-X-001-004','METALIZE [ETIKET] 230 GSM [ETIKET]','[{\"nama_kertas\":\"METALIZE [ETIKET]\",\"bagian_kemasan\":\"ETIKET\",\"gramasi\":\"230\",\"tebal_kertas\":\"0.30\",\"up\":\"1\",\"ukuran_cetak\":\"65x150\",\"keterangan\":null}]','230','0.30','4','embos,lem,cutting','1',NULL,NULL,NULL,'65x150',5.50,2.20,9.00,NULL,NULL,'2026-10-20 06:42:01','2026-10-20 06:46:49',NULL);
/*!40000 ALTER TABLE `finish_good_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gajis`
--

DROP TABLE IF EXISTS `gajis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gajis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_slip` varchar(50) NOT NULL,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `periode` varchar(20) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL DEFAULT 'Transfer Bank',
  `account_kas_id` bigint(20) unsigned DEFAULT NULL,
  `gaji_pokok` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tunjangan_jabatan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tunjangan_kompetensi` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tunjangan_intensif` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_lembur` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_bonus` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_pendapatan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `potongan_tunjangan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `potongan_kehadiran` decimal(18,2) NOT NULL DEFAULT 0.00,
  `potongan_pinjaman` decimal(18,2) NOT NULL DEFAULT 0.00,
  `potongan_bpjs` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_potongan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `gaji_bersih` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'Draft',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gajis_kode_slip_unique` (`kode_slip`),
  KEY `gajis_karyawan_id_foreign` (`karyawan_id`),
  KEY `gajis_account_kas_id_foreign` (`account_kas_id`),
  CONSTRAINT `gajis_account_kas_id_foreign` FOREIGN KEY (`account_kas_id`) REFERENCES `master_coas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gajis_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gajis`
--

LOCK TABLES `gajis` WRITE;
/*!40000 ALTER TABLE `gajis` DISABLE KEYS */;
/*!40000 ALTER TABLE `gajis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hari_liburs`
--

DROP TABLE IF EXISTS `hari_liburs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hari_liburs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal_libur` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hari_liburs`
--

LOCK TABLES `hari_liburs` WRITE;
/*!40000 ALTER TABLE `hari_liburs` DISABLE KEYS */;
/*!40000 ALTER TABLE `hari_liburs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imr_printing`
--

DROP TABLE IF EXISTS `imr_printing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imr_printing` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_imr` varchar(100) DEFAULT NULL,
  `spk_id` bigint(20) unsigned NOT NULL,
  `departemen_id` bigint(20) unsigned NOT NULL,
  `tanggal_request` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(255) DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_imr` (`no_imr`),
  KEY `fk_imr_spk` (`spk_id`),
  KEY `fk_imr_printing_departemen` (`departemen_id`),
  CONSTRAINT `fk_imr_printing_departemen` FOREIGN KEY (`departemen_id`) REFERENCES `master_departemens` (`id`),
  CONSTRAINT `fk_imr_spk` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imr_printing`
--

LOCK TABLES `imr_printing` WRITE;
/*!40000 ALTER TABLE `imr_printing` DISABLE KEYS */;
INSERT INTO `imr_printing` VALUES (32,'IMR-202610-0032',26,2,'2026-10-20','2026-10-20 07:23:46','2026-10-20 07:28:28','Approved'),(33,'IMR-202610-0033',16,2,'2026-10-20','2026-10-20 08:06:17','2026-10-20 08:07:29','Issued'),(34,'IMR-202610-0034',27,2,'2026-10-23','2026-10-22 23:45:43','2026-10-22 23:46:16','Rejected');
/*!40000 ALTER TABLE `imr_printing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imr_printing_items`
--

DROP TABLE IF EXISTS `imr_printing_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imr_printing_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `imr_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `catatan` text DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `total_pesanan` decimal(15,2) DEFAULT 0.00,
  `qty_approved` decimal(15,2) DEFAULT 0.00,
  `qty_issued` decimal(15,2) DEFAULT 0.00,
  `qty_returned` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty_request` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_imr_items_header` (`imr_id`),
  CONSTRAINT `fk_imr_items_header` FOREIGN KEY (`imr_id`) REFERENCES `imr_printing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imr_printing_items`
--

LOCK TABLES `imr_printing_items` WRITE;
/*!40000 ALTER TABLE `imr_printing_items` DISABLE KEYS */;
INSERT INTO `imr_printing_items` VALUES (62,33,18,NULL,'LITER',20.00,5.00,10.00,0.00,5.00,'2026-10-20 08:06:17','2026-10-20 08:07:29'),(63,33,1,NULL,'SHEET',100.00,15.00,30.00,0.00,15.00,'2026-10-20 08:06:17','2026-10-20 08:07:29');
/*!40000 ALTER TABLE `imr_printing_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internal_return_items`
--

DROP TABLE IF EXISTS `internal_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internal_return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `internal_return_id` bigint(20) unsigned NOT NULL,
  `imr_printing_item_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned DEFAULT NULL,
  `qty_return` decimal(15,2) NOT NULL DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `internal_return_items_internal_return_id_foreign` (`internal_return_id`),
  KEY `internal_return_items_imr_printing_item_id_foreign` (`imr_printing_item_id`),
  KEY `internal_return_items_item_id_foreign` (`item_id`),
  CONSTRAINT `internal_return_items_imr_printing_item_id_foreign` FOREIGN KEY (`imr_printing_item_id`) REFERENCES `imr_printing_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `internal_return_items_internal_return_id_foreign` FOREIGN KEY (`internal_return_id`) REFERENCES `internal_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `internal_return_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `master_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_return_items`
--

LOCK TABLES `internal_return_items` WRITE;
/*!40000 ALTER TABLE `internal_return_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `internal_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internal_returns`
--

DROP TABLE IF EXISTS `internal_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internal_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_return` varchar(255) NOT NULL,
  `imr_id` bigint(20) unsigned NOT NULL,
  `tanggal_return` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('draft','approved','cancel') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `internal_returns_no_return_unique` (`no_return`),
  KEY `internal_returns_imr_id_foreign` (`imr_id`),
  KEY `internal_returns_created_by_foreign` (`created_by`),
  CONSTRAINT `internal_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `internal_returns_imr_id_foreign` FOREIGN KEY (`imr_id`) REFERENCES `imr_printing` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internal_returns`
--

LOCK TABLES `internal_returns` WRITE;
/*!40000 ALTER TABLE `internal_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `internal_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inv_payments`
--

DROP TABLE IF EXISTS `inv_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inv_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `metode_bayar_id` bigint(20) unsigned NOT NULL,
  `coa_id` bigint(20) unsigned NOT NULL,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `nominal_bayar` bigint(20) DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `status` varchar(100) DEFAULT 'PAID',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_inv_payment_invoice` (`invoice_id`),
  KEY `fk_inv_payment_metode` (`metode_bayar_id`),
  KEY `fk_inv_payment_coa` (`coa_id`),
  KEY `fk_inv_payment_karyawan` (`karyawan_id`),
  CONSTRAINT `fk_inv_payment_coa` FOREIGN KEY (`coa_id`) REFERENCES `master_coas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_payment_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_payment_metode` FOREIGN KEY (`metode_bayar_id`) REFERENCES `metode_bayars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inv_payments`
--

LOCK TABLES `inv_payments` WRITE;
/*!40000 ALTER TABLE `inv_payments` DISABLE KEYS */;
INSERT INTO `inv_payments` VALUES (13,11,1,1,1,'2026-10-20',280000,NULL,'SUCCESS','2026-10-20 08:35:04','2026-10-20 08:35:04'),(14,18,1,505,1,'2026-10-31',50000324,NULL,'SUCCESS','2026-10-31 01:01:41','2026-10-31 01:01:41');
/*!40000 ALTER TABLE `inv_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `item` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `komponen` varchar(255) DEFAULT NULL,
  `kertas` varchar(255) DEFAULT NULL,
  `finishing` varchar(255) DEFAULT NULL,
  `qty` double NOT NULL DEFAULT 0,
  `harga` double NOT NULL DEFAULT 0,
  `total` double NOT NULL DEFAULT 0,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_invoice` varchar(255) NOT NULL,
  `order_id` varchar(255) DEFAULT NULL,
  `surat_jalan_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `sales_order_id` bigint(20) unsigned DEFAULT NULL,
  `no_po_customer` varchar(100) DEFAULT NULL,
  `tanggal_invoice` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `dibuat_oleh` varchar(255) DEFAULT 'PT TEKNOGRAFI TRI CAWANASKA',
  `kepada` varchar(255) DEFAULT NULL,
  `subtotal` bigint(20) NOT NULL DEFAULT 0,
  `diskon` decimal(5,2) NOT NULL DEFAULT 0.00,
  `potongan` double NOT NULL DEFAULT 0,
  `ppn` decimal(5,2) NOT NULL DEFAULT 0.00,
  `ongkir` bigint(20) NOT NULL DEFAULT 0,
  `uang_muka` bigint(20) NOT NULL DEFAULT 0,
  `grand_total` bigint(20) NOT NULL DEFAULT 0,
  `total_payment` decimal(18,2) NOT NULL DEFAULT 0.00,
  `sisa_tagihan` bigint(20) NOT NULL DEFAULT 0,
  `status` enum('Draft','Unpaid','Partial','Paid','Cancelled') NOT NULL DEFAULT 'Draft',
  `is_printed` tinyint(1) NOT NULL DEFAULT 0,
  `catatan` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `bank_accounts` text DEFAULT NULL,
  `terbilang_override` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_invoice` (`kode_invoice`),
  KEY `fk_invoice_surat_jalan` (`surat_jalan_id`),
  KEY `fk_invoice_customer` (`customer_id`),
  KEY `fk_invoice_sales_order` (`sales_order_id`),
  CONSTRAINT `fk_invoice_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `fk_invoice_sales_order` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`),
  CONSTRAINT `fk_invoice_surat_jalan` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (11,'000001/INV/072026',NULL,19,1,37,'12345678','2026-07-22','2026-07-31','PT TEKNOGRAFI TRI CAWANASKA',NULL,280000,0.00,0,0.00,5000000,5000000,5280000,5280000.00,0,'Paid',0,NULL,NULL,NULL,NULL,'2026-07-19 03:16:28','2026-10-20 08:35:04'),(17,'000002/INV/102026',NULL,23,1,40,'12345678','2026-10-26','2026-11-25','PT TEKNOGRAFI TRI CAWANASKA',NULL,24422069,0.00,0,0.00,2500000,0,26922069,0.00,26922069,'Unpaid',0,NULL,NULL,NULL,NULL,'2026-10-25 19:59:23','2026-10-25 19:59:23'),(18,'000003/INV/102026',NULL,47,4,54,'PO0001','2026-10-31','2026-11-30','PT TEKNOGRAFI TRI CAWANASKA',NULL,324,0.00,0,0.00,50000000,0,50000324,50000324.00,0,'Paid',0,NULL,NULL,NULL,NULL,'2026-10-31 00:56:53','2026-10-31 01:01:41');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `izins`
--

DROP TABLE IF EXISTS `izins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `izins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `tanggal_izin` date NOT NULL,
  `jenis_izin` enum('terlambat','meninggalkan_kantor','pulang_awal','dinas_luar','alpha') NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  CONSTRAINT `izins_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `izins`
--

LOCK TABLES `izins` WRITE;
/*!40000 ALTER TABLE `izins` DISABLE KEYS */;
INSERT INTO `izins` VALUES (1,1,'2026-04-28','terlambat','19:18:06','15:18:10','-','2026-04-28 01:18:16','2026-04-28 01:18:16'),(2,1,'2026-06-05','dinas_luar','08:30:00','11:00:00','efwefwefwefwe','2026-06-04 23:16:09','2026-06-04 23:16:09');
/*!40000 ALTER TABLE `izins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawans`
--

DROP TABLE IF EXISTS `karyawans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `karyawans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `nip` varchar(255) NOT NULL,
  `tempat_lahir` varchar(25) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `departemen_id` bigint(20) unsigned NOT NULL,
  `jabatan` varchar(255) NOT NULL,
  `kantor` varchar(255) DEFAULT NULL,
  `tanggal_masuk_kerja` date DEFAULT NULL,
  `tangal_akhir_kontrak` date DEFAULT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `nik` varchar(20) NOT NULL,
  `no_ktp` varchar(20) NOT NULL,
  `status_pegawai` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `jenis_kelamin` varchar(20) NOT NULL,
  `agama` varchar(20) NOT NULL,
  `gaji_pokok` int(255) NOT NULL,
  `tipe_gaji` varchar(50) NOT NULL,
  `tunjangan_kompetensi` int(50) NOT NULL,
  `tunjangan_jabatan` int(50) NOT NULL,
  `tunjangan_intensif` int(50) NOT NULL,
  `nama_npwp` varchar(50) NOT NULL,
  `nomor_npwp` varchar(30) NOT NULL,
  `alamat_npwp` varchar(100) NOT NULL,
  `tanggal_npwp` date NOT NULL,
  `PTKP` varchar(50) NOT NULL,
  `nama_bank` varchar(20) NOT NULL,
  `rekening_atas_nama` varchar(30) NOT NULL,
  `no_rekening` varchar(20) NOT NULL,
  `nama_bpjs` varchar(20) NOT NULL,
  `bpjs_kesehatan` varchar(50) NOT NULL,
  `bpjs_ketenagakerjaan` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karyawans_nip_unique` (`nip`),
  KEY `karyawans_departemen_id_foreign` (`departemen_id`),
  CONSTRAINT `karyawans_departemen_id_foreign` FOREIGN KEY (`departemen_id`) REFERENCES `master_departemens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawans`
--

LOCK TABLES `karyawans` WRITE;
/*!40000 ALTER TABLE `karyawans` DISABLE KEYS */;
INSERT INTO `karyawans` VALUES (1,'farhan','12345','sdiaorjo','2026-04-24',1,'kepsek','freferferfer','2026-04-24','2026-04-24','09182301','23424324','342432432423','Aktif','L','islam',450000,'Bulanan',45000,25000,10000,'Farhan','21312321321321','sidoarjo','2026-04-24','3424324324','Mandiri','Farhan','2930821903892013','eedwef','ewfewfewfwef','wefewfwefwefwe','2026-04-23 23:53:51','2026-04-23 23:53:51');
/*!40000 ALTER TABLE `karyawans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lemburs`
--

DROP TABLE IF EXISTS `lemburs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lemburs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_lembur` varchar(50) DEFAULT NULL,
  `karyawan_id` bigint(20) unsigned DEFAULT NULL,
  `kode_gudang` varchar(100) DEFAULT NULL,
  `tanggal_lembur` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `durasi_lembur` decimal(5,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  CONSTRAINT `lemburs_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lemburs`
--

LOCK TABLES `lemburs` WRITE;
/*!40000 ALTER TABLE `lemburs` DISABLE KEYS */;
INSERT INTO `lemburs` VALUES (1,'LMB-20260428-001',1,'999','2026-04-28','11:43:25','01:42:30',13.98,'nonton timnas','2026-04-27 21:41:27','2026-04-27 21:41:27');
/*!40000 ALTER TABLE `lemburs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_coas`
--

DROP TABLE IF EXISTS `master_coas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_coas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `coa_class_id` bigint(20) unsigned NOT NULL,
  `periode` varchar(20) DEFAULT NULL,
  `gudang` varchar(100) DEFAULT NULL,
  `kode_akun` varchar(50) DEFAULT NULL,
  `nama_akun` varchar(100) DEFAULT NULL,
  `saldo_debit` decimal(15,2) DEFAULT 0.00,
  `saldo_kredit` decimal(15,2) DEFAULT 0.00,
  `nominal_default` decimal(15,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `parent_code` varchar(255) DEFAULT NULL,
  `level` int(10) unsigned DEFAULT NULL,
  `account_type` varchar(255) DEFAULT NULL,
  `account_class` varchar(255) DEFAULT NULL,
  `financial_statement` varchar(255) DEFAULT NULL,
  `cash_flow_type` varchar(255) DEFAULT NULL,
  `posting` varchar(255) DEFAULT NULL,
  `normal_balance` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `karyawan_id` (`karyawan_id`),
  KEY `coa_class_id` (`coa_class_id`),
  CONSTRAINT `master_coas_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_coas_ibfk_2` FOREIGN KEY (`coa_class_id`) REFERENCES `coa_classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=561 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_coas`
--

LOCK TABLES `master_coas` WRITE;
/*!40000 ALTER TABLE `master_coas` DISABLE KEYS */;
INSERT INTO `master_coas` VALUES (306,1,1,'2026-04','TTC','1000','ASET LANCAR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Asset','Current Asset','Balance Sheet','-','No','Debit'),(307,1,1,'2026-04','TTC','1100','KAS & BANK',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1000',2,'Account Group','Current Asset','Balance Sheet','-','No',NULL),(308,1,1,'2026-04','TTC','1110','Kas Tunai',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',3,'Asset','Current - Cash','Balance Sheet','Operating','No','Debit'),(309,1,1,'2026-04','TTC','1111','Bank BRI - Operasional',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',3,'Asset','Current - Bank','Balance Sheet','Operating','Yes','Debit'),(310,1,1,'2026-04','TTC','1112','Bank BCA - Operasional',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',3,'Asset','Current - Bank','Balance Sheet','Operating','Yes','Debit'),(311,1,1,'2026-04','TTC','1113','Bank Lain - Efendi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',3,'Asset','Current - Bank','Balance Sheet','Operating','Yes','Debit'),(312,1,1,'2026-04','TTC','1114','Bank Lain - Giro',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',3,'Asset','Current - Bank','Balance Sheet','Operating','Yes','Debit'),(313,1,1,'2026-04','TTC','1115','Bank Lain - Trading',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',3,'Asset','Current - Bank','Balance Sheet','-','Yes','Debit'),(314,1,1,'2026-04','TTC','1116','Petty Cash',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',3,'Asset','Current - Cash','Balance Sheet','Operating','Yes','Debit'),(315,1,1,'2026-04','TTC','1117','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(316,1,1,'2026-04','TTC','1118','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(317,1,1,'2026-04','TTC','1119','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1110',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(318,1,1,'2026-04','TTC','1120','PIUTANG USAHA',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Account Group','Current Asset','Balance Sheet','-','No',NULL),(319,1,1,'2026-04','TTC','1121','Piutang - Barang Jadi IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(320,1,1,'2026-04','TTC','1122','Piutang - Barang Jadi USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(321,1,1,'2026-04','TTC','1123','Piutang - Barang Jadi CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(322,1,1,'2026-04','TTC','1124','Piutang - Barang Setengah Jadi IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(323,1,1,'2026-04','TTC','1125','Piutang - Barang Setengah Jadi USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(324,1,1,'2026-04','TTC','1126','Piutang - Barang Setengah Jadi CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(325,1,1,'2026-04','TTC','1127','Piutang - Trading Alat & Parts IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(326,1,1,'2026-04','TTC','1128','Piutang - Trading Alat & Parts USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(327,1,1,'2026-04','TTC','1129','Piutang - Trading Alat & Parts CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1120',3,'Asset','Current - AR','Balance Sheet','-','Yes','Debit'),(328,1,1,'2026-04','TTC','1130','Penyisihan Piutang Ragu-Ragu',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1130',3,'Asset','Current - Allowance','Balance Sheet','-','No','Credit'),(329,1,1,'2026-04','TTC','1131','Piutang Karyawan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1130',1,'Asset','CURRENT ASSET','Balance Sheet',NULL,'Yes','Debit'),(330,1,1,'2026-04','TTC','1140','PERSEDIAAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1140',3,'Account Group','Current Asset','Balance Sheet','-','No',NULL),(331,1,1,'2026-04','TTC','1141','Persediaan Bahan Baku',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1140',3,'Asset','Current - Inventory','Balance Sheet','-','Yes','Debit'),(332,1,1,'2026-04','TTC','1142','Persediaan Barang Setengah Jadi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1140',3,'Asset','Current - Inventory','Balance Sheet','-','Yes','Debit'),(333,1,1,'2026-04','TTC','1143','Persediaan Barang Jadi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1140',3,'Asset','Current - Inventory','Balance Sheet','-','Yes','Debit'),(334,1,1,'2026-04','TTC','1144','Persediaan Alat & Sparepart',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1140',3,'Asset','Current - Inventory','Balance Sheet','-','Yes','Debit'),(335,1,1,'2026-04','TTC','1150','BIAYA DIBAYAR DIMUKA',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1150',3,'Account Group','Current Asset','Balance Sheet','-','No',NULL),(336,1,1,'2026-04','TTC','1151','Asuransi Dibayar Dimuka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1150',3,'Asset','Current - Prepaid','Balance Sheet','-','Yes','Debit'),(337,1,1,'2026-04','TTC','1152','Sewa Dibayar Dimuka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1150',3,'Asset','Current - Prepaid','Balance Sheet','-','Yes','Debit'),(338,1,1,'2026-04','TTC','1153','Biaya Lainnya Dibayar Dimuka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1150',3,'Asset','Current - Prepaid','Balance Sheet','-','Yes','Debit'),(339,1,1,'2026-04','TTC','1161','Uang Muka Subcont',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1150',1,'Asset','CURRENT ASSET','Balance Sheet',NULL,'Yes','Debit'),(340,1,1,'2026-04','TTC','1162','Uang Muka Pembelian Aset',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1150',3,'Asset','Prepayment / Advance','Balance Sheet','Operating','Yes','Debit'),(341,1,1,'2026-04','TTC','1170','PAJAK DIBAYAR DIMUKA / PPN MASUKAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1000',2,'Asset','Other Asset','Balance Sheet','Investing','No','Debit'),(342,1,1,'2026-04','TTC','1171','PPN Masukan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1170',3,'Asset','Tax Receivable','Balance Sheet','Operating','Yes','Debit'),(343,1,1,'2026-04','TTC','1172','Pajak Dibayar di Muka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1170',3,'Asset','Tax Prepayment','Balance Sheet','Operating','Yes','Debit'),(344,1,1,'2026-04','TTC','1175','PPh 25 Dibayar di Muka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1170',3,'Asset','Tax Prepayment','Balance Sheet','Operating','Yes','Debit'),(345,1,1,'2026-04','TTC','1174','PPh 23 Dibayar di Muka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1170',3,'Asset','Tax Prepayment','Balance Sheet','Operating','Yes','Debit'),(346,1,1,'2026-04','TTC','1173','PPh 22 Dibayar di Muka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1170',3,'Asset','Tax Prepayment','Balance Sheet','Operating','Yes','Debit'),(347,1,1,'2026-04','TTC','1200','ASET TETAP',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1000',2,'Account Group','Fixed Asset','Balance Sheet','-','No',NULL),(348,1,1,'2026-04','TTC','1210','Tanah & Bangunan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1210',3,'Account Group','Fixed Asset','Balance Sheet','-','No',NULL),(349,1,1,'2026-04','TTC','1211','Tanah & Bangunan - Harga Perolehan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1210',3,'Asset','Fixed - Gross','Balance Sheet','Investing','Yes','Debit'),(350,1,1,'2026-04','TTC','1212','Akumulasi Penyusutan Bangunan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1210',3,'Asset','Fixed - Accumulated Deprec','Balance Sheet','Non Cash','Yes','Credit'),(351,1,1,'2026-04','TTC','1213','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1210',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(352,1,1,'2026-04','TTC','1214','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1210',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(353,1,1,'2026-04','TTC','1215','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1210',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(354,1,1,'2026-04','TTC','1220','MESIN & PERALATAN PRODUKSI',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1220',3,'Account Group','Fixed Asset','Balance Sheet',NULL,'No',NULL),(355,1,1,'2026-04','TTC','1221','Mesin & Peralatan - Harga Perolehan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1220',3,'Asset','Fixed - Gross','Balance Sheet','Investing','Yes','Debit'),(356,1,1,'2026-04','TTC','1222','Akumulasi Penyusutan Mesin',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1220',3,'Asset','Fixed - Accumulated Deprec','Balance Sheet','Non Cash','Yes','Credit'),(357,1,1,'2026-04','TTC','1223','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1220',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(358,1,1,'2026-04','TTC','1224','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1220',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(359,1,1,'2026-04','TTC','1225','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1220',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(360,1,1,'2026-04','TTC','1230','KENDARAAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1230',3,'Account Group','Fixed Asset','Balance Sheet','-','No',NULL),(361,1,1,'2026-04','TTC','1231','Kendaraan - Harga Perolehan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1230',3,'Asset','Fixed - Gross','Balance Sheet','Investing','Yes','Debit'),(362,1,1,'2026-04','TTC','1232','Akumulasi Penyusutan Kendaraan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1230',3,'Asset','Fixed - Accumulated Deprec','Balance Sheet','Non Cash','Yes','Credit'),(363,1,1,'2026-04','TTC','1233','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1230',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(364,1,1,'2026-04','TTC','1234','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1230',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(365,1,1,'2026-04','TTC','1235','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1230',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(366,1,1,'2026-04','TTC','1240','FURNITURE & FIXTURES',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1240',3,'Account Group','Fixed Asset','Balance Sheet','-','No',NULL),(367,1,1,'2026-04','TTC','1241','Furniture & Fixtures - Harga Perolehan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1240',3,'Asset','Fixed - Gross','Balance Sheet','Investing','Yes','Debit'),(368,1,1,'2026-04','TTC','1242','Akumulasi Penyusutan Furniture',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1240',3,'Asset','Fixed - Accumulated Deprec','Balance Sheet','Non Cash','Yes','Credit'),(369,1,1,'2026-04','TTC','1243','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1240',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(370,1,1,'2026-04','TTC','1244','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1240',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(371,1,1,'2026-04','TTC','1245','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1240',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(372,1,1,'2026-04','TTC','1250','ASET DALAM PEMBANGUNAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1200',3,'Asset','Fixed Asset','Balance Sheet','Investing','No','Debit'),(373,1,1,'2026-04','TTC','1251','Bangunan Dalam Pembangunan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1200',1,'Asset','FIXED ASSET','Balance Sheet',NULL,'Yes','Debit'),(374,1,1,'2026-04','TTC','1252','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1250',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(375,1,1,'2026-04','TTC','1253','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1250',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(376,1,1,'2026-04','TTC','1254','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1250',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(377,1,1,'2026-04','TTC','1260','RENOVASI & LEASEHOLD IMPROVEMENT',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1200',3,'Asset','Fixed Asset','Balance Sheet','Investing','No','Debit'),(378,1,1,'2026-04','TTC','1261','Renovasi & Leasehold Improvement - Harga Perolehan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1260',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(379,1,1,'2026-04','TTC','1262','Akumulasi Penyusutan Renovasi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1260',4,'Asset','Fixed Asset','Balance Sheet','Investing','Yes','Debit'),(380,1,1,'2026-04','TTC','1263','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1260',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(381,1,1,'2026-04','TTC','1264','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1260',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(382,1,1,'2026-04','TTC','1265','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1260',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(383,1,1,'2026-04','TTC','1300','ASET LAINNYA',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1000',2,'Account Group','Other Asset','Balance Sheet','-','No',NULL),(384,1,1,'2026-04','TTC','1310','Investasi Saham',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1310',3,'Asset','Other - Investment','Balance Sheet','Investing','Yes','Debit'),(385,1,1,'2026-04','TTC','1320','Deposito Berjangka',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1320',3,'Asset','Other - Investment','Balance Sheet','Investing','Yes','Debit'),(386,1,1,'2026-04','TTC','1330','ASET VALAS',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1300',3,'Asset','Other Asset','Balance Sheet','Investing','No','Debit'),(387,1,1,'2026-04','TTC','1331','Aset Valas - USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1330',3,'Asset','Other - FX Reserve','Balance Sheet','-','Yes','Debit'),(388,1,1,'2026-04','TTC','1332','Aset Valas - CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1330',3,'Asset','Other - FX Reserve','Balance Sheet','-','Yes','Debit'),(389,1,1,'2026-04','TTC','1333','Deposit / Jaminan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1300',3,'Asset','Other - Deposit','Balance Sheet','Operating','Yes','Debit'),(390,1,1,'2026-04','TTC','1334','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1330',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(391,1,1,'2026-04','TTC','1335','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','1330',4,'Asset','Other Asset','Balance Sheet','Investing','Yes','Debit'),(392,1,1,'2026-04','TTC','2000','KEWAJIBAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Liability','-','Balance Sheet','-','No','Credit'),(393,1,1,'2026-04','TTC','2100','KEWAJIBAN LANCAR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2000',2,'Account Group','Current Liability','Balance Sheet','-','No',NULL),(394,1,1,'2026-04','TTC','2110','UTANG USAHA',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',3,'Account Group','Current Liability','Balance Sheet','-','No',NULL),(395,1,1,'2026-04','TTC','2111','Utang Usaha - Bahan Baku IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',3,'Liability','Current - Payable','Balance Sheet','-','Yes','Credit'),(396,1,1,'2026-04','TTC','2112','Utang Usaha - Bahan Baku USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',3,'Liability','Current - Payable','Balance Sheet','-','Yes','Credit'),(397,1,1,'2026-04','TTC','2113','Utang Usaha - Bahan Baku CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',3,'Liability','Current - Payable','Balance Sheet','-','Yes','Credit'),(398,1,1,'2026-04','TTC','2114','Utang Usaha - Trading IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',3,'Liability','Current - Payable','Balance Sheet','-','Yes','Credit'),(399,1,1,'2026-04','TTC','2115','Utang Usaha - Trading USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',3,'Liability','Current - Payable','Balance Sheet','-','Yes','Credit'),(400,1,1,'2026-04','TTC','2116','Utang Usaha - Trading CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',3,'Liability','Current - Payable','Balance Sheet','-','Yes','Credit'),(401,1,1,'2026-04','TTC','2117','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(402,1,1,'2026-04','TTC','2118','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(403,1,1,'2026-04','TTC','2119','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2110',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(404,1,1,'2026-04','TTC','2120','HUTANG PAJAK',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',3,'Account Group','Current Liability','Balance Sheet','-','No',NULL),(405,1,1,'2026-04','TTC','2121','PPN Terutang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',3,'Liability','Current - Tax','Balance Sheet','-','Yes','Credit'),(406,1,1,'2026-04','TTC','2122','PPh 21 Terutang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',3,'Liability','Current - Tax','Balance Sheet','-','Yes','Credit'),(407,1,1,'2026-04','TTC','2123','PPh 23 Terutang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',3,'Liability','Current - Tax','Balance Sheet','-','Yes','Credit'),(408,1,1,'2026-04','TTC','2124','PPh Badan Terutang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',3,'Liability','Current - Tax','Balance Sheet','-','Yes','Credit'),(409,1,1,'2026-04','TTC','2125','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(410,1,1,'2026-04','TTC','2126','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(411,1,1,'2026-04','TTC','2127','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2120',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(412,1,1,'2026-04','TTC','2130','BIAYA YANG MASIH HARUS DIBAYAR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',3,'Account Group','Current Liability','Balance Sheet','-','No',NULL),(413,1,1,'2026-04','TTC','2131','Gaji Terutang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',3,'Liability','Current - Accrued','Balance Sheet','-','Yes','Credit'),(414,1,1,'2026-04','TTC','2132','Utilitas Terutang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',3,'Liability','Current - Accrued','Balance Sheet','-','Yes','Credit'),(415,1,1,'2026-04','TTC','2133','Biaya Lainnya Terutang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',3,'Liability','Current - Accrued','Balance Sheet','-','Yes','Credit'),(416,1,1,'2026-04','TTC','2134','Hutang kepada Karyawan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',1,'Liability','CURRENT LIABILITY','Balance Sheet',NULL,'Yes','Credit'),(417,1,1,'2026-04','TTC','2135','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(418,1,1,'2026-04','TTC','2136','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(419,1,1,'2026-04','TTC','2137','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2130',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(420,1,1,'2026-04','TTC','2140','HUTANG JANGKA PENDEK LAINNYA',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2140',3,'Account Group','Current Liability','Balance Sheet','-','No',NULL),(421,1,1,'2026-04','TTC','2141','Hutang Bank - Bagian Lancar',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2140',3,'Liability','Current - Loan','Balance Sheet','Financing','Yes','Credit'),(422,1,1,'2026-04','TTC','2142','Hutang kepada Pemilik',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2140',3,'Liability','Current - Related Party','Balance Sheet','Financing','Yes','Credit'),(423,1,1,'2026-04','TTC','2143','Hutang Pembiayaan - Bagian Lancar',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2140',4,'Liability','Current Financing','Balance Sheet','Financing','Yes','Credit'),(424,1,1,'2026-04','TTC','2144','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2140',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(425,1,1,'2026-04','TTC','2145','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2140',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(426,1,1,'2026-04','TTC','2146','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2140',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(427,1,1,'2026-04','TTC','2150','UANG MUKA PENJUALAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2100',3,'Liability','Long-term Financing','Balance Sheet','Financing','No','Credit'),(428,1,1,'2026-04','TTC','2151','Uang Muka Penjualan - IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2150',4,'Liability','Customer Advance','Balance Sheet','Operating','Yes','Credit'),(429,1,1,'2026-04','TTC','2152','Uang Muka Penjualan - USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2150',4,'Liability','Customer Advance','Balance Sheet','Operating','Yes','Credit'),(430,1,1,'2026-04','TTC','2153','Uang Muka Penjualan - CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2150',4,'Liability','Customer Advance','Balance Sheet','Operating','Yes','Credit'),(431,1,1,'2026-04','TTC','2154','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2150',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(432,1,1,'2026-04','TTC','2155','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2150',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(433,1,1,'2026-04','TTC','2156','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2150',4,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(434,1,1,'2026-04','TTC','2200','KEWAJIBAN JANGKA PANJANG',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2000',2,'Account Group','Long-term Liability','Balance Sheet','-','No',NULL),(435,1,1,'2026-04','TTC','2210','Hutang Bank - Jangka Panjang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2210',3,'Liability','Long-term - Loan','Balance Sheet','Financing','Yes','Credit'),(436,1,1,'2026-04','TTC','2220','Hutang Pembiayaan Jangka Panjang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2220',3,'Liability','Long-term - Other','Balance Sheet','-','Yes','Credit'),(437,1,1,'2026-04','TTC','2230','Hutang Lainnya Jangka Panjang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','2200',3,'Liability','Long-term Financing','Balance Sheet','Financing','Yes','Credit'),(438,1,1,'2026-04','TTC','3000','MODAL',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Equity','-','Balance Sheet','-','No','Credit'),(439,1,1,'2026-04','TTC','3100','Modal Awal Pemilik',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Capital','Balance Sheet','Financing','Yes','Credit'),(440,1,1,'2026-04','TTC','3200','Laba Ditahan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Retained Earnings','Balance Sheet','-','Yes','Credit'),(441,1,1,'2026-04','TTC','3300','Laba/Rugi Tahun Berjalan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Current Year','Balance Sheet','-','Yes','Credit'),(442,1,1,'2026-04','TTC','3400','Ekuitas Saldo Awal',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Opening Balance','Balance Sheet','-','Yes','Credit'),(443,1,1,'2026-04','TTC','3500','Prive / Pengambilan Pemilik',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Owner Withdrawal','Balance Sheet','Financing','Yes','Debit'),(444,1,1,'2026-04','TTC','3501','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Equity','Balance Sheet','Financing','Yes','Credit'),(445,1,1,'2026-04','TTC','3502','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Equity','Balance Sheet','Financing','Yes','Credit'),(446,1,1,'2026-04','TTC','3503','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Equity','Balance Sheet','Financing','Yes','Credit'),(447,1,1,'2026-04','TTC','3991','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Equity','Balance Sheet','Financing','Yes','Credit'),(448,1,1,'2026-04','TTC','3992','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Equity','Balance Sheet','Financing','Yes','Credit'),(449,1,1,'2026-04','TTC','3993','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','3000',2,'Equity','Equity','Balance Sheet','Financing','Yes','Credit'),(450,1,1,'2026-04','TTC','4000','PENDAPATAN USAHA',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Revenue','-','Income Statement','-','No','Credit'),(451,1,1,'2026-04','TTC','4100','PENJUALAN BARANG JADI',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Account Group','Revenue - Sales','Balance Sheet','-','No',NULL),(452,1,1,'2026-04','TTC','4101','Penjualan Barang Jadi - IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4100',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(453,1,1,'2026-04','TTC','4102','Penjualan Barang Jadi - USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4100',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(454,1,1,'2026-04','TTC','4103','Penjualan Barang Jadi - CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4100',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(455,1,1,'2026-04','TTC','4200','PENJUALAN BARANG SETENGAH JADI',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Account Group','Revenue - Sales','Balance Sheet','-','No',NULL),(456,1,1,'2026-04','TTC','4201','Penjualan Semi-Finished - IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4200',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(457,1,1,'2026-04','TTC','4202','Penjualan Semi-Finished - USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4200',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(458,1,1,'2026-04','TTC','4203','Penjualan Semi-Finished - CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4200',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(459,1,1,'2026-04','TTC','4300','PENJUALAN TRADING',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Account Group','Revenue - Sales','Balance Sheet','-','No',NULL),(460,1,1,'2026-04','TTC','4301','Penjualan Trading - IDR',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4300',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(461,1,1,'2026-04','TTC','4302','Penjualan Trading - USD',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4300',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(462,1,1,'2026-04','TTC','4303','Penjualan Trading - CNY',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4300',3,'Revenue','Revenue - Sales','Income Statement','-','Yes','Credit'),(463,1,1,'2026-04','TTC','4400','RETUR & POTONGAN PENJUALAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Account Group','Revenue - Adjustment','Balance Sheet','-','No',NULL),(464,1,1,'2026-04','TTC','4401','Retur Penjualan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4400',3,'Revenue','Revenue - Adjustment','Income Statement','-','Yes','Credit'),(465,1,1,'2026-04','TTC','4402','Diskon Penjualan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4400',3,'Revenue','Revenue - Adjustment','Income Statement','-','Yes','Credit'),(466,1,1,'2026-04','TTC','4500','PENDAPATAN LAINNYA',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Account Group','Revenue - Other','Balance Sheet','-','No',NULL),(467,1,1,'2026-04','TTC','4510','Pendapatan Bunga',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4510',3,'Revenue','Revenue - Other','Income Statement','-','Yes','Credit'),(468,1,1,'2026-04','TTC','4520','Pendapatan Dividen',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4520',3,'Revenue','Revenue - Other','Income Statement','-','Yes','Credit'),(469,1,1,'2026-04','TTC','4530','Keuntungan Kurs',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4530',3,'Revenue','Revenue - Other','Income Statement','-','Yes','Credit'),(470,1,1,'2026-04','TTC','4540','Pendapatan Lain-lain',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4540',3,'Revenue','Revenue - Other','Income Statement','-','Yes','Credit'),(471,1,1,'2026-04','TTC','4550','Keuntungan Penjualan Aset',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4500',3,'Revenue','Other Income','Income Statement','Operating','Yes','Credit'),(472,1,1,'2026-04','TTC','4551','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Revenue','Other Income','Income Statement','Operating','Yes','Credit'),(473,1,1,'2026-04','TTC','4552','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Revenue','Other Income','Income Statement','Operating','Yes','Credit'),(474,1,1,'2026-04','TTC','4553','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','4000',2,'Revenue','Other Income','Income Statement','Operating','Yes','Credit'),(475,1,1,'2026-04','TTC','5000','BEBAN POKOK PENJUALAN (COGS)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Account Group','-','Balance Sheet','-','No',NULL),(476,1,1,'2026-04','TTC','5100','BAHAN BAKU DIPAKAI',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5000',2,'Account Group','COGS - Material','Balance Sheet','-','No',NULL),(477,1,1,'2026-04','TTC','5101','Pemakaian Bahan Baku - Produksi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5100',3,'COGS','COGS - Material','Income Statement','-','Yes','Debit'),(478,1,1,'2026-04','TTC','5102','Pemakaian Bahan Baku - Trading',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5100',3,'COGS','COGS - Material','Income Statement','-','Yes','Debit'),(479,1,1,'2026-04','TTC','5200','TENAGA KERJA LANGSUNG',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5000',2,'Account Group','COGS - Labor','Balance Sheet','-','No',NULL),(480,1,1,'2026-04','TTC','5201','Gaji Produksi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5200',3,'COGS','COGS - Labor','Income Statement','-','Yes','Debit'),(481,1,1,'2026-04','TTC','5202','Tunjangan Produksi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5200',3,'COGS','COGS - Labor','Income Statement','-','Yes','Debit'),(482,1,1,'2026-04','TTC','5203','Insentif Produksi (FORMULA BLANK)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5200',3,'COGS','COGS - Labor','Income Statement','-','Yes','Debit'),(483,1,1,'2026-04','TTC','5204','Lembur Produksi (FORMULA BLANK)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5200',3,'COGS','COGS - Labor','Income Statement','-','Yes','Debit'),(484,1,1,'2026-04','TTC','5300','OVERHEAD PABRIK',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5000',2,'Account Group','COGS - Overhead','Balance Sheet','-','No',NULL),(485,1,1,'2026-04','TTC','5301','Listrik & Air Pabrik',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',3,'COGS','COGS - Overhead','Income Statement','-','Yes','Debit'),(486,1,1,'2026-04','TTC','5302','Gas/Bahan Bakar Produksi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',3,'COGS','COGS - Overhead','Income Statement','-','Yes','Debit'),(487,1,1,'2026-04','TTC','5303','Pemeliharaan Mesin',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',3,'COGS','COGS - Overhead','Income Statement','-','Yes','Debit'),(488,1,1,'2026-04','TTC','5304','Bahan Bakar & Pelumas',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',3,'COGS','COGS - Overhead','Income Statement','-','Yes','Debit'),(489,1,1,'2026-04','TTC','5305','Suku Cadang Produksi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',3,'COGS','COGS - Overhead','Income Statement','-','Yes','Debit'),(490,1,1,'2026-04','TTC','5306','Penyusutan Mesin',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',3,'COGS','COGS - Overhead','Income Statement','Non Cash','Yes','Debit'),(491,1,1,'2026-04','TTC','5307','Gaji Supervisor Produksi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',3,'COGS','COGS - Overhead','Income Statement','-','Yes','Debit'),(492,1,1,'2026-04','TTC','5308','Biaya Subcont Produksi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5300',1,'COGS','FACTORY OVERHEAD','Income Statement',NULL,'Yes','Debit'),(493,1,1,'2026-04','TTC','5400','BEBAN POKOK PENJUALAN - TRADING',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5000',2,'Account Group','COGS - Trading','Balance Sheet','-','No',NULL),(494,1,1,'2026-04','TTC','5401','Harga Pokok Pembelian Trading',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5400',3,'COGS','COGS - Trading','Income Statement','-','Yes','Debit'),(495,1,1,'2026-04','TTC','5402','Ongkos Angkut Masuk Trading',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5400',3,'COGS','COGS - Trading','Income Statement','-','Yes','Debit'),(496,1,1,'2026-04','TTC','5403','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5000',2,'Expense','COGS','Income Statement','Operating','Yes','Debit'),(497,1,1,'2026-04','TTC','5404','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5000',2,'Expense','COGS','Income Statement','Operating','Yes','Debit'),(498,1,1,'2026-04','TTC','5405','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','5000',2,'Expense','COGS','Income Statement','Operating','Yes','Debit'),(499,1,1,'2026-04','TTC','6000','BEBAN OPERASIONAL',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Expense','-','Income Statement','-','No','Debit'),(500,1,1,'2026-04','TTC','6100','BEBAN PENJUALAN & PEMASARAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Account Group','Expense - Sales','Balance Sheet','-','No',NULL),(501,1,1,'2026-04','TTC','6101','Gaji Bagian Penjualan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',3,'Expense','Expense - Sales','Income Statement','-','Yes','Debit'),(502,1,1,'2026-04','TTC','6102','Komisi Penjual',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',3,'Expense','Expense - Sales','Income Statement','-','Yes','Debit'),(503,1,1,'2026-04','TTC','6103','Iklan & Promosi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',3,'Expense','Expense - Sales','Income Statement','-','Yes','Debit'),(504,1,1,'2026-04','TTC','6104','Biaya Pengiriman Barang',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',3,'Expense','Expense - Sales','Income Statement','-','Yes','Debit'),(505,1,1,'2026-04','TTC','6105','Biaya Warehouse Penjualan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',3,'Expense','Expense - Sales','Income Statement','-','Yes','Debit'),(506,1,1,'2026-04','TTC','6106','Embalasi & Packaging',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',3,'Expense','Expense - Sales','Income Statement','-','Yes','Debit'),(507,1,1,'2026-04','TTC','6107','Asuransi Pengiriman',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',3,'Expense','Expense - Sales','Income Statement','-','Yes','Debit'),(508,1,1,'2026-04','TTC','6108','Komisi Marketing / Referral',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6100',1,'Expense','SELLING EXPENSE','Income Statement',NULL,'Yes','Debit'),(509,1,1,'2026-04','TTC','6200','BEBAN ADMINISTRASI & UMUM',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Account Group','Expense - Admin','Balance Sheet','-','No',NULL),(510,1,1,'2026-04','TTC','6201','Gaji Administrasi',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(511,1,1,'2026-04','TTC','6202','Sewa Kantor',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(512,1,1,'2026-04','TTC','6203','Listrik & Air Kantor',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(513,1,1,'2026-04','TTC','6204','Asuransi Kantor & Properti',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(514,1,1,'2026-04','TTC','6205','Biaya Profesional',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(515,1,1,'2026-04','TTC','6206','Alat Tulis Kantor',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(516,1,1,'2026-04','TTC','6207','Telepon & Internet',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(517,1,1,'2026-04','TTC','6208','Perawatan Kendaraan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(518,1,1,'2026-04','TTC','6209','Bahan Bakar dan Etoll Kendaraan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(519,1,1,'2026-04','TTC','6210','Asuransi Kendaraan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6210',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(520,1,1,'2026-04','TTC','6211','Pajak Kendaraan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6210',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(521,1,1,'2026-04','TTC','6212','Iuran & Keanggotaan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6210',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(522,1,1,'2026-04','TTC','6213','Hadiah & Sumbangan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6210',3,'Expense','Expense - Admin','Income Statement','-','Yes','Debit'),(523,1,1,'2026-04','TTC','6214','Biaya Administrasi Bank',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Operating Expense','Income Statement','Operating','Yes','Debit'),(524,1,1,'2026-04','TTC','6215','Perawatan Gedung/Bangunan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Operating Expense','Income Statement','Operating','Yes','Debit'),(525,1,1,'2026-04','TTC','6216','Perlengkapan dan Furniture',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Operating Expense','Income Statement','Operating','Yes','Debit'),(526,1,1,'2026-04','TTC','6217','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6200',3,'Expense','Operating Expense','Income Statement','Operating','Yes','Debit'),(527,1,1,'2026-04','TTC','6300','BEBAN TENAGA KERJA (NON-PRODUKSI)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Account Group','Expense - Labor','Balance Sheet','-','No',NULL),(528,1,1,'2026-04','TTC','6301','Gaji Karyawan Umum',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6300',3,'Expense','Expense - Labor','Income Statement','-','Yes','Debit'),(529,1,1,'2026-04','TTC','6302','Tunjangan Karyawan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6300',3,'Expense','Expense - Labor','Income Statement','-','Yes','Debit'),(530,1,1,'2026-04','TTC','6303','Pesangon & Biaya Terakhir',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6300',3,'Expense','Expense - Labor','Income Statement','-','Yes','Debit'),(531,1,1,'2026-04','TTC','6304','Biaya Kesehatan Karyawan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6300',3,'Expense','Expense - Labor','Income Statement','-','Yes','Debit'),(532,1,1,'2026-04','TTC','6305','Training & Pengembangan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6300',3,'Expense','Expense - Labor','Income Statement','-','Yes','Debit'),(533,1,1,'2026-04','TTC','6400','BEBAN PAJAK & RETRIBUSI',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Account Group','Expense - Tax','Balance Sheet','-','No',NULL),(534,1,1,'2026-04','TTC','6401','Pajak Bumi & Bangunan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6400',3,'Expense','Expense - Tax','Income Statement','-','Yes','Debit'),(535,1,1,'2026-04','TTC','6402','Retribusi & Perizinan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6400',3,'Expense','Expense - Tax','Income Statement','-','Yes','Debit'),(536,1,1,'2026-04','TTC','6500','BEBAN KESEHATAN & KESELAMATAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Account Group','Expense - HSE','Balance Sheet','-','No',NULL),(537,1,1,'2026-04','TTC','6501','Kontribusi BPJS Kesehatan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6500',3,'Expense','Expense - HSE','Income Statement','-','Yes','Debit'),(538,1,1,'2026-04','TTC','6502','Kontribusi BPJS Ketenagakerjaan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6500',3,'Expense','Expense - HSE','Income Statement','-','Yes','Debit'),(539,1,1,'2026-04','TTC','6503','Program Kesejahteraan Karyawan',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6500',3,'Expense','Expense - HSE','Income Statement','-','Yes','Debit'),(540,1,1,'2026-04','TTC','6504','Biaya K3 (Keselamatan Kerja)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6500',3,'Expense','Expense - HSE','Income Statement','-','Yes','Debit'),(541,1,1,'2026-04','TTC','6600','BEBAN LAIN-LAIN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Account Group','Expense - Other','Balance Sheet','-','No',NULL),(542,1,1,'2026-04','TTC','6610','Biaya Miscellaneous',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6610',3,'Expense','Expense - Other','Income Statement','-','Yes','Debit'),(543,1,1,'2026-04','TTC','6611','Pembulatan Selisih Bayar',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6600',3,'Expense','Expense - Other','Income Statement','Operating','Yes','Debit'),(544,1,1,'2026-04','TTC','6612','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Expense','Operating Expense','Income Statement','Operating','Yes','Debit'),(545,1,1,'2026-04','TTC','6613','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','6000',2,'Expense','Operating Expense','Income Statement','Operating','Yes','Debit'),(546,1,1,'2026-04','TTC','7000','BEBAN LAIN-LAIN (NON-OPERASIONAL)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Expense','-','Income Statement','-','No','Debit'),(547,1,1,'2026-04','TTC','7100','Bunga Pinjaman',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','7000',2,'Expense','Expense - Other','Income Statement','-','Yes','Debit'),(548,1,1,'2026-04','TTC','7200','Kerugian Kurs',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','7000',2,'Expense','Expense - Other','Income Statement','-','Yes','Debit'),(549,1,1,'2026-04','TTC','7300','Kerugian Penjualan Aset',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','7000',2,'Expense','Expense - Other','Income Statement','-','Yes','Debit'),(550,1,1,'2026-04','TTC','7400','Beban Lain-lain',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','7000',2,'Expense','Expense - Other','Income Statement','-','Yes','Debit'),(551,1,1,'2026-04','TTC','7401','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','7000',2,'Expense','Other Expense','Income Statement','Operating','Yes','Debit'),(552,1,1,'2026-04','TTC','7402','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','7000',2,'Expense','Other Expense','Income Statement','Operating','Yes','Debit'),(553,1,1,'2026-04','TTC','7403','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','7000',2,'Expense','Other Expense','Income Statement','Operating','Yes','Debit'),(554,1,1,'2026-04','TTC','8000','AKUN PEMBANTU & PENYESUAIAN',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12',NULL,1,'Other','-','Balance Sheet','-','No','Debit'),(555,1,1,'2026-04','TTC','8100','Penyusutan Aset (Clearing)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','8000',2,'Other','Adjustment','Balance Sheet','Non Cash','Yes','Debit'),(556,1,1,'2026-04','TTC','8200','Piutang Ragu-Ragu (Clearing)',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','8000',2,'Other','Adjustment','Balance Sheet','Non Cash','Yes','Debit'),(557,1,1,'2026-04','TTC','8300','Akun Closing',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','8000',2,'Other','Adjustment','Balance Sheet','-','Yes','Debit'),(558,1,1,'2026-04','TTC','8301','COA Cadangan 1',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','8000',2,'Adjustment','Adjustment',NULL,'-','Yes','Debit'),(559,1,1,'2026-04','TTC','8302','COA Cadangan 2',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','8000',2,'Adjustment','Adjustment',NULL,'-','Yes','Debit'),(560,1,1,'2026-04','TTC','8303','COA Cadangan 3',0.00,0.00,0.00,'Import Excel COA','aktif','2026-10-21 04:35:12','2026-10-21 04:35:12','8000',2,'Adjustment','Adjustment',NULL,'-','Yes','Debit');
/*!40000 ALTER TABLE `master_coas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_departemens`
--

DROP TABLE IF EXISTS `master_departemens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_departemens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(255) NOT NULL,
  `nama_departemen` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_departemens_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_departemens`
--

LOCK TABLES `master_departemens` WRITE;
/*!40000 ALTER TABLE `master_departemens` DISABLE KEYS */;
INSERT INTO `master_departemens` VALUES (1,'HRD','Human Resource','Departemen HRD',1,'2026-03-05 08:47:19','2026-03-05 08:47:19'),(2,'PRNT','Printing','Departemen Printing',1,'2026-04-27 01:20:02','2026-04-27 01:20:27'),(3,'PRCHS','Purchasing','purchasing',1,'2026-04-27 01:20:44','2026-04-27 01:20:44'),(4,'SPOTUV','SPOT UV','SPOT UV',1,'2026-04-27 01:21:11','2026-04-27 01:21:11'),(5,'SPOTHL','SPOT HOLO','	SPOT HOLO',1,'2026-04-27 01:23:18','2026-04-27 01:23:18');
/*!40000 ALTER TABLE `master_departemens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_finishing`
--

DROP TABLE IF EXISTS `master_finishing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_finishing` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `spk_id` bigint(20) unsigned NOT NULL,
  `kode_finishing` varchar(100) DEFAULT NULL,
  `mesin_finishing_id` bigint(20) unsigned NOT NULL,
  `operator_finishing_id` bigint(20) unsigned NOT NULL,
  `tanggal_entri` date NOT NULL,
  `proses_finishing` enum('protol','sorter','lem') NOT NULL,
  `tahap_finishing` enum('reguler','blokir','semi_waste','retur') NOT NULL,
  `hasil_baik` int(11) DEFAULT 0,
  `hasil_rusak` int(11) DEFAULT 0,
  `semi_waste` int(11) DEFAULT 0,
  `error_production_id` bigint(20) unsigned DEFAULT NULL,
  `keterangan_spk` enum('reguler','subcount') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_finishing_spk` (`spk_id`),
  KEY `fk_finishing_mesin` (`mesin_finishing_id`),
  KEY `fk_finishing_operator` (`operator_finishing_id`),
  KEY `fk_master_finishing_error_production` (`error_production_id`),
  CONSTRAINT `fk_finishing_mesin` FOREIGN KEY (`mesin_finishing_id`) REFERENCES `mesin_finishing` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_finishing_operator` FOREIGN KEY (`operator_finishing_id`) REFERENCES `operator_finishings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_finishing_spk` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_master_finishing_error_production` FOREIGN KEY (`error_production_id`) REFERENCES `error_productions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_finishing`
--

LOCK TABLES `master_finishing` WRITE;
/*!40000 ALTER TABLE `master_finishing` DISABLE KEYS */;
/*!40000 ALTER TABLE `master_finishing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_items`
--

DROP TABLE IF EXISTS `master_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_master_item` varchar(255) NOT NULL,
  `nama_master_item` varchar(255) NOT NULL,
  `min_stock` int(11) DEFAULT NULL,
  `min_order` int(11) DEFAULT NULL,
  `satuan_id` bigint(20) unsigned NOT NULL,
  `category_item_id` bigint(20) unsigned NOT NULL,
  `type_item_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `berat` decimal(10,2) DEFAULT NULL,
  `tipe_penjualan` enum('JUAL','TIDAK_DIJUAL') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_items_kode_master_item_unique` (`kode_master_item`),
  KEY `master_items_satuan_id_foreign` (`satuan_id`),
  KEY `master_items_category_item_id_foreign` (`category_item_id`),
  KEY `master_items_type_item_id_foreign` (`type_item_id`),
  CONSTRAINT `master_items_category_item_id_foreign` FOREIGN KEY (`category_item_id`) REFERENCES `category_items` (`id`),
  CONSTRAINT `master_items_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `master_satuans` (`id`),
  CONSTRAINT `master_items_type_item_id_foreign` FOREIGN KEY (`type_item_id`) REFERENCES `type_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_items`
--

LOCK TABLES `master_items` WRITE;
/*!40000 ALTER TABLE `master_items` DISABLE KEYS */;
INSERT INTO `master_items` VALUES (1,'K01','KERTAS IVORY 230 GR 53 X 68.5',50,10,1,2,1,'2026-03-06 22:14:23','2026-03-06 22:14:23',10,53.00,68.50,NULL,230.00,NULL),(2,'KO2','KERTAS IVORY 250 GR 79 X 109',100,10,2,2,1,'2026-03-15 13:15:04','2026-03-15 13:15:44',100,79.00,109.00,NULL,250.00,NULL),(3,'ROLL01','KERTAS ROLL 4000 M',1,1,9,2,1,'2026-04-15 01:59:29','2026-04-30 00:27:39',10,4000.00,45.00,4000.00,10.00,NULL),(4,'P005','PLAT MICRO EMBOS',1,1,1,2,4,'2026-04-27 01:35:03','2026-04-27 01:35:03',NULL,NULL,NULL,NULL,NULL,NULL),(5,'	P004','PLAT CETAK',0,0,1,2,4,'2026-04-28 01:53:03','2026-04-28 01:53:03',NULL,NULL,NULL,NULL,NULL,NULL),(6,'CH001','ALKOHOL (IPA)',10,10,4,2,5,'2026-05-09 05:31:31','2026-05-09 05:31:31',NULL,NULL,NULL,NULL,NULL,NULL),(7,'K10','KERTAS FOIL SILVER GSM 60 GR 80 X 2000 M',10,10,3,2,1,'2026-05-09 08:04:57','2026-05-09 08:04:57',NULL,NULL,NULL,NULL,NULL,NULL),(8,'K11','KERTAS DUPLEX 250 GR 89 X 120',10,10,2,2,1,'2026-05-09 08:06:19','2026-05-09 08:06:19',NULL,89.00,120.00,NULL,250.00,NULL),(9,'CH003','UV WASH',10,10,4,2,5,'2026-05-09 08:08:12','2026-05-09 08:08:12',NULL,NULL,NULL,NULL,NULL,NULL),(10,'CH004','CLEANFIX',10,10,5,2,5,'2026-05-09 08:09:11','2026-05-09 08:09:11',NULL,NULL,NULL,NULL,NULL,NULL),(11,'K08','KERTAS METALIZED 237 GR 79 X 109',10,1,2,2,1,'2026-05-25 19:08:30','2026-05-25 19:08:30',NULL,79.00,109.00,NULL,237.00,NULL),(12,'T11','TINTA METALIZED CYAN',10,1,3,2,3,'2026-05-25 19:09:47','2026-05-25 19:09:47',NULL,NULL,NULL,NULL,NULL,NULL),(13,'T12','TINTA METALIZED MAGENTA',10,1,3,2,3,'2026-05-25 19:10:35','2026-05-25 19:10:35',NULL,NULL,NULL,NULL,NULL,NULL),(14,'T13','TINTA METALIZED YELLOW',10,1,3,2,3,'2026-05-25 19:11:14','2026-05-25 19:11:14',NULL,NULL,NULL,NULL,NULL,NULL),(15,'T14','TINTA METALIZED BLACK',10,1,3,2,3,'2026-05-25 19:12:03','2026-05-25 19:12:03',NULL,NULL,NULL,NULL,NULL,NULL),(16,'T15','TINTA METALIZED WHITE',1,1,3,2,3,'2026-05-25 19:12:53','2026-05-25 19:12:53',NULL,NULL,NULL,NULL,NULL,NULL),(17,'CH005','SPAREGUM',10,1,6,2,5,'2026-05-25 19:14:04','2026-05-25 19:14:04',NULL,NULL,NULL,NULL,NULL,NULL),(18,'CH008','CLEANER 190, ECOWASH',10,1,4,2,5,'2026-05-25 19:14:57','2026-05-25 19:14:57',NULL,NULL,NULL,NULL,NULL,NULL),(19,'CH006','FOUNTAIN',10,1,4,2,5,'2026-05-25 19:15:51','2026-05-25 19:15:51',NULL,NULL,NULL,NULL,NULL,NULL),(20,'CH012','PLATE CLEANER',10,1,4,2,5,'2026-05-25 19:17:09','2026-05-25 19:17:09',NULL,NULL,NULL,NULL,NULL,NULL),(21,'BP010','KARDUS 485 X 268 X 335 MM',1,1,8,2,4,'2026-05-25 19:18:04','2026-05-25 19:18:04',NULL,NULL,NULL,NULL,NULL,NULL),(22,'KR01','KERTAS IVORY ROLL',NULL,NULL,12,2,1,'2026-06-17 00:01:10','2026-06-17 00:01:10',NULL,4000.00,2.00,NULL,NULL,NULL);
/*!40000 ALTER TABLE `master_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_konversis`
--

DROP TABLE IF EXISTS `master_konversis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_konversis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type_item_id` bigint(20) unsigned NOT NULL,
  `satuan_satu_id` bigint(20) unsigned NOT NULL,
  `satuan_dua_id` bigint(20) unsigned NOT NULL,
  `nilai_konversi` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_konversis_type_item_id_foreign` (`type_item_id`),
  KEY `master_konversis_satuan_satu_id_foreign` (`satuan_satu_id`),
  KEY `master_konversis_satuan_dua_id_foreign` (`satuan_dua_id`),
  CONSTRAINT `master_konversis_satuan_dua_id_foreign` FOREIGN KEY (`satuan_dua_id`) REFERENCES `master_satuans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_konversis_satuan_satu_id_foreign` FOREIGN KEY (`satuan_satu_id`) REFERENCES `master_satuans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_konversis_type_item_id_foreign` FOREIGN KEY (`type_item_id`) REFERENCES `type_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_konversis`
--

LOCK TABLES `master_konversis` WRITE;
/*!40000 ALTER TABLE `master_konversis` DISABLE KEYS */;
INSERT INTO `master_konversis` VALUES (1,1,2,2,500,'2026-04-13 01:18:57','2026-04-13 01:18:57');
/*!40000 ALTER TABLE `master_konversis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_operators`
--

DROP TABLE IF EXISTS `master_operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_operators` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_operator` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_operators`
--

LOCK TABLES `master_operators` WRITE;
/*!40000 ALTER TABLE `master_operators` DISABLE KEYS */;
INSERT INTO `master_operators` VALUES (1,'Juan','2026-04-13 04:02:51','2026-04-13 04:02:51');
/*!40000 ALTER TABLE `master_operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_printings`
--

DROP TABLE IF EXISTS `master_printings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_printings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(255) NOT NULL,
  `jenis_mesin` varchar(255) NOT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `proses` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_printings`
--

LOCK TABLES `master_printings` WRITE;
/*!40000 ALTER TABLE `master_printings` DISABLE KEYS */;
INSERT INTO `master_printings` VALUES (1,'SM 74','PRINTING',100,'PRT',1,'2026-03-07 00:28:03','2026-03-07 00:28:03'),(2,'FLEXO ','PRINTING',13,'PRINTING ',1,'2026-10-20 07:22:34','2026-10-20 07:23:06');
/*!40000 ALTER TABLE `master_printings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_satuans`
--

DROP TABLE IF EXISTS `master_satuans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_satuans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_satuan` varchar(255) NOT NULL,
  `nama_satuan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_satuans_kode_satuan_unique` (`kode_satuan`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_satuans`
--

LOCK TABLES `master_satuans` WRITE;
/*!40000 ALTER TABLE `master_satuans` DISABLE KEYS */;
INSERT INTO `master_satuans` VALUES (1,'SHT','SHEET','2026-03-06 16:34:40','2026-03-06 16:34:40'),(2,'LBR','LEMBAR','2026-03-06 16:34:48','2026-03-06 16:34:48'),(3,'KG','KILOGRAM','2026-03-06 16:34:55','2026-03-06 16:34:55'),(4,'LTR','LITER','2026-03-06 16:35:02','2026-03-06 16:35:02'),(5,'TUB','TUB','2026-03-06 16:35:11','2026-03-06 16:35:11'),(6,'KLG','KALENG','2026-03-06 16:35:18','2026-03-06 16:35:18'),(7,'CAN','CAN','2026-03-06 16:35:24','2026-03-06 16:35:24'),(8,'PCS','PIECES','2026-03-06 16:35:31','2026-03-06 16:35:31'),(9,'ROLL','ROLL','2026-03-06 16:35:41','2026-03-06 16:35:41'),(10,'BTL','BOTOL','2026-03-06 16:35:48','2026-03-06 16:35:48'),(11,'LT','LITER','2026-04-15 02:01:44','2026-04-15 02:01:44'),(12,'MTR','METER','2026-06-16 22:54:49','2026-06-16 22:54:49');
/*!40000 ALTER TABLE `master_satuans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_spk`
--

DROP TABLE IF EXISTS `master_spk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_spk` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_spk` varchar(255) DEFAULT NULL,
  `kode_ik` varchar(50) DEFAULT NULL,
  `sales_order_id` bigint(20) unsigned DEFAULT NULL,
  `production_plan` varchar(255) DEFAULT NULL,
  `status` enum('ON PROCESS','FINISH') NOT NULL DEFAULT 'ON PROCESS',
  `tanggal_estimasi_selesai` date DEFAULT NULL,
  `tanggal_po` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `up_satu` int(11) DEFAULT NULL,
  `up_dua` int(11) DEFAULT NULL,
  `up_tiga` int(11) DEFAULT NULL,
  `ukuran_potong` varchar(100) DEFAULT NULL,
  `ukuran_cetak` varchar(100) DEFAULT NULL,
  `spesifikasi_kertas` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_spk` (`no_spk`),
  KEY `fk_spk_sales_order` (`sales_order_id`),
  CONSTRAINT `fk_spk_sales_order` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_spk`
--

LOCK TABLES `master_spk` WRITE;
/*!40000 ALTER TABLE `master_spk` DISABLE KEYS */;
INSERT INTO `master_spk` VALUES (12,'0001/SPK-IK25/062026','25',21,'NEW','ON PROCESS','2026-07-30','2026-06-18','2026-06-17 19:59:37','2026-06-17 19:59:37',15,10,13,'1','1','KERTAS IVORY 230 GR 53 X 68.5'),(13,'0002/SPK-IK25/062026','25',23,'NEW','ON PROCESS',NULL,'2026-06-18','2026-06-18 00:38:09','2026-06-18 00:38:09',1,NULL,NULL,NULL,NULL,'KERTAS IVORY ROLL'),(14,'0003/SPK-IK10/062026','10',24,'NEW','ON PROCESS',NULL,'2026-06-18','2026-06-18 01:31:52','2026-06-18 01:31:52',1,NULL,NULL,NULL,'1','ETIKET GUDANG GARAM FILTER'),(15,'0004/SPK-IK10/062026','10',25,'NEW','ON PROCESS','2026-06-26','2026-06-25','2026-06-26 05:27:09','2026-06-26 05:27:09',1,1,1,'1','1','PLAT CETAK POLYMER (COBA COBA)'),(16,'0005/SPK-IK80/072026','80',26,'NEW','ON PROCESS','2026-07-28','2026-07-11','2026-07-11 01:12:26','2026-07-11 01:12:26',15,10,13,'1','1','COBA COBA MATERIAL STOCK'),(18,'0006/SPK-IK80/072026','80',25,'NEW','ON PROCESS',NULL,'2026-06-25','2026-07-11 04:45:57','2026-07-11 04:45:57',1,NULL,NULL,NULL,'1','PLAT CETAK POLYMER (COBA COBA)'),(19,'0007/SPK-IK80/072026','80',25,'NEW','ON PROCESS','2026-07-13','2026-06-25','2026-07-13 03:00:50','2026-07-13 03:00:50',1,1,1,'1','1','PLAT CETAK POLYMER (COBA COBA)'),(26,'0001/SPK-IK001/102026','001',53,NULL,'ON PROCESS','2026-10-31','2026-10-18','2026-10-18 04:05:33','2026-10-18 04:05:33',12,NULL,NULL,NULL,'60 x 100 cm','KERTAS METALIZED [ETIKET]'),(27,'0002/SPK-IKIK001/DFISKT16/102026','IK001/DFISKT16',54,NULL,'FINISH','2026-10-22','2026-10-20','2026-10-20 07:19:51','2026-10-22 23:40:25',1,NULL,NULL,NULL,'65x150','METALIZE [ETIKET] 230 GSM [ETIKET]');
/*!40000 ALTER TABLE `master_spk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_suppliers`
--

DROP TABLE IF EXISTS `master_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_supplier` varchar(255) NOT NULL,
  `nama_supplier` varchar(255) NOT NULL,
  `jenis_supplier` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_suppliers_kode_supplier_unique` (`kode_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_suppliers`
--

LOCK TABLES `master_suppliers` WRITE;
/*!40000 ALTER TABLE `master_suppliers` DISABLE KEYS */;
INSERT INTO `master_suppliers` VALUES (1,'SUPP-001','ALEXA','Material','manukan',NULL,'2026-03-15 10:03:46','2026-03-15 10:03:46');
/*!40000 ALTER TABLE `master_suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_movements`
--

DROP TABLE IF EXISTS `material_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `material_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) unsigned NOT NULL,
  `reference_type` varchar(50) NOT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `movement_type` varchar(30) NOT NULL,
  `qty` decimal(18,2) NOT NULL DEFAULT 0.00,
  `stock_before` decimal(18,2) NOT NULL DEFAULT 0.00,
  `stock_after` decimal(18,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_material_movement_item` (`item_id`),
  KEY `fk_material_movement_user` (`created_by`),
  CONSTRAINT `fk_material_movement_item` FOREIGN KEY (`item_id`) REFERENCES `master_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_material_movement_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_movements`
--

LOCK TABLES `material_movements` WRITE;
/*!40000 ALTER TABLE `material_movements` DISABLE KEYS */;
INSERT INTO `material_movements` VALUES (1,18,'IMR',26,'IN',26.00,0.00,0.00,'Approve IMR',NULL,'2026-07-11 01:34:50','2026-07-11 01:34:50'),(2,21,'IMR',26,'IN',70.00,0.00,0.00,'Approve IMR',NULL,'2026-07-11 01:34:50','2026-07-11 01:34:50'),(3,1,'IMR',26,'IN',120.00,0.00,0.00,'Approve IMR',NULL,'2026-07-11 01:34:50','2026-07-11 01:34:50'),(4,18,'IMR',26,'ISSUE',26.00,99.00,73.00,'Issue Material : IMR-202607-0026',NULL,'2026-07-11 03:11:33','2026-07-11 03:11:33'),(5,21,'IMR',26,'ISSUE',69.99,962.00,892.01,'Issue Material : IMR-202607-0026',NULL,'2026-07-11 03:11:33','2026-07-11 03:11:33'),(6,1,'IMR',26,'ISSUE',120.00,99697.00,99577.00,'Issue Material : IMR-202607-0026',NULL,'2026-07-11 03:11:33','2026-07-11 03:11:33'),(7,21,'IMR',26,'ISSUE',0.01,892.01,892.00,'Issue Material : IMR-202607-0026',NULL,'2026-07-11 04:28:39','2026-07-11 04:28:39'),(8,5,'IMR',27,'APPROVE',100.00,1002.00,1002.00,'Approve IMR IMR-202607-0027',NULL,'2026-07-11 05:16:59','2026-07-11 05:16:59'),(9,5,'IMR',27,'ISSUE',-100.00,1002.00,902.00,'Issue Material IMR-202607-0027',NULL,'2026-07-11 05:17:33','2026-07-11 05:17:33'),(10,5,'IMR',29,'APPROVE',99.00,902.00,902.00,'Approve IMR IMR-202607-0029',NULL,'2026-07-13 06:03:52','2026-07-13 06:03:52'),(11,5,'IMR',29,'ISSUE',-99.00,902.00,803.00,'Issue Material IMR-202607-0029',NULL,'2026-07-13 06:04:33','2026-07-13 06:04:33'),(12,18,'IMR',31,'APPROVE',25.00,73.00,73.00,'Approve IMR IMR-202607-0031',NULL,'2026-07-13 06:22:15','2026-07-13 06:22:15'),(13,21,'IMR',31,'APPROVE',60.00,892.00,892.00,'Approve IMR IMR-202607-0031',NULL,'2026-07-13 06:22:15','2026-07-13 06:22:15'),(14,1,'IMR',31,'APPROVE',120.00,99577.00,99577.00,'Approve IMR IMR-202607-0031',NULL,'2026-07-13 06:22:15','2026-07-13 06:22:15'),(15,18,'IMR',31,'ISSUE',-10.00,73.00,63.00,'Issue Material IMR-202607-0031',NULL,'2026-07-13 06:25:11','2026-07-13 06:25:11'),(16,21,'IMR',31,'ISSUE',-50.00,892.00,842.00,'Issue Material IMR-202607-0031',NULL,'2026-07-13 06:25:11','2026-07-13 06:25:11'),(17,1,'IMR',31,'ISSUE',-100.00,99577.00,99477.00,'Issue Material IMR-202607-0031',NULL,'2026-07-13 06:25:11','2026-07-13 06:25:11'),(18,10,'App\\Models\\SuratJalan',14,'OUT',2.00,200.00,198.00,'Pengiriman Material - SJ-20260718-001',1,'2026-07-18 07:13:55','2026-07-18 07:13:55'),(19,8,'App\\Models\\SuratJalan',14,'OUT',5.00,200.00,195.00,'Pengiriman Material - SJ-20260718-001',1,'2026-07-18 07:13:55','2026-07-18 07:13:55'),(20,10,'App\\Models\\SuratJalan',16,'OUT',2.00,198.00,196.00,'Pengiriman Material - SJ-20260718-002',1,'2026-07-18 07:30:44','2026-07-18 07:30:44'),(21,8,'App\\Models\\SuratJalan',16,'OUT',5.00,195.00,190.00,'Pengiriman Material - SJ-20260718-002',1,'2026-07-18 07:30:44','2026-07-18 07:30:44'),(22,1,'App\\Models\\SuratJalan',18,'OUT',500.00,99477.00,98977.00,'Pengiriman Material - SJ-20260718-004',1,'2026-07-18 09:14:34','2026-07-18 09:14:34'),(23,21,'App\\Models\\SuratJalan',19,'OUT',5.00,842.00,837.00,'Pengiriman Material - SJ-20260719-001',1,'2026-07-19 03:07:30','2026-07-19 03:07:30'),(24,21,'App\\Models\\SuratJalan',20,'OUT',5.00,837.00,832.00,'Pengiriman Material - SJ-20260719-002',1,'2026-07-19 03:08:58','2026-07-19 03:08:58'),(25,20,'App\\Models\\SuratJalan',21,'OUT',5.00,9.00,4.00,'Pengiriman Material - SJ-20260719-003',1,'2026-07-19 06:37:00','2026-07-19 06:37:00'),(26,17,'App\\Models\\SuratJalan',22,'OUT',10.00,99.00,89.00,'Pengiriman Material - SJ-20260909-001',1,'2026-09-09 00:00:22','2026-09-09 00:00:22'),(27,18,'App\\Models\\SuratJalan',22,'OUT',5.00,63.00,58.00,'Pengiriman Material - SJ-20260909-001',1,'2026-09-09 00:00:22','2026-09-09 00:00:22'),(28,1,'App\\Models\\SuratJalan',23,'OUT',977.00,98977.00,98000.00,'Pengiriman Material - SJ-20260909-002',1,'2026-09-09 00:20:16','2026-09-09 00:20:16'),(29,1,'MANUAL',NULL,'IN',5.00,98000.00,98005.00,'Test StockService Increase',NULL,'2026-10-13 23:48:22','2026-10-13 23:48:22'),(30,1,'MANUAL',NULL,'OUT',5.00,98005.00,98000.00,'Test StockService Decrease',NULL,'2026-10-13 23:48:22','2026-10-13 23:48:22'),(31,1,'MANUAL',NULL,'ALLOCATE',5.00,98000.00,97995.00,'Test Allocation',NULL,'2026-10-13 23:53:48','2026-10-13 23:53:48'),(32,1,'MANUAL',NULL,'ALLOCATE',5.00,97995.00,97990.00,'Test Allocation',NULL,'2026-10-13 23:57:20','2026-10-13 23:57:20'),(46,6,'App\\Models\\PenerimaanBarang',28,'IN',1.00,265.00,266.00,'Penerimaan Barang PO - PB-20261020-001',1,'2026-10-20 07:58:39','2026-10-20 07:58:39'),(47,10,'App\\Models\\PenerimaanBarang',28,'IN',1.00,196.00,197.00,'Penerimaan Barang PO - PB-20261020-001',1,'2026-10-20 07:58:39','2026-10-20 07:58:39'),(48,6,'App\\Models\\PenerimaanBarang',28,'OUT',1.00,266.00,265.00,'Rollback Penerimaan Barang Edit - PB-20261020-001',1,'2026-10-20 07:58:57','2026-10-20 07:58:57'),(49,10,'App\\Models\\PenerimaanBarang',28,'OUT',1.00,197.00,196.00,'Rollback Penerimaan Barang Edit - PB-20261020-001',1,'2026-10-20 07:58:57','2026-10-20 07:58:57'),(50,6,'App\\Models\\PenerimaanBarang',28,'IN',1.00,265.00,266.00,'Penerimaan Barang PO - PB-20261020-001',1,'2026-10-20 07:58:58','2026-10-20 07:58:58'),(51,10,'App\\Models\\PenerimaanBarang',28,'IN',1.00,196.00,197.00,'Penerimaan Barang PO - PB-20261020-001',1,'2026-10-20 07:58:58','2026-10-20 07:58:58'),(52,18,'IMR',33,'APPROVE',5.00,58.00,58.00,'Approve IMR IMR-202610-0033',NULL,'2026-10-20 08:06:52','2026-10-20 08:06:52'),(53,1,'IMR',33,'APPROVE',15.00,97990.00,97990.00,'Approve IMR IMR-202610-0033',NULL,'2026-10-20 08:06:52','2026-10-20 08:06:52'),(54,18,'IMR',33,'ISSUE',-5.00,58.00,53.00,'Issue Material IMR-202610-0033',NULL,'2026-10-20 08:07:29','2026-10-20 08:07:29'),(55,1,'IMR',33,'ISSUE',-15.00,97990.00,97975.00,'Issue Material IMR-202610-0033',NULL,'2026-10-20 08:07:29','2026-10-20 08:07:29');
/*!40000 ALTER TABLE `material_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `material_stocks`
--

DROP TABLE IF EXISTS `material_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `material_stocks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) unsigned NOT NULL,
  `on_hand` decimal(15,2) DEFAULT 0.00,
  `allocation` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`),
  CONSTRAINT `material_stocks_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `master_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `material_stocks`
--

LOCK TABLES `material_stocks` WRITE;
/*!40000 ALTER TABLE `material_stocks` DISABLE KEYS */;
INSERT INTO `material_stocks` VALUES (6,1,97975.00,430.00,'2026-04-30 02:22:01','2026-10-20 08:07:29'),(7,2,1138.00,0.00,'2026-04-30 02:22:01','2026-07-05 20:17:16'),(8,3,20.00,0.00,'2026-04-30 02:22:01','2026-07-05 20:17:16'),(9,5,803.00,2.00,'2026-04-30 02:22:01','2026-07-13 06:04:33'),(10,4,1080.00,26.00,'2026-04-30 02:22:01','2026-07-05 20:17:16'),(11,10,197.00,0.00,'2026-05-09 08:32:32','2026-10-20 07:58:58'),(12,6,266.00,31.00,'2026-05-09 08:32:32','2026-10-20 07:58:58'),(13,8,190.00,0.00,'2026-05-09 08:32:32','2026-07-18 07:30:44'),(14,9,200.00,0.00,'2026-05-09 08:32:32','2026-05-10 20:49:47'),(15,11,9010.00,60.00,'2026-05-25 20:40:27','2026-07-06 01:57:37'),(16,12,9.00,1.00,'2026-05-25 20:40:27','2026-05-25 21:40:23'),(17,13,9.00,1.00,'2026-05-25 20:40:27','2026-05-25 21:40:23'),(18,14,0.00,1.00,'2026-05-25 20:40:27','2026-05-25 21:40:23'),(19,17,89.00,1.00,'2026-05-25 20:40:27','2026-09-09 00:00:22'),(20,18,53.00,16.00,'2026-05-25 20:40:27','2026-10-20 08:07:29'),(21,19,99.00,1.00,'2026-05-25 20:40:27','2026-05-25 21:40:23'),(22,20,4.00,1.00,'2026-05-25 20:40:27','2026-07-19 06:37:00'),(23,21,832.00,60.00,'2026-05-25 20:40:27','2026-07-19 03:08:58'),(24,15,9.00,1.00,'2026-05-25 21:06:25','2026-05-25 21:40:23'),(25,16,7.00,3.00,'2026-05-25 21:27:20','2026-07-10 17:05:54'),(26,7,2.00,12.00,'2026-05-27 01:48:12','2026-06-26 03:48:08'),(28,22,30984.00,9016.00,'2026-06-17 00:38:22','2026-07-10 17:05:54');
/*!40000 ALTER TABLE `material_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesin_die_makings`
--

DROP TABLE IF EXISTS `mesin_die_makings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesin_die_makings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(255) NOT NULL,
  `jenis_mesin` varchar(255) NOT NULL,
  `kapasitas` int(11) NOT NULL DEFAULT 0,
  `proses` varchar(255) NOT NULL,
  `status` enum('aktif','maintenance','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesin_die_makings`
--

LOCK TABLES `mesin_die_makings` WRITE;
/*!40000 ALTER TABLE `mesin_die_makings` DISABLE KEYS */;
INSERT INTO `mesin_die_makings` VALUES (1,'JIN BAO','	UV SPOT',15000,'	UV SPOT','aktif','2026-04-14 03:44:09','2026-04-14 03:44:09');
/*!40000 ALTER TABLE `mesin_die_makings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesin_finishing`
--

DROP TABLE IF EXISTS `mesin_finishing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesin_finishing` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(255) NOT NULL,
  `jenis_mesin` varchar(255) NOT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `proses` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesin_finishing`
--

LOCK TABLES `mesin_finishing` WRITE;
/*!40000 ALTER TABLE `mesin_finishing` DISABLE KEYS */;
INSERT INTO `mesin_finishing` VALUES (2,'BLANKING','semi_otomatis',30000,'PROTOL',1,'2026-05-06 21:53:32','2026-05-06 21:53:32');
/*!40000 ALTER TABLE `mesin_finishing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mesin_pra_cetaks`
--

DROP TABLE IF EXISTS `mesin_pra_cetaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mesin_pra_cetaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mesin` varchar(255) NOT NULL,
  `jenis_mesin` varchar(255) NOT NULL,
  `kapasitas` varchar(100) DEFAULT NULL,
  `proses` varchar(100) NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mesin_pra_cetaks`
--

LOCK TABLES `mesin_pra_cetaks` WRITE;
/*!40000 ALTER TABLE `mesin_pra_cetaks` DISABLE KEYS */;
INSERT INTO `mesin_pra_cetaks` VALUES (2,'BLANKING','PRA CETAK','10','plate_making','aktif','2026-05-25 19:05:08','2026-05-25 19:05:08');
/*!40000 ALTER TABLE `mesin_pra_cetaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metode_bayars`
--

DROP TABLE IF EXISTS `metode_bayars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metode_bayars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_pembayaran` varchar(50) NOT NULL,
  `metode_pembayaran` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pembayaran` (`kode_pembayaran`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metode_bayars`
--

LOCK TABLES `metode_bayars` WRITE;
/*!40000 ALTER TABLE `metode_bayars` DISABLE KEYS */;
INSERT INTO `metode_bayars` VALUES (1,'TF ','Transfer','2026-05-18 23:03:03','2026-05-18 23:03:30');
/*!40000 ALTER TABLE `metode_bayars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_03_05_153839_create_master_departemens_table',2),(6,'2026_03_05_153954_create_karyawans_table',2),(7,'2026_03_05_163031_create_customers_table',3),(8,'2026_03_06_230825_create_category_items_table',4),(9,'2026_03_06_231710_create_type_items_table',5),(10,'2026_03_06_232622_create_master_satuans_table',6),(11,'2026_03_06_235514_create_master_konversis_table',7),(12,'2026_03_07_000400_create_master_suppliers_table',8),(13,'2026_03_07_001649_create_master_items_table',9),(14,'2026_03_07_044136_add_dimension_to_master_items',10),(15,'2026_03_07_045005_add_tipe_penjualan_to_master_items',11),(16,'2026_03_07_070423_create_master_printings_table',12),(17,'2026_03_07_071800_create_master_operators_table',13),(18,'2026_03_09_081820_create_master_finishings_table',14),(19,'2026_03_09_083451_create_operator_finishings_table',15),(20,'2026_03_12_103218_create_purchase_requests_table',16),(21,'2026_03_12_104046_create_purchase_request_items_table',16),(22,'2026_03_15_162454_create_purchase_orders_table',17),(23,'2026_03_15_183421_create_purchase_order_items_table',18),(24,'2026_03_16_063745_add_nomor_pr_to_purchase_requests',19),(25,'2026_03_16_065545_add_no_po_to_purchase_orders',20),(26,'2026_03_15_165731_create_external_returns_table',21),(27,'2026_03_15_170459_create_internal_returns_table',21),(28,'2026_03_15_170936_create_purchase_orders_table',22),(29,'2026_03_15_172144_create_finish_good_items_table',22),(30,'2026_03_15_173446_create_absensis_table',22),(31,'2026_03_15_173749_create_lemburs_table',22),(32,'2026_03_15_173851_create_izins_table',22),(33,'2026_03_15_174030_create_cutis_table',22),(34,'2026_03_15_180452_create_surat_jalans_table',22),(35,'2026_03_15_180937_create_subcount_outs_table',22),(36,'2026_03_15_181327_create_subcount_ins_table',22),(37,'2026_03_15_184713_create_inv_payments_table',22),(38,'2026_03_15_185114_create_coa_classes_table',22),(39,'2026_03_15_185223_create_metode_bayars_table',22),(40,'2026_03_15_185325_create_master_c_o_a_s_table',22),(41,'2026_03_15_185458_create_trans_fakturs_table',22),(42,'2026_03_15_185609_create_trans_kas_table',22),(43,'2026_03_15_185715_create_trans_kas_banks_table',22),(44,'2026_03_15_185844_create_operasional_pays_table',22),(45,'2026_03_15_185946_create_po_billings_table',22),(46,'2026_03_15_190101_create_po_bill_pays_table',22),(47,'2026_03_19_144700_create_finish_good_item_materials_table',22),(48,'2026_03_25_001217_create_sales_orders_table',22),(49,'2026_04_13_083654_create_penerimaan_barangs_table',22),(50,'2026_04_13_084053_create_penerimaan_barang_items_table',22),(51,'2026_04_13_105455_create_printings_table',22),(52,'2026_04_13_145532_create_mesin_die_makings_table',22),(53,'2026_04_14_070513_create_operator_diemaking_table',22),(54,'2026_04_26_124144_create_pracetak_processes_table',22),(55,'2026_05_16_132503_create_invoices_table',22),(56,'2026_05_21_055926_create_trans_faktur_items_table',22),(57,'2026_05_27_080703_create_po_billing_items_table',22),(58,'2026_05_27_135500_create_po_bill_pay_items_table',22),(59,'2026_06_09_060806_create_error_productions_table',22),(60,'2026_07_08_002032_external_return_items',22),(61,'2026_07_09_034900_imr_printing',22),(62,'2026_07_09_035055_imr_printing_items',22),(63,'2026_07_16_155938_sales_order_items',22),(64,'2026_07_17_120811_surat_jalan_items',22),(65,'2026_08_01_000001_create_mesin_pra_cetaks_table',22),(66,'2026_08_01_000002_create_material_stocks_table',22),(67,'2026_08_01_000003_create_material_movements_table',22),(68,'2026_08_01_000004_create_master_spk_table',22),(69,'2026_08_01_000005_create_diemakings_table',22),(70,'2026_08_01_000006_create_operator_pracetak_table',22),(71,'2026_08_01_000007_create_subcount_out_items_table',22),(72,'2026_08_01_000008_create_subcount_in_items_table',22),(73,'2026_08_01_000009_create_mesin_finishing_table',22),(74,'2026_08_01_000010_create_master_finishing_table',22),(75,'2026_08_01_000011_create_peng_pinjamans_table',22),(76,'2026_08_01_000012_create_pemb_pinjamans_table',22),(77,'2026_08_01_000013_create_packagings_table',22),(78,'2026_10_14_000001_add_spec_columns_to_finish_good_items_table',23),(79,'2026_10_14_000002_add_spec_and_terms_to_sales_orders_table',23),(80,'2026_10_14_000003_create_coa_movements_table',24),(81,'2026_10_14_000004_create_payroll_and_hr_tables',25),(82,'2026_10_15_000001_create_approval_forms_and_order_confirmations_tables',26),(83,'2026_10_15_000002_add_file_design_to_approval_forms_table',27),(84,'2026_10_15_000003_make_qty_nullable_in_finish_good_item_materials_table',28),(85,'2026_10_18_000001_add_kertas_items_to_finish_good_items_table',29),(86,'2026_10_18_000002_create_internal_returns_and_items_tables',30),(87,'2026_10_15_052552_create_berita_acaras_table',31),(88,'2026_10_20_140000_add_source_document_links_to_berita_acaras_table',32),(89,'2026_10_20_150000_create_berita_acara_items_table',33),(90,'2026_10_21_103818_add_account_details_to_master_coas_table',34),(91,'2026_10_06_120000_update_invoices_and_create_invoice_items_table',35),(92,'2026_10_07_000000_add_potongan_to_invoices_table',36);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operasional_pays`
--

DROP TABLE IF EXISTS `operasional_pays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operasional_pays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(100) DEFAULT NULL,
  `gudang` varchar(100) DEFAULT NULL,
  `periode` varchar(10) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `account_kas_id` bigint(20) unsigned DEFAULT NULL,
  `account_beban_id` bigint(20) unsigned DEFAULT NULL,
  `karyawan_id` bigint(20) unsigned DEFAULT NULL,
  `nominal` bigint(20) DEFAULT 0,
  `nopol` varchar(100) DEFAULT NULL,
  `odometer` varchar(100) DEFAULT NULL,
  `mesin` varchar(100) DEFAULT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `kode` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operasional_pays`
--

LOCK TABLES `operasional_pays` WRITE;
/*!40000 ALTER TABLE `operasional_pays` DISABLE KEYS */;
INSERT INTO `operasional_pays` VALUES (1,'OPR-00001','UGRMS','2026','2026-06-05',1,1,1,50000,NULL,NULL,NULL,NULL,NULL,'BELI GALON CUY','ACTIVE','2026-06-05 01:03:19','2026-06-05 01:03:19'),(2,'OPR-00002','UGRMS','2026','2026-06-10',1,1,1,200000,'8894324',NULL,NULL,NULL,NULL,NULL,'ACTIVE','2026-06-10 08:47:26','2026-06-10 08:47:26');
/*!40000 ALTER TABLE `operasional_pays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operator_diemaking`
--

DROP TABLE IF EXISTS `operator_diemaking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operator_diemaking` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_operator` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_diemaking`
--

LOCK TABLES `operator_diemaking` WRITE;
/*!40000 ALTER TABLE `operator_diemaking` DISABLE KEYS */;
INSERT INTO `operator_diemaking` VALUES (1,'Juan','2026-04-14 02:48:15','2026-04-14 02:48:15');
/*!40000 ALTER TABLE `operator_diemaking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operator_finishings`
--

DROP TABLE IF EXISTS `operator_finishings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operator_finishings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_operator` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_finishings`
--

LOCK TABLES `operator_finishings` WRITE;
/*!40000 ALTER TABLE `operator_finishings` DISABLE KEYS */;
INSERT INTO `operator_finishings` VALUES (1,'Juan','2026-05-06 22:58:58','2026-05-06 22:58:58');
/*!40000 ALTER TABLE `operator_finishings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operator_pracetak`
--

DROP TABLE IF EXISTS `operator_pracetak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operator_pracetak` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_operator` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_pracetak`
--

LOCK TABLES `operator_pracetak` WRITE;
/*!40000 ALTER TABLE `operator_pracetak` DISABLE KEYS */;
INSERT INTO `operator_pracetak` VALUES (1,'gwejh','2026-04-26 06:23:10','2026-04-26 06:23:10');
/*!40000 ALTER TABLE `operator_pracetak` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_confirmation_items`
--

DROP TABLE IF EXISTS `order_confirmation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_confirmation_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_confirmation_id` bigint(20) unsigned NOT NULL,
  `approval_form_item_id` bigint(20) unsigned DEFAULT NULL,
  `finish_good_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `komponen` varchar(100) DEFAULT NULL,
  `isi` varchar(50) DEFAULT NULL,
  `kertas` varchar(255) DEFAULT NULL,
  `warna` varchar(100) DEFAULT NULL,
  `finishing` text DEFAULT NULL,
  `qty` decimal(15,2) NOT NULL DEFAULT 0.00,
  `harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `satuan` varchar(50) NOT NULL DEFAULT 'Pcs',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_confirmation_items_order_confirmation_id_foreign` (`order_confirmation_id`),
  KEY `order_confirmation_items_approval_form_item_id_foreign` (`approval_form_item_id`),
  KEY `order_confirmation_items_finish_good_item_id_foreign` (`finish_good_item_id`),
  CONSTRAINT `order_confirmation_items_approval_form_item_id_foreign` FOREIGN KEY (`approval_form_item_id`) REFERENCES `approval_form_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_confirmation_items_finish_good_item_id_foreign` FOREIGN KEY (`finish_good_item_id`) REFERENCES `finish_good_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_confirmation_items_order_confirmation_id_foreign` FOREIGN KEY (`order_confirmation_id`) REFERENCES `order_confirmations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_confirmation_items`
--

LOCK TABLES `order_confirmation_items` WRITE;
/*!40000 ALTER TABLE `order_confirmation_items` DISABLE KEYS */;
INSERT INTO `order_confirmation_items` VALUES (1,1,NULL,NULL,'PDW88 Sliding Luar','SKT','Etiket','20','Metalize','5','Spot UV, Emboss, Lem, Cutting',50000.00,470.00,23500000.00,'Pcs','2026-10-15 02:33:53','2026-10-15 02:33:53'),(2,1,NULL,NULL,'PDW88 Sliding Dalam','SKT','Inner','20','Ivory','5','Spot UV, Cutting',50000.00,350.00,17500000.00,'Pcs','2026-10-15 02:33:53','2026-10-15 02:33:53'),(3,2,NULL,23,'Wali Jagat','SKT','Etiket','12','Metalized','6','Spot UV, Embos, Debos',100000.00,2000.00,200000000.00,'Pcs','2026-10-15 19:22:03','2026-10-15 19:22:03'),(4,3,4,4,'ETIKET DJANDA BOLD 20 SKM','SKM','FILTER','26','Metalized','8','Spot UV, Cutting',50000.00,5000.00,250000000.00,'Pcs','2026-10-17 22:07:25','2026-10-17 22:07:25'),(5,4,5,13,'ETIKET DJARUM SUPER','SKM','ETIKET','20','KERTAS IVORY ROLLL','6','Spot UV, emboss, lem, cutting',10000.00,5000.00,50000000.00,'Pcs','2026-10-18 01:05:04','2026-10-18 01:30:24'),(6,5,7,25,'Dafi ','SKT','ETIKET','16','METALIZE','4','embos,lem,cutting',50000.00,324.00,16200000.00,'Pcs','2026-10-20 06:54:56','2026-10-20 06:54:56'),(7,5,7,NULL,'dafi','skm','eetiket','16','metalize','4','embos,lem,cutting',50000.00,324.00,16200000.00,'Pcs','2026-10-20 06:54:56','2026-10-20 06:54:56');
/*!40000 ALTER TABLE `order_confirmation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_confirmations`
--

DROP TABLE IF EXISTS `order_confirmations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_confirmations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approval_form_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `doc_no` varchar(100) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `lead_time` varchar(100) DEFAULT '21 Hari Kerja',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `terms_conditions` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'draft',
  `disetujui_customer` varchar(150) DEFAULT NULL,
  `diketahui_ttc` varchar(150) DEFAULT 'PT. Teknografi Tri Cawanaska',
  `sales_order_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_confirmations_doc_no_unique` (`doc_no`),
  KEY `order_confirmations_approval_form_id_foreign` (`approval_form_id`),
  KEY `order_confirmations_customer_id_foreign` (`customer_id`),
  KEY `order_confirmations_sales_order_id_foreign` (`sales_order_id`),
  KEY `order_confirmations_created_by_foreign` (`created_by`),
  KEY `order_confirmations_order_id_index` (`order_id`),
  CONSTRAINT `order_confirmations_approval_form_id_foreign` FOREIGN KEY (`approval_form_id`) REFERENCES `approval_forms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_confirmations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_confirmations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `order_confirmations_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_confirmations`
--

LOCK TABLES `order_confirmations` WRITE;
/*!40000 ALTER TABLE `order_confirmations` DISABLE KEYS */;
INSERT INTO `order_confirmations` VALUES (1,NULL,'OR-26-X-C005-001','SC-26-X-C005-001',2,'2026-10-15','21 Hari Kerja',41000000.00,0.00,41000000.00,'1. Spesifikasi, quantity, dan harga telah disepakati.\n2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.\n3. Produksi dimulai setelah dokumen ini disetujui.',NULL,'deal',NULL,'PT. Teknografi Tri Cawanaska',51,NULL,'2026-10-15 02:33:53','2026-10-15 02:33:53'),(2,NULL,'OR-26-X-	CS001-002','SC-26-X-	CS001-002',1,'2026-10-16','2 bulan pengerjaan',200000000.00,0.00,200000000.00,'1. Spesifikasi, quantity, dan harga telah disepakati.\n2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.\n3. Produksi dimulai setelah dokumen ini disetujui.',NULL,'draft','Juan anjayyy','PT. Teknografi Tri Cawanaska',NULL,1,'2026-10-15 19:22:03','2026-10-15 19:22:03'),(3,3,'OR-26-X-	CS001-001','SC-26-X-	CS001-003',1,'2026-10-18','1 bulan',250000000.00,1000000.00,249000000.00,'1. Spesifikasi, quantity, dan harga telah disepakati.\n2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.\n3. Produksi dimulai setelah dokumen ini disetujui.',NULL,'deal','Juan anjayyy','PT. Teknografi Tri Cawanaska',52,1,'2026-10-17 22:07:24','2026-10-17 23:00:14'),(4,4,'OR-26-X-CS003-002','SC-26-X-CS003-004',3,'2026-10-18','2 minggu',50000000.00,0.00,50000000.00,'1. Spesifikasi, quantity, dan harga telah disepakati.\n2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.\n3. Produksi dimulai setelah dokumen ini disetujui.',NULL,'converted_to_so','Farhan Abdillah','PT. Teknografi Tri Cawanaska',53,1,'2026-10-18 01:05:04','2026-10-18 01:48:47'),(5,6,'OR-26-X-001-004','SC-26-X-001-005',4,'2026-10-20','21 ',32400000.00,0.00,32400000.00,'1. Spesifikasi, quantity, dan harga telah disepakati.\n2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.\n3. Produksi dimulai setelah dokumen ini disetujui.',NULL,'converted_to_so','Catur Wiratama Tobacco ','PT. Teknografi Tri Cawanaska',54,1,'2026-10-20 06:54:56','2026-10-20 07:00:57');
/*!40000 ALTER TABLE `order_confirmations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packagings`
--

DROP TABLE IF EXISTS `packagings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packagings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_packaging` varchar(100) DEFAULT NULL,
  `spk_id` bigint(20) unsigned NOT NULL,
  `satuan_transfer` enum('Box','Pallete','Pack') NOT NULL,
  `jenis_transfer` enum('barang_hasil_baik','label_kuning','blokir') NOT NULL,
  `tgl_transfer` date NOT NULL,
  `jumlah_satuan_penuh` int(11) DEFAULT 0,
  `qty_per_satuan_penuh` int(11) DEFAULT 0,
  `jumlah_satuan_sisa` int(11) DEFAULT 0,
  `qty_per_satuan_sisa` int(11) DEFAULT 0,
  `total_satuan_penuh` bigint(20) DEFAULT 0,
  `total_satuan_sisa` bigint(20) DEFAULT 0,
  `grand_total` bigint(20) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_packaging_spk` (`spk_id`),
  CONSTRAINT `fk_packaging_spk` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packagings`
--

LOCK TABLES `packagings` WRITE;
/*!40000 ALTER TABLE `packagings` DISABLE KEYS */;
INSERT INTO `packagings` VALUES (9,'PKG-20261023-001',27,'Box','barang_hasil_baik','2026-10-23',100,100,0,0,10000,0,10000,'2026-10-22 23:38:43','2026-10-22 23:38:43');
/*!40000 ALTER TABLE `packagings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pemb_pinjamans`
--

DROP TABLE IF EXISTS `pemb_pinjamans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pemb_pinjamans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_bukti` varchar(50) NOT NULL,
  `peng_pinjaman_id` bigint(20) unsigned NOT NULL,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `tahap_cicilan` int(11) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `nominal_pembayaran` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_bukti` (`no_bukti`),
  KEY `fk_pemb_pinjamans_peng_pinjaman` (`peng_pinjaman_id`),
  KEY `fk_pemb_pinjamans_karyawan` (`karyawan_id`),
  CONSTRAINT `fk_pemb_pinjamans_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pemb_pinjamans_peng_pinjaman` FOREIGN KEY (`peng_pinjaman_id`) REFERENCES `peng_pinjamans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemb_pinjamans`
--

LOCK TABLES `pemb_pinjamans` WRITE;
/*!40000 ALTER TABLE `pemb_pinjamans` DISABLE KEYS */;
/*!40000 ALTER TABLE `pemb_pinjamans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penerimaan_barang_items`
--

DROP TABLE IF EXISTS `penerimaan_barang_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penerimaan_barang_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penerimaan_barang_id` bigint(20) unsigned NOT NULL,
  `purchase_order_item_id` bigint(20) unsigned NOT NULL,
  `qty_terima` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tgl_exp` date DEFAULT NULL,
  `no_lot` varchar(100) DEFAULT NULL,
  `catatan_item` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `qty_return` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `fk_penerimaan_barang_item_header` (`penerimaan_barang_id`),
  KEY `fk_penerimaan_barang_item_po_item` (`purchase_order_item_id`),
  CONSTRAINT `fk_penerimaan_barang_item_header` FOREIGN KEY (`penerimaan_barang_id`) REFERENCES `penerimaan_barangs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_penerimaan_barang_item_po_item` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan_barang_items`
--

LOCK TABLES `penerimaan_barang_items` WRITE;
/*!40000 ALTER TABLE `penerimaan_barang_items` DISABLE KEYS */;
INSERT INTO `penerimaan_barang_items` VALUES (5,17,66,2.00,'2026-07-03','888','KULAKAN KERTAS GES','2026-05-27 01:48:12','2026-05-27 01:48:12',0.00),(6,18,67,10.00,NULL,NULL,NULL,'2026-06-10 08:57:15','2026-06-10 08:57:15',0.00),(7,18,68,12.00,NULL,NULL,NULL,'2026-06-10 08:57:15','2026-06-10 08:57:15',0.00),(8,19,69,40000.00,'2029-07-26','888','terima pengantaran','2026-06-17 00:10:45','2026-06-17 00:10:45',0.00),(9,20,69,40000.00,'2026-06-17','123','hehehhe','2026-06-17 00:38:22','2026-06-17 00:38:22',0.00),(10,21,71,1000.00,'2026-06-26','123','wewewew','2026-06-26 04:39:05','2026-06-26 04:39:05',0.00),(11,22,72,1000.00,'2026-06-26','3E32E23E23E','RESTOCK COBA COBA','2026-06-26 05:06:16','2026-06-26 05:06:16',0.00),(12,23,42,50.00,NULL,NULL,NULL,'2026-07-05 20:17:16','2026-07-05 20:17:16',0.00),(13,23,43,98.00,NULL,NULL,NULL,'2026-07-05 20:17:16','2026-07-05 20:17:16',0.00),(14,23,44,9.00,NULL,NULL,NULL,'2026-07-05 20:17:16','2026-07-05 20:17:16',0.00),(15,23,45,3.00,NULL,NULL,NULL,'2026-07-05 20:17:16','2026-07-05 20:17:16',0.00),(16,23,46,50.00,NULL,NULL,NULL,'2026-07-05 20:17:16','2026-07-05 20:17:16',0.00),(17,24,75,10.00,'2026-07-29','888','wewewewe','2026-07-06 01:57:37','2026-07-06 01:57:37',0.00),(18,25,76,10.00,NULL,NULL,NULL,'2026-07-08 00:07:48','2026-07-08 00:07:48',0.00);
/*!40000 ALTER TABLE `penerimaan_barang_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penerimaan_barangs`
--

DROP TABLE IF EXISTS `penerimaan_barangs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penerimaan_barangs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_penerimaan` varchar(100) NOT NULL,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `tanggal_terima` date NOT NULL,
  `no_surat_jalan` varchar(100) DEFAULT NULL,
  `nama_pengirim` varchar(100) DEFAULT NULL,
  `nopol_kendaraan` varchar(50) DEFAULT NULL,
  `catatan_pengiriman` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_penerimaan` (`no_penerimaan`),
  KEY `fk_penerimaan_barang_po` (`purchase_order_id`),
  CONSTRAINT `fk_penerimaan_barang_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerimaan_barangs`
--

LOCK TABLES `penerimaan_barangs` WRITE;
/*!40000 ALTER TABLE `penerimaan_barangs` DISABLE KEYS */;
INSERT INTO `penerimaan_barangs` VALUES (9,'PB-20260430-001',19,'2026-04-30','12345678','Farhan ','W 3087 VU','material production','2026-04-30 02:22:00','2026-04-30 02:22:00'),(10,'PB-20260503-001',20,'2026-05-03','12345','Farhan','W 3087 VU','vevevevev','2026-05-02 22:14:23','2026-05-02 22:14:23'),(11,'PB-20260509-001',21,'2026-05-09','12345','Farhan','W 3087 VU','-','2026-05-09 08:32:32','2026-05-09 08:32:32'),(12,'PB-20260511-001',21,'2026-05-11','12345','Farhan','W 3087 VU','PO MATERIAL PRODUCTION','2026-05-10 20:49:47','2026-05-10 20:49:47'),(13,'PB-20260511-002',22,'2026-05-11','12345','Farhan','W 3087 VU','-','2026-05-10 20:54:52','2026-05-10 20:54:52'),(14,'PB-20260526-001',23,'2026-05-26','12345','Farhan','W 3087 VU','kulakan','2026-05-25 20:40:27','2026-05-25 20:40:27'),(15,'PB-20260526-002',24,'2026-05-26','12345','Farhan','W 3087 VU','kikikikiki','2026-05-25 21:06:25','2026-05-25 21:06:25'),(16,'PB-20260526-003',25,'2026-05-26','12345','Farhan','W 3087 VU','0000','2026-05-25 21:27:20','2026-05-25 21:27:20'),(17,'PB-20260527-001',26,'2026-05-27','12345','Farhan','W 3087 VU','NATURA ANJAYY','2026-05-27 01:48:12','2026-05-27 01:48:12'),(18,'PB-20260610-001',27,'2026-06-10','12345',NULL,NULL,NULL,'2026-06-10 08:57:15','2026-06-10 08:57:15'),(19,'PB-20260617-001',28,'2026-06-17','12345','Farhan','W 3087 VU','hehehe','2026-06-17 00:10:45','2026-06-17 00:10:45'),(20,'PB-20260617-002',28,'2026-06-17','12345','Farhan','W 3087 VU','hehheheh','2026-06-17 00:38:22','2026-06-17 00:38:22'),(21,'PB-20260626-001',31,'2026-06-26','12345','Farhan','W 3087 VU','halo','2026-06-26 04:39:05','2026-06-26 04:39:05'),(22,'PB-20260626-002',32,'2026-06-18','12345','Farhan','W 3087 VU','wewewew','2026-06-26 05:06:16','2026-06-26 05:06:16'),(23,'PB-20260706-001',19,'2026-07-15','12345','Farhan','W 3087 VU','uhuhuhu','2026-07-05 20:17:16','2026-07-05 20:17:16'),(24,'PB-20260706-002',36,'2026-07-23','12345','Farhan','W 3087 VU',NULL,'2026-07-06 01:57:37','2026-07-06 01:57:37'),(25,'PB-20260708-001',37,'2026-07-08','12345','Farhan','W 3087 VU',NULL,'2026-07-08 00:07:48','2026-07-08 00:07:48'),(28,'PB-20261020-001',40,'2026-10-20','sjk001dfskt16','YAUL','N 444 TTC',NULL,'2026-10-20 07:58:39','2026-10-20 07:58:39');
/*!40000 ALTER TABLE `penerimaan_barangs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peng_pinjamans`
--

DROP TABLE IF EXISTS `peng_pinjamans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peng_pinjamans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_bukti` varchar(50) NOT NULL,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `kode_gudang` varchar(50) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `nilai_pinjaman` decimal(15,2) NOT NULL DEFAULT 0.00,
  `jangka_waktu` int(11) NOT NULL COMMENT 'Dalam bulan',
  `cicilan_per_bulan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keperluan_pinjaman` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_bukti` (`nomor_bukti`),
  KEY `fk_peng_pinjamans_karyawan` (`karyawan_id`),
  CONSTRAINT `fk_peng_pinjamans_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peng_pinjamans`
--

LOCK TABLES `peng_pinjamans` WRITE;
/*!40000 ALTER TABLE `peng_pinjamans` DISABLE KEYS */;
/*!40000 ALTER TABLE `peng_pinjamans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_bill_pay_items`
--

DROP TABLE IF EXISTS `po_bill_pay_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `po_bill_pay_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `po_bill_pay_id` bigint(20) unsigned NOT NULL,
  `metode_bayar_id` bigint(20) unsigned DEFAULT NULL,
  `coa_debit_id` bigint(20) unsigned DEFAULT NULL,
  `coa_kredit_id` bigint(20) unsigned DEFAULT NULL,
  `nominal_pembayaran` decimal(18,2) DEFAULT 0.00,
  `mata_uang` varchar(50) DEFAULT 'IDR',
  `nama_bank` varchar(255) DEFAULT NULL,
  `no_rekening` varchar(255) DEFAULT NULL,
  `atas_nama` varchar(255) DEFAULT NULL,
  `memo` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_bill_pay_items`
--

LOCK TABLES `po_bill_pay_items` WRITE;
/*!40000 ALTER TABLE `po_bill_pay_items` DISABLE KEYS */;
INSERT INTO `po_bill_pay_items` VALUES (1,1,1,1,1,222000.00,'IDR','MANDIRI','12345678','Farhan','pelunasan','2026-06-05 00:43:12','2026-06-05 00:43:12');
/*!40000 ALTER TABLE `po_bill_pay_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_bill_pays`
--

DROP TABLE IF EXISTS `po_bill_pays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `po_bill_pays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_pembayaran` varchar(255) DEFAULT NULL,
  `po_billing_id` bigint(20) unsigned NOT NULL,
  `karyawan_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `periode` varchar(255) DEFAULT NULL,
  `total_tagihan` decimal(18,2) DEFAULT 0.00,
  `total_pembayaran` decimal(18,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pembayaran` (`kode_pembayaran`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_bill_pays`
--

LOCK TABLES `po_bill_pays` WRITE;
/*!40000 ALTER TABLE `po_bill_pays` DISABLE KEYS */;
INSERT INTO `po_bill_pays` VALUES (1,'PAY-20260605-001',3,NULL,'2026-06-05','UGRMS','2026',222000.00,222000.00,NULL,'2026-06-05 00:43:12','2026-06-05 00:43:12');
/*!40000 ALTER TABLE `po_bill_pays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_billing_items`
--

DROP TABLE IF EXISTS `po_billing_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `po_billing_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `po_billing_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `qty` decimal(18,2) DEFAULT 0.00,
  `harga` decimal(18,2) DEFAULT 0.00,
  `diskon` decimal(5,2) DEFAULT 0.00,
  `subtotal` decimal(18,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_po_billing_items_billing` (`po_billing_id`),
  CONSTRAINT `fk_po_billing_items_billing` FOREIGN KEY (`po_billing_id`) REFERENCES `po_billings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_billing_items`
--

LOCK TABLES `po_billing_items` WRITE;
/*!40000 ALTER TABLE `po_billing_items` DISABLE KEYS */;
INSERT INTO `po_billing_items` VALUES (2,3,NULL,'KERTAS FOIL SILVER GSM 60 GR 80 X 2000 M',2.00,100000.00,0.00,200000.00,'2026-05-27 04:37:37','2026-05-27 04:37:37'),(3,4,NULL,'KARDUS 485 X 268 X 335 MM',10.00,0.00,0.00,0.00,'2026-06-10 09:05:04','2026-06-10 09:05:04'),(4,4,NULL,'KERTAS FOIL SILVER GSM 60 GR 80 X 2000 M',12.00,0.00,0.00,0.00,'2026-06-10 09:05:04','2026-06-10 09:05:04');
/*!40000 ALTER TABLE `po_billing_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_billings`
--

DROP TABLE IF EXISTS `po_billings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `po_billings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_tagihan` varchar(255) DEFAULT NULL,
  `penerimaan_barang_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `karyawan_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_vendor` varchar(255) DEFAULT NULL,
  `periode` year(4) DEFAULT NULL,
  `tanggal_transaksi` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `total_barang` decimal(18,2) DEFAULT 0.00,
  `ppn` decimal(18,2) DEFAULT 0.00,
  `ongkir` decimal(18,2) DEFAULT 0.00,
  `dp` decimal(18,2) DEFAULT 0.00,
  `grand_total` decimal(18,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_tagihan` (`kode_tagihan`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_billings`
--

LOCK TABLES `po_billings` WRITE;
/*!40000 ALTER TABLE `po_billings` DISABLE KEYS */;
INSERT INTO `po_billings` VALUES (3,'BILL-20260527-001',17,26,1,NULL,'1234567890',2026,'2026-05-27','2026-07-15','wewewewew',200000.00,22000.00,500000.00,0.00,722000.00,'2026-05-27 04:37:37','2026-10-27 02:10:21'),(4,'BILL-20260610-002',18,27,1,1,'1234567890',2026,'2026-06-10','2026-06-10','restock material',20.00,11.00,25000.00,20000.00,50000.00,'2026-06-10 09:05:04','2026-06-10 09:05:04');
/*!40000 ALTER TABLE `po_billings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pot_tunjangans`
--

DROP TABLE IF EXISTS `pot_tunjangans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pot_tunjangans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `karyawan_id` bigint(20) unsigned NOT NULL,
  `periode_payroll` varchar(30) NOT NULL,
  `potongan_jabatan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `potongan_kompetensi` decimal(18,2) NOT NULL DEFAULT 0.00,
  `potongan_intensif` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_potongan` decimal(18,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pot_tunjangans_karyawan_id_foreign` (`karyawan_id`),
  CONSTRAINT `pot_tunjangans_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pot_tunjangans`
--

LOCK TABLES `pot_tunjangans` WRITE;
/*!40000 ALTER TABLE `pot_tunjangans` DISABLE KEYS */;
/*!40000 ALTER TABLE `pot_tunjangans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pracetak_processes`
--

DROP TABLE IF EXISTS `pracetak_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pracetak_processes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `spk_id` bigint(20) unsigned NOT NULL,
  `qty` int(11) NOT NULL,
  `status` enum('plat_making','selesai') NOT NULL DEFAULT 'plat_making',
  `ket_spk` enum('reguler','subcount') DEFAULT 'reguler',
  `nama_plat` varchar(255) NOT NULL,
  `hasil_baik` int(11) DEFAULT NULL,
  `hasil_rusak` int(11) DEFAULT NULL,
  `tgl_entry` date DEFAULT NULL,
  `operator_id` bigint(20) unsigned DEFAULT NULL,
  `mesin_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pracetak_spk` (`spk_id`),
  KEY `fk_pracetak_operator` (`operator_id`),
  KEY `fk_pracetak_mesin` (`mesin_id`),
  CONSTRAINT `fk_pracetak_mesin` FOREIGN KEY (`mesin_id`) REFERENCES `mesin_pra_cetaks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pracetak_operator` FOREIGN KEY (`operator_id`) REFERENCES `operator_pracetak` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pracetak_spk` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pracetak_processes`
--

LOCK TABLES `pracetak_processes` WRITE;
/*!40000 ALTER TABLE `pracetak_processes` DISABLE KEYS */;
INSERT INTO `pracetak_processes` VALUES (6,12,4,'selesai','reguler','PLAT SERTIFIKAT SISWA',5,0,'2026-06-18',1,2,'2026-06-17 20:02:01','2026-06-17 20:02:38'),(7,13,3,'selesai','reguler','PLAT MARLBOO LONG',3,0,'2026-06-18',1,2,'2026-06-18 00:59:36','2026-06-18 00:59:58'),(11,26,185,'selesai','reguler','PLAT',120000,100000,'2026-10-20',1,2,'2026-10-20 01:00:00','2026-10-20 01:01:24'),(12,27,50000,'plat_making','reguler','PLAT DAFI SKT 16',NULL,NULL,NULL,NULL,NULL,'2026-10-20 07:20:55','2026-10-20 07:20:55'),(13,27,13,'selesai','reguler','PLAT A',1,0,'2026-10-23',1,2,'2026-10-22 23:43:53','2026-10-22 23:45:06');
/*!40000 ALTER TABLE `pracetak_processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `printings`
--

DROP TABLE IF EXISTS `printings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `printings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `spk_id` bigint(20) unsigned NOT NULL,
  `mesin_id` bigint(20) unsigned NOT NULL,
  `operator_id` bigint(20) unsigned NOT NULL,
  `tanggal_entri` date NOT NULL,
  `proses_printing` enum('color-printing','cold-foil','spot-uv','furnish-uv','drip-off') NOT NULL,
  `tahap_printing` enum('potong','proses_cetak','proses_cetak_2') NOT NULL,
  `hasil_baik` int(11) NOT NULL DEFAULT 0,
  `hasil_rusak` int(11) NOT NULL DEFAULT 0,
  `semi_waste` int(11) NOT NULL DEFAULT 0,
  `error_production_id` bigint(20) unsigned DEFAULT NULL,
  `note_waste` enum('CETAK_LUNTUR','CETAK_BOCOR_BANJIR') DEFAULT NULL,
  `keterangan_spk` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_printing_spk` (`spk_id`),
  KEY `fk_printing_mesin` (`mesin_id`),
  KEY `fk_printing_operator` (`operator_id`),
  KEY `fk_printings_error_production` (`error_production_id`),
  CONSTRAINT `fk_printing_mesin` FOREIGN KEY (`mesin_id`) REFERENCES `master_printings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_printing_operator` FOREIGN KEY (`operator_id`) REFERENCES `master_operators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_printing_spk` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_printings_error_production` FOREIGN KEY (`error_production_id`) REFERENCES `error_productions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `printings`
--

LOCK TABLES `printings` WRITE;
/*!40000 ALTER TABLE `printings` DISABLE KEYS */;
INSERT INTO `printings` VALUES (5,14,1,1,'2026-07-03','cold-foil','proses_cetak',8,0,0,NULL,NULL,'REGULER','2026-06-18 01:53:30','2026-06-18 01:53:30'),(8,27,2,1,'2026-10-23','color-printing','potong',5,1,1,NULL,NULL,'BELUM DI INPUT','2026-10-22 23:48:01','2026-10-22 23:48:01');
/*!40000 ALTER TABLE `printings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `qty_pr` int(11) DEFAULT NULL,
  `qty_po` int(11) DEFAULT NULL,
  `qty_konversi` int(11) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT 0.00,
  `discount` decimal(5,2) DEFAULT 0.00,
  `total` decimal(15,2) DEFAULT 0.00,
  `satuan` varchar(255) DEFAULT NULL,
  `eta` date DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `qty_received` decimal(18,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_item_id_foreign` (`item_id`),
  KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  CONSTRAINT `purchase_order_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `master_items` (`id`),
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
INSERT INTO `purchase_order_items` VALUES (42,19,1,50,50,50,10000.00,0.00,500000.00,'SHEET','2026-04-30','-','2026-04-30 02:14:12','2026-07-05 20:17:16',50.00),(43,19,2,100,100,100,10000.00,0.00,1000000.00,'LEMBAR','2026-04-30','-','2026-04-30 02:14:12','2026-07-05 20:17:16',98.00),(44,19,3,10,10,10,25000.00,0.00,250000.00,'ROLL','2026-04-30','-','2026-04-30 02:14:12','2026-07-05 20:17:16',9.00),(45,19,5,50,5,5,50000.00,0.00,250000.00,'SHEET','2026-04-30','-','2026-04-30 02:14:12','2026-07-05 20:17:16',3.00),(46,19,4,50,50,50,50000.00,0.00,2500000.00,'SHEET','2026-04-30','-','2026-04-30 02:14:12','2026-07-05 20:17:16',50.00),(47,20,4,25,10,10,50000.00,0.00,500000.00,'SHEET','2026-05-03','MATERIAL STOCK','2026-05-02 22:11:00','2026-05-02 22:11:00',0.00),(48,20,3,1,1,1,750000.00,0.00,750000.00,'ROLL','2026-05-03','MATERIAL STOCK','2026-05-02 22:11:00','2026-05-02 22:11:00',0.00),(49,21,10,100,100,100,5000.00,0.00,500000.00,'TUB','2026-05-09','BELI CLEANFIX 100 TUB','2026-05-09 08:28:25','2026-05-09 08:28:25',0.00),(50,21,6,100,100,100,10000.00,0.00,1000000.00,'LITER','2026-05-09','BELI ALKOHOL IPA 100 TUB','2026-05-09 08:28:25','2026-05-09 08:28:25',0.00),(51,21,8,100,100,100,10000.00,0.00,1000000.00,'LEMBAR','2026-05-09','-','2026-05-09 08:28:25','2026-05-09 08:28:25',0.00),(52,21,9,100,100,100,25000.00,0.00,2500000.00,'LITER','2026-05-09','-','2026-05-09 08:28:25','2026-05-09 08:28:25',0.00),(53,22,1,100000,100000,100000,15000.00,10.00,1350000000.00,'SHEET','2026-05-11','MATERIAL PRODUCTION KERTAS','2026-05-10 20:53:37','2026-05-10 20:53:37',0.00),(54,23,11,10000,10000,10000,2000.00,0.00,20000000.00,'LEMBAR','2026-05-26','BLI KERTAS','2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(55,23,12,10,10,10,15000.00,0.00,150000.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(56,23,13,10,10,10,15000.00,0.00,150000.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(57,23,14,100,1,1,15000.00,0.00,15000.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(58,23,17,100,100,100,10000.00,0.00,1000000.00,'KALENG','2026-05-26',NULL,'2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(59,23,18,100,100,100,5000.00,0.00,500000.00,'LITER','2026-05-27','CLEANER 190, ECOWASH','2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(60,23,19,100,100,100,1000.00,0.00,100000.00,'LITER','2026-05-26','FOUNTAIN','2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(61,23,6,100,100,100,1000.00,0.00,100000.00,'LITER','2026-05-26',NULL,'2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(62,23,20,10,10,10,1000.00,0.00,10000.00,'LITER','2026-05-26',NULL,'2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(63,23,21,1000,1000,1000,1000.00,0.00,1000000.00,'PIECES','2026-05-26',NULL,'2026-05-25 20:31:13','2026-05-25 20:31:13',0.00),(64,24,15,10,10,10,1000.00,0.00,10000.00,'KILOGRAM','2026-05-19',NULL,'2026-05-25 21:05:31','2026-05-25 21:05:31',0.00),(65,25,16,100,10,10,1000.00,0.00,10000.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 21:26:40','2026-05-25 21:26:40',0.00),(66,26,7,2,2,2,100000.00,0.00,200000.00,'KILOGRAM','2026-05-30','KULAKAN','2026-05-27 01:46:28','2026-05-27 01:46:28',0.00),(67,27,21,10,0,0,0.00,0.00,0.00,'PIECES',NULL,NULL,'2026-06-10 08:54:14','2026-06-10 08:54:14',0.00),(68,27,7,20,0,0,0.00,0.00,0.00,'KILOGRAM',NULL,NULL,'2026-06-10 08:54:14','2026-06-10 08:54:14',0.00),(69,28,22,40000,40000,40000,500.00,NULL,20000000.00,'METER','2026-07-02','restock kertas roll ivory','2026-06-17 00:05:57','2026-06-17 00:05:57',0.00),(70,30,17,1,1,1,40000.00,0.00,40000.00,'KALENG','2026-06-18',NULL,'2026-06-24 01:12:54','2026-06-24 01:12:54',0.00),(71,31,4,1000,1000,1000,5000.00,0.00,5000000.00,'SHEET','2026-07-03','kulakan','2026-06-26 04:33:15','2026-06-26 04:33:15',0.00),(72,32,5,1000,1000,1000,24998.00,0.00,24998000.00,'SHEET','2026-06-26','PLAT CETAK RESTOCK','2026-06-26 05:05:17','2026-06-26 05:06:16',1000.00),(74,35,10,5,5,5,5000.00,0.00,25000.00,'TUB','2026-07-21',NULL,'2026-07-05 21:12:05','2026-07-05 21:12:05',0.00),(75,36,11,10,10,10,65000.00,0.00,650000.00,'LEMBAR','2026-07-01',NULL,'2026-07-06 01:55:39','2026-07-06 01:57:37',10.00),(76,37,21,10,10,10,5000.00,0.00,50000.00,'PIECES','2026-07-20',NULL,'2026-07-07 16:28:23','2026-07-08 00:07:48',10.00),(86,40,6,1,1,1,5000.00,0.00,5000.00,'LITER','2026-10-24',NULL,'2026-10-20 07:13:32','2026-10-20 07:58:58',1.00),(87,40,10,1,1,1,50000.00,0.00,50000.00,'TUB','2026-10-24',NULL,'2026-10-20 07:13:32','2026-10-20 07:58:58',1.00);
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_po` varchar(255) NOT NULL,
  `po_type` enum('reguler','subcount') DEFAULT 'reguler',
  `purchase_request_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `po_date` date NOT NULL,
  `eta` date DEFAULT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'IDR',
  `ppn` decimal(5,2) NOT NULL DEFAULT 0.00,
  `ongkir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('Open','Partial','Completed') DEFAULT 'Open',
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_no_po_unique` (`no_po`),
  KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  KEY `purchase_orders_purchase_request_id_foreign` (`purchase_request_id`),
  CONSTRAINT `purchase_orders_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`),
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `master_suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
INSERT INTO `purchase_orders` VALUES (19,'PO-UGRMS-20260430-001','subcount',14,1,'2026-04-30','2026-04-30','IDR',10.00,10000.00,2500000.00,'2026-04-30 02:14:12','2026-04-30 02:14:12','Open'),(20,'PO-UGRMS-20260503-001','reguler',15,1,'2026-05-03','2026-05-03','IDR',10.00,10000.00,500000.00,'2026-05-02 22:11:00','2026-05-02 22:11:00','Open'),(21,'PO-UGRMS-20260509-001','reguler',16,1,'2026-05-09','2026-05-09','IDR',10.00,10000.00,3500000.00,'2026-05-09 08:28:25','2026-05-09 08:28:25','Open'),(22,'PO-UGRMS-20260511-001','reguler',17,1,'2026-05-11','2026-05-11','IDR',10.00,10000.00,50000.00,'2026-05-10 20:53:37','2026-05-10 20:53:37','Open'),(23,'PO-UGRMS-20260526-001','reguler',19,1,'2026-05-26','2026-05-26','IDR',11.00,25000.00,9996.00,'2026-05-25 20:31:13','2026-05-25 20:31:13','Open'),(24,'PO-UGRMS-20260526-002','reguler',20,1,'2026-05-26','2026-05-26','IDR',10.00,10000.00,7002.00,'2026-05-25 21:05:31','2026-05-25 21:05:31','Open'),(25,'PO-UGRMS-20260526-003','reguler',21,1,'2026-05-26','2026-05-26','IDR',11.00,9998.00,1000.00,'2026-05-25 21:26:40','2026-05-25 21:26:40','Open'),(26,'PO-UGRMS-20260527-001','reguler',22,1,'2026-05-27','2026-05-27','IDR',11.00,10000.00,49998.00,'2026-05-27 01:46:28','2026-05-27 01:46:28','Open'),(27,'PO-UGRMS-20260610-001','reguler',23,1,'2026-06-10','2026-06-10','IDR',11.00,24998.00,0.00,'2026-06-10 08:54:14','2026-06-10 08:54:14','Open'),(28,'PO-UGRMS-20260617-001','reguler',24,1,'2026-06-17','2026-06-17','IDR',5.00,9999.00,0.00,'2026-06-17 00:05:57','2026-06-17 00:05:57','Open'),(29,'PO-UGRMS-20260624-001','reguler',25,1,'2026-06-24',NULL,'IDR',0.00,0.00,0.00,'2026-06-24 01:07:34','2026-06-24 01:07:34','Open'),(30,'PO-UGRMS-20260624-002','reguler',25,1,'2026-06-24',NULL,'IDR',0.00,0.00,0.00,'2026-06-24 01:12:54','2026-06-24 01:12:54','Open'),(31,'PO-UGRMS-20260626-001','reguler',26,1,'2026-06-26',NULL,'IDR',0.00,0.00,0.00,'2026-06-26 04:33:15','2026-06-26 04:33:15','Open'),(32,'PO-UGRMS-20260626-002','subcount',27,1,'2026-06-26','2026-07-03','IDR',0.00,0.00,0.00,'2026-06-26 05:05:17','2026-06-26 05:05:17','Open'),(33,'PO-UGRMS-20260706-001','reguler',28,1,'2026-07-06',NULL,'IDR',0.00,0.00,0.00,'2026-07-05 21:01:45','2026-07-05 21:01:45','Open'),(34,'PO-UGRMS-20260713-001','reguler',28,1,'2026-07-13','2026-07-07','IDR',0.00,0.00,0.00,'2026-07-05 21:08:12','2026-07-05 21:08:12','Open'),(35,'PO-UGRMS-20260725-001','reguler',28,1,'2026-07-25',NULL,'IDR',0.00,0.00,0.00,'2026-07-05 21:12:05','2026-07-05 21:12:05','Open'),(36,'PO-UGRMS-20260714-001','reguler',30,1,'2026-07-14','2026-07-08','IDR',0.00,0.00,0.00,'2026-07-06 01:55:39','2026-07-06 01:55:39','Open'),(37,'PO-UGRMS-20260708-001','reguler',31,1,'2026-07-08','2026-07-17','IDR',0.00,0.00,0.00,'2026-07-07 16:28:23','2026-07-07 16:28:23','Open'),(40,'PO-UGRMS-20261020-001','reguler',33,1,'2026-10-20','2026-10-24','IDR',0.00,100000.00,5000000.00,'2026-10-20 07:10:46','2026-10-20 07:58:39','Completed');
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_request_items`
--

DROP TABLE IF EXISTS `purchase_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_request_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` bigint(20) unsigned NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL,
  `qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `satuan` varchar(255) DEFAULT NULL,
  `eta` date DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  KEY `purchase_request_items_item_id_foreign` (`item_id`),
  CONSTRAINT `purchase_request_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `master_items` (`id`),
  CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_request_items`
--

LOCK TABLES `purchase_request_items` WRITE;
/*!40000 ALTER TABLE `purchase_request_items` DISABLE KEYS */;
INSERT INTO `purchase_request_items` VALUES (25,14,1,50.00,'SHEET','2026-04-30','-','2026-04-30 02:11:39','2026-04-30 02:11:39'),(26,14,2,100.00,'LEMBAR','2026-04-30','-','2026-04-30 02:11:39','2026-04-30 02:11:39'),(27,14,3,10.00,'ROLL','2026-04-30','-','2026-04-30 02:11:39','2026-04-30 02:11:39'),(28,14,5,50.00,'SHEET','2026-04-30','-','2026-04-30 02:11:39','2026-04-30 02:11:39'),(29,14,4,50.00,'SHEET','2026-04-30','-','2026-04-30 02:11:39','2026-04-30 02:11:39'),(30,15,4,25.00,'SHEET','2026-05-03','MATERIAL STOCK','2026-05-02 22:07:46','2026-05-02 22:07:46'),(31,15,3,1.00,'ROLL','2026-05-03','MATERIAL STOCK','2026-05-02 22:07:46','2026-05-02 22:07:46'),(32,16,10,100.00,'TUB','2026-05-09','BELI CLEANFIX 100 TUB','2026-05-09 08:20:14','2026-05-09 08:20:14'),(33,16,6,100.00,'LITER','2026-05-09','BELI ALKOHOL IPA 100 TUB','2026-05-09 08:20:14','2026-05-09 08:20:14'),(34,16,8,100.00,'LEMBAR','2026-05-09','-','2026-05-09 08:20:14','2026-05-09 08:20:14'),(35,16,9,100.00,'LITER','2026-05-09','-','2026-05-09 08:20:14','2026-05-09 08:20:14'),(36,17,1,100000.00,'SHEET',NULL,'MATERIAL PROD','2026-05-10 04:17:16','2026-05-10 04:17:16'),(37,18,5,1.00,'SHEET','2026-05-20',NULL,'2026-05-20 03:53:30','2026-05-20 03:53:30'),(38,18,8,1.00,'LEMBAR','2026-05-20',NULL,'2026-05-20 03:53:30','2026-05-20 03:53:30'),(39,19,11,10000.00,'LEMBAR','2026-05-26','BLI KERTAS','2026-05-25 20:15:37','2026-05-25 20:15:37'),(40,19,12,10.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 20:15:37','2026-05-25 20:15:37'),(41,19,13,10.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 20:15:37','2026-05-25 20:15:37'),(42,19,14,100.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 20:15:37','2026-05-25 20:15:37'),(43,19,17,100.00,'KALENG','2026-05-26',NULL,'2026-05-25 20:15:37','2026-05-25 20:15:37'),(44,19,18,100.00,'LITER','2026-05-27','CLEANER 190, ECOWASH','2026-05-25 20:15:37','2026-05-25 20:15:37'),(45,19,19,100.00,'LITER','2026-05-26','FOUNTAIN','2026-05-25 20:15:37','2026-05-25 20:15:37'),(46,19,6,100.00,'LITER','2026-05-26',NULL,'2026-05-25 20:15:37','2026-05-25 20:15:37'),(47,19,20,10.00,'LITER','2026-05-26',NULL,'2026-05-25 20:15:37','2026-05-25 20:15:37'),(48,19,21,1000.00,'PIECES','2026-05-26',NULL,'2026-05-25 20:15:37','2026-05-25 20:15:37'),(49,20,15,10.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 21:03:11','2026-05-25 21:03:11'),(50,21,16,100.00,'KILOGRAM','2026-05-26',NULL,'2026-05-25 21:07:42','2026-05-25 21:07:42'),(51,22,7,2.00,'KILOGRAM','2026-05-27','KULAKAN','2026-05-27 01:36:23','2026-05-27 01:36:23'),(52,23,21,10.00,'PIECES',NULL,NULL,'2026-06-10 08:50:40','2026-06-10 08:50:40'),(53,23,7,20.00,'KILOGRAM',NULL,NULL,'2026-06-10 08:50:40','2026-06-10 08:50:40'),(54,24,22,40000.00,'METER','2026-06-17','restock kertas roll ivory','2026-06-17 00:02:20','2026-06-17 00:02:20'),(55,25,17,1.00,'KALENG','2026-06-18',NULL,'2026-06-24 01:04:59','2026-06-24 01:04:59'),(56,26,4,1000.00,'SHEET','2026-07-03','kulakan','2026-06-26 04:31:53','2026-06-26 04:31:53'),(57,27,5,1000.00,'SHEET','2026-06-26','PLAT CETAK RESTOCK','2026-06-26 05:04:02','2026-06-26 05:04:02'),(58,28,10,5.00,'TUB','2026-07-21',NULL,'2026-07-05 20:44:11','2026-07-05 20:44:11'),(59,29,12,1.00,'KILOGRAM','2026-07-01',NULL,'2026-07-06 01:39:26','2026-07-06 01:39:26'),(60,30,11,10.00,'LEMBAR','2026-07-01',NULL,'2026-07-06 01:41:27','2026-07-06 01:41:27'),(61,31,21,10.00,'PIECES','2026-07-12',NULL,'2026-07-07 16:25:01','2026-07-07 16:25:01'),(62,32,5,1000.00,'SHEET','2026-07-16',NULL,'2026-07-13 03:06:35','2026-07-13 03:06:35'),(63,32,10,100.00,'TUB','2026-07-17',NULL,'2026-07-13 03:06:35','2026-07-13 03:06:35'),(64,32,22,1000.00,'METER','2026-07-15',NULL,'2026-07-13 03:06:35','2026-07-13 03:06:35'),(65,32,21,100.00,'PIECES','2026-07-13',NULL,'2026-07-13 03:06:35','2026-07-13 03:06:35'),(66,33,6,1.00,'LITER','2026-10-24',NULL,'2026-10-20 07:03:16','2026-10-20 07:03:16'),(67,33,10,1.00,'TUB','2026-10-24',NULL,'2026-10-20 07:03:16','2026-10-20 07:03:16');
/*!40000 ALTER TABLE `purchase_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_pr` varchar(255) NOT NULL,
  `departemen_id` bigint(20) unsigned NOT NULL,
  `tanggal_pr` date NOT NULL,
  `status` enum('otorisasi','deotorisasi') NOT NULL DEFAULT 'deotorisasi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_requests_nomor_pr_unique` (`nomor_pr`),
  KEY `purchase_requests_departemen_id_foreign` (`departemen_id`),
  CONSTRAINT `purchase_requests_departemen_id_foreign` FOREIGN KEY (`departemen_id`) REFERENCES `master_departemens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_requests`
--

LOCK TABLES `purchase_requests` WRITE;
/*!40000 ALTER TABLE `purchase_requests` DISABLE KEYS */;
INSERT INTO `purchase_requests` VALUES (14,'PR-20260430-001',5,'2026-04-30','otorisasi','2026-04-30 02:11:39','2026-04-30 02:11:52'),(15,'PR-20260503-001',5,'2026-05-03','otorisasi','2026-05-02 22:07:46','2026-05-02 22:08:21'),(16,'PR-20260509-001',3,'2026-05-09','otorisasi','2026-05-09 08:20:14','2026-05-09 08:22:56'),(17,'PR-20260510-001',2,'2026-05-10','otorisasi','2026-05-10 04:17:16','2026-05-10 04:17:32'),(18,'PR-20260520-001',5,'2026-05-20','otorisasi','2026-05-20 03:53:30','2026-05-20 03:54:06'),(19,'PR-20260526-001',2,'2026-05-26','otorisasi','2026-05-25 20:15:37','2026-05-25 20:15:59'),(20,'PR-20260526-002',2,'2026-05-26','otorisasi','2026-05-25 21:03:11','2026-05-25 21:04:26'),(21,'PR-20260526-003',2,'2026-05-26','otorisasi','2026-05-25 21:07:42','2026-05-25 21:07:58'),(22,'PR-20260527-001',5,'2026-05-27','otorisasi','2026-05-27 01:36:23','2026-05-27 01:38:49'),(23,'PR-20260610-001',2,'2026-06-10','otorisasi','2026-06-10 08:50:40','2026-06-10 08:52:09'),(24,'PR-20260617-001',2,'2026-06-17','otorisasi','2026-06-17 00:02:20','2026-06-17 00:04:10'),(25,'PR-20260624-001',2,'2026-06-24','otorisasi','2026-06-24 01:04:59','2026-06-24 01:04:59'),(26,'PR-20260626-001',2,'2026-06-26','otorisasi','2026-06-26 04:31:53','2026-06-26 04:32:04'),(27,'PR-20260626-002',2,'2026-06-26','otorisasi','2026-06-26 05:04:02','2026-06-26 05:04:17'),(28,'PR-20260708-001',5,'2026-07-08','otorisasi','2026-07-05 20:44:11','2026-07-06 01:36:52'),(29,'PR-20260715-001',1,'2026-07-15','otorisasi','2026-07-06 01:39:26','2026-07-06 01:54:52'),(30,'PR-20260715-002',4,'2026-07-15','otorisasi','2026-07-06 01:41:27','2026-07-06 01:54:14'),(31,'PR-20260709-001',1,'2026-07-09','otorisasi','2026-07-07 16:25:01','2026-07-07 16:27:35'),(32,'PR-20260714-001',2,'2026-07-14','otorisasi','2026-07-13 03:06:35','2026-07-13 03:07:47'),(33,'PR-20261020-001',2,'2026-10-20','otorisasi','2026-10-20 07:03:16','2026-10-20 07:08:49');
/*!40000 ALTER TABLE `purchase_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_items`
--

DROP TABLE IF EXISTS `sales_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sales_order_id` bigint(20) unsigned NOT NULL,
  `master_item_id` bigint(20) unsigned DEFAULT NULL,
  `qty` decimal(18,2) NOT NULL DEFAULT 0.00,
  `harga` decimal(18,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_so_items` (`sales_order_id`),
  KEY `fk_sales_order_item_material` (`master_item_id`),
  CONSTRAINT `fk_sales_order_item_material` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_so_items` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_items`
--

LOCK TABLES `sales_order_items` WRITE;
/*!40000 ALTER TABLE `sales_order_items` DISABLE KEYS */;
INSERT INTO `sales_order_items` VALUES (1,33,6,12.00,80000.00,0.00,'2026-07-16 09:00:53','2026-07-16 09:00:53'),(2,33,10,5.00,75000.00,0.00,'2026-07-16 09:00:53','2026-07-16 09:00:53'),(3,34,18,5.00,780000.00,0.00,'2026-07-17 05:52:11','2026-07-17 05:52:11'),(4,35,10,2.00,20000.00,0.00,'2026-07-18 06:50:56','2026-07-18 06:50:56'),(5,35,8,5.00,25000.00,0.00,'2026-07-18 06:50:56','2026-07-18 06:50:56'),(6,36,1,1000.00,5000.00,0.00,'2026-07-18 08:53:57','2026-07-18 08:53:57'),(7,37,21,10.00,56000.00,0.00,'2026-07-19 03:06:19','2026-07-19 03:06:19'),(8,38,20,5.00,67000.00,0.00,'2026-07-19 06:36:36','2026-07-19 06:36:36'),(9,39,17,10.00,249998.00,0.00,'2026-09-08 23:55:41','2026-09-08 23:55:41'),(10,39,18,5.00,50000.00,0.00,'2026-09-08 23:55:41','2026-09-08 23:55:41'),(11,40,1,977.00,24997.00,0.00,'2026-09-09 00:17:45','2026-09-09 00:17:45'),(21,51,NULL,50000.00,470.00,0.00,'2026-10-15 02:33:53','2026-10-15 02:33:53'),(22,51,NULL,50000.00,350.00,0.00,'2026-10-15 02:33:53','2026-10-15 02:33:53'),(23,52,NULL,50000.00,5000.00,0.00,'2026-10-17 23:00:14','2026-10-17 23:00:14');
/*!40000 ALTER TABLE `sales_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_orders`
--

DROP TABLE IF EXISTS `sales_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `itemable_type` varchar(255) DEFAULT NULL,
  `itemable_id` bigint(20) unsigned DEFAULT NULL,
  `material_item_id` bigint(20) DEFAULT NULL,
  `kode_material` varchar(100) DEFAULT NULL,
  `finish_good_item_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `no_sales_order` varchar(255) NOT NULL,
  `no_po_customer` varchar(255) DEFAULT NULL,
  `qty` decimal(15,2) DEFAULT NULL,
  `harga_pcs` decimal(15,2) DEFAULT NULL,
  `harga_kirim` decimal(15,2) DEFAULT NULL,
  `diskon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `mata_uang` varchar(50) DEFAULT NULL,
  `syarat_pembayaran` varchar(255) DEFAULT NULL,
  `tanggal_po` date DEFAULT NULL,
  `lead_time` varchar(100) DEFAULT '21 Hari Kerja',
  `klaim_kertas` enum('claim','not_claim') DEFAULT 'not_claim',
  `dipesan_via` enum('whatsapp','email','sosmed') DEFAULT NULL,
  `tipe_pesanan` varchar(255) DEFAULT NULL,
  `toleransi_pengiriman` decimal(5,2) DEFAULT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `komponen` varchar(100) DEFAULT NULL,
  `isi` varchar(50) DEFAULT NULL,
  `kertas` varchar(255) DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `finishing` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `catatan_colour_range` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `order_type` enum('SPK','MATERIAL') NOT NULL DEFAULT 'SPK',
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_sales_order` (`no_sales_order`),
  KEY `finish_good_item_id` (`finish_good_item_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`finish_good_item_id`) REFERENCES `finish_good_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_orders`
--

LOCK TABLES `sales_orders` WRITE;
/*!40000 ALTER TABLE `sales_orders` DISABLE KEYS */;
INSERT INTO `sales_orders` VALUES (21,'App\\Models\\FinishGoodItem',9,NULL,'JS-123',NULL,1,'0021/IK-10/062026','12345678',50.00,5000.00,20000.00,0.00,'rupiah','CASH','2026-06-18','21 Hari Kerja','claim','whatsapp','NEW',2.00,NULL,NULL,NULL,NULL,NULL,NULL,'-','-','2026-06-17 19:58:35','2026-06-17 19:58:35',NULL,'SPK'),(22,'App\\Models\\FinishGoodItem',13,NULL,'ET-001',NULL,1,'0022/IK-10/062026','12345678',20.00,500.00,20000.00,0.00,'rupiah','CASH','2026-06-19','21 Hari Kerja','not_claim','whatsapp','NEW',4.00,NULL,NULL,NULL,NULL,NULL,NULL,'-','-','2026-06-17 20:53:59','2026-06-17 20:53:59',NULL,'SPK'),(23,'App\\Models\\FinishGoodItem',14,NULL,'ET-345',NULL,1,'0023/IK-10/062026','12345678',20.00,500.00,20000.00,0.00,'rupiah','CASH','2026-06-18','21 Hari Kerja','claim','whatsapp','NEW',2.00,NULL,NULL,NULL,NULL,NULL,NULL,'-','-','2026-06-18 00:29:30','2026-06-18 00:29:30',NULL,'SPK'),(24,'App\\Models\\FinishGoodItem',15,NULL,'ET-456',NULL,1,'0024/IK-10/062026','12345678',20.00,500.00,20000.00,0.00,'rupiah','CASH','2026-06-18','21 Hari Kerja','not_claim','whatsapp','NEW',2.00,NULL,NULL,NULL,NULL,NULL,NULL,'-','-','2026-06-18 01:31:26','2026-06-18 01:31:26',NULL,'SPK'),(25,'App\\Models\\FinishGoodItem',16,NULL,'BP-123',NULL,1,'0025/IK-10/062026','12345678',99.00,70000.00,8000.00,0.00,'rupiah','CASH','2026-06-25','21 Hari Kerja','claim','whatsapp','NEW',9.00,NULL,NULL,NULL,NULL,NULL,NULL,'-','-','2026-06-26 05:25:26','2026-07-11 04:45:35',NULL,'SPK'),(26,'App\\Models\\FinishGoodItem',17,NULL,'ET-909',NULL,1,'0026/IK-10/072026','12345678',20.00,20000.00,10000.00,0.00,'rupiah','CASH','2026-07-11','21 Hari Kerja','claim','whatsapp','REPEAT',10.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-11 00:55:47','2026-07-11 00:55:47',NULL,'SPK'),(27,'App\\Models\\MasterItem',1,NULL,NULL,NULL,1,'0027/IK-10/072026',NULL,150.00,1000.00,10000.00,0.00,'rupiah','CASH','2026-07-16','21 Hari Kerja','claim','whatsapp',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-13 18:00:31','2026-07-18 06:49:51','2026-07-18 06:49:51','SPK'),(28,'App\\Models\\MasterItem',2,NULL,'KO2',NULL,1,'0028/IK-10/072026','12345678',100.00,1000.00,5000.00,0.00,'rupiah','CASH','2026-07-13','21 Hari Kerja','claim','whatsapp','NEW',5.00,NULL,NULL,NULL,NULL,NULL,NULL,'-','-','2026-07-13 19:13:23','2026-07-18 06:49:51','2026-07-18 06:49:51','SPK'),(29,'App\\Models\\MasterItem',17,NULL,'CH005',NULL,1,'0029/IK-10/072026','12345678',10.00,100000.00,20000.00,0.00,'rupiah','CASH','2026-07-16','21 Hari Kerja','not_claim','whatsapp','NEW',5.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-16 08:10:01','2026-07-18 06:49:51','2026-07-18 06:49:51','SPK'),(30,NULL,NULL,NULL,NULL,NULL,1,'0030/IK-10/072026','12345678',NULL,NULL,15000.00,0.00,'rupiah','CASH',NULL,'21 Hari Kerja','not_claim','whatsapp','NEW',10.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-16 08:49:58','2026-07-18 06:49:51','2026-07-18 06:49:51','SPK'),(31,NULL,NULL,NULL,NULL,NULL,1,'0031/IK-10/072026','12345678',NULL,NULL,15000.00,0.00,'rupiah','CASH','2026-07-16','21 Hari Kerja','not_claim','whatsapp','NEW',1.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-16 08:52:23','2026-07-18 06:49:51','2026-07-18 06:49:51','SPK'),(32,NULL,NULL,NULL,NULL,NULL,1,'0032/IK-10/072026','12345678',NULL,NULL,45000.00,0.00,'rupiah','selesai','2026-07-08','21 Hari Kerja','not_claim',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-16 08:57:08','2026-07-18 06:49:51','2026-07-18 06:49:51','SPK'),(33,NULL,NULL,NULL,NULL,NULL,1,'0033/IK-10/072026','12345678',NULL,NULL,45000.00,0.00,'rupiah','CASH','2026-07-21','21 Hari Kerja','not_claim','whatsapp','NEW',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-16 09:00:53','2026-09-09 19:44:42',NULL,'MATERIAL'),(34,NULL,NULL,NULL,NULL,NULL,1,'0034/IK-10/072026','12345678',NULL,NULL,70000.00,0.00,'rupiah','CASH','2026-07-08','21 Hari Kerja','not_claim',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 05:52:11','2026-09-09 19:44:42',NULL,'MATERIAL'),(35,NULL,NULL,NULL,NULL,NULL,1,'0035/IK-10/072026','12345678',NULL,NULL,5000.00,0.00,'rupiah','CASH','2026-07-15','21 Hari Kerja','not_claim','whatsapp','NEW',1.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-18 06:50:56','2026-09-09 19:44:42',NULL,'MATERIAL'),(36,NULL,NULL,NULL,NULL,NULL,1,'0036/IK-10/072026','12345678',NULL,NULL,500000.00,0.00,'rupiah','CASH','2026-07-16','21 Hari Kerja','claim','whatsapp','NEW',1.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-18 08:53:57','2026-09-09 19:44:42',NULL,'MATERIAL'),(37,NULL,NULL,NULL,NULL,NULL,1,'0037/IK-10/072026','12345678',NULL,NULL,50000.00,0.00,'rupiah','CASH','2026-07-15','21 Hari Kerja','not_claim','email',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-19 03:06:19','2026-09-09 19:44:42',NULL,'MATERIAL'),(38,NULL,NULL,NULL,NULL,NULL,1,'0038/IK-10/072026','12345678',NULL,NULL,50000.00,0.00,'rupiah','CASH','2026-07-15','21 Hari Kerja','not_claim','whatsapp','NEW',10.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-19 06:36:36','2026-09-09 19:44:42',NULL,'MATERIAL'),(39,NULL,NULL,NULL,NULL,NULL,1,'0039/IK-10/092026','12345678',NULL,NULL,50000.00,0.00,'rupiah','CASH','2026-09-09','21 Hari Kerja','not_claim','whatsapp','NEW',8.00,NULL,NULL,NULL,NULL,NULL,NULL,'Pembelian material mentah',NULL,'2026-09-08 23:55:41','2026-09-09 19:44:42',NULL,'MATERIAL'),(40,NULL,NULL,NULL,NULL,NULL,1,'0040/IK-10/092026','12345678',NULL,NULL,25000.00,0.00,'rupiah','CASH','2026-09-09','21 Hari Kerja','not_claim','whatsapp','NEW',10.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-09 00:17:45','2026-09-09 19:44:42',NULL,'MATERIAL'),(51,NULL,NULL,NULL,NULL,NULL,2,'OR-26-X-C005-001',NULL,100000.00,470.00,0.00,0.00,NULL,NULL,'2026-10-15','21 Hari Kerja','not_claim',NULL,NULL,NULL,'SKT','Etiket','20','Metalize','5','Spot UV, Emboss, Lem, Cutting',NULL,NULL,'2026-10-15 02:33:53','2026-10-15 02:33:53',NULL,'SPK'),(52,'App\\Models\\FinishGoodItem',4,NULL,NULL,NULL,1,'OR-26-X-	CS001-001',NULL,50000.00,5000.00,0.00,1000000.00,NULL,NULL,'2026-10-18','1 bulan','not_claim',NULL,NULL,NULL,'SKM','FILTER','26','Metalized','8','Spot UV, Cutting',NULL,NULL,'2026-10-17 23:00:14','2026-10-17 23:00:14',NULL,'SPK'),(53,'App\\Models\\FinishGoodItem',13,NULL,NULL,NULL,3,'OR-26-X-CS003-002',NULL,10000.00,5000.00,600000.00,0.00,'rupiah','30 Hari setelah invoice','2026-10-18','2 minggu','not_claim',NULL,'BARU',10.00,'SKM','ETIKET','20','KERTAS IVORY ROLLL','6','Spot UV, emboss, lem, cutting',NULL,NULL,'2026-10-18 01:48:47','2026-10-22 23:33:35',NULL,'SPK'),(54,'App\\Models\\FinishGoodItem',25,NULL,'FG-001-DAFI-2DB5',NULL,4,'OR-26-X-001-004','PO0001',1.00,324.00,500000.00,100000.00,'rupiah','30 Hari setelah invoice','2026-10-20','2','not_claim','email','baru',0.00,'SKT','ETIKET','16','METALIZE [ETIKET] 230 GSM [ETIKET]','4','embos,lem,cutting',NULL,NULL,'2026-10-20 07:00:57','2026-10-20 07:00:57',NULL,'SPK');
/*!40000 ALTER TABLE `sales_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcount_in_items`
--

DROP TABLE IF EXISTS `subcount_in_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcount_in_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subcount_in_id` bigint(20) unsigned NOT NULL,
  `subcount_out_id` bigint(20) unsigned NOT NULL,
  `item_hasil_id` bigint(20) unsigned NOT NULL,
  `qty_diterima` decimal(18,2) DEFAULT 0.00,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `item_asal_id` bigint(20) DEFAULT NULL,
  `qty_dikirim` decimal(18,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `subcount_in_id` (`subcount_in_id`),
  KEY `subcount_out_id` (`subcount_out_id`),
  KEY `item_hasil_id` (`item_hasil_id`),
  CONSTRAINT `subcount_in_items_ibfk_1` FOREIGN KEY (`subcount_in_id`) REFERENCES `subcount_ins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subcount_in_items_ibfk_2` FOREIGN KEY (`subcount_out_id`) REFERENCES `subcount_outs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subcount_in_items_ibfk_3` FOREIGN KEY (`item_hasil_id`) REFERENCES `master_items` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcount_in_items`
--

LOCK TABLES `subcount_in_items` WRITE;
/*!40000 ALTER TABLE `subcount_in_items` DISABLE KEYS */;
INSERT INTO `subcount_in_items` VALUES (2,2,3,2,940.00,'kulakan','2026-06-22 04:58:58','2026-06-22 04:58:58',11,940.00);
/*!40000 ALTER TABLE `subcount_in_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcount_ins`
--

DROP TABLE IF EXISTS `subcount_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcount_ins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_subcount` varchar(50) DEFAULT NULL,
  `tanggal_subcount` date NOT NULL,
  `surat_jalan_pengiriman` varchar(100) DEFAULT NULL,
  `admin_produksi` varchar(100) DEFAULT NULL,
  `supervisor` varchar(100) DEFAULT NULL,
  `admin_mainstore` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_jalan_pengiriman` (`surat_jalan_pengiriman`),
  UNIQUE KEY `no_subcount` (`no_subcount`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcount_ins`
--

LOCK TABLES `subcount_ins` WRITE;
/*!40000 ALTER TABLE `subcount_ins` DISABLE KEYS */;
INSERT INTO `subcount_ins` VALUES (2,'SUB-IN/06-2026/000001','2026-06-22','3242423432','FARHAN','FARHAN','FARHAN','erterterterter','2026-06-22 04:58:58','2026-06-22 04:58:58');
/*!40000 ALTER TABLE `subcount_ins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcount_out_items`
--

DROP TABLE IF EXISTS `subcount_out_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcount_out_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subcount_out_id` bigint(20) unsigned NOT NULL,
  `sumber_item` enum('spk','material') DEFAULT NULL,
  `spk_id` bigint(20) unsigned DEFAULT NULL,
  `item_id` bigint(20) unsigned DEFAULT NULL,
  `qty` decimal(18,2) DEFAULT 0.00,
  `satuan_id` bigint(20) unsigned DEFAULT NULL,
  `nama_produk` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subcount_out_id` (`subcount_out_id`),
  KEY `spk_id` (`spk_id`),
  KEY `item_id` (`item_id`),
  KEY `satuan_id` (`satuan_id`),
  CONSTRAINT `subcount_out_items_ibfk_1` FOREIGN KEY (`subcount_out_id`) REFERENCES `subcount_outs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subcount_out_items_ibfk_2` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`),
  CONSTRAINT `subcount_out_items_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `master_items` (`id`),
  CONSTRAINT `subcount_out_items_ibfk_4` FOREIGN KEY (`satuan_id`) REFERENCES `master_satuans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcount_out_items`
--

LOCK TABLES `subcount_out_items` WRITE;
/*!40000 ALTER TABLE `subcount_out_items` DISABLE KEYS */;
INSERT INTO `subcount_out_items` VALUES (5,3,'material',NULL,11,940.00,2,'KERTAS METALIZED 237 GR 79 X 109','ggergergergegegegeger','2026-06-21 17:01:34','2026-06-21 17:01:34');
/*!40000 ALTER TABLE `subcount_out_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcount_outs`
--

DROP TABLE IF EXISTS `subcount_outs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcount_outs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal_subcount` date NOT NULL,
  `surat_jalan_subcount` varchar(50) DEFAULT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `admin_produksi` varchar(255) DEFAULT NULL,
  `supervisor` varchar(255) DEFAULT NULL,
  `admin_mainstore` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_jalan_subcount` (`surat_jalan_subcount`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `subcount_outs_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `master_suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcount_outs`
--

LOCK TABLES `subcount_outs` WRITE;
/*!40000 ALTER TABLE `subcount_outs` DISABLE KEYS */;
INSERT INTO `subcount_outs` VALUES (3,'2026-06-22','SUB-OUT/06-2026/000001',1,'FARHAN','FARHAN','FARHAN','refwefewrwerwe','2026-06-21 17:01:34','2026-06-21 17:01:34');
/*!40000 ALTER TABLE `subcount_outs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat_jalan_items`
--

DROP TABLE IF EXISTS `surat_jalan_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surat_jalan_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `surat_jalan_id` bigint(20) unsigned NOT NULL,
  `sales_order_item_id` bigint(20) unsigned NOT NULL,
  `master_item_id` bigint(20) unsigned NOT NULL,
  `qty_kirim` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sji_surat_jalan` (`surat_jalan_id`),
  KEY `fk_sji_so_item` (`sales_order_item_id`),
  KEY `fk_sji_material` (`master_item_id`),
  CONSTRAINT `fk_sji_material` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sji_so_item` FOREIGN KEY (`sales_order_item_id`) REFERENCES `sales_order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sji_surat_jalan` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat_jalan_items`
--

LOCK TABLES `surat_jalan_items` WRITE;
/*!40000 ALTER TABLE `surat_jalan_items` DISABLE KEYS */;
INSERT INTO `surat_jalan_items` VALUES (1,10,1,6,0.00,'2026-07-17 06:22:14','2026-07-17 06:22:14'),(2,10,2,10,0.00,'2026-07-17 06:22:14','2026-07-17 06:22:14'),(3,10,1,6,12.00,'2026-07-17 06:22:14','2026-07-17 06:22:14'),(4,10,2,10,5.00,'2026-07-17 06:22:14','2026-07-17 06:22:14'),(12,16,4,10,2.00,'2026-07-18 07:30:44','2026-07-18 07:30:44'),(13,16,5,8,5.00,'2026-07-18 07:30:44','2026-07-18 07:30:44'),(14,16,4,10,2.00,'2026-07-18 07:30:44','2026-07-18 07:30:44'),(15,16,5,8,5.00,'2026-07-18 07:30:44','2026-07-18 07:30:44'),(16,18,6,1,500.00,'2026-07-18 09:14:34','2026-07-18 09:14:34'),(17,19,7,21,5.00,'2026-07-19 03:07:30','2026-07-19 03:07:30'),(18,20,7,21,5.00,'2026-07-19 03:08:58','2026-07-19 03:08:58'),(19,21,8,20,5.00,'2026-07-19 06:37:00','2026-07-19 06:37:00'),(20,22,9,17,10.00,'2026-09-09 00:00:22','2026-09-09 00:00:22'),(21,22,10,18,5.00,'2026-09-09 00:00:22','2026-09-09 00:00:22'),(22,22,9,17,10.00,'2026-09-09 00:00:22','2026-09-09 00:00:22'),(23,22,10,18,5.00,'2026-09-09 00:00:22','2026-09-09 00:00:22'),(24,23,11,1,977.00,'2026-09-09 00:20:16','2026-09-09 00:20:16');
/*!40000 ALTER TABLE `surat_jalan_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat_jalans`
--

DROP TABLE IF EXISTS `surat_jalans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surat_jalans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_surat_jalan` varchar(100) DEFAULT NULL,
  `sales_order_id` bigint(20) unsigned NOT NULL,
  `spk_id` bigint(20) unsigned DEFAULT NULL,
  `qty_pengiriman` bigint(20) NOT NULL DEFAULT 0,
  `tanggal_surat_jalan` date NOT NULL,
  `alamat_tujuan` text NOT NULL,
  `transportasi` varchar(255) NOT NULL,
  `no_polisi` varchar(100) NOT NULL,
  `driver` varchar(255) NOT NULL,
  `pengirim` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sj_sales_order` (`sales_order_id`),
  KEY `fk_sj_spk` (`spk_id`),
  CONSTRAINT `fk_sj_sales_order` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sj_spk` FOREIGN KEY (`spk_id`) REFERENCES `master_spk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat_jalans`
--

LOCK TABLES `surat_jalans` WRITE;
/*!40000 ALTER TABLE `surat_jalans` DISABLE KEYS */;
INSERT INTO `surat_jalans` VALUES (10,'SJ-20260717-001',33,NULL,0,'2026-07-17','wewewewe','wewew','ewew','ewewew','ewewe','-','2026-07-17 06:22:14','2026-07-17 06:22:14'),(15,'SJ-20260718-002',34,NULL,5,'2026-07-18','wewewe','wewewe','wewewew','wewewewe','wewewewe','-','2026-07-18 07:25:41','2026-07-18 07:25:41'),(16,'SJ-20260718-002',35,NULL,0,'2026-07-18','wewewe','wewewe','wewew','ewewewe','wewewewe','-ewewewe','2026-07-18 07:30:44','2026-07-18 07:30:44'),(17,'SJ-20260718-003',36,NULL,500,'2026-07-30','wewewe','wewewe','wewewe','wewewe','wewewewe','-','2026-07-18 08:54:39','2026-07-18 08:54:39'),(18,'SJ-20260718-004',36,NULL,500,'2026-07-23','wewewe','wewewee','wewewewe','wewewe','wewewewe','-','2026-07-18 09:14:34','2026-07-18 09:14:34'),(19,'SJ-20260719-001',37,NULL,5,'2026-07-19','wewewe','wewewew','ewewewew','wewewewe','wewewewew','-','2026-07-19 03:07:30','2026-07-19 03:07:30'),(20,'SJ-20260719-002',37,NULL,5,'2026-07-30','tyutyutyu','tyutyutyutyutyuyt','tyutyutyutyu','tyutyutyuyutuyt','tyutyutyutyutyuty','-','2026-07-19 03:08:58','2026-07-19 03:08:58'),(21,'SJ-20260719-003',38,NULL,5,'2026-07-19','wewewe','wewew','ewewewe','wewewe','wewewewewe','-','2026-07-19 06:37:00','2026-07-19 06:37:00'),(22,'SJ-20260909-001',39,NULL,0,'2026-09-09','Sidoarjo','Becak','000000','JUAN','ALEXA','-','2026-09-09 00:00:22','2026-09-09 00:00:22'),(23,'SJ-20260909-002',40,NULL,977,'2026-09-09','Mojokerto','BECAK','000000','wewew','ALEXA','pengiriman','2026-09-09 00:20:16','2026-09-09 00:20:16'),(47,'SJ-20261031-001',54,27,1,'2026-10-31','yonkavling singosari','MOBIL BOX','L 1223 TTC','yaul','yaul p','-','2026-10-31 00:54:03','2026-10-31 00:54:03');
/*!40000 ALTER TABLE `surat_jalans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_faktur_items`
--

DROP TABLE IF EXISTS `trans_faktur_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trans_faktur_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trans_faktur_id` bigint(20) unsigned NOT NULL,
  `deskripsi` varchar(255) NOT NULL,
  `qty` decimal(18,2) DEFAULT 0.00,
  `unit` varchar(100) DEFAULT NULL,
  `harga_satuan` decimal(18,2) DEFAULT 0.00,
  `diskon` decimal(18,2) DEFAULT 0.00,
  `total` decimal(18,2) DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_trans_faktur_items` (`trans_faktur_id`),
  CONSTRAINT `fk_trans_faktur_items` FOREIGN KEY (`trans_faktur_id`) REFERENCES `trans_fakturs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_faktur_items`
--

LOCK TABLES `trans_faktur_items` WRITE;
/*!40000 ALTER TABLE `trans_faktur_items` DISABLE KEYS */;
INSERT INTO `trans_faktur_items` VALUES (1,1,'PLAT MICRO EMBOS',10.00,'SHEET',50000.00,0.00,500000.00,NULL,'2026-05-21 00:22:32','2026-05-21 00:22:32'),(2,1,'KERTAS ROLL 4000 M',1.00,'ROLL',750000.00,0.00,750000.00,NULL,'2026-05-21 00:22:32','2026-05-21 00:22:32'),(3,2,'KERTAS IVORY 230 GR 53 X 68.5',100000.00,'SHEET',15000.00,10.00,1499999990.00,NULL,'2026-06-10 08:39:37','2026-06-10 08:39:37');
/*!40000 ALTER TABLE `trans_faktur_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_fakturs`
--

DROP TABLE IF EXISTS `trans_fakturs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trans_fakturs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `pic_id` bigint(20) unsigned DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `no_invoice` varchar(255) DEFAULT NULL,
  `no_faktur` varchar(255) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `subtotal` decimal(18,2) DEFAULT 0.00,
  `ppn` decimal(18,2) DEFAULT 0.00,
  `grand_total` decimal(18,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tf_po` (`purchase_order_id`),
  KEY `fk_tf_pic` (`pic_id`),
  CONSTRAINT `fk_tf_pic` FOREIGN KEY (`pic_id`) REFERENCES `karyawans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tf_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_fakturs`
--

LOCK TABLES `trans_fakturs` WRITE;
/*!40000 ALTER TABLE `trans_fakturs` DISABLE KEYS */;
INSERT INTO `trans_fakturs` VALUES (1,20,1,'UGRMS','123','123','2026-05-22',1250000.00,137500.00,1387500.00,'2026-05-21 00:22:32','2026-05-21 00:22:32'),(2,22,1,'UGRMS','123','123','2026-06-10',1499999990.00,164999998.90,1664999988.90,'2026-06-10 08:39:37','2026-06-10 08:39:37');
/*!40000 ALTER TABLE `trans_fakturs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_kas`
--

DROP TABLE IF EXISTS `trans_kas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trans_kas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(100) DEFAULT NULL,
  `tipe_transaksi` enum('KAS_MASUK','KAS_KELUAR') NOT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `karyawan_id` bigint(20) unsigned DEFAULT NULL,
  `account_bank_id` bigint(20) unsigned DEFAULT NULL,
  `account_kas_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `nominal` bigint(20) DEFAULT 0,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  KEY `fk_trans_kas_karyawan` (`karyawan_id`),
  KEY `fk_trans_kas_bank` (`account_bank_id`),
  KEY `fk_trans_kas_kas` (`account_kas_id`),
  CONSTRAINT `fk_trans_kas_bank` FOREIGN KEY (`account_bank_id`) REFERENCES `master_coas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trans_kas_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trans_kas_kas` FOREIGN KEY (`account_kas_id`) REFERENCES `master_coas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_kas`
--

LOCK TABLES `trans_kas` WRITE;
/*!40000 ALTER TABLE `trans_kas` DISABLE KEYS */;
INSERT INTO `trans_kas` VALUES (1,'TRK-202606-0001','KAS_MASUK','UGRMS','2026-06-05','2026',1,1,1,123,4999999,'ACTIVE','Uang jatuh dari langit\n','2026-06-05 02:12:52','2026-06-05 02:12:52'),(3,'TRK-202606-0002','KAS_MASUK','UGRMS','2026-06-05','2026',1,1,1,NULL,200000,'ACTIVE',NULL,'2026-06-05 02:17:09','2026-06-05 02:17:09'),(4,'TRK-202606-0003','KAS_MASUK','UGRMS','2026-06-05','2026',1,1,1,NULL,199999,'ACTIVE','Beli bensin','2026-06-05 02:28:29','2026-06-05 02:28:29'),(5,'TRK-202606-0004','KAS_MASUK','UGRMS','2026-06-05',NULL,1,1,1,NULL,400000,'ACTIVE','BELI BENSIN DELICA','2026-06-05 02:33:56','2026-06-05 02:33:56'),(6,'TRK-202606-0005','KAS_KELUAR','UGRMS','2026-06-05',NULL,1,1,1,NULL,900000,'ACTIVE','bensin','2026-06-05 02:37:23','2026-06-05 02:37:23'),(7,'TRK-202606-0006','KAS_MASUK','UGRMS','2026-06-10','2026',1,1,1,1,5000,'ACTIVE','sisa saldo','2026-06-10 08:42:12','2026-06-10 08:42:12');
/*!40000 ALTER TABLE `trans_kas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_kas_banks`
--

DROP TABLE IF EXISTS `trans_kas_banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trans_kas_banks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(100) DEFAULT NULL,
  `tipe_transaksi` enum('BANK_MASUK','BANK_KELUAR') NOT NULL,
  `gudang` varchar(100) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `periode` varchar(20) DEFAULT NULL,
  `account_bank_id` bigint(20) unsigned DEFAULT NULL,
  `account_lawan_id` bigint(20) unsigned DEFAULT NULL,
  `nominal` bigint(20) NOT NULL DEFAULT 0,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `atas_nama` varchar(100) DEFAULT NULL,
  `no_rekening` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_kas_banks`
--

LOCK TABLES `trans_kas_banks` WRITE;
/*!40000 ALTER TABLE `trans_kas_banks` DISABLE KEYS */;
INSERT INTO `trans_kas_banks` VALUES (1,'BANK-00001','BANK_MASUK','UGRMS','2026-05-22','2026',1,1,10000000,1,'BCA','Ulfa','ewewwewewwe','wewewewew','ACTIVE','2026-05-22 00:11:54','2026-05-22 00:11:54'),(2,'BANK-00002','BANK_KELUAR','UGRMS','2026-06-10','2026',1,1,19999999,1,'BCA','Ulfa','1234567890','bayar pajak','ACTIVE','2026-06-10 08:45:05','2026-06-10 08:45:05');
/*!40000 ALTER TABLE `trans_kas_banks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type_items`
--

DROP TABLE IF EXISTS `type_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_type_item` varchar(255) NOT NULL,
  `nama_type_item` varchar(255) NOT NULL,
  `category_item_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_items_kode_type_item_unique` (`kode_type_item`),
  KEY `type_items_category_item_id_foreign` (`category_item_id`),
  CONSTRAINT `type_items_category_item_id_foreign` FOREIGN KEY (`category_item_id`) REFERENCES `category_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type_items`
--

LOCK TABLES `type_items` WRITE;
/*!40000 ALTER TABLE `type_items` DISABLE KEYS */;
INSERT INTO `type_items` VALUES (1,'KI','KERTAS IVORY',2,'2026-03-06 16:21:04','2026-03-06 16:21:04'),(2,'JS','JASA',3,'2026-03-06 16:21:16','2026-03-06 16:21:16'),(3,'T','TINTA',2,'2026-03-06 16:21:30','2026-03-06 16:21:30'),(4,'BP','BAHAN PENUNJANG',2,'2026-04-27 01:26:37','2026-04-27 01:26:37'),(5,'CH','CHEMICAL',2,'2026-04-27 01:27:10','2026-04-27 01:27:10'),(6,'FL','FOIL',2,'2026-04-27 01:30:04','2026-04-27 01:30:04'),(7,'P','PLAT',5,'2026-04-28 01:49:47','2026-04-28 01:49:47'),(8,'ET','ETIKET',5,'2026-05-25 19:19:24','2026-05-25 19:19:24');
/*!40000 ALTER TABLE `type_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@gmail.com',NULL,NULL,'$2y$12$XyyWsUoJ2wZX7TdmV7hjU.h.wP8HJsR2cPfB3lqL83VYXJUd9rof.',NULL,'2026-03-05 02:07:25','2026-03-05 02:07:25');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ttc'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-02 12:16:35
