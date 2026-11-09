/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Receive;

import Delivery.*;
import Accounting.*;
import DialogPublicTP.DialogFileReceive;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogInvoice;
import DialogPublicTP.DialogMembers;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogSatuan;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Purchase.PurchaseRequest;
import RawMaterial.StockOpname;
import Tools.ConnectDB;
import java.awt.Color;
import java.awt.Rectangle;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Scanner;
import java.util.Timer;
import java.util.TimerTask;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JFileChooser;
import javax.swing.JOptionPane;
import javax.swing.KeyStroke;
import javax.swing.filechooser.FileFilter;
import javax.swing.filechooser.FileNameExtensionFilter;
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
public class ReceiveAntarGudang extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="RECEIVE ANTAR GUDANG";
    String idKas="D1";
    ArrayList<String> deleteRow;
    ArrayList<String> idSkat;
    public ReceiveAntarGudang(String mode) {
        initComponents();
        if("IN".equals(mode)) {
            jLabel2.setText("Transaksi Kas Masuk");
        } else if("OUT".equals(mode)) {
            jLabel2.setText("Transaksi Kas Keluar");
        }
        deleteRow = new ArrayList<>();
        push_button();
        clear();
        deffBatal();
        deff();
        loadData();
        
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

    private void deff(){
        btDelete.setVisible(false);
        btHapus.setVisible(false);
        btBatal.setVisible(false);
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(new Date());
        rfa.clear_table(keyssDef, jTable1);
        
        tcodes.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        LastCode();
        loadSkat();
    }
    private void loadSkat(){
        idSkat = new ArrayList<>();
        idSkat.clear();
        cbSkat.removeAll();
        cbSkat.removeAllItems();
        idSkat.add("-1");
        cbSkat.addItem("Pilih Skat");
        
        rs = con.queryResult("Select * from mskat where ccode='"+tcode.getText()+"' ");
        try {
            while(rs.next()){
                idSkat.add(rs.getString("id"));
                cbSkat.addItem(rs.getString("cname"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(StockOpname.class.getName()).log(Level.SEVERE, null, ex);
        }
        cbSkat.setSelectedIndex(0);
        
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tket,tproduk,tsatuanhigh,tsatuanlow,
                tsatuanhigh,tsatuanlow,tket);
        rfa.clear_table(keyssDef, jTable1);
        deleteRow.clear();
        tket.setText("");
    } 
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranReceive where ccode='"+tcode.getText()+"' and cnbkt like '%"+"REC/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/"+Login.subdefault+"-")+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("REC/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/")+Login.subdefault+"-");
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
    } 
    private void loadData(){
        sql="select * from TranReceive where ccode='"+tcodes.getText()+"' and "
                + " cnbkt like '%"+tbuktis.getText()+"%' and (dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"') "
                + " order by dtran desc,cnbkt desc";
       
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cnbkt"),rs.getString("ccode"),rs.getString("ccodedesc"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),rs.getString("cpenerima"),rs.getString("cnote"),
                    
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
     
    }
    private void loadEdit(){
        btSave.setText("Update");
        btDelete.setVisible(true);
        String bkt ="";
        
        sql="select a.*,b.cname as asal,c.csupir as supirkirim from TranReceive a \n" +
                "join MGudang b on a.ccodedesc=b.ccode\n" +
                "join TranDeliveryGudang c on a.ccodedesc=c.ccode and a.cnbkt_kirim=c.cnbkt where a.cnbkt='"+cnbkt+"' and a.ccode= '"+tcodes.getText()+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tbukti.setText(rs.getString("cnbkt").substring(0, rs.getString("cnbkt").length()-4));
                tbukti1.setText(rs.getString("cnbkt").substring(rs.getString("cnbkt").length()-4, rs.getString("cnbkt").length()));
                tcode.setText(rs.getString("ccode"));
                ccodeasal.setText(rs.getString("ccodedesc"));
                ccodeasal_name.setText(rs.getString("asal"));
                tinfobkt.setText("Supir : "+rs.getString("supirkirim"));
                tbuktikirim.setText(rs.getString("cnbkt_kirim"));
                tpenerima.setText(rs.getString("cpenerima"));
                jDateTran.setDate(rs.getDate("dtran"));
                tket.setText(rs.getString("cnote"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(ReceiveAntarGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="select a.*,b.cname as skat from TranReceiveD  a left join mskat b on a.idskat=b.id   \n" +
            "where a.cnbkt = '"+cnbkt+"'  and a.ccode= '"+tcode.getText()+"'";
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("cbox"),rs.getString("cgroup"),rs.getString("cproduct"),
                    rs.getDouble("nqty"),rs.getString("csatuan"),rs.getDouble("nqty_small"),
                    rs.getInt("idskat"),rs.getString("skat"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
        ccodeasal.requestFocus();
        tproduk.requestFocus();
        tket.requestFocus();
    }
    private Boolean cekSave(){
        if(tproduknama.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Satuan Slop wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tsatuan.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Slop wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tproduk.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Produk wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(cbSkat.getItemCount()>1) {
            if(cbSkat.getSelectedIndex() == 0) {
                JOptionPane.showMessageDialog(this,"Pilih Skat Terlebih dahulu", "Info", JOptionPane.ERROR_MESSAGE);
                return false;
            }
        }
        return true;
    }
//    private void hitungPack(){
//        Double pack=0.0,sloppack=0.0,slopval=0.0;
//        if(!tslopsatuan.getText().isEmpty()) {
//            sloppack = Double.valueOf(tslopsatuan.getText());
//        }
//        if(!tslop.getText().isEmpty()) {
//            slopval = Double.valueOf(tslop.getText());
//        }
//        
//        pack = sloppack*slopval;
//        tpack.setText(rfa.formatDecimal3.format(pack));
//    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTable1.getValueAt(baris, 0).toString();
        
        tbox.setText(jTable1.getValueAt(baris,1).toString());
        tprodukgroup.setText(jTable1.getValueAt(baris,2).toString());
        tproduk.setText(jTable1.getValueAt(baris,3).toString());
        
        tqty.setText(rfa.formatDecimal3.format(jTable1.getValueAt(baris, 4)));
        tsatuan.setText(jTable1.getValueAt(baris,5).toString());
 
        rs = con.queryResult("select * from mproduk where cproduct='"+tproduk.getText()+"'");
        try {
            while(rs.next()){   tproduknama.setText(rs.getString("cdesc"));  }
        } catch (SQLException ex) {  }
        
        btAdd.setText("Ubah");
        btBatal.setVisible(true);
        btHapus.setVisible(true);
        tproduk.requestFocus();
    }
    
    private void addData(){
        if(!cekSave()) {
            return;
        }
        String satuanterkecil="";
        rs = con.queryResult("select * from mproduk where cproduct='"+tproduk.getText()+"'");
        try {
            while(rs.next()){   satuanterkecil = rs.getString("cunit");   }
        } catch (SQLException ex) {  }
        
        Double nilaiterkecil = con.loadNilaiSatuanTerkecil(tsatuan.getText(), satuanterkecil);
        
        if(btAdd.getText().equalsIgnoreCase("Add")) {
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            model.addRow(new Object[]{
                -1,
                tbox.getText(),
                tprodukgroup.getText(),tproduk.getText(),
                Integer.valueOf(tqty.getText()),
                tsatuan.getText(),
                nilaiterkecil*Integer.valueOf(tqty.getText()),
                idSkat.get(cbSkat.getSelectedIndex()),cbSkat.getSelectedItem().toString(),
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(iddetail, barisEdit, col++);
            jTable1.setValueAt(tbox.getText(), barisEdit, col++);
            jTable1.setValueAt(tprodukgroup.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduk.getText(), barisEdit, col++);
            jTable1.setValueAt(Integer.valueOf(tqty.getText()), barisEdit, col++);
            jTable1.setValueAt(tsatuan.getText(), barisEdit, col++);
            jTable1.setValueAt(nilaiterkecil*Integer.valueOf(tqty.getText()), barisEdit, col++);
            jTable1.setValueAt(idSkat.get(cbSkat.getSelectedIndex()), barisEdit, col++);
            jTable1.setValueAt(cbSkat.getSelectedItem().toString(), barisEdit, col++);
        }
        btBatal.doClick();
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        iddetail = "";
        rfa.empty(MenuAdmin.keyssDef,tbox,tsatuan,tsatuanhigh,tsatuanlow,tproduk,tprodukgroup,tproduknama);
         cbSkat.setSelectedIndex(0);
    }
    private void delete_row(){
        if(!"-1".equals(String.valueOf(iddetail))) {
            deleteRow.add(String.valueOf(iddetail));
        }
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.removeRow(barisEdit);
        btBatal.doClick();
        
    }
    
    
    
    private int insertData(){
        int err= 0;
        for(int i=0; i<jTable1.getRowCount();i++){
            if("-1".equalsIgnoreCase(jTable1.getValueAt(i, 0).toString())) {
                HashMap<String,String> listObj = new HashMap<String,String>();    
                listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
                listObj.put("ccode",tcode.getText());
                listObj.put("cbox",jTable1.getValueAt(i, 1).toString());
                listObj.put("cgroup",jTable1.getValueAt(i, 2).toString());
                listObj.put("cproduct",jTable1.getValueAt(i, 3).toString());
                listObj.put("csatuan",jTable1.getValueAt(i,5).toString());
                listObj.put("nqty",rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
                listObj.put("nqty_small",rfa.formatDecimal3.format(jTable1.getValueAt(i, 6)));
                listObj.put("idskat",rfa.formatDecimal3.format(Integer.valueOf(jTable1.getValueAt(i, 7).toString())));
                listObj.put("cusername",Login.user);
                sql=con.insertValue("TranReceiveD", listObj);
            } else {
                HashMap<String,String> listObj = new HashMap<String,String>();        
                listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
                listObj.put("ccode",tcode.getText());
                listObj.put("cbox",jTable1.getValueAt(i, 1).toString());
                listObj.put("cgroup",jTable1.getValueAt(i, 2).toString());
                listObj.put("cproduct",jTable1.getValueAt(i, 3).toString());
                listObj.put("csatuan",jTable1.getValueAt(i,5).toString());
                listObj.put("nqty",rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
                listObj.put("nqty_small",rfa.formatDecimal3.format(jTable1.getValueAt(i, 6)));
                listObj.put("idskat",rfa.formatDecimal3.format(Integer.valueOf(jTable1.getValueAt(i, 7).toString())));
                listObj.put("cusername_update",Login.user);
                listObj.put("dupdat",rfa.DateString(keyssDef, new Date(), "yyyy-MM-dd"));
                sql=con.updateValue("TranReceiveD", listObj);
                sql+=" where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
        
            }
            
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    private boolean ceksave(){
        boolean status = true;
        for(int i=0; i<jTable1.getRowCount(); i++){
            if(jTable1.getValueAt(i, 7) != null) {
                if(jTable1.getValueAt(i, 7).toString().equals("")) {
                    JOptionPane.showMessageDialog(this,"Pilih penempatan skat terlebih dahulu", "Save", JOptionPane.ERROR_MESSAGE);
                    return false;
                }
                if(Integer.valueOf(jTable1.getValueAt(i, 7).toString()) <= 0) {
                    JOptionPane.showMessageDialog(this,"Pilih penempatan skat terlebih dahulu", "Save", JOptionPane.ERROR_MESSAGE);
                    return false;
                }
            } else {
                JOptionPane.showMessageDialog(this,"Pilih penempatan skat terlebih dahulu", "Save", JOptionPane.ERROR_MESSAGE);
                    return false;
            }
        }
        if(jTable1.getRowCount()<=0){
            JOptionPane.showMessageDialog(this,"Detail pengiriman belum ada", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        return status;
    }
    private void save(){
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
        listObj.put("ctran","DP");
        listObj.put("ccode",tcode.getText());
        listObj.put("ccodedesc",ccodeasal.getText());
        listObj.put("cyear",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy"));
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("cnote",tket.getText());
        listObj.put("cpenerima",tpenerima.getText());
        listObj.put("cnbkt_kirim",tbuktikirim.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("TranReceive", listObj);
        if(con.queryUpdate(sql)>0) {
            insertData();
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clear();
            deff();
            LastCode();
            loadData();
        } else {
            JOptionPane.showMessageDialog(this,"Header gagal di simpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
        
    }
    private void update(){
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranReceiveD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
        listObj.put("ctran","DP");
        listObj.put("ccode",tcode.getText());
        listObj.put("ccodedesc",ccodeasal.getText());
        listObj.put("cyear",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy"));
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("cpenerima",tpenerima.getText());
        listObj.put("cnote",tket.getText());
        listObj.put("cusername",Login.user);
        sql=con.updateValue("TranReceive", listObj);
        sql+=" where id = '"+id+"' ";
        System.out.println(sql);
        if(con.queryUpdate(sql)>0) {
            insertData();
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
            clear();
            deff();
            LastCode();
            loadData();
        } else {
            JOptionPane.showMessageDialog(this,"Header Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
        
        
        
    }
    private void delete(){
        int err=0;
        sql="delete from  TranReceiveD  where cnbkt = '"+tbukti.getText()+tbukti1.getText()+"' ";
        con.queryUpdate(sql);
        sql="delete from  TranReceive  where id = '"+id+"' ";
        if(con.queryUpdate(sql)<=0){
            err++;
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
    private void print(){
        HashMap param = new HashMap();
        param.clear();
                
        String pengaju="";
        Double total =0.0;
        
        sql="select a.*,b.cname as gudangtujuan from TranReceive a left join MGudang b on a.ccodedesc=b.ccode\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+cnbkt_print+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                param.put("title", "BUKTI BANK MASUK");
                param.put("pt", deffProgram.get("pt"));
                param.put("alamat", deffProgram.get("alamat"));
                param.put("telp", "Telp. "+deffProgram.get("telp"));
                param.put("mail", "Email : "+deffProgram.get("mail"));
                param.put("bukti",rs.getString("cnbkt"));
                param.put("customer",rs.getString("ccodedesc")+" - "+rs.getString("gudangtujuan"));
                param.put("tanggal",rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"));
                param.put("via",rs.getString("cvia"));
                param.put("supir",rs.getString("csupir"));
                param.put("nopol",rs.getString("cnopol"));
                param.put("note",rs.getString("cnote"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(DeliveryOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="select * from TranReceiveD\n" +
            "where ccode='"+tcode.getText()+"' and cnbkt='"+cnbkt_print+"'";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    nomor++,
                    rs.getString("cbox"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("csatuan"),
                    rs.getDouble("cslop"),
                    rs.getDouble("cslop_value"),
                });
                
                total+=rs.getDouble("cslop_value");
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranDeliveryAntarGudang.jrxml";
            
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void bp_nt_x(){
        String txt="";
        DecimalFormat decf = new DecimalFormat("000.00");
        try {
            final JFileChooser chooser = new JFileChooser();
            chooser.setDialogTitle("Specify a Folder to Save");
            chooser.setFileSelectionMode(JFileChooser.DIRECTORIES_ONLY);
            chooser.setCurrentDirectory(new File("C:\\Users\\Administrator\\Documents"));
            int retrival = chooser.showSaveDialog(null);
            
            if (retrival == chooser.APPROVE_OPTION) {
                File r = new File(chooser.getSelectedFile() + "\\Synkronisasi Delivery Product "+(tbukti.getText()+tbukti1.getText()).replaceAll("/", "")+".txt");
                FileWriter pw = new FileWriter(r);
                PrintWriter pr = new PrintWriter(pw);
                txt="DELIVERY";
                pr.println(txt);
                
                //bahan pembantu nt dan x
                sql="select a.cnbkt,a.ccode,a.ccodedesc,a.dtran,b.cbox,b.cgroup,b.cproduct,b.csatuan,b.cslop,b.cslop_value \n" +
                    "from TranReceive a join TranReceiveD b on a.cnbkt=b.cnbkt \n" +
                    "where a.ccode='"+tcode.getText()+"' and a.ccodedesc='"+ccodeasal.getText()+"' and a.cnbkt='"+tbukti.getText()+tbukti1.getText()+"'";
                rs=con.queryResult(sql);
                while(rs.next()){
                    txt="SENDP" + "~";
                    txt+=rs.getString("cnbkt")+ "~";
                    txt+=rs.getString("ccode")+ "~";
                    txt+=rs.getString("ccodedesc") + "~";
                    txt+=rs.getString("dtran")+ "~";
                    txt+=rs.getString("cbox") + "~";
                    txt+=rs.getString("cgroup")+ "~";
                    txt+=rs.getString("cproduct")+ "~";
                    txt+=rs.getString("csatuan")+ "~";
                    txt+=rs.getString("cslop")+ "~";
                    txt+=rs.getString("cslop_value")+ "~";
                    pr.println(txt.replaceAll("\n", " ").replaceAll("\t", " ") );
                }
                
                pw.close();
                JOptionPane.showMessageDialog(this, "File Telah Disimpan Di " + chooser.getSelectedFile() + "\\Delivery "+tbukti.getText()+tbukti1.getText()+".txt", "Informasi", JOptionPane.INFORMATION_MESSAGE);
            }
        } catch (IOException ex) {
            ex.printStackTrace();
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    
    private void upload_chose(){
        FileFilter filter = new FileNameExtensionFilter("Text File","txt");
        JFileChooser chooser = new JFileChooser();
        chooser.setFileFilter(filter);
        chooser.setDialogTitle("Specify a File to Import");
        chooser.setFileSelectionMode(JFileChooser.FILES_ONLY);
        chooser.setCurrentDirectory(new File("C:\\Users\\Administrator\\Documents"));
        int retrival = chooser.showSaveDialog(null);
        
        ArrayList list = new ArrayList();
        Iterator it = list.iterator();
        list.clear();
        boolean cek = false;
        int brs=0, line=0, pos1=0, pos2=0, index=0;
        int trantbcin1=0, detailtbcin1=0;
        String str="", txt="";
        
        if (retrival == JFileChooser.APPROVE_OPTION) {
            try {
                File r = new File(chooser.getSelectedFile().toString());
                Scanner in = new Scanner(r);
                
                String kode= in.nextLine();               
                if("DELIVERY".equalsIgnoreCase(kode)){
                    bp_nt_x_upload(chooser.getSelectedFile().toString());
                } 
                else {
                    JOptionPane.showMessageDialog(this, "File Tidak Dapat Dibaca", "Upload Failed", JOptionPane.ERROR_MESSAGE);
                    return;
                }
                
            } catch (FileNotFoundException ex) {
                ex.printStackTrace();
            } catch (Exception ex) {
                ex.printStackTrace();
            } 
        } 
    }
    private void bp_nt_x_upload(String filee){
        
        ArrayList list = new ArrayList();
        Iterator it = list.iterator();
        list.clear();
        boolean cek = false;
        int brs=0, line=0, pos1=0, pos2=0, index=0;
        int trantbcin1=0, detailtbcin1=0;
        String str="", txt="";
        String date1="",date2="";
        try {
            File r = new File(filee);
            Scanner in = new Scanner(r);
            if(!"DELIVERY".equalsIgnoreCase(in.nextLine())){
                JOptionPane.showMessageDialog(this, "File Tidak Dapat Dibaca", "Upload Failed", JOptionPane.ERROR_MESSAGE);
                return;
            }
            while(in.hasNextLine()){
                line++;
                in.nextLine();
            }
            in.reset();
            in=null;
            in=new Scanner(r);
            txt=in.nextLine().trim();

            jProgressBar1.setMinimum(0);
            jProgressBar1.setMaximum(line-1);
            jProgressBar1.setValue(0);
            jProgressBar1.setForeground(Color.BLUE);

            while(in.hasNextLine()){
                list.clear();
                str = in.nextLine();
                String[] data_parts = str.split("~");
                
                pos1 = str.indexOf("~");
                while (pos1 >= 0) {
                    pos2 = str.indexOf("~", pos1 + 1);
                    if(pos2>=pos1){
                        list.add(str.substring(pos1+1, pos2));
                    }
                    pos1 = pos2;
                }
                    
                if("SENDP-DEL".equals(data_parts[0])){
                    sql="delete from TranReceiveFile where ccode='"+data_parts[1]+"' and  ccodedesc='"+data_parts[2]+"'  and  cnbkt='"+data_parts[3]+"'  ";
                }
                else if("SENDP".equals(data_parts[0])){
                    HashMap<String,String> listObj = new HashMap<String,String>();  
                    int indexx=0;
                    listObj.put("cnbkt",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("ccode",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("ccodedesc",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("dtran",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("cbox",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("cgroup",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("cproduct",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("csatuan",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("cslop",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("cslop_value",list.get(indexx++).toString().replaceAll("'", "''"));
                    listObj.put("cball",list.get(indexx++).toString().replaceAll("'", "''"));
                   
                    sql=con.insertValue("TranReceiveFile", listObj);
                }
                
                sql = sql.replace("'null'","NULL");
                //System.out.println(sql);
                con.queryUpdate(sql);
                brs++;
                jProgressBar1.setValue(brs);
                Rectangle progressRect = jProgressBar1.getBounds();
                progressRect.x = 0;
                progressRect.y = 0;
                jProgressBar1.paintImmediately(progressRect);
            }
            
            JOptionPane.showMessageDialog(this, "Upload Telah Selesai dan Nomor Bukti sudah di Reset", "Upload Done", JOptionPane.INFORMATION_MESSAGE);
            
            
        } catch (FileNotFoundException ex) {
            ex.printStackTrace();
        } catch (Exception ex) {
            ex.printStackTrace();
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

        tsatuanhigh = new javax.swing.JTextField();
        tsatuanlow = new javax.swing.JTextField();
        tproduk = new javax.swing.JTextField();
        jScrollPane5 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jButton1 = new javax.swing.JButton();
        jLabel8 = new javax.swing.JLabel();
        jProgressBar1 = new javax.swing.JProgressBar();
        tprodukgroup = new javax.swing.JTextField();
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
        jLabel20 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField13 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        ccodeasal = new javax.swing.JTextField();
        btPrint = new javax.swing.JButton();
        ccodeasal_name = new javax.swing.JTextField();
        jScrollPane4 = new javax.swing.JScrollPane();
        tket = new javax.swing.JTextArea();
        jLabel14 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        jButton8 = new javax.swing.JButton();
        jLabel16 = new javax.swing.JLabel();
        jLabel17 = new javax.swing.JLabel();
        tbuktikirim = new javax.swing.JTextField();
        jButton9 = new javax.swing.JButton();
        jLabel21 = new javax.swing.JLabel();
        tinfobkt = new javax.swing.JTextField();
        jLabel25 = new javax.swing.JLabel();
        tpenerima = new javax.swing.JTextField();
        jPanel8 = new javax.swing.JPanel();
        jLabel26 = new javax.swing.JLabel();
        btHapus = new javax.swing.JButton();
        btBatal = new javax.swing.JButton();
        btAdd = new javax.swing.JButton();
        jScrollPane6 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jLabel18 = new javax.swing.JLabel();
        jLabel19 = new javax.swing.JLabel();
        jLabel10 = new javax.swing.JLabel();
        tqty = new javax.swing.JTextField();
        tsatuan = new javax.swing.JTextField();
        tproduknama = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        jButton10 = new javax.swing.JButton();
        jLabel23 = new javax.swing.JLabel();
        tbox = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        cbSkat = new javax.swing.JComboBox();

        tsatuanhigh.setText("jTextField1");

        tsatuanlow.setText("jTextField1");

        tproduk.setText("jTextField1");

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "no", "cbox", "cgroup", "cproduct", "csatuan", "cslop", "cslop_value"
            }
        ));
        jScrollPane5.setViewportView(jtable_print);

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/notepad-15.png"))); // NOI18N
        jButton1.setText("Upload File");
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Upload File");

        jProgressBar1.setMaximum(0);

        tprodukgroup.setText("jTextField1");

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Penerimaan Produk - Antar Gudang");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jLabel12.setText("No Bukti");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));
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

        jLabel20.setText("Gudang");
        jPanel6.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

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
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Nomor Bukti", "Gudang", "Asal", "Tanggal", "Bukti Kirim", "Penerima", "Keterangan"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable3.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable3MouseClicked(evt);
            }
        });
        jScrollPane3.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(0).setMinWidth(0);
            jTable3.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(4).setMinWidth(90);
            jTable3.getColumnModel().getColumn(4).setMaxWidth(90);
            jTable3.getColumnModel().getColumn(5).setMinWidth(0);
            jTable3.getColumnModel().getColumn(5).setMaxWidth(0);
        }

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null}
            },
            new String [] {
                "Box", "Group", "Product", "Jml", "Satuan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane2.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(1).setMinWidth(0);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(0);
        }

        jTextField13.setEditable(false);
        jTextField13.setBackground(new java.awt.Color(204, 204, 255));
        jTextField13.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField13.setText("Detail");

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1160, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jScrollPane3)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING, false)
                            .addComponent(jTextField13)
                            .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 361, Short.MAX_VALUE))))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane2, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE))
                    .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 308, Short.MAX_VALUE))
                .addGap(11, 11, 11)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel5.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Log");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Transaksi");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Penerimaan");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 130, 20));

        ccodeasal.setEditable(false);
        ccodeasal.setBackground(new java.awt.Color(153, 153, 255));
        ccodeasal.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        ccodeasal.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(ccodeasal, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 120, 90, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 80, 130, -1));

        ccodeasal_name.setEditable(false);
        ccodeasal_name.setBackground(new java.awt.Color(153, 153, 255));
        ccodeasal_name.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        ccodeasal_name.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(ccodeasal_name, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 120, 200, -1));

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

        jPanel1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 240, 330, 50));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Gudang Asal");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 120, 110, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete All");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(780, 410, 110, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(900, 410, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(1010, 410, 97, -1));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 90, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 20, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 20, 200, -1));

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
        jPanel1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 180, -1));

        tbukti.setEditable(false);
        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 120, -1));

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
        jPanel1.add(tbukti1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 80, 60, -1));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 120, 30, -1));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Penerima");
        jPanel1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 210, 110, 20));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Keterangan");
        jPanel1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 240, 72, -1));

        tbuktikirim.setEditable(false);
        tbuktikirim.setBackground(new java.awt.Color(153, 153, 255));
        tbuktikirim.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbuktikirim.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbuktikirim, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 150, 290, -1));

        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 150, 30, -1));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Bukti Pengiriman");
        jPanel1.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 150, 110, 20));

        tinfobkt.setEditable(false);
        tinfobkt.setBackground(new java.awt.Color(153, 153, 255));
        tinfobkt.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tinfobkt.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tinfobkt, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 180, 330, -1));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Info Pengiriman");
        jPanel1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 180, 110, 20));
        jPanel1.add(tpenerima, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 210, 330, -1));

        jPanel8.setBorder(javax.swing.BorderFactory.createTitledBorder(javax.swing.BorderFactory.createEtchedBorder(), "Detail Item", javax.swing.border.TitledBorder.DEFAULT_JUSTIFICATION, javax.swing.border.TitledBorder.DEFAULT_POSITION, new java.awt.Font("Tahoma", 0, 12))); // NOI18N
        jPanel8.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel26.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/qrcode.png"))); // NOI18N
        jPanel8.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 30, 40, 20));

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
        jPanel8.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(400, 340, 30, 25));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jPanel8.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(440, 340, 70, 25));

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
        jPanel8.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 340, 80, 25));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Box", "Group", "Product", "Jumlah", "Satuan", "qty_small", "idskat", "Skat"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Integer.class, java.lang.Object.class, java.lang.Integer.class, java.lang.Integer.class, java.lang.Object.class
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
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jScrollPane6.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(2).setMinWidth(0);
            jTable1.getColumnModel().getColumn(2).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(4).setMinWidth(80);
            jTable1.getColumnModel().getColumn(4).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(6).setMinWidth(0);
            jTable1.getColumnModel().getColumn(6).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(7).setMinWidth(0);
            jTable1.getColumnModel().getColumn(7).setMaxWidth(0);
        }

        jPanel8.add(jScrollPane6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 180, 570, 150));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel18.setText("Produk");
        jPanel8.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, 90, 20));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Satuan");
        jPanel8.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 90, 90, 20));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Jumlah Item");
        jPanel8.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 120, 90, 20));

        tqty.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tqty.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tqtyCaretUpdate(evt);
            }
        });
        jPanel8.add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 120, 90, -1));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 90, 250, -1));

        tproduknama.setEditable(false);
        tproduknama.setBackground(new java.awt.Color(153, 153, 255));
        tproduknama.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tproduknama, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 60, 250, -1));

        btsatuan.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btsatuan.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btsatuanActionPerformed(evt);
            }
        });
        btsatuan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btsatuanKeyPressed(evt);
            }
        });
        jPanel8.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 90, 30, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 60, 30, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel23.setText("No Box");
        jPanel8.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 90, 20));

        tbox.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tboxFocusLost(evt);
            }
        });
        jPanel8.add(tbox, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 30, 330, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Skat / Tempat");
        jPanel8.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 150, 100, 20));

        cbSkat.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jPanel8.add(cbSkat, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 150, 280, -1));

        jPanel1.add(jPanel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 20, 610, 380));

        jPanel5.add(jPanel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 10, 1150, 450));

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

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        if(evt.getKeyCode() == 10){
            tbox.requestFocus();
        }
    }//GEN-LAST:event_tketKeyPressed

    private void tketKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyTyped
        
    }//GEN-LAST:event_tketKeyTyped

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
            loadSkat();
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 4, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed
        //if(tacc.isEnabled()) rfa.EnterNextFocus(evt, tacc);
    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            ccodeasal.setText(a.ccode);
            ccodeasal_name.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton8ActionPerformed

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
                            if(ceksave()) save();
                        }
                    }, 1); 
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    if(ceksave()) update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        clear();
        deffBatal();
        deff();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 1) {
            int baris = jTable3.getSelectedRow();
            String bkt = jTable3.getValueAt(baris,1).toString();
            
            sql="select a.* from TranReceiveD a\n" +
            "where a.cnbkt = '"+bkt+"' and ccode='"+tcodes.getText()+"'";
            rfa.clear_table(MenuAdmin.keyssDef, jTable2);
            DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{
                        rs.getString("cbox"),rs.getString("cgroup"),rs.getString("cproduct"),
                        rs.getDouble("nqty"),rs.getString("csatuan"),
                    });
                }
            } catch (SQLException ex) {
                Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
            }
        } else if(evt.getClickCount() == 2) {
            int baris = jTable3.getSelectedRow();
            id = jTable3.getValueAt(baris, 0).toString();
            cnbkt = jTable3.getValueAt(baris, 1).toString();
            cnbkt_print = cnbkt;
            loadEdit();
        } 
    }//GEN-LAST:event_jTable3MouseClicked

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        upload_chose();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        DialogFileReceive a = new DialogFileReceive(ccodeasal.getText());
        a.setVisible(true);
        if(a.status) {
            tbuktikirim.setText(a.id);
            tinfobkt.setText("Supir : "+a.supir);
            
            sql="select a.* from TranDeliveryGudangD a\n" +
                "where a.ccode='"+ccodeasal.getText()+"' and a.cnbkt = '"+a.id+"'";
                rfa.clear_table(MenuAdmin.keyssDef, jTable1);
                DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
                rs = con.queryResult(sql);
                try {
                    while(rs.next()){
                        model.addRow(new Object[]{
                            -1,
                            rs.getString("cbox"),
                            rs.getString("cgroup"),rs.getString("cproduct"),
                            rs.getInt("nqty"),
                            rs.getString("csatuan"),
                            rs.getDouble("nqty_small")
                        });
                    }
                } catch (SQLException ex) {
                    Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
                }

        }
    }//GEN-LAST:event_jButton9ActionPerformed

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

    private void btHapusActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btHapusActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete_row();
        }
    }//GEN-LAST:event_btHapusActionPerformed

    private void btHapusKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btHapusKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_btHapusKeyPressed

    private void btBatalActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btBatalActionPerformed
        deffBatal();
    }//GEN-LAST:event_btBatalActionPerformed

    private void btAddActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btAddActionPerformed
        addData();
    }//GEN-LAST:event_btAddActionPerformed

    private void btAddKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btAddKeyPressed
        if(evt.getKeyCode()==10) btAdd.doClick();
    }//GEN-LAST:event_btAddKeyPressed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable1.getSelectedRow();
            loadEditItem(baris);
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void tqtyCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tqtyCaretUpdate
        
        
    }//GEN-LAST:event_tqtyCaretUpdate

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        String satuanterkecil="";
        rs = con.queryResult("select * from mproduk where cproduct='"+tproduk.getText()+"'");
        try {
            while(rs.next()){   satuanterkecil = rs.getString("cunit");   }
        } catch (SQLException ex) {  }
        DialogSatuan a = new DialogSatuan(satuanterkecil);
        a.setVisible(true);
        if(a.status){
            tsatuan.setText(a.unit);
            tsatuanhigh.setText(rfa.formatDecimal3.format(a.big_value));
            tsatuanlow.setText(rfa.formatDecimal3.format(a.small_value));
            System.out.println(tsatuanlow.getText());
        }
    }//GEN-LAST:event_btsatuanActionPerformed

    private void btsatuanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuanKeyPressed
        if(evt.getKeyCode()==10) btsatuan.doClick();
    }//GEN-LAST:event_btsatuanKeyPressed

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tproduknama.setText(a.namaBarang);

            tsatuan.setText("");
        } else {

        }
    }//GEN-LAST:event_jButton10ActionPerformed

    private void tboxFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tboxFocusLost
        sql = "select a.*,b.cgroup,b.cproduct from PBoxPacking a\n" +
        "join PBland b on a.cnbkt_bland=b.cnbkt where a.ccode = '"+tcode.getText()+"' and a.cnbkt+'@'+a.cbox='"+tbox.getText()+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tsatuan.setText(rs.getString("csatuan"));
                tproduk.setText(rs.getString("cproduct"));
                tprodukgroup.setText(rs.getString("cgroup"));
                tqty.setText("1");
                tproduk.requestFocus();
            }
        } catch (Exception e){
            e.printStackTrace();
        }
    }//GEN-LAST:event_tboxFocusLost



    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAdd;
    private javax.swing.JButton btBatal;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JComboBox cbSkat;
    private javax.swing.JTextField ccodeasal;
    private javax.swing.JTextField ccodeasal_name;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
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
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel26;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JProgressBar jProgressBar1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField13;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTextField tbox;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktikirim;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tinfobkt;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tpenerima;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tproduknama;
    private javax.swing.JTextField tqty;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    // End of variables declaration//GEN-END:variables
}
