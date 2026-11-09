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
import java.awt.Color;
import java.awt.Component;
import java.awt.Dialog;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.JScrollPane;
import javax.swing.JTable;
import javax.swing.JTextField;
import javax.swing.RowFilter;
import javax.swing.event.DocumentEvent;
import javax.swing.event.DocumentListener;
import javax.swing.table.DefaultTableCellRenderer;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableCellRenderer;
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
public class DialogPurchaseOrderBilling extends javax.swing.JDialog {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="",po_data="";
    int barisEdit=-1;
    String akses="TRANSAKSI OPRASIONAL KELUAR";
    public Boolean status = false;
    public String kode,kodetagihan,kodeinv,groupBarang, namaBarang,unit;
    public Double big_value,small_value;
    private TableRowSorter sorter;
    private DefaultTableModel model;
    private JScrollPane scrollPane;
    ArrayList<Integer> colorSet;
    public DialogPurchaseOrderBilling(String po) {
        this.setModalityType(Dialog.ModalityType.APPLICATION_MODAL); 
        initComponents();
        setLocationRelativeTo(null);
        po_data = po;
        cmbStatus.removeAll();
        cmbStatus.removeAllItems();
        cmbJatuhTempo.removeAll();
        cmbJatuhTempo.removeAllItems();
        
        cmbStatus.addItem("");
        cmbStatus.addItem("BELUM LUNAS");
        cmbStatus.addItem("LUNAS");
        
        cmbJatuhTempo.addItem("");
        cmbJatuhTempo.addItem("LEWAT JATUH TEMPO");
        colorSet = new ArrayList<>();
        tcodes.setText(deffProgram.get("ccode"));
        
        loadData();
    }

