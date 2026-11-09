/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Absensi;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import Main.DefaultProgram;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Tools.ConnectDB;
import java.awt.Color;
import java.awt.Cursor;
import java.awt.Rectangle;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Scanner;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JFileChooser;
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
import org.apache.poi.hssf.usermodel.HSSFCell;
import org.apache.poi.hssf.usermodel.HSSFRow;
import org.apache.poi.hssf.usermodel.HSSFSheet;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.ss.usermodel.DataFormatter;
import org.jdesktop.swingx.autocomplete.AutoCompleteDecorator;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class Holidays extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="TRANSAKSI PAYROLL KARYAWAN";
    String idKas="21";
    ArrayList<String> deleteRow;
    final JFileChooser chooser2 = new JFileChooser();
    public Holidays(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        push_button();
         
        clear();
                
        jDate.setDate(new Date());
        loadData();
    }
    
    private void push_button(){
        InputMap im = this.getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = this.getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F9, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
//                 System.out.println("Print "+jLabel2.getText());
            }
        });
    }
    
    private void clear(){
        rfa.empty(keyssDef, tket );
        btDelete.setVisible(false);
        btSave.setText("Save");
        id="";
    }
    
    private void loadData(){ 
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        sql="select * from holidays order by ctgl desc";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rfa.DateString(keyssDef,rs.getDate("ctgl"),"dd/MM/yyyy"),
                    rs.getString("cnote"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(Holidays.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadEdit(){
        sql="select * from holidays where id='"+id+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tket.setText(rs.getString("cnote"));
                jDate.setDate(rs.getDate("ctgl"));
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(Holidays.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        btDelete.setVisible(true);
    }
    
    private void save(){
        sql="delete from holidays where ctgl='"+rfa.DateString(keyssDef,jDate.getDate(),"yyyy-MM-dd")+"' ";
        con.queryUpdate(sql);

         HashMap<String,String> listObj = new HashMap<String,String>();  
         listObj.put("ctgl",rfa.DateString(keyssDef,jDate.getDate(),"yyyy-MM-dd")); 
         listObj.put("cnote",tket.getText());
         sql=con.insertValue("holidays", listObj);

         con.queryUpdate(sql);
            
       clear();
       loadData();
        JOptionPane.showMessageDialog(this,"Absensi Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
    }
    
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jScrollPane3 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jLabel21 = new javax.swing.JLabel();
        jLabel23 = new javax.swing.JLabel();
        tmenit = new javax.swing.JTextField();
        tjam = new javax.swing.JTextField();
        jLabel15 = new javax.swing.JLabel();
        jLabel22 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jPanel1 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jDate = new com.toedter.calendar.JDateChooser();
        jLabel16 = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel27 = new javax.swing.JLabel();

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ctran", "cnbkt", "ccode", "gudang", "cyear", "dtran", "cacc", "cacc_desc", "caccll", "caccll_desc", "cnote", "namnt"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane3.setViewportView(jtable_print);

        jLabel21.setText("Dalam Jam");

        jLabel23.setText("Dalam Menit");

        tmenit.setEditable(false);
        tmenit.setBackground(new java.awt.Color(153, 153, 255));
        tmenit.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tmenit.setForeground(new java.awt.Color(255, 255, 255));
        tmenit.setHorizontalAlignment(javax.swing.JTextField.CENTER);

        tjam.setEditable(false);
        tjam.setBackground(new java.awt.Color(153, 153, 255));
        tjam.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tjam.setForeground(new java.awt.Color(255, 255, 255));
        tjam.setHorizontalAlignment(javax.swing.JTextField.CENTER);

        jLabel15.setText("Jam");

        jLabel22.setText(" Menit");

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null},
                {null, null, null},
                {null, null, null},
                {null, null, null}
            },
            new String [] {
                "id", "Tanggal", "Keterangan Lembur"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false
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
            jTable2.getColumnModel().getColumn(1).setMinWidth(90);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(90);
        }

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setAutoscrolls(true);
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jDate.setDateFormatString("dd/MM/yyyy");
        jDate.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDatePropertyChange(evt);
            }
        });
        jDate.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateKeyPressed(evt);
            }
        });
        jLayeredPane1.add(jDate, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 30, 150, -1));

        jLabel16.setText("Tanggal");
        jLayeredPane1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        jLayeredPane1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 60, 310, -1));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 110, 90, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 110, 100, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(320, 110, -1, -1));

        jLabel27.setText("Keterangan");
        jLayeredPane1.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 447, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addGap(12, 12, 12)
                        .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addContainerGap(689, Short.MAX_VALUE))
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel4Layout.createSequentialGroup()
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane2)
                        .addContainerGap())))
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jLayeredPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 574, Short.MAX_VALUE)
                    .addComponent(jScrollPane2))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(11, 11, 11))
        );

        jTabbedPane1.addTab("Set", jPanel4);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString(); 
            loadEdit();
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jDatePropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDatePropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDatePropertyChange

    private void jDateKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateKeyPressed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(con.akses(akses, rfa.DELETE)) {
                sql="delete from holidays where id='"+id+"' ";
            con.queryUpdate(sql);
            clear();
            loadData();
            }
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    save();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_btCancelActionPerformed

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private com.toedter.calendar.JDateChooser jDate;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTextField tjam;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tmenit;
    // End of variables declaration//GEN-END:variables
}
