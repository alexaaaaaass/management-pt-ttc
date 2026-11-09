-- Database Schema for PROGRAM INDIGAMA (Microsoft SQL Server / Express)
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'db_printing')
BEGIN
    CREATE DATABASE [db_printing];
END
GO
USE [db_printing];
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='AbsensiLembur' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[AbsensiLembur] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [Tanggal] [datetime] NULL,
  [cket] [nvarchar](255) NULL,
  [cnamnt] [float] NULL,
  [ctotallembur] [nvarchar](255) NULL,
  [jamakhir] [nvarchar](255) NULL,
  [jamakhir_set] [nvarchar](50) NULL,
  [jamawal] [nvarchar](255) NULL,
  [jamawal_set] [nvarchar](50) NULL,
  [karyawan] [nvarchar](255) NULL,
  [nik] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='AbsensiRekap' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[AbsensiRekap] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cidk] [nvarchar](255) NULL,
  [cjabatan] [nvarchar](255) NULL,
  [clockin] [nvarchar](255) NULL,
  [clockout] [nvarchar](255) NULL,
  [early] [nvarchar](255) NULL,
  [late] [nvarchar](255) NULL,
  [lembur_menit] [nvarchar](255) NULL,
  [name] [nvarchar](255) NULL,
  [offduty] [nvarchar](255) NULL,
  [onduty] [nvarchar](255) NULL,
  [tanggal] [nvarchar](255) NULL,
  [timetable] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='AppUpdate' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[AppUpdate] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [app_type] [nvarchar](50) NULL,
  [app_update] [nvarchar](50) NULL,
  [datecreated] [datetime] NULL,
  [status] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='BOMProduct' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[BOMProduct] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cplot] [float] NULL,
  [cproduct] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='BOMProductCost' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[BOMProductCost] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cinterval] [int] NULL,
  [cost] [nvarchar](255) NULL,
  [cprice] [float] NULL,
  [kode_cost] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='BOMProductD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[BOMProductD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='FormBonusKaryawan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[FormBonusKaryawan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cname] [nvarchar](255) NULL,
  [cnik] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [namnt] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='FormOperasional' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[FormOperasional] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cdevisi] [nvarchar](255) NULL,
  [ckeperluan] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cprice] [float] NULL,
  [cprice_total] [float] NULL,
  [csatuan] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL,
  [nqty] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='FormOperasionalD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[FormOperasionalD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ckeperluan] [nvarchar](255) NULL,
  [cprice] [float] NULL,
  [cprice_total] [float] NULL,
  [csatuan] [nvarchar](255) NULL,
  [nqty] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='FormPenawaran' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[FormPenawaran] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [dtran_expired] [datetime] NULL,
  [kode_customer] [nvarchar](255) NULL,
  [npengerjaan] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nprice_total] [float] NULL,
  [nqty] [float] NULL,
  [nspek] [nvarchar](255) NULL,
  [nukuran] [nvarchar](255) NULL,
  [nunit] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='FormPenawaranD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[FormPenawaranD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cnameproduct] [nvarchar](255) NULL,
  [npengerjaan] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nprice_total] [float] NULL,
  [nqty] [float] NULL,
  [nspek] [nvarchar](255) NULL,
  [nukuran] [nvarchar](255) NULL,
  [nunit] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='Hppbahan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[Hppbahan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cogs] [float] NULL,
  [qty] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MAccountClass' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MAccountClass] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [caccnt_class] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [fstatus] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MCompany' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MCompany] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [addr] [nvarchar](255) NULL,
  [city] [nvarchar](255) NULL,
  [districts] [nvarchar](255) NULL,
  [fax] [nvarchar](255) NULL,
  [ket_inv] [nvarchar](255) NULL,
  [mail] [nvarchar](255) NULL,
  [name] [nvarchar](255) NULL,
  [postalcode] [nvarchar](255) NULL,
  [rek1_an] [nvarchar](255) NULL,
  [rek1_jenis] [nvarchar](255) NULL,
  [rek1_no] [nvarchar](255) NULL,
  [rek2_an] [nvarchar](255) NULL,
  [rek2_jenis] [nvarchar](255) NULL,
  [rek2_no] [nvarchar](255) NULL,
  [telp] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MCost' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MCost] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cket] [nvarchar](255) NULL,
  [cost] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [lastno] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MCuti' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MCuti] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cdesc] [nvarchar](255) NULL,
  [cidc] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MDefault' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MDefault] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cket] [nvarchar](255) NULL,
  [code] [nvarchar](50) NULL,
  [default_name] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MErrorProduction' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MErrorProduction] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cname] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [lastno] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MGroup' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MGroup] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cket] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MMenu' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MMenu] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [name] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MMesin' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MMesin] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cgroup] [nvarchar](50) NULL,
  [cname] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cproduct] [nvarchar](50) NULL,
  [cunit] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [kstatus] [nvarchar](255) NULL,
  [qty] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MMesinInv' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MMesinInv] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [idmesin] [nvarchar](255) NULL,
  [mesin] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MMetodePembayaran' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MMetodePembayaran] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [KodeBayar] [nvarchar](255) NULL,
  [MetodeBayar] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MOperasional' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MOperasional] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cdesc] [nvarchar](255) NULL,
  [id_op] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [nstatus] [int] NULL,
  [username] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MPriceBorongan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MPriceBorongan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [nperiod] [datetime] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MPriceBoronganTunj' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MPriceBoronganTunj] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [nperiod] [datetime] NULL,
  [nprice_hadir] [float] NULL,
  [nprice_lain] [float] NULL,
  [nprice_makan] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MStepProduction' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MStepProduction] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cfinal_process] [int] NULL,
  [cket] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [idmesin] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [mesin] [nvarchar](255) NULL,
  [urut] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='MaterialStock' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[MaterialStock] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cr] [float] NULL,
  [cunit] [nvarchar](255) NULL,
  [db] [float] NULL,
  [dtran] [datetime] NULL,
  [kstatus] [nvarchar](255) NULL,
  [note] [nvarchar](255) NULL,
  [qty] [float] NULL,
  [terpakai] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PBorongan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PBorongan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cname] [nvarchar](255) NULL,
  [cnik] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [nilaigaji] [float] NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [tipe] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PBoxPacking' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PBoxPacking] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cket] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_order] [nvarchar](255) NULL,
  [cnik] [nvarchar](50) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csatuan] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [cworkorder] [nvarchar](255) NULL,
  [deadline] [datetime] NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [karyawan] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [nqty] [float] NULL,
  [nqty_box] [float] NULL,
  [nqty_small] [float] NULL,
  [qtyfinish] [float] NULL,
  [satuanproduk] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PBoxPackingD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PBoxPackingD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cverifikasi] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PCustomerPO' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PCustomerPO] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cgroup_bahan] [nvarchar](255) NULL,
  [cket] [nvarchar](255) NULL,
  [cmarketing] [nvarchar](255) NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](500) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cproduct_bahan] [nvarchar](255) NULL,
  [deadline] [datetime] NULL,
  [dtran] [datetime] NULL,
  [kode] [int] NULL,
  [kode_customer] [nvarchar](255) NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PCustomerPOD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PCustomerPOD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [bahan] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cket] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [deadline] [datetime] NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [jml] [float] NULL,
  [kode] [nvarchar](255) NULL,
  [marketing] [nvarchar](255) NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [produk] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PProdErr' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PProdErr] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cerr_name] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [nqty_fail] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PProdSteps' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PProdSteps] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cusername] [nvarchar](255) NULL,
  [dtran] [datetime] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PWorkOrder' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PWorkOrder] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgood] [int] NULL,
  [cgood_pcs] [int] NULL,
  [cgood_unit] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [ckeperluan] [nvarchar](255) NULL,
  [cket] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cnik] [nvarchar](255) NULL,
  [cprice] [float] NULL,
  [cprice_total] [float] NULL,
  [cproduct] [nvarchar](255) NULL,
  [cproduct_bahan] [nvarchar](255) NULL,
  [csatuan] [nvarchar](255) NULL,
  [cstep_kode] [nvarchar](255) NULL,
  [cstep_name] [nvarchar](255) NULL,
  [ctype] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cwaste] [int] NULL,
  [cwaste_pcs] [int] NULL,
  [cworkorder] [nvarchar](255) NULL,
  [deadline] [datetime] NULL,
  [dtran] [datetime] NULL,
  [good] [nvarchar](255) NULL,
  [karyawan] [nvarchar](255) NULL,
  [ketpo] [nvarchar](255) NULL,
  [kode] [int] NULL,
  [kode_customer] [nvarchar](50) NULL,
  [npengerjaan] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nprice_total] [float] NULL,
  [nqty] [float] NULL,
  [nqty_small] [float] NULL,
  [nqty_total] [float] NULL,
  [nspek] [nvarchar](255) NULL,
  [nukuran] [nvarchar](255) NULL,
  [nunit] [nvarchar](255) NULL,
  [operator] [nvarchar](255) NULL,
  [waste] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PWorkOrderStep' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PWorkOrderStep] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cnbkt] [nvarchar](255) NULL,
  [cstep_kode] [nvarchar](255) NULL,
  [cstep_name] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [kode_customer] [nvarchar](255) NULL,
  [urut] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PayrollBorongan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PayrollBorongan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cname] [nvarchar](255) NULL,
  [cnik] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [nprice_hadir] [int] NULL,
  [nprice_lain] [int] NULL,
  [nprice_makan] [int] NULL,
  [pack] [int] NULL,
  [pack_gaji] [int] NULL,
  [protol] [int] NULL,
  [protol_gaji] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='PayrollD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[PayrollD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [A] [nvarchar](255) NULL,
  [CUTI] [nvarchar](255) NULL,
  [ClockIn] [nvarchar](255) NULL,
  [ClockOut] [nvarchar](255) NULL,
  [Early] [nvarchar](255) NULL,
  [HOLIDAY] [nvarchar](255) NULL,
  [IJIN] [nvarchar](255) NULL,
  [IJIN_MENIT] [nvarchar](255) NULL,
  [LEMBUR] [nvarchar](255) NULL,
  [LEMBUR_MENIT] [nvarchar](255) NULL,
  [LIBUR] [nvarchar](255) NULL,
  [Late] [nvarchar](255) NULL,
  [Name] [nvarchar](255) NULL,
  [OK] [nvarchar](255) NULL,
  [OffDuty] [nvarchar](255) NULL,
  [OnDuty] [nvarchar](255) NULL,
  [PCP] [nvarchar](255) NULL,
  [PCP_MENIT] [nvarchar](255) NULL,
  [SAKIT] [nvarchar](255) NULL,
  [SAKIT_SDOKTER] [nvarchar](255) NULL,
  [TELAT] [nvarchar](255) NULL,
  [TELAT_MENIT] [nvarchar](255) NULL,
  [Tanggal] [nvarchar](255) NULL,
  [cidk] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='ProductStock' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[ProductStock] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cr] [float] NULL,
  [db] [float] NULL,
  [dtran] [datetime] NULL,
  [mtcr] [float] NULL,
  [mtdb] [float] NULL,
  [note] [nvarchar](255) NULL,
  [qty] [float] NULL,
  [qtyawal] [float] NULL,
  [qtydebet] [float] NULL,
  [qtykredit] [float] NULL,
  [satuan_in] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='StockOpname' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[StockOpname] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [total] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranDeliveryGudang' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranDeliveryGudang] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [asal] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [ccodedesc] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_inv] [nvarchar](255) NULL,
  [cnopol] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [csupir] [nvarchar](255) NULL,
  [cvia] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudangtujuan] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [totalpack] [float] NULL,
  [tujuan] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranDeliveryGudangD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranDeliveryGudangD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cbox] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csatuan] [nvarchar](255) NULL,
  [cslop_value] [float] NULL,
  [nqty] [int] NULL,
  [nqty_small] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranDeliveryOrder' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranDeliveryOrder] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_inv] [nvarchar](255) NULL,
  [cnopol] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [csupir] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [cvia] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [kode_customer] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [totalpack] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranDeliveryOrderD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranDeliveryOrderD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cbox] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [cnbkt_wo] [nvarchar](50) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csatuan] [nvarchar](255) NULL,
  [csatuan_small] [nvarchar](255) NULL,
  [nqty] [int] NULL,
  [nqty_box] [int] NULL,
  [nqty_small] [float] NULL,
  [produkalias] [nvarchar](250) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranKas' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranKas] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [acc_bank] [nvarchar](255) NULL,
  [acc_kas] [nvarchar](255) NULL,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [idmesin] [int] NULL,
  [kode] [int] NULL,
  [kode_customer] [nvarchar](50) NULL,
  [lastno] [int] NULL,
  [mesin] [nvarchar](255) NULL,
  [namnt] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranKasBank' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranKasBank] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [Name] [nvarchar](255) NULL,
  [a] [int] NULL,
  [acc_bank] [nvarchar](255) NULL,
  [acc_kas] [nvarchar](255) NULL,
  [bank] [nvarchar](255) NULL,
  [bank_an] [nvarchar](255) NULL,
  [bank_rekening] [nvarchar](255) NULL,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cidk] [nvarchar](255) NULL,
  [cjabatan] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [idmesin] [int] NULL,
  [kode] [int] NULL,
  [kode_customer] [nvarchar](50) NULL,
  [lastno] [int] NULL,
  [mesin] [nvarchar](255) NULL,
  [namnt] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL,
  [ok] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranKasPinjaman' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranKasPinjaman] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [acc_bank] [nvarchar](255) NULL,
  [acc_kas] [nvarchar](255) NULL,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_form] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [namnt] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranMemorial' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranMemorial] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [acc_bank] [nvarchar](255) NULL,
  [acc_kas] [nvarchar](255) NULL,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [customerll] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [idmesin] [int] NULL,
  [idmesinll] [int] NULL,
  [kode_customer] [nvarchar](50) NULL,
  [kode_customerll] [nvarchar](50) NULL,
  [lastno] [int] NULL,
  [mesin] [nvarchar](255) NULL,
  [namnt] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranOpOut' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranOpOut] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [acc_bank] [nvarchar](255) NULL,
  [acc_kas] [nvarchar](255) NULL,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cjenis] [nvarchar](255) NULL,
  [cjenis_name] [nvarchar](255) NULL,
  [ckendaraan] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [codometer] [nvarchar](255) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [idmesin] [int] NULL,
  [kode] [int] NULL,
  [lastno] [int] NULL,
  [mesin] [nvarchar](255) NULL,
  [namnt] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPayment' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPayment] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [cbank] [nvarchar](255) NULL,
  [cbank_an] [nvarchar](255) NULL,
  [cbank_rekening] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cdue_date] [datetime] NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_PO] [nvarchar](255) NULL,
  [cnbkt_PR] [nvarchar](255) NULL,
  [cnbkt_bill] [nvarchar](255) NULL,
  [cnbkt_inv] [nvarchar](255) NULL,
  [cnbktbill] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ctype] [nvarchar](255) NULL,
  [curs] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cusername_update] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [dupdat] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [method] [nvarchar](255) NULL,
  [namnt] [float] NULL,
  [nominalpay] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [nqtyin] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [pay] [float] NULL,
  [ppn] [float] NULL,
  [status] [nvarchar](255) NULL,
  [statusduedate] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL,
  [total] [float] NULL,
  [totalnilai] [float] NULL,
  [tpay] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPayment_detail' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPayment_detail] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [cbank] [nvarchar](255) NULL,
  [cbank_an] [nvarchar](255) NULL,
  [cbank_rekening] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cdue_date] [datetime] NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_PO] [nvarchar](255) NULL,
  [cnbktbill] [nvarchar](255) NULL,
  [curs] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cusername_update] [nvarchar](255) NULL,
  [dtran] [nvarchar](255) NULL,
  [dupdat] [nvarchar](255) NULL,
  [fstatus] [nvarchar](255) NULL,
  [id_cnbkt] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [method] [nvarchar](255) NULL,
  [namnt] [float] NULL,
  [ndisc] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [ntotalall] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [ppn] [float] NULL,
  [supplier] [nvarchar](255) NULL,
  [tglpo] [datetime] NULL,
  [total] [float] NULL,
  [totalbayar] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPayroll' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPayroll] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [a] [float] NULL,
  [bonus] [float] NULL,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cidk] [nvarchar](255) NULL,
  [cjab] [nvarchar](50) NULL,
  [cjabatan] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnik] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cpayrol] [nvarchar](255) NULL,
  [cperiode] [nvarchar](255) NULL,
  [cuti] [float] NULL,
  [dend] [nvarchar](255) NULL,
  [dstart] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gp] [float] NULL,
  [gp_lembur] [float] NULL,
  [lastno] [int] NULL,
  [lembur] [float] NULL,
  [name] [nvarchar](255) NULL,
  [namnt] [float] NULL,
  [ok] [float] NULL,
  [packing_gaji] [float] NULL,
  [packing_qty] [float] NULL,
  [pcp] [float] NULL,
  [pot_a] [float] NULL,
  [pot_bpjskerja] [float] NULL,
  [pot_lain] [float] NULL,
  [pot_pcp] [float] NULL,
  [pot_pinjaman] [float] NULL,
  [pot_pph21] [float] NULL,
  [pot_sakit] [float] NULL,
  [pot_telat] [float] NULL,
  [protol_gaji] [float] NULL,
  [protol_qty] [float] NULL,
  [sakit] [float] NULL,
  [statusbonus] [nvarchar](255) NULL,
  [telat] [float] NULL,
  [total] [float] NULL,
  [tunj_hadir] [float] NULL,
  [tunj_lain] [float] NULL,
  [tunj_makan] [float] NULL,
  [tunjangan] [float] NULL,
  [tunjangan_komp] [float] NULL,
  [tunjangan_lain] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPayrollD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPayrollD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [Name] [nvarchar](255) NULL,
  [a] [int] NULL,
  [bonus] [int] NULL,
  [cidk] [nvarchar](255) NULL,
  [cjabatan] [nvarchar](255) NULL,
  [cperiode] [nvarchar](255) NULL,
  [cuti] [int] NULL,
  [gp] [int] NULL,
  [gp_lembur] [int] NULL,
  [lembur] [int] NULL,
  [ok] [int] NULL,
  [pcp] [int] NULL,
  [pot_a] [int] NULL,
  [pot_bpjskerja] [int] NULL,
  [pot_lain] [int] NULL,
  [pot_pcp] [int] NULL,
  [pot_pinjaman] [int] NULL,
  [pot_pph21] [int] NULL,
  [pot_sakit] [int] NULL,
  [pot_telat] [int] NULL,
  [sakit] [int] NULL,
  [telat] [int] NULL,
  [total] [int] NULL,
  [tunjangan] [int] NULL,
  [tunjangan_komp] [int] NULL,
  [tunjangan_lain] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPayrollDBorongan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPayrollDBorongan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cidk] [nvarchar](255) NULL,
  [dtran] [nvarchar](255) NULL,
  [name] [nvarchar](255) NULL,
  [packing_gaji] [float] NULL,
  [packing_qty] [float] NULL,
  [protol_gaji] [float] NULL,
  [protol_qty] [float] NULL,
  [total] [float] NULL,
  [tunj_hadir] [float] NULL,
  [tunj_lain] [float] NULL,
  [tunj_makan] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPayrollDData' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPayrollDData] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [Name] [nvarchar](255) NULL,
  [Tanggal] [nvarchar](255) NULL,
  [a] [int] NULL,
  [cidk] [nvarchar](255) NULL,
  [clockin] [nvarchar](255) NULL,
  [clockout] [nvarchar](255) NULL,
  [cuti] [int] NULL,
  [early] [nvarchar](255) NULL,
  [holiday] [int] NULL,
  [ijin] [int] NULL,
  [ijin_menit] [int] NULL,
  [late] [nvarchar](255) NULL,
  [lembur] [int] NULL,
  [lembur_menit] [int] NULL,
  [libur] [int] NULL,
  [ok] [int] NULL,
  [pcp] [int] NULL,
  [pcp_menit] [int] NULL,
  [sakit] [int] NULL,
  [sakit_sdokter] [int] NULL,
  [telat] [int] NULL,
  [telat_menit] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPurchaseOrder' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPurchaseOrder] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [big_value] [float] NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cdue_date] [datetime] NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_PR] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ctype] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [dtran_delivery] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ndisc] [float] NULL,
  [ndp] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL,
  [nppn] [float] NULL,
  [nppn_nilai] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL,
  [small_value] [float] NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPurchaseOrderBilling' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPurchaseOrderBilling] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cdue_date] [datetime] NULL,
  [cgroup] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_PR] [nvarchar](255) NULL,
  [cnbkt_inv] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [dtrantagihan] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ndisc] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL,
  [nominalpay] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [ntotalall] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL,
  [ppn] [float] NULL,
  [status] [nvarchar](255) NULL,
  [statusduedate] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL,
  [total] [float] NULL,
  [totalakhir] [float] NULL,
  [totalbill] [nvarchar](255) NULL,
  [totaltagihan] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPurchaseOrderBillingD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPurchaseOrderBillingD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [fstatus] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [ndisc] [nvarchar](255) NULL,
  [nprice] [nvarchar](255) NULL,
  [nqty] [nvarchar](255) NULL,
  [ntotal] [nvarchar](255) NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [nvarchar](255) NULL,
  [nunit_smallvalue] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPurchaseOrder_detail' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPurchaseOrder_detail] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_bill] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csj_vendor] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [ndisc] [nvarchar](255) NULL,
  [nprice] [nvarchar](255) NULL,
  [nqty] [nvarchar](255) NULL,
  [ntotal] [nvarchar](255) NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [nvarchar](255) NULL,
  [nunit_smallvalue] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPurchaseReq' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPurchaseReq] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [capv] [int] NULL,
  [capv_by] [nvarchar](255) NULL,
  [capv_date] [datetime] NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ndisc] [float] NULL,
  [nik] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL,
  [total] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranPurchaseReq_detail' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranPurchaseReq_detail] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [capv] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [ndisc] [nvarchar](255) NULL,
  [nik_cname] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialAdjust' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialAdjust] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cacckas] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ctype] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [dtranpo] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL,
  [total] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialAdjustD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialAdjustD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ndisc] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialIn' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialIn] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_bill] [nvarchar](255) NULL,
  [cnbkt_invpo] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csj_vendor] [nvarchar](100) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [dtranpo] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ncogs] [float] NULL,
  [ndisc] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [nqty_kg] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialInD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialInD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [ndisc] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [nqty_kg] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialOut' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialOut] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [akundesc] [nvarchar](255) NULL,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [ccodeDest] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cworkorder] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [gudangtujuan] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ncogs] [float] NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL,
  [produk_wo] [nvarchar](255) NULL,
  [total] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialOutD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialOutD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnote] [nvarchar](max) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [fstatus] [nvarchar](255) NULL,
  [ncogs] [nvarchar](255) NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [nvarchar](255) NULL,
  [nunit_smallvalue] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialProdReturn' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialProdReturn] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_wo] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ncogs] [float] NULL,
  [ndisc] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL,
  [supplier] [nvarchar](255) NULL,
  [total] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialProdReturnD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialProdReturnD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cnameproduct] [nvarchar](255) NULL,
  [ndisc] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialReturn' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialReturn] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cacckas] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ctran] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [cyear] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [dtranpo] [datetime] NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ncogs] [float] NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL,
  [total] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranRawMaterialReturnD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranRawMaterialReturnD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [ndisc] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranReceive' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranReceive] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [asal] [nvarchar](255) NULL,
  [cball] [float] NULL,
  [ccode] [nvarchar](255) NULL,
  [ccodedesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_kirim] [nvarchar](255) NULL,
  [cnopol] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cpenerima] [nvarchar](250) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csatuan] [nvarchar](255) NULL,
  [cslop] [float] NULL,
  [cslop_value] [float] NULL,
  [csupir] [nvarchar](255) NULL,
  [cvia] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudangtujuan] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [supirkirim] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranReceiveD' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranReceiveD] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cbox] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csatuan] [nvarchar](255) NULL,
  [cslop] [float] NULL,
  [cslop_value] [float] NULL,
  [idskat] [int] NULL,
  [nqty] [float] NULL,
  [nqty_small] [float] NULL,
  [skat] [nvarchar](255) NULL,
  [total] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranSale' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranSale] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [akun] [nvarchar](255) NULL,
  [akunll] [nvarchar](255) NULL,
  [caccll] [nvarchar](255) NULL,
  [caccnt] [nvarchar](255) NULL,
  [cmarketing] [nvarchar](250) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [comp_name] [nvarchar](255) NULL,
  [cunit] [nvarchar](250) NULL,
  [customer] [nvarchar](255) NULL,
  [cworkorder] [nvarchar](250) NULL,
  [debet] [float] NULL,
  [diskon_barang] [nvarchar](255) NULL,
  [diskon_member] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [harga] [nvarchar](255) NULL,
  [hargajual] [nvarchar](255) NULL,
  [id_barang] [nvarchar](255) NULL,
  [id_member] [nvarchar](255) NULL,
  [id_user] [nvarchar](255) NULL,
  [jumlah] [nvarchar](255) NULL,
  [kembali] [nvarchar](255) NULL,
  [kode_customer] [nvarchar](255) NULL,
  [kredit] [float] NULL,
  [nilai] [float] NULL,
  [nqty_small] [float] NULL,
  [total] [nvarchar](255) NULL,
  [totalcicilan] [float] NULL,
  [user_login] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranSaleBon' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranSaleBon] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ServerDate] [nvarchar](255) NULL,
  [akhirtempo] [nvarchar](255) NULL,
  [awaltempo] [nvarchar](255) NULL,
  [bayarawal] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [lamatempo] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranSaleBonPay' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranSaleBonPay] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [SisaCicilan] [float] NULL,
  [TotalDibayar] [float] NULL,
  [akhirtempo] [datetime] NULL,
  [bayar] [float] NULL,
  [caccll_memorial] [nvarchar](50) NULL,
  [ccode] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_memorial] [nvarchar](50) NULL,
  [cnbkt_order] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [gudang] [nvarchar](255) NULL,
  [id_member] [nvarchar](255) NULL,
  [kodebayar] [nvarchar](255) NULL,
  [kodetranurut] [nvarchar](255) NULL,
  [lamatempo] [nvarchar](255) NULL,
  [metodebayar] [nvarchar](255) NULL,
  [name] [nvarchar](255) NULL,
  [total] [float] NULL,
  [urut] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='TranSaleHeader' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[TranSaleHeader] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [kode] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='UserLoginKeys' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[UserLoginKeys] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [id_user] [int] NULL,
  [idform] [int] NULL,
  [keys] [nvarchar](255) NULL,
  [name] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='cuti_ambil' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[cuti_ambil] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cdesc] [nvarchar](255) NULL,
  [cdoc] [nvarchar](255) NULL,
  [cidk] [nvarchar](255) NULL,
  [cjamakhir] [nvarchar](255) NULL,
  [cjamawal] [nvarchar](255) NULL,
  [cket] [nvarchar](255) NULL,
  [ctglcuti] [datetime] NULL,
  [karyawan] [nvarchar](255) NULL,
  [nidca] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='formpinjaman' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[formpinjaman] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ccode] [nvarchar](15) NULL,
  [cname] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnik] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [cwaktu_bulan] [float] NULL,
  [dtran] [datetime] NULL,
  [lastno] [int] NULL,
  [namnt] [float] NULL,
  [namnt_cicilan] [float] NULL,
  [status] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='holidays' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[holidays] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cnote] [nvarchar](255) NULL,
  [ctgl] [datetime] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='maccount' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[maccount] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [NCredit] [float] NULL,
  [NDebet] [float] NULL,
  [NNominal] [float] NULL,
  [cPeriod] [nvarchar](255) NULL,
  [caccnt] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_po] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [csj_vendor] [nvarchar](255) NULL,
  [cusername] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [fAccount] [nvarchar](255) NULL,
  [fstatus] [nvarchar](255) NULL,
  [gudang] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mcustomer' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mcustomer] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [addr] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [ctelp] [nvarchar](255) NULL,
  [kode_customer] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [username] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mdepartemen' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mdepartemen] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cdept] [nvarchar](255) NULL,
  [cjabatan] [nvarchar](255) NULL,
  [ctunj] [float] NULL,
  [cusername] [nvarchar](255) NULL,
  [id_dept] [nvarchar](255) NULL,
  [id_jab] [nvarchar](255) NULL,
  [lastno] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mgudang' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mgudang] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [bahan] [nvarchar](255) NULL,
  [caddr] [nvarchar](255) NULL,
  [ccity] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesa] [nvarchar](255) NULL,
  [cdue_date] [datetime] NULL,
  [cfax] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [chead] [nvarchar](255) NULL,
  [chead_acc] [nvarchar](255) NULL,
  [ckecamatan] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cnbkt_PR] [nvarchar](255) NULL,
  [cnbkt_delivery] [nvarchar](255) NULL,
  [cnbkt_order] [nvarchar](255) NULL,
  [cnote] [nvarchar](255) NULL,
  [coperator] [nvarchar](255) NULL,
  [cpostalcode] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cproses] [nvarchar](255) NULL,
  [ctelp] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [ndisc] [float] NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [nqty_box] [float] NULL,
  [nqty_box_waste] [float] NULL,
  [nqty_small] [float] NULL,
  [nqty_small_waste] [float] NULL,
  [nqtyin] [float] NULL,
  [ntotal] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunitin] [nvarchar](255) NULL,
  [supplier] [nvarchar](255) NULL,
  [tglout] [datetime] NULL,
  [username] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mjabatan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mjabatan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cdept] [nvarchar](255) NULL,
  [cjabatan] [nvarchar](255) NULL,
  [ctunj] [float] NULL,
  [id_dept] [nvarchar](255) NULL,
  [id_jab] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [username] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mkaryawan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mkaryawan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ServerDate] [datetime] NULL,
  [caddr] [nvarchar](255) NULL,
  [caddr_ktp] [nvarchar](255) NULL,
  [cbank] [nvarchar](255) NULL,
  [cbank_an] [nvarchar](255) NULL,
  [cbank_number] [nvarchar](255) NULL,
  [cborongan] [nvarchar](255) NULL,
  [cbpjs_kesehatan] [nvarchar](255) NULL,
  [cbpjs_name] [nvarchar](255) NULL,
  [ccity] [nvarchar](255) NULL,
  [ccity_ktp] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cdept] [nvarchar](255) NULL,
  [cdesa] [nvarchar](255) NULL,
  [cdesa_ktp] [nvarchar](255) NULL,
  [cgander] [nvarchar](255) NULL,
  [cid_finger] [nvarchar](255) NULL,
  [ckecamatan] [nvarchar](255) NULL,
  [ckecamatan_ktp] [nvarchar](255) NULL,
  [cktp] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cpayrol] [nvarchar](255) NULL,
  [cpostalcode] [nvarchar](255) NULL,
  [cpostalcode_ktp] [nvarchar](255) NULL,
  [creligy] [nvarchar](255) NULL,
  [cresign] [datetime] NULL,
  [csalary] [nvarchar](255) NULL,
  [ctelp] [nvarchar](255) NULL,
  [ctgl_lahir] [datetime] NULL,
  [ctgl_masuk] [datetime] NULL,
  [ctunjintensif] [int] NULL,
  [ctunjjab] [int] NULL,
  [ctunjkomp] [int] NULL,
  [jabatan] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [namajab] [nvarchar](255) NULL,
  [nik] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mpayment' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mpayment] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cdesc] [nvarchar](255) NULL,
  [id_pay] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mpayroll' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mpayroll] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cdesc] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [value1] [int] NULL,
  [value2] [int] NULL,
  [value3] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mproduk' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mproduk] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [CDProduct] [nvarchar](255) NULL,
  [Fstatus] [nvarchar](255) NULL,
  [NPrPurc] [float] NULL,
  [NPrSell] [float] NULL,
  [NQty] [float] NULL,
  [ccode] [nvarchar](255) NULL,
  [cdesc] [nvarchar](255) NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [csale] [int] NULL,
  [cunit] [nvarchar](255) NULL,
  [customer] [nvarchar](255) NULL,
  [cwarningstock] [nvarchar](255) NULL,
  [kode_customer] [nvarchar](255) NULL,
  [n1] [float] NULL,
  [n2] [float] NULL,
  [n3] [float] NULL,
  [n4] [float] NULL,
  [n5] [float] NULL,
  [n6] [float] NULL,
  [ncogs] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [nvarchar](255) NULL,
  [nunit_smallvalue] [nvarchar](255) NULL,
  [qty] [float] NULL,
  [t1] [nvarchar](255) NULL,
  [t2] [nvarchar](255) NULL,
  [t3] [nvarchar](255) NULL,
  [t4] [nvarchar](255) NULL,
  [t5] [nvarchar](255) NULL,
  [t6] [nvarchar](255) NULL,
  [t7] [nvarchar](255) NULL,
  [unit] [nvarchar](255) NULL,
  [username] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mprodukjual' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mprodukjual] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [NPrPurc] [float] NULL,
  [NPrSell] [float] NULL,
  [big_value] [float] NULL,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [cunit] [nvarchar](255) NULL,
  [jenis] [nvarchar](255) NULL,
  [small_value] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mprodukwarning' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mprodukwarning] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mptkp' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mptkp] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [golongan] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='msatuan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[msatuan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [Name] [nvarchar](255) NULL,
  [big_value] [float] NULL,
  [cbox_no] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cnameProduct] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [cworkorder] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [id_turunan] [nvarchar](255) NULL,
  [kolom] [nvarchar](255) NULL,
  [namanya] [nvarchar](255) NULL,
  [namasatuan] [nvarchar](250) NULL,
  [nqty] [float] NULL,
  [nqty_small] [float] NULL,
  [satuankonversi] [nvarchar](255) NULL,
  [satuanproduk] [nvarchar](255) NULL,
  [small_value] [float] NULL,
  [terkecil] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mskat' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mskat] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cname] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [tgl] [datetime] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='msupplier' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[msupplier] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [addr] [nvarchar](255) NULL,
  [bank] [nvarchar](255) NULL,
  [bank_number] [nvarchar](255) NULL,
  [bank_owner] [nvarchar](255) NULL,
  [cjenis] [nvarchar](255) NULL,
  [cname] [nvarchar](255) NULL,
  [cs] [nvarchar](255) NULL,
  [ctelp] [nvarchar](255) NULL,
  [kode_supplier] [nvarchar](255) NULL,
  [lastno] [int] NULL,
  [username] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='mtoko' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[mtoko] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [alamat] [nvarchar](255) NULL,
  [toko] [nvarchar](255) NULL,
  [username] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='penjualan' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[penjualan] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [TotalBarang] [nvarchar](255) NULL,
  [TotalDijual] [nvarchar](255) NULL,
  [TotalReturn] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='pworkordercost' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[pworkordercost] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cinterval] [float] NULL,
  [cost] [nvarchar](255) NULL,
  [cprice] [float] NULL,
  [kode_cost] [nvarchar](255) NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='pworkorderd' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[pworkorderd] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [cgroup] [nvarchar](255) NULL,
  [cproduct] [nvarchar](255) NULL,
  [nprice] [float] NULL,
  [nqty] [float] NULL,
  [nunit] [nvarchar](255) NULL,
  [nunit_bigvalue] [float] NULL,
  [nunit_smallvalue] [float] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='service_load' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[service_load] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [bayar] [nvarchar](255) NULL,
  [ccode] [nvarchar](255) NULL,
  [cnbkt] [nvarchar](255) NULL,
  [diskon_barang] [nvarchar](255) NULL,
  [diskon_member] [nvarchar](255) NULL,
  [dtran] [datetime] NULL,
  [harga] [nvarchar](255) NULL,
  [id_barang] [nvarchar](255) NULL,
  [jumlah] [nvarchar](255) NULL,
  [kembali] [nvarchar](255) NULL,
  [kode] [nvarchar](255) NULL,
  [nama] [nvarchar](255) NULL,
  [note] [nvarchar](255) NULL,
  [pengaju] [nvarchar](255) NULL,
  [totalnotif] [int] NULL
);
END
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='userlogin' AND xtype='U')
BEGIN
CREATE TABLE [dbo].[userlogin] (
  [id] [numeric](18, 0) IDENTITY(1,1) NOT NULL PRIMARY KEY,
  [ServerDate] [datetime] NULL,
  [UpdateDate] [datetime] NULL,
  [cdivisi] [nvarchar](50) NULL,
  [cname] [nvarchar](100) NULL,
  [cpass] [nvarchar](100) NULL,
  [created_at] [datetime] NULL,
  [cstatus] [int] NULL,
  [cuser] [nvarchar](50) NULL,
  [nik] [nvarchar](50) NULL
);
END
GO

