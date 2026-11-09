-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_printing
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Table structure for table `absensilembur`
--

DROP TABLE IF EXISTS `absensilembur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensilembur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Tanggal` datetime DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `cnamnt` double DEFAULT NULL,
  `ctotallembur` varchar(255) DEFAULT NULL,
  `jamakhir` varchar(255) DEFAULT NULL,
  `jamakhir_set` varchar(50) DEFAULT NULL,
  `jamawal` varchar(255) DEFAULT NULL,
  `jamawal_set` varchar(50) DEFAULT NULL,
  `karyawan` varchar(255) DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensilembur`
--

LOCK TABLES `absensilembur` WRITE;
/*!40000 ALTER TABLE `absensilembur` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensilembur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `absensirekap`
--

DROP TABLE IF EXISTS `absensirekap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensirekap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cidk` varchar(255) DEFAULT NULL,
  `cjabatan` varchar(255) DEFAULT NULL,
  `clockin` varchar(255) DEFAULT NULL,
  `clockout` varchar(255) DEFAULT NULL,
  `early` varchar(255) DEFAULT NULL,
  `late` varchar(255) DEFAULT NULL,
  `lembur_menit` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `offduty` varchar(255) DEFAULT NULL,
  `onduty` varchar(255) DEFAULT NULL,
  `tanggal` varchar(255) DEFAULT NULL,
  `timetable` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensirekap`
--

