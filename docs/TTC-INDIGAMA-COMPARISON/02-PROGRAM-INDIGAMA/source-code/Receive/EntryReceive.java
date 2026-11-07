/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Receive;

import static Main.DefaultProgram.deffProgram;
import Main.Login;
import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class EntryReceive extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    String gudang="";
    int barisEdit=-1;
    String akses="RECEIVE ANTAR GUDANG";
    String idKas="D1";
    ArrayList<String> deleteRow;
    public EntryReceive() {
        initComponents();
        jLayeredPane1.setVisible(false);
        gudang = deffProgram.get("ccode");
    }

    private void check(){
        jLayeredPane1.setVisible(false);
        sql ="select * from TranReceive where cnbkt = '"+tbukti.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                jLabel2.setText("Info : Bukti Kirim ("+rs.getString("cnbkt_kirim")+") dari gudang ("+rs.getString("ccodedesc"));
                jLayeredPane1.setVisible(true);
                tbox.requestFocus();
            }
        } catch (SQLException ex) {
            Logger.getLogger(EntryReceive.class.getName()).log(Level.SEVERE, null, ex);
        }
        loadTotal();
    }
    private void loadData(){
        sql="select b.* \n" +
            "from TranReceive a join TranReceiveFile b on a.cnbkt_kirim=b.cnbkt and a.ccodedesc=b.ccode\n" +
            "where a.cnbkt = '"+tbukti.getText()+"' and cbox = '"+tbox.getText()+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tunit.setText(rs.getString("csatuan"));
                tgroup.setText(rs.getString("cgroup"));
                tproduct.setText(rs.getString("cproduct"));
                tball.setText(rfa.formatDecimal3.format(rs.getDouble("cball")));
                tslop.setText(rfa.formatDecimal3.format(rs.getDouble("cslop")));
                tslopvalue.setText(rfa.formatDecimal3.format(rs.getDouble("cslop_value")));
                btSave.requestFocus();
            }
        } catch (SQLException ex) {
            Logger.getLogger(EntryReceive.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="select a.* \n" +
            "from TranReceiveD a " +
            "where a.cnbkt = '"+tbukti.getText()+"' and cboxscan = '"+tbox.getText()+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                JOptionPane.showMessageDialog(this,"No Box sudah ada", "Info", JOptionPane.ERROR_MESSAGE);
                clear();
            }
        } catch (SQLException ex) {
            Logger.getLogger(EntryReceive.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    private void loadTotal(){
        jLabel4.setText("Total Masuk : 0");
        sql="select count(id) as total from TranReceiveD where cnbkt = '"+tbukti.getText()+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                jLabel4.setText("Total Masuk : "+rs.getString("total"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(EntryReceive.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    private void clear(){
        rfa.empty(keyssDef, tunit,tgroup,tproduct,tslop,tslopvalue,tbox);
        tbox.requestFocus();
    }
    
    private void save(){
        String[] data_parts = tbox.getText().split("@");
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText());
        listObj.put("ccode",gudang);
        listObj.put("cnbkt_box",data_parts[0]);
        listObj.put("cbox",data_parts[1]);
        listObj.put("cboxscan",tbox.getText());
        listObj.put("dtran",rfa.DateString(keyssDef, new Date(), "yyyy-MM-dd"));
        listObj.put("cgroup",tgroup.getText());
        listObj.put("cproduct",tproduct.getText());
        listObj.put("csatuan",tunit.getText());
        listObj.put("cslop",tslop.getText());
        listObj.put("cslop_value",tslopvalue.getText());
        listObj.put("cball",tball.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("TranReceiveD", listObj);
        if(con.queryUpdate(sql)>0) {
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clear();
            loadTotal();
        } else {
            JOptionPane.showMessageDialog(this,"Header gagal di simpan.", "Save", JOptionPane.ERROR_MESSAGE);
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
        java.awt.GridBagConstraints gridBagConstraints;

        jPanel1 = new javax.swing.JPanel();
        tbukti = new javax.swing.JTextField();
        jLabel2 = new javax.swing.JLabel();
        jButton1 = new javax.swing.JButton();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel1 = new javax.swing.JLabel();
        jTextField1 = new javax.swing.JTextField();
        tbox = new javax.swing.JTextField();
        jLabel4 = new javax.swing.JLabel();
        tproduct = new javax.swing.JTextField();
        tslop = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        btSave = new javax.swing.JButton();
        tgroup = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        tslopvalue = new javax.swing.JTextField();
        jLabel8 = new javax.swing.JLabel();
        tunit = new javax.swing.JTextField();
        jLabel9 = new javax.swing.JLabel();
        tball = new javax.swing.JTextField();
        jLabel3 = new javax.swing.JLabel();

        setLayout(new java.awt.GridBagLayout());

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        tbukti.setFont(new java.awt.Font("Tahoma", 1, 24)); // NOI18N
        tbukti.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusGained(java.awt.event.FocusEvent evt) {
                tbuktiFocusGained(evt);
            }
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbuktiFocusLost(evt);
            }
        });
        tbukti.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbuktiKeyPressed(evt);
            }
        });
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 30, 570, 50));

        jLabel2.setFont(new java.awt.Font("Tahoma", 2, 11)); // NOI18N
        jLabel2.setText("Info");
        jPanel1.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 80, 570, 20));

        jButton1.setFont(new java.awt.Font("Tahoma", 0, 18)); // NOI18N
        jButton1.setText("Check");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jButton1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jButton1KeyPressed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(760, 30, -1, 50));

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 0, 24)); // NOI18N
        jLabel1.setText("Box");
        jLayeredPane1.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 40, 110, 50));

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(153, 153, 255));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField1.setForeground(new java.awt.Color(255, 255, 255));
        jTextField1.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField1.setText("ENTRY DATA BOX");
        jLayeredPane1.add(jTextField1, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 810, 30));

        tbox.setFont(new java.awt.Font("Tahoma", 1, 24)); // NOI18N
        tbox.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tboxFocusLost(evt);
            }
        });
        tbox.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tboxKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tbox, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 40, 640, 50));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 14)); // NOI18N
        jLabel4.setText("Total Masuk");
        jLayeredPane1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 340, 380, 30));

        tproduct.setEditable(false);
        tproduct.setBackground(new java.awt.Color(153, 153, 255));
        tproduct.setFont(new java.awt.Font("Tahoma", 1, 24)); // NOI18N
        tproduct.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tproduct, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 180, 290, 50));

        tslop.setEditable(false);
        tslop.setBackground(new java.awt.Color(153, 153, 255));
        tslop.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        tslop.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tslop, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 250, 90, 40));

        jLabel5.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel5.setText("Ball");
        jLayeredPane1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 250, 70, 40));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 24)); // NOI18N
        jLabel6.setText("Unit");
        jLayeredPane1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 110, 110, 50));

        btSave.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        btSave.setText("SAVE");
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
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(690, 310, 100, 50));

        tgroup.setEditable(false);
        tgroup.setBackground(new java.awt.Color(153, 153, 255));
        tgroup.setFont(new java.awt.Font("Tahoma", 1, 24)); // NOI18N
        tgroup.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tgroup, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 180, 180, 50));

        jLabel7.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel7.setText("Pack");
        jLayeredPane1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(470, 250, 70, 40));

        tslopvalue.setEditable(false);
        tslopvalue.setBackground(new java.awt.Color(153, 153, 255));
        tslopvalue.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        tslopvalue.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tslopvalue, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 250, 100, 40));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 24)); // NOI18N
        jLabel8.setText("Product");
        jLayeredPane1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 180, 110, 50));

        tunit.setEditable(false);
        tunit.setBackground(new java.awt.Color(153, 153, 255));
        tunit.setFont(new java.awt.Font("Tahoma", 1, 24)); // NOI18N
        tunit.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tunit, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 490, 50));

        jLabel9.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel9.setText("Slop");
        jLayeredPane1.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 250, 60, 40));

        tball.setEditable(false);
        tball.setBackground(new java.awt.Color(153, 153, 255));
        tball.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        tball.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tball, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 250, 90, 40));

        jPanel1.add(jLayeredPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 130, 810, 380));

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 24)); // NOI18N
        jLabel3.setText("Bukti");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 30, 70, 50));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.ipadx = 52;
        gridBagConstraints.ipady = 37;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        add(jPanel1, gridBagConstraints);
    }// </editor-fold>//GEN-END:initComponents

    private void tbuktiFocusGained(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbuktiFocusGained
       tbukti.setText("REC/"+rfa.DateString(keyssDef, new Date(),"yyMM/")+Login.subdefault+"-");
    }//GEN-LAST:event_tbuktiFocusGained

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        check();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void tbuktiKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktiKeyPressed
        if(evt.getKeyCode() == 10){
            check();
            
        }
    }//GEN-LAST:event_tbuktiKeyPressed

    private void tbuktiFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbuktiFocusLost
        
    }//GEN-LAST:event_tbuktiFocusLost

    private void tboxKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tboxKeyPressed
      if(evt.getKeyCode() == 10){
          loadData();
      }
    }//GEN-LAST:event_tboxKeyPressed

    private void jButton1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jButton1KeyPressed
        if(evt.getKeyCode() == 10){
            jButton1.doClick();
        }
    }//GEN-LAST:event_jButton1KeyPressed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        if(con.akses(akses, rfa.INSERT)) {
            save();
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode() == 10){
            btSave.doClick();
        }
    }//GEN-LAST:event_btSaveKeyPressed

    private void tboxFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tboxFocusLost
        sql="select a.* \n" +
            "from TranReceiveD a " +
            "where a.cnbkt = '"+tbukti.getText()+"' and cboxscan = '"+tbox.getText()+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                JOptionPane.showMessageDialog(this,"No Box sudah ada", "Info", JOptionPane.ERROR_MESSAGE);
                clear();
            }
        } catch (SQLException ex) {
            Logger.getLogger(EntryReceive.class.getName()).log(Level.SEVERE, null, ex);
        }
    }//GEN-LAST:event_tboxFocusLost


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton1;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JTextField tball;
    private javax.swing.JTextField tbox;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tgroup;
    private javax.swing.JTextField tproduct;
    private javax.swing.JTextField tslop;
    private javax.swing.JTextField tslopvalue;
    private javax.swing.JTextField tunit;
    // End of variables declaration//GEN-END:variables
}
