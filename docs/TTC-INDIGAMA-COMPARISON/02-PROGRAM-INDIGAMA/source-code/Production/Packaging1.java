/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Production;

import Accounting.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogSatuan;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Timer;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JOptionPane;
import javax.swing.JTextField;
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
public class Packaging1 extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="PRODUKSI BOX PACKAGING";
    String idKas="BX",cgroup="",cproduct="";
    ArrayList<String> deleteRow;
    Double totalPackInput=0.0,packLinting=0.0;
    String satuanterkecil="";
    public Packaging1(String mode) {
        initComponents();
        if("IN".equals(mode)) {
            jLabel2.setText("Transaksi Kas Masuk");
        } else if("OUT".equals(mode)) {
            jLabel2.setText("Transaksi Kas Keluar");
        }
        
        push_button();
        deff();
        loadData();
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
        btDelete.setVisible(false);
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        
        Calendar calendar = Calendar.getInstance();
        Date today = calendar.getTime();
        calendar.add(Calendar.DAY_OF_YEAR, -7);
        Date addDays = calendar.getTime();
        
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(addDays);
        
        
        tcodes.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        LastCode();
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tsatuan,tsatuanhigh,tsatuanlow,tket,two,tpo,tcustomer,tproduk,tnote,
                tbox,tikat);
        tpcs.setText("");
    }
    private void hitungPcs(){
        try{
            Double ikat=0.0,pcskonversi=0.0,total=0.0,box=0.0,tall=0.0;
            if(!tikat.getText().isEmpty()) {
                ikat = Double.valueOf(tikat.getText());
            }
            if(!tsatuanlow.getText().isEmpty()) {
                pcskonversi = Double.valueOf(tsatuanlow.getText());
            }
            if(!tbox.getText().isEmpty()) {
                box = Double.valueOf(tbox.getText());
            }
            
            total = ikat*pcskonversi;
            
            tpcs.setText(rfa.formatDecimal3.format(total));
            
            
            
        }catch(Exception e){
            
        }
    }
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,left(cnbkt,3))) as lastno "
                + " from PBoxPacking "
                + " where  ccode='"+tcode.getText()+"' "
                + " and month(dtran)= '"+rfa.DateString(keyssDef, jDateTran.getDate(),"MM")+"' ");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("/BOX-IK01/"+con.romawi(rfa.DateString(keyssDef, jDateTran.getDate(),"MM"))+"/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yy"));
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql="select a.ccode,a.cnbkt,a.dtran,a.cworkorder,bb.cnameproduct,c.cnbkt as pocustomer,d.cname as customer,a.nqty_box,a.nqty,a.csatuan,a.nqty_small*nqty_box as totalpcs,a.cnote,e.cname as operator \n" +
                "from PBoxPacking a\n" +
                "join PWorkOrder b on a.cworkorder=b.cnbkt and a.ccode=b.ccode\n" +
                "join MProduk bb on b.cgroup=bb.cgroup and b.cproduct=bb.cproduct\n" +
                "left join PCustomerPO c on b.cnbkt_order=c.cnbkt and b.ccode=c.ccode\n" +
                "left join MCustomer d on c.kode_customer=d.kode_customer\n" +
                "left join MKaryawan e on a.cnik=e.nik\n" +
                "where a.ccode='"+tcodes.getText()+"' and (a.dtran>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"') ";
        if(!tbuktis.getText().isEmpty()) {
            sql+=" and a.cnbkt like '%"+tbuktis.getText()+"%'";
        }
        if(!tkets.getText().isEmpty()) {
            sql+=" and a.cnote like '%"+tkets.getText()+"%'";
        }
        if(!tcustomers.getText().isEmpty()) {
            sql+=" and d.cname like '%"+tcustomers.getText()+"%'";
        }
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cworkorder"), 
                    rs.getString("cnameproduct"),
                    rs.getString("pocustomer"),
                    rs.getString("operator"),
                    rs.getString("customer"),
                    rs.getDouble("nqty_box"),rs.getDouble("nqty"),
                    rs.getString("csatuan"),
                    rs.getDouble("totalpcs"),
                    rs.getString("cnote"),
                    
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();
//        con.resizeColumnWidth(jTable2);
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
     
    }
    private void loadEdit(){
        String wo="";
        btSave.setText("Update");
        btDelete.setVisible(true);
        sql="select a.*,b.cname as karyawan from PBoxPacking a left join mkaryawan b on a.cnik=b.nik where a.ccode='"+tcodes.getText()+"' and a.cnbkt='"+cnbkt+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                jDateTran.setDate(rs.getDate("dtran"));
                tbukti1.setText(rs.getString("cnbkt").substring(0,3));
                tbukti.setText(rs.getString("cnbkt").substring(3, rs.getString("cnbkt").length()));
                two.setText(rs.getString("cworkorder"));
                tket.setText(rs.getString("cnote"));
                tkaryawankode.setText(rs.getString("cnik"));
                tkaryawan.setText(rs.getString("karyawan"));
                tsatuan.setText(rs.getString("csatuan"));
                tsatuanlow.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_small")/rs.getDouble("nqty")));
                tbox.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_box")));
                tikat.setText(rfa.formatDecimal3.format(rs.getDouble("nqty")));
                wo=rs.getString("cworkorder");
            }
        } catch (SQLException ex) {
            Logger.getLogger(Packaging1.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="select a.id,a.ccode,a.cnbkt,a.dtran,a.deadline,a.cnbkt_order,a.cgroup,a.cproduct,c.cnameProduct,a.nqty,a.nqty_add,a.nqty_total,a.cket,\n" +
            "b.kode_customer,d.cname as customer,b.dtran as dtranorder,c.cunit as satuanproduk\n" +
            "from PWorkOrder a left join PCustomerPO b on a.ccode=b.ccode and a.cnbkt_order=b.cnbkt\n" +
            "join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
            "left join MCustomer d on b.kode_customer=d.kode_customer\n" +
            "where a.ccode='"+tcodes.getText()+"'  "+
            "and a.cnbkt='"+wo+"'"+
            "";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                tpo.setText(rs.getString("cnbkt_order"));
                tcustomer.setText(rs.getString("customer"));
                tproduk.setText(rs.getString("cnameProduct"));
                tnote.setText(rs.getString("cket"));
                satuanterkecil = rs.getString("satuanproduk");
                System.out.println(satuanterkecil);
                tsatuankecil.setText(rs.getString("satuanproduk"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(Packaging1.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="select a.cname,a.small_value,b.cname as satuansmall From MSatuan a join MSatuan b on a.id_turunan=b.id\n" +
                "where a.cname='"+tsatuan.getText()+"' and b.cname='"+satuanterkecil+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                tsatuanlow.setText(rfa.formatDecimal3.format(rs.getDouble("small_value")));
            }
        } catch (SQLException ex) {
            Logger.getLogger(Packaging1.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        hitungPcs();
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
        tbox.requestFocus();
    }
    private void print(){
        String pengaju="";
        Double total =0.0;
        sql="select aa.id,a.ccode,a.cnbkt,a.dtran,a.cworkorder,c.cnbkt as pocustomer,d.cname as customer,a.nqty_box,a.nqty as nqtyikat,a.csatuan,a.nqty_small*nqty_box as totalpcs,a.cnote,\n" +
                "aa.cbox_no,aa.nqty,aa.csatuan,aa.nqty_small,aa.csatuan_small,e.cnameProduct,e.cunit as satuanproduk\n" +
                "from PBoxPacking a join PBoxPackingD aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "join PWorkOrder b on a.cworkorder=b.cnbkt and a.ccode=b.ccode\n" +
                "left join PCustomerPO c on b.cnbkt_order=c.cnbkt and b.ccode=c.ccode\n" +
                "left join MCustomer d on c.kode_customer=d.kode_customer\n" +
                "left join MProduk e on b.cproduct=e.cproduct and b.cgroup=e.cgroup\n" +
                "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+cnbkt_print+"' \n"+
                "order by aa.id asc,aa.cbox_no asc";
        System.out.println(sql);
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),rs.getString("cnbkt")+"~"+rs.getString("cbox_no"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cworkorder"),rs.getString("cnameProduct"),
                    rs.getDouble("nqty"),rs.getDouble("nqty_small"),
                    rs.getString("cbox_no"),
                    rs.getString("satuanproduk"),
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranBoxPackaging.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "");
            param.put("pengaju", pengaju); 
            param.put("terbilang", rfa.Pembilang(total).toUpperCase()+" RUPIAH" );
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void save_detail(){
        int totalboxsave=0;
        sql="select id,ccode,cnbkt,dtran,nqty_box,cworkorder\n" +
            "from PBoxPacking  \n" +
            "where ccode='"+tcode.getText()+"' and cworkorder='"+two.getText()+"'\n" +
            "order by id,ccode,dtran,cnbkt";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                
                System.out.println(rs.getString("cnbkt")+"-"+(tbukti1.getText()+tbukti.getText()));
                if(rs.getString("cnbkt").equalsIgnoreCase(tbukti1.getText()+tbukti.getText())) {
                    break;
                }
                totalboxsave=totalboxsave+rs.getInt("nqty_box");
                System.out.println(totalboxsave+"-"+rs.getInt("nqty_box"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(Packaging1.class.getName()).log(Level.SEVERE, null, ex);
        }
        totalboxsave=totalboxsave+1;
        int tboxx=Integer.valueOf(tbox.getText());
        con.queryUpdate("delete from PBoxPackingD where ccode='"+tcode.getText()+"' and cnbkt='"+tbukti1.getText()+tbukti.getText()+"'");
        for(int i=0;i<tboxx; i++){
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
            listObj.put("ccode",tcode.getText());
            listObj.put("cbox_no",rfa.NumberBukti(MenuAdmin.keyssDef,4, totalboxsave++));
            listObj.put("nqty",tikat.getText());
            listObj.put("csatuan",tsatuan.getText());
            listObj.put("nqty_small",tpcs.getText());
            listObj.put("csatuan_small",satuanterkecil);
            sql=con.insertValue("PBoxPackingD", listObj);
            con.queryUpdate(sql);
        }
    }
    private void save(){
        cnbkt_print = tbukti1.getText()+tbukti.getText();
        String due_date = "NULL";
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
        listObj.put("cworkorder",two.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cyear",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy"));
        listObj.put("cnote",tket.getText());
        listObj.put("nqty_box",tbox.getText());
        listObj.put("nqty",tikat.getText());
        listObj.put("nqty_small",tpcs.getText());
        listObj.put("csatuan",tsatuan.getText());
        listObj.put("csatuan_small",satuanterkecil);
        listObj.put("cnik",tkaryawankode.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("PBoxPacking", listObj);
        if(con.queryUpdate(sql)>0) {
            save_detail();
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            if(pil == 0) btPrint.doClick();
            clear();
            deff();
            LastCode();
            loadData();
        } else {
            JOptionPane.showMessageDialog(this,"Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void update(){
        cnbkt_print = tbukti1.getText()+tbukti.getText();
        String due_date = "NULL";
        HashMap<String,String> listObj = new HashMap<String,String>();     
        listObj.put("cworkorder",two.getText());
        listObj.put("dtran",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cyear",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy"));
        listObj.put("cnote",tket.getText());
        listObj.put("nqty_box",tbox.getText());
        listObj.put("nqty",tikat.getText());
        listObj.put("nqty_small",tpcs.getText());
        listObj.put("csatuan",tsatuan.getText());
        listObj.put("csatuan_small",satuanterkecil);
        listObj.put("cnik",tkaryawankode.getText());
        listObj.put("cusername",Login.user);
        sql=con.updateValue("PBoxPacking", listObj);
        sql+=" where ccode='"+tcode.getText()+"' and cnbkt='"+tbukti1.getText()+tbukti.getText()+"'";
        System.out.println(sql);
        if(con.queryUpdate(sql)>0) {
            save_detail();
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            if(pil == 0) btPrint.doClick();
            clear();
            deff();
            LastCode();
            loadData();
        } else {
            JOptionPane.showMessageDialog(this,"Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void delete(){
        con.queryUpdate("delete from PBoxPackingD where ccode='"+tcode.getText()+"' and cnbkt='"+tbukti1.getText()+tbukti.getText()+"'");
        con.queryUpdate("delete from PBoxPacking where ccode='"+tcode.getText()+"' and cnbkt='"+tbukti1.getText()+tbukti.getText()+"'");
        JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
        clear();
        deff();
        LastCode();
        loadData();    
    }
    /**
     * This method is called from within the constructor to initialize the form.
     * WARNING: Do NOT modify this code. The content of this method is always
     * regenerated by the Form Editor.
     */
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        tsatuanlow = new javax.swing.JTextField();
        tsatuanhigh = new javax.swing.JTextField();
        jScrollPane5print = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jLabel26 = new javax.swing.JLabel();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jLabel12 = new javax.swing.JLabel();
        tbuktis = new javax.swing.JTextField();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        jDate1 = new com.toedter.calendar.JDateChooser();
        tkets = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jLabel16 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jLabel17 = new javax.swing.JLabel();
        tcustomers = new javax.swing.JTextField();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        btPrint = new javax.swing.JButton();
        jLabel11 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        jButton14 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        tbox = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        tikat = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        tsatuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        jLabel10 = new javax.swing.JLabel();
        jLabel21 = new javax.swing.JLabel();
        jLabel22 = new javax.swing.JLabel();
        tproduk = new javax.swing.JTextField();
        tcustomer = new javax.swing.JTextField();
        tpo = new javax.swing.JTextField();
        two = new javax.swing.JTextField();
        jButton9 = new javax.swing.JButton();
        tnote = new javax.swing.JTextField();
        jLabel37 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tsatuankecil = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        jLabel18 = new javax.swing.JLabel();
        tkaryawan = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        tkaryawankode = new javax.swing.JTextField();
        tpcs = new javax.swing.JLabel();
        jLabel24 = new javax.swing.JLabel();
        jLabel27 = new javax.swing.JLabel();
        jButton1 = new javax.swing.JButton();

        tsatuanlow.setText("jTextField1");

        tsatuanhigh.setText("jTextField1");

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "qr", "dtran", "wo", "produk", "cikat", "cpcs", "cbox", "satuan"
            }
        ));
        jScrollPane5print.setViewportView(jtable_print);

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Pcs");
        jLabel26.setToolTipText("");

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
        jLabel2.setText("Packaging");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jLabel12.setText("No Bukti");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        tbuktis.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbuktisKeyPressed(evt);
            }
        });
        jPanel6.add(tbuktis, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 140, -1));

        jDate2.setDateFormatString("dd/MM/yyyy");
        jDate2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDate2PropertyChange(evt);
            }
        });
        jDate2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDate2KeyPressed(evt);
            }
        });
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(530, 30, 150, -1));

        jLabel13.setText("Customer");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));

        jDate1.setDateFormatString("dd/MM/yyyy");
        jDate1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDate1PropertyChange(evt);
            }
        });
        jDate1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDate1KeyPressed(evt);
            }
        });
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 30, 150, -1));

        tkets.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketsKeyPressed(evt);
            }
        });
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 30, 280, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(990, 60, 90, -1));

        jLabel15.setText("Keterangan");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, -1, 20));

        jLabel16.setText("Gudang");
        jPanel6.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        tcodes.setEditable(false);
        tcodes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodesFocusLost(evt);
            }
        });
        jPanel6.add(tcodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 110, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 30, 30, -1));

        jLabel17.setText("Periode ");
        jPanel6.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

        tcustomers.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tcustomersKeyPressed(evt);
            }
        });
        jPanel6.add(tcustomers, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 330, -1));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Gudang", "Bukti", "Tgl Packing", "No SPK", "Produk", "No PO", "Operator", "Customer", "Jml Box", "Jml Ikat (1 Box)", "Satuan Ikat", "Total Keseluruhan (PCS)", "Note"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable2.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable2.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable2MouseClicked(evt);
            }
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                jTable2MouseEntered(evt);
            }
        });
        jScrollPane2.setViewportView(jTable2);

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField11.setText("Double Click to Edit");

        jTextField12.setEditable(false);
        jTextField12.setBackground(new java.awt.Color(204, 204, 255));
        jTextField12.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField12.setText("Total Data : 100");

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1100, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 276, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 50, 130, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Nomor Box");
        jLabel11.setToolTipText("");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 130, 20));

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 110, 20));
        jPanel1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 90, -1));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("Tgl. Packing");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 20, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 20, 200, -1));

        tbox.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tboxKeyReleased(evt);
            }
        });
        jPanel1.add(tbox, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 290, 150, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Jml Box");
        jLabel19.setToolTipText("");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 290, 130, 20));

        tikat.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tikatKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tikatKeyTyped(evt);
            }
        });
        jPanel1.add(tikat, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 320, 150, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Note");
        jPanel1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 380, 90, 20));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 350, 150, -1));

        btsatuan.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btsatuan.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btsatuanActionPerformed(evt);
            }
        });
        btsatuan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btsatuanKeyPressed(evt);
            }
        });
        jPanel1.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 350, 30, -1));

        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        tbukti.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbuktiFocusLost(evt);
            }
        });
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 80, 230, -1));

        tbukti1.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbukti1FocusLost(evt);
            }
        });
        tbukti1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbukti1KeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tbukti1KeyTyped(evt);
            }
        });
        jPanel1.add(tbukti1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 60, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("No SPK");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 130, 20));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("No PO Customer");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Customer");
        jPanel1.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 110, 20));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Produk");
        jPanel1.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, 110, 20));

        tproduk.setEditable(false);
        tproduk.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 290, -1));

        tcustomer.setEditable(false);
        tcustomer.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 290, -1));

        tpo.setEditable(false);
        tpo.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tpo, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 290, -1));

        two.setEditable(false);
        jPanel1.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 260, -1));

        jButton9.setText("...");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 110, 30, -1));

        tnote.setEditable(false);
        tnote.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tnote, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 230, 290, -1));

        jLabel37.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel37.setText("Note PO");
        jPanel1.add(jLabel37, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 230, 110, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 410, 97, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 410, 97, 30));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 410, 97, -1));

        jDateTran.setDateFormatString("dd/MM/yyyy");
        jDateTran.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateTranPropertyChange(evt);
            }
        });
        jDateTran.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateTranKeyPressed(evt);
            }
        });
        jPanel1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 120, -1));

        tsatuankecil.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        tsatuankecil.setText("Satuan Kecil");
        jPanel1.add(tsatuankecil, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 320, 90, 20));

        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        jPanel1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 380, 330, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Operator");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 260, 130, 20));

        tkaryawan.setEditable(false);
        jPanel1.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 260, 260, -1));

        jButton11.setText("...");
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 260, 30, -1));
        jPanel1.add(tkaryawankode, new org.netbeans.lib.awtextra.AbsoluteConstraints(440, 260, 5, -1));

        tpcs.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        tpcs.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        tpcs.setText("0");
        tpcs.setToolTipText("");
        jPanel1.add(tpcs, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 320, 100, 20));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Satuan Ikat");
        jPanel1.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 350, 90, 20));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("Jml PCS dlm 1 Box");
        jLabel27.setToolTipText("");
        jPanel1.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 320, 130, 20));

        jButton1.setText("Reload");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 80, 80, -1));

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 1000, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );

        jTabbedPane1.addTab("Add/Edit", jPanel5);

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

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        
        sql="select * from mproduk where cgroup='"+cgroup+"' and cproduct='"+cproduct+"' ";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                satuanterkecil=rs.getString("cunit");
                tsatuankecil.setText(satuanterkecil);
            }
        } catch (SQLException ex) {
            Logger.getLogger(Packaging1.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        DialogSatuan a = new DialogSatuan(satuanterkecil);
        a.setVisible(true);
        if(a.status){
            tsatuan.setText(a.unit);
            tsatuanhigh.setText(rfa.formatDecimal3.format(a.big_value));
            rs = con.queryResult("select a.*,\n" +
                "isnull(a.small_value,1) as n1,isnull(b.small_value,1) as n2,isnull(c.small_value,1) as n3,isnull(d.small_value,1) as n4,isnull(e.small_value,1) as n5,isnull(f.small_value,1) as n6,isnull(g.small_value,1) as n7,\n" +
                "a.cname as t1,b.cname as t2,c.cname as t3,d.cname as t4,e.cname as t5,f.cname as t6,g.cname as t7\n" +
                "from MSatuan a\n" +
                "left join MSatuan b on a.id_turunan=b.id\n" +
                "left join MSatuan c on b.id_turunan=c.id\n" +
                "left join MSatuan d on c.id_turunan=d.id\n" +
                "left join MSatuan e on d.id_turunan=e.id\n" +
                "left join MSatuan f on e.id_turunan=f.id\n" +
                "left join MSatuan g on f.id_turunan=g.id\n" +
                "where a.cname = '"+a.unit+"'");
            Double nilaisatuan=0.0;
            try {
                while(rs.next()){
                    if(satuanterkecil.equals(rs.getString("t1")))  nilaisatuan=1.0;
                    if(satuanterkecil.equals(rs.getString("t2")))  nilaisatuan=rs.getDouble("n1");
                    if(satuanterkecil.equals(rs.getString("t3")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2");
                    if(satuanterkecil.equals(rs.getString("t4")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3");
                    if(satuanterkecil.equals(rs.getString("t5")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3")*rs.getDouble("n4");
                    if(satuanterkecil.equals(rs.getString("t6")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3")*rs.getDouble("n4")*rs.getDouble("n5");
                    if(satuanterkecil.equals(rs.getString("t7")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3")*rs.getDouble("n4")*rs.getDouble("n5")*rs.getDouble("n6");
                }
            } catch (SQLException ex) {  }
            System.out.println(nilaisatuan);
            tsatuanlow.setText(rfa.formatDecimal3.format(nilaisatuan));
            hitungPcs();
        }
    }//GEN-LAST:event_btsatuanActionPerformed

    private void btsatuanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuanKeyPressed
        if(evt.getKeyCode()==10) btsatuan.doClick();
    }//GEN-LAST:event_btsatuanKeyPressed

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 3, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed

    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        DialogPublicTP.DialogWO a = new DialogWO();
        a.setVisible(true);
        if(a.status){
            two.setText(a.cnbkt);
            tpo.setText(a.cnbktpo);
            tcustomer.setText(a.customer);
            tproduk.setText(a.produk);
            tnote.setText(a.cnote);
            cgroup = a.cgroup;
            cproduct=a.cproduct;
            
            sql="select * from mproduk where cgroup='"+cgroup+"' and cproduct='"+cproduct+"' ";
            System.out.println(sql);
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    satuanterkecil=rs.getString("cunit");
                    tsatuan.setText(satuanterkecil);
                    tsatuankecil.setText(satuanterkecil);
                    tsatuanlow.setText("1");
                }
            } catch (SQLException ex) {
                Logger.getLogger(Packaging1.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
    }//GEN-LAST:event_jButton9ActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    //LastCode();
                    save();
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        clear();
        deff();
        LastCode();
        loadData();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode() == 10) loadData();
    }//GEN-LAST:event_tbuktisKeyPressed

    private void jDate2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2PropertyChange

    private void jDate2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2KeyPressed

    private void jDate1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1PropertyChange

    private void jDate1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1KeyPressed

    private void tketsKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketsKeyPressed
        if(evt.getKeyCode() == 10) loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_tketsKeyPressed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void tcodesFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodesFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodesFocusLost

    private void jButton15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton15ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcodes.setText(a.ccode);
        }
    }//GEN-LAST:event_jButton15ActionPerformed

    private void tcustomersKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tcustomersKeyPressed
        if(evt.getKeyCode() == 10) loadData();
    }//GEN-LAST:event_tcustomersKeyPressed

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tketFocusLost

    private void tbuktiFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbuktiFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tbuktiFocusLost

    private void tikatKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tikatKeyTyped
       
    }//GEN-LAST:event_tikatKeyTyped

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 1).toString();
            cnbkt_print = cnbkt;
            tcode.setText(tcodes.getText());
            loadEdit();
            
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void tikatKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tikatKeyReleased
        hitungPcs();
    }//GEN-LAST:event_tikatKeyReleased

    private void tboxKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tboxKeyReleased
        hitungPcs();
    }//GEN-LAST:event_tboxKeyReleased

    private void jTable2MouseEntered(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseEntered
        // TODO add your handling code here:
    }//GEN-LAST:event_jTable2MouseEntered

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawankode.setText(a.Kode);
            tkaryawan.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jButton1ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton9;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
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
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel26;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel37;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane5print;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTextField tbox;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tcustomer;
    private javax.swing.JTextField tcustomers;
    private javax.swing.JTextField tikat;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawankode;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tnote;
    private javax.swing.JLabel tpcs;
    private javax.swing.JTextField tpo;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JLabel tsatuankecil;
    private javax.swing.JTextField tsatuanlow;
    private javax.swing.JTextField two;
    // End of variables declaration//GEN-END:variables
}
