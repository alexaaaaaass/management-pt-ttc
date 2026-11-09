/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Report;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogMembers;
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
public class RAnalisaTransaksi extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs,rs1;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    String akses="REPORT ANALISA TRANSAKSI";
    
    ArrayList<String> idmesin;
    public RAnalisaTransaksi(String mode) {
        initComponents();
  
        deff();
        push_button();
        jTabbedPane1.remove(2);
        /*
        
        select * from (
	select 0 as urut,a.ccode,'' as dtran,'' as caccnt,'INVESTASI' as akun,'' as caccll,'SALDO AWAL' as akunll,
	case when a.ctran='IV' then a.nilai*-1 else  a.nilai end as nilai,
	sum(case when (case when a.ctran='IV' then a.nilai*-1 else  a.nilai end)>0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end else 0 end) as debet,
	sum(case when case when a.ctran='IV' then a.nilai*-1 else  a.nilai end<=0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end*-1 else 0 end) as kredit,
	a.kode_customer,b.cname as customer ,'Saldo Awal' as cnote
	from AccAll a join MCustomer b on a.kode_customer=b.kode_customer
	join MAccount c on a.caccnt=c.caccnt left join MAccount d on a.caccll=d.caccnt
	where isnull(a.kode_customer,'') != '' 
	and ( left(a.caccnt,3) = '101' or left(a.caccnt,3) = '102' or a.ctran='IV')  
	group by a.ccode,a.nilai,a.kode_customer,b.cname,a.ctran

	union all

	select 0 as urut,a.ccode,a.dtran,a.caccnt,c.cdesc as akun,'' as caccll,d.cdesc as akunll,
	case when a.ctran='IV' then a.nilai*-1 else  a.nilai end as nilai,
	(case when (case when a.ctran='IV' then a.nilai*-1 else  a.nilai end)>0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end else 0 end) as debet,
	(case when case when a.ctran='IV' then a.nilai*-1 else  a.nilai end<=0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end*-1 else 0 end) as kredit,
	a.kode_customer,b.cname as customer ,a.cnote
	from AccAll a join MCustomer b on a.kode_customer=b.kode_customer
	join MAccount c on a.caccnt=c.caccnt left join MAccount d on a.caccll=d.caccnt
	where isnull(a.kode_customer,'') != '' 
	 and ( left(a.caccnt,3) = '101' or left(a.caccnt,3) = '102' or a.ctran='IV')  
	
	--and a.dtran >= '+dtran1+' and a.dtran <='+dtran2+'
) a
order by a.ccode,a.kode_customer,caccnt,dtran,urut
        */
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
        Date3.setDate(new Date());
        Date4.setDate(new Date());
        
        tcode.setText(deffProgram.get("ccode"));
        tcode2.setText(deffProgram.get("ccode"));
        tcode4.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  
            tcode1.setText(rs.getString("cname"));
            tcode3.setText(rs.getString("cname"));
            tcode5.setText(rs.getString("cname"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        mesin.removeAll();
        mesin.removeAllItems();
        idmesin = new ArrayList<>();
        mesin.addItem(""); idmesin.add("0");
        sql = "select * from MMesin";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  mesin.addItem(rs.getString("cname")); idmesin.add(rs.getString("id"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }
    
    private void kartustock(){
        String dtran1=rfa.DateString(keyssDef, DataBland1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DataBland2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, tableBland);
        DefaultTableModel model = (DefaultTableModel) tableBland.getModel();
        
        sql="select a.status as kstatus,a.dtran,a.cgroup,a.cproduct,a.nqty as qty,a.cunit ,b.cnameProduct\n" +
                "from ProductStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
                "order by a.cgroup,b.cproduct,a.dtran";
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{
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
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableBland.getModel());
            String treport = "report/RKartuStockBahan.jrxml";
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
    

    private void summaryaccount(){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tabel.getModel();
        rfa.clear_table(keyssDef, tabel);
            
        sql="declare\n" +
            "@dawal datetime,\n" +
            "@dakhir datetime,\n" +
            "@ccode nvarchar(10)\n" +
            "\n" +
            "set @dawal = '"+dtran1+"'\n" +
            "set @dakhir = '"+dtran2+"'\n" +
            "set @ccode = '"+tcode.getText()+"'\n" +
            "\n" +
            "Select a.ccode, upper(faccount) as GroupAcc, a.caccnt, b.cdesc as cdesc, \n" +
            "case sign( Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end)) when 1 then\n" +
            "Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end)\n" +
            "else 0 end  as Sadbt,\n" +
            "case sign( Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end)) when -1 then\n" +
            "abs(Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end))\n" +
            "else 0 end  as SaCrd,\n" +
            "Sum( case Sign(datediff(d, @dawal, a.dtran)) when -1 then 0 else case sign(a.nilai) when -1 then 0 else a.nilai end  end) as MtDbt,\n" +
            "Sum( case Sign(datediff(d, @dawal, a.dtran)) when -1 then 0 else case sign(a.nilai) when 1 then 0 else abs(a.nilai) end  end) as MtCrd,\n" +
            "case sign( sum(a.nilai)) when 1 then sum(a.nilai) else 0 end as SkDbt, \n" +
            "case sign(sum(a.nilai)) when -1 then abs(sum(a.nilai)) else 0 end as SkCrd,\n" +
            "0 as saldo\n" +
            "from AccAll a join MAccount b on a.caccnt=b.caccnt\n" +
            "where a.dtran <= @dakhir and a.ccode= @ccode\n" +
            "group by a.ccode, a.caccnt, faccount, b.cdesc\n" +
            "order by a.caccnt";
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{rs.getString("ccode"),rs.getString("groupacc"),
                        rs.getString("caccnt"),rs.getString("cdesc"),rs.getDouble("sadbt"),
                        rs.getDouble("sacrd"),rs.getDouble("mtdbt"),
                        rs.getDouble("mtcrd"),rs.getDouble("skdbt"),
                        rs.getDouble("skcrd"),rs.getDouble("saldo")});
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tabel.getModel());
            String treport = "report/rsummaryaccount.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode1.getText());
            param.put("periode", dtran1+" s/d "+dtran2);
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
    private void neraca(){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tabel.getModel();
        rfa.clear_table(keyssDef, tabel);
            
        sql="Select a.ccode, case upper(faccount) when 'CL_ASET' then 'AKTIVA' else 'PASIVA' end AS GroupAcc, a.caccnt, cdesc,\n" +
            "case sign(sum(a.nilai)) when 1 then sum(a.nilai) else 0 end as Debet, \n" +
            "abs( case sign(sum(a.nilai)) when 1 then 0 else sum(a.nilai) end) as Kredit,\n" +
            "sum(a.nilai) as saldo \n" +
            "FROM AccAll a join MAccount b on a.caccnt=b.caccnt\n" +
            "WHERE a.ccode = '"+tcode.getText()+"' and a.dtran <= '"+dtran2+"'\n" +
            "GROUP BY a.ccode, a.caccnt, cdesc, faccount \n" +
            "order by GroupAcc";
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{rs.getString("ccode"),rs.getString("groupacc"),
                        rs.getString("caccnt"),rs.getString("cdesc"),rs.getDouble("debet"),
                        rs.getDouble("kredit"),0,0,0,0,rs.getDouble("saldo")});
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tabel.getModel());
            String treport = "report/rneraca.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode1.getText());
            param.put("periode", dtran1+" s/d "+dtran2);
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
    private void labarugi(){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tabel.getModel();
        rfa.clear_table(keyssDef, tabel);
             
        sql="exec LabaRugi '"+tcode.getText()+"','"+dtran1+"','"+dtran2+"'";
        
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{rs.getString("ccode"),rs.getString("groupacc"),
                        rs.getString("caccnt"),rs.getString("cdesc"),rs.getDouble("skdb"),
                        rs.getDouble("skcr"),0,0,0,0,rs.getDouble("saldo")});
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tabel.getModel());
            String treport = "report/LabaRugi.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode1.getText());
            param.put("periode", dtran1+" s/d "+dtran2);
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
    
    private void labarugimesin(){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tabel.getModel();
        rfa.clear_table(keyssDef, tabel);
            
        sql="Select a.ccode, \n" +
            "case upper(faccount) when 'CL_ASET' then 'AKTIVA' else 'PASIVA' end as GroupAcc,\n" +
            "a.caccnt, cdesc, \n" +
            "case sign(sum(nilai)) when 1 then sum(nilai) else 0 end as SkDb,\n" +
            "case sign(sum(nilai)) when 1 then 0 else abs(sum(nilai)) end as SkCr,\n" +
            "sum(nilai) as saldo\n" +
            "from AccAll  a join MAccount b on a.caccnt=b.caccnt\n" +
            "where a.dtran <= '"+dtran2+"' and a.ccode = '"+tcode.getText()+"'  and upper(faccount) = 'CL_BEBAN'\n" + //CL_RL
            "group by a.ccode, faccount, a.caccnt, cdesc";
        
        sql="exec LabaRugiMesin '"+tcode.getText()+"','"+dtran1+"','"+dtran2+"',"+idmesin.get(mesin.getSelectedIndex());
        
        Double nilaiakhir = 0.0;
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{rs.getString("ccode"),rs.getString("groupacc"),
                        rs.getString("caccnt"),rs.getString("cdesc"),rs.getDouble("skdb"),
                        rs.getDouble("skcr"),0,0,0,0,rs.getDouble("saldo")});
                
                nilaiakhir+=rs.getDouble("saldo");
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tabel.getModel());
            String treport = "report/LabaRugiMesin.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode1.getText());
            param.put("periode", dtran1+" s/d "+dtran2);
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            param.put("mesin", mesin.getSelectedItem().toString());
            
            if(mesin.getSelectedItem().toString().equalsIgnoreCase("MESIN 3 A")) {
                param.put("percent1", "(53%) "+rfa.formatDecimal2.format(nilaiakhir*0.53));
                param.put("percent2", "(47%) "+rfa.formatDecimal2.format(nilaiakhir*0.47));
            } else if(mesin.getSelectedItem().toString().equalsIgnoreCase("MESIN 4")) {
                param.put("percent1", "(40%) "+rfa.formatDecimal2.format(nilaiakhir*0.4));
                param.put("percent2", "(60%) "+rfa.formatDecimal2.format(nilaiakhir*0.6));
            }
            
            
            
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void labarugimesind(){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tabel.getModel();
        rfa.clear_table(keyssDef, tabel);
            
        sql="Select a.ccode, \n" +
            "case upper(faccount) when 'CL_ASET' then 'AKTIVA' else 'PASIVA' end as GroupAcc,\n" +
            "a.caccnt, cdesc, \n" +
            "case sign(sum(nilai)) when 1 then sum(nilai) else 0 end as SkDb,\n" +
            "case sign(sum(nilai)) when 1 then 0 else abs(sum(nilai)) end as SkCr,\n" +
            "sum(nilai) as saldo\n" +
            "from AccAll  a join MAccount b on a.caccnt=b.caccnt\n" +
            "where a.dtran <= '"+dtran2+"' and a.ccode = '"+tcode.getText()+"'  and upper(faccount) = 'CL_BEBAN'\n" + //CL_RL
            "group by a.ccode, faccount, a.caccnt, cdesc";
        
        sql="exec LabaRugiMesinD '"+tcode.getText()+"','"+dtran1+"','"+dtran2+"',"+idmesin.get(mesin.getSelectedIndex());
        
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                
                String groupacc = "";
                String desc = "";
                if(rs.getInt("laba") == 0) {
                    groupacc = "Pendapatan (Detail)";
                    desc=rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy")+" / "+rs.getString("namaproduk")+" ("+rfa.formatDecimal2.format(rs.getDouble("jumlah"))+" "+rs.getString("cunit")+") - "+rs.getString("customer");
                } else {
                    groupacc="Pengeluaran (Detail)";
                    desc=rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy")+" / "+rs.getString("akunlain")+" / "+rs.getString("namaproduk") + " "+rs.getString("customer");
                }
                
                Double debet = 0.0,kredit=0.0;
                if(rs.getDouble("total") <0) {
                    kredit = rs.getDouble("total")*-1;
                } else {
                    debet = rs.getDouble("total");
                }
                
                model.addRow(new Object[]{rs.getString("ccode"),groupacc,
                        rs.getString("cnbkt"),
                        desc,
                        debet,kredit,0,0,0,0,rs.getDouble("total")});
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tabel.getModel());
            String treport = "report/LabaRugiMesinD.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode1.getText());
            param.put("periode", dtran1+" s/d "+dtran2);
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            param.put("mesin", mesin.getSelectedItem().toString());
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void jurnalumum(){
        String dtran1=rfa.DateString(keyssDef, DataBland1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DataBland2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tabel4.getModel();
        rfa.clear_table(keyssDef, tabel4);
        
        String akunstart = "000000";
        if(!tacc.getText().isEmpty()){
            akunstart = tacc.getText();
        }
        
        String akunend = "zzzzzzzzz";
        if(!taccll.getText().isEmpty()){
            akunend = taccll.getText();
        }
        
        sql="declare\n" +
            "@dawal datetime,\n" +
            "@dakhir datetime,\n" +
            "@ccode nvarchar(10)\n" +
            "\n" +
            "set @dawal = '"+dtran1+"'\n" +
            "set @dakhir = '"+dtran2+"'\n" +
            "set @ccode = '"+tcode2.getText()+"'\n" +
            "\n" +
            "\n" +
            "select * from (\n" +
            "	select -2 as id,AccAll.ccode, AccAll.caccnt, max(AccAll.dtran) as dtran, \n" +
            "		   space(20) as cnbkt , 'Saldo awal' as notes, MAccount.cdesc, \n" +
            "		   (case sign(sum(AccAll.nilai)) when 1 then sum(AccAll.nilai) else 0 end )as Debet, \n" +
            "		   (case sign(sum(AccAll.nilai)) when -1 then abs(sum(AccAll.nilai)) else 0 end )as Kredit, \n" +
            "		   sum(AccAll.nilai) as Saldo, 0 as sn,\n" +
            "		   0 as MtDbt, 0 as MtCrd, space(10) as Account\n" +
            "	from AccAll, MAccount\n" +
            "	where AccAll.ccode = MAccount.ccode and AccAll.caccnt = MAccount.caccnt\n" +
            "	  and AccAll.dtran < @dawal\n" +
            "	  and AccAll.ccode = @ccode\n" +
            "	group by AccAll.ccode, AccAll.caccnt, MAccount.cdesc\n" +
            "\n" +
            "	union \n" +
            "\n" +
            "	select AccAll.id,AccAll.ccode, AccAll.caccnt, AccAll.dtran,  \n" +
            "		   AccAll.cnbkt, AccAll.cnote as notes, MAccount.cdesc, \n" +
            "		   case sign(AccAll.nilai) when 1 then AccAll.nilai else 0 end as Debet, \n" +
            "		   case sign(AccAll.nilai) when -1 then abs(AccAll.nilai) else 0 end as Kredit, \n" +
            "		   AccAll.nilai as saldo,  sign(AccAll.nilai) as sn,\n" +
            "		   case sign(AccAll.nilai) when 1 then AccAll.nilai else 0 end as MtDbt, \n" +
            "		   case sign(AccAll.nilai) when -1 then abs(AccAll.nilai) else 0 end as MtCrd, \n" +
            "		   AccAll.caccll as Account\n" +
            "	from AccAll, MAccount\n" +
            "	where AccAll.ccode = MAccount.ccode and AccAll.caccnt = MAccount.caccnt\n" +
            "	  and AccAll.dtran >= @dawal\n" +
            "	  and AccAll.dtran <= @dakhir\n" +
            "	  and AccAll.ccode = @ccode\n" +
            ") final\n" +
            "where caccnt >= '"+akunstart+"' and caccnt <='"+akunend+"' \n" +
            "order by ccode, caccnt, dtran, cnbkt,id,sn desc\n" +
            "";
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{rs.getString("ccode"),rs.getString("caccnt"),rs.getDate("dtran"),
                    rs.getString("notes").trim(),rs.getString("cnbkt"),rs.getString("cdesc"),
                    rs.getDouble("debet"),rs.getDouble("kredit"),
                    rs.getDouble("saldo"),"",rs.getDouble("mtdbt"),
                    rs.getDouble("mtcrd"),rs.getString("account")});
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tabel4.getModel());
            String treport = "report/RGeneralLedger.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode3.getText());
            param.put("periode", dtran1+" s/d "+dtran2);
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
    
    private void detailcustomer(){
        String dtran1=rfa.DateString(keyssDef, Date3.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date4.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) ExcelTable.getModel();
        rfa.clear_table(keyssDef, ExcelTable);
        
        sql="exec invest_all '"+tcode4.getText()+"','"+dtran1+"','"+dtran2+"','"+tcustomerk.getText()+"'";
        System.out.println(sql);
        rs = con.queryResult(sql);
        Double saldo=0.0;
        try {
            while(rs.next()){
                if(rs.getInt("urut") == 0) {
                    saldo = rs.getDouble("debet") - rs.getDouble("kredit");
                } else {
                    saldo = saldo + (rs.getDouble("debet") - rs.getDouble("kredit"));
                }
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("kode_customer"),
                    rs.getString("customer"),
                    rs.getString("caccnt"),
                    rs.getString("akun"),
                    rs.getString("caccll"),
                    rs.getString("akunll"),
                    rs.getDouble("debet"),
                    rs.getDouble("kredit"),
                    saldo,
                    rs.getString("cnote")
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Kode Gudang");
            kolom.add("Tanggal");
            kolom.add("Kode Customer");
            kolom.add("Nama Customer");
            kolom.add("Kode Akun");
            kolom.add("Nama Akun");
            kolom.add("Akun Pengurang");
            kolom.add("Nama Akun Pengurang");
            kolom.add("Debet");
            kolom.add("Kredit");
            kolom.add("Saldo Akhir");
            kolom.add("Keterangan");
            con.exportExcel(ExcelTable,kolom,"Laporan Detail Investor-Customer ",dtran1,dtran2);
    }
    private void rekapcustomer(){
        String dtran1=rfa.DateString(keyssDef, Date3.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date4.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) ExcelTable.getModel();
        rfa.clear_table(keyssDef, ExcelTable);
        
        sql="select urut,ccode,caccnt,akun,caccll,akunll,sum(debet) as debet,sum(kredit) as kredit,kode_customer,customer from (\n" +
            "	select 0 as urut,a.ccode,'"+dtran1+"' as dtran,'' as caccnt,'INVESTASI' as akun,'' as caccll,'SALDO AWAL' as akunll,\n" +
            "	sum(case when a.ctran='IV' then a.nilai*-1 else  a.nilai end) as nilai,\n" +
            "	sum(case when (case when a.ctran='IV' then a.nilai*-1 else  a.nilai end)>0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end else 0 end) as debet,\n" +
            "	sum(case when case when a.ctran='IV' then a.nilai*-1 else  a.nilai end<=0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end*-1 else 0 end) as kredit,\n" +
            "	a.kode_customer,b.cname as customer ,'Saldo Awal' as cnote\n" +
            "	from AccAll a join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "	join MAccount c on a.caccnt=c.caccnt left join MAccount d on a.caccll=d.caccnt\n" +
            "	where isnull(a.kode_customer,'') != '' \n" +
            "	and a.kode_customer like '%"+tcustomerk.getText()+"%'\n" +
            "	and ( left(a.caccnt,3) = '101' or left(a.caccnt,3) = '102' or a.ctran='IV')  \n" +
            "	and a.ccode = '"+tcode4.getText()+"'\n" +
            "	and a.dtran<'"+dtran1+"'\n" +
            "	group by a.ccode,a.kode_customer,b.cname\n" +
            "\n" +
            "	union all\n" +
            "\n" +
            "	select 1 as urut,a.ccode,a.dtran,a.caccnt,c.cdesc as akun,'' as caccll,d.cdesc as akunll,\n" +
            "	case when a.ctran='IV' then a.nilai*-1 else  a.nilai end as nilai,\n" +
            "	(case when (case when a.ctran='IV' then a.nilai*-1 else  a.nilai end)>0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end else 0 end) as debet,\n" +
            "	(case when case when a.ctran='IV' then a.nilai*-1 else  a.nilai end<=0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end*-1 else 0 end) as kredit,\n" +
            "	a.kode_customer,b.cname as customer ,a.cnote\n" +
            "	from AccAll a join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "	join MAccount c on a.caccnt=c.caccnt left join MAccount d on a.caccll=d.caccnt\n" +
            "	where isnull(a.kode_customer,'') != '' \n" +
            "	and a.kode_customer like '%"+tcustomerk.getText()+"%'\n" +
            "	and ( left(a.caccnt,3) = '101' or left(a.caccnt,3) = '102' or a.ctran='IV')  \n" +
            "	and a.ccode = '"+tcode4.getText()+"'\n" +
            "	and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"' \n" +
            ") a\n" +
            "group by urut,ccode,caccnt,akun,caccll,akunll,kode_customer,customer\n" +
            "order by a.ccode,a.kode_customer,caccnt,urut\n" +
            "";
        rs = con.queryResult(sql);
        Double saldo=0.0;
        try {
            while(rs.next()){
                if(rs.getInt("urut") == 0) {
                    saldo = rs.getDouble("debet") - rs.getDouble("kredit");
                } else {
                    saldo = saldo + (rs.getDouble("debet") - rs.getDouble("kredit"));
                }
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rs.getString("kode_customer"),
                    rs.getString("customer"),
                    rs.getString("caccnt"),
                    rs.getString("akun"),
                    rs.getString("caccll"),
                    rs.getString("akunll"),
                    rs.getDouble("debet"),
                    rs.getDouble("kredit"),
                    saldo
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        ArrayList<String> kolom;
        kolom = new ArrayList<>();
        kolom.add("Kode Gudang");
        kolom.add("Kode Customer");
        kolom.add("Nama Customer");
        kolom.add("Kode Akun");
        kolom.add("Nama Akun");
        kolom.add("Akun Pengurang");
        kolom.add("Nama Akun Pengurang");
        kolom.add("Debet");
        kolom.add("Kredit");
        kolom.add("Saldo Akhir");
        con.exportExcel(ExcelTable,kolom,"Laporan Rekap Investor-Customer ",dtran1,dtran2);
    }
    private void saldocustomer(){
        String dtran1=rfa.DateString(keyssDef, Date3.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date4.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) ExcelTable.getModel();
        rfa.clear_table(keyssDef, ExcelTable);
        
        sql="select ccode,sum(debet) as debet,sum(kredit) as kredit,kode_customer,customer from (\n" +
            "	select 0 as urut,a.ccode,'"+dtran1+"' as dtran,'' as caccnt,'INVESTASI' as akun,'' as caccll,'SALDO AWAL' as akunll,\n" +
            "	sum(case when a.ctran='IV' then a.nilai*-1 else  a.nilai end) as nilai,\n" +
            "	sum(case when (case when a.ctran='IV' then a.nilai*-1 else  a.nilai end)>0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end else 0 end) as debet,\n" +
            "	sum(case when case when a.ctran='IV' then a.nilai*-1 else  a.nilai end<=0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end*-1 else 0 end) as kredit,\n" +
            "	a.kode_customer,b.cname as customer ,'Saldo Awal' as cnote\n" +
            "	from AccAll a join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "	join MAccount c on a.caccnt=c.caccnt left join MAccount d on a.caccll=d.caccnt\n" +
            "	where isnull(a.kode_customer,'') != '' \n" +
            "	and a.kode_customer like '%"+tcustomerk.getText()+"%'\n" +
            "	and ( left(a.caccnt,3) = '101' or left(a.caccnt,3) = '102' or a.ctran='IV')  \n" +
            "	and a.ccode = '"+tcode4.getText()+"'\n" +
            "	and a.dtran<'"+dtran1+"'\n" +
            "	group by a.ccode,a.kode_customer,b.cname\n" +
            "\n" +
            "	union all\n" +
            "\n" +
            "	select 1 as urut,a.ccode,a.dtran,a.caccnt,c.cdesc as akun,'' as caccll,d.cdesc as akunll,\n" +
            "	case when a.ctran='IV' then a.nilai*-1 else  a.nilai end as nilai,\n" +
            "	(case when (case when a.ctran='IV' then a.nilai*-1 else  a.nilai end)>0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end else 0 end) as debet,\n" +
            "	(case when case when a.ctran='IV' then a.nilai*-1 else  a.nilai end<=0 then case when a.ctran='IV' then a.nilai*-1 else  a.nilai end*-1 else 0 end) as kredit,\n" +
            "	a.kode_customer,b.cname as customer ,a.cnote\n" +
            "	from AccAll a join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "	join MAccount c on a.caccnt=c.caccnt left join MAccount d on a.caccll=d.caccnt\n" +
            "	where isnull(a.kode_customer,'') != '' \n" +
            "	and a.kode_customer like '%"+tcustomerk.getText()+"%'\n" +
            "	and ( left(a.caccnt,3) = '101' or left(a.caccnt,3) = '102' or a.ctran='IV')  \n" +
            "	and a.ccode = '"+tcode4.getText()+"'\n" +
            "	and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"' \n" +
            ") a\n" +
            "group by ccode,kode_customer,customer\n" +
            "order by a.ccode,a.kode_customer\n" +
            "" +
            "";
        System.out.println(sql);
        rs = con.queryResult(sql);
        Double saldo=0.0;
        try {
            while(rs.next()){
                saldo = rs.getDouble("debet") - rs.getDouble("kredit");
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rs.getString("kode_customer"),
                    rs.getString("customer"),
                    rs.getDouble("debet"),
                    rs.getDouble("kredit"),
                    saldo
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Kode Gudang");
            kolom.add("Kode Customer");
            kolom.add("Nama Customer");
            kolom.add("Debet");
            kolom.add("Kredit");
            kolom.add("Saldo Akhir");
            con.exportExcel(ExcelTable,kolom,"Laporan Saldo Investor-Customer ",dtran1,dtran2);
    }
    
    private void viewdetailinvestor(){
        String dtran1=rfa.DateString(keyssDef, Date3.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date4.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTableDetailInvestor.getModel();
        rfa.clear_table(keyssDef, jTableDetailInvestor);
        
        sql="exec invest_all '"+tcode4.getText()+"','"+dtran1+"','"+dtran2+"','"+tcustomerk.getText()+"'";
        rs = con.queryResult(sql);
        Double saldo=0.0;
        try {
            while(rs.next()){
                if(rs.getInt("nomor") == 1) {
                    saldo = rs.getDouble("debet") - rs.getDouble("kredit");
                } else {
                    saldo = saldo + (rs.getDouble("debet") - rs.getDouble("kredit"));
                }
                
                String ket=rs.getString("cnote");
                if(rs.getString("caccnt").equals("103.101")) {
                    ket="INVOICE\n";
                    rs1 = con.queryResult("select cproduct,jumlah*harga as total from transale \n" +
                                        "where ccode='"+tcode4.getText()+"' and id_member='"+rs.getString("kode_customer")+"' and cnbkt='"+rs.getString("cnbkt")+"'");
                    while(rs1.next()){
                        ket+=rs1.getString("cproduct")+" @"+rfa.formatDecimal1.format(rs1.getDouble("total"))+"\n";
                    }
                }
                
                
                model.addRow(new Object[]{
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cnbkt"),
                    rs.getString("caccnt"),rs.getString("akun"),rs.getString("caccll"),rs.getString("akunll"),
                    rs.getDouble("nilai"),rs.getDouble("debet"),rs.getDouble("kredit"),saldo,
                    rs.getString("kode_customer"),
                    rs.getString("customer"),
                    ket
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTableDetailInvestor.getModel());
            String treport = "report/RInvestorDetail.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("periode", dtran1+" s/d "+dtran2);
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
    
    private void viewsaldoinvestor(){
        String dtran1=rfa.DateString(keyssDef, Date3.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date4.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTableDetailInvestor.getModel();
        rfa.clear_table(keyssDef, jTableDetailInvestor);
        
        sql="exec invest_saldo '"+tcode4.getText()+"','"+dtran1+"','"+dtran2+"','"+tcustomerk.getText()+"'";
        rs = con.queryResult(sql);
        Double saldo=0.0;
        try {
            while(rs.next()){
                saldo = rs.getDouble("debet") - rs.getDouble("kredit");
              
                String ket="";
                
                
                model.addRow(new Object[]{
                    "",
                    "",
                    "","","","",
                    0.0,rs.getDouble("debet"),rs.getDouble("kredit"),saldo,
                    rs.getString("kode_customer"),
                    rs.getString("customer"),
                    ket
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTableDetailInvestor.getModel());
            String treport = "report/RInvestorSaldo.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("periode", dtran1+" s/d "+dtran2);
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
    
    private void ExportDetailInvestor(int mode){
        String dtran1=rfa.DateString(keyssDef, DataBland1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DataBland2.getDate(),"yyyy-MM-dd");
        
//        sql = "select a.ccode,a.cnbkt,a.cnbkt_PR,a.dtran,a.cdue_date,b.cgroup,b.cproduct,b.nqty,b.nprice,b.ndisc,b.ntotal,b.nunit,b.kode_supplier,c.cname as supplier,\n" +
//                "d.nqty as nqtyin,d.nunit as nunitin\n" +
//                "from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
//                "join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
//                "left join (\n" +
//                "	select a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,sum(b.nqty) as nqty,b.nunit,b.kode_supplier \n" +
//                "	from TranRawMaterialIn a join TranRawMaterialInD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
//                "	where isnull(a.cnbkt_po,'') <> ''\n" +
//                "	group by a.ccode,a.cnbkt_po,b.cgroup,b.cproduct,b.nunit,b.kode_supplier \n" +
//                ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt_po and b.cgroup=d.cgroup and b.cproduct=d.cproduct and b.kode_supplier=d.kode_supplier\n" +
//                " where a.ccode='"+tcode.getText()+"' and (a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"') and a.cnbkt like '%"+tbukti_po.getText()+"%' "+
//                " order by ccode,cnbkt\n" +
//                "";
        
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
            kolom.add("Bukti Permintaan");
            kolom.add("Tanggal");
            kolom.add("Jatuh Tempo");
            kolom.add("Group Produk");
            kolom.add("Produk/Bahan");
            kolom.add("Qty/Berat");
            kolom.add("Harga Per Qty");
            kolom.add("Diskon");
            kolom.add("Total");
            kolom.add("Satuan");
            kolom.add("Kode Supplier");
            kolom.add("Nama Supplier");
            kolom.add("Qty Masuk");
            kolom.add("Satuan Masuk");
            con.exportExcel(tableBland,kolom,"Laporan PO RawMaterial",dtran1,dtran2);
            return;
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

                
            
                String f = chooser.getSelectedFile() + "\\Export "+namafile+" "+dawal+" sd "+dakhir+" "+rfa.DateString(keyssDef,new Date(),"yyyyMMdd hhmmss")+".xls";
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
        GroupMaterialProduksi = new javax.swing.ButtonGroup();
        jScrollPane2 = new javax.swing.JScrollPane();
        tabel = new javax.swing.JTable();
        jScrollPane4 = new javax.swing.JScrollPane();
        tableSKTBorongan = new javax.swing.JTable();
        jScrollPane5 = new javax.swing.JScrollPane();
        tableSKTList = new javax.swing.JTable();
        jScrollPane6 = new javax.swing.JScrollPane();
        tablereqpo = new javax.swing.JTable();
        jScrollPane1 = new javax.swing.JScrollPane();
        tableBland = new javax.swing.JTable();
        jScrollPane7 = new javax.swing.JScrollPane();
        tabel4 = new javax.swing.JTable();
        jScrollPane3 = new javax.swing.JScrollPane();
        ExcelTable = new javax.swing.JTable();
        jScrollPane8DetailInvestor = new javax.swing.JScrollPane();
        jTableDetailInvestor = new javax.swing.JTable();
        jButton3 = new javax.swing.JButton();
        jButton18 = new javax.swing.JButton();
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
        jLabel3 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        jButton11 = new javax.swing.JButton();
        jLabel12 = new javax.swing.JLabel();
        mesin = new javax.swing.JComboBox();
        jButton17 = new javax.swing.JButton();
        jButton19 = new javax.swing.JButton();
        jPanel8 = new javax.swing.JPanel();
        jLabel7 = new javax.swing.JLabel();
        DataBland1 = new com.toedter.calendar.JDateChooser();
        DataBland2 = new com.toedter.calendar.JDateChooser();
        jLabel9 = new javax.swing.JLabel();
        jLabel18 = new javax.swing.JLabel();
        jButton8 = new javax.swing.JButton();
        tacc = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        jLabel8 = new javax.swing.JLabel();
        jLabel11 = new javax.swing.JLabel();
        taccll = new javax.swing.JTextField();
        jButton6 = new javax.swing.JButton();
        taccll1 = new javax.swing.JTextField();
        tacc1 = new javax.swing.JTextField();
        tcode2 = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        tcode3 = new javax.swing.JTextField();
        jPanel1 = new javax.swing.JPanel();
        jLabel10 = new javax.swing.JLabel();
        tcode4 = new javax.swing.JTextField();
        jButton16 = new javax.swing.JButton();
        tcode5 = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        tcustomerk1 = new javax.swing.JTextField();
        tcustomerk = new javax.swing.JTextField();
        jButton4 = new javax.swing.JButton();
        jButton9 = new javax.swing.JButton();
        jButton12 = new javax.swing.JButton();
        jLabel19 = new javax.swing.JLabel();
        Date3 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        Date4 = new com.toedter.calendar.JDateChooser();
        jButton1 = new javax.swing.JButton();
        jButton2 = new javax.swing.JButton();

        tabel.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "groupacc", "caccnt", "cdesc", "sadbt", "sacrd", "mtdbt", "mtcrd", "skdbt", "skcrd", "saldo"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane2.setViewportView(tabel);

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
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null}
            },
            new String [] {
                "cgroup", "cproduct", "namaproduk", "qty", "nunit"
            }
        ));
        jScrollPane6.setViewportView(tablereqpo);

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

        tabel4.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "caccnt", "dtran", "notes", "cnbkt", "cdesc", "debet", "kredit", "saldo", "sn", "mtdbt", "mtcrd", "account"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane7.setViewportView(tabel4);

        ExcelTable.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "Title 1", "Title 2", "Title 3", "Title 4", "Title 5", "Title 6", "Title 7", "Title 8", "Title 9", "Title 10", "Title 11", "Title 12", "Title 13", "Title 14", "Title 15", "Title 16", "Title 17", "Title 18", "Title 19", "Title 20", "Title 21", "Title 22", "Title 23", "Title 24", "Title 25", "Title 26", "Title 27", "Title 28", "Title 29", "Title 30", "Title 31", "Title 32", "Title 33", "Title 34", "Title 35", "Title 36", "Title 37", "Title 38", "Title 39", "Title 40", "Title 41", "Title 42", "Title 43", "Title 44", "Title 45", "Title 46", "Title 47", "Title 48", "Title 49", "Title 50", "Title 51", "Title 52", "Title 53", "Title 54", "Title 55"
            }
        ));
        ExcelTable.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jScrollPane3.setViewportView(ExcelTable);

        jTableDetailInvestor.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cnbkt", "caccnt", "akun", "caccll", "akunll", "nilai", "debet", "kredit", "saldo", "kode_customer", "customer", "cnote"
            }
        ));
        jScrollPane8DetailInvestor.setViewportView(jTableDetailInvestor);

        jButton3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton3.setText("View Rekap Out");

        jButton18.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton18.setText("Export Rekap Pengeluaran");
        jButton18.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton18ActionPerformed(evt);
            }
        });

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
        jLabel2.setText("Report Analisa Transaksi");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("s/d");
        jPanel4.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 60, 20, 20));

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
        jPanel4.add(DateReq2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 60, 120, -1));

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
        jPanel4.add(DateReq1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 60, 120, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Periode");
        jPanel4.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 60, 100, 20));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton7.setText(" Summary Account");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 150, 210, -1));

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang");
        jPanel4.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 30, 130, 20));

        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel4.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 30, 90, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 30, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel4.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 30, 200, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton10.setText("Neraca");
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 150, 210, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton11.setText("Laba Rugi (Global)");
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 200, 210, -1));

        jLabel12.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel12.setText("Mesin");
        jPanel4.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 90, 40, 20));

        mesin.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jPanel4.add(mesin, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 90, 180, -1));

        jButton17.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton17.setText("Laba Rugi (Mesin)-Detail");
        jButton17.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton17ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton17, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 250, 210, -1));

        jButton19.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton19.setText("Laba Rugi (Mesin)");
        jButton19.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton19ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton19, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 200, 210, -1));

        jTabbedPane1.addTab("Trial Balance", jPanel4);

        jPanel8.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Kode Log");
        jPanel8.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 72, 20));

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
        jPanel8.add(DataBland1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 120, -1));

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
        jPanel8.add(DataBland2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 70, 120, -1));

        jLabel9.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel9.setText("s/d");
        jPanel8.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 20, 20));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Periode");
        jPanel8.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 100, 20));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton8.setText("View");
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 170, 120, -1));

        tacc.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                taccFocusLost(evt);
            }
        });
        tacc.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                taccKeyPressed(evt);
            }
        });
        jPanel8.add(tacc, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 100, 90, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 100, 30, -1));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Kode Akun");
        jPanel8.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 100, 90, 20));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Sampai");
        jPanel8.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 130, 120, 20));

        taccll.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                taccllFocusLost(evt);
            }
        });
        taccll.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                taccllKeyPressed(evt);
            }
        });
        jPanel8.add(taccll, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 130, 90, -1));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 130, 30, -1));

        taccll1.setEditable(false);
        taccll1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel8.add(taccll1, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 130, 150, -1));

        tacc1.setEditable(false);
        tacc1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel8.add(tacc1, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 100, 150, -1));

        tcode2.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcode2FocusLost(evt);
            }
        });
        jPanel8.add(tcode2, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 90, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 40, 30, -1));

        tcode3.setEditable(false);
        tcode3.setBackground(new java.awt.Color(153, 153, 255));
        tcode3.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tcode3, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 200, -1));

        jTabbedPane1.addTab("Jurnal Umum", jPanel8);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Kode Gudang");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 110, 20));

        tcode4.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcode4FocusLost(evt);
            }
        });
        jPanel1.add(tcode4, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 90, -1));

        jButton16.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton16.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton16ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton16, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 40, 30, -1));

        tcode5.setEditable(false);
        tcode5.setBackground(new java.awt.Color(153, 153, 255));
        tcode5.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tcode5, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 200, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Customer");
        jPanel1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 100, -1, 20));

        tcustomerk1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tcustomerk1KeyTyped(evt);
            }
        });
        jPanel1.add(tcustomerk1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 100, 90, -1));

        tcustomerk.setEditable(false);
        jPanel1.add(tcustomerk, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 100, 50, -1));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton4ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 100, 30, -1));

        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton9.setText("Export Saldo Akhir");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 230, 200, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton12.setText("Export Detail Transkasi");
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 190, 200, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Periode");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 100, 20));

        Date3.setDateFormatString("dd/MM/yyyy");
        Date3.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                Date3PropertyChange(evt);
            }
        });
        Date3.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                Date3KeyPressed(evt);
            }
        });
        jPanel1.add(Date3, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 120, -1));

        jLabel13.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel13.setText("s/d");
        jPanel1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 20, 20));

        Date4.setDateFormatString("dd/MM/yyyy");
        Date4.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                Date4PropertyChange(evt);
            }
        });
        Date4.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                Date4KeyPressed(evt);
            }
        });
        jPanel1.add(Date4, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 70, 120, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton1.setText("View Saldo Akhir");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(80, 230, 190, -1));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton2.setText("View Detail");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(80, 190, 190, -1));

        jTabbedPane1.addTab("Transaksi Investor", jPanel1);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jTabbedPane1)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 198, Short.MAX_VALUE)))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 370, Short.MAX_VALUE)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        if(con.akses(akses, rfa.PRINT)) jurnalumum();
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
        if(con.akses(akses, rfa.PRINT)) summaryaccount();
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

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        if(con.akses(akses, rfa.PRINT)) neraca();
    }//GEN-LAST:event_jButton10ActionPerformed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        if(con.akses(akses, rfa.PRINT)) labarugi();
    }//GEN-LAST:event_jButton11ActionPerformed

    private void taccFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccFocusLost
        sql = "select * from maccount where caccnt = '"+tacc.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, tacc1);
            while(rs.next()){  tacc1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_taccFocusLost

    private void taccKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccKeyPressed
        rfa.EnterNextFocus(evt, taccll);
    }//GEN-LAST:event_taccKeyPressed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            tacc.setText(a.caccnt);
            tacc1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void taccllFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccllFocusLost
        sql = "select * from maccount where caccnt = '"+taccll.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, taccll1);
            while(rs.next()){  taccll1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_taccllFocusLost

    private void taccllKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccllKeyPressed
        
    }//GEN-LAST:event_taccllKeyPressed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            taccll.setText(a.caccnt);
            taccll1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void tcode2FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcode2FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcode2FocusLost

    private void jButton15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton15ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode2.setText(a.ccode);
            tcode3.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton15ActionPerformed

    private void tcode4FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcode4FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcode4FocusLost

    private void jButton16ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton16ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode4.setText(a.ccode);
            tcode5.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton16ActionPerformed

    private void tcustomerk1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tcustomerk1KeyTyped
        if(evt.getKeyChar() == KeyEvent.VK_BACK_SPACE) {
            rfa.empty(keyssDef, tcustomerk,tcustomerk1);
        }
    }//GEN-LAST:event_tcustomerk1KeyTyped

    private void jButton4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton4ActionPerformed
        DialogMembers a = new DialogMembers();
        a.setVisible(true);
        rfa.empty(keyssDef, tcustomerk,tcustomerk1);
        if(a.status){
            tcustomerk.setText(a.Kode);
            tcustomerk1.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton4ActionPerformed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        saldocustomer();
    }//GEN-LAST:event_jButton9ActionPerformed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        detailcustomer();
    }//GEN-LAST:event_jButton12ActionPerformed

    private void Date3PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_Date3PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_Date3PropertyChange

    private void Date3KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_Date3KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_Date3KeyPressed

    private void Date4PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_Date4PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_Date4PropertyChange

    private void Date4KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_Date4KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_Date4KeyPressed

    private void jButton18ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton18ActionPerformed
        rekapcustomer();
    }//GEN-LAST:event_jButton18ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        viewdetailinvestor();
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        viewsaldoinvestor();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jButton17ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton17ActionPerformed
        labarugimesind();
    }//GEN-LAST:event_jButton17ActionPerformed

    private void jButton19ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton19ActionPerformed
        if(con.akses(akses, rfa.PRINT)) labarugimesin();
    }//GEN-LAST:event_jButton19ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private com.toedter.calendar.JDateChooser DataBland1;
    private com.toedter.calendar.JDateChooser DataBland2;
    private com.toedter.calendar.JDateChooser Date3;
    private com.toedter.calendar.JDateChooser Date4;
    private com.toedter.calendar.JDateChooser DateReq1;
    private com.toedter.calendar.JDateChooser DateReq2;
    private javax.swing.JTable ExcelTable;
    private javax.swing.ButtonGroup GroupBland;
    private javax.swing.ButtonGroup GroupMaterialProduksi;
    private javax.swing.ButtonGroup GroupSKM;
    private javax.swing.ButtonGroup GroupSKT;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton16;
    private javax.swing.JButton jButton17;
    private javax.swing.JButton jButton18;
    private javax.swing.JButton jButton19;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton4;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JScrollPane jScrollPane7;
    private javax.swing.JScrollPane jScrollPane8DetailInvestor;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTableDetailInvestor;
    private javax.swing.JComboBox mesin;
    private javax.swing.JTable tabel;
    private javax.swing.JTable tabel4;
    private javax.swing.JTable tableBland;
    private javax.swing.JTable tableSKTBorongan;
    private javax.swing.JTable tableSKTList;
    private javax.swing.JTable tablereqpo;
    private javax.swing.JTextField tacc;
    private javax.swing.JTextField tacc1;
    private javax.swing.JTextField taccll;
    private javax.swing.JTextField taccll1;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcode2;
    private javax.swing.JTextField tcode3;
    private javax.swing.JTextField tcode4;
    private javax.swing.JTextField tcode5;
    private javax.swing.JTextField tcustomerk;
    private javax.swing.JTextField tcustomerk1;
    // End of variables declaration//GEN-END:variables
}
