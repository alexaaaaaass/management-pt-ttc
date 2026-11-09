/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Production;

import Accounting.*;
import DialogPublicTP.DialogDelivery;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogSatuan;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Purchase.PurchaseRequest;
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
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class PackagingReturn extends javax.swing.JPanel {

     ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="PRODUKSI RETURN";
    String idKas="R2";
    ArrayList<String> deleteRow;
    ArrayList<String> cproductWO;
    public PackagingReturn(String mode) {
        initComponents();
      
        push_button();
        clear();
        deff();
        loadData();
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
        rfa.empty(MenuAdmin.keyssDef,tket,tproduk,tprodukgroup,tproduknama,tsatuanhigh,tsatuanlow,
                tcustomer,tcustomerkode,
                tsj,tspk,tpo,
                tbox,tbox_waste,
                tikat,tikat_waste,
                tsatuan,tsatuan_waste,tsatuanhigh,tsatuanhigh_waste,tsatuanlow,tsatuanlow_waste);
        rfa.empty(keyssDef, tpcs,tpcs_waste); 
    }
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,left(cnbkt,3))) as lastno "
                + " from PReturn "
                + " where  ccode='"+tcode.getText()+"' "
                + " and month(dtran)= '"+rfa.DateString(keyssDef, jDateTran.getDate(),"MM")+"' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("/RETURN/UG-RMS/"+con.romawi(rfa.DateString(keyssDef, jDateTran.getDate(),"MM"))+"/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yy"));
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql="select a.*,b.cname as customer,c.cnameProduct\n" +
            "from PReturn a left join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
            "where a.ccode='"+tcodes.getText()+"' and (a.dtran>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "\n";
        if(!tbuktis.getText().isEmpty()) sql+=" and a.cnbkt like '%"+tbuktis.getText()+"%' ";
        if(!tkets.getText().isEmpty()) sql+=" and b.cname like '%"+tkets.getText()+"%' ";
        sql+="order by a.id desc";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cnbkt_delivery"),
                    rs.getString("cnbkt_order"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("nqty_small")*rs.getDouble("nqty_box"),
                    rs.getDouble("nqty_small_waste")*rs.getDouble("nqty_box_waste"), 
                    rs.getString("cnote"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
    }
    private void loadEdit(){
        btSave.setText("Update");
        btDelete.setVisible(true);
        sql = "select a.*,b.cname as customer,c.cnameProduct\n" +
            "from PReturn a left join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
            "where a.id='"+id+"'  ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){ 
                no++; 
                tbukti1.setText(rs.getString("cnbkt").substring(0,3));
                tbukti.setText(rs.getString("cnbkt").substring(3, rs.getString("cnbkt").length()));
                tcustomerkode.setText(rs.getString("kode_customer"));
                tcustomer.setText(rs.getString("customer"));
                tsj.setText(rs.getString("cnbkt_delivery"));
                tpo.setText(rs.getString("cnbkt_order"));
                tket.setText(rs.getString("cnote"));
                tproduk.setText(rs.getString("cproduct"));
                tprodukgroup.setText(rs.getString("cgroup"));
                tproduknama.setText(rs.getString("cnameProduct"));
                jDateTran.setDate(rs.getDate("dtran"));
                
                tsatuan.setText(rs.getString("csatuan"));
                tsatuanlow.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_small")/rs.getDouble("nqty")));
                tbox.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_box")));
                tikat.setText(rfa.formatDecimal3.format(rs.getDouble("nqty")));
                
                tsatuan_waste.setText(rs.getString("csatuan_waste"));
                tsatuanlow_waste.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_small_waste")/rs.getDouble("nqty_waste")));
                tbox_waste.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_box_waste")));
                tikat_waste.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_waste")));
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        hitungPcs();
        hitungPcs_waste();
         
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
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
    
    private void hitungPcs(){
        try{
            int ikat=0,pcskonversi=0,total=0;
            if(!tikat.getText().isEmpty()) {
                ikat = Integer.valueOf(tikat.getText());
            }
            if(!tsatuanlow.getText().isEmpty()) {
                pcskonversi = Integer.valueOf(tsatuanlow.getText());
            }
            
            total = ikat*pcskonversi;
            tpcs.setText(rfa.formatDecimal3.format(total));
            
        }catch(Exception e){
            
        }
    }
    private void hitungPcs_waste(){
        try{
            int ikat=0,pcskonversi=0,total=0;
            if(!tikat_waste.getText().isEmpty()) {
                ikat = Integer.valueOf(tikat_waste.getText());
            }
            if(!tsatuanlow_waste.getText().isEmpty()) {
                pcskonversi = Integer.valueOf(tsatuanlow_waste.getText());
            }
            
            total = ikat*pcskonversi;
            tpcs_waste.setText(rfa.formatDecimal3.format(total));
            
        }catch(Exception e){
            
        }
    }
    private void print(){
        
    }
    private Boolean ceksave(){
        if(tcustomer.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Customer Belum di pilih", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tproduk.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Produk Belum di pilih", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tbox.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Sorting Good Belum lengkap", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tikat.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Sorting Good Belum lengkap", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tsatuan.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Sorting Good Belum lengkap", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tbox_waste.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Sorting Waste Belum lengkap", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tikat_waste.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Sorting Waste Belum lengkap", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tsatuan_waste.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Sorting Waste Belum lengkap", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        
        
        return true;
    }
    private void save(){
        if(!ceksave()) return;
        cnbkt_print = tbukti1.getText()+tbukti.getText();
        String due_date = "NULL";
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("ctran","IR");
        listObj.put("dtran",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cyear",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy"));
        listObj.put("cnbkt_delivery",tsj.getText());
        listObj.put("cnbkt_order",tpo.getText());
        listObj.put("cworkorder",tspk.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("cgroup",tprodukgroup.getText());
        listObj.put("cproduct",tproduk.getText());
        listObj.put("cnote",tket.getText());
        listObj.put("nqty_box",tbox.getText());
        listObj.put("nqty",tikat.getText());
        listObj.put("csatuan",tsatuan.getText());
        listObj.put("nqty_small",tpcs.getText());
        listObj.put("csatuan_small","PCS");
        listObj.put("nqty_box_waste",tbox_waste.getText());
        listObj.put("nqty_waste",tikat_waste.getText());
        listObj.put("csatuan_waste",tsatuan_waste.getText());
        listObj.put("nqty_small_waste",tpcs_waste.getText());
        listObj.put("csatuan_small_waste","PCS");
        listObj.put("cusername",Login.user);
        sql=con.insertValue("PReturn", listObj);
        if(con.queryUpdate(sql)>0) {
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
        if(!ceksave()) return;
        cnbkt_print = tbukti1.getText()+tbukti.getText();
        String due_date = "NULL";
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("ctran","IR");
        listObj.put("dtran",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cyear",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy"));
        listObj.put("cnbkt_delivery",tsj.getText());
        listObj.put("cnbkt_order",tpo.getText());
        listObj.put("cworkorder",tspk.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("cgroup",tprodukgroup.getText());
        listObj.put("cproduct",tproduk.getText());
        listObj.put("cnote",tket.getText());
        listObj.put("nqty_box",tbox.getText());
        listObj.put("nqty",tikat.getText());
        listObj.put("csatuan",tsatuan.getText());
        listObj.put("nqty_small",tpcs.getText());
        listObj.put("csatuan_small","PCS");
        listObj.put("nqty_box_waste",tbox_waste.getText());
        listObj.put("nqty_waste",tikat_waste.getText());
        listObj.put("csatuan_waste",tsatuan_waste.getText());
        listObj.put("nqty_small_waste",tpcs_waste.getText());
        listObj.put("csatuan_small_waste","PCS");
        listObj.put("cusername",Login.user);
        sql=con.updateValue("PReturn", listObj);
        sql+=" where id="+id;
        if(con.queryUpdate(sql)>0) {
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
        tsatuanlow_waste = new javax.swing.JTextField();
        tsatuanhigh_waste = new javax.swing.JTextField();
        tprodukgroup = new javax.swing.JTextField();
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
        jLabel31 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jLabel4 = new javax.swing.JLabel();
        jDateTran = new com.toedter.calendar.JDateChooser();
        jLabel18 = new javax.swing.JLabel();
        jLabel21 = new javax.swing.JLabel();
        tcustomerkode = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tcustomer = new javax.swing.JTextField();
        tproduk = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        tproduknama = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        tbukti = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        jButton1 = new javax.swing.JButton();
        jButton4 = new javax.swing.JButton();
        tsj = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel19 = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        tikat = new javax.swing.JTextField();
        jLabel23 = new javax.swing.JLabel();
        tsatuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        tbox = new javax.swing.JTextField();
        tpcs = new javax.swing.JLabel();
        jLabel26 = new javax.swing.JLabel();
        jTextField2 = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        jLayeredPane2 = new javax.swing.JLayeredPane();
        jLabel20 = new javax.swing.JLabel();
        jLabel27 = new javax.swing.JLabel();
        tikat_waste = new javax.swing.JTextField();
        jLabel24 = new javax.swing.JLabel();
        tsatuan_waste = new javax.swing.JTextField();
        tbox_waste = new javax.swing.JTextField();
        tpcs_waste = new javax.swing.JLabel();
        jLabel28 = new javax.swing.JLabel();
        jTextField1 = new javax.swing.JTextField();
        btsatuan1 = new javax.swing.JButton();
        jLabel29 = new javax.swing.JLabel();
        jLabel10 = new javax.swing.JLabel();
        tspk = new javax.swing.JTextField();
        tpo = new javax.swing.JTextField();
        jLabel30 = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        btPrint = new javax.swing.JButton();

        tsatuanlow.setText("jTextField1");

        tsatuanhigh.setText("jTextField1");

        tsatuanlow_waste.setText("jTextField1");

        tsatuanhigh_waste.setText("jTextField1");

        tprodukgroup.setText("jTextField1");

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
        jLabel2.setText("Return Customer");
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

        tkets.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketsKeyPressed(evt);
            }
        });
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 340, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("Customer");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));

        jLabel31.setText("Gudang");
        jPanel6.add(jLabel31, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

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

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Gudang", "Nomor Bukti", "Tanggal", "No SJ", "No PO", "Produk", "Qty Good (Pcs)", "Qty Waste (Pcs_", "Keterangan"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable2.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable2.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable2MouseClicked(evt);
            }
        });
        jScrollPane2.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
        }

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
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1141, Short.MAX_VALUE)
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
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 241, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 100, 20));
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
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 20, 170, -1));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Transaksi");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

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
        jPanel1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 180, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Keterangan");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 260, 90, 20));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("No Surat Jalan");
        jPanel1.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 130, 20));

        tcustomerkode.setEditable(false);
        jPanel1.add(tcustomerkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 60, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 110, 30, -1));

        tcustomer.setEditable(false);
        jPanel1.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 110, 200, -1));

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
        jPanel1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 230, 80, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 230, 30, -1));

        tproduknama.setEditable(false);
        tproduknama.setBackground(new java.awt.Color(153, 153, 255));
        tproduknama.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tproduknama, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 230, 180, -1));

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

        tbukti.setEditable(false);
        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 80, 240, -1));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 320, 97, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 320, 97, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        jButton1.setText("Cancel");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 320, 97, -1));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton4ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(420, 140, 30, -1));

        tsj.setEditable(false);
        jPanel1.add(tsj, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 270, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Customer");
        jPanel1.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 130, 20));

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Jml Box");
        jLabel19.setToolTipText("");
        jLayeredPane1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 130, 20));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Jml Ikat dlm 1 Box");
        jLabel25.setToolTipText("");
        jLayeredPane1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, 130, 20));

        tikat.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tikatKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tikatKeyTyped(evt);
            }
        });
        jLayeredPane1.add(tikat, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 70, 150, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Satuan Ikat");
        jLayeredPane1.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 100, 90, 20));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 100, 150, -1));

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
        jLayeredPane1.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 100, 30, -1));

        tbox.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tboxKeyReleased(evt);
            }
        });
        jLayeredPane1.add(tbox, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 40, 150, -1));

        tpcs.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        tpcs.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        tpcs.setText("0");
        tpcs.setToolTipText("");
        jLayeredPane1.add(tpcs, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 60, 20));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Pcs");
        jLabel26.setToolTipText("");
        jLayeredPane1.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 70, 30, 20));

        jTextField2.setEditable(false);
        jTextField2.setBackground(new java.awt.Color(153, 153, 255));
        jTextField2.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField2.setForeground(new java.awt.Color(255, 255, 255));
        jTextField2.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField2.setText("GOOD SORTING");
        jTextField2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField2ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jTextField2, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 420, 30));

        jPanel1.add(jLayeredPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 10, 420, 140));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("No. Bukti ");
        jLabel7.setToolTipText("");
        jPanel1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 72, 20));

        jLayeredPane2.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane2.setOpaque(true);
        jLayeredPane2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Jml Box");
        jLabel20.setToolTipText("");
        jLayeredPane2.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 130, 20));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("Jml Ikat dlm 1 Box");
        jLabel27.setToolTipText("");
        jLayeredPane2.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, 130, 20));

        tikat_waste.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tikat_wasteKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tikat_wasteKeyTyped(evt);
            }
        });
        jLayeredPane2.add(tikat_waste, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 70, 150, -1));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Satuan Ikat");
        jLayeredPane2.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 100, 90, 20));

        tsatuan_waste.setEditable(false);
        tsatuan_waste.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan_waste.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane2.add(tsatuan_waste, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 100, 150, -1));

        tbox_waste.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbox_wasteKeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tbox_wasteKeyReleased(evt);
            }
        });
        jLayeredPane2.add(tbox_waste, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 40, 150, -1));

        tpcs_waste.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        tpcs_waste.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        tpcs_waste.setText("0");
        tpcs_waste.setToolTipText("");
        jLayeredPane2.add(tpcs_waste, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 60, 20));

        jLabel28.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel28.setText("Pcs");
        jLabel28.setToolTipText("");
        jLayeredPane2.add(jLabel28, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 70, 30, 20));

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(255, 51, 102));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField1.setForeground(new java.awt.Color(255, 255, 255));
        jTextField1.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField1.setText("WASTE SORTING");
        jTextField1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField1ActionPerformed(evt);
            }
        });
        jLayeredPane2.add(jTextField1, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 420, 30));

        btsatuan1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btsatuan1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btsatuan1ActionPerformed(evt);
            }
        });
        btsatuan1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btsatuan1KeyPressed(evt);
            }
        });
        jLayeredPane2.add(btsatuan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 100, 30, -1));

        jPanel1.add(jLayeredPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 160, 420, 140));

        jLabel29.setText("Bukti PO");
        jPanel1.add(jLabel29, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, -1, 20));

        jLabel10.setText("Bukti SPK ");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, -1, 20));

        tspk.setEditable(false);
        jPanel1.add(tspk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 170, -1));

        tpo.setEditable(false);
        jPanel1.add(tpo, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 170, -1));

        jLabel30.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel30.setText("Produk");
        jPanel1.add(jLabel30, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 230, 90, 20));

        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        jPanel1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 260, 300, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 50, 110, -1));

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 1082, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 79, Short.MAX_VALUE))
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 366, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 40, Short.MAX_VALUE))
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

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
         
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
         
    }//GEN-LAST:event_tprodukKeyPressed

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tproduknama.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton10ActionPerformed

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 4, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed

    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jButton12ActionPerformed

    private void jButton4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton4ActionPerformed
        DialogDelivery a = new DialogDelivery();
        a.setVisible(true);
        if(a.status) {
            tsj.setText(a.buktisj);
            tpo.setText(a.buktipo);
            tspk.setText(a.buktispk);
            tcustomerkode.setText(a.kodecustomer);
            tcustomer.setText(a.customer);
        }
    }//GEN-LAST:event_jButton4ActionPerformed

    private void jTextField1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField1ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField1ActionPerformed

    private void tikatKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tikatKeyReleased
        hitungPcs();
    }//GEN-LAST:event_tikatKeyReleased

    private void tikatKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tikatKeyTyped

    }//GEN-LAST:event_tikatKeyTyped

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        String satuanterkecil="PCS";
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

    private void tboxKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tboxKeyReleased
        hitungPcs();
    }//GEN-LAST:event_tboxKeyReleased

    private void jTextField2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField2ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField2ActionPerformed

    private void tikat_wasteKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tikat_wasteKeyReleased
        hitungPcs_waste();
    }//GEN-LAST:event_tikat_wasteKeyReleased

    private void tikat_wasteKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tikat_wasteKeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tikat_wasteKeyTyped

    private void btsatuan1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuan1ActionPerformed
        String satuanterkecil="PCS";
        DialogSatuan a = new DialogSatuan(satuanterkecil);
        a.setVisible(true);
        if(a.status){
            tsatuan_waste.setText(a.unit);
            tsatuanhigh_waste.setText(rfa.formatDecimal3.format(a.big_value));
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
            tsatuanlow_waste.setText(rfa.formatDecimal3.format(nilaisatuan));
            hitungPcs_waste();
        }
    }//GEN-LAST:event_btsatuan1ActionPerformed

    private void btsatuan1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuan1KeyPressed
        
    }//GEN-LAST:event_btsatuan1KeyPressed

    private void tbox_wasteKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbox_wasteKeyReleased
        // TODO add your handling code here:
    }//GEN-LAST:event_tbox_wasteKeyReleased

    private void tbox_wasteKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbox_wasteKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tbox_wasteKeyPressed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {


            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    //LastCode();
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

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        if(con.akses(akses, rfa.PRINT)) print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode() == 10) jButton7.doClick();
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
        if(evt.getKeyCode() == 10) jButton7.doClick();
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

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 3).toString();
            cnbkt_print = cnbkt;
            tcode.setText(tcodes.getText());
            loadEdit();
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        clear();
        deff();
        loadData();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JButton btsatuan1;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton4;
    private javax.swing.JButton jButton7;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel15;
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
    private javax.swing.JLabel jLabel28;
    private javax.swing.JLabel jLabel29;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel30;
    private javax.swing.JLabel jLabel31;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JLayeredPane jLayeredPane2;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField2;
    private javax.swing.JTextField tbox;
    private javax.swing.JTextField tbox_waste;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tcustomer;
    private javax.swing.JTextField tcustomerkode;
    private javax.swing.JTextField tikat;
    private javax.swing.JTextField tikat_waste;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JLabel tpcs;
    private javax.swing.JLabel tpcs_waste;
    private javax.swing.JTextField tpo;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tproduknama;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuan_waste;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanhigh_waste;
    private javax.swing.JTextField tsatuanlow;
    private javax.swing.JTextField tsatuanlow_waste;
    private javax.swing.JTextField tsj;
    private javax.swing.JTextField tspk;
    // End of variables declaration//GEN-END:variables
}