LOCK TABLES `absensirekap` WRITE;
/*!40000 ALTER TABLE `absensirekap` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensirekap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appupdate`
--

DROP TABLE IF EXISTS `appupdate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appupdate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_type` varchar(50) DEFAULT NULL,
  `app_update` varchar(50) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appupdate`
--

LOCK TABLES `appupdate` WRITE;
/*!40000 ALTER TABLE `appupdate` DISABLE KEYS */;
INSERT INTO `appupdate` VALUES (1,'desktop','20231010.3','2026-10-31 15:39:49',1),(2,'desktop','20231010','2026-10-31 15:39:49',1);
/*!40000 ALTER TABLE `appupdate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bomproduct`
--

DROP TABLE IF EXISTS `bomproduct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bomproduct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cplot` double DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bomproduct`
--

LOCK TABLES `bomproduct` WRITE;
/*!40000 ALTER TABLE `bomproduct` DISABLE KEYS */;
/*!40000 ALTER TABLE `bomproduct` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bomproductcost`
--

DROP TABLE IF EXISTS `bomproductcost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bomproductcost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cinterval` int(11) DEFAULT NULL,
  `cost` varchar(255) DEFAULT NULL,
  `cprice` double DEFAULT NULL,
  `kode_cost` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bomproductcost`
--

LOCK TABLES `bomproductcost` WRITE;
/*!40000 ALTER TABLE `bomproductcost` DISABLE KEYS */;
/*!40000 ALTER TABLE `bomproductcost` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bomproductd`
--

DROP TABLE IF EXISTS `bomproductd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bomproductd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bomproductd`
--

LOCK TABLES `bomproductd` WRITE;
/*!40000 ALTER TABLE `bomproductd` DISABLE KEYS */;
/*!40000 ALTER TABLE `bomproductd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuti_ambil`
--

DROP TABLE IF EXISTS `cuti_ambil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cuti_ambil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdesc` varchar(255) DEFAULT NULL,
  `cdoc` varchar(255) DEFAULT NULL,
  `cidk` varchar(255) DEFAULT NULL,
  `cjamakhir` varchar(255) DEFAULT NULL,
  `cjamawal` varchar(255) DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `ctglcuti` datetime DEFAULT NULL,
  `karyawan` varchar(255) DEFAULT NULL,
  `nidca` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuti_ambil`
--

LOCK TABLES `cuti_ambil` WRITE;
/*!40000 ALTER TABLE `cuti_ambil` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuti_ambil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formbonuskaryawan`
--

DROP TABLE IF EXISTS `formbonuskaryawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formbonuskaryawan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) DEFAULT NULL,
  `cnik` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `namnt` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formbonuskaryawan`
--

LOCK TABLES `formbonuskaryawan` WRITE;
/*!40000 ALTER TABLE `formbonuskaryawan` DISABLE KEYS */;
/*!40000 ALTER TABLE `formbonuskaryawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formoperasional`
--

DROP TABLE IF EXISTS `formoperasional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formoperasional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cdevisi` varchar(255) DEFAULT NULL,
  `ckeperluan` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cprice` double DEFAULT NULL,
  `cprice_total` double DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formoperasional`
--

LOCK TABLES `formoperasional` WRITE;
/*!40000 ALTER TABLE `formoperasional` DISABLE KEYS */;
/*!40000 ALTER TABLE `formoperasional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formoperasionald`
--

DROP TABLE IF EXISTS `formoperasionald`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formoperasionald` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ckeperluan` varchar(255) DEFAULT NULL,
  `cprice` double DEFAULT NULL,
  `cprice_total` double DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formoperasionald`
--

LOCK TABLES `formoperasionald` WRITE;
/*!40000 ALTER TABLE `formoperasionald` DISABLE KEYS */;
/*!40000 ALTER TABLE `formoperasionald` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formpenawaran`
--

DROP TABLE IF EXISTS `formpenawaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formpenawaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `dtran_expired` datetime DEFAULT NULL,
  `kode_customer` varchar(255) DEFAULT NULL,
  `npengerjaan` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nprice_total` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nspek` varchar(255) DEFAULT NULL,
  `nukuran` varchar(255) DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formpenawaran`
--

LOCK TABLES `formpenawaran` WRITE;
/*!40000 ALTER TABLE `formpenawaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `formpenawaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formpenawarand`
--

DROP TABLE IF EXISTS `formpenawarand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formpenawarand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `npengerjaan` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nprice_total` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nspek` varchar(255) DEFAULT NULL,
  `nukuran` varchar(255) DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formpenawarand`
--

LOCK TABLES `formpenawarand` WRITE;
/*!40000 ALTER TABLE `formpenawarand` DISABLE KEYS */;
/*!40000 ALTER TABLE `formpenawarand` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formpinjaman`
--

DROP TABLE IF EXISTS `formpinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formpinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(15) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnik` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cwaktu_bulan` double DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `namnt_cicilan` double DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formpinjaman`
--

LOCK TABLES `formpinjaman` WRITE;
/*!40000 ALTER TABLE `formpinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `formpinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cnote` varchar(255) DEFAULT NULL,
  `ctgl` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hppbahan`
--

DROP TABLE IF EXISTS `hppbahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hppbahan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cogs` double DEFAULT NULL,
  `qty` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hppbahan`
--

LOCK TABLES `hppbahan` WRITE;
/*!40000 ALTER TABLE `hppbahan` DISABLE KEYS */;
/*!40000 ALTER TABLE `hppbahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maccount`
--

DROP TABLE IF EXISTS `maccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maccount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `NCredit` double DEFAULT NULL,
  `NDebet` double DEFAULT NULL,
  `NNominal` double DEFAULT NULL,
  `cPeriod` varchar(255) DEFAULT NULL,
  `caccnt` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `csj_vendor` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `fAccount` varchar(255) DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maccount`
--

LOCK TABLES `maccount` WRITE;
/*!40000 ALTER TABLE `maccount` DISABLE KEYS */;
INSERT INTO `maccount` VALUES (1,0,15000000,15000000,'2026','1101','GD001','Kas Kasir / Kas Tunai',NULL,NULL,'Kas operasional harian kasir & penerimaan tunai',NULL,'admin',NULL,'1','1',NULL,NULL),(2,0,85000000,85000000,'2026','1102','GD001','Bank Central Asia (BCA)',NULL,NULL,'Rekening penerimaan pelanggan & giro masuk',NULL,'admin',NULL,'1','1',NULL,NULL),(3,0,45000000,45000000,'2026','1103','GD001','Bank Mandiri',NULL,NULL,'Rekening pembayaran supplier & operasional',NULL,'admin',NULL,'1','1',NULL,NULL),(4,0,28500000,28500000,'2026','1104','GD001','Piutang Usaha Pelanggan',NULL,NULL,'Piutang cetak PO & tagihan invoice tempo',NULL,'admin',NULL,'1','1',NULL,NULL),(5,0,35000000,35000000,'2026','1105','GD001','Persediaan Bahan Baku (Raw Material)',NULL,NULL,'Nilai stok fisik kertas, tinta, dan bahan roll',NULL,'admin',NULL,'1','1',NULL,NULL),(6,0,18000000,18000000,'2026','1106','GD001','Persediaan Barang Jadi (Finished Goods)',NULL,NULL,'Nilai persediaan barang cetak siap kirim',NULL,'admin',NULL,'1','1',NULL,NULL),(7,0,450000000,450000000,'2026','1201','GD001','Aktiva Tetap - Mesin Percetakan',NULL,NULL,'Inventaris mesin offset, digital press & finishing',NULL,'admin',NULL,'1','1',NULL,NULL),(8,45000000,0,-45000000,'2026','1202','GD001','Akumulasi Penyusutan Mesin',NULL,NULL,'Akumulasi depresiasi aktiva tetap mesin',NULL,'admin',NULL,'1','1',NULL,NULL),(9,22000000,0,22000000,'2026','2101','GD001','Hutang Usaha Supplier Bahan',NULL,NULL,'Hutang pembelian kertas & tinta tempo 30 hari',NULL,'admin',NULL,'2','1',NULL,NULL),(10,15000000,0,15000000,'2026','2102','GD001','Hutang Gaji & Tunjangan Karyawan',NULL,NULL,'Alokasi payroll gaji akhir bulan',NULL,'admin',NULL,'2','1',NULL,NULL),(11,500000000,0,500000000,'2026','3101','GD001','Modal Pemilik / Disetor',NULL,NULL,'Modal awal pendirian percetakan',NULL,'admin',NULL,'3','1',NULL,NULL),(12,94500000,0,94500000,'2026','3201','GD001','Laba Ditahan',NULL,NULL,'Akumulasi laba bersih periode sebelumnya',NULL,'admin',NULL,'3','1',NULL,NULL),(13,0,0,0,'2026','4101','GD001','Pendapatan Penjualan Cetak Offset',NULL,NULL,'Omset dari pesanan cetak brosur, majalah, nota',NULL,'admin',NULL,'4','1',NULL,NULL),(14,0,0,0,'2026','4102','GD001','Pendapatan Penjualan Digital Printing',NULL,NULL,'Omset dari cetak stiker, banner, kartu nama',NULL,'admin',NULL,'4','1',NULL,NULL),(15,0,0,0,'2026','4103','GD001','Pendapatan Jasa Finishing & Desain',NULL,NULL,'Pendapatan biaya desain, setting, dan jilid',NULL,'admin',NULL,'4','1',NULL,NULL),(16,0,0,0,'2026','5101','GD001','HPP - Pemakaian Bahan Kertas',NULL,NULL,'Harga pokok kertas yang terpakai pada SPK',NULL,'admin',NULL,'5','1',NULL,NULL),(17,0,0,0,'2026','5102','GD001','HPP - Pemakaian Tinta & Kimia',NULL,NULL,'Harga pokok tinta & bahan kimia cetak',NULL,'admin',NULL,'5','1',NULL,NULL),(18,0,0,0,'2026','5103','GD001','HPP - Biaya Tenaga Kerja Langsung',NULL,NULL,'Upah borongan & lembur operator produksi',NULL,'admin',NULL,'5','1',NULL,NULL),(19,0,0,0,'2026','6101','GD001','Beban Gaji Karyawan & Staff',NULL,NULL,'Gaji pokok & tunjangan staff bulanan',NULL,'admin',NULL,'6','1',NULL,NULL),(20,0,0,0,'2026','6102','GD001','Beban Listrik, Air & Workshop',NULL,NULL,'Biaya daya listrik industri & utilitas',NULL,'admin',NULL,'6','1',NULL,NULL),(21,0,0,0,'2026','6103','GD001','Beban Pemeliharaan & Sparepart Mesin',NULL,NULL,'Oli, karet roll, service berkala mesin',NULL,'admin',NULL,'6','1',NULL,NULL),(22,0,0,0,'2026','6104','GD001','Beban Pemasaran & Promosi',NULL,NULL,'Iklan, sample produk, dan fee marketing',NULL,'admin',NULL,'6','1',NULL,NULL);
/*!40000 ALTER TABLE `maccount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maccountclass`
--

DROP TABLE IF EXISTS `maccountclass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maccountclass` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caccnt_class` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `fstatus` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maccountclass`
--

LOCK TABLES `maccountclass` WRITE;
/*!40000 ALTER TABLE `maccountclass` DISABLE KEYS */;
INSERT INTO `maccountclass` VALUES (1,'1','AKTIVA / HARTA','admin',1),(2,'2','KEWAJIBAN / HUTANG','admin',1),(3,'3','MODAL / EKUITAS','admin',1),(4,'4','PENDAPATAN / REVENUE','admin',1),(5,'5','HARGA POKOK PENJUALAN (HPP)','admin',1),(6,'6','BEBAN OPERASIONAL','admin',1);
/*!40000 ALTER TABLE `maccountclass` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materialstock`
--

DROP TABLE IF EXISTS `materialstock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materialstock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cr` double DEFAULT NULL,
  `cunit` varchar(255) DEFAULT NULL,
  `db` double DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `kstatus` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `terpakai` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materialstock`
--

LOCK TABLES `materialstock` WRITE;
/*!40000 ALTER TABLE `materialstock` DISABLE KEYS */;
/*!40000 ALTER TABLE `materialstock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mcompany`
--

DROP TABLE IF EXISTS `mcompany`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mcompany` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addr` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `districts` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `ket_inv` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `postalcode` varchar(255) DEFAULT NULL,
  `rek1_an` varchar(255) DEFAULT NULL,
  `rek1_jenis` varchar(255) DEFAULT NULL,
  `rek1_no` varchar(255) DEFAULT NULL,
  `rek2_an` varchar(255) DEFAULT NULL,
  `rek2_jenis` varchar(255) DEFAULT NULL,
  `rek2_no` varchar(255) DEFAULT NULL,
  `telp` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mcompany`
--

LOCK TABLES `mcompany` WRITE;
/*!40000 ALTER TABLE `mcompany` DISABLE KEYS */;
INSERT INTO `mcompany` VALUES (1,'Jl. Industri Grafika No. 128, Gedangan','Sidoarjo','Gedangan','031-8912346','Terima kasih atas kepercayaan Anda bermitra dengan Indigama Printing. Pembayaran tempo maksimal 30 hari kalender.','sales@indigama.co.id','CV. Indigama Khatulistiwa','61254','CV Indigama Khatulistiwa','BNI','0892345112','CV Indigama Khatulistiwa','BCA','0149928310','031-8912345');
/*!40000 ALTER TABLE `mcompany` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mcost`
--

DROP TABLE IF EXISTS `mcost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mcost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cket` varchar(255) DEFAULT NULL,
  `cost` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mcost`
--

LOCK TABLES `mcost` WRITE;
/*!40000 ALTER TABLE `mcost` DISABLE KEYS */;
INSERT INTO `mcost` VALUES (1,'Dihitung per jam jalan mesin cetak (Rp 75.000/jam)','Biaya Listrik Mesin Cetak Offset','C001',NULL),(2,'Output plat 4 warna CMYK ukuran 52 (Rp 35.000/plat)','Biaya Pembuatan Plat CTP Aluminium','C002',NULL),(3,'Pembuatan pisau pon bentuk dus & packaging kemasan','Biaya Pisau Pond / Die-Cut Custom','C003',NULL),(4,'Kardus packing, lakban, shrink wrap & strapping band','Biaya Bahan Packing & Pengiriman','C004',NULL);
/*!40000 ALTER TABLE `mcost` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mcustomer`
--

DROP TABLE IF EXISTS `mcustomer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mcustomer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addr` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `ctelp` varchar(255) DEFAULT NULL,
  `kode_customer` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mcustomer`
--

LOCK TABLES `mcustomer` WRITE;
/*!40000 ALTER TABLE `mcustomer` DISABLE KEYS */;
INSERT INTO `mcustomer` VALUES (1,'Jl. Raya Rungkut Industri No. 10 Surabaya','PT. Indofood Sukses Makmur Tbk','031-8432100','CM001',NULL,'admin'),(2,'Jl. Pahlawan No. 45 Sidoarjo','CV. Berkah Abadi Bakery','031-8923456','CM002',NULL,'admin'),(3,'Jl. Ketintang Surabaya','Universitas Negeri Surabaya (UNESA)','031-8280009','CM003',NULL,'admin'),(4,'Jl. Gajah Mada No. 88 Sidoarjo','Klinik Pratama Sehat Sejahtera','031-8967812','CM004',NULL,'admin'),(5,'Pasar Atom Mall Lt. 2 Tahap IV No. 20 Surabaya','Toko Busana Muslim Amanah','031-3554422','CM005',NULL,'admin'),(6,'Ruko Graha Family Blok B-12 Surabaya','Agensi Kreatif Digital Nusantara','031-7345678','CM006',NULL,'admin');
/*!40000 ALTER TABLE `mcustomer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mcuti`
--

DROP TABLE IF EXISTS `mcuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mcuti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdesc` varchar(255) DEFAULT NULL,
  `cidc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mcuti`
--

LOCK TABLES `mcuti` WRITE;
/*!40000 ALTER TABLE `mcuti` DISABLE KEYS */;
INSERT INTO `mcuti` VALUES (1,'Cuti Tahunan (12 Hari Kerja)','CT001'),(2,'Cuti Khusus Pernikahan (3 Hari)','CT002'),(3,'Cuti Bersalin / Melahirkan (3 Bulan)','CT003'),(4,'Ijin Sakit (Surat Dokter)','CK019'),(5,'Ijin Terlambat Masuk Kantor','CK020'),(6,'Ijin Pulang Cepat / Urusan Mendesak','CK021'),(7,'Ijin Urusan Keluarga / Kedukaan','CK022');
/*!40000 ALTER TABLE `mcuti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdefault`
--

DROP TABLE IF EXISTS `mdefault`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdefault` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cket` varchar(255) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `default_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdefault`
--

LOCK TABLES `mdefault` WRITE;
/*!40000 ALTER TABLE `mdefault` DISABLE KEYS */;
INSERT INTO `mdefault` VALUES (1,NULL,'ccode','01'),(2,NULL,'pt','INDIGAMA PRINTING'),(3,NULL,'alamat','Jl. Percetakan No. 1'),(4,NULL,'telp','021-1234567'),(5,NULL,'mail','indigama@example.com'),(6,NULL,'acc_bank','1102'),(7,NULL,'acc_modal','3101'),(8,NULL,'acc_kas','1101');
/*!40000 ALTER TABLE `mdefault` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdepartemen`
--

DROP TABLE IF EXISTS `mdepartemen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdepartemen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdept` varchar(255) DEFAULT NULL,
  `cjabatan` varchar(255) DEFAULT NULL,
  `ctunj` double DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `id_dept` varchar(255) DEFAULT NULL,
  `id_jab` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdepartemen`
--

LOCK TABLES `mdepartemen` WRITE;
/*!40000 ALTER TABLE `mdepartemen` DISABLE KEYS */;
INSERT INTO `mdepartemen` VALUES (1,'PRODUKSI & CETAK',NULL,NULL,'admin','DP01',NULL,NULL),(2,'GUDANG & LOGISTIK',NULL,NULL,'admin','DP02',NULL,NULL),(3,'FINANCE & ACCOUNTING',NULL,NULL,'admin','DP03',NULL,NULL),(4,'MARKETING & SALES',NULL,NULL,'admin','DP04',NULL,NULL),(5,'HRD & UMUM',NULL,NULL,'admin','DP05',NULL,NULL),(6,'DESAIN & PRE-PRESS',NULL,NULL,'admin','DP06',NULL,NULL);
/*!40000 ALTER TABLE `mdepartemen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merrorproduction`
--

DROP TABLE IF EXISTS `merrorproduction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merrorproduction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merrorproduction`
--

LOCK TABLES `merrorproduction` WRITE;
/*!40000 ALTER TABLE `merrorproduction` DISABLE KEYS */;
INSERT INTO `merrorproduction` VALUES (1,'Kertas Nyangkut / Paper Jam','ERR01',NULL),(2,'Tinta Belang / Ketebalan Warna Tidak Merata','ERR02',NULL),(3,'Register Warna Meleset / Berbayang','ERR03',NULL),(4,'Salah Presisi Potong / Ukuran Tidak Sesuai','ERR04',NULL),(5,'Laminasi Berkerut / Ada Gelembung Udara','ERR05',NULL),(6,'Lem Jilid Kurang Melekat / Halaman Lepas','ERR06',NULL);
/*!40000 ALTER TABLE `merrorproduction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mgroup`
--

DROP TABLE IF EXISTS `mgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mgroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mgroup`
--

LOCK TABLES `mgroup` WRITE;
/*!40000 ALTER TABLE `mgroup` DISABLE KEYS */;
INSERT INTO `mgroup` VALUES (1,'KERTAS SHEET','Bahan baku kertas lembaran plano & potong','admin'),(2,'TINTA & KIMIA','Tinta offset, toner & chemical percetakan','admin'),(3,'BAHAN ROLL','Bahan flexi, banner mmt, sticker roll','admin'),(4,'PACKAGING & BINDING','Plastik laminasi, lem binding, spiral kawat, dus','admin'),(5,'PRODUK CETAK OFFSET','Produk jadi cetak offset massal (brosur, nota, buku)','admin'),(6,'PRODUK DIGITAL PRINT','Produk jadi cetak digital cepat (kartu nama, banner, stiker)','admin'),(7,'PRODUK PACKAGING','Produk jadi box kemasan makanan, obat & produk','admin');
/*!40000 ALTER TABLE `mgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mgudang`
--

DROP TABLE IF EXISTS `mgudang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mgudang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bahan` varchar(255) DEFAULT NULL,
  `caddr` varchar(255) DEFAULT NULL,
  `ccity` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesa` varchar(255) DEFAULT NULL,
  `cdue_date` datetime DEFAULT NULL,
  `cfax` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `chead` varchar(255) DEFAULT NULL,
  `chead_acc` varchar(255) DEFAULT NULL,
  `ckecamatan` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_PR` varchar(255) DEFAULT NULL,
  `cnbkt_delivery` varchar(255) DEFAULT NULL,
  `cnbkt_order` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `coperator` varchar(255) DEFAULT NULL,
  `cpostalcode` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cproses` varchar(255) DEFAULT NULL,
  `ctelp` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqty_box` double DEFAULT NULL,
  `nqty_box_waste` double DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  `nqty_small_waste` double DEFAULT NULL,
  `nqtyin` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunitin` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `tglout` datetime DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mgudang`
--

LOCK TABLES `mgudang` WRITE;
/*!40000 ALTER TABLE `mgudang` DISABLE KEYS */;
INSERT INTO `mgudang` VALUES (1,NULL,'Jl. Industri Grafika No. 128','Sidoarjo','GD001','Sawotratap',NULL,'031-8912346',NULL,'Bambang Wijaya','Sri Wahyuni','Gedangan','Gudang Pusat & Bahan Baku',NULL,NULL,NULL,NULL,NULL,'Gudang utama penerimaan dan penyimpanan bahan baku plano/roll',NULL,'61254',NULL,NULL,'031-8912345',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(2,NULL,'Jl. Raya Tropodo No. 45','Sidoarjo','GD002','Tropodo',NULL,'031-8667891',NULL,'Joko Susilo','Sri Wahyuni','Waru','Gudang Produk Jadi & Display',NULL,NULL,NULL,NULL,NULL,'Gudang transit produk jadi siap kirim & display toko',NULL,'61256',NULL,NULL,'031-8667890',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(3,NULL,'Kawasan Pergudangan Sirie Blok C-10','Sidoarjo','GD003','Rangkah',NULL,'031-8051235',NULL,'Agus Hendra','Sri Wahyuni','Lingkar Timur','Gudang Logistik & Sparepart',NULL,NULL,NULL,NULL,NULL,'Gudang penyimpanan packaging kardus, palet, dan sparepart mesin',NULL,'61234',NULL,NULL,'031-8051234',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin');
/*!40000 ALTER TABLE `mgudang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mjabatan`
--

DROP TABLE IF EXISTS `mjabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mjabatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdept` varchar(255) DEFAULT NULL,
  `cjabatan` varchar(255) DEFAULT NULL,
  `ctunj` double DEFAULT NULL,
  `id_dept` varchar(255) DEFAULT NULL,
  `id_jab` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mjabatan`
--

LOCK TABLES `mjabatan` WRITE;
/*!40000 ALTER TABLE `mjabatan` DISABLE KEYS */;
INSERT INTO `mjabatan` VALUES (1,NULL,'Manager Produksi',1500000,'DP01','J001',NULL,'admin'),(2,NULL,'Operator Cetak Offset',600000,'DP01','J002',NULL,'admin'),(3,NULL,'Operator Finishing & Pond',450000,'DP01','J003',NULL,'admin'),(4,NULL,'Kepala Gudang & Logistik',750000,'DP02','J004',NULL,'admin'),(5,NULL,'Staff Finance & Accounting',650000,'DP03','J005',NULL,'admin'),(6,NULL,'Account Executive / Sales',800000,'DP04','J006',NULL,'admin'),(7,NULL,'Staff HRD & Personalia',600000,'DP05','J007',NULL,'admin'),(8,NULL,'Graphic Designer & CTP',700000,'DP06','J008',NULL,'admin');
/*!40000 ALTER TABLE `mjabatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mkaryawan`
--

DROP TABLE IF EXISTS `mkaryawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mkaryawan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ServerDate` datetime DEFAULT NULL,
  `caddr` varchar(255) DEFAULT NULL,
  `caddr_ktp` varchar(255) DEFAULT NULL,
  `cbank` varchar(255) DEFAULT NULL,
  `cbank_an` varchar(255) DEFAULT NULL,
  `cbank_number` varchar(255) DEFAULT NULL,
  `cborongan` varchar(255) DEFAULT NULL,
  `cbpjs_kesehatan` varchar(255) DEFAULT NULL,
  `cbpjs_name` varchar(255) DEFAULT NULL,
  `ccity` varchar(255) DEFAULT NULL,
  `ccity_ktp` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdept` varchar(255) DEFAULT NULL,
  `cdesa` varchar(255) DEFAULT NULL,
  `cdesa_ktp` varchar(255) DEFAULT NULL,
  `cgander` varchar(255) DEFAULT NULL,
  `cid_finger` varchar(255) DEFAULT NULL,
  `ckecamatan` varchar(255) DEFAULT NULL,
  `ckecamatan_ktp` varchar(255) DEFAULT NULL,
  `cktp` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cpayrol` varchar(255) DEFAULT NULL,
  `cpostalcode` varchar(255) DEFAULT NULL,
  `cpostalcode_ktp` varchar(255) DEFAULT NULL,
  `creligy` varchar(255) DEFAULT NULL,
  `cresign` datetime DEFAULT NULL,
  `csalary` varchar(255) DEFAULT NULL,
  `ctelp` varchar(255) DEFAULT NULL,
  `ctgl_lahir` datetime DEFAULT NULL,
  `ctgl_masuk` datetime DEFAULT NULL,
  `ctunjintensif` int(11) DEFAULT NULL,
  `ctunjjab` int(11) DEFAULT NULL,
  `ctunjkomp` int(11) DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `namajab` varchar(255) DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `cjabatan` varchar(255) DEFAULT NULL,
  `clembur` varchar(255) DEFAULT NULL,
  `cmarried` varchar(255) DEFAULT NULL,
  `cbpjs_ketenagakerjaan` varchar(255) DEFAULT NULL,
  `cbpjs_cabang` varchar(255) DEFAULT NULL,
  `cbpjs_tgl` datetime DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cnpwp_name` varchar(255) DEFAULT NULL,
  `cnpwp_alamat` varchar(255) DEFAULT NULL,
  `cnpwp_no` varchar(255) DEFAULT NULL,
  `cnpwp_tgl` datetime DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mkaryawan`
--

LOCK TABLES `mkaryawan` WRITE;
/*!40000 ALTER TABLE `mkaryawan` DISABLE KEYS */;
INSERT INTO `mkaryawan` VALUES (1,'2026-10-31 17:08:40','Perum Puri Surya Jaya Blok B-12',NULL,'BCA','Budi Santoso','0142234123','0','000123456789','Budi Santoso','Sidoarjo',NULL,'GD001',NULL,'Puri',NULL,'Laki - Laki','101','Gedangan',NULL,'3515011005850001','Budi Santoso, S.T.','MONTH','61254',NULL,'Islam',NULL,'6500000','081234567890','1985-05-10 00:00:00','2023-01-10 00:00:00',500000,1500000,500000,NULL,NULL,NULL,'230110001','J001','LMB01','Menikah','98765432100','Sidoarjo','2023-02-01 00:00:00','Karyawan Tetap - Manager Produksi','Budi Santoso',NULL,'85.123.456.7-603.000',NULL,'admin'),(2,'2026-10-31 17:08:40','Desa Sawotratap RT 03 RW 02',NULL,'BCA','Ahmad Fauzi','0142234124','0','000123456790','Ahmad Fauzi','Sidoarjo',NULL,'GD001',NULL,'Sawotratap',NULL,'Laki - Laki','102','Gedangan',NULL,'3515022008900002','Ahmad Fauzi','MONTH','61254',NULL,'Islam',NULL,'4800000','081234567891','1990-08-20 00:00:00','2023-03-15 00:00:00',300000,600000,300000,NULL,NULL,NULL,'230315002','J002','LMB01','Menikah','98765432101','Sidoarjo','2023-04-01 00:00:00','Operator Mesin Offset Utama','Ahmad Fauzi',NULL,'85.123.456.7-603.001',NULL,'admin'),(3,'2026-10-31 17:08:40','Jl. Kolonel Sugiono No. 25',NULL,'BRI','Siti Rahmawati','002101988123','1','000123456791','Siti Rahmawati','Sidoarjo',NULL,'GD001',NULL,'Kureksari',NULL,'Perempuan','103','Waru',NULL,'3515031512950003','Siti Rahmawati','MONTH','61256',NULL,'Islam',NULL,'4200000','081234567892','1995-12-15 00:00:00','2023-06-01 00:00:00',200000,450000,250000,NULL,NULL,NULL,'230601003','J003','LMB01','Belum Menikah','98765432102','Sidoarjo','2023-07-01 00:00:00','Operator Finishing & Quality Control','Siti Rahmawati',NULL,'85.123.456.7-603.002',NULL,'admin'),(4,'2026-10-31 17:08:40','Dusun Rangkah Kidul No. 08',NULL,'Mandiri','Bambang Wijaya','142001889234','0','000123456792','Bambang Wijaya','Sidoarjo',NULL,'GD001',NULL,'Rangkah',NULL,'Laki - Laki','104','Sidoarjo',NULL,'3515041004880004','Bambang Wijaya','MONTH','61234',NULL,'Islam',NULL,'5000000','081234567893','1988-04-10 00:00:00','2023-02-01 00:00:00',400000,750000,300000,NULL,NULL,NULL,'230201004','J004','LMB01','Menikah','98765432103','Sidoarjo','2023-03-01 00:00:00','Kepala Gudang Bahan Baku & Finished Goods','Bambang Wijaya',NULL,'85.123.456.7-603.003',NULL,'admin'),(5,'2026-10-31 17:08:40','Jl. Letjen Sutoyo No. 18',NULL,'BCA','Dewi Lestari','0142234125','0','000123456793','Dewi Lestari','Sidoarjo',NULL,'GD002',NULL,'Medaeng',NULL,'Perempuan','105','Waru',NULL,'3515052207960005','Dewi Lestari, S.Ak.','MONTH','61256',NULL,'Islam',NULL,'4700000','081234567894','1996-07-22 00:00:00','2023-05-10 00:00:00',300000,650000,300000,NULL,NULL,NULL,'230510005','J005','LMB01','Belum Menikah','98765432104','Sidoarjo','2023-06-01 00:00:00','Staff Finance, Billing & Pajak','Dewi Lestari',NULL,'85.123.456.7-603.004',NULL,'admin'),(6,'2026-10-31 17:08:40','Taman Candiloka Blok F-05',NULL,'BCA','Rian Pratama','0142234126','0','000123456794','Rian Pratama','Sidoarjo',NULL,'GD002',NULL,'Ngasinan',NULL,'Laki - Laki','106','Candi',NULL,'3515061803940006','Rian Pratama','MONTH','61271',NULL,'Islam',NULL,'4500000','081234567895','1994-03-18 00:00:00','2023-08-20 00:00:00',500000,800000,300000,NULL,NULL,NULL,'230820006','J006','LMB01','Menikah','98765432105','Sidoarjo','2023-09-01 00:00:00','Account Executive Korporat & B2B','Rian Pratama',NULL,'85.123.456.7-603.005',NULL,'admin'),(7,'2026-10-31 17:08:40','Jl. Jenggolo II No. 14',NULL,'BCA','Dian Anggraini','0142234127','0','000123456795','Dian Anggraini','Sidoarjo',NULL,'GD001',NULL,'Banjarkemantren',NULL,'Perempuan','107','Buduran',NULL,'3515072811970007','Dian Anggraini, S.Ds.','MONTH','61252',NULL,'Islam',NULL,'4900000','081234567896','1997-11-28 00:00:00','2023-04-12 00:00:00',400000,700000,300000,NULL,NULL,NULL,'230412007','J008','LMB01','Belum Menikah','98765432106','Sidoarjo','2023-05-01 00:00:00','Desain Grafis Pre-Press & Separasi Warna','Dian Anggraini',NULL,'85.123.456.7-603.006',NULL,'admin');
/*!40000 ALTER TABLE `mkaryawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mmenu`
--

DROP TABLE IF EXISTS `mmenu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mmenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mmenu`
--

LOCK TABLES `mmenu` WRITE;
/*!40000 ALTER TABLE `mmenu` DISABLE KEYS */;
INSERT INTO `mmenu` VALUES (50,'BONUS KARYAWAN'),(56,'DELIVERY MITRA'),(49,'DELIVERY REKAP PO CUSTOMER'),(58,'FAKTUR PAJAK'),(4,'FORM PENAWARAN CUSTOMER'),(3,'FORM PINJAMAN KARYAWAN'),(37,'HOLIDAYS'),(35,'KARYAWAN CUTI'),(36,'KARYAWAN IJIN'),(20,'KAS & BANK'),(27,'LAPORAN ACCOUNTING'),(25,'LAPORAN PENJUALAN'),(26,'LAPORAN PRODUKSI'),(5,'MARKETING FEE'),(14,'MASTER AKUN'),(38,'MASTER COMPANY'),(10,'MASTER CUSTOMER'),(6,'MASTER DATA'),(39,'MASTER FREELANCE'),(40,'MASTER GAJI BORONGAN'),(12,'MASTER GUDANG'),(8,'MASTER KARYAWAN'),(153,'MASTER MATERIAL'),(11,'MASTER MESIN'),(7,'MASTER PRODUK'),(42,'MASTER PRODUKSI BOM'),(43,'MASTER PRODUKSI COST'),(45,'MASTER PRODUKSI FAIL'),(44,'MASTER PRODUKSI STEP'),(13,'MASTER SATUAN'),(9,'MASTER SUPPLIER'),(21,'MEMORIAL'),(1,'NOTIFICATION'),(22,'OPERASIONAL'),(23,'PAYROLL / GAJI'),(2,'PRODUKSI BORONGAN'),(28,'PRODUKSI PO CUSTOMER'),(29,'PRODUKSI RETURN'),(31,'PRODUKSI STOCK CHECK'),(30,'PRODUKSI STOCKB'),(47,'PRODUKSI VERIFIKASI PRODUK JADI'),(41,'PURCHASE BILL'),(55,'PURCHASE ORDER MITRA'),(19,'PURCHASING / PO'),(24,'RAW MATERIAL'),(57,'RECEIVE MITRA'),(18,'RECEIVE ORDER'),(53,'REPORT PAYROLL'),(46,'ROW MATERIAL RETURN PRODUKSI'),(17,'SURAT JALAN / DELIVERY'),(54,'TRANSAKSI PAYROLL BORONGAN'),(15,'TRANSAKSI PENJUALAN'),(16,'WORK ORDER');
/*!40000 ALTER TABLE `mmenu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mmesin`
--

DROP TABLE IF EXISTS `mmesin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mmesin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cgroup` varchar(50) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cproduct` varchar(50) DEFAULT NULL,
  `cunit` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `kstatus` varchar(255) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mmesin`
--

LOCK TABLES `mmesin` WRITE;
/*!40000 ALTER TABLE `mmesin` DISABLE KEYS */;
INSERT INTO `mmesin` VALUES (1,'MSN01','PRODUKSI CETAK','Mesin Offset Heidelberg Speedmaster SM 52 4-Warna',NULL,'GD001',NULL,NULL,NULL,NULL),(2,'MSN02','PRODUKSI DIGITAL','Mesin Digital Production Konica Minolta AccurioPress C4070',NULL,'GD001',NULL,NULL,NULL,NULL),(3,'MSN03','LARGE FORMAT','Mesin Large Format Indoor Roland TrueVIS SG3-540',NULL,'GD001',NULL,NULL,NULL,NULL),(4,'MSN04','LARGE FORMAT','Mesin Outdoor Flora Polar 320m Konica 512i',NULL,'GD001',NULL,NULL,NULL,NULL),(5,'MSN05','FINISHING','Mesin Potong Kertas Otomatis Polar Mohr 115 High Speed',NULL,'GD001',NULL,NULL,NULL,NULL),(6,'MSN06','FINISHING','Mesin Laminasi Thermal Doff & Glossy Automatic YDFM-720',NULL,'GD001',NULL,NULL,NULL,NULL),(7,'MSN07','FINISHING','Mesin Lem Panas Jilid Buku / Perfect Binding Horizon BQ-270',NULL,'GD001',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `mmesin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mmesininv`
--

DROP TABLE IF EXISTS `mmesininv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mmesininv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `idmesin` varchar(255) DEFAULT NULL,
  `mesin` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mmesininv`
--

LOCK TABLES `mmesininv` WRITE;
/*!40000 ALTER TABLE `mmesininv` DISABLE KEYS */;
/*!40000 ALTER TABLE `mmesininv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mmetodepembayaran`
--

DROP TABLE IF EXISTS `mmetodepembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mmetodepembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `KodeBayar` varchar(255) DEFAULT NULL,
  `MetodeBayar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mmetodepembayaran`
--

LOCK TABLES `mmetodepembayaran` WRITE;
/*!40000 ALTER TABLE `mmetodepembayaran` DISABLE KEYS */;
INSERT INTO `mmetodepembayaran` VALUES (1,'01','TUNAI / CASH'),(2,'02','TRANSFER BCA'),(3,'03','TRANSFER MANDIRI'),(4,'04','QRIS'),(5,'05','BON / KREDIT');
/*!40000 ALTER TABLE `mmetodepembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moperasional`
--

DROP TABLE IF EXISTS `moperasional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moperasional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdesc` varchar(255) DEFAULT NULL,
  `id_op` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `nstatus` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moperasional`
--

LOCK TABLES `moperasional` WRITE;
/*!40000 ALTER TABLE `moperasional` DISABLE KEYS */;
INSERT INTO `moperasional` VALUES (1,'Tagihan Listrik PLN Workshop & Kantor','OP01',NULL,1,'admin'),(2,'Biaya Akses Internet Fiber & Telepon Kantor','OP02',NULL,1,'admin'),(3,'Uang Makan & Konsumsi Lembur Karyawan','OP03',NULL,1,'admin'),(4,'BBM & Operasional Armada Mobil Box Pengiriman','OP04',NULL,1,'admin'),(5,'Pembelian ATK, Nota & Perlengkapan Kantor','OP05',NULL,1,'admin'),(6,'Biaya Servis Rutin & Penggantian Oli Mesin','OP06',NULL,1,'admin');
/*!40000 ALTER TABLE `moperasional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpayment`
--

DROP TABLE IF EXISTS `mpayment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpayment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdesc` varchar(255) DEFAULT NULL,
  `id_pay` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpayment`
--

LOCK TABLES `mpayment` WRITE;
/*!40000 ALTER TABLE `mpayment` DISABLE KEYS */;
INSERT INTO `mpayment` VALUES (1,'Pembayaran Tunai Kasir','CASH'),(2,'Transfer Rekening Bank BCA','TRANSFER-BCA'),(3,'Transfer Rekening Bank Mandiri','TRANSFER-MANDIRI'),(4,'Pembayaran QRIS Statis / Dinamis','QRIS'),(5,'Pembayaran Bilyet Giro Tempo','GIRO'),(6,'Kredit Piutang Dagang 30 Hari','TEMPO');
/*!40000 ALTER TABLE `mpayment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpayroll`
--

DROP TABLE IF EXISTS `mpayroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpayroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdesc` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `value1` int(11) DEFAULT NULL,
  `value2` int(11) DEFAULT NULL,
  `value3` double DEFAULT NULL,
  `key1` varchar(255) DEFAULT NULL,
  `key2` varchar(255) DEFAULT NULL,
  `key3` varchar(255) DEFAULT NULL,
  `flag` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpayroll`
--

LOCK TABLES `mpayroll` WRITE;
/*!40000 ALTER TABLE `mpayroll` DISABLE KEYS */;
INSERT INTO `mpayroll` VALUES (1,'Lembur Hari Kerja Biasa','LMB01',60,60,25000,'Start Menit','Kelipatan Menit','Nominal','LEMBUR'),(2,'Lembur Hari Libur / Weekend','LMB02',60,60,40000,'Start Menit','Kelipatan Menit','Nominal','LEMBUR');
/*!40000 ALTER TABLE `mpayroll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpriceborongan`
--

DROP TABLE IF EXISTS `mpriceborongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpriceborongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nperiod` datetime DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpriceborongan`
--

LOCK TABLES `mpriceborongan` WRITE;
/*!40000 ALTER TABLE `mpriceborongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpriceborongan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpriceborongantunj`
--

DROP TABLE IF EXISTS `mpriceborongantunj`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpriceborongantunj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nperiod` datetime DEFAULT NULL,
  `nprice_hadir` double DEFAULT NULL,
  `nprice_lain` double DEFAULT NULL,
  `nprice_makan` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpriceborongantunj`
--

LOCK TABLES `mpriceborongantunj` WRITE;
/*!40000 ALTER TABLE `mpriceborongantunj` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpriceborongantunj` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mproduk`
--

DROP TABLE IF EXISTS `mproduk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mproduk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CDProduct` varchar(255) DEFAULT NULL,
  `Fstatus` varchar(255) DEFAULT NULL,
  `NPrPurc` double DEFAULT NULL,
  `NPrSell` double DEFAULT NULL,
  `NQty` double DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csale` int(11) DEFAULT NULL,
  `cunit` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `cwarningstock` varchar(255) DEFAULT NULL,
  `kode_customer` varchar(255) DEFAULT NULL,
  `n1` double DEFAULT NULL,
  `n2` double DEFAULT NULL,
  `n3` double DEFAULT NULL,
  `n4` double DEFAULT NULL,
  `n5` double DEFAULT NULL,
  `n6` double DEFAULT NULL,
  `ncogs` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` varchar(255) DEFAULT NULL,
  `nunit_smallvalue` varchar(255) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `t1` varchar(255) DEFAULT NULL,
  `t2` varchar(255) DEFAULT NULL,
  `t3` varchar(255) DEFAULT NULL,
  `t4` varchar(255) DEFAULT NULL,
  `t5` varchar(255) DEFAULT NULL,
  `t6` varchar(255) DEFAULT NULL,
  `t7` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mproduk`
--

LOCK TABLES `mproduk` WRITE;
/*!40000 ALTER TABLE `mproduk` DISABLE KEYS */;
INSERT INTO `mproduk` VALUES (1,'M','1',320000,0,85,'GD001','Bahan kartu nama, cover buku, dus','KERTAS SHEET','Art Carton 260 GSM Plano 65x100',NULL,'MAT-AC260',NULL,'RIM',NULL,'10',NULL,NULL,NULL,NULL,NULL,NULL,NULL,320000,NULL,'1','500',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(2,'M','1',210000,0,120,'GD001','Bahan brosur, flyer, isi majalah','KERTAS SHEET','Art Paper 150 GSM Plano 65x100',NULL,'MAT-AP150',NULL,'RIM',NULL,'15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,210000,NULL,'1','500',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(3,'M','1',145000,0,150,'GD001','Bahan kop surat, nota, buku teks','KERTAS SHEET','Kertas HVS 80 GSM Plano 65x90',NULL,'MAT-HVS80',NULL,'RIM',NULL,'20',NULL,NULL,NULL,NULL,NULL,NULL,NULL,145000,NULL,'1','500',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(4,'M','1',285000,0,60,'GD001','Bahan box kemasan makanan & snack','KERTAS SHEET','Kertas Duplex 310 GSM Plano 79x109',NULL,'MAT-DUP310',NULL,'RIM',NULL,'10',NULL,NULL,NULL,NULL,NULL,NULL,NULL,285000,NULL,'1','500',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(5,'M','1',650000,0,18,'GD001','Bahan spanduk, banner outdoor','BAHAN ROLL','Flexi Frontlite Outdoor 280 GSM Lebar 3.2m',NULL,'MAT-FLX280',NULL,'ROLL',NULL,'5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,650000,NULL,'1','50',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(6,'M','1',520000,0,22,'GD001','Bahan stiker label botol, kemasan','BAHAN ROLL','Sticker Vinyl Glossy Lebar 1.27m',NULL,'MAT-VIN-GLOSS',NULL,'ROLL',NULL,'5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,520000,NULL,'1','50',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(7,'M','1',110000,0,30,'GD001','Tinta biru proses 4-warna','TINTA & KIMIA','Tinta Cetak Offset Toyo Ink Cyan 1 Kg',NULL,'MAT-TNT-CYAN',NULL,'KG',NULL,'5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,110000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(8,'M','1',110000,0,30,'GD001','Tinta merah proses 4-warna','TINTA & KIMIA','Tinta Cetak Offset Toyo Ink Magenta 1 Kg',NULL,'MAT-TNT-MAG',NULL,'KG',NULL,'5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,110000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(9,'M','1',110000,0,30,'GD001','Tinta kuning proses 4-warna','TINTA & KIMIA','Tinta Cetak Offset Toyo Ink Yellow 1 Kg',NULL,'MAT-TNT-YEL',NULL,'KG',NULL,'5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,110000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(10,'M','1',95000,0,40,'GD001','Tinta hitam proses 4-warna','TINTA & KIMIA','Tinta Cetak Offset Toyo Ink Black 1 Kg',NULL,'MAT-TNT-BLK',NULL,'KG',NULL,'5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,95000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(11,'M','1',240000,0,15,'GD001','Bahan laminasi doff elegan','PACKAGING & BINDING','Plastik Thermal Laminating Doff 30 Micron',NULL,'MAT-LAM-DOFF',NULL,'ROLL',NULL,'3',NULL,NULL,NULL,NULL,NULL,NULL,NULL,240000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(12,'M','1',48000,0,50,'GD001','Lem jilid buku perekat kuat','PACKAGING & BINDING','Lem Panas Glue Binding Pellet 1 Kg',NULL,'MAT-LEM-HOT',NULL,'KG',NULL,'10',NULL,NULL,NULL,NULL,NULL,NULL,NULL,48000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(13,'P','1',180000,350000,25,'GD002','Brosur A4 2 sisi full color minimal 1 Rim','PRODUK CETAK OFFSET','Brosur Promosi A4 Art Paper 150gr',NULL,'PRD-BRO-A4',NULL,'RIM',NULL,'5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,180000,NULL,'1','500',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(14,'P','1',22000,45000,150,'GD002','Kartu nama AC 260gr cetak bolak balik','PRODUK DIGITAL PRINT','Kartu Nama Premium 2 Sisi + Box + Doff',NULL,'PRD-KRT-NAMA',NULL,'BOX',NULL,'20',NULL,NULL,NULL,NULL,NULL,NULL,NULL,22000,NULL,'1','100',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(15,'P','1',12000,25000,200,'GD002','Banner outdoor cetak tajam siap pasang','PRODUK DIGITAL PRINT','Banner Spanduk Flexi 280gr Mata Ayam',NULL,'PRD-BNR-FLX',NULL,'METER',NULL,'30',NULL,NULL,NULL,NULL,NULL,NULL,NULL,12000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(16,'P','1',4500,10000,500,'GD002','Stiker kemasan tahan air & tidak mudah robek','PRODUK DIGITAL PRINT','Sticker Vinyl Glossy Kiss Cut A3+',NULL,'PRD-STK-VYN',NULL,'PCS',NULL,'50',NULL,NULL,NULL,NULL,NULL,NULL,NULL,4500,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(17,'P','1',16000,35000,120,'GD002','Kalender meja custom dudukan linen hardcover','PRODUK CETAK OFFSET','Kalender Meja Standar Spiral Kawat 13 Lbr',NULL,'PRD-KAL-MEJA',NULL,'PCS',NULL,'25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,16000,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(18,'P','1',8500,18000,80,'GD002','Nota custom nomorator + perforasi rapi','PRODUK CETAK OFFSET','Buku Nota NCR 3 Rangkap (50 Set)',NULL,'PRD-NOT-3R',NULL,'BUKU',NULL,'15',NULL,NULL,NULL,NULL,NULL,NULL,NULL,8500,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin'),(19,'P','1',1200,2500,3500,'GD002','Dus kue & catering die cut kancing otomatis','PRODUK PACKAGING','Dus Kemasan Snack Duplex 310gr Full Color',NULL,'PRD-BOX-SNACK',NULL,'PCS',NULL,'500',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1200,NULL,'1','1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin');
/*!40000 ALTER TABLE `mproduk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mprodukjual`
--

DROP TABLE IF EXISTS `mprodukjual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mprodukjual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `NPrPurc` double DEFAULT NULL,
  `NPrSell` double DEFAULT NULL,
  `big_value` double DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cunit` varchar(255) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `small_value` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mprodukjual`
--

LOCK TABLES `mprodukjual` WRITE;
/*!40000 ALTER TABLE `mprodukjual` DISABLE KEYS */;
/*!40000 ALTER TABLE `mprodukjual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mprodukwarning`
--

DROP TABLE IF EXISTS `mprodukwarning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mprodukwarning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mprodukwarning`
--

LOCK TABLES `mprodukwarning` WRITE;
/*!40000 ALTER TABLE `mprodukwarning` DISABLE KEYS */;
/*!40000 ALTER TABLE `mprodukwarning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mptkp`
--

DROP TABLE IF EXISTS `mptkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mptkp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `golongan` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mptkp`
--

LOCK TABLES `mptkp` WRITE;
/*!40000 ALTER TABLE `mptkp` DISABLE KEYS */;
/*!40000 ALTER TABLE `mptkp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msatuan`
--

DROP TABLE IF EXISTS `msatuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msatuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `big_value` double DEFAULT NULL,
  `cbox_no` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cworkorder` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `id_turunan` varchar(255) DEFAULT NULL,
  `kolom` varchar(255) DEFAULT NULL,
  `namanya` varchar(255) DEFAULT NULL,
  `namasatuan` varchar(250) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  `satuankonversi` varchar(255) DEFAULT NULL,
  `satuanproduk` varchar(255) DEFAULT NULL,
  `small_value` double DEFAULT NULL,
  `terkecil` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msatuan`
--

LOCK TABLES `msatuan` WRITE;
/*!40000 ALTER TABLE `msatuan` DISABLE KEYS */;
INSERT INTO `msatuan` VALUES (1,NULL,1,NULL,'PCS',NULL,NULL,NULL,NULL,'0',NULL,NULL,'Pieces / Lembar Satuan',NULL,NULL,NULL,NULL,1,NULL),(2,NULL,1,NULL,'RIM',NULL,NULL,NULL,NULL,'1',NULL,NULL,'Rim (500 Lembar)',NULL,NULL,NULL,NULL,500,NULL),(3,NULL,1,NULL,'BOX',NULL,NULL,NULL,NULL,'1',NULL,NULL,'Box / Dus Kemasan',NULL,NULL,NULL,NULL,100,NULL),(4,NULL,1,NULL,'PACK',NULL,NULL,NULL,NULL,'1',NULL,NULL,'Pack / Bungkus',NULL,NULL,NULL,NULL,10,NULL),(5,NULL,1,NULL,'KG',NULL,NULL,NULL,NULL,'0',NULL,NULL,'Kilogram (1000 Gram)',NULL,NULL,NULL,NULL,1000,NULL),(6,NULL,1,NULL,'METER',NULL,NULL,NULL,NULL,'0',NULL,NULL,'Meter Lari / Persegi',NULL,NULL,NULL,NULL,1,NULL),(7,NULL,1,NULL,'ROLL',NULL,NULL,NULL,NULL,'6',NULL,NULL,'Roll / Gulungan 50m',NULL,NULL,NULL,NULL,50,NULL),(8,NULL,1,NULL,'BUKU',NULL,NULL,NULL,NULL,'0',NULL,NULL,'Buku / Eksemplar',NULL,NULL,NULL,NULL,1,NULL);
/*!40000 ALTER TABLE `msatuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mskat`
--

DROP TABLE IF EXISTS `mskat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mskat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `tgl` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mskat`
--

LOCK TABLES `mskat` WRITE;
/*!40000 ALTER TABLE `mskat` DISABLE KEYS */;
INSERT INTO `mskat` VALUES (1,'Rak A - Kertas Sheet & Plano',NULL,NULL),(2,'Rak B - Tinta, Toner & Kimia',NULL,'2026-10-31 17:08:40'),(3,'Rak C - Bahan Roll & Banner MMT',NULL,'2026-10-31 17:08:40'),(4,'Rak D - Kemasan & Packaging',NULL,'2026-10-31 17:08:40'),(5,'Rak E - Barang Jadi Siap Kirim',NULL,'2026-10-31 17:08:40');
/*!40000 ALTER TABLE `mskat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mstepproduction`
--

DROP TABLE IF EXISTS `mstepproduction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mstepproduction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cfinal_process` int(11) DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `idmesin` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `mesin` varchar(255) DEFAULT NULL,
  `urut` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mstepproduction`
--

LOCK TABLES `mstepproduction` WRITE;
/*!40000 ALTER TABLE `mstepproduction` DISABLE KEYS */;
INSERT INTO `mstepproduction` VALUES (1,0,'Pemeriksaan file, separasi warna & burning plat CTP','1. Desain Pre-Press & Output CTP','1','STP01',NULL,NULL,'1','GD001','admin'),(2,0,'Potong bahan plano menjadi ukuran siap masuk mesin cetak','2. Persiapan Bahan Kertas & Potong Awal','5','STP02',NULL,NULL,'2','GD001','admin'),(3,0,'Pencetakan bahan sesuai SPK & approval sample warna','3. Proses Cetak Offset / Digital','1','STP03',NULL,NULL,'3','GD001','admin'),(4,0,'Pelapisan plastik thermal pelindung cetakan','4. Proses Laminasi Thermal (Doff/Glossy)','6','STP04',NULL,NULL,'4','GD001','admin'),(5,0,'Pemotongan ukuran jadi atau pengeleman jilid buku','5. Finishing Potong Sisir / Lem Jilid','7','STP05',NULL,NULL,'5','GD001','admin'),(6,1,'Pemeriksaan kualitas akhir dan pengemasan ke kardus','6. Quality Control & Packing Pengiriman','0','STP06',NULL,NULL,'6','GD001','admin');
/*!40000 ALTER TABLE `mstepproduction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msupplier`
--

DROP TABLE IF EXISTS `msupplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msupplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addr` varchar(255) DEFAULT NULL,
  `bank` varchar(255) DEFAULT NULL,
  `bank_number` varchar(255) DEFAULT NULL,
  `bank_owner` varchar(255) DEFAULT NULL,
  `cjenis` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cs` varchar(255) DEFAULT NULL,
  `ctelp` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msupplier`
--

LOCK TABLES `msupplier` WRITE;
/*!40000 ALTER TABLE `msupplier` DISABLE KEYS */;
INSERT INTO `msupplier` VALUES (1,'Kawasan Industri Rungkut Megah Raya Blok M-08 Surabaya','BCA','0148892123','PT Surya Kertas Nusantara','Kertas Sheet & Plano','PT. Surya Kertas Nusantara','Heru Purnomo','031-8712345','S001',NULL,'admin'),(2,'Jl. Letjen Sutoyo No. 89 Waru, Sidoarjo','Mandiri','142001988231','CV Tinta Warna Mandiri','Tinta Offset & Toner','CV. Tinta Warna Mandiri','David Chandra','031-8945678','S002',NULL,'admin'),(3,'Jl. Ngagel Tama No. 15 Surabaya','BCA','0147721890','PT Duta Grafika Media','Bahan Roll & Digital Print','PT. Duta Grafika Media','Maya Kusuma','031-5678912','S003',NULL,'admin'),(4,'Jl. Raya Tropodo Wetan No. 34 Sidoarjo','BRI','0021019876543','Haryono Santoso','Lem, Lakban & Packaging','UD. Mitra Lem & Packaging','Haryono Santoso','031-8664321','S004',NULL,'admin');
/*!40000 ALTER TABLE `msupplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mtoko`
--

DROP TABLE IF EXISTS `mtoko`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mtoko` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alamat` varchar(255) DEFAULT NULL,
  `toko` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mtoko`
--

LOCK TABLES `mtoko` WRITE;
/*!40000 ALTER TABLE `mtoko` DISABLE KEYS */;
/*!40000 ALTER TABLE `mtoko` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payrollborongan`
--

DROP TABLE IF EXISTS `payrollborongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payrollborongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) DEFAULT NULL,
  `cnik` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `nprice_hadir` int(11) DEFAULT NULL,
  `nprice_lain` int(11) DEFAULT NULL,
  `nprice_makan` int(11) DEFAULT NULL,
  `pack` int(11) DEFAULT NULL,
  `pack_gaji` int(11) DEFAULT NULL,
  `protol` int(11) DEFAULT NULL,
  `protol_gaji` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrollborongan`
--

LOCK TABLES `payrollborongan` WRITE;
/*!40000 ALTER TABLE `payrollborongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `payrollborongan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payrolld`
--

DROP TABLE IF EXISTS `payrolld`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payrolld` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `A` varchar(255) DEFAULT NULL,
  `CUTI` varchar(255) DEFAULT NULL,
  `ClockIn` varchar(255) DEFAULT NULL,
  `ClockOut` varchar(255) DEFAULT NULL,
  `Early` varchar(255) DEFAULT NULL,
  `HOLIDAY` varchar(255) DEFAULT NULL,
  `IJIN` varchar(255) DEFAULT NULL,
  `IJIN_MENIT` varchar(255) DEFAULT NULL,
  `LEMBUR` varchar(255) DEFAULT NULL,
  `LEMBUR_MENIT` varchar(255) DEFAULT NULL,
  `LIBUR` varchar(255) DEFAULT NULL,
  `Late` varchar(255) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `OK` varchar(255) DEFAULT NULL,
  `OffDuty` varchar(255) DEFAULT NULL,
  `OnDuty` varchar(255) DEFAULT NULL,
  `PCP` varchar(255) DEFAULT NULL,
  `PCP_MENIT` varchar(255) DEFAULT NULL,
  `SAKIT` varchar(255) DEFAULT NULL,
  `SAKIT_SDOKTER` varchar(255) DEFAULT NULL,
  `TELAT` varchar(255) DEFAULT NULL,
  `TELAT_MENIT` varchar(255) DEFAULT NULL,
  `Tanggal` varchar(255) DEFAULT NULL,
  `cidk` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolld`
--

LOCK TABLES `payrolld` WRITE;
/*!40000 ALTER TABLE `payrolld` DISABLE KEYS */;
/*!40000 ALTER TABLE `payrolld` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pborongan`
--

DROP TABLE IF EXISTS `pborongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pborongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) DEFAULT NULL,
  `cnik` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `nilaigaji` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `tipe` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pborongan`
--

LOCK TABLES `pborongan` WRITE;
/*!40000 ALTER TABLE `pborongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `pborongan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pboxpacking`
--

DROP TABLE IF EXISTS `pboxpacking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pboxpacking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_order` varchar(255) DEFAULT NULL,
  `cnik` varchar(50) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `cworkorder` varchar(255) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `karyawan` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqty_box` double DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  `qtyfinish` double DEFAULT NULL,
  `satuanproduk` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pboxpacking`
--

LOCK TABLES `pboxpacking` WRITE;
/*!40000 ALTER TABLE `pboxpacking` DISABLE KEYS */;
/*!40000 ALTER TABLE `pboxpacking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pboxpackingd`
--

DROP TABLE IF EXISTS `pboxpackingd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pboxpackingd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cverifikasi` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pboxpackingd`
--

LOCK TABLES `pboxpackingd` WRITE;
/*!40000 ALTER TABLE `pboxpackingd` DISABLE KEYS */;
/*!40000 ALTER TABLE `pboxpackingd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pcustomerpo`
--

DROP TABLE IF EXISTS `pcustomerpo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcustomerpo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cgroup_bahan` varchar(255) DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `cmarketing` varchar(255) DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(500) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cproduct_bahan` varchar(255) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `kode` int(11) DEFAULT NULL,
  `kode_customer` varchar(255) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pcustomerpo`
--

LOCK TABLES `pcustomerpo` WRITE;
/*!40000 ALTER TABLE `pcustomerpo` DISABLE KEYS */;
/*!40000 ALTER TABLE `pcustomerpo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pcustomerpod`
--

DROP TABLE IF EXISTS `pcustomerpod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcustomerpod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bahan` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `jml` double DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `marketing` varchar(255) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `produk` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pcustomerpod`
--

LOCK TABLES `pcustomerpod` WRITE;
/*!40000 ALTER TABLE `pcustomerpod` DISABLE KEYS */;
/*!40000 ALTER TABLE `pcustomerpod` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `TotalBarang` varchar(255) DEFAULT NULL,
  `TotalDijual` varchar(255) DEFAULT NULL,
  `TotalReturn` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pproderr`
--

DROP TABLE IF EXISTS `pproderr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pproderr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cerr_name` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `nqty_fail` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pproderr`
--

LOCK TABLES `pproderr` WRITE;
/*!40000 ALTER TABLE `pproderr` DISABLE KEYS */;
/*!40000 ALTER TABLE `pproderr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pprodsteps`
--

DROP TABLE IF EXISTS `pprodsteps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pprodsteps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cusername` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pprodsteps`
--

LOCK TABLES `pprodsteps` WRITE;
/*!40000 ALTER TABLE `pprodsteps` DISABLE KEYS */;
/*!40000 ALTER TABLE `pprodsteps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productstock`
--

DROP TABLE IF EXISTS `productstock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productstock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cr` double DEFAULT NULL,
  `db` double DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `mtcr` double DEFAULT NULL,
  `mtdb` double DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `qtyawal` double DEFAULT NULL,
  `qtydebet` double DEFAULT NULL,
  `qtykredit` double DEFAULT NULL,
  `satuan_in` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productstock`
--

LOCK TABLES `productstock` WRITE;
/*!40000 ALTER TABLE `productstock` DISABLE KEYS */;
/*!40000 ALTER TABLE `productstock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pworkorder`
--

DROP TABLE IF EXISTS `pworkorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pworkorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgood` int(11) DEFAULT NULL,
  `cgood_pcs` int(11) DEFAULT NULL,
  `cgood_unit` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `ckeperluan` varchar(255) DEFAULT NULL,
  `cket` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cnik` varchar(255) DEFAULT NULL,
  `cprice` double DEFAULT NULL,
  `cprice_total` double DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cproduct_bahan` varchar(255) DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `cstep_kode` varchar(255) DEFAULT NULL,
  `cstep_name` varchar(255) DEFAULT NULL,
  `ctype` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cwaste` int(11) DEFAULT NULL,
  `cwaste_pcs` int(11) DEFAULT NULL,
  `cworkorder` varchar(255) DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `good` varchar(255) DEFAULT NULL,
  `karyawan` varchar(255) DEFAULT NULL,
  `ketpo` varchar(255) DEFAULT NULL,
  `kode` int(11) DEFAULT NULL,
  `kode_customer` varchar(50) DEFAULT NULL,
  `npengerjaan` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nprice_total` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  `nqty_total` double DEFAULT NULL,
  `nspek` varchar(255) DEFAULT NULL,
  `nukuran` varchar(255) DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `waste` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pworkorder`
--

LOCK TABLES `pworkorder` WRITE;
/*!40000 ALTER TABLE `pworkorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `pworkorder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pworkordercost`
--

DROP TABLE IF EXISTS `pworkordercost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pworkordercost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cinterval` double DEFAULT NULL,
  `cost` varchar(255) DEFAULT NULL,
  `cprice` double DEFAULT NULL,
  `kode_cost` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pworkordercost`
--

LOCK TABLES `pworkordercost` WRITE;
/*!40000 ALTER TABLE `pworkordercost` DISABLE KEYS */;
/*!40000 ALTER TABLE `pworkordercost` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pworkorderd`
--

DROP TABLE IF EXISTS `pworkorderd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pworkorderd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pworkorderd`
--

LOCK TABLES `pworkorderd` WRITE;
/*!40000 ALTER TABLE `pworkorderd` DISABLE KEYS */;
/*!40000 ALTER TABLE `pworkorderd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pworkorderstep`
--

DROP TABLE IF EXISTS `pworkorderstep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pworkorderstep` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cstep_kode` varchar(255) DEFAULT NULL,
  `cstep_name` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `kode_customer` varchar(255) DEFAULT NULL,
  `urut` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pworkorderstep`
--

LOCK TABLES `pworkorderstep` WRITE;
/*!40000 ALTER TABLE `pworkorderstep` DISABLE KEYS */;
/*!40000 ALTER TABLE `pworkorderstep` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_load`
--

DROP TABLE IF EXISTS `service_load`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_load` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bayar` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `diskon_barang` varchar(255) DEFAULT NULL,
  `diskon_member` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `harga` varchar(255) DEFAULT NULL,
  `id_barang` varchar(255) DEFAULT NULL,
  `jumlah` varchar(255) DEFAULT NULL,
  `kembali` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `pengaju` varchar(255) DEFAULT NULL,
  `totalnotif` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_load`
--

LOCK TABLES `service_load` WRITE;
/*!40000 ALTER TABLE `service_load` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_load` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stockopname`
--

DROP TABLE IF EXISTS `stockopname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockopname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `total` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stockopname`
--

LOCK TABLES `stockopname` WRITE;
/*!40000 ALTER TABLE `stockopname` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockopname` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trandeliverygudang`
--

DROP TABLE IF EXISTS `trandeliverygudang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trandeliverygudang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asal` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `ccodedesc` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_inv` varchar(255) DEFAULT NULL,
  `cnopol` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `csupir` varchar(255) DEFAULT NULL,
  `cvia` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudangtujuan` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `totalpack` double DEFAULT NULL,
  `tujuan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trandeliverygudang`
--

LOCK TABLES `trandeliverygudang` WRITE;
/*!40000 ALTER TABLE `trandeliverygudang` DISABLE KEYS */;
/*!40000 ALTER TABLE `trandeliverygudang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trandeliverygudangd`
--

DROP TABLE IF EXISTS `trandeliverygudangd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trandeliverygudangd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbox` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `cslop_value` double DEFAULT NULL,
  `nqty` int(11) DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trandeliverygudangd`
--

LOCK TABLES `trandeliverygudangd` WRITE;
/*!40000 ALTER TABLE `trandeliverygudangd` DISABLE KEYS */;
/*!40000 ALTER TABLE `trandeliverygudangd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trandeliveryorder`
--

DROP TABLE IF EXISTS `trandeliveryorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trandeliveryorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_inv` varchar(255) DEFAULT NULL,
  `cnopol` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `csupir` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `cvia` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `kode_customer` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `totalpack` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trandeliveryorder`
--

LOCK TABLES `trandeliveryorder` WRITE;
/*!40000 ALTER TABLE `trandeliveryorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `trandeliveryorder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trandeliveryorderd`
--

DROP TABLE IF EXISTS `trandeliveryorderd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trandeliveryorderd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbox` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `cnbkt_wo` varchar(50) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `csatuan_small` varchar(255) DEFAULT NULL,
  `nqty` int(11) DEFAULT NULL,
  `nqty_box` int(11) DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  `produkalias` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trandeliveryorderd`
--

LOCK TABLES `trandeliveryorderd` WRITE;
/*!40000 ALTER TABLE `trandeliveryorderd` DISABLE KEYS */;
/*!40000 ALTER TABLE `trandeliveryorderd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trankas`
--

DROP TABLE IF EXISTS `trankas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trankas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_bank` varchar(255) DEFAULT NULL,
  `acc_kas` varchar(255) DEFAULT NULL,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `idmesin` int(11) DEFAULT NULL,
  `kode` int(11) DEFAULT NULL,
  `kode_customer` varchar(50) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `mesin` varchar(255) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trankas`
--

LOCK TABLES `trankas` WRITE;
/*!40000 ALTER TABLE `trankas` DISABLE KEYS */;
/*!40000 ALTER TABLE `trankas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trankasbank`
--

DROP TABLE IF EXISTS `trankasbank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trankasbank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `a` int(11) DEFAULT NULL,
  `acc_bank` varchar(255) DEFAULT NULL,
  `acc_kas` varchar(255) DEFAULT NULL,
  `bank` varchar(255) DEFAULT NULL,
  `bank_an` varchar(255) DEFAULT NULL,
  `bank_rekening` varchar(255) DEFAULT NULL,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cidk` varchar(255) DEFAULT NULL,
  `cjabatan` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `idmesin` int(11) DEFAULT NULL,
  `kode` int(11) DEFAULT NULL,
  `kode_customer` varchar(50) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `mesin` varchar(255) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  `ok` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trankasbank`
--

LOCK TABLES `trankasbank` WRITE;
/*!40000 ALTER TABLE `trankasbank` DISABLE KEYS */;
/*!40000 ALTER TABLE `trankasbank` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trankaspinjaman`
--

DROP TABLE IF EXISTS `trankaspinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trankaspinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_bank` varchar(255) DEFAULT NULL,
  `acc_kas` varchar(255) DEFAULT NULL,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_form` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trankaspinjaman`
--

LOCK TABLES `trankaspinjaman` WRITE;
/*!40000 ALTER TABLE `trankaspinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `trankaspinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranmemorial`
--

DROP TABLE IF EXISTS `tranmemorial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranmemorial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_bank` varchar(255) DEFAULT NULL,
  `acc_kas` varchar(255) DEFAULT NULL,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `customerll` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `idmesin` int(11) DEFAULT NULL,
  `idmesinll` int(11) DEFAULT NULL,
  `kode_customer` varchar(50) DEFAULT NULL,
  `kode_customerll` varchar(50) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `mesin` varchar(255) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranmemorial`
--

LOCK TABLES `tranmemorial` WRITE;
/*!40000 ALTER TABLE `tranmemorial` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranmemorial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranopout`
--

DROP TABLE IF EXISTS `tranopout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranopout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_bank` varchar(255) DEFAULT NULL,
  `acc_kas` varchar(255) DEFAULT NULL,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cjenis` varchar(255) DEFAULT NULL,
  `cjenis_name` varchar(255) DEFAULT NULL,
  `ckendaraan` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `codometer` varchar(255) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `idmesin` int(11) DEFAULT NULL,
  `kode` int(11) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `mesin` varchar(255) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranopout`
--

LOCK TABLES `tranopout` WRITE;
/*!40000 ALTER TABLE `tranopout` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranopout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpayment`
--

DROP TABLE IF EXISTS `tranpayment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpayment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `cbank` varchar(255) DEFAULT NULL,
  `cbank_an` varchar(255) DEFAULT NULL,
  `cbank_rekening` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cdue_date` datetime DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_PO` varchar(255) DEFAULT NULL,
  `cnbkt_PR` varchar(255) DEFAULT NULL,
  `cnbkt_bill` varchar(255) DEFAULT NULL,
  `cnbkt_inv` varchar(255) DEFAULT NULL,
  `cnbktbill` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ctype` varchar(255) DEFAULT NULL,
  `curs` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cusername_update` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `dupdat` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `method` varchar(255) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `nominalpay` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqtyin` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `pay` double DEFAULT NULL,
  `ppn` double DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `statusduedate` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totalnilai` double DEFAULT NULL,
  `tpay` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpayment`
--

LOCK TABLES `tranpayment` WRITE;
/*!40000 ALTER TABLE `tranpayment` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpayment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpayment_detail`
--

DROP TABLE IF EXISTS `tranpayment_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpayment_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `cbank` varchar(255) DEFAULT NULL,
  `cbank_an` varchar(255) DEFAULT NULL,
  `cbank_rekening` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cdue_date` datetime DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_PO` varchar(255) DEFAULT NULL,
  `cnbktbill` varchar(255) DEFAULT NULL,
  `curs` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cusername_update` varchar(255) DEFAULT NULL,
  `dtran` varchar(255) DEFAULT NULL,
  `dupdat` varchar(255) DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `id_cnbkt` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `method` varchar(255) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `ntotalall` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `ppn` double DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `tglpo` datetime DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totalbayar` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpayment_detail`
--

LOCK TABLES `tranpayment_detail` WRITE;
/*!40000 ALTER TABLE `tranpayment_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpayment_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpayroll`
--

DROP TABLE IF EXISTS `tranpayroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpayroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a` double DEFAULT NULL,
  `bonus` double DEFAULT NULL,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cidk` varchar(255) DEFAULT NULL,
  `cjab` varchar(50) DEFAULT NULL,
  `cjabatan` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnik` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cpayrol` varchar(255) DEFAULT NULL,
  `cperiode` varchar(255) DEFAULT NULL,
  `cuti` double DEFAULT NULL,
  `dend` varchar(255) DEFAULT NULL,
  `dstart` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gp` double DEFAULT NULL,
  `gp_lembur` double DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `lembur` double DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `namnt` double DEFAULT NULL,
  `ok` double DEFAULT NULL,
  `packing_gaji` double DEFAULT NULL,
  `packing_qty` double DEFAULT NULL,
  `pcp` double DEFAULT NULL,
  `pot_a` double DEFAULT NULL,
  `pot_bpjskerja` double DEFAULT NULL,
  `pot_lain` double DEFAULT NULL,
  `pot_pcp` double DEFAULT NULL,
  `pot_pinjaman` double DEFAULT NULL,
  `pot_pph21` double DEFAULT NULL,
  `pot_sakit` double DEFAULT NULL,
  `pot_telat` double DEFAULT NULL,
  `protol_gaji` double DEFAULT NULL,
  `protol_qty` double DEFAULT NULL,
  `sakit` double DEFAULT NULL,
  `statusbonus` varchar(255) DEFAULT NULL,
  `telat` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `tunj_hadir` double DEFAULT NULL,
  `tunj_lain` double DEFAULT NULL,
  `tunj_makan` double DEFAULT NULL,
  `tunjangan` double DEFAULT NULL,
  `tunjangan_komp` double DEFAULT NULL,
  `tunjangan_lain` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpayroll`
--

LOCK TABLES `tranpayroll` WRITE;
/*!40000 ALTER TABLE `tranpayroll` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpayroll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpayrolld`
--

DROP TABLE IF EXISTS `tranpayrolld`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpayrolld` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `a` int(11) DEFAULT NULL,
  `bonus` int(11) DEFAULT NULL,
  `cidk` varchar(255) DEFAULT NULL,
  `cjabatan` varchar(255) DEFAULT NULL,
  `cperiode` varchar(255) DEFAULT NULL,
  `cuti` int(11) DEFAULT NULL,
  `gp` int(11) DEFAULT NULL,
  `gp_lembur` int(11) DEFAULT NULL,
  `lembur` int(11) DEFAULT NULL,
  `ok` int(11) DEFAULT NULL,
  `pcp` int(11) DEFAULT NULL,
  `pot_a` int(11) DEFAULT NULL,
  `pot_bpjskerja` int(11) DEFAULT NULL,
  `pot_lain` int(11) DEFAULT NULL,
  `pot_pcp` int(11) DEFAULT NULL,
  `pot_pinjaman` int(11) DEFAULT NULL,
  `pot_pph21` int(11) DEFAULT NULL,
  `pot_sakit` int(11) DEFAULT NULL,
  `pot_telat` int(11) DEFAULT NULL,
  `sakit` int(11) DEFAULT NULL,
  `telat` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `tunjangan` int(11) DEFAULT NULL,
  `tunjangan_komp` int(11) DEFAULT NULL,
  `tunjangan_lain` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpayrolld`
--

LOCK TABLES `tranpayrolld` WRITE;
/*!40000 ALTER TABLE `tranpayrolld` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpayrolld` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpayrolldborongan`
--

DROP TABLE IF EXISTS `tranpayrolldborongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpayrolldborongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cidk` varchar(255) DEFAULT NULL,
  `dtran` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `packing_gaji` double DEFAULT NULL,
  `packing_qty` double DEFAULT NULL,
  `protol_gaji` double DEFAULT NULL,
  `protol_qty` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `tunj_hadir` double DEFAULT NULL,
  `tunj_lain` double DEFAULT NULL,
  `tunj_makan` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpayrolldborongan`
--

LOCK TABLES `tranpayrolldborongan` WRITE;
/*!40000 ALTER TABLE `tranpayrolldborongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpayrolldborongan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpayrollddata`
--

DROP TABLE IF EXISTS `tranpayrollddata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpayrollddata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Tanggal` varchar(255) DEFAULT NULL,
  `a` int(11) DEFAULT NULL,
  `cidk` varchar(255) DEFAULT NULL,
  `clockin` varchar(255) DEFAULT NULL,
  `clockout` varchar(255) DEFAULT NULL,
  `cuti` int(11) DEFAULT NULL,
  `early` varchar(255) DEFAULT NULL,
  `holiday` int(11) DEFAULT NULL,
  `ijin` int(11) DEFAULT NULL,
  `ijin_menit` int(11) DEFAULT NULL,
  `late` varchar(255) DEFAULT NULL,
  `lembur` int(11) DEFAULT NULL,
  `lembur_menit` int(11) DEFAULT NULL,
  `libur` int(11) DEFAULT NULL,
  `ok` int(11) DEFAULT NULL,
  `pcp` int(11) DEFAULT NULL,
  `pcp_menit` int(11) DEFAULT NULL,
  `sakit` int(11) DEFAULT NULL,
  `sakit_sdokter` int(11) DEFAULT NULL,
  `telat` int(11) DEFAULT NULL,
  `telat_menit` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpayrollddata`
--

LOCK TABLES `tranpayrollddata` WRITE;
/*!40000 ALTER TABLE `tranpayrollddata` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpayrollddata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpurchaseorder`
--

DROP TABLE IF EXISTS `tranpurchaseorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpurchaseorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `big_value` double DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cdue_date` datetime DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_PR` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ctype` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `dtran_delivery` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `ndp` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  `nppn` double DEFAULT NULL,
  `nppn_nilai` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  `small_value` double DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpurchaseorder`
--

LOCK TABLES `tranpurchaseorder` WRITE;
/*!40000 ALTER TABLE `tranpurchaseorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpurchaseorder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpurchaseorder_detail`
--

DROP TABLE IF EXISTS `tranpurchaseorder_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpurchaseorder_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_bill` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csj_vendor` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `ndisc` varchar(255) DEFAULT NULL,
  `nprice` varchar(255) DEFAULT NULL,
  `nqty` varchar(255) DEFAULT NULL,
  `ntotal` varchar(255) DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` varchar(255) DEFAULT NULL,
  `nunit_smallvalue` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpurchaseorder_detail`
--

LOCK TABLES `tranpurchaseorder_detail` WRITE;
/*!40000 ALTER TABLE `tranpurchaseorder_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpurchaseorder_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpurchaseorderbilling`
--

DROP TABLE IF EXISTS `tranpurchaseorderbilling`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpurchaseorderbilling` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cdue_date` datetime DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_PR` varchar(255) DEFAULT NULL,
  `cnbkt_inv` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `dtrantagihan` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `kode` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  `nominalpay` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `ntotalall` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  `ppn` double DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `statusduedate` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totalakhir` double DEFAULT NULL,
  `totalbill` varchar(255) DEFAULT NULL,
  `totaltagihan` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpurchaseorderbilling`
--

LOCK TABLES `tranpurchaseorderbilling` WRITE;
/*!40000 ALTER TABLE `tranpurchaseorderbilling` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpurchaseorderbilling` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpurchaseorderbillingd`
--

DROP TABLE IF EXISTS `tranpurchaseorderbillingd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpurchaseorderbillingd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `ndisc` varchar(255) DEFAULT NULL,
  `nprice` varchar(255) DEFAULT NULL,
  `nqty` varchar(255) DEFAULT NULL,
  `ntotal` varchar(255) DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` varchar(255) DEFAULT NULL,
  `nunit_smallvalue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpurchaseorderbillingd`
--

LOCK TABLES `tranpurchaseorderbillingd` WRITE;
/*!40000 ALTER TABLE `tranpurchaseorderbillingd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpurchaseorderbillingd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpurchasereq`
--

DROP TABLE IF EXISTS `tranpurchasereq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpurchasereq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `capv` int(11) DEFAULT NULL,
  `capv_by` varchar(255) DEFAULT NULL,
  `capv_date` datetime DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nik` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `total` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpurchasereq`
--

LOCK TABLES `tranpurchasereq` WRITE;
/*!40000 ALTER TABLE `tranpurchasereq` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpurchasereq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranpurchasereq_detail`
--

DROP TABLE IF EXISTS `tranpurchasereq_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranpurchasereq_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `capv` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `ndisc` varchar(255) DEFAULT NULL,
  `nik_cname` varchar(255) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranpurchasereq_detail`
--

LOCK TABLES `tranpurchasereq_detail` WRITE;
/*!40000 ALTER TABLE `tranpurchasereq_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranpurchasereq_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialadjust`
--

DROP TABLE IF EXISTS `tranrawmaterialadjust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialadjust` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacckas` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ctype` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `dtranpo` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialadjust`
--

LOCK TABLES `tranrawmaterialadjust` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialadjust` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialadjust` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialadjustd`
--

DROP TABLE IF EXISTS `tranrawmaterialadjustd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialadjustd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialadjustd`
--

LOCK TABLES `tranrawmaterialadjustd` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialadjustd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialadjustd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialin`
--

DROP TABLE IF EXISTS `tranrawmaterialin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_bill` varchar(255) DEFAULT NULL,
  `cnbkt_invpo` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csj_vendor` varchar(100) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `dtranpo` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ncogs` double DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqty_kg` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialin`
--

LOCK TABLES `tranrawmaterialin` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialin` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialind`
--

DROP TABLE IF EXISTS `tranrawmaterialind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqty_kg` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialind`
--

LOCK TABLES `tranrawmaterialind` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialind` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialind` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialout`
--

DROP TABLE IF EXISTS `tranrawmaterialout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `akundesc` varchar(255) DEFAULT NULL,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `ccodeDest` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cworkorder` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `gudangtujuan` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ncogs` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  `produk_wo` varchar(255) DEFAULT NULL,
  `total` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialout`
--

LOCK TABLES `tranrawmaterialout` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialout` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialoutd`
--

DROP TABLE IF EXISTS `tranrawmaterialoutd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialoutd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ccode` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnote` text DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `ncogs` varchar(255) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` varchar(255) DEFAULT NULL,
  `nunit_smallvalue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialoutd`
--

LOCK TABLES `tranrawmaterialoutd` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialoutd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialoutd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialprodreturn`
--

DROP TABLE IF EXISTS `tranrawmaterialprodreturn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialprodreturn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_wo` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `kode_supplier` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ncogs` double DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `total` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialprodreturn`
--

LOCK TABLES `tranrawmaterialprodreturn` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialprodreturn` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialprodreturn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialprodreturnd`
--

DROP TABLE IF EXISTS `tranrawmaterialprodreturnd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialprodreturnd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameproduct` varchar(255) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialprodreturnd`
--

LOCK TABLES `tranrawmaterialprodreturnd` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialprodreturnd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialprodreturnd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialreturn`
--

DROP TABLE IF EXISTS `tranrawmaterialreturn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialreturn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacckas` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cdesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnameProduct` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_po` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ctran` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `cyear` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `dtranpo` datetime DEFAULT NULL,
  `fstatus` varchar(255) DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `ncogs` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `nunit_bigvalue` double DEFAULT NULL,
  `nunit_smallvalue` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialreturn`
--

LOCK TABLES `tranrawmaterialreturn` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialreturn` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialreturn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranrawmaterialreturnd`
--

DROP TABLE IF EXISTS `tranrawmaterialreturnd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranrawmaterialreturnd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `ndisc` double DEFAULT NULL,
  `nprice` double DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `ntotal` double DEFAULT NULL,
  `nunit` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranrawmaterialreturnd`
--

LOCK TABLES `tranrawmaterialreturnd` WRITE;
/*!40000 ALTER TABLE `tranrawmaterialreturnd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranrawmaterialreturnd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranreceive`
--

DROP TABLE IF EXISTS `tranreceive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranreceive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asal` varchar(255) DEFAULT NULL,
  `cball` double DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `ccodedesc` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_kirim` varchar(255) DEFAULT NULL,
  `cnopol` varchar(255) DEFAULT NULL,
  `cnote` varchar(255) DEFAULT NULL,
  `cpenerima` varchar(250) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `cslop` double DEFAULT NULL,
  `cslop_value` double DEFAULT NULL,
  `csupir` varchar(255) DEFAULT NULL,
  `cvia` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudangtujuan` varchar(255) DEFAULT NULL,
  `lastno` int(11) DEFAULT NULL,
  `supirkirim` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranreceive`
--

LOCK TABLES `tranreceive` WRITE;
/*!40000 ALTER TABLE `tranreceive` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranreceive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranreceived`
--

DROP TABLE IF EXISTS `tranreceived`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tranreceived` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cbox` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cgroup` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cproduct` varchar(255) DEFAULT NULL,
  `csatuan` varchar(255) DEFAULT NULL,
  `cslop` double DEFAULT NULL,
  `cslop_value` double DEFAULT NULL,
  `idskat` int(11) DEFAULT NULL,
  `nqty` double DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  `skat` varchar(255) DEFAULT NULL,
  `total` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranreceived`
--

LOCK TABLES `tranreceived` WRITE;
/*!40000 ALTER TABLE `tranreceived` DISABLE KEYS */;
/*!40000 ALTER TABLE `tranreceived` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transale`
--

DROP TABLE IF EXISTS `transale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `akun` varchar(255) DEFAULT NULL,
  `akunll` varchar(255) DEFAULT NULL,
  `caccll` varchar(255) DEFAULT NULL,
  `caccnt` varchar(255) DEFAULT NULL,
  `cmarketing` varchar(250) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `comp_name` varchar(255) DEFAULT NULL,
  `cunit` varchar(250) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `cworkorder` varchar(250) DEFAULT NULL,
  `debet` double DEFAULT NULL,
  `diskon_barang` varchar(255) DEFAULT NULL,
  `diskon_member` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `harga` varchar(255) DEFAULT NULL,
  `hargajual` varchar(255) DEFAULT NULL,
  `id_barang` varchar(255) DEFAULT NULL,
  `id_member` varchar(255) DEFAULT NULL,
  `id_user` varchar(255) DEFAULT NULL,
  `jumlah` varchar(255) DEFAULT NULL,
  `kembali` varchar(255) DEFAULT NULL,
  `kode_customer` varchar(255) DEFAULT NULL,
  `kredit` double DEFAULT NULL,
  `nilai` double DEFAULT NULL,
  `nqty_small` double DEFAULT NULL,
  `total` varchar(255) DEFAULT NULL,
  `totalcicilan` double DEFAULT NULL,
  `user_login` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transale`
--

LOCK TABLES `transale` WRITE;
/*!40000 ALTER TABLE `transale` DISABLE KEYS */;
/*!40000 ALTER TABLE `transale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transalebon`
--

DROP TABLE IF EXISTS `transalebon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transalebon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ServerDate` varchar(255) DEFAULT NULL,
  `akhirtempo` varchar(255) DEFAULT NULL,
  `awaltempo` varchar(255) DEFAULT NULL,
  `bayarawal` varchar(255) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cusername` varchar(255) DEFAULT NULL,
  `lamatempo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transalebon`
--

LOCK TABLES `transalebon` WRITE;
/*!40000 ALTER TABLE `transalebon` DISABLE KEYS */;
/*!40000 ALTER TABLE `transalebon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transalebonpay`
--

DROP TABLE IF EXISTS `transalebonpay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transalebonpay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SisaCicilan` double DEFAULT NULL,
  `TotalDibayar` double DEFAULT NULL,
  `akhirtempo` datetime DEFAULT NULL,
  `bayar` double DEFAULT NULL,
  `caccll_memorial` varchar(50) DEFAULT NULL,
  `ccode` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cnbkt` varchar(255) DEFAULT NULL,
  `cnbkt_memorial` varchar(50) DEFAULT NULL,
  `cnbkt_order` varchar(255) DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `dtran` datetime DEFAULT NULL,
  `gudang` varchar(255) DEFAULT NULL,
  `id_member` varchar(255) DEFAULT NULL,
  `kodebayar` varchar(255) DEFAULT NULL,
  `kodetranurut` varchar(255) DEFAULT NULL,
  `lamatempo` varchar(255) DEFAULT NULL,
  `metodebayar` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `urut` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transalebonpay`
--

LOCK TABLES `transalebonpay` WRITE;
/*!40000 ALTER TABLE `transalebonpay` DISABLE KEYS */;
/*!40000 ALTER TABLE `transalebonpay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaleheader`
--

DROP TABLE IF EXISTS `transaleheader`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaleheader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaleheader`
--

LOCK TABLES `transaleheader` WRITE;
/*!40000 ALTER TABLE `transaleheader` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaleheader` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userlogin`
--

DROP TABLE IF EXISTS `userlogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userlogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ServerDate` datetime DEFAULT NULL,
  `UpdateDate` datetime DEFAULT NULL,
  `cdivisi` varchar(50) DEFAULT NULL,
  `cname` varchar(100) DEFAULT NULL,
  `cpass` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `cstatus` int(11) DEFAULT NULL,
  `cuser` varchar(50) DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userlogin`
--

LOCK TABLES `userlogin` WRITE;
/*!40000 ALTER TABLE `userlogin` DISABLE KEYS */;
INSERT INTO `userlogin` VALUES (1,NULL,NULL,'IT','Administrator','YWRtaW4=','2026-10-31 15:22:24',1,'admin','EMP001');
/*!40000 ALTER TABLE `userlogin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userloginkeys`
--

DROP TABLE IF EXISTS `userloginkeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userloginkeys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `idform` int(11) DEFAULT NULL,
  `keys` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userloginkeys`
--

LOCK TABLES `userloginkeys` WRITE;
/*!40000 ALTER TABLE `userloginkeys` DISABLE KEYS */;
INSERT INTO `userloginkeys` VALUES (1,1,1,'MTsxOzE7MTsxOzE=',NULL),(2,1,2,'MTsxOzE7MTsxOzE=',NULL),(3,1,3,'MTsxOzE7MTsxOzE=',NULL),(4,1,4,'MTsxOzE7MTsxOzE=',NULL),(5,1,5,'MTsxOzE7MTsxOzE=',NULL),(6,1,6,'MTsxOzE7MTsxOzE=',NULL),(7,1,7,'MTsxOzE7MTsxOzE=',NULL),(8,1,8,'MTsxOzE7MTsxOzE=',NULL),(9,1,9,'MTsxOzE7MTsxOzE=',NULL),(10,1,10,'MTsxOzE7MTsxOzE=',NULL),(11,1,11,'MTsxOzE7MTsxOzE=',NULL),(12,1,12,'MTsxOzE7MTsxOzE=',NULL),(13,1,13,'MTsxOzE7MTsxOzE=',NULL),(14,1,14,'MTsxOzE7MTsxOzE=',NULL),(15,1,15,'MTsxOzE7MTsxOzE=',NULL),(16,1,16,'MTsxOzE7MTsxOzE=',NULL),(17,1,17,'MTsxOzE7MTsxOzE=',NULL),(18,1,18,'MTsxOzE7MTsxOzE=',NULL),(19,1,19,'MTsxOzE7MTsxOzE=',NULL),(20,1,20,'MTsxOzE7MTsxOzE=',NULL),(21,1,21,'MTsxOzE7MTsxOzE=',NULL),(22,1,22,'MTsxOzE7MTsxOzE=',NULL),(23,1,23,'MTsxOzE7MTsxOzE=',NULL),(24,1,24,'MTsxOzE7MTsxOzE=',NULL),(25,1,25,'MTsxOzE7MTsxOzE=',NULL),(26,1,26,'MTsxOzE7MTsxOzE=',NULL),(27,1,27,'MTsxOzE7MTsxOzE=',NULL);
/*!40000 ALTER TABLE `userloginkeys` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-31 17:15:27
