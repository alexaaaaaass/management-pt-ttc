/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Master;

import Accounting.*;
import Delivery.*;
import DialogPublicTP.DialogGroup;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogSatuan;
import Main.DefaultProgram;
import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
import java.awt.TextField;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
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
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class MProduk extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER PRODUK";
    public MProduk(String mode) {
        initComponents();
        loadData();
        push_button();
        deff();
        loadGroup();
    }
    
    private void loadGroup(){
        tgroup.removeAll();
        tgroup.removeAllItems();
        sql="select * From MGroup order by cgroup";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tgroup.addItem(rs.getString("cgroup"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(MMaterial.class.getName()).log(Level.SEVERE, null, ex);
        }
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
        tcode.setText(DefaultProgram.deffProgram.get("ccode"));
        tcode.requestFocus();
        tgroup.requestFocus();
        btDelete.setVisible(false);
        btSave.setText("Save");
        tgroup.setEnabled(true);
        tproduk.setEnabled(true);
        tgroup.setSelectedItem("RKK");
        id = "";
        jTabbedPane1.setTitleAt(1, "Add");
    }
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,tcode,tcode1,tproduk,tnama,tketerangan,tstok,tjual,tbeli,tsatuan,tsatuan,tcost,twarning);
    }
 
    private void loadData(){
        sql = "select * from mproduk where cgroup like'%"+tgroups.getText()+"%'  and cproduct like '%"+tproduks.getText()+"%' "
                + "and CDProduct='P' and cnameProduct like '%"+tnamas.getText()+"%' "
                + "order by cproduct,cnameProduct ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        try {
            while(rs.next()){
                String status = "Tidak Aktif";
                if(rs.getString("Fstatus").equals("1")) status = "Aktif";
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("ccode"),rs.getString("cnameProduct"),rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cdesc"),
                    rs.getDouble("NQty"),
                    rs.getDouble("NPrPurc"),rs.getDouble("NPrSell"),rs.getString("cunit"),status,rs.getDouble("ncogs")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        tinfo.setText("Total Data : "+String.valueOf(jTable3.getRowCount()));
        jTabbedPane1.setSelectedIndex(0);
    }
    private void loadEdit(){
        sql = "select * from mproduk where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tcode.setText(rs.getString("ccode"));
                tgroup.setSelectedItem(rs.getString("cgroup"));
                tproduk.setText(rs.getString("cproduct"));
                tnama.setText(rs.getString("cnameProduct"));
                tketerangan.setText(rs.getString("cdesc"));
                tstok.setText(rs.getString("NQty"));
                tjual.setText(rs.getString("NPrSell"));
                tbeli.setText(rs.getString("NPrPurc"));
                tsatuan.setText(rs.getString("cunit"));
                tsatuanhigh.setText(rs.getString("nunit_bigvalue"));
                tsatuanlow.setText(rs.getString("nunit_smallvalue"));
                tcost.setText(rfa.formatDecimal3.format(rs.getDouble("ncogs")));
                
                jTabbedPane1.setSelectedIndex(1);
                jTabbedPane1.setTitleAt(1, "Edit");
                btSave.setText("Update");
                btDelete.setVisible(true);
                
                tgroup.setEnabled(false);
                tproduk.setEnabled(false);
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void save(){
        String status = "0";
        if(tstatuscombobox.getSelectedIndex() == 0) status = "1";
        
        sql="INSERT INTO MProduk (ccode,cgroup,CDProduct,cproduct\n" +
            ",cdesc,cunit,NQty,NPrPurc\n" +
            ",NPrSell,Fstatus,cnameProduct,username,nunit_bigvalue,nunit_smallvalue,ncogs,cwarningstock)\n" +
            "VALUES\n" +
            "('"+tcode.getText()+"','"+tgroup.getSelectedItem().toString()+"','P','"+tproduk.getText()+"',"
                + "'"+tketerangan.getText()+"','"+tsatuan.getText()+"','"+tstok.getText()+"','"+tbeli.getText()+"','"+tjual.getText()+"',"
                + "'"+status+"','"+tnama.getText()+"','"+Login.user+"','"+tsatuanhigh.getText()+"','"+tsatuanlow.getText()+"','"+tcost.getText()+"',"
                + "'"+twarning.getText()+"')";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
   
    private void update(){
        String status = "0";
        if(tstatuscombobox.getSelectedIndex() == 0) status = "1";
        sql="update MProduk set ccode='"+tcode.getText()+"',cgroup='"+tgroup.getSelectedItem().toString()+"',CDProduct='P',cproduct='"+tproduk.getText()+"'\n" +
            ",cdesc='"+tketerangan.getText()+"',cunit='"+tsatuan.getText()+"',NQty='"+tstok.getText()+"',NPrPurc='"+tbeli.getText()+"'\n" +
            ",NPrSell='"+tjual.getText()+"',Fstatus='"+status+"',cnameProduct='"+tnama.getText()+"',username='"+Login.user+"' \n"
                + " ,nunit_bigvalue='"+tsatuanhigh.getText()+"',nunit_smallvalue='"+tsatuanlow.getText()+"',ncogs='"+tcost.getText()+"',"
                + "cwarningstock='"+twarning.getText()+"' " +
            " where id = '"+id+"'";
        
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
//            LastCode();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void delete(){
        sql="delete from  MProduk  where id = '"+id+"' ";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
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
        jLabel21 = new javax.swing.JLabel();
        tcost = new javax.swing.JTextField();
        tcode = new javax.swing.JTextField();
        jLabel3 = new javax.swing.JLabel();
        tcode1 = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel12 = new javax.swing.JLabel();
        tproduks = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel13 = new javax.swing.JLabel();
        tgroups = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        tnamas = new javax.swing.JTextField();
        jTextField11 = new javax.swing.JTextField();
        tinfo = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel8 = new javax.swing.JLabel();
        tnama = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        tbeli = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        jLabel10 = new javax.swing.JLabel();
        jLabel16 = new javax.swing.JLabel();
        tproduk = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        tketerangan = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        tstok = new javax.swing.JTextField();
        tjual = new javax.swing.JTextField();
        jLabel23 = new javax.swing.JLabel();
        tstatuscombobox = new javax.swing.JComboBox();
        tsatuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        jLabel24 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jButton14 = new javax.swing.JButton();
        tgroup = new javax.swing.JComboBox();
        jLabel25 = new javax.swing.JLabel();
        twarning = new javax.swing.JTextField();

        tsatuanlow.setText("jTextField1");

        tsatuanhigh.setText("jTextField1");

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Cost Per Batang");

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Log");

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));

        jButton12.setText("...");
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Master Produk");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel12.setText("Kode Produk");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));
        jPanel6.add(tproduks, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 140, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 60, 90, -1));

        jLabel13.setText("Group Produk");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));
        jPanel6.add(tgroups, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 140, -1));

        jLabel14.setText("Nama Produk");
        jPanel6.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));
        jPanel6.add(tnamas, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 30, 140, -1));

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField11.setText("Double Click to Edit");

        tinfo.setEditable(false);
        tinfo.setBackground(new java.awt.Color(204, 204, 255));
        tinfo.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tinfo.setText("Total Data : 100");

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Gudang", "Nama Produk", "Kode Group", "Produk", "Keterangan", "Stock Awal", "Harga Beli", "Harga Jual", "Satuan", "Status", "Hpp"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable3.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable3MouseClicked(evt);
            }
        });
        jScrollPane3.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(0).setMinWidth(0);
            jTable3.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(1).setMinWidth(0);
            jTable3.getColumnModel().getColumn(1).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(6).setMinWidth(0);
            jTable3.getColumnModel().getColumn(6).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(11).setMinWidth(0);
            jTable3.getColumnModel().getColumn(11).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 916, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(tinfo, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jScrollPane3))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 283, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(tinfo, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel5.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Status Aktif");
        jLayeredPane1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 280, 120, 20));

        tnama.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnamaFocusLost(evt);
            }
        });
        tnama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnamaKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tnama, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 70, 330, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Nama Produk");
        jLayeredPane1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, 110, 20));

        tbeli.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbeliFocusLost(evt);
            }
        });
        tbeli.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbeliKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tbeli, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 190, 160, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Harga Beli");
        jLayeredPane1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 190, 110, 20));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Group Produk");
        jLayeredPane1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 90, 20));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Sub Produk");
        jLayeredPane1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 40, 90, 20));

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
        jLayeredPane1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 40, 160, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Keterangan");
        jLayeredPane1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 100, 110, 20));

        tketerangan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketeranganFocusLost(evt);
            }
        });
        tketerangan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketeranganKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tketerangan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 100, 330, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Stock Awal");
        jLayeredPane1.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 130, 110, 20));

        tstok.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tstokFocusLost(evt);
            }
        });
        tstok.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tstokKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tstok, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 130, 160, -1));

        tjual.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tjualFocusLost(evt);
            }
        });
        tjual.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tjualKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tjual, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 160, 160, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Harga Jual");
        jLayeredPane1.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 160, 110, 20));

        tstatuscombobox.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Aktif", "Tidak Aktif" }));
        jLayeredPane1.add(tstatuscombobox, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 280, 160, -1));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        jLayeredPane1.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 250, 160, -1));

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
        jLayeredPane1.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 250, 30, -1));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Satuan");
        jLayeredPane1.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 250, 110, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 330, 97, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        btSave.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btSaveKeyPressed(evt);
            }
        });
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 330, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 330, 97, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 10, 30, -1));

        tgroup.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jLayeredPane1.add(tgroup, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 10, 120, -1));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Warning Stock");
        jLayeredPane1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 220, 130, 20));

        twarning.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                twarningFocusLost(evt);
            }
        });
        twarning.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                twarningKeyPressed(evt);
            }
        });
        jLayeredPane1.add(twarning, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 220, 160, -1));

        jPanel5.add(jLayeredPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 10, 760, 400));

        jTabbedPane1.addTab("Add/Edit", jPanel5);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jTabbedPane1)
                .addContainerGap())
            .addGroup(layout.createSequentialGroup()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
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

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!a.ccode.equals("")){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 2){
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            loadEdit();
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        rfa.FieldCapital(tproduk);
        if(tproduk.getText().isEmpty()) return;
        
        sql = "select * from MProduk where cproduct = '"+tproduk.getText()+"' ";
        rs=con.queryResult(sql);
        Boolean checkkode= false;
        try {
            while(rs.next()){
                checkkode=true;
            }
        } catch (SQLException e){
            e.printStackTrace();
        }
        if(checkkode){
            JOptionPane.showMessageDialog(this,"Kode Produk sudah ada.", "Save", JOptionPane.ERROR_MESSAGE);
            tproduk.setText("");
            tproduk.requestFocus();
        }
    }//GEN-LAST:event_tprodukFocusLost

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
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
    }//GEN-LAST:event_btCancelActionPerformed

    private void tnamaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnamaFocusLost
        rfa.FieldCapital(tnama);
    }//GEN-LAST:event_tnamaFocusLost

    private void tketeranganFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketeranganFocusLost
        rfa.FieldCapital(tketerangan);
    }//GEN-LAST:event_tketeranganFocusLost

    private void tstokFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tstokFocusLost
        rfa.FieldCapital(tstok);
    }//GEN-LAST:event_tstokFocusLost

    private void tjualFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tjualFocusLost
        rfa.FieldCapital(tjual);
    }//GEN-LAST:event_tjualFocusLost

    private void tbeliFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbeliFocusLost
        rfa.FieldCapital(tbeli);
    }//GEN-LAST:event_tbeliFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
        rfa.EnterNextFocus(evt,tnama);
    }//GEN-LAST:event_tprodukKeyPressed

    private void tnamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnamaKeyPressed
        rfa.EnterNextFocus(evt,tketerangan);
    }//GEN-LAST:event_tnamaKeyPressed

    private void tketeranganKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketeranganKeyPressed
        rfa.EnterNextFocus(evt,tstok);
    }//GEN-LAST:event_tketeranganKeyPressed

    private void tstokKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tstokKeyPressed
        rfa.EnterNextFocus(evt,tjual);
    }//GEN-LAST:event_tstokKeyPressed

    private void tjualKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tjualKeyPressed
        rfa.EnterNextFocus(evt,tbeli);
    }//GEN-LAST:event_tjualKeyPressed

    private void tbeliKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbeliKeyPressed
        rfa.EnterNextFocus(evt,btSave);
    }//GEN-LAST:event_tbeliKeyPressed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode() == 10) {
            btSave.doClick();
        }
    }//GEN-LAST:event_btSaveKeyPressed

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tcode1.setText(rs.getString("cname"));
            }
        } catch (Exception e){}        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        DialogSatuan a = new DialogSatuan("");
        a.setVisible(true);
        if(a.status){
            tsatuan.setText(a.unit);
            tsatuanhigh.setText(rfa.formatDecimal3.format(a.big_value));
            tsatuanlow.setText(rfa.formatDecimal3.format(a.small_value));
        }
    }//GEN-LAST:event_btsatuanActionPerformed

    private void btsatuanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuanKeyPressed
        if(evt.getKeyCode()==10) btsatuan.doClick();
    }//GEN-LAST:event_btsatuanKeyPressed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogGroup a = new DialogGroup();
        a.setVisible(true);
        loadGroup();
    }//GEN-LAST:event_jButton14ActionPerformed

    private void twarningFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_twarningFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_twarningFocusLost

    private void twarningKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_twarningKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_twarningKeyPressed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton7;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField tbeli;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcost;
    private javax.swing.JComboBox tgroup;
    private javax.swing.JTextField tgroups;
    private javax.swing.JTextField tinfo;
    private javax.swing.JTextField tjual;
    private javax.swing.JTextField tketerangan;
    private javax.swing.JTextField tnama;
    private javax.swing.JTextField tnamas;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tproduks;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    private javax.swing.JComboBox tstatuscombobox;
    private javax.swing.JTextField tstok;
    private javax.swing.JTextField twarning;
    // End of variables declaration//GEN-END:variables
}
