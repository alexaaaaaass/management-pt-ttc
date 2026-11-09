/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Master;

import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogSatuan;
import Main.DefaultProgram;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
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
public class MPriceBoronganTunj extends javax.swing.JPanel {
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String type="TUNJ";
    String akses="MASTER GAJI BORONGAN";
    
    ArrayList<String> idmesin;
    
    boolean row_active = false;
    int index;
    DefaultTableModel modelrow;
    
    public MPriceBoronganTunj() {
        initComponents(); 
        deff();
        loadData();
        
    }
    
    private void deff(){
        btDelete.setVisible(false);
        btSave.setText("Save");
        jDate1.setDate(new Date());
        id = ""; 
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
           
    }
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,tnilaihadir,tnilailain,tnilaimakan); 
    }
     
    private void loadData(){
        sql = "select * from MPriceBoronganTunj where ccode='"+tcode.getText()+"' and ctype ='"+type+"'  "
                + "order by nperiod ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++), 
                    rfa.DateString(keyssDef, rs.getDate("nperiod"), "yyyy-MM-dd"),
                    rs.getDouble("nprice_hadir"),
                    rs.getDouble("nprice_makan"),
                    rs.getDouble("nprice_lain"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void loadEdit(){
        sql = "select* from MPriceBoronganTunj " 
                + " where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                jDate1.setDate(rs.getDate("nperiod")); 
                tnilaihadir.setText(rfa.formatDecimal3.format(rs.getDouble("nprice_hadir"))); 
                tnilaimakan.setText(rfa.formatDecimal3.format(rs.getDouble("nprice_makan"))); 
                tnilailain.setText(rfa.formatDecimal3.format(rs.getDouble("nprice_lain"))); 
                 
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
        listObj.put("ctype",type);               
        listObj.put("nprice_hadir",rfa.formatDecimal3.format(Double.valueOf(tnilaihadir.getText())));        
        listObj.put("nprice_makan",rfa.formatDecimal3.format(Double.valueOf(tnilaimakan.getText())));        
        listObj.put("nprice_lain",rfa.formatDecimal3.format(Double.valueOf(tnilailain.getText())));        
        listObj.put("nperiod",rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd"));  
        
        if(id.equalsIgnoreCase("")) {
            listObj.put("cusername",Login.user);   
            sql=con.insertValue("MPriceBoronganTunj", listObj);
        } else {
            listObj.put("cusername_update",Login.user);   
            listObj.put("updatedate",rfa.DateString(keyssDef, new Date(), "yyyy-MM-dd hh:mm:ss"));   
            sql=con.updateValue("MPriceBoronganTunj", listObj);
            sql+=" where id="+id;
        }
        
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear(); 
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
   
    private void delete(){
        sql="delete from  MPriceBoronganTunj  where id = '"+id+"' ";
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

        jPanel4 = new javax.swing.JPanel();
        jLabel13 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        btDelete = new javax.swing.JButton();
        tcode = new javax.swing.JTextField();
        tcode1 = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        jLabel24 = new javax.swing.JLabel();
        tnilaihadir = new javax.swing.JTextField();
        jDate1 = new com.toedter.calendar.JDateChooser();
        jLabel27 = new javax.swing.JLabel();
        jLabel28 = new javax.swing.JLabel();
        tnilaimakan = new javax.swing.JTextField();
        jLabel29 = new javax.swing.JLabel();
        jLabel30 = new javax.swing.JLabel();
        tnilailain = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        btSearch1 = new javax.swing.JButton();

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel4.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel14.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel14.setText("Price Tunjangan Borongan");
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
        btSave.setBounds(130, 200, 100, 25);

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel);
        btCancel.setBounds(240, 200, 100, 25);

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete);
        btDelete.setBounds(20, 200, 100, 25);

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jLayeredPane1.add(tcode);
        tcode.setBounds(350, 20, 0, 20);

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        jLayeredPane1.add(tcode1);
        tcode1.setBounds(140, 20, 180, 20);

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton12);
        jButton12.setBounds(320, 20, 30, 23);

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Tunj. Kehadiran");
        jLayeredPane1.add(jLabel24);
        jLabel24.setBounds(20, 90, 100, 20);
        jLayeredPane1.add(tnilaihadir);
        tnilaihadir.setBounds(140, 90, 120, 20);

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
        jLayeredPane1.add(jDate1);
        jDate1.setBounds(140, 50, 150, 20);

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("Gudang");
        jLayeredPane1.add(jLabel27);
        jLabel27.setBounds(20, 20, 50, 20);

        jLabel28.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel28.setText("Periode Gaji");
        jLayeredPane1.add(jLabel28);
        jLabel28.setBounds(20, 50, 90, 20);
        jLayeredPane1.add(tnilaimakan);
        tnilaimakan.setBounds(140, 120, 120, 20);

        jLabel29.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel29.setText("Tunj. UMakan");
        jLayeredPane1.add(jLabel29);
        jLabel29.setBounds(20, 120, 100, 20);

        jLabel30.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel30.setText("Tunj. Lain");
        jLayeredPane1.add(jLabel30);
        jLabel30.setBounds(20, 150, 100, 20);
        jLayeredPane1.add(tnilailain);
        tnilailain.setBounds(140, 150, 120, 20);

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "No", "Periode Mulai", "Tunj. Kehadiran", "Tunj. Makan", "Tunj. Lain"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false
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
        jScrollPane1.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(0).setMinWidth(0);
            jTable3.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(1).setMinWidth(50);
            jTable3.getColumnModel().getColumn(1).setMaxWidth(50);
        }

        btSearch1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btSearch1.setText("Search");
        btSearch1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSearch1ActionPerformed(evt);
            }
        });

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(layout.createSequentialGroup()
                        .addContainerGap()
                        .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 415, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(18, 18, 18)
                        .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 805, Short.MAX_VALUE))
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 389, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(btSearch1, javax.swing.GroupLayout.PREFERRED_SIZE, 100, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(btSearch1))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane1)
                    .addComponent(jLayeredPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 468, Short.MAX_VALUE))
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
                    save();
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

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode() == 10) btSave.doClick();// TODO add your handling code here:
    }//GEN-LAST:event_btSaveKeyPressed

    private void btSearch1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSearch1ActionPerformed
        loadData();
    }//GEN-LAST:event_btSearch1ActionPerformed

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

    private void jDate1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1PropertyChange

    private void jDate1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1KeyPressed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btSearch1;
    private javax.swing.JButton jButton12;
    private com.toedter.calendar.JDateChooser jDate1;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLabel jLabel28;
    private javax.swing.JLabel jLabel29;
    private javax.swing.JLabel jLabel30;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tnilaihadir;
    private javax.swing.JTextField tnilailain;
    private javax.swing.JTextField tnilaimakan;
    // End of variables declaration//GEN-END:variables
}
