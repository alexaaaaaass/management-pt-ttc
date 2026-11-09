/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Main;

import Absensi.Cuti;
import Absensi.Holidays;
import Absensi.Ijin;
import Absensi.Lembur;
import Absensi.RekapAbsensi;
import Absensi.UploadAbsensi;
import Accounting.FormOperasional;
import Accounting.TranBankIn;
import Accounting.TranBankOut;
import Accounting.TranKasIn;
import Accounting.TranKasOut;
import Accounting.TranMemorial;
import Accounting.TranOpIn;
import Accounting.TranOpOut;
import Accounting.TranPinjaman;
import Delivery.DeliveryAntarGudang;
import Delivery.DeliveryOrder;
import Delivery.DeliveryPORekap;
import DialogPublicTP.DialogNotif;
import Kasir.CashierFull;
import Kasir.FormPenawaran;
import Kasir.PembayaranBon;
import Kasir.PenjualanUtility;
import Kasir.ReturnPenjualan;
import static Main.Login.subdefault;
import static Main.MenuAdmin1.formView;
import static Main.MenuAdmin1.keyssDef;
import Master.MAccount;
import Master.MCompany;
import Master.MCostProduksi;
import Master.MDept;
import Master.MErrorProduction;
import Master.MGudang;
import Master.MJabatan;
import Master.MKaryawan;
import Master.MMaterial;
import Master.MMembers;
import Master.MMesin;
import Master.MOperasional;
import Master.MPayment;
import Master.MPayrollDefault;
import Master.MPriceBoronganPack;
import Master.MPriceBoronganProtol;
import Master.MPriceBoronganTunj;
import Master.MProduk;
import Master.MSatuan;
import Master.MStepProduksi; 
import Master.MSupplier; 
import Payroll.BonusKaryawan;
import Payroll.FormPinjamanKaryawan;
import Payroll.PayrollBorongan;
import Payroll.PayrollKaryawan;
import Payroll.SlipGaji;
import Production.BoronganPacking;
import Production.BoronganProtol;
import Production.BoronganRekap;
import Production.Packaging1;
import Production.PackagingReturn;
import Production.PreCustomer;
import Production.ProductionStock;
import Production.ProductionStockCheck;
import Production.ProduksiInput2;
import Production.ProduksiInputUtility;
import Production.VerifikasiHasilProduksi;
import Production.WorkOrder;
import Purchase.PaymentPurchase;
import Purchase.PurchaseOrder;
import Purchase.PurchaseOrderBilling;
import Purchase.PurchaseRequest;
import Purchase.SetBIllPOMaterial;
import RPurchasing.RPurchasing;
import RawMaterial.BOMProduct;
import RawMaterial.RawMateriaReturn;
import RawMaterial.RawMaterialAdjust;
import RawMaterial.RawMaterialIn;
import RawMaterial.RawMaterialOut;
import RawMaterial.RawMaterialReturnProduksi;
import RawMaterial.StockOpname;
import RawMaterial.WarningStock;
import Receive.EntryReceive;
import Receive.ReceiveAntarGudang;
import Report.RAnalisaPenjualan;
import Report.RAnalisaTransaksi;
import Report.RMarketingFee;
import Report.RMaterial;
import Report.RPayroll;
import Report.RPenjualan;
import Report.RProduk;
import ReportProduction.RProduction;
import Tools.Closingtab;
import Tools.ConnectDB;
import Tools.UserAkses;
import java.awt.Cursor;
import java.awt.Dimension;
import java.awt.GraphicsEnvironment;
import java.awt.Rectangle;
import java.awt.Toolkit;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.Box;
import javax.swing.ImageIcon;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JFrame;
import javax.swing.JOptionPane;
import javax.swing.JPanel;
import javax.swing.KeyStroke;
import rf4declare.Rf4declare;
import sun.audio.AudioPlayer;
import sun.audio.AudioStream;

/**
 *
 * @author HP
 */
public class MenuAdmin extends javax.swing.JFrame {

    JPanel panelOpen=null;
    int selectedTab=0;
    ConnectDB con = new ConnectDB();
    ResultSet rs;
    String sql;
    public static String keyssDef = "VjFSS01GbFdSbGhTYmtaVFYwYzRPUT09";
    rf4declare.Rf4declare rfa = new Rf4declare();
    int jumlahnotif=0,checkload=0;
    int notif_accses = 0;
    public MenuAdmin() {
        ImageIcon img = new ImageIcon("src/icon/logo.png");
        this.setIconImage(img.getImage());
        
        Dimension scrnSize = Toolkit.getDefaultToolkit().getScreenSize();
        Rectangle winSize = GraphicsEnvironment.getLocalGraphicsEnvironment().getMaximumWindowBounds();
        int taskBarHeight = scrnSize.height - winSize.height;
        System.out.println(taskBarHeight);
        
        initComponents();
        
        setLocationRelativeTo(null);
        Dimension screenSize;
        screenSize=Toolkit.getDefaultToolkit().getScreenSize();
        setSize(screenSize.width,screenSize.height-taskBarHeight);
        setLocation(0, -1);
        jMenuBar1.add(Box.createHorizontalGlue()); //<-- horizontal glue
        jMenuBar1.add(jMenu6);
        jMenuBar1.add(jMenu14);
        
        
        jMenuItem14.setVisible(false);
        //this.setExtendedState( this.getExtendedState()|JFrame.MAXIMIZED_BOTH );
    
        jMenu6.setText("  Haii "+Login.user+"      ");
        DefaultProgram.loadDeff();
        subdefault = DefaultProgram.deffProgram.get("sub");
        
        //deffProgram.put("ccode","PMK02"); //coba pamekasan marketing
        
        addAkses("PRODUKSI PO CUSTOMER");
        addAkses("PRODUKSI RETURN");
        addAkses("PRODUKSI STOCKB");
        addAkses("PRODUKSI STOCK CHECK");
        addAkses("PRODUKSI BORONGAN");
        addAkses("MASTER MESIN");  
        addAkses("NOTIFICATION");
        addAkses("KARYAWAN CUTI");
        addAkses("KARYAWAN IJIN");
        addAkses("HOLIDAYS");
        addAkses("MASTER COMPANY");
        addAkses("MASTER GAJI BORONGAN");
        addAkses("PURCHASE BILL");
        addAkses("MASTER PRODUKSI BOM");
        addAkses("MASTER PRODUKSI COST");
        addAkses("MASTER PRODUKSI STEP");
        addAkses("MASTER PRODUKSI FAIL");
        addAkses("ROW MATERIAL RETURN PRODUKSI");
        addAkses("PRODUKSI VERIFIKASI PRODUK JADI");
        addAkses("FORM PINJAMAN KARYAWAN");
        addAkses("DELIVERY REKAP PO CUSTOMER");
        addAkses("BONUS KARYAWAN");
        addAkses("FORM PENAWARAN CUSTOMER");
        addAkses("MARKETING FEE");
        addAkses("REPORT PAYROLL"); 
        notif_accses = 0;
        if(!Login.usercode.equals("MIT")) {
            notif_accses = 0;
            formAkses();
            
            if(Login.usercode.equals("USRKEY")) {
                jMenu1.setVisible(true);
                jMenuItem1UserAkses.setVisible(true);
                jMenuItem3.setVisible(true);
                jMenuItem4.setVisible(false);
                jMenuItem2GantiPass.setVisible(false);
                jButton7Cashier.setEnabled(false);
                jButton8InputStock.setEnabled(false);
                jButton9ReportJual.setEnabled(false);
            }
        } else {
            notif_accses = 1;
        }
        
        if(notif_accses ==1 ) {
            nt();
        }
    }
 
    
    private void formAkses(){
        jMenuItem1UserAkses.setVisible(false);
        jMenu2.setVisible(false);
        jMenu4.setVisible(false);
        jMenuItem10.setVisible(false);
        jMenuItem11.setVisible(false);
        jMenuItem12.setVisible(false);
        jMenuItem13.setVisible(false);
        jMenuItem15.setVisible(false);
        jMenuItem16.setVisible(false); 
        jMenu7.setVisible(false);
        jMenuItem1.setVisible(false);
        jMenuItem42.setVisible(false);
        jMenu5.setVisible(false);
        jMenuItem28.setVisible(false);
        jMenuItem43.setVisible(false);
        jMenuItem44.setVisible(false);
        jMenu12.setVisible(false);
        jMenuItem45.setVisible(false);
        jMenuItem47.setVisible(false);
        jMenu11.setVisible(false);
        jMenuItem35.setVisible(false);
        jMenuItem36.setVisible(false);
        jMenuItem37.setVisible(false);
        jMenu9.setVisible(false);
        jMenuItem18.setVisible(false);
        jMenuItem19.setVisible(false);
        jMenuItem20.setVisible(false);
        jMenuItem51.setVisible(false);
        jMenu10.setVisible(false);
        jMenuItem24.setVisible(false);
        jMenuItem25.setVisible(false);
        jMenuItem26.setVisible(false);
        jMenuItem27.setVisible(false);
        jMenu4Master.setVisible(false);
        jMenuItem39.setVisible(false);
        jMenuItem5.setVisible(false);
        jMenuItem5MasterProduct.setVisible(false);
        jMenuItem9MasterSupplier.setVisible(false);
        jMenuItem10Member.setVisible(false);
        jMenuItem1MKaryawan.setVisible(false);
        jMenuItem38.setVisible(false);
        jMenuItem9.setVisible(false);
        jMenuItem6.setVisible(false);
        jMenuItem23.setVisible(false);
        jMenuItem29.setVisible(false);
        jMenuItem49.setVisible(false);
        jMenu5Report.setVisible(false);
        jMenuItem32.setVisible(false);
        jMenuItem52.setVisible(false);
        jMenuItem31.setVisible(false);
        jMenuItem6RStockProduct.setVisible(false);
        jMenuItem7.setVisible(false);
        jMenuItem33.setVisible(false);
        jMenuItem14RPenjualan.setVisible(false);
        jMenuItem1RPengiriman.setVisible(false);
        jMenu6Tools.setVisible(false);
        jMenuItem21.setVisible(false);
        jMenuItem41.setVisible(false);
        jMenuItem40.setVisible(false);
        jMenuItem48.setVisible(false); 
        jMenuItem57.setVisible(false);
        jMenuItem2.setVisible(false);
        jMenu16.setVisible(false);
        jMenuItem50.setVisible(false);
        jMenuItem68.setVisible(false);
        jMenuItem67.setVisible(false);
        jMenuItem22.setVisible(false);
        jMenuItem73.setVisible(false);
        jMenuItem72.setVisible(false); 
        jMenuItem74.setVisible(false); 
        jButton8InputStock.setEnabled(false); 
        jButton9ReportJual.setEnabled(false); 
        jButton7Cashier.setEnabled(false);
        jMenuItem30.setVisible(false); 
        jMenu15.setVisible(false);
        jMenuItem34.setVisible(false);
        jMenuItem54.setVisible(false);
        jMenuItem55.setVisible(false);
        jMenu8.setVisible(false);
        jMenuItem59.setVisible(false);
        jMenu13.setVisible(false);
        jMenu8.setVisible(false);
        jMenuItem69.setVisible(false);
        jMenuItem78.setVisible(false);
        jMenuItem80.setVisible(false);
        jMenuItem81.setVisible(false);
        jMenuItem84.setVisible(false);
        
        openAkses();
    }
    private void openAkses(){
        sql="select * from UserLoginKeys a join MMenu b on a.idform=b.id\n" +
            "where id_user= '"+Login.idd+"'\n" +
            "order by name";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                String akses = null;
                try {
                    akses = rfa.decrypt_rfa(1, rs.getString("keys"));
                } catch (Exception ex) {
                    ex.printStackTrace();
                }
                String[] parts = akses.split(";");
                
                if("NOTIFICATION".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    notif_accses = 1;
                }
                
                if("PRODUKSI BORONGAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu8.setVisible(true);
                } 
                if("FORM PINJAMAN KARYAWAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu7.setVisible(true);
                    jMenuItem59.setVisible(true);
                } 
                if("FORM PENAWARAN CUSTOMER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true); 
                    jMenuItem80.setVisible(true);
                } 
                if("MARKETING FEE".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true); 
                    jMenuItem81.setVisible(true);
                } 
                if("BONUS KARYAWAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu7.setVisible(true);
                    jMenuItem78.setVisible(true);
                } 
                if("MASTER GAJI BORONGAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true); 
                    jMenu13.setVisible(true);
                } 
                if("MASTER PRODUKSI BOM".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenu15.setVisible(true);
                    jMenuItem67.setVisible(true);
                } 
                if("MASTER PRODUKSI COST".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenu15.setVisible(true);
                    jMenuItem76.setVisible(true);
                } 
                if("MASTER PRODUKSI STEP".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenu15.setVisible(true);
                    jMenuItem53.setVisible(true);
                } 
                if("MASTER PRODUKSI FAIL".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenu15.setVisible(true);
                    jMenuItem56.setVisible(true);
                } 
                if("TRANSAKSI LABA RUGI MESIN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu4.setVisible(true);
                } 
              
