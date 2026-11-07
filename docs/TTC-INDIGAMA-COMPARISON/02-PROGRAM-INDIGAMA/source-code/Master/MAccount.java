/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Master;

import Accounting.*;
import Delivery.*;
import DialogPublicTP.DialogAccountClass;
import Main.DefaultProgram;
import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Date;
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
public class MAccount extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="MASTER ACCOUNT";
    public MAccount(String mode) {
        initComponents();
        
        push_button();
        deff();
        loadData();
        
        
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
        TextGudang.setText(DefaultProgram.deffProgram.get("ccode"));
        TextGudang.requestFocus();
        TextKode.requestFocus();
        btDelete.setVisible(false);
        btSave.setText("Save");
        TextPeriode.setText("202301");
        id = "";
        jTabbedPane1.setTitleAt(1, "Add");
    }
    private void clear(){
        rfa.empty(MenuAdmin.keyssDef,TextKode,TextKet,TGroup,TGroup1,TextDebet,TextKredit,
                TextKeterangan,TextGudang,TextGudang1,TextPeriode,TextNominal);
    }
   
    private void loadData(){
        sql = "select * from maccount where caccnt like '%"+tkodes.getText()+"%' and cdesc like '%"+tnamas.getText()+"%' "
                + "order by caccnt,cdesc ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        try {
            while(rs.next()){
                String st = "Tidak Aktif";
                if(rs.getString("fstatus").equals("1")) {
                    st = "Aktif";
                }
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("caccnt"),rs.getString("cdesc"),rs.getString("fAccount"),
                    rs.getString("cPeriod"),
                    rs.getDouble("NDebet"),rs.getDouble("NCredit"),rs.getDouble("NNominal")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        jTabbedPane1.setSelectedIndex(0);
    }
    private void loadEdit(){
        sql = "select * from maccount where id = '"+id+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                TextKode.setText(rs.getString("caccnt"));
                TextKet.setText(rs.getString("cdesc"));
                TextPeriode.setText(rs.getString("cPeriod"));
                TextDebet.setText(rs.getString("NDebet"));
                TextKredit.setText(rs.getString("NCredit"));
                TGroup.setText(rs.getString("fAccount"));
                TextNominal.setText(rs.getString("NNominal"));
             
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
        Boolean  cekakun=true;
        sql="select * from maccount where caccnt = '"+TextKode.getText()+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                cekakun = true;
            }
        } catch (SQLException ex) {
            Logger.getLogger(MAccount.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        if(!cekakun){
            JOptionPane.showMessageDialog(this,"Nomor Akun sudah terdaftar", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        
        String status = "0";
        if(jComboBox1.getSelectedIndex() == 0) {
            status = "1";
        }
        sql="INSERT INTO maccount (caccnt,cdesc,ccode,fAccount,cPeriod\n" +
            ",NDebet,NCredit,fstatus\n" +
            ",cusername,cnote,NNominal)\n" +
            "VALUES\n" +
            "('"+TextKode.getText()+"','"+TextKet.getText()+"','"+TextGudang.getText()+"','"+
                TGroup.getText()+"','"+TextPeriode.getText()+"','"+TextDebet.getText()+"','"+TextKredit.getText()+"','"+status+"','"+Login.user+"','"+TextKeterangan.getText()+"',"
                + "'"+TextNominal.getText()+"')";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
   
    private void update(){
        String status = "0";
        if(jComboBox1.getSelectedIndex() == 0) {
            status = "1";
        }
        sql="update maccount set caccnt='"+TextKode.getText()+"',cdesc='"+TextKet.getText()+"',ccode='"+TextGudang.getText()+"',"
                + "fAccount='"+TGroup.getText()+"',cPeriod='"+TextPeriode.getText()+"'\n" +
            ",NDebet='"+TextDebet.getText()+"',NCredit='"+TextKredit.getText()+"',fstatus='"+status+"'\n" +
            ",cusername='"+Login.user+"',cnote='"+TextKeterangan.getText()+"',NNominal='"+TextNominal.getText()+"' where id = '"+id+"'";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
            loadData();
            clear();
            deff();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void delete(){
        sql="delete from  maccount  where id = '"+id+"' ";
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
        java.awt.GridBagConstraints gridBagConstraints;

        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel12 = new javax.swing.JLabel();
        tkodes = new javax.swing.JTextField();
        tnamas = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel7 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        jLabel9 = new javax.swing.JLabel();
        jLabel13 = new javax.swing.JLabel();
        TextKode = new javax.swing.JTextField();
        TextKet = new javax.swing.JTextField();
        TGroup1 = new javax.swing.JTextField();
        TextDebet = new javax.swing.JTextField();
        TextKredit = new javax.swing.JTextField();
        TextPeriode = new javax.swing.JTextField();
        TextGudang1 = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        TextKeterangan = new javax.swing.JTextField();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        TextGudang = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        TGroup = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        jLabel8 = new javax.swing.JLabel();
        jComboBox1 = new javax.swing.JComboBox();
        jLabel16 = new javax.swing.JLabel();
        TextNominal = new javax.swing.JTextField();

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Master Account (COA)");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel12.setText("Kode Akun");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));
        jPanel6.add(tkodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 140, -1));
        jPanel6.add(tnamas, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 270, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, 90, -1));

        jLabel15.setText("Nama");
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
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No Accounting (COA)", "Keterangan", "Group", "Periode", "Debet", "Kredit", "Value Default"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false
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
            jTable3.getColumnModel().getColumn(5).setMinWidth(0);
            jTable3.getColumnModel().getColumn(5).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(6).setMinWidth(0);
            jTable3.getColumnModel().getColumn(6).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(7).setMinWidth(0);
            jTable3.getColumnModel().getColumn(7).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
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
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 323, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel5.setLayout(new java.awt.GridBagLayout());

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Periode");
        jLabel7.setToolTipText("");
        jLayeredPane1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Gudang");
        jLayeredPane1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 40, 72, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("Kode Akuntansi");
        jLayeredPane1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 100, 100, 20));

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Nama Akun");
        jLabel3.setToolTipText("");
        jLayeredPane1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 130, 110, 20));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Group Akun");
        jLayeredPane1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, 110, 20));

        jLabel9.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel9.setText("Saldo Debet");
        jLabel9.setToolTipText("");
        jLayeredPane1.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 160, 110, 20));

        jLabel13.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel13.setText("Nominal Default");
        jLayeredPane1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 220, 130, 20));

        TextKode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TextKodeFocusLost(evt);
            }
        });
        TextKode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextKodeKeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                TextKodeKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TextKodeKeyTyped(evt);
            }
        });
        jLayeredPane1.add(TextKode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 100, 110, 21));

        TextKet.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TextKetFocusLost(evt);
            }
        });
        TextKet.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextKetKeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                TextKetKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TextKetKeyTyped(evt);
            }
        });
        jLayeredPane1.add(TextKet, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 130, 350, 21));

        TGroup1.setEditable(false);
        TGroup1.setBackground(new java.awt.Color(153, 153, 255));
        TGroup1.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        TGroup1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TGroup1KeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                TGroup1KeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TGroup1KeyTyped(evt);
            }
        });
        jLayeredPane1.add(TGroup1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 70, 230, 21));

        TextDebet.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextDebetKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TextDebetKeyTyped(evt);
            }
        });
        jLayeredPane1.add(TextDebet, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 160, 110, 21));

        TextKredit.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextKreditKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TextKreditKeyTyped(evt);
            }
        });
        jLayeredPane1.add(TextKredit, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 190, 110, 21));

        TextPeriode.setEditable(false);
        TextPeriode.setBackground(new java.awt.Color(153, 153, 255));
        TextPeriode.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        TextPeriode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextPeriodeKeyPressed(evt);
            }
        });
        jLayeredPane1.add(TextPeriode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 10, 110, 21));

        TextGudang1.setEditable(false);
        TextGudang1.setBackground(new java.awt.Color(153, 153, 255));
        TextGudang1.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        TextGudang1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextGudang1KeyPressed(evt);
            }
        });
        jLayeredPane1.add(TextGudang1, new org.netbeans.lib.awtextra.AbsoluteConstraints(272, 40, 230, 21));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Keterangan");
        jLayeredPane1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 250, 72, 20));

        TextKeterangan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TextKeteranganFocusLost(evt);
            }
        });
        TextKeterangan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextKeteranganKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                TextKeteranganKeyTyped(evt);
            }
        });
        jLayeredPane1.add(TextKeterangan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 250, 350, 21));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 330, 97, -1));

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
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 330, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 330, 97, -1));

        TextGudang.setEditable(false);
        TextGudang.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                TextGudangFocusLost(evt);
            }
        });
        jLayeredPane1.add(TextGudang, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 40, 80, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.setToolTipText("");
        jLayeredPane1.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 40, 30, -1));

        TGroup.setEditable(false);
        jLayeredPane1.add(TGroup, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 70, 80, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.setToolTipText("");
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 70, 30, -1));

        jLabel8.setText("Status");
        jLayeredPane1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 280, -1, 20));

        jComboBox1.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Aktif", "Tidak Aktif" }));
        jLayeredPane1.add(jComboBox1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 280, 110, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Saldo Kredit");
        jLayeredPane1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 190, 72, 20));
        jLayeredPane1.add(TextNominal, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 220, 110, -1));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.ipadx = 28;
        gridBagConstraints.ipady = 19;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        gridBagConstraints.insets = new java.awt.Insets(50, 130, 54, 180);
        jPanel5.add(jLayeredPane1, gridBagConstraints);

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
                        .addGap(0, 0, Short.MAX_VALUE))))
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGap(4, 4, 4)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1))
        );
    }// </editor-fold>//GEN-END:initComponents

    private void TextKetKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKetKeyPressed
        if(evt.getKeyCode()==10) jButton13.doClick();
    }//GEN-LAST:event_TextKetKeyPressed

    private void TextKetKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKetKeyReleased
        
    }//GEN-LAST:event_TextKetKeyReleased

    private void TextKetKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKetKeyTyped
       
    }//GEN-LAST:event_TextKetKeyTyped

    private void TGroup1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TGroup1KeyPressed
        if (evt.getKeyCode()==10){TextDebet.requestFocus();}
    }//GEN-LAST:event_TGroup1KeyPressed

    private void TGroup1KeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TGroup1KeyReleased
      
    }//GEN-LAST:event_TGroup1KeyReleased

    private void TGroup1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TGroup1KeyTyped
        
    }//GEN-LAST:event_TGroup1KeyTyped

    private void TextDebetKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextDebetKeyPressed
        if (evt.getKeyCode()==10){TextKredit.requestFocus();}
    }//GEN-LAST:event_TextDebetKeyPressed

    private void TextDebetKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextDebetKeyTyped
        
    }//GEN-LAST:event_TextDebetKeyTyped

    private void TextKreditKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKreditKeyPressed
       if (evt.getKeyCode()==10){btSave.requestFocus();}
    }//GEN-LAST:event_TextKreditKeyPressed

    private void TextKreditKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKreditKeyTyped
       
    }//GEN-LAST:event_TextKreditKeyTyped

    private void TextPeriodeKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextPeriodeKeyPressed

    }//GEN-LAST:event_TextPeriodeKeyPressed

    private void TextGudang1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextGudang1KeyPressed
        if (evt.getKeyCode()==10){TextKode.requestFocus();}
    }//GEN-LAST:event_TextGudang1KeyPressed

    private void TextKeteranganKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKeteranganKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_TextKeteranganKeyPressed

    private void TextKeteranganKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKeteranganKeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_TextKeteranganKeyTyped

    private void TextKodeKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKodeKeyTyped

    }//GEN-LAST:event_TextKodeKeyTyped

    private void TextKodeKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKodeKeyReleased

    }//GEN-LAST:event_TextKodeKeyReleased

    private void TextKodeKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextKodeKeyPressed
        if (evt.getKeyCode()==10){TextKet.requestFocus();}
    }//GEN-LAST:event_TextKodeKeyPressed

    private void TextKodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TextKodeFocusLost
        if(!"".equals(TextKode.getText().trim())){
            if(!TextKode.getText().contains(".")){
                try {
                    TextKode.setText(TextKode.getText().substring(0, 3)+"."+TextKode.getText().substring(3));
                } catch (Exception ex) {
                    System.out.println(ex);
                }
            }
        }
    }//GEN-LAST:event_TextKodeFocusLost

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogAccountClass a = new DialogAccountClass();
        a.setVisible(true);
        if(!a.classid.equals("")) {
            TGroup.setText(a.classid);
            TGroup1.setText(a.classname);
            TextDebet.requestFocus();
        }
    }//GEN-LAST:event_jButton13ActionPerformed

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

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 2){
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            loadEdit();
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        clear();
        deff();
    }//GEN-LAST:event_btCancelActionPerformed

    private void TextGudangFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TextGudangFocusLost
        sql = "select * from mgudang where ccode = '"+TextGudang.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                TextGudang1.setText(rs.getString("cname"));
            }
        } catch (Exception e){}
    }//GEN-LAST:event_TextGudangFocusLost

    private void TextKetFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TextKetFocusLost
        rfa.FieldCapital(TextKet);
    }//GEN-LAST:event_TextKetFocusLost

    private void TextKeteranganFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_TextKeteranganFocusLost
        rfa.FieldCapital(TextKeterangan);
    }//GEN-LAST:event_TextKeteranganFocusLost

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if (evt.getKeyCode()==10)   btSave.doClick();     // TODO add your handling code here:
    }//GEN-LAST:event_btSaveKeyPressed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField TGroup;
    private javax.swing.JTextField TGroup1;
    private javax.swing.JTextField TextDebet;
    private javax.swing.JTextField TextGudang;
    private javax.swing.JTextField TextGudang1;
    private javax.swing.JTextField TextKet;
    private javax.swing.JTextField TextKeterangan;
    private javax.swing.JTextField TextKode;
    private javax.swing.JTextField TextKredit;
    private javax.swing.JTextField TextNominal;
    private javax.swing.JTextField TextPeriode;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton7;
    private javax.swing.JComboBox jComboBox1;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField tkodes;
    private javax.swing.JTextField tnamas;
    // End of variables declaration//GEN-END:variables
}
