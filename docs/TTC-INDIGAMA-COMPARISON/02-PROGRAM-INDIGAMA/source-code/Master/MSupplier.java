/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Master;

import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.table.DefaultTableModel;
import rf4declare.Rf4declare;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class MSupplier extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER SUPPLIER";
    public MSupplier() {
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
        rfa.empty(MenuAdmin.keyssDef,aNama,aOwner,aTelp,aJenis,aBank,aBankAn,aNoRek);
        rfa.empty(MenuAdmin.keyssDef,aAlamat);
    }
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(kode_supplier,3))) as lastno from MSupplier");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        aKode.setText("S"+rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql = "select * from MSupplier where cs like '%"+iOwner.getText()+"%' and cname like '%"+iNama.getText()+"%' and addr like '%"+iAlamat.getText()+"%' "
                + "order by kode_supplier ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),rs.getString("kode_supplier"),rs.getString("cname"),rs.getString("addr"),
                    rs.getString("cjenis"),rs.getString("ctelp"),
                    rs.getString("cs"),
                    rs.getString("bank"),rs.getString("bank_owner"),rs.getString("bank_number")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void loadEdit(){
        sql = "select * from MSupplier where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                aKode.setText(rs.getString("kode_supplier"));
                aNama.setText(rs.getString("cname"));
                aAlamat.setText(rs.getString("addr"));
                aJenis.setText(rs.getString("cjenis"));
                aTelp.setText(rs.getString("ctelp"));
                aOwner.setText(rs.getString("cs"));
                aBank.setText(rs.getString("bank"));
                aBankAn.setText(rs.getString("bank_owner"));
                aNoRek.setText(rs.getString("bank_number"));
                
                btSave.setText("Update");
                btDelete.setVisible(true);
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void save(){
        sql="INSERT INTO MSupplier (kode_supplier,cname,addr,cjenis\n" +
            ",ctelp,cs,bank,bank_owner,bank_number,username)\n" +
            "VALUES\n" +
            "('"+aKode.getText()+"','"+aNama.getText()+"','"+aAlamat.getText()+"','"+aJenis.getText()+"','"+aTelp.getText()+"','"+aOwner.getText()+"','"+aBank.getText()+"','"+aBankAn.getText()+"','"+aNoRek.getText()+"','"+Login.user+"')";
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
        sql="update  MSupplier set kode_supplier='"+aKode.getText()+"',cname='"+aNama.getText()+"',addr='"+aAlamat.getText()+"',cjenis='"+aJenis.getText()+"'\n" +
            ",ctelp='"+aTelp.getText()+"',cs='"+aOwner.getText()+"',bank='"+aBank.getText()+"',bank_owner='"+aBankAn.getText()+"',bank_number='"+aNoRek.getText()+"',username='"+Login.user+"' \n" +
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
        sql="delete from  MSupplier  where id = '"+id+"' ";
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
        jLabel4 = new javax.swing.JLabel();
        aNama = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        aKode = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        aTelp = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        aOwner = new javax.swing.JTextField();
        jScrollPane2 = new javax.swing.JScrollPane();
        aAlamat = new javax.swing.JTextArea();
        btDelete = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        jLabel9 = new javax.swing.JLabel();
        aNoRek = new javax.swing.JTextField();
        jLabel10 = new javax.swing.JLabel();
        aJenis = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        aBankAn = new javax.swing.JTextField();
        jLabel12 = new javax.swing.JLabel();
        aBank = new javax.swing.JTextField();
        jPanel6 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jLabel1 = new javax.swing.JLabel();
        iOwner = new javax.swing.JTextField();
        iNama = new javax.swing.JTextField();
        jLabel3 = new javax.swing.JLabel();
        iAlamat = new javax.swing.JTextField();
        btSearch = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel4.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel14.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel14.setText("Master Supplier");
        jPanel4.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setOpaque(true);

        jLabel4.setText("Nama Supplier");
        jLayeredPane1.add(jLabel4);
        jLabel4.setBounds(20, 50, 120, 20);

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
        aNama.setBounds(150, 50, 220, 20);

        jLabel5.setText("Kode Supplier");
        jLayeredPane1.add(jLabel5);
        jLabel5.setBounds(20, 20, 120, 20);

        aKode.setEditable(false);
        jLayeredPane1.add(aKode);
        aKode.setBounds(150, 20, 220, 20);

        jLabel6.setText("Alamat");
        jLayeredPane1.add(jLabel6);
        jLabel6.setBounds(20, 80, 120, 20);

        aTelp.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aTelpFocusLost(evt);
            }
        });
        aTelp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aTelpKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aTelp);
        aTelp.setBounds(150, 160, 220, 20);

        jLabel7.setText("Telp");
        jLayeredPane1.add(jLabel7);
        jLabel7.setBounds(20, 160, 120, 20);

        jLabel8.setText("Owner / CS");
        jLayeredPane1.add(jLabel8);
        jLabel8.setBounds(20, 190, 120, 20);

        aOwner.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aOwnerFocusLost(evt);
            }
        });
        aOwner.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aOwnerKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aOwner);
        aOwner.setBounds(150, 190, 220, 20);

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
        jScrollPane2.setBounds(150, 80, 220, 70);

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete);
        btDelete.setBounds(30, 360, 100, 25);

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel);
        btCancel.setBounds(250, 360, 100, 25);

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
        btSave.setBounds(140, 360, 100, 25);

        jLabel9.setText("Nomor Rekening");
        jLayeredPane1.add(jLabel9);
        jLabel9.setBounds(20, 310, 120, 20);

        aNoRek.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aNoRekFocusLost(evt);
            }
        });
        aNoRek.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aNoRekKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aNoRek);
        aNoRek.setBounds(150, 310, 220, 20);

        jLabel10.setText("Jenis Supplier");
        jLayeredPane1.add(jLabel10);
        jLabel10.setBounds(20, 220, 120, 20);

        aJenis.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aJenisFocusLost(evt);
            }
        });
        aJenis.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aJenisKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aJenis);
        aJenis.setBounds(150, 220, 220, 20);

        jLabel11.setText("Bank A/N");
        jLayeredPane1.add(jLabel11);
        jLabel11.setBounds(20, 280, 120, 20);

        aBankAn.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aBankAnFocusLost(evt);
            }
        });
        aBankAn.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aBankAnKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aBankAn);
        aBankAn.setBounds(150, 280, 220, 20);

        jLabel12.setText("Bank");
        jLayeredPane1.add(jLabel12);
        jLabel12.setBounds(20, 250, 120, 20);

        aBank.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                aBankFocusLost(evt);
            }
        });
        aBank.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                aBankKeyPressed(evt);
            }
        });
        jLayeredPane1.add(aBank);
        aBank.setBounds(150, 250, 220, 20);

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel2.setText("Nama Supplier");
        jPanel6.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        jLabel1.setText("Owner");
        jPanel6.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        iOwner.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iOwnerKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iOwnerKeyTyped(evt);
            }
        });
        jPanel6.add(iOwner, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 60, 160, -1));

        iNama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iNamaKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iNamaKeyTyped(evt);
            }
        });
        jPanel6.add(iNama, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 30, 160, -1));

        jLabel3.setText("Alamat Supplier");
        jPanel6.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(320, 30, -1, 20));

        iAlamat.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iAlamatKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iAlamatKeyTyped(evt);
            }
        });
        jPanel6.add(iAlamat, new org.netbeans.lib.awtextra.AbsoluteConstraints(430, 30, 160, -1));

        btSearch.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btSearch.setText("Search");
        btSearch.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSearchActionPerformed(evt);
            }
        });
        jPanel6.add(btSearch, new org.netbeans.lib.awtextra.AbsoluteConstraints(490, 60, 100, -1));

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "No", "ID Supplier", "Nama Supplier", "Alamat", "Jenis", "Telp", "Owner / CS", "Bank", "Bank A/N", "No. Rekening"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false
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
                    .addGroup(layout.createSequentialGroup()
                        .addContainerGap()
                        .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 386, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 289, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane1)
                    .addComponent(jPanel6, javax.swing.GroupLayout.DEFAULT_SIZE, 607, Short.MAX_VALUE))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, layout.createSequentialGroup()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel4, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(jLayeredPane1))
                    .addGroup(layout.createSequentialGroup()
                        .addGap(38, 38, 38)
                        .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 340, Short.MAX_VALUE)))
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void btSearchActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSearchActionPerformed
        loadData();
    }//GEN-LAST:event_btSearchActionPerformed

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
        if(evt.getClickCount() == 2){
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            loadEdit();
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void aNamaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aNamaFocusLost
        aNama.setText(aNama.getText().toUpperCase());        // TODO add your handling code here:
    }//GEN-LAST:event_aNamaFocusLost

    private void aAlamatFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aAlamatFocusLost
        aAlamat.setText(aAlamat.getText().toUpperCase());
    }//GEN-LAST:event_aAlamatFocusLost

    private void aOwnerFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aOwnerFocusLost
        aOwner.setText(aOwner.getText().toUpperCase());
    }//GEN-LAST:event_aOwnerFocusLost

    private void aNamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aNamaKeyPressed
        if(evt.getKeyCode() == 10 ) {
            aAlamat.requestFocus();
        }
    }//GEN-LAST:event_aNamaKeyPressed

    private void aAlamatKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aAlamatKeyPressed
        if(evt.getKeyCode() == 10 ) {
            aTelp.requestFocus();
        }        // TODO add your handling code here:
    }//GEN-LAST:event_aAlamatKeyPressed

    private void aTelpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aTelpKeyPressed
        if(evt.getKeyCode() == 10 ) {
            aOwner.requestFocus();
        }        // TODO add your handling code here:
    }//GEN-LAST:event_aTelpKeyPressed

    private void aOwnerKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aOwnerKeyPressed
        if(evt.getKeyCode() == 10 ) {
            aJenis.requestFocus();
        }        // TODO add your handling code here:
    }//GEN-LAST:event_aOwnerKeyPressed

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode() == 10 ) {
            btSave.doClick();
        }
    }//GEN-LAST:event_btSaveKeyPressed

    private void aNoRekFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aNoRekFocusLost
        rfa.FieldCapital(aNoRek);
    }//GEN-LAST:event_aNoRekFocusLost

    private void aNoRekKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aNoRekKeyPressed
        rfa.EnterNextFocus(evt, btSave);        // TODO add your handling code here:
    }//GEN-LAST:event_aNoRekKeyPressed

    private void aJenisFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aJenisFocusLost
        rfa.FieldCapital(aJenis);
    }//GEN-LAST:event_aJenisFocusLost

    private void aJenisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aJenisKeyPressed
        rfa.EnterNextFocus(evt, aBank);
    }//GEN-LAST:event_aJenisKeyPressed

    private void aBankAnFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aBankAnFocusLost
        rfa.FieldCapital(aBankAn);
    }//GEN-LAST:event_aBankAnFocusLost

    private void aBankAnKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aBankAnKeyPressed
        rfa.EnterNextFocus(evt, aNoRek);        // TODO add your handling code here:
    }//GEN-LAST:event_aBankAnKeyPressed

    private void aBankFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aBankFocusLost
        rfa.FieldCapital(aBank);
    }//GEN-LAST:event_aBankFocusLost

    private void aBankKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_aBankKeyPressed
        rfa.EnterNextFocus(evt, aBankAn);        // TODO add your handling code here:
    }//GEN-LAST:event_aBankKeyPressed

    private void aTelpFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_aTelpFocusLost
        rfa.FieldCapital(aTelp);
    }//GEN-LAST:event_aTelpFocusLost

    private void iNamaKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNamaKeyTyped
            // TODO add your handling code here:
    }//GEN-LAST:event_iNamaKeyTyped

    private void iOwnerKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iOwnerKeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_iOwnerKeyTyped

    private void iAlamatKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iAlamatKeyTyped
            // TODO add your handling code here:
    }//GEN-LAST:event_iAlamatKeyTyped

    private void iNamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNamaKeyPressed
        if(evt.getKeyCode() == 10){
            loadData();
        }
    }//GEN-LAST:event_iNamaKeyPressed

    private void iOwnerKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iOwnerKeyPressed
if(evt.getKeyCode() == 10){
            loadData();
        }        // TODO add your handling code here:
    }//GEN-LAST:event_iOwnerKeyPressed

    private void iAlamatKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iAlamatKeyPressed
if(evt.getKeyCode() == 10){
            loadData();
        }        // TODO add your handling code here:
    }//GEN-LAST:event_iAlamatKeyPressed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextArea aAlamat;
    private javax.swing.JTextField aBank;
    private javax.swing.JTextField aBankAn;
    private javax.swing.JTextField aJenis;
    private javax.swing.JTextField aKode;
    private javax.swing.JTextField aNama;
    private javax.swing.JTextField aNoRek;
    private javax.swing.JTextField aOwner;
    private javax.swing.JTextField aTelp;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btSearch;
    private javax.swing.JTextField iAlamat;
    private javax.swing.JTextField iNama;
    private javax.swing.JTextField iOwner;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTable jTable3;
    // End of variables declaration//GEN-END:variables
}
