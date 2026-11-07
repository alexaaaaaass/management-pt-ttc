/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Report;

import Accounting.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogReqOrder;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Production.*;
import RPurchasing.*;
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
public class RMaterial extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    String akses="REPORT MATERIAL";
    public RMaterial(String mode) {
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
        DateReq1.setDate(new Date());
        DateReq2.setDate(new Date());
        DataBland1.setDate(new Date());
        DataBland2.setDate(new Date());
        DateSkt1.setDate(new Date());
        DateSkt2.setDate(new Date());
        DateSkm1.setDate(new Date());
        DateSkm2.setDate(new Date());
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }
    
    private void kartustock(int mode){
        String dtran1=rfa.DateString(keyssDef, DataBland1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DataBland2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, kartustock);
        DefaultTableModel model = (DefaultTableModel) kartustock.getModel();
        
        sql="select a.dtran,a.kstatus,a.cgroup,a.cproduct,b.cnameProduct,sum(a.nqty) as qty,a.cunit\n" +
            "from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            " where a.ccode='"+tcode.getText()+"' and (a.dtran<='"+dtran1+"' and a.dtran<='"+dtran2+"') and a.cproduct like '%"+tproduk1.getText()+"%' "+
            "group by a.dtran,a.cgroup,a.cproduct,a.cunit,a.kstatus,b.cnameProduct\n" +
                
            "order by a.cgroup,a.cproduct,a.dtran,a.kstatus";
        
        sql="select * from (\n" +
            "	select 0 as urut,'' as cnbkt,'"+dtran1+"' as dtran,'"+dtran1+"' as serverdate,'Saldo Akhir' as kstatus,a.cgroup,a.cproduct,b.cnameProduct,\n" +
            "	case when sum(a.nqty) > 0 then sum(a.nqty) else 0 end as db,\n" +
            "	case when sum(a.nqty) < 0 then sum(a.nqty) else 0 end as cr, \n" +
            "	a.cunit,'Stock Akhir '+b.cnameProduct as note\n" +
            "	from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "	where a.ccode='"+tcode.getText()+"' and (a.dtran<'"+dtran1+"') \n" +
            "	and a.cproduct like '%"+tproduk1.getText()+"%' \n" +
            "   and a.cproduct in (select cproduct from MaterialStock where ccode='"+tcode.getText()+"' and dtran>='"+dtran1+"' and dtran<='"+dtran2+"')"+
            "	group by  a.cgroup,a.cproduct,a.cunit, b.cnameProduct\n" +
            "\n" +
            "	union all\n" +
            "\n" +
            "	select 1 as urut,a.cnbkt,a.dtran,a.ServerDate,a.kstatus,a.cgroup,a.cproduct,b.cnameProduct,\n" +
            "	case when (a.nqty) > 0 then (a.nqty) else 0 end as db,\n" +
            "	case when (a.nqty) < 0 then (a.nqty)*-1 else 0 end as cr, \n" +
            "	a.cunit,a.notes\n" +
            "	from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "	where a.ccode='"+tcode.getText()+"' and (a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"') \n" +
            "	and a.cproduct like '%"+tproduk1.getText()+"%'  \n" +
            " ) a \n" +
            " order by cgroup,cproduct,urut,dtran,serverdate";
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        Double saldoakhir=0.0,mutasi=0.0;
        String kode = "";
        try {
            while(rs.next()){
                if(!kode.equals(rs.getString("cgroup")+rs.getString("cproduct"))) {
                    mutasi=rs.getDouble("db") - rs.getDouble("cr");
                } else {
                    mutasi=(mutasi+rs.getDouble("db")) - rs.getDouble("cr");
                    if(mutasi==0.0) {
                       mutasi = 0.0; 
                    }
                } 
                
                
                count++;
                model.addRow(new Object[]{
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("kstatus"),
                    rs.getString("cnbkt"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getString("note"),
                    rs.getString("cunit"),
                    rs.getDouble("db"),
                    rs.getDouble("cr"), 
                    mutasi,
                    
                });
                
                kode = rs.getString("cgroup")+rs.getString("cproduct");
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMaterial.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Tanggal");
            kolom.add("Status");
            kolom.add("Group");
            kolom.add("Kode Produk");
            kolom.add("Nama Produk");
            kolom.add("Qty");
            kolom.add("Unit");
            con.exportExcel(tableBland,kolom,"Laporan Kartu Stock",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(kartustock.getModel());
            String treport = "report/RKartuStockMaterial.jrxml";
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
    private void exportdetailkartustock(int mode){
        String dtran1=rfa.DateString(keyssDef, DataBland1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DataBland2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, exportTable);
        DefaultTableModel model = (DefaultTableModel) exportTable.getModel();
        
        sql="select a.dtran,a.kstatus,a.cgroup,a.cproduct,b.cnameProduct,sum(a.nqty) as qty,a.cunit\n" +
            "from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            " where a.ccode='"+tcode.getText()+"' and (a.dtran<='"+dtran1+"' and a.dtran<='"+dtran2+"') "+
            "group by a.dtran,a.cgroup,a.cproduct,a.cunit,a.kstatus,b.cnameProduct\n" +
                
            "order by a.cgroup,a.cproduct,a.dtran,a.kstatus";
        sql="select a.ccode,a.dtran,a.kstatus,a.cgroup,a.cproduct,b.cnameProduct,(a.nqty) as qty,a.cunit,\n" +
            "case when a.cunit=b.cunit then a.nqty else  (a.nqty*a.nunit_smallvalue) end as qtyconversion,b.cunit as satuanreal,\n" +
            "case when (case when a.cunit=b.cunit then a.nqty else  (a.nqty*a.nunit_smallvalue) end)>=0 then (case when a.cunit=b.cunit then a.nqty else  (a.nqty*a.nunit_smallvalue) end)\n" +
            "	else 0 end as debit,\n" +
            "case when (case when a.cunit=b.cunit then a.nqty else  (a.nqty*a.nunit_smallvalue) end)<0 then (case when a.cunit=b.cunit then a.nqty else  (a.nqty*a.nunit_smallvalue) end)*-1\n" +
            "	else 0 end as kredit,\n" +
            "notes\n" +
            "from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"'  and a.cproduct like '%"+tproduk1.getText()+"%'\n" +
            "order by a.cgroup,a.cproduct,a.dtran,a.kstatus";
        rs = con.queryResult(sql);
        int count=0;
        String produk="";
        Double stock=0.0;
        try {
            while(rs.next()){
                if(rs.getString("kstatus").equalsIgnoreCase("SA")) {
                    if(count>=1){
                        model.addRow(new Object[]{
                            null,
//                            null,
//                            "STOCK AKHIR",
//                            null,
//                            null,
//                            null,
//                            null,
//                            null,
//                            null,
//                            null,
//                            stock,
//                            "STOCK AKHIR",
                        });
                    }
                    stock = rs.getDouble("debit")-rs.getDouble("kredit");
                } else {
                    stock = stock+rs.getDouble("debit")-rs.getDouble("kredit");
                }
                
                
                
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("qty"),
                    rs.getString("cunit"),
                    rs.getDouble("qtyconversion"),
                    rs.getString("satuanreal"),
                    rs.getDouble("debit"),
                    rs.getDouble("kredit"),
                    stock,
                    rs.getString("satuanreal"),
                    rs.getString("notes"),
                    
                });
                
                count++;
                produk = rs.getString("cproduct");
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMaterial.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Gudang");
            kolom.add("Tanggal");
            kolom.add("Kode Produk");
            kolom.add("Nama Produk");
            kolom.add("Qty");
            kolom.add("Satuan");
            kolom.add("Qty Konversi");
            kolom.add("Satuan Terkecil");
            kolom.add("Debet (Konversi)");
            kolom.add("Kredit (Konversi)");
            kolom.add("Saldo Stock");
            kolom.add("Satuan");
            kolom.add("Keterangan");
            con.exportExcel(exportTable,kolom,"Laporan Detail Stock Bahan",dtran1,dtran2);
            return;
        }
    }
    private void kartustocktf(int mode){
        String dtran1=rfa.DateString(keyssDef, DateSkm1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateSkm2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, tableBland1);
        DefaultTableModel model = (DefaultTableModel) tableBland1.getModel();
        
        sql="select a.ccodedest,a.dtran,a.kstatus,a.cgroup,a.cproduct,b.cnameProduct,case when a.cunit=b.cunit then sum(a.nqty) else  sum(a.nqty*a.nunit_smallvalue) end as  qty,a.cunit\n" +
            "from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            " where a.ccode='"+tcode.getText()+"' and a.ccodedest <> '' and (a.dtran<='"+dtran1+"' and a.dtran<='"+dtran2+"') "+
            "group by a.ccodedest,a.dtran,a.cgroup,a.cproduct,a.cunit,a.kstatus,b.cnameProduct,b.cunit\n" +
                
            "order by a.cgroup,a.cproduct,a.dtran,a.kstatus";
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{
                    rs.getString("ccodedest"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("kstatus"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("qty"),
                    rs.getString("cunit"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMaterial.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Tujuan");
            kolom.add("Tanggal");
            kolom.add("Status");
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Qty");
            kolom.add("Unit");
            con.exportExcel(tableBland1,kolom,"Laporan Material Transfer",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableBland1.getModel());
            String treport = "report/RKartuStockMaterialTF.jrxml";
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
    
    private void stocknominal(int mode){
        String dtran1=rfa.DateString(keyssDef, DateSkt1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateSkt2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tablepayment.getModel();
        rfa.clear_table(keyssDef, tablepayment);

        sql = "select a.cgroup,a.cproduct,b.cnameProduct,case when a.cunit=b.cunit then sum(a.nqty) else  sum(a.nqty*a.nunit_smallvalue) end as  qty,b.cunit,a.ncogs as hpp,a.ncogs*sum(a.nqty*a.nunit_smallvalue) as totalhpp\n" +
"from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
                " where a.ccode='"+tcode.getText()+"' and a.dtran<='"+dtran2+"' "+
"group by a.cgroup,a.cproduct,b.cnameProduct,b.cunit,a.ncogs,a.cunit";
        rs = con.queryResult(sql);
        
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("qty"),
                    rs.getString("cunit"),
                    rs.getDouble("hpp"),
                    rs.getDouble("totalhpp"),
                    
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMaterial.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Nama Produk");
            kolom.add("Qty");
            kolom.add("Unit");
            kolom.add("Hpp");
            kolom.add("Total HPP");
            con.exportExcel(tablepayment,kolom,"Laporan Stock Nominal",dtran1,dtran2);
            return;
        }

        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tablepayment.getModel());
            String treport = "report/RStockMaterialNominal.jrxml";
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
    
    private void stock(int mode){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tablereqpo.getModel();
        rfa.clear_table(keyssDef, tablereqpo);
            
        //nanti pke yg ini
        
        sql="select b.cnameProduct,a.ccode,a.cgroup,a.cproduct,sum(qtyawal) as qtyawal,sum(qtydebet) as qtydebet,sum(qtykredit) as qtykredit,satuan_in from (\n" +
"	select ccode,cgroup,cproduct,sum(nqty) as qtyawal,0 as qtydebet,0 as qtykredit,cunit as satuan_in  \n" +
"	from MaterialStock \n" +
"	where ccode = '"+tcode.getText()+"' and dtran<'"+dtran2+"'\n" +
"	group by ccode,cgroup,cproduct,cunit \n" +
"	union all\n" +
"	select ccode,cgroup,cproduct,0 as qtyawal,\n" +
"	sum(case when nqty > 0 then nqty else 0 end) as qtydebet,\n" +
"	sum(case when nqty <=0 then nqty*-1 else 0 end) as qtykredit,\n" +
"	cunit as satuan_in  \n" +
"	from MaterialStock\n" +
"	where ccode = '"+tcode.getText()+"' and (dtran>='"+dtran1+"' and dtran<='"+dtran2+"')\n" +
"	group by ccode,cgroup,cproduct,cunit \n" +
") a join MProduk b on a.cproduct=b.cproduct\n" +
"group by  b.cnameProduct,a.ccode,a.cgroup,a.cproduct,a.satuan_in \n" +
"order by a.ccode,a.cgroup,a.cproduct";
        
        System.out.println(sql);
//        sql="select a.cgroup,a.cproduct,b.cnameProduct,case when a.cunit=b.cunit then sum(a.nqty) else  sum(a.nqty*a.nunit_smallvalue) end as qty,b.cunit\n" +
//            "from MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
//                " where a.ccode = '"+tcode.getText()+"' and a.dtran<='"+dtran2+"' "+
//            "group by a.cgroup,a.cproduct,b.cnameProduct,b.cunit,a.cunit\n"
//                + " order by a.cgroup,a.cproduct,b.cnameProduct";
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("qtyawal"),
                    rs.getString("satuan_in"),
                    rs.getDouble("qtyawal"),
                    rs.getDouble("qtydebet"),
                    rs.getDouble("qtykredit"),
                    (rs.getDouble("qtyawal")+rs.getDouble("qtydebet"))-rs.getDouble("qtykredit"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMaterial.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Nama Produk");
            kolom.add("Qty");
            kolom.add("Unit");
            con.exportExcel(tablereqpo,kolom,"Laporan Stock ",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tablereqpo.getModel());
            String treport = "report/RStockMaterial.jrxml";
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
        String dtran1=rfa.DateString(keyssDef, DateSkm1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateSkm2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm.getModel();
        rfa.clear_table(keyssDef, tableskm);
        
        sql="select * from (\n" +
            "	select a.id,a.cnbkt,a.dtran,a.cdesc,sum((b.nqty*b.nprice)-b.ndisc) as total,b.kode_supplier,c.cname as supplier,\n" +
            "	isnull(d.nominalpay,0) as nominalpay,a.cdue_date,\n" +
            "	case when sum((b.nqty*b.nprice)-b.ndisc)<=isnull(d.nominalpay,0) then 'LUNAS' else 'BELUM LUNAS' end as status,\n" +
            "	case when sum((b.nqty*b.nprice)-b.ndisc)<=isnull(d.nominalpay,0) then '1' else '0' end as statusurut,\n" +
            "	case when a.cdue_date<getdate() then 'MELEWATI JATUH TEMPO' else '' end as statusduedate\n" +
            "	from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt\n" +
            "	join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
            "	left join (\n" +
            "		select a.cnbkt,a.cnbkt_PO,sum(b.namnt) as nominalpay from TranPayment a join TranPayment_detail b on a.cnbkt=b.cnbkt and a.id=b.id_cnbkt\n" +
            "		group by a.cnbkt,a.cnbkt_PO\n" +
            "	) d on a.cnbkt=d.cnbkt_PO\n" +
            "	group by a.id,a.cnbkt,a.dtran,a.cdesc,b.kode_supplier,c.cname,d.nominalpay,a.cdue_date\n" +
            ") final\n" +
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
            Logger.getLogger(RMaterial.class.getName()).log(Level.SEVERE, null, ex);
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
                    for (int j = 0; j < kolom.size(); j++) {
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
                JOptionPane.showMessageDialog(this, "TIDAK DAPAT DI SIMPAN, MOHON TUTUP TERLEBIH DAHULU FILE EXCEL HASIL EXPORT", "File Created", JOptionPane.ERROR_MESSAGE);
                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
            } catch (IOException ex) {
                JOptionPane.showMessageDialog(this, "TIDAK DAPAT DI SIMPAN, MOHON TUTUP TERLEBIH DAHULU FILE EXCEL HASIL EXPORT", "File Created", JOptionPane.ERROR_MESSAGE);
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
        jScrollPane2 = new javax.swing.JScrollPane();
        tablepayment = new javax.swing.JTable();
        GroupMaterialProduksi = new javax.swing.ButtonGroup();
        jScrollPane4 = new javax.swing.JScrollPane();
        tableSKTBorongan = new javax.swing.JTable();
        jScrollPane5 = new javax.swing.JScrollPane();
        tableSKTList = new javax.swing.JTable();
        jScrollPane6Stok = new javax.swing.JScrollPane();
        tablereqpo = new javax.swing.JTable();
        jScrollPane7 = new javax.swing.JScrollPane();
        tableskm = new javax.swing.JTable();
        jScrollPane1 = new javax.swing.JScrollPane();
        tableBland = new javax.swing.JTable();
        jScrollPane3 = new javax.swing.JScrollPane();
        tableBland1 = new javax.swing.JTable();
        jScrollPane8 = new javax.swing.JScrollPane();
        exportTable = new javax.swing.JTable();
        jScrollPane9kartustock = new javax.swing.JScrollPane();
        kartustock = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jLabel4 = new javax.swing.JLabel();
        DateReq2 = new com.toedter.calendar.JDateChooser();
        DateReq1 = new com.toedter.calendar.JDateChooser();
        jLabel6 = new javax.swing.JLabel();
        jButton7 = new javax.swing.JButton();
        jButton2 = new javax.swing.JButton();
        jPanel8 = new javax.swing.JPanel();
        DataBland1 = new com.toedter.calendar.JDateChooser();
        DataBland2 = new com.toedter.calendar.JDateChooser();
        jLabel9 = new javax.swing.JLabel();
        jLabel18 = new javax.swing.JLabel();
        jButton8 = new javax.swing.JButton();
        jButton9 = new javax.swing.JButton();
        jLabel20 = new javax.swing.JLabel();
        tproduk1 = new javax.swing.JTextField();
        tproduk3 = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        jPanel1 = new javax.swing.JPanel();
        DateSkt2 = new com.toedter.calendar.JDateChooser();
        jLabel12 = new javax.swing.JLabel();
        DateSkt1 = new com.toedter.calendar.JDateChooser();
        jLabel15 = new javax.swing.JLabel();
        jButton10 = new javax.swing.JButton();
        jButton11 = new javax.swing.JButton();
        jLabel19 = new javax.swing.JLabel();
        tproduk = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tproduk2 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        DateSkm1 = new com.toedter.calendar.JDateChooser();
        jLabel16 = new javax.swing.JLabel();
        DateSkm2 = new com.toedter.calendar.JDateChooser();
        jLabel17 = new javax.swing.JLabel();
        jButton5 = new javax.swing.JButton();
        jButton16 = new javax.swing.JButton();
        jLabel8 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton21 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();

        tablepayment.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "cgroup", "cproduct", "namaproduk", "qty", "cunit", "hpp", "totalhpp"
            }
        ));
        jScrollPane2.setViewportView(tablepayment);

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
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cgroup", "cproduct", "namaproduk", "qty", "nunit", "sa", "db", "cr", "total"
            }
        ));
        jScrollPane6Stok.setViewportView(tablereqpo);

        tableskm.setModel(new javax.swing.table.DefaultTableModel(
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
        jScrollPane7.setViewportView(tableskm);
        if (tableskm.getColumnModel().getColumnCount() > 0) {
            tableskm.getColumnModel().getColumn(0).setHeaderValue("cnbkt");
            tableskm.getColumnModel().getColumn(1).setHeaderValue("dtran");
            tableskm.getColumnModel().getColumn(2).setHeaderValue("cdesc");
            tableskm.getColumnModel().getColumn(3).setHeaderValue("total");
            tableskm.getColumnModel().getColumn(9).setHeaderValue("status_duedate");
        }

        tableBland.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "kstatus", "cgroup", "cproduct", "namaproduk", "qty", "cunit"
            }
        ));
        jScrollPane1.setViewportView(tableBland);
        if (tableBland.getColumnModel().getColumnCount() > 0) {
            tableBland.getColumnModel().getColumn(5).setHeaderValue("qty");
        }

        tableBland1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccodedest", "dtran", "kstatus", "cgroup", "cproduct", "namaproduk", "qty", "cunit"
            }
        ));
        jScrollPane3.setViewportView(tableBland1);

        exportTable.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Title 1", "Title 2", "Title 3", "Title 4", "Title 5", "Title 6", "Title 7", "Title 8", "Title 9", "Title 10", "Title 11", "Title 12", "Title 13", "Title 14", "Title 15", "Title 16", "Title 17", "Title 18", "Title 19", "Title 20", "Title 21", "Title 22"
            }
        ));
        jScrollPane8.setViewportView(exportTable);

        kartustock.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "kstatus", "cnbkt", "cgroup", "cproduct", "namaproduk", "note", "cunit", "db", "cr", "mts"
            }
        ));
        jScrollPane9kartustock.setViewportView(kartustock);

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

        jPanel2.add(jPanel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 500, 5));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Material Report");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("s/d");
        jPanel4.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 20, 20));

        DateReq2.setDateFormatString("dd/MM/yyyy");
        DateReq2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateReq2PropertyChange(evt);
            }
        });
        DateReq2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateReq2KeyPressed(evt);
            }
        });
        jPanel4.add(DateReq2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 40, 120, -1));

        DateReq1.setDateFormatString("dd/MM/yyyy");
        DateReq1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateReq1PropertyChange(evt);
            }
        });
        DateReq1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateReq1KeyPressed(evt);
            }
        });
        jPanel4.add(DateReq1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Periode");
        jPanel4.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 100, 20));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton7.setText("View");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 120, 120, -1));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton2.setText("Excel");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 150, 120, -1));

        jTabbedPane1.addTab("Stock", jPanel4);

        jPanel8.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        DataBland1.setDateFormatString("dd/MM/yyyy");
        DataBland1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DataBland1PropertyChange(evt);
            }
        });
        DataBland1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DataBland1KeyPressed(evt);
            }
        });
        jPanel8.add(DataBland1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        DataBland2.setDateFormatString("dd/MM/yyyy");
        DataBland2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DataBland2PropertyChange(evt);
            }
        });
        DataBland2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DataBland2KeyPressed(evt);
            }
        });
        jPanel8.add(DataBland2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 40, 120, -1));

        jLabel9.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel9.setText("s/d");
        jPanel8.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 20, 20));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Periode");
        jPanel8.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 100, 20));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton8.setText("View");
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 120, 170, -1));

        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton9.setText("Export Stock Bahan");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 150, 170, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Produk");
        jPanel8.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 90, 20));

        tproduk1.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tproduk1FocusLost(evt);
            }
        });
        tproduk1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tproduk1KeyPressed(evt);
            }
        });
        jPanel8.add(tproduk1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 80, -1));

        tproduk3.setEditable(false);
        tproduk3.setBackground(new java.awt.Color(153, 153, 255));
        tproduk3.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tproduk3, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 70, 210, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 70, 30, -1));

        jTabbedPane1.addTab("Kartu Stock", jPanel8);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        DateSkt2.setDateFormatString("dd/MM/yyyy");
        DateSkt2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkt2PropertyChange(evt);
            }
        });
        DateSkt2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkt2KeyPressed(evt);
            }
        });
        jPanel1.add(DateSkt2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 40, 120, -1));

        jLabel12.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel12.setText("s/d");
        jPanel1.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 20, 20));

        DateSkt1.setDateFormatString("dd/MM/yyyy");
        DateSkt1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkt1PropertyChange(evt);
            }
        });
        DateSkt1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkt1KeyPressed(evt);
            }
        });
        jPanel1.add(DateSkt1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        jLabel15.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel15.setText("Periode");
        jPanel1.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 100, 20));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton10.setText("View");
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 120, 120, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton11.setText("Excel");
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 150, 120, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Produk");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 90, 20));

        tproduk.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tprodukFocusLost(evt);
            }
        });
        tproduk.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tprodukKeyPressed(evt);
            }
        });
        jPanel1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 80, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 70, 30, -1));

        tproduk2.setEditable(false);
        tproduk2.setBackground(new java.awt.Color(153, 153, 255));
        tproduk2.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tproduk2, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 70, 210, -1));

        jTabbedPane1.addTab("Kartu Stock ^ Nominal", jPanel1);

        jPanel5.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        DateSkm1.setDateFormatString("dd/MM/yyyy");
        DateSkm1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkm1PropertyChange(evt);
            }
        });
        DateSkm1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkm1KeyPressed(evt);
            }
        });
        jPanel5.add(DateSkm1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("s/d");
        jPanel5.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 20, 20));

        DateSkm2.setDateFormatString("dd/MM/yyyy");
        DateSkm2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkm2PropertyChange(evt);
            }
        });
        DateSkm2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkm2KeyPressed(evt);
            }
        });
        jPanel5.add(DateSkm2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 40, 120, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Periode");
        jPanel5.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 100, 20));

        jButton5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton5.setText("View");
        jButton5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton5ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton5, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 90, 120, -1));

        jButton16.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton16.setText("Excel");
        jButton16.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton16ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton16, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 120, 120, -1));

        jTabbedPane1.addTab("Transfer Stock", jPanel5);

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Kode Gudang");

        tcode.setEditable(false);

        jButton21.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton21.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton21ActionPerformed(evt);
            }
        });

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(layout.createSequentialGroup()
                        .addGap(10, 10, 10)
                        .addComponent(jLabel8, javax.swing.GroupLayout.PREFERRED_SIZE, 110, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(20, 20, 20)
                        .addComponent(tcode, javax.swing.GroupLayout.PREFERRED_SIZE, 90, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, 0)
                        .addComponent(jButton21, javax.swing.GroupLayout.PREFERRED_SIZE, 30, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(10, 10, 10)
                        .addComponent(tcode1, javax.swing.GroupLayout.PREFERRED_SIZE, 200, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addContainerGap(javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                    .addGroup(layout.createSequentialGroup()
                        .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jTabbedPane1)
                            .addGroup(layout.createSequentialGroup()
                                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addGap(0, 132, Short.MAX_VALUE)))
                        .addContainerGap())))
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jLabel8, javax.swing.GroupLayout.PREFERRED_SIZE, 20, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(tcode, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jButton21)
                    .addComponent(tcode1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jTabbedPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 313, Short.MAX_VALUE)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jButton5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton5ActionPerformed
        kartustocktf(1);
    }//GEN-LAST:event_jButton5ActionPerformed

    private void DateSkm2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm2KeyPressed

    private void DateSkm2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm2PropertyChange

    private void DateSkm1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm1KeyPressed

    private void DateSkm1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm1PropertyChange

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        if(con.akses(akses, rfa.PRINT)) stocknominal(1);
    }//GEN-LAST:event_jButton10ActionPerformed

    private void DateSkt1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkt1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt1KeyPressed

    private void DateSkt1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkt1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt1PropertyChange

    private void DateSkt2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkt2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt2KeyPressed

    private void DateSkt2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkt2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt2PropertyChange

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        if(con.akses(akses, rfa.PRINT)) kartustock(1);
    }//GEN-LAST:event_jButton8ActionPerformed

    private void DataBland2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DataBland2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland2KeyPressed

    private void DataBland2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DataBland2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland2PropertyChange

    private void DataBland1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DataBland1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland1KeyPressed

    private void DataBland1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DataBland1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland1PropertyChange

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        if(con.akses(akses, rfa.PRINT)) stock(1);
    }//GEN-LAST:event_jButton7ActionPerformed

    private void DateReq1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateReq1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq1KeyPressed

    private void DateReq1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateReq1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq1PropertyChange

    private void DateReq2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateReq2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq2KeyPressed

    private void DateReq2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateReq2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq2PropertyChange

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
       stock(2);
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        exportdetailkartustock(2);
    }//GEN-LAST:event_jButton9ActionPerformed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        stocknominal(2);
    }//GEN-LAST:event_jButton11ActionPerformed

    private void jButton16ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton16ActionPerformed
        kartustocktf(2);
    }//GEN-LAST:event_jButton16ActionPerformed

    private void jButton21ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton21ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton21ActionPerformed

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        sql = "select * from mproduk where cproduct = '"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tproduk1.setText(rs.getString("cgroup"));
            tproduk2.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
