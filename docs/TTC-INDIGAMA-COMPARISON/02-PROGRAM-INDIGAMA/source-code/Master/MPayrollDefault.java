/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Master;

import Accounting.*;
import Delivery.*;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
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
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class MPayrollDefault extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="MASTER PAYROLL";
    public MPayrollDefault(String mode) {
        initComponents();
        if("IN".equals(mode)) {
            jLabel2.setText("Transaksi Kas Masuk");
        } else if("OUT".equals(mode)) {
            jLabel2.setText("Transaksi Kas Keluar");
        }
        
        push_button();
        listLembur();
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

     
    private void listLembur(){
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        
        sql="select * from MPayroll where flag = 'LEMBUR'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("kode"),
                    rs.getInt("value1"),
                    rs.getInt("value2"),
                    rs.getDouble("value3"),
                    rs.getString("cdesc")
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(MPayrollDefault.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void clearLembur(){
        rfa.empty(keyssDef, tkodelembur,tjamawal,tkelipatan,tnominallembur,tketlembur);
        rfa.empty(keyssDef,idLembur);
        btSaveLembur.setText("Save");
        tkodelembur.setEnabled(true);
    }
    private void saveLembur(){
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("kode",tkodelembur.getText());
        listObj.put("cdesc",tketlembur.getText());
        listObj.put("key1","Start Menit");
        listObj.put("key2","Kelipatan Menit");
        listObj.put("key3","Nominal");
        listObj.put("value1",tjamawal.getText());
        listObj.put("value2",tkelipatan.getText());
        listObj.put("value3",tnominallembur.getText());
        listObj.put("flag","LEMBUR");
        sql=con.insertValue("MPayroll", listObj);
        if(con.queryUpdate(sql)>0) {
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clearLembur();
            listLembur();
        } else {
            JOptionPane.showMessageDialog(this,"Header gagal di simpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void updateLembur(){
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("kode",tkodelembur.getText());
        listObj.put("cdesc",tketlembur.getText());
        listObj.put("key1","Start Menit");
        listObj.put("key2","Kelipatan Menit");
        listObj.put("key3","Nominal");
        listObj.put("value1",tjamawal.getText());
        listObj.put("value2",tkelipatan.getText());
        listObj.put("value3",tnominallembur.getText());
        listObj.put("flag","LEMBUR");
        sql=con.updateValue("MPayroll", listObj);
        sql+=" where id = '"+idLembur.getText()+"' ";
        if(con.queryUpdate(sql)>0) {
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clearLembur();
            listLembur();
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

        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel1 = new javax.swing.JPanel();
        idLembur = new javax.swing.JLabel();
        tkodelembur = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        tjamawal = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        jLabel7 = new javax.swing.JLabel();
        tkelipatan = new javax.swing.JTextField();
        jLabel9 = new javax.swing.JLabel();
        jLabel11 = new javax.swing.JLabel();
        tnominallembur = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        btSaveLembur = new javax.swing.JButton();
        btCancel1 = new javax.swing.JButton();
        jLabel16 = new javax.swing.JLabel();
        jLabel12 = new javax.swing.JLabel();
        tketlembur = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jTextField2 = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jScrollPane4 = new javax.swing.JScrollPane();
        TextAreaKet = new javax.swing.JTextArea();
        jLabel13 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        jRadioButton5 = new javax.swing.JRadioButton();
        jRadioButton6 = new javax.swing.JRadioButton();
        jLabel18 = new javax.swing.JLabel();
        jRadioButton7 = new javax.swing.JRadioButton();
        jRadioButton8 = new javax.swing.JRadioButton();
        jLabel4 = new javax.swing.JLabel();
        jTextField5 = new javax.swing.JTextField();
        jPanel3 = new javax.swing.JPanel();
        jPanel7 = new javax.swing.JPanel();
        jPanel8 = new javax.swing.JPanel();

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Master Payroll");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        idLembur.setText("id");
        jPanel1.add(idLembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 30, 30, 20));
        jPanel1.add(tkodelembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 30, 120, -1));

        jLabel5.setText("Menit");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 90, 40, 20));

        tjamawal.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tjamawalCaretUpdate(evt);
            }
        });
        jPanel1.add(tjamawal, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 60, 70, -1));

        jLabel6.setText("Jam Kelipatan");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 90, -1, 20));

        jLabel7.setText("Start Jam ");
        jPanel1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 60, -1, 20));
        jPanel1.add(tkelipatan, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 90, 70, -1));

        jLabel9.setText("Menit");
        jPanel1.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 60, 40, 20));

        jLabel11.setText("Keterangan");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 150, -1, 20));
        jPanel1.add(tnominallembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 120, 120, -1));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null}
            },
            new String [] {
                "id", "Kode", "Jam Start", "Kelipatan (Jam)", "Nominal", "Keterangan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                true, false, false, false, false, false
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
        }

        jPanel1.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 180, 620, 180));

        btSaveLembur.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSaveLembur.setText("Save");
        btSaveLembur.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveLemburActionPerformed(evt);
            }
        });
        jPanel1.add(btSaveLembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 80, 97, -1));

        btCancel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel1.setText("Cancel");
        btCancel1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancel1ActionPerformed(evt);
            }
        });
        jPanel1.add(btCancel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 110, 97, -1));

        jLabel16.setText("Kode Lembur");
        jPanel1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 30, -1, 20));

        jLabel12.setText("Nominal Lembur");
        jPanel1.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 120, -1, 20));
        jPanel1.add(tketlembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 150, 510, -1));

        jTabbedPane1.addTab("Rumus Lembur", jPanel1);

        jPanel5.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());
        jLayeredPane1.add(jTextField2, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 170, 110, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 220, 97, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 220, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 220, 97, -1));

        jScrollPane4.setHorizontalScrollBarPolicy(javax.swing.ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);

        TextAreaKet.setColumns(20);
        TextAreaKet.setFont(new java.awt.Font("sansserif", 0, 11)); // NOI18N
        TextAreaKet.setLineWrap(true);
        TextAreaKet.setRows(5);
        TextAreaKet.setWrapStyleWord(true);
        TextAreaKet.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TextAreaKetFocusLost(evt);
            }
        });
        TextAreaKet.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextAreaKetKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TextAreaKetKeyTyped(evt);
            }
        });
        jScrollPane4.setViewportView(TextAreaKet);

        jLayeredPane1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 220, 50));

        jLabel13.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel13.setText("Keterangan");
        jLayeredPane1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 110, 20));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Rumus Potongan");
        jLayeredPane1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        jRadioButton5.setText("Nominal");
        jLayeredPane1.add(jRadioButton5, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, -1, -1));

        jRadioButton6.setText("Tidak");
        jLayeredPane1.add(jRadioButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 110, -1, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Potongan Harian");
        jLayeredPane1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 110, 20));

        jRadioButton7.setText("Ya");
        jLayeredPane1.add(jRadioButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, -1, -1));

        jRadioButton8.setText("Gaji / Total Hari (1 Bulan)");
        jLayeredPane1.add(jRadioButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, -1, -1));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Type Payroll");
        jLayeredPane1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 130, 20));
        jLayeredPane1.add(jTextField5, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 110, -1));

        jPanel5.add(jLayeredPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 20, 980, 320));

        jTabbedPane1.addTab("Default Payroll", jPanel5);

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 1113, Short.MAX_VALUE)
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 374, Short.MAX_VALUE)
        );

        jTabbedPane1.addTab("Tunjangan Jabatan", jPanel3);

        javax.swing.GroupLayout jPanel7Layout = new javax.swing.GroupLayout(jPanel7);
        jPanel7.setLayout(jPanel7Layout);
        jPanel7Layout.setHorizontalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 1113, Short.MAX_VALUE)
        );
        jPanel7Layout.setVerticalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 374, Short.MAX_VALUE)
        );

        jTabbedPane1.addTab("Transport", jPanel7);

        javax.swing.GroupLayout jPanel8Layout = new javax.swing.GroupLayout(jPanel8);
        jPanel8.setLayout(jPanel8Layout);
        jPanel8Layout.setHorizontalGroup(
            jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 1113, Short.MAX_VALUE)
        );
        jPanel8Layout.setVerticalGroup(
            jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 374, Short.MAX_VALUE)
        );

        jTabbedPane1.addTab("Uang Makan", jPanel8);

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
                .addGap(4, 4, 4)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void TextAreaKetKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextAreaKetKeyTyped

    }//GEN-LAST:event_TextAreaKetKeyTyped

    private void TextAreaKetKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextAreaKetKeyPressed

    }//GEN-LAST:event_TextAreaKetKeyPressed

    private void TextAreaKetFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TextAreaKetFocusLost
        TextAreaKet.setText(TextAreaKet.getText().toUpperCase());
    }//GEN-LAST:event_TextAreaKetFocusLost

    private void btSaveLemburActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveLemburActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSaveLembur.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    saveLembur();
                }
            } else if(btSaveLembur.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    updateLembur();
                }
            }
        }

    }//GEN-LAST:event_btSaveLemburActionPerformed

    private void btCancel1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancel1ActionPerformed
        btSaveLembur.setText("Save");
        rfa.empty(MenuAdmin.keyssDef, idLembur);
        rfa.empty(MenuAdmin.keyssDef, tkodelembur,tjamawal,tkelipatan,tnominallembur);
    }//GEN-LAST:event_btCancel1ActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
       if(evt.getClickCount() == 2) {
            int baris = jTable1.getSelectedRow();
            String id = jTable1.getValueAt(baris,0).toString();
            idLembur.setText(id);
            tkodelembur.setText(jTable1.getValueAt(baris,1).toString());
            tjamawal.setText(jTable1.getValueAt(baris,2).toString());
            tkelipatan.setText(jTable1.getValueAt(baris,3).toString());
            tnominallembur.setText(rfa.formatDecimal3.format(jTable1.getValueAt(baris,4)));
            tketlembur.setText(jTable1.getValueAt(baris,5).toString());
            tkodelembur.setEnabled(false);
            btSaveLembur.setText("Update");
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void tjamawalCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tjamawalCaretUpdate

    }//GEN-LAST:event_tjamawalCaretUpdate


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextArea TextAreaKet;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btCancel1;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btSaveLembur;
    private javax.swing.JLabel idLembur;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel7;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JRadioButton jRadioButton5;
    private javax.swing.JRadioButton jRadioButton6;
    private javax.swing.JRadioButton jRadioButton7;
    private javax.swing.JRadioButton jRadioButton8;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTextField jTextField2;
    private javax.swing.JTextField jTextField5;
    private javax.swing.JTextField tjamawal;
    private javax.swing.JTextField tkelipatan;
    private javax.swing.JTextField tketlembur;
    private javax.swing.JTextField tkodelembur;
    private javax.swing.JTextField tnominallembur;
    // End of variables declaration//GEN-END:variables
}