                if("MASTER COMPANY".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem2.setVisible(true);
                }
                if("MASTER ACCOUNT".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem39.setVisible(true);
                }
                if("MASTER CUSTOMER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem10Member.setVisible(true);
                }
                if("MASTER GUDANG".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem5.setVisible(true);
                }
                if("MASTER JABATAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem38.setVisible(true);
                    jMenuItem55.setVisible(true);
                }
                if("MASTER KARYAWAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem1MKaryawan.setVisible(true);
                }
                if("MASTER CUSTOMER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem10Member.setVisible(true);
                }
                if("MASTER OPERASIONAL".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem23.setVisible(true);
                }
                if("MASTER PAYMENT".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem29.setVisible(true);
                }
                if("MASTER PRODUK".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem5MasterProduct.setVisible(true);
                    jButton8InputStock.setEnabled(true);
                }
                if("MASTER SATUAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem49.setVisible(true);
                }
                if("MASTER SUPPLIER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem9MasterSupplier.setVisible(true);
                }
//                if("MASTER PAYROLL DEFAULT".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
//                    jMenu4Master.setVisible(true);
//                    jMenuItem9.setVisible(true);
//                }
                if("MASTER MATERIAL".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu4Master.setVisible(true);
                    jMenuItem6.setVisible(true);
                }
                
                
                if("TRANSAKSI KAS MASUK".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu4.setVisible(true);
                    jMenuItem10.setVisible(true);
                }
                if("TRANSAKSI KAS KELUAR".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu4.setVisible(true);
                    jMenuItem11.setVisible(true);
                }
                if("TRANSAKSI BANK MASUK".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu4.setVisible(true);
                    jMenuItem12.setVisible(true);
                }
                if("TRANSAKSI BANK KELUAR".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu4.setVisible(true);
                    jMenuItem13.setVisible(true);
                }
                if("TRANSAKSI OPRASIONAL KELUAR".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu4.setVisible(true);
                    jMenuItem15.setVisible(true);
                }
                if("TRANSAKSI MEMORIAL".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu4.setVisible(true);
                    jMenuItem16.setVisible(true);
                }
                
                
                if("TRANSAKSI PAYROLL KARYAWAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu7.setVisible(true);
                    jMenuItem1.setVisible(true);
                    jMenuItem42.setVisible(true);
                    jMenuItem46.setVisible(true);
                    jMenuItem58.setVisible(true);
                    jMenuItem65.setVisible(true);
                    jMenuItem17.setVisible(true);
                }
                
                if("DELIVERY ORDER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu5.setVisible(true);
                    jMenuItem28.setVisible(true);
                }
                if("DELIVERY ANTAR GUDANG".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu5.setVisible(true);
                    jMenuItem43.setVisible(true);
                }
                if("DELIVERY REKAP PO CUSTOMER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu5.setVisible(true);
                    jMenuItem69.setVisible(true);
                }
                
                if("RECEIVE ANTAR GUDANG".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu12.setVisible(true);
                    jMenuItem45.setVisible(true);
                    jMenuItem47.setVisible(true);
                }
                
                if("PURCHASE REQUEST".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu11.setVisible(true);
                    jMenuItem35.setVisible(true);
                }
                if("PURCHASE ORDER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu11.setVisible(true);
                    jMenuItem36.setVisible(true);
                }
                if("PURCHASE BILL".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu11.setVisible(true);
                    jMenuItem50.setVisible(true);
                    jMenuItem54.setVisible(true);
                }
                if("PURCHASE PAYMENT".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu11.setVisible(true);
                    jMenuItem37.setVisible(true);
                }
                
                if("ROW MATERIAL IN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu9.setVisible(true);
                    jMenuItem18.setVisible(true);
                }
                if("ROW MATERIAL OUT".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu9.setVisible(true);
                    jMenuItem19.setVisible(true);
                }
                if("ROW MATERIAL RETURN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu9.setVisible(true);
                    jMenuItem20.setVisible(true);
                }
                if("ROW MATERIAL RETURN PRODUKSI".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu9.setVisible(true);
                    jMenuItem22.setVisible(true);
                }
                
                if("PRODUKSI PO CUSTOMER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem68.setVisible(true);
                }  
                if("PRODUKSI WORK ORDER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem51.setVisible(true);
                }  
                if("PRODUKSI INPUT".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem70.setVisible(true);
                    jMenuItem34.setVisible(true);
                }  
                if("PRODUKSI BOX PACKAGING".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem71.setVisible(true);
                }  
                if("PRODUKSI VERIFIKASI PRODUK JADI".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem30.setVisible(true); 
                } 
                if("PRODUKSI STOCKB".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem72.setVisible(true); 
                }  
                if("PRODUKSI STOCK CHECK".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem73.setVisible(true); 
                }  
                if("PRODUKSI RETURN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu16.setVisible(true);
                    jMenuItem74.setVisible(true); 
                }  
                
                if("CASHIER".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu10.setVisible(true);
                    jMenuItem24.setVisible(true);
                    jButton9ReportJual.setEnabled(true);
                    jButton7Cashier.setEnabled(true);
                }
                if("PELUNASAN BON".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu10.setVisible(true);
                    jMenuItem25.setVisible(true);
                }
                if("RETURN PENJUALAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu10.setVisible(true);
                    jMenuItem26.setVisible(true);
                }
                if("EDIT PENJUALAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu2.setVisible(true);
                    jMenu10.setVisible(true);
                    jMenuItem27.setVisible(true);
                }
                
                if("REPORT PRODUCTION".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem32.setVisible(true);
                }
                if("REPORT PAYROLL".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem84.setVisible(true);
                }
                if("REPORT PURCHASING".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem52.setVisible(true);
                }
                if("REPORT MATERIAL".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem31.setVisible(true);
                }
                if("REPORT PRODUK".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem6RStockProduct.setVisible(true);
                }
                if("REPORT PENJUALAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem7.setVisible(true);
                }
                if("REPORT ANALISA TRANSAKSI".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem33.setVisible(true);
                }
                if("REPORT ANALISA PENJUALAN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
                    jMenuItem14RPenjualan.setVisible(true);
                }
                if("REPORT DELIVERY".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu5Report.setVisible(true);
//                    jMenuItem1RPengiriman.setVisible(true);
                }
                
