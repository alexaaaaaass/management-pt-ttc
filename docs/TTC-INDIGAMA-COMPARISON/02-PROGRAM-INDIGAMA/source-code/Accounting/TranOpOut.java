/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Accounting;

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
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
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
public class TranOpOut extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="TRANSAKSI OPRASIONAL KELUAR";
    String idKas="O2";
    ArrayList<String> deleteRow;
    ArrayList<String> idJenis;
    ArrayList<String> idmesin;
    public TranOpOut(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        idJenis= new ArrayList<>();
        push_button();
        clear();
        deff();
        loadData();
        loadJenisOprasional();
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

    private void loadJenisOprasional(){
        sql="select * from MOperasional";
        rs = con.queryResult(sql);
        idJenis.clear();
        cJenis.removeAll();
        cJenis.removeAllItems();
        idJenis.add("");
        cJenis.addItem("");
        try {
            while(rs.next()){
                idJenis.add(rs.getString("id_op"));
                cJenis.addItem(rs.getString("cdesc"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(TranOpOut.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void deff(){
        btDelete.setVisible(false);
        btBatal.setVisible(false);
        btHapus.setVisible(false);
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(new Date());
        
        tcodes.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        tacc.setText(deffProgram.get("acc_bank"));
        taccll.setText(deffProgram.get("acc_modal"));
        
        if(tcode.getText().equals("PMK02")) {
            tacc.setText("103.101");
        }
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        sql = "select * from maccount where caccnt = '"+tacc.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  tacc1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        sql = "select * from maccount where caccnt = '"+taccll.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  taccll1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        mesin.removeAll();
        mesin.removeAllItems();
        idmesin = new ArrayList<>();
        mesin.addItem(""); idmesin.add("0");
        sql = "select * from MMesin";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){  mesin.addItem(rs.getString("cname")); idmesin.add(rs.getString("id"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        LastCode();
        hitungTotalTransaksi();
        deffBatal();
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tacc,tacc1,taccll,taccll1,tket,tnilai,tnopol,todometer);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        deleteRow.clear();
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        id = "";
        rfa.empty(MenuAdmin.keyssDef,tket,tnilai,tnopol,todometer,tkode);
        cJenis.setSelectedIndex(0);
        mesin.setSelectedIndex(0);
    }
    
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranOpOut where  ccode='"+tcode.getText()+"' and cnbkt like '%"+"BOPK/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/")+Login.subdefault+"-"+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("BOPK/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/")+Login.subdefault+"-");
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
    } 
    private void loadData(){
        sql = "SELECT a.id,a.ctran,a.cnbkt,a.ccode,b.cname as gudang,a.cyear,a.dtran,a.cacckas,c.cdesc as acc_bank,"
                + "a.caccll,d.cdesc as acc_kas\n" +
            ",a.namnt,a.cnote,a.cusername,a.cjenis_name,a.ckendaraan,a.codometer,a.nik,a.nik_cname,f.cname as mesin,a.kode\n" +
            "FROM TranOpOut a join mgudang b on a.ccode=b.ccode \n" +
            "left join MAccount c on a.cacckas=c.caccnt\n" +
            "left join MAccount d on a.caccll=d.caccnt\n" +
            "left join MMesin f on a.idmesin=f.id\n" +
            "where a.ccode='"+tcodes.getText()+"' and a.ctran='"+idKas+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
            "and a.cnote like '%"+tkets.getText()+"%' "
                + "order by a.dtran,a.cnbkt";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("ccode"),rs.getString("gudang"),
                    rs.getString("cnbkt"),
                    rs.getString("kode"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cacckas"),rs.getString("acc_bank"),
                    rs.getString("caccll"),rs.getString("acc_kas"),
                    rs.getString("cnote"),rs.getDouble("namnt"),
                    rs.getString("cjenis_name"),
                    rs.getString("nik_cname"),
                    rs.getString("ckendaraan"),rs.getString("codometer"),
                    rs.getString("mesin"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        con.resizeColumnWidth(jTable2);
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
    }
    private void loadEdit(){
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        sql = "select a.*,c.cname as mesin from TranOpOut a "
                + "left join mmesin c on a.idmesin=c.id "
                + "where a.ctran='"+idKas+"' and  a.cnbkt = '"+cnbkt+"' and a.ccode='"+tcodes.getText()+"' ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                int kode=0;
                if(rs.getObject("kode") != null) kode=rs.getInt("kode");
                
                model.addRow(new Object[]{
                    rs.getInt("id"),rs.getString("cacckas"),rs.getString("caccll"),
                    rs.getString("cnote"),rs.getDouble("namnt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"), "yyyy-MM-dd"),
                    rs.getString("cnbkt"),
                    rs.getString("cjenis"),rs.getString("cjenis_name"),
                    rs.getString("nik"),rs.getString("nik_cname"),
                    rs.getString("ckendaraan"),rs.getString("codometer"),
                    rs.getString("mesin"),rs.getString("idmesin"),
                    kode
                });
                
                btSave.setText("Update");
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
        mesin.setSelectedIndex(0);
        id = jTable1.getValueAt(baris, 0).toString();
        tacc.setText(jTable1.getValueAt(baris, 1).toString());
        taccll.setText(jTable1.getValueAt(baris, 2).toString());
        tket.setText(jTable1.getValueAt(baris, 3).toString());
        tnilai.setText(rfa.formatDecimal3.format(jTable1.getValueAt(baris, 4)));
        jDateTran.setDate(rfa.StringtoDate(keyssDef,jTable1.getValueAt(baris, 5).toString(),"yyyy-MM-dd"));
        tbukti.setText(jTable1.getValueAt(baris,6).toString().substring(0, jTable1.getValueAt(baris,6).toString().length()-4));
        tbukti1.setText(jTable1.getValueAt(baris,6).toString().substring(jTable1.getValueAt(baris,6).toString().length()-4, jTable1.getValueAt(baris,6).toString().length()));
        
        if(jTable1.getValueAt(baris,8) != null) {
            cJenis.setSelectedItem(jTable1.getValueAt(baris,8));
        }
        
        if(jTable1.getValueAt(baris,9) != null) tkaryawan.setText(jTable1.getValueAt(baris,9).toString());
        if(jTable1.getValueAt(baris,10) != null) tkaryawan1.setText(jTable1.getValueAt(baris,10).toString());
        if(jTable1.getValueAt(baris,11) != null) tnopol.setText(jTable1.getValueAt(baris,11).toString());
        if(jTable1.getValueAt(baris,12) != null) todometer.setText(jTable1.getValueAt(baris,12).toString());
        if(jTable1.getValueAt(baris,13) != null) mesin.setSelectedItem(jTable1.getValueAt(baris,13).toString());
        if(jTable1.getValueAt(baris,15) != null) tkode.setText(jTable1.getValueAt(baris,15).toString());
        
        btAdd.setText("Ubah");
        btBatal.setVisible(true);
        btHapus.setVisible(true);
        
        tacc.requestFocus();
        taccll.requestFocus();
        tket.requestFocus();
    }
    
    private Boolean cekSave(){
        if(tkode.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Kode Wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tacc1.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Account Bank Tidak Terdaftar", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(taccll1.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Account Kas Tidak Terdaftar", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tket.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Keterangan wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tnilai.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Nominal wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        
        if(taccll.getText().equals("301.001")) {
            if(tkaryawan.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"Karyawan wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
                return false;
            }
        }
        
        if(taccll.getText().equals("302.002")) {
            if(mesin.getSelectedIndex() == 0 ) {
                JOptionPane.showMessageDialog(this,"Mesin wajib di isi apabila pemeliharaan/service", "Info", JOptionPane.ERROR_MESSAGE);
                return false;
            }
        }
        return true;
    }
    private void addData(){
        if(!cekSave()) {
            return;
        }
        
        
        if(btAdd.getText().equalsIgnoreCase("Add")) {
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            model.addRow(new Object[]{
                -1,tacc.getText(),taccll.getText(),tket.getText(),Double.valueOf(tnilai.getText()),
                rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"),
                tbukti.getText()+tbukti1.getText(),
                idJenis.get(cJenis.getSelectedIndex()),cJenis.getSelectedItem().toString(),
                tkaryawan.getText(),tkaryawan1.getText(),
                tnopol.getText(),todometer.getText(),
                mesin.getSelectedItem().toString(),idmesin.get(mesin.getSelectedIndex()),
                tkode.getText(),
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(id, barisEdit, col++);
            jTable1.setValueAt(tacc.getText(), barisEdit, col++);
            jTable1.setValueAt(taccll.getText(), barisEdit, col++);
            jTable1.setValueAt(tket.getText(), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tnilai.getText()), barisEdit, col++);
            jTable1.setValueAt(rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"), barisEdit, col++);
            jTable1.setValueAt(tbukti.getText()+tbukti1.getText(), barisEdit, col++);
            jTable1.setValueAt(idJenis.get(cJenis.getSelectedIndex()), barisEdit, col++);
            jTable1.setValueAt(cJenis.getSelectedItem().toString(), barisEdit, col++);
            jTable1.setValueAt(tkaryawan.getText(), barisEdit, col++);
            jTable1.setValueAt(tkaryawan1.getText(), barisEdit, col++);
            jTable1.setValueAt(tnopol.getText(), barisEdit, col++);
            jTable1.setValueAt(todometer.getText(), barisEdit, col++);
            jTable1.setValueAt(mesin.getSelectedItem().toString(), barisEdit, col++);
            jTable1.setValueAt(idmesin.get(mesin.getSelectedIndex()), barisEdit, col++);
            jTable1.setValueAt(tkode.getText(), barisEdit, col++);
        }
        hitungTotalTransaksi();
        deffBatal();
        taccll.requestFocus();
        
    }
    private void hitungTotalTransaksi(){
        Double totalNilai=0.0;
        for(int i=0; i<jTable1.getRowCount(); i++){
            totalNilai+=Double.valueOf(jTable1.getValueAt(i, 4).toString());
        }
        infoTotal.setText("Total Transaksi : Rp. "+rfa.formatDecimal2.format(totalNilai));
        if(jTable1.getRowCount()>0){
            tacc.setEnabled(false);
            jButton12.setEnabled(false);
        }
    }
    
    private int insertData(){
        int err= 0;
        for(int i=0; i<jTable1.getRowCount();i++){
            if("-1".equalsIgnoreCase(jTable1.getValueAt(i, 0).toString())) {
                //INSERT
                sql="INSERT INTO TranOpOut (ctran,cnbkt,ccode,cyear,dtran,cacckas,caccll\n" +
                    ",namnt,cnote,cusername,cjenis,cjenis_name,nik,nik_cname,ckendaraan,codometer,idmesin,kode)\n" +
                    "VALUES\n" +
                    "('"+idKas+"','"+jTable1.getValueAt(i, 6).toString()+"','"+tcode.getText()+"','"+jTable1.getValueAt(i, 5).toString().substring(0, 4)+"','"+jTable1.getValueAt(i, 5).toString()+"','"+jTable1.getValueAt(i, 1).toString()+"','"+jTable1.getValueAt(i, 2).toString()+"',"
                        + "'"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"','"+jTable1.getValueAt(i, 3).toString()+"','"+Login.user+"',"
                        + "'"+jTable1.getValueAt(i, 7).toString()+"','"+jTable1.getValueAt(i, 8).toString()+"',"
                        + "'"+jTable1.getValueAt(i, 9).toString()+"','"+jTable1.getValueAt(i,10).toString()+"',"
                        + "'"+jTable1.getValueAt(i,11).toString()+"','"+jTable1.getValueAt(i,12).toString()+"',"
                        + "'"+jTable1.getValueAt(i,14).toString()+"','"+jTable1.getValueAt(i,15).toString()+"')";
            } else {  
                //UPDATE
                sql="UPDATE TranOpOut set ctran='"+idKas+"',cnbkt='"+jTable1.getValueAt(i, 6).toString()+"',ccode='"+tcode.getText()+"',cyear='"+jTable1.getValueAt(i, 5).toString().substring(0, 4)+"',dtran='"+jTable1.getValueAt(i, 5).toString()+"',cacckas='"+jTable1.getValueAt(i, 1).toString()+"',caccll='"+jTable1.getValueAt(i, 2).toString()+"'\n" +
                    ",namnt='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"',cnote='"+jTable1.getValueAt(i, 3).toString()+"',cusername='"+Login.user+"',"
                        + "cjenis='"+jTable1.getValueAt(i, 7).toString()+"',cjenis_name='"+jTable1.getValueAt(i, 8).toString()+"',"
                        + "nik='"+jTable1.getValueAt(i, 9).toString()+"',nik_cname='"+jTable1.getValueAt(i, 10).toString()+"', \n" 
                        + "ckendaraan='"+jTable1.getValueAt(i,11).toString()+"',codometer='"+jTable1.getValueAt(i,12).toString()+"',"
                        + "idmesin='"+jTable1.getValueAt(i,14).toString()+"',kode='"+jTable1.getValueAt(i,15).toString()+"' \n" +
                    " where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
            
            }
            
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    private void save(){
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        if(insertData()==0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            System.out.println(pil);
            if(pil == 0) { btPrint.doClick(); }
            jButton1.doClick();
        } else {
            JOptionPane.showMessageDialog(this,"Sebagian Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
        
        
    }
    private void update(){
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranOpOut  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        if(insertData()==0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
            if(pil == 0) btPrint.doClick();
            jButton1.doClick();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Mengubah Sebagian Data.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void delete(){
        int err=0;
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  TranOpOut  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                if(con.queryUpdate(sql)<=0){
                    err++;
                } 
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
        btBatal.doClick();
        
        System.out.println(deleteRow);
    }
    
    private void print(){
        Double total =0.0;
        sql = "SELECT a.id,a.ctran,a.cnbkt,a.ccode,b.cname as gudang,a.cyear,a.dtran,a.cacckas,c.cdesc as acc_bank,"
                + "a.caccll,d.cdesc as acc_kas\n" +
            ",a.namnt,a.cnote,a.cusername,a.cjenis_name,a.ckendaraan,a.codometer,a.nik,a.nik_cname\n" +
            "FROM TranOpOut a join mgudang b on a.ccode=b.ccode \n" +
            "left join MAccount c on a.cacckas=c.caccnt\n" +
            "left join MAccount d on a.caccll=d.caccnt\n" +
            "where a.cnbkt = '"+cnbkt_print+"' and a.ccode='"+tcode.getText()+"'\n" +
            " ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                
                String ket = rs.getString("cnote");
                
                if(rs.getString("cjenis_name").length()>0) {
                    ket = ket+", Jenis "+rs.getString("cjenis_name");
                }
                if(rs.getString("nik_cname").length()>0) {
                    ket = ket+", Karyawan "+rs.getString("nik_cname");
                }
                if(rs.getString("ckendaraan").length()>0) {
                    ket = ket+", Nopol "+rs.getString("ckendaraan");
                }
                if(rs.getString("codometer").length()>0) {
                    ket = ket+", Odometer "+rs.getString("codometer");
                }
                model.addRow(new Object[]{
                    rs.getString("ctran"),
                    rs.getString("cnbkt"),
                    rs.getString("ccode"),rs.getString("gudang"),
                    rs.getString("cyear"),rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cacckas"),rs.getString("acc_bank"),
                    rs.getString("caccll"),rs.getString("acc_kas"),
                    ket,rs.getDouble("namnt"),
                });
                
                total+=rs.getDouble("namnt");
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranOpOut.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
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
        tbuktis = new javax.swing.JTextField();
        jDate2 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        jDate1 = new com.toedter.calendar.JDateChooser();
        tkets = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jLabel17 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tket = new javax.swing.JTextField();
        tcode = new javax.swing.JTextField();
        tbukti = new javax.swing.JTextField();
        tacc = new javax.swing.JTextField();
        tnilai = new javax.swing.JTextField();
        btPrint = new javax.swing.JButton();
        jButton6 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        btAdd = new javax.swing.JButton();
        jLabel7 = new javax.swing.JLabel();
        taccll = new javax.swing.JTextField();
        taccll1 = new javax.swing.JTextField();
        tacc1 = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        jButton12 = new javax.swing.JButton();
        tbukti1 = new javax.swing.JTextField();
        jButton2 = new javax.swing.JButton();
        btBatal = new javax.swing.JButton();
        btHapus = new javax.swing.JButton();
        tnopol = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        todometer = new javax.swing.JTextField();
        jLabel16 = new javax.swing.JLabel();
        jLabel18 = new javax.swing.JLabel();
        tkaryawan = new javax.swing.JTextField();
        jButton8 = new javax.swing.JButton();
        tkaryawan1 = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        cJenis = new javax.swing.JComboBox();
        jButton4 = new javax.swing.JButton();
        jButton5 = new javax.swing.JButton();
        jLabel10 = new javax.swing.JLabel();
        mesin = new javax.swing.JComboBox();
        jLabel20 = new javax.swing.JLabel();
        tkode = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btDelete = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        infoTotal = new javax.swing.JLabel();

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
        jLabel2.setText("Transaksi Operasional Keluar");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 20, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jLabel12.setText("No Bukti");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        tbuktis.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbuktisKeyPressed(evt);
            }
        });
        jPanel6.add(tbuktis, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 140, -1));

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(530, 30, 150, -1));

        jLabel13.setText("Periode ");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

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
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 30, 150, -1));

        tkets.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketsKeyPressed(evt);
            }
        });
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 340, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("Keterangan");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));

        jLabel17.setText("Gudang");
        jPanel6.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        tcodes.setEditable(false);
        tcodes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodesFocusLost(evt);
            }
        });
        jPanel6.add(tcodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 110, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 30, 30, -1));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No", "Gudang", "Nama Gudang", "Nomor Bukti", "Kode", "Tanggal", "Akun Debet", "Akun Desc (Debet)", "Akun Lainnya", "Lainnya (Desc)", "Keterangan", "Nominal", "Jenis", "Karyawan", "Nopol", "Odometer", "Mesin"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable2.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
        jTable2.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable2MouseClicked(evt);
            }
        });
        jScrollPane2.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(1).setMinWidth(40);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(40);
            jTable2.getColumnModel().getColumn(2).setMinWidth(80);
            jTable2.getColumnModel().getColumn(2).setMaxWidth(80);
            jTable2.getColumnModel().getColumn(3).setMinWidth(150);
            jTable2.getColumnModel().getColumn(4).setMinWidth(120);
            jTable2.getColumnModel().getColumn(6).setMinWidth(90);
            jTable2.getColumnModel().getColumn(6).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(7).setMinWidth(100);
            jTable2.getColumnModel().getColumn(8).setMinWidth(150);
            jTable2.getColumnModel().getColumn(9).setMinWidth(100);
            jTable2.getColumnModel().getColumn(10).setMinWidth(150);
            jTable2.getColumnModel().getColumn(11).setMinWidth(150);
            jTable2.getColumnModel().getColumn(13).setMinWidth(150);
            jTable2.getColumnModel().getColumn(14).setMinWidth(150);
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
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 998, Short.MAX_VALUE)
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
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 277, Short.MAX_VALUE)
                .addGap(18, 18, 18)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Gudang");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Transaksi");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 40, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Bukti");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, 72, 20));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Account Kas");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 100, -1, 20));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Keterangan");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 160, 72, 20));

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

        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        tket.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketKeyPressed(evt);
            }
        });
        jPanel1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 160, 330, -1));
        jPanel1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 10, 90, -1));

        tbukti.setEditable(false);
        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 70, 90, -1));

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
        jPanel1.add(tacc, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 100, 90, -1));

        tnilai.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnilaiKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tnilaiKeyTyped(evt);
            }
        });
        jPanel1.add(tnilai, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 10, 120, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(351, 70, 130, -1));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 130, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 10, 200, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Nominal");
        jLabel11.setToolTipText("");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 10, -1, 20));

        btAdd.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        btAdd.setText("Add");
        btAdd.setMargin(new java.awt.Insets(2, 2, 2, 2));
        btAdd.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btAddActionPerformed(evt);
            }
        });
        btAdd.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btAddKeyPressed(evt);
            }
        });
        jPanel1.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(790, 200, 80, 25));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Account Beban");
        jPanel1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 130, -1, 20));

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
        jPanel1.add(taccll, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 130, 90, -1));

        taccll1.setEditable(false);
        taccll1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(taccll1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 130, 200, -1));

        tacc1.setEditable(false);
        tacc1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(tacc1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 100, 200, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 10, 30, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 100, 30, -1));

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

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/reset-20.png"))); // NOI18N
        jPanel1.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 70, 30, -1));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jPanel1.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(720, 200, 70, 25));

        btHapus.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btHapus.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btHapusActionPerformed(evt);
            }
        });
        btHapus.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btHapusKeyPressed(evt);
            }
        });
        jPanel1.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 200, 30, 25));

        tnopol.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnopolFocusLost(evt);
            }
        });
        tnopol.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnopolKeyPressed(evt);
            }
        });
        jPanel1.add(tnopol, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 40, 120, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Nopol");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 40, -1, 20));

        todometer.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                todometerFocusLost(evt);
            }
        });
        todometer.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                todometerKeyPressed(evt);
            }
        });
        jPanel1.add(todometer, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 70, 120, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Odometer");
        jPanel1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 70, -1, 20));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Karyawan");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 100, -1, 20));

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
        jPanel1.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 100, 90, 20));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(770, 100, 30, 20));

        tkaryawan1.setEditable(false);
        tkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(tkaryawan1, new org.netbeans.lib.awtextra.AbsoluteConstraints(810, 100, 180, 20));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Jenis");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 190, 70, 20));

        cJenis.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jPanel1.add(cJenis, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 190, 300, -1));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/update-left-rotation-15.png"))); // NOI18N
        jButton4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton4ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 190, 30, -1));

        jButton5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jPanel1.add(jButton5, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 40, 30, -1));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Mesin");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 130, 40, 20));

        mesin.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jPanel1.add(mesin, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 130, 180, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Kode Internal/Eksternal");
        jLabel20.setToolTipText("");
        jPanel1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 160, 130, 20));
        jPanel1.add(tkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 160, 50, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        jButton1.setText("Cancel");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete All");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Akun (Debit)", "Akun Lainnya (Kredit)", "Keterangan", "Nominal", "tgl", "cnbkt", "Jenis", "Jenis Ket", "NIK", "Karyawan", "Nopol", "Odometer", "Mesin", "MesinID", "Kode"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false
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
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(1).setMinWidth(130);
            jTable1.getColumnModel().getColumn(1).setMaxWidth(130);
            jTable1.getColumnModel().getColumn(2).setMinWidth(130);
            jTable1.getColumnModel().getColumn(2).setMaxWidth(130);
            jTable1.getColumnModel().getColumn(3).setMinWidth(150);
            jTable1.getColumnModel().getColumn(5).setMinWidth(0);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(8).setMinWidth(100);
            jTable1.getColumnModel().getColumn(9).setMinWidth(100);
            jTable1.getColumnModel().getColumn(10).setMinWidth(100);
        }

        infoTotal.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        infoTotal.setText("Total Transaksi : ");
        infoTotal.setToolTipText("");

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addComponent(infoTotal)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(btDelete)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(btSave, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jButton1, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 998, Short.MAX_VALUE)
                    .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 239, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 139, Short.MAX_VALUE)
                .addGap(18, 18, 18)
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jButton1)
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

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
       if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed
       
    }//GEN-LAST:event_jDateTranKeyPressed

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

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
                // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void tnilaiKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnilaiKeyTyped
        rfa.filterAngka(keyssDef, evt);
    }//GEN-LAST:event_tnilaiKeyTyped

    private void btAddActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btAddActionPerformed
        addData();
    }//GEN-LAST:event_btAddActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount()==2){
            int baris = jTable1.getSelectedRow();
            loadEditItem(baris);
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void btBatalActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btBatalActionPerformed
        deffBatal();
    }//GEN-LAST:event_btBatalActionPerformed

    private void taccKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccKeyPressed
        rfa.EnterNextFocus(evt, taccll);
    }//GEN-LAST:event_taccKeyPressed

    private void taccllKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccllKeyPressed
        rfa.EnterNextFocus(evt, tket);
    }//GEN-LAST:event_taccllKeyPressed

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        rfa.EnterNextFocus(evt, tnilai);        // TODO add your handling code here:
    }//GEN-LAST:event_tketKeyPressed

    private void tnilaiKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnilaiKeyPressed
        rfa.EnterNextFocus(evt, tnopol);        // TODO add your handling code here:
    }//GEN-LAST:event_tnilaiKeyPressed

    private void btAddKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btAddKeyPressed
        if(evt.getKeyCode()==10) btAdd.doClick();
    }//GEN-LAST:event_btAddKeyPressed

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        rfa.FieldCapital(tket);
    }//GEN-LAST:event_tketFocusLost

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

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
                            save();
                        }
                    }, 1); 
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btHapusActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btHapusActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete_row();
        }
        
    }//GEN-LAST:event_btHapusActionPerformed

    private void btHapusKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btHapusKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_btHapusKeyPressed

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 4, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed
        if(tacc.isEnabled()) rfa.EnterNextFocus(evt, tacc);
        else if(taccll.isEnabled()) rfa.EnterNextFocus(evt, taccll);
    }//GEN-LAST:event_tbukti1KeyPressed

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

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode()==10)loadData();
    }//GEN-LAST:event_tbuktisKeyPressed

    private void tketsKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketsKeyPressed
        if(evt.getKeyCode()==10)loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_tketsKeyPressed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        deffBatal();
        clear();
        deff();
        LastCode();
        loadData();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        if(con.akses(akses, rfa.PRINT)) print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            tacc.setText(a.caccnt);
            tacc1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            taccll.setText(a.caccnt);
            taccll1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void taccFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccFocusLost
        sql = "select * from maccount where caccnt = '"+tacc.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, tacc1);
            while(rs.next()){  tacc1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
    }//GEN-LAST:event_taccFocusLost

    private void taccllFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccllFocusLost
        sql = "select * from maccount where caccnt = '"+taccll.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, taccll1);
            while(rs.next()){  taccll1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_taccllFocusLost

    private void tnopolFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnopolFocusLost
        rfa.FieldCapital(tnopol);
    }//GEN-LAST:event_tnopolFocusLost

    private void tnopolKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnopolKeyPressed
        rfa.EnterNextFocus(evt, todometer);  
    }//GEN-LAST:event_tnopolKeyPressed

    private void todometerFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_todometerFocusLost
        rfa.FieldCapital(todometer);
    }//GEN-LAST:event_todometerFocusLost

    private void todometerKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_todometerKeyPressed
        rfa.EnterNextFocus(evt, btAdd);  
    }//GEN-LAST:event_todometerKeyPressed

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
        }
    }//GEN-LAST:event_jButton8ActionPerformed

    private void jButton4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton4ActionPerformed
        loadJenisOprasional();
    }//GEN-LAST:event_jButton4ActionPerformed

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


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAdd;
    private javax.swing.JButton btBatal;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JComboBox cJenis;
    private javax.swing.JLabel infoTotal;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton4;
    private javax.swing.JButton jButton5;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
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
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTable jtable_print;
    private javax.swing.JComboBox mesin;
    private javax.swing.JTextField tacc;
    private javax.swing.JTextField tacc1;
    private javax.swing.JTextField taccll;
    private javax.swing.JTextField taccll1;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawan1;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tkode;
    private javax.swing.JTextField tnilai;
    private javax.swing.JTextField tnopol;
    private javax.swing.JTextField todometer;
    // End of variables declaration//GEN-END:variables
}