//        rfa.EnterNextFocus(evt, tqty);
    }//GEN-LAST:event_tprodukKeyPressed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogProduk a = new DialogProduk(2);
        a.setVisible(true);
        
        rfa.empty(keyssDef, tproduk,tproduk2);
                
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tproduk2.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void tproduk1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tproduk1FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tproduk1FocusLost

    private void tproduk1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tproduk1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tproduk1KeyPressed

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogProduk a = new DialogProduk(2);
        a.setVisible(true);
        
        rfa.empty(keyssDef, tproduk1,tproduk3);
                
        if(a.status){
            tproduk1.setText(a.kodeBarang);
            tproduk3.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton13ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private com.toedter.calendar.JDateChooser DataBland1;
    private com.toedter.calendar.JDateChooser DataBland2;
    private com.toedter.calendar.JDateChooser DateReq1;
    private com.toedter.calendar.JDateChooser DateReq2;
    private com.toedter.calendar.JDateChooser DateSkm1;
    private com.toedter.calendar.JDateChooser DateSkm2;
    private com.toedter.calendar.JDateChooser DateSkt1;
    private com.toedter.calendar.JDateChooser DateSkt2;
    private javax.swing.ButtonGroup GroupBland;
    private javax.swing.ButtonGroup GroupMaterialProduksi;
    private javax.swing.ButtonGroup GroupSKM;
    private javax.swing.ButtonGroup GroupSKT;
    private javax.swing.JTable exportTable;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton16;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton21;
    private javax.swing.JButton jButton5;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6Stok;
    private javax.swing.JScrollPane jScrollPane7;
    private javax.swing.JScrollPane jScrollPane8;
    private javax.swing.JScrollPane jScrollPane9kartustock;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable kartustock;
    private javax.swing.JTable tableBland;
    private javax.swing.JTable tableBland1;
    private javax.swing.JTable tableSKTBorongan;
    private javax.swing.JTable tableSKTList;
    private javax.swing.JTable tablepayment;
    private javax.swing.JTable tablereqpo;
    private javax.swing.JTable tableskm;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tproduk1;
    private javax.swing.JTextField tproduk2;
    private javax.swing.JTextField tproduk3;
    // End of variables declaration//GEN-END:variables
}
