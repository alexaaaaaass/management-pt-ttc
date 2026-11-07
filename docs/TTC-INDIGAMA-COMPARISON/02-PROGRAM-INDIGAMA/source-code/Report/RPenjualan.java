/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Report;

import DialogPublicTP.DialogProduk;
import Main.Login;
import Main.MenuAdmin;
import Printer.PrinterUtility;
import Tools.ConnectDB;
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
public class RPenjualan extends javax.swing.JPanel {

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
    
    public RPenjualan() {
        initComponents();
        
        jDateChooser1.setDate(Calendar.getInstance().getTime());
        jDateChooser2.setDate(Calendar.getInstance().getTime());
        listDataPenjualan();
    }
    
    private void listDataPenjualan(){
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.getDataVector().removeAllElements();
        jTable1.removeAll();
        nomor = 1;
        sql =   "  SELECT a.[cnbkt],a.[id_barang],b.nama,c.username\n" +
                "      ,a.[dtran],a.[jumlah],a.[harga],a.[diskon_barang],a.[diskon_member],a.[bayar] ,a.[kembali]\n" +
                "  FROM [Penjualan] a\n" +
                "  inner join ProdukBarang b on a.[id_barang] = b.[id_barang] \n" +
                "  left join UserDatabase c on a.[id_user] = c.[id_user]\n" +
                "  where  a.cnbkt not like '%R%' and a.dtran >= '"+new SimpleDateFormat("yyyy/MM/dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy/MM/dd").format(jDateChooser2.getDate())+"'\n";
        
        
      
        
        sql = "SELECT '1' as urut,a.cnbkt,a.cnbkt as kodetranurut,a.cproduct,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga],a.[diskon_barang],a.diskon_member,a.bayar ,a.kembali\n" +
                "FROM TranSale a\n" +
                "inner join MProduk b on a.cproduct = b.cproduct\n" +
                "where  a.cnbkt not like '%R%'  and a.dtran >= '"+rfa.DateString(MenuAdmin.keyssDef, jDateChooser1.getDate(),"yyyy-MM-dd")+"' and a.dtran <= '"+rfa.DateString(MenuAdmin.keyssDef, jDateChooser2.getDate(),"yyyy-MM-dd")+"'\n" +
                "            \n" +
                "union all \n" +
                "SELECT '2' as urut,a.cnbkt,right(a.cnbkt,13) as kodetranurut,a.cproduct,b.cnameProduct as  nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga]*-1,a.[diskon_barang],a.diskon_member,a.bayar ,a.kembali\n" +
                "FROM TranSale a\n" +
                "inner join MProduk b on a.cproduct = b.cproduct\n" +
                "where  a.cnbkt like '%R%'  and a.dtran >= '"+rfa.DateString(MenuAdmin.keyssDef, jDateChooser1.getDate(),"yyyy-MM-dd")+"' and a.dtran <= '"+rfa.DateString(MenuAdmin.keyssDef, jDateChooser2.getDate(),"yyyy-MM-dd")+"'\n" +
                "            \n" +
                "order by kodetranurut,urut,dtran";
        
        System.out.println(sql.toLowerCase());
        
        Double totalPendapatan = 0.0;
        String bukti = "";
        Double bayarr=0.0,sisaa=0.0;
        rs = con.queryResult(sql.toLowerCase());
        try {
            while(rs.next()){
                Double hargaBarang = rs.getDouble("harga");
                Double jumlahTotal = rs.getDouble("jumlah");
                Double diskonBarang = rs.getDouble("diskon_barang");
                Double HargaSetDiskon = hargaBarang-((hargaBarang*diskonBarang)/100);
                Double totalHarga = HargaSetDiskon*jumlahTotal;
                totalPendapatan =totalPendapatan + totalHarga;
                String user = "";
                if(rs.getString("username") != null) {
                    user = rs.getString("username");
                }else {
                    user = Login.user;
                }
                
                if(bukti.equals(rs.getString("cnbkt"))) {
                    sisaa = bayarr = 0.0;
                } else {
                    bayarr=rs.getDouble("bayar");
                    sisaa=rs.getDouble("kembali");
                }
                
                bukti = rs.getString("cnbkt");
                
                model.addRow(new Object[]{nomor++,rs.getString("cnbkt"),
                    rfa.DateString(MenuAdmin.keyssDef, rs.getDate("dtran"), "dd/MM/yyyy"),
                rs.getString("cproduct"),
                rs.getString("nama"),
                rs.getDouble("harga"),
                rs.getDouble("diskon_barang"),
                rs.getDouble("jumlah"),totalHarga,user,bayarr,sisaa});
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPenjualan.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        jTextField1.setText("Total Penjualan : "+(nomor-1)+" Items");
        jTextField2.setText("Total Pendapatan : RP "+dFormat.format(totalPendapatan));
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
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jDateChooser1 = new com.toedter.calendar.JDateChooser();
        jDateChooser2 = new com.toedter.calendar.JDateChooser();
        jLabel3 = new javax.swing.JLabel();
        idBarang = new javax.swing.JTextField();
        jButton2 = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jTextField1 = new javax.swing.JTextField();
        jTextField2 = new javax.swing.JTextField();
        jTextField3 = new javax.swing.JTextField();

        jPanel2.setBorder(javax.swing.BorderFactory.createTitledBorder("Berdasarkan"));
        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setText("Tanggal Akhir");
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        jLabel2.setText("Nama Barang");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(320, 30, -1, 20));

        jDateChooser1.setDateFormatString("dd/MM/yyyy");
        jPanel2.add(jDateChooser1, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 30, 160, -1));

        jDateChooser2.setDateFormatString("dd/MM/yyyy");
        jPanel2.add(jDateChooser2, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 60, 160, -1));

