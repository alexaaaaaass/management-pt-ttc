/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package DialogPublicTP;

import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.Dialog;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.RowFilter;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableRowSorter;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class DialogFormPinjaman extends javax.swing.JDialog {

    public Boolean status = false;
    public String kode,nama,gudang,nik,cname;
    public Double nominal=0.0;
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int nomor=0;
    int flag=0;
    public DialogFormPinjaman(String ccode) {
        this.setModalityType(Dialog.ModalityType.APPLICATION_MODAL); 
        initComponents();
        setLocationRelativeTo(null);
        
        gudang = ccode;
        
        listData();
        iNama.requestFocus();
    }

    private void listData(){
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.getDataVector().removeAllElements();
        jTable1.removeAll();
        
        sql = "select * from formpinjaman where ccode='"+gudang+"' ";
   
       
        rs = con.queryResult(sql+" order  by cnbkt");
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    String.valueOf(nomor++), 
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rs.getString("cnik"),
                    rs.getString("cname"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"), "dd/MM/yyyy"), 
                    rs.getDouble("namnt"),
                    rs.getDouble("namnt_cicilan"),
                    rs.getDouble("cwaktu_bulan"), 
                    rs.getString("cnote"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(DialogFormPinjaman.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns();
        
    }
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jPanel1 = new javax.swing.JPanel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        iOwner = new javax.swing.JTextField();
        iNama = new javax.swing.JTextField();

        setDefaultCloseOperation(javax.swing.WindowConstants.DISPOSE_ON_CLOSE);
        setTitle("List Supplier");

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "No", "Gudang", "Bukti Pinjaman", "NIK", "Karyawan", "Tanggal Pengajuan", "Nominal", "Cicilan perbulan", "Keterangan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable1.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(50);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(50);
        }

        jPanel1.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 60, 1020, 430));

        iOwner.setEditable(false);
        iOwner.setBackground(new java.awt.Color(153, 153, 153));
        iOwner.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        iOwner.setForeground(new java.awt.Color(255, 255, 255));
        iOwner.setText("  SEARCH");
        iOwner.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iOwnerKeyTyped(evt);
            }
        });
        jPanel1.add(iOwner, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 30, 160, 30));

        iNama.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        iNama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                iNamaKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iNamaKeyTyped(evt);
            }
        });
        jPanel1.add(iNama, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 30, 530, 30));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 1083, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 513, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount() == 2) {
            gudang = jTable1.getValueAt(jTable1.getSelectedRow(), 1).toString();
            kode = jTable1.getValueAt(jTable1.getSelectedRow(), 2).toString();
            nik = jTable1.getValueAt(jTable1.getSelectedRow(), 3).toString();
            cname = jTable1.getValueAt(jTable1.getSelectedRow(), 4).toString();
            nominal = Double.valueOf(jTable1.getValueAt(jTable1.getSelectedRow(),6).toString());
            
            status = true;
            dispose();
//            aKode.setText(jTable1.getValueAt(jTable1.getSelectedRow(), 1).toString());
           
        }// TODO add your handling code here:
    }//GEN-LAST:event_jTable1MouseClicked

    private void iNamaKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNamaKeyTyped
            // TODO add your handling code here:
    }//GEN-LAST:event_iNamaKeyTyped

    private void iOwnerKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iOwnerKeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_iOwnerKeyTyped

    private void iNamaKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNamaKeyReleased
        DefaultTableModel tablee = (DefaultTableModel)jTable1.getModel();
        String search = iNama.getText();
        TableRowSorter<DefaultTableModel> tr = new TableRowSorter<DefaultTableModel>(tablee);
        jTable1.setRowSorter(tr);
        tr.setRowFilter(RowFilter.regexFilter(search.toUpperCase()));
    }//GEN-LAST:event_iNamaKeyReleased

    
    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField iNama;
    private javax.swing.JTextField iOwner;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JTable jTable1;
    // End of variables declaration//GEN-END:variables
}
