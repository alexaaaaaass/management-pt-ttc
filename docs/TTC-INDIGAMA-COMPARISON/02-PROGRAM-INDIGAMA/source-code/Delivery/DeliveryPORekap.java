/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Delivery;

import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogMembers;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Printer.PrinterUtility;
import Production.*;
import Report.*;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.Color;
import java.awt.Component;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.JTable;
import javax.swing.RowFilter;
import javax.swing.table.DefaultTableCellRenderer;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableCellRenderer;
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
public class DeliveryPORekap extends javax.swing.JPanel {

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
    
    public DeliveryPORekap() {
        initComponents();
        deff();
        loadData();
    }
    
    private void deff(){ 
        Calendar calendar = Calendar.getInstance();
        Date today = calendar.getTime();
        calendar.add(Calendar.DAY_OF_YEAR, -45);
        Date addDays = calendar.getTime();
        
        id = ""; 
        jDate2.setDate(new Date());
        jDate1.setDate(addDays); 
        
        
        tcodes.setText(deffProgram.get("ccode"));  
         
    }
    
    
    private void loadData(){
        ArrayList<Integer> colorSet = new ArrayList<>();
        colorSet.clear();
        int roww=0;
        
        sql="select a.ccode,a.cnbkt,a.dtran,a.kode_customer,c.cname as customer,b.cgroup,b.cproduct,d.cnameProduct,b.nqty,b.nunit,isnull(e.nqty,0) as qtykirim,e.csatuan_small as nunitkirim,b.nqty - isnull(e.nqty,0) as sisa\n" +
            "from PCustomerPO a join PCustomerPOD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join MCustomer c on a.kode_customer=c.kode_customer\n" +
            "join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "left join (\n" +
            "	select a.ccode,a.cnbkt_inv,b.cgroup,b.cproduct,sum(b.nqty) as nqty,b.csatuan_small\n" +
            "	from TranDeliveryOrder a\n" +
            "	join (\n" +
            "		select ccode,cnbkt,cgroup,cproduct,sum(nqty_small) as nqty,csatuan_small\n" +
            "		from TranDeliveryOrderD\n" +
            "		group by  ccode,cnbkt,cgroup,cproduct,csatuan_small\n" +
            "	) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt \n" +
            "    group by a.ccode,a.cnbkt_inv,b.cgroup,b.cproduct,b.csatuan_small\n" +
            ") e on b.ccode=e.ccode and b.cnbkt=e.cnbkt_inv and b.cgroup=e.cgroup and e.cproduct=b.cproduct\n" +
            "where a.ccode='"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"') ";
        if(!tcustomerkode.getText().isEmpty()) sql+=" and a.kode_customer='"+tcustomerkode.getText()+"' ";
            sql+="order by a.dtran,a.cnbkt";
        System.out.println(sql);
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        try {
            while(rs.next()) {
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("customer"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rfa.formatDecimal2.format(rs.getDouble("nqty")),
                    rs.getString("nunit"),
                    rfa.formatDecimal2.format(rs.getDouble("qtykirim")),
                    rs.getString("nunitkirim"),
                    rfa.formatDecimal2.format(rs.getDouble("sisa"))+" "+rs.getString("nunit"), 
                });
                
                if(rs.getDouble("sisa")<=0.0) {
                    colorSet.add(roww);
                }
                roww++;
            }
        } catch (SQLException ex) {
            Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
        } 
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable3);
        tca.adjustColumns();
        
