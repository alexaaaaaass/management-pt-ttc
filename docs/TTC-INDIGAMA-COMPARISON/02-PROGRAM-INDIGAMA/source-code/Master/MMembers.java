/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Master;

import Main.DefaultProgram;
import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;
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
public class MMembers extends javax.swing.JPanel {
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER CUSTOMER";
    
    public MMembers() {
        initComponents();
        loadData();
        LastCode();
        deff();
    }
    
    private void deff(){
        btDelete.setVisible(false);
        btSave.setText("Save");
        id = "";
        LastCode();
    }
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,aNama,aTelp);
        rfa.empty(MenuAdmin.keyssDef,aAlamat);
    }
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(kode_customer,3))) as lastno from MCustomer");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        aKode.setText("CM"+rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql = "select * from MCustomer where  cname like '%"+iNama1.getText()+"%' and addr like '%"+iAlamat1.getText()+"%' "
                + "order by kode_customer ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),rs.getString("kode_customer"),rs.getString("cname"),rs.getString("addr"),
                    rs.getString("ctelp")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void loadEdit(){
        sql = "select * from MCustomer where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                aKode.setText(rs.getString("kode_customer"));
                aNama.setText(rs.getString("cname"));
                aAlamat.setText(rs.getString("addr"));
                aTelp.setText(rs.getString("ctelp"));
                
                btSave.setText("Update");
                btDelete.setVisible(true);
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void save(){
        sql="INSERT INTO MCustomer (kode_customer,cname,addr,ctelp,username)\n" +
            "VALUES\n" +
            "('"+aKode.getText()+"','"+aNama.getText()+"','"+aAlamat.getText()+"','"+aTelp.getText()+"','"+Login.user+"')";
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
        sql="update  MCustomer set kode_customer='"+aKode.getText()+"',cname='"+aNama.getText()+"',addr='"+aAlamat.getText()+"',ctelp='"+aTelp.getText()+"',username='"+Login.user+"' \n" +
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
        sql="delete from  MCustomer  where id = '"+id+"' ";
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
        aAlamat = new javax.swing.JTextArea();
        jLabel7 = new javax.swing.JLabel();
        aKode = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        aTelp = new javax.swing.JTextField();
        aNama = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        jLabel6 = new javax.swing.JLabel();
        jPanel6 = new javax.swing.JPanel();
        jLabel8 = new javax.swing.JLabel();
        iNama1 = new javax.swing.JTextField();
        jLabel10 = new javax.swing.JLabel();
        iAlamat1 = new javax.swing.JTextField();
        btSearch1 = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel4.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel14.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel14.setText("Master Members");
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
        btSave.setBounds(130, 250, 100, 25);

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel);
        btCancel.setBounds(240, 250, 100, 25);

        jLabel4.setText("Nama Member");
        jLayeredPane1.add(jLabel4);
        jLabel4.setBounds(20, 50, 90, 20);

        aAlamat.setColumns(20);
        aAlamat.setRows(5);
        aAlamat.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aAlamatFocusLost(evt);
            }
        });
        aAlamat.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aAlamatKeyPressed(evt);
            }
        });
        jScrollPane2.setViewportView(aAlamat);

        jLayeredPane1.add(jScrollPane2);
        jScrollPane2.setBounds(120, 80, 220, 110);

        jLabel7.setText("Telp");
        jLayeredPane1.add(jLabel7);
        jLabel7.setBounds(20, 200, 90, 20);

        aKode.setEditable(false);
        jLayeredPane1.add(aKode);
        aKode.setBounds(120, 20, 220, 20);

        jLabel5.setText("Kode Member");
        jLayeredPane1.add(jLabel5);
        jLabel5.setBounds(20, 20, 90, 20);

        aTelp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aTelpKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aTelp);
        aTelp.setBounds(120, 200, 220, 20);

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
        aNama.setBounds(120, 50, 220, 20);

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete);
        btDelete.setBounds(20, 250, 100, 25);

        jLabel6.setText("Alamat");
        jLayeredPane1.add(jLabel6);
        jLabel6.setBounds(20, 80, 90, 20);

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel8.setText("Nama Member");
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

        jLabel10.setText("Alamat ");
        jPanel6.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        iAlamat1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iAlamat1KeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iAlamat1KeyTyped(evt);
            }
        });
        jPanel6.add(iAlamat1, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 60, 160, -1));

        btSearch1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btSearch1.setText("Search");
        btSearch1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSearch1ActionPerformed(evt);
            }
        });
        jPanel6.add(btSearch1, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 60, 100, -1));

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "No", "Kode Customer", "Nama Member", "Alamat", "Telp"
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
        }

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
                    .addComponent(jPanel6, javax.swing.GroupLayout.DEFAULT_SIZE, 507, Short.MAX_VALUE)
                    .addComponent(jScrollPane1))
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
                        .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 245, Short.MAX_VALUE)))
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

    private void aAlamatFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aAlamatFocusLost
        aAlamat.setText(aAlamat.getText().toUpperCase());
    }//GEN-LAST:event_aAlamatFocusLost

    private void aNamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aNamaKeyPressed
        if(evt.getKeyCode() == 10) aAlamat.requestFocus();
    }//GEN-LAST:event_aNamaKeyPressed

    private void aAlamatKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aAlamatKeyPressed
        if(evt.getKeyCode() == 10) aTelp.requestFocus();
    }//GEN-LAST:event_aAlamatKeyPressed

    private void aTelpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aTelpKeyPressed
        if(evt.getKeyCode() == 10) btSave.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_aTelpKeyPressed

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode() == 10) btSave.doClick();// TODO add your handling code here:
    }//GEN-LAST:event_btSaveKeyPressed

    private void btSearch1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSearch1ActionPerformed
        loadData();
    }//GEN-LAST:event_btSearch1ActionPerformed

    private void iAlamat1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iAlamat1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_iAlamat1KeyTyped

    private void iAlamat1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iAlamat1KeyPressed
        if(evt.getKeyCode() == 10){
            loadData();
        }        // TODO add your handling code here:
    }//GEN-LAST:event_iAlamat1KeyPressed

    private void iNama1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNama1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_iNama1KeyTyped

    private void iNama1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNama1KeyPressed
        if(evt.getKeyCode() == 10){
            loadData();
        }
    }//GEN-LAST:event_iNama1KeyPressed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextArea aAlamat;
    private javax.swing.JTextField aKode;
    private javax.swing.JTextField aNama;
    private javax.swing.JTextField aTelp;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btSearch1;
    private javax.swing.JTextField iAlamat1;
    private javax.swing.JTextField iNama1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTable jTable3;
    // End of variables declaration//GEN-END:variables
}
