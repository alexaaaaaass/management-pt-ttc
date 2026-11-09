/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package RawMaterial;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogPurchaseOrder;
import DialogPublicTP.DialogSatuan;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Purchase.PaymentPurchase;
import Purchase.PurchaseOrder;
import Purchase.PurchaseRequest;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Calendar;
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
import javax.swing.RowFilter;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableRowSorter;
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
public class RawMaterialOut extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="ROW MATERIAL OUT";
    String idKas="R2";
    ArrayList<String> deleteRow;
    ArrayList<String> cproductWO;
    public RawMaterialOut(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        cproductWO = new ArrayList<>();
        clear();
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
        btBatal.setVisible(false);
        btHapus.setVisible(false);
        btDebet.setEnabled(true);
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        
        Calendar calendar = Calendar.getInstance();
        Date today = calendar.getTime();
        calendar.add(Calendar.DAY_OF_YEAR, -7);
        Date addDays = calendar.getTime();
        
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(addDays);
        
        tcodes.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        tacc.setText(deffProgram.get("acc_produksi"));
        taccll.setText(deffProgram.get("acc_materialin"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
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
        
        LastCode();
        hitungTotalTransaksi();
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tket,tproduk,tproduk1,tproduk2,tqty,tsatuan,tsatuanhigh,tsatuanlow,two,two1,tcode2,tcode3);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        deleteRow.clear();
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        rfa.empty(MenuAdmin.keyssDef,tproduk,tproduk1,tproduk2,tqty,tsatuan,tsatuanhigh,tsatuanlow,tketproduk);
        
    }
    
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranRawMaterialOut where  ccode='"+tcode.getText()+"' and cnbkt like '%"+"RMO/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/"+Login.subdefault+"-")+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("RMO/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/")+Login.subdefault+"-");
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
    } 
    private void loadData(){
        sql = "select a.id,a.cnbkt,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cnote,\n" +
            "b.cname as gudang,sum(c.nqty) as total,\n" +
            "a.cworkorder + (case when isnull(d.kode_customer,'') != '' then ' - '+e.cname else '' end) as cworkorder ,d.cproduct as produk_wo\n" +
            "from TranRawMaterialOut a join MGudang b on a.ccode=b.ccode\n" +
            "join TranRawMaterialOutD c on a.cnbkt=c.cnbkt and a.ccode=c.ccode\n" +
            "left join PWorkOrder d on a.ccode = d.ccode and a.cworkorder=d.cnbkt\n" +
            "left join MCustomer e on d.kode_customer=e.kode_customer\n" +
            "where a.ccode = '"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
            "and a.cnote like '%"+tkets.getText()+"%' "
                + "group by a.id,a.cnbkt,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cnote,b.cname,d.cproduct,a.cworkorder,d.kode_customer,e.cname\n"
                + " order by a.id desc";
        System.out.println(sql);
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("gudang"),
                    rs.getString("cnbkt"),"",
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cnote"),rs.getDouble("total"),
                    rs.getString("cworkorder"),rs.getString("produk_wo")
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
        sql = "select a.*,d.cproduct from TranRawMaterialOut a left join PWorkOrder d on a.ccode = d.ccode and a.cworkorder=d.cnbkt where a.id='"+id+"'  ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                
                no++;
                
                tbukti.setText(rs.getString("cnbkt").substring(0, rs.getString("cnbkt").length()-4));
                tbukti1.setText(rs.getString("cnbkt").substring(rs.getString("cnbkt").length()-4, rs.getString("cnbkt").length()));
                tket.setText(rs.getString("cnote"));
                tcode2.setText(rs.getString("ccodeDest"));
                jDateTran.setDate(rs.getDate("dtran"));
                two.setText(rs.getString("cworkorder"));
                two1.setText(rs.getString("cproduct"));
                tacc.requestFocus();
                taccll.requestFocus();
                tket.requestFocus();
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        if(!two.getText().isEmpty()){
            loadDetailWO(two.getText());
        }
        
        
        String bkt = cnbkt;
        sql="select a.*,b.cnameProduct \n" +
        "from TranRawMaterialOutD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
        "where a.cnbkt = '"+bkt+"' and a.ccode = '"+tcode.getText()+"'";
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                System.out.println(rs.getString("id"));
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cproduct"),
                    rs.getString("cgroup"),
                    rs.getString("cnameProduct"),
                    rs.getDouble("nqty"),0.0,
                    0.0,0.0,rs.getString("nunit"),rfa.formatDecimal3.format(rs.getDouble("nunit_bigvalue")),rfa.formatDecimal3.format(rs.getDouble("nunit_smallvalue")),
                    null,null,
                    rfa.formatDecimal3.format(rs.getDouble("ncogs")), 
                    rs.getString("cnote"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        hitungTotalTransaksi();
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns();
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTable1.getValueAt(baris, 0).toString();
        
        if(jTable1.getValueAt(baris,1)!=null) tproduk.setText(jTable1.getValueAt(baris, 1).toString());
        if(jTable1.getValueAt(baris,4)!=null) tqty.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris, 4).toString())));
        if(jTable1.getValueAt(baris,8)!=null) tsatuan.setText(jTable1.getValueAt(baris, 8).toString());
        if(jTable1.getValueAt(baris,9)!=null) tsatuanhigh.setText(jTable1.getValueAt(baris, 9).toString());
        if(jTable1.getValueAt(baris,10)!=null) tsatuanlow.setText(jTable1.getValueAt(baris, 10).toString());
        if(jTable1.getValueAt(baris,14)!=null) tketproduk.setText(jTable1.getValueAt(baris, 14).toString());
        tproduk.requestFocus();
        tqty.requestFocus();
        btAdd.setText("Ubah");
        btBatal.setVisible(true);
        btHapus.setVisible(true);
    }
    private void loadDetailWO(String cnbkt){
        cproductWO.clear();
        sql="select a.*,b.cnameProduct from PWorkOrderD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.cnbkt='"+cnbkt+"' and a.ccode='"+tcode.getText()+"'";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                cproductWO.add(rs.getString("cproduct"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private Double loadLastHPP(){
        Double nilai=0.0;
        Double out=0.0;
        sql="select cgroup,cproduct,sum(nqty*nunit_smallvalue)*-1 as terpakai from MaterialStock\n" +
            "where kstatus='OUT' and  dtran<='"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"' "
                + "and cgroup='"+tproduk1.getText()+"' and cproduct='"+tproduk.getText()+"'\n" +
            "group by cgroup,cproduct";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                out = rs.getDouble("terpakai");
            }
        } catch (SQLException ex) {
            Logger.getLogger(RawMaterialOut.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        System.out.println(out);
        Double hitung=0.0;
        sql="select * from Hppbahan\n" +
            "where cgroup = '"+tproduk1.getText()+"' and cproduct ='"+tproduk.getText()+"'\n" +
            "order by cgroup,cproduct,dtran";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                hitung+= rs.getDouble("qty");
                if(out<hitung) {
                    nilai=rs.getDouble("cogs");
                    break;
                }
                nilai=rs.getDouble("cogs");
            }
        } catch (SQLException ex) {
            Logger.getLogger(RawMaterialOut.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        return nilai;
    }
    
    private Boolean cekSave(){
        if(tproduk.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Produk wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tqty.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Quantity wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tsatuan.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Satuan wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tqty.getText().toString().indexOf(",")>0) {
            JOptionPane.showMessageDialog(this,"Qty tidak boleh koma (,) - harus titik (.)", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(btAdd.getText().equalsIgnoreCase("Add")){
            for(int i=0; i<jTable1.getRowCount();i++){
                if(tproduk.getText().equals(jTable1.getValueAt(i, 1).toString()) && tsatuan.getText().equals(jTable1.getValueAt(i, 7).toString())) {
                    JOptionPane.showMessageDialog(this,"Kode Produk sudah ada dalam table", "Info", JOptionPane.ERROR_MESSAGE);
                    return false;
                }
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
                -1,tproduk.getText(),tproduk1.getText(),tproduk2.getText(),
                Double.valueOf(tqty.getText()),0,0,0,
                tsatuan.getText(),tsatuanhigh.getText(),tsatuanlow.getText(),
                null,null,
                loadLastHPP(),
                tketproduk.getText(),
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(iddetail, barisEdit, col++);
            jTable1.setValueAt(tproduk.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduk1.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduk2.getText(), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tqty.getText()), barisEdit, col++);
            jTable1.setValueAt(0, barisEdit, col++);
            jTable1.setValueAt(0, barisEdit, col++);
            jTable1.setValueAt(0, barisEdit, col++);
            jTable1.setValueAt(tsatuan.getText(), barisEdit, col++);
            jTable1.setValueAt(tsatuanhigh.getText(), barisEdit, col++);
            jTable1.setValueAt(tsatuanlow.getText(), barisEdit, col++);
            col++;
            col++;
            jTable1.setValueAt(rfa.formatDecimal3.format(loadLastHPP()), barisEdit, col++); 
            jTable1.setValueAt(tketproduk.getText(), barisEdit, col++);
            
        }
        hitungTotalTransaksi();
        deffBatal();
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns();
    }
    private void hitungTotalTransaksi(){
        Double totalNilai=0.0;
//        for(int i=0; i<jTable1.getRowCount(); i++){
//            totalNilai+=Double.valueOf(jTable1.getValueAt(i, 4).toString());
//        }
        infoTotal.setText("");
        if(jTable1.getRowCount()>0){
            btDebet.setEnabled(false);
        }
    }
    private void hitungTotalIn(){
        Double qty=0.0,harga=0.0,disc=0.0,totalsub=0.0,total=0.0,totalAll=0.0;
        if(!tqty.getText().isEmpty()) qty = Double.valueOf(tqty.getText());
        
        
        total = qty*harga;
        totalAll = total-disc;
    }
    
    private int insertData(){
        int err= 0;
        for(int i=0; i<jTable1.getRowCount();i++){
            if("-1".equalsIgnoreCase(jTable1.getValueAt(i, 0).toString())) {
                //INSERT
                sql="INSERT INTO TranRawMaterialOutD (cnbkt,ccode,cproduct,cgroup,"
                        + "nqty,nunit,"
                        + "fstatus,cusername,nunit_bigvalue,nunit_smallvalue,ncogs,cnote)\n" 
                        + "VALUES\n" 
                        + "('"+tbukti.getText()+tbukti1.getText()+"','"+tcode.getText()+"',"
                        + "'"+jTable1.getValueAt(i,1).toString()+"',"
                        + "'"+jTable1.getValueAt(i, 2).toString()+"',"
                        + "'"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)).replaceAll(",", "")+"',"
                        + "'"+jTable1.getValueAt(i, 8).toString()+"',"
                        + "'1','"+Login.user+"',"
                        + "'"+jTable1.getValueAt(i,9).toString().replaceAll(",", "")+"',"
                        + "'"+jTable1.getValueAt(i,10).toString().replaceAll(",", "")+"',"
                        + "'"+jTable1.getValueAt(i,13).toString().replaceAll(",", "")+"',"
                        + "'"+jTable1.getValueAt(i,14).toString().replaceAll(",", "")+"')";
            } else {  
                //UPDATE
                sql="update TranRawMaterialOutD set cnbkt='"+tbukti.getText()+tbukti1.getText()+"',ccode='"+tcode.getText()+"',"
                        + "cproduct='"+jTable1.getValueAt(i,1).toString()+"',"
                        + "cgroup='"+jTable1.getValueAt(i, 2).toString()+"',"
                        + "nqty='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"',"
                        + "nunit='"+jTable1.getValueAt(i, 8).toString()+"',"
                        + "fstatus='1',"
                        + "cusername='"+Login.user+"', \n"
                        + "nunit_bigvalue='"+jTable1.getValueAt(i,9).toString().replaceAll(",", "")+"',"
                        + "nunit_smallvalue='"+jTable1.getValueAt(i,10).toString().replaceAll(",", "")+"',"
                        + "ncogs='"+jTable1.getValueAt(i,13).toString().replaceAll(",", "")+"', "
                        + "cnote='"+jTable1.getValueAt(i,14).toString().replaceAll(",", "")+"' "
                        + " where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
            
            }
            
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    private void save(){
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        String due_date = "NULL";
        sql="insert into TranRawMaterialOut (ctran,cnbkt,ccode,ccodeDest,cyear,dtran,cacckas,caccll,cnote,fstatus,cusername,cworkorder) values "
                + " ('"+idKas+"','"+tbukti.getText()+tbukti1.getText()+"',"
                + "'"+tcode.getText()+"','"+tcode2.getText()+"',"
                + "'"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy")+"','"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"',"
                + "'"+tacc.getText()+"','"+taccll.getText()+"',"
                + "'"+tket.getText()+"','1','"+Login.user+"','"+two.getText()+"')";
        if(con.queryUpdate(sql)>0) {
            if(insertData()==0){
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
        } else {
            JOptionPane.showMessageDialog(this,"Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void update(){
        String due_date = "NULL";
        
        sql="update TranRawMaterialOut set cnbkt='"+tbukti.getText()+tbukti1.getText()+"',ccode='"+tcode.getText()+"',ccodeDest='"+tcode2.getText()+"',"
                + "cyear='"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy")+"',dtran='"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"',"
                + "cnote='"+tket.getText()+"',fstatus=1,cusername='"+Login.user+"',cworkorder='"+two.getText()+"' where id='"+id+"'";
        
        if(con.queryUpdate(sql)>0) {
            for(int i=0; i<deleteRow.size();i++){
                sql="delete from  TranRawMaterialOutD  where id = '"+deleteRow.get(i)+"' ";
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
        
        
    }
    private void delete(){
        int err=0;
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranRawMaterialOutD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  TranRawMaterialOutD  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                if(con.queryUpdate(sql)<=0){
                    err++;
                } 
            }
            
        }
        sql="delete from  TranRawMaterialOut  where id = '"+id+"' ";
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
    private void delete_row(){
        if(!"-1".equals(iddetail)) {
            deleteRow.add(iddetail);
        }
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.removeRow(barisEdit);
        btBatal.doClick();
        
        System.out.println(deleteRow);
    }
    
    private void print(){
        HashMap param = new HashMap();
        param.clear();
                
        String pengaju="";
        Double total =0.0;
        
        sql="select a.*,bb.cdesc as akundesc,b.cname as gudangtujuan,c.cgroup,c.cproduct from TranRawMaterialOut a \n" +
                "left join MAccount bb on a.cacckas=bb.caccnt\n" +
            "left join mgudang b on a.ccodeDest=b.ccode\n" +
            "left join PWorkOrder c on a.cworkorder=c.cnbkt\n" +
            "where a.ccode = '"+tcode.getText()+"' and a.cnbkt = '"+cnbkt_print+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                param.put("pt", deffProgram.get("pt"));
                param.put("alamat", deffProgram.get("alamat"));
                param.put("telp", "Telp. "+deffProgram.get("telp"));
                param.put("mail", "Email : "+deffProgram.get("mail"));
                param.put("bukti",rs.getString("cnbkt"));
                param.put("tanggal",rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"));
                param.put("akun",rs.getString("cacckas")+" - "+rs.getString("akundesc"));
                param.put("ket",rs.getString("cnote"));
                param.put("gudangtujuan",rs.getString("ccodeDest")+" - "+rs.getString("gudangtujuan"));
                param.put("wo",rs.getString("cworkorder"));
                param.put("produk_wo",rs.getString("cproduct"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        sql="select a.*,b.cnameproduct from TranRawMaterialOutD a join mproduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+cnbkt_print+"'";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    nomor++,
                    rs.getString("cgroup"),
                    rs.getString("cnameproduct"),
                    rs.getDouble("nqty"),
                    rs.getString("nunit"),
                    rs.getString("cnote"),
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranMaterialOut.jrxml";
            
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

        tsatuanhigh = new javax.swing.JTextField();
        tsatuanlow = new javax.swing.JTextField();
        tproduk1 = new javax.swing.JTextField();
        jScrollPane5 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        jPanel3 = new javax.swing.JPanel();
        jLabel14 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        taccll = new javax.swing.JTextField();
        tacc = new javax.swing.JTextField();
        btDebet = new javax.swing.JButton();
        btKredit = new javax.swing.JButton();
        taccll1 = new javax.swing.JTextField();
        tacc1 = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        tcode2 = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        tcode3 = new javax.swing.JTextField();
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
        jLabel16 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jTextField13 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        jScrollPane4 = new javax.swing.JScrollPane();
        tket = new javax.swing.JTextArea();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        btHapus = new javax.swing.JButton();
        btBatal = new javax.swing.JButton();
        btAdd = new javax.swing.JButton();
        tproduk = new javax.swing.JTextField();
        jLabel17 = new javax.swing.JLabel();
        jLabel18 = new javax.swing.JLabel();
        tsatuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        tqty = new javax.swing.JTextField();
        tproduk2 = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        btPrint = new javax.swing.JButton();
        jLabel19 = new javax.swing.JLabel();
        two = new javax.swing.JTextField();
        jLabel21 = new javax.swing.JLabel();
        jButton11 = new javax.swing.JButton();
        two1 = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        tketproduk = new javax.swing.JTextField();
        infoTotal = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();

        tsatuanhigh.setText("jTextField1");

        tsatuanlow.setText("jTextField1");

        tproduk1.setText("jTextField1");

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null}
            },
            new String [] {
                "no", "group", "product", "qty", "satuan", "cnote"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane5.setViewportView(jtable_print);

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Account Kredit");
        jPanel3.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Account Debet");
        jPanel3.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 90, 20));

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
        jPanel3.add(taccll, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 90, -1));

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
        jPanel3.add(tacc, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 90, -1));

        btDebet.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btDebet.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDebetActionPerformed(evt);
            }
        });
        jPanel3.add(btDebet, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 110, 30, -1));

        btKredit.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        btKredit.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btKreditActionPerformed(evt);
            }
        });
        jPanel3.add(btKredit, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 140, 30, -1));

        taccll1.setEditable(false);
        taccll1.setBackground(new java.awt.Color(153, 153, 255));
        taccll1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel3.add(taccll1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 140, 200, -1));

        tacc1.setEditable(false);
        tacc1.setBackground(new java.awt.Color(153, 153, 255));
        tacc1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel3.add(tacc1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 110, 200, -1));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Gudang Tujuan");

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });

        tcode3.setEditable(false);
        tcode3.setBackground(new java.awt.Color(153, 153, 255));
        tcode3.setForeground(new java.awt.Color(255, 255, 255));

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Raw Material Keluar");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(530, 30, -1, 20));

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(560, 30, 120, -1));

        jLabel13.setText("Periode ");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 30, -1, 20));

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
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 30, 120, -1));

        tkets.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tketsKeyReleased(evt);
            }
        });
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 60, 290, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("Keterangan");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 60, -1, 20));

        jLabel16.setText("Gudang");
        jPanel6.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

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

        jTextField13.setEditable(false);
        jTextField13.setBackground(new java.awt.Color(204, 204, 255));
        jTextField13.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField13.setText("Detail");

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No", "Gudang", "Nomor Bukti", "PO Request", "Tanggal", "Keterangan", "Total Qty", "WO/Perintah Kerja", "WO Produk"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable2.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable2MouseClicked(evt);
            }
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                jTable2MouseEntered(evt);
            }
        });
        jScrollPane3.setViewportView(jTable2);
        if (jTable2.getColumnModel().getColumnCount() > 0) {
            jTable2.getColumnModel().getColumn(0).setMinWidth(0);
            jTable2.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(1).setMinWidth(70);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(70);
            jTable2.getColumnModel().getColumn(4).setMinWidth(0);
            jTable2.getColumnModel().getColumn(4).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(7).setMinWidth(0);
            jTable2.getColumnModel().getColumn(7).setMaxWidth(0);
        }

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Group", "Product", "Desc", "Qty", "Harga", "Total ", "Disc", "Total All"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jScrollPane2.setViewportView(jTable3);
        if (jTable3.getColumnModel().getColumnCount() > 0) {
            jTable3.getColumnModel().getColumn(4).setMinWidth(0);
            jTable3.getColumnModel().getColumn(4).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(5).setMinWidth(0);
            jTable3.getColumnModel().getColumn(5).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(6).setMinWidth(0);
            jTable3.getColumnModel().getColumn(6).setMaxWidth(0);
            jTable3.getColumnModel().getColumn(7).setMinWidth(0);
            jTable3.getColumnModel().getColumn(7).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1150, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, 326, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE))
                    .addComponent(jScrollPane3)
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.Alignment.TRAILING))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 132, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 128, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 100, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Transaksi");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Bukti");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 72, 20));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Keterangan");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 72, -1));

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

        jPanel1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 330, 80));

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
        jPanel1.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 180, 30, 30));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jPanel1.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(710, 180, 70, 30));

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
        jPanel1.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(780, 180, 80, 30));

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
        jPanel1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 50, 90, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("No WO / SPK");
        jPanel1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 20, 120, 20));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Keterangan Bahan");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 140, 130, 20));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        tsatuan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tsatuanFocusLost(evt);
            }
        });
        jPanel1.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 80, 90, -1));

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
        jPanel1.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(760, 80, 30, -1));

        tqty.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tqtyCaretUpdate(evt);
            }
        });
        tqty.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tqtyKeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tqtyKeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tqtyKeyTyped(evt);
            }
        });
        jPanel1.add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 110, 80, -1));

        tproduk2.setEditable(false);
        tproduk2.setBackground(new java.awt.Color(153, 153, 255));
        tproduk2.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tproduk2, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 50, 200, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(760, 50, 30, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 70, 130, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Bahan/Produk");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 50, 90, 20));
        jPanel1.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 20, 90, -1));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Satuan");
        jPanel1.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 80, 90, 20));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(760, 20, 30, -1));

        two1.setEditable(false);
        two1.setBackground(new java.awt.Color(153, 153, 255));
        two1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(two1, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 20, 200, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Qty");
        jPanel1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 110, 90, 20));
        jPanel1.add(tketproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 140, 330, -1));

        infoTotal.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        infoTotal.setText("Total Transaksi : Rp. 1.000.000.000,-");
        infoTotal.setToolTipText("");

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "Kode Product", "Kode Group", "Item", "Qty", "Harga", "Disc", "Total", "Satuan ", "Satuan_nilai", "Satuan_nilaikecil", "ID Supplier", "Supplier", "HPP", "Keterangan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false, false, false
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
            jTable1.getColumnModel().getColumn(0).setMinWidth(50);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(50);
            jTable1.getColumnModel().getColumn(5).setMinWidth(0);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(6).setMinWidth(0);
            jTable1.getColumnModel().getColumn(6).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(7).setMinWidth(0);
            jTable1.getColumnModel().getColumn(7).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(9).setMinWidth(0);
            jTable1.getColumnModel().getColumn(9).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(11).setMinWidth(0);
            jTable1.getColumnModel().getColumn(11).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(12).setMinWidth(0);
            jTable1.getColumnModel().getColumn(12).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addContainerGap()
                        .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jScrollPane1)
                            .addGroup(jPanel5Layout.createSequentialGroup()
                                .addComponent(infoTotal)
                                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, 604, Short.MAX_VALUE)
                                .addComponent(btDelete, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addGap(13, 13, 13)
                                .addComponent(btSave, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                                .addGap(13, 13, 13)
                                .addComponent(btCancel, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE))))
                    .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 234, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 153, Short.MAX_VALUE)
                .addGap(18, 18, 18)
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(infoTotal, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.PREFERRED_SIZE, 20, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                        .addComponent(btDelete)
                        .addComponent(btSave)
                        .addComponent(btCancel)))
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
                    .addGroup(layout.createSequentialGroup()
                        .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 546, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE))
                    .addComponent(jTabbedPane1))
                .addContainerGap())
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

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 3).toString();
            cnbkt_print = cnbkt;
            loadEdit();
        } else if(evt.getClickCount() == 1) {
            int baris = jTable2.getSelectedRow();
            String bkt = jTable2.getValueAt(baris, 3).toString();
            sql="select a.*,b.cnameProduct \n" +
            "from TranRawMaterialOutD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.cnbkt = '"+bkt+"' and a.ccode='"+tcodes.getText()+"'";
            rfa.clear_table(MenuAdmin.keyssDef, jTable3);
            DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{
                        rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameProduct"),
                        rs.getDouble("nqty"),0.0,0.0,
                        0.0,0.0,rs.getString("nunit")
                    });
                }
            } catch (SQLException ex) {
                Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
            }

        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jTable2MouseEntered(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseEntered
        // TODO add your handling code here:
    }//GEN-LAST:event_jTable2MouseEntered

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        rfa.EnterNextFocus(evt, tproduk);
    }//GEN-LAST:event_tketKeyPressed

    private void tketKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyTyped

    }//GEN-LAST:event_tketKeyTyped

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
        if(evt.getKeyCode()==10) tket.requestFocus();
    }//GEN-LAST:event_taccllKeyPressed

    private void btDebetActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDebetActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            tacc.setText(a.caccnt);
            tacc1.setText(a.cdesc);
        }
    }//GEN-LAST:event_btDebetActionPerformed

    private void btKreditActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btKreditActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            taccll.setText(a.caccnt);
            taccll1.setText(a.cdesc);
        }
    }//GEN-LAST:event_btKreditActionPerformed

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

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

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

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        sql = "select * from mproduk where cproduct = '"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tproduk1.setText(rs.getString("cgroup"));
            tproduk2.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
        rfa.EnterNextFocus(evt, tqty);
    }//GEN-LAST:event_tprodukKeyPressed

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        if(tproduk2.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Isi Produk/Bahan terlebih dahulu.", "Info", JOptionPane.ERROR_MESSAGE);
            return;
        }
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
            rs = con.queryResult("select a.*,\n" +
                    "isnull(a.small_value,1) as n1,isnull(b.small_value,1) as n2,isnull(c.small_value,1) as n3,isnull(d.small_value,1) as n4,isnull(e.small_value,1) as n5,isnull(f.small_value,1) as n6,isnull(g.small_value,1) as n7,\n" +
                    "a.cname as t1,b.cname as t2,c.cname as t3,d.cname as t4,e.cname as t5,f.cname as t6,g.cname as t7\n" +
                    "from MSatuan a\n" +
                    "left join MSatuan b on a.id_turunan=b.id\n" +
                    "left join MSatuan c on b.id_turunan=c.id\n" +
                    "left join MSatuan d on c.id_turunan=d.id\n" +
                    "left join MSatuan e on d.id_turunan=e.id\n" +
                    "left join MSatuan f on e.id_turunan=f.id\n" +
                    "left join MSatuan g on f.id_turunan=g.id\n" +
                    "where a.cname = '"+a.unit+"'");
            Double nilaisatuan=0.0;
            try {
                while(rs.next()){   
                    if(satuanterkecil.equals(rs.getString("t1")))  nilaisatuan=1.0;
                    if(satuanterkecil.equals(rs.getString("t2")))  nilaisatuan=rs.getDouble("n1");
                    if(satuanterkecil.equals(rs.getString("t3")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2");
                    if(satuanterkecil.equals(rs.getString("t4")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3");
                    if(satuanterkecil.equals(rs.getString("t5")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3")*rs.getDouble("n4");
                    if(satuanterkecil.equals(rs.getString("t6")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3")*rs.getDouble("n4")*rs.getDouble("n5");
                    if(satuanterkecil.equals(rs.getString("t7")))  nilaisatuan=rs.getDouble("n1")*rs.getDouble("n2")*rs.getDouble("n3")*rs.getDouble("n4")*rs.getDouble("n5")*rs.getDouble("n6");
                }
            } catch (SQLException ex) {  }
            System.out.println(nilaisatuan);
            tsatuanlow.setText(rfa.formatDecimal3.format(nilaisatuan));
            
            btAdd.requestFocus();
        }
        
    }//GEN-LAST:event_btsatuanActionPerformed

    private void btsatuanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuanKeyPressed
        if(evt.getKeyCode()==10) btsatuan.doClick();
    }//GEN-LAST:event_btsatuanKeyPressed

    private void tqtyCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tqtyCaretUpdate
        hitungTotalIn();
    }//GEN-LAST:event_tqtyCaretUpdate

    private void tqtyKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyPressed
        if(tsatuan.getText().isEmpty()) {
            if(evt.getKeyCode() ==10) {
            btsatuan.doClick();
            }
        } else {
            rfa.EnterNextFocus(evt, btAdd);
        }
        
    }//GEN-LAST:event_tqtyKeyPressed

    private void tqtyKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyTyped
        //rfa.filterAngka(keyssDef, evt);
    }//GEN-LAST:event_tqtyKeyTyped

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogProduk a = new DialogProduk(0);
        a.setVisible(true);
        if(a.status){
            if(cproductWO.size()>0) {
                Boolean cek=false;
                for(int i=0; i<cproductWO.size();i++){
                    if(a.kodeBarang.equalsIgnoreCase(cproductWO.get(i))) {
                        cek = true;
                        break;
                    }
                }
//                if(!cek) {
//                    JOptionPane.showMessageDialog(this,"Jenis Material tidak terdaftar dalam WO tersebut", "Save", JOptionPane.ERROR_MESSAGE);
//                    return;
//                }
            }
            tproduk.setText(a.kodeBarang);
            tproduk1.setText(a.groupBarang);
            tproduk2.setText(a.namaBarang);
            tsatuan.setText(a.unit);
            tsatuanhigh.setText("1");
            tsatuanlow.setText("1");

            tqty.requestFocus();
        }
    }//GEN-LAST:event_jButton10ActionPerformed

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        if(con.akses(akses, rfa.PRINT)) print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(tket.getText().isEmpty()){
                JOptionPane.showMessageDialog(this,"Keterangan wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
                return;
            }
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

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        deleteRow = new ArrayList<>();
        push_button();
        clear();
        deff();
        loadData();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount()==2){
            deffBatal();
            int baris = jTable1.getSelectedRow();
            loadEditItem(baris);
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogWO a = new DialogWO();
        a.setVisible(true);
        if(a.status){
            two.setText(a.cnbkt);
            two1.setText(a.produk);
            loadDetailWO(a.cnbkt);
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void tsatuanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tsatuanFocusLost
        sql="select * from msatuan where cname = '"+tsatuan.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tsatuanhigh.setText(rfa.formatDecimal3.format(rs.getDouble("big_value")));
                tsatuanlow.setText(rfa.formatDecimal3.format(rs.getDouble("small_value")));
            }
        } catch (Exception e){
            
        }
    }//GEN-LAST:event_tsatuanFocusLost

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode2.setText(a.ccode);
            tcode3.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

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

    private void tqtyKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyReleased
        if(tqty.getText().toString().indexOf(",")>0) {
            tqty.setText(tqty.getText().replaceAll(",", "."));
        }
    }//GEN-LAST:event_tqtyKeyReleased

    private void tketsKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketsKeyReleased
        DefaultTableModel tablee = (DefaultTableModel)jTable2.getModel();
        String search = tkets.getText();
        TableRowSorter<DefaultTableModel> tr = new TableRowSorter<DefaultTableModel>(tablee);
        jTable2.setRowSorter(tr);
        tr.setRowFilter(RowFilter.regexFilter(search.toUpperCase())); 
    }//GEN-LAST:event_tketsKeyReleased


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAdd;
    private javax.swing.JButton btBatal;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDebet;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btKredit;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JLabel infoTotal;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private javax.swing.JLabel jLabel1;
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
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField13;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTextField tacc;
    private javax.swing.JTextField tacc1;
    private javax.swing.JTextField taccll;
    private javax.swing.JTextField taccll1;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcode2;
    private javax.swing.JTextField tcode3;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextField tketproduk;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tproduk1;
    private javax.swing.JTextField tproduk2;
    private javax.swing.JTextField tqty;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    private javax.swing.JTextField two;
    private javax.swing.JTextField two1;
    // End of variables declaration//GEN-END:variables
}
