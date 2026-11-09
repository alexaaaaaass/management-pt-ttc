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
public class DialogWO extends javax.swing.JDialog {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    int barisEdit=-1;
    String akses="TRANSAKSI OPRASIONAL KELUAR";
    public Boolean status = false;
    public String ccode,cnbkt,cnote,produk,customer,cnbktpo,cgroup,cproduct;
    public Double big_value,small_value;
    private TableRowSorter sorter;
    private DefaultTableModel model;
    private JScrollPane scrollPane;
    public DialogWO() {
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
        sql="select a.id,a.ccode,a.cnbkt,a.dtran,a.deadline,a.cnbkt_order,a.cgroup,a.cproduct,c.cnameProduct,a.nqty,a.nqty_add,a.nqty_total,a.cket,\n" +
            "b.kode_customer,d.cname as customer,b.dtran as dtranorder,a.kode\n" +
            "from PWorkOrder a left join PCustomerPO b on a.ccode=b.ccode and a.cnbkt_order=b.cnbkt\n" +
            "join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
            "left join MCustomer d on b.kode_customer=d.kode_customer\n" +
            "where a.ccode='"+tcodes.getText()+"'  "+
            "and (a.dtran>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cket like '%"+tkets.getText()+"%'  order by a.dtran desc,a.id desc"+
            "";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("kode"),
                    rs.getString("cnbkt"),rs.getString("ccode"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cnbkt_order"),
                    rs.getString("cket"),rs.getString("cnameProduct"),
                    rs.getString("customer"),
                    rs.getDouble("nqty_total"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        //con.resizeColumnWidth(jTable2);
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
        tkets = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();

        setDefaultCloseOperation(javax.swing.WindowConstants.DISPOSE_ON_CLOSE);
        setTitle("List Work Order (WO)");

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jLabel12.setText("No Bukti");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));
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
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 340, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("Keterangan");
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

        jPanel1.add(jPanel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 1100, 90));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Kode", "No WO/SPK", "Gudang", "Tanggal", "No PO Customer", "Keterangan SPK", "Produk", "Customer", "Qty", "Group", "CProduk"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

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
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(1).setMinWidth(50);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(50);
            jTable2.getColumnModel().getColumn(2).setMinWidth(130);
            jTable2.getColumnModel().getColumn(3).setMinWidth(80);
            jTable2.getColumnModel().getColumn(3).setMaxWidth(80);
            jTable2.getColumnModel().getColumn(4).setMinWidth(100);
            jTable2.getColumnModel().getColumn(4).setMaxWidth(100);
            jTable2.getColumnModel().getColumn(10).setMinWidth(0);
            jTable2.getColumnModel().getColumn(10).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(11).setMinWidth(0);
            jTable2.getColumnModel().getColumn(11).setMaxWidth(0);
        }

        jPanel1.add(jScrollPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 120, 1100, 410));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 1148, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 558, Short.MAX_VALUE)
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
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 2).toString();
            ccode = jTable2.getValueAt(baris, 3).toString();
            if(jTable2.getValueAt(baris, 5) != null) cnbktpo = jTable2.getValueAt(baris, 5).toString();
            if(jTable2.getValueAt(baris, 6) != null) cnote = jTable2.getValueAt(baris, 6).toString();
            if(jTable2.getValueAt(baris, 7) != null) produk = jTable2.getValueAt(baris, 7).toString();
            if(jTable2.getValueAt(baris, 8) != null) customer = jTable2.getValueAt(baris, 8).toString();
            if(jTable2.getValueAt(baris,10) != null) cgroup = jTable2.getValueAt(baris,10).toString();
            if(jTable2.getValueAt(baris,11) != null) cproduct = jTable2.getValueAt(baris,11).toString();
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
    private javax.swing.JTextField tkets;
    // End of variables declaration//GEN-END:variables
}
