/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Master;

import Delivery.*;
import Accounting.*;
import DialogPublicTP.DialogDepartemen;
import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
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
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableModel;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class MJabatan extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER JABATAN";
    public MJabatan(String mode) {
        initComponents();
        loadData();
        LastCode();
        deff();
        push_button();
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
        btDelete.setVisible(false);
        btSave.setText("Save");
        id = "";
        LastCode();
    }
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,tkode,tdepart,tdepart1,tjabatan);
    }
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(id_jab,3))) as lastno from MJabatan");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tkode.setText("J"+rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql = "select a.*,b.cdept from mjabatan a join MDepartemen b on a.id_dept=b.id_dept\n" +
"where b.cdept like '%"+tdepts.getText()+"%' and a.cjabatan like '%"+tjabatans.getText()+"%' "
                + " order by b.id_dept,a.id_jab ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("id_dept"),rs.getString("cdept"),
                    rs.getString("id_jab"),rs.getString("cjabatan"),
                    rs.getDouble("ctunj")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void loadEdit(){
        sql = "select * from MJabatan where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tkode.setText(rs.getString("id_jab"));
                tjabatan.setText(rs.getString("cjabatan"));
                tdepart.setText(rs.getString("id_dept"));
                tdepart.requestFocus();
                tjabatan.requestFocus();
                btSave.setText("Update");
                btDelete.setVisible(true);
                ttunj.setText(rfa.formatDecimal1.format(rs.getDouble("ctunj")));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void save(){
        sql="INSERT INTO MJabatan (id_jab,cjabatan,id_dept,username,ctunj)\n" +
            "VALUES\n" +
            "('"+tkode.getText()+"','"+tjabatan.getText()+"','"+tdepart.getText()+"','"+Login.user+"','"+ttunj.getText()+"')";
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
        sql="update  MJabatan set id_jab='"+tkode.getText()+"',cjabatan='"+tjabatan.getText()+"',id_dept='"+tdepart.getText()+"',username='"+Login.user+"' \n" +
            ",ctunj='"+ttunj.getText()+"'"+
            " where id = '"+id+"' ";
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
        sql="delete from  MJabatan  where id = '"+id+"' ";
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

        jLabel11 = new javax.swing.JLabel();
        ttunj = new javax.swing.JTextField();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        tjabatan = new javax.swing.JTextField();
        jLabel13 = new javax.swing.JLabel();
        tdepart1 = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        tkode = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        tdepart = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel12 = new javax.swing.JLabel();
        jPanel6 = new javax.swing.JPanel();
        Depat = new javax.swing.JLabel();
        tdepts = new javax.swing.JTextField();
        tjabatans = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        Jabatan = new javax.swing.JLabel();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Nilai Tunjangan");

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Master Jabatan");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        tjabatan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tjabatanFocusLost(evt);
            }
        });
        tjabatan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tjabatanKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tjabatan, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 120, 190, -1));

        jLabel13.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel13.setText("Departemen");
        jLayeredPane1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 110, 20));

        tdepart1.setEditable(false);
        tdepart1.setBackground(new java.awt.Color(153, 153, 255));
        jLayeredPane1.add(tdepart1, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 90, 190, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Id Jabatan");
        jLayeredPane1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 110, 20));

        tkode.setEditable(false);
        jLayeredPane1.add(tkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 30, 70, -1));

        jButton1.setText("...");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 60, 30, -1));

        tdepart.setEditable(false);
        tdepart.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tdepartFocusLost(evt);
            }
        });
        jLayeredPane1.add(tdepart, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 60, 130, -1));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 210, 97, -1));

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
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 210, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 210, 97, -1));

        jLabel12.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel12.setText("Jabatan");
        jLayeredPane1.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 120, 110, 20));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        Depat.setText("Depat");
        jPanel6.add(Depat, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        tdepts.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tdeptsKeyPressed(evt);
            }
        });
        jPanel6.add(tdepts, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 140, -1));

        tjabatans.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tjabatansKeyPressed(evt);
            }
        });
        jPanel6.add(tjabatans, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 320, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 20, 90, -1));

        Jabatan.setText("Jabatan");
        jPanel6.add(Jabatan, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No", "Id Dept", "Departemen", "ID Jabatan", "Jabatan", "Nilai Tunjangan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false
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
            jTable3.getColumnModel().getColumn(1).setMinWidth(50);
            jTable3.getColumnModel().getColumn(1).setMaxWidth(50);
            jTable3.getColumnModel().getColumn(6).setMinWidth(0);
            jTable3.getColumnModel().getColumn(6).setMaxWidth(0);
        }

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
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 340, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(18, 18, 18)
                        .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 509, Short.MAX_VALUE)
                            .addComponent(jPanel6, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGap(4, 4, 4)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jLayeredPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 327, Short.MAX_VALUE)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane3, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        DialogDepartemen a = new DialogDepartemen();
        a.setVisible(true);
        if(!a.classid.equals("")) {
            tdepart.setText(a.classid);
            tdepart1.setText(a.classname);
        }
    }//GEN-LAST:event_jButton1ActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(ttunj.getText().isEmpty()){
                ttunj.setText("0");
            }
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
        deff();// TODO add your handling code here:
    }//GEN-LAST:event_btCancelActionPerformed

    private void tdeptsKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tdeptsKeyPressed
        if(evt.getKeyCode()==10) loadData();
    }//GEN-LAST:event_tdeptsKeyPressed

    private void tjabatansKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tjabatansKeyPressed
        if(evt.getKeyCode()==10) loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_tjabatansKeyPressed

    private void tdepartFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tdepartFocusLost
        sql="select * from mdepartemen where id_dept = '"+tdepart.getText()+"' ";
        rs= con.queryResult(sql);
        try {
            while(rs.next()){
                tdepart1.setText(rs.getString("cdept"));
            }
        } catch (Exception e){
            
        }
    }//GEN-LAST:event_tdepartFocusLost

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 2){
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            loadEdit();
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void tjabatanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tjabatanFocusLost
        rfa.FieldCapital(tjabatan);
    }//GEN-LAST:event_tjabatanFocusLost

    private void tjabatanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tjabatanKeyPressed
        rfa.EnterNextFocus(evt, btSave);
    }//GEN-LAST:event_tjabatanKeyPressed

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode()==10) btSave.doClick();
    }//GEN-LAST:event_btSaveKeyPressed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JLabel Depat;
    private javax.swing.JLabel Jabatan;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton7;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField tdepart;
    private javax.swing.JTextField tdepart1;
    private javax.swing.JTextField tdepts;
    private javax.swing.JTextField tjabatan;
    private javax.swing.JTextField tjabatans;
    private javax.swing.JTextField tkode;
    private javax.swing.JTextField ttunj;
    // End of variables declaration//GEN-END:variables
}
