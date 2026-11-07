/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Report;

import Accounting.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogOrderCustomer;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogReqOrder;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Production.*;
import RPurchasing.*;
import ReportAccounting.*;
import ReportProduction.*;
import Tools.ConnectDB;
import java.awt.Dimension;
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
import javax.swing.JFrame;
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
import net.sf.jasperreports.view.JRViewer;
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
public class RMarketingFee extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    String akses="MARKETING FEE";
    String ccode="";
    
    ArrayList<String> idSkat;
    public RMarketingFee(String mode) {
        initComponents();
  
        deff();
        push_button();
        loadSkat();
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
        DateReq1.setDate(new Date());
        DateReq2.setDate(new Date());
        
        tcode.setText(deffProgram.get("ccode"));
        ccode= (deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        
    }
    private void loadSkat(){
        idSkat = new ArrayList<>();
        idSkat.clear();
        cbSkat.removeAll();
        cbSkat.removeAllItems();
        idSkat.add("");
        cbSkat.addItem("Pilih Skat");
        
        rs = con.queryResult("Select * from mskat where ccode='"+tcode.getText()+"' ");
        try {
            while(rs.next()){
                idSkat.add(rs.getString("id"));
                cbSkat.addItem(rs.getString("cname"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        cbSkat.setSelectedIndex(0);
        
    }
    
    private void kartustock(int mode){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        rfa.clear_table(MenuAdmin.keyssDef, tablemutasi);
        DefaultTableModel model = (DefaultTableModel) tablemutasi.getModel();
        
        sql="select a.*,b.cnameProduct from (\n" +
            "	select 0 as urut,ccode,'"+dtran1+"' as dtran,cgroup,cproduct,\n" +
            "	sum(totalpcs) as db,\n" +
            "	0 as cr,0 as mtdb,0 as mtcr,\n" +
            "	'Saldo Awal ' as note,satuan as satuan_in\n" +
            "	from ProductStock\n" +
            "	where ccode='"+tcode.getText()+"'   and tglstock<'"+dtran1+"'\n" +
            "   and cproduct in (select cproduct from ProductStock where ccode='"+tcode.getText()+"' and tglstock>='"+dtran1+"' and tglstock<='"+dtran2+"')"+
            "	group by ccode, cgroup,cproduct,satuan\n" +
            "	union all\n" +
            "	select urutv as urut,ccode,tglstock as dtran,cgroup,cproduct,\n" +
            "	case when totalpcs>0 then totalpcs else 0 end as db,\n" +
            "	case when totalpcs<0 then totalpcs*-1 else 0 end as cr,\n" +
            "	case when totalpcs>0 then totalpcs else 0 end as mtdb,\n" +
            "	case when totalpcs<0 then totalpcs*-1 else 0 end as mtcr,\n" +
            "	note,satuan as satuan_in\n" +
            "	from ProductStock\n" +
            "	where ccode='"+tcode.getText()+"'  \n" +
            "	and (tglstock>='"+dtran1+"' and tglstock<='"+dtran2+"') \n" +
            ")  a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where 1=1 \n" +
            "";
       
        if(!tproduk1.getText().isEmpty()) {
            sql+=" and a.cproduct  like '%"+tproduk1.getText()+"%' ";
        }
        sql+=" order by a.cgroup,b.cproduct,a.dtran,a.urut ";
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        Double sisa=0.0;
        String lastProduk = "";
        try {
            while(rs.next()){
                if(!lastProduk.equals(rs.getString("cproduct"))) {
                    sisa = rs.getDouble("db") - rs.getDouble("cr");
                } else {
                    sisa = sisa + (rs.getDouble("mtdb") - rs.getDouble("mtcr"));
                }
                count++;
                model.addRow(new Object[]{
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    "",
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("db"),
                    rs.getDouble("cr"),
                    rs.getDouble("mtdb"),
                    rs.getDouble("mtcr"),
                    rs.getString("note"),
                    rs.getString("satuan_in"),
                    sisa
                });
                
                lastProduk = rs.getString("cproduct");
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMarketingFee.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Tanggal");
            kolom.add("Status");
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Nama Produk");
            kolom.add("Qty");
            kolom.add("Unit");
            con.exportExcel(tablemutasi,kolom,"Laporan Kartu Stock Produk",dtran1,dtran2);
            return;
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tablemutasi.getModel());
            String treport = "report/RKartuStockProduk.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode1.getText());
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
//            JasperViewer.viewReport(JPrint,true); 
            JRViewer test = new JRViewer(JPrint);
        
            JFrame frame = new JFrame("Report");
            frame.getContentPane().add(test); //new JRViewer(jasperPrint)
            frame.setPreferredSize(new Dimension(900, 700));
            frame.pack();
            frame.setVisible(true);    
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    

    private void stock(int mode){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tablestock.getModel();
        rfa.clear_table(keyssDef, tablestock);
        
        sql="select b.cnameProduct,a.ccode,a.cgroup,a.cproduct,sum(qtyawal) as qtyawal,sum(qtydebet) as qtydebet,sum(qtykredit) as qtykredit,satuan_in from (\n" +
            "	select ccode,cgroup,cproduct,sum(totalpcs) as qtyawal,0 as qtydebet,0 as qtykredit,satuan as satuan_in  from ProductStock \n" +
            "	where ccode = '"+tcode.getText()+"' and tglstock<'"+dtran1+"'\n" +
            "	group by ccode,cgroup,cproduct,satuan \n" +
            "	union all\n" +
            "	select ccode,cgroup,cproduct,0 as qtyawal,\n" +
            "	sum(case when totalpcs > 0 then totalpcs else 0 end) as qtydebet,\n" +
            "	sum(case when totalpcs <=0 then totalpcs*-1 else 0 end) as qtykredit,\n" +
            "	satuan as satuan_in  from ProductStock\n" +
            "	where ccode = '"+tcode.getText()+"' and (tglstock>='"+dtran1+"' and tglstock<='"+dtran2+"')\n" +
            "	group by ccode,cgroup,cproduct,satuan \n" +
            ") a join MProduk b on a.cproduct=b.cproduct\n" +
            " where a.cproduct like '%"+tproduk1.getText()+"%'\n" + 
            "group by  b.cnameProduct,a.ccode,a.cgroup,a.cproduct,a.satuan_in ";
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("qtyawal"),
                    rs.getDouble("qtydebet"),
                    rs.getDouble("qtykredit"),
                    (rs.getDouble("qtyawal")+rs.getDouble("qtydebet"))-rs.getDouble("qtykredit"),rs.getString("satuan_in"),
                    
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMarketingFee.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Nama Produk");
            kolom.add("Qty Awal");
            kolom.add("Qty Masuk");
            kolom.add("Qty Keluar");
            kolom.add("Qty Akhir");
            kolom.add("Unit");
            con.exportExcel(tablestock,kolom,"Laporan Stock Produk",dtran1,dtran2);
            return;
        }
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tablestock.getModel());
            String treport = "report/RStockProduk.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
//            param.put("skat", cbSkat.getSelectedItem().toString());
            param.put("skat", "Summary");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
//            JasperViewer.viewReport(JPrint,false);    //With Preview
            JRViewer test = new JRViewer(JPrint);
        
            JFrame frame = new JFrame("Report");
            frame.getContentPane().add(test); //new JRViewer(jasperPrint)
//            frame.setExtendedState(JFrame.);
            frame.setPreferredSize(new Dimension(900, 700));
            frame.pack();
            frame.setVisible(true);   
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void feemarketing(int mode){
        String dtran1=rfa.DateString(keyssDef, DateReq1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, DateReq2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTablefee.getModel();
        rfa.clear_table(keyssDef, jTablefee);
       
        
        sql="select a.ccode,a.cnbkt,a.dtran,a.cmarketing,c.cname as marketing,\n" +
            "b.cgroup,b.cproduct,d.cnameProduct,b.nqty,b.nunit,isnull(e.harga,0) as harga,isnull(e.harga,0)*b.nqty as total\n" +
            "from PCustomerPO a \n" +
            "join PCustomerPOD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join MKaryawan c on a.cmarketing=c.nik\n" +
            "join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "left join (\n" +
            "	select a.ccode,a.cnbkt,a.cnbkt_order,b.cgroup,b.cproduct,min(b.harga) as harga \n" +
            "	from TranSaleHeader a \n" +
            "	join TranSale b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	group by a.ccode,a.cnbkt,a.cnbkt_order,b.cgroup,b.cproduct\n" +
            ") e on a.ccode=e.ccode and a.cnbkt=e.cnbkt_order and b.cgroup=e.cgroup and b.cproduct=e.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"'\n" +
            "and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n" +
            "and b.nqty>="+tqty.getText()+" \n";
        if(!tpocustomer.getText().isEmpty()) {
            sql+="and a.cnbkt = '"+tpocustomer.getText()+"'\n";
        }
        if(!tkaryawan.getText().isEmpty()) {
            sql+="and a.cmarketing = '"+tkaryawankode.getText()+"'\n";
        }
        sql+="order by c.cname,a.dtran";
        
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                Double fee=0.0;
                if(rs.getDouble("total")>0) {
                    Double feen = Double.valueOf(tfee.getText());
                    fee = rs.getDouble("total") * (feen/100);
                }
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("marketing"),
                    rs.getString("cproduct"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("nqty"),
                    rs.getString("nunit"),
                    rs.getDouble("harga"),
                    rs.getDouble("total"),
                    fee,
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RMarketingFee.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Nama Produk");
            kolom.add("Qty Awal");
            kolom.add("Qty Masuk");
            kolom.add("Qty Keluar");
            kolom.add("Qty Akhir");
            kolom.add("Unit");
            con.exportExcel(tablestock,kolom,"Laporan Stock Produk",dtran1,dtran2);
            return;
        }
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTablefee.getModel());
            String treport = "report/RFeeMarketing.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
//            param.put("skat", cbSkat.getSelectedItem().toString());
            param.put("skat", "Summary");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
//            JasperViewer.viewReport(JPrint,false);    //With Preview
            JRViewer test = new JRViewer(JPrint);
        
            JFrame frame = new JFrame("Report");
            frame.getContentPane().add(test); //new JRViewer(jasperPrint)
//            frame.setExtendedState(JFrame.);
            frame.setPreferredSize(new Dimension(900, 700));
            frame.pack();
            frame.setVisible(true);   
        } catch (Exception e) {
            e.printStackTrace();
        }
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
                    for (int j = 0; j < table.getColumnCount(); j++) {
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
                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
            } catch (IOException ex) {
                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
            }
                
         
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

        GroupBland = new javax.swing.ButtonGroup();
        GroupSKT = new javax.swing.ButtonGroup();
        GroupSKM = new javax.swing.ButtonGroup();
        GroupMaterialProduksi = new javax.swing.ButtonGroup();
        jScrollPane5 = new javax.swing.JScrollPane();
        tableBland = new javax.swing.JTable();
        jScrollPane6 = new javax.swing.JScrollPane();
        tableSKTList = new javax.swing.JTable();
        jScrollPane1 = new javax.swing.JScrollPane();
        tableSKTBorongan = new javax.swing.JTable();
        jScrollPane4 = new javax.swing.JScrollPane();
        tablestock = new javax.swing.JTable();
        jScrollPane5mutasi = new javax.swing.JScrollPane();
        tablemutasi = new javax.swing.JTable();
        jLabel7 = new javax.swing.JLabel();
        jLabel20 = new javax.swing.JLabel();
        tproduk1 = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        tproduk3 = new javax.swing.JTextField();
        cbSkat = new javax.swing.JComboBox();
        jScrollPanefee = new javax.swing.JScrollPane();
        jTablefee = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        tcode1 = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        DateReq1 = new com.toedter.calendar.JDateChooser();
        jLabel4 = new javax.swing.JLabel();
        DateReq2 = new com.toedter.calendar.JDateChooser();
        jLabel6 = new javax.swing.JLabel();
        jButton7 = new javax.swing.JButton();
        jLabel5 = new javax.swing.JLabel();
        tkaryawankode = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        tkaryawan = new javax.swing.JTextField();
        jLabel8 = new javax.swing.JLabel();
        jLabel9 = new javax.swing.JLabel();
        tfee = new javax.swing.JTextField();
        jLabel10 = new javax.swing.JLabel();
        tqty = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        jButton6 = new javax.swing.JButton();
        tpocustomer = new javax.swing.JTextField();
        jLabel12 = new javax.swing.JLabel();
        jButton8 = new javax.swing.JButton();

        tableBland.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "kstatus", "cgroup", "cproduct", "namaproduk", "qty", "cunit"
            }
        ));
        jScrollPane5.setViewportView(tableBland);

        tableSKTList.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cgroup", "cproduct", "satuan", "pack", "pack_value", "borongan", "operator"
            }
        ));
        jScrollPane6.setViewportView(tableSKTList);
        if (tableSKTList.getColumnModel().getColumnCount() > 0) {
            tableSKTList.getColumnModel().getColumn(3).setHeaderValue("satuan");
            tableSKTList.getColumnModel().getColumn(4).setHeaderValue("pack");
            tableSKTList.getColumnModel().getColumn(5).setHeaderValue("pack_value");
            tableSKTList.getColumnModel().getColumn(6).setHeaderValue("borongan");
            tableSKTList.getColumnModel().getColumn(7).setHeaderValue("operator");
        }

        tableSKTBorongan.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cgroup", "cproduct", "satuan", "pack", "pack_value", "borongan"
            }
        ));
        jScrollPane1.setViewportView(tableSKTBorongan);

        tablestock.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cgroup", "cproduct", "namaproduk", "qtysaldo", "qtydebet", "qtykredit", "qtyakhir", "nunit"
            }
        ));
        jScrollPane4.setViewportView(tablestock);

        tablemutasi.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "skat", "cgroup", "cproduct", "namaproduk", "db", "cr", "mtdb", "mtcr", "note", "cunit", "sisa"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane5mutasi.setViewportView(tablemutasi);

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Skat / Tempat");

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Produk");

        tproduk1.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tproduk1FocusLost(evt);
            }
        });
        tproduk1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tproduk1KeyPressed(evt);
            }
        });

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });

        tproduk3.setEditable(false);
        tproduk3.setBackground(new java.awt.Color(153, 153, 255));
        tproduk3.setForeground(new java.awt.Color(255, 255, 255));

        cbSkat.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));

        jTablefee.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "tgl", "marketing", "cproduct", "produk", "jumlah", "satuan", "harga", "total", "fee"
            }
        ));
        jScrollPanefee.setViewportView(jTablefee);

        setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

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
        jLabel2.setText("Marketing Fee");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        add(jPanel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 11, 546, -1));

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang");
        add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 90, 120, 20));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 90, 90, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 90, 300, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 90, 30, -1));

        DateReq1.setDateFormatString("dd/MM/yyyy");
        DateReq1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateReq1PropertyChange(evt);
            }
        });
        DateReq1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateReq1KeyPressed(evt);
            }
        });
        add(DateReq1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 120, 120, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("s/d");
        add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 120, 20, 20));

        DateReq2.setDateFormatString("dd/MM/yyyy");
        DateReq2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateReq2PropertyChange(evt);
            }
        });
        DateReq2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateReq2KeyPressed(evt);
            }
        });
        add(DateReq2, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 120, 120, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Periode");
        add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 120, 100, 20));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton7.setText("View Fee");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 280, 200, -1));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("Persentase Fee");
        jLabel5.setToolTipText("");
        add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 240, 110, 20));

        tkaryawankode.setEditable(false);
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
        add(tkaryawankode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 180, 90, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 180, 30, -1));

        tkaryawan.setEditable(false);
        add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 180, 200, -1));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Marketing*");
        jLabel8.setToolTipText("");
        add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 180, 72, 20));

        jLabel9.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel9.setText("%");
        jLabel9.setToolTipText("");
        add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 240, 110, 20));

        tfee.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tfee.setText("5");
        add(tfee, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 240, 120, -1));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Minimum Qty");
        jLabel10.setToolTipText("");
        add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 210, 110, 20));

        tqty.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tqty.setText("1000000");
        add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 210, 120, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Pcs");
        jLabel11.setToolTipText("");
        add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 210, 110, 20));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 150, 30, -1));

        tpocustomer.setEditable(false);
        add(tpocustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 150, 300, -1));

        jLabel12.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel12.setText("Nomor PO Customer");
        add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 150, 130, 20));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(490, 150, 30, -1));
    }// </editor-fold>//GEN-END:initComponents

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        if(con.akses(akses, rfa.PRINT)) feemarketing(1);
    }//GEN-LAST:event_jButton7ActionPerformed

    private void DateReq1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateReq1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq1KeyPressed

    private void DateReq1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateReq1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq1PropertyChange

    private void DateReq2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateReq2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq2KeyPressed

    private void DateReq2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateReq2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateReq2PropertyChange

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
            ccode=a.ccode;
            loadSkat();
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);

        rfa.empty(keyssDef, tproduk1,tproduk3);

        if(a.status){
            tproduk1.setText(a.kodeBarang);
            tproduk3.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void tproduk1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tproduk1FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tproduk1FocusLost

    private void tproduk1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tproduk1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tproduk1KeyPressed

    private void tkaryawankodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawankodeFocusLost
        
    }//GEN-LAST:event_tkaryawankodeFocusLost

    private void tkaryawankodeKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawankodeKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawankodeKeyPressed

    private void jButton15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton15ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        tkaryawankode.setText("");
        tkaryawan.setText("");
        if(a.status){
            tkaryawankode.setText(a.Kode);
            tkaryawan.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton15ActionPerformed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogOrderCustomer a = new DialogOrderCustomer();
        a.setVisible(true);
        if(a.status){
            tpocustomer.setText(a.cnbkt);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        tpocustomer.setText("");
    }//GEN-LAST:event_jButton8ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private com.toedter.calendar.JDateChooser DateReq1;
    private com.toedter.calendar.JDateChooser DateReq2;
    private javax.swing.ButtonGroup GroupBland;
    private javax.swing.ButtonGroup GroupMaterialProduksi;
    private javax.swing.ButtonGroup GroupSKM;
    private javax.swing.ButtonGroup GroupSKT;
    private javax.swing.JComboBox cbSkat;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane5mutasi;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JScrollPane jScrollPanefee;
    private javax.swing.JTable jTablefee;
    private javax.swing.JTable tableBland;
    private javax.swing.JTable tableSKTBorongan;
    private javax.swing.JTable tableSKTList;
    private javax.swing.JTable tablemutasi;
    private javax.swing.JTable tablestock;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tfee;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawankode;
    private javax.swing.JTextField tpocustomer;
    private javax.swing.JTextField tproduk1;
    private javax.swing.JTextField tproduk3;
    private javax.swing.JTextField tqty;
    // End of variables declaration//GEN-END:variables
}