        jLabel3.setText("Tanggal Awal");
        jPanel2.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        idBarang.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                idBarangKeyReleased(evt);
            }
        });
        jPanel2.add(idBarang, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 30, 130, -1));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton2.setText("Search");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(630, 30, 90, -1));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "No", "Nomor Kwitansi", "Tanggal Penjualan", "Kode Barang", "Nama Barang", "Harga", "Disc", "Jumlah", "Perolehan", "User Input", "bayar", "sisa"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class
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
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(50);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(50);
            jTable1.getColumnModel().getColumn(10).setMinWidth(0);
            jTable1.getColumnModel().getColumn(10).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(11).setMinWidth(0);
            jTable1.getColumnModel().getColumn(11).setMaxWidth(0);
        }

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(102, 102, 102));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField1.setForeground(new java.awt.Color(255, 255, 255));
        jTextField1.setText("Total Penjualan : 0 Items");

        jTextField2.setEditable(false);
        jTextField2.setBackground(new java.awt.Color(51, 51, 255));
        jTextField2.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField2.setForeground(new java.awt.Color(255, 255, 255));
        jTextField2.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField2.setText("Total Pendapatan : Rp");

        jTextField3.setEditable(false);
        jTextField3.setBackground(new java.awt.Color(153, 153, 153));
        jTextField3.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField3.setForeground(new java.awt.Color(255, 255, 255));
        jTextField3.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField3.setText("Total Perbarang : Rp.");

        javax.swing.GroupLayout jPanel1Layout = new javax.swing.GroupLayout(jPanel1);
        jPanel1.setLayout(jPanel1Layout);
        jPanel1Layout.setHorizontalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jPanel2, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1184, Short.MAX_VALUE)
                    .addGroup(jPanel1Layout.createSequentialGroup()
                        .addComponent(jTextField1, javax.swing.GroupLayout.PREFERRED_SIZE, 329, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField3, javax.swing.GroupLayout.PREFERRED_SIZE, 377, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jTextField2, javax.swing.GroupLayout.PREFERRED_SIZE, 311, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addContainerGap())
        );
        jPanel1Layout.setVerticalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 112, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 397, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField3, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
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

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        listDataPenjualan();        // TODO add your handling code here:
    }//GEN-LAST:event_jButton2ActionPerformed

    private void idBarangKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_idBarangKeyReleased
        DefaultTableModel tablee = (DefaultTableModel)jTable1.getModel();
        String search = idBarang.getText();
        TableRowSorter<DefaultTableModel> tr = new TableRowSorter<DefaultTableModel>(tablee);
        jTable1.setRowSorter(tr);
        tr.setRowFilter(RowFilter.regexFilter(search.toUpperCase()));   
        
//        jTextField3
//        System.out.println(jTable1.getRowCount());
        Double totalPendapatan =0.0;
        if(jTable1.getRowCount()>0) {
            totalPendapatan =0.0;
            for(int i=0; i<jTable1.getRowCount(); i++) {
                totalPendapatan =totalPendapatan + Double.valueOf(jTable1.getValueAt(i, 8).toString());
            }
            if(idBarang.getText().isEmpty()) {
                jTextField3.setText("Total PerBarang Rp. 0");
            } else {
                jTextField3.setText("("+String.valueOf(jTable1.getRowCount())+") Total PerBarang Rp. "+dFormat.format(totalPendapatan));
            }
        } else {
            jTextField3.setText("Total PerBarang Rp. 0");
        }
        
    }//GEN-LAST:event_idBarangKeyReleased


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField idBarang;
    private javax.swing.JButton jButton2;
    private com.toedter.calendar.JDateChooser jDateChooser1;
    private com.toedter.calendar.JDateChooser jDateChooser2;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JTextField jTextField2;
    private javax.swing.JTextField jTextField3;
    // End of variables declaration//GEN-END:variables
}
