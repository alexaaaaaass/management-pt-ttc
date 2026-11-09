/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Report;

import RPurchasing.*;
import ReportProduction.*;
import Accounting.*;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogReqOrder;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Production.*;
import ReportAccounting.*;
import Tools.ConnectDB;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
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
import javax.swing.JOptionPane;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class RProduct extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    public RProduct(String mode) {
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
        
        GroupBland.add(rb_bland1);
        GroupBland.add(rb_bland2);
        GroupBland.add(rb_bland3);
        
        GroupSKT.add(rb_skt1);
        GroupSKT.add(rb_skt2);
        GroupSKT.add(rb_skt3);
    }
    
    private void po_rawmaterial(){
        String dtran1=rfa.DateString(keyssDef, DataBland1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DataBland2.getDate(),"yyyy-MM-dd");
        
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
                "order by ccode,cnbkt\n" +
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
    
    private void payment(){
        String dtran1=rfa.DateString(keyssDef, DateSkt1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateSkt2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tablepayment.getModel();
        rfa.clear_table(keyssDef, tablepayment);

        sql = "select a.ccode,a.cnbkt,a.cnbkt_PO,b.ServerDate,b.cdesc,b.method,b.curs,b.cbank,b.cbank_an,b.cbank_rekening,b.namnt,b.cacckas,b.caccll\n" +
                "from TranPayment a join TranPayment_detail b on a.ccode=b.ccode and a.cnbkt=b.cnbkt";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),rs.getString("cnbkt_PO"),
                    rfa.DateString(keyssDef, rs.getDate("ServerDate"),"dd/MM/yyyy"),
                    rs.getString("cdesc"),rs.getString("method"),
                    rs.getString("curs"),
                    rs.getString("cbank"),rs.getString("cbank_an"),rs.getString("cbank_rekening"),
                    rs.getDouble("namnt"),
                    rs.getString("cacckas"),rs.getString("caccll"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduct.class.getName()).log(Level.SEVERE, null, ex);
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
    
    private void reqpo(){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
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
            "join MSupplier d on b.kode_supplier=d.kode_supplier";
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
            Logger.getLogger(RProduct.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
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
    private void hutangbiaya(){
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
            Logger.getLogger(RProduct.class.getName()).log(Level.SEVERE, null, ex);
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
    private void hutangbiaya_jangkawaktu(){
        String dtran1=rfa.DateString(keyssDef, DateSkm1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateSkm2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm1.getModel();
        rfa.clear_table(keyssDef, tableskm1);
        
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
"where statusurut = 0 and cdue_date is not null\n" +
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
            Logger.getLogger(RProduct.class.getName()).log(Level.SEVERE, null, ex);
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
    private void rekap_hutang(){
        String dtran1=rfa.DateString(keyssDef, DateSkm1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateSkm2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm2.getModel();
        rfa.clear_table(keyssDef, tableskm2);
        
        sql="select kode_supplier,supplier,sum(total) as total,sum(nominalpay) as nominalpay,status  from (\n" +
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
            "\n" +
            "group by kode_supplier,supplier,status\n" +
            "order by kode_supplier,supplier,status";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("kode_supplier"),
                    rs.getString("supplier"),
                    rs.getDouble("total"),rs.getDouble("nominalpay"),
                    rs.getString("status"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduct.class.getName()).log(Level.SEVERE, null, ex);
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
        jScrollPane4 = new javax.swing.JScrollPane();
        tableSKTBorongan = new javax.swing.JTable();
        jScrollPane5 = new javax.swing.JScrollPane();
        tableSKTList = new javax.swing.JTable();
        jScrollPane6 = new javax.swing.JScrollPane();
        tablereqpo = new javax.swing.JTable();
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
        jButton14 = new javax.swing.JButton();
        two = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        jPanel8 = new javax.swing.JPanel();
        jLabel7 = new javax.swing.JLabel();
        DataBland1 = new com.toedter.calendar.JDateChooser();
        jTextField23 = new javax.swing.JTextField();
        DataBland2 = new com.toedter.calendar.JDateChooser();
        jTextField24 = new javax.swing.JTextField();
        jLabel9 = new javax.swing.JLabel();
        jPanel10 = new javax.swing.JPanel();
        rb_bland1 = new javax.swing.JRadioButton();
        rb_bland3 = new javax.swing.JRadioButton();
        jButton8 = new javax.swing.JButton();
        jButton9 = new javax.swing.JButton();
        rb_bland2 = new javax.swing.JRadioButton();
        jLabel10 = new javax.swing.JLabel();
        jLabel18 = new javax.swing.JLabel();
        tblandkaryawan = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tblandkaryawan1 = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        tableBland = new javax.swing.JTable();
        jPanel1 = new javax.swing.JPanel();
        jScrollPane2 = new javax.swing.JScrollPane();
        tablepayment = new javax.swing.JTable();
        jLabel11 = new javax.swing.JLabel();
        jTextField25 = new javax.swing.JTextField();
        jTextField26 = new javax.swing.JTextField();
        DateSkt2 = new com.toedter.calendar.JDateChooser();
        jLabel12 = new javax.swing.JLabel();
        DateSkt1 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        jPanel11 = new javax.swing.JPanel();
        rb_skt1 = new javax.swing.JRadioButton();
        jButton10 = new javax.swing.JButton();
        jButton11 = new javax.swing.JButton();
        rb_skt2 = new javax.swing.JRadioButton();
        rb_skt3 = new javax.swing.JRadioButton();
        jLabel15 = new javax.swing.JLabel();
        tsktkaryawan = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tsktkaryawan1 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jScrollPane7 = new javax.swing.JScrollPane();
        tableskm = new javax.swing.JTable();
        jLabel14 = new javax.swing.JLabel();
        DateSkm1 = new com.toedter.calendar.JDateChooser();
        jTextField27 = new javax.swing.JTextField();
        jTextField28 = new javax.swing.JTextField();
        jLabel16 = new javax.swing.JLabel();
        DateSkm2 = new com.toedter.calendar.JDateChooser();
        jLabel17 = new javax.swing.JLabel();
        jButton5 = new javax.swing.JButton();
        jLabel20 = new javax.swing.JLabel();
        two1 = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jButton16 = new javax.swing.JButton();
        jPanel6 = new javax.swing.JPanel();
        jScrollPane8 = new javax.swing.JScrollPane();
        tableskm1 = new javax.swing.JTable();
        jLabel21 = new javax.swing.JLabel();
        DateSkm3 = new com.toedter.calendar.JDateChooser();
        jTextField29 = new javax.swing.JTextField();
        jTextField30 = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        DateSkm4 = new com.toedter.calendar.JDateChooser();
        jLabel23 = new javax.swing.JLabel();
        jButton6 = new javax.swing.JButton();
        jLabel24 = new javax.swing.JLabel();
        two2 = new javax.swing.JTextField();
        jButton17 = new javax.swing.JButton();
        jButton18 = new javax.swing.JButton();
        jPanel7 = new javax.swing.JPanel();
        jLabel25 = new javax.swing.JLabel();
        jLabel26 = new javax.swing.JLabel();
        DateSkm5 = new com.toedter.calendar.JDateChooser();
        jTextField31 = new javax.swing.JTextField();
        DateSkm6 = new com.toedter.calendar.JDateChooser();
        jTextField32 = new javax.swing.JTextField();
        jLabel27 = new javax.swing.JLabel();
        jScrollPane9 = new javax.swing.JScrollPane();
        tableskm2 = new javax.swing.JTable();
        jButton19 = new javax.swing.JButton();
        jButton20 = new javax.swing.JButton();

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
        jLabel2.setText("Purchasing Report");
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
        jPanel4.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 150, 120, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(420, 70, 30, -1));
        jPanel4.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 250, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("No Req PO");
        jPanel4.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 120, 20));

        jTabbedPane1.addTab("Request PO", jPanel4);

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
        jPanel8.add(jTextField23, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

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

        jTextField24.setEditable(false);
        jTextField24.setBackground(new java.awt.Color(153, 153, 255));
        jPanel8.add(jTextField24, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 150, -1));

        jLabel9.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel9.setText("s/d");
        jPanel8.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 20, 20));

        jPanel10.setBorder(javax.swing.BorderFactory.createTitledBorder("Option"));
        jPanel10.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        rb_bland1.setText("By Product");
        jPanel10.add(rb_bland1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 200, -1));

        rb_bland3.setText("By Tanggal");
        jPanel10.add(rb_bland3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 90, 200, -1));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton8.setText("View");
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel10.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 30, 120, -1));

        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton9.setText("Excel");
        jPanel10.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 60, 120, -1));

        rb_bland2.setText("By Operator");
        jPanel10.add(rb_bland2, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 200, -1));

        jPanel8.add(jPanel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 140, 400, 130));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Operator");
        jPanel8.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 100, 100, 20));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Periode");
        jPanel8.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 100, 20));

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
        jPanel8.add(tblandkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 100, 90, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 100, 30, -1));

        tblandkaryawan1.setEditable(false);
        tblandkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        tblandkaryawan1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tblandkaryawan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 100, 200, -1));

        tableBland.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "cnbkt", "cnbkt_pr", "dtran", "cdue_date", "cgroup", "cproduct", "nqty", "nprice", "ndisc", "ntotal", "nunit", "kode_supplier", "supplier", "nqtyin", "nunitin"
            }
        ));
        jScrollPane1.setViewportView(tableBland);

        jPanel8.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(42, 287, 670, 110));

        jTabbedPane1.addTab("Purchase Order", jPanel8);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        tablepayment.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "cnbkt_po", "dtran", "cdesc", "method", "curs", "cbank", "cbank_an", "cbank_rekening", "namnt", "cacckas", "caccll"
            }
        ));
        jScrollPane2.setViewportView(tablepayment);

        jPanel1.add(jScrollPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(22, 287, 600, 140));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Kode Log");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 72, 20));
        jPanel1.add(jTextField25, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        jTextField26.setEditable(false);
        jTextField26.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(jTextField26, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 150, -1));

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
        jPanel1.add(DateSkt2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 70, 120, -1));

        jLabel12.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel12.setText("s/d");
        jPanel1.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 20, 20));

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
        jPanel1.add(DateSkt1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 120, -1));

        jLabel13.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel13.setText("Borongan");
        jPanel1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 100, 100, 20));

        jPanel11.setBorder(javax.swing.BorderFactory.createTitledBorder("Option"));
        jPanel11.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        rb_skt1.setText("Rekap by Product");
        jPanel11.add(rb_skt1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 200, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton10.setText("View");
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel11.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 30, 120, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton11.setText("Excel");
        jPanel11.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 60, 120, -1));

        rb_skt2.setText("By Borongan");
        jPanel11.add(rb_skt2, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 200, -1));

        rb_skt3.setText("By Tanggal");
        jPanel11.add(rb_skt3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 90, 200, -1));

        jPanel1.add(jPanel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 140, 410, 130));

        jLabel15.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel15.setText("Periode");
        jPanel1.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 100, 20));

        tsktkaryawan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tsktkaryawanFocusLost(evt);
            }
        });
        tsktkaryawan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tsktkaryawanKeyPressed(evt);
            }
        });
        jPanel1.add(tsktkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 100, 90, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 100, 30, -1));

        tsktkaryawan1.setEditable(false);
        tsktkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        tsktkaryawan1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tsktkaryawan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 100, 200, -1));

        jTabbedPane1.addTab("Payment", jPanel1);

        jPanel5.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

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

        jPanel5.add(jScrollPane7, new org.netbeans.lib.awtextra.AbsoluteConstraints(32, 217, 680, 180));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Kode Log");
        jPanel5.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 72, 20));

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
        jPanel5.add(DateSkm1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 120, -1));
        jPanel5.add(jTextField27, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        jTextField28.setEditable(false);
        jTextField28.setBackground(new java.awt.Color(153, 153, 255));
        jPanel5.add(jTextField28, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 150, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("s/d");
        jPanel5.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 20, 20));

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
        jPanel5.add(DateSkm2, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 70, 120, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Periode");
        jPanel5.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 100, 20));

        jButton5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton5.setText("View");
        jButton5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton5ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton5, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 140, 120, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("No Produksi /WO");
        jPanel5.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 100, 120, 20));
        jPanel5.add(two1, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 100, 250, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(420, 100, 30, -1));

        jButton16.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton16.setText("Excel");
        jPanel5.add(jButton16, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 170, 120, -1));

        jTabbedPane1.addTab("Hutang Biaya", jPanel5);

        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

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

        jPanel6.add(jScrollPane8, new org.netbeans.lib.awtextra.AbsoluteConstraints(32, 217, 680, 180));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Kode Log");
        jPanel6.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 72, 20));

        DateSkm3.setDateFormatString("dd/MM/yyyy");
        DateSkm3.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkm3PropertyChange(evt);
            }
        });
        DateSkm3.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkm3KeyPressed(evt);
            }
        });
        jPanel6.add(DateSkm3, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 120, -1));
        jPanel6.add(jTextField29, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        jTextField30.setEditable(false);
        jTextField30.setBackground(new java.awt.Color(153, 153, 255));
        jPanel6.add(jTextField30, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 150, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("s/d");
        jPanel6.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 20, 20));

        DateSkm4.setDateFormatString("dd/MM/yyyy");
        DateSkm4.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkm4PropertyChange(evt);
            }
        });
        DateSkm4.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkm4KeyPressed(evt);
            }
        });
        jPanel6.add(DateSkm4, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 70, 120, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Periode");
        jPanel6.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 100, 20));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton6.setText("View");
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 140, 120, -1));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("No Produksi /WO");
        jPanel6.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 100, 120, 20));
        jPanel6.add(two2, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 100, 250, -1));

        jButton17.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton17.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton17ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton17, new org.netbeans.lib.awtextra.AbsoluteConstraints(420, 100, 30, -1));

        jButton18.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton18.setText("Excel");
        jPanel6.add(jButton18, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 170, 120, -1));

        jTabbedPane1.addTab("Jangka Waktu Hutang", jPanel6);

        jPanel7.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Periode");
        jPanel7.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 70, 100, 20));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Kode Log");
        jPanel7.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 72, 20));

        DateSkm5.setDateFormatString("dd/MM/yyyy");
        DateSkm5.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkm5PropertyChange(evt);
            }
        });
        DateSkm5.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkm5KeyPressed(evt);
            }
        });
        jPanel7.add(DateSkm5, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 120, -1));
        jPanel7.add(jTextField31, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 120, -1));

        DateSkm6.setDateFormatString("dd/MM/yyyy");
        DateSkm6.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateSkm6PropertyChange(evt);
            }
        });
        DateSkm6.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateSkm6KeyPressed(evt);
            }
        });
        jPanel7.add(DateSkm6, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 70, 120, -1));

        jTextField32.setEditable(false);
        jTextField32.setBackground(new java.awt.Color(153, 153, 255));
        jPanel7.add(jTextField32, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 150, -1));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("s/d");
        jPanel7.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 20, 20));

        tableskm2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null}
            },
            new String [] {
                "kode_supplier", "supplier", "total", "nominalpay", "status"
            }
        ));
        jScrollPane9.setViewportView(tableskm2);

        jPanel7.add(jScrollPane9, new org.netbeans.lib.awtextra.AbsoluteConstraints(32, 217, 680, 180));

        jButton19.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton19.setText("View");
        jButton19.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton19ActionPerformed(evt);
            }
        });
        jPanel7.add(jButton19, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 140, 120, -1));

        jButton20.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton20.setText("Excel");
        jPanel7.add(jButton20, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 170, 120, -1));

        jTabbedPane1.addTab("Rekap Hutang", jPanel7);

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
                        .addGap(0, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void DateReq2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateReq2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq2PropertyChange

    private void DateReq2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateReq2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq2KeyPressed

    private void DateReq1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateReq1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq1PropertyChange

    private void DateReq1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateReq1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq1KeyPressed

    private void DataBland1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DataBland1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland1PropertyChange

    private void DataBland1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DataBland1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland1KeyPressed

    private void DataBland2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DataBland2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland2PropertyChange

    private void DataBland2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DataBland2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DataBland2KeyPressed

    private void DateSkt2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkt2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt2PropertyChange

    private void DateSkt2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkt2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt2KeyPressed

    private void DateSkt1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkt1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt1PropertyChange

    private void DateSkt1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkt1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkt1KeyPressed

    private void DateSkm1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm1PropertyChange

    private void DateSkm1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm1KeyPressed

    private void DateSkm2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm2PropertyChange

    private void DateSkm2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm2KeyPressed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        if(con.akses("REPORT PRODUCTION BLAND", rfa.PRINT)) po_rawmaterial();
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
        if(con.akses("REPORT PRODUCTION SKT", rfa.PRINT)) payment();
    }//GEN-LAST:event_jButton10ActionPerformed

    private void tsktkaryawanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tsktkaryawanFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tsktkaryawanFocusLost

    private void tsktkaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tsktkaryawanKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tsktkaryawanKeyPressed

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tsktkaryawan.setText(a.Kode);
            tsktkaryawan1.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogReqOrder a = new DialogReqOrder();
        a.setVisible(true);
        if(a.status) {
            two.setText(a.kode);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        if(con.akses("REPORT PURCHASE REQUEST", rfa.PRINT)) reqpo();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jButton15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton15ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jButton15ActionPerformed

    private void jButton5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton5ActionPerformed
        if(con.akses("REPORT PRODUCTION SKM", rfa.PRINT)) hutangbiaya();
    }//GEN-LAST:event_jButton5ActionPerformed

    private void DateSkm3PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm3PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm3PropertyChange

    private void DateSkm3KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm3KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm3KeyPressed

    private void DateSkm4PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm4PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm4PropertyChange

    private void DateSkm4KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm4KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm4KeyPressed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        if(con.akses("REPORT PRODUCTION SKM", rfa.PRINT)) hutangbiaya_jangkawaktu();
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jButton17ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton17ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jButton17ActionPerformed

    private void DateSkm5PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm5PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm5PropertyChange

    private void DateSkm5KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm5KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm5KeyPressed

    private void DateSkm6PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateSkm6PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm6PropertyChange

    private void DateSkm6KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateSkm6KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateSkm6KeyPressed

    private void jButton19ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton19ActionPerformed
        if(con.akses("REPORT PRODUCTION SKM", rfa.PRINT)) rekap_hutang();
    }//GEN-LAST:event_jButton19ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private com.toedter.calendar.JDateChooser DataBland1;
    private com.toedter.calendar.JDateChooser DataBland2;
    private com.toedter.calendar.JDateChooser DateReq1;
    private com.toedter.calendar.JDateChooser DateReq2;
    private com.toedter.calendar.JDateChooser DateSkm1;
    private com.toedter.calendar.JDateChooser DateSkm2;
    private com.toedter.calendar.JDateChooser DateSkm3;
    private com.toedter.calendar.JDateChooser DateSkm4;
    private com.toedter.calendar.JDateChooser DateSkm5;
    private com.toedter.calendar.JDateChooser DateSkm6;
    private com.toedter.calendar.JDateChooser DateSkt1;
    private com.toedter.calendar.JDateChooser DateSkt2;
    private javax.swing.ButtonGroup GroupBland;
    private javax.swing.ButtonGroup GroupMaterialProduksi;
    private javax.swing.ButtonGroup GroupSKM;
    private javax.swing.ButtonGroup GroupSKT;
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
    private javax.swing.JButton jButton20;
    private javax.swing.JButton jButton5;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel26;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel10;
    private javax.swing.JPanel jPanel11;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel7;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JScrollPane jScrollPane7;
    private javax.swing.JScrollPane jScrollPane8;
    private javax.swing.JScrollPane jScrollPane9;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTextField jTextField23;
    private javax.swing.JTextField jTextField24;
    private javax.swing.JTextField jTextField25;
    private javax.swing.JTextField jTextField26;
    private javax.swing.JTextField jTextField27;
    private javax.swing.JTextField jTextField28;
    private javax.swing.JTextField jTextField29;
    private javax.swing.JTextField jTextField30;
    private javax.swing.JTextField jTextField31;
    private javax.swing.JTextField jTextField32;
    private javax.swing.JRadioButton rb_bland1;
    private javax.swing.JRadioButton rb_bland2;
    private javax.swing.JRadioButton rb_bland3;
    private javax.swing.JRadioButton rb_skt1;
    private javax.swing.JRadioButton rb_skt2;
    private javax.swing.JRadioButton rb_skt3;
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
    private javax.swing.JTextField tsktkaryawan;
    private javax.swing.JTextField tsktkaryawan1;
    private javax.swing.JTextField two;
    private javax.swing.JTextField two1;
    private javax.swing.JTextField two2;
    // End of variables declaration//GEN-END:variables
}
