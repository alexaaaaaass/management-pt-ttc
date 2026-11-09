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
import DialogPublicTP.Dialogcost;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Purchase.PaymentPurchase;
import Purchase.PurchaseOrder;
import Purchase.PurchaseRequest;
import Tools.ConnectDB;
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
public class BOMProduct extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",iddetailcost="",cnbkt="",cnbkt_print="";
    int barisEdit=-1,rowcost=-1;
    String akses="MASTER PRODUKSI BOM";
    String idKas="R1";
    String satuanterkecil;
    ArrayList<String> deleteRow,deleteRowCost;
    public BOMProduct(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        deleteRowCost = new ArrayList<>();
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
        btDelete.setVisible(false);
        btBatal.setVisible(false);
        btHapus.setVisible(false);
        jButton12.setEnabled(true);
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        
        Calendar calendar = Calendar.getInstance();
        Date today = calendar.getTime();
        calendar.add(Calendar.DAY_OF_YEAR, -7);
        Date addDays = calendar.getTime();
        
        id = "";
        jDate2.setDate(new Date());
        jDate1.setDate(addDays);
        
        tcodes.setText(deffProgram.get("ccode"));
        
        LastCode();
        hitungTotalTransaksi();
        
        deffBatalCost();
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tname,tket,tqtybom,tprodukbom,tprodukbomgroup,tprodukbomname,plot);
        rfa.empty(MenuAdmin.keyssDef,tket,tproduk,tprodukgroup,tprodukname,tqty,tsatuan,tsatuanhigh,tsatuanlow);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable4);
        deleteRow.clear();
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        rfa.empty(MenuAdmin.keyssDef,tproduk,tprodukgroup,tprodukname,tqty,tsatuan,tsatuanhigh,tsatuanlow,tprice);
        
    }
    private void deffBatalCost(){
        btAddcost.setText("Add");
        btBatalCost.setVisible(false);
        btHapusCost.setVisible(false);
        rfa.empty(MenuAdmin.keyssDef,tjasa,tjasaname,tpricecost);
        cbInterval.setSelected(false);
    }
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(cast(right(cnbkt,4) as signed)) as lastno from BOMProduct where cnbkt like '%"+"RM-BOM/%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("RM-BOM/");
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
    } 
    private void loadData(){
        
        sql = "select a.id,a.cnbkt,a.cnbkt_po,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cnote,\n" +
            "b.cname as gudang,sum(c.ntotal) as total \n" +
            "from BOMProduct a join MGudang b on a.ccode=b.ccode\n" +
            "join BOMProductD c on a.cnbkt=c.cnbkt\n" +
            "where a.ccode='"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
            "and a.cnote like '%"+tkets.getText()+"%' "
                + "group by a.id,a.cnbkt,a.cnbkt_po,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cnote,b.cname\n"
                + " order by a.cnbkt desc ";
        sql = "select * From BOMProduct"
                + " order by id desc";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("cnbkt"),rs.getString("cname"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getDouble("nqty"),
                    rs.getString("nunit"),
                    rs.getString("cnote"),
                    
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
        sql = "select * from BOMProduct where id='"+id+"'  ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                
                no++;
                
                tbukti.setText(rs.getString("cnbkt").substring(0, rs.getString("cnbkt").length()-4));
                tbukti1.setText(rs.getString("cnbkt").substring(rs.getString("cnbkt").length()-4, rs.getString("cnbkt").length()));
                tket.setText(rs.getString("cnote"));
                tname.setText(rs.getString("cname"));
                tprodukbomgroup.setText(rs.getString("cgroup"));
                tprodukbom.setText(rs.getString("cproduct"));
                tqtybom.setText(rfa.formatDecimal3.format(rs.getDouble("nqty")));
                plot.setText(rfa.formatDecimal3.format(rs.getDouble("cplot")));
                listUnit.setSelectedItem(rs.getString("nunit"));
                tprodukbom.requestFocus();
                tket.requestFocus();
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        String bkt = cnbkt;
        sql="SELECT * FROM BOMProductD \n" +
        "where cnbkt = '"+bkt+"'  ";
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                System.out.println(rs.getString("id"));
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),
                    rs.getString("nunit"),rfa.formatDecimal3.format(rs.getDouble("nunit_bigvalue")),rfa.formatDecimal3.format(rs.getDouble("nunit_smallvalue")),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        sql="SELECT * FROM BOMProductCost \n" +
        "where cnbkt = '"+bkt+"'  ";
        rfa.clear_table(keyssDef, jTable4);
        DefaultTableModel model1 = (DefaultTableModel) jTable4.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                System.out.println(rs.getString("id"));
                String interval="Tidak";
                if(rs.getInt("cinterval") == 1) { interval="Ya";}
                model1.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("kode_cost"),
                    rs.getString("cost"),
                    interval,
                    rs.getDouble("cprice"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTable1.getValueAt(baris, 0).toString();
        
        if(jTable1.getValueAt(baris,1)!=null) tprodukgroup.setText(jTable1.getValueAt(baris, 1).toString());
        if(jTable1.getValueAt(baris,2)!=null) tproduk.setText(jTable1.getValueAt(baris, 2).toString());
        if(jTable1.getValueAt(baris,3)!=null) tqty.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris, 3).toString())));
        if(jTable1.getValueAt(baris,4)!=null) tprice.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris, 4).toString())));
        if(jTable1.getValueAt(baris,5)!=null) tsatuan.setText(jTable1.getValueAt(baris, 5).toString());
        if(jTable1.getValueAt(baris,6)!=null) tsatuanhigh.setText(jTable1.getValueAt(baris, 6).toString());
        if(jTable1.getValueAt(baris,7)!=null) tsatuanlow.setText(jTable1.getValueAt(baris, 7).toString());
        
        tproduk.requestFocus();
        tqty.requestFocus();
        btAdd.setText("Ubah");
        btBatal.setVisible(true);
        btHapus.setVisible(true);
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
        return true;
    }
    private void addData(){
        if(!cekSave()) {
            return;
        }
        

        if(btAdd.getText().equalsIgnoreCase("Add")) {
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            model.addRow(new Object[]{
                -1,
                tprodukgroup.getText(),
                tproduk.getText(),
                Double.valueOf(tqty.getText()),
                Double.valueOf(tprice.getText()),
                tsatuan.getText(),
                tsatuanhigh.getText(),
                tsatuanlow.getText(),
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(iddetail, barisEdit, col++);
            jTable1.setValueAt(tprodukgroup.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduk.getText(), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tqty.getText()), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tprice.getText()), barisEdit, col++);
            jTable1.setValueAt(tsatuan.getText(), barisEdit, col++);
            jTable1.setValueAt(tsatuanhigh.getText(), barisEdit, col++);
            jTable1.setValueAt(tsatuanlow.getText(), barisEdit, col++);
            
        }
        
        deffBatal();
        
    }
    private void hitungTotalTransaksi(){
        Double totalNilai=0.0;
        for(int i=0; i<jTable1.getRowCount(); i++){
            totalNilai+=Double.valueOf(jTable1.getValueAt(i, 5).toString());
        }
    }
    private void hitungTotalIn(){
        Double qty=0.0,harga=0.0,disc=0.0,totalsub=0.0,total=0.0,totalAll=0.0;
        if(!tqty.getText().isEmpty()) qty = Double.valueOf(tqty.getText());
        
        
        total = qty*harga;
        totalAll = total-disc;
    }
    
    private void addDataCost(){
        String interval="Tidak";
        if(cbInterval.isSelected()){
            interval="Ya";
        }
        if(btAddcost.getText().equalsIgnoreCase("Add")) {
            DefaultTableModel model = (DefaultTableModel) jTable4.getModel();
            model.addRow(new Object[]{
                -1,
                tjasa.getText(),
                tjasaname.getText(),
                interval,
                Double.valueOf(tpricecost.getText())
            });
        } else { //Ubah
            int col=0;
            jTable4.setValueAt(iddetailcost, rowcost, col++);
            jTable4.setValueAt(tjasa.getText(), rowcost, col++);
            jTable4.setValueAt(tjasaname.getText(), rowcost, col++);
            jTable4.setValueAt(interval, rowcost, col++);
            jTable4.setValueAt(Double.valueOf(tpricecost.getText()), rowcost, col++);
        }
        
        deffBatalCost();
        
    }
    
    private int insertData(){
        int err= 0;
        for(int i=0; i<jTable1.getRowCount();i++){
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
            listObj.put("cgroup",jTable1.getValueAt(i,1).toString());
            listObj.put("cproduct",jTable1.getValueAt(i, 2).toString());
            listObj.put("nqty",rfa.formatDecimal3.format(jTable1.getValueAt(i, 3)));
            listObj.put("nprice",rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
            listObj.put("nunit",jTable1.getValueAt(i, 5).toString());
            listObj.put("nunit_bigvalue",jTable1.getValueAt(i, 6).toString());
            listObj.put("nunit_smallvalue",jTable1.getValueAt(i,7).toString());
            listObj.put("cusername",Login.user);
            
            if("-1".equalsIgnoreCase(jTable1.getValueAt(i, 0).toString())) {
                //INSERT
                sql=con.insertValue("BOMProductD", listObj);
                
            } else {  
                //UPDATE
                sql=con.updateValue("BOMProductD", listObj);
                sql+=" where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";                
            }
            
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        for(int i=0; i<jTable4.getRowCount();i++){
            String interval="0";
            if(jTable4.getValueAt(i, 3) != null) {
                if(jTable4.getValueAt(i, 3).toString().equals("Ya")){
                    interval ="1";
                }
            }
            
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());
            listObj.put("kode_cost",jTable4.getValueAt(i,1).toString());
            listObj.put("cost",jTable4.getValueAt(i, 2).toString());
            listObj.put("cinterval",interval);
            listObj.put("cprice",rfa.formatDecimal3.format(jTable4.getValueAt(i, 4)));
            
            if("-1".equalsIgnoreCase(jTable4.getValueAt(i, 0).toString())) {
                //INSERT
                sql=con.insertValue("BOMProductCost", listObj);
                
            } else {  
                //UPDATE
                sql=con.updateValue("BOMProductCost", listObj);
                sql+=" where id = '"+jTable4.getValueAt(i, 0).toString()+"' ";                
            }
            System.out.println(sql);
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    private void save(){
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        String due_date = "NULL";
        sql="";
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());     
        listObj.put("cname",tname.getText());  
        listObj.put("cgroup",tprodukbomgroup.getText());  
        listObj.put("cproduct",tprodukbom.getText());  
        listObj.put("nqty",tqtybom.getText());  
        listObj.put("nunit",listUnit.getSelectedItem().toString());  
        listObj.put("cnote",tket.getText());  
        listObj.put("fstatus","1");  
        listObj.put("cplot",plot.getText());  
        
        listObj.put("cusername",Login.user);
        sql=con.insertValue("BOMProduct", listObj);
        if(con.queryUpdate(sql)>0) {
            if(insertData()==0){
                JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
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
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText()+tbukti1.getText());     
        listObj.put("cname",tname.getText());  
        listObj.put("cgroup",tprodukbomgroup.getText());  
        listObj.put("cproduct",tprodukbom.getText());  
        listObj.put("nqty",tqtybom.getText());  
        listObj.put("nunit",listUnit.getSelectedItem().toString());  
        listObj.put("cnote",tket.getText());  
        listObj.put("fstatus","1");  
        listObj.put("cplot",plot.getText());
        listObj.put("cusername",Login.user);
        sql=con.updateValue("BOMProduct", listObj);
        sql+=" where id='"+id+"'";
        
        if(con.queryUpdate(sql)>0) {
            for(int i=0; i<deleteRow.size();i++){
                sql="delete from  BOMProductD  where id = '"+deleteRow.get(i)+"' ";
                con.queryUpdate(sql);
            }
            for(int i=0; i<deleteRowCost.size();i++){
                sql="delete from  BOMProductCost  where id = '"+deleteRowCost.get(i)+"' ";
                con.queryUpdate(sql);
            }
            if(insertData()==0){
                JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
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
        con.queryUpdate("delete from  BOMProductCost  where cnbkt = '"+tbukti.getText()+tbukti1.getText()+"' ");
        con.queryUpdate("delete from  BOMProductD  where cnbkt = '"+tbukti.getText()+tbukti1.getText()+"' ");
        sql="delete from  BOMProduct  where id = '"+id+"' ";
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
    private void delete_row_cost(){
        if(!"-1".equals(iddetailcost)) {
            deleteRowCost.add(iddetailcost);
        }
        DefaultTableModel model = (DefaultTableModel) jTable4.getModel();
        model.removeRow(rowcost);
        btBatalCost.doClick();
        
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
        tjasagroup = new javax.swing.JTextField();
        tprodukbomgroup = new javax.swing.JTextField();
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
        jLabel21 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel5 = new javax.swing.JLabel();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        jLabel18 = new javax.swing.JLabel();
        tprodukbom = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        tprodukbomname = new javax.swing.JTextField();
        jLabel10 = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        listUnit = new javax.swing.JComboBox();
        tqtybom = new javax.swing.JTextField();
        btSave = new javax.swing.JButton();
        btDelete = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel6 = new javax.swing.JLabel();
        tname = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        jLabel16 = new javax.swing.JLabel();
        plot = new javax.swing.JTextField();
        jLabel8 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        jTabbedPane2 = new javax.swing.JTabbedPane();
        jPanel3 = new javax.swing.JPanel();
        jPanel8 = new javax.swing.JPanel();
        jLabel17 = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        tqty = new javax.swing.JTextField();
        tproduk = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        tprodukname = new javax.swing.JTextField();
        tsatuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        btHapus = new javax.swing.JButton();
        btBatal = new javax.swing.JButton();
        btAdd = new javax.swing.JButton();
        jLabel26 = new javax.swing.JLabel();
        tprice = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jPanel7 = new javax.swing.JPanel();
        jPanel9 = new javax.swing.JPanel();
        jLabel20 = new javax.swing.JLabel();
        tjasa = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tjasaname = new javax.swing.JTextField();
        btHapusCost = new javax.swing.JButton();
        btBatalCost = new javax.swing.JButton();
        btAddcost = new javax.swing.JButton();
        cbInterval = new javax.swing.JCheckBox();
        jLabel22 = new javax.swing.JLabel();
        tpricecost = new javax.swing.JTextField();
        jScrollPane4 = new javax.swing.JScrollPane();
        jTable4 = new javax.swing.JTable();

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "no", "supplier", "group", "product", "qty", "harga", "disc", "total", "satuan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane5.setViewportView(jtable_print);

        tsatuanhigh.setText("jTextField1");

        tsatuanlow.setText("jTextField1");

        tprodukgroup.setText("jTextField1");

        tjasagroup.setText("jTextField1");

        tprodukbomgroup.setText("jTextField1");

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("BOM (Bill of Material)");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, 230, -1));

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

        jLabel21.setText("Gudang");
        jPanel6.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

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

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No", "Nomor Bukti", "Name BOM", "Group", "Produk", "Qty", "Unit", "Keterangan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
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
            jTable2.getColumnModel().getColumn(1).setMinWidth(50);
            jTable2.getColumnModel().getColumn(1).setMaxWidth(50);
        }

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1040, Short.MAX_VALUE)
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
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 322, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("Nama BOM");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 72, 20));

        tbukti.setEditable(false);
        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 10, 120, -1));

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
        jPanel1.add(tbukti1, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 10, 60, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Qty");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 100, 90, 20));

        tprodukbom.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tprodukbomFocusLost(evt);
            }
        });
        tprodukbom.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tprodukbomKeyPressed(evt);
            }
        });
        jPanel1.add(tprodukbom, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 70, 80, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 70, 30, -1));

        tprodukbomname.setEditable(false);
        tprodukbomname.setBackground(new java.awt.Color(153, 153, 255));
        tprodukbomname.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tprodukbomname, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 70, 170, -1));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Keterangan");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 130, 72, -1));

        tket.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tket.setHorizontalAlignment(javax.swing.JTextField.LEFT);
        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        jPanel1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 130, 290, -1));

        listUnit.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "PCS", "KG", "ROLL", "REAMS", " " }));
        jPanel1.add(listUnit, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 100, 80, -1));

        tqtybom.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tqtybom.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jPanel1.add(tqtybom, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 100, 110, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(600, 90, 97, -1));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(490, 90, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(710, 90, 97, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("No. Bukti");
        jLabel6.setToolTipText("");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 72, 20));

        tname.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tname.setHorizontalAlignment(javax.swing.JTextField.LEFT);
        tname.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnameFocusLost(evt);
            }
        });
        jPanel1.add(tname, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 40, 290, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Produk");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, 72, 20));

        jLabel16.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel16.setText("Plot");
        jPanel1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(490, 10, 50, 20));
        jPanel1.add(plot, new org.netbeans.lib.awtextra.AbsoluteConstraints(570, 10, 60, -1));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel8.setText("Pcs");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(630, 10, 30, 20));

        jLabel14.setFont(new java.awt.Font("Tahoma", 2, 12)); // NOI18N
        jLabel14.setText("*Jml Pcs dalam 1 Lembar");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(570, 30, 150, 20));

        jTabbedPane2.setBackground(new java.awt.Color(153, 153, 153));

        jPanel3.setBorder(new javax.swing.border.SoftBevelBorder(javax.swing.border.BevelBorder.RAISED));

        jPanel8.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Bahan");
        jPanel8.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 90, 20));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Price");
        jPanel8.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 0, 90, 20));

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
        jPanel8.add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 20, 80, -1));

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
        jPanel8.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 0, 80, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel8.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 0, 30, -1));

        tprodukname.setEditable(false);
        tprodukname.setBackground(new java.awt.Color(153, 153, 255));
        tprodukname.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tprodukname, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 0, 150, -1));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        jPanel8.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 150, -1));

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
        jPanel8.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 20, 30, -1));

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
        jPanel8.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 0, 30, 30));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jPanel8.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(720, 0, 70, 30));

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
        jPanel8.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(790, 0, 80, 30));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Qty");
        jPanel8.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 20, 90, 20));
        jPanel8.add(tprice, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 0, 140, 20));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "Group", "Product", "Qty", "Price", "Satuan ", "Satuan_nilai", "Satuan_nilaikecil"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
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
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(30);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(30);
        }

        javax.swing.GroupLayout jPanel3Layout = new javax.swing.GroupLayout(jPanel3);
        jPanel3.setLayout(jPanel3Layout);
        jPanel3Layout.setHorizontalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel3Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 1009, Short.MAX_VALUE)
                    .addComponent(jPanel8, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel3Layout.setVerticalGroup(
            jPanel3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel3Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel8, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 177, Short.MAX_VALUE)
                .addContainerGap())
        );

        jTabbedPane2.addTab("Material", jPanel3);

        jPanel7.setBorder(new javax.swing.border.SoftBevelBorder(javax.swing.border.BevelBorder.RAISED));

        jPanel9.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Price");
        jPanel9.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 30, 90, 20));

        tjasa.setEditable(false);
        tjasa.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tjasaFocusLost(evt);
            }
        });
        tjasa.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tjasaKeyPressed(evt);
            }
        });
        jPanel9.add(tjasa, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 0, 80, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel9.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 0, 30, -1));

        tjasaname.setEditable(false);
        tjasaname.setBackground(new java.awt.Color(153, 153, 255));
        tjasaname.setForeground(new java.awt.Color(255, 255, 255));
        jPanel9.add(tjasaname, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 0, 150, -1));

        btHapusCost.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btHapusCost.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btHapusCostActionPerformed(evt);
            }
        });
        btHapusCost.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btHapusCostKeyPressed(evt);
            }
        });
        jPanel9.add(btHapusCost, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 20, 30, 30));

        btBatalCost.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatalCost.setText("Batal");
        btBatalCost.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatalCost.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalCostActionPerformed(evt);
            }
        });
        jPanel9.add(btBatalCost, new org.netbeans.lib.awtextra.AbsoluteConstraints(420, 20, 70, 30));

        btAddcost.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        btAddcost.setText("Add");
        btAddcost.setMargin(new java.awt.Insets(2, 2, 2, 2));
        btAddcost.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btAddcostActionPerformed(evt);
            }
        });
        btAddcost.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btAddcostKeyPressed(evt);
            }
        });
        jPanel9.add(btAddcost, new org.netbeans.lib.awtextra.AbsoluteConstraints(490, 20, 80, 30));

        cbInterval.setText("Interval / Kelipatan");
        jPanel9.add(cbInterval, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 30, -1, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Cost");
        jPanel9.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 90, 20));
        jPanel9.add(tpricecost, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 30, 140, 20));

        jTable4.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "Kode", "Cost", "Interval", "Price"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.String.class, java.lang.Double.class
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
        jTable4.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable4MouseClicked(evt);
            }
        });
        jScrollPane4.setViewportView(jTable4);
        if (jTable4.getColumnModel().getColumnCount() > 0) {
            jTable4.getColumnModel().getColumn(0).setMinWidth(50);
            jTable4.getColumnModel().getColumn(0).setMaxWidth(50);
        }

        javax.swing.GroupLayout jPanel7Layout = new javax.swing.GroupLayout(jPanel7);
        jPanel7.setLayout(jPanel7Layout);
        jPanel7Layout.setHorizontalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel7Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane4, javax.swing.GroupLayout.DEFAULT_SIZE, 1009, Short.MAX_VALUE)
                    .addComponent(jPanel9, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel7Layout.setVerticalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel7Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel9, javax.swing.GroupLayout.PREFERRED_SIZE, 61, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane4, javax.swing.GroupLayout.DEFAULT_SIZE, 159, Short.MAX_VALUE)
                .addContainerGap())
        );

        jTabbedPane2.addTab("Cost", jPanel7);

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addComponent(jTabbedPane2)
                        .addContainerGap())
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addGap(28, 28, 28))))
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 160, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(18, 18, 18)
                .addComponent(jTabbedPane2)
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

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 4, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed
      
    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

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
            tprodukgroup.setText(rs.getString("cgroup"));
            tprodukname.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
        rfa.EnterNextFocus(evt, tqty);
    }//GEN-LAST:event_tprodukKeyPressed

    private void btsatuanActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btsatuanActionPerformed
        if(tprodukname.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Isi Produk/Bahan terlebih dahulu.", "Info", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
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
        //rfa.EnterNextFocus(evt, tharga);
    }//GEN-LAST:event_tqtyKeyPressed

    private void tqtyKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyTyped
        //rfa.filterAngka(keyssDef, evt);
    }//GEN-LAST:event_tqtyKeyTyped

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogProduk a = new DialogProduk(2);
        a.setVisible(true);
        if(a.status){
            satuanterkecil="";
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tprodukname.setText(a.namaBarang);
            tsatuan.setText(a.unit);
            tsatuanhigh.setText("1");
            tsatuanlow.setText("1");
            satuanterkecil = a.unit;
            tqty.requestFocus();
        }
    }//GEN-LAST:event_jButton10ActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount()==2){
            deffBatal();
            int baris = jTable1.getSelectedRow();
            loadEditItem(baris);
        }
    }//GEN-LAST:event_jTable1MouseClicked

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

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        deleteRow = new ArrayList<>();
        push_button();
        clear();
        deff();
        loadData();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 2).toString();
            cnbkt_print = cnbkt;
             
            loadEdit();
        } 
    }//GEN-LAST:event_jTable2MouseClicked

    private void jTable2MouseEntered(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseEntered
        // TODO add your handling code here:
    }//GEN-LAST:event_jTable2MouseEntered

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
       loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode() == 10) jButton7.doClick();
    }//GEN-LAST:event_tbuktisKeyPressed

    private void tketsKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketsKeyPressed
        if(evt.getKeyCode() == 10) jButton7.doClick();
    }//GEN-LAST:event_tketsKeyPressed

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

    private void tprodukbomFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukbomFocusLost
        sql = "select * from mproduk where cproduct = '"+tprodukbom.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tprodukbomgroup.setText(rs.getString("cgroup"));
            tprodukbomname.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukbomFocusLost

    private void tprodukbomKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukbomKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tprodukbomKeyPressed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        if(a.status){
            tprodukbom.setText(a.kodeBarang);
            tprodukbomgroup.setText(a.groupBarang);
            tprodukbomname.setText(a.namaBarang);
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void tjasaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tjasaFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tjasaFocusLost

    private void tjasaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tjasaKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tjasaKeyPressed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        Dialogcost a = new Dialogcost();
        a.setVisible(true);
        if(a.status){
            tjasa.setText(a.Kode);
            tjasaname.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void btHapusCostActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btHapusCostActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete_row_cost();
        }
    }//GEN-LAST:event_btHapusCostActionPerformed

    private void btHapusCostKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btHapusCostKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_btHapusCostKeyPressed

    private void btBatalCostActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btBatalCostActionPerformed
        deffBatalCost();
    }//GEN-LAST:event_btBatalCostActionPerformed

    private void btAddcostActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btAddcostActionPerformed
        addDataCost();
    }//GEN-LAST:event_btAddcostActionPerformed

    private void btAddcostKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btAddcostKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_btAddcostKeyPressed

    private void jTable4MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable4MouseClicked
        if(evt.getClickCount() == 2){
            rowcost = jTable4.getSelectedRow();
            iddetailcost = jTable4.getValueAt(rowcost, 0).toString();
            tjasa.setText(jTable4.getValueAt(rowcost,1).toString());
            tjasaname.setText(jTable4.getValueAt(rowcost,2).toString());
            if(jTable4.getValueAt(rowcost,3) != null) {
                cbInterval.setSelected(false);
                if(jTable4.getValueAt(rowcost,3).toString().equalsIgnoreCase("Ya")) {
                    cbInterval.setSelected(true);
                }
            }
            tpricecost.setText(rfa.formatDecimal3.format(Double.valueOf(jTable4.getValueAt(rowcost,4).toString())));
            
            btAddcost.setText("Ubah");
            btBatalCost.setVisible(true);
            btHapusCost.setVisible(true);
        }
    }//GEN-LAST:event_jTable4MouseClicked

    private void tnameFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnameFocusLost
        tname.setText(rfa.toUpper(keyssDef, tname.getText()));
    }//GEN-LAST:event_tnameFocusLost

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(rfa.toUpper(keyssDef, tket.getText()));
    }//GEN-LAST:event_tketFocusLost


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAdd;
    private javax.swing.JButton btAddcost;
    private javax.swing.JButton btBatal;
    private javax.swing.JButton btBatalCost;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btHapusCost;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JCheckBox cbInterval;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
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
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel26;
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
    private javax.swing.JPanel jPanel8;
    private javax.swing.JPanel jPanel9;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTabbedPane jTabbedPane2;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable4;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTable jtable_print;
    private javax.swing.JComboBox listUnit;
    private javax.swing.JTextField plot;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tjasa;
    private javax.swing.JTextField tjasagroup;
    private javax.swing.JTextField tjasaname;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tname;
    private javax.swing.JTextField tprice;
    private javax.swing.JTextField tpricecost;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tprodukbom;
    private javax.swing.JTextField tprodukbomgroup;
    private javax.swing.JTextField tprodukbomname;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tprodukname;
    private javax.swing.JTextField tqty;
    private javax.swing.JTextField tqtybom;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    // End of variables declaration//GEN-END:variables
}