    private void loadData(){
        String where="";
        if(cmbJatuhTempo.getSelectedIndex()>=1) {
            where+=" and cdue_date < GETDATE() ";
        }
        if(cmbStatus.getSelectedIndex()>=1) {
            if(cmbStatus.getSelectedItem().equals("LUNAS")) {
                where+=" and nominalpay >= total\n ";
            } else {
                where+=" and nominalpay < total\n ";
            }
            
        }
        sql="select * from (\n" +
            "	select a.ccode,a.id,a.cnbkt,cc.cnbkt as cnbktbill,cc.cnbkt_inv,cc.dtran,a.cdesc,cc.ntotal as total,a.kode_supplier,c.cname as supplier,\n" +
            "	isnull(d.nominalpay,0) as nominalpay,cc.cdue_date,\n" +
            "	case when cc.ntotal<=isnull(d.nominalpay,0) then 'LUNAS' else 'BELUM LUNAS' end as status,\n" +
            "	case when cc.cdue_date<getdate() then 'MELEWATI JATUH TEMPO' else '' end as statusduedate\n" +
            "	from (\n" +
            "		select a.id,a.ccode,a.cnbkt,a.cnbkt_PR,a.dtran,a.cdesc,a.nik,a.nik_cname,a.cdue_date,\n" +
            "		b.kode_supplier,sum(b.nqty) as qty,sum(b.nprice) as price,sum(b.ndisc) as disc,sum(b.ntotal) as total\n" +
            "		from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
            "		group by a.id,a.ccode,a.cnbkt,a.cnbkt_PR,a.dtran,a.cdesc,a.nik,a.nik_cname,a.cdue_date,\n" +
            "		b.kode_supplier\n" +
            "	) a \n" +
            "	join MSupplier c on a.kode_supplier=c.kode_supplier\n" +
            "	join (\n" +
            "		select a.ccode,a.cnbkt,a.cnbkt_po,a.cnbkt_inv,a.dtran,a.cdue_date,sum(b.ntotalall) as ntotal from TranPurchaseOrderBilling a\n" +
            "		join TranPurchaseOrderBillingD b on a.cnbkt=b.cnbkt and a.ccode = b.ccode\n" +
            "		group by a.ccode,a.cnbkt,a.cnbkt_po,a.cnbkt_inv,a.dtran,a.cdue_date\n" +
            "	) cc on a.ccode=cc.ccode and a.cnbkt=cc.cnbkt_po\n" +
            "	left join (\n" +
            "		select a.ccode,a.cnbkt,a.cnbkt_PO,a.cnbkt_bill,sum(b.namnt) as nominalpay from TranPayment a join TranPayment_detail b on a.cnbkt=b.cnbkt and a.id=b.id_cnbkt\n" +
            "		group by a.ccode,a.cnbkt,a.cnbkt_PO,a.cnbkt_bill\n" +
            "	) d on a.cnbkt=d.cnbkt_PO and a.ccode=d.ccode and cc.cnbkt=d.cnbkt_bill and cc.ccode=d.ccode\n" +
            ") final\n" +
            "where  ccode='"+tcodes.getText()+"' \n" +
            "and cdesc like '%"+tkets.getText()+"%'\n" +
            " "+where;
        if(!po_data.isEmpty()) sql+=" and cnbkt='"+po_data+"' ";
        
        System.out.println(sql);
        
        rs = con.queryResult(sql+" order by cnbkt desc");
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1,roww=0;
        colorSet.clear();
        try {
            while(rs.next()){
//                if(rs.getString("statusduedate").equals("MELEWATI JATUH TEMPO")) {
//                    if(rs.getString("status").equals("BELUM LUNAS")) {
//                        colorSet.add(roww);
//                    }
//                } else {
//                    if(rs.getString("status").equals("BELUM LUNAS")) {
//                        colorSet.add(roww);
//                    }
//                }
                
                if(rs.getString("status").equals("LUNAS")) {
                    colorSet.add(roww);
                }
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("cnbkt"),
                    rs.getString("cnbktbill"),
                    rs.getString("cnbkt_inv"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("supplier"),rs.getString("cdesc"),
                    rfa.formatDecimal2.format(rs.getDouble("total")),
                    rfa.formatDecimal2.format(rs.getDouble("nominalpay")),
                    rs.getString("status"),rfa.DateString(keyssDef, rs.getDate("cdue_date"),"dd/MM/yyyy"),
                    rs.getString("statusduedate"),
                });
                roww++;
                
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        //renderBarisTable();
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();
        jTable2.setAutoResizeMode(JTable.AUTO_RESIZE_OFF);
        con.colorTableSet(jTable2, colorSet);
//        con.resizeColumnWidth(jTable2);
    }
    
    private void renderBarisTable(){
        jTable2.setDefaultRenderer(Object.class, new TableCellRenderer(){
        private DefaultTableCellRenderer DEFAULT_RENDERER =  new DefaultTableCellRenderer();

            @Override
            public Component getTableCellRendererComponent(JTable table, Object value, boolean isSelected, boolean hasFocus, int row, int column) {
                Component c = DEFAULT_RENDERER.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);
                if(isSelected){
                    //c.setBackground(Color.YELLOW);
                }else{
//                    if(row == 2) {
//                        c.setBackground(Color.LIGHT_GRAY);
//                        //break;
//                    }else {
//                        c.setBackground(Color.WHITE);
//                    } 
//                    System.out.println(row+"-"+column);
                    for(int i=0; i<colorSet.size(); i++){
                        if(row == colorSet.get(i)) {
                            c.setBackground(Color.LIGHT_GRAY);
                            break;
                        } else {
                            c.setBackground(Color.WHITE);
                        }
                    }
//                    if (row == 0 || row == 3){
//                        c.setBackground(Color.LIGHT_GRAY);
//                    }
//                    else {
//                        c.setBackground(Color.WHITE);
//                    }   
//                    if(column==7) {
//                        System.out.println(value);
//                        if(value.toString().length() == 5) {
//                            c.setBackground(Color.LIGHT_GRAY);
//                        }
//                    }
                }

                
       //Add below code here
                return c;
            }

        });
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
        jLabel12 = new javax.swing.JLabel();
        tbuktis = new javax.swing.JTextField();
        tkets = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jLabel13 = new javax.swing.JLabel();
        cmbJatuhTempo = new javax.swing.JComboBox();
        jLabel14 = new javax.swing.JLabel();
        cmbStatus = new javax.swing.JComboBox();
        jLabel16 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField13 = new javax.swing.JTextField();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();

        setDefaultCloseOperation(javax.swing.WindowConstants.DISPOSE_ON_CLOSE);
        setTitle("List Tagihan");

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel12.setText("Status Jatuh Tempo");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(570, 60, -1, 20));

