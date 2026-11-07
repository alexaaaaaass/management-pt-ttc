/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Tools;

import Control.aesEncrypt;
import DialogPublicTP.DialogKaryawan;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
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
public class UserAkses extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    String ID;
    Date tanggal=null;
    int nomor=0;
    public UserAkses() {
        initComponents();
        deff();
        loadUserLogin();
        loadMenu();
    }

    private void deff(){
        rfa.empty(keyssDef,tkaryawan,tkaryawan1,username,pass);
        btSave.setText("Save");
        btDelete.setVisible(false);
        ID="";
        jCheckBox1.setSelected(false);
        jCheckBox2.setSelected(false);
        jCheckBox3.setSelected(false);
        jCheckBox5.setSelected(false);
        
    }
    private void loadUserLogin(){
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        sql = "select * from UserLogin";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("cuser"),"***",
                    rs.getString("cname"),rs.getString("nik"),
                    rs.getDate("ServerDate"),rs.getDate("UpdateDate"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(UserAkses.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadMenu(){
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        sql = "select * from MMenu order by name";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("name"),false,false,false,false
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(UserAkses.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadEdit(String id){
        btSave.setText("Edit");
        btDelete.setVisible(true);
        rs = con.queryResult("select * from UserLogin where id = '"+id+"'");
        try {
            while(rs.next()){
                ID = rs.getString("id").toString();
                username.setText(rs.getString("cuser").toString());
                pass.setText(rfa.decrypt_rfa(1,rs.getString("cpass")));
                tkaryawan.setText(rs.getString("nik"));
                tkaryawan1.setText(rs.getString("cname"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(UserAkses.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        rs = con.queryResult("select * from UserLoginKeys where id_user = '"+ID+"'");
        try {
            while(rs.next()){
                for(int i=0; i<jTable1.getRowCount(); i++) {
                    if(jTable1.getValueAt(i,0).toString().equals(rs.getString("idform").toString())) {
                        String akses = null;
                        try {
                            akses = rfa.decrypt_rfa(1, rs.getString("keys"));
                        } catch (Exception ex) {
                            Logger.getLogger(UserAkses.class.getName()).log(Level.SEVERE, null, ex);
                        }
                        String[] parts = akses.split(";");
                        
                        jTable1.setValueAt(false, i, 2);
                        jTable1.setValueAt(false, i, 3);
                        jTable1.setValueAt(false, i, 4);
                        jTable1.setValueAt(false, i, 5);
                        
                        if(parts[0].equals("1")) {
                            jTable1.setValueAt(true, i, 2);
                        } 
                        if(parts[1].equals("1")) {
                            jTable1.setValueAt(true, i, 3);
                        }
                        if(parts[2].equals("1")) {
                            jTable1.setValueAt(true, i, 4);
                        }
                        if(parts[3].equals("1")) {
                            jTable1.setValueAt(true, i, 5);
                        }
                        break;
                    }
                }
            }
        } catch (SQLException ex) {
            Logger.getLogger(UserAkses.class.getName()).log(Level.SEVERE, null, ex);
        }
     
        
    }
    private Boolean berforeSave(){
        Boolean status = true;
        if(btSave.getText().equalsIgnoreCase("Save")) {
            sql = "select * from UserLogin where cuser='"+username.getText()+"'  ";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    status = false;
                    JOptionPane.showMessageDialog(this, "Username sudah ada", "Informastion", JOptionPane.ERROR_MESSAGE);
                    return false;
                }
            } catch (SQLException ex) {
                Logger.getLogger(UserAkses.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
        
        if(tkaryawan.getText().isEmpty()){
            JOptionPane.showMessageDialog(this, "Karyawan wajib di isi", "Informastion", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(username.getText().isEmpty()){
            JOptionPane.showMessageDialog(this, "Username wajib di isi", "Informastion", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(pass.getText().isEmpty()){
            JOptionPane.showMessageDialog(this, "Password wajib di isi", "Informastion", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        
        return status;
    }
    private Boolean saveUser(){
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cuser",username.getText());
        listObj.put("nik",tkaryawan.getText());
        listObj.put("cname",tkaryawan1.getText());
        listObj.put("cpass",rfa.encrypt_rfa(1, pass.getText()));
        
        if(btSave.getText().equalsIgnoreCase("Save")) {
            sql=con.insertValue("UserLogin", listObj);
        } else {
            listObj.put("UpdateDate","GETDATE()");
            sql=con.updateValue("UserLogin", listObj);
            sql+=" where id = "+ID;
        }
        
        if(con.queryUpdate(sql)>0) {
            sql = "select * from UserLogin where cuser='"+username.getText()+"' and nik='"+tkaryawan.getText()+"' ";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    ID = rs.getString("id");
                }
            } catch (SQLException ex) {
                Logger.getLogger(UserAkses.class.getName()).log(Level.SEVERE, null, ex);
            }
            return true;
        } else {
            return false;
        }
    }
    private Boolean saveAkses(){
        Boolean status = false;
        con.queryUpdate("delete from UserLoginKeys where id_user="+ID);
        String sqlSave="";
        for(int i=0; i<jTable1.getRowCount();i++){
            String a1="0",a2="0",a3="0",a4="0";
            if(jTable1.getValueAt(i, 2).toString().equalsIgnoreCase("true")) {
                a1="1";
            }
            if(jTable1.getValueAt(i, 3).toString().equalsIgnoreCase("true")) {
                a2="1";
            }
            if(jTable1.getValueAt(i, 4).toString().equalsIgnoreCase("true")) {
                a3="1";
            }
            if(jTable1.getValueAt(i, 5).toString().equalsIgnoreCase("true")) {
                a4="1";
            }
            
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("id_user",ID);
            listObj.put("keys",rfa.encrypt_rfa(1, a1+";"+a2+";"+a3+";"+a4));
            listObj.put("idform",jTable1.getValueAt(i,0).toString());
            sql=con.insertValue("UserLoginKeys", listObj);
            System.out.println(sql);
            sqlSave+=sql+"\n;";
           
        }
         con.queryUpdate(sqlSave);
        return status;
    }
    
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {
        java.awt.GridBagConstraints gridBagConstraints;

        jPanel1 = new javax.swing.JPanel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel2 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        username = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        jLabel7 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jButton12 = new javax.swing.JButton();
        tkaryawan = new javax.swing.JTextField();
        tkaryawan1 = new javax.swing.JTextField();
        pass = new javax.swing.JPasswordField();
        jPanel3 = new javax.swing.JPanel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jPanel4 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jTextField2 = new javax.swing.JTextField();
        jCheckBox2 = new javax.swing.JCheckBox();
        jCheckBox3 = new javax.swing.JCheckBox();
        jCheckBox1 = new javax.swing.JCheckBox();
        jCheckBox5 = new javax.swing.JCheckBox();
        btDelete = new javax.swing.JButton();
        btcancel = new javax.swing.JButton();
        btSave = new javax.swing.JButton();

        setLayout(new java.awt.GridBagLayout());

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel2.setText("IDK");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 20, -1, 20));
        jPanel2.add(username, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 80, 160, -1));

        jLabel6.setText("Username");
        jPanel2.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 80, -1, 20));

        jLabel7.setText("Nama Karyawan");
        jPanel2.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 50, -1, 20));

        jLabel8.setText("Password");
        jPanel2.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 110, -1, 20));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "Username", "Password", "Nama User", "User ID", "Create Date", "Update Date"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
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

        jPanel2.add(jScrollPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 150, 600, 250));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 20, 30, -1));

        tkaryawan.setEditable(false);
        tkaryawan.setBackground(new java.awt.Color(153, 153, 255));
        tkaryawan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkaryawanFocusLost(evt);
            }
        });
        tkaryawan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkaryawanKeyPressed(evt);
            }
        });
        jPanel2.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 20, 130, -1));

        tkaryawan1.setEditable(false);
        tkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        tkaryawan1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel2.add(tkaryawan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 50, 160, -1));
        jPanel2.add(pass, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 110, 160, -1));

        jTabbedPane1.addTab("Add User", jPanel2);

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "Nama Menu", "Akses", "Input", "Update", "Delete"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Boolean.class, java.lang.Boolean.class, java.lang.Boolean.class, java.lang.Boolean.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, true, true, true, true
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(2).setMinWidth(90);
            jTable1.getColumnModel().getColumn(2).setMaxWidth(90);
            jTable1.getColumnModel().getColumn(3).setMinWidth(90);
            jTable1.getColumnModel().getColumn(3).setMaxWidth(90);
            jTable1.getColumnModel().getColumn(4).setMinWidth(90);
            jTable1.getColumnModel().getColumn(4).setMaxWidth(90);
            jTable1.getColumnModel().getColumn(5).setMinWidth(90);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(90);
        }

        jPanel3.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 620, 330));

        jPanel4.setBackground(new java.awt.Color(204, 255, 255));
        jPanel4.setBorder(javax.swing.BorderFactory.createLineBorder(new java.awt.Color(0, 0, 0)));
        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("Menu");
        jPanel4.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 15, -1, 20));

        jTextField2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField2ActionPerformed(evt);
            }
        });
        jPanel4.add(jTextField2, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 15, 170, -1));

        jCheckBox2.setText("Akses");
        jCheckBox2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jCheckBox2ActionPerformed(evt);
            }
        });
        jPanel4.add(jCheckBox2, new org.netbeans.lib.awtextra.AbsoluteConstraints(320, 10, -1, 30));

        jCheckBox3.setText("Input");
        jCheckBox3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jCheckBox3ActionPerformed(evt);
            }
        });
        jPanel4.add(jCheckBox3, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 10, -1, 30));

        jCheckBox1.setText("Update");
        jCheckBox1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jCheckBox1ActionPerformed(evt);
            }
        });
        jPanel4.add(jCheckBox1, new org.netbeans.lib.awtextra.AbsoluteConstraints(460, 10, -1, 30));

        jCheckBox5.setText("Delete");
        jCheckBox5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jCheckBox5ActionPerformed(evt);
            }
        });
        jPanel4.add(jCheckBox5, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 10, -1, 30));

        jPanel3.add(jPanel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 360, 620, 50));

        jTabbedPane1.addTab("User Akses", jPanel3);

        jPanel1.add(jTabbedPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 670, 450));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 470, 90, -1));

        btcancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btcancel.setText("Cancel");
        btcancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btcancelActionPerformed(evt);
            }
        });
        jPanel1.add(btcancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(590, 470, 90, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(490, 470, 90, -1));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.ipadx = 21;
        gridBagConstraints.ipady = 15;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        gridBagConstraints.insets = new java.awt.Insets(10, 10, 21, 14);
        add(jPanel1, gridBagConstraints);
    }// </editor-fold>//GEN-END:initComponents

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan melakukan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0){
            if(berforeSave()){
                saveUser();
                saveAkses();
                JOptionPane.showMessageDialog(this, "Data Berhasil Disimpan", "Informastion", JOptionPane.INFORMATION_MESSAGE);

                btcancel.doClick();
            }
            
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btcancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btcancelActionPerformed
        deff();
        loadUserLogin();
        loadMenu();
    }//GEN-LAST:event_btcancelActionPerformed

    private void jTextField2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField2ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField2ActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            loadEdit(jTable2.getValueAt(jTable2.getSelectedRow(), 0).toString());
        }// TODO add your handling code here:
    }//GEN-LAST:event_jTable2MouseClicked

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        
    }//GEN-LAST:event_btDeleteActionPerformed

    private void jCheckBox2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jCheckBox2ActionPerformed
        for(int i=0; i<jTable1.getRowCount(); i++) {

            if(!jCheckBox2.isSelected()) {
                jTable1.setValueAt(false, i, 2);
            } else {
                jTable1.setValueAt(true, i, 2);
            }
        }
    }//GEN-LAST:event_jCheckBox2ActionPerformed

    private void jCheckBox3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jCheckBox3ActionPerformed
       for(int i=0; i<jTable1.getRowCount(); i++) {

            if(!jCheckBox3.isSelected()) {
                jTable1.setValueAt(false, i, 3);
            } else {
                jTable1.setValueAt(true, i, 3);
            }
        }
    }//GEN-LAST:event_jCheckBox3ActionPerformed

    private void jCheckBox1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jCheckBox1ActionPerformed
       for(int i=0; i<jTable1.getRowCount(); i++) {

            if(!jCheckBox1.isSelected()) {
                jTable1.setValueAt(false, i, 4);
            } else {
                jTable1.setValueAt(true, i, 4);
            }
        }
    }//GEN-LAST:event_jCheckBox1ActionPerformed

    private void jCheckBox5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jCheckBox5ActionPerformed
        for(int i=0; i<jTable1.getRowCount(); i++) {

            if(!jCheckBox5.isSelected()) {
                jTable1.setValueAt(false, i, 5);
            } else {
                jTable1.setValueAt(true, i, 5);
            }
        }
    }//GEN-LAST:event_jCheckBox5ActionPerformed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawan.setText(a.Kode);
            tkaryawan1.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void tkaryawanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawanFocusLost
        sql = "select * from mkaryawan where nik = '"+tkaryawan.getText()+"'  ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tkaryawan1.setText(rs.getString("cname"));

            }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tkaryawanFocusLost

    private void tkaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawanKeyPressed

    }//GEN-LAST:event_tkaryawanKeyPressed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btcancel;
    private javax.swing.JButton jButton12;
    private javax.swing.JCheckBox jCheckBox1;
    private javax.swing.JCheckBox jCheckBox2;
    private javax.swing.JCheckBox jCheckBox3;
    private javax.swing.JCheckBox jCheckBox5;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField jTextField2;
    private javax.swing.JPasswordField pass;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawan1;
    private javax.swing.JTextField username;
    // End of variables declaration//GEN-END:variables
}
