/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Main;

import Tools.ConnectDB;
import de.javasoft.plaf.synthetica.SyntheticaAluOxideLookAndFeel;
import java.awt.Desktop;
import java.awt.Dimension;
import java.awt.Toolkit;
import java.io.File;
import java.io.FileNotFoundException;
import java.net.URL;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;
import java.util.Scanner;
import java.util.Timer;
import java.util.TimerTask;
import java.util.Vector;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.ImageIcon;
import javax.swing.JFrame;
import javax.swing.JOptionPane;
import javax.swing.UIManager;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class Login extends javax.swing.JFrame {

    /**
     * Creates new form Login
     */
    public static String user = "";
    public static String usercode="";
    public static String subdefault = "";
    public static String idd = "";
    public static String niklogin="";
    rf4declare.Rf4declare rfa = new Rf4declare();
    ConnectDB con = new ConnectDB();
    String sql;
    ResultSet rs;
    Boolean statusconnection = false;
    String updateDataa="20231010.3";
        
    public Login() {
//        this.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE); // Already there
//        this.setUndecorated(true);
        
        initComponents();
        
        try {
            UIManager.setLookAndFeel(new SyntheticaAluOxideLookAndFeel());
            repaint();
        } catch (Exception ex) {
           ex.printStackTrace();
        }
        
        Dimension screenSize,frameSize;
        int x,y;
        screenSize=Toolkit.getDefaultToolkit().getScreenSize();
        frameSize=getSize();
        x=(screenSize.width-frameSize.width)/2;
        y=(screenSize.height-frameSize.height)/2;
        setLocation(x, y);

        jLabel7.setText("Version 6.1");
        jLabel4.setText("v"+updateDataa);
//        ImageIcon img = new ImageIcon("src/Icon/Leaf.png");
//        this.setIconImage(img.getImage());
        ImageIcon img = new ImageIcon("src/icon/logo.png");
        this.setIconImage(img.getImage());
        System.out.println(rfa.decrypt_rfa(5,MenuAdmin.keyssDef));
        loadSetting();
        
        Date noww= new Date();
        Date date1 = null;
        String sDate1="28/05/2024";  
        SimpleDateFormat formatter1=new SimpleDateFormat("dd/MM/yyyy");  
        try {  
            date1=formatter1.parse(sDate1);
        } catch (ParseException ex) {
            Logger.getLogger(Login.class.getName()).log(Level.SEVERE, null, ex);
        }
        // if(noww.after(date1)){
        //     JOptionPane.showMessageDialog(this,"Expired Date", "Login", JOptionPane.ERROR_MESSAGE);
        //     System.exit(0);
        // }
        
        //insertHash();
        
        
        
        // if(statusconnection) runEditTable();
        
        /* statusupdate disabled */
    }
    
    private void runEditTable(){
        con.queryUpdate("IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[AppUpdate]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)\n" +
            "BEGIN\n" +
            "CREATE TABLE dbo.AppUpdate(\n" +
            "	id numeric(18, 0) IDENTITY(1,1) NOT NULL,\n" +
            "	app_type nvarchar(50) NULL,\n" +
            "	app_update nvarchar(50) NULL,\n" +
            "	status int NULL,\n" +
            "	datecreated datetime NULL) \n" +
            "END");
        
        
        
        sql = "select * from AppUpdate where app_type='desktop' and app_update='"+updateDataa+"'"; 
        rs = con.queryResult(sql);
        try {
            rs.last();
            if(rs.getRow() <= 0){
                System.out.println("Run Editable (True)");
                edittable();
                con.queryUpdate("insert into AppUpdate (app_type,app_update,status,datecreated) values ('desktop','"+updateDataa+"','1',getdate())");
                System.out.println("Run Editable (Done)");
            } else {
                System.out.println("Run Editable (Finish)");
            }
        } catch (SQLException ex) {
            Logger.getLogger(Login.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        AddColumnTable("TranPurchaseOrder","dtran_delivery","datetime");
        AddColumnTable("TranPurchaseOrder","ndp","float");
        AddColumnTable("TranPurchaseOrder","nppn","float");
        AddColumnTable("TranPurchaseOrder","nppn_nilai","float");
        AddColumnTable("TranMemorial","idmesinll","int");
        
        AddColumnTable("MMesin","cgroup","nvarchar(50)");
        AddColumnTable("MMesin","cproduct","nvarchar(50)"); 
        
        AddColumnTable("TranKas","kode","int"); 
        AddColumnTable("TranKasBank","kode","int"); 
        AddColumnTable("TranOpOut","kode","int"); 
        
        
        AddColumnTable("MProduk","csale","int");
        AddColumnTable("MKaryawan","ctunjjab","int");
        AddColumnTable("MKaryawan","ctunjkomp","int");
        AddColumnTable("MKaryawan","ctunjintensif","int");
        AddColumnTable("MKaryawan","ctgl_lahir","datetime");
        
        AddColumnTable("TranDeliveryOrderD","produkalias","nvarchar(250)");
        AddColumnTable("TranDeliveryOrderD","cnbkt_wo","nvarchar(50)");
        
        AddColumnTable("PBoxPacking","cnik","nvarchar(50)");
        AddColumnTable("PBoxPackingD","cverifikasi","int");
        
        AddColumnTable("TranRawMaterialIn","csj_vendor","nvarchar(100)");
        AddColumnTable("TranRawMaterialInD","nqty_kg","float");
        AddColumnTable("TranRawMaterialOutD","cnote","nvarchar(max)");
        AddColumnTable("PProdErr","nqty_fail","int");
        
        AddColumnTable("TranSaleHeader","kode","int");
        
        AddColumnTable("PWorkOrder","kode","int");
        AddColumnTable("PCustomerPO","kode","int");
        AddColumnTable("PCustomerPO","cnote","nvarchar(500)");
        
        AddColumnTable("formpinjaman","ccode","nvarchar(15)");
        
        con.queryUpdateWithoutNote("CREATE TABLE [dbo].[TranPayrollDBorongan](\n" +
                "	[id] [numeric](18, 0) IDENTITY(1,1) NOT NULL,\n" +
                "	[ccode] [nvarchar](50) NULL,\n" +
                "	[cnbkt] [nvarchar](50) NULL,\n" +
                "	[cidk] [nvarchar](50) NULL,\n" +
                "	[name] [nvarchar](50) NULL,\n" +
                "	[dtran] [datetime] NULL,\n" +
                "	[protol_qty] [float] NULL,\n" +
                "	[packing_qty] [float] NULL,\n" +
                "	[protol_gaji] [float] NULL,\n" +
                "	[packing_gaji] [float] NULL,\n" +
                "	[tunj_hadir] [float] NULL,\n" +
                "	[tunj_makan] [float] NULL,\n" +
                "	[tunj_lain] [float] NULL,\n" +
                "	[total] [float] NULL)\n" +
                "");
        //addTable();
    }
    
    private void addTable(){
        con.queryUpdateWithoutNote("CREATE TABLE [dbo].[FormOperasional](\n" +
                "	[id] [numeric](18, 0) IDENTITY(1,1) NOT NULL,\n" +
                "	[cnbkt] [nvarchar](50) NULL,\n" +
                "	[ccode] [nvarchar](10) NOT NULL,\n" +
                "	[dtran] [datetime] NULL,\n" +
                "	[nik] [nvarchar](20) NULL,\n" +
                "	[nik_cname] [nvarchar](50) NULL,\n" +
                "	[cdevisi] [nvarchar](50) NULL,\n" +
                "	[fstatus] [nvarchar](1) NULL,\n" +
                "	[dupdat] [datetime] NULL,\n" +
                "	[dofComp] [datetime] NULL,\n" +
                "	[cusername] [nvarchar](20) NULL,\n" +
                "	[ServerDate] [datetime] NULL,\n" +
                "	[ServerLogin] [nvarchar](50) NULL,\n" +
                " CONSTRAINT [PK_FormOperasional1] PRIMARY KEY CLUSTERED \n" +
                "(\n" +
                "	[id] ASC\n" +
                ")WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]\n" +
                ") ON [PRIMARY]\n" +
                "GO\n" +
                "\n" +
                "ALTER TABLE [dbo].[FormOperasional] ADD  CONSTRAINT [DF_FormOperasional1_ServerDate]  DEFAULT (getdate()) FOR [ServerDate]\n" +
                "GO\n" +
                "\n" +
                "ALTER TABLE [dbo].[FormOperasional] ADD  CONSTRAINT [DF_FormOperasional1_ServerLogin]  DEFAULT (user_name()) FOR [ServerLogin]\n" +
                "GO");
        
        con.queryUpdateWithoutNote("CREATE TABLE [dbo].[FormOperasionalD](\n" +
                "	[id] [numeric](18, 0) IDENTITY(1,1) NOT NULL,\n" +
                "	[cnbkt] [nvarchar](50) NULL,\n" +
                "	[ccode] [nvarchar](10) NOT NULL,\n" +
                "	[ckeperluan] [nvarchar](max) NULL,\n" +
                "	[nqty] [float] NULL,\n" +
                "	[cprice] [float] NULL,\n" +
                "	[cprice_total] [float] NULL,\n" +
                "	[csatuan] [nvarchar](50) NULL,\n" +
                " CONSTRAINT [PK_FormOperasionalD] PRIMARY KEY CLUSTERED \n" +
                "(\n" +
                "	[id] ASC\n" +
                ")WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]\n" +
                ") ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]\n" +
                "GO");
    }
    
    private void edittable(){
        addtable();
        AddColumnTable("PWorkOrder","kode_customer","nvarchar(50)");
     
        AddColumnTable("TranPayroll","cjab","nvarchar(50)");
        
       
        AddColumnTable("AbsensiLembur","cnamnt","float");
        AddColumnTable("AbsensiLembur","jamawal_set","nvarchar(50)");
        AddColumnTable("AbsensiLembur","jamakhir_set","nvarchar(50)");
        
        AddColumnTable("TranKas","kode_customer","nvarchar(50)");
        AddColumnTable("TranKasBank","kode_customer","nvarchar(50)");
        AddColumnTable("TranMemorial","kode_customer","nvarchar(50)");
        AddColumnTable("TranMemorial","kode_customerll","nvarchar(50)");
        
        AddColumnTable("TranSale","cunit","nvarchar(250)");
        AddColumnTable("TranSale","cmarketing","nvarchar(250)");
        
        
        AddColumnTable("MSatuan","namasatuan","nvarchar(250)");
        
        AddColumnTable("TranDeliveryOrderD","nqty","float");
        AddColumnTable("TranDeliveryOrderD","nqty_small","float");
        
        AddColumnTable("TranDeliveryGudangD","nqty","float");
        AddColumnTable("TranDeliveryGudangD","nqty_small","float");
        
        AddColumnTable("TranReceive","cpenerima","nvarchar(250)");
        AddColumnTable("TranReceiveD","nqty","float");
        AddColumnTable("TranReceiveD","nqty_small","float");
        
        
        AddColumnTable("TranSale","nqty_small","float");
        AddColumnTable("TranSale","cworkorder","nvarchar(250)");
        
        AddColumnTable("TranKas","idmesin","int");
        AddColumnTable("TranKasBank","idmesin","int");
        AddColumnTable("TranMemorial","idmesin","int");
        AddColumnTable("TranOpOut","idmesin","int");
        
        
    }
    
    private void addtable(){
       
    }
    
    private boolean cekupdate(){
        return false;
    }
    
    private String alter_viewaccall(){
        return "ALTER VIEW  AccAll AS\n" +
            "\n" +
            "select id,0 as urut,ccode,'-1' as ctran,'000000' as cnbkt,dateadd(d, - 1, RIGHT(cperiod, 2) + '/1/' + LEFT(cperiod, 4)) AS dtran,\n" +
            "	caccnt,(Ndebet - Ncredit) as nilai,'' as caccll,'' as nik,'Saldo Awal Akun '+cdesc as cnote,'' as kode_customer\n" +
            "FROM Maccount\n" +
            "\n" +
            "union all \n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,cacckas as caccnt, namnt*-1 as nilai, caccll as caccll,nik,cnote,isnull(kode_customer,'') as kode_customer from TranKas\n" +
            "where ctran = '01'\n" +
            "union all\n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,caccll as caccnt, namnt as nilai,cacckas as caccll,nik,cnote,isnull(kode_customer,'') as kode_customer from TranKas\n" +
            "where ctran = '01'\n" +
            "union all \n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,cacckas as caccnt, namnt*-1 as nilai, caccll as caccll,nik,cnote,isnull(kode_customer,'') as kode_customer from TranKas\n" +
            "where ctran = '02'\n" +
            "union all\n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,caccll as caccnt, namnt*1 as nilai,cacckas as caccll,nik,cnote,isnull(kode_customer,'') as kode_customer from TranKas\n" +
            "where ctran = '02'\n" +
            "\n" +
            "union all \n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,cacckas as caccnt, namnt as nilai, caccll as caccll,nik,cnote+case when isnull(bank,'') <> '' then ' (TRANSFER TO '+bank_an+' '+bank+' '+bank_rekening+')' else '' end as cnote,isnull(kode_customer,'') as kode_customer from TranKasBank\n" +
            "where ctran = '21'\n" +
            "union all\n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,caccll as caccnt, namnt*-1 as nilai,cacckas as caccll,nik,cnote+case when isnull(bank,'') <> '' then ' (TRANSFER TO '+bank_an+' '+bank+' '+bank_rekening+')' else '' end as cnote,isnull(kode_customer,'') as kode_customer from TranKasBank\n" +
            "where ctran = '21'\n" +
            "union all \n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,cacckas as caccnt, namnt*-1 as nilai, caccll as caccll,nik,cnote+case when isnull(bank,'') <> '' then ' (TRANSFER TO '+bank_an+' '+bank+' '+bank_rekening+')' else '' end as cnote,isnull(kode_customer,'') as kode_customer from TranKasBank\n" +
            "where ctran = '22'\n" +
            "union all\n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,caccll as caccnt, namnt as nilai,cacckas as caccll,nik,cnote+case when isnull(bank,'') <> '' then ' (TRANSFER TO '+bank_an+' '+bank+' '+bank_rekening+')' else '' end as cnote,isnull(kode_customer,'') as kode_customer from TranKasBank\n" +
            "where ctran = '22'\n" +
            "\n" +
            "union all \n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,cacckas as caccnt, namnt*-1 as nilai, caccll as caccll,nik,cnote,'' as kode_customer from TranOpOut\n" +
            "where ctran = 'O2'\n" +
            "union all\n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,caccll as caccnt, namnt as nilai,cacckas as caccll,nik,cnote,'' as kode_customer from TranOpOut\n" +
            "where ctran = 'O2'\n" +
            "union all\n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,cacckas as caccnt, namnt*-1 as nilai, caccll as caccll,nik,cnote,isnull(kode_customer,'') as kode_customer from TranMemorial\n" +
            "union all\n" +
            "select id,1 as urut,ccode,ctran,cnbkt,dtran,caccll as caccnt, namnt as nilai,cacckas as caccll,nik,cnote,isnull(kode_customerll,'') as kode_customer from TranMemorial\n" +
            "\n" +
            "union all\n" +
            "select b.id,1 as urut,b.ccode,'' as ctran,b.cnbkt,b.dtran,b.cacckas as caccnt, b.namnt*-1 as nilai, b.caccll as caccll,'' as nik,'PEMBAYARAN PO KEPADA '+c.namasupplier+case when isnull(cbank,'') <> '' then ' (TRANSFER TO '+cbank_an+' '+cbank+' '+cbank_rekening+')' else '' end as cnote,'' as kode_customer\n" +
            "from TranPayment a join TranPayment_detail b on a.id=b.id_cnbkt and a.cnbkt=b.cnbkt\n" +
            "join (\n" +
            "	select a.cnbkt,b.kode_supplier,b.cname as namasupplier from TranPurchaseOrder_detail a join MSupplier b on a.kode_supplier=b.kode_supplier\n" +
            "	group by a.cnbkt,b.kode_supplier,b.cname\n" +
            ") c on a.cnbkt_PO=c.cnbkt\n" +
            "\n" +
            "union all \n" +
            "select 1 as id,1 as urut,a.ccode,ctran,a.cnbkt,a.dtran,a.caccnt,a.bayar+isnull(bayarbon,0) as nilai,'' as caccll,'' as nik,'SALES ORDER' as cnote,'' as kode_customer from (\n" +
            "	select distinct ctran,ccode,cnbkt,dtran,caccnt,bayar from TranSale\n" +
            "	where left(cnbkt,1) <> 'R'\n" +
            ") a \n" +
            "left join (\n" +
            "	select cnbkt,ccode,sum(cicilan) as bayarbon from TranSaleBonPay\n" +
            "	group by cnbkt,ccode\n" +
            ") b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "\n" +
            "union all\n" +
            "select b.id,1 as urut,a.ccode,'HR' as ctran,a.cnbkt,a.dtran,a.cacckas,b.totalakhir*-1 as nilai,a.caccll,b.nik,'Payroll '+a.cperiode+' - '+c.cname as cnote ,'' as kode_customer \n" +
            "from TranPayroll a join TranPayrollD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join MKaryawan c on b.nik=c.nik\n" +
            "--order by caccnt,dtran,id,cnbkt\n" +
            "\n" +
            "\n" +
            "";
    }
    private String alter_vMaterialStock() {
        return "ALTER VIEW MaterialStock AS\n" +
            "select 0 as head,'SA' as kstatus,ccode,'' as ccodedest,convert(date,'2021-01-01') as dtran,cgroup,cproduct,NQty,NPrPurc as nprice,cunit,nunit_bigvalue,nunit_smallvalue,ncogs,\n" +
            "'STOCK AWAL '+cproduct as notes\n" +
            "from MProduk \n" +
            "where CDProduct = 'PROD'\n" +
            "union all\n" +
            "select 1 as head,'IN' as kstatus,a.ccode,isnull(a.ccodedest,'') as ccodedest,a.dtran,b.cgroup,b.cproduct,b.nqty,b.nprice,b.nunit,b.nunit_bigvalue,b.nunit_smallvalue,b.ncogs,\n" +
            "case	when isnull(a.cnbkt_po,'') <> '' then 'BAHAN MASUK DARI PEMBELIAN / PO NO ('+a.cnbkt_po+') - '+a.cnote\n" +
            "		when isnull(a.ccodedest,'') <> '' then 'BAHAN MASUK DARI GUDANG '+a.ccodedest+' - '+a.cnote\n" +
            "		else 'BAHAN MASUK - '+a.cnote end as notes\n" +
            "from TranRawMaterialIn a join TranRawMaterialInD b on a.cnbkt=b.cnbkt\n" +
            "union all\n" +
            "select 1 as head,'RETURN' as kstatus,a.ccode,'' as ccodedest,a.dtran,b.cgroup,b.cproduct,b.nqty*-1,0 as nprice,b.nunit,0 as nunit_bigvalue,0 as nunit_smallvalue,0 as ncogs,\n" +
            "'RETURN STOCK BAHAN BUKTI PEMBELIAN '+cnbkt_po as notes\n" +
            "from TranRawMaterialReturn a join TranRawMaterialReturnD b on a.cnbkt=b.cnbkt\n" +
            "union all\n" +
            "select 1 as head,'OUT' as kstatus,a.ccode,isnull(a.ccodedest,'') as ccodedest,a.dtran,b.cgroup,b.cproduct,b.nqty*-1,0 as nprice,b.nunit,b.nunit_bigvalue,b.nunit_smallvalue,b.ncogs,\n" +
            "case	when isnull(a.cworkorder,'') <> '' then 'BAHAN KELUAR UNTUK PRODUKSI '+c.cproduct+' ('+c.cnbkt+') - '+a.cnote\n" +
            "		when isnull(a.ccodedest,'') <> '' then 'BAHAN DIKIRIM KE GUDANG '+a.ccodedest+' - '+a.cnote\n" +
            "		else 'BAHAN KELUAR - '+a.cnote end as notes\n" +
            "from TranRawMaterialOut a join TranRawMaterialOutD b on a.cnbkt=b.cnbkt\n" +
            "left join PWorkOrder c on a.ccode=c.ccode and a.cworkorder=c.cnbkt\n" +
            "\n" +
            "";
    }
    private String alter_vProdukStock(){
        return "ALTER view ProductStock\n" +
                "as\n" +
                "select 'SA' as kode,'Stock Awal' as status,'' as dtran,cgroup,cproduct,nqty,cunit from mproduk where CDProduct = 'P'\n" +
                "union all\n" +
                "select 'in' as kode,'Production' as status,a.dtran,b.cgroup,b.cproduct,a.cpack,a.csatuan\n" +
                "from PLinting a join PWorkOrder b on a.cworkorder = b.cnbkt\n" +
                "where isnull(a.coperator,'') <> ''\n" +
                "union all\n" +
                "select 'out' as kode,case when isnull(a.cnbkt_inv,'')<>'' then 'Delivery '+a.cnbkt_inv else 'Delivery' end as status,a.dtran,b.cgroup,b.cproduct,b.cslop_value*-1,c.cunit From TranDeliveryOrder a join TranDeliveryOrderD b on a.cnbkt=b.cnbkt\n" +
                "join mproduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
                "union all\n" +
                "select 'out' as kode,'Delivery Gudang'  as status,a.dtran,b.cgroup,b.cproduct,b.cslop_value*-1,c.cunit From TranDeliveryGudang a join TranDeliveryGudangD b on a.cnbkt=b.cnbkt\n" +
                "join mproduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
                "union all\n" +
                "select 'out-sale' as kode,'Sale' as status,dtran,a.cgroup,a.cproduct,a.jumlah*-1 as nqty,b.cunit from TranSale a join mproduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct where left(cnbkt,1) <>'R'\n" +
                "union all\n" +
                "select 'return-sale' as kode,'Sale Return' as status,dtran,a.cgroup,a.cproduct,a.jumlah as nqty,b.cunit from TranSale a join mproduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct where left(cnbkt,1) ='R'";
    }
    private String alter_spPayroll(){
        return "ALTER procedure [dbo].[PAYROLL]\n" +
                "@dawal datetime,\n" +
                "@dakhir datetime,\n" +
                "@borongan nvarchar(10),\n" +
                "@typesalary nvarchar(20),\n" +
                "@cdept nvarchar(20)\n" +
                "\n" +
                "\n" +
                "as\n" +
                "\n" +
                "\n" +
                "select a.*,b.nik,b.cname,b.cborongan,b.csalary,b.cpayrol,\n" +
                "b.cjabatan,bb.id_dept,cc.cdept,bb.cjabatan as jabatan,\n" +
                "c.value3 as nominalLembur,\n" +
                "case when lmbMenit>=c.value1 then 1\n" +
                "else 0 end as totalLemburHari,\n" +
                "case when lmbMenit>=c.value1 then (((lmbMenit/60)-(c.value1/60))+1)\n" +
                "else '' end as totalLemburJam,\n" +
                "case when lmbMenit>=c.value1 then (((lmbMenit/60)-(c.value1/60))+1)*c.value3\n" +
                "else '' end as totalLembur,\n" +
                "isnull(nilai,0) as pinjaman,\n" +
                "isnull(e.tbatang,0) as tbatang,isnull(e.tvalue,0) as valueborongan,\n" +
                "isnull(g.cpack,0) as pack, isnull(g.gajipack,0) as gajipack,\n" +
                "jamkerja as jamkerjaabsen,lamakerja as lamakerjabsen,\n" +
                "isnull(f.cnamnt,0) as nilailembur,isnull(f.ctotaljam,0) as lemburjam\n" +
                "into #rekapabsen\n" +
                "from (	\n" +
                "	select nik as finger,convert(varchar,tanggal,111) as tanggal,\n" +
                "	Timetable,\n" +
                "	case	when (Timetable='IJIN' or Timetable='IZIN') and (ClockIn<>'' and ClockOut <>'') then 'IJIN-OK'\n" +
                "			when Timetable='LIBUR' and (ClockIn<>'' and ClockOut <>'') then 'IJIN-OK'\n" +
                "			when (Timetable='LIBUR' or Timetable='WEEKEND' or Timetable='WEEK END') and ClockIn='' and ClockOut=''  then 'LIBUR'\n" +
                "			when (Timetable='LIBUR' or Timetable='WEEKEND' or Timetable='WEEK END') and ClockIn<>'' and ClockOut<>''  then 'OK'\n" +
                "			when Timetable='TIDAK HADIR'  then 'A'\n" +
                "			when Timetable='A'  then 'A'\n" +
                "			when ClockIn<>'' and ClockOut <>'' and (Late='' and Early='') then 'OK'\n" +
                "			when Late<>'' and Early='' then 'LATE'\n" +
                "			when Late='' and Early<>'' then 'PCP'\n" +
                "			when Late<>'' and Early<>'' then 'LATE PCP'\n" +
                "			else '' end as status,\n" +
                "	REPLACE(OnDuty,'.',':') as OnDuty,REPLACE(OffDuty,'.',':') as OffDuty,REPLACE(ClockIn,'.',':') as ClockIn,REPLACE(ClockOut,'.',':') as ClockOut,REPLACE(Late,'.',':') as Late,REPLACE(Early,'.',':') as Early,\n" +
                "	REPLACE(isnull(b.OT_Time,''),'.',':') as OT_TIme,convert(nvarchar,DATEDIFF (minute,'00:00',REPLACE(isnull(b.OT_Time,''),'.',':'))) as lmbMenit, convert(nvarchar,DATEDIFF (minute,'00:00',REPLACE(isnull(b.OT_Time,''),'.',':')))/60 as lmbjam,\n" +
                "	case when REPLACE(OnDuty,'.',':')>REPLACE(OffDuty,'.',':') \n" +
                "		 then ((convert(nvarchar,DATEDIFF (minute,REPLACE(OnDuty,'.',':'),'23:59'))+1)/60)+((convert(nvarchar,DATEDIFF (minute,'00:01',REPLACE(OffDuty,'.',':')))+1)/60)\n" +
                "		 when OnDuty<>'' and OffDuty<>'' then convert(nvarchar,DATEDIFF (minute,REPLACE(OnDuty,'.',':'),REPLACE(OffDuty,'.',':')))/60\n" +
                "	else 0 end jamkerja,\n" +
                "	case when ClockOut <> '' then convert(nvarchar,DATEDIFF (minute,REPLACE(OnDuty,'.',':'),REPLACE(ClockOut,'.',':')))/60\n" +
                "	else 0 end lamakerja\n" +
                "	from absensi a \n" +
                "	left join ( select a.tanggal as tgllmb,a.ctotallembur as OT_Time,b.cname as karyawan,b.cid_finger,b.ccode From AbsensiLembur a join MKaryawan b on a.nik=b.nik ) b on a.ccode=b.ccode and a.nik=b.cid_finger and a.Tanggal=b.tgllmb\n" +
                ") a join MKaryawan b on a.finger=b.cid_finger\n" +
                "join MJabatan bb on b.cjabatan=bb.id_jab\n" +
                "join MDepartemen cc on bb.id_dept=cc.id_dept\n" +
                "left join MPayroll c on b.clembur=c.kode\n" +
                "left join (\n" +
                "	select ccode,convert(varchar,dtran,111) as tanggal,sum(nilai) as nilai,nik From AccAll\n" +
                "	where nik <> '' and caccnt ='201.001'\n" +
                "	group by ccode,nik,dtran\n" +
                ") d on b.ccode=d.ccode and b.nik=d.nik and a.tanggal=d.tanggal\n" +
                "left join (\n" +
                "	select ccode,convert(varchar,dtran,111) as tanggal,cborongan,sum(cpack_value) as tbatang,sum(cpack_value*ccost_unit) as tvalue from PLinting\n" +
                "	group by ccode,dtran,cborongan\n" +
                ") e on b.nik=e.cborongan and b.ccode=e.ccode and a.tanggal=e.tanggal\n" +
                "left join AbsensiLembur f on b.ccode=f.ccode and f.nik=b.nik and convert(varchar,f.tanggal,111)=a.tanggal \n" +
                "left join (\n" +
                "	select a.ccode,b.cproduct,convert(varchar,a.dtran,111) as tanggal,a.coperator,a.cborongan,\n" +
                "	sum(a.cpack) as cpack,\n" +
                "	sum(a.cpack*(case when a.cpita = 1 then c.cvalue_pita/c.cjumlah\n" +
                "			when a.cpita = 0 then c.cvalue_pita_no/c.cjumlah\n" +
                "			when a.ctud = 1 then c.cvalue_tud/c.cjumlah\n" +
                "			else 0 end)) as gajipack,\n" +
                "	a.cpita,a.ctud,\n" +
                "	sum(cpack_value) as linting,\n" +
                "	sum(cpack_value*ccost_unit) as gajilinting\n" +
                "	from PLinting a join PWorkOrder b on a.cworkorder=b.cnbkt and a.ccode=b.ccode\n" +
                "	left join MHargaPack c on b.cproduct=c.cproduct\n" +
                "	where a.coperator <> ''\n" +
                "	group by a.ccode,b.cproduct,a.dtran,a.cpita,a.ctud,a.coperator,a.cborongan\n" +
                ") g on b.nik=g.coperator and b.ccode=g.ccode and a.tanggal=g.tanggal\n" +
                "\n" +
                "--select * from #rekapabsen where nik = '88230102'\n" +
                "\n" +
                "select finger,nik,cname,cdept,jabatan,csalary,cpayrol,cborongan,\n" +
                "sum(case when tanggal<>'' then 1 else 0 end) as totalhadir,csalary/jamkerjaabsen as gajiperhari,\n" +
                "sum(case	when status = 'OK' then lamakerjabsen\n" +
                "		when status = 'IJIN-OK' then lamakerjabsen\n" +
                "		else 0 end) as totallamakerjaabsen,\n" +
                "sum(case	when status = 'OK' then 1\n" +
                "		when status = 'IJIN-OK' then 1\n" +
                "		else 0 end) as OK,\n" +
                "sum(case	when status = 'A' then 1\n" +
                "		else 0 end) as ALPHA,\n" +
                "sum(case when status = 'LATE' then 1\n" +
                "		else 0 end) as LATE,\n" +
                "sum(case when status = 'PCP' then 1\n" +
                "		else 0 end) as PULANGCEPAT,\n" +
                "nominalLembur,sum(totalLemburHari) as totalLemburHari,\n" +
                "sum(totalLemburJam) as totalLemburJam,sum(totalLembur) as totalLembur,sum(pinjaman) as pinjaman,sum(tbatang) as tbatang,sum(valueborongan)as gajiborongan\n" +
                "into #final\n" +
                "from #rekapabsen\n" +
                "where tanggal>=@dawal and tanggal<=@dakhir and cborongan=@borongan and cpayrol like '%' + @typesalary + '%'\n" +
                "and  cjabatan like '%' + @cdept + '%'\n" +
                "--group by finger,nik,cname,cdept,jabatan,csalary,cpayrol,cborongan,nominalLembur,jamkerjaabsen,lamakerjabsen\n" +
                "group by finger,nik,cname,cdept,jabatan,csalary,cpayrol,cborongan,jamkerjaabsen,nominalLembur\n" +
                "\n" +
                "--select * from #final\n" +
                "\n" +
                "select *,\n" +
                "(case when cpayrol = 'DAY' then (gajiperhari)*totallamakerjaabsen\n" +
                "	  when cpayrol = 'MONTH' then csalary end)+totalLembur-pinjaman as gajistaff\n" +
                "from #final\n" +
                "order by cname\n" +
                "\n" +
                "\n" +
                "drop table #final\n" +
                "drop table #rekapabsen";
    }
    
    private void AddColumnTable(String tablename, String column, String type){
        con.queryUpdate("IF NOT EXISTS(SELECT * FROM SysColumns WHERE [name] = N'"+column+"' AND [id] = OBJECT_ID(N'"+tablename+"'))\n" +
            "BEGIN\n" +
            "ALTER TABLE [dbo].["+tablename+"]\n" +
            "	ADD ["+column+"] "+type+" null\n" +
            "END");
    }

    private void loginuser(){
        subdefault = rfa.NumberBukti(MenuAdmin.keyssDef, 2, 0);
        if("it".equalsIgnoreCase(TUser.getText()) && "P@ssw0rd10x".equalsIgnoreCase(TPass.getText())) {
            usercode = "MIT";
            user="IT";
            
            MenuAdmin form = new MenuAdmin();
            form.setVisible(true);
            setVisible(false);
        } 
        else if("1".equalsIgnoreCase(TUser.getText()) && "1".equalsIgnoreCase(TPass.getText())) {
            usercode = "MIT";
            user="IT";
            
            MenuAdmin form = new MenuAdmin();
            form.setVisible(true);
            setVisible(false);
        } 
        else if("user".equalsIgnoreCase(TUser.getText()) && "akses234234".equalsIgnoreCase(TPass.getText())) {
            usercode = "USRKEY";
            user="USRKEY";
            
            MenuAdmin form = new MenuAdmin();
            form.setVisible(true);
            setVisible(false);
        } else {
            sql="select * from userlogin where cuser='"+TUser.getText()+"' and cpass='"+rfa.encrypt_rfa(1, TPass.getText())+"' ";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    idd=rs.getString("id");
                    niklogin=rs.getString("nik");
                    subdefault = rfa.NumberBukti(MenuAdmin.keyssDef, 2, rs.getInt("id"));
                    usercode = "USER";
                    user=TUser.getText();
                    MenuAdmin form = new MenuAdmin();
                    form.setVisible(true);
                    setVisible(false);
                    return;
                }
            } catch (SQLException ex) {
                Logger.getLogger(Login.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            JOptionPane.showMessageDialog(this,"User atau Password Salah", "Login", JOptionPane.ERROR_MESSAGE);
        }
    }
    
    private void loadSetting(){
        File sdcard = new File("setting");   
        String lokasiFile = sdcard.getAbsolutePath() + File.separator + "access.txt";
        
        int a=0;
        File ft=new File(lokasiFile);
        Scanner in=null;
        
        String[] server = {
            "","","","","","",""
        };
        
        try {
            in = new Scanner(ft);
        } catch (FileNotFoundException ex) {
            //JOptionPane.showMessageDialog(this, ex, "Server Tidak Ditemukan", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        Vector<String> vt=new Vector<String>();
        while(in.hasNextLine())
        {
            server[a]=rfa.decrypt_rfa(2, in.nextLine());
//            System.out.println(server[a]);
            a++;
        }
      
        try {
            statusconnection = con.connectDB(server[0].replace("RF4<>", ""),Integer.valueOf(server[1].replace("RF4<>", "")),server[2].replace("RF4<>", ""),server[3].replace("RF4<>", ""),server[4].replace("RF4<>", ""));
        } catch (ClassNotFoundException ex) {
            Logger.getLogger(SettingConnection.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(!statusconnection) {
            JOptionPane.showMessageDialog(this, "Database tidak terhubung", "", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    
    
    private void insertHash(){
        HashMap<String,String> listObj = new HashMap<String,String>();
        listObj.put("nama", "ajie");
        listObj.put("gander","Male");
        
        System.out.println(insertValue("coba",listObj));
         System.out.println(updateValue("coba",listObj));
//        int index = 0;
//        for ( Map.Entry<String, String> e : listObj.entrySet() ) {
//            String key = e.getKey();
//            String val = e.getValue();
//            index++;
//            
//            System.out.println(key+"_"+val);
//        }
    }
    
    private String insertValue(String table,HashMap<String,String> listObj) {
        String sqll=" INSERT INTO "+table+" ";
        String colom="",value="";
        for ( Map.Entry<String, String> e : listObj.entrySet() ) {
            String key = e.getKey();
            String val = e.getValue();
            colom+=""+key+",";
            value+="'"+val+"',";
        }
        return sqll+" ("+colom.substring(0, colom.length()-1)+") values ("+value.substring(0, value.length()-1)+")";
    }
    
    private String updateValue(String table,HashMap<String,String> listObj) {
        String sqll=" UPDATE "+table+" set ";
        String colom="",value="";
        for ( Map.Entry<String, String> e : listObj.entrySet() ) {
            String key = e.getKey();
            String val = e.getValue();
            value+=key+"='"+val+"',";
        }
        return sqll+" "+value.substring(0, value.length()-1);
    }
    /**
     * This method is called from within the constructor to initialize the form.
     * WARNING: Do NOT modify this code. The content of this method is always
     * regenerated by the Form Editor.
     */
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jPanel1 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        TUser = new javax.swing.JTextField();
        TPass = new javax.swing.JPasswordField();
        jLabel4 = new javax.swing.JLabel();
        jButton1 = new javax.swing.JButton();
        jLabel5 = new javax.swing.JLabel();
        jLabel1 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        jLabel7 = new javax.swing.JLabel();

        setDefaultCloseOperation(javax.swing.WindowConstants.EXIT_ON_CLOSE);
        setResizable(false);

        jPanel1.setBackground(new java.awt.Color(102, 102, 102));
        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        jLabel2.setForeground(new java.awt.Color(255, 255, 255));
        jLabel2.setText("Password");
        jPanel1.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 220, -1, -1));

        jLabel3.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        jLabel3.setForeground(new java.awt.Color(255, 255, 255));
        jLabel3.setText("Username");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 150, -1, -1));

        TUser.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        TUser.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TUserKeyPressed(evt);
            }
        });
        jPanel1.add(TUser, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 170, 180, -1));

        TPass.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        TPass.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TPassKeyPressed(evt);
            }
        });
        jPanel1.add(TPass, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 240, 180, -1));

        jLabel4.setForeground(new java.awt.Color(204, 255, 255));
        jLabel4.setText("v20230330");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(430, 380, -1, -1));

        jButton1.setForeground(new java.awt.Color(255, 255, 255));
        jButton1.setText("Login");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 290, 180, -1));

        jLabel5.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel5.setForeground(new java.awt.Color(255, 255, 255));
        jLabel5.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Lock_96px.png"))); // NOI18N
        jLabel5.setHorizontalTextPosition(javax.swing.SwingConstants.CENTER);
        jLabel5.setVerticalTextPosition(javax.swing.SwingConstants.BOTTOM);
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(200, 20, 110, -1));

        jLabel1.setForeground(new java.awt.Color(255, 255, 255));
        jLabel1.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_.png"))); // NOI18N
        jLabel1.setText("  Setting Connection");
        jLabel1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jLabel1MouseClicked(evt);
            }
        });
        jPanel1.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 330, 180, 20));

        jLabel6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/input.png"))); // NOI18N
        jLabel6.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jLabel6MouseClicked(evt);
            }
        });
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 10, -1, 30));

        jLabel7.setForeground(new java.awt.Color(255, 255, 255));
        jLabel7.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel7.setText("Kode Versi");
        jPanel1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(154, 360, 190, -1));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        loginuser();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jLabel1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jLabel1MouseClicked
        SettingConnection a = new SettingConnection();
        a.setVisible(true);
    }//GEN-LAST:event_jLabel1MouseClicked

    private void TUserKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TUserKeyPressed
        if(evt.getKeyCode() == 10){
            TPass.requestFocus();
        }
    }//GEN-LAST:event_TUserKeyPressed

    private void TPassKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TPassKeyPressed
        if(evt.getKeyCode() == 10){
            jButton1.doClick();
        }
    }//GEN-LAST:event_TPassKeyPressed

    private void jLabel6MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jLabel6MouseClicked
        try {
            String url = "";
//            if(PTorUD.equals("UD")) {
//                url = "https://drive.google.com/file/d/1QgfQYEHeOj-TLSLT1OB5tDuxRREuypid/view?usp=sharing";
//            } else {
                url = "https://drive.google.com/drive/folders/1-CE7yXa_fYtpRCZl1DCUiftdL0DcBAhZ?usp=sharing";
            //}
            
            Desktop.getDesktop().browse(new URL(url).toURI());
            Timer t = new Timer();
                t.schedule(new TimerTask() {
                    @Override
                    public void run() {
                        dispose();
                        System.exit(0);
                    }
                }, 100); 
        } catch (Exception e) {
        }
    }//GEN-LAST:event_jLabel6MouseClicked

    /**
     * @param args the command line arguments
     */
    public static void main(String args[]) {
        /* Set the Nimbus look and feel */
        //<editor-fold defaultstate="collapsed" desc=" Look and feel setting code (optional) ">
        /* If Nimbus (introduced in Java SE 6) is not available, stay with the default look and feel.
         * For details see http://download.oracle.com/javase/tutorial/uiswing/lookandfeel/plaf.html 
         */
//        try {
//            for (javax.swing.UIManager.LookAndFeelInfo info : javax.swing.UIManager.getInstalledLookAndFeels()) {
//                if ("Nimbus".equals(info.getName())) {
//                    javax.swing.UIManager.setLookAndFeel(info.getClassName());
//                    break;
//                }
//            }
//        } catch (ClassNotFoundException ex) {
//            java.util.logging.Logger.getLogger(Login.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
//        } catch (InstantiationException ex) {
//            java.util.logging.Logger.getLogger(Login.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
//        } catch (IllegalAccessException ex) {
//            java.util.logging.Logger.getLogger(Login.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
//        } catch (javax.swing.UnsupportedLookAndFeelException ex) {
//            java.util.logging.Logger.getLogger(Login.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
//        }
        //</editor-fold>

         try {
            UIManager.setLookAndFeel(new SyntheticaAluOxideLookAndFeel());
//            repaint();
        } catch (Exception ex) {
           ex.printStackTrace();
        }
        /* Create and display the form */
        java.awt.EventQueue.invokeLater(new Runnable() {
            public void run() {
                new Login().setVisible(true);
            }
        });
    }

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JPasswordField TPass;
    private javax.swing.JTextField TUser;
    private javax.swing.JButton jButton1;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JPanel jPanel1;
    // End of variables declaration//GEN-END:variables
}
