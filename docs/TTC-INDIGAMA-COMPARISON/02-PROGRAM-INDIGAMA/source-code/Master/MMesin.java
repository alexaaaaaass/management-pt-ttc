/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Master;

import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogSatuan;
import Main.DefaultProgram;
import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.table.DefaultTableModel;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class MMesin extends javax.swing.JPanel {
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER MESIN";
    String satuansebelum="";
    
    ArrayList<String> idmesin;
    public MMesin() {
        initComponents();
        loadData();
        LastCode();
        deff();
        loadLink();
    }
    
    
    
    private void deff(){
        btDelete.setVisible(false);
        btSave.setText("Save");
        id = "";
        LastCode();
        
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
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,nama,tcode,tcode1);
    }
    
    private void LastCode(){
       
    } 
    private void loadData(){
        sql = "select * from mmesin \n" +
                "where cname like '%"+iNama1.getText()+"%' \n" +
                "order by cname ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),rs.getString("cname"),rs.getString("ccode"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void loadEdit(){
        sql = "select * from mmesin where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                nama.setText(rs.getString("cname"));
                tcode.setText(rs.getString("ccode"));
                tprodukgroup.setText(rs.getString("cgroup"));
                tproduk.setText(rs.getString("cproduct"));
                
                btSave.setText("Update");
                btDelete.setVisible(true);
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
    }
    private void save(){
        HashMap<String,String> listObj = new HashMap<String,String>();      
        listObj.put("ccode",tcode.getText());
        listObj.put("cname",nama.getText());
//        listObj.put("cgroup",tprodukgroup.getText());
//        listObj.put("cproduct",tproduk.getText());
        sql=con.insertValue("mmesin", listObj);
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            LastCode();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
   
    private void update(){
        HashMap<String,String> listObj = new HashMap<String,String>();      
        listObj.put("ccode",tcode.getText());
        listObj.put("cname",nama.getText());
//        listObj.put("cgroup",tprodukgroup.getText());
//        listObj.put("cproduct",tproduk.getText());
        sql=con.updateValue("mmesin", listObj);
        sql+=" where id = "+id;
        if(con.queryUpdate(sql)>0){
           
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            LastCode();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void delete(){
         JOptionPane.showMessageDialog(this,"Dilarang menghapus data master mesin", "Save", JOptionPane.ERROR_MESSAGE);
    }

    private void saveLink(){
        int stat=1;
        sql = "select * from MMesinInv where cgroup = '"+tprodukgroup.getText()+"' and cproduct='"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                stat = 0;          
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        if(stat == 0) {
            JOptionPane.showMessageDialog(this,"Produk sudah terdaftar", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        HashMap<String,String> listObj = new HashMap<String,String>();      
        listObj.put("idmesin",idmesin.get(mesin.getSelectedIndex()));
        listObj.put("cgroup",tprodukgroup.getText());
        listObj.put("cproduct",tproduk.getText());
        sql=con.insertValue("MMesinInv", listObj);
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadLink();
            clear();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
   
    private void loadLink(){
        sql = "select a.*,b.cname as mesin from MMesinInv a join MMesin b on a.idmesin=b.id\n" +
                "order by b.cname ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getInt("id"),rs.getString("idmesin"),rs.getString("mesin"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    
    private void deletelink(String id){
        sql="delete from MMesinInv where id="+id;
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadLink();
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

        jLabel9 = new javax.swing.JLabel();
        abig = new javax.swing.JTextField();
        jPanel4 = new javax.swing.JPanel();
        jLabel13 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel1 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel4 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        nama = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode = new javax.swing.JTextField();
        tcode1 = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        jPanel6 = new javax.swing.JPanel();
        jLabel8 = new javax.swing.JLabel();
        iNama1 = new javax.swing.JTextField();
        btSearch1 = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jLabel5 = new javax.swing.JLabel();
        tproduknama = new javax.swing.JTextField();
        tproduk = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        tprodukgroup = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        mesin = new javax.swing.JComboBox();
        btSave1 = new javax.swing.JButton();

        jLabel9.setText("Big Value");

        abig.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                abigFocusLost(evt);
            }
        });
        abig.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                abigKeyPressed(evt);
            }
        });

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel4.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel14.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel14.setText("Master Mesin");
        jPanel4.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setOpaque(true);

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
        jLayeredPane1.add(btSave);
        btSave.setBounds(130, 210, 100, 25);

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel);
        btCancel.setBounds(240, 210, 100, 25);

        jLabel4.setText("Nama Mesin");
        jLayeredPane1.add(jLabel4);
        jLabel4.setBounds(20, 30, 90, 20);

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete);
        btDelete.setBounds(20, 210, 100, 25);

        nama.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                namaFocusLost(evt);
            }
        });
        jLayeredPane1.add(nama);
        nama.setBounds(130, 30, 110, 20);

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton13);
        jButton13.setBounds(240, 60, 30, 23);
        jLayeredPane1.add(tcode);
        tcode.setBounds(130, 60, 110, 20);

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tcode1);
        tcode1.setBounds(130, 90, 220, 20);

        jLabel6.setText("Lokasi Mesin");
        jLayeredPane1.add(jLabel6);
        jLabel6.setBounds(20, 60, 90, 20);

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel8.setText("Mesin");
        jPanel6.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        iNama1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iNama1KeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iNama1KeyTyped(evt);
            }
        });
        jPanel6.add(iNama1, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 30, 160, -1));

        btSearch1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btSearch1.setText("Search");
        btSearch1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSearch1ActionPerformed(evt);
            }
        });
        jPanel6.add(btSearch1, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 30, 100, -1));

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "No", "Nama Mesin", "Lokasi Mesin", "Group", "Produk"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable3.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable3MouseClicked(evt);
            }
        });
        jScrollPane1.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(0).setMinWidth(0);
            jTable3.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(1).setMinWidth(50);
            jTable3.getColumnModel().getColumn(1).setMaxWidth(50);
            jTable3.getColumnModel().getColumn(4).setMinWidth(0);
            jTable3.getColumnModel().getColumn(4).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(5).setMinWidth(0);
            jTable3.getColumnModel().getColumn(5).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel1Layout = new javax.swing.GroupLayout(jPanel1);
        jPanel1.setLayout(jPanel1Layout);
        jPanel1Layout.setHorizontalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 363, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 591, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel1Layout.setVerticalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jLayeredPane1)
                    .addGroup(jPanel1Layout.createSequentialGroup()
                        .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 69, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 367, Short.MAX_VALUE)))
                .addContainerGap())
        );

        jTabbedPane1.addTab("Mesin", jPanel1);

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null}
            },
            new String [] {
                "id", "Id Mesin", "Mesin", "Group", "Produk"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                true, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jScrollPane2.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
        }

        jLabel5.setText("Produk Link Tagihan");

        tproduknama.setEditable(false);
        tproduknama.setBackground(new java.awt.Color(153, 153, 255));
        tproduknama.setForeground(new java.awt.Color(255, 255, 255));

        tproduk.setEditable(false);
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

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });

        tprodukgroup.setEditable(false);
        tprodukgroup.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tprodukgroupFocusLost(evt);
            }
        });
        tprodukgroup.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tprodukgroupKeyPressed(evt);
            }
        });

        jLabel7.setText("Mesin");

        mesin.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));

        btSave1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave1.setText("Add");
        btSave1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSave1ActionPerformed(evt);
            }
        });
        btSave1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btSave1KeyPressed(evt);
            }
        });

        javax.swing.GroupLayout jPanel2Layout = new javax.swing.GroupLayout(jPanel2);
        jPanel2.setLayout(jPanel2Layout);
        jPanel2Layout.setHorizontalGroup(
            jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel2Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(btSave1, javax.swing.GroupLayout.PREFERRED_SIZE, 100, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addGroup(jPanel2Layout.createSequentialGroup()
                        .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jLabel5, javax.swing.GroupLayout.PREFERRED_SIZE, 100, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(jLabel7, javax.swing.GroupLayout.PREFERRED_SIZE, 100, javax.swing.GroupLayout.PREFERRED_SIZE))
                        .addGap(59, 59, 59)
                        .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING, false)
                            .addGroup(jPanel2Layout.createSequentialGroup()
                                .addComponent(tproduk, javax.swing.GroupLayout.PREFERRED_SIZE, 110, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addGap(0, 0, 0)
                                .addComponent(jButton11, javax.swing.GroupLayout.PREFERRED_SIZE, 30, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addGap(10, 10, 10)
                                .addComponent(tprodukgroup, javax.swing.GroupLayout.PREFERRED_SIZE, 70, javax.swing.GroupLayout.PREFERRED_SIZE))
                            .addComponent(tproduknama)
                            .addComponent(mesin, 0, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))))
                .addGap(18, 18, 18)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 563, Short.MAX_VALUE)
                .addContainerGap())
        );
        jPanel2Layout.setVerticalGroup(
            jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel2Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 442, Short.MAX_VALUE)
                    .addGroup(jPanel2Layout.createSequentialGroup()
                        .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jLabel5, javax.swing.GroupLayout.PREFERRED_SIZE, 20, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(tproduk, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(jButton11)
                            .addComponent(tprodukgroup, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                        .addGap(7, 7, 7)
                        .addComponent(tproduknama, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(12, 12, 12)
                        .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                            .addComponent(jLabel7, javax.swing.GroupLayout.PREFERRED_SIZE, 20, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(mesin, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                        .addGap(18, 18, 18)
                        .addComponent(btSave1)
                        .addGap(0, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );

        jTabbedPane1.addTab("Invoice Mesin", jPanel2);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 289, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE))
                    .addGroup(layout.createSequentialGroup()
                        .addContainerGap()
                        .addComponent(jTabbedPane1)))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(18, 18, 18)
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

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
        loadData();
            clear();
            LastCode();
            deff();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            loadEdit();
        }// TODO add your handling code here:
    }//GEN-LAST:event_jTable3MouseClicked

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode() == 10) btSave.doClick();// TODO add your handling code here:
    }//GEN-LAST:event_btSaveKeyPressed

    private void btSearch1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSearch1ActionPerformed
        loadData();
    }//GEN-LAST:event_btSearch1ActionPerformed

    private void iNama1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNama1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_iNama1KeyTyped

    private void iNama1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNama1KeyPressed
        if(evt.getKeyCode() == 10){
            loadData();
        }
    }//GEN-LAST:event_iNama1KeyPressed

    private void abigFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_abigFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_abigFocusLost

    private void abigKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_abigKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_abigKeyPressed

    private void namaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_namaFocusLost
        nama.setText(nama.getText().toUpperCase());
    }//GEN-LAST:event_namaFocusLost

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        sql = "select * from mproduk where cproduct = '"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tprodukgroup.setText(rs.getString("cgroup"));
            tproduknama.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed

    }//GEN-LAST:event_tprodukKeyPressed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tproduknama.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void tprodukgroupFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukgroupFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tprodukgroupFocusLost

    private void tprodukgroupKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukgroupKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tprodukgroupKeyPressed

    private void btSave1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSave1ActionPerformed
        saveLink();
    }//GEN-LAST:event_btSave1ActionPerformed

    private void btSave1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSave1KeyPressed
        
    }//GEN-LAST:event_btSave1KeyPressed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount() == 2) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) {
                int baris = jTable1.getSelectedRow();
                deletelink(jTable1.getValueAt(baris, 0).toString());
            }
        }
    }//GEN-LAST:event_jTable1MouseClicked


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField abig;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btSave1;
    private javax.swing.JButton btSearch1;
    private javax.swing.JTextField iNama1;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton13;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable3;
    private javax.swing.JComboBox mesin;
    private javax.swing.JTextField nama;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tproduknama;
    // End of variables declaration//GEN-END:variables
}