        jTable3.setDefaultRenderer(Object.class, new TableCellRenderer(){
        private DefaultTableCellRenderer DEFAULT_RENDERER =  new DefaultTableCellRenderer();

            @Override
            public Component getTableCellRendererComponent(JTable table, Object value, boolean isSelected, boolean hasFocus, int row, int column) {
                Component c = DEFAULT_RENDERER.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);
                
                
                
                
                if (isSelected) {
                    c.setBackground(Color.CYAN);
                } else {
                    c.setBackground(Color.WHITE);
                    
                    for(int i=0; i<colorSet.size(); i++){
                        if(row == colorSet.get(i)) { 
                            c.setBackground(Color.LIGHT_GRAY); 
                        }  
                    } 
                }
                return c;
            }

        });
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
        jLabel21 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jLabel13 = new javax.swing.JLabel();
        jDate1 = new com.toedter.calendar.JDateChooser();
        jLabel9 = new javax.swing.JLabel();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jButton7 = new javax.swing.JButton();
        tcustomerkode = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        jButton3 = new javax.swing.JButton();
        tcustomer = new javax.swing.JTextField();
        jLabel4 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jScrollPane4 = new javax.swing.JScrollPane();
        jTable4 = new javax.swing.JTable();
        jTextField1 = new javax.swing.JTextField();

        jPanel2.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel21.setText("Gudang");
        jPanel2.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        tcodes.setEditable(false);
        tcodes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodesFocusLost(evt);
            }
        });
        jPanel2.add(tcodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 110, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 30, 30, -1));

        jLabel13.setText("Tgl PO");
        jPanel2.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

        jDate1.setDateFormatString("dd/MM/yyyy");
        jDate1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDate1PropertyChange(evt);
            }
        });
        jDate1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDate1KeyPressed(evt);
            }
        });
        jPanel2.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 30, 150, -1));

        jLabel9.setText("s/d");
        jPanel2.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jDate2.setDateFormatString("dd/MM/yyyy");
        jDate2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDate2PropertyChange(evt);
            }
        });
        jDate2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDate2KeyPressed(evt);
            }
        });
        jPanel2.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(530, 30, 150, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        tcustomerkode.setEditable(false);
        tcustomerkode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcustomerkodeFocusLost(evt);
            }
        });
        tcustomerkode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tcustomerkodeKeyTyped(evt);
            }
        });
        jPanel2.add(tcustomerkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 60, 0, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Customer");
        jLabel6.setToolTipText("");
        jPanel2.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 72, 20));

        jButton3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton3, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 60, 30, -1));

        tcustomer.setEditable(false);
        jPanel2.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 110, -1));

        jLabel4.setFont(new java.awt.Font("Tahoma", 1, 20)); // NOI18N
        jLabel4.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel4.setText("Rekapituasi Pengiriman PO Customer");

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel3.setText("(Menu ini untuk melihat data PO yang sudah terkirim atau belum)");

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "Gudang", "No PO", "Tgl PO", "Customer", "Kode Produk", "Nama Produk", "Qty Order", "Satuan Order", "Qty Terkirim", "Satuan Kirim", "Kurang Kirim"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false
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

        jTable4.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "No Surat Jalan", "Tgl Pengiriman", "Via", "Supir", "Nopol", "Keterangan", "Dibuat Oleh", "Jumlah Box", "Qty Total", "Satuan", "No SPK"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

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

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(255, 255, 255));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField1.setText("RINCIAN PENGIRIMAN");

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
                    .addComponent(jScrollPane4, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 946, Short.MAX_VALUE)
                    .addComponent(jScrollPane3)
                    .addGroup(jPanel1Layout.createSequentialGroup()
                        .addComponent(jTextField1, javax.swing.GroupLayout.PREFERRED_SIZE, 201, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );
        jPanel1Layout.setVerticalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jLabel4, javax.swing.GroupLayout.PREFERRED_SIZE, 30, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jLabel3, javax.swing.GroupLayout.PREFERRED_SIZE, 20, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(18, 18, 18)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 93, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 238, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jTextField1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane4, javax.swing.GroupLayout.DEFAULT_SIZE, 183, Short.MAX_VALUE))
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

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 1){
            int row = jTable3.getSelectedRow();
            sql="select a.ccode,a.cnbkt,a.dtran,a.cnbkt_inv,a.cvia,a.cnopol,a.csupir,a.cnote,a.cusername,b.cgroup,b.cproduct,b.nqty_box,b.nqty,b.csatuan_small,cnbkt_wo\n" +
                "from TranDeliveryOrder a \n" +
                "join (\n" +
                "	select ccode,cnbkt,cgroup,cproduct,sum(nqty_box) as nqty_box,sum(nqty_small) as nqty,csatuan_small,cnbkt_wo \n" +
                "	from TranDeliveryOrderD\n" +
                "	group by ccode,cnbkt,cgroup,cproduct,csatuan_small,cnbkt_wo \n" +
                ") b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                "where a.ccode='"+tcodes.getText()+"' and a.cnbkt_inv='"+jTable3.getValueAt(row, 1).toString()+"' and b.cproduct='"+jTable3.getValueAt(row,4).toString()+"'\n" +
                "order by a.dtran,a.cnbkt"; 
            rfa.clear_table(MenuAdmin.keyssDef, jTable4);
            DefaultTableModel model = (DefaultTableModel) jTable4.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{ 
                        rs.getString("cnbkt"),
                        rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                        rs.getString("cvia"),
                        rs.getString("csupir"),
                        rs.getString("cnopol"),
                        rs.getString("cnote"),
                        rs.getString("cusername"), 
                        rs.getDouble("nqty_box"), 
                        rs.getDouble("nqty"),
                        rs.getString("csatuan_small"), 
                        rs.getString("cnbkt_wo"), 
                    });
                }
            } catch (SQLException ex) {
                Logger.getLogger(DeliveryPORekap.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            TableColumnAdjuster tca = new TableColumnAdjuster(jTable4);
            tca.adjustColumns();
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void jTable4MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable4MouseClicked
         
    }//GEN-LAST:event_jTable4MouseClicked

    private void tcodesFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodesFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodesFocusLost

    private void jButton15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton15ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcodes.setText(a.ccode);
        }
    }//GEN-LAST:event_jButton15ActionPerformed

    private void jDate1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1PropertyChange

    private void jDate1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1KeyPressed

    private void jDate2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2PropertyChange

    private void jDate2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2KeyPressed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void tcustomerkodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcustomerkodeFocusLost
        sql = "select * from mcustomer where kode_customer = '"+tcustomerkode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tcustomer.setText(rs.getString("cname"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tcustomerkodeFocusLost

    private void tcustomerkodeKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tcustomerkodeKeyTyped
      
    }//GEN-LAST:event_tcustomerkodeKeyTyped

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        DialogMembers a = new DialogMembers();
        a.setVisible(true);
        rfa.empty(keyssDef, tcustomer,tcustomerkode);
        if(a.status){
            tcustomerkode.setText(a.Kode);
            tcustomer.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton3ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton7;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JTable jTable3;
    private javax.swing.JTable jTable4;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tcustomer;
    private javax.swing.JTextField tcustomerkode;
    // End of variables declaration//GEN-END:variables
}
