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
import Production.ProduksiInput2;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.Color;
import java.awt.Component;
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
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Scanner;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JFileChooser;
import javax.swing.JOptionPane;
import javax.swing.JTable;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableCellRenderer;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableCellRenderer;
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
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class RekapAbsensi extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="REKAP ABSENSI";
    String idKas="21";
    ArrayList<String> deleteRow;
    
    List listData;
    final JFileChooser chooser2 = new JFileChooser();
    public RekapAbsensi(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        push_button();
        deff();
    }
    
    private void deff(){
        jDate1.setDate(new Date());
        jDate2.setDate(new Date());
        jDate3.setDate(new Date());
        jDate4.setDate(new Date());
    }
    
    private void push_button(){
        InputMap im = this.getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = this.getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F9, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                 System.out.println("Print "+jLabel2.getText());
            }
        });
    }
    
    private void loadData(){
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        sql="select * from AbsensiRekap where cidk like '%"+tkaryawan.getText()+"%' and (tanggal>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy/MM/dd")+"' and tanggal<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy/MM/dd")+"') "
                + " order by tanggal ";
        //System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cidk"),
                    rs.getString("name"),
                    rs.getString("cjabatan"),
                    rs.getString("tanggal"),
                    rs.getString("timetable"), 
                    rs.getString("onduty"),
                    rs.getString("offduty"),
                    rs.getString("clockin"),
                    rs.getString("clockout"),
                    rs.getString("late"),
                    rs.getString("early"), 
                    rs.getString("lembur_menit"), 
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RekapAbsensi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
    }
    
    private void loadData2(){ 
         
        sql="/****** Script for SelectTopNRows command from SSMS  ******/\n" +
                    "SELECT  [ccode]\n" +
                    "      ,[cidk]\n" +
                    "      ,[Name]\n" +
                    "      ,[cjabatan]\n" +
                    "      ,[Tanggal]\n" +
                    "      ,[OnDuty]\n" +
                    "      ,[OffDuty]\n" +
                    "      ,[ClockIn]\n" +
                    "      ,[ClockOut]\n" +
                    "      ,[Late]\n" +
                    "      ,[Early]\n" +
                    "      ,[OK]\n" +
                    "      ,[A]\n" +
                    "      ,[LIBUR]\n" +
                    "      ,[HOLIDAY]\n" +
                    "      ,[TELAT]\n" +
                    "      ,[TELAT_MENIT]\n" +
                    "      ,[PCP]\n" +
                    "      ,[PCP_MENIT]\n" +
                    "      ,[LEMBUR]\n" +
                    "      ,[LEMBUR_MENIT]\n" +
                    "      ,FORMAT(GP_LEMBUR,'#,###') as NILAI_LEMBUR\n" +
                    "      ,[IJIN]\n" +
                    "      ,[IJIN_MENIT]\n" +
                    "      ,[CUTI]\n" +
                    "      ,[SAKIT]\n" +
                    "      ,[SAKIT_SDOKTER] \n" +
                    "  FROM [PayrollD] where cidk like '%"+tkaryawan.getText().replaceAll(" ", "")+"%'    and (tanggal>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy/MM/dd")+"' and tanggal<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy/MM/dd")+"') order by cidk,tanggal ";
        //System.out.println(sql);
        sql="select *,'' as SPACE from PayrollD  where cidk like '%"+tkaryawan.getText().replaceAll(" ", "")+"%'    and (tanggal>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy/MM/dd")+"' and tanggal<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy/MM/dd")+"') order by cidk,tanggal ";
        System.out.println(sql);
        listData = con.queryToListMap(this, sql);
        if(listData.size()<=0) {
            return;
        }
        LinkedHashMap mapData = (LinkedHashMap) listData.get(listData.size() - 1);
        
        List listHeader = new ArrayList();
        listHeader.add(listHeader.size(), "No");
        for (int i = 0; i < mapData.size(); i++) {
            listHeader.add(listHeader.size(), mapData.keySet().toArray()[i].toString().replaceAll("'", ""));
        }
        
        DefaultTableModel tabelModel = new DefaultTableModel() {

            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }

            @Override
            public Class getColumnClass(int col) {
//                if (getRowCount() > 0) {
//                    try {
//                        return getValueAt(0, col).getClass();
//                    } catch (Exception ex) {
//                        return Object.class;
//                    }
//                } else {
//                    return Object.class;
//                }
                return Object.class;
            }
        };
        for (int i = 0; i < listHeader.size(); i++) {
            tabelModel.addColumn(listHeader.get(i));
        }
        jTable2.setModel(tabelModel);
        

        reloadData();
    }
    private void reloadData(){
        DefaultTableModel tabelModel = (DefaultTableModel) jTable2.getModel();
        rfa.clear_table(keyssDef, jTable2);
 
        ArrayList<Integer> colorSet = new ArrayList<>();
        colorSet.clear();
        int count=0;
        int ixRow = 0;
        for (int row = 0; row < listData.size(); row++) { 
            
            LinkedHashMap mapResult = (LinkedHashMap) listData.get(row);
            //System.out.println(mapResult); 
            Object[] data = new Object[mapResult.size() + 1];
            data[0] = row + 1;
            System.arraycopy(mapResult.values().toArray(), 0, data, 1, data.length - 1);
            
            tabelModel.insertRow(ixRow, data);
            ixRow++;
        }

        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();
 
        
        jTable2.setAutoResizeMode(JTable.AUTO_RESIZE_OFF);
         
    }
    
    private void loadDataRekap(){
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        sql="select   cidk,Name,cjabatan,\n" +
                "SUM(OK) as OK,\n" +
                "SUM(A) as A,\n" +
                "SUM(TELAT) as TELAT,\n" +
                "SUM(PCP) as PCP,\n" +
                "SUM(LEMBUR) as LEMBUR,\n" +
                "SUM(IJIN) as IJIN,\n" +
                "SUM(CUTI) as CUTI,\n" +
                "SUM(SAKIT) as SAKIT\n" +
                "from AbsensiRekap\n" +
                "where Name like '%"+tkaryawan.getText()+"%' and (convert(varchar,Tanggal,111)>='"+rfa.DateString(keyssDef, jDate3.getDate(), "yyyy/MM/dd")+"' and convert(varchar,Tanggal,111)<='"+rfa.DateString(keyssDef, jDate4.getDate(), "yyyy/MM/dd")+"')\n" +
                "group by cidk,Name,cjabatan";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("Name"),
                    rs.getString("cjabatan"),
                    rs.getDouble("ok"),
                    rs.getDouble("TELAT"),
                    rs.getDouble("pcp"),
                    rs.getDouble("a"),
                    rs.getDouble("IJIN"),
                    rs.getDouble("cuti"),
                    rs.getDouble("lembur"), 
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RekapAbsensi.class.getName()).log(Level.SEVERE, null, ex);
        }
        
    }
    
    
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jScrollPane3 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jLabel12 = new javax.swing.JLabel();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        jDate1 = new com.toedter.calendar.JDateChooser();
        jButton7 = new javax.swing.JButton();
        tkaryawan = new javax.swing.JTextField();
        jButton8 = new javax.swing.JButton();
        tkaryawan1 = new javax.swing.JTextField();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jPanel1 = new javax.swing.JPanel();
        jPanel3 = new javax.swing.JPanel();
        jPanel7 = new javax.swing.JPanel();
        jLabel10 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        jDate3 = new com.toedter.calendar.JDateChooser();
        jLabel15 = new javax.swing.JLabel();
        jDate4 = new com.toedter.calendar.JDateChooser();
        jButton9 = new javax.swing.JButton();
        tkaryawan2 = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        tkaryawan3 = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();

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

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Rekap Absensi ");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 60, -1, 20));

        jLabel12.setText("Karyawan");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 60, 150, -1));

        jLabel13.setText("Periode ");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

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
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 150, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(470, 30, 90, -1));

        tkaryawan.setEditable(false);
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
        jPanel6.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 90, 20));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 30, 30, 20));

        tkaryawan1.setEditable(false);
        tkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel6.add(tkaryawan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 30, 210, 20));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "NIK", "Nama", "Jabatan", "Tanggal", "TimeTable", "OnDuty", "OffDuty", "ClockIn", "ClockOut", "Late", "Early", "Lembur Jam"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false
            };

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
            jTable2.getColumnModel().getColumn(5).setMinWidth(90);
            jTable2.getColumnModel().getColumn(5).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(6).setMinWidth(90);
            jTable2.getColumnModel().getColumn(6).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(7).setMinWidth(90);
            jTable2.getColumnModel().getColumn(7).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(8).setMinWidth(90);
            jTable2.getColumnModel().getColumn(8).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(9).setMinWidth(90);
            jTable2.getColumnModel().getColumn(9).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(10).setMinWidth(90);
            jTable2.getColumnModel().getColumn(10).setMaxWidth(90);
        }

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 971, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 408, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(25, 25, 25))
        );

        jTabbedPane1.addTab("List Absensi", jPanel4);

        jPanel7.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel7.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel10.setText("s/d");
        jPanel7.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 60, -1, 20));

        jLabel14.setText("Karyawan");
        jPanel7.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        jDate3.setDateFormatString("dd/MM/yyyy");
        jDate3.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDate3PropertyChange(evt);
            }
        });
        jDate3.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDate3KeyPressed(evt);
            }
        });
        jPanel7.add(jDate3, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 150, -1));

        jLabel15.setText("Periode ");
        jPanel7.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        jDate4.setDateFormatString("dd/MM/yyyy");
        jDate4.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDate4PropertyChange(evt);
            }
        });
        jDate4.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDate4KeyPressed(evt);
            }
        });
        jPanel7.add(jDate4, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 60, 150, -1));

        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton9.setText("Cari");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel7.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(460, 30, 90, -1));

        tkaryawan2.setEditable(false);
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
        jPanel7.add(tkaryawan2, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 90, 20));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel7.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(190, 30, 30, 20));

        tkaryawan3.setEditable(false);
        tkaryawan3.setBackground(new java.awt.Color(153, 153, 255));
        jPanel7.add(tkaryawan3, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 30, 210, 20));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Nama Karyawan", "Jabatan", "OK", "Terlambat", "PCP", "Alpha", "Izin", "Cuti", "Lembur"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class
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
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(2).setMinWidth(80);
            jTable1.getColumnModel().getColumn(2).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(3).setMinWidth(80);
            jTable1.getColumnModel().getColumn(3).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(4).setMinWidth(80);
            jTable1.getColumnModel().getColumn(4).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(5).setMinWidth(80);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(6).setMinWidth(80);
            jTable1.getColumnModel().getColumn(6).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(7).setMinWidth(80);
            jTable1.getColumnModel().getColumn(7).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(8).setMinWidth(80);
            jTable1.getColumnModel().getColumn(8).setMaxWidth(80);
        }

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel3Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jScrollPane1)
                    .addComponent(jPanel7, javax.swing.GroupLayout.DEFAULT_SIZE, 971, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel3Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel7, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1)
                .addContainerGap())
        );

        jTabbedPane1.addTab("Rekap Kehadiran", jPanel3);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jTabbedPane1)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 41, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
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
        
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData2();
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

    private void tkaryawanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawanFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawanFocusLost

    private void tkaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawanKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawanKeyPressed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawan.setText(a.Kode);
            tkaryawan1.setText(a.Nama);
        } else {
            rfa.empty(keyssDef, tkaryawan,tkaryawan1);
        }
    }//GEN-LAST:event_jButton8ActionPerformed

    private void jDate3PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate3PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate3PropertyChange

    private void jDate3KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate3KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate3KeyPressed

    private void jDate4PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate4PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate4PropertyChange

    private void jDate4KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate4KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate4KeyPressed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        loadDataRekap();
    }//GEN-LAST:event_jButton9ActionPerformed

    private void tkaryawan2FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawan2FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawan2FocusLost

    private void tkaryawan2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawan2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawan2KeyPressed

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawan2.setText(a.Kode);
            tkaryawan3.setText(a.Nama);
        } else {
            rfa.empty(keyssDef, tkaryawan2,tkaryawan3);
        }
    }//GEN-LAST:event_jButton10ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDate3;
    private com.toedter.calendar.JDateChooser jDate4;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel7;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawan1;
    private javax.swing.JTextField tkaryawan2;
    private javax.swing.JTextField tkaryawan3;
    // End of variables declaration//GEN-END:variables
}
