/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package DialogPublicTP;

import Kasir.PenjualanUtility;
import Main.MenuAdmin;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.Dialog;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JScrollPane;
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
public class DialogInvoice extends javax.swing.JDialog {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="TRANSAKSI OPRASIONAL KELUAR";
    public Boolean status = false;
    public String noinv,idmember,nama_member,ccode;
    private TableRowSorter sorter;
    private DefaultTableModel model;
    private JScrollPane scrollPane;
    public DialogInvoice() {
        this.setModalityType(Dialog.ModalityType.APPLICATION_MODAL); 
        initComponents();
        setLocationRelativeTo(null);
        jTable1.getTableHeader().setEnabled(false);
        
        listData();
        
        iNama.requestFocus();
    }

    
    private void listData(){
        
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.getDataVector().removeAllElements();

        
        jTable1.getTableHeader().setEnabled(false);
        sql =   "select a.dtran,a.cnbkt,count(jumlah) as total,isnull(a.id_member,'') as id_member,isnull(b.cname,'') as cname,a.ccode,c.cname as gudang\n" +
        "from TranSale a left join MCustomer b on a.id_member=b.kode_customer\n" +
        "join mgudang c on a.ccode=c.ccode \n" +
        "where left(cnbkt,1) <> 'R'\n" +
        "group by a.dtran,a.cnbkt,a.id_member,b.cname,a.ccode,c.cname\n" +
        "order by a.dtran desc,a.cnbkt desc";
        
        rs=con.queryResult(sql);
        int no=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    no++,
                    rfa.DateString(MenuAdmin.keyssDef, rs.getDate("dtran"), "dd MMM yyyy"),
                    rs.getString("cnbkt"),
                    rs.getString("ccode"),rs.getString("gudang"),
                    rs.getString("id_member"),rs.getString("cname"),rs.getDouble("total"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(DialogInvoice.class.getName()).log(Level.SEVERE, null, ex);
        }
       TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns();
    }
    
