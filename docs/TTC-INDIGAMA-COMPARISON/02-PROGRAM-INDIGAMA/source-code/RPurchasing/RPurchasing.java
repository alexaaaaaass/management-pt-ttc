/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package RPurchasing;

import Accounting.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogPurchaseOrder;
import DialogPublicTP.DialogReqOrder;
import DialogPublicTP.DialogSupplier;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Production.*;
import ReportAccounting.*;
import ReportProduction.*;
import Tools.ConnectDB;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JFileChooser;
import javax.swing.JOptionPane;
import javax.swing.JTable;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import org.apache.poi.hssf.usermodel.HSSFCellStyle;
import org.apache.poi.hssf.usermodel.HSSFFont;
import org.apache.poi.hssf.usermodel.HSSFSheet;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.hssf.util.HSSFColor;
import org.apache.poi.ss.usermodel.Cell;
import org.apache.poi.ss.usermodel.Row;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class RPurchasing extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    String akses="REPORT PURCHASING";
    public RPurchasing(String mode) {
        initComponents();
  
        deff();
        push_button();
    }
    
     private void push_button(){
        InputMap im = this.getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = this.getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F9, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                 System.out.println("Print "+jLabel2.getText());
            }
        });
    }

    private void deff(){
        Date1.setDate(new Date());
        Date2.setDate(new Date()); 
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }
    
    private void po_rawmaterial(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        sql = "select a.ccode,a.cnbkt,a.cnbkt_PR,a.dtran,a.cdue_date,b.cgroup,b.cproduct,b.nqty,b.nprice,b.ndisc,b.ntotal,b.nunit,b.kode_supplier,c.cname as supplier,\n" +
                "d.nqty as nqtyin,d.nunit as nunitin\n" +
                "from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
                "join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
                "left join (\n" +
                "	select a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,sum(b.nqty) as nqty,b.nunit,b.kode_supplier \n" +
                "	from TranRawMaterialIn a join TranRawMaterialInD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                "	where isnull(a.cnbkt_po,'') <> ''\n" +
                "	group by a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,b.nunit,b.kode_supplier \n" +
                ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt_po and b.cgroup=d.cgroup and b.cproduct=d.cproduct and b.kode_supplier=d.kode_supplier\n" +
                " where a.ccode='"+tcode.getText()+"' and (a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"') and a.cnbkt like '%"+tbukti_po.getText()+"%' "+
                " order by ccode,cnbkt\n" +
                "";
        System.out.println(sql);
        rfa.clear_table(MenuAdmin.keyssDef, tableBland);
        DefaultTableModel model = (DefaultTableModel) tableBland.getModel();
        int nomor=1;
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rs.getString("cnbkt_PR"),
                    rs.getString("kode_supplier"),rs.getString("supplier"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),rfa.DateString(keyssDef, rs.getDate("cdue_date"),"dd/MM/yyyy"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),rs.getDouble("ndisc"),rs.getDouble("ntotal"),rs.getString("nunit"),
                    rs.getDouble("nqtyin"),rs.getString("nunitin"),
                    rs.getDouble("nqty")-rs.getDouble("nqtyin"),
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Gudang");
            kolom.add("No Bukti");
            kolom.add("Bukti Permintaan");
            kolom.add("Kode Supplier");
            kolom.add("Nama Supplier");
            kolom.add("Tanggal");
            kolom.add("Jatuh Tempo");
            kolom.add("Group Produk");
            kolom.add("Produk/Bahan");
            kolom.add("Qty/Berat");
            kolom.add("Harga Per Qty");
            kolom.add("Diskon");
            kolom.add("Total");
            kolom.add("Satuan");
            kolom.add("Qty Masuk");
            kolom.add("Satuan Masuk");
            kolom.add("Sisa");
            con.exportExcel(tableBland,kolom,"Laporan PO RawMaterial",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableBland.getModel());
            String treport = "report/RPORaw.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void po_rawmaterialexport(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        sql = "select a.ccode,a.cnbkt,a.cnbkt_PR,a.dtran,a.cdue_date,b.cgroup,b.cproduct,dd.cnameProduct,b.nqty,b.nprice,b.ndisc,b.ntotal,b.nunit,b.kode_supplier,c.cname as supplier,\n" +
                "d.nqty as nqtyin,d.nunit as nunitin\n" +
                "from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
                "join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
                "join MProduk dd on b.cproduct=dd.cproduct\n" +
                "left join (\n" +
                "	select a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,sum(b.nqty) as nqty,b.nunit,b.kode_supplier \n" +
                "	from TranRawMaterialIn a join TranRawMaterialInD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                "	where isnull(a.cnbkt_po,'') <> ''\n" +
                "	group by a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,b.nunit,b.kode_supplier \n" +
                ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt_po and b.cgroup=d.cgroup and b.cproduct=d.cproduct and b.kode_supplier=d.kode_supplier\n" +
                "where a.ccode='"+tcode.getText()+"' and (a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"') and a.cnbkt like '%%' \n" +
                "order by ccode,cnbkt\n" +
                "";
        
        rfa.clear_table(MenuAdmin.keyssDef, tableBland);
        DefaultTableModel model = (DefaultTableModel) tableBland.getModel();
        int nomor=1;
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rs.getString("cnbkt_PR"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),rfa.DateString(keyssDef, rs.getDate("cdue_date"),"dd/MM/yyyy"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),rs.getDouble("ndisc"),rs.getDouble("ntotal"),rs.getString("nunit"),
                    rs.getString("kode_supplier"),rs.getString("supplier"),
                    rs.getDouble("nqtyin"),rs.getString("nunitin"),
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Gudang");
            kolom.add("No Bukti");
            kolom.add("PO Req");
            kolom.add("Tanggal");
            kolom.add("Jatuh Tempo ");
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Qty");
            kolom.add("Harga");
            kolom.add("Diskon");
            kolom.add("Total");
            kolom.add("Satuan");
            kolom.add("Kode Supplier");
            kolom.add("Supplier");
            kolom.add("Qty In");
            kolom.add("Satuan In");
            con.exportExcel(tableBland,kolom,"Laporan PO RawMaterial",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableBland.getModel());
            String treport = "report/RPORaw.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    
    private void payment(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tablepayment.getModel();
        rfa.clear_table(keyssDef, tablepayment);
        
        String wherePO_Kode="";
        if(!tkodeineks.getText().isEmpty()) wherePO_Kode+=" and c.kode='"+tkodeineks.getText()+"' ";
        if(!tkodeppn.getText().isEmpty()) {
            if(tkodeppn.getText().equals("1")) wherePO_Kode+=" and c.nppn > 0 ";
            else if(tkodeppn.getText().equals("2")) wherePO_Kode+=" and isnull(c.nppn,0) = 0 ";
        }

        sql = "select a.ccode,a.cnbkt,a.cnbkt_PO,c.kode_supplier,c.supplier,b.dtran,b.cdesc,b.method,b.curs,b.cbank,b.cbank_an,b.cbank_rekening,b.namnt,b.cacckas,b.caccll,c.total,c.cdesc as note\n" +
                "from TranPayment a join TranPayment_detail b on a.ccode=b.ccode and a.cnbkt=b.cnbkt  \n" +
                "left join (\n" +
                "	select a.ccode,a.cnbkt,a.kode_supplier,b.cname as supplier ,sum((a.nqty*a.nprice)-a.ndisc)+isnull(c.nppn_nilai,0) as total,c.cdesc\n" +
                "	from TranPurchaseOrder_detail a join MSupplier b on a.kode_supplier=b.kode_supplier\n" +
                "	join TranPurchaseOrder c on a.ccode=c.ccode and a.cnbkt=c.cnbkt\n" +
                "	where 1=1 "+wherePO_Kode+" \n" +
                "	group by a.ccode,a.cnbkt,a.kode_supplier,b.cname,c.cdesc,isnull(c.nppn_nilai,0)\n" +
                ") c on a.cnbkt_PO=c.cnbkt and a.ccode=c.ccode"
                + " where a.ccode='"+tcode.getText()+"' and (b.dtran>='"+dtran1+"' and b.dtran<='"+dtran2+"') ";
        
        if(!tsupplier.getText().isEmpty()) sql+="and c.kode_supplier = '"+tsupplier.getText()+"'  ";
        
        sql+=  "order by c.kode_supplier,b.dtran";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),rs.getString("cnbkt_PO")+" - "+rs.getString("supplier"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cdesc"),rs.getString("method"),
                    rs.getString("curs"),
                    rs.getString("cbank"),rs.getString("cbank_an"),rs.getString("cbank_rekening"),
                    rs.getDouble("namnt"),
                    rs.getString("cacckas"),rs.getString("caccll"),
                    rs.getDouble("total"),
                    rs.getString("note"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPurchasing.class.getName()).log(Level.SEVERE, null, ex);
        }

        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No Bukti");
            kolom.add("Bukti PO");
            kolom.add("Tanggal");
            kolom.add("Desc");
            kolom.add("Method");
            kolom.add("Curs");
            kolom.add("Bank");
            kolom.add("Bank AM");
            kolom.add("Bank Rekening");
            kolom.add("Nilai");
            kolom.add("Akun Debet");
            kolom.add("Akun Kredit");
            con.exportExcel(tablepayment,kolom,"Laporan Payment",dtran1,dtran2);
            return;
        }
        
        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tablepayment.getModel());
            String treport = "report/RPayment.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void payment_tagihan(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tablepayment.getModel();
        rfa.clear_table(keyssDef, tablepayment);

        String wherePO_Kode="";
        if(!tkodeineks.getText().isEmpty()) wherePO_Kode+=" and c.kode='"+tkodeineks.getText()+"' ";
        if(!tkodeppn.getText().isEmpty()) {
            if(tkodeppn.getText().equals("1")) wherePO_Kode+=" and c.nppn > 0 ";
            else if(tkodeppn.getText().equals("2")) wherePO_Kode+=" and isnull(c.nppn,0) = 0 ";
        }
        
        sql = "select a.ccode,a.cnbkt,a.cnbkt_PO,a.cnbkt_bill,c.kode_supplier,c.supplier,b.dtran,b.cdesc,b.method,b.curs,b.cbank,b.cbank_an,b.cbank_rekening,b.namnt,b.cacckas,b.caccll,c.total,c.cdesc as note\n" +
                "from TranPayment a join TranPayment_detail b on a.ccode=b.ccode and a.cnbkt=b.cnbkt  \n" +
                "left join (\n" +
                "	select a.ccode,a.cnbkt,a.kode_supplier,b.cname as supplier ,sum((a.nqty*a.nprice)-a.ndisc)+isnull(c.nppn_nilai,0) as total,c.cdesc\n" +
                "	from TranPurchaseOrder_detail a join MSupplier b on a.kode_supplier=b.kode_supplier\n" +
                "	join TranPurchaseOrder c on a.ccode=c.ccode and a.cnbkt=c.cnbkt\n" +
                "	where 1=1 "+wherePO_Kode+" \n" +
                "	group by a.ccode,a.cnbkt,a.kode_supplier,b.cname,c.cdesc,isnull(c.nppn_nilai,0)\n" +
                ") c on a.cnbkt_PO=c.cnbkt and a.ccode=c.ccode"
                + " where a.ccode='"+tcode.getText()+"' and (b.dtran>='"+dtran1+"' and b.dtran<='"+dtran2+"') ";
        
        if(!tsupplier.getText().isEmpty()) sql+="and c.kode_supplier = '"+tsupplier.getText()+"'  ";
        
        sql+=  "order by c.kode_supplier,b.dtran";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),rs.getString("cnbkt_PO")+" - Bill No "+rs.getString("cnbkt_bill")+" - "+rs.getString("supplier"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cdesc"),rs.getString("method"),
                    rs.getString("curs"),
                    rs.getString("cbank"),rs.getString("cbank_an"),rs.getString("cbank_rekening"),
                    rs.getDouble("namnt"),
                    rs.getString("cacckas"),rs.getString("caccll"),
                    rs.getDouble("total"),
                    rs.getString("note"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPurchasing.class.getName()).log(Level.SEVERE, null, ex);
        }

        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No Bukti");
            kolom.add("Bukti PO");
            kolom.add("Tanggal");
            kolom.add("Desc");
            kolom.add("Method");
            kolom.add("Curs");
            kolom.add("Bank");
            kolom.add("Bank AM");
            kolom.add("Bank Rekening");
            kolom.add("Nilai");
            kolom.add("Akun Debet");
            kolom.add("Akun Kredit");
            con.exportExcel(tablepayment,kolom,"Laporan Payment",dtran1,dtran2);
            return;
        }
        
        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tablepayment.getModel());
            String treport = "report/RPaymentTagihan.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", deffProgram.get("pt"));
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void reqpo(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tablereqpo.getModel();
        rfa.clear_table(keyssDef, tablereqpo);
            
        sql="select a.ccode,a.cnbkt,a.dtran,a.cdesc,a.nik,a.nik_cname,b.cgroup,b.cproduct,b.kode_supplier,d.cname as supplier,b.nqty,b.nprice,b.ndisc,b.ntotal,b.nunit,\n" +
            "c.dtran as dtranpo,c.nqty as nqtypo,c.nprice as npricepo,c.ndisc as ndiscpo,c.ntotal as ntotalpo\n" +
            "from TranPurchaseReq a join TranPurchaseReq_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
            "left join \n" +
            "(\n" +
            "	select a.ccode,a.cnbkt_PR,a.dtran,b.cgroup,b.cproduct,b.nqty,b.nprice,b.ndisc,b.ntotal,b.nunit,b.kode_supplier \n" +
            "	from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
            ") c on a.cnbkt=c.cnbkt_PR and a.ccode=c.ccode and b.cgroup=c.cgroup and b.cproduct=c.cproduct and b.kode_supplier=c.kode_supplier\n" +
            "join MSupplier d on b.kode_supplier=d.kode_supplier "+
            " where (a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"') and a.cnbkt like '%"+treq.getText()+"%' ";
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                    rs.getString("cdesc"),
                    rs.getString("nik"),rs.getString("nik_cname"),
                    rs.getString("supplier"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),rs.getDouble("ndisc"),rs.getDouble("ntotal"),
                    rs.getString("nunit"),
                    rfa.DateString(keyssDef, rs.getDate("dtranpo"),"dd/MM/yyyy"),
                    rs.getDouble("nqtypo"),rs.getDouble("npricepo"),rs.getDouble("ndiscpo"),rs.getDouble("ntotalpo"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPurchasing.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No Bukti");
            kolom.add("Tanggal");
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Desc");
            kolom.add("NIK");
            kolom.add("Karyawan");
            kolom.add("Supplier");
            kolom.add("Qty");
            kolom.add("Harga");
            kolom.add("Diskon");
            kolom.add("Total");
            kolom.add("Satuan");
            kolom.add("Tgl PO");
            kolom.add("Qty PO");
            kolom.add("Harga PO");
            kolom.add("Diskon PO");
            kolom.add("Total PO");
            con.exportExcel(tablereqpo,kolom,"Laporan Request PO",dtran1,dtran2);
            return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tablereqpo.getModel());
            String treport = "report/RReqPO.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void hutangbiaya(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm.getModel();
        rfa.clear_table(keyssDef, tableskm);
        
        String wherePO_Kode="";
        if(!tkodeineks.getText().isEmpty()) wherePO_Kode+=" and a.kode='"+tkodeineks.getText()+"' ";
        if(!tkodeppn.getText().isEmpty()) {
            if(tkodeppn.getText().equals("1")) wherePO_Kode+=" and a.nppn > 0 ";
            else if(tkodeppn.getText().equals("2")) wherePO_Kode+=" and isnull(a.nppn,0) = 0 ";
        }
        
        sql="select * from (\n" +
            "	select a.id,a.cnbkt,a.dtran,a.cdesc,sum((b.nqty*b.nprice)-b.ndisc)+isnull(a.nppn_nilai,0) as total,b.kode_supplier,c.cname as supplier,\n" +
            "	isnull(d.nominalpay,0) as nominalpay,a.cdue_date,\n" +
            "	case when sum((b.nqty*b.nprice)-b.ndisc)<=isnull(d.nominalpay,0) then 'LUNAS' else 'BELUM LUNAS' end as status,\n" +
            "	case when sum((b.nqty*b.nprice)-b.ndisc)<=isnull(d.nominalpay,0) then '1' else '0' end as statusurut,\n" +
            "	case when a.cdue_date<getdate() then 'MELEWATI JATUH TEMPO' else '' end as statusduedate\n" +
            "	from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt\n" +
            "	join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
            "	left join (\n" +
            "		select a.cnbkt_PO,sum(b.namnt) as nominalpay from TranPayment a join TranPayment_detail b on a.cnbkt=b.cnbkt and a.id=b.id_cnbkt\n" +
            "		group by  a.cnbkt_PO\n" +
            "	) d on a.cnbkt=d.cnbkt_PO\n" +    
            "	where 1=1 "+wherePO_Kode+"\n" +    
            "	group by a.id,a.cnbkt,a.dtran,a.cdesc,b.kode_supplier,c.cname,d.nominalpay,a.cdue_date,isnull(a.nppn_nilai,0)\n" +
            ") final\n" +
            " where dtran >= '"+dtran1+"' and dtran<='"+dtran2+"' ";
        
        if(!tsupplier.getText().isEmpty()) sql+="and kode_supplier = '"+tsupplier.getText()+"'  ";
        
        sql+=  "order by statusurut,dtran";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cdesc").replaceAll("\n", " "),
                    rs.getDouble("total"),
                    rs.getString("kode_supplier"),
                    rs.getString("supplier"),rs.getDouble("nominalpay"),
                    rfa.DateString(keyssDef, rs.getDate("cdue_date"),"dd/MM/yyyy"),
                    rs.getString("status"),rs.getString("statusduedate"),
                    rs.getDouble("total")-rs.getDouble("nominalpay"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPurchasing.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No Bukti");
            kolom.add("Tanggal");
            kolom.add("Desc");
            kolom.add("Total");
            kolom.add("Kode Suppplier");
            kolom.add("Supplier");
            kolom.add("Nominal Terbayar");
            kolom.add("Jatuh Tempo");
            kolom.add("Status");
            kolom.add("Status Jatuh Tempo");
            kolom.add("Sisa Tagihan");
            con.exportExcel(tableskm,kolom,"Laporan Hutang Biaya",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableskm.getModel());
            String treport = "report/RHutangBiaya.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
        
    }
    private void hutangbiaya_bill(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm.getModel();
        rfa.clear_table(keyssDef, tableskm);
        String wherePO_Kode="";
        if(!tkodeineks.getText().isEmpty()) wherePO_Kode+=" and ccc.kode='"+tkodeineks.getText()+"' ";
        if(!tkodeppn.getText().isEmpty()) {
            if(tkodeppn.getText().equals("1")) wherePO_Kode+=" and ccc.nppn > 0 ";
            else if(tkodeppn.getText().equals("2")) wherePO_Kode+=" and isnull(ccc.nppn,0) = 0 ";
        }
        
        sql="select * from (\n" +
            "	select a.id,a.cnbkt,a.cnbkt_po,a.dtran,a.cdesc,sum(b.ntotalall) as total,ccc.kode_supplier,ccc.supplier,\n" +
            "	isnull(d.nominalpay,0) as nominalpay,a.cdue_date,\n" +
            "	case when sum(b.ntotalall)<=isnull(d.nominalpay,0) then 'LUNAS' else 'BELUM LUNAS' end as status,\n" +
            "	case when sum(b.ntotalall)<=isnull(d.nominalpay,0) then '1' else '0' end as statusurut,\n" +
            "	case when a.cdue_date<getdate() then 'MELEWATI JATUH TEMPO' else '' end as statusduedate\n" +
            "	from TranPurchaseOrderBilling a join TranPurchaseOrderBillingD b on a.cnbkt=b.cnbkt\n" +
            "	join (\n" +
            "		select a.kode,a.ccode,a.cnbkt,a.dtran,b.kode_supplier,c.cname as supplier,\n" +
            "		sum(b.ntotal) as total,isnull(a.nppn,0) as nppn, sum(b.ntotal)+isnull(a.nppn_nilai,0) as ntotalall\n" +
            "		from TranPurchaseOrder a \n" +
            "		join TranPurchaseOrder_detail b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "		join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
            "		group by a.kode,a.ccode,a.cnbkt,a.dtran,b.kode_supplier,c.cname,a.nppn,a.nppn_nilai \n" +
            "	) ccc on a.ccode=ccc.ccode and a.cnbkt_po=ccc.cnbkt\n" +
            "	left join (\n" +
            "		select a.ccode,a.cnbkt,a.cnbkt_PO,a.cnbkt_bill,sum(b.namnt) as nominalpay from TranPayment a join TranPayment_detail b on a.cnbkt=b.cnbkt and a.id=b.id_cnbkt\n" +
            "		group by a.ccode,a.cnbkt,a.cnbkt_PO,a.cnbkt_bill\n" +
            "	) d on a.cnbkt=d.cnbkt_bill and a.cnbkt_po=d.cnbkt_po and a.ccode=d.ccode\n" +
            "	where 1=1  "+wherePO_Kode+"  \n" +
            "	group by a.id,a.cnbkt,a.cnbkt_po,a.dtran,a.cdesc,ccc.kode_supplier,ccc.supplier,d.nominalpay,a.cdue_date\n" +
            ") final\n" +
            " where dtran >= '"+dtran1+"' and dtran<='"+dtran2+"' ";
        
        if(!tsupplier.getText().isEmpty()) sql+=" and kode_supplier = '"+tsupplier.getText()+"'  ";
        
        sql+=  "order by statusurut,dtran";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cdesc").replaceAll("\n", " "),
                    rs.getDouble("total"),
                    rs.getString("kode_supplier"),
                    rs.getString("supplier"),rs.getDouble("nominalpay"),
                    rfa.DateString(keyssDef, rs.getDate("cdue_date"),"dd/MM/yyyy"),
                    rs.getString("status"),rs.getString("statusduedate"),
                    rs.getDouble("total")-rs.getDouble("nominalpay"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPurchasing.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No Bukti");
            kolom.add("Tanggal");
            kolom.add("Desc");
            kolom.add("Total");
            kolom.add("Kode Suppplier");
            kolom.add("Supplier");
            kolom.add("Nominal Terbayar");
            kolom.add("Jatuh Tempo");
            kolom.add("Status");
            kolom.add("Status Jatuh Tempo");
            kolom.add("Sisa Tagihan");
            con.exportExcel(tableskm,kolom,"Laporan Hutang Biaya",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableskm.getModel());
            String treport = "report/RHutangBiaya.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
        
    }
    
    private void hutangbiaya_jangkawaktu(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm1.getModel();
        rfa.clear_table(keyssDef, tableskm1);
        
        sql="select * from (\n" +
"	select a.id,a.cnbkt,a.dtran,a.cdesc,sum((b.nqty*b.nprice)-b.ndisc)+isnull(a.nppn_nilai,0) as total,b.kode_supplier,c.cname as supplier,\n" +
"	isnull(d.nominalpay,0) as nominalpay,a.cdue_date,\n" +
"	case when sum((b.nqty*b.nprice)-b.ndisc)<=isnull(d.nominalpay,0) then 'LUNAS' else 'BELUM LUNAS' end as status,\n" +
"	case when sum((b.nqty*b.nprice)-b.ndisc)<=isnull(d.nominalpay,0) then '1' else '0' end as statusurut,\n" +
"	case when a.cdue_date<getdate() then 'MELEWATI JATUH TEMPO' else '' end as statusduedate\n" +
"	from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt\n" +
"	join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
"	left join (\n" +
"		select  a.cnbkt_PO,sum(b.namnt) as nominalpay from TranPayment a join TranPayment_detail b on a.cnbkt=b.cnbkt and a.id=b.id_cnbkt\n" +
"		group by  a.cnbkt_PO\n" +
"	) d on a.cnbkt=d.cnbkt_PO\n" +
"	group by a.id,a.cnbkt,a.dtran,a.cdesc,b.kode_supplier,c.cname,d.nominalpay,a.cdue_date,isnull(a.nppn_nilai,0)\n" +
") final\n" +
"where statusurut = 0 and cdue_date is not null and (dtran>='"+dtran1+"' and dtran<='"+dtran2+"')\n" +
"order by statusurut,dtran";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"yyyy-MM-dd"),
                    rs.getString("cdesc"),
                    rs.getDouble("total"),
                    rs.getString("kode_supplier"),
                    rs.getString("supplier"),rs.getDouble("nominalpay"),
                    rfa.DateString(keyssDef, rs.getDate("cdue_date"),"yyyy-MM-dd"),
                    rs.getString("status"),rs.getString("statusduedate"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPurchasing.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No Bukti");
            kolom.add("Tanggal");
            kolom.add("Desc");
            kolom.add("Total");
            kolom.add("Kode Suppplier");
            kolom.add("Supplier");
            kolom.add("Nominal Terbayar");
            kolom.add("Jatuh Tempo");
            kolom.add("Status");
            kolom.add("Status Jatuh Tempo");
            con.exportExcel(tableskm1,kolom,"Laporan Hutang Biaya Jangka Waktu",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableskm1.getModel());
            String treport = "report/RHutangBiaya_JangkaWaktu.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
        
    }
    private void rekap_hutang(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm2.getModel();
        rfa.clear_table(keyssDef, tableskm2);
        
        sql="select kode_supplier,supplier,sum(total) as total,sum(nominalpay) as nominalpay,case when sum(total)-sum(nominalpay)=0 then 'Lunas' else 'Belum Lunas' end as status  from (\n" +
            "	select a.id,a.cnbkt,a.dtran,a.cdesc,sum((b.nqty*b.nprice)-b.ndisc)+isnull(a.nppn_nilai,0) as total,b.kode_supplier,c.cname as supplier,\n" +
            "	isnull(d.nominalpay,0) as nominalpay,a.cdue_date,\n" +
            "	case when sum((b.nqty*b.nprice)-b.ndisc)+isnull(a.nppn_nilai,0)<=isnull(d.nominalpay,0) then 'LUNAS' else 'BELUM LUNAS' end as status,\n" +
            "	case when sum((b.nqty*b.nprice)-b.ndisc)+isnull(a.nppn_nilai,0)<=isnull(d.nominalpay,0) then '1' else '0' end as statusurut,\n" +
            "	case when a.cdue_date<getdate() then 'MELEWATI JATUH TEMPO' else '' end as statusduedate\n" +
            "	from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt\n" +
            "	join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
            "	left join (\n" +
            "		select a.cnbkt,a.cnbkt_PO,sum(b.namnt) as nominalpay from TranPayment a join TranPayment_detail b on a.cnbkt=b.cnbkt and a.id=b.id_cnbkt\n" +
            "		group by a.cnbkt,a.cnbkt_PO\n" +
            "	) d on a.cnbkt=d.cnbkt_PO\n" +
            "	group by a.id,a.cnbkt,a.dtran,a.cdesc,b.kode_supplier,c.cname,d.nominalpay,a.cdue_date,isnull(a.nppn_nilai,0)\n" +
            ") final\n" +
            "where 1=1 \n";
        if(!tsupplier.getText().isEmpty()){
            sql+=" and kode_supplier= '"+tsupplier.getText()+"' ";
        }
        sql+="group by kode_supplier,supplier\n" +
            "order by supplier";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("kode_supplier"),
                    rs.getString("supplier"),
                    rs.getDouble("total"),rs.getDouble("nominalpay"),
                    rs.getString("status"),
                    rs.getDouble("total")-rs.getDouble("nominalpay"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPurchasing.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Kode Suppplier");
            kolom.add("Supplier");
            kolom.add("Total Hutang");
            kolom.add("Nominal Terbayar");
            kolom.add("Status");
            kolom.add("Sisa Tagihan");
            con.exportExcel(tableskm2,kolom,"Laporan Rekap Hutang",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableskm2.getModel());
            String treport = "report/RRekapHutang.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
        
    }
    
    private void po_penerimaan(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        sql="select a.cnbkt,a.kode,a.ctype,a.dtran,c.cname as supplier,a.cdue_date,b.cproduct,f.cnameProduct,b.nqty,b.nunit,\n" +
            "b.nprice,b.ntotal,b.ntotal*(isnull(a.nppn,0)/100) as ppn,b.ntotal+(b.ntotal*(isnull(a.nppn,0)/100)) as totalnilai,isnull(g.tpay,0) as tpay,\n" +
            "case when b.nunit <> d.nunit and isnull(d.nunit,'') <> '' then d.nqty*isnull(e.small_value,1)\n" +
            "	 else d.nqty end as nqtyin \n" +
            "from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
            "join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
            "left join (\n" +
            "	select ccode,cnbkt_po,cgroup,cproduct,sum(nqty) as nqty,nunit from (	\n" +
            "		select a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,sum(b.nqty) as nqty,b.nunit \n" +
            "		from TranRawMaterialIn a join TranRawMaterialInD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "		where isnull(a.cnbkt_po,'') <> ''\n" +
            "		group by a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,b.nunit\n" +
            "		union all\n" +
            "		select a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,a.cgood,a.cgood_unit from PProdSteps a join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "		where isnull(a.cnbkt_po,'') <> ''\n" +
            "	) a\n" +
            "	group by ccode,cnbkt_po,cgroup,cproduct,nunit\n" +
            ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt_po and b.cgroup=d.cgroup and b.cproduct=d.cproduct \n" +
            "left join (\n" +
            "	select a.cname as unit1,b.cname as unit2,a.small_value from MSatuan a join MSatuan b on a.id_turunan=b.id\n" +
            ") e on d.nunit=e.unit1 and b.nunit=e.unit2\n" +
            "join MProduk f on b.cgroup=f.cgroup and b.cproduct=f.cproduct\n" +
            "left join (\n" +
            "	select a.ccode,a.cnbkt_PO,sum(b.namnt) as tpay From TranPayment a join TranPayment_detail b on a.ccode=b.ccode\n" +
            "	group by a.ccode,a.cnbkt_PO\n" +
            ") g on a.ccode=g.ccode and a.cnbkt=g.cnbkt_PO\n" +
            "where a.ccode='"+tcode.getText()+"' and (a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"') \n";
        
        if(!tbukti_po.getText().isEmpty()) sql+=" and a.cnbkt= like '%"+tbukti_po.getText()+"%' ";
        if(!tkodeineks.getText().isEmpty()) sql+=" and a.kode= = '"+tkodeineks.getText()+"' ";
        if(!tkodeppn.getText().isEmpty()) {
            if(tkodeppn.getText().equals("1")) sql+=" and isnull(a.nppn,0) > 0 ";
            else if(tkodeppn.getText().equals("2")) sql+=" and isnull(a.nppn,0) = 0 "; 
        }
        
        sql+="\n order by a.dtran,a.cnbkt,b.id";
        
        System.out.println(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTablepo_penerimaan);
        DefaultTableModel model = (DefaultTableModel) jTablepo_penerimaan.getModel();
        int nomor=1;
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                String status="Belum Diterima";
                if(rs.getDouble("nqtyin")>0) status="Parsial";
                if(rs.getDouble("nqtyin")>=rs.getDouble("nqty")) status="Done";
                model.addRow(new Object[]{ 
                    rs.getString("cnbkt"),
                    rs.getString("kode"),
                    rs.getString("ctype"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"), 
                    rs.getString("supplier"), 
                    rs.getString("cproduct"),
                    rs.getString("cnameproduct"),
                    rs.getDouble("nqty"),
                    rs.getString("nunit"),
                    rs.getDouble("nprice"),
                    rs.getDouble("ntotal"),
                    rs.getDouble("ppn"),
                    rs.getDouble("totalnilai"),
                    rs.getDouble("tpay"),
                    rs.getDouble("nqtyin"), 
                    status
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTablepo_penerimaan.getModel());
            String treport = "report/RPOPenerimaan.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void exportExcel(JTable table,ArrayList kolom,String namafile,String dawal,String dakhir){
        final JFileChooser chooser = new JFileChooser();
        chooser.setDialogTitle("Specify a Folder to Save");
        chooser.setFileSelectionMode(JFileChooser.DIRECTORIES_ONLY);
        chooser.setCurrentDirectory(new File("C:\\Users\\Administrator\\Documents"));
        
        HSSFWorkbook workbook = new HSSFWorkbook();
        HSSFSheet sheet = workbook.createSheet("Sheet 1");

        HSSFFont font = workbook.createFont();
        HSSFCellStyle styleHeader = workbook.createCellStyle();
        font.setBoldweight(HSSFFont.BOLDWEIGHT_BOLD);
        styleHeader.setAlignment(styleHeader.ALIGN_CENTER);            
        styleHeader.setFillForegroundColor(HSSFColor.LIME.index);
        styleHeader.setFillPattern(HSSFCellStyle.SOLID_FOREGROUND);
        styleHeader.setBorderBottom(HSSFCellStyle.BORDER_THIN);
        styleHeader.setBorderTop(HSSFCellStyle.BORDER_THIN);
        styleHeader.setBorderLeft(HSSFCellStyle.BORDER_THIN);
        styleHeader.setBorderRight(HSSFCellStyle.BORDER_THIN);
        styleHeader.setFont(font);

        HSSFCellStyle styleHeader2 = workbook.createCellStyle();
        styleHeader2.setAlignment(styleHeader2.ALIGN_LEFT);
        styleHeader2.setFont(font);

        HSSFCellStyle styleBorder = workbook.createCellStyle();
        styleBorder.setBorderBottom(HSSFCellStyle.BORDER_THIN);
        styleBorder.setBorderTop(HSSFCellStyle.BORDER_THIN);
        styleBorder.setBorderLeft(HSSFCellStyle.BORDER_THIN);
        styleBorder.setBorderRight(HSSFCellStyle.BORDER_THIN);
        
        int retrival = chooser.showSaveDialog(null);
        if (retrival == JFileChooser.APPROVE_OPTION) {
            try {
                Row row = sheet.createRow(0);
                Cell cell = row.createCell(0); cell.setCellValue((String)namafile); cell.setCellStyle(styleHeader2);
                row = sheet.createRow(1);
                cell = row.createCell(0); cell.setCellValue((String)"Periode "+dawal+" s/d "+dakhir); cell.setCellStyle(styleHeader2);

                row = sheet.createRow(3);
                for(int i=0; i<kolom.size();i++){
                    cell = row.createCell(i); cell.setCellValue((String)kolom.get(i)); cell.setCellStyle(styleHeader);
                }

                for (int i = 0; i < table.getRowCount(); i++) {
                    row = sheet.createRow(i+4);
                    for (int j = 0; j < table.getColumnCount(); j++) {
                        cell = row.createCell(j);
                        if(table.getValueAt(i, j) instanceof String)
                            cell.setCellValue((String)table.getValueAt(i, j));
                        else if(table.getValueAt(i, j) instanceof Integer)
                            cell.setCellValue((Integer)table.getValueAt(i, j));
                        else if(table.getValueAt(i, j) instanceof Double)
                            cell.setCellValue((Double)table.getValueAt(i, j));
                        cell.setCellStyle(styleBorder);
                    }
                }

                
            
                String f = chooser.getSelectedFile() + "\\Export "+namafile+" "+dawal+" sd "+dakhir+".xls";
                FileOutputStream excel= new FileOutputStream(new File(f));
                workbook.write(excel);
                excel.close();
                JOptionPane.showMessageDialog(this, "File Excel Disimpan Di "+f, "File Created", JOptionPane.INFORMATION_MESSAGE);
            } catch (FileNotFoundException ex) {
                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
            } catch (IOException ex) {
                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
            }
                
         
        }
    }
    
    /**
     * This method is called from within the constructor to initialize the form.
     * WARNING: Do NOT modify this code. The content of this method is always
     * regenerated by the Form Editor.
     */
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        GroupBland = new javax.swing.ButtonGroup();
        GroupSKT = new javax.swing.ButtonGroup();
        GroupSKM = new javax.swing.ButtonGroup();
        jScrollPane8 = new javax.swing.JScrollPane();
        tableskm1 = new javax.swing.JTable();
        GroupMaterialProduksi = new javax.swing.ButtonGroup();
        jScrollPane1 = new javax.swing.JScrollPane();
        tableBland = new javax.swing.JTable();
        jScrollPane2payment = new javax.swing.JScrollPane();
        tablepayment = new javax.swing.JTable();
        jScrollPane7 = new javax.swing.JScrollPane();
        tableskm = new javax.swing.JTable();
        jScrollPane4 = new javax.swing.JScrollPane();
        tableSKTBorongan = new javax.swing.JTable();
        jScrollPane5 = new javax.swing.JScrollPane();
        tableSKTList = new javax.swing.JTable();
        jScrollPane6 = new javax.swing.JScrollPane();
        tablereqpo = new javax.swing.JTable();
        jScrollPane9 = new javax.swing.JScrollPane();
        tableskm2 = new javax.swing.JTable();
        jButton8 = new javax.swing.JButton();
        jScrollPanePOPenerimaan = new javax.swing.JScrollPane();
        jTablepo_penerimaan = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jButton7 = new javax.swing.JButton();
        jButton2 = new javax.swing.JButton();
        jPanel1 = new javax.swing.JPanel();
        jButton10 = new javax.swing.JButton();
        jButton11 = new javax.swing.JButton();
        jButton22 = new javax.swing.JButton();
        jPanel5 = new javax.swing.JPanel();
        jButton5 = new javax.swing.JButton();
        jButton16 = new javax.swing.JButton();
        jButton19 = new javax.swing.JButton();
        jButton20 = new javax.swing.JButton();
        jButton23 = new javax.swing.JButton();
        jPanel6 = new javax.swing.JPanel();
        jButton6 = new javax.swing.JButton();
        jButton18 = new javax.swing.JButton();
        jPanel8 = new javax.swing.JPanel();
        jLabel10 = new javax.swing.JLabel();
        tblandkaryawan = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tblandkaryawan1 = new javax.swing.JTextField();
        jButton9 = new javax.swing.JButton();
        jButton24 = new javax.swing.JButton();
        jPanel9 = new javax.swing.JPanel();
        jLabel7 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton21 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        Date1 = new com.toedter.calendar.JDateChooser();
        jLabel6 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        Date2 = new com.toedter.calendar.JDateChooser();
        jLabel17 = new javax.swing.JLabel();
        tsupplier = new javax.swing.JTextField();
        jButton17 = new javax.swing.JButton();
        tsupplier2 = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        treq = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        jLabel11 = new javax.swing.JLabel();
        jLabel12 = new javax.swing.JLabel();
        tkodeppn = new javax.swing.JTextField();
        tkodeineks = new javax.swing.JTextField();
        jLabel8 = new javax.swing.JLabel();
        tbukti_po = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();

        tableskm1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "cdesc", "total", "kode_supplier", "supplier", "nominalpay", "cdue_date", "status", "status_duedate"
            }
        ));
        jScrollPane8.setViewportView(tableskm1);
        if (tableskm1.getColumnModel().getColumnCount() > 0) {
            tableskm1.getColumnModel().getColumn(0).setHeaderValue("cnbkt");
            tableskm1.getColumnModel().getColumn(1).setHeaderValue("dtran");
            tableskm1.getColumnModel().getColumn(2).setHeaderValue("cdesc");
            tableskm1.getColumnModel().getColumn(3).setHeaderValue("total");
            tableskm1.getColumnModel().getColumn(9).setHeaderValue("status_duedate");
        }

        tableBland.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "cnbkt", "cnbkt_pr", "dtran", "cdue_date", "cgroup", "cproduct", "nqty", "nprice", "ndisc", "ntotal", "nunit", "kode_supplier", "supplier", "nqtyin", "nunitin", "Title 17", "Title 18", "Title 19", "Title 20", "Title 21", "Title 22", "Title 23", "Title 24", "Title 25", "Title 26", "Title 27", "Title 28", "Title 29"
            }
        ));
        jScrollPane1.setViewportView(tableBland);

        tablepayment.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "cnbkt_po", "dtran", "cdesc", "method", "curs", "cbank", "cbank_an", "cbank_rekening", "namnt", "cacckas", "caccll", "total", "note"
            }
        ));
        jScrollPane2payment.setViewportView(tablepayment);

        tableskm.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "cdesc", "total", "kode_supplier", "supplier", "nominalpay", "cdue_date", "status", "status_duedate", "Title 11", "Title 12", "Title 13", "Title 14", "Title 15"
            }
        ));
        jScrollPane7.setViewportView(tableskm);

        tableSKTBorongan.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cgroup", "cproduct", "satuan", "pack", "pack_value", "borongan"
            }
        ));
        jScrollPane4.setViewportView(tableSKTBorongan);

        tableSKTList.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cgroup", "cproduct", "satuan", "pack", "pack_value", "borongan", "operator"
            }
        ));
        jScrollPane5.setViewportView(tableSKTList);
        if (tableSKTList.getColumnModel().getColumnCount() > 0) {
            tableSKTList.getColumnModel().getColumn(3).setHeaderValue("satuan");
            tableSKTList.getColumnModel().getColumn(4).setHeaderValue("pack");
            tableSKTList.getColumnModel().getColumn(5).setHeaderValue("pack_value");
            tableSKTList.getColumnModel().getColumn(6).setHeaderValue("borongan");
            tableSKTList.getColumnModel().getColumn(7).setHeaderValue("operator");
        }

        tablereqpo.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "cgroup", "cproduct", "cdesc", "coperator", "operator", "supplier", "nqty", "nprice", "ndisc", "ntotal", "nunit", "dtranpo", "nqtypo", "npricepo", "ndiscpo", "ntotalpo"
            }
        ));
        jScrollPane6.setViewportView(tablereqpo);

        tableskm2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "kode_supplier", "supplier", "total", "nominalpay", "status", "Title 6", "Title 7", "Title 8"
            }
        ));
        jScrollPane9.setViewportView(tableskm2);

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton8.setText("View Rekap");
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });

        jTablepo_penerimaan.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "kode", "ctype", "dtran", "supplier", "cproduct", "cnameproduct", "nqty", "nunit", "nprice", "ntotal", "ppn", "totalnilai", "tpay", "nqtyin", "status"
            }
        ));
        jScrollPanePOPenerimaan.setViewportView(jTablepo_penerimaan);

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jPanel3.setBackground(new java.awt.Color(153, 204, 255));

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 500, Short.MAX_VALUE)
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 0, Short.MAX_VALUE)
        );

        jPanel2.add(jPanel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 40, 500, 5));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Purchasing Report");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton7.setText("View Request PO");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 190, -1));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton2.setText("Export");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 190, -1));

        jTabbedPane1.addTab("Request PO", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton10.setText("View Payment PO");
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 190, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton11.setText("Export");
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 190, -1));

        jButton22.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton22.setText("Payment PO (Tagihan)");
        jButton22.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton22ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton22, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 20, 190, -1));

        jTabbedPane1.addTab("Payment", jPanel1);

        jPanel5.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton5.setText("View Hutang PO Supplier");
        jButton5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton5ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 240, -1));

        jButton16.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton16.setText("Export");
        jButton16.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton16ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 240, -1));

        jButton19.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton19.setText("View Rekap Hutang");
        jButton19.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton19ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton19, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 20, 230, -1));

        jButton20.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton20.setText("Export");
        jButton20.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton20ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton20, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 50, 230, -1));

        jButton23.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton23.setText("View Hutang PO Supplier (Bill)");
        jButton23.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton23ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton23, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 240, -1));

        jTabbedPane1.addTab("Hutang Biaya", jPanel5);

        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton6.setText("View Jatuh Tempo");
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 220, -1));

        jButton18.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton18.setText("Export");
        jButton18.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton18ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 220, -1));

        jTabbedPane1.addTab("Jangka Waktu Hutang", jPanel6);

        jPanel8.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Operator");
        jPanel8.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 20, 100, 20));

        tblandkaryawan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tblandkaryawanFocusLost(evt);
            }
        });
        tblandkaryawan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tblandkaryawanKeyPressed(evt);
            }
        });
        jPanel8.add(tblandkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 20, 90, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 20, 30, -1));

        tblandkaryawan1.setEditable(false);
        tblandkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        tblandkaryawan1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tblandkaryawan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 20, 200, -1));

        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton9.setText("Export");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 110, 210, 30));

        jButton24.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton24.setText("Status Penerimaan PO");
        jButton24.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton24ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton24, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 70, 210, 30));

        jTabbedPane1.addTab("Purchase Order", jPanel8);

        jPanel9.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel9.setToolTipText("Filter");
        jPanel9.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Kode Gudang");
        jPanel9.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 107, 20));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel9.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 30, 90, -1));

        jButton21.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton21.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton21ActionPerformed(evt);
            }
        });
        jPanel9.add(jButton21, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel9.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 30, 210, -1));

        Date1.setDateFormatString("dd/MM/yyyy");
        Date1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                Date1PropertyChange(evt);
            }
        });
        Date1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                Date1KeyPressed(evt);
            }
        });
        jPanel9.add(Date1, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 60, 120, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel6.setText("PPN/Non PPN");
        jPanel9.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 60, 120, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("s/d");
        jPanel9.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 60, 20, 20));

        Date2.setDateFormatString("dd/MM/yyyy");
        Date2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                Date2PropertyChange(evt);
            }
        });
        Date2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                Date2KeyPressed(evt);
            }
        });
        jPanel9.add(Date2, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 60, 120, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Supplier");
        jPanel9.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 90, 100, 20));

        tsupplier.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tsupplierFocusLost(evt);
            }
        });
        tsupplier.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tsupplierKeyPressed(evt);
            }
        });
        jPanel9.add(tsupplier, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 90, 90, -1));

        jButton17.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton17.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton17ActionPerformed(evt);
            }
        });
        jPanel9.add(jButton17, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 90, 30, -1));

        tsupplier2.setEditable(false);
        tsupplier2.setBackground(new java.awt.Color(153, 153, 255));
        tsupplier2.setForeground(new java.awt.Color(255, 255, 255));
        jPanel9.add(tsupplier2, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 90, 210, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("No Req PO");
        jPanel9.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 120, 120, 20));
        jPanel9.add(treq, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 120, 250, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel9.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(440, 120, 30, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Periode");
        jPanel9.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 100, 20));

        jLabel12.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel12.setText("Kode In/Eksternal");
        jPanel9.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 30, 150, 20));
        jPanel9.add(tkodeppn, new org.netbeans.lib.awtextra.AbsoluteConstraints(810, 60, 50, -1));
        jPanel9.add(tkodeineks, new org.netbeans.lib.awtextra.AbsoluteConstraints(810, 30, 50, -1));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Nomor PO");
        jPanel9.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 150, 110, 20));
        jPanel9.add(tbukti_po, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 150, 250, 20));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel9.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(440, 150, 30, -1));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jTabbedPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 1064, Short.MAX_VALUE)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE))
                    .addComponent(jPanel9, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jPanel9, javax.swing.GroupLayout.PREFERRED_SIZE, 186, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 226, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addContainerGap(22, Short.MAX_VALUE))
        );
    }// </editor-fold>//GEN-END:initComponents

    private void Date2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_Date2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_Date2PropertyChange

    private void Date2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_Date2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_Date2KeyPressed

    private void Date1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_Date1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_Date1PropertyChange

    private void Date1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_Date1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_Date1KeyPressed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        if(con.akses(akses, rfa.PRINT)) po_rawmaterial(1);
    }//GEN-LAST:event_jButton8ActionPerformed

    private void tblandkaryawanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tblandkaryawanFocusLost
        sql = "select * from mkaryawan where nik = '"+tblandkaryawan.getText()+"'  ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tblandkaryawan1.setText(rs.getString("cname"));

            }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tblandkaryawanFocusLost

    private void tblandkaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tblandkaryawanKeyPressed
      
    }//GEN-LAST:event_tblandkaryawanKeyPressed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tblandkaryawan.setText(a.Kode);
            tblandkaryawan1.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        if(con.akses(akses, rfa.PRINT)) payment(1);
    }//GEN-LAST:event_jButton10ActionPerformed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogReqOrder a = new DialogReqOrder();
        a.setVisible(true);
        if(a.status) {
            treq.setText(a.kode);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        if(con.akses(akses, rfa.PRINT)) reqpo(1);
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jButton5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton5ActionPerformed
        if(con.akses(akses, rfa.PRINT)) hutangbiaya(1);
    }//GEN-LAST:event_jButton5ActionPerformed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        if(con.akses(akses, rfa.PRINT)) hutangbiaya_jangkawaktu(1);
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jButton19ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton19ActionPerformed
        if(con.akses(akses, rfa.PRINT)) rekap_hutang(1);
    }//GEN-LAST:event_jButton19ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        if(con.akses(akses, rfa.PRINT)) reqpo(2);
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        if(con.akses(akses, rfa.PRINT)) po_rawmaterial(2);
    }//GEN-LAST:event_jButton9ActionPerformed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        if(con.akses(akses, rfa.PRINT)) payment(2);
    }//GEN-LAST:event_jButton11ActionPerformed

    private void jButton16ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton16ActionPerformed
        if(con.akses(akses, rfa.PRINT)) hutangbiaya(2);
    }//GEN-LAST:event_jButton16ActionPerformed

    private void jButton18ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton18ActionPerformed
        if(con.akses(akses, rfa.PRINT)) hutangbiaya_jangkawaktu(2);
    }//GEN-LAST:event_jButton18ActionPerformed

    private void jButton20ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton20ActionPerformed
        if(con.akses(akses, rfa.PRINT)) rekap_hutang(2);
    }//GEN-LAST:event_jButton20ActionPerformed

    private void jButton21ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton21ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton21ActionPerformed

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton15ActionPerformed
        DialogPurchaseOrder a = new DialogPurchaseOrder();
        a.setVisible(true);
        if(a.status){

            tbukti_po.setText(a.kode);

        }
    }//GEN-LAST:event_jButton15ActionPerformed

    private void tsupplierFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tsupplierFocusLost
        sql = "select * from msupplier where kode_supplier = '"+tsupplier.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tsupplier2.setText(rs.getString("cname"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tsupplierFocusLost

    private void tsupplierKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tsupplierKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tsupplierKeyPressed

    private void jButton17ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton17ActionPerformed
        DialogSupplier a = new DialogSupplier(1);
        a.setVisible(true);
        if(a.status){
            tsupplier.setText(a.kode);
            tsupplier2.setText(a.nama);
        }
    }//GEN-LAST:event_jButton17ActionPerformed

    private void jButton22ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton22ActionPerformed
        if(con.akses(akses, rfa.PRINT)) payment_tagihan(1);
    }//GEN-LAST:event_jButton22ActionPerformed

    private void jButton23ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton23ActionPerformed
        if(con.akses(akses, rfa.PRINT)) hutangbiaya_bill(1);
    }//GEN-LAST:event_jButton23ActionPerformed

    private void jButton24ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton24ActionPerformed
        po_penerimaan();
    }//GEN-LAST:event_jButton24ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private com.toedter.calendar.JDateChooser Date1;
    private com.toedter.calendar.JDateChooser Date2;
    private javax.swing.ButtonGroup GroupBland;
    private javax.swing.ButtonGroup GroupMaterialProduksi;
    private javax.swing.ButtonGroup GroupSKM;
    private javax.swing.ButtonGroup GroupSKT;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton16;
    private javax.swing.JButton jButton17;
    private javax.swing.JButton jButton18;
    private javax.swing.JButton jButton19;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton20;
    private javax.swing.JButton jButton21;
    private javax.swing.JButton jButton22;
    private javax.swing.JButton jButton23;
    private javax.swing.JButton jButton24;
    private javax.swing.JButton jButton5;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JPanel jPanel9;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2payment;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JScrollPane jScrollPane7;
    private javax.swing.JScrollPane jScrollPane8;
    private javax.swing.JScrollPane jScrollPane9;
    private javax.swing.JScrollPane jScrollPanePOPenerimaan;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTablepo_penerimaan;
    private javax.swing.JTable tableBland;
    private javax.swing.JTable tableSKTBorongan;
    private javax.swing.JTable tableSKTList;
    private javax.swing.JTable tablepayment;
    private javax.swing.JTable tablereqpo;
    private javax.swing.JTable tableskm;
    private javax.swing.JTable tableskm1;
    private javax.swing.JTable tableskm2;
    private javax.swing.JTextField tblandkaryawan;
    private javax.swing.JTextField tblandkaryawan1;
    private javax.swing.JTextField tbukti_po;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tkodeineks;
    private javax.swing.JTextField tkodeppn;
    private javax.swing.JTextField treq;
    private javax.swing.JTextField tsupplier;
    private javax.swing.JTextField tsupplier2;
    // End of variables declaration//GEN-END:variables
}
