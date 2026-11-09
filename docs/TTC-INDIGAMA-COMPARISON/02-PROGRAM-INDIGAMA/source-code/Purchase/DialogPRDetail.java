/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Purchase;

//import Tools.Koneksi;
//import Tools.dataControl1;
import DialogPublicTP.*;
import Main.Login;
import Main.MenuAdmin;
import Tools.ConnectDB;
import java.awt.Dialog;
import java.awt.Dimension;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.microedition.lcdui.Alert;
import javax.swing.JOptionPane;
import javax.swing.JScrollPane;
import javax.swing.JTable;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableRowSorter;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class DialogPRDetail extends javax.swing.JDialog {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbktset="",cnbkt_print="";
    int barisEdit=-1;
    String akses="TRANSAKSI OPRASIONAL KELUAR";
    public Boolean status = false;
    public String noinv,idmember,nama_member,ccodeset;
    private TableRowSorter sorter;
    private DefaultTableModel model;
    private JScrollPane scrollPane;
    
    public DialogPRDetail(String ccode, String cnbkt) {
        this.setModalityType(Dialog.ModalityType.APPLICATION_MODAL); 
        initComponents();
        setLocationRelativeTo(null);
        cnbktset = cnbkt;
        ccodeset=ccode;
        loadPOHeader(cnbkt,ccode);
        loadPO(cnbkt,ccode);
    }
    
    private void loadPOHeader(String cnbkt,String ccode){
        sql="select * from TranPurchaseReq where  cnbkt = '"+cnbkt+"' and ccode='"+ccode+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                pengaju.setText(rs.getString("nik_cname"));
                tgl.setText(rfa.DateString(MenuAdmin.keyssDef,rs.getDate("dtran"), "dd MMMM yyyy"));
                bukti.setText(rs.getString("cnbkt"));
                note.setText(rs.getString("cdesc"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(DialogPRDetail.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadPO(String cnbkt,String ccode){
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.getDataVector().removeAllElements();
        
        sql="select a.*,b.cname as supplier,c.cnameProduct from TranPurchaseReq_detail a left join MSupplier b on a.kode_supplier=b.kode_supplier\n" +
            "left join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct where a.cnbkt = '"+cnbkt+"' and a.ccode='"+ccode+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("supplier"),
                    rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameProduct"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),rs.getDouble("ntotal"),
                    rs.getString("nunit"),true,
                });
//                pengaju.setText(rs.getString("nik_cname"));
//                tgl.setText(rfa.DateString(MenuAdmin.keyssDef,rs.getDate("dtran"), "dd MMMM yyyy"));
//                bukti.setText(rs.getString("cnbkt"));
//                note.setText(rs.getString("cdesc"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(DialogPRDetail.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    private void reject(){
        sql="update TranPurchaseReq set capv = -1 ,capv_by='"+Login.user+"',capv_date=getdate() where cnbkt = '"+cnbktset+"' and ccode='"+ccodeset+"' ";
        con.queryUpdate(sql);
        
        sql="update TranPurchaseReq_detail set capv = -1  where cnbkt = '"+cnbktset+"' and ccode='"+ccodeset+"' ";
        con.queryUpdate(sql);
        
        JOptionPane.showMessageDialog(this,"Permintaan telah di tolak/reject", "Info", JOptionPane.INFORMATION_MESSAGE);
        
        status = true;
        dispose();
        
    }
    
    private void approve(){
        sql="update TranPurchaseReq set capv = 1,capv_by='"+Login.user+"',capv_date=getdate()  where cnbkt = '"+cnbktset+"' and ccode='"+ccodeset+"' ";
        con.queryUpdate(sql);
        
        for(int i=0; i<jTable1.getRowCount(); i++){
            sql="update TranPurchaseReq_detail set capv = 1  where id = '"+jTable1.getValueAt(i, 0).toString()+"' and ccode='"+ccodeset+"' ";
            con.queryUpdate(sql);
        }
        
        JOptionPane.showMessageDialog(this,"Permintaan berhasil di-setujui", "Info", JOptionPane.INFORMATION_MESSAGE);
            
        status = true;
        dispose();
        
    }
    /**
     * This method is called from within the constructor to initialize the form.
     * WARNING: Do NOT modify this code. The content of this method is always
     * regenerated by the Form Editor.
     */
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jPanel1 = new javax.swing.JPanel();
        jPanel2 = new javax.swing.JPanel();
        jPanel3 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        pengaju = new javax.swing.JTextField();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        jTextField3 = new javax.swing.JTextField();
        jLabel8 = new javax.swing.JLabel();
        jScrollPane4 = new javax.swing.JScrollPane();
        note = new javax.swing.JTextArea();
        tgl = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jLabel6 = new javax.swing.JLabel();
        bukti = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        jButton2 = new javax.swing.JButton();

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());
        jPanel1.add(jPanel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 10, -1, 60));

        jPanel3.setBackground(new java.awt.Color(153, 204, 255));

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 500, Short.MAX_VALUE)
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 0, Short.MAX_VALUE)
        );

        jPanel1.add(jPanel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 50, -1, 5));

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel1.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 20, 30, -1));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Detail Purchase Request");
        jPanel1.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 20, -1, -1));

        pengaju.setEditable(false);
        pengaju.setBackground(new java.awt.Color(204, 255, 255));
        pengaju.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jPanel1.add(pengaju, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 90, 280, -1));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Pengaju");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 90, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Bukti");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 130, 72, 20));

        jTextField3.setEditable(false);
        jTextField3.setBackground(new java.awt.Color(153, 153, 255));
        jTextField3.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField3.setForeground(new java.awt.Color(255, 255, 255));
        jTextField3.setText("Detail Order");
        jPanel1.add(jTextField3, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 250, 280, -1));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Keterangan /Keperluan");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 150, 130, -1));

        jScrollPane4.setHorizontalScrollBarPolicy(javax.swing.ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);

        note.setEditable(false);
        note.setBackground(new java.awt.Color(204, 255, 255));
        note.setColumns(20);
        note.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        note.setLineWrap(true);
        note.setRows(5);
        note.setWrapStyleWord(true);
        note.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                noteFocusLost(evt);
            }
        });
        note.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                noteKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                noteKeyTyped(evt);
            }
        });
        jScrollPane4.setViewportView(note);

        jPanel1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 150, 330, 80));

        tgl.setEditable(false);
        tgl.setBackground(new java.awt.Color(204, 255, 255));
        tgl.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jPanel1.add(tgl, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 110, 280, -1));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Supplier", "Group", "Product", "Nama Product", "Qty", "Harga", "Total", "Unit", "Check Approve"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Boolean.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, true
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
        }

        jPanel1.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 270, 880, 210));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Tgl. Transaksi");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 110, 100, 20));

        bukti.setEditable(false);
        bukti.setBackground(new java.awt.Color(204, 255, 255));
        bukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jPanel1.add(bukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 130, 280, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Close_Window_16px.png"))); // NOI18N
        jButton1.setText("Tolak");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 500, 110, 30));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Ok_15px.png"))); // NOI18N
        jButton2.setText("Setujui");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 500, 110, 30));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 966, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 558, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void noteFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_noteFocusLost
        note.setText(note.getText().toUpperCase());
    }//GEN-LAST:event_noteFocusLost

    private void noteKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_noteKeyPressed

    }//GEN-LAST:event_noteKeyPressed

    private void noteKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_noteKeyTyped

    }//GEN-LAST:event_noteKeyTyped

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin menolak pengajuan tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            
            if(pil == 0)reject();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin menyetujui pengajuan tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            
            if(pil == 0) approve();
    }//GEN-LAST:event_jButton2ActionPerformed

  

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField bukti;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton2;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JTable jTable1;
    private javax.swing.JTextField jTextField3;
    private javax.swing.JTextArea note;
    private javax.swing.JTextField pengaju;
    private javax.swing.JTextField tgl;
    // End of variables declaration//GEN-END:variables
}
