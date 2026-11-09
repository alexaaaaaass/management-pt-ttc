/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Delivery;

import Accounting.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogInvoice;
import DialogPublicTP.DialogMembers;
import DialogPublicTP.DialogOrderCustomer;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogSatuan;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Production.WorkOrder;
import Purchase.PurchaseRequest;
import RawMaterial.StockOpname;
import Tools.ConnectDB;
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
public class DeliveryOrder extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="DELIVERY ORDER";
    String idKas="D1";
    String cgroup_po="", cproduct_po="",cproductname_po="";
    int produk_status = 0;
    ArrayList<String> deleteRow;
    ArrayList<String> idSkat;
    
    public DeliveryOrder(String mode) {
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
    }
    
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tket,tprodukgroup,tsatuanhigh,tsatuanlow,
                tsatuanhigh,tsatuanlow,tpack,tvia,tsupir,tnopol,tket,two);
        rfa.clear_table(keyssDef, jTable1);
        deleteRow.clear();
        tket.setText("");
        produk_status = 0;
    }
    
    private void LastCode(){
        int last = 0;
//        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranDeliveryOrder "
//                + "where  ccode='"+tcode.getText()+"' "
//                + "and cnbkt like '%"+"BKP/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/"+Login.subdefault+"-")+"%' ");
        rs = con.queryResult("select max(convert(int,left(cnbkt,2))) as lastno "
                + " from TranDeliveryOrder "
                + " where  ccode='"+tcode.getText()+"' "
                + " and month(dtran)= '"+rfa.DateString(keyssDef, jDateTran.getDate(),"MM")+"' ");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 2, last+1)+"/SJ/"+con.romawi(rfa.DateString(keyssDef, jDateTran.getDate(),"MM"))+"/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yy"));
        
    } 
    private void loadData(){
        sql = "select a.id,a.cnbkt,a.cnbkt_inv,a.ccode,a.kode_customer,c.cname,a.dtran,a.cvia,a.csupir,a.cnopol,a.cnote,\n" +
                "sum(b.nqty_small) as totalpack\n" +
                "from TranDeliveryOrder a left join TranDeliveryOrderD b on a.cnbkt=b.cnbkt\n" +
                "left join MCustomer c on a.kode_customer=c.kode_customer\n" +
                    "where a.ccode='"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
                    "group by a.id,a.cnbkt,a.cnbkt_inv,a.ccode,a.kode_customer,a.dtran,a.cvia,a.csupir,a.cnopol,a.cnote,c.cname\n"+
                "order by a.dtran desc,a.cnbkt desc,a.id desc";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cnbkt"),rs.getString("ccode"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cnbkt_inv"),rs.getString("cname"),
                    rs.getString("cvia"),rs.getString("csupir"),rs.getString("cnopol"),
                    rs.getString("cnote"),rs.getDouble("totalpack"),
                    
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
        
        sql="select * from TranDeliveryOrder where cnbkt='"+cnbkt+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){ 
                tbukti.setText(rs.getString("cnbkt"));
                tpocustomer.setText(rs.getString("cnbkt_inv"));
                tcode.setText(rs.getString("ccode"));
                tcustomerkode.setText(rs.getString("kode_customer"));
                jDateTran.setDate(rs.getDate("dtran"));
                tcustomerkode.setText(rs.getString("kode_customer"));
                tvia.setText(rs.getString("cvia"));
                tsupir.setText(rs.getString("csupir"));
                tnopol.setText(rs.getString("cnopol"));
                tket.setText(rs.getString("cnote"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(DeliveryOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="select a.*  from TranDeliveryOrderD  a    \n" +
            "where a.cnbkt = '"+cnbkt+"' and a.ccode='"+tcode.getText()+"' ";
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){                
                model.addRow(new Object[]{
                        rs.getString("id"),
                        rs.getString("cbox"),
                        rs.getString("cgroup"),rs.getString("cproduct"),
                        rs.getInt("nqty_box"),
                        rs.getInt("nqty"),
                        rs.getString("csatuan"),
                        rs.getDouble("nqty_small"),
                        rs.getString("produkalias"),
                        rs.getString("cnbkt_wo"),
                    });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        
        
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
        tcustomerkode.requestFocus();
        tproduk.requestFocus();
        tket.requestFocus();
    }
    private Boolean cekspk(String ccode,String spk){
        Boolean spkcek=false;
        sql="select * from PWorkOrder where ccode='"+ccode+"' and cnbkt='"+spk+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                spkcek=true;
            }
        } catch (SQLException ex) {
            Logger.getLogger(DeliveryOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        return spkcek;
    }
    private Boolean cekSave(){
        if(tsatuan.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Satuan wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tqty.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Jumlah item wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tproduknama.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Produk wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        } 
        if(two.getText().equals("") && produk_status == 1) {
            JOptionPane.showMessageDialog(this,"SPK wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        } 
        if(cekspk(tcode.getText(),two.getText()) == false && produk_status == 1) {
            JOptionPane.showMessageDialog(this,"Nomor SPK sudah tidak terdaftar karena dihapus/ada perubahan pada nomor SPK", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        } 
         
        if(produk_status == 1) {
            String produkPO="";
            Boolean cek=false;
            sql="select a.*,b.cnameProduct from PCustomerPOD a \n" +
                "join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
                "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+tpocustomer.getText()+"'";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    produkPO+=rs.getString("cnameProduct")+", ";
                    if(rs.getString("cgroup").equals(tprodukgroup.getText()) && rs.getString("cproduct").equals(tproduk.getText())) {
                        cek=true;
                    }
                }
            } catch (SQLException ex) {
                Logger.getLogger(DeliveryOrder.class.getName()).log(Level.SEVERE, null, ex);
            }
            if(!cek) {
                JOptionPane.showMessageDialog(this,"Data Produk ada Box tidak sesuai dengan Produk PO Customer\n"+
                            "dimana produk yang seharusnya adalah ("+produkPO+") bukan ("+tproduknama.getText()+")", "Info", JOptionPane.INFORMATION_MESSAGE);
                    return false;
            }
        }
        return true;
    }
    private void hitungPack(){
        try{
            Double ikat=0.0,pcskonversi=0.0,total=0.0;
            if(!tqty.getText().isEmpty()) {
                ikat = Double.valueOf(tqty.getText());
            }
            if(!tbox_qty.getText().isEmpty()) {
                pcskonversi = Double.valueOf(tbox_qty.getText());
            }
            
            total = ikat*pcskonversi;
            tpcs.setText(rfa.formatDecimal3.format(total));
            
        }catch(Exception e){
            
        }
        
//        Double pack=0.0,sloppack=0.0,slopval=0.0;
//        if(!tslopsatuan.getText().isEmpty()) {
//            sloppack = Double.valueOf(tslopsatuan.getText());
//        }
//        if(!tqty.getText().isEmpty()) {
//            slopval = Double.valueOf(tqty.getText());
//        }
//        
//        pack = sloppack*slopval;
//        tpack.setText(rfa.formatDecimal3.format(pack));
    }
    
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTable1.getValueAt(baris, 0).toString();
        
        if(jTable1.getValueAt(baris,1) != null) tbox.setText(jTable1.getValueAt(baris,1).toString());
        if(jTable1.getValueAt(baris,2) != null) tprodukgroup.setText(jTable1.getValueAt(baris,2).toString());
        if(jTable1.getValueAt(baris,3) != null) tproduk.setText(jTable1.getValueAt(baris,3).toString());
        if(jTable1.getValueAt(baris,4) != null) tbox_qty.setText(rfa.formatDecimal3.format(jTable1.getValueAt(baris, 4)));
        if(jTable1.getValueAt(baris,5) != null) tqty.setText(rfa.formatDecimal3.format(jTable1.getValueAt(baris, 5)));
        if(jTable1.getValueAt(baris,6) != null) tsatuan.setText(jTable1.getValueAt(baris,6).toString());
        if(jTable1.getValueAt(baris,7) != null) tpcs.setText(rfa.formatDecimal3.format(jTable1.getValueAt(baris, 7)));
        if(jTable1.getValueAt(baris,8) != null) tprodukalias.setText(jTable1.getValueAt(baris,8).toString());
        if(jTable1.getValueAt(baris,9) != null) two.setText(jTable1.getValueAt(baris, 9).toString());
        
        tsatuanhigh.setText("1");
        Double qtysatuan = Double.valueOf(jTable1.getValueAt(baris, 7).toString())/Double.valueOf(jTable1.getValueAt(baris, 5).toString());
        tsatuanlow.setText(rfa.formatDecimal3.format(qtysatuan));
        
        rs = con.queryResult("select * from mproduk where cproduct='"+jTable1.getValueAt(baris,3).toString()+"' and cgroup='"+jTable1.getValueAt(baris,2).toString()+"'");
        try {
            while(rs.next()){   tproduknama.setText(rs.getString("cnameproduct"));  }
        } catch (SQLException ex) { ex.printStackTrace(); }
       
        btAdd.setText("Ubah");
        btBatal.setVisible(true);
        btHapus.setVisible(true);
        tproduk.requestFocus();
    }
    private void loadPOCustomer(String id){
        System.out.println(id);
        cgroup_po = "";
        cproduct_po = "";
        cproductname_po = "";
        sql="select a.id,a.ccode,a.cnbkt,a.dtran,a.cmarketing,d.cname as marketing,\n" +
                "a.kode_customer,c.cname as customer,\n" +
                "b.cgroup,b.cproduct,e.cnameProduct,b.nqty,b.nunit,b.cgroup_bahan,b.cproduct_bahan,f.cnameProduct as cnameProductbahan,b.deadline\n" +
                "from PCustomerPO a \n" +
                "join PCustomerPOD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                "join MCustomer c on a.kode_customer=c.kode_customer\n" +
                "join MKaryawan d on a.cmarketing=d.nik\n" +
                "join MProduk e on b.cgroup=e.cgroup and b.cproduct=e.cproduct\n" +
                "left join MProduk f on b.cgroup_bahan=f.cgroup and b.cproduct_bahan=f.cproduct\n" +
                "where b.id = "+id;
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                tpocustomer.setText(rs.getString("cnbkt"));
                tcustomerkode.setText(rs.getString("kode_customer"));
                tcustomer.setText(rs.getString("customer"));
                tproduk.setText(rs.getString("cproduct"));
                tprodukgroup.setText(rs.getString("cgroup"));
                tproduknama.setText(rs.getString("cnameProduct"));
                tsatuan.setText(rs.getString("nunit"));
                tsatuanlow.setText("1");
                tsatuanhigh.setText("1");
                tqty.setText(rfa.formatDecimal3.format(rs.getDouble("nqty")));
                
                cgroup_po = (rs.getString("cgroup"));
                cproduct_po = (rs.getString("cproduct"));
                cproductname_po = (rs.getString("cnameProduct"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(WorkOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        
        sql="select * from PWorkOrder\n" +
            "where ccode='"+tcode.getText()+"' and cnbkt_order='"+tpocustomer.getText()+"' and cgroup='"+tprodukgroup.getText()+"' and cproduct='"+tproduk.getText()+"'";
        two.setText("");
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                two.setText(rs.getString("cnbkt")); 
            }
        } catch (SQLException ex) {
            Logger.getLogger(WorkOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
         
    }
    private void loadBox(){
        System.out.println("CEK DATA");
        sql="select a.*,aa.cworkorder,b.cgroup,b.cproduct,c.cnameProduct\n" +
            "from PBoxPackingD a\n" +
            "join PBoxPacking aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
            "join PWorkOrder b on aa.ccode=b.ccode and aa.cworkorder=b.cnbkt\n" +
            "join MProduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt+'~'+a.cbox_no =  '"+tbox.getText()+"' --and aa.cworkorder='"+tpocustomer.getText()+"' "; 
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                if(!cgroup_po.equals(rs.getString("cgroup")) ||
                        !cproduct_po.equals(rs.getString("cproduct"))
                        )  {
                    JOptionPane.showMessageDialog(this,"Data Produk ada Box tidak sesuai dengan Produk PO Customer\n"+
                            "dimana produk yang seharusnya adalah ("+cproductname_po+") bukan ("+rs.getString("cnameproduct")+")", "Info", JOptionPane.INFORMATION_MESSAGE);
                    return;

                }

                two.setText(rs.getString("cworkorder"));
                tproduk.setText(rs.getString("cproduct"));
                tprodukgroup.setText(rs.getString("cgroup"));
                tproduknama.setText(rs.getString("cnameproduct"));
                tprodukalias.setText(rs.getString("cnameproduct"));
                tsatuan.setText(rs.getString("csatuan"));
                tsatuanlow.setText("1");
                tsatuanhigh.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_small")/rs.getDouble("nqty")));

                tbox_qty.setText("1");
                tqty.setText(rfa.formatDecimal3.format(rs.getDouble("nqty")));
                tpcs.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_small")));
                btAdd.requestFocus();
            }   
        } catch (Exception e){
            e.printStackTrace();
        }
    }
    
    private void addData(){
        if(!cekSave()) {
            return;
        }
        
        String satuanterkecil="";
        rs = con.queryResult("select * from mproduk where cgroup='"+tprodukgroup.getText()+"' and cproduct='"+tproduk.getText()+"'");
        try {
            while(rs.next()){   satuanterkecil = rs.getString("cunit");    }
        } catch (SQLException ex) {  }
        
        Double nilaiterkecil = con.loadNilaiSatuanTerkecil(tsatuan.getText(), satuanterkecil);
        
        if(btAdd.getText().equalsIgnoreCase("Add")) {
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            model.addRow(new Object[]{
                -1,
                tbox.getText(),
                tprodukgroup.getText(),
                tproduk.getText(),
                Integer.valueOf(tbox_qty.getText()),
                Double.valueOf(tqty.getText()),
                tsatuan.getText(),
                Double.valueOf(tpcs.getText()),
                tprodukalias.getText(),
                two.getText(),
                satuanterkecil,
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(iddetail, barisEdit, col++);
            jTable1.setValueAt(tbox.getText(), barisEdit, col++);
            jTable1.setValueAt(tprodukgroup.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduk.getText(), barisEdit, col++);
            jTable1.setValueAt(Integer.valueOf(tbox_qty.getText()), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tqty.getText()), barisEdit, col++);
            jTable1.setValueAt(tsatuan.getText(), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tpcs.getText()), barisEdit, col++);
            jTable1.setValueAt(tprodukalias.getText(), barisEdit, col++);
            jTable1.setValueAt(two.getText(), barisEdit, col++);
            jTable1.setValueAt(satuanterkecil, barisEdit, col++);
        }
        
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns();
        
        //btBatal.doClick();
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        iddetail = "";
        rfa.empty(MenuAdmin.keyssDef,tbox_qty,tqty);
        tpcs.setText("0");
        tbox.requestFocus();
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        iddetail = "";
        rfa.empty(MenuAdmin.keyssDef,tslopsatuan,tqty,tpack,tbox,tsatuan,tsatuanhigh,tsatuanlow,tproduk,tprodukgroup,tproduknama,
                tbox_qty,tprodukalias);
        tpcs.setText("0");
        tbox.requestFocus();
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
                listObj.put("cnbkt",tbukti.getText());
                listObj.put("ccode",tcode.getText());
                listObj.put("cbox",jTable1.getValueAt(i, 1).toString());
                listObj.put("cgroup",jTable1.getValueAt(i, 2).toString());
                listObj.put("cproduct",jTable1.getValueAt(i, 3).toString());
                listObj.put("nqty_box",rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
                listObj.put("nqty",rfa.formatDecimal3.format(jTable1.getValueAt(i, 5)));
                listObj.put("csatuan",jTable1.getValueAt(i,6).toString());
                listObj.put("nqty_small",rfa.formatDecimal3.format(jTable1.getValueAt(i, 7)));
                listObj.put("produkalias",jTable1.getValueAt(i, 8).toString());
                listObj.put("cnbkt_wo",jTable1.getValueAt(i, 9).toString());
                listObj.put("csatuan_small",jTable1.getValueAt(i,10).toString());
                listObj.put("cusername",Login.user);
                sql=con.insertValue("TranDeliveryOrderD", listObj);
            } else {
                HashMap<String,String> listObj = new HashMap<String,String>();        
                listObj.put("cnbkt",tbukti.getText());
                listObj.put("ccode",tcode.getText());
                listObj.put("cbox",jTable1.getValueAt(i, 1).toString());
                listObj.put("cgroup",jTable1.getValueAt(i, 2).toString());
                listObj.put("cproduct",jTable1.getValueAt(i, 3).toString());
                listObj.put("nqty_box",rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
                listObj.put("nqty",rfa.formatDecimal3.format(jTable1.getValueAt(i, 5)));
                listObj.put("csatuan",jTable1.getValueAt(i,6).toString());
                listObj.put("nqty_small",rfa.formatDecimal3.format(jTable1.getValueAt(i, 7)));
                listObj.put("produkalias",jTable1.getValueAt(i, 8).toString());
                listObj.put("cnbkt_wo",jTable1.getValueAt(i, 9).toString());
                listObj.put("csatuan_small",jTable1.getValueAt(i,10).toString());
                listObj.put("cusername_update",Login.user);
                listObj.put("dupdat",rfa.DateString(keyssDef, new Date(), "yyyy-MM-dd"));
                sql=con.updateValue("TranDeliveryOrderD", listObj);
                sql+=" where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
        
            }
            System.out.println(sql);
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    
    private boolean ceksave(){
        boolean status = true;
         
        if(tcustomerkode.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Customer Wajib di isi", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(jTable1.getRowCount()<=0){
            JOptionPane.showMessageDialog(this,"Detail pengiriman belum ada", "Save", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!cekspk(tcode.getText(),jTable1.getValueAt(i, 9).toString()) && produk_status == 1){
                JOptionPane.showMessageDialog(this,"Ada Nomor SPK sudah tidak terdaftar karena dihapus/ada perubahan pada nomor SPK", "Info", JOptionPane.ERROR_MESSAGE);
                return false;
            }
        }
        return status;
    }
    
    private void save(){
        cnbkt_print =  tbukti.getText();
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt", tbukti.getText());
        listObj.put("ctran","DP");
        listObj.put("cnbkt_inv",tpocustomer.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("cyear",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy"));
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("cvia",tvia.getText());
        listObj.put("csupir",tsupir.getText());
        listObj.put("cnopol",tnopol.getText());
        listObj.put("cnote",tket.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("TranDeliveryOrder", listObj);
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
            JOptionPane.showMessageDialog(this,"Header gagal di simpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
        
    }
    private void update(){
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranDeliveryOrderD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        cnbkt_print =  tbukti.getText();
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt", tbukti.getText());
        listObj.put("ctran","DP");
        listObj.put("cnbkt_inv",tpocustomer.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("cyear",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy"));
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("cvia",tvia.getText());
        listObj.put("csupir",tsupir.getText());
        listObj.put("cnopol",tnopol.getText());
        listObj.put("cnote",tket.getText());
        listObj.put("cusername",Login.user);
        sql=con.updateValue("TranDeliveryOrder", listObj);
        sql+=" where id = '"+id+"' ";
        System.out.println(sql);
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
            JOptionPane.showMessageDialog(this,"Header Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
        }
        
        
        
    }
    private void delete(){
        int err=0;
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranDeliveryOrderD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  TranDeliveryOrderD  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                if(con.queryUpdate(sql)<=0){
                    err++;
                } 
            }
            
        }
        sql="delete from  TranDeliveryOrder  where id = '"+id+"' ";
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
        
        sql="select a.*,b.cname as customer from TranDeliveryOrder a left join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "where a.ccode='"+tcodes.getText()+"' and a.cnbkt='"+cnbkt_print+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                param.put("title", "BUKTI BANK MASUK");
                param.put("pt", deffProgram.get("pt"));
                param.put("alamat", deffProgram.get("alamat"));
                param.put("telp", "Telp. "+deffProgram.get("telp"));
                param.put("mail", "Email : "+deffProgram.get("mail"));
                param.put("bukti",rs.getString("cnbkt"));
                param.put("inv",rs.getString("cnbkt_inv"));
                param.put("customer",rs.getString("customer"));
                param.put("tanggal",rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"));
                param.put("via",rs.getString("cvia"));
                param.put("supir",rs.getString("csupir"));
                param.put("nopol",rs.getString("cnopol"));
                param.put("note",rs.getString("cnote"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(DeliveryOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="select a.*,isnull(a.produkalias,b.cnameproduct) as cnameproduct from TranDeliveryOrderD a join mproduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+cnbkt_print+"'";
        
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                String satuan[] = rs.getString("csatuan_small").split(" ");
                model.addRow(new Object[]{
                    nomor++,
                    rs.getString("cbox"),
                    rs.getString("cgroup"),
                    rs.getString("cnameproduct"),
                    satuan[0],
                    rs.getDouble("nqty_box"),
                    rs.getDouble("nqty_small"),
                });
                
                total+=rs.getDouble("nqty");
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranDeliveryOrder.jrxml";
            
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

        jScrollPane5 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        tsatuanhigh = new javax.swing.JTextField();
        tsatuanlow = new javax.swing.JTextField();
        tprodukgroup = new javax.swing.JTextField();
        tproduk = new javax.swing.JTextField();
        tslopsatuan = new javax.swing.JTextField();
        jTextField24 = new javax.swing.JTextField();
        tpack = new javax.swing.JTextField();
        jLabel26 = new javax.swing.JLabel();
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
        jLabel6 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        tpocustomer = new javax.swing.JTextField();
        tcustomerkode = new javax.swing.JTextField();
        btPrint = new javax.swing.JButton();
        jButton6 = new javax.swing.JButton();
        tcustomer = new javax.swing.JTextField();
        jScrollPane4 = new javax.swing.JScrollPane();
        tket = new javax.swing.JTextArea();
        jLabel14 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        tvia = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        tsupir = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        jLabel21 = new javax.swing.JLabel();
        tnopol = new javax.swing.JTextField();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tbukti = new javax.swing.JTextField();
        jButton8 = new javax.swing.JButton();
        jPanel7 = new javax.swing.JPanel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jPanel3 = new javax.swing.JPanel();
        jLabel18 = new javax.swing.JLabel();
        tbox = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        jLabel23 = new javax.swing.JLabel();
        tproduknama = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        tqty = new javax.swing.JTextField();
        btHapus = new javax.swing.JButton();
        btBatal = new javax.swing.JButton();
        btAdd = new javax.swing.JButton();
        jLabel19 = new javax.swing.JLabel();
        tsatuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        jLabel17 = new javax.swing.JLabel();
        jLabel24 = new javax.swing.JLabel();
        tbox_qty = new javax.swing.JTextField();
        tpcs = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        tprodukalias = new javax.swing.JTextField();
        jLabel27 = new javax.swing.JLabel();
        two = new javax.swing.JTextField();
        jButton9 = new javax.swing.JButton();

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

        tsatuanhigh.setText("jTextField1");

        tsatuanlow.setText("jTextField1");

        tprodukgroup.setText("jTextField1");

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

        tslopsatuan.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tslopsatuan.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tslopsatuanCaretUpdate(evt);
            }
        });

        jTextField24.setEditable(false);
        jTextField24.setBackground(new java.awt.Color(153, 153, 255));
        jTextField24.setForeground(new java.awt.Color(255, 255, 255));
        jTextField24.setText(" Pack");

        tpack.setEditable(false);
        tpack.setBackground(new java.awt.Color(153, 153, 255));
        tpack.setForeground(new java.awt.Color(255, 255, 255));
        tpack.setHorizontalAlignment(javax.swing.JTextField.CENTER);

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Pcs");
        jLabel26.setToolTipText("");

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Delivery Order");
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

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Nomor Bukti", "Log", "Tanggal", "No Inv", "Member", "Via", "Supir", "Nopol", "Keterangan", "Jumal Item"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false
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
            jTable3.getColumnModel().getColumn(3).setMinWidth(90);
            jTable3.getColumnModel().getColumn(3).setMaxWidth(90);
            jTable3.getColumnModel().getColumn(10).setMinWidth(0);
            jTable3.getColumnModel().getColumn(10).setMaxWidth(0);
        }

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null},
                {null, null, null, null, null}
            },
            new String [] {
                "Box", "Group", "Product", "Jumlah", "Satuan"
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
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1203, Short.MAX_VALUE)
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
                    .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 331, Short.MAX_VALUE))
                .addGap(11, 11, 11)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Log");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Transaksi");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Pengiriman");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 130, 20));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Nomor PO Customer");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 130, 20));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Keterangan");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 260, 72, -1));

        tpocustomer.setEditable(false);
        jPanel1.add(tpocustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 300, -1));

        tcustomerkode.setEditable(false);
        tcustomerkode.setBackground(new java.awt.Color(153, 153, 255));
        tcustomerkode.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tcustomerkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 90, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 50, 130, -1));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 110, 30, -1));

        tcustomer.setEditable(false);
        tcustomer.setBackground(new java.awt.Color(153, 153, 255));
        tcustomer.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 140, 200, -1));

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

        jPanel1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 260, 330, 80));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Tujuan (Customer)");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete All");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 370, 110, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 370, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 370, 97, -1));

        tvia.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tviaFocusLost(evt);
            }
        });
        tvia.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tviaKeyPressed(evt);
            }
        });
        jPanel1.add(tvia, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 180, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Via");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 110, 20));

        tsupir.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tsupirFocusLost(evt);
            }
        });
        tsupir.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tsupirKeyPressed(evt);
            }
        });
        jPanel1.add(tsupir, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 330, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Supir");
        jPanel1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, 110, 20));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Nopol");
        jPanel1.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 230, 110, 20));

        tnopol.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnopolFocusLost(evt);
            }
        });
        tnopol.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnopolKeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tnopolKeyReleased(evt);
            }
        });
        jPanel1.add(tnopol, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 230, 330, -1));

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

        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 330, -1));

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 140, 30, -1));

        jPanel7.setBorder(javax.swing.BorderFactory.createTitledBorder(javax.swing.BorderFactory.createEtchedBorder(), "Daftar Produk", javax.swing.border.TitledBorder.DEFAULT_JUSTIFICATION, javax.swing.border.TitledBorder.DEFAULT_POSITION, new java.awt.Font("Tahoma", 0, 12))); // NOI18N

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Box QR", "Group", "Product", "Jml Box", "Jumlah", "Satuan", "Qty Pcs", "Produk Alias", "SPK", "Satuan Item"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Integer.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false
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
            jTable1.getColumnModel().getColumn(2).setMinWidth(0);
            jTable1.getColumnModel().getColumn(2).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(5).setMinWidth(90);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(90);
        }

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel18.setText("Nama Alias");
        jPanel3.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 90, 120, 20));

        tbox.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tboxFocusLost(evt);
            }
        });
        tbox.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tboxKeyPressed(evt);
            }
        });
        jPanel3.add(tbox, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 0, 280, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel22.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/qrcode.png"))); // NOI18N
        jPanel3.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 0, 40, 20));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel23.setText("No SPK");
        jPanel3.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 30, 90, 20));

        tproduknama.setEditable(false);
        tproduknama.setBackground(new java.awt.Color(153, 153, 255));
        tproduknama.setForeground(new java.awt.Color(255, 255, 255));
        jPanel3.add(tproduknama, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 60, 250, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 60, 30, -1));

        tqty.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tqty.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tqtyCaretUpdate(evt);
            }
        });
        tqty.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tqtyFocusLost(evt);
            }
        });
        tqty.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tqtyKeyReleased(evt);
            }
        });
        jPanel3.add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 150, 90, -1));

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
        jPanel3.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(430, 170, 30, -1));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jPanel3.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(470, 170, 70, 25));

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
        jPanel3.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 170, 80, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Satuan");
        jPanel3.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 180, 90, 20));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        jPanel3.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 180, 250, -1));

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
        jPanel3.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 180, 30, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Total dlm 1 Box");
        jPanel3.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 150, 120, 20));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Jml Box");
        jLabel24.setToolTipText("");
        jPanel3.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 120, 100, 20));

        tbox_qty.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tbox_qty.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbox_qtyFocusLost(evt);
            }
        });
        tbox_qty.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                tbox_qtyKeyReleased(evt);
            }
        });
        jPanel3.add(tbox_qty, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 120, 90, -1));

        tpcs.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        tpcs.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        tpcs.setText("0");
        tpcs.setToolTipText("");
        jPanel3.add(tpcs, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 150, 180, 20));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel25.setText("Produk");
        jPanel3.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 60, 90, 20));
        jPanel3.add(tprodukalias, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 90, 280, -1));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jLabel27.setText("No Box");
        jPanel3.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 90, 20));

        two.setEditable(false);
        jPanel3.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(120, 30, 250, -1));

        jButton9.setText("...");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 30, 30, -1));

        javax.swing.GroupLayout jPanel7Layout = new javax.swing.GroupLayout(jPanel7);
        jPanel7.setLayout(jPanel7Layout);
        jPanel7Layout.setHorizontalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel7Layout.createSequentialGroup()
                .addGap(4, 4, 4)
                .addGroup(jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING, false)
                    .addComponent(jPanel3, javax.swing.GroupLayout.DEFAULT_SIZE, 660, Short.MAX_VALUE)
                    .addComponent(jScrollPane1))
                .addContainerGap(13, Short.MAX_VALUE))
        );
        jPanel7Layout.setVerticalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel7Layout.createSequentialGroup()
                .addGap(3, 3, 3)
                .addComponent(jPanel3, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE)
                .addContainerGap())
        );

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 508, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jPanel7, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addContainerGap(javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel7, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 437, Short.MAX_VALUE))
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
                .addGap(4, 4, 4)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 487, Short.MAX_VALUE))
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

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogOrderCustomer a = new DialogOrderCustomer();
        a.setVisible(true);
        if(a.status){
            loadPOCustomer(a.id);
            rfa.clear_table(keyssDef, jTable1);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_jButton7ActionPerformed

    private void tboxFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tboxFocusLost
        if(!tbox.getText().isEmpty()) loadBox();
    }//GEN-LAST:event_tboxFocusLost

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        DialogMembers a = new DialogMembers();
        a.setVisible(true);
        if(a.status){
            tcustomerkode.setText(a.Kode);
            tcustomer.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton8ActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable1.getSelectedRow();
            loadEditItem(baris);
            hitungPack();
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    if(ceksave()) save();
                    
                }
            } else if(btSave.getText().equals("Update")){
                if(con.akses(akses, rfa.UPDATE)) {
                    if(ceksave()) update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

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
            
            hitungPack();
        }
    }//GEN-LAST:event_btsatuanActionPerformed

    private void btsatuanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btsatuanKeyPressed
        if(evt.getKeyCode()==10) btsatuan.doClick();
    }//GEN-LAST:event_btsatuanKeyPressed

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

    private void tviaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tviaFocusLost
        rfa.FieldCapital(tvia);
    }//GEN-LAST:event_tviaFocusLost

    private void tsupirFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tsupirFocusLost
        rfa.FieldCapital(tsupir);
    }//GEN-LAST:event_tsupirFocusLost

    private void tnopolFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnopolFocusLost
        rfa.FieldCapital(tnopol);
    }//GEN-LAST:event_tnopolFocusLost

    private void tviaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tviaKeyPressed
        rfa.EnterNextFocus(evt, tsupir);
    }//GEN-LAST:event_tviaKeyPressed

    private void tsupirKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tsupirKeyPressed
        rfa.EnterNextFocus(evt, tnopol);
    }//GEN-LAST:event_tsupirKeyPressed

    private void tnopolKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnopolKeyPressed

        if(evt.getKeyCode()==10){
            tkets.requestFocus();
        }
    }//GEN-LAST:event_tnopolKeyPressed

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 1) {
            int baris = jTable3.getSelectedRow();
            String bkt = jTable3.getValueAt(baris,1).toString();
            
            sql="select a.* from TranDeliveryOrderD a   \n" +
            "where a.cnbkt = '"+bkt+"' and a.ccode='"+tcodes.getText()+"'";
            rfa.clear_table(MenuAdmin.keyssDef, jTable2);
            DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{
                        rs.getString("cbox"),rs.getString("cgroup"),rs.getString("cproduct"),
                        rs.getDouble("nqty"),rs.getString("csatuan")
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
            tcode.setText(tcodes.getText());
            loadEdit();
        } 
    }//GEN-LAST:event_jTable3MouseClicked

    private void tnopolKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnopolKeyReleased
        // TODO add your handling code here:
    }//GEN-LAST:event_tnopolKeyReleased

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void tslopsatuanCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tslopsatuanCaretUpdate
        hitungPack();
    }//GEN-LAST:event_tslopsatuanCaretUpdate

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogProduk a = new DialogProduk(0);
        a.setVisible(true);
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tproduknama.setText(a.namaBarang);
            tprodukalias.setText(a.namaBarang); 
            tsatuan.setText(a.unit);
            tsatuanlow.setText("1");
            two.setText("");
            produk_status = a.produk_status;
        } else {
            
        }
    }//GEN-LAST:event_jButton10ActionPerformed

    private void tqtyCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tqtyCaretUpdate
        
    }//GEN-LAST:event_tqtyCaretUpdate

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
        //rfa.EnterNextFocus(evt, tkaryawan);
    }//GEN-LAST:event_tprodukKeyPressed

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        sql = "select * from mproduk where cproduct = '"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tprodukgroup.setText(rs.getString("cgroup"));
            tproduknama.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

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

    private void tboxKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tboxKeyPressed
        System.out.println(evt.getKeyCode());
        if(evt.getKeyCode() == 10){
            if(tbox_qty.getText().isEmpty()) {
                tbox_qty.requestFocus();
            } else {
                btAdd.requestFocus();
            }
        }
    }//GEN-LAST:event_tboxKeyPressed

    private void tbox_qtyKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbox_qtyKeyReleased
        hitungPack();
    }//GEN-LAST:event_tbox_qtyKeyReleased

    private void tqtyKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyReleased
        hitungPack();
    }//GEN-LAST:event_tqtyKeyReleased

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        DialogPublicTP.DialogWO a = new DialogWO();
        a.setVisible(true);
        if(a.status){
            two.setText(a.cnbkt); 
            tproduk.setText(a.cproduct);
            tprodukgroup.setText(a.cgroup);
            tproduknama.setText(a.produk);
            tprodukalias.setText(a.produk);
        }
    }//GEN-LAST:event_jButton9ActionPerformed

    private void tbox_qtyFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbox_qtyFocusLost
        hitungPack();
    }//GEN-LAST:event_tbox_qtyFocusLost

    private void tqtyFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tqtyFocusLost
        hitungPack();
    }//GEN-LAST:event_tqtyFocusLost



    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAdd;
    private javax.swing.JButton btBatal;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
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
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
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
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField13;
    private javax.swing.JTextField jTextField24;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTextField tbox;
    private javax.swing.JTextField tbox_qty;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tcustomer;
    private javax.swing.JTextField tcustomerkode;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tnopol;
    private javax.swing.JTextField tpack;
    private javax.swing.JLabel tpcs;
    private javax.swing.JTextField tpocustomer;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tprodukalias;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tproduknama;
    private javax.swing.JTextField tqty;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    private javax.swing.JTextField tslopsatuan;
    private javax.swing.JTextField tsupir;
    private javax.swing.JTextField tvia;
    private javax.swing.JTextField two;
    // End of variables declaration//GEN-END:variables
}
