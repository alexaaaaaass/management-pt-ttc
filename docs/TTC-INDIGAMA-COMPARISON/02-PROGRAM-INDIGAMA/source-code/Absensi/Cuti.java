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
public class Cuti extends javax.swing.JPanel {

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
    ArrayList<String> idCTK;
    public Cuti(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        idCTK = new ArrayList<>();
        push_button();
        loadctk(); 
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
        rfa.empty(keyssDef, tket,tkaryawankode,tkaryawannama,tjam,tmenit);
        btDelete.setVisible(false);
        btSave.setText("Save");
        id=""; 
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
    }
    private void loadctk(){
        sql="select * from MCuti where cidc not in ('CK019','CK020','CK021')";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                listCuti.addItem(rs.getString("cdesc"));
                idCTK.add(rs.getString("cidc"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(Cuti.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadData(){
        String dtran1=rfa.DateString(keyssDef, jDate1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, jDate2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        sql="select a.*,b.cname as karyawan,c.ctglcuti,d.cdesc from cuti_ambil a join MKaryawan b on a.cidk=b.nik\n" +
            "join cuti_ambil_dt c on a.nidca=c.nidca\n" +
            "join MCuti d on a.cidc=d.cidc where a.cjenis = 'CUTI KHUSUS'  \n ";
        if(!tkaryawan2.getText().isEmpty()) sql+=" and b.cname like '%"+tkaryawan2.getText()+"%' ";
        sql+=" order by a.nidca desc,c.ctglcuti";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("nidca"),
                    rs.getString("cidk"),
                    rs.getString("karyawan"),
                    rfa.DateString(keyssDef,rs.getDate("ctglcuti"),"dd/MM/yyyy"),
                    rs.getString("cdesc") ,
                    rs.getString("cdoc") ,
                    rs.getString("cjamawal") ,
                    rs.getString("cjamakhir") ,
                    rs.getString("cket"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(Cuti.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadEdit(){ 
        btSave.setText("Update");
        sql="select a.*,b.cname as karyawan,c.ctglcuti,d.cdesc from cuti_ambil a join MKaryawan b on a.cidk=b.nik\n" +
            "join cuti_ambil_dt c on a.nidca=c.nidca\n" +
            "join MCuti d on a.cidc=d.cidc where a.nidca='"+id+"' \n ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                
                jDateLembur.setDate(rs.getDate("ctglcuti"));
                tkaryawankode.setText(rs.getString("cidk"));
                tkaryawannama.setText(rs.getString("karyawan"));
                tket.setText(rs.getString("cket")); 
                listCuti.setSelectedItem(rs.getString("cdesc"));
                
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(Cuti.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
       
        sql="select a.*,b.cname as karyawan,c.ctglcuti,d.cdesc from cuti_ambil a join MKaryawan b on a.cidk=b.nik\n" +
            "join cuti_ambil_dt c on a.nidca=c.nidca\n" +
            "join MCuti d on a.cidc=d.cidc where a.nidca='"+id+"' \n ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                
                model.addRow(new Object[]{ 
                    rfa.DateString(keyssDef,rs.getDate("ctglcuti"),"dd MMMM yyyy"), 
                    rfa.DateString(keyssDef,rs.getDate("ctglcuti"),"yyyy-MM-dd"), 
                    
                });
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(Cuti.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        
        btDelete.setVisible(true);
    }
    
    private void save(int statuss){
        HashMap<String,String> listObj = new HashMap<String,String>();    
        listObj.put("cidk",tkaryawankode.getText());
        listObj.put("cjenis","CUTI KHUSUS");
        listObj.put("cidc",idCTK.get(listCuti.getSelectedIndex()));
        listObj.put("cket",tket.getText());
        listObj.put("nstatus","1");
        listObj.put("ctglterima",rfa.DateString(keyssDef,new Date(),"yyyy-MM-dd")); 
        
        if(jCheckBox1.isSelected())  listObj.put("cdoc","YA");
        else   listObj.put("cdoc","");
        if(statuss == 1) {
            sql=con.insertValue("Cuti_Ambil", listObj);
        } else {
            sql=con.updateValue("Cuti_Ambil", listObj);
            sql+=" where nidca='"+id+"' ";
        }
        
        if(con.queryUpdate(sql)>0) { 
            if(statuss == 2) {
                con.queryUpdate("delete from Cuti_Ambil_dt where nidca='"+id+"' "); 
            }
            int nidca = 0;
            sql="select max(nidca) as nidca from cuti_ambil where cidk = '"+tkaryawankode.getText()+"' and cjenis = 'CUTI KHUSUS' and cidc = '"+idCTK.get(listCuti.getSelectedIndex())+"' and cket='"+tket.getText()+"'";
            rs = con.queryResult(sql);
            try {
                while(rs.next()) {
                    nidca = rs.getInt("nidca");
                }
            } catch (SQLException ex) {
                Logger.getLogger(Cuti.class.getName()).log(Level.SEVERE, null, ex);
            }

            for(int i=0; i<jTable1.getRowCount();i++){
                HashMap<String,String> listObjd= new HashMap<String,String>();    
                listObjd.put("nidca",rfa.formatDecimal3.format(nidca));
                listObjd.put("ctglcuti",jTable1.getValueAt(i, 1).toString());
                listObjd.put("cthn",rfa.DateString(keyssDef,jDateLembur.getDate(),"yyyy"));
                listObjd.put("nharidt","1");
                sql=con.insertValue("Cuti_Ambil_dt", listObjd);
                con.queryUpdate(sql); 
            }
            

            clear();
            loadData();
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
        }
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
        tkaryawan2 = new javax.swing.JTextField();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jPanel1 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel13 = new javax.swing.JLabel();
        tkaryawankode = new javax.swing.JTextField();
        jButton8 = new javax.swing.JButton();
        tkaryawannama = new javax.swing.JTextField();
        jDateLembur = new com.toedter.calendar.JDateChooser();
        jLabel16 = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel27 = new javax.swing.JLabel();
        listCuti = new javax.swing.JComboBox();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jButton1 = new javax.swing.JButton();
        jLabel17 = new javax.swing.JLabel();
        jLabel20 = new javax.swing.JLabel();
        jCheckBox1 = new javax.swing.JCheckBox();

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 30, 150, -1));

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
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 60, 90, -1));

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

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "NIK", "Nama Karyawan", "Tanggal", "Ijin", "Lampiran/Surat Dokter", "Jam Mulai", "Jam Selesai", "Keterangan Lembur"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false
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
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(1).setMinWidth(80);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(80);
            jTable2.getColumnModel().getColumn(3).setMinWidth(90);
            jTable2.getColumnModel().getColumn(3).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(6).setMinWidth(90);
            jTable2.getColumnModel().getColumn(6).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(7).setMinWidth(90);
            jTable2.getColumnModel().getColumn(7).setMaxWidth(90);
        }

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setAutoscrolls(true);
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setText("Karyawan");
        jLayeredPane1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, -1, 20));

        tkaryawankode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkaryawankodeFocusLost(evt);
            }
        });
        tkaryawankode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkaryawankodeKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tkaryawankode, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 20, 90, -1));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(200, 20, 30, 20));

        tkaryawannama.setEditable(false);
        tkaryawannama.setBackground(new java.awt.Color(153, 153, 255));
        tkaryawannama.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tkaryawannama, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 20, 210, 20));

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
        jLayeredPane1.add(jDateLembur, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 150, 120, -1));

        jLabel16.setText("Double click to delete");
        jLayeredPane1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 420, 300, 20));

        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        jLayeredPane1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 110, 260, -1));

        jLabel19.setText("Lampiran");
        jLayeredPane1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, -1, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 470, 90, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 470, 100, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(320, 470, -1, -1));

        jLabel27.setText("Keterangan");
        jLayeredPane1.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, -1, 20));

        jLayeredPane1.add(listCuti, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 50, 260, -1));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null},
                {null, null},
                {null, null},
                {null, null}
            },
            new String [] {
                "Tanggal", "Title 2"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(1).setMinWidth(0);
            jTable1.getColumnModel().getColumn(1).setMaxWidth(0);
        }

        jLayeredPane1.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 180, 260, 240));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        jButton1.setText("Add");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 150, -1, -1));

        jLabel17.setText("Tanggal");
        jLayeredPane1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 150, -1, 20));

        jLabel20.setText("Jenis Izin");
        jLayeredPane1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, -1, 20));

        jCheckBox1.setText("Ya   (Lampiran/Surat Dokter)");
        jLayeredPane1.add(jCheckBox1, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 80, -1, -1));

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

        jTabbedPane1.addTab("Cuti", jPanel4);

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

    private void tkaryawankodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawankodeFocusLost
        sql = "select * from mkaryawan where nik = '"+tkaryawankode.getText()+"'  ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tkaryawannama.setText(rs.getString("cname"));

            }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tkaryawankodeFocusLost

    private void tkaryawankodeKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawankodeKeyPressed
        rfa.EnterNextFocus(evt, tket);
    }//GEN-LAST:event_tkaryawankodeKeyPressed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawankode.setText(a.Kode);
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
                sql="delete from cuti_ambil where nidca='"+id+"' ";
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
                    save(1);
                }
            } else {
                if(con.akses(akses, rfa.UPDATE)) {
                    save(2);
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void tkaryawan2FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawan2FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawan2FocusLost

    private void tkaryawan2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawan2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawan2KeyPressed

    private void tkaryawan2CaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tkaryawan2CaretUpdate
        loadData();
    }//GEN-LAST:event_tkaryawan2CaretUpdate

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
       clear();
    }//GEN-LAST:event_btCancelActionPerformed

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        String cek = rfa.DateString(keyssDef,jDateLembur.getDate(),"yyyy-MM-dd");
        for(int i=0; i<jTable1.getRowCount();i++){
            if(cek.equals(jTable1.getValueAt(i, 1))) {
                return;
            }
        }
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.addRow(new Object[]{
                    rfa.DateString(keyssDef,jDateLembur.getDate(),"dd MMMM yyyy"),
                    rfa.DateString(keyssDef,jDateLembur.getDate(),"yyyy-MM-dd"),
                    
                });
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount()==2) {
            try {
                DefaultTableModel model = (DefaultTableModel) jTable1.getModel();

                int baris = jTable1.getSelectedRow();
                model.removeRow(baris); 
            } catch (Exception e) {}
        }
    }//GEN-LAST:event_jTable1MouseClicked


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JCheckBox jCheckBox1;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateLembur;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jtable_print;
    private javax.swing.JComboBox listCuti;
    private javax.swing.JTextField tjam;
    private javax.swing.JTextField tkaryawan2;
    private javax.swing.JTextField tkaryawankode;
    private javax.swing.JTextField tkaryawannama;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tmenit;
    // End of variables declaration//GEN-END:variables
}