                if("UPLOAD ABSENSI".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu6Tools.setVisible(true);
                    jMenuItem21.setVisible(true);
                }
                if("REKAP ABSENSI".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu6Tools.setVisible(true);
                    jMenuItem41.setVisible(true);
                }
                if("DEFAULT DATA".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")) {
                    jMenu6Tools.setVisible(true);
                    jMenuItem48.setVisible(true);
                } 
                if("MASTER MESIN".equalsIgnoreCase(rs.getString("name")) && parts[0].equals("1")){
                    jMenuItem57.setVisible(true);
                    jMenu4Master.setVisible(true);
                }
                 
                
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(MenuAdmin.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void addAkses(String menu){
        sql="IF NOT EXISTS(SELECT * FROM MMenu where name = '"+menu+"')\n" +
            "begin \n" +
            "	insert into MMenu (name) values ('"+menu+"')\n" +
            "end ";
        con.queryUpdate(sql);
    }
    private void push_button(){
        InputMap im = getRootPane().getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = getRootPane().getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F2, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
      
            }
        });
    }
    
    private void nt(){
        updateThread = new Thread(new Runnable() {
            @Override
            public void run() {
                String temp="";
                InputStream is = null;
                BufferedReader bfReader = null;
                String a;
                int i=0;
                while (true) {
                    if(jumlahnotif == 0) jMenu14.setVisible(false);
                    if(checkload==0 && notif_accses == 1) {
                        String sqlnotif="select count(*) as totalnotif from (\n" +
                                        "	select * from service_load\n" +
                                        ") a ";

                        rs= con.queryResultNotif(sqlnotif);
                        int loadnotif=0;
                        checkload = 1;
                        Boolean check = false;
                        try {
                            while(rs.next()) {
                                loadnotif = rs.getInt("totalnotif");
                                checkload=0;
                                check = true;
                            }
                        } catch (Exception e){

                        }
                        if(loadnotif ==0 && check){
                            jMenu14.setVisible(false);
                            jumlahnotif = loadnotif;
                        } else if(loadnotif > jumlahnotif && notif_accses == 1 && check) {
                            jMenu14.setVisible(true);
                            jMenu14.setText(String.valueOf(loadnotif)+" Notification");
                            SuaraOn("tone");
                            jumlahnotif = loadnotif;
                             
                        } else if(loadnotif != jumlahnotif && notif_accses == 1 && check) {
                            jMenu14.setVisible(true);
                            jMenu14.setText(String.valueOf(loadnotif)+" Notification");
                            jumlahnotif = loadnotif;
                        }   
                    }
                    try {
                        Thread.sleep(1000);
                    } catch (InterruptedException e) {
                        e.printStackTrace();
                    }
                }
            }
        });
        updateThread.start();
    }
     
    
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        menuBar1 = new java.awt.MenuBar();
        menu1 = new java.awt.Menu();
        menu2 = new java.awt.Menu();
        jPanel1 = new javax.swing.JPanel();
        jButton6 = new javax.swing.JButton();
        jButton7Cashier = new javax.swing.JButton();
        jButton8InputStock = new javax.swing.JButton();
        jButton9ReportJual = new javax.swing.JButton();
        formView = new Closingtab();
        jLabel1 = new javax.swing.JLabel();
        jMenuBar1 = new javax.swing.JMenuBar();
        jMenu1 = new javax.swing.JMenu();
        jMenuItem1UserAkses = new javax.swing.JMenuItem();
        jMenuItem2GantiPass = new javax.swing.JMenuItem();
        jSeparator1 = new javax.swing.JPopupMenu.Separator();
        jMenuItem3 = new javax.swing.JMenuItem();
        jMenuItem4 = new javax.swing.JMenuItem();
        jMenu2 = new javax.swing.JMenu();
        jMenu4 = new javax.swing.JMenu();
        jMenuItem10 = new javax.swing.JMenuItem();
        jMenuItem11 = new javax.swing.JMenuItem();
        jSeparator2 = new javax.swing.JPopupMenu.Separator();
        jMenuItem12 = new javax.swing.JMenuItem();
        jMenuItem13 = new javax.swing.JMenuItem();
        jSeparator3 = new javax.swing.JPopupMenu.Separator();
        jMenuItem14 = new javax.swing.JMenuItem();
        jMenuItem15 = new javax.swing.JMenuItem();
        jSeparator4 = new javax.swing.JPopupMenu.Separator();
        jMenuItem16 = new javax.swing.JMenuItem();
        jSeparator10 = new javax.swing.JPopupMenu.Separator();
        jMenuItem60 = new javax.swing.JMenuItem();
        jMenu7 = new javax.swing.JMenu();
        jMenuItem1 = new javax.swing.JMenuItem();
        jMenuItem82 = new javax.swing.JMenuItem();
        jMenuItem42 = new javax.swing.JMenuItem();
        jSeparator9 = new javax.swing.JPopupMenu.Separator();
        jMenuItem46 = new javax.swing.JMenuItem();
        jMenuItem58 = new javax.swing.JMenuItem();
        jMenuItem65 = new javax.swing.JMenuItem();
        jSeparator11 = new javax.swing.JPopupMenu.Separator();
        jMenuItem17 = new javax.swing.JMenuItem();
        jSeparator17 = new javax.swing.JPopupMenu.Separator();
        jMenuItem59 = new javax.swing.JMenuItem();
        jMenuItem78 = new javax.swing.JMenuItem();
        jSeparator20 = new javax.swing.JPopupMenu.Separator();
        jMenuItem83 = new javax.swing.JMenuItem();
        jMenu5 = new javax.swing.JMenu();
        jMenuItem28 = new javax.swing.JMenuItem();
        jMenuItem43 = new javax.swing.JMenuItem();
        jMenuItem44 = new javax.swing.JMenuItem();
        jSeparator19 = new javax.swing.JPopupMenu.Separator();
        jMenuItem69 = new javax.swing.JMenuItem();
        jMenu12 = new javax.swing.JMenu();
        jMenuItem45 = new javax.swing.JMenuItem();
        jMenuItem47 = new javax.swing.JMenuItem();
        jMenu11 = new javax.swing.JMenu();
        jMenuItem35 = new javax.swing.JMenuItem();
        jMenuItem36 = new javax.swing.JMenuItem();
        jMenuItem50 = new javax.swing.JMenuItem();
        jMenuItem54 = new javax.swing.JMenuItem();
        jMenuItem37 = new javax.swing.JMenuItem();
        jMenu9 = new javax.swing.JMenu();
        jMenuItem18 = new javax.swing.JMenuItem();
        jMenuItem19 = new javax.swing.JMenuItem();
        jMenuItem75 = new javax.swing.JMenuItem();
        jSeparator12 = new javax.swing.JPopupMenu.Separator();
        jMenuItem20 = new javax.swing.JMenuItem();
        jMenuItem22 = new javax.swing.JMenuItem();
        jMenu10 = new javax.swing.JMenu();
        jMenuItem24 = new javax.swing.JMenuItem();
        jMenuItem25 = new javax.swing.JMenuItem();
        jMenuItem26 = new javax.swing.JMenuItem();
        jMenuItem27 = new javax.swing.JMenuItem();
        jMenu16 = new javax.swing.JMenu();
        jMenuItem68 = new javax.swing.JMenuItem();
        jMenuItem51 = new javax.swing.JMenuItem();
        jMenuItem70 = new javax.swing.JMenuItem();
        jMenuItem34 = new javax.swing.JMenuItem();
        jMenuItem71 = new javax.swing.JMenuItem();
        jSeparator15 = new javax.swing.JPopupMenu.Separator();
        jMenuItem30 = new javax.swing.JMenuItem();
        jSeparator16 = new javax.swing.JPopupMenu.Separator();
        jMenuItem72 = new javax.swing.JMenuItem();
        jMenuItem73 = new javax.swing.JMenuItem();
        jMenuItem74 = new javax.swing.JMenuItem();
        jMenu8 = new javax.swing.JMenu();
        jMenuItem61 = new javax.swing.JMenuItem();
        jMenuItem62 = new javax.swing.JMenuItem();
        jSeparator18 = new javax.swing.JPopupMenu.Separator();
        jMenuItem66 = new javax.swing.JMenuItem();
        jMenuItem77 = new javax.swing.JMenuItem();
        jMenuItem80 = new javax.swing.JMenuItem();
        jMenuItem81 = new javax.swing.JMenuItem();
        jMenu4Master = new javax.swing.JMenu();
        jMenuItem39 = new javax.swing.JMenuItem();
        jMenuItem2 = new javax.swing.JMenuItem();
        jMenuItem5 = new javax.swing.JMenuItem();
        jSeparator5 = new javax.swing.JPopupMenu.Separator();
        jMenuItem5MasterProduct = new javax.swing.JMenuItem();
        jMenu15 = new javax.swing.JMenu();
        jMenuItem67 = new javax.swing.JMenuItem();
        jMenuItem76 = new javax.swing.JMenuItem();
        jMenuItem57 = new javax.swing.JMenuItem();
        jSeparator13 = new javax.swing.JPopupMenu.Separator();
        jMenuItem53 = new javax.swing.JMenuItem();
        jMenuItem56 = new javax.swing.JMenuItem();
        jSeparator14 = new javax.swing.JPopupMenu.Separator();
        jSeparator6 = new javax.swing.JPopupMenu.Separator();
        jMenuItem9MasterSupplier = new javax.swing.JMenuItem();
        jMenuItem10Member = new javax.swing.JMenuItem();
        jSeparator7 = new javax.swing.JPopupMenu.Separator();
        jMenuItem1MKaryawan = new javax.swing.JMenuItem();
        jMenuItem55 = new javax.swing.JMenuItem();
        jMenuItem38 = new javax.swing.JMenuItem();
        jMenuItem9 = new javax.swing.JMenuItem();
        jMenu13 = new javax.swing.JMenu();
        jMenuItem63 = new javax.swing.JMenuItem();
        jMenuItem64 = new javax.swing.JMenuItem();
        jMenuItem79 = new javax.swing.JMenuItem();
        jSeparator8 = new javax.swing.JPopupMenu.Separator();
        jMenuItem6 = new javax.swing.JMenuItem();
        jMenuItem23 = new javax.swing.JMenuItem();
        jMenuItem29 = new javax.swing.JMenuItem();
        jMenuItem49 = new javax.swing.JMenuItem();
        jMenu5Report = new javax.swing.JMenu();
        jMenuItem32 = new javax.swing.JMenuItem();
        jMenuItem52 = new javax.swing.JMenuItem();
        jMenuItem31 = new javax.swing.JMenuItem();
        jMenuItem6RStockProduct = new javax.swing.JMenuItem();
        jMenuItem7 = new javax.swing.JMenuItem();
        jMenuItem33 = new javax.swing.JMenuItem();
        jMenuItem14RPenjualan = new javax.swing.JMenuItem();
        jMenuItem1RPengiriman = new javax.swing.JMenuItem();
        jMenuItem84 = new javax.swing.JMenuItem();
        jMenu6Tools = new javax.swing.JMenu();
        jMenuItem21 = new javax.swing.JMenuItem();
        jMenuItem41 = new javax.swing.JMenuItem();
        jMenuItem40 = new javax.swing.JMenuItem();
        jMenuItem48 = new javax.swing.JMenuItem();
        jMenu3 = new javax.swing.JMenu();
        jMenuItem8 = new javax.swing.JMenuItem();
        jMenu14 = new javax.swing.JMenu();
        jMenu6 = new javax.swing.JMenu();

        menu1.setLabel("File");
        menuBar1.add(menu1);

        menu2.setLabel("Edit");
        menuBar1.add(menu2);

        setDefaultCloseOperation(javax.swing.WindowConstants.DO_NOTHING_ON_CLOSE);
        addWindowListener(new java.awt.event.WindowAdapter() {
            public void windowActivated(java.awt.event.WindowEvent evt) {
                formWindowActivated(evt);
            }
            public void windowClosing(java.awt.event.WindowEvent evt) {
                formWindowClosing(evt);
            }
        });

        jPanel1.setBackground(new java.awt.Color(0, 102, 102));
        jPanel1.setToolTipText("Cashier");
        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton6.setForeground(new java.awt.Color(204, 255, 255));
        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/exit.png"))); // NOI18N
        jButton6.setText("Logout");
        jButton6.setHorizontalTextPosition(javax.swing.SwingConstants.CENTER);
        jButton6.setVerticalTextPosition(javax.swing.SwingConstants.BOTTOM);
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 10, -1, 60));

        jButton7Cashier.setForeground(new java.awt.Color(204, 255, 255));
        jButton7Cashier.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cashier.png"))); // NOI18N
        jButton7Cashier.setText("Cashier");
        jButton7Cashier.setHorizontalTextPosition(javax.swing.SwingConstants.CENTER);
        jButton7Cashier.setVerticalTextPosition(javax.swing.SwingConstants.BOTTOM);
        jButton7Cashier.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7CashierActionPerformed(evt);
            }
        });
        jPanel1.add(jButton7Cashier, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, -1, 60));

        jButton8InputStock.setForeground(new java.awt.Color(204, 255, 255));
        jButton8InputStock.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/input.png"))); // NOI18N
        jButton8InputStock.setText(" Product");
        jButton8InputStock.setHorizontalTextPosition(javax.swing.SwingConstants.CENTER);
        jButton8InputStock.setVerticalTextPosition(javax.swing.SwingConstants.BOTTOM);
        jButton8InputStock.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8InputStockActionPerformed(evt);
            }
        });
        jPanel1.add(jButton8InputStock, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 10, 80, 60));

        jButton9ReportJual.setForeground(new java.awt.Color(204, 255, 255));
        jButton9ReportJual.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton9ReportJual.setText("Report");
        jButton9ReportJual.setHorizontalTextPosition(javax.swing.SwingConstants.CENTER);
        jButton9ReportJual.setVerticalTextPosition(javax.swing.SwingConstants.BOTTOM);
        jButton9ReportJual.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ReportJualActionPerformed(evt);
            }
        });
        jPanel1.add(jButton9ReportJual, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 10, -1, 60));

        formView.setBackground(new java.awt.Color(102, 102, 255));
        formView.setTabLayoutPolicy(javax.swing.JTabbedPane.SCROLL_TAB_LAYOUT);
        formView.addChangeListener(new javax.swing.event.ChangeListener() {
            public void stateChanged(javax.swing.event.ChangeEvent evt) {
                formViewStateChanged(evt);
            }
        });

        jLabel1.setText("jLabel1");

        jMenu1.setMnemonic('f');
        jMenu1.setText("File   ");

        jMenuItem1UserAkses.setAccelerator(javax.swing.KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_A, java.awt.event.InputEvent.CTRL_MASK));
        jMenuItem1UserAkses.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem1UserAkses.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/User_Shield_20px.png"))); // NOI18N
        jMenuItem1UserAkses.setText("User Access");
        jMenuItem1UserAkses.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem1UserAksesActionPerformed(evt);
            }
        });
        jMenu1.add(jMenuItem1UserAkses);

        jMenuItem2GantiPass.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem2GantiPass.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Password_20px.png"))); // NOI18N
        jMenuItem2GantiPass.setText("Change Password");
        jMenuItem2GantiPass.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem2GantiPassActionPerformed(evt);
            }
        });
        jMenu1.add(jMenuItem2GantiPass);
        jMenu1.add(jSeparator1);

        jMenuItem3.setAccelerator(javax.swing.KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_F4, java.awt.event.InputEvent.SHIFT_MASK));
        jMenuItem3.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Logout_Rounded_Down_20px.png"))); // NOI18N
        jMenuItem3.setText("Logout");
        jMenuItem3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem3ActionPerformed(evt);
            }
        });
        jMenu1.add(jMenuItem3);

        jMenuItem4.setAccelerator(javax.swing.KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_F4, java.awt.event.InputEvent.ALT_MASK));
        jMenuItem4.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Shutdown_20px.png"))); // NOI18N
        jMenuItem4.setText("Exit");
        jMenuItem4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem4ActionPerformed(evt);
            }
        });
        jMenu1.add(jMenuItem4);

        jMenuBar1.add(jMenu1);

        jMenu2.setText("Transaction    ");

        jMenu4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/accounting-22.png"))); // NOI18N
        jMenu4.setText("Accounting            ");
        jMenu4.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem10.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem10.setText("Kas Masuk");
        jMenuItem10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem10ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem10);

        jMenuItem11.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem11.setText("Kas Keluar");
        jMenuItem11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem11ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem11);
        jMenu4.add(jSeparator2);

        jMenuItem12.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem12.setText("Bank Masuk");
        jMenuItem12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem12ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem12);

        jMenuItem13.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem13.setText("Bank Keluar");
        jMenuItem13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem13ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem13);
        jMenu4.add(jSeparator3);

        jMenuItem14.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem14.setText("Operasional Masuk");
        jMenuItem14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem14ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem14);

        jMenuItem15.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem15.setText("Operasional Keluar");
        jMenuItem15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem15ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem15);
        jMenu4.add(jSeparator4);

        jMenuItem16.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem16.setText("Memorial");
        jMenuItem16.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem16ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem16);
        jMenu4.add(jSeparator10);

        jMenuItem60.setText("Pinjaman Karyawan");
        jMenuItem60.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem60ActionPerformed(evt);
            }
        });
        jMenu4.add(jMenuItem60);

        jMenu2.add(jMenu4);

        jMenu7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/approved-22.png"))); // NOI18N
        jMenu7.setText("Payroll");
        jMenu7.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem1.setText("Payroll Karyawan");
        jMenuItem1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem1ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem1);

        jMenuItem82.setText("Payroll Borongan");
        jMenuItem82.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem82ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem82);

        jMenuItem42.setText("Slip Gaji");
        jMenuItem42.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem42ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem42);
        jMenu7.add(jSeparator9);

        jMenuItem46.setText("Pengajuan Lembur");
        jMenuItem46.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem46ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem46);

        jMenuItem58.setText("Pengajuan Cuti");
        jMenuItem58.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem58ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem58);

        jMenuItem65.setText("Pengajuan Ijin");
        jMenuItem65.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem65ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem65);
        jMenu7.add(jSeparator11);

        jMenuItem17.setText("Hari Libur");
        jMenuItem17.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem17ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem17);
        jMenu7.add(jSeparator17);

        jMenuItem59.setText("Form Pengajuan Pinjaman Karyawan");
        jMenuItem59.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem59ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem59);

        jMenuItem78.setText("Bonus Karyawan");
        jMenuItem78.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem78ActionPerformed(evt);
            }
        });
        jMenu7.add(jMenuItem78);
        jMenu7.add(jSeparator20);

        jMenuItem83.setText("Potongan Tunj. Insentif Karyawan");
        jMenu7.add(jMenuItem83);

        jMenu2.add(jMenu7);

        jMenu5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/deliver-food-22.png"))); // NOI18N
        jMenu5.setText("Delivery / Pengiriman");
        jMenu5.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem28.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem28.setText("Delivery/Pengiriman Order");
        jMenuItem28.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem28ActionPerformed(evt);
            }
        });
        jMenu5.add(jMenuItem28);

        jMenuItem43.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem43.setText("Delivery Antar Gudang");
        jMenuItem43.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem43ActionPerformed(evt);
            }
        });
        jMenu5.add(jMenuItem43);

        jMenuItem44.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem44.setText("File Transfer (Antar Gudang)");
        jMenu5.add(jMenuItem44);
        jMenu5.add(jSeparator19);

        jMenuItem69.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem69.setText("Rekapituasi Pengiriman PO");
        jMenuItem69.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem69ActionPerformed(evt);
            }
        });
        jMenu5.add(jMenuItem69);

        jMenu2.add(jMenu5);

        jMenu12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_Delivery_20px_2.png"))); // NOI18N
        jMenu12.setText("Receive             ");
        jMenu12.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem45.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem45.setText("Receive Antar Gudang");
        jMenuItem45.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem45ActionPerformed(evt);
            }
        });
        jMenu12.add(jMenuItem45);

        jMenuItem47.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem47.setText("Entry Receive");
        jMenuItem47.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem47ActionPerformed(evt);
            }
        });
        jMenu12.add(jMenuItem47);

        jMenu2.add(jMenu12);

        jMenu11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/price-tag-usd-22.png"))); // NOI18N
        jMenu11.setText("Purchase / Pembelian");
        jMenu11.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem35.setText("Purchase Request / Permintaan Pembelian (PR) ");
        jMenuItem35.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem35ActionPerformed(evt);
            }
        });
        jMenu11.add(jMenuItem35);

        jMenuItem36.setText("Purchase Order / Order Pesanan (PO) ");
        jMenuItem36.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem36ActionPerformed(evt);
            }
        });
        jMenu11.add(jMenuItem36);

        jMenuItem50.setText("Billing of Purchase Order / Penagihan");
        jMenuItem50.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem50ActionPerformed(evt);
            }
        });
        jMenu11.add(jMenuItem50);

        jMenuItem54.setText("Setting Bill PO & Terima Barang (Material Masuk)");
        jMenuItem54.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem54ActionPerformed(evt);
            }
        });
        jMenu11.add(jMenuItem54);

        jMenuItem37.setText("Payment Order / Pembayaran Order");
        jMenuItem37.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem37ActionPerformed(evt);
            }
        });
        jMenu11.add(jMenuItem37);

        jMenu2.add(jMenu11);

        jMenu9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/product-management-22.png"))); // NOI18N
        jMenu9.setText("Raw Material / Bahan Baku");
        jMenu9.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem18.setText("Raw Material In (Masuk)");
        jMenuItem18.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem18ActionPerformed(evt);
            }
        });
        jMenu9.add(jMenuItem18);

        jMenuItem19.setText("Raw Material Out (Keluar)");
        jMenuItem19.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem19ActionPerformed(evt);
            }
        });
        jMenu9.add(jMenuItem19);

        jMenuItem75.setText("Penyesuaian Stock");
        jMenuItem75.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem75ActionPerformed(evt);
            }
        });
        jMenu9.add(jMenuItem75);
        jMenu9.add(jSeparator12);

        jMenuItem20.setText("Raw Material Return");
        jMenuItem20.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem20ActionPerformed(evt);
            }
        });
        jMenu9.add(jMenuItem20);

        jMenuItem22.setText("Raw Material Return Produksi");
        jMenuItem22.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem22ActionPerformed(evt);
            }
        });
        jMenu9.add(jMenuItem22);

        jMenu2.add(jMenu9);

        jMenu10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/purchase-22.png"))); // NOI18N
        jMenu10.setText("Sales Order            ");
        jMenu10.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem24.setText("Orders");
        jMenuItem24.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem24ActionPerformed(evt);
            }
        });
        jMenu10.add(jMenuItem24);

        jMenuItem25.setText("Pembayaran Bon/Cicilan");
        jMenuItem25.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem25ActionPerformed(evt);
            }
        });
        jMenu10.add(jMenuItem25);

        jMenuItem26.setText("Sale Return");
        jMenuItem26.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem26ActionPerformed(evt);
            }
        });
        jMenu10.add(jMenuItem26);

        jMenuItem27.setText("Invoice");
        jMenuItem27.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem27ActionPerformed(evt);
            }
        });
        jMenu10.add(jMenuItem27);

        jMenu2.add(jMenu10);

        jMenu16.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/product-management-22.png"))); // NOI18N
        jMenu16.setText("Production");
        jMenu16.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem68.setText("Pre Order (Customer)");
        jMenuItem68.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem68ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem68);

        jMenuItem51.setText("Work Order / Perintah Kerja");
        jMenuItem51.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem51ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem51);

        jMenuItem70.setText("Production Process");
        jMenuItem70.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem70ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem70);

        jMenuItem34.setText("Production Process (Utility)");
        jMenuItem34.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem34ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem34);

        jMenuItem71.setText("Box Packaging");
        jMenuItem71.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem71ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem71);

        jSeparator15.setForeground(new java.awt.Color(204, 255, 255));
        jMenu16.add(jSeparator15);

        jMenuItem30.setText("Verifikasi Produk Jadi ");
        jMenuItem30.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem30ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem30);
        jMenu16.add(jSeparator16);

        jMenuItem72.setText("Stock Production");
        jMenuItem72.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem72ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem72);

        jMenuItem73.setText("Stock Production (Check)");
        jMenuItem73.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem73ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem73);

        jMenuItem74.setText("Return Customer");
        jMenuItem74.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem74ActionPerformed(evt);
            }
        });
        jMenu16.add(jMenuItem74);

        jMenu2.add(jMenu16);

        jMenu8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/product-management-22.png"))); // NOI18N
        jMenu8.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenu8.setLabel("Borongan ");

        jMenuItem61.setText("Data Protol");
        jMenuItem61.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem61ActionPerformed(evt);
            }
        });
        jMenu8.add(jMenuItem61);

        jMenuItem62.setText("Data Packing");
        jMenuItem62.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem62ActionPerformed(evt);
            }
        });
        jMenu8.add(jMenuItem62);
        jMenu8.add(jSeparator18);

        jMenuItem66.setText("Rekap Data Borongan");
        jMenuItem66.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem66ActionPerformed(evt);
            }
        });
        jMenu8.add(jMenuItem66);

        jMenu2.add(jMenu8);

        jMenuItem77.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem77.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/business-report-22.png"))); // NOI18N
        jMenuItem77.setText("Form Pengajuan Operasional");
        jMenuItem77.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem77ActionPerformed(evt);
            }
        });
        jMenu2.add(jMenuItem77);

        jMenuItem80.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem80.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/business-report-22.png"))); // NOI18N
        jMenuItem80.setText("Form Penawaran Customer");
        jMenuItem80.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem80ActionPerformed(evt);
            }
        });
        jMenu2.add(jMenuItem80);

        jMenuItem81.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem81.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/price-tag-usd-22.png"))); // NOI18N
        jMenuItem81.setText("Marketing Fee");
        jMenuItem81.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem81ActionPerformed(evt);
            }
        });
        jMenu2.add(jMenuItem81);

        jMenuBar1.add(jMenu2);

        jMenu4Master.setMnemonic('m');
        jMenu4Master.setText("Master    ");

        jMenuItem39.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem39.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jMenuItem39.setText(" Master Account (COA)");
        jMenuItem39.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem39ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem39);

        jMenuItem2.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/company-22.png"))); // NOI18N
        jMenuItem2.setText(" Company");
        jMenuItem2.setToolTipText("");
        jMenuItem2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem2ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem2);

        jMenuItem5.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/warehouse-20.png"))); // NOI18N
        jMenuItem5.setText(" Master Gudang");
        jMenuItem5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem5ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem5);
        jMenu4Master.add(jSeparator5);

        jMenuItem5MasterProduct.setAccelerator(javax.swing.KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_P, java.awt.event.InputEvent.ALT_MASK));
        jMenuItem5MasterProduct.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem5MasterProduct.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Input_20px.png"))); // NOI18N
        jMenuItem5MasterProduct.setText(" Master Product            ");
        jMenuItem5MasterProduct.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem5MasterProductActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem5MasterProduct);

        jMenu15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/new-product-22.png"))); // NOI18N
        jMenu15.setText(" Master Production");
        jMenu15.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem67.setText("Bill of Material & Cost");
        jMenuItem67.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem67ActionPerformed(evt);
            }
        });
        jMenu15.add(jMenuItem67);

        jMenuItem76.setText("Cost Produksi");
        jMenuItem76.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem76ActionPerformed(evt);
            }
        });
        jMenu15.add(jMenuItem76);

        jMenuItem57.setText("Daftar Mesin");
        jMenuItem57.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem57ActionPerformed(evt);
            }
        });
        jMenu15.add(jMenuItem57);
        jMenu15.add(jSeparator13);

        jMenuItem53.setText("Step Production");
        jMenuItem53.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem53ActionPerformed(evt);
            }
        });
        jMenu15.add(jMenuItem53);

        jMenuItem56.setText("Production Fail");
        jMenuItem56.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem56ActionPerformed(evt);
            }
        });
        jMenu15.add(jMenuItem56);
        jMenu15.add(jSeparator14);

        jMenu4Master.add(jMenu15);
        jMenu4Master.add(jSeparator6);

        jMenuItem9MasterSupplier.setAccelerator(javax.swing.KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_S, java.awt.event.InputEvent.ALT_MASK));
        jMenuItem9MasterSupplier.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem9MasterSupplier.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_Supplier_20px.png"))); // NOI18N
        jMenuItem9MasterSupplier.setText(" Master Supplier            ");
        jMenuItem9MasterSupplier.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem9MasterSupplierActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem9MasterSupplier);

        jMenuItem10Member.setAccelerator(javax.swing.KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_M, java.awt.event.InputEvent.ALT_MASK));
        jMenuItem10Member.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem10Member.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_Customer_Support_20px.png"))); // NOI18N
        jMenuItem10Member.setText(" Customers / Members            ");
        jMenuItem10Member.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem10MemberActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem10Member);
        jMenu4Master.add(jSeparator7);

        jMenuItem1MKaryawan.setAccelerator(javax.swing.KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_K, java.awt.event.InputEvent.ALT_MASK));
        jMenuItem1MKaryawan.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem1MKaryawan.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_User_Groups_20px.png"))); // NOI18N
        jMenuItem1MKaryawan.setText(" Master Karyawan            ");
        jMenuItem1MKaryawan.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem1MKaryawanActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem1MKaryawan);

        jMenuItem55.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem55.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/job-15.png"))); // NOI18N
        jMenuItem55.setText(" Master Departemen");
        jMenuItem55.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem55ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem55);

        jMenuItem38.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem38.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/job-15.png"))); // NOI18N
        jMenuItem38.setText(" Master Jabatan");
        jMenuItem38.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem38ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem38);

        jMenuItem9.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/money-transfer-15.png"))); // NOI18N
        jMenuItem9.setText(" Master Payroll Default");
        jMenuItem9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem9ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem9);

        jMenu13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/money-transfer-15.png"))); // NOI18N
        jMenu13.setText(" Master Gaji Borongan");
        jMenu13.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N

        jMenuItem63.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem63.setText(" Master Gaji Protol");
        jMenuItem63.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem63ActionPerformed(evt);
            }
        });
        jMenu13.add(jMenuItem63);

        jMenuItem64.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem64.setText(" Master Gaji Packing");
        jMenuItem64.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem64ActionPerformed(evt);
            }
        });
        jMenu13.add(jMenuItem64);

        jMenuItem79.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem79.setText(" Tunjangan Borongan");
        jMenuItem79.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem79ActionPerformed(evt);
            }
        });
        jMenu13.add(jMenuItem79);

        jMenu4Master.add(jMenu13);
        jMenu4Master.add(jSeparator8);

        jMenuItem6.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/project-setup-22.png"))); // NOI18N
        jMenuItem6.setText(" Master Material Produksi");
        jMenuItem6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem6ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem6);

        jMenuItem23.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem23.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/accounting-22.png"))); // NOI18N
        jMenuItem23.setText(" Master Operasional");
        jMenuItem23.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem23ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem23);

        jMenuItem29.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem29.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/price-tag-usd-22.png"))); // NOI18N
        jMenuItem29.setText(" Master Payment");
        jMenuItem29.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem29ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem29);

        jMenuItem49.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem49.setText(" Master Satuan");
        jMenuItem49.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem49ActionPerformed(evt);
            }
        });
        jMenu4Master.add(jMenuItem49);

        jMenuBar1.add(jMenu4Master);

        jMenu5Report.setMnemonic('r');
        jMenu5Report.setText("Report   ");

        jMenuItem32.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem32.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_Customer_Support_20px.png"))); // NOI18N
        jMenuItem32.setText("Report Production");
        jMenuItem32.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem32ActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem32);

        jMenuItem52.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem52.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/purchase-22.png"))); // NOI18N
        jMenuItem52.setText("Report Purchasing");
        jMenuItem52.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem52ActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem52);

        jMenuItem31.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem31.setText("Report Material Stock");
        jMenuItem31.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem31ActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem31);

        jMenuItem6RStockProduct.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem6RStockProduct.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jMenuItem6RStockProduct.setText("Report Stock Product            ");
        jMenuItem6RStockProduct.setRequestFocusEnabled(false);
        jMenuItem6RStockProduct.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem6RStockProductActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem6RStockProduct);

        jMenuItem7.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jMenuItem7.setText("Report Penjualan            ");
        jMenuItem7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem7ActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem7);

        jMenuItem33.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem33.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Area_Chart_20px.png"))); // NOI18N
        jMenuItem33.setText("Analisa Transaksi");
        jMenuItem33.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem33ActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem33);

        jMenuItem14RPenjualan.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem14RPenjualan.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Area_Chart_20px.png"))); // NOI18N
        jMenuItem14RPenjualan.setText("Analisa Report Penjualan            ");
        jMenuItem14RPenjualan.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem14RPenjualanActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem14RPenjualan);

        jMenuItem1RPengiriman.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem1RPengiriman.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_Delivery_20px_2.png"))); // NOI18N
        jMenuItem1RPengiriman.setText("Report Pengiriman");
        jMenuItem1RPengiriman.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem1RPengirimanActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem1RPengiriman);

        jMenuItem84.setFont(new java.awt.Font("Segoe UI", 0, 14)); // NOI18N
        jMenuItem84.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jMenuItem84.setText("Report Payroll");
        jMenuItem84.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem84ActionPerformed(evt);
            }
        });
        jMenu5Report.add(jMenuItem84);

        jMenuBar1.add(jMenu5Report);

        jMenu6Tools.setMnemonic('t');
        jMenu6Tools.setText("Tools    ");

        jMenuItem21.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/absen-leave-22.png"))); // NOI18N
        jMenuItem21.setText("Upload Absensi");
        jMenuItem21.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem21ActionPerformed(evt);
            }
        });
        jMenu6Tools.add(jMenuItem21);

        jMenuItem41.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/absen-leave-22.png"))); // NOI18N
        jMenuItem41.setText("Rekap Absensi");
        jMenuItem41.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem41ActionPerformed(evt);
            }
        });
        jMenu6Tools.add(jMenuItem41);

        jMenuItem40.setText("Backup Data (Database)");
        jMenuItem40.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem40ActionPerformed(evt);
            }
        });
        jMenu6Tools.add(jMenuItem40);

        jMenuItem48.setText("Default Data");
        jMenuItem48.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem48ActionPerformed(evt);
            }
        });
        jMenu6Tools.add(jMenuItem48);

        jMenuBar1.add(jMenu6Tools);

        jMenu3.setText("Help     ");

        jMenuItem8.setFont(new java.awt.Font("Segoe UI", 1, 12)); // NOI18N
        jMenuItem8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8_About_20px.png"))); // NOI18N
        jMenuItem8.setText("About          ");
        jMenuItem8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenuItem8ActionPerformed(evt);
            }
        });
        jMenu3.add(jMenuItem8);

        jMenuBar1.add(jMenu3);

        jMenu14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/icons8-notification-15.png"))); // NOI18N
        jMenu14.setText("Notification");
        jMenu14.addMenuKeyListener(new javax.swing.event.MenuKeyListener() {
            public void menuKeyPressed(javax.swing.event.MenuKeyEvent evt) {
            }
            public void menuKeyReleased(javax.swing.event.MenuKeyEvent evt) {
                jMenu14MenuKeyReleased(evt);
            }
            public void menuKeyTyped(javax.swing.event.MenuKeyEvent evt) {
            }
        });
        jMenu14.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jMenu14MouseClicked(evt);
            }
        });
        jMenu14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jMenu14ActionPerformed(evt);
            }
        });
        jMenuBar1.add(jMenu14);

        jMenu6.setForeground(new java.awt.Color(102, 102, 102));
        jMenu6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/User_Shield_20px.png"))); // NOI18N
        jMenu6.setText("Hai Kamu...");
        jMenu6.setCursor(new java.awt.Cursor(java.awt.Cursor.DEFAULT_CURSOR));
        jMenu6.setFocusable(false);
        jMenu6.setHorizontalAlignment(javax.swing.SwingConstants.TRAILING);
        jMenu6.setMargin(new java.awt.Insets(0, 10, 0, 10));
        jMenuBar1.add(jMenu6);

        setJMenuBar(jMenuBar1);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 924, Short.MAX_VALUE)
            .addComponent(formView)
            .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                .addGroup(layout.createSequentialGroup()
                    .addGap(0, 0, Short.MAX_VALUE)
                    .addComponent(jLabel1, javax.swing.GroupLayout.PREFERRED_SIZE, 0, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addGap(0, 0, Short.MAX_VALUE)))
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(formView, javax.swing.GroupLayout.DEFAULT_SIZE, 388, Short.MAX_VALUE))
            .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                .addGroup(layout.createSequentialGroup()
                    .addGap(0, 0, Short.MAX_VALUE)
                    .addComponent(jLabel1, javax.swing.GroupLayout.PREFERRED_SIZE, 0, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addGap(0, 0, Short.MAX_VALUE)))
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        int tutup = JOptionPane.showConfirmDialog(this,
        "Apakah Anda Ingin Menutup Aplikasi Ini ?","Exit",
        JOptionPane.YES_NO_OPTION);
        if (tutup==0)
        {
            this.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
            Login a = new Login();
            a.setVisible(true);     
            dispose();
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jButton7CashierActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7CashierActionPerformed
        CashierFull a = new CashierFull();
        a.setVisible(true);
        
        /*
        String nameForm = "Cashier";
        if(openForm(nameForm)){
            panelOpen = new Cashier();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        */
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jButton7CashierActionPerformed

    private void jButton8InputStockActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8InputStockActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Master Produk";
        if(openForm(nameForm)){
            panelOpen = new MProduk(nameForm);
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR)); 
    }//GEN-LAST:event_jButton8InputStockActionPerformed

    private void jButton9ReportJualActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ReportJualActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Laporan Penjualan";
        if(openForm(nameForm)){
            panelOpen = new RPenjualan();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jButton9ReportJualActionPerformed

    private void formViewStateChanged(javax.swing.event.ChangeEvent evt) {//GEN-FIRST:event_formViewStateChanged
        for (int i = 0; i < formView.getTabCount(); i++) {
            if (formView.getTitleAt(i).equalsIgnoreCase("Master Karyawan")) {
//                JTextField jTextField1 = MKaryawan.jTextField1;
//                jTextField1.requestFocus();
            }
        }
    }//GEN-LAST:event_formViewStateChanged

    private void jMenuItem1UserAksesActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem1UserAksesActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "User Akses";
        if(openForm(nameForm)){
            panelOpen = new UserAkses();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem1UserAksesActionPerformed

    private void jMenuItem2GantiPassActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem2GantiPassActionPerformed
        ChangePassword a = new ChangePassword();
        a.setVisible(true);
        if(a.status){
            System.out.println("sss");
            this.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
            Login aa = new Login();
            aa.setVisible(true);     
            dispose();
        }
    }//GEN-LAST:event_jMenuItem2GantiPassActionPerformed

    private void jMenuItem3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem3ActionPerformed
        Login a = new Login();
        a.setVisible(true);     
        dispose();    
    }//GEN-LAST:event_jMenuItem3ActionPerformed

    private void jMenuItem4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem4ActionPerformed
        System.exit(0);     
    }//GEN-LAST:event_jMenuItem4ActionPerformed

    private void jMenuItem10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem10ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "TranKasIn";
        if(openForm(nameForm)){
            panelOpen = new TranKasIn("IN");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem10ActionPerformed

    private void jMenuItem11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem11ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "TranKasOut";
        if(openForm(nameForm)){
            panelOpen = new TranKasOut("OUT");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem11ActionPerformed

    private void jMenuItem12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem12ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "TranBankIn";
        if(openForm(nameForm)){
            panelOpen = new TranBankIn("IN");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem12ActionPerformed

    private void jMenuItem13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem13ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "TranBankOut";
        if(openForm(nameForm)){
            panelOpen = new TranBankOut("OUT");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem13ActionPerformed

    private void jMenuItem14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem14ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "OperasionalIn";
        if(openForm(nameForm)){
            panelOpen = new TranOpIn("IN");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem14ActionPerformed

    private void jMenuItem15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem15ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "OperasionalOut";
        if(openForm(nameForm)){
            panelOpen = new TranOpOut("OUT");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem15ActionPerformed

    private void jMenuItem16ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem16ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "TranMemorial";
        if(openForm(nameForm)){
            panelOpen = new TranMemorial("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem16ActionPerformed

    private void jMenuItem1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem1ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem1.getText();
        if(openForm(nameForm)){
            panelOpen = new PayrollKaryawan("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem1ActionPerformed

    private void jMenuItem42ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem42ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem42.getText();
        if(openForm(nameForm)){
            panelOpen = new SlipGaji();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem42ActionPerformed

    private void jMenuItem46ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem46ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem46.getText();
        if(openForm(nameForm)){
            panelOpen = new Lembur("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem46ActionPerformed

    private void jMenuItem58ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem58ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem58.getText();
        if(openForm(nameForm)){
            panelOpen = new Cuti("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem58ActionPerformed

    private void jMenuItem65ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem65ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem65.getText();
        if(openForm(nameForm)){
            panelOpen = new Ijin("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem65ActionPerformed

    private void jMenuItem17ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem17ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem17.getText();
        if(openForm(nameForm)){
            panelOpen = new Holidays("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem17ActionPerformed

    private void jMenuItem28ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem28ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem28.getText();
        if(openForm(nameForm)){
            panelOpen = new DeliveryOrder("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem28ActionPerformed

    private void jMenuItem43ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem43ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem43.getText();
        if(openForm(nameForm)){
            panelOpen = new DeliveryAntarGudang("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem43ActionPerformed

    private void jMenuItem45ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem45ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem45.getText();
        if(openForm(nameForm)){
            panelOpen = new ReceiveAntarGudang("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem45ActionPerformed

    private void jMenuItem47ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem47ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem47.getText();
        if(openForm(nameForm)){
            panelOpen = new EntryReceive();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem47ActionPerformed

    private void jMenuItem35ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem35ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem35.getText();
        if(openForm(nameForm)){
            panelOpen = new PurchaseRequest("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem35ActionPerformed

    private void jMenuItem36ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem36ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem36.getText();
        if(openForm(nameForm)){
            panelOpen = new PurchaseOrder("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem36ActionPerformed

    private void jMenuItem50ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem50ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem50.getText();
        if(openForm(nameForm)){
            panelOpen = new PurchaseOrderBilling("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem50ActionPerformed

    private void jMenuItem54ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem54ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem54.getText();
        if(openForm(nameForm)){
            panelOpen = new SetBIllPOMaterial("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem54ActionPerformed

    private void jMenuItem37ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem37ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem37.getText();
        if(openForm(nameForm)){
            panelOpen = new PaymentPurchase("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem37ActionPerformed

    private void jMenuItem18ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem18ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem18.getText();
        if(openForm(nameForm)){
            panelOpen = new RawMaterialIn("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem18ActionPerformed

    private void jMenuItem19ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem19ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem19.getText();
        if(openForm(nameForm)){
            panelOpen = new RawMaterialOut("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem19ActionPerformed

    private void jMenuItem75ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem75ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem75.getText();
        if(openForm(nameForm)){
            panelOpen = new RawMaterialAdjust("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem75ActionPerformed

    private void jMenuItem20ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem20ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem20.getText();
        if(openForm(nameForm)){
            panelOpen = new RawMateriaReturn("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    
    }//GEN-LAST:event_jMenuItem20ActionPerformed

    private void jMenuItem22ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem22ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem22.getText();
        if(openForm(nameForm)){
            panelOpen = new RawMaterialReturnProduksi("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem22ActionPerformed

    private void jMenuItem24ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem24ActionPerformed
         CashierFull a = new CashierFull();
        a.setVisible(true);
    }//GEN-LAST:event_jMenuItem24ActionPerformed

    private void jMenuItem25ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem25ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Pembayaran BON";
        if(openForm(nameForm)){
            panelOpen = new PembayaranBon();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem25ActionPerformed

    private void jMenuItem26ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem26ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Return Penjualan";
        if(openForm(nameForm)){
            panelOpen = new ReturnPenjualan();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem26ActionPerformed

    private void jMenuItem27ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem27ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Invoice";
        if(openForm(nameForm)){
            panelOpen = new PenjualanUtility();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem27ActionPerformed

    private void jMenuItem68ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem68ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem68.getText();
        if(openForm(nameForm)){
            panelOpen = new PreCustomer("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem68ActionPerformed

    private void jMenuItem51ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem51ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem51.getText();
        if(openForm(nameForm)){
            panelOpen = new WorkOrder("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem51ActionPerformed

    private void jMenuItem70ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem70ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem70.getText();
        if(openForm(nameForm)){
            panelOpen = new ProduksiInput2(sql);
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem70ActionPerformed

    private void jMenuItem34ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem34ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem34.getText();
        if(openForm(nameForm)){
            panelOpen = new ProduksiInputUtility();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem34ActionPerformed

    private void jMenuItem71ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem71ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem71.getText();
        if(openForm(nameForm)){
            panelOpen = new Packaging1("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem71ActionPerformed

    private void jMenuItem30ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem30ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem30.getText();
        if(openForm(nameForm)){
            panelOpen = new VerifikasiHasilProduksi();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem30ActionPerformed

    private void jMenuItem72ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem72ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem72.getText();
        if(openForm(nameForm)){
            panelOpen = new ProductionStock();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem72ActionPerformed

    private void jMenuItem73ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem73ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem73.getText();
        if(openForm(nameForm)){
            panelOpen = new ProductionStockCheck();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem73ActionPerformed

    private void jMenuItem74ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem74ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem74.getText();
        if(openForm(nameForm)){
            panelOpen = new PackagingReturn("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem74ActionPerformed

    private void jMenuItem39ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem39ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem39.getText();
        if(openForm(nameForm)){
            panelOpen = new MAccount("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem39ActionPerformed

    private void jMenuItem2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem2ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem2.getText();
        if(openForm(nameForm)){
            panelOpen = new MCompany();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem2ActionPerformed

    private void jMenuItem5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem5ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem5.getText();
        if(openForm(nameForm)){
            panelOpen = new MGudang("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem5ActionPerformed

    private void jMenuItem5MasterProductActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem5MasterProductActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Master Produk";
        if(openForm(nameForm)){
            panelOpen = new MProduk(nameForm);
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR)); 
    }//GEN-LAST:event_jMenuItem5MasterProductActionPerformed

    private void jMenuItem67ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem67ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem67.getText();
        if(openForm(nameForm)){
            panelOpen = new BOMProduct("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem67ActionPerformed

    private void jMenuItem76ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem76ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem76.getText();
        if(openForm(nameForm)){
            panelOpen = new MCostProduksi();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem76ActionPerformed

    private void jMenuItem57ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem57ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem57.getText();
        if(openForm(nameForm)){
            panelOpen = new MMesin();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem57ActionPerformed

    private void jMenuItem53ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem53ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem53.getText();
        if(openForm(nameForm)){
            panelOpen = new MStepProduksi();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem53ActionPerformed

    private void jMenuItem56ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem56ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem56.getText();
        if(openForm(nameForm)){
            panelOpen = new MErrorProduction();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem56ActionPerformed

    private void jMenuItem9MasterSupplierActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem9MasterSupplierActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Master Supplier";
        if(openForm(nameForm)){
            panelOpen = new MSupplier();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem9MasterSupplierActionPerformed

    private void jMenuItem10MemberActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem10MemberActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Master Members";
        if(openForm(nameForm)){
            panelOpen = new MMembers();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem10MemberActionPerformed

    private void jMenuItem1MKaryawanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem1MKaryawanActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Master Karyawan";
        if(openForm(nameForm)){
            panelOpen = new MKaryawan("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem1MKaryawanActionPerformed

    private void jMenuItem38ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem38ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem38.getText();
        if(openForm(nameForm)){
            panelOpen = new MJabatan("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem38ActionPerformed

    private void jMenuItem9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem9ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem9.getText();
        if(openForm(nameForm)){
            panelOpen = new MPayrollDefault("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem9ActionPerformed

    private void jMenuItem6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem6ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem6.getText();
        if(openForm(nameForm)){
            panelOpen = new MMaterial("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem6ActionPerformed

    private void jMenuItem23ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem23ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem23.getText();
        if(openForm(nameForm)){
            panelOpen = new MOperasional();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem23ActionPerformed

    private void jMenuItem29ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem29ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem29.getText();
        if(openForm(nameForm)){
            panelOpen = new MPayment("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem29ActionPerformed

    private void jMenuItem49ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem49ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem49.getText();
        if(openForm(nameForm)){
            panelOpen = new MSatuan();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem49ActionPerformed

    private void jMenuItem32ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem32ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem32.getText();
        if(openForm(nameForm)){
            panelOpen = new RProduction("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem32ActionPerformed

    private void jMenuItem52ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem52ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem52.getText();
        if(openForm(nameForm)){
            panelOpen = new RPurchasing("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem52ActionPerformed

    private void jMenuItem31ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem31ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem31.getText();
        if(openForm(nameForm)){
            panelOpen = new RMaterial("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem31ActionPerformed

    private void jMenuItem6RStockProductActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem6RStockProductActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem6RStockProduct.getText();
        if(openForm(nameForm)){
            panelOpen = new RProduk("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem6RStockProductActionPerformed

    private void jMenuItem7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem7ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Laporan Penjualan";
        if(openForm(nameForm)){
            panelOpen = new RPenjualan();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem7ActionPerformed

    private void jMenuItem33ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem33ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem33.getText();
        if(openForm(nameForm)){
            panelOpen = new RAnalisaTransaksi("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem33ActionPerformed

    private void jMenuItem14RPenjualanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem14RPenjualanActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = "Laporan Analisa Penjualan";
        if(openForm(nameForm)){
            panelOpen = new RAnalisaPenjualan();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem14RPenjualanActionPerformed

    private void jMenuItem1RPengirimanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem1RPengirimanActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jMenuItem1RPengirimanActionPerformed

    private void jMenuItem21ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem21ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem21.getText();
        if(openForm(nameForm)){
            panelOpen = new UploadAbsensi("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem21ActionPerformed

    private void jMenuItem41ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem41ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem41.getText();
        if(openForm(nameForm)){
            panelOpen = new RekapAbsensi("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR)); 
    }//GEN-LAST:event_jMenuItem41ActionPerformed

    private void jMenuItem40ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem40ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jMenuItem40ActionPerformed

    private void jMenuItem48ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem48ActionPerformed
        DefaultProgram a = new DefaultProgram();
        a.setVisible(true);
    }//GEN-LAST:event_jMenuItem48ActionPerformed

    private void jMenuItem8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem8ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jMenuItem8ActionPerformed

    private void jMenu14MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jMenu14MouseClicked
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        DialogNotif a = new DialogNotif();
        a.setVisible(true);
        if(a.status){
           
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenu14MouseClicked

    private void jMenu14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenu14ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jMenu14ActionPerformed

    private void formWindowActivated(java.awt.event.WindowEvent evt) {//GEN-FIRST:event_formWindowActivated
        this.setTitle(rfa.getCmpERP(keyssDef,"INK"));
    }//GEN-LAST:event_formWindowActivated

    private void formWindowClosing(java.awt.event.WindowEvent evt) {//GEN-FIRST:event_formWindowClosing
        int tutup = JOptionPane.showConfirmDialog(this,
        "Apakah Anda Ingin Menutup Aplikasi Ini ?","Exit",
        JOptionPane.YES_NO_OPTION);
        if (tutup==0)
        {
            try{
                updateThread.stop();
                updateThread = null;
            } catch (Exception e){}
            
            this.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        }
    }//GEN-LAST:event_formWindowClosing

    private void jMenuItem55ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem55ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem55.getText();
        if(openForm(nameForm)){
            panelOpen = new MDept("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem55ActionPerformed

    private void jMenu14MenuKeyReleased(javax.swing.event.MenuKeyEvent evt) {//GEN-FIRST:event_jMenu14MenuKeyReleased
        // TODO add your handling code here:
    }//GEN-LAST:event_jMenu14MenuKeyReleased

    private void jMenuItem59ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem59ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem59.getText();
        if(openForm(nameForm)){
            panelOpen = new FormPinjamanKaryawan("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem59ActionPerformed

    private void jMenuItem63ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem63ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem63.getText();
        if(openForm(nameForm)){
            panelOpen = new MPriceBoronganProtol();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem63ActionPerformed

    private void jMenuItem64ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem64ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem64.getText();
        if(openForm(nameForm)){
            panelOpen = new MPriceBoronganPack();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem64ActionPerformed

    private void jMenuItem61ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem61ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem61.getText();
        if(openForm(nameForm)){
            panelOpen = new BoronganProtol("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem61ActionPerformed

    private void jMenuItem62ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem62ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem62.getText();
        if(openForm(nameForm)){
            panelOpen = new BoronganPacking("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem62ActionPerformed

    private void jMenuItem66ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem66ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem66.getText();
        if(openForm(nameForm)){
            panelOpen = new BoronganRekap("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem66ActionPerformed

    private void jMenuItem69ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem69ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem69.getText();
        if(openForm(nameForm)){
            panelOpen = new DeliveryPORekap();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem69ActionPerformed

    private void jMenuItem60ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem60ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem60.getText();
        if(openForm(nameForm)){
            panelOpen = new TranPinjaman("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem60ActionPerformed

    private void jMenuItem77ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem77ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem77.getText();
        if(openForm(nameForm)){
            panelOpen = new FormOperasional("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem77ActionPerformed

    private void jMenuItem78ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem78ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem78.getText();
        if(openForm(nameForm)){
            panelOpen = new BonusKaryawan("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem78ActionPerformed

    private void jMenuItem80ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem80ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem80.getText();
        if(openForm(nameForm)){
            panelOpen = new FormPenawaran("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem80ActionPerformed

    private void jMenuItem81ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem81ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem81.getText();
        if(openForm(nameForm)){
            panelOpen = new RMarketingFee("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem81ActionPerformed

    private void jMenuItem79ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem79ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem79.getText();
        if(openForm(nameForm)){
            panelOpen = new MPriceBoronganTunj();
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem79ActionPerformed

    private void jMenuItem82ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem82ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem82.getText();
        if(openForm(nameForm)){
            panelOpen = new PayrollBorongan("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem82ActionPerformed

    private void jMenuItem84ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jMenuItem84ActionPerformed
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        String nameForm = jMenuItem84.getText();
        if(openForm(nameForm)){
            panelOpen = new RPayroll("");
            formView.add(nameForm, panelOpen);
            formView.setSelectedIndex(selectedTab);
        }
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jMenuItem84ActionPerformed

    private void SuaraOn(String fileName) {
        InputStream music;
        try{
            music=new FileInputStream(new File("setting\\"+fileName)+".WAV");
            AudioStream audios=new AudioStream(music);
            AudioPlayer.player.start(audios); //play
            JOptionPane.showMessageDialog(this,"Anda Mempunyai Beberapa Notifikasi", "Info", JOptionPane.INFORMATION_MESSAGE);
        }
        catch(Exception e){
        } 
    }
    Thread updateThread = null;
    
    /**
     * @param args the command line arguments
     */
    public static void main(String args[]) {
        /* Set the Nimbus look and feel */
        //<editor-fold defaultstate="collapsed" desc=" Look and feel setting code (optional) ">
        /* If Nimbus (introduced in Java SE 6) is not available, stay with the default look and feel.
         * For details see http://download.oracle.com/javase/tutorial/uiswing/lookandfeel/plaf.html 
         */
        try {
            for (javax.swing.UIManager.LookAndFeelInfo info : javax.swing.UIManager.getInstalledLookAndFeels()) {
                if ("Nimbus".equals(info.getName())) {
                    javax.swing.UIManager.setLookAndFeel(info.getClassName());
                    break;
                }
            }
        } catch (ClassNotFoundException ex) {
            java.util.logging.Logger.getLogger(MenuAdmin.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        } catch (InstantiationException ex) {
            java.util.logging.Logger.getLogger(MenuAdmin.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        } catch (IllegalAccessException ex) {
            java.util.logging.Logger.getLogger(MenuAdmin.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        } catch (javax.swing.UnsupportedLookAndFeelException ex) {
            java.util.logging.Logger.getLogger(MenuAdmin.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        }
        //</editor-fold>

        /* Create and display the form */
        java.awt.EventQueue.invokeLater(new Runnable() {
            public void run() {
                new MenuAdmin().setVisible(true);
            }
        });
    }

    // Variables declaration - do not modify//GEN-BEGIN:variables
    public static javax.swing.JTabbedPane formView;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7Cashier;
    private javax.swing.JButton jButton8InputStock;
    private javax.swing.JButton jButton9ReportJual;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JMenu jMenu1;
    private javax.swing.JMenu jMenu10;
    private javax.swing.JMenu jMenu11;
    private javax.swing.JMenu jMenu12;
    private javax.swing.JMenu jMenu13;
    private javax.swing.JMenu jMenu14;
    private javax.swing.JMenu jMenu15;
    private javax.swing.JMenu jMenu16;
    private javax.swing.JMenu jMenu2;
    private javax.swing.JMenu jMenu3;
    private javax.swing.JMenu jMenu4;
    private javax.swing.JMenu jMenu4Master;
    private javax.swing.JMenu jMenu5;
    private javax.swing.JMenu jMenu5Report;
    private javax.swing.JMenu jMenu6;
    private javax.swing.JMenu jMenu6Tools;
    private javax.swing.JMenu jMenu7;
    private javax.swing.JMenu jMenu8;
    private javax.swing.JMenu jMenu9;
    private javax.swing.JMenuBar jMenuBar1;
    private javax.swing.JMenuItem jMenuItem1;
    private javax.swing.JMenuItem jMenuItem10;
    private javax.swing.JMenuItem jMenuItem10Member;
    private javax.swing.JMenuItem jMenuItem11;
    private javax.swing.JMenuItem jMenuItem12;
    private javax.swing.JMenuItem jMenuItem13;
    private javax.swing.JMenuItem jMenuItem14;
    private javax.swing.JMenuItem jMenuItem14RPenjualan;
    private javax.swing.JMenuItem jMenuItem15;
    private javax.swing.JMenuItem jMenuItem16;
    private javax.swing.JMenuItem jMenuItem17;
    private javax.swing.JMenuItem jMenuItem18;
    private javax.swing.JMenuItem jMenuItem19;
    private javax.swing.JMenuItem jMenuItem1MKaryawan;
    private javax.swing.JMenuItem jMenuItem1RPengiriman;
    private javax.swing.JMenuItem jMenuItem1UserAkses;
    private javax.swing.JMenuItem jMenuItem2;
    private javax.swing.JMenuItem jMenuItem20;
    private javax.swing.JMenuItem jMenuItem21;
    private javax.swing.JMenuItem jMenuItem22;
    private javax.swing.JMenuItem jMenuItem23;
    private javax.swing.JMenuItem jMenuItem24;
    private javax.swing.JMenuItem jMenuItem25;
    private javax.swing.JMenuItem jMenuItem26;
    private javax.swing.JMenuItem jMenuItem27;
    private javax.swing.JMenuItem jMenuItem28;
    private javax.swing.JMenuItem jMenuItem29;
    private javax.swing.JMenuItem jMenuItem2GantiPass;
    private javax.swing.JMenuItem jMenuItem3;
    private javax.swing.JMenuItem jMenuItem30;
    private javax.swing.JMenuItem jMenuItem31;
    private javax.swing.JMenuItem jMenuItem32;
    private javax.swing.JMenuItem jMenuItem33;
    private javax.swing.JMenuItem jMenuItem34;
    private javax.swing.JMenuItem jMenuItem35;
    private javax.swing.JMenuItem jMenuItem36;
    private javax.swing.JMenuItem jMenuItem37;
    private javax.swing.JMenuItem jMenuItem38;
    private javax.swing.JMenuItem jMenuItem39;
    private javax.swing.JMenuItem jMenuItem4;
    private javax.swing.JMenuItem jMenuItem40;
    private javax.swing.JMenuItem jMenuItem41;
    private javax.swing.JMenuItem jMenuItem42;
    private javax.swing.JMenuItem jMenuItem43;
    private javax.swing.JMenuItem jMenuItem44;
    private javax.swing.JMenuItem jMenuItem45;
    private javax.swing.JMenuItem jMenuItem46;
    private javax.swing.JMenuItem jMenuItem47;
    private javax.swing.JMenuItem jMenuItem48;
    private javax.swing.JMenuItem jMenuItem49;
    private javax.swing.JMenuItem jMenuItem5;
    private javax.swing.JMenuItem jMenuItem50;
    private javax.swing.JMenuItem jMenuItem51;
    private javax.swing.JMenuItem jMenuItem52;
    private javax.swing.JMenuItem jMenuItem53;
    private javax.swing.JMenuItem jMenuItem54;
    private javax.swing.JMenuItem jMenuItem55;
    private javax.swing.JMenuItem jMenuItem56;
    private javax.swing.JMenuItem jMenuItem57;
    private javax.swing.JMenuItem jMenuItem58;
    private javax.swing.JMenuItem jMenuItem59;
    private javax.swing.JMenuItem jMenuItem5MasterProduct;
    private javax.swing.JMenuItem jMenuItem6;
    private javax.swing.JMenuItem jMenuItem60;
    private javax.swing.JMenuItem jMenuItem61;
    private javax.swing.JMenuItem jMenuItem62;
    private javax.swing.JMenuItem jMenuItem63;
    private javax.swing.JMenuItem jMenuItem64;
    private javax.swing.JMenuItem jMenuItem65;
    private javax.swing.JMenuItem jMenuItem66;
    private javax.swing.JMenuItem jMenuItem67;
    private javax.swing.JMenuItem jMenuItem68;
    private javax.swing.JMenuItem jMenuItem69;
    private javax.swing.JMenuItem jMenuItem6RStockProduct;
    private javax.swing.JMenuItem jMenuItem7;
    private javax.swing.JMenuItem jMenuItem70;
    private javax.swing.JMenuItem jMenuItem71;
    private javax.swing.JMenuItem jMenuItem72;
    private javax.swing.JMenuItem jMenuItem73;
    private javax.swing.JMenuItem jMenuItem74;
    private javax.swing.JMenuItem jMenuItem75;
    private javax.swing.JMenuItem jMenuItem76;
    private javax.swing.JMenuItem jMenuItem77;
    private javax.swing.JMenuItem jMenuItem78;
    private javax.swing.JMenuItem jMenuItem79;
    private javax.swing.JMenuItem jMenuItem8;
    private javax.swing.JMenuItem jMenuItem80;
    private javax.swing.JMenuItem jMenuItem81;
    private javax.swing.JMenuItem jMenuItem82;
    private javax.swing.JMenuItem jMenuItem83;
    private javax.swing.JMenuItem jMenuItem84;
    private javax.swing.JMenuItem jMenuItem9;
    private javax.swing.JMenuItem jMenuItem9MasterSupplier;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPopupMenu.Separator jSeparator1;
    private javax.swing.JPopupMenu.Separator jSeparator10;
    private javax.swing.JPopupMenu.Separator jSeparator11;
    private javax.swing.JPopupMenu.Separator jSeparator12;
    private javax.swing.JPopupMenu.Separator jSeparator13;
    private javax.swing.JPopupMenu.Separator jSeparator14;
    private javax.swing.JPopupMenu.Separator jSeparator15;
    private javax.swing.JPopupMenu.Separator jSeparator16;
    private javax.swing.JPopupMenu.Separator jSeparator17;
    private javax.swing.JPopupMenu.Separator jSeparator18;
    private javax.swing.JPopupMenu.Separator jSeparator19;
    private javax.swing.JPopupMenu.Separator jSeparator2;
    private javax.swing.JPopupMenu.Separator jSeparator20;
    private javax.swing.JPopupMenu.Separator jSeparator3;
    private javax.swing.JPopupMenu.Separator jSeparator4;
    private javax.swing.JPopupMenu.Separator jSeparator5;
    private javax.swing.JPopupMenu.Separator jSeparator6;
    private javax.swing.JPopupMenu.Separator jSeparator7;
    private javax.swing.JPopupMenu.Separator jSeparator8;
    private javax.swing.JPopupMenu.Separator jSeparator9;
    private java.awt.Menu menu1;
    private java.awt.Menu menu2;
    private java.awt.MenuBar menuBar1;
    // End of variables declaration//GEN-END:variables
    
    private boolean openForm(String nama) {
        Boolean status = true;
        selectedTab=0;
        if (formView.getTabCount() >= 0) {
            for (int i = 0; i < formView.getTabCount(); i++) {
                if (formView.getTitleAt(i).equalsIgnoreCase(nama)) {
                    formView.setSelectedIndex(i);
                    status = false;
                    break;
                }
                selectedTab++;
            }
        } 
        return status;
    }
}
