/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Master;

import DialogPublicTP.DialogGudang;
import Main.DefaultProgram;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
import java.awt.Color;
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
public class MStepProduksi extends javax.swing.JPanel {
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER PRODUKSI STEP";
    
    ArrayList<String> idmesin;
    
    boolean row_active = false;
    int index;
    DefaultTableModel modelrow;
    
    public MStepProduksi() {
        initComponents();
        LastCode();
        deff();
        loadData();
        
    }
    
    private void deff(){
        btDelete.setVisible(false);
        btSave.setText("Save");
        id = "";
        cbFinal.setSelected(false);
        
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
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
        mesin.setSelectedIndex(0);
        
        LastCode();
    }
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,aNama);
        rfa.empty(MenuAdmin.keyssDef,tket);
    }
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(kode,3))) as lastno from MStepProduction");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        aKode.setText("PROD"+rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql = "select * from MStepProduction where ccode='"+tcode.getText()+"' and cname like '%"+iNama1.getText()+"%'  "
                + "order by kode ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("kode"),
                    rs.getString("cname"),
                    rs.getString("cket"),
                    rs.getString("idmesin"),
                    rs.getString("cfinal_process"),
                    rs.getString("urut"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void loadEdit(){
        sql = "select a.*,c.cname as mesin  from MStepProduction a "
                + "left join MMesin c on a.idmesin=c.id "
                + " where a.id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                aKode.setText(rs.getString("kode"));
                aNama.setText(rs.getString("cname"));
                tket.setText(rs.getString("cket"));
                turut.setText(rs.getString("urut"));
                
                mesin.setSelectedItem(rs.getString("mesin"));
                
                cbFinal.setSelected(false);
                if(rs.getInt("cfinal_process") == 1) cbFinal.setSelected(true);
                
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
        listObj.put("kode",aKode.getText());     
        listObj.put("cname",aNama.getText());    
        listObj.put("cket",tket.getText());    
        listObj.put("idmesin",idmesin.get(mesin.getSelectedIndex()));    
        
        if(cbFinal.isSelected()) {
            listObj.put("cfinal_process","1");
        }  else {
            listObj.put("cfinal_process","0");
        }
        
        listObj.put("cusername",Login.user);  
        listObj.put("urut",turut.getText()); 
        
        sql=con.insertValue("MStepProduction", listObj);
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
        listObj.put("kode",aKode.getText());     
        listObj.put("cname",aNama.getText());    
        listObj.put("cket",tket.getText());    
        listObj.put("idmesin",idmesin.get(mesin.getSelectedIndex()));    
        
        if(cbFinal.isSelected()) {
            listObj.put("cfinal_process","1");
        }  else {
            listObj.put("cfinal_process","0");
        }
        
        listObj.put("cusername",Login.user);  
        listObj.put("urut",turut.getText()); 
        
        sql=con.updateValue("MStepProduction", listObj);
        sql+=" where id = '"+id+"' ";
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
        sql="delete from  MStepProduction  where id = '"+id+"' ";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            LastCode();
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

        jPanel4 = new javax.swing.JPanel();
        jLabel13 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel4 = new javax.swing.JLabel();
        jScrollPane2 = new javax.swing.JScrollPane();
        tket = new javax.swing.JTextArea();
        aKode = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        aNama = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        jLabel6 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        tcode1 = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        jLabel11 = new javax.swing.JLabel();
        mesin = new javax.swing.JComboBox();
        jLabel15 = new javax.swing.JLabel();
        cbFinal = new javax.swing.JCheckBox();
        jLabel16 = new javax.swing.JLabel();
        turut = new javax.swing.JTextField();
        jPanel6 = new javax.swing.JPanel();
        jLabel8 = new javax.swing.JLabel();
        iNama1 = new javax.swing.JTextField();
        btSearch1 = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jButton1 = new javax.swing.JButton();
        jButton2 = new javax.swing.JButton();
        jButton3 = new javax.swing.JButton();

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel4.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel14.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel14.setText("Master Step Produksi");
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
        btSave.setBounds(130, 300, 100, 25);

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel);
        btCancel.setBounds(240, 300, 100, 25);

        jLabel4.setText("Nama Produksi");
        jLayeredPane1.add(jLabel4);
        jLabel4.setBounds(20, 80, 90, 20);

        tket.setColumns(20);
        tket.setRows(5);
        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        tket.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketKeyPressed(evt);
            }
        });
        jScrollPane2.setViewportView(tket);

        jLayeredPane1.add(jScrollPane2);
        jScrollPane2.setBounds(120, 110, 220, 60);

        aKode.setEditable(false);
        jLayeredPane1.add(aKode);
        aKode.setBounds(120, 50, 220, 20);

        jLabel5.setText("Kode Produksi");
        jLayeredPane1.add(jLabel5);
        jLabel5.setBounds(20, 50, 90, 20);

        aNama.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aNamaFocusLost(evt);
            }
        });
        aNama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aNamaKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aNama);
        aNama.setBounds(120, 80, 220, 20);

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete);
        btDelete.setBounds(20, 300, 100, 25);

        jLabel6.setText("Keterangan");
        jLayeredPane1.add(jLabel6);
        jLabel6.setBounds(20, 110, 90, 20);

        jLabel3.setText("Gudang");
        jLayeredPane1.add(jLabel3);
        jLabel3.setBounds(20, 20, 37, 14);

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jLayeredPane1.add(tcode);
        tcode.setBounds(120, 20, 70, 20);

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        jLayeredPane1.add(tcode1);
        tcode1.setBounds(220, 20, 130, 20);

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton12);
        jButton12.setBounds(190, 20, 30, 23);

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Jenis Mesin");
        jLayeredPane1.add(jLabel11);
        jLabel11.setBounds(20, 180, 100, 15);

        mesin.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jLayeredPane1.add(mesin);
        mesin.setBounds(120, 180, 220, 20);

        jLabel15.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel15.setText("Urut");
        jLayeredPane1.add(jLabel15);
        jLabel15.setBounds(20, 250, 100, 20);

        cbFinal.setBackground(new java.awt.Color(204, 204, 204));
        cbFinal.setText("Ya");
        jLayeredPane1.add(cbFinal);
        cbFinal.setBounds(120, 210, 220, 23);

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Proses Final");
        jLayeredPane1.add(jLabel16);
        jLabel16.setBounds(20, 210, 100, 20);

        turut.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                turutKeyPressed(evt);
            }
        });
        jLayeredPane1.add(turut);
        turut.setBounds(120, 250, 130, 20);

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel8.setText("Produksi");
        jPanel6.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        iNama1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iNama1KeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iNama1KeyTyped(evt);
            }
        });
        jPanel6.add(iNama1, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 30, 260, -1));

        btSearch1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btSearch1.setText("Search");
        btSearch1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSearch1ActionPerformed(evt);
            }
        });
        jPanel6.add(btSearch1, new org.netbeans.lib.awtextra.AbsoluteConstraints(460, 30, 100, -1));

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "No", "Kode Produksi", "Name", "Keterangan", "Mesin", "Proses Final", "Urut"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false
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
        }

        jButton1.setText("Down");
        jButton1.setMargin(new java.awt.Insets(2, 2, 2, 2));
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });

        jButton2.setText("Up");
        jButton2.setMargin(new java.awt.Insets(2, 2, 2, 2));
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });

        jButton3.setText("Update");
        jButton3.setMargin(new java.awt.Insets(2, 2, 2, 2));
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 289, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addGroup(layout.createSequentialGroup()
                        .addContainerGap()
                        .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 363, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.DEFAULT_SIZE, 869, Short.MAX_VALUE)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jScrollPane1)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jButton2, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.PREFERRED_SIZE, 52, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(jButton1, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.PREFERRED_SIZE, 52, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(jButton3, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.PREFERRED_SIZE, 52, javax.swing.GroupLayout.PREFERRED_SIZE))))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jLayeredPane1))
                    .addGroup(layout.createSequentialGroup()
                        .addGap(36, 36, 36)
                        .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 68, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 403, Short.MAX_VALUE)
                            .addGroup(layout.createSequentialGroup()
                                .addComponent(jButton2, javax.swing.GroupLayout.PREFERRED_SIZE, 69, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                                .addComponent(jButton1, javax.swing.GroupLayout.PREFERRED_SIZE, 69, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addGap(18, 18, 18)
                                .addComponent(jButton3, javax.swing.GroupLayout.PREFERRED_SIZE, 69, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addGap(0, 0, Short.MAX_VALUE)))))
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
        clear();
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

    private void aNamaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aNamaFocusLost
        aNama.setText(aNama.getText().toUpperCase());
    }//GEN-LAST:event_aNamaFocusLost

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void aNamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aNamaKeyPressed
        if(evt.getKeyCode() == 10) tket.requestFocus();
    }//GEN-LAST:event_aNamaKeyPressed

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        if(evt.getKeyCode() == 10) turut.requestFocus();
    }//GEN-LAST:event_tketKeyPressed

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

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tcode1.setText(rs.getString("cname"));
            }
        } catch (Exception e){}
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!a.ccode.equals("")){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
            loadData();
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void turutKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_turutKeyPressed
        rfa.EnterNextFocus(evt, btSave);        // TODO add your handling code here:
    }//GEN-LAST:event_turutKeyPressed

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jButton3ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        if(!row_active) {
            modelrow = (DefaultTableModel) jTable3.getModel();
            row_active = true;
        }
        index = jTable3.getSelectedRow();
        if(index>0) {
            modelrow.moveRow(index, index, index-1);
            jTable3.setRowSelectionInterval(index-1, index-1);
            jTable3.setSelectionBackground(Color.LIGHT_GRAY);
        }
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        if(!row_active) {
            modelrow = (DefaultTableModel) jTable3.getModel();
            row_active = true;
        }
        index = jTable3.getSelectedRow();
        if(index<modelrow.getRowCount()-1) {
            modelrow.moveRow(index, index, index+1);
            jTable3.setRowSelectionInterval(index+1, index+1);
            jTable3.setSelectionBackground(Color.LIGHT_GRAY);
        }
    }//GEN-LAST:event_jButton1ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField aKode;
    private javax.swing.JTextField aNama;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btSearch1;
    private javax.swing.JCheckBox cbFinal;
    private javax.swing.JTextField iNama1;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton3;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTable jTable3;
    private javax.swing.JComboBox mesin;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextField turut;
    // End of variables declaration//GEN-END:variables
}
