/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Absensi;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
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
import org.apache.poi.ss.usermodel.Cell;
import org.apache.poi.ss.usermodel.DataFormatter;
import org.apache.poi.ss.usermodel.FormulaEvaluator;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class UploadAbsensi extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="UPLOAD ABSENSI";
    String idKas="21";
    ArrayList<String> deleteRow;
    final JFileChooser chooser2 = new JFileChooser();
    public UploadAbsensi(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        push_button();
        btSave.setEnabled(false);
        
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        list1.setSelectedIndex(0);
        list2.setSelectedIndex(1);
        list3.setSelectedIndex(2);
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
        btSave.setEnabled(false);
    }
    
    private void load_excel(String lokasiFile, int tipe){
        File ft=new File(lokasiFile);
        DataFormatter formatter = new DataFormatter();
        Scanner in=null;
        String kodeGudang="";
        String namaGudang="";
        String[] temp;
        int brs=1;
        temp = new String[16];
        DefaultTableModel tabel = (DefaultTableModel) jTable2.getModel();
        rfa.clear_table(keyssDef,jTable2);
        
        try {
            in = new Scanner(ft);
        } catch (FileNotFoundException ex) {
            System.out.println(ex);
        }
        
        try {
            FileInputStream fis = new FileInputStream(new File(lokasiFile));
            HSSFWorkbook workbook = new HSSFWorkbook(fis);
            //org.apache.poi.ss.usermodel.Workbook workbook = WorkbookFactory.create(fis);
            if(tipe==1){
                btSave.setEnabled(false);
                int no = workbook.getNumberOfSheets();
                jComboBoxSheet.removeAllItems();
                for (int a = 0; a < no; a++) {
                    jComboBoxSheet.addItem(workbook.getSheetName(a));
                }
                jComboBoxSheet.setSelectedIndex(0);
                fis.close();
                return;
            }
            HSSFSheet sheet = workbook.getSheetAt(jComboBoxSheet.getSelectedIndex());
            //org.apache.poi.ss.usermodel.Sheet sheet = workbook.getSheetAt(jComboBoxSheet.getSelectedIndex());
            HSSFRow row;
            HSSFCell cell;

            int rows; // No of rows
            rows = sheet.getPhysicalNumberOfRows();

            int cols = 0; // No of columns
            int tmp = 0;
            String kodeProd="";

            // This trick ensures that we get the data properly even if it doesn't start from first few rows
//            for(int i = 0; i < 10 || i < rows; i++) {
//                row = sheet.getRow(i);
//                if(row != null) {
//                    tmp = sheet.getRow(i).getPhysicalNumberOfCells();
//                    if(tmp > cols) cols = tmp;
//                }
//            }
            
            jProgressBar1.setMinimum(0);
            jProgressBar1.setMaximum(rows);
            jProgressBar1.setValue(0);
            jProgressBar1.setForeground(Color.BLUE);
            
            for(int r = 1; r < rows; r++) {
                row = (HSSFRow) sheet.getRow(r);
                if(row != null) {
                    if(row.getCell(0) != null) {
                        String lembur="";
                        String telat="";
                        String pcp="";
                        
                        FormulaEvaluator evaluator = workbook.getCreationHelper().createFormulaEvaluator();

                        if(row.getCell(8).getCellType() == Cell.CELL_TYPE_NUMERIC) {
                            int telatt = (int) row.getCell(8).getNumericCellValue();
                            if((telatt/60)>0) {
                                int menit = telatt - ((telatt/60)*60);
                                telat = rfa.NumberBukti(MenuAdmin.keyssDef, 2,(telatt/60))+":"+rfa.NumberBukti(MenuAdmin.keyssDef, 2,menit);
                            } else if(telatt>0 && telatt<60){
                                telat = "00:"+rfa.NumberBukti(MenuAdmin.keyssDef, 2,telatt);
                            }
                        } else {
                            telat = row.getCell(8).getStringCellValue();
                        }
                        
                        if(row.getCell(9).getCellType() == Cell.CELL_TYPE_NUMERIC) {
                            int telatt = (int) row.getCell(9).getNumericCellValue();
                            if((telatt/60)>0) {
                                int menit = telatt - ((telatt/60)*60);
                                System.out.println(telatt+" - "+(telatt/60)+"-"+menit);
                                pcp = rfa.NumberBukti(MenuAdmin.keyssDef, 2,(telatt/60))+":"+rfa.NumberBukti(MenuAdmin.keyssDef, 2,menit);
                            } else if(telatt>0 && telatt<60){
                                pcp = "00:"+rfa.NumberBukti(MenuAdmin.keyssDef, 2,telatt);
                            }
                        } else {
                            pcp = row.getCell(9).getStringCellValue();
                        }
                        
                        if(row.getCell(10).getCellType() == Cell.CELL_TYPE_NUMERIC) {
                            int telatt = (int) row.getCell(10).getNumericCellValue();
                            if((telatt/60)>0) {
                                int menit = telatt - ((telatt/60)*60);
                                lembur = rfa.NumberBukti(MenuAdmin.keyssDef, 2,(telatt/60))+":"+rfa.NumberBukti(MenuAdmin.keyssDef, 2,menit);
                            } else if(telatt>0 && telatt<60){
                                lembur = "00:"+rfa.NumberBukti(MenuAdmin.keyssDef, 2,telatt);
                            }
                        } else {
                            lembur = row.getCell(10).getStringCellValue();
                        }
                        
                        
//                        if(row.getCell(10).getStringCellValue().length()>5) {
//                            lembur = row.getCell(10).getStringCellValue().substring(0, 5);
//                            String[] lmb = lembur.split(":");
//                            String setlmb = rfa.NumberBukti(keyssDef,2,Integer.valueOf(lmb[0]))+":"+rfa.NumberBukti(keyssDef,2,Integer.valueOf(lmb[1]));
//                            lembur = setlmb;
//                            
//                        }
                        
                        String tgl = row.getCell(2).getStringCellValue();
                        //System.out.println(tgl);
                        if(tgl.isEmpty()) break;
                        
                        String splitdata = "/";
                        if(tgl.indexOf("-")>0) {
                            splitdata ="-";
                        }
                        
                        
                        String[] parts = tgl.split(splitdata);
                        String settgl = rfa.NumberBukti(keyssDef,2,Integer.valueOf(parts[0]))+"/"+rfa.NumberBukti(keyssDef,2,Integer.valueOf(parts[1]))+"/"+parts[2];
                        
                        
                        
                        tabel.addRow(new Object[]{
                            row.getCell(0).getStringCellValue(), 
                            row.getCell(1).getStringCellValue(), 
                            settgl,
                            row.getCell(3).getStringCellValue(),
                            row.getCell(4).getStringCellValue(),
                            row.getCell(5).getStringCellValue(),
                            row.getCell(6).getStringCellValue(),
                            row.getCell(7).getStringCellValue(),
                            telat,
                            pcp,
                            lembur,
                            tcode.getText(),
                        });
                        
                        brs++;
                        
                        jProgressBar1.setValue(brs);
                        Rectangle progressRect = jProgressBar1.getBounds();
                        progressRect.x = 0;
                        progressRect.y = 0;
                        jProgressBar1.paintImmediately(progressRect);
                    }
                }
            }
            brs--;
            if(brs<0){brs=0;}
            jLabel3.setText("Total Data : " +brs);
            btSave.setEnabled(true);
        } catch(Exception ioe) {
            ioe.printStackTrace();
        }
    }
    
    private void save(){
        jProgressBar1.setMinimum(0);
        jProgressBar1.setMaximum(jTable2.getRowCount());
        jProgressBar1.setValue(0);
        jProgressBar1.setForeground(Color.BLUE);
        for(int i=0; i<jTable2.getRowCount();i++){
            String d1="";
            if(list1.getSelectedIndex() == 0) d1 = "dd";
            else if(list1.getSelectedIndex() == 1) d1 = "MM";
            else if(list1.getSelectedIndex() == 2) d1 = "yyyy";
            String d2="";
            if(list2.getSelectedIndex() == 0) d2 = "dd";
            else if(list2.getSelectedIndex() == 1) d2 = "MM";
            else if(list2.getSelectedIndex() == 2) d2 = "yyyy";
            String d3="";
            if(list3.getSelectedIndex() == 0) d3 = "dd";
            else if(list3.getSelectedIndex() == 1) d3 = "MM";
            else if(list3.getSelectedIndex() == 2) d3 = "yyyy";
            
            sql="delete from absensi where ccode='"+jTable2.getValueAt(i,11).toString()+"' and nik='"+jTable2.getValueAt(i, 0).toString()+"' and tanggal='"+rfa.DateString(keyssDef,rfa.StringtoDate(keyssDef,jTable2.getValueAt(i, 2).toString(), d1+"/"+d2+"/"+d3),"yyyy-MM-dd")+"' ";
            con.queryUpdate(sql);
            
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("nik",jTable2.getValueAt(i, 0).toString());
            listObj.put("Name",jTable2.getValueAt(i, 1).toString().toUpperCase().replaceAll("'", "''"));
            
            
            
            listObj.put("Tanggal",rfa.DateString(keyssDef,rfa.StringtoDate(keyssDef,jTable2.getValueAt(i, 2).toString(), d1+"/"+d2+"/"+d3),"yyyy-MM-dd"));
            listObj.put("Timetable",jTable2.getValueAt(i, 3).toString());
            
            listObj.put("OnDuty","");
            listObj.put("OffDuty","");
            listObj.put("ClockIn","");
            listObj.put("ClockOut","");
            
            System.out.println(jTable2.getValueAt(i, 7).toString().length());
            if(jTable2.getValueAt(i, 4) != null && jTable2.getValueAt(i, 4).toString().length()>3) listObj.put("OnDuty",jTable2.getValueAt(i, 4).toString().substring(0, 5));
            if(jTable2.getValueAt(i, 5) != null && jTable2.getValueAt(i, 5).toString().length()>3) listObj.put("OffDuty",jTable2.getValueAt(i, 5).toString().substring(0, 5));
            if(jTable2.getValueAt(i, 6) != null && jTable2.getValueAt(i, 6).toString().length()>3) listObj.put("ClockIn",jTable2.getValueAt(i, 6).toString().substring(0, 5));
            if(jTable2.getValueAt(i, 7) != null && jTable2.getValueAt(i, 7).toString().length()>3) listObj.put("ClockOut",jTable2.getValueAt(i, 7).toString().substring(0, 5));
            
            listObj.put("Late",jTable2.getValueAt(i, 8).toString());
            listObj.put("Early",jTable2.getValueAt(i, 9).toString());
            listObj.put("OT_TIme",jTable2.getValueAt(i, 10).toString());
            listObj.put("ccode",jTable2.getValueAt(i, 11).toString());
            listObj.put("cusername",Login.user);
            sql=con.insertValue("absensi", listObj);
            
            con.queryUpdate(sql);
            
            jProgressBar1.setValue(i+1);
            Rectangle progressRect = jProgressBar1.getBounds();
            progressRect.x = 0;
            progressRect.y = 0;
            jProgressBar1.paintImmediately(progressRect);
       }
        JOptionPane.showMessageDialog(this,"Absensi Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
        rfa.empty(keyssDef,TextFolder);
        jComboBoxSheet.removeAllItems();
    }
    
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {
        java.awt.GridBagConstraints gridBagConstraints;

        jScrollPane3 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        jDate1 = new com.toedter.calendar.JDateChooser();
        jButton7 = new javax.swing.JButton();
        tcode = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        jButton21 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jPanel1 = new javax.swing.JPanel();
        jPanel3 = new javax.swing.JPanel();
        jLabel5 = new javax.swing.JLabel();
        TextFolder = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        jComboBoxSheet = new javax.swing.JComboBox();
        jButtonFolder = new javax.swing.JButton();
        jButtonProcess = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        jLabel8 = new javax.swing.JLabel();
        list3 = new javax.swing.JComboBox();
        list1 = new javax.swing.JComboBox();
        list2 = new javax.swing.JComboBox();
        jLabel10 = new javax.swing.JLabel();
        jLabel11 = new javax.swing.JLabel();
        jProgressBar1 = new javax.swing.JProgressBar();
        jLabel3 = new javax.swing.JLabel();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();

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
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Upload Absensi ");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 20, -1, -1));

        jPanel4.setLayout(new java.awt.GridBagLayout());

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 30, 150, -1));

        jLabel13.setText("Periode ");
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
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 30, 150, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 30, 90, -1));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel6.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 60, 110, -1));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Kode Gudang");
        jPanel6.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, -1));

        jButton21.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton21.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton21ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton21, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 60, 40, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel6.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 60, 260, -1));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.gridwidth = 2;
        gridBagConstraints.ipadx = 620;
        gridBagConstraints.ipady = 18;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        gridBagConstraints.insets = new java.awt.Insets(11, 0, 0, 15);
        jPanel4.add(jPanel6, gridBagConstraints);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());
        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 1;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        jPanel4.add(jPanel1, gridBagConstraints);

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel5.setText("/");
        jPanel3.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(710, 10, 10, 21));

        TextFolder.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        TextFolder.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                TextFolderKeyPressed(evt);
            }
        });
        jPanel3.add(TextFolder, new org.netbeans.lib.awtextra.AbsoluteConstraints(80, 10, 280, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Sheet ");
        jPanel3.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 62, 21));

        jPanel3.add(jComboBoxSheet, new org.netbeans.lib.awtextra.AbsoluteConstraints(80, 40, 280, -1));

        jButtonFolder.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jButtonFolder.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/folder-15.png"))); // NOI18N
        jButtonFolder.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButtonFolderActionPerformed(evt);
            }
        });
        jPanel3.add(jButtonFolder, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 10, 30, -1));

        jButtonProcess.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/upload-15.png"))); // NOI18N
        jButtonProcess.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButtonProcessActionPerformed(evt);
            }
        });
        jPanel3.add(jButtonProcess, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 40, 30, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel3.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(430, 40, 91, -1));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("File Excel");
        jPanel3.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 62, 21));

        list3.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Hari", "Bulan", "Tahun" }));
        jPanel3.add(list3, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 10, -1, -1));

        list1.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Hari", "Bulan", "Tahun" }));
        jPanel3.add(list1, new org.netbeans.lib.awtextra.AbsoluteConstraints(570, 10, -1, -1));

        list2.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Hari", "Bulan", "Tahun" }));
        jPanel3.add(list2, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 10, -1, -1));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Format Tanggal");
        jPanel3.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(430, 10, 120, 21));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel11.setText("/");
        jPanel3.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(630, 10, 10, 21));
        jPanel3.add(jProgressBar1, new org.netbeans.lib.awtextra.AbsoluteConstraints(570, 40, 327, 24));

        jLabel3.setText("jLabel3");
        jPanel3.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(910, 40, -1, -1));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 3;
        gridBagConstraints.gridwidth = 2;
        gridBagConstraints.ipadx = 293;
        gridBagConstraints.ipady = 8;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        gridBagConstraints.insets = new java.awt.Insets(10, 0, 18, 15);
        jPanel4.add(jPanel3, gridBagConstraints);

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "NIK", "Nama Karyawan", "Tanggal", "TimeTable", "OnDuty", "OffDuty", "ClockIn", "ClockOut", "Late", "Early", "OT Time", "Gudang", "Status"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false
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
            jTable2.getColumnModel().getColumn(4).setMinWidth(90);
            jTable2.getColumnModel().getColumn(4).setMaxWidth(90);
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
            jTable2.getColumnModel().getColumn(12).setMinWidth(90);
            jTable2.getColumnModel().getColumn(12).setMaxWidth(90);
        }

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 1;
        gridBagConstraints.gridwidth = 2;
        gridBagConstraints.gridheight = 2;
        gridBagConstraints.fill = java.awt.GridBagConstraints.BOTH;
        gridBagConstraints.ipadx = 1167;
        gridBagConstraints.ipady = 203;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        gridBagConstraints.weightx = 1.0;
        gridBagConstraints.weighty = 1.0;
        gridBagConstraints.insets = new java.awt.Insets(8, 0, 0, 15);
        jPanel4.add(jScrollPane2, gridBagConstraints);

        jTabbedPane1.addTab("Upload", jPanel4);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jTabbedPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 1257, Short.MAX_VALUE)
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 479, Short.MAX_VALUE)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

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

    private void jButtonProcessActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButtonProcessActionPerformed
        if(TextFolder.getText().trim().isEmpty()){return;}
        this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
        load_excel(TextFolder.getText().trim(),2);
        this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
    }//GEN-LAST:event_jButtonProcessActionPerformed

    private void jButtonFolderActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButtonFolderActionPerformed
        int retrival = chooser2.showSaveDialog(null);

        TextFolder.setText("");
        if (retrival == JFileChooser.APPROVE_OPTION) {
            this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
            TextFolder.setText(chooser2.getSelectedFile().toString());
            load_excel(TextFolder.getText().trim(),1);
            this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
        }
    }//GEN-LAST:event_jButtonFolderActionPerformed

    private void TextFolderKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_TextFolderKeyPressed

    }//GEN-LAST:event_TextFolderKeyPressed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 4).toString();
            cnbkt_print = cnbkt;
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jButton21ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton21ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton21ActionPerformed

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

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


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField TextFolder;
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton21;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButtonFolder;
    private javax.swing.JButton jButtonProcess;
    private javax.swing.JComboBox jComboBoxSheet;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JProgressBar jProgressBar1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jtable_print;
    private javax.swing.JComboBox list1;
    private javax.swing.JComboBox list2;
    private javax.swing.JComboBox list3;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    // End of variables declaration//GEN-END:variables
}
