/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package RawMaterial;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogPurchaseOrder;
import DialogPublicTP.DialogSatuan;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Purchase.PaymentPurchase;
import Purchase.PurchaseOrder;
import Purchase.PurchaseRequest;
import Tools.ConnectDB;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Timer;
import java.util.TimerTask;
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
public class StockOpname extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="STOCK OPNAME";
    String idKas="R1",satuanterkecil="";
    ArrayList<String> deleteRow;
    ArrayList<String> idSkat;
    public StockOpname(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        clear();
        deff();
        loadData();
        jTabbedPane1.remove(0);
    }
    
     private void push_button(){
        InputMap im = this.getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = this.getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F12, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                 btSave.doClick();
            }
        });
    }
    private void deff(){
        btDelete.setVisible(false);
        btSave.setText("Save");
        //jTabbedPane1.setTitleAt(0, "StockOpname");
        
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
       
        
        
        loadSkat();
        LastCode();
        
    }
    private void loadSkat(){
        idSkat = new ArrayList<>();
        idSkat.clear();
        cbSkat.removeAll();
        cbSkat.removeAllItems();
        
        if(!tcode.getText().equalsIgnoreCase("PMK02")) {
            idSkat.add("");
            cbSkat.addItem("");
        }
        rs = con.queryResult("Select * from mskat where ccode='"+tcode.getText()+"' ");
        try {
            while(rs.next()){
                idSkat.add(rs.getString("id"));
                cbSkat.addItem(rs.getString("cname"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(StockOpname.class.getName()).log(Level.SEVERE, null, ex);
        }
        cbSkat.setSelectedIndex(0);
        
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tket,tproduk1,tsatuanhigh,tsatuanlow);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        deleteRow.clear();
        tket.setText("");
    }
    private void deffBatal(){
        barisEdit = -1;
        rfa.empty(MenuAdmin.keyssDef,tproduk1,tsatuanhigh,tsatuanlow);
        
        tket.setText("");
    }
    
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from StockOpname where  ccode='"+tcode.getText()+"' and cnbkt like '%"+"OPNAME/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/"+Login.subdefault+"-")+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("OPNAME/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/")+Login.subdefault+"-");
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
    } 
    private void loadData(){
        sql = "select a.id,a.cnbkt,a.cnbkt_po,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cnote,\n" +
            "b.cname as gudang,sum(c.nqty) as total \n" +
            "from TranRawMaterialReturn a join MGudang b on a.ccode=b.ccode\n" +
            "join TranRawMaterialReturnD c on a.cnbkt=c.cnbkt\n" +
            "where a.ccode = '"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
            " "
                + "group by a.id,a.cnbkt,a.cnbkt_po,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cnote,b.cname\n "
                + " order by a.cnbkt desc";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("gudang"),
                    rs.getString("cnbkt"),rs.getString("cnbkt_po"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cnote"),rs.getDouble("total"),
                    
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
    }
    private void loadEdit(){
        btSave.setText("Update");
        btDelete.setVisible(true);
        sql = "select * from TranRawMaterialReturn where id='"+id+"'  ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                
                no++;
                
                tbukti.setText(rs.getString("cnbkt").substring(0, rs.getString("cnbkt").length()-4));
                tbukti1.setText(rs.getString("cnbkt").substring(rs.getString("cnbkt").length()-4, rs.getString("cnbkt").length()));
                tket.setText(rs.getString("cnote"));
                jDateTran.setDate(rs.getDate("dtran"));
                tket.requestFocus();
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        
        String bkt = cnbkt;
        sql="select a.*,b.cnameProduct \n" +
        "from TranRawMaterialReturnD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
        "where a.cnbkt = '"+bkt+"'";
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                System.out.println(rs.getString("id"));
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cproduct"),rs.getString("cgroup"),
                    rs.getDouble("nqty"),0,
                    0,0,rs.getString("nunit"),rfa.formatDecimal3.format(rs.getDouble("nunit_bigvalue")),rfa.formatDecimal3.format(rs.getDouble("nunit_smallvalue")),
                    null,null,
                    rfa.formatDecimal3.format(rs.getDouble("ncogs")),
                    rs.getString("cnote"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTable1.getValueAt(baris, 0).toString();
      
    }
    private void loadItem(){
        if(jComboBox1.getSelectedItem().toString().equalsIgnoreCase("Produk")) {
            sql="select b.ccode,a.cgroup,a.cproduct,a.cnameProduct,b.qty,isnull(satuan_in,a.cunit) as cunit,b.skat from MProduk a\n" +
                "left join (\n" +
                "	select a.ccode,a.cgroup,a.cproduct,b.cnameProduct,sum(a.qty_in) as qty,a.satuan_in,a.skat\n" +
                "	from ProductStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
                "	where a.ccode='"+tcode.getText()+"' and a.dtran<'"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"'\n" +
                "	and a.skat='"+cbSkat.getSelectedItem().toString()+"'\n" +
                "	group by a.ccode,a.cgroup,a.cproduct,a.cunit,b.cnameProduct,a.satuan_in,a.skat\n" +
                ") b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n"+
                " where a.CDProduct='P' ";
            
            if(tcode.getText().equalsIgnoreCase("PMK02")) {
                sql+=" and a.cproduct in (select cproduct from MProdukJual)";
            }
            sql+=" order by a.cgroup,a.cnameProduct";
        } else {
            
            sql="select b.ccode,a.cgroup,a.cproduct,a.cnameProduct,b.qty,a.cunit from MProduk a\n" +
                "left join (\n" +
                "	select a.ccode,a.cgroup,a.cproduct,b.cnameProduct,sum(a.NQty*a.nunit_smallvalue) as qty,b.cunit \n" +
                "	From MaterialStock a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
                "	where a.ccode='"+tcode.getText()+"' and a.dtran<'"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"'\n" +
                "	group by a.ccode,a.cgroup,a.cproduct,b.cunit ,b.cnameProduct\n" +
                ") b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
                "where CDProduct='PROD'";
            sql+=" order by a.cgroup,a.cnameProduct";
        }
      
        
        
        System.out.println(sql);
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameProduct"),
                    rs.getDouble("qty"),rs.getString("cunit"),null,null,null,rs.getString("cunit")
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(StockOpname.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    private Boolean cekSave(){
        if(tket.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"keterangan wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        return true;
    }
    
    private void save(){
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("cket",tket.getText());
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("username",Login.user);
        listObj.put("idskat",idSkat.get(cbSkat.getSelectedIndex()));
        
        sql=con.insertValue("StockOpname", listObj);
        
        if(con.queryUpdate(sql)>0) {
            for(int i=0; i<jTable1.getRowCount(); i++){
                HashMap<String,String> listd = new HashMap<String,String>();        
                listd.put("cnbkt",tbukti.getText()+tbukti1.getText());
                listd.put("ccode",tcode.getText());
                listd.put("cgroup",jTable1.getValueAt(i,0).toString());
                listd.put("cproduct",jTable1.getValueAt(i,1).toString());
                
                listd.put("nqty_system",rfa.formatDecimal3.format(jTable1.getValueAt(i,3)));
                listd.put("satuan_system",jTable1.getValueAt(i,4).toString());
                
                if(jTable1.getValueAt(i,5) !=null)  listd.put("nqty_real",rfa.formatDecimal3.format(jTable1.getValueAt(i,5)));
                else                                listd.put("nqty_real",rfa.formatDecimal3.format(jTable1.getValueAt(i,3)));
                    
                if(jTable1.getValueAt(i,9) !=null)  listd.put("nqty_real_small",rfa.formatDecimal3.format(jTable1.getValueAt(i,9)));
                else                                listd.put("nqty_real_small",rfa.formatDecimal3.format(jTable1.getValueAt(i,3)));
                    
                if(jTable1.getValueAt(i,6) !=null)  listd.put("satuan_real",jTable1.getValueAt(i,6).toString());
                else                                listd.put("satuan_real",jTable1.getValueAt(i,4).toString());
                
                if(jTable1.getValueAt(i,7) !=null)  listd.put("selisih",rfa.formatDecimal3.format(jTable1.getValueAt(i,7)));
                else                                listd.put("selisih","0");
                
                sql=con.insertValue("StockOpnameD", listd);
                con.queryUpdate(sql);
            }
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
        String due_date = "NULL";
        
        
        
        if(con.queryUpdate(sql)>0) {
                JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
                int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
                if(pil == 0) btPrint.doClick();
                clear();
                deff();
                LastCode();
                loadData();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Mengubah Sebagian Data.", "Save", JOptionPane.ERROR_MESSAGE);

        }   
        
    }
    private void delete(){
        int err=0;
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranRawMaterialReturnD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  TranRawMaterialReturnD  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                if(con.queryUpdate(sql)<=0){
                    err++;
                } 
            }
            
        }
        sql="delete from  TranRawMaterialReturn  where id = '"+id+"' ";
        if(con.queryUpdate(sql)<=0){
            err++;
        } 
        
        if(err==0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clear();
            deff();
            LastCode();
            loadData();
            
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void print(){
        HashMap param = new HashMap();
        param.clear();
                
        String pengaju="";
        Double total =0.0;
        
        sql="select a.*,b.cdesc,c.dtran as dtranpo from TranRawMaterialReturn a join MAccount b on a.cacckas=b.caccnt and a.ccode=b.ccode\n" +
            "left join TranPurchaseOrder c on a.cnbkt_po=c.cnbkt and a.ccode=c.ccode\n" +
            "where a.ccode = '"+tcode.getText()+"' and a.cnbkt = '"+cnbkt_print+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                param.put("pt", deffProgram.get("pt"));
                param.put("alamat", deffProgram.get("alamat"));
                param.put("telp", "Telp. "+deffProgram.get("telp"));
                param.put("mail", "Email : "+deffProgram.get("mail"));
                param.put("bukti",rs.getString("cnbkt"));
                param.put("tanggal",rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"));
                param.put("tanggalpo",rfa.DateString(keyssDef,rs.getDate("dtranpo"),"dd/MM/yyyy"));
                param.put("akun",rs.getString("cacckas")+" - "+rs.getString("cdesc"));
                param.put("buktipo",rs.getString("cnbkt_po"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        sql="select a.*,b.cname as supplier from TranRawMaterialReturnD a\n" +
            "left join MSupplier b on a.kode_supplier=b.kode_supplier\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+cnbkt_print+"'";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    nomor++,
                    rs.getString("supplier"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getDouble("nqty"),
                    rs.getDouble("nprice"),
                    rs.getDouble("ndisc"),
                    rs.getDouble("ntotal"),
                    rs.getString("nunit"),
                    rs.getString("cnote"),
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranMaterialReturn.jrxml";
            
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

        tsatuanhigh = new javax.swing.JTextField();
        tsatuanlow = new javax.swing.JTextField();
        tproduk1 = new javax.swing.JTextField();
        jScrollPane5 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jDialog1 = new javax.swing.JDialog();
        jPanel3 = new javax.swing.JPanel();
        jLabel7 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        jLabel11 = new javax.swing.JLabel();
        namaproduk = new javax.swing.JTextField();
        satuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        qty = new javax.swing.JTextField();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
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
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jTextField13 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        btPrint = new javax.swing.JButton();
        jLabel10 = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        cbSkat = new javax.swing.JComboBox();
        jLabel17 = new javax.swing.JLabel();
        jComboBox1 = new javax.swing.JComboBox();
        jButton1 = new javax.swing.JButton();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();

        tsatuanhigh.setText("jTextField1");

        tsatuanlow.setText("jTextField1");

        tproduk1.setText("jTextField1");

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "no", "supplier", "group", "product", "qty", "harga", "disc", "total", "satuan", "ket"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane5.setViewportView(jtable_print);

        jDialog1.getContentPane().setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel7.setText("Satuan");
        jPanel3.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, -1, 20));

        jLabel8.setText("Nama Produk");
        jPanel3.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 11, -1, 20));

        jLabel11.setText("Qty");
        jPanel3.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, -1, 20));

        namaproduk.setEditable(false);
        jPanel3.add(namaproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 10, 330, -1));

        satuan.setEditable(false);
        satuan.setBackground(new java.awt.Color(153, 153, 255));
        satuan.setForeground(new java.awt.Color(255, 255, 255));
        jPanel3.add(satuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 70, 220, -1));

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
        jPanel3.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 70, 30, -1));
        jPanel3.add(qty, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 40, 110, -1));

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Stock Opname");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jLabel12.setText("No Bukti");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));
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

        jLabel13.setText("Periode ");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

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
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 340, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("Keterangan");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));

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

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField11.setText("Double Click to Edit");

        jTextField12.setEditable(false);
        jTextField12.setBackground(new java.awt.Color(204, 204, 255));
        jTextField12.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField12.setText("Total Data : 100");

        jTextField13.setEditable(false);
        jTextField13.setBackground(new java.awt.Color(204, 204, 255));
        jTextField13.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField13.setText("Detail");

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No", "Gudang", "Nomor Bukti", "PO Request", "Tanggal", "Keterangan", "Total Qty"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable2.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable2MouseClicked(evt);
            }
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                jTable2MouseEntered(evt);
            }
        });
        jScrollPane3.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
        }

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Group", "Product", "Desc", "Qty", "Harga", "Total ", "Disc", "Total All"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane2.setViewportView(jTable3);

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1130, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, 326, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE))
                    .addComponent(jScrollPane3)
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.Alignment.TRAILING))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.PREFERRED_SIZE, 208, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 83, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Log");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Skat (Marketing)");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("Jenis");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 100, 20));

        tbukti.setEditable(false);
        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 120, -1));

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
        jPanel1.add(tbukti1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 50, 60, -1));

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
        jPanel1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 180, -1));
        jPanel1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 90, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 20, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 20, 200, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 50, 130, -1));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("No. Bukti");
        jLabel10.setToolTipText("");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 72, 20));
        jPanel1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 330, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel6.setText("*Klik setiap barisnya pada (Qty saat ini & satuan) untuk mengisi hasil stock opname ");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(530, 180, 550, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Tgl. Transaksi");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 100, 20));

        cbSkat.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jPanel1.add(cbSkat, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 180, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Keterangan");
        jLabel17.setToolTipText("");
        jPanel1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 100, 20));

        jComboBox1.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Produk", "Material" }));
        jPanel1.add(jComboBox1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 180, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/reset-20.png"))); // NOI18N
        jButton1.setText("Load Item");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 170, 130, -1));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "Group", "Product", "Nama Produk/Bahan", "Qty System", "Satuan System", "Qty Saat ini", "Satuan", "Selisih", "Satuan Selisih", "qty_small"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(1).setMinWidth(0);
            jTable1.getColumnModel().getColumn(1).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(3).setMinWidth(90);
            jTable1.getColumnModel().getColumn(3).setMaxWidth(90);
            jTable1.getColumnModel().getColumn(5).setMinWidth(90);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(90);
            jTable1.getColumnModel().getColumn(9).setMinWidth(0);
            jTable1.getColumnModel().getColumn(9).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addGap(0, 0, Short.MAX_VALUE)
                        .addComponent(btDelete, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(13, 13, 13)
                        .addComponent(btSave, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(13, 13, 13)
                        .addComponent(btCancel, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 1130, Short.MAX_VALUE))
                .addContainerGap())
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 1090, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 209, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 216, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(btDelete)
                    .addComponent(btSave)
                    .addComponent(btCancel))
                .addContainerGap())
        );

        jTabbedPane1.addTab("Stock Opname", jPanel5);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE))
                    .addComponent(jTabbedPane1))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGap(4, 4, 4)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

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

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 3).toString();
            cnbkt_print = cnbkt;
            loadEdit();
        } else if(evt.getClickCount() == 1) {
            int baris = jTable2.getSelectedRow();
            String bkt = jTable2.getValueAt(baris, 3).toString();
            sql="select a.*,b.cnameProduct \n" +
            "from TranRawMaterialReturnD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.cnbkt = '"+bkt+"'";
            rfa.clear_table(MenuAdmin.keyssDef, jTable3);
            DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{
                        rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameProduct"),
                        rs.getDouble("nqty"),0,0,
                        0,0,rs.getString("nunit")
                    });
                }
            } catch (SQLException ex) {
                Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
            }

        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jTable2MouseEntered(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseEntered
        // TODO add your handling code here:
    }//GEN-LAST:event_jTable2MouseEntered

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 4, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed

    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
      
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
            loadSkat();
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        if(con.akses(akses, rfa.PRINT)) print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) { 
            if(tcode.getText().equals("PMK02")){
                for(int i=0; i<jTable1.getRowCount(); i++){
                    if(jTable1.getValueAt(i, 5) == null) {
                        JOptionPane.showMessageDialog(this,"Lengkapi Proses Stock Opname terlebih dahulu", "Info", JOptionPane.INFORMATION_MESSAGE);
            
                        return;
                    }
                }
            }
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    LastCode();
                    Timer t = new Timer();
                    t.schedule(new TimerTask() {
                        @Override
                        public void run() {
                            save();
                        }
                    }, 1); 
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        deleteRow = new ArrayList<>();
        push_button();
        clear();
        deff();
        loadData();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        int baris = jTable1.getSelectedRow();
        int kolom = jTable1.getSelectedColumn();
        if(evt.getClickCount()==1){
            namaproduk.setText(jTable1.getValueAt(baris, 2).toString());
            rfa.empty(keyssDef, qty,satuan);
            if(jTable1.getValueAt(baris, 4) != null) satuanterkecil=jTable1.getValueAt(baris, 4).toString();
    
            if(jTable1.getValueAt(baris, 5) != null) qty.setText(rfa.formatDecimal3.format(jTable1.getValueAt(baris, 5)));
            if(jTable1.getValueAt(baris, 6) != null) satuan.setText((jTable1.getValueAt(baris, 6).toString()));
            
            JOptionPane jp = new JOptionPane();
            int nilai = jp.showConfirmDialog(this, jPanel3, "Masukkan Nilai Stok saat ini", JOptionPane.OK_CANCEL_OPTION, JOptionPane.PLAIN_MESSAGE);
           
            if(satuan.getText().equalsIgnoreCase("")){
                JOptionPane.showMessageDialog(this,"Satuan wajib di isi.", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            
            if(nilai ==0) {
                jTable1.setValueAt(Double.valueOf(qty.getText()),baris, 5);
                jTable1.setValueAt((satuan.getText()),baris,6);
                
                Double qtyreal=Double.valueOf(qty.getText())*Double.valueOf(tsatuanlow.getText());
                Double qtysystem=Double.valueOf(jTable1.getValueAt(baris, 3).toString());
                Double selisih= qtyreal - qtysystem;
                jTable1.setValueAt(selisih,baris,7);
                jTable1.setValueAt(qtyreal,baris,9);
            }
        }
    }//GEN-LAST:event_jTable1MouseClicked

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

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        DialogSatuan a = new DialogSatuan(satuanterkecil);
        a.setVisible(true);
        if(a.status){
            satuan.setText(a.unit);
            
            Double nilaiterkecil = con.loadNilaiSatuanTerkecil(a.unit,satuanterkecil);
            
            tsatuanlow.setText(rfa.formatDecimal3.format(nilaiterkecil));
        }
    }//GEN-LAST:event_btsatuanActionPerformed

    private void btsatuanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuanKeyPressed
        if(evt.getKeyCode()==10) btsatuan.doClick();
    }//GEN-LAST:event_btsatuanKeyPressed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        loadItem();
    }//GEN-LAST:event_jButton1ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JComboBox cbSkat;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private javax.swing.JComboBox jComboBox1;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private javax.swing.JDialog jDialog1;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField13;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTextField namaproduk;
    private javax.swing.JTextField qty;
    private javax.swing.JTextField satuan;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tproduk1;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    // End of variables declaration//GEN-END:variables
}