-- Seed Default Data
INSERT INTO [dbo].[MDefault] ([code], [default_name]) VALUES
('ccode', '01'), ('pt', 'INDIGAMA PRINTING'), ('alamat', 'Jl. Percetakan No. 1'), ('telp', '021-1234567'), ('mail', 'indigama@example.com'), ('acc_bank', '1102'), ('acc_modal', '3101'), ('acc_kas', '1101');
GO

-- Seed App Version
INSERT INTO [dbo].[AppUpdate] ([app_type], [app_update], [status], [datecreated]) VALUES
('desktop', '20231010.3', 1, GETDATE()), ('desktop', '20231010', 1, GETDATE());
GO

-- Seed Admin User (password: 'admin' encoded as base64 'YWRtaW4=')
SET IDENTITY_INSERT [dbo].[userlogin] ON;
INSERT INTO [dbo].[userlogin] ([id], [cuser], [cpass], [nik], [cname], [cdivisi], [cstatus], [created_at]) VALUES
(1, 'admin', 'YWRtaW4=', 'EMP001', 'Administrator', 'IT', 1, GETDATE());
SET IDENTITY_INSERT [dbo].[userlogin] OFF;
GO

-- Seed Menus & Permissions
SET IDENTITY_INSERT [dbo].[MMenu] ON;
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (1, 'NOTIFICATION');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 1, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (2, 'PRODUKSI BORONGAN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 2, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (3, 'FORM PINJAMAN KARYAWAN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 3, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (4, 'FORM PENAWARAN CUSTOMER');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 4, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (5, 'MARKETING FEE');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 5, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (6, 'MASTER DATA');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 6, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (7, 'MASTER PRODUK');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 7, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (8, 'MASTER KARYAWAN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 8, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (9, 'MASTER SUPPLIER');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 9, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (10, 'MASTER CUSTOMER');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 10, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (11, 'MASTER MESIN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 11, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (12, 'MASTER GUDANG');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 12, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (13, 'MASTER SATUAN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 13, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (14, 'MASTER AKUN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 14, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (15, 'TRANSAKSI PENJUALAN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 15, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (16, 'WORK ORDER');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 16, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (17, 'SURAT JALAN / DELIVERY');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 17, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (18, 'RECEIVE ORDER');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 18, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (19, 'PURCHASING / PO');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 19, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (20, 'KAS & BANK');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 20, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (21, 'MEMORIAL');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 21, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (22, 'OPERASIONAL');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 22, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (23, 'PAYROLL / GAJI');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 23, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (24, 'RAW MATERIAL');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 24, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (25, 'LAPORAN PENJUALAN');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 25, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (26, 'LAPORAN PRODUKSI');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 26, 'MTsxOzE7MTsxOzE=');
INSERT INTO [dbo].[MMenu] ([id], [name]) VALUES (27, 'LAPORAN ACCOUNTING');
INSERT INTO [dbo].[UserLoginKeys] ([id_user], [idform], [keys]) VALUES (1, 27, 'MTsxOzE7MTsxOzE=');
SET IDENTITY_INSERT [dbo].[MMenu] OFF;
GO
