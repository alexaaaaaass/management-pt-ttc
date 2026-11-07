/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Report;

import DialogPublicTP.DialogGudang;
import static Main.DefaultProgram.deffProgram;
import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
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
import javax.swing.table.DefaultTableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import org.apache.poi.hssf.usermodel.HSSFCellStyle;
import org.apache.poi.hssf.usermodel.HSSFFont;
import org.apache.poi.hssf.usermodel.HSSFSheet;
import org.apache.poi.hssf.usermodel.HSSFWorkbook;
import org.apache.poi.hssf.util.HSSFColor;
import org.apache.poi.ss.usermodel.Cell;
import org.apache.poi.ss.usermodel.Row;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class RPayroll extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1",akses="REPORT PRODUCTION";
    ArrayList<String> deleteRow;
    ArrayList<String> idmesin;
    public RPayroll(String mode) {
        initComponents();
  
        deff(); 
        push_button();
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

    private void deff(){ 
        
        tcode.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  
            tcode3.setText(rs.getString("cname"));
        } } catch (SQLException ex) { ex.printStackTrace();   }
    }
     
     
    private void exportExcel(JTable table,ArrayList kolom,String namafile,String dawal,String dakhir){
        final JFileChooser chooser = new JFileChooser();
        chooser.setDialogTitle("Specify a Folder to Save");
        chooser.setFileSelectionMode(JFileChooser.DIRECTORIES_ONLY);
        chooser.setCurrentDirectory(new File("C:\\Users\\Administrator\\Documents"));
        
        HSSFWorkbook workbook = new HSSFWorkbook();
        HSSFSheet sheet = workbook.createSheet("Sheet 1");

        HSSFFont font = workbook.createFont();
        HSSFCellStyle styleHeader = workbook.createCellStyle();
        font.setBoldweight(HSSFFont.BOLDWEIGHT_BOLD);
        styleHeader.setAlignment(styleHeader.ALIGN_CENTER);            
        styleHeader.setFillForegroundColor(HSSFColor.LIME.index);
        styleHeader.setFillPattern(HSSFCellStyle.SOLID_FOREGROUND);
        styleHeader.setBorderBottom(HSSFCellStyle.BORDER_THIN);
        styleHeader.setBorderTop(HSSFCellStyle.BORDER_THIN);
        styleHeader.setBorderLeft(HSSFCellStyle.BORDER_THIN);
        styleHeader.setBorderRight(HSSFCellStyle.BORDER_THIN);
        styleHeader.setFont(font);

        HSSFCellStyle styleHeader2 = workbook.createCellStyle();
        styleHeader2.setAlignment(styleHeader2.ALIGN_LEFT);
        styleHeader2.setFont(font);

        HSSFCellStyle styleBorder = workbook.createCellStyle();
        styleBorder.setBorderBottom(HSSFCellStyle.BORDER_THIN);
        styleBorder.setBorderTop(HSSFCellStyle.BORDER_THIN);
        styleBorder.setBorderLeft(HSSFCellStyle.BORDER_THIN);
        styleBorder.setBorderRight(HSSFCellStyle.BORDER_THIN);
        
        int retrival = chooser.showSaveDialog(null);
        if (retrival == JFileChooser.APPROVE_OPTION) {
            try {
                Row row = sheet.createRow(0);
                Cell cell = row.createCell(0); cell.setCellValue((String)namafile); cell.setCellStyle(styleHeader2);
                row = sheet.createRow(1);
                cell = row.createCell(0); cell.setCellValue((String)"Periode "+dawal+" s/d "+dakhir); cell.setCellStyle(styleHeader2);

                row = sheet.createRow(3);
                for(int i=0; i<kolom.size();i++){
                    cell = row.createCell(i); cell.setCellValue((String)kolom.get(i)); cell.setCellStyle(styleHeader);
                }

                for (int i = 0; i < table.getRowCount(); i++) {
                    row = sheet.createRow(i+4);
                    for (int j = 0; j < kolom.size(); j++) {
                        cell = row.createCell(j);
                        if(table.getValueAt(i, j) instanceof String)
                            cell.setCellValue((String)table.getValueAt(i, j));
                        else if(table.getValueAt(i, j) instanceof Integer)
                            cell.setCellValue((Integer)table.getValueAt(i, j));
                        else if(table.getValueAt(i, j) instanceof Double)
                            cell.setCellValue((Double)table.getValueAt(i, j));
                        cell.setCellStyle(styleBorder);
                    }
                }

                
            
                String f = chooser.getSelectedFile() + "\\Export "+namafile+" "+dawal+" sd "+dakhir+".xls";
                FileOutputStream excel= new FileOutputStream(new File(f));
                workbook.write(excel);
                excel.close();
                JOptionPane.showMessageDialog(this, "File Excel Disimpan Di "+f, "File Created", JOptionPane.INFORMATION_MESSAGE);
            } catch (FileNotFoundException ex) {
                Logger.getLogger(RPayroll.class.getName()).log(Level.SEVERE, null, ex);
            } catch (IOException ex) {
                Logger.getLogger(RPayroll.class.getName()).log(Level.SEVERE, null, ex);
            }
                
         
        }
    }
 
    private void karyawan(int ii){ 
        DefaultTableModel model = (DefaultTableModel) jTablekaryawan.getModel();
        rfa.clear_table(keyssDef, jTablekaryawan);

         
        sql="select a.cperiode,b.* from TranPayroll a \n" +
            "join TranPayrollD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt where 1=1 \n";
         
        if(!jComboBox1.getSelectedItem().toString().equals("All")) sql+=" and a.cperiode='"+jComboBox1.getSelectedItem().toString()+ttahun.getText()+"' "; 
        
        if(ii == 0) sql+=" order by a.ccode,a.cperiode,b.name";
        if(ii == 1) sql+=" order by a.ccode,b.name,a.cperiode";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cperiode"),
                    rs.getString("cidk"),
                    rs.getString("name"),
                    rs.getString("cjabatan"),
                    rs.getDouble("ok"),
                    rs.getDouble("a"),
                    rs.getDouble("telat"),
                    rs.getDouble("pcp"),
                    rs.getDouble("lembur"),
                    rs.getDouble("cuti"),
                    rs.getDouble("sakit"),
                    rs.getDouble("gp"),
                    rs.getDouble("tunjangan"),
                    rs.getDouble("tunjangan_komp"),
                    rs.getDouble("tunjangan_lain"),
                    rs.getDouble("pot_bpjskerja"),
                    rs.getDouble("pot_a"),
                    rs.getDouble("pot_telat"),
                    rs.getDouble("pot_pcp"),
                    rs.getDouble("pot_sakit"),
                    rs.getDouble("pot_pinjaman"),
                    rs.getDouble("pot_pph21"),
                    rs.getDouble("pot_lain"),
                    rs.getDouble("gp_lembur"),
                    rs.getDouble("bonus"),
                    rs.getDouble("total"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RPayroll.class.getName()).log(Level.SEVERE, null, ex);
        }

        if(model.getRowCount() <= 0) {
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTablekaryawan.getModel());
            String treport = "report/RPayrollKaryawan.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", "");
            param.put("dtran2", "");
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
     
    
    /**
     * This method is called from within the constructor to initialize the form.
     * WARNING: Do NOT modify this code. The content of this method is always
     * regenerated by the Form Editor.
     */
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jScrollPane4karyawan = new javax.swing.JScrollPane();
        jTablekaryawan = new javax.swing.JTable();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jButton30 = new javax.swing.JButton();
        jButton34 = new javax.swing.JButton();
        jPanel8 = new javax.swing.JPanel();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jPanel6 = new javax.swing.JPanel();
        jLabel7 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        tcode3 = new javax.swing.JTextField();
        jButton18 = new javax.swing.JButton();
        jLabel16 = new javax.swing.JLabel();
        jComboBox1 = new javax.swing.JComboBox();
        ttahun = new javax.swing.JTextField();

        jTablekaryawan.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "periode", "cidk", "name", "cjabatan", "ok", "a", "telat", "pcp", "lembur", "cuti", "sakit", "gp", "tunj", "tunjkomp", "tunjlain", "potbpjs", "pota", "pottelat", "potpcp", "potsakit", "potpinjaman", "potpph21", "potlain", "gplembur", "bonus", "total"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                true, true, true, true, true, false, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane4karyawan.setViewportView(jTablekaryawan);
        if (jTablekaryawan.getColumnModel().getColumnCount() > 0) {
            jTablekaryawan.getColumnModel().getColumn(0).setResizable(false);
            jTablekaryawan.getColumnModel().getColumn(5).setResizable(false);
            jTablekaryawan.getColumnModel().getColumn(25).setResizable(false);
        }

        setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton30.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton30.setText("Berdasarkan Nama");
        jButton30.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton30ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton30, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, 210, 30));

        jButton34.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton34.setText("Berdasarkan Periode");
        jButton34.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton34ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton34, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 210, 30));

        jTabbedPane1.addTab("Payroll Karyawan", jPanel4);

        jPanel8.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());
        jTabbedPane1.addTab("Payroll Borongan", jPanel8);

        add(jTabbedPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 160, 1210, 230));

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jPanel3.setBackground(new java.awt.Color(153, 204, 255));

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 500, Short.MAX_VALUE)
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGap(0, 5, Short.MAX_VALUE)
        );

        jPanel2.add(jPanel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 500, 5));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Payroll Report");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        add(jPanel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 11, 546, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Gudang");
        jPanel6.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 72, 20));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel6.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 30, 90, -1));

        tcode3.setEditable(false);
        tcode3.setBackground(new java.awt.Color(153, 153, 255));
        tcode3.setForeground(new java.awt.Color(255, 255, 255));
        jPanel6.add(tcode3, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, 230, -1));

        jButton18.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton18.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton18ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton18, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 30, 30, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Bulan");
        jPanel6.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 100, 20));

        jComboBox1.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "All", "Januari", "Februari", "Maret", "April", "Mei ", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember" }));
        jPanel6.add(jComboBox1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 60, 140, -1));

        ttahun.setText("2024");
        jPanel6.add(ttahun, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 60, 60, -1));

        add(jPanel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 62, 1210, 90));
    }// </editor-fold>//GEN-END:initComponents

    private void jButton18ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton18ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode3.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton18ActionPerformed

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton30ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton30ActionPerformed
        karyawan(1);
    }//GEN-LAST:event_jButton30ActionPerformed

    private void jButton34ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton34ActionPerformed
        karyawan(0);
    }//GEN-LAST:event_jButton34ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton jButton18;
    private javax.swing.JButton jButton30;
    private javax.swing.JButton jButton34;
    private javax.swing.JComboBox jComboBox1;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JScrollPane jScrollPane4karyawan;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTablekaryawan;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode3;
    private javax.swing.JTextField ttahun;
    // End of variables declaration//GEN-END:variables
}
