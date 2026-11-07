/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Master;

import Delivery.*;
import Accounting.*;
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
import javax.swing.JTable;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableModel;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class MGudang extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    public MGudang(String mode) {
        initComponents();
        loadData();
        LastCode();
        push_button();
        deff();
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
        jTabbedPane1.setTitleAt(1, "Add");
    }
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,TCode,TName,TAlamat,Tkota,TKec,TDesa,TKodepos,TTelp,TFax,THead,THeadAcc);
        rfa.empty(MenuAdmin.keyssDef,TKet);
    }
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select count(ccode) as lastno from mgudang ");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        TCode.setText("WR"+rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql = "select * from mgudang where ccode like '%"+TCodeS.getText()+"%' and cname like '%"+TNameS.getText()+"%' "
                + "order by ccode ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("ccode"),rs.getString("cname"),rs.getString("caddr"),rs.getString("ctelp"),rs.getString("cfax"),
                    rs.getString("chead"),rs.getString("chead_acc"),rs.getString("cnote")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        jTabbedPane1.setSelectedIndex(0);
    }
    private void loadEdit(){
        sql = "select * from mgudang where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                TCode.setText(rs.getString("ccode"));
                TName.setText(rs.getString("cname"));
                TAlamat.setText(rs.getString("caddr"));
                Tkota.setText(rs.getString("ccity"));
                TKec.setText(rs.getString("ckecamatan"));
                TDesa.setText(rs.getString("cdesa"));
                TKodepos.setText(rs.getString("cpostalcode"));
                TTelp.setText(rs.getString("ctelp"));
                TFax.setText(rs.getString("cfax"));
                THead.setText(rs.getString("chead"));
                THeadAcc.setText(rs.getString("chead_acc"));
                TKet.setText(rs.getString("cnote"));
                
                jTabbedPane1.setSelectedIndex(1);
                jTabbedPane1.setTitleAt(1, "Edit");
                btSave.setText("Update");
                btDelete.setVisible(true);
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void save(){
        sql="INSERT INTO MGudang (ccode,cname,caddr,ccity,ckecamatan,cdesa,cpostalcode,ctelp,cfax,chead,chead_acc,cnote,username)\n" +
            "VALUES\n" +
            "('"+TCode.getText()+"','"+TName.getText().replace("'", "''")+"','"+TAlamat.getText().replace("'", "''")+"','"+Tkota.getText().replace("'", "''")+"','"+TKec.getText().replace("'", "''")+"','"+TDesa.getText().replace("'", "''")+"','"+TKodepos.getText().replace("'", "''")+"','"+TTelp.getText().replace("'", "''")+"','"+TFax.getText().replace("'", "''")+"','"+THead.getText().replace("'", "''")+"','"+THeadAcc.getText().replace("'", "''")+"','"+TKet.getText().replace("\n", "").replace("\r", "")+"','"+Login.user+"')";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Setting Server Tidak Dapat Disimpan", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            LastCode();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
   
    private void update(){
        sql="UPDATE MGudang set ccode='"+TCode.getText()+"',cname='"+TName.getText().replace("'", "''")+"',caddr='"+TAlamat.getText().replace("'", "''")+"',"
                + "ccity='"+Tkota.getText().replace("'", "''")+"',ckecamatan='"+TKec.getText().replace("'", "''")+"',"
                + "cdesa='"+TDesa.getText().replace("'", "''")+"',cpostalcode='"+TKodepos.getText().replace("'", "''")+"',"
                + "ctelp='"+TTelp.getText().replace("'", "''")+"',cfax='"+TFax.getText().replace("'", "''")+"',"
                + "chead='"+THead.getText().replace("'", "''")+"',chead_acc='"+THeadAcc.getText().replace("'", "''")+"',"
                + "cnote='"+TKet.getText().replace("\n", "").replace("\r", "")+"',username='"+Login.user+"' where id = '"+id+"' ";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Setting Server Tidak Dapat Disimpan", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            LastCode();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void delete(){
        sql="delete from  MGudang  where id = '"+id+"' ";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Setting Server Tidak Dapat Disimpan", JOptionPane.INFORMATION_MESSAGE);
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
        java.awt.GridBagConstraints gridBagConstraints;

        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel12 = new javax.swing.JLabel();
        TCodeS = new javax.swing.JTextField();
        TNameS = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        TCode = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        TName = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        TDesa = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        jLabel19 = new javax.swing.JLabel();
        TAlamat = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        Tkota = new javax.swing.JTextField();
        TKec = new javax.swing.JTextField();
        jLabel23 = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        TKodepos = new javax.swing.JTextField();
        jLabel26 = new javax.swing.JLabel();
        THead = new javax.swing.JTextField();
        jLabel27 = new javax.swing.JLabel();
        TTelp = new javax.swing.JTextField();
        jLabel28 = new javax.swing.JLabel();
        TFax = new javax.swing.JTextField();
        jLabel29 = new javax.swing.JLabel();
        THeadAcc = new javax.swing.JTextField();
        jLabel30 = new javax.swing.JLabel();
        jScrollPane4 = new javax.swing.JScrollPane();
        TKet = new javax.swing.JTextArea();

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Master Gudang");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel12.setText("Kode Log/Gudang");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));
        jPanel6.add(TCodeS, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 30, 260, -1));
        jPanel6.add(TNameS, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 60, 370, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 30, 90, -1));

        jLabel15.setText("Nama Gudang");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField11.setText("Double Click to Edit");

        jTextField12.setEditable(false);
        jTextField12.setBackground(new java.awt.Color(204, 204, 255));
        jTextField12.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField12.setText("Total Data : 100");

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Kode Gudang", "Nama Gudang", "Alamat", "Telp", "Fax", "Kepala Gudang", "Kepala Accounting", "Keterangan"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false
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
        jScrollPane3.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(0).setMinWidth(0);
            jTable3.getColumnModel().getColumn(0).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1093, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jScrollPane3))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 304, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel5.setLayout(new java.awt.GridBagLayout());

        jPanel1.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang/Log");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 130, 20));

        TCode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TCodeFocusLost(evt);
            }
        });
        jPanel1.add(TCode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 110, -1));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(630, 260, 97, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(740, 260, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(850, 260, 97, -1));

        TName.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TNameFocusLost(evt);
            }
        });
        TName.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TNameKeyPressed(evt);
            }
        });
        jPanel1.add(TName, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 270, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Nama Gudang");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 110, 20));

        TDesa.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TDesaFocusLost(evt);
            }
        });
        TDesa.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TDesaKeyPressed(evt);
            }
        });
        jPanel1.add(TDesa, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 270, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Desa");
        jPanel1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 110, 20));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Alamat ");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 110, 20));

        TAlamat.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TAlamatFocusLost(evt);
            }
        });
        TAlamat.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TAlamatKeyPressed(evt);
            }
        });
        jPanel1.add(TAlamat, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 270, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Kota/Kab");
        jPanel1.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 110, 20));

        Tkota.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TkotaFocusLost(evt);
            }
        });
        Tkota.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TkotaKeyPressed(evt);
            }
        });
        jPanel1.add(Tkota, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 270, -1));

        TKec.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TKecFocusLost(evt);
            }
        });
        TKec.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TKecKeyPressed(evt);
            }
        });
        jPanel1.add(TKec, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 270, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Kecamatan");
        jPanel1.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Kode Pos");
        jPanel1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, 110, 20));

        TKodepos.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TKodeposFocusLost(evt);
            }
        });
        TKodepos.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TKodeposKeyPressed(evt);
            }
        });
        jPanel1.add(TKodepos, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 270, -1));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Kepala Gudang");
        jPanel1.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 80, 110, 20));

        THead.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                THeadFocusLost(evt);
            }
        });
        THead.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                THeadKeyPressed(evt);
            }
        });
        jPanel1.add(THead, new org.netbeans.lib.awtextra.AbsoluteConstraints(660, 80, 280, -1));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("No. Telp");
        jPanel1.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 20, 110, 20));

        TTelp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TTelpKeyPressed(evt);
            }
        });
        jPanel1.add(TTelp, new org.netbeans.lib.awtextra.AbsoluteConstraints(660, 20, 280, -1));

        jLabel28.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel28.setText("No. Fax");
        jPanel1.add(jLabel28, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 50, 110, 20));

        TFax.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TFaxKeyPressed(evt);
            }
        });
        jPanel1.add(TFax, new org.netbeans.lib.awtextra.AbsoluteConstraints(660, 50, 280, -1));

        jLabel29.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel29.setText("Kepala Accounting");
        jPanel1.add(jLabel29, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 110, 110, 20));

        THeadAcc.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                THeadAccFocusLost(evt);
            }
        });
        THeadAcc.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                THeadAccKeyPressed(evt);
            }
        });
        jPanel1.add(THeadAcc, new org.netbeans.lib.awtextra.AbsoluteConstraints(660, 110, 280, -1));

        jLabel30.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel30.setText("Keterangan Tambahan");
        jPanel1.add(jLabel30, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 140, 130, 20));

        jScrollPane4.setHorizontalScrollBarPolicy(javax.swing.ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);

        TKet.setColumns(20);
        TKet.setFont(new java.awt.Font("sansserif", 0, 11)); // NOI18N
        TKet.setLineWrap(true);
        TKet.setRows(5);
        TKet.setWrapStyleWord(true);
        TKet.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TKetFocusLost(evt);
            }
        });
        TKet.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TKetKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TKetKeyTyped(evt);
            }
        });
        jScrollPane4.setViewportView(TKet);

        jPanel1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(660, 140, 280, 80));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.ipadx = 33;
        gridBagConstraints.ipady = 17;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        gridBagConstraints.insets = new java.awt.Insets(11, 10, 28, 10);
        jPanel5.add(jPanel1, gridBagConstraints);

        jTabbedPane1.addTab("Add/Edit", jPanel5);

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

    private void TKetFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TKetFocusLost
         TKet.setText(rfa.toUpper(MenuAdmin.keyssDef,TKet.getText()));  
    }//GEN-LAST:event_TKetFocusLost

    private void TKetKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TKetKeyPressed

    }//GEN-LAST:event_TKetKeyPressed

    private void TKetKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TKetKeyTyped

    }//GEN-LAST:event_TKetKeyTyped

    private void TCodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TCodeFocusLost
        TCode.setText(rfa.toUpper(MenuAdmin.keyssDef,TCode.getText()));
    }//GEN-LAST:event_TCodeFocusLost

    private void TNameFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TNameFocusLost
        TName.setText(rfa.toUpper(MenuAdmin.keyssDef,TName.getText()));
    }//GEN-LAST:event_TNameFocusLost

    private void TAlamatFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TAlamatFocusLost
         TAlamat.setText(rfa.toUpper(MenuAdmin.keyssDef,TAlamat.getText()));
    }//GEN-LAST:event_TAlamatFocusLost

    private void TkotaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TkotaFocusLost
         Tkota.setText(rfa.toUpper(MenuAdmin.keyssDef,Tkota.getText()));       // TODO add your handling code here:
    }//GEN-LAST:event_TkotaFocusLost

    private void TKecFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TKecFocusLost
         TKec.setText(rfa.toUpper(MenuAdmin.keyssDef,TKec.getText()));          // TODO add your handling code here:
    }//GEN-LAST:event_TKecFocusLost

    private void TDesaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TDesaFocusLost
         TDesa.setText(rfa.toUpper(MenuAdmin.keyssDef,TDesa.getText()));          // TODO add your handling code here:
    }//GEN-LAST:event_TDesaFocusLost

    private void TKodeposFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TKodeposFocusLost
         TKodepos.setText(rfa.toUpper(MenuAdmin.keyssDef,TKodepos.getText()));          // TODO add your handling code here:
    }//GEN-LAST:event_TKodeposFocusLost

    private void THeadFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_THeadFocusLost
         THead.setText(rfa.toUpper(MenuAdmin.keyssDef,THead.getText()));          // TODO add your handling code here:
    }//GEN-LAST:event_THeadFocusLost

    private void THeadAccFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_THeadAccFocusLost
         THeadAcc.setText(rfa.toUpper(MenuAdmin.keyssDef,THeadAcc.getText()));          // TODO add your handling code here:
    }//GEN-LAST:event_THeadAccFocusLost

    private void TNameKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TNameKeyPressed
        if(evt.getKeyCode()==10) TAlamat.requestFocus();
    }//GEN-LAST:event_TNameKeyPressed

    private void TAlamatKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TAlamatKeyPressed
        if(evt.getKeyCode()==10) Tkota.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_TAlamatKeyPressed

    private void TkotaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TkotaKeyPressed
        if(evt.getKeyCode()==10)  TKec.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_TkotaKeyPressed

    private void TKecKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TKecKeyPressed
        if(evt.getKeyCode()==10) TDesa.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_TKecKeyPressed

    private void TDesaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TDesaKeyPressed
        if(evt.getKeyCode()==10) TKodepos.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_TDesaKeyPressed

    private void TKodeposKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TKodeposKeyPressed
        if(evt.getKeyCode()==10) TTelp.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_TKodeposKeyPressed

    private void TTelpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TTelpKeyPressed
        if(evt.getKeyCode()==10) TFax.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_TTelpKeyPressed

    private void TFaxKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TFaxKeyPressed
        if(evt.getKeyCode()==10) THead.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_TFaxKeyPressed

    private void THeadKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_THeadKeyPressed
        if(evt.getKeyCode()==10) THeadAcc.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_THeadKeyPressed

    private void THeadAccKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_THeadAccKeyPressed
        if(evt.getKeyCode()==10) TAlamat.requestFocus();        // TODO add your handling code here:
    }//GEN-LAST:event_THeadAccKeyPressed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(TCode.getText().toString().length() != 5) {
                JOptionPane.showMessageDialog(this,"Kode Gudang harus 5 Digit", "Warning", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(btSave.getText().equals("Save")) {
                if(con.akses("MASTER GUDANG", rfa.INSERT)) {
                    save();
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses("MASTER GUDANG", rfa.UPDATE)) {
                    update();
                }
            }
        }
        
        
    }//GEN-LAST:event_btSaveActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses("MASTER GUDANG", rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 2){
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            loadEdit();
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        deff();
        clear();
        LastCode();
    }//GEN-LAST:event_btCancelActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField TAlamat;
    private javax.swing.JTextField TCode;
    private javax.swing.JTextField TCodeS;
    private javax.swing.JTextField TDesa;
    private javax.swing.JTextField TFax;
    private javax.swing.JTextField THead;
    private javax.swing.JTextField THeadAcc;
    private javax.swing.JTextField TKec;
    private javax.swing.JTextArea TKet;
    private javax.swing.JTextField TKodepos;
    private javax.swing.JTextField TName;
    private javax.swing.JTextField TNameS;
    private javax.swing.JTextField TTelp;
    private javax.swing.JTextField Tkota;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton7;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel26;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLabel jLabel28;
    private javax.swing.JLabel jLabel29;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel30;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    // End of variables declaration//GEN-END:variables
}
