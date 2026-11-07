/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package DialogPublicTP;

import static Main.DefaultProgram.deffProgram;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.Dialog;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JScrollPane;
import javax.swing.JTable;
import javax.swing.JTextField;
import javax.swing.RowFilter;
import javax.swing.event.DocumentEvent;
import javax.swing.event.DocumentListener;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableRowSorter;
import okhttp3.FormBody;
import okhttp3.RequestBody;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class DialogDelivery extends javax.swing.JDialog {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="TRANSAKSI OPRASIONAL KELUAR";
    public Boolean status = false;
    public String id="",kode,kodecustomer,customer,buktisj,buktipo,buktispk,ccode;
    public Double big_value,small_value;
    private TableRowSorter sorter;
    private DefaultTableModel model;
    private JScrollPane scrollPane;
    public DialogDelivery() {
        this.setModalityType(Dialog.ModalityType.APPLICATION_MODAL); 
        initComponents();
        setLocationRelativeTo(null);
        
        Calendar calendar = Calendar.getInstance();
        Date today = calendar.getTime();
        calendar.add(Calendar.DAY_OF_YEAR, -40);
        Date addDays = calendar.getTime();
        
        jDate2.setDate(new Date());
        jDate1.setDate(addDays);
        tcodes.setText(deffProgram.get("ccode"));
        
        loadData();
    }

    private void loadData(){
        sql="select a.id,a.ccode,a.cnbkt,a.dtran,a.cmarketing,d.cname as marketing,\n" +
                "a.kode_customer,c.cname as customer,\n" +
                "b.cgroup,b.cproduct,e.cnameProduct,b.nqty,b.nunit,b.cgroup_bahan,b.cproduct_bahan,f.cnameProduct as cnameProductbahan,b.deadline,g.qtysmall,g.csatuan_small \n" +
                "from PCustomerPO a \n" +
                "join PCustomerPOD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                "join MCustomer c on a.kode_customer=c.kode_customer\n" +
                "join MKaryawan d on a.cmarketing=d.nik\n" +
                "join MProduk e on b.cgroup=e.cgroup and b.cproduct=e.cproduct\n" +
                "left join MProduk f on b.cgroup_bahan=f.cgroup and b.cproduct_bahan=f.cproduct\n" +
                "left join (select a.ccode,sum(a.nqty_small) as qtysmall,a.csatuan_small,b.cnbkt_order\n" +
                        "from PBoxPackingD a\n" +
                        "join PBoxPacking aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                        "join PWorkOrder b on aa.ccode=b.ccode and aa.cworkorder=b.cnbkt\n" +
                        "group by a.ccode,a.csatuan_small,b.cnbkt_order) g on a.ccode=g.ccode and a.cnbkt=g.cnbkt_order \n"+
                "where a.ccode='"+tcodes.getText()+"' \n" +
                "and (a.dtran>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
                "and d.cname like '%"+tcustomer.getText()+"%'\n" +
                "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
                "order by a.dtran desc,a.cnbkt desc";
        
        sql="select a.ccode,a.cnbkt,a.dtran,a.kode_customer,e.cname as customer,a.cnbkt_inv,c.cnbkt as cworkorder,\n" +
            "c.cgroup,c.cproduct,d.cnameProduct,a.cvia,a.csupir,a.cnopol,a.cnote\n" +
            "from TranDeliveryOrder a\n" +
            "left join PCustomerPO b on a.ccode=b.ccode and a.cnbkt_inv=b.cnbkt\n" +
            "left join PWorkOrder c on a.ccode=c.ccode and a.cnbkt_inv=c.cnbkt_order  and isnull(a.cnbkt_inv,'') <> '' \n" +
            "left join MProduk d on c.cgroup=d.cgroup and c.cproduct=d.cproduct\n" +
            "left join MCustomer e on a.kode_customer=e.kode_customer\n" +
            "where a.ccode='"+tcodes.getText()+"'\n" +
            "and (a.dtran>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n";
            
        if(!tbuktis.getText().isEmpty()) sql+=" and a.cnbkt like '%"+tbuktis.getText()+"%' ";
        if(!tcustomer.getText().isEmpty()) sql+=" and e.cname like '%"+tcustomer.getText()+"%' ";
        sql+=" order by a.ccode,a.dtran desc, a.cnbkt desc";
        System.out.println(sql);
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("kode_customer"),rs.getString("customer"),
                    rs.getString("cnbkt_inv"),
                    rs.getString("cworkorder"),rs.getString("cnameProduct"),
                    rs.getString("cvia"),
                    rs.getString("csupir"),
                    rs.getString("cnopol"),
                    rs.getString("cnote"),
                    
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        con.resizeColumnWidth(jTable2);
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();

    }
   
    private TableRowSorter getRowSorter() {
    if (sorter == null){
      sorter = new TableRowSorter(model);
    }
    return sorter;
  }
  /**
  * Mendeklarasikan aksi txtFilter
  */
  private void aksiTxtFilter(final JTextField data) {
    data.getDocument().addDocumentListener(new DocumentListener() {
        @Override
        public void insertUpdate(DocumentEvent e) {
          changedUpdate(e);
        }
        @Override
        public void removeUpdate(DocumentEvent e) {
          changedUpdate(e);
        }
        @Override
        public void changedUpdate(DocumentEvent e) {
          if (data.getText().length() <= 0){
            getRowSorter().setRowFilter(null);
          } else{
            getRowSorter().setRowFilter(RowFilter.regexFilter(data.getText()));
          }
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
        jPanel6 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jLabel12 = new javax.swing.JLabel();
        tbuktis = new javax.swing.JTextField();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        jDate1 = new com.toedter.calendar.JDateChooser();
        tcustomer = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();

        setDefaultCloseOperation(javax.swing.WindowConstants.DISPOSE_ON_CLOSE);
        setTitle("List Delivery Order");

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jLabel12.setText("No Kirim");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 80, 20));
        jPanel6.add(tbuktis, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 140, -1));

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(530, 30, 150, -1));

        jLabel13.setText("Gudang");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

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
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 30, 150, -1));
        jPanel6.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 340, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("CUstomer");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));

        jLabel14.setText("Periode ");
        jPanel6.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

        tcodes.setEditable(false);
        tcodes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodesFocusLost(evt);
            }
        });
        jPanel6.add(tcodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 110, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 30, 30, -1));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Gudang", "Bukti Kirim", "Tgl Kirim", "Kode Customer", "Customer", "No Order", "No SPK", "Produk", "Via", "Supir", "Nopol", "Keterangan"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable2.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable2.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable2MouseClicked(evt);
            }
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                jTable2MouseEntered(evt);
            }
        });
        jScrollPane2.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(0).setMinWidth(80);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(80);
            jTable2.getColumnModel().getColumn(1).setMinWidth(130);
            jTable2.getColumnModel().getColumn(2).setMinWidth(100);
            jTable2.getColumnModel().getColumn(2).setMaxWidth(100);
            jTable2.getColumnModel().getColumn(8).setMinWidth(90);
        }

        javax.swing.GroupLayout jPanel1Layout = new javax.swing.GroupLayout(jPanel1);
        jPanel1.setLayout(jPanel1Layout);
        jPanel1Layout.setHorizontalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jScrollPane2)
                    .addComponent(jPanel6, javax.swing.GroupLayout.DEFAULT_SIZE, 1049, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel1Layout.setVerticalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addGap(12, 12, 12)
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 90, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 401, Short.MAX_VALUE)
                .addContainerGap())
        );

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void jDate2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2PropertyChange

    private void jDate2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2KeyPressed

    private void jDate1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1PropertyChange

    private void jDate1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1KeyPressed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            
            if(jTable2.getValueAt(baris, 0) != null) ccode = jTable2.getValueAt(baris, 0).toString();
            if(jTable2.getValueAt(baris, 1) != null) buktisj = jTable2.getValueAt(baris, 1).toString();
            if(jTable2.getValueAt(baris, 3) != null) kodecustomer = jTable2.getValueAt(baris, 3).toString();
            if(jTable2.getValueAt(baris, 4) != null) customer = jTable2.getValueAt(baris, 4).toString();
            if(jTable2.getValueAt(baris, 5) != null) buktipo = jTable2.getValueAt(baris, 5).toString();
            if(jTable2.getValueAt(baris, 6) != null) buktispk = jTable2.getValueAt(baris, 6).toString();
            status = true;
            dispose();
        }
    }//GEN-LAST:event_jTable2MouseClicked

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

    private void jTable2MouseEntered(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseEntered
        // TODO add your handling code here:
    }//GEN-LAST:event_jTable2MouseEntered

   

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tcustomer;
    // End of variables declaration//GEN-END:variables
}
