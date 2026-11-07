/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Master;

import DialogPublicTP.DialogSatuan;
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
public class MSatuan extends javax.swing.JPanel {
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER SATUAN";
    String satuansebelum="";
    public MSatuan() {
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
        rfa.empty(MenuAdmin.keyssDef,aNama,abig,asmall,tsatuan,tsatuan2,anamasatuan);
    }
    
    private void LastCode(){
       
    } 
    private void loadData(){
        sql = "select a.*,isnull(b.cname,a.cname) as satuankonversi from MSatuan a left join MSatuan b on a.id_turunan=b.id\n" +
                "where a.cname like '%"+iNama1.getText()+"%' \n" +
                "order by a.cname ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),rs.getString("cname"),rs.getString("namasatuan"),rs.getString("big_value"),
                    rs.getDouble("small_value"),rs.getString("satuankonversi")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void loadEdit(){
        sql = "select * from MSatuan where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                satuansebelum = rs.getString("cname");
                aNama.setText(rs.getString("cname"));
                anamasatuan.setText(rs.getString("namasatuan"));
                abig.setText(rs.getString("big_value"));
                asmall.setText(rs.getString("small_value"));
                tsatuan2.setText(rs.getString("id_turunan"));
                btSave.setText("Update");
                btDelete.setVisible(true);
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        sql = "select * from MSatuan where id = '"+tsatuan2.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tsatuan.setText(rs.getString("cname"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void save(){
        sql="INSERT INTO MSatuan (cname,big_value,small_value,id_turunan,namasatuan)\n" +
            "VALUES\n" +
            "('"+aNama.getText()+"','1','"+asmall.getText()+"','"+tsatuan2.getText()+"','"+anamasatuan.getText()+"')";
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
        sql="update  MSatuan set cname='"+aNama.getText()+"',big_value='1',small_value='"+asmall.getText()+"',id_turunan='"+tsatuan2.getText()+"',namasatuan='"+anamasatuan.getText()+"' \n" +
            " where id = '"+id+"' ";
        if(con.queryUpdate(sql)>0){
            sql="SELECT  SCHEMA_NAME(o.schema_id) SchemaName, o.Name, c.Name as kolom\n" +
                "FROM     sys.columns c \n" +
                "         JOIN sys.objects o ON o.object_id = c.object_id \n" +
                "WHERE    o.type = 'U' AND (c.name like '%satuan%' or c.name like '%unit%')\n" +
                "ORDER BY o.Name, c.Name";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    con.queryUpdateWithoutNote("update "+rs.getString("Name")+" set "+rs.getString("kolom")+"='"+aNama.getText()+"' where "+rs.getString("kolom")+"='"+satuansebelum+"'");
                }
            } catch (SQLException ex) {
                Logger.getLogger(MSatuan.class.getName()).log(Level.SEVERE, null, ex);
            }
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
        sql="delete from  MSatuan  where id = '"+id+"' ";
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

        jLabel9 = new javax.swing.JLabel();
        abig = new javax.swing.JTextField();
        jPanel4 = new javax.swing.JPanel();
        jLabel13 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel4 = new javax.swing.JLabel();
        aNama = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        asmall = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        tsatuan2 = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        jLabel12 = new javax.swing.JLabel();
        tsatuan = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        anamasatuan = new javax.swing.JTextField();
        jPanel6 = new javax.swing.JPanel();
        jLabel8 = new javax.swing.JLabel();
        iNama1 = new javax.swing.JTextField();
        btSearch1 = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();

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
        jLabel14.setText("Master Satuan");
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
        btSave.setBounds(140, 160, 100, 25);

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel);
        btCancel.setBounds(250, 160, 100, 25);

        jLabel4.setText("Nama Satuan");
        jLayeredPane1.add(jLabel4);
        jLabel4.setBounds(20, 30, 90, 20);

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
        aNama.setBounds(120, 60, 220, 20);

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete);
        btDelete.setBounds(30, 160, 100, 25);

        asmall.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                asmallFocusLost(evt);
            }
        });
        asmall.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                asmallKeyPressed(evt);
            }
        });
        jLayeredPane1.add(asmall);
        asmall.setBounds(120, 90, 110, 20);

        jLabel11.setText("Satuan Konversi");
        jLayeredPane1.add(jLabel11);
        jLabel11.setBounds(20, 120, 90, 20);

        tsatuan2.setEditable(false);
        tsatuan2.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan2.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tsatuan2);
        tsatuan2.setBounds(270, 120, 20, 20);

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
        jLayeredPane1.add(btsatuan);
        btsatuan.setBounds(230, 120, 30, 23);

        jLabel12.setText("Nilai Konversi");
        jLayeredPane1.add(jLabel12);
        jLabel12.setBounds(20, 90, 90, 20);

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tsatuan);
        tsatuan.setBounds(120, 120, 110, 20);

        jLabel5.setText("Satuan");
        jLayeredPane1.add(jLabel5);
        jLabel5.setBounds(20, 60, 90, 20);

        anamasatuan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                anamasatuanFocusLost(evt);
            }
        });
        jLayeredPane1.add(anamasatuan);
        anamasatuan.setBounds(120, 30, 110, 20);

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel8.setText("Satuan");
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
                "id", "No", "Satuan", "Nama Satuan", "Big Value", "Nilai Konversi", "Satuan Konversi"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class
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
        jScrollPane1.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(0).setMinWidth(0);
            jTable3.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(1).setMinWidth(50);
            jTable3.getColumnModel().getColumn(1).setMaxWidth(50);
            jTable3.getColumnModel().getColumn(4).setMinWidth(0);
            jTable3.getColumnModel().getColumn(4).setMaxWidth(0);
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
                        .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 69, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 272, Short.MAX_VALUE)))
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            
            Boolean cek=false;
            sql="select * from MSatuan where cname='"+aNama.getText()+"'";
            rs=con.queryResult(sql);
            try {
                while(rs.next()){
                    cek = true;
                }
            } catch (SQLException ex) {
                Logger.getLogger(MSatuan.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            if(btSave.getText().equals("Save")) {
                if(cek == true) {
                    JOptionPane.showMessageDialog(this,"Satuan sudah ada", "Save", JOptionPane.ERROR_MESSAGE);
                    return;
                }
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

    private void aNamaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aNamaFocusLost
        aNama.setText(aNama.getText().toUpperCase());
    }//GEN-LAST:event_aNamaFocusLost

    private void aNamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aNamaKeyPressed
        if(evt.getKeyCode() == 10) abig.requestFocus();
    }//GEN-LAST:event_aNamaKeyPressed

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

    private void asmallFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_asmallFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_asmallFocusLost

    private void asmallKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_asmallKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_asmallKeyPressed

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        DialogSatuan a = new DialogSatuan("");
        a.setVisible(true);
        if(a.status){
            tsatuan.setText(a.unit);
            tsatuan2.setText(a.id);
        }
    }//GEN-LAST:event_btsatuanActionPerformed

    private void btsatuanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuanKeyPressed
        if(evt.getKeyCode()==10) btsatuan.doClick();
    }//GEN-LAST:event_btsatuanKeyPressed

    private void anamasatuanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_anamasatuanFocusLost
        anamasatuan.setText(anamasatuan.getText().toUpperCase());
    }//GEN-LAST:event_anamasatuanFocusLost


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField aNama;
    private javax.swing.JTextField abig;
    private javax.swing.JTextField anamasatuan;
    private javax.swing.JTextField asmall;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btSearch1;
    private javax.swing.JButton btsatuan;
    private javax.swing.JTextField iNama1;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuan2;
    // End of variables declaration//GEN-END:variables
}
