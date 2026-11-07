/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Absensi;

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
public class Lembur extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="TRANSAKSI PAYROLL KARYAWAN";
    String idKas="21";
    ArrayList<String> deleteRow;
    final JFileChooser chooser2 = new JFileChooser();
    public Lembur(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        push_button();
        
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        AutoCompleteDecorator.decorate(this.time1);
        AutoCompleteDecorator.decorate(this.time2);
        clear();
        
        jDate1.setDate(new Date());
        jDate2.setDate(new Date());
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
        rfa.empty(keyssDef, tket,tkaryawan,tkaryawan1,tjam,tmenit);
        btDelete.setVisible(false);
        btSave.setText("Save");
        id="";
        time1.setSelectedIndex(0);
        time2.setSelectedIndex(0);
        
        hitungMenit();
    }
    
    private void loadData(){
        String dtran1=rfa.DateString(keyssDef, jDate1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, jDate2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        sql="select a.*,b.cname as karyawan From AbsensiLembur a join MKaryawan b on a.nik=b.nik "
                + " where b.cname like '%"+tkaryawan2.getText()+"%' "
                + " and (tanggal>='"+dtran1+"' and tanggal<='"+dtran2+"') order by b.cname,a.tanggal";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("nik"),
                    rs.getString("karyawan"),
                    rfa.DateString(keyssDef,rs.getDate("tanggal"),"dd/MM/yyyy"),
                    rs.getString("jamawal")+" "+rs.getString("jamawal_set"),
                    rs.getString("jamakhir")+" "+rs.getString("jamakhir_set"),
                    rs.getString("ctotallembur"),
                    rs.getDouble("cnamnt"),
                    rs.getString("cket"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(Lembur.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadEdit(){
        sql="select * from AbsensiLembur where id='"+id+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tkaryawan.setText(rs.getString("nik"));
                jDateLembur.setDate(rs.getDate("Tanggal"));
                time1.setSelectedItem(rs.getString("jamawal"));
                time2.setSelectedItem(rs.getString("jamakhir"));
                time1set.setSelectedItem(rs.getString("jamawal_set"));
                time2set.setSelectedItem(rs.getString("jamakhir_set"));
                tket.setText(rs.getString("cket"));
                tnominal.setText(rs.getString("cnamnt"));
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(Lembur.class.getName()).log(Level.SEVERE, null, ex);
        }
        
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
        sql="delete from AbsensiLembur where ccode='"+tcode.getText()+"' "
                + "and nik='"+tkaryawan.getText()+"' "
                + "and tanggal='"+rfa.DateString(keyssDef,jDateLembur.getDate(),"yyyy-MM-dd")+"' ";
        con.queryUpdate(sql);

         HashMap<String,String> listObj = new HashMap<String,String>(); 
         listObj.put("ccode",tcode.getText());       
         listObj.put("nik",tkaryawan.getText());
         listObj.put("Tanggal",rfa.DateString(keyssDef,jDateLembur.getDate(),"yyyy-MM-dd"));
         listObj.put("jamawal",time1.getSelectedItem().toString());
         listObj.put("jamakhir",time2.getSelectedItem().toString());
         listObj.put("jamawal_set",time1set.getSelectedItem().toString());
         listObj.put("jamakhir_set",time2set.getSelectedItem().toString());
         listObj.put("ctotaljam",tjam.getText());
         listObj.put("ctotalmenit",tmenit.getText());
         listObj.put("ctotallembur",tlembur.getText());
         listObj.put("cket",tket.getText());
         listObj.put("cnamnt",tnominal.getText());
         sql=con.insertValue("AbsensiLembur", listObj);

         con.queryUpdate(sql);
            
       clear();
       loadData();
        JOptionPane.showMessageDialog(this,"Absensi Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
    }
    
    private void hitungMenit(){
        String[] timemulai = time1.getSelectedItem().toString().split(":");
        String[] timeselesai = time2.getSelectedItem().toString().split(":");
                
        int jam1 = Integer.valueOf(timemulai[0]);
        int menit1 = Integer.valueOf(timemulai[1]);
        int jam2 = Integer.valueOf(timeselesai[0]);
        int menit2 = Integer.valueOf(timeselesai[1]);
        
        if(time1set.getSelectedItem().toString().equalsIgnoreCase("PM")) {
            jam1=jam1+12;
        }else {
            if(jam1==12) {
                jam1 = jam1-12;
            }
        }
        if(time2set.getSelectedItem().toString().equalsIgnoreCase("PM")) {
            jam2=jam2+12;
        } else {
            if(jam2==12) {
                jam2 = jam2-12;
            }
        }
        
        if(jam2<jam1) {
            jam2 = jam2+24;
        }
        
        int waktu1, waktu2, selisih;
       
        waktu1 = (3600*jam1)+(60*menit1);
        waktu2 = (3600*jam2)+(60*menit2);
        selisih = waktu1-waktu2;
        
        int t_jam = (selisih/60/60)*-1;
        int t_menit =(selisih/60)*-1;
        
        tjam.setText(rfa.formatDecimal3.format(t_jam));
        tmenit.setText(rfa.formatDecimal3.format(t_menit));
        
        
        tlembur.setText(rfa.NumberBukti(keyssDef, 2,t_jam)+":"+rfa.NumberBukti(keyssDef, 2,t_menit-t_jam*60));
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
        tnominal = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        jLabel26 = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        tlembur = new javax.swing.JTextField();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jDate1 = new com.toedter.calendar.JDateChooser();
        jButton7 = new javax.swing.JButton();
        jLabel14 = new javax.swing.JLabel();
        jLabel24 = new javax.swing.JLabel();
        tkaryawan2 = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jPanel1 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel13 = new javax.swing.JLabel();
        tkaryawan = new javax.swing.JTextField();
        jButton8 = new javax.swing.JButton();
        tkaryawan1 = new javax.swing.JTextField();
        jDateLembur = new com.toedter.calendar.JDateChooser();
        jLabel16 = new javax.swing.JLabel();
        jLabel17 = new javax.swing.JLabel();
        time2 = new javax.swing.JComboBox();
        time1 = new javax.swing.JComboBox();
        jLabel18 = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel7 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton21 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        time2set = new javax.swing.JComboBox();
        time1set = new javax.swing.JComboBox();

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

        tnominal.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tnominal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                tnominalActionPerformed(evt);
            }
        });

        jLabel20.setText("Rp");

        jLabel26.setText("Nominal Lembur*");

        jLabel25.setText("Total Lembur");

        tlembur.setEditable(false);
        tlembur.setBackground(new java.awt.Color(153, 153, 255));
        tlembur.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tlembur.setForeground(new java.awt.Color(255, 255, 255));
        tlembur.setHorizontalAlignment(javax.swing.JTextField.CENTER);

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

        tkaryawan2.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tkaryawan2CaretUpdate(evt);
            }
        });
        tkaryawan2.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkaryawan2FocusLost(evt);
            }
        });
        tkaryawan2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkaryawan2KeyPressed(evt);
            }
        });
        jPanel6.add(tkaryawan2, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 60, 150, -1));

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
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "NIK", "Nama Karyawan", "Tanggal", "Jam Mulai", "Jam Selesai", "Total Jam", "Nominal", "Keterangan Lembur"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class
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
            jTable2.getColumnModel().getColumn(4).setMinWidth(90);
            jTable2.getColumnModel().getColumn(4).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(5).setMinWidth(90);
            jTable2.getColumnModel().getColumn(5).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(6).setMinWidth(0);
            jTable2.getColumnModel().getColumn(6).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(7).setMinWidth(0);
            jTable2.getColumnModel().getColumn(7).setMaxWidth(0);
        }

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setAutoscrolls(true);
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setText("Karyawan");
        jLayeredPane1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, -1, 20));

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
        jLayeredPane1.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 70, 90, -1));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 70, 30, 20));

        tkaryawan1.setEditable(false);
        tkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        tkaryawan1.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tkaryawan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 100, 260, 20));

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
        jLayeredPane1.add(jDateLembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 130, 150, -1));

        jLabel16.setText("Tanggal Lembur");
        jLayeredPane1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 130, -1, 20));

        jLabel17.setText("Jam Awal Lembur");
        jLayeredPane1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 160, -1, 20));

        time2.setEditable(true);
        time2.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "01:00", "01:01", "01:02", "01:03", "01:04", "01:05", "01:06", "01:07", "01:08", "01:09", "01:10", "01:11", "01:12", "01:13", "01:14", "01:15", "01:16", "01:17", "01:18", "01:19", "01:20", "01:21", "01:22", "01:23", "01:24", "01:25", "01:26", "01:27", "01:28", "01:29", "01:30", "01:31", "01:32", "01:33", "01:34", "01:35", "01:36", "01:37", "01:38", "01:39", "01:40", "01:41", "01:42", "01:43", "01:44", "01:45", "01:46", "01:47", "01:48", "01:49", "01:50", "01:51", "01:52", "01:53", "01:54", "01:55", "01:56", "01:57", "01:58", "01:59", "02:00", "02:01", "02:02", "02:03", "02:04", "02:05", "02:06", "02:07", "02:08", "02:09", "02:10", "02:11", "02:12", "02:13", "02:14", "02:15", "02:16", "02:17", "02:18", "02:19", "02:20", "02:21", "02:22", "02:23", "02:24", "02:25", "02:26", "02:27", "02:28", "02:29", "02:30", "02:31", "02:32", "02:33", "02:34", "02:35", "02:36", "02:37", "02:38", "02:39", "02:40", "02:41", "02:42", "02:43", "02:44", "02:45", "02:46", "02:47", "02:48", "02:49", "02:50", "02:51", "02:52", "02:53", "02:54", "02:55", "02:56", "02:57", "02:58", "02:59", "03:00", "03:01", "03:02", "03:03", "03:04", "03:05", "03:06", "03:07", "03:08", "03:09", "03:10", "03:11", "03:12", "03:13", "03:14", "03:15", "03:16", "03:17", "03:18", "03:19", "03:20", "03:21", "03:22", "03:23", "03:24", "03:25", "03:26", "03:27", "03:28", "03:29", "03:30", "03:31", "03:32", "03:33", "03:34", "03:35", "03:36", "03:37", "03:38", "03:39", "03:40", "03:41", "03:42", "03:43", "03:44", "03:45", "03:46", "03:47", "03:48", "03:49", "03:50", "03:51", "03:52", "03:53", "03:54", "03:55", "03:56", "03:57", "03:58", "03:59", "04:00", "04:01", "04:02", "04:03", "04:04", "04:05", "04:06", "04:07", "04:08", "04:09", "04:10", "04:11", "04:12", "04:13", "04:14", "04:15", "04:16", "04:17", "04:18", "04:19", "04:20", "04:21", "04:22", "04:23", "04:24", "04:25", "04:26", "04:27", "04:28", "04:29", "04:30", "04:31", "04:32", "04:33", "04:34", "04:35", "04:36", "04:37", "04:38", "04:39", "04:40", "04:41", "04:42", "04:43", "04:44", "04:45", "04:46", "04:47", "04:48", "04:49", "04:50", "04:51", "04:52", "04:53", "04:54", "04:55", "04:56", "04:57", "04:58", "04:59", "05:00", "05:01", "05:02", "05:03", "05:04", "05:05", "05:06", "05:07", "05:08", "05:09", "05:10", "05:11", "05:12", "05:13", "05:14", "05:15", "05:16", "05:17", "05:18", "05:19", "05:20", "05:21", "05:22", "05:23", "05:24", "05:25", "05:26", "05:27", "05:28", "05:29", "05:30", "05:31", "05:32", "05:33", "05:34", "05:35", "05:36", "05:37", "05:38", "05:39", "05:40", "05:41", "05:42", "05:43", "05:44", "05:45", "05:46", "05:47", "05:48", "05:49", "05:50", "05:51", "05:52", "05:53", "05:54", "05:55", "05:56", "05:57", "05:58", "05:59", "06:00", "06:01", "06:02", "06:03", "06:04", "06:05", "06:06", "06:07", "06:08", "06:09", "06:10", "06:11", "06:12", "06:13", "06:14", "06:15", "06:16", "06:17", "06:18", "06:19", "06:20", "06:21", "06:22", "06:23", "06:24", "06:25", "06:26", "06:27", "06:28", "06:29", "06:30", "06:31", "06:32", "06:33", "06:34", "06:35", "06:36", "06:37", "06:38", "06:39", "06:40", "06:41", "06:42", "06:43", "06:44", "06:45", "06:46", "06:47", "06:48", "06:49", "06:50", "06:51", "06:52", "06:53", "06:54", "06:55", "06:56", "06:57", "06:58", "06:59", "07:00", "07:01", "07:02", "07:03", "07:04", "07:05", "07:06", "07:07", "07:08", "07:09", "07:10", "07:11", "07:12", "07:13", "07:14", "07:15", "07:16", "07:17", "07:18", "07:19", "07:20", "07:21", "07:22", "07:23", "07:24", "07:25", "07:26", "07:27", "07:28", "07:29", "07:30", "07:31", "07:32", "07:33", "07:34", "07:35", "07:36", "07:37", "07:38", "07:39", "07:40", "07:41", "07:42", "07:43", "07:44", "07:45", "07:46", "07:47", "07:48", "07:49", "07:50", "07:51", "07:52", "07:53", "07:54", "07:55", "07:56", "07:57", "07:58", "07:59", "08:00", "08:01", "08:02", "08:03", "08:04", "08:05", "08:06", "08:07", "08:08", "08:09", "08:10", "08:11", "08:12", "08:13", "08:14", "08:15", "08:16", "08:17", "08:18", "08:19", "08:20", "08:21", "08:22", "08:23", "08:24", "08:25", "08:26", "08:27", "08:28", "08:29", "08:30", "08:31", "08:32", "08:33", "08:34", "08:35", "08:36", "08:37", "08:38", "08:39", "08:40", "08:41", "08:42", "08:43", "08:44", "08:45", "08:46", "08:47", "08:48", "08:49", "08:50", "08:51", "08:52", "08:53", "08:54", "08:55", "08:56", "08:57", "08:58", "08:59", "09:00", "09:01", "09:02", "09:03", "09:04", "09:05", "09:06", "09:07", "09:08", "09:09", "09:10", "09:11", "09:12", "09:13", "09:14", "09:15", "09:16", "09:17", "09:18", "09:19", "09:20", "09:21", "09:22", "09:23", "09:24", "09:25", "09:26", "09:27", "09:28", "09:29", "09:30", "09:31", "09:32", "09:33", "09:34", "09:35", "09:36", "09:37", "09:38", "09:39", "09:40", "09:41", "09:42", "09:43", "09:44", "09:45", "09:46", "09:47", "09:48", "09:49", "09:50", "09:51", "09:52", "09:53", "09:54", "09:55", "09:56", "09:57", "09:58", "09:59", "10:00", "10:01", "10:02", "10:03", "10:04", "10:05", "10:06", "10:07", "10:08", "10:09", "10:10", "10:11", "10:12", "10:13", "10:14", "10:15", "10:16", "10:17", "10:18", "10:19", "10:20", "10:21", "10:22", "10:23", "10:24", "10:25", "10:26", "10:27", "10:28", "10:29", "10:30", "10:31", "10:32", "10:33", "10:34", "10:35", "10:36", "10:37", "10:38", "10:39", "10:40", "10:41", "10:42", "10:43", "10:44", "10:45", "10:46", "10:47", "10:48", "10:49", "10:50", "10:51", "10:52", "10:53", "10:54", "10:55", "10:56", "10:57", "10:58", "10:59", "11:00", "11:01", "11:02", "11:03", "11:04", "11:05", "11:06", "11:07", "11:08", "11:09", "11:10", "11:11", "11:12", "11:13", "11:14", "11:15", "11:16", "11:17", "11:18", "11:19", "11:20", "11:21", "11:22", "11:23", "11:24", "11:25", "11:26", "11:27", "11:28", "11:29", "11:30", "11:31", "11:32", "11:33", "11:34", "11:35", "11:36", "11:37", "11:38", "11:39", "11:40", "11:41", "11:42", "11:43", "11:44", "11:45", "11:46", "11:47", "11:48", "11:49", "11:50", "11:51", "11:52", "11:53", "11:54", "11:55", "11:56", "11:57", "11:58", "11:59", "12:00", "12:01", "12:02", "12:03", "12:04", "12:05", "12:06", "12:07", "12:08", "12:09", "12:10", "12:11", "12:12", "12:13", "12:14", "12:15", "12:16", "12:17", "12:18", "12:19", "12:20", "12:21", "12:22", "12:23", "12:24", "12:25", "12:26", "12:27", "12:28", "12:29", "12:30", "12:31", "12:32", "12:33", "12:34", "12:35", "12:36", "12:37", "12:38", "12:39", "12:40", "12:41", "12:42", "12:43", "12:44", "12:45", "12:46", "12:47", "12:48", "12:49", "12:50", "12:51", "12:52", "12:53", "12:54", "12:55", "12:56", "12:57", "12:58", "12:59" }));
        time2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                time2PropertyChange(evt);
            }
        });
        jLayeredPane1.add(time2, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 190, 80, -1));

        time1.setEditable(true);
        time1.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "01:00", "01:01", "01:02", "01:03", "01:04", "01:05", "01:06", "01:07", "01:08", "01:09", "01:10", "01:11", "01:12", "01:13", "01:14", "01:15", "01:16", "01:17", "01:18", "01:19", "01:20", "01:21", "01:22", "01:23", "01:24", "01:25", "01:26", "01:27", "01:28", "01:29", "01:30", "01:31", "01:32", "01:33", "01:34", "01:35", "01:36", "01:37", "01:38", "01:39", "01:40", "01:41", "01:42", "01:43", "01:44", "01:45", "01:46", "01:47", "01:48", "01:49", "01:50", "01:51", "01:52", "01:53", "01:54", "01:55", "01:56", "01:57", "01:58", "01:59", "02:00", "02:01", "02:02", "02:03", "02:04", "02:05", "02:06", "02:07", "02:08", "02:09", "02:10", "02:11", "02:12", "02:13", "02:14", "02:15", "02:16", "02:17", "02:18", "02:19", "02:20", "02:21", "02:22", "02:23", "02:24", "02:25", "02:26", "02:27", "02:28", "02:29", "02:30", "02:31", "02:32", "02:33", "02:34", "02:35", "02:36", "02:37", "02:38", "02:39", "02:40", "02:41", "02:42", "02:43", "02:44", "02:45", "02:46", "02:47", "02:48", "02:49", "02:50", "02:51", "02:52", "02:53", "02:54", "02:55", "02:56", "02:57", "02:58", "02:59", "03:00", "03:01", "03:02", "03:03", "03:04", "03:05", "03:06", "03:07", "03:08", "03:09", "03:10", "03:11", "03:12", "03:13", "03:14", "03:15", "03:16", "03:17", "03:18", "03:19", "03:20", "03:21", "03:22", "03:23", "03:24", "03:25", "03:26", "03:27", "03:28", "03:29", "03:30", "03:31", "03:32", "03:33", "03:34", "03:35", "03:36", "03:37", "03:38", "03:39", "03:40", "03:41", "03:42", "03:43", "03:44", "03:45", "03:46", "03:47", "03:48", "03:49", "03:50", "03:51", "03:52", "03:53", "03:54", "03:55", "03:56", "03:57", "03:58", "03:59", "04:00", "04:01", "04:02", "04:03", "04:04", "04:05", "04:06", "04:07", "04:08", "04:09", "04:10", "04:11", "04:12", "04:13", "04:14", "04:15", "04:16", "04:17", "04:18", "04:19", "04:20", "04:21", "04:22", "04:23", "04:24", "04:25", "04:26", "04:27", "04:28", "04:29", "04:30", "04:31", "04:32", "04:33", "04:34", "04:35", "04:36", "04:37", "04:38", "04:39", "04:40", "04:41", "04:42", "04:43", "04:44", "04:45", "04:46", "04:47", "04:48", "04:49", "04:50", "04:51", "04:52", "04:53", "04:54", "04:55", "04:56", "04:57", "04:58", "04:59", "05:00", "05:01", "05:02", "05:03", "05:04", "05:05", "05:06", "05:07", "05:08", "05:09", "05:10", "05:11", "05:12", "05:13", "05:14", "05:15", "05:16", "05:17", "05:18", "05:19", "05:20", "05:21", "05:22", "05:23", "05:24", "05:25", "05:26", "05:27", "05:28", "05:29", "05:30", "05:31", "05:32", "05:33", "05:34", "05:35", "05:36", "05:37", "05:38", "05:39", "05:40", "05:41", "05:42", "05:43", "05:44", "05:45", "05:46", "05:47", "05:48", "05:49", "05:50", "05:51", "05:52", "05:53", "05:54", "05:55", "05:56", "05:57", "05:58", "05:59", "06:00", "06:01", "06:02", "06:03", "06:04", "06:05", "06:06", "06:07", "06:08", "06:09", "06:10", "06:11", "06:12", "06:13", "06:14", "06:15", "06:16", "06:17", "06:18", "06:19", "06:20", "06:21", "06:22", "06:23", "06:24", "06:25", "06:26", "06:27", "06:28", "06:29", "06:30", "06:31", "06:32", "06:33", "06:34", "06:35", "06:36", "06:37", "06:38", "06:39", "06:40", "06:41", "06:42", "06:43", "06:44", "06:45", "06:46", "06:47", "06:48", "06:49", "06:50", "06:51", "06:52", "06:53", "06:54", "06:55", "06:56", "06:57", "06:58", "06:59", "07:00", "07:01", "07:02", "07:03", "07:04", "07:05", "07:06", "07:07", "07:08", "07:09", "07:10", "07:11", "07:12", "07:13", "07:14", "07:15", "07:16", "07:17", "07:18", "07:19", "07:20", "07:21", "07:22", "07:23", "07:24", "07:25", "07:26", "07:27", "07:28", "07:29", "07:30", "07:31", "07:32", "07:33", "07:34", "07:35", "07:36", "07:37", "07:38", "07:39", "07:40", "07:41", "07:42", "07:43", "07:44", "07:45", "07:46", "07:47", "07:48", "07:49", "07:50", "07:51", "07:52", "07:53", "07:54", "07:55", "07:56", "07:57", "07:58", "07:59", "08:00", "08:01", "08:02", "08:03", "08:04", "08:05", "08:06", "08:07", "08:08", "08:09", "08:10", "08:11", "08:12", "08:13", "08:14", "08:15", "08:16", "08:17", "08:18", "08:19", "08:20", "08:21", "08:22", "08:23", "08:24", "08:25", "08:26", "08:27", "08:28", "08:29", "08:30", "08:31", "08:32", "08:33", "08:34", "08:35", "08:36", "08:37", "08:38", "08:39", "08:40", "08:41", "08:42", "08:43", "08:44", "08:45", "08:46", "08:47", "08:48", "08:49", "08:50", "08:51", "08:52", "08:53", "08:54", "08:55", "08:56", "08:57", "08:58", "08:59", "09:00", "09:01", "09:02", "09:03", "09:04", "09:05", "09:06", "09:07", "09:08", "09:09", "09:10", "09:11", "09:12", "09:13", "09:14", "09:15", "09:16", "09:17", "09:18", "09:19", "09:20", "09:21", "09:22", "09:23", "09:24", "09:25", "09:26", "09:27", "09:28", "09:29", "09:30", "09:31", "09:32", "09:33", "09:34", "09:35", "09:36", "09:37", "09:38", "09:39", "09:40", "09:41", "09:42", "09:43", "09:44", "09:45", "09:46", "09:47", "09:48", "09:49", "09:50", "09:51", "09:52", "09:53", "09:54", "09:55", "09:56", "09:57", "09:58", "09:59", "10:00", "10:01", "10:02", "10:03", "10:04", "10:05", "10:06", "10:07", "10:08", "10:09", "10:10", "10:11", "10:12", "10:13", "10:14", "10:15", "10:16", "10:17", "10:18", "10:19", "10:20", "10:21", "10:22", "10:23", "10:24", "10:25", "10:26", "10:27", "10:28", "10:29", "10:30", "10:31", "10:32", "10:33", "10:34", "10:35", "10:36", "10:37", "10:38", "10:39", "10:40", "10:41", "10:42", "10:43", "10:44", "10:45", "10:46", "10:47", "10:48", "10:49", "10:50", "10:51", "10:52", "10:53", "10:54", "10:55", "10:56", "10:57", "10:58", "10:59", "11:00", "11:01", "11:02", "11:03", "11:04", "11:05", "11:06", "11:07", "11:08", "11:09", "11:10", "11:11", "11:12", "11:13", "11:14", "11:15", "11:16", "11:17", "11:18", "11:19", "11:20", "11:21", "11:22", "11:23", "11:24", "11:25", "11:26", "11:27", "11:28", "11:29", "11:30", "11:31", "11:32", "11:33", "11:34", "11:35", "11:36", "11:37", "11:38", "11:39", "11:40", "11:41", "11:42", "11:43", "11:44", "11:45", "11:46", "11:47", "11:48", "11:49", "11:50", "11:51", "11:52", "11:53", "11:54", "11:55", "11:56", "11:57", "11:58", "11:59", "12:00", "12:01", "12:02", "12:03", "12:04", "12:05", "12:06", "12:07", "12:08", "12:09", "12:10", "12:11", "12:12", "12:13", "12:14", "12:15", "12:16", "12:17", "12:18", "12:19", "12:20", "12:21", "12:22", "12:23", "12:24", "12:25", "12:26", "12:27", "12:28", "12:29", "12:30", "12:31", "12:32", "12:33", "12:34", "12:35", "12:36", "12:37", "12:38", "12:39", "12:40", "12:41", "12:42", "12:43", "12:44", "12:45", "12:46", "12:47", "12:48", "12:49", "12:50", "12:51", "12:52", "12:53", "12:54", "12:55", "12:56", "12:57", "12:58", "12:59" }));
        time1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                time1PropertyChange(evt);
            }
        });
        jLayeredPane1.add(time1, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 160, 80, -1));

        jLabel18.setText("Jam Selesai Lembur");
        jLayeredPane1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 190, -1, 20));
        jLayeredPane1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 220, 260, -1));

        jLabel19.setText("Keterangan Lembur");
        jLayeredPane1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 220, -1, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 280, 90, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 280, 100, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 280, -1, -1));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Kode Gudang");
        jLayeredPane1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, -1, -1));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jLayeredPane1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 10, 110, 20));

        jButton21.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton21.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton21ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton21, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 10, 40, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 40, 260, 20));

        time2set.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "AM", "PM" }));
        time2set.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                time2setPropertyChange(evt);
            }
        });
        jLayeredPane1.add(time2set, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 190, 60, -1));

        time1set.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "AM", "PM" }));
        time1set.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                time1setPropertyChange(evt);
            }
        });
        jLayeredPane1.add(time1set, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 160, 60, -1));

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

        jTabbedPane1.addTab("Lembur", jPanel4);

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
            cnbkt = jTable2.getValueAt(baris, 4).toString();
            cnbkt_print = cnbkt;
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
                tkaryawan1.setText(rs.getString("cname"));

            }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tkaryawanFocusLost

    private void tkaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawanKeyPressed
        rfa.EnterNextFocus(evt, tket);
    }//GEN-LAST:event_tkaryawanKeyPressed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawan.setText(a.Kode);
            tkaryawan1.setText(a.Nama);
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
                sql="delete from AbsensiLembur where id='"+id+"' ";
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
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void time1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_time1PropertyChange
        try{
            hitungMenit();
        } catch (Exception e){
            
        }
    }//GEN-LAST:event_time1PropertyChange

    private void time2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_time2PropertyChange
        try{
            hitungMenit();
        } catch (Exception e){
            
        }
    }//GEN-LAST:event_time2PropertyChange

    private void time2setPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_time2setPropertyChange
        try{
            hitungMenit();
        } catch (Exception e){
            
        }
    }//GEN-LAST:event_time2setPropertyChange

    private void time1setPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_time1setPropertyChange
        try{
            hitungMenit();
        } catch (Exception e){
            
        }
    }//GEN-LAST:event_time1setPropertyChange

    private void tkaryawan2FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawan2FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawan2FocusLost

    private void tkaryawan2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawan2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawan2KeyPressed

    private void tkaryawan2CaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tkaryawan2CaretUpdate
        loadData();
    }//GEN-LAST:event_tkaryawan2CaretUpdate

    private void tnominalActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_tnominalActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_tnominalActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        // TODO add your handling code here:
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
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel26;
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
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JComboBox time1;
    private javax.swing.JComboBox time1set;
    private javax.swing.JComboBox time2;
    private javax.swing.JComboBox time2set;
    private javax.swing.JTextField tjam;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawan1;
    private javax.swing.JTextField tkaryawan2;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tlembur;
    private javax.swing.JTextField tmenit;
    private javax.swing.JTextField tnominal;
    // End of variables declaration//GEN-END:variables
}