        tbuktis.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbuktisKeyPressed(evt);
            }
        });
        jPanel6.add(tbuktis, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 30, 150, -1));

        tkets.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketsKeyPressed(evt);
            }
        });
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 440, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(860, 30, 90, -1));

        jLabel15.setText("Keterangan");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        jLabel13.setText("Gudang");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        cmbJatuhTempo.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Belum Lunas", "Lunas" }));
        jPanel6.add(cmbJatuhTempo, new org.netbeans.lib.awtextra.AbsoluteConstraints(710, 60, 130, -1));

        jLabel14.setText("Status");
        jPanel6.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(570, 30, -1, 20));

        cmbStatus.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Belum Lunas", "Lunas" }));
        jPanel6.add(cmbStatus, new org.netbeans.lib.awtextra.AbsoluteConstraints(710, 30, 130, -1));

        jLabel16.setText("No Bukti");
        jPanel6.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 30, -1, 20));

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

        jPanel1.add(jPanel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 20, 970, 90));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No", "No PO", "No Tagihan", "Invoice Vendor", "Tanggal Tagihan", "Supplier", "Keterangan PO", "Nominal Tagihan", "Total Bayar", "Status", "Tgl Tempo", "Status Tempo"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.String.class, java.lang.String.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false
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
        });
        jScrollPane3.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(1).setMinWidth(40);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(40);
            jTable2.getColumnModel().getColumn(5).setMinWidth(110);
            jTable2.getColumnModel().getColumn(7).setMinWidth(150);
            jTable2.getColumnModel().getColumn(8).setMinWidth(150);
            jTable2.getColumnModel().getColumn(9).setMinWidth(150);
            jTable2.getColumnModel().getColumn(11).setMinWidth(100);
            jTable2.getColumnModel().getColumn(12).setMinWidth(100);
        }

        jPanel1.add(jScrollPane3, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 120, 970, 220));

        jTextField13.setEditable(false);
        jTextField13.setBackground(new java.awt.Color(204, 204, 255));
        jTextField13.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField13.setText("Detail");
        jPanel1.add(jTextField13, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 370, 710, -1));

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Group", "Product", "Desc", "Qty", "Harga", "Disc", "Total ", "PPN", "Total All"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class
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
        jScrollPane2.setViewportView(jTable3);

        jPanel1.add(jScrollPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 390, 970, 120));

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.LEFT);
        jTextField11.setText("Double Click to Continue");
        jPanel1.add(jTextField11, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 340, 310, -1));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 1036, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 522, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode()==10)  loadData();
    }//GEN-LAST:event_tbuktisKeyPressed

    private void tketsKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketsKeyPressed
        if(evt.getKeyCode()==10)  loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_tketsKeyPressed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        int baris = jTable2.getSelectedRow();
        if(evt.getClickCount() == 2) {
            
            id = jTable2.getValueAt(baris, 0).toString();
            kode = jTable2.getValueAt(baris, 2).toString();
            
//            if(jTable2.getValueAt(baris, 3) == null) {
//                JOptionPane.showMessageDialog(this,"Tagihan PO Belum di buat", "Get Data", JOptionPane.ERROR_MESSAGE);
//                return;
//            }
            if(jTable2.getValueAt(baris, 3) != null) kodetagihan = jTable2.getValueAt(baris, 3).toString();
            if(jTable2.getValueAt(baris, 4) != null) kodeinv = jTable2.getValueAt(baris, 4).toString();
            
            status = true;
            dispose();
        } else if(evt.getClickCount() == 1) {
            if(jTable2.getValueAt(baris, 3) == null) {
                return;
            }
            
            String bkt = jTable2.getValueAt(baris, 3).toString();
            sql="select a.*,b.cnameProduct \n" +
            "from TranPurchaseOrderBillingD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.cnbkt = '"+bkt+"'";
            rfa.clear_table(MenuAdmin.keyssDef, jTable3);
            DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{
                        rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameProduct"),
                        rs.getDouble("nqty"),rs.getDouble("nprice"),rs.getDouble("ndisc"),
                        rs.getDouble("ntotal"),rs.getDouble("ppn"),rs.getDouble("ntotalall")
                    });
                }
            } catch (SQLException ex) {
                ex.printStackTrace();
            }

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

   

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JComboBox cmbJatuhTempo;
    private javax.swing.JComboBox cmbStatus;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField13;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tkets;
    // End of variables declaration//GEN-END:variables
}