    private void loadDEtail(String cnbkt){
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        model.getDataVector().removeAllElements();
        jTable2.removeAll();
        
        sql="select a.dtran, a.cnbkt,a.cgroup,a.cproduct as id_barang,a.id_member,c.cname as name,b.cnameProduct as nama,a.jumlah,a.harga,a.diskon_barang,a.diskon_member,a.total,a.bayar,a.kembali,b.cunit as unit \n" +
        "from TranSale a left join MProduk b on a.cproduct = b.cproduct left join MCustomer c on a.id_member = c.kode_customer \n" +
        "where a.cnbkt = '"+cnbkt+"' or cnbkt like '%R-"+cnbkt+"%';";
//        System.out.println(sql);
        int i=1;
        rs = con.queryResult(sql.toLowerCase());
        try {
            while(rs.next()){
                Double hargaBarang = 0.0; if(rs.getString("harga") != null) { hargaBarang =  Double.valueOf(rs.getString("harga").toString());  }
                Double jumlahBarang = 0.0; if(rs.getString("jumlah") != null) { jumlahBarang =  Double.valueOf(rs.getString("jumlah").toString());  }
                Double diskon_barang = 0.0; if(rs.getString("diskon_barang") != null) { diskon_barang =  Double.valueOf(rs.getString("diskon_barang").toString());  }
                Double diskon_member = 0.0; if(rs.getString("diskon_member") != null) { diskon_member =  Double.valueOf(rs.getString("diskon_member").toString());  }
                Double bayar = 0.0; if(rs.getString("bayar") != null) { bayar =  Double.valueOf(rs.getString("bayar").toString());  }
                Double kembali = 0.0; if(rs.getString("kembali") != null) { kembali =  Double.valueOf(rs.getString("kembali").toString());  }
                
                
                String NamaBarang = (rs.getString("nama"));
                if("R".equals(rs.getString("cnbkt").toString().substring(0, 1))) {
                    NamaBarang = "(RETURN) "+NamaBarang;
                    hargaBarang = hargaBarang;
                    jumlahBarang = jumlahBarang*-1;
                }
                model.addRow(new Object[]{rs.getString("cnbkt"),rs.getString("id_barang"),NamaBarang,jumlahBarang,
                hargaBarang,diskon_barang,jumlahBarang*(hargaBarang-((hargaBarang*diskon_barang)/100)),rs.getString("unit"),i++,rs.getString("cgroup")});
             
            }
        } catch (SQLException ex) {
            Logger.getLogger(PenjualanUtility.class.getName()).log(Level.SEVERE, null, ex);
        }
        
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
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jTextField1 = new javax.swing.JTextField();
        iNama = new javax.swing.JTextField();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();

        setDefaultCloseOperation(javax.swing.WindowConstants.DISPOSE_ON_CLOSE);
        setTitle("List Invoice");

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "No", "Tanggal", "No INV", "Kode Gudang", "Gudang", "Kode Customer", "Nama Customer", "Total"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class
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
        jTable1.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jTable1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jTable1KeyPressed(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(60);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(60);
        }

        jPanel1.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 55, 990, 340));

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(153, 153, 153));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField1.setForeground(new java.awt.Color(255, 255, 255));
        jTextField1.setText("  SEARCH");
        jPanel1.add(jTextField1, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 30, 110, 25));

        iNama.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        iNama.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                iNamaCaretUpdate(evt);
            }
        });
        iNama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iNamaKeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                iNamaKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                iNamaKeyTyped(evt);
            }
        });
        jPanel1.add(iNama, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 30, 880, 25));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "No Kwitansi", "Kode", "Nama", "Jumlah", "Harga", "Diskon", "Total", "Satuan", "No", "group"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable2.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable2MouseClicked(evt);
            }
        });
        jScrollPane2.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(8).setMinWidth(0);
            jTable2.getColumnModel().getColumn(8).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(9).setMinWidth(0);
            jTable2.getColumnModel().getColumn(9).setMaxWidth(0);
        }

        jPanel1.add(jScrollPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 410, 990, 190));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 1063, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 631, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        int row = jTable1.getSelectedRow();
        if(evt.getClickCount()==2) {
            
//                if(jTable1.getValueAt(row,2) != null) noinv = jTable1.getValueAt(row,2).toString();
               // if(jTable1.getValueAt(row,3) != null) ccode = jTable1.getValueAt(row,3).toString();
//                try {
//                    if(jTable1.getValueAt(row,5) != null)   idmember = (jTable1.getValueAt(row,5).toString());
//                    if(jTable1.getValueAt(row,6) != null)   nama_member = (jTable1.getValueAt(row,6).toString());
//                } catch (Exception e){}
                status = true;
                dispose();
           
            
        }else if(evt.getClickCount()==1){
            loadDEtail((jTable1.getValueAt(row,2).toString()));
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void iNamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNamaKeyPressed
        if(evt.getKeyCode() == 10 )
            jTable1.requestFocus();
    }//GEN-LAST:event_iNamaKeyPressed

    private void iNamaKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNamaKeyTyped
//        listData();           // TODO add your handling code here:
    }//GEN-LAST:event_iNamaKeyTyped

    private void jTable1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTable1KeyPressed

    }//GEN-LAST:event_jTable1KeyPressed

    private void iNamaCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_iNamaCaretUpdate
//        try {
//            listData(); 
//        } catch (Exception re ) {}// TODO add your handling code here:
    }//GEN-LAST:event_iNamaCaretUpdate

    private void iNamaKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iNamaKeyReleased
        DefaultTableModel tablee = (DefaultTableModel)jTable1.getModel();
        String search = iNama.getText();
        TableRowSorter<DefaultTableModel> tr = new TableRowSorter<DefaultTableModel>(tablee);
        jTable1.setRowSorter(tr);
        tr.setRowFilter(RowFilter.regexFilter(search.toUpperCase()));
    }//GEN-LAST:event_iNamaKeyReleased

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        
    }//GEN-LAST:event_jTable2MouseClicked

   

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField iNama;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField jTextField1;
    // End of variables declaration//GEN-END:variables
}
