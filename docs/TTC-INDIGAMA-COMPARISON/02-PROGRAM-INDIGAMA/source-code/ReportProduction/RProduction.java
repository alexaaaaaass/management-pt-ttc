/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package ReportProduction;

import Accounting.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogMembers;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Production.*;
import ReportAccounting.*;
import Tools.ConnectDB;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
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
public class RProduction extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1",akses="REPORT PRODUCTION";
    ArrayList<String> deleteRow;
    ArrayList<String> idmesin;
    public RProduction(String mode) {
        initComponents();
  
        deff();
        mesin.removeAll();
        mesin.removeAllItems();
        idmesin = new ArrayList<>();
        
        mesin.addItem(""); idmesin.add("");
        
        sql = "select * from MMesin";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  mesin.addItem(rs.getString("cname")); idmesin.add(rs.getString("id"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
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
        Date1.setDate(new Date());
        Date2.setDate(new Date());
        
        
        tcode.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  
            tcode3.setText(rs.getString("cname"));
        } } catch (SQLException ex) { ex.printStackTrace();   }
    }
     
    private void bahanPrint(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        
        DefaultTableModel model = (DefaultTableModel) tableMaterial.getModel();
        rfa.clear_table(keyssDef, tableMaterial);
            
        sql="select a.cnbkt,a.dtran,a.cgroup,a.cproduct,a.coperator,b.cproduct as bahan,c.cnameProduct,b.cproses,\n" +
            "d.dtran as tglout,d.nunit,d.nqty,d.total as totalhpp\n" +
            "from PWorkOrder a left join PWorkOrderD b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
            "join MProduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
            "left join (\n" +
            "	select a.ccode,a.cworkorder,b.cnbkt,a.dtran,b.cgroup,b.cproduct,b.nunit,b.nqty,b.ncogs,b.nunit_smallvalue,(b.ncogs*b.nunit_smallvalue) as harga,(b.ncogs*b.nunit_smallvalue)*b.nqty as total\n" +
            "	from TranRawMaterialOut a join TranRawMaterialOutD b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
            ") d on a.ccode=d.ccode and b.cgroup=d.cgroup and b.cproduct=d.cproduct and a.cnbkt=d.cworkorder\n" +
            "where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"' \n" +
            "and a.cnbkt like '%"+two.getText()+"%'  "
                + "union all\n" +
            "select a.cnbkt,a.dtran,a.cgroup,a.cproduct,a.coperator,'BEBAN BIAYA' as bahan,'BEBAN BIAYA BORONGAN' as cnameProduct,case when b.ctran='M1' then 'MESIN' else 'LINTING BORONGAN' end as cproses,\n" +
            "null as tglout,'PER BATANG' as nunit,sum(b.cpack_value) as batang,sum(b.cpack_value*b.ccost_unit) as totalhpp from PLinting b join PWorkOrder a on b.cworkorder=a.cnbkt and a.ccode=b.ccode\n"
                            + " where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"' \n" +
                        "and a.cnbkt like '%"+two.getText()+"%'   " +
            "group by b.ccode,b.cworkorder,b.ccost_unit,b.ctran,a.cnbkt,a.dtran,a.cgroup,a.cproduct,a.coperator"
                + "";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                    rs.getString("coperator"),
                    rs.getString("bahan"),rs.getString("cnameProduct"),rs.getString("cproses"),
                    rfa.DateString(keyssDef, rs.getDate("tglout"),"dd MMM yyyy"),
                    rs.getString("nunit"),
                    rs.getDouble("nqty"),
                    rs.getDouble("totalhpp"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No Bukti");
            kolom.add("Tanggal");
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Operator");
            kolom.add("Bahan");
            kolom.add("Nama Bahan");
            kolom.add("Proses");
            kolom.add("Tgl Keluar");
            kolom.add("Unit");
            kolom.add("Qty");
            kolom.add("Total HPP");
            con.exportExcel(tableMaterial,kolom,"Laporan Material Produksi",dtran1,dtran2);
            return;
        }
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableMaterial.getModel());
            String treport = "report/ProductionMaterial.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
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
                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
            } catch (IOException ex) {
                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
            }
                
         
        }
    }
    
    private void SKMPrint(int mode){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableskm.getModel();
        rfa.clear_table(keyssDef, tableskm);
        
        sql="select a.cworkorder,a.cnbkt,a.cnbkt_bland,a.dtran,a.coperator,c.cname as operator,a.cnote,a.csatuan,a.cpack,a.cpack_value,\n" +
            "b.cgroup,b.cproduct \n" +
            "from PLinting a join PWorkOrder b on a.cworkorder=b.cnbkt\n" +
            "join MKaryawan c on a.coperator = c.nik\n" +
            "where a.ctran='M1' and a.ccode='"+tcode.getText()+"' \n" +
            "and (a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"')\n" +
            "and a.cworkorder like '%"+two.getText()+"%'\n" +
            "order by a.cworkorder,a.cnbkt";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cworkorder"),rs.getString("cnbkt"),rs.getString("cnbkt_bland"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"yyyy-MM-dd"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                    rs.getString("operator"),
                    rs.getString("cnote"),rs.getString("csatuan"),
                    rs.getDouble("cpack"),rs.getDouble("cpack_value"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(mode == 2) {
            ArrayList<String> kolom;
            kolom = new ArrayList<>();
            kolom.add("No WO");
            kolom.add("No Bukti");
            kolom.add("No Bland");
            kolom.add("Tanggal");
            kolom.add("Group");
            kolom.add("Produk");
            kolom.add("Operator");
            kolom.add("Keterangan");
            kolom.add("Unit");
            kolom.add("Pack");
            kolom.add("Batang");
            con.exportExcel(tableskm,kolom,"Laporan SKM Produksi",dtran1,dtran2);
            return;
        }
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableskm.getModel());
            String treport = "report/LintingSKMList.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
        
    }
    
    private void bahan_customer(int status){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) tableBahan.getModel();
        rfa.clear_table(keyssDef, tableBahan);
        
        sql="select a.ccode,a.dtran,a.cnbkt,a.cgroup,a.cproduct,aa.cdesc as produk,a.kode_customer,b.cname as customer,\n" +
            "c.cnbkt as buktiout,c.dtran as tglout,c.cproduct as kodebahan,c.produk as bahan,c.nqty,c.nunit\n" +
            "from PWorkOrder a left join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "join MProduk aa on a.cgroup=aa.cgroup and a.cproduct=aa.cproduct\n" +
            "left join (\n" +
            "	select a.cnbkt,a.ccode,a.dtran,a.cworkorder,b.cgroup,b.cproduct,c.cdesc as produk,b.nqty,b.nunit\n" +
            "	from TranRawMaterialOut a join TranRawMaterialOutD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	join mproduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
            ") c on a.ccode=c.ccode and a.cnbkt=c.cworkorder\n" +
            "where a.ccode='"+tcode.getText()+"' \n" +
            "and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n" +
            "\n";
        if(status == 0)  sql+="and isnull(a.kode_customer,'') != ''";  //customer
        if(status == 1)  sql+="and isnull(a.kode_customer,'') = ''";  //pabrik
        if(!tcustomerk.getText().isEmpty()) sql+=" and a.kode_customer='"+tcustomerk.getText()+"' ";
        if(!tproduk.getText().isEmpty()) sql+=" and a.cproduct='"+tproduk.getText()+"' ";
        if(!tbahan.getText().isEmpty()) sql+=" and c.cproduct='"+tbahan.getText()+"' ";
        
        sql+=    "order by a.cnbkt,c.produk";
        
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("ccode"),rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),rs.getString("cnbkt"),
                    rs.getString("cgroup"),rs.getString("cproduct"),
                    rs.getString("produk"),rs.getString("kode_customer"),rs.getString("customer"),
                    rs.getString("buktiout"),rfa.DateString(keyssDef, rs.getDate("tglout"),"dd/MM/yyyy"),rs.getString("kodebahan"),
                    rs.getString("bahan"),rs.getDouble("nqty"),rs.getString("nunit"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }
       
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tableBahan.getModel());
            String treport = "report/RWOMaterialCustomer.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
        
    }

    private void pencapaianproduksi(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rfa.clear_table(keyssDef, jTable1);

        
        sql="select a.ccode,a.cnbkt,a.dtran,a.cnik,c.cname as karyawan,\n" +
            "d.cnameProduct,\n" +
            "a.cstep_name,e.urut as cstep_urut, \n" +
            "a.cgood,\n" +
            "case when a.cgood_unit <> 'PCS' then a.cgood*b.cplot else a.cgood end as cgood_pcs,\n" +
            "a.cgood_unit,\n" +
            "a.cwaste,\n" +
            "case when a.cwaste_unit <> 'PCS' then a.cwaste*b.cplot else a.cwaste end as cwaste_pcs,\n" +
            "a.cwaste_unit\n" +
            "from PProdSteps a\n" +
            "join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "left join MKaryawan c on a.cnik=c.nik\n" +
            "join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n";
        
        sql="select * from (\n" +
            "	select a.ccode,a.cnbkt,a.dtran,a.cnik,c.cname as karyawan,\n" +
            "	d.cproduct,d.cnameProduct,b.kode_customer,\n" +
            "	a.cstep_name,e.urut as cstep_urut, \n" +
            "	a.cgood,\n" +
            "	case when a.cgood_unit <> 'PCS' then a.cgood*b.cplot else a.cgood end as cgood_pcs,\n" +
            "	a.cgood_unit,\n" +
            "	a.cwaste,\n" +
            "	case when a.cwaste_unit <> 'PCS' then a.cwaste*b.cplot else a.cwaste end as cwaste_pcs,\n" +
            "	a.cwaste_unit\n" +
            "	from PProdSteps a\n" +
            "	join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	left join MKaryawan c on a.cnik=c.nik\n" +
            "	join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "	left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "	\n" +
            "\n" +
            "	union all\n" +
            "\n" +
            "\n" +
            "	select a.ccode,a.cworkorder as cnbkt,a.dtran,a.cnik,d.cname as karyawan,e.cproduct,e.cnameProduct,c.kode_customer,'PACKING' as cstep_name,99 as cstep_urut,\n" +
            "	a.nqty_small*a.nqty_box as cgood,a.nqty_small*a.nqty_box as cgood_pcs,a.csatuan_small as cgood_unit,0 as cwaste,0 as cwaste_pcs,a.csatuan_small as cwaste_unit\n" +
            "	From PBoxPacking a --join PBoxPackingD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) c on a.ccode=c.ccode and a.cworkorder=c.cnbkt\n" +
            "	left join mkaryawan d on a.cnik=d.nik \n" +
            "	join MProduk e on c.cgroup=e.cgroup and c.cproduct=e.cproduct\n" +
            "\n" +
            ") a\n"+
            "where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n";
        
        if(!two.getText().isEmpty()) sql+=" and a.cnbkt='"+two.getText()+"' ";
        if(!tproduk.getText().isEmpty()) sql+=" and a.cproduct='"+tproduk.getText()+"' ";
        if(!tcustomerk.getText().isEmpty()) sql+=" and a.kode_customer='"+tcustomerk.getText()+"' ";
        
        sql+="order by cnbkt,cstep_urut,dtran,cnik,cnameProduct";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy"),
                    rs.getString("cnik"),
                    rs.getString("karyawan"),
                    rs.getString("cnameproduct"),
                    rs.getString("cstep_name"),
                    rs.getInt("cgood"),rs.getString("cgood_unit"),
                    rs.getInt("cwaste"),
                    rs.getInt("cgood_pcs"),
                    rs.getInt("cwaste_pcs"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }

        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable1.getModel());
            String treport = "report/RPencapaianProd.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void pencapaianspk(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rfa.clear_table(keyssDef, jTable1);

        
        sql="select a.ccode,a.cnbkt,a.dtran,a.cnik,c.cname as karyawan,\n" +
            "d.cnameProduct,\n" +
            "a.cstep_name,e.urut as cstep_urut, \n" +
            "a.cgood,\n" +
            "case when a.cgood_unit <> 'PCS' then a.cgood*b.cplot else a.cgood end as cgood_pcs,\n" +
            "a.cgood_unit,\n" +
            "a.cwaste,\n" +
            "case when a.cwaste_unit <> 'PCS' then a.cwaste*b.cplot else a.cwaste end as cwaste_pcs,\n" +
            "a.cwaste_unit\n" +
            "from PProdSteps a\n" +
            "join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "left join MKaryawan c on a.cnik=c.nik\n" +
            "join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "where a.ccode='"+tcode.getText()+"' \n";
        
        sql="select * from (\n" +
            "	select isnull(b.kode,0) as kode,a.ccode,a.cnbkt,a.dtran,a.cnik,c.cname as karyawan,\n" +
            "	d.cproduct,d.cnameProduct,b.kode_customer,\n" +
            "	a.cstep_name,e.urut as cstep_urut, \n" +
            "	a.cgood,\n" +
            "	case when a.cgood_unit <> 'PCS' then a.cgood*b.cplot else a.cgood end as cgood_pcs,\n" +
            "	a.cgood_unit,\n" +
            "	a.cwaste,\n" +
            "	case when a.cwaste_unit <> 'PCS' then a.cwaste*b.cplot else a.cwaste end as cwaste_pcs,\n" +
            "	a.cwaste_unit\n" +
            "	from PProdSteps a\n" +
            "	join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	left join MKaryawan c on a.cnik=c.nik\n" +
            "	join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "	left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "	\n" +
            "\n" +
            "	union all\n" +
            "\n" +
            "\n" +
            "	select isnull(c.kode,0) as kode,a.ccode,a.cworkorder as cnbkt,a.dtran,a.cnik,d.cname as karyawan,e.cproduct,e.cnameProduct,c.kode_customer,'PACKING' as cstep_name,99 as cstep_urut,\n" +
            "	a.nqty_small*a.nqty_box as cgood,a.nqty_small*a.nqty_box as cgood_pcs,a.csatuan_small as cgood_unit,0 as cwaste,0 as cwaste_pcs,a.csatuan_small as cwaste_unit\n" +
            "	From PBoxPacking a --join PBoxPackingD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) c on a.ccode=c.ccode and a.cworkorder=c.cnbkt\n" +
            "	left join mkaryawan d on a.cnik=d.nik \n" +
            "	join MProduk e on c.cgroup=e.cgroup and c.cproduct=e.cproduct\n" +
            "\n" +
            ") a\n"+
            "where a.ccode='"+tcode.getText()+"'  \n";
        
        if(!two.getText().isEmpty()) sql+=" and a.cnbkt='"+two.getText()+"' "; 
        if(kodeppn.getSelectedIndex() > 0) sql+=" and kode='"+kodeppn.getSelectedIndex()+"' ";
