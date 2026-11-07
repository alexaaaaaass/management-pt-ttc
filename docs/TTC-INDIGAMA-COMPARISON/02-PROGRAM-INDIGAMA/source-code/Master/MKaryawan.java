/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Master;

import Accounting.*;
import Delivery.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogJab;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Report.RAnalisaTransaksi;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
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
import javax.swing.JOptionPane;
import javax.swing.JTextField;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class MKaryawan extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="MASTER KARYAWAN";
    ArrayList<String> cjabatanid;
    ArrayList<String> cjabatanid_cetak;
    ArrayList<String> cptkp;
    ArrayList<String> cum;
    ArrayList<String> clembur;
    String typePayroll="";
    
    public MKaryawan(String mode) {
        initComponents();
        push_button();
        cjabatanid = new ArrayList<>();
        cjabatanid_cetak = new ArrayList<>();
        cptkp = new ArrayList<>();
        cum = new ArrayList<>();
        clembur = new ArrayList<>();
        clear();
        deff();
        loadData();
        loadJabatan();
        loadPTKP();
        loadLembur();
    }
    
    private void push_button(){
        InputMap im = this.getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = this.getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F12, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                 btSave.doClick();
            }
        });
    }
     
    private void clear(){
        DateResign.setDate(null);
        DateLahir.setDate(null);
        tbpjstanggal.setDate(null);
        tnpwptanggal.setDate(null);
        tket.setText("");
        rfa.empty(keyssDef,tnik,tnamakaryawan,tktp,ttelp,tagama,
                talamat,tkota,tkec,tdesa,tkodepos,
                talamatktp,tkotaktp,tkecktp,tdesaktp,tkodeposktp,
                tgaji,ttunjjab,
                tnpwpnama,tnpwpalamat,tnpwpnomor,
                tbank,tbankan1,tbankrek,
                tbpjscabang,tbpjskes,tbpjsketeranagakerjaan,tbpjsnama,
                tfinger,tagama,tjab,tjab_kode,tdept,ttunjkomp,
                ttunjintensif
                
                );
        rbGajiBulanan.setSelected(false);
        rbGajiHari.setSelected(false);
        cbBorongan.setSelected(false);
        
        
    } 
    private void deff(){
        GroupSalary.add(rbGajiBulanan);
        GroupSalary.add(rbGajiHari);
        btDelete.setVisible(false);
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        rbGajiHari.setSelected(false);
        rbGajiBulanan.setSelected(false);
        id = "";
        DateMasuk.setDate(new Date());
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        lastNikKaryawan();
        
        for(int i=1; i<jTable3.getColumnCount(); i++){
            jTable3.getColumnModel().getColumn(i).setMinWidth(150);
        }
        
    }

    private void loadJabatan(){
        cjabatanid.clear();
        cjabatanid_cetak.clear(); 
        tjabatan1.removeAllItems();
        sql = "select a.cdept,b.id_jab,b.cjabatan from MDepartemen a join MJabatan b on a.id_dept=b.id_dept\n" +
        "order by a.cdept,b.cjabatan";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){ 
                tjabatan1.addItem(rs.getString("cdept")+" - "+rs.getString("cjabatan"));
                cjabatanid.add(rs.getString("id_jab"));
                cjabatanid_cetak.add(rs.getString("id_jab"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(MKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadPTKP(){
        cptkp.clear();
        tptkp.removeAllItems();
        sql = "select * from mptkp";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tptkp.addItem(rs.getString("kode")+" - "+rs.getString("golongan"));
                cptkp.add(rs.getString("kode"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(MKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadLembur(){
        clembur.clear();
        tlembur.removeAllItems();
        sql = "select * from mpayroll where flag = 'LEMBUR'";
        tlembur.addItem("");
        clembur.add("");
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tlembur.addItem(rs.getString("kode")+" - "+rs.getString("cdesc"));
                clembur.add(rs.getString("kode"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(MKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void lastNikKaryawan(){
        String kode="";
        int last=0;
        rs = con.queryResult("select max(convert(int,right(nik,2))) as lastno from MKaryawan where nik like '%"+kode+rfa.DateString(keyssDef, DateMasuk.getDate(),"yyMMdd")+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        tnik.setText(kode+rfa.DateString(keyssDef, DateMasuk.getDate(),"yyMMdd")+rfa.NumberBukti(MenuAdmin.keyssDef,3, last+1));
    } 
    private void loadEdit(){
        clear();
        sql="select b.cjabatan as namajab,c.cdept,a.* from mkaryawan a \n" +
            "left join MJabatan b on a.cjabatan=b.id_jab\n" +
            "left join MDepartemen c on b.id_dept=c.id_dept\n" +
            "where a.id = '"+id+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                btSave.setText("Update");
                btDelete.setVisible(true);
                
                try { tnik.setText(rs.getString("nik"));  } catch(Exception ee){}
                try { tnamakaryawan.setText(rs.getString("cname")); } catch(Exception ee){}
                try { tktp.setText(rs.getString("cktp")); } catch(Exception ee){}
                try { DateMasuk.setDate(rs.getDate("ctgl_masuk")); } catch(Exception ee){}
                try { DateLahir.setDate(rs.getDate("ctgl_lahir")); } catch(Exception ee){}
                try { tfinger.setText(rs.getString("cid_finger")); } catch(Exception ee){}
                
                try { tagama.setText(rs.getString("creligy")); } catch(Exception ee){}
                
                try { talamat.setText(rs.getString("caddr")); } catch(Exception ee){}
                try { tkota.setText(rs.getString("ccity")); } catch(Exception ee){}
                try { tkec.setText(rs.getString("ckecamatan")); } catch(Exception ee){}
                try { tdesa.setText(rs.getString("cdesa")); } catch(Exception ee){}
                try { tkodepos.setText(rs.getString("cpostalcode")); } catch(Exception ee){}
                try { talamatktp.setText(rs.getString("caddr_ktp")); } catch(Exception ee){}
                try { tkotaktp.setText(rs.getString("ccity_ktp")); } catch(Exception ee){}
                try { tkecktp.setText(rs.getString("ckecamatan_ktp")); } catch(Exception ee){}
                try { tdesaktp.setText(rs.getString("cdesa_ktp")); } catch(Exception ee){}
                try { tkodeposktp.setText(rs.getString("cpostalcode_ktp")); } catch(Exception ee){}
                try { ttelp.setText(rs.getString("ctelp")); } catch(Exception ee){}
                try { typePayroll = (rs.getString("cpayrol")); } catch(Exception ee){}
                
                try { tgaji.setText(rs.getString("csalary")); } catch(Exception ee){}
                try { ttunjjab.setText(rs.getString("ctunjjab")); } catch(Exception ee){}
                try { ttunjkomp.setText(rs.getString("ctunjkomp")); } catch(Exception ee){}
                try { ttunjintensif.setText(rs.getString("ctunjintensif")); } catch(Exception ee){}
                try { tbank.setText(rs.getString("cbank")); } catch(Exception ee){}
                try { tbankan1.setText(rs.getString("cbank_an")); } catch(Exception ee){}
                try { tbankrek.setText(rs.getString("cbank_number")); } catch(Exception ee){}
                try { tbpjsnama.setText(rs.getString("cbpjs_name")); } catch(Exception ee){}
                try { tbpjskes.setText(rs.getString("cbpjs_kesehatan")); } catch(Exception ee){}
                try { tbpjsketeranagakerjaan.setText(rs.getString("cbpjs_ketenagakerjaan")); } catch(Exception ee){}
                try { tbpjscabang.setText(rs.getString("cbpjs_cabang")); } catch(Exception ee){}
                try { tbpjstanggal.setDate(rs.getDate("cbpjs_tgl")); } catch(Exception ee){}
                try { DateResign.setDate(rs.getDate("cresign")); } catch(Exception ee){}
                try { tket.setText(rs.getString("cnote")); } catch(Exception ee){}
                try { tcode.setText(rs.getString("ccode")); } catch(Exception ee){}
                try { tnpwpnama.setText(rs.getString("cnpwp_name")); } catch(Exception ee){}
                try { tnpwpalamat.setText(rs.getString("cnpwp_alamat")); } catch(Exception ee){}
                try { tnpwpnomor.setText(rs.getString("cnpwp_no")); } catch(Exception ee){}
                try { tnpwptanggal.setDate(rs.getDate("cnpwp_tgl")); } catch(Exception ee){}

                
                try { tgander.setSelectedItem(rs.getString("cgander")); } catch(Exception ee){}
                try { tmaried.setSelectedItem(rs.getString("cmarried")); } catch(Exception ee){}
                
                try { 
                    tjab.setText(rs.getString("namajab"));
                    tjab_kode.setText(rs.getString("cjabatan"));
                    tdept.setText(rs.getString("cdept"));
                } catch(Exception ee){ee.printStackTrace();}
                
                try { 
                for(int i=0; i<clembur.size(); i++){
                    if(clembur.get(i).equalsIgnoreCase(rs.getString("clembur"))) {
                        tlembur.setSelectedIndex(i);
                    }
                }
                } catch(Exception ee){ee.printStackTrace();}
                
                try { 
                    if(rs.getInt("cborongan") == 1) cbBorongan.setSelected(true);
                } catch(Exception ee){ee.printStackTrace();}
                
                
                
                
                try { 
                    rbGajiHari.setSelected(false);
                    rbGajiBulanan.setSelected(false);
                    if(rs.getString("cpayrol").equals("DAY")) {
                        rbGajiHari.setSelected(true);
                    } else if(rs.getString("cpayrol").equals("MONTH")) {
                        rbGajiBulanan.setSelected(true);
                    } 
                } catch(Exception ee){ee.printStackTrace();}
                
                
                
                
                jTabbedPane1.setSelectedIndex(1);
                
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(MKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
        
    }
    
    private void loadData(){
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        sql = "select * from MKaryawan where cname like '%"+jTextField10.getText()+"%' order by nik";
        sql="select b.cjabatan as namajab,c.cdept,a.* from mkaryawan a \n" +
            "left join MJabatan b on a.cjabatan=b.id_jab\n" +
            "left join MDepartemen c on b.id_dept=c.id_dept\n" +
            "where a.cname like '%"+jTextField10.getText()+"%' order by a.nik";
        rs = con.queryResult(sql);
        try {
            
            while(rs.next()){
                String statuss="Tidak Aktif";
                if(rs.getDate("cresign") ==null) {
                    statuss = "Aktif";
                }
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("ccode"),
                    rs.getString("nik"),
                    rs.getString("cid_finger"),
                    rs.getString("cname"),
                    rs.getString("caddr"),
                    rs.getString("namajab"),
                    rs.getString("cborongan"),
                    rs.getString("creligy"),
                    rs.getString("cktp"),
                    rs.getString("ctgl_masuk"),
                    rs.getString("cresign"),
                    statuss,
                    rfa.DateString(keyssDef, rs.getDate("ServerDate"), "dd/MM/yyyy")+" "+rs.getString("ServerDate").substring(11, 19),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(MKaryawan.class.getName()).log(Level.SEVERE, null, ex);
        }
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable3);
        tca.adjustColumns(); 
        jTextField12.setText("Total Data : "+jTable3.getRowCount());
    }
    private HashMap<String, String> dataSave(){
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("nik",tnik.getText());
        listObj.put("cname",tnamakaryawan.getText());
        listObj.put("cktp",tktp.getText());
        listObj.put("ctgl_masuk",rfa.DateString(keyssDef, DateMasuk.getDate(), "yyyy-MM-dd"));
        if(DateLahir.getDate() != null) listObj.put("ctgl_lahir",rfa.DateString(keyssDef, DateLahir.getDate(), "yyyy-MM-dd"));
        listObj.put("cid_finger",tfinger.getText());
        listObj.put("cgander",tgander.getSelectedItem().toString());
        listObj.put("creligy",tagama.getText());
        listObj.put("cmarried",tmaried.getSelectedItem().toString());
        //listObj.put("cbangsa",);
        listObj.put("caddr",talamat.getText());
        listObj.put("ccity",tkota.getText());
        listObj.put("ckecamatan",tkec.getText());
        listObj.put("cdesa",tdesa.getText());
        listObj.put("cpostalcode",tkodepos.getText());
        listObj.put("caddr_ktp",talamatktp.getText());
        listObj.put("ccity_ktp",tkotaktp.getText());
        listObj.put("ckecamatan_ktp",tkecktp.getText());
        listObj.put("cdesa_ktp",tdesaktp.getText());
        listObj.put("cpostalcode_ktp",tkodeposktp.getText());
        listObj.put("ctelp",ttelp.getText());
        listObj.put("cjabatan",tjab_kode.getText());
        listObj.put("cpayrol",typePayroll);
        listObj.put("clembur",clembur.get(tlembur.getSelectedIndex()));
        listObj.put("csalary",tgaji.getText());
        listObj.put("ctunjjab",ttunjjab.getText());
        listObj.put("ctunjkomp",ttunjkomp.getText());
        listObj.put("ctunjintensif",ttunjintensif.getText());
        //listObj.put("pph21",);
        listObj.put("cbank",tbank.getText());
        listObj.put("cbank_an",tbankan1.getText());
        listObj.put("cbank_number",tbankrek.getText());
        listObj.put("cbpjs_name",tbpjsnama.getText());
        listObj.put("cbpjs_kesehatan",tbpjskes.getText());
        listObj.put("cbpjs_ketenagakerjaan",tbpjsketeranagakerjaan.getText());
        listObj.put("cbpjs_cabang",tbpjscabang.getText());
        if(tbpjstanggal.getDate() != null)  listObj.put("cbpjs_tgl",rfa.DateString(keyssDef, tbpjstanggal.getDate(), "yyyy-MM-dd"));
        
        if(DateResign.getDate() != null) listObj.put("cresign",rfa.DateString(keyssDef, DateResign.getDate(), "yyyy-MM-dd"));
        
        listObj.put("cnote",tket.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("cnpwp_name",tnpwpnama.getText());
        listObj.put("cnpwp_alamat",tnpwpalamat.getText());
        listObj.put("cnpwp_no",tnpwpnomor.getText());
        if(tnpwptanggal.getDate() != null)  listObj.put("cnpwp_tgl",rfa.DateString(keyssDef, tnpwptanggal.getDate(), "yyyy_MM-dd"));
        
        if(cbBorongan.isSelected()) listObj.put("cborongan","1");
        else                        listObj.put("cborongan","0");
        
        
        if(btSave.getText().equals("Save")) {
            listObj.put("cusername",Login.user);
        } else {
//            listObj.put("cusername_update",Login.user);
        }
        return listObj;
    }
    private void save(){
        sql=con.insertValue("MKaryawan", dataSave());
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            btCancel.doClick();
        } else {
            JOptionPane.showMessageDialog(this,"Sebagian Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void update(){;
        sql=con.updateValue("MKaryawan", dataSave());
        sql+=" where id = '"+id+"'";
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            btCancel.doClick();
        } else {
            JOptionPane.showMessageDialog(this,"Sebagian Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void delete(){
        sql="delete from  MKaryawan   where id = '"+id+"' ";
           
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clear();
            deff();
            lastNikKaryawan();
            loadData();
            
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    
    private void cetakcard(){
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rfa.clear_table(keyssDef, jTable1);

        sql="select * from mkaryawan where cresign is null and cjabatan= '"+cjabatanid_cetak.get(tjabatan1.getSelectedIndex())+"' and ccode='"+tcodes.getText()+"'\n" +
            "order by cname";
        
        System.out.println(sql);
        rs = con.queryResult(sql);
        int count=0;
        try {
            while(rs.next()){
                count++;
                model.addRow(new Object[]{rs.getString("nik"),rs.getString("cname")});
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaTransaksi.class.getName()).log(Level.SEVERE, null, ex);
        }
        if(count<=0){
           JOptionPane.showMessageDialog(this,"Tidak ada data", "Info", JOptionPane.INFORMATION_MESSAGE);
           return;
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable1.getModel());
            String treport = "report/NameCard.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", tcode1.getText());
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
        java.awt.GridBagConstraints gridBagConstraints;

        GroupLembur = new javax.swing.ButtonGroup();
        GroupSalary = new javax.swing.ButtonGroup();
        jLabel40 = new javax.swing.JLabel();
        tlembur = new javax.swing.JComboBox();
        jButton5 = new javax.swing.JButton();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel12 = new javax.swing.JLabel();
        jTextField9 = new javax.swing.JTextField();
        jTextField10 = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jPanel1 = new javax.swing.JPanel();
        jPanel3 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        tnik = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        tdesa = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        jLabel19 = new javax.swing.JLabel();
        talamat = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        tkota = new javax.swing.JTextField();
        tkec = new javax.swing.JTextField();
        jLabel23 = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        tkodepos = new javax.swing.JTextField();
        jLabel26 = new javax.swing.JLabel();
        jLabel27 = new javax.swing.JLabel();
        ttelp = new javax.swing.JTextField();
        tbank = new javax.swing.JTextField();
        jLabel29 = new javax.swing.JLabel();
        jLabel30 = new javax.swing.JLabel();
        jScrollPane4 = new javax.swing.JScrollPane();
        tket = new javax.swing.JTextArea();
        jLabel13 = new javax.swing.JLabel();
        tnamakaryawan = new javax.swing.JTextField();
        tptkp = new javax.swing.JComboBox();
        jLabel14 = new javax.swing.JLabel();
        tktp = new javax.swing.JTextField();
        jLabel31 = new javax.swing.JLabel();
        jLabel32 = new javax.swing.JLabel();
        jLabel33 = new javax.swing.JLabel();
        tgaji = new javax.swing.JTextField();
        tbpjsnama = new javax.swing.JTextField();
        jLabel35 = new javax.swing.JLabel();
        tbpjstanggal = new com.toedter.calendar.JDateChooser();
        jLabel16 = new javax.swing.JLabel();
        jLabel24 = new javax.swing.JLabel();
        jLabel21 = new javax.swing.JLabel();
        jLabel36 = new javax.swing.JLabel();
        jLabel37 = new javax.swing.JLabel();
        jLabel38 = new javax.swing.JLabel();
        tkodeposktp = new javax.swing.JTextField();
        tdesaktp = new javax.swing.JTextField();
        tkecktp = new javax.swing.JTextField();
        tkotaktp = new javax.swing.JTextField();
        talamatktp = new javax.swing.JTextField();
        jLabel39 = new javax.swing.JLabel();
        tbankrek = new javax.swing.JTextField();
        tdefoultpayroll = new javax.swing.JComboBox();
        jLabel41 = new javax.swing.JLabel();
        tbpjskes = new javax.swing.JTextField();
        jLabel42 = new javax.swing.JLabel();
        tbpjsketeranagakerjaan = new javax.swing.JTextField();
        jLabel43 = new javax.swing.JLabel();
        tbpjscabang = new javax.swing.JTextField();
        DateMasuk = new com.toedter.calendar.JDateChooser();
        jLabel44 = new javax.swing.JLabel();
        jLabel17 = new javax.swing.JLabel();
        DateResign = new com.toedter.calendar.JDateChooser();
        jSeparator1 = new javax.swing.JSeparator();
        jSeparator2 = new javax.swing.JSeparator();
        jButton3 = new javax.swing.JButton();
        jButton4 = new javax.swing.JButton();
        jLabel45 = new javax.swing.JLabel();
        rbGajiHari = new javax.swing.JRadioButton();
        rbGajiBulanan = new javax.swing.JRadioButton();
        jLabel46 = new javax.swing.JLabel();
        jLabel47 = new javax.swing.JLabel();
        tnpwpnama = new javax.swing.JTextField();
        jLabel48 = new javax.swing.JLabel();
        tnpwpalamat = new javax.swing.JTextField();
        jLabel49 = new javax.swing.JLabel();
        tnpwptanggal = new com.toedter.calendar.JDateChooser();
        tnpwpnomor = new javax.swing.JTextField();
        jLabel50 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        tbankan1 = new javax.swing.JTextField();
        cbBorongan = new javax.swing.JCheckBox();
        jLabel28 = new javax.swing.JLabel();
        jLabel51 = new javax.swing.JLabel();
        tgander = new javax.swing.JComboBox();
        jLabel52 = new javax.swing.JLabel();
        tmaried = new javax.swing.JComboBox();
        jLabel53 = new javax.swing.JLabel();
        tagama = new javax.swing.JTextField();
        jLabel18 = new javax.swing.JLabel();
        tfinger = new javax.swing.JTextField();
        jLabel55 = new javax.swing.JLabel();
        ttunjjab = new javax.swing.JTextField();
        jLabel56 = new javax.swing.JLabel();
        tdept = new javax.swing.JTextField();
        jLabel57 = new javax.swing.JLabel();
        tjab = new javax.swing.JTextField();
        jButton6 = new javax.swing.JButton();
        tjab_kode = new javax.swing.JTextField();
        jLabel58 = new javax.swing.JLabel();
        ttunjkomp = new javax.swing.JTextField();
        jLabel59 = new javax.swing.JLabel();
        DateLahir = new com.toedter.calendar.JDateChooser();
        jLabel60 = new javax.swing.JLabel();
        ttunjintensif = new javax.swing.JTextField();
        jPanel7 = new javax.swing.JPanel();
        jLabel34 = new javax.swing.JLabel();
        tjabatan1 = new javax.swing.JComboBox();
        jButton1 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        tcodes = new javax.swing.JTextField();
        jLabel54 = new javax.swing.JLabel();
        jButton15 = new javax.swing.JButton();

        jLabel40.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel40.setText("Upah Lembur");

        tlembur.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tlemburKeyPressed(evt);
            }
        });

        jButton5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/update-left-rotation-15.png"))); // NOI18N
        jButton5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton5ActionPerformed(evt);
            }
        });

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Master Karyawan");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel12.setText("ID Karyawan");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));
        jPanel6.add(jTextField9, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 30, 260, -1));

        jTextField10.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                jTextField10CaretUpdate(evt);
            }
        });
        jTextField10.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                jTextField10KeyTyped(evt);
            }
        });
        jPanel6.add(jTextField10, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 60, 370, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 30, 90, -1));

        jLabel15.setText("Nama Karyawan");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField11.setText("Double Click to Edit");

        jTextField12.setEditable(false);
        jTextField12.setBackground(new java.awt.Color(204, 204, 255));
        jTextField12.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField12.setText("Total Data : 100");

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Gudang", "NIK", "Finger ID", "Nama", "Alamat", "Jabatan", "Borongan", "Agama", "No KTP", "Tgl Masuk", "Tgl Resign", "Status", "Tgl Input"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable3.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable3.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable3MouseClicked(evt);
            }
        });
        jTable3.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jTable3KeyPressed(evt);
            }
        });
        jScrollPane3.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(0).setMinWidth(0);
            jTable3.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(1).setMinWidth(100);
            jTable3.getColumnModel().getColumn(2).setMinWidth(100);
            jTable3.getColumnModel().getColumn(3).setMinWidth(100);
            jTable3.getColumnModel().getColumn(4).setMinWidth(100);
            jTable3.getColumnModel().getColumn(5).setMinWidth(100);
            jTable3.getColumnModel().getColumn(6).setMinWidth(100);
            jTable3.getColumnModel().getColumn(7).setMinWidth(100);
            jTable3.getColumnModel().getColumn(8).setMinWidth(100);
            jTable3.getColumnModel().getColumn(9).setMinWidth(100);
            jTable3.getColumnModel().getColumn(10).setMinWidth(100);
            jTable3.getColumnModel().getColumn(11).setMinWidth(100);
            jTable3.getColumnModel().getColumn(12).setMinWidth(100);
        }

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1103, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jScrollPane3))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 313, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jPanel1.setLayout(new java.awt.GridBagLayout());

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang/Log");
        jPanel3.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 130, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel3.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(560, 870, 97, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        btSave.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btSaveKeyPressed(evt);
            }
        });
        jPanel3.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 870, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel3.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(780, 870, 97, -1));

        tnik.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnikKeyPressed(evt);
            }
        });
        jPanel3.add(tnik, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 150, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("NIK");
        jPanel3.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 110, 20));

        tdesa.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tdesaFocusLost(evt);
            }
        });
        tdesa.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tdesaKeyPressed(evt);
            }
        });
        jPanel3.add(tdesa, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 410, 270, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Desa");
        jPanel3.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 410, 130, 20));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Alamat Sekarang");
        jPanel3.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 320, 130, 20));

        talamat.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                talamatFocusLost(evt);
            }
        });
        talamat.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                talamatKeyPressed(evt);
            }
        });
        jPanel3.add(talamat, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 320, 270, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Kota/Kab");
        jPanel3.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 350, 130, 20));

        tkota.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkotaFocusLost(evt);
            }
        });
        tkota.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkotaKeyPressed(evt);
            }
        });
        jPanel3.add(tkota, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 350, 270, -1));

        tkec.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkecFocusLost(evt);
            }
        });
        tkec.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkecKeyPressed(evt);
            }
        });
        jPanel3.add(tkec, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 380, 270, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Kecamatan");
        jPanel3.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 380, 130, 20));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Kode Pos");
        jPanel3.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 440, 130, 20));

        tkodepos.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkodeposKeyPressed(evt);
            }
        });
        jPanel3.add(tkodepos, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 440, 270, -1));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Status Karyawan");
        jPanel3.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 170, 110, 20));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("Tgl Lahir");
        jPanel3.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 20, 110, 20));

        ttelp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                ttelpKeyPressed(evt);
            }
        });
        jPanel3.add(ttelp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 80, 270, -1));

        tbank.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbankFocusLost(evt);
            }
        });
        tbank.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbankKeyPressed(evt);
            }
        });
        jPanel3.add(tbank, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 680, 270, -1));

        jLabel29.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel29.setText("Default Payroll");
        jPanel3.add(jLabel29, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 490, 110, 20));

        jLabel30.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel30.setText("Keterangan Tambahan");
        jPanel3.add(jLabel30, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, 130, 20));

        jScrollPane4.setHorizontalScrollBarPolicy(javax.swing.ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);

        tket.setColumns(20);
        tket.setFont(new java.awt.Font("sansserif", 0, 11)); // NOI18N
        tket.setLineWrap(true);
        tket.setRows(5);
        tket.setWrapStyleWord(true);
        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        tket.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tketKeyTyped(evt);
            }
        });
        jScrollPane4.setViewportView(tket);

        jPanel3.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 270, 40));

        jLabel13.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel13.setText("ID Finger");
        jPanel3.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 110, 20));

        tnamakaryawan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnamakaryawanFocusLost(evt);
            }
        });
        tnamakaryawan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnamakaryawanKeyPressed(evt);
            }
        });
        jPanel3.add(tnamakaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 270, -1));

        tptkp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tptkpKeyPressed(evt);
            }
        });
        jPanel3.add(tptkp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 490, 240, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("No. KTP");
        jPanel3.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 50, 110, 20));

        tktp.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tktpFocusLost(evt);
            }
        });
        tktp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tktpKeyPressed(evt);
            }
        });
        jPanel3.add(tktp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 50, 270, -1));

        jLabel31.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel31.setText("Rekening A/N");
        jPanel3.add(jLabel31, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 710, 110, 20));

        jLabel32.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel32.setText("Bank");
        jPanel3.add(jLabel32, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 680, 110, 20));

        jLabel33.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel33.setText("PTKP");
        jPanel3.add(jLabel33, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 490, 110, 20));

        tgaji.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tgajiKeyPressed(evt);
            }
        });
        jPanel3.add(tgaji, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 520, 270, -1));

        tbpjsnama.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbpjsnamaFocusLost(evt);
            }
        });
        tbpjsnama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbpjsnamaKeyPressed(evt);
            }
        });
        jPanel3.add(tbpjsnama, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 700, 270, -1));

        jLabel35.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel35.setText("BPJS Nama");
        jPanel3.add(jLabel35, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 700, 140, 20));

        tbpjstanggal.setDateFormatString("dd/MM/yyyy");
        tbpjstanggal.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                tbpjstanggalPropertyChange(evt);
            }
        });
        tbpjstanggal.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbpjstanggalKeyPressed(evt);
            }
        });
        jPanel3.add(tbpjstanggal, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 820, 150, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Tanggal Masuk");
        jPanel3.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, -1, 20));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Kota/Kab");
        jPanel3.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 350, 130, 20));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Alamat KTP");
        jPanel3.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 320, 130, 20));

        jLabel36.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel36.setText("Kecamatan");
        jPanel3.add(jLabel36, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 380, 130, 20));

        jLabel37.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel37.setText("Desa");
        jPanel3.add(jLabel37, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 410, 130, 20));

        jLabel38.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel38.setText("Kode Pos");
        jPanel3.add(jLabel38, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 440, 130, 20));

        tkodeposktp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkodeposktpKeyPressed(evt);
            }
        });
        jPanel3.add(tkodeposktp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 440, 270, -1));

        tdesaktp.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tdesaktpFocusLost(evt);
            }
        });
        tdesaktp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tdesaktpKeyPressed(evt);
            }
        });
        jPanel3.add(tdesaktp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 410, 270, -1));

        tkecktp.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkecktpFocusLost(evt);
            }
        });
        tkecktp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkecktpKeyPressed(evt);
            }
        });
        jPanel3.add(tkecktp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 380, 270, -1));

        tkotaktp.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkotaktpFocusLost(evt);
            }
        });
        tkotaktp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkotaktpKeyPressed(evt);
            }
        });
        jPanel3.add(tkotaktp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 350, 270, -1));

        talamatktp.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                talamatktpFocusLost(evt);
            }
        });
        talamatktp.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                talamatktpKeyPressed(evt);
            }
        });
        jPanel3.add(talamatktp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 320, 270, -1));

        jLabel39.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel39.setText("Nomor Rekening");
        jPanel3.add(jLabel39, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 740, 110, 20));

        tbankrek.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbankrekFocusLost(evt);
            }
        });
        tbankrek.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbankrekKeyPressed(evt);
            }
        });
        jPanel3.add(tbankrek, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 740, 270, -1));

        tdefoultpayroll.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tdefoultpayrollKeyPressed(evt);
            }
        });
        jPanel3.add(tdefoultpayroll, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 490, 240, -1));

        jLabel41.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel41.setText("BPJS Kesehatan");
        jPanel3.add(jLabel41, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 730, 140, 20));

        tbpjskes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbpjskesFocusLost(evt);
            }
        });
        tbpjskes.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbpjskesKeyPressed(evt);
            }
        });
        jPanel3.add(tbpjskes, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 730, 270, -1));

        jLabel42.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel42.setText("BPJS Ketenagakerjaan");
        jPanel3.add(jLabel42, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 760, 140, 20));

        tbpjsketeranagakerjaan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbpjsketeranagakerjaanFocusLost(evt);
            }
        });
        tbpjsketeranagakerjaan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbpjsketeranagakerjaanKeyPressed(evt);
            }
        });
        jPanel3.add(tbpjsketeranagakerjaan, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 760, 270, -1));

        jLabel43.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel43.setText("BPJS Tanggal");
        jPanel3.add(jLabel43, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 820, 140, 20));

        tbpjscabang.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbpjscabangFocusLost(evt);
            }
        });
        tbpjscabang.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbpjscabangKeyPressed(evt);
            }
        });
        jPanel3.add(tbpjscabang, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 790, 270, -1));

        DateMasuk.setDateFormatString("dd/MM/yyyy");
        DateMasuk.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateMasukPropertyChange(evt);
            }
        });
        DateMasuk.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateMasukKeyPressed(evt);
            }
        });
        jPanel3.add(DateMasuk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 150, -1));

        jLabel44.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel44.setText("BPJS Cabang");
        jPanel3.add(jLabel44, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 790, 140, 20));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Tanggal Resign");
        jPanel3.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, -1, 20));

        DateResign.setDateFormatString("dd/MM/yyyy");
        DateResign.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateResignPropertyChange(evt);
            }
        });
        DateResign.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateResignKeyPressed(evt);
            }
        });
        jPanel3.add(DateResign, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 150, -1));

        jSeparator1.setForeground(new java.awt.Color(51, 51, 51));
        jSeparator1.setBorder(javax.swing.BorderFactory.createLineBorder(new java.awt.Color(0, 0, 0)));
        jPanel3.add(jSeparator1, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 470, 890, 3));

        jSeparator2.setForeground(new java.awt.Color(51, 51, 51));
        jSeparator2.setBorder(javax.swing.BorderFactory.createLineBorder(new java.awt.Color(0, 0, 0)));
        jPanel3.add(jSeparator2, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 300, 890, 3));

        jButton3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/update-left-rotation-15.png"))); // NOI18N
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton3, new org.netbeans.lib.awtextra.AbsoluteConstraints(850, 490, 30, -1));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/update-left-rotation-15.png"))); // NOI18N
        jPanel3.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 490, 30, -1));

        jLabel45.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel45.setText("Salary");
        jPanel3.add(jLabel45, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 520, 110, 20));

        rbGajiHari.setText("Harian");
        rbGajiHari.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                rbGajiHariMouseClicked(evt);
            }
        });
        jPanel3.add(rbGajiHari, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 550, -1, -1));

        rbGajiBulanan.setText("Bulanan");
        rbGajiBulanan.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                rbGajiBulananMouseClicked(evt);
            }
        });
        jPanel3.add(rbGajiBulanan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 550, -1, -1));

        jLabel46.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel46.setText("Tunj. Kompetensi");
        jPanel3.add(jLabel46, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 520, 110, 20));

        jLabel47.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel47.setText("Tanggal NPWP");
        jPanel3.add(jLabel47, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 620, 110, 20));

        tnpwpnama.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnpwpnamaFocusLost(evt);
            }
        });
        tnpwpnama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnpwpnamaKeyPressed(evt);
            }
        });
        jPanel3.add(tnpwpnama, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 600, 270, -1));

        jLabel48.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel48.setText("Alamat NPWP");
        jPanel3.add(jLabel48, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 630, 110, 20));

        tnpwpalamat.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnpwpalamatFocusLost(evt);
            }
        });
        tnpwpalamat.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnpwpalamatKeyPressed(evt);
            }
        });
        jPanel3.add(tnpwpalamat, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 630, 270, -1));

        jLabel49.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel49.setText("Nama NPWP");
        jPanel3.add(jLabel49, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 600, 110, 20));

        tnpwptanggal.setDateFormatString("dd/MM/yyyy");
        tnpwptanggal.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                tnpwptanggalPropertyChange(evt);
            }
        });
        tnpwptanggal.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnpwptanggalKeyPressed(evt);
            }
        });
        jPanel3.add(tnpwptanggal, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 620, 150, -1));

        tnpwpnomor.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnpwpnomorFocusLost(evt);
            }
        });
        tnpwpnomor.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnpwpnomorKeyPressed(evt);
            }
        });
        jPanel3.add(tnpwpnomor, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 650, 270, -1));

        jLabel50.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel50.setText("Nomor NPWP");
        jPanel3.add(jLabel50, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 650, 110, 20));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel3.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 90, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 20, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel3.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 20, 150, -1));

        tbankan1.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbankan1FocusLost(evt);
            }
        });
        tbankan1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbankan1KeyPressed(evt);
            }
        });
        jPanel3.add(tbankan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 710, 270, -1));

        cbBorongan.setText("Borongan");
        cbBorongan.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                cbBoronganActionPerformed(evt);
            }
        });
        jPanel3.add(cbBorongan, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 170, -1, -1));

        jLabel28.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel28.setText("Jabatan");
        jPanel3.add(jLabel28, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 140, 110, 20));

        jLabel51.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel51.setText("Agama");
        jPanel3.add(jLabel51, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 260, 120, 20));

        tgander.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Laki - Laki", "Perempuan" }));
        jPanel3.add(tgander, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 200, 270, -1));

        jLabel52.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel52.setText("Jenis Kelamin");
        jPanel3.add(jLabel52, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 200, 100, 20));

        tmaried.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Lajang", "Menikah" }));
        jPanel3.add(tmaried, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 230, 270, -1));

        jLabel53.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel53.setText("Status Maried");
        jPanel3.add(jLabel53, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 230, 120, 20));

        tagama.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tagamaFocusLost(evt);
            }
        });
        tagama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tagamaKeyPressed(evt);
            }
        });
        jPanel3.add(tagama, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 260, 270, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Nama Karyawan");
        jPanel3.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        tfinger.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tfingerKeyPressed(evt);
            }
        });
        jPanel3.add(tfinger, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 150, -1));

        jLabel55.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel55.setText("Salary Type");
        jPanel3.add(jLabel55, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 550, 110, 20));
        jPanel3.add(ttunjjab, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 550, 270, -1));

        jLabel56.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel56.setText("Departemen");
        jPanel3.add(jLabel56, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 110, 110, 20));

        tdept.setEditable(false);
        jPanel3.add(tdept, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 110, 270, -1));

        jLabel57.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel57.setText("Jabatan");
        jPanel3.add(jLabel57, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 140, 110, 20));

        tjab.setEditable(false);
        jPanel3.add(tjab, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 140, 240, -1));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(850, 140, 30, -1));

        tjab_kode.setEditable(false);
        jPanel3.add(tjab_kode, new org.netbeans.lib.awtextra.AbsoluteConstraints(890, 140, 1, -1));

        jLabel58.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel58.setText("Tunj.Jabatan");
        jPanel3.add(jLabel58, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 550, 110, 20));
        jPanel3.add(ttunjkomp, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 520, 270, -1));

        jLabel59.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel59.setText("No. Telp");
        jPanel3.add(jLabel59, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 80, 110, 20));

        DateLahir.setDateFormatString("dd/MM/yyyy");
        DateLahir.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                DateLahirPropertyChange(evt);
            }
        });
        DateLahir.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                DateLahirKeyPressed(evt);
            }
        });
        jPanel3.add(DateLahir, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 20, 180, -1));

        jLabel60.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel60.setText("Tunj.Insentif");
        jPanel3.add(jLabel60, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 580, 110, 20));
        jPanel3.add(ttunjintensif, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 580, 270, -1));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.ipadx = 10;
        gridBagConstraints.ipady = 161;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        jPanel1.add(jPanel3, gridBagConstraints);

        jScrollPane1.setViewportView(jPanel1);

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 1123, Short.MAX_VALUE)
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 457, Short.MAX_VALUE)
                .addContainerGap())
        );

        jTabbedPane1.addTab("Add/Edit", jPanel5);

        jPanel7.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel34.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel34.setText("Pilih Dept / Jabatan");
        jPanel7.add(jLabel34, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 70, 110, 20));

        tjabatan1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tjabatan1KeyPressed(evt);
            }
        });
        jPanel7.add(tjabatan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 70, 240, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton1.setText("Cetak");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel7.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 110, 160, 30));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null},
                {null, null},
                {null, null},
                {null, null}
            },
            new String [] {
                "nik", "nama"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane2.setViewportView(jTable1);

        jPanel7.add(jScrollPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 40, 490, 400));

        tcodes.setEditable(false);
        tcodes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodesFocusLost(evt);
            }
        });
        jPanel7.add(tcodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 40, 110, -1));

        jLabel54.setText("Gudang");
        jPanel7.add(jLabel54, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 40, -1, 20));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel7.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 40, 30, -1));

        jTabbedPane1.addTab("Print NameCard", jPanel7);

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
                        .addGap(0, 0, Short.MAX_VALUE))))
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addGap(4, 4, 4)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        if(evt.getKeyCode() ==10) tktp.requestFocus();
    }//GEN-LAST:event_tketKeyPressed

    private void tketKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyTyped
        
    }//GEN-LAST:event_tketKeyTyped

    private void tptkpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tptkpKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tptkpKeyPressed

    private void tbpjstanggalPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_tbpjstanggalPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_tbpjstanggalPropertyChange

    private void tbpjstanggalKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbpjstanggalKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tbpjstanggalKeyPressed

    private void tdefoultpayrollKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tdefoultpayrollKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tdefoultpayrollKeyPressed

    private void DateMasukPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateMasukPropertyChange
        if(btSave.getText().equalsIgnoreCase("Save")){
            lastNikKaryawan();
        }
    }//GEN-LAST:event_DateMasukPropertyChange

    private void DateMasukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateMasukKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateMasukKeyPressed

    private void DateResignPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateResignPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateResignPropertyChange

    private void DateResignKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateResignKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateResignKeyPressed

    private void tlemburKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tlemburKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tlemburKeyPressed

    private void tnpwptanggalPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_tnpwptanggalPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_tnpwptanggalPropertyChange

    private void tnpwptanggalKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnpwptanggalKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tnpwptanggalKeyPressed

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void cbBoronganActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_cbBoronganActionPerformed
        if(btSave.getText().equalsIgnoreCase("Save")) lastNikKaryawan();
    }//GEN-LAST:event_cbBoronganActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    save();
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        loadPTKP();
    }//GEN-LAST:event_jButton3ActionPerformed

    private void tnamakaryawanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnamakaryawanFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tnamakaryawanFocusLost

    private void tktpFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tktpFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tktpFocusLost

    private void tagamaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tagamaFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tagamaFocusLost

    private void talamatFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_talamatFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_talamatFocusLost

    private void tkotaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkotaFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tkotaFocusLost

    private void tkecFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkecFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tkecFocusLost

    private void tdesaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tdesaFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tdesaFocusLost

    private void talamatktpFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_talamatktpFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_talamatktpFocusLost

    private void tkotaktpFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkotaktpFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tkotaktpFocusLost

    private void tkecktpFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkecktpFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tkecktpFocusLost

    private void tdesaktpFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tdesaktpFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tdesaktpFocusLost

    private void tnpwpnamaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnpwpnamaFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tnpwpnamaFocusLost

    private void tnpwpalamatFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnpwpalamatFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tnpwpalamatFocusLost

    private void tnpwpnomorFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnpwpnomorFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tnpwpnomorFocusLost

    private void tbankFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbankFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tbankFocusLost

    private void tbankan1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbankan1FocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tbankan1FocusLost

    private void tbankrekFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbankrekFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tbankrekFocusLost

    private void tbpjsnamaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbpjsnamaFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tbpjsnamaFocusLost

    private void tbpjskesFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbpjskesFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tbpjskesFocusLost

    private void tbpjsketeranagakerjaanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbpjsketeranagakerjaanFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tbpjsketeranagakerjaanFocusLost

    private void tbpjscabangFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbpjscabangFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());// TODO add your handling code here:
    }//GEN-LAST:event_tbpjscabangFocusLost

    private void tnikKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnikKeyPressed
        rfa.EnterNextFocus(evt, tnamakaryawan);
    }//GEN-LAST:event_tnikKeyPressed

    private void tnamakaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnamakaryawanKeyPressed
        rfa.EnterNextFocus(evt, tket);// TODO add your handling code here:
    }//GEN-LAST:event_tnamakaryawanKeyPressed

    private void tktpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tktpKeyPressed
        rfa.EnterNextFocus(evt, ttelp);// TODO add your handling code here:
    }//GEN-LAST:event_tktpKeyPressed

    private void ttelpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_ttelpKeyPressed
        rfa.EnterNextFocus(evt, tagama);// TODO add your handling code here:
    }//GEN-LAST:event_ttelpKeyPressed

    private void tagamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tagamaKeyPressed
        rfa.EnterNextFocus(evt, talamat);// TODO add your handling code here:
    }//GEN-LAST:event_tagamaKeyPressed

    private void talamatKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_talamatKeyPressed
        rfa.EnterNextFocus(evt, tkota);// TODO add your handling code here:
    }//GEN-LAST:event_talamatKeyPressed

    private void tkotaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkotaKeyPressed
        rfa.EnterNextFocus(evt, tkec);// TODO add your handling code here:
    }//GEN-LAST:event_tkotaKeyPressed

    private void tkecKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkecKeyPressed
        rfa.EnterNextFocus(evt, tdesa);// TODO add your handling code here:
    }//GEN-LAST:event_tkecKeyPressed

    private void tdesaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tdesaKeyPressed
        rfa.EnterNextFocus(evt, tkodepos);// TODO add your handling code here:
    }//GEN-LAST:event_tdesaKeyPressed

    private void tkodeposKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkodeposKeyPressed
        rfa.EnterNextFocus(evt, talamatktp);// TODO add your handling code here:
    }//GEN-LAST:event_tkodeposKeyPressed

    private void talamatktpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_talamatktpKeyPressed
        rfa.EnterNextFocus(evt, tkotaktp);// TODO add your handling code here:
    }//GEN-LAST:event_talamatktpKeyPressed

    private void tkotaktpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkotaktpKeyPressed
        rfa.EnterNextFocus(evt, tkecktp);// TODO add your handling code here:
    }//GEN-LAST:event_tkotaktpKeyPressed

    private void tkecktpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkecktpKeyPressed
        rfa.EnterNextFocus(evt, tdesaktp);// TODO add your handling code here:
    }//GEN-LAST:event_tkecktpKeyPressed

    private void tdesaktpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tdesaktpKeyPressed
        rfa.EnterNextFocus(evt, tkodeposktp);// TODO add your handling code here:
    }//GEN-LAST:event_tdesaktpKeyPressed

    private void tkodeposktpKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkodeposktpKeyPressed
        rfa.EnterNextFocus(evt, tgaji);// TODO add your handling code here:
    }//GEN-LAST:event_tkodeposktpKeyPressed

    private void tgajiKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tgajiKeyPressed
        rfa.EnterNextFocus(evt, tnpwpnama);// TODO add your handling code here:
    }//GEN-LAST:event_tgajiKeyPressed

    private void tnpwpnamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnpwpnamaKeyPressed
        rfa.EnterNextFocus(evt, tnpwpalamat);// TODO add your handling code here:
    }//GEN-LAST:event_tnpwpnamaKeyPressed

    private void tnpwpalamatKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnpwpalamatKeyPressed
        rfa.EnterNextFocus(evt, tnpwpnomor);// TODO add your handling code here:
    }//GEN-LAST:event_tnpwpalamatKeyPressed

    private void tnpwpnomorKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnpwpnomorKeyPressed
        rfa.EnterNextFocus(evt, tbank);// TODO add your handling code here:
    }//GEN-LAST:event_tnpwpnomorKeyPressed

    private void tbankKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbankKeyPressed
        rfa.EnterNextFocus(evt, tbankan1);// TODO add your handling code here:
    }//GEN-LAST:event_tbankKeyPressed

    private void tbankan1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbankan1KeyPressed
        rfa.EnterNextFocus(evt, tbankrek);// TODO add your handling code here:
    }//GEN-LAST:event_tbankan1KeyPressed

    private void tbankrekKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbankrekKeyPressed
        rfa.EnterNextFocus(evt, tbpjsnama);// TODO add your handling code here:
    }//GEN-LAST:event_tbankrekKeyPressed

    private void tbpjsnamaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbpjsnamaKeyPressed
        rfa.EnterNextFocus(evt, tbpjskes);// TODO add your handling code here:
    }//GEN-LAST:event_tbpjsnamaKeyPressed

    private void tbpjskesKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbpjskesKeyPressed
        rfa.EnterNextFocus(evt, tbpjsketeranagakerjaan);// TODO add your handling code here:
    }//GEN-LAST:event_tbpjskesKeyPressed

    private void tbpjsketeranagakerjaanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbpjsketeranagakerjaanKeyPressed
        rfa.EnterNextFocus(evt, tbpjscabang);// TODO add your handling code here:
    }//GEN-LAST:event_tbpjsketeranagakerjaanKeyPressed

    private void tbpjscabangKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbpjscabangKeyPressed
        rfa.EnterNextFocus(evt, btSave);
    }//GEN-LAST:event_tbpjscabangKeyPressed

    private void btSaveKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btSaveKeyPressed
        if(evt.getKeyCode()==10){
            btSave.doClick();
        }
    }//GEN-LAST:event_btSaveKeyPressed

    private void tfingerKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tfingerKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tfingerKeyPressed

    private void jButton5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton5ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jButton5ActionPerformed

    private void jTable3KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTable3KeyPressed
        
    }//GEN-LAST:event_jTable3KeyPressed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 2){
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            loadEdit();
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void rbGajiBulananMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_rbGajiBulananMouseClicked
        typePayroll = "MONTH";
    }//GEN-LAST:event_rbGajiBulananMouseClicked

    private void rbGajiHariMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_rbGajiHariMouseClicked
        typePayroll = "DAY";
    }//GEN-LAST:event_rbGajiHariMouseClicked

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        clear();
        deff();
        loadData();
        
        jTabbedPane1.setTitleAt(1, "Add");
    }//GEN-LAST:event_btCancelActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jTextField10CaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_jTextField10CaretUpdate
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField10CaretUpdate

    private void jTextField10KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTextField10KeyTyped
        loadData();
    }//GEN-LAST:event_jTextField10KeyTyped

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? \nData Setelah di hapus tidak dapat di kembalikan kembali. ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void tjabatan1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tjabatan1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tjabatan1KeyPressed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        cetakcard();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void tcodesFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodesFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodesFocusLost

    private void jButton15ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton15ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcodes.setText(a.ccode);
        }
    }//GEN-LAST:event_jButton15ActionPerformed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogJab a = new DialogJab();
        a.setVisible(true);
        if(!"".equals(a.idjab)){
            tjab.setText(a.jab);
            tdept.setText(a.dept);
            tjab_kode.setText(a.idjab);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void DateLahirPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_DateLahirPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_DateLahirPropertyChange

    private void DateLahirKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_DateLahirKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_DateLahirKeyPressed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private com.toedter.calendar.JDateChooser DateLahir;
    private com.toedter.calendar.JDateChooser DateMasuk;
    private com.toedter.calendar.JDateChooser DateResign;
    private javax.swing.ButtonGroup GroupLembur;
    private javax.swing.ButtonGroup GroupSalary;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btSave;
    private javax.swing.JCheckBox cbBorongan;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton4;
    private javax.swing.JButton jButton5;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel16;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel19;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel26;
    private javax.swing.JLabel jLabel27;
    private javax.swing.JLabel jLabel28;
    private javax.swing.JLabel jLabel29;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel30;
    private javax.swing.JLabel jLabel31;
    private javax.swing.JLabel jLabel32;
    private javax.swing.JLabel jLabel33;
    private javax.swing.JLabel jLabel34;
    private javax.swing.JLabel jLabel35;
    private javax.swing.JLabel jLabel36;
    private javax.swing.JLabel jLabel37;
    private javax.swing.JLabel jLabel38;
    private javax.swing.JLabel jLabel39;
    private javax.swing.JLabel jLabel40;
    private javax.swing.JLabel jLabel41;
    private javax.swing.JLabel jLabel42;
    private javax.swing.JLabel jLabel43;
    private javax.swing.JLabel jLabel44;
    private javax.swing.JLabel jLabel45;
    private javax.swing.JLabel jLabel46;
    private javax.swing.JLabel jLabel47;
    private javax.swing.JLabel jLabel48;
    private javax.swing.JLabel jLabel49;
    private javax.swing.JLabel jLabel50;
    private javax.swing.JLabel jLabel51;
    private javax.swing.JLabel jLabel52;
    private javax.swing.JLabel jLabel53;
    private javax.swing.JLabel jLabel54;
    private javax.swing.JLabel jLabel55;
    private javax.swing.JLabel jLabel56;
    private javax.swing.JLabel jLabel57;
    private javax.swing.JLabel jLabel58;
    private javax.swing.JLabel jLabel59;
    private javax.swing.JLabel jLabel60;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel7;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JSeparator jSeparator1;
    private javax.swing.JSeparator jSeparator2;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField10;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField9;
    private javax.swing.JRadioButton rbGajiBulanan;
    private javax.swing.JRadioButton rbGajiHari;
    private javax.swing.JTextField tagama;
    private javax.swing.JTextField talamat;
    private javax.swing.JTextField talamatktp;
    private javax.swing.JTextField tbank;
    private javax.swing.JTextField tbankan1;
    private javax.swing.JTextField tbankrek;
    private javax.swing.JTextField tbpjscabang;
    private javax.swing.JTextField tbpjskes;
    private javax.swing.JTextField tbpjsketeranagakerjaan;
    private javax.swing.JTextField tbpjsnama;
    private com.toedter.calendar.JDateChooser tbpjstanggal;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JComboBox tdefoultpayroll;
    private javax.swing.JTextField tdept;
    private javax.swing.JTextField tdesa;
    private javax.swing.JTextField tdesaktp;
    private javax.swing.JTextField tfinger;
    private javax.swing.JTextField tgaji;
    private javax.swing.JComboBox tgander;
    private javax.swing.JTextField tjab;
    private javax.swing.JTextField tjab_kode;
    private javax.swing.JComboBox tjabatan1;
    private javax.swing.JTextField tkec;
    private javax.swing.JTextField tkecktp;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextField tkodepos;
    private javax.swing.JTextField tkodeposktp;
    private javax.swing.JTextField tkota;
    private javax.swing.JTextField tkotaktp;
    private javax.swing.JTextField tktp;
    private javax.swing.JComboBox tlembur;
    private javax.swing.JComboBox tmaried;
    private javax.swing.JTextField tnamakaryawan;
    private javax.swing.JTextField tnik;
    private javax.swing.JTextField tnpwpalamat;
    private javax.swing.JTextField tnpwpnama;
    private javax.swing.JTextField tnpwpnomor;
    private com.toedter.calendar.JDateChooser tnpwptanggal;
    private javax.swing.JComboBox tptkp;
    private javax.swing.JTextField ttelp;
    private javax.swing.JTextField ttunjintensif;
    private javax.swing.JTextField ttunjjab;
    private javax.swing.JTextField ttunjkomp;
    // End of variables declaration//GEN-END:variables
}
