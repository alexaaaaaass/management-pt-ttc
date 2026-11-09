/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Production;

import Absensi.*;
import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import Main.DefaultProgram;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Tools.ConnectDB;
import java.awt.Color;
import java.awt.Cursor;
import java.awt.Rectangle;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Scanner;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JFileChooser;
import javax.swing.JOptionPane;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import org.apache.poi.hssf.usermodel.HSSFCell;
import org.apache.poi.hssf.usermodel.HSSFRow;
import org.apache.poi.hssf.usermodel.HSSFSheet;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.ss.usermodel.DataFormatter;
import org.jdesktop.swingx.autocomplete.AutoCompleteDecorator;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class BoronganPacking extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="",type="PACK";
    int barisEdit=-1;
    String akses="PRODUKSI BORONGAN";
    String idKas="21";
    ArrayList<String> deleteRow;
    final JFileChooser chooser2 = new JFileChooser();
    public BoronganPacking(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        push_button();
        
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
         
        listSatuan.removeAll();
        listSatuan.removeAllItems();
        sql = "select distinct nunit from MPriceBorongan where ctype = '"+type+"'  ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  
            listSatuan.addItem(rs.getString("nunit"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        clear();
        
        jDate1.setDate(new Date());
        jDate2.setDate(new Date());
        
        loadData();
    }
    
    private void push_button(){
        InputMap im = this.getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = this.getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F9, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
//                 System.out.println("Print "+jLabel2.getText());
            }
        });
    }
    
    
    
    private void clear(){
        rfa.empty(keyssDef, tkaryawan,tkaryawannama,tqty);
        btDelete.setVisible(false);
        btSave.setText("Save");
        id=""; 
         
    }
    
    private void loadData(){
        String dtran1=rfa.DateString(keyssDef, jDate1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, jDate2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        sql="select * from PBorongan "
                + " where ctype='"+type+"' and cname like '%"+tkaryawanserach.getText()+"%' "
                + " and (dtran>='"+dtran1+"' and dtran<='"+dtran2+"') order by cname,dtran";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cnik"),
                    rs.getString("cname"),
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"), 
                    rs.getDouble("nqty"),
                    rs.getString("nunit"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(BoronganPacking.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadEdit(){
        sql="select * from PBorongan where id='"+id+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tkaryawan.setText(rs.getString("cnik"));
                tkaryawannama.setText(rs.getString("cname"));
                listSatuan.setSelectedItem(rs.getString("nunit"));
                jDateLembur.setDate(rs.getDate("dtran")); 
                tqty.setText(rs.getString("nqty"));
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(BoronganPacking.class.getName()).log(Level.SEVERE, null, ex);
        }
        btSave.setText("Update");
        btDelete.setVisible(true);
    }
    private void export(){
        String dtran1=rfa.DateString(keyssDef, jDate1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, jDate2.getDate(),"yyyy-MM-dd");
        
        ArrayList<String> kolom;
        kolom = new ArrayList<>();
        kolom.add("id");
        kolom.add("NIK");
        kolom.add("Nama Karyawan");
        kolom.add("Tanggal");
        kolom.add("Jam Awal");
        kolom.add("Jam Akhir");
        kolom.add("Total Jam");
        kolom.add("Nominal");
        kolom.add("Keterangan");
        con.exportExcel(jTable2,kolom,"Laporan Lembur Karyawan ",dtran1,dtran2);
        return;
    }
    private void save(){ 

         HashMap<String,String> listObj = new HashMap<String,String>(); 
         listObj.put("ccode",tcode.getText());       
         listObj.put("ctype",type);       
         listObj.put("cnik",tkaryawan.getText());
         listObj.put("cname",tkaryawannama.getText());
         listObj.put("dtran",rfa.DateString(keyssDef,jDateLembur.getDate(),"yyyy-MM-dd")); 
         listObj.put("nunit",listSatuan.getSelectedItem().toString());
         listObj.put("nqty",rfa.formatDecimal3.format(Double.valueOf(tqty.getText())));
         
         if(id.equals("")) { 
            listObj.put("cusername",Login.user);
            sql=con.insertValue("PBorongan", listObj);
         } else {
            listObj.put("updateby",Login.user);
            sql=con.updateValue("PBorongan", listObj);
            sql+=" where id="+id;
         }
         

         con.queryUpdate(sql);
            
       clear();
       loadData();
        JOptionPane.showMessageDialog(this,"Absensi Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
    }
    
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jScrollPane3 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jLabel21 = new javax.swing.JLabel();
        jLabel23 = new javax.swing.JLabel();
        tmenit = new javax.swing.JTextField();
        tjam = new javax.swing.JTextField();
        jLabel15 = new javax.swing.JLabel();
        jLabel22 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jDate1 = new com.toedter.calendar.JDateChooser();
        jButton7 = new javax.swing.JButton();
        jLabel14 = new javax.swing.JLabel();
        jLabel24 = new javax.swing.JLabel();
        tkaryawanserach = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jPanel1 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel13 = new javax.swing.JLabel();
        tkaryawan = new javax.swing.JTextField();
        jButton8 = new javax.swing.JButton();
        tkaryawannama = new javax.swing.JTextField();
        jDateLembur = new com.toedter.calendar.JDateChooser();
        jLabel16 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel7 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton21 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        tqty = new javax.swing.JTextField();
        jLabel26 = new javax.swing.JLabel();
        listSatuan = new javax.swing.JComboBox();
        jLabel27 = new javax.swing.JLabel();

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ctran", "cnbkt", "ccode", "gudang", "cyear", "dtran", "cacc", "cacc_desc", "caccll", "caccll_desc", "cnote", "namnt"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class, java.lang.String.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane3.setViewportView(jtable_print);

        jLabel21.setText("Dalam Jam");

        jLabel23.setText("Dalam Menit");

        tmenit.setEditable(false);
        tmenit.setBackground(new java.awt.Color(153, 153, 255));
        tmenit.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tmenit.setForeground(new java.awt.Color(255, 255, 255));
        tmenit.setHorizontalAlignment(javax.swing.JTextField.CENTER);

        tjam.setEditable(false);
        tjam.setBackground(new java.awt.Color(153, 153, 255));
        tjam.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tjam.setForeground(new java.awt.Color(255, 255, 255));
        tjam.setHorizontalAlignment(javax.swing.JTextField.CENTER);

        jLabel15.setText("Jam");

        jLabel22.setText(" Menit");

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 30, 180, -1));

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
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 30, 150, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 30, 110, -1));

        jLabel14.setText("Periode ");
        jPanel6.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        jLabel24.setText("Karyawan");
        jPanel6.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        tkaryawanserach.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tkaryawanserachCaretUpdate(evt);
            }
        });
        tkaryawanserach.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkaryawanserachFocusLost(evt);
            }
        });
        tkaryawanserach.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkaryawanserachKeyPressed(evt);
            }
        });
        jPanel6.add(tkaryawanserach, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 60, 150, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton1.setText("Export");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 60, 110, -1));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null}
            },
            new String [] {
                "id", "NIK", "Nama Karyawan", "Tanggal", "Qty Perolehan.", "Satuan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false
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
            jTable2.getColumnModel().getColumn(1).setMinWidth(80);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(80);
            jTable2.getColumnModel().getColumn(3).setMinWidth(90);
            jTable2.getColumnModel().getColumn(3).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(4).setMinWidth(110);
            jTable2.getColumnModel().getColumn(4).setMaxWidth(110);
        }

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setAutoscrolls(true);
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel13.setText("Karyawan");
        jLayeredPane1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 40, -1, 20));

        tkaryawan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkaryawanFocusLost(evt);
            }
        });
        tkaryawan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkaryawanKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 40, 10, -1));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 40, 30, 20));

        tkaryawannama.setEditable(false);
        tkaryawannama.setBackground(new java.awt.Color(153, 153, 255));
        tkaryawannama.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tkaryawannama, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 40, 220, 20));

        jDateLembur.setDateFormatString("dd/MM/yyyy");
        jDateLembur.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateLemburPropertyChange(evt);
            }
        });
        jDateLembur.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateLemburKeyPressed(evt);
            }
        });
        jLayeredPane1.add(jDateLembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 70, 150, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Tanggal Hasil");
        jLayeredPane1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, -1, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(80, 180, 90, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 180, 100, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 180, -1, -1));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Kode Gudang");
        jLayeredPane1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, -1, 20));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jLayeredPane1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 10, 10, 20));

        jButton21.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton21.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton21ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton21, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 10, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 10, 220, 20));

        tqty.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tqty.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                tqtyActionPerformed(evt);
            }
        });
        jLayeredPane1.add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 100, 150, -1));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Satuan Pack");
        jLayeredPane1.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 130, -1, 20));

        listSatuan.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jLayeredPane1.add(listSatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 130, 150, -1));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("Qty Packl");
        jLayeredPane1.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 100, -1, 20));

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 447, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addGap(12, 12, 12)
                        .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addContainerGap(689, Short.MAX_VALUE))
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel4Layout.createSequentialGroup()
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jScrollPane2, javax.swing.GroupLayout.Alignment.TRAILING)
                            .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                        .addContainerGap())))
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane2, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE))
                    .addComponent(jLayeredPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 574, Short.MAX_VALUE))
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(11, 11, 11))
        );

        jTabbedPane1.addTab("PACK", jPanel4);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString(); 
            loadEdit();
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jDate1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1KeyPressed

    private void jDate1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1PropertyChange

    private void jDate2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2KeyPressed

    private void jDate2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2PropertyChange

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton21ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton21ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton21ActionPerformed

    private void tkaryawanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawanFocusLost
        sql = "select * from mkaryawan where nik = '"+tkaryawan.getText()+"'  ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tkaryawannama.setText(rs.getString("cname"));

            }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tkaryawanFocusLost

    private void tkaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawanKeyPressed
       
    }//GEN-LAST:event_tkaryawanKeyPressed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawan.setText(a.Kode);
            tkaryawannama.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton8ActionPerformed

    private void jDateLemburPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateLemburPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateLemburPropertyChange

    private void jDateLemburKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateLemburKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateLemburKeyPressed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(con.akses(akses, rfa.DELETE)) {
                sql="delete from PBorongan where id='"+id+"' ";
            con.queryUpdate(sql);
            clear();
            loadData();
            }
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    save();
                }
            } else if(btSave.getText().equals("Update")) {
                if(con.akses(akses, rfa.UPDATE)) {
                    save();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void tkaryawanserachFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawanserachFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawanserachFocusLost

    private void tkaryawanserachKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawanserachKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawanserachKeyPressed

    private void tkaryawanserachCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tkaryawanserachCaretUpdate
        loadData();
    }//GEN-LAST:event_tkaryawanserachCaretUpdate

    private void tqtyActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_tqtyActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_tqtyActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        clear();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        export();
    }//GEN-LAST:event_jButton1ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton21;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateLembur;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel26;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jtable_print;
    private javax.swing.JComboBox listSatuan;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tjam;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawannama;
    private javax.swing.JTextField tkaryawanserach;
    private javax.swing.JTextField tmenit;
    private javax.swing.JTextField tqty;
    // End of variables declaration//GEN-END:variables
}