//        if(!tproduk.getText().isEmpty()) sql+=" and b.cproduct='"+tproduk.getText()+"' ";
//        if(!tcustomerk.getText().isEmpty()) sql+=" and b.kode_customer='"+tcustomerk.getText()+"' ";
        
        sql+="order by cnbkt,cstep_urut,dtran,cnik,cnameProduct";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy"),
                    rs.getString("cnik"),
                    rs.getString("karyawan"),
                    rs.getString("cnameproduct"),
                    rs.getString("cstep_name"),
                    rs.getInt("cgood"),rs.getString("cgood_unit"),
                    rs.getInt("cwaste"),
                    rs.getInt("cgood_pcs"),
                    rs.getInt("cwaste_pcs"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }

        if(model.getRowCount() <= 0) {
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable1.getModel());
            String treport = "report/RPencapaianProdSPK.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    
    private void pencapaiankaryawan(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rfa.clear_table(keyssDef, jTable1);

        
        sql="select a.ccode,a.cnbkt,a.dtran,a.cnik,c.cname as karyawan,\n" +
            "d.cnameProduct,\n" +
            "a.cstep_name,e.urut as cstep_urut, \n" +
            "a.cgood,\n" +
            "case when a.cgood_unit <> 'PCS' then a.cgood*b.cplot else a.cgood end as cgood_pcs,\n" +
            "a.cgood_unit,\n" +
            "a.cwaste,\n" +
            "case when a.cwaste_unit <> 'PCS' then a.cwaste*b.cplot else a.cwaste end as cwaste_pcs,\n" +
            "a.cwaste_unit\n" +
            "from PProdSteps a\n" +
            "join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "left join MKaryawan c on a.cnik=c.nik\n" +
            "join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n";
        
        sql="select * from (\n" +
            "	select a.ccode,a.cnbkt,a.dtran,a.cnik,c.cname as karyawan,\n" +
            "	d.cproduct,d.cnameProduct,b.kode_customer,\n" +
            "	a.cstep_name,e.urut as cstep_urut, \n" +
            "	a.cgood,\n" +
            "	case when a.cgood_unit <> 'PCS' then a.cgood*b.cplot else a.cgood end as cgood_pcs,\n" +
            "	a.cgood_unit,\n" +
            "	a.cwaste,\n" +
            "	case when a.cwaste_unit <> 'PCS' then a.cwaste*b.cplot else a.cwaste end as cwaste_pcs,\n" +
            "	a.cwaste_unit\n" +
            "	from PProdSteps a\n" +
            "	join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	left join MKaryawan c on a.cnik=c.nik\n" +
            "	join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "	left join PWorkOrderStep e on a.cstep_kode=e.cstep_kode and a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
            "	\n" +
            "\n" +
            "	union all\n" +
            "\n" +
            "\n" +
            "	select a.ccode,a.cworkorder as cnbkt,a.dtran,a.cnik,d.cname as karyawan,e.cproduct,e.cnameProduct,c.kode_customer,'PACKING' as cstep_name,99 as cstep_urut,\n" +
            "	a.nqty_small*a.nqty_box as cgood,a.nqty_small*a.nqty_box as cgood_pcs,a.csatuan_small as cgood_unit,0 as cwaste,0 as cwaste_pcs,a.csatuan_small as cwaste_unit\n" +
            "	From PBoxPacking a --join PBoxPackingD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	join (select a.*,b.cplot from PWorkOrder a join BOMProduct b on a.cnbkt_bom=b.cnbkt) c on a.ccode=c.ccode and a.cworkorder=c.cnbkt\n" +
            "	left join mkaryawan d on a.cnik=d.nik \n" +
            "	join MProduk e on c.cgroup=e.cgroup and c.cproduct=e.cproduct\n" +
            "\n" +
            ") a\n"+
            "where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n";
        
        if(!two.getText().isEmpty()) sql+=" and a.cnbkt='"+two.getText()+"' ";
        if(!tproduk.getText().isEmpty()) sql+=" and a.cproduct='"+tproduk.getText()+"' ";
        if(!tcustomerk.getText().isEmpty()) sql+=" and a.kode_customer='"+tcustomerk.getText()+"' ";
        
        sql+="order by cnbkt,cstep_urut,dtran,cnik,cnameProduct";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy"),
                    rs.getString("cnik"),
                    rs.getString("karyawan"),
                    rs.getString("cnameproduct"),
                    rs.getString("cstep_name"),
                    rs.getInt("cgood"),rs.getString("cgood_unit"),
                    rs.getInt("cwaste"),
                    rs.getInt("cgood_pcs"),
                    rs.getInt("cwaste_pcs"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }

        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable1.getModel());
            String treport = "report/RPencapaianProdKaryawan.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void pencapaiankaryawan_err(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        rfa.clear_table(keyssDef, jTable2);

        
        sql="select a.ccode,a.cnbkt,a.dtran,a.cnik,c.cname as karyawan,\n" +
            "d.cnameProduct,\n" +
            "a.cstep_name,a.nqty_fail,aa.cwaste_unit,a.cerr_name\n" +
            "from PProdErr a join PProdSteps aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt and a.cnik=aa.cnik and a.cstep_kode=aa.cstep_kode and a.dtran=aa.dtran\n" +
            "join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join MKaryawan c on a.cnik=c.nik\n" +
            "join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n";
        if(!two.getText().isEmpty()) sql+=" and a.cnbkt='"+two.getText()+"' ";
        if(!tproduk.getText().isEmpty()) sql+=" and b.cproduct='"+tproduk.getText()+"' ";
        if(!tcustomerk.getText().isEmpty()) sql+=" and b.kode_customer='"+tcustomerk.getText()+"' ";
        
        sql+="order by a.dtran,a.cnbkt,a.cnik,a.cstep_name,d.cnameProduct";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy"),
                    rs.getString("cnik"),
                    rs.getString("karyawan"),
                    rs.getString("cnameproduct"),
                    rs.getString("cstep_name"),
                    rs.getInt("nqty_fail"),rs.getString("cwaste_unit"),
                    rs.getString("cerr_name"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }

        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable2.getModel());
            String treport = "report/RPencapaianProdErr.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void rab(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        rfa.clear_table(keyssDef, jTable3);

        
        sql="select * from (\n" +
            "	select distinct 0 as urut,'Bahan' as cstatus,isnull(c.kode,0) as kode,a.ccode,a.cnbkt,c.nqty,c.nqty_add,c.nqty_total,d.cnameProduct as produkspk,b.cnameProduct as bahan,a.nqty as qtybahan,a.nprice/a.nqty as price,a.nprice as total\n" +
            "	from PWorkOrderD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "	join PWorkOrder c on a.ccode=c.ccode and a.cnbkt=c.cnbkt\n" +
            "	join MProduk d on c.cgroup=d.cgroup and c.cproduct=d.cproduct\n" +
            "	union all\n" +
            "	select distinct 1 as urut,'Cost' as cstatus,isnull(b.kode,0) as kode,a.ccode,a.cnbkt,b.nqty,b.nqty_add,b.nqty_total,d.cnameProduct as produkspk,cost+' ('+convert(nvarchar,b.nqty)+' PCS)' as bahan,a.cprice/b.nqty_total  as qtybahan,b.nqty_total,a.cprice\n" +
            "	from PWorkOrderCost a join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	join MProduk d on b.cgroup=d.cgroup and b.cproduct=d.cproduct\n" +
            " ) a\n" +
            " where ccode='"+tcode.getText()+"'  \n";
        if(!two.getText().isEmpty()) sql+=" and cnbkt='"+two.getText()+"' ";
        if(!tproduk.getText().isEmpty()) sql+=" and produkspk='"+tproduk1.getText()+"' ";
        if(kodeppn.getSelectedIndex() > 0) sql+=" and kode='"+kodeppn.getSelectedIndex()+"' ";
        sql+=" order by ccode,cnbkt,urut";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cstatus"),
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),
                    rs.getDouble("nqty"),
                    rs.getDouble("nqty_add"),
                    rs.getDouble("nqty_total"), 
                    rs.getString("produkspk"),
                    rs.getString("bahan"), 
                    rs.getDouble("qtybahan"),
                    rs.getDouble("price"),
                    rs.getDouble("total"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }

        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable3.getModel());
            String treport = "report/RAB.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void box_detail(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTablebox.getModel();
        rfa.clear_table(keyssDef, jTablebox);

        sql="select a.ccode,a.cworkorder,c.cproduct,d.cnameProduct,a.dtran,\n" +
            "count(b.cbox_no) as jmlbox,sum(b.nqty_small) as qty,b.csatuan_small \n" +
            "from PBoxPacking a join PBoxPackingD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join PWorkOrder c on a.ccode=c.ccode and a.cworkorder=c.cnbkt\n" +
            "join MProduk d on c.cgroup=d.cgroup and c.cproduct=d.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"'\n";
        
        if(!two.getText().isEmpty()) sql+=" and a.cworkorder='"+two.getText()+"' ";
        if(kodeppn.getSelectedIndex() > 0) sql+=" and c.kode='"+kodeppn.getSelectedIndex()+"' ";
        
        sql+="group by a.ccode,a.cworkorder,c.cproduct,d.cnameProduct,a.cworkorder,b.csatuan_small ,a.dtran\n" +
            "order by a.cworkorder,a.dtran";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{ 
                    rs.getString("ccode"),
                    rs.getString("cworkorder"),
                    rs.getString("cproduct"),
                    rs.getString("cnameproduct"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getDouble("jmlbox"),
                    rs.getDouble("qty"), 
                    rs.getString("csatuan_small"), 
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }

        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTablebox.getModel());
            String treport = "report/HasilBoxDetail.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void box_rekap(){
        String dtran1=rfa.DateString(keyssDef, Date1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, Date2.getDate(),"yyyy-MM-dd");
        
        DefaultTableModel model = (DefaultTableModel) jTablebox.getModel();
        rfa.clear_table(keyssDef, jTablebox);

        sql="select a.ccode,a.cworkorder,c.cproduct,d.cnameProduct,c.dtran,\n" +
            "count(b.cbox_no) as jmlbox,sum(b.nqty_small) as qty,b.csatuan_small \n" +
            "from PBoxPacking a join PBoxPackingD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join PWorkOrder c on a.ccode=c.ccode and a.cworkorder=c.cnbkt\n" +
            "join MProduk d on c.cgroup=d.cgroup and c.cproduct=d.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"'\n";
        
        if(!two.getText().isEmpty()) sql+=" and a.cworkorder='"+two.getText()+"' ";
        if(kodeppn.getSelectedIndex() > 0) sql+=" and c.kode='"+kodeppn.getSelectedIndex()+"' ";
        
        sql+="group by a.ccode,a.cworkorder,c.cproduct,d.cnameProduct,a.cworkorder,b.csatuan_small ,c.dtran\n" +
            "order by a.cworkorder,c.dtran";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{ 
                    rs.getString("ccode"),
                    rs.getString("cworkorder"),
                    rs.getString("cproduct"),
                    rs.getString("cnameproduct"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getDouble("jmlbox"),
                    rs.getDouble("qty"), 
                    rs.getString("csatuan_small"), 
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);
        }

        try{
        String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTablebox.getModel());
            String treport = "report/HasilBoxRekap.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
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

        GroupBland = new javax.swing.ButtonGroup();
        GroupSKT = new javax.swing.ButtonGroup();
        GroupSKM = new javax.swing.ButtonGroup();
        GroupMaterialProduksi = new javax.swing.ButtonGroup();
        jScrollPane1 = new javax.swing.JScrollPane();
        tableBland = new javax.swing.JTable();
        jScrollPane2 = new javax.swing.JScrollPane();
        tableSKTProduct = new javax.swing.JTable();
        jScrollPane4sktborongan = new javax.swing.JScrollPane();
        tableSKTBorongan = new javax.swing.JTable();
        jScrollPane5 = new javax.swing.JScrollPane();
        tableSKTList = new javax.swing.JTable();
        jScrollPane6 = new javax.swing.JScrollPane();
        tableMaterial = new javax.swing.JTable();
        jScrollPane7 = new javax.swing.JScrollPane();
        tableskm = new javax.swing.JTable();
        jScrollPane3 = new javax.swing.JScrollPane();
        tableExport = new javax.swing.JTable();
        buttonGroup1 = new javax.swing.ButtonGroup();
        jButton5 = new javax.swing.JButton();
        jScrollPanelBahan = new javax.swing.JScrollPane();
        tableBahan = new javax.swing.JTable();
        jScrollPanelmesin = new javax.swing.JScrollPane();
        tablemesin = new javax.swing.JTable();
        jScrollPaneBoronganPack = new javax.swing.JScrollPane();
        tableBoronganPack = new javax.swing.JTable();
        jbonusmesin = new javax.swing.JScrollPane();
        tablebonusmesin = new javax.swing.JTable();
        jrekapmesin = new javax.swing.JScrollPane();
        tablerekapmesin = new javax.swing.JTable();
        jtablehlp = new javax.swing.JScrollPane();
        tablehlp = new javax.swing.JTable();
        jbonushlp = new javax.swing.JScrollPane();
        tablebonushlp = new javax.swing.JTable();
        jScrollPanecapaikaryawan = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jScrollPanecapaikaryawanerr = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jScrollPaneRAB = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jButton7 = new javax.swing.JButton();
        jButton2 = new javax.swing.JButton();
        jButton25 = new javax.swing.JButton();
        jButton22 = new javax.swing.JButton();
        jScrollPanebox = new javax.swing.JScrollPane();
        jTablebox = new javax.swing.JTable();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jButton30 = new javax.swing.JButton();
        jButton31 = new javax.swing.JButton();
        jButton34 = new javax.swing.JButton();
        jPanel8 = new javax.swing.JPanel();
        jButton32 = new javax.swing.JButton();
        jButton33 = new javax.swing.JButton();
        jButton35 = new javax.swing.JButton();
        jLabel8 = new javax.swing.JLabel();
        kodeppn = new javax.swing.JComboBox();
        jButton36 = new javax.swing.JButton();
        jButton37 = new javax.swing.JButton();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        jPanel6 = new javax.swing.JPanel();
        jLabel7 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        tcode3 = new javax.swing.JTextField();
        jButton18 = new javax.swing.JButton();
        jLabel6 = new javax.swing.JLabel();
        Date1 = new com.toedter.calendar.JDateChooser();
        jLabel4 = new javax.swing.JLabel();
        Date2 = new com.toedter.calendar.JDateChooser();
        jLabel19 = new javax.swing.JLabel();
        two = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        jLabel22 = new javax.swing.JLabel();
        tcustomerk1 = new javax.swing.JTextField();
        jButton4 = new javax.swing.JButton();
        tcustomerk = new javax.swing.JTextField();
        jLabel23 = new javax.swing.JLabel();
        jLabel24 = new javax.swing.JLabel();
        tproduk = new javax.swing.JTextField();
        jButton23 = new javax.swing.JButton();
        tproduk1 = new javax.swing.JTextField();
        tbahan = new javax.swing.JTextField();
        jButton24 = new javax.swing.JButton();
        tbahan1 = new javax.swing.JTextField();
        jLabel25 = new javax.swing.JLabel();
        mesin = new javax.swing.JComboBox();

        tableBland.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "cnbkt_rmo", "dtran", "cgroup", "cproduct", "coperator", "operator", "bahangroup", "bahanproduk", "namabahan", "nqty", "nunit", "timestart", "timeend", "Title 15", "Title 16", "Title 17", "Title 18", "Title 19", "Title 20", "Title 21", "Title 22", "Title 23"
            }
        ));
        jScrollPane1.setViewportView(tableBland);

        tableSKTProduct.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cgroup", "cproduct", "satuan", "pack", "pack_value"
            }
        ));
        jScrollPane2.setViewportView(tableSKTProduct);

        tableSKTBorongan.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cgroup", "cproduct", "satuan", "pack", "pack_value", "borongan", "gaji", "Title 9", "Title 10", "Title 11"
            }
        ));
        jScrollPane4sktborongan.setViewportView(tableSKTBorongan);

        tableSKTList.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "dtran", "cgroup", "cproduct", "satuan", "pack", "pack_value", "borongan", "operator", "Title 9", "Title 10", "Title 11"
            }
        ));
        jScrollPane5.setViewportView(tableSKTList);

        tableMaterial.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "cgroup", "cproduct", "coperator", "bahan", "namabahan", "cproses", "tglout", "nunit", "nqty", "totalhpp"
            }
        ));
        jScrollPane6.setViewportView(tableMaterial);

        tableskm.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "wo", "cnbkt", "bland", "dtran", "cgroup", "cproduct", "coperator", "note", "satuan", "pack", "pack_value", "Title 12", "Title 13", "Title 14", "Title 15", "Title 16", "Title 17", "Title 18", "Title 19", "Title 20", "Title 21", "Title 22", "Title 23"
            }
        ));
        jScrollPane7.setViewportView(tableskm);

        tableExport.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Title 1", "Title 2", "Title 3", "Title 4", "Title 5", "Title 6", "Title 7", "Title 8", "Title 9", "Title 10", "Title 11", "Title 12", "Title 13", "Title 14", "Title 15", "Title 16", "Title 17", "Title 18", "Title 19", "Title 20"
            }
        ));
        jScrollPane3.setViewportView(tableExport);
        if (tableExport.getColumnModel().getColumnCount() > 0) {
            tableExport.getColumnModel().getColumn(14).setHeaderValue("");
            tableExport.getColumnModel().getColumn(15).setHeaderValue("Title 16");
            tableExport.getColumnModel().getColumn(16).setHeaderValue("Title 17");
            tableExport.getColumnModel().getColumn(17).setHeaderValue("Title 18");
            tableExport.getColumnModel().getColumn(18).setHeaderValue("Title 19");
            tableExport.getColumnModel().getColumn(19).setHeaderValue("Title 20");
        }

        jButton5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton5.setText("View");
        jButton5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton5ActionPerformed(evt);
            }
        });

        tableBahan.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "dtran", "cnbkt", "cgroup", "cproduct", "produk", "kode_customer", "customer", "buktiout", "tglout", "kodebahan", "bahan", "nqty", "nunit"
            }
        ));
        jScrollPanelBahan.setViewportView(tableBahan);

        tablemesin.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "dtran", "cnbkt", "cgroup", "cproduct", "produk", "kode_customer", "customer", "coperator", "operator", "buktimesin", "tglmesin", "nqty_sebelum", "nqty_sisa", "mesin", "shift", "reject", "jml", "hasil", "qty", "nunit", "kode"
            }
        ));
        jScrollPanelmesin.setViewportView(tablemesin);
        if (tablemesin.getColumnModel().getColumnCount() > 0) {
            tablemesin.getColumnModel().getColumn(13).setHeaderValue("nqty_sisa");
            tablemesin.getColumnModel().getColumn(16).setHeaderValue("reject");
        }

        tableBoronganPack.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "cgroup", "cproduct", "produk", "buktilinting", "tgllinting", "coperator", "operator", "cpack", "csatuan", "pita", "tud", "pabrik", "sendiri", "oppsendiripabrik", "oppmesinpabrik", "oppsendirisendiri", "oppmesinsendiri", "gajipack"
            }
        ));
        jScrollPaneBoronganPack.setViewportView(tableBoronganPack);

        tablebonusmesin.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "cnbkt", "cgroup", "cproduct", "cnameproduct", "coperator", "operator", "shift", "mesin", "dtran", "brutohasil", "brutobahan", "qty", "bonus"
            }
        ));
        jbonusmesin.setViewportView(tablebonusmesin);
        if (tablebonusmesin.getColumnModel().getColumnCount() > 0) {
            tablebonusmesin.getColumnModel().getColumn(12).setHeaderValue("qty");
        }

        tablerekapmesin.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "cgroup", "cproduct", "cnameproduct", "mesin", "brutohasil", "brutobahan", "qty"
            }
        ));
        jrekapmesin.setViewportView(tablerekapmesin);
        if (tablerekapmesin.getColumnModel().getColumnCount() > 0) {
            tablerekapmesin.getColumnModel().getColumn(7).setHeaderValue("qty");
        }

        tablehlp.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "dtran", "cnbkt", "cgroup", "cproduct", "produk", "kode_customer", "customer", "coperator", "operator", "buktimesin", "tglmesin", "ntotalpack", "mesin", "shift", "jml", "hasil", "qty", "nunit", "kode"
            }
        ));
        jtablehlp.setViewportView(tablehlp);

        tablebonushlp.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "cnbkt", "cgroup", "cproduct", "cnameproduct", "coperator", "operator", "shift", "mesin", "dtran", "ntotalpack", "ball", "bonus"
            }
        ));
        jbonushlp.setViewportView(tablebonushlp);

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {"", null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "cnik", "karyawan", "cnameproduct", "cstep_name", "cgood", "cgood_unit", "cwaste", "cgood_pcs", "cwaste_pcs"
            }
        ));
        jScrollPanecapaikaryawan.setViewportView(jTable1);

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {"", null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "cnik", "karyawan", "cnameproduct", "cstep_name", "cwaste", "cwaste_unit", "cerr_name"
            }
        ));
        jScrollPanecapaikaryawanerr.setViewportView(jTable2);

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cstatus", "ccode", "cnbkt", "nqty", "nqty_add", "nqty_total", "produkspk", "bahan", "qtybahan", "price", "total"
            }
        ));
        jScrollPaneRAB.setViewportView(jTable3);

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton7.setText("View Tiap WO");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Excel_16px.png"))); // NOI18N
        jButton2.setText("Export");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });

        jButton25.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton25.setText("WO Pabrik");
        jButton25.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton25ActionPerformed(evt);
            }
        });

        jButton22.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton22.setText("WO Customer");
        jButton22.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton22ActionPerformed(evt);
            }
        });

        jTablebox.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "ccode", "spk", "cproduct", "cnameproduct", "dtran", "jmlbox", "qty", "cunit"
            }
        ));
        jScrollPanebox.setViewportView(jTablebox);

        setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton30.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton30.setText("Waste Produksi");
        jButton30.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton30ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton30, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, 210, 30));

        jButton31.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton31.setText("Pencapaian Karyawan");
        jButton31.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton31ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton31, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 20, 210, 30));

        jButton34.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton34.setText("Pencapaian");
        jButton34.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton34ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton34, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 210, 30));

        jTabbedPane1.addTab("Daily Karyawan", jPanel4);

        jPanel8.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton32.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton32.setText("RAB (Rencana Anggaran Biaya)");
        jButton32.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton32ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton32, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 250, 30));

        jButton33.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton33.setText("Material Produksi");
        jButton33.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton33ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton33, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 160, 250, 30));

        jButton35.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton35.setText("Pencapaian Berdasarkan SPK");
        jButton35.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton35ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton35, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 250, 30));

        jLabel8.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel8.setText("PPN/Non PPN");
        jPanel8.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 120, 20));

        kodeppn.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "SEMUA", "PPN", "NON PPN" }));
        kodeppn.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                kodeppnActionPerformed(evt);
            }
        });
        jPanel8.add(kodeppn, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 120, -1));

        jButton36.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton36.setText("Hasil Box SPK (Detail)");
        jButton36.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton36ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton36, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 110, 250, 30));

        jButton37.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton37.setText("Hasil Box SPK (Rekap)");
        jButton37.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton37ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton37, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 60, 250, 30));

        jTabbedPane1.addTab("Produksi", jPanel8);

        add(jTabbedPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 250, 1210, 230));

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
        jLabel2.setText("Production Report");
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

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Periode");
        jPanel6.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 100, 20));

        Date1.setDateFormatString("dd/MM/yyyy");
        Date1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                Date1PropertyChange(evt);
            }
        });
        Date1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                Date1KeyPressed(evt);
            }
        });
        jPanel6.add(Date1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 60, 120, -1));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("s/d");
        jPanel6.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, 20, 20));

        Date2.setDateFormatString("dd/MM/yyyy");
        Date2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                Date2PropertyChange(evt);
            }
        });
        Date2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                Date2KeyPressed(evt);
            }
        });
        jPanel6.add(Date2, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 60, 120, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("No Produksi /WO");
        jPanel6.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 90, 120, 20));
        jPanel6.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 90, 250, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(400, 90, 30, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Nama Bahan");
        jPanel6.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 150, -1, 20));

        tcustomerk1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tcustomerk1KeyTyped(evt);
            }
        });
        jPanel6.add(tcustomerk1, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 30, 90, -1));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton4ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(770, 30, 30, -1));

        tcustomerk.setEditable(false);
        jPanel6.add(tcustomerk, new org.netbeans.lib.awtextra.AbsoluteConstraints(810, 30, 50, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Mesin No");
        jPanel6.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 60, -1, 20));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Nama Produk");
        jPanel6.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 120, -1, 20));

        tproduk.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tprodukFocusLost(evt);
            }
        });
        tproduk.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tprodukKeyPressed(evt);
            }
        });
        jPanel6.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 120, 90, -1));

        jButton23.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton23.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton23ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton23, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 120, 30, -1));

        tproduk1.setEditable(false);
        tproduk1.setBackground(new java.awt.Color(153, 153, 255));
        tproduk1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel6.add(tproduk1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 120, 230, -1));

        tbahan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbahanFocusLost(evt);
            }
        });
        tbahan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbahanKeyPressed(evt);
            }
        });
        jPanel6.add(tbahan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 150, 90, -1));

        jButton24.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton24.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton24ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton24, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 150, 30, -1));

        tbahan1.setEditable(false);
        tbahan1.setBackground(new java.awt.Color(153, 153, 255));
        tbahan1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel6.add(tbahan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 150, 230, -1));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Customer");
        jPanel6.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 30, -1, 20));

        mesin.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jPanel6.add(mesin, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 60, 170, -1));

        add(jPanel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 62, 1210, 180));
    }// </editor-fold>//GEN-END:initComponents

    private void Date2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_Date2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_Date2PropertyChange

    private void Date2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_Date2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_Date2KeyPressed

    private void Date1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_Date1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_Date1PropertyChange

    private void Date1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_Date1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_Date1KeyPressed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogWO a = new DialogWO();
        a.setVisible(true);
        if(a.status){
            two.setText(a.cnbkt);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void jButton5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton5ActionPerformed
        if(con.akses(akses, rfa.PRINT)) SKMPrint(1);
    }//GEN-LAST:event_jButton5ActionPerformed

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

    private void tcustomerk1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tcustomerk1KeyTyped
        if(evt.getKeyChar() == KeyEvent.VK_BACK_SPACE) {
            rfa.empty(keyssDef, tcustomerk,tcustomerk1);
        }
    }//GEN-LAST:event_tcustomerk1KeyTyped

    private void jButton4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton4ActionPerformed
        DialogMembers a = new DialogMembers();
        a.setVisible(true);
        rfa.empty(keyssDef, tcustomerk,tcustomerk1);
        if(a.status){
            tcustomerk.setText(a.Kode);
            tcustomerk1.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton4ActionPerformed

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        sql = "select * from mproduk where cproduct = '"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tbahan.setText(rs.getString("cgroup"));
            tproduk1.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
        
    }//GEN-LAST:event_tprodukKeyPressed

    private void jButton23ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton23ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        rfa.empty(keyssDef, tproduk,tproduk1);
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tproduk1.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton23ActionPerformed

    private void tbahanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbahanFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tbahanFocusLost

    private void tbahanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbahanKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tbahanKeyPressed

    private void jButton24ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton24ActionPerformed
        DialogProduk a = new DialogProduk(2);
        a.setVisible(true);
        rfa.empty(keyssDef, tbahan,tbahan1);
        if(a.status){
            tbahan.setText(a.kodeBarang);
            tbahan1.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton24ActionPerformed

    private void jButton32ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton32ActionPerformed
        rab();
    }//GEN-LAST:event_jButton32ActionPerformed

    private void jButton31ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton31ActionPerformed
        pencapaiankaryawan();
    }//GEN-LAST:event_jButton31ActionPerformed

    private void jButton30ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton30ActionPerformed
        pencapaiankaryawan_err();
    }//GEN-LAST:event_jButton30ActionPerformed

    private void jButton25ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton25ActionPerformed
        bahan_customer(1);
    }//GEN-LAST:event_jButton25ActionPerformed

    private void jButton22ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton22ActionPerformed
        bahan_customer(0);
    }//GEN-LAST:event_jButton22ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        if(con.akses(akses, rfa.PRINT)) bahanPrint(2);
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        if(con.akses(akses, rfa.PRINT)) bahanPrint(1);
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jButton33ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton33ActionPerformed
        JOptionPane.showMessageDialog(this,"Dalam Perbaikan...", "Info", JOptionPane.INFORMATION_MESSAGE);
    }//GEN-LAST:event_jButton33ActionPerformed

    private void jButton34ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton34ActionPerformed
        pencapaianproduksi();
    }//GEN-LAST:event_jButton34ActionPerformed

    private void jButton35ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton35ActionPerformed
        pencapaianspk();
    }//GEN-LAST:event_jButton35ActionPerformed

    private void kodeppnActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_kodeppnActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_kodeppnActionPerformed

    private void jButton36ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton36ActionPerformed
        box_detail();
    }//GEN-LAST:event_jButton36ActionPerformed

    private void jButton37ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton37ActionPerformed
        box_rekap();
    }//GEN-LAST:event_jButton37ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private com.toedter.calendar.JDateChooser Date1;
    private com.toedter.calendar.JDateChooser Date2;
    private javax.swing.ButtonGroup GroupBland;
    private javax.swing.ButtonGroup GroupMaterialProduksi;
    private javax.swing.ButtonGroup GroupSKM;
    private javax.swing.ButtonGroup GroupSKT;
    private javax.swing.ButtonGroup buttonGroup1;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton18;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton22;
    private javax.swing.JButton jButton23;
    private javax.swing.JButton jButton24;
    private javax.swing.JButton jButton25;
    private javax.swing.JButton jButton30;
    private javax.swing.JButton jButton31;
    private javax.swing.JButton jButton32;
    private javax.swing.JButton jButton33;
    private javax.swing.JButton jButton34;
    private javax.swing.JButton jButton35;
    private javax.swing.JButton jButton36;
    private javax.swing.JButton jButton37;
    private javax.swing.JButton jButton4;
    private javax.swing.JButton jButton5;
    private javax.swing.JButton jButton7;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4sktborongan;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JScrollPane jScrollPane7;
    private javax.swing.JScrollPane jScrollPaneBoronganPack;
    private javax.swing.JScrollPane jScrollPaneRAB;
    private javax.swing.JScrollPane jScrollPanebox;
    private javax.swing.JScrollPane jScrollPanecapaikaryawan;
    private javax.swing.JScrollPane jScrollPanecapaikaryawanerr;
    private javax.swing.JScrollPane jScrollPanelBahan;
    private javax.swing.JScrollPane jScrollPanelmesin;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTable jTablebox;
    private javax.swing.JScrollPane jbonushlp;
    private javax.swing.JScrollPane jbonusmesin;
    private javax.swing.JScrollPane jrekapmesin;
    private javax.swing.JScrollPane jtablehlp;
    private javax.swing.JComboBox kodeppn;
    private javax.swing.JComboBox mesin;
    private javax.swing.JTable tableBahan;
    private javax.swing.JTable tableBland;
    private javax.swing.JTable tableBoronganPack;
    private javax.swing.JTable tableExport;
    private javax.swing.JTable tableMaterial;
    private javax.swing.JTable tableSKTBorongan;
    private javax.swing.JTable tableSKTList;
    private javax.swing.JTable tableSKTProduct;
    private javax.swing.JTable tablebonushlp;
    private javax.swing.JTable tablebonusmesin;
    private javax.swing.JTable tablehlp;
    private javax.swing.JTable tablemesin;
    private javax.swing.JTable tablerekapmesin;
    private javax.swing.JTable tableskm;
    private javax.swing.JTextField tbahan;
    private javax.swing.JTextField tbahan1;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode3;
    private javax.swing.JTextField tcustomerk;
    private javax.swing.JTextField tcustomerk1;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tproduk1;
    private javax.swing.JTextField two;
    // End of variables declaration//GEN-END:variables
}
