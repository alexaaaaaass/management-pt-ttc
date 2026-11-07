/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Production;

import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogWO;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Printer.PrinterUtility;
import Report.*;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.RowFilter;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableRowSorter;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JRException;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class ProduksiInputUtility extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    
    String ID;
    DecimalFormat dFormat = new DecimalFormat("#,##0.00");
    int nomor = 0;
    
    String ccode="",akses="PRODUKSI INPUT";
    
    public ProduksiInputUtility() {
        initComponents();
          
    }
    
    private void load_stepinput(){
        sql="select a.*,c.cplot,d.cname as operator,\n" +
            "case when a.cgood_unit='LEMBAR' then convert(varchar,a.cgood)+ ' Lembar ('+convert(varchar,a.cgood*c.cplot)+' Pcs)' else convert(varchar,a.cgood)+' Pcs' end as good,\n" +
            "case when a.cwaste_unit='LEMBAR' then convert(varchar,a.cwaste)+ ' Lembar ('+convert(varchar,a.cwaste*c.cplot)+' Pcs)' else convert(varchar,a.cwaste)+' Pcs' end as waste\n" +
            "from PProdSteps a\n" +
            "join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join BOMProduct c on b.cnbkt_bom=c.cnbkt\n" +
            "join MKaryawan d on a.cnik=d.nik\n" +
            "left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "where a.ccode='"+ccode+"' and a.cnbkt='"+two.getText()+"'   \n"+
            "order by e.urut,a.dtran";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        try {
            while(rs.next()) {
                model.addRow(new Object[]{
                    rs.getString("cstep_name"),
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("operator"),
                    rs.getString("good"),
                    rs.getString("waste"),
                    rs.getString("ctype"),
                    rs.getString("cnbkt_po"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
        } 
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable3);
        tca.adjustColumns();
    } 
    
    private void load_errinput(){
        sql="select a.*,c.cplot\n" +
            "from PProdErr a\n" +
            "join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join BOMProduct c on b.cnbkt_bom=c.cnbkt\n" +
            "left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "where a.ccode='"+ccode+"' and a.cnbkt='"+two.getText()+"'   \n"+
            "order by e.urut,a.dtran";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable4);
        DefaultTableModel model = (DefaultTableModel) jTable4.getModel();
        try {
            while(rs.next()) {
                model.addRow(new Object[]{
                    rs.getString("cstep_name"),
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cerr_name"),
                    rs.getInt("nqty_fail"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
        }
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable4);
        tca.adjustColumns();
    }
    
    private void delete(){
        con.queryUpdate("delete from PProdSteps where ccode='"+ccode+"' and cnbkt='"+two.getText()+"'  ");
        con.queryUpdate("delete from PProdErr where ccode='"+ccode+"' and cnbkt='"+two.getText()+"'  ");
        
        load_stepinput();
        load_errinput();
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
        jLabel26 = new javax.swing.JLabel();
        two = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        jButton1 = new javax.swing.JButton();
        jLabel4 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jScrollPane4 = new javax.swing.JScrollPane();
        jTable4 = new javax.swing.JTable();

        jPanel2.setBorder(javax.swing.BorderFactory.createTitledBorder("Berdasarkan"));
        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("No WO / SPK");
        jPanel2.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 120, 20));
        jPanel2.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 30, 210, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 30, 30, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        jButton1.setText("Delete Semua Hasil Produksi");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 30, 250, 30));

        jLabel4.setFont(new java.awt.Font("Tahoma", 1, 24)); // NOI18N
        jLabel4.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel4.setText("Produksi Utility");

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel3.setText("(Menu ini untuk menghapus semua hasil Produksi sesuai SPK yang dipilih)");

        jScrollPane3.setPreferredSize(null);

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "Step", "Tgl", "Operator", "Good", "Waste", "Type", "No PO"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable3.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable3.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable3MouseClicked(evt);
            }
        });
        jScrollPane3.setViewportView(jTable3);

        jScrollPane4.setPreferredSize(null);

        jTable4.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "Step", "Tanggal", "Kesalahan", "Qty"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable4.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable4.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable4MouseClicked(evt);
            }
        });
        jScrollPane4.setViewportView(jTable4);

        javax.swing.GroupLayout jPanel1Layout = new javax.swing.GroupLayout(jPanel1);
        jPanel1.setLayout(jPanel1Layout);
        jPanel1Layout.setHorizontalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel2, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addComponent(jLabel4, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addComponent(jLabel3, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel1Layout.createSequentialGroup()
                        .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 468, Short.MAX_VALUE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(jScrollPane4, javax.swing.GroupLayout.DEFAULT_SIZE, 468, Short.MAX_VALUE)))
                .addContainerGap())
        );
        jPanel1Layout.setVerticalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jLabel4, javax.swing.GroupLayout.PREFERRED_SIZE, 38, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jLabel3, javax.swing.GroupLayout.PREFERRED_SIZE, 20, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(18, 18, 18)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 71, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane4, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addContainerGap())
        );

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogWO a = new DialogWO();
        a.setVisible(true);
        if(a.status){
            two.setText(a.cnbkt); 
            ccode = a.ccode;
            
        } else {
            two.setText(""); 
        }
        load_stepinput();
        load_errinput();
    }//GEN-LAST:event_jButton11ActionPerformed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
          
    }//GEN-LAST:event_jTable3MouseClicked

    private void jTable4MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable4MouseClicked
         
    }//GEN-LAST:event_jTable4MouseClicked

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_jButton1ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton11;
    private javax.swing.JLabel jLabel26;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JTable jTable3;
    private javax.swing.JTable jTable4;
    private javax.swing.JTextField two;
    // End of variables declaration//GEN-END:variables
}
