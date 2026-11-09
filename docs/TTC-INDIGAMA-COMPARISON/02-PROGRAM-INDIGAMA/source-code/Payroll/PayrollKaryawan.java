/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Payroll;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogDataPayroll;
import DialogPublicTP.DialogDataPayrollKaryawan;
import DialogPublicTP.DialogDepartemen;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogJabatan;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Tools.ConnectDB;
import Tools.FrozenTablePane;
import Tools.TableColumnAdjuster;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Timer;
import java.util.TimerTask;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JOptionPane;
import javax.swing.JScrollPane;
import javax.swing.JTable;
import javax.swing.JViewport;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.JTableHeader;
import javax.swing.table.TableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import static org.openxmlformats.schemas.drawingml.x2006.chart.STAxPos.Enum.table;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class PayrollKaryawan extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="TRANSAKSI PAYROLL KARYAWAN";
    String idKas="21",typePayroll;
    ArrayList<String> deleteRow;
    public PayrollKaryawan(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        push_button();
        clear();
        deff();
        loadData();
        
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
         for(int i=1; i<jTable1.getColumnCount(); i++){
            jTable1.getColumnModel().getColumn(i).setMinWidth(150);
        }
        
         
        btDelete.setVisible(false);
        btDelete.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        buttonGroup1.add(rbGajiBulanan);
        buttonGroup1.add(rbGajiHari);
        
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(new Date());
        
        jComboBox1.setSelectedIndex(Integer.valueOf(rfa.DateString(keyssDef, new Date(), "MM"))-1);
        
        tcode.setText(deffProgram.get("ccode"));
//        tacc.setText(deffProgram.get("acc_payrolldebet"));
//        taccll.setText(deffProgram.get("acc_payrollkredit"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
//        sql = "select * from maccount where caccnt = '"+tacc.getText()+"' ";
//        rs = con.queryResult(sql);
//        try {
//            while(rs.next()){  tacc1.setText(rs.getString("cdesc"));}
//        } catch (SQLException ex) { ex.printStackTrace();   }
//        
//        sql = "select * from maccount where caccnt = '"+taccll.getText()+"' ";
//        rs = con.queryResult(sql);
//        try {
//            while(rs.next()){  taccll1.setText(rs.getString("cdesc"));}
//        } catch (SQLException ex) { ex.printStackTrace();   }
        
        LastCode();
        hitungTotalTransaksi();
//        FrozenTablePane frozenPane = new FrozenTablePane(jTable1,1);
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tacc,tacc1,taccll,taccll1,tjab,tjab1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        deleteRow.clear();
    }
    private void deffBatal(){
        barisEdit = -1;
        id = "";
//        rfa.empty(MenuAdmin.keyssDef,tket,tnilai,tbank,tbankan,tnorek);
        
    }
    
    
    private void LastCode(){
        String buktiawal = "HR/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/1/"+Login.subdefault+"-");
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranPayroll where  ccode='"+tcode.getText()+"' and cnbkt like '%"+buktiawal+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText(buktiawal);
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
    } 
    private void loadData(){
        sql = "select * from TranPayroll where cborongan = 0 and cperiode like '%"+jComboBox2.getSelectedItem().toString().replaceAll("All", "")+"%' "
                + " order by id desc" ;
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("ccode"),
                    rs.getString("cnbkt"),rs.getString("cperiode"),
                    rs.getString("dstart"),rs.getString("dend"),
                    rs.getString("cacckas"),rs.getString("caccll"),
                    rs.getString("cpayrol"),rs.getString("cjab"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
    }
    private void loadEdit(){
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        sql = "select * from TranKasBank where ctran='"+idKas+"' and  cnbkt = '"+cnbkt+"' ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getInt("id"),rs.getString("cacckas"),rs.getString("caccll"),
                    rs.getString("cnote"),rs.getDouble("namnt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"), "yyyy-MM-dd"),
                    rs.getString("cnbkt"),
                    rs.getString("bank"),rs.getString("bank_an"),rs.getString("bank_rekening"),
                });
                
                btDelete.setText("Update");
                btDelete.setVisible(true);
                no++;
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
        hitungTotalTransaksi();
        
        for(int i=0; i<jTable1.getRowCount();i++){
            if(jTable1.getValueAt(i, 0).toString().equalsIgnoreCase(id)) {
                loadEditItem(i);
                return;
            }
        }
    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        id = jTable1.getValueAt(baris, 0).toString();
        tacc.setText(jTable1.getValueAt(baris, 1).toString());
        taccll.setText(jTable1.getValueAt(baris, 2).toString());
        jDateTran.setDate(rfa.StringtoDate(keyssDef,jTable1.getValueAt(baris, 5).toString(),"yyyy-MM-dd"));
        tbukti.setText(jTable1.getValueAt(baris,6).toString().substring(0, jTable1.getValueAt(baris,6).toString().length()-4));
        tbukti1.setText(jTable1.getValueAt(baris,6).toString().substring(jTable1.getValueAt(baris,6).toString().length()-4, jTable1.getValueAt(baris,6).toString().length()));
 
    }
    
    private void loadGaji(int save){
        if(rbGajiBulanan.isSelected()) {
            typePayroll = "MONTH";
        } else {
            typePayroll = "DAY";
        }
        
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        
        rfa.clear_table(MenuAdmin.keyssDef, jTable5);
        DefaultTableModel model1 = (DefaultTableModel) jTable5.getModel();
        
        
        sql="exec PAYROLL '"+tcode.getText()+"','"+rfa.DateString(keyssDef,jDate1.getDate(),"yyyy/MM/dd")+"','"+rfa.DateString(keyssDef,jDate2.getDate(),"yyyy/MM/dd")+"'";
        //System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model1.addRow(new Object[]{
                    rs.getString("cidk"),
                    rs.getString("Name")
                        
                });
                model.addRow(new Object[]{
                    rs.getString("cidk"),
                    rs.getString("Name"),
                    rs.getString("cjabatan"),
                    rs.getInt("ok"),
                    rs.getInt("a"),
                    rs.getInt("telat"),
                    rs.getInt("pcp"),
                    rs.getInt("lembur"),
                    rs.getInt("cuti"),
                    rs.getInt("sakit"),
                    rs.getInt("gp"),
                    rs.getInt("tunjangan"),
                    rs.getInt("tunjangan_komp"),
                    0, //tunjangan lain
                    rs.getInt("pot_bpjskerja"),
                    rs.getInt("pot_a"),
                    rs.getInt("pot_telat"),
                    rs.getInt("pot_pcp"),
                    rs.getInt("pot_sakit"),
                    rs.getInt("pot_pinjaman"),
                    0, //pph21
                    0, //pot lain lain
                    rs.getInt("gp_lembur"),
                    rs.getInt("gp_bonus"),
                    0,
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PayrollKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }

        TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns(); 
        koreksi();
        hitungTotalTransaksi();
        
    }
    private Boolean cekSave(){
        if(tacc1.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Account Bank Tidak Terdaftar", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(taccll1.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Account Kas Tidak Terdaftar", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        return true;
    }
    private void addData(){
        if(!cekSave()) {
            return;
        }
        
        
        taccll.requestFocus();
        
    }
    
    private void koreksi(){
        System.out.println("Update");
        for(int i=0; i<jTable1.getRowCount(); i++){
            Double gaji =  Double.valueOf(jTable1.getValueAt(i,10).toString())+
                        Double.valueOf(jTable1.getValueAt(i,11).toString())+
                        Double.valueOf(jTable1.getValueAt(i,12).toString())+
                        Double.valueOf(jTable1.getValueAt(i,13).toString());
            Double pot =   Double.valueOf(jTable1.getValueAt(i,14).toString())+
                        Double.valueOf(jTable1.getValueAt(i,15).toString())+
                        Double.valueOf(jTable1.getValueAt(i,16).toString())+
                        Double.valueOf(jTable1.getValueAt(i,17).toString())+
                        Double.valueOf(jTable1.getValueAt(i,18).toString())+
                        Double.valueOf(jTable1.getValueAt(i,19).toString())+
                        Double.valueOf(jTable1.getValueAt(i,20).toString())+
                        Double.valueOf(jTable1.getValueAt(i,21).toString());
            Double add =   Double.valueOf(jTable1.getValueAt(i,22).toString())+ 
                        Double.valueOf(jTable1.getValueAt(i,23).toString()); 
            jTable1.setValueAt(gaji-pot+add, i, 24);
        }
    }
    private void hitungTotalTransaksi(){
//        int nilaitotal = 0; 
//        for(int i=0; i<jTable1.getRowCount(); i++){
//            nilaitotal = Integer.valueOf(jTable1.getValueAt(i,24).toString());
//            
//        }
    }
    
    private int insertData(){
        int err= 0;
        int col=0;
        for(int i=0; i<jTable1.getRowCount();i++){
            col=0;
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
            listObj.put("ccode",tcode.getText());
            listObj.put("cidk",jTable1.getValueAt(i, col++).toString());
            listObj.put("name",jTable1.getValueAt(i, col++).toString());
            listObj.put("cjabatan",jTable1.getValueAt(i, col++).toString());
            listObj.put("ok",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("a",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("telat",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pcp",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("lembur",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("cuti",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("sakit",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("gp",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("tunjangan",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("tunjangan_komp",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("tunjangan_lain",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_bpjskerja",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_a",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_telat",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_pcp",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_sakit",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_pinjaman",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_pph21",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("pot_lain",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("gp_lembur",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("bonus",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));
            listObj.put("total",rfa.formatDecimal3.format(jTable1.getValueAt(i, col++)));

 
            if(con.queryUpdate(con.insertValue("TranPayrollD", listObj))<=0){
                err++;
            } 
        }
        
        return err;
    }
    private void insertDataAbs(){
        sql="select * from PayrollD where  ccode='"+tcode.getText()+"' and Tanggal>='"+rfa.DateString(keyssDef,jDate1.getDate(),"yyyy/MM/dd")+"' and Tanggal<='"+rfa.DateString(keyssDef,jDate2.getDate(),"yyyy/MM/dd")+"'";
        //System.out.println(sql);=
        rs = con.queryResult(sql);
        try {
            while(rs.next()){ 
                HashMap<String,String> listObj = new HashMap<String,String>();        
                listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
                listObj.put("ccode",tcode.getText());
                listObj.put("cidk",rs.getString("cidk"));
                listObj.put("Name",rs.getString("Name"));
                listObj.put("Tanggal",rs.getString("Tanggal").replaceAll("/", "-"));
                listObj.put("OnDuty",rs.getString("OnDuty"));
                listObj.put("OffDuty",rs.getString("OffDuty"));
                listObj.put("ClockIn",rs.getString("ClockIn"));
                listObj.put("ClockOut",rs.getString("ClockOut"));
                listObj.put("Late",rs.getString("Late"));
                listObj.put("Early",rs.getString("Early"));
                listObj.put("OK",rs.getString("OK"));
                listObj.put("A",rs.getString("A"));
                listObj.put("LIBUR",rs.getString("LIBUR"));
                listObj.put("HOLIDAY",rs.getString("HOLIDAY"));
                listObj.put("TELAT",rs.getString("TELAT"));
                listObj.put("TELAT_MENIT",rs.getString("TELAT_MENIT"));
                listObj.put("PCP",rs.getString("PCP"));
                listObj.put("PCP_MENIT",rs.getString("PCP_MENIT"));
                listObj.put("LEMBUR",rs.getString("LEMBUR"));
                listObj.put("LEMBUR_MENIT",rs.getString("LEMBUR_MENIT"));
                listObj.put("IJIN",rs.getString("IJIN"));
                listObj.put("IJIN_MENIT",rs.getString("IJIN_MENIT"));
                listObj.put("CUTI",rs.getString("CUTI"));
                listObj.put("SAKIT",rs.getString("SAKIT"));
                listObj.put("SAKIT_SDOKTER",rs.getString("SAKIT_SDOKTER"));

                sql=con.insertValue("TranPayrollDData", listObj);
                con.queryUpdate(sql);
            }
        } catch (SQLException ex) {
            Logger.getLogger(PayrollKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void save(){
        int cekdata=0;
        sql="select * from TranPayroll where ccode='"+tcode.getText()+"' and cjab='"+tjab.getText()+"' "
                + "and cperiode<='"+jComboBox1.getSelectedItem().toString()+jTextField1.getText()+"' "
                + "and dstart='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy/MM/dd")+"' "
                + "and dend='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy/MM/dd")+"' ";
        try {
            while(rs.next()){
                cekdata = 1;
            }
        } catch (SQLException ex) {
            Logger.getLogger(PayrollKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        if(cekdata == 1){
            JOptionPane.showMessageDialog(this,"Data sudah ada.", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("cacckas",tacc.getText());
        listObj.put("caccll",taccll.getText());
        listObj.put("cperiode",jComboBox1.getSelectedItem().toString()+jTextField1.getText());
        listObj.put("dstart",rfa.DateString(keyssDef, jDate1.getDate(), "yyyy/MM/dd"));
        listObj.put("dend",rfa.DateString(keyssDef, jDate2.getDate(), "yyyy/MM/dd"));
        listObj.put("cborongan","0");
        listObj.put("cpayrol",typePayroll);
        listObj.put("cjab",tjab.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("TranPayroll", listObj);
        if(con.queryUpdate(sql)>0){
            insertData();
            insertDataAbs();
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            if(pil == 0) btPrint.doClick();
            clear();
            deff();
            LastCode();
            loadData();
        } else {
            JOptionPane.showMessageDialog(this,"Sebagian Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
        
        
    }
    private void update(){
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranKasBank  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        if(insertData()==0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            if(pil == 0) btPrint.doClick();
            clear();
            deff();
            LastCode();
            loadData();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Mengubah Sebagian Data.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void delete(){
        int err=0;
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  TranKasBank  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
            }
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        if(err==0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clear();
            deff();
            LastCode();
            loadData();
            
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void delete_row(){
        if(!"-1".equals(id)) {
            deleteRow.add(id);
        }
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.removeRow(barisEdit);
        
        System.out.println(deleteRow);
    }
    
    private void print(){
        String tgl = "";
        Double total =0.0;
        sql = "select b.*,a.cperiode,a.dstart,a.dend,a.dtran from tranpayroll a join tranpayrolld b on a.cnbkt=b.cnbkt\n" +
            "where a.cnbkt = '"+cnbkt_print+"' and a.ccode='"+tcode.getText()+"'\n" +
            " ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                tgl = rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy");
                model.addRow(new Object[]{
                    rs.getString("cidk"),
                    rs.getString("name"),
                    "",rs.getString("cjabatan"),
                    rs.getDouble("gp"),
                    rs.getDouble("bonus"),
                    0.0,
                    rs.getDouble("gp_lembur"),
                    rs.getDouble("pot_pinjaman"),
                    rs.getDouble("pot_lain"),
                    rs.getDouble("total"),
                    0.0,
                    0.0,
                    rs.getString("cperiode"),
                    rs.getString("dstart"),
                    rs.getString("dend"),
                    
                    rs.getDouble("tunjangan"),
                    rs.getDouble("tunjangan_komp"),
                    rs.getDouble("tunjangan_lain"),
                    
                    rs.getDouble("pot_bpjskerja"),
                    rs.getDouble("pot_pph21"),
                    rs.getDouble("pot_a")+rs.getDouble("pot_telat")+rs.getDouble("pot_pcp")+
                            rs.getDouble("pot_sakit"), //kehadiran
                    rs.getDouble("pot_lain"),
                    
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable3.getModel());
            String treport = "report/TranPayrollStaf.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("periode", tgl);
            param.put("terbilang", rfa.Pembilang(total).toUpperCase()+" RUPIAH" );
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

        buttonGroup1 = new javax.swing.ButtonGroup();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        tacc = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        jLabel7 = new javax.swing.JLabel();
        taccll = new javax.swing.JTextField();
        jButton6 = new javax.swing.JButton();
        jButton12 = new javax.swing.JButton();
        tacc1 = new javax.swing.JTextField();
        taccll1 = new javax.swing.JTextField();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jButton7 = new javax.swing.JButton();
        jLabel18 = new javax.swing.JLabel();
        jComboBox2 = new javax.swing.JComboBox();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        btPrint = new javax.swing.JButton();
        btHitung = new javax.swing.JButton();
        jLabel14 = new javax.swing.JLabel();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jLabel16 = new javax.swing.JLabel();
        jDate1 = new com.toedter.calendar.JDateChooser();
        jLabel17 = new javax.swing.JLabel();
        jComboBox1 = new javax.swing.JComboBox();
        tcode = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        rbGajiBulanan = new javax.swing.JRadioButton();
        rbGajiHari = new javax.swing.JRadioButton();
        jTextField1 = new javax.swing.JTextField();
        jLabel9 = new javax.swing.JLabel();
        tjab = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        tjab1 = new javax.swing.JTextField();
        jScrollPane4 = new javax.swing.JScrollPane();
        jTable4 = new javax.swing.JTable();
        btCancel = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btDelete = new javax.swing.JButton();
        infoTotal = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
        jScrollPane5 = new javax.swing.JScrollPane();
        jTable5 = new javax.swing.JTable();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "nik", "nama", "dept", "jab", "gaji", "bonus", "thr", "lembur", "pinjaman", "potongan", "totalstaff", "linting", "totalborongan", "periode", "dstart", "dend", "tunjjab", "tunjkomp", "tunjlain", "bpjskerja", "pph21", "kehadiran", "potlain"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, true, true, false, false, false, false, false, false, true, true, true, true, true, true, true, true, true, true
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable3.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable3.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jTable3PropertyChange(evt);
            }
        });
        jScrollPane3.setViewportView(jTable3);

        tacc.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                taccFocusLost(evt);
            }
        });
        tacc.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                taccKeyPressed(evt);
            }
        });

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Account Debet");

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Account Beban ");

        taccll.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                taccllFocusLost(evt);
            }
        });
        taccll.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                taccllKeyPressed(evt);
            }
        });

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });

        tacc1.setEditable(false);
        tacc1.setBackground(new java.awt.Color(153, 153, 255));

        taccll1.setEditable(false);
        taccll1.setBackground(new java.awt.Color(153, 153, 255));

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Payroll Karyawan");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 20, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 30, 90, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Periode");
        jPanel6.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 70, 20));

        jComboBox2.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "All", "Januari", "Februari", "Maret", "April", "Mei ", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember" }));
        jPanel6.add(jComboBox2, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 30, 180, -1));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Gudang", "Nomor Bukti", "Periode", "Tgl Awal", "Tgl Akhir", "Akun Debet", "Akun Kredit", "Payrol", "Jabatan"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false
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
        }

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField11.setText("Double Click to Edit");

        jTextField12.setEditable(false);
        jTextField12.setBackground(new java.awt.Color(204, 204, 255));
        jTextField12.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField12.setText("Total Data : 100");

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1204, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 76, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 346, Short.MAX_VALUE)
                .addGap(18, 18, 18)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Log");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Transaksi");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 40, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Bukti");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, 72, 20));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Jabatan");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(880, 10, 72, 20));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(351, 70, 130, -1));

        btHitung.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        btHitung.setText("Hitung");
        btHitung.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btHitungActionPerformed(evt);
            }
        });
        jPanel1.add(btHitung, new org.netbeans.lib.awtextra.AbsoluteConstraints(690, 130, 100, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel14.setText("s/d");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 40, 30, 20));

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
        jPanel1.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 70, 140, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Bulan");
        jPanel1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 100, 100, 20));

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
        jPanel1.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 40, 140, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Periode CutOff");
        jPanel1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 40, 100, 20));

        jComboBox1.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Januari", "Februari", "Maret", "April", "Mei ", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember" }));
        jPanel1.add(jComboBox1, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 100, 140, -1));
        jPanel1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 10, 90, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 10, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 10, 200, -1));

        jDateTran.setDateFormatString("dd/MM/yyyy");
        jDateTran.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateTranPropertyChange(evt);
            }
        });
        jDateTran.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateTranKeyPressed(evt);
            }
        });
        jPanel1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 40, 180, -1));

        tbukti.setEditable(false);
        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 70, 90, -1));

        tbukti1.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbukti1FocusLost(evt);
            }
        });
        tbukti1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbukti1KeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tbukti1KeyTyped(evt);
            }
        });
        jPanel1.add(tbukti1, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 70, 60, -1));

        rbGajiBulanan.setText("Bulanan");
        rbGajiBulanan.addItemListener(new java.awt.event.ItemListener() {
            public void itemStateChanged(java.awt.event.ItemEvent evt) {
                rbGajiBulananItemStateChanged(evt);
            }
        });
        rbGajiBulanan.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                rbGajiBulananMouseClicked(evt);
            }
        });
        rbGajiBulanan.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                rbGajiBulananPropertyChange(evt);
            }
        });
        jPanel1.add(rbGajiBulanan, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 10, -1, -1));

        rbGajiHari.setText("Harian");
        rbGajiHari.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                rbGajiHariMouseClicked(evt);
            }
        });
        jPanel1.add(rbGajiHari, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 10, -1, -1));

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(153, 153, 255));
        jTextField1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(jTextField1, new org.netbeans.lib.awtextra.AbsoluteConstraints(790, 100, 60, -1));

        jLabel9.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel9.setText("Type Salary");
        jPanel1.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 10, 72, 20));

        tjab.setEditable(false);
        tjab.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tjabFocusLost(evt);
            }
        });
        jPanel1.add(tjab, new org.netbeans.lib.awtextra.AbsoluteConstraints(950, 10, 70, -1));

        jButton1.setText("...");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(1020, 10, 30, -1));

        tjab1.setEditable(false);
        tjab1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(tjab1, new org.netbeans.lib.awtextra.AbsoluteConstraints(1050, 10, 140, -1));

        jTable4.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null},
                {null, null},
                {null, null},
                {null, null}
            },
            new String [] {
                "Jabatan", "Nama Jabatan"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane4.setViewportView(jTable4);

        jPanel1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(880, 40, 310, 110));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });

        infoTotal.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        infoTotal.setText(" ");
        infoTotal.setToolTipText("");

        jTable5.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null},
                {null, null},
                {null, null},
                {null, null}
            },
            new String [] {
                "Nik", "Nama"
            }
        ));
        jTable5.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable5.addComponentListener(new java.awt.event.ComponentAdapter() {
            public void componentHidden(java.awt.event.ComponentEvent evt) {
                jTable5ComponentHidden(evt);
            }
        });
        jScrollPane5.setViewportView(jTable5);

        jScrollPane1.addMouseMotionListener(new java.awt.event.MouseMotionAdapter() {
            public void mouseDragged(java.awt.event.MouseEvent evt) {
                jScrollPane1MouseDragged(evt);
            }
        });
        jScrollPane1.addInputMethodListener(new java.awt.event.InputMethodListener() {
            public void caretPositionChanged(java.awt.event.InputMethodEvent evt) {
                jScrollPane1CaretPositionChanged(evt);
            }
            public void inputMethodTextChanged(java.awt.event.InputMethodEvent evt) {
            }
        });
        jScrollPane1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jScrollPane1PropertyChange(evt);
            }
        });

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "NIK", "Nama", "Jabatan", "Absensi OK", "Absen/Alpha", "Telat", "PCP", "Lembur", "Cuti Khusus", "Sakit", "Gaji Pokok", "Tunjangan", "Tunjangan Kompetensi", "Tunjangan Lain-lain", "Pot BPJS Ket", "Pot Alpha", "Pot Telat", "Pot PCP", "Pot Sakit", "Pot Pinjaman", "Pot PPh 21", "Pot Lain-lain", "Nilai Lembur", "Bonus", "Total Akhir"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false, true, false, false, true, false, false, true, true, true, false, true, false
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
        jTable1.addComponentListener(new java.awt.event.ComponentAdapter() {
            public void componentMoved(java.awt.event.ComponentEvent evt) {
                jTable1ComponentMoved(evt);
            }
        });
        jTable1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jTable1PropertyChange(evt);
            }
        });
        jTable1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jTable1KeyPressed(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel3Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jScrollPane5, javax.swing.GroupLayout.PREFERRED_SIZE, 282, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, 0)
                .addComponent(jScrollPane1)
                .addContainerGap())
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel3Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane5, javax.swing.GroupLayout.DEFAULT_SIZE, 239, Short.MAX_VALUE)
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE))
                .addContainerGap())
        );

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel3, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addGap(462, 462, 462)
                        .addComponent(infoTotal)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, 445, Short.MAX_VALUE)
                        .addComponent(btDelete, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(btSave, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(btCancel, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addContainerGap())))
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addGap(18, 18, 18)
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 163, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jPanel3, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(btCancel)
                    .addComponent(btSave)
                    .addComponent(btDelete)
                    .addComponent(infoTotal, javax.swing.GroupLayout.PREFERRED_SIZE, 20, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("Add/Edit", jPanel5);

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
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(18, 18, 18)
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jDate2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2PropertyChange

    private void jDate2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate2KeyPressed

    private void jDate1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDate1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1PropertyChange

    private void jDate1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDate1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDate1KeyPressed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btDelete.getText().equals("Save")) LastCode();
        jTextField1.setText(rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy"));
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 4, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed
        if(tacc.isEnabled()) rfa.EnterNextFocus(evt, tacc);
        else if(taccll.isEnabled()) rfa.EnterNextFocus(evt, taccll);
    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void taccFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccFocusLost
        sql = "select * from maccount where caccnt = '"+tacc.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, tacc1);
            while(rs.next()){  tacc1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }

    }//GEN-LAST:event_taccFocusLost

    private void taccKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccKeyPressed
        rfa.EnterNextFocus(evt, taccll);
    }//GEN-LAST:event_taccKeyPressed

    private void taccllFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccllFocusLost
        sql = "select * from maccount where caccnt = '"+taccll.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, taccll1);
            while(rs.next()){  taccll1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_taccllFocusLost

    private void taccllKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccllKeyPressed
        
    }//GEN-LAST:event_taccllKeyPressed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            taccll.setText(a.caccnt);
            taccll1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            tacc.setText(a.caccnt);
            tacc1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void rbGajiBulananMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_rbGajiBulananMouseClicked
        typePayroll = "MONTH";
    }//GEN-LAST:event_rbGajiBulananMouseClicked

    private void rbGajiHariMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_rbGajiHariMouseClicked
        typePayroll = "DAY";
    }//GEN-LAST:event_rbGajiHariMouseClicked

    private void btHitungActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btHitungActionPerformed
        if(jTable4.getRowCount() ==0) {
            JOptionPane.showMessageDialog(this,"Jabatan Wajib Di isi", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
//        if(tjab.getText().isEmpty()) {
//            JOptionPane.showMessageDialog(this,"Jabatan Wajib Di isi", "Save", JOptionPane.ERROR_MESSAGE);
//            return;
//        }
        loadGaji(0);
    }//GEN-LAST:event_btHitungActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    LastCode();
                    Timer t = new Timer();
                    t.schedule(new TimerTask() {
                        @Override
                        public void run() {
                        }
                    }, 1); 
                    save();
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void jTable1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jTable1PropertyChange
        try{
            koreksi();
            hitungTotalTransaksi();
        } catch (Exception e){}
    }//GEN-LAST:event_jTable1PropertyChange

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount()==3){
            int baris = jTable2.getSelectedRow();
            DialogDataPayroll a = new DialogDataPayroll(jTable2.getValueAt(baris, 2).toString(), jTable2.getValueAt(baris,1).toString());
            a.setVisible(true);
            if(a.status) {
                cnbkt_print = jTable2.getValueAt(baris, 2).toString();
                if(a.id.equalsIgnoreCase("PRINT")) {
                    print();
                } else if(a.id.equalsIgnoreCase("DELETE")) {
                    sql="delete from tranpayroll where ccode='"+jTable2.getValueAt(baris,1).toString()+"' and cnbkt='"+jTable2.getValueAt(baris, 2).toString()+"' ";
                    con.queryUpdate(sql);
                    JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            
                    loadData();
                }
            }
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_btDeleteActionPerformed

    private void rbGajiBulananPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_rbGajiBulananPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_rbGajiBulananPropertyChange

    private void rbGajiBulananItemStateChanged(java.awt.event.ItemEvent evt) {//GEN-FIRST:event_rbGajiBulananItemStateChanged
        // TODO add your handling code here:
    }//GEN-LAST:event_rbGajiBulananItemStateChanged

    private void jTable3PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jTable3PropertyChange

    }//GEN-LAST:event_jTable3PropertyChange

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
       print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void tjabFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tjabFocusLost
        sql="select * from mdepartemen where id_dept = '"+tjab.getText()+"' ";
        rs= con.queryResult(sql);
        try {
            while(rs.next()){
                tjab1.setText(rs.getString("cdept"));
            }
        } catch (Exception e){

        }
    }//GEN-LAST:event_tjabFocusLost

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        DialogJabatan a = new DialogJabatan();
        a.setVisible(true);
        if(a.status) {
//            tjab.setText(a.classid);
//            tjab1.setText(a.classname);
            rfa.clear_table(MenuAdmin.keyssDef, jTable4);
            DefaultTableModel model = (DefaultTableModel) jTable4.getModel();
            for(int i=0; i<a.jab.size(); i++){
                model.addRow(new Object[]{
                    a.jab.get(i),a.jabname.get(i)
                });
            }
        }
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jTable1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTable1KeyPressed
        if(evt.getKeyCode() == 10) {
            try{
                koreksi();
                hitungTotalTransaksi();
            } catch (Exception e){}
        }
    }//GEN-LAST:event_jTable1KeyPressed

    private void jScrollPane1CaretPositionChanged(java.awt.event.InputMethodEvent evt) {//GEN-FIRST:event_jScrollPane1CaretPositionChanged
        
    }//GEN-LAST:event_jScrollPane1CaretPositionChanged

    private void jScrollPane1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jScrollPane1PropertyChange
        
    }//GEN-LAST:event_jScrollPane1PropertyChange

    private void jScrollPane1MouseDragged(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jScrollPane1MouseDragged
        
    }//GEN-LAST:event_jScrollPane1MouseDragged

    private void jTable1ComponentMoved(java.awt.event.ComponentEvent evt) {//GEN-FIRST:event_jTable1ComponentMoved
        jScrollPane5.getVerticalScrollBar().setValue(jScrollPane1.getVerticalScrollBar().getValue());
        
//        jTable5.setFocu 
    }//GEN-LAST:event_jTable1ComponentMoved

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        jTable5.changeSelection(jTable1.getSelectedRow(), 0, true, false);
        if(evt.getClickCount() == 2){
            int baris = jTable1.getSelectedRow();
            DialogDataPayrollKaryawan a = new DialogDataPayrollKaryawan(jTable1.getValueAt(baris, 0).toString(),"",tcode.getText(),
                                                                        rfa.DateString(keyssDef,jDate1.getDate(),"yyyy/MM/dd"),
                                                                        rfa.DateString(keyssDef,jDate2.getDate(),"yyyy/MM/dd"));
            a.setVisible(true);
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void jTable5ComponentHidden(java.awt.event.ComponentEvent evt) {//GEN-FIRST:event_jTable5ComponentHidden
        jScrollPane1.getVerticalScrollBar().setValue(jScrollPane5.getVerticalScrollBar().getValue());
    }//GEN-LAST:event_jTable5ComponentHidden


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHitung;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.ButtonGroup buttonGroup1;
    private javax.swing.JLabel infoTotal;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JComboBox jComboBox1;
    private javax.swing.JComboBox jComboBox2;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTable jTable4;
    private javax.swing.JTable jTable5;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JRadioButton rbGajiBulanan;
    private javax.swing.JRadioButton rbGajiHari;
    private javax.swing.JTextField tacc;
    private javax.swing.JTextField tacc1;
    private javax.swing.JTextField taccll;
    private javax.swing.JTextField taccll1;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tjab;
    private javax.swing.JTextField tjab1;
    // End of variables declaration//GEN-END:variables

  
}
