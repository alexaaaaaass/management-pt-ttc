/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Production;

import Accounting.*;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogMembers;
import DialogPublicTP.DialogOrderCustomer;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogSatuan;
import DialogPublicTP.DialogSteps;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
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
public class WorkOrder extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="PRODUKSI WORK ORDER";
    String idKas="WO";
    ArrayList<String> deleteRow;
    ArrayList<String> listBOMID;
    public WorkOrder(String mode) {
        initComponents();        
        push_button();
        deleteRow = new ArrayList<>();
        listBOMID = new ArrayList<>();
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
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        rfa.clear_table(keyssDef, jTable1);
        Calendar calendar = Calendar.getInstance();
        Date today = calendar.getTime();
        calendar.add(Calendar.DAY_OF_YEAR, -7);
        Date addDays = calendar.getTime();
        
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(addDays);
        jDateTran1.setDate(new Date());
        
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
        rfa.empty(MenuAdmin.keyssDef,tket,tprodukgroup,tsatuanhigh,tsatuanlow,tkaryawan,tkaryawan1,tproduk,tprodukgroup,tproduknama,tbahan1,
                tcustomer,tcustomerkode,tpocustomer,tjumlah);
        rfa.clear_table(keyssDef, jTableMaterial);
        deleteRow.clear();
        tket.setText("");
        rfa.clear_table(keyssDef, jTable1);
        rfa.clear_table(keyssDef, jTableMaterial);
        rfa.clear_table(keyssDef, jTableCost);
        listbom.setSelectedIndex(0);
        jButton11.setEnabled(true);
    }
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,left(cnbkt,5))) as lastno "
                + " from PWorkOrder "
                + " where  ccode='"+tcode.getText()+"' and  ISNUMERIC(left(cnbkt,5)) = 1 "
                + " and month(dtran)= '"+rfa.DateString(keyssDef, jDateTran.getDate(),"MM")+"' ");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("/SPK-IK10/"+con.romawi(rfa.DateString(keyssDef, jDateTran.getDate(),"MM"))+"/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yy"));
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 5, last+1));
    } 
    private void loadData(){
        String where="";
        if(!tbuktis.getText().isEmpty()) where+=" and a.cnbkt like '%"+tbuktis.getText()+"%' ";
        if(!tkets.getText().isEmpty()) where+=" and a.cket like '%"+tkets.getText()+"%' ";
        if(!tcustomers.getText().isEmpty()) where+=" and bb.cname like '%"+tcustomers.getText()+"%' ";
        sql="select a.id,aa.cname as gudang,\n" +
            "a.cnbkt,a.dtran,c.cnameProduct,bb.cname as customer,a.cnbkt_order,a.deadline,a.nqty,a.nqty_total,\n" +
            "a.cket,a.kode,isnull(d.qtyfinish,0) as qtyfinish\n" +
            "from PWorkOrder a\n" +
            "join mgudang aa on a.ccode=aa.ccode\n" +
            "Left join PCustomerPO b on a.cnbkt_order=b.cnbkt and a.ccode=b.ccode\n" +
            "left join MCustomer bb on b.kode_customer=bb.kode_customer\n" +
            "join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
            "left join (\n" +
            "	select a.ccode,a.cworkorder,sum(b.nqty_small) as qtyfinish from PBoxPacking a join PBoxPackingD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	group by a.ccode,a.cworkorder\n" +
            ") d on a.ccode=d.ccode and a.cnbkt=d.cworkorder\n"+
            "where a.ccode = '"+tcodes.getText()+"'  \n" +
            "and (a.dtran>='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            where+
            " order by a.dtran desc,a.cnbkt desc";
        System.out.println(sql);
        
        ArrayList<Integer> colorSet = new ArrayList<>();
        colorSet.clear();
        int roww=0;
        
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("gudang"),rs.getString("cnbkt"),
                    rs.getString("kode"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cnameProduct"),
                    rs.getString("customer"),rs.getString("cnbkt_order"),
                    rfa.DateString(keyssDef, rs.getDate("deadline"),"dd/MM/yyyy"),
                    rfa.formatDecimal1.format(rs.getDouble("nqty")),
                    rs.getString("cket"),
                    rfa.formatDecimal1.format(rs.getDouble("qtyfinish"))
                });
                if(rs.getDouble("qtyfinish")>=rs.getDouble("nqty")) {
                    colorSet.add(roww);
                }
                roww++;
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
        
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();
        con.colorTableSet(jTable2, colorSet);
    }
    private void loadEdit(){
        btSave.setText("Update");
        btDelete.setVisible(true);
        String idorder = "",bom="",cgroup="",cproduct="";
        sql="select a.*,isnull(b.id,0) as idorder ,c.cname as bom\n" +
            "from PWorkOrder a left join PCustomerPOD b on a.ccode=b.ccode and a.cnbkt_order=b.cnbkt and a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "join BOMProduct c on a.cnbkt_bom=c.cnbkt\n" +
            "where a.id='"+id+"'";
        rs = con.queryResult(sql);
        
        try {
            while(rs.next()){
                String bukti = rs.getString("cnbkt");
                String[] buktis = bukti.split("/");
                tbukti1.setText(buktis[0]);
                tbukti.setText(rs.getString("cnbkt").substring(buktis[0].length(), rs.getString("cnbkt").length()));
                
                jDateTran.setDate(rs.getDate("dtran"));
                tproduk.setText(rs.getString("cproduct"));
                tpocustomer.setText(rs.getString("cnbkt_order"));
                idorder = rs.getString("idorder");
                
                tjumlah.setText(rfa.formatDecimal3.format(rs.getDouble("nqty")));
                tjumlahadd.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_add")));
                tjumlahtotal.setText(rfa.formatDecimal3.format(rs.getDouble("nqty_total")));
                
                tdeadline.setDate(rs.getDate("deadline"));
                tket.setText(rs.getString("cket"));
                tkode.setText(rs.getString("kode"));
                bom = rs.getString("bom");
                cgroup = rs.getString("cgroup");
                cproduct = rs.getString("cproduct");
                
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        loadPOCustomer(idorder);
        loadBOM(cgroup, cproduct);
        listbom.setSelectedItem(bom);
        DefaultTableModel model = (DefaultTableModel) jTableMaterial.getModel();
        rfa.clear_table(keyssDef, jTableMaterial);
        sql="select * from pworkorderd where ccode='"+tcode.getText()+"' and cnbkt = '"+cnbkt+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),
                    rs.getString("nunit"),rfa.formatDecimal3.format(rs.getDouble("nunit_bigvalue")),rfa.formatDecimal3.format(rs.getDouble("nunit_smallvalue")),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        DefaultTableModel model1 = (DefaultTableModel) jTableCost.getModel();
        rfa.clear_table(keyssDef, jTableCost);
        
        sql="select * from pworkordercost where ccode='"+tcode.getText()+"' and cnbkt = '"+cnbkt+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                model1.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("kode_cost"),
                    rs.getString("cost"),
                    rs.getDouble("cinterval"),
                    rs.getDouble("cprice")
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        DefaultTableModel model2 = (DefaultTableModel) jTable1.getModel();
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model3 = (DefaultTableModel) tablestep.getModel();
        rfa.clear_table(keyssDef, tablestep);
        sql="select * from PWorkOrderStep where ccode='"+tcode.getText()+"' and cnbkt = '"+cnbkt+"'";
        rs=con.queryResult(sql);
        int nomorr=1;
        try {
            while(rs.next()){
                model2.addRow(new Object[]{
                    rs.getString("urut"),
                    rs.getString("cstep_kode"),
                    rs.getString("cstep_name"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"), "yyyy-MM-dd"),
                });
                
                model3.addRow(new Object[]{
                    nomorr++,
                    rs.getString("cstep_name"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"), "yyyy-MM-dd"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
        tproduk.requestFocus();
        tket.requestFocus();
    }
    private Boolean cekSave(){
        if(tbahan1.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Produk wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        return true;
    }
    private void hitungPenambahan(){
        try{
            Double jml = Double.valueOf(tjumlah.getText());
            Double add = Double.valueOf(tjumlahadd.getText());
            Double percent = jml*(add/100);
            Double hasil = Math.ceil(jml + percent);
            tjumlahtotal.setText(rfa.formatDecimal3.format(hasil));
        } catch (Exception e){}
    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTableMaterial.getValueAt(baris, 0).toString();
        tbahan1.setText(jTableMaterial.getValueAt(baris,1).toString());
    }
    private void loadPOCustomer(String idd){
        System.out.println(id);
        String cgroup="",cproduct="";
        sql="select a.id,a.ccode,a.cnbkt,a.dtran,a.cmarketing,d.cname as marketing,\n" +
                "a.kode_customer,c.cname as customer,\n" +
                "b.cgroup,b.cproduct,e.cnameProduct,b.nqty,b.nunit,b.cgroup_bahan,b.cproduct_bahan,f.cnameProduct as cnameProductbahan,b.deadline\n" +
                "from PCustomerPO a \n" +
                "join PCustomerPOD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                "join MCustomer c on a.kode_customer=c.kode_customer\n" +
                "join MKaryawan d on a.cmarketing=d.nik\n" +
                "join MProduk e on b.cgroup=e.cgroup and b.cproduct=e.cproduct\n" +
                "left join MProduk f on b.cgroup_bahan=f.cgroup and b.cproduct_bahan=f.cproduct\n" +
                "where b.id = "+idd;
        System.out.print(sql);
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                tpocustomer.setText(rs.getString("cnbkt"));
                tcustomerkode.setText(rs.getString("kode_customer"));
                tcustomer.setText(rs.getString("customer"));
                tproduk.setText(rs.getString("cproduct"));
                tprodukgroup.setText(rs.getString("cgroup"));
                tproduknama.setText(rs.getString("cnameProduct"));
                
                cgroup = rs.getString("cgroup");
                cproduct = rs.getString("cproduct");
                
                tbahan.setText(rs.getString("cnameProductbahan"));
                
                if(btSave.getText().equals("Save")){
                    tdeadline.setDate(rs.getDate("deadline"));
                    tjumlah.setText(rfa.formatDecimal3.format(rs.getDouble("nqty")));
                    listUnit.setSelectedItem(rs.getString("nunit"));
                }
                jButton11.setEnabled(false);
            }
        } catch (SQLException ex) {
            Logger.getLogger(WorkOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        loadBOM(cgroup,cproduct);
        loadStepWOSebelum(cgroup,cproduct);
    }
    
    private void loadBOM(String group, String produk){
        listbom.removeAll();
        listbom.removeAllItems();
        listBOMID.clear();
        
        listBOMID.add("");
        listbom.addItem("");
        
        sql="select * from BOMProduct where cgroup = '"+group+"' and cproduct = '"+produk+"' ";
        System.out.println(sql);
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                listBOMID.add(rs.getString("cnbkt"));
                listbom.addItem(rs.getString("cname"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(WorkOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    private void loadBOMMaterialCost(){
        if(tjumlahtotal.getText().isEmpty()) {
            return;
        }
        Double qty=0.0;
        sql="select * from BOMProduct where cnbkt = '"+listBOMID.get(listbom.getSelectedIndex())+"' ";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                qty = rs.getDouble("nqty");
            }
        } catch (SQLException ex) {
            Logger.getLogger(WorkOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        DefaultTableModel model = (DefaultTableModel) jTableMaterial.getModel();
        rfa.clear_table(keyssDef, jTableMaterial);
        
        sql="select * from BOMProductD where cnbkt = '"+listBOMID.get(listbom.getSelectedIndex())+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                Double qtybahan = (rs.getDouble("nqty")/qty);
                Double pricebahan = (rs.getDouble("nprice")/qty);
                Double nilai = Double.valueOf(tjumlahtotal.getText());
                
                model.addRow(new Object[]{
                    -1,
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    Math.ceil(qtybahan*nilai),Math.ceil(pricebahan*nilai),
                    rs.getString("nunit"),
                    rfa.formatDecimal3.format((rs.getDouble("nunit_bigvalue")/qty)*nilai),
                    rfa.formatDecimal3.format((rs.getDouble("nunit_smallvalue")/qty)*nilai),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        DefaultTableModel model1 = (DefaultTableModel) jTableCost.getModel();
        rfa.clear_table(keyssDef, jTableCost);
        
        sql="select * from BOMProductCost where cnbkt = '"+listBOMID.get(listbom.getSelectedIndex())+"'";
        rs=con.queryResult(sql);
        try {
            while(rs.next()){
                Double nilai = Double.valueOf(tjumlahtotal.getText());
                Double price=rs.getDouble("cprice")/qty;
                String interval="Tidak";
                if(rs.getInt("cinterval") == 1) {
                    interval="Ya";
                    
                }
                price=Math.ceil(price*nilai);
                model1.addRow(new Object[]{
                    -1,
                    rs.getString("kode_cost"),
                    rs.getString("cost"),
                    interval,
                    price
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    }
    private void resetNoUrutStep(){
        for(int i=0; i<jTable1.getRowCount();i++){
            jTable1.setValueAt(i+1, i, 0);
        }
    }
   
    private void loadStepWOSebelum(String cgroup,String cproduct){
        if(btSave.getText().equals("Save")) {
            sql="select a.* from PWorkOrderStep a \n" +
                "join (\n" +
                "	select a.id,a.ccode,a.cnbkt,a.cgroup,a.cproduct from PWorkOrder a\n" +
                "	join (\n" +
                "		select max(id) as idlast,cgroup,cproduct from PWorkOrder\n" +
                "		group by cgroup,cproduct\n" +
                "	) b on a.id=b.idlast\n" +
                "	where a.cgroup='"+cgroup+"' and a.cproduct='"+cproduct+"'\n" +
                ") b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                "order by a.urut";
            rs = con.queryResult(sql);
            DefaultTableModel model2 = (DefaultTableModel) jTable1.getModel();
            rfa.clear_table(keyssDef, jTable1); 
            int nomorr=1;
            try {
                while(rs.next()){
                    model2.addRow(new Object[]{
                        nomorr++,
                        rs.getString("cstep_kode"),
                        rs.getString("cstep_name"),
                        rfa.DateString(keyssDef, rs.getDate("dtran"), "yyyy-MM-dd"),
                    });

                }
            } catch (SQLException ex) {
                ex.printStackTrace();
            }
        }
    }
    
    private int insertData(){
        int err= 0;
        if(btSave.getText().equals("Save")) {
            cnbkt = tbukti1.getText()+tbukti.getText();
        }
        con.queryUpdate("delete from PWorkOrderD where ccode='"+tcode.getText()+"' and cnbkt='"+cnbkt+"' ");
        
        for(int i=0; i<jTableMaterial.getRowCount();i++){
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("ccode",tcode.getText());      
            listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
            listObj.put("cgroup",jTableMaterial.getValueAt(i,1).toString());
            listObj.put("cproduct",jTableMaterial.getValueAt(i, 2).toString());
            listObj.put("nqty",rfa.formatDecimal3.format(jTableMaterial.getValueAt(i, 3)));
            listObj.put("nprice",rfa.formatDecimal3.format(jTableMaterial.getValueAt(i, 4)));
            listObj.put("nunit",jTableMaterial.getValueAt(i, 5).toString());
            listObj.put("nunit_bigvalue",jTableMaterial.getValueAt(i, 6).toString());
            listObj.put("nunit_smallvalue",jTableMaterial.getValueAt(i,7).toString());
            listObj.put("cusername",Login.user);
            
//            if("-1".equalsIgnoreCase(jTableMaterial.getValueAt(i, 0).toString())) {
//                //INSERT
//                
//                
//            } else {  
//                //UPDATE
//                sql=con.updateValue("PWorkOrderD", listObj);
//                sql+=" where id = '"+jTableMaterial.getValueAt(i, 0).toString()+"' ";                
//            }
            sql=con.insertValue("PWorkOrderD", listObj);
            System.out.println(sql);
            if(con.queryUpdate(sql)<=0){
                err++;
                return err;
            } 
        }
        
        con.queryUpdate("delete from PWorkOrderCost where ccode='"+tcode.getText()+"' and cnbkt='"+cnbkt+"' "); 
        for(int i=0; i<jTableCost.getRowCount();i++){
            String interval="0";
            if(jTableCost.getValueAt(i, 3) != null) {
                if(jTableCost.getValueAt(i, 3).toString().equals("Ya")){
                    interval ="1";
                }
            }
            
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("ccode",tcode.getText());      
            listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
            listObj.put("kode_cost",jTableCost.getValueAt(i,1).toString());
            listObj.put("cost",jTableCost.getValueAt(i, 2).toString());
            listObj.put("cinterval",interval);
            listObj.put("cprice",rfa.formatDecimal3.format(jTableCost.getValueAt(i, 4)));
            
            sql=con.insertValue("PWorkOrderCost", listObj);
            System.out.println(sql);
            if(con.queryUpdate(sql)<=0){
                err++;
                return err;
            } 
        }
        
        
        con.queryUpdate("delete from PWorkOrderStep where ccode='"+tcode.getText()+"' and cnbkt='"+cnbkt+"' ");
        for(int i=0; i<jTable1.getRowCount();i++){ 
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("ccode",tcode.getText());      
            listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
            listObj.put("cstep_kode",jTable1.getValueAt(i,1).toString());
            listObj.put("cstep_name",jTable1.getValueAt(i,2).toString());
            listObj.put("dtran",jTable1.getValueAt(i,3).toString());
            listObj.put("urut",jTable1.getValueAt(i,0).toString());
            
            sql=con.insertValue("PWorkOrderStep", listObj);
            if(con.queryUpdate(sql)<=0){
                err++;
                return err;
            } 
        }
        
        return err;
    }
    
    private void save(){
        cnbkt_print = tbukti1.getText()+tbukti.getText();
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("cnbkt_order",tpocustomer.getText());
        listObj.put("cgroup",tprodukgroup.getText());
        listObj.put("cproduct",tproduk.getText());
        listObj.put("nqty",tjumlah.getText());
        listObj.put("nqty_add",tjumlahadd.getText());
        listObj.put("nqty_total",tjumlahtotal.getText());
        listObj.put("deadline",rfa.DateString(keyssDef, tdeadline.getDate(), "yyyy-MM-dd"));
        listObj.put("cnbkt_bom",listBOMID.get(listbom.getSelectedIndex()));
        listObj.put("cket",tket.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("kode",tkode.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("PWorkOrder", listObj);
        if(con.queryUpdate(sql)>0) {
            if(insertData()==0){
                JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
                int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
                if(pil == 0) btPrint.doClick();
                
                btCancel.doClick();
            } else {
                JOptionPane.showMessageDialog(this,"Sebagian Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            }
        }else {
            
        }     
        
    }
    private void update(){
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  PWorkOrderD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        cnbkt_print = tbukti1.getText()+tbukti.getText();
        
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti1.getText()+tbukti.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef, jDateTran.getDate(), "yyyy-MM-dd"));
        listObj.put("cnbkt_order",tpocustomer.getText());
        listObj.put("cgroup",tprodukgroup.getText());
        listObj.put("cproduct",tproduk.getText());
        listObj.put("nqty",tjumlah.getText());
        listObj.put("nqty_add",tjumlahadd.getText());
        listObj.put("nqty_total",tjumlahtotal.getText());
        listObj.put("deadline",rfa.DateString(keyssDef, tdeadline.getDate(), "yyyy-MM-dd"));
        listObj.put("cnbkt_bom",listBOMID.get(listbom.getSelectedIndex()));
        listObj.put("cket",tket.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("kode",tkode.getText());
        listObj.put("cusername",Login.user);
        sql=con.updateValue("PWorkOrder", listObj);
        sql+=" where id = '"+id+"' ";
        if(con.queryUpdate(sql)>0) {
            if(insertData()==0){
                cnbkt = tbukti1.getText()+tbukti.getText();
                cnbkt_print = tbukti1.getText()+tbukti.getText();
                
                JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
                int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
                if(pil == 0) btPrint.doClick();
                btCancel.doClick();
            } else {
                JOptionPane.showMessageDialog(this,"Sebagian Data Gagal Disimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            }
        }else {
            
        }
        
        
        
    }
    private void delete(){
        int err=0;
        
        sql="delete from  PWorkOrderStep where ccode = '"+tcode.getText()+"' and cnbkt = '"+cnbkt+"' ";
        con.queryUpdate(sql);
        
        sql="delete from  PWorkOrderCost where ccode = '"+tcode.getText()+"' and cnbkt = '"+cnbkt+"' ";
        con.queryUpdate(sql);
        
        sql="delete from  PWorkOrderD where ccode = '"+tcode.getText()+"' and cnbkt = '"+cnbkt+"' ";
        con.queryUpdate(sql);
            
        sql="delete from  PWorkOrder  where id = '"+id+"' ";
        if(con.queryUpdate(sql)<=0){
            err++;
        } 
        
        if(err==0){
            JOptionPane.showMessageDialog(this,"Data Berhasil Di Hapus.", "Info", JOptionPane.INFORMATION_MESSAGE);
            btCancel.doClick();
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menyimpan.", "Save", JOptionPane.ERROR_MESSAGE);
            
        }
    }
    private void print(){
        String pengaju="",group="",product="";
        Double total =0.0;
        sql = "select 0 as urut,'Produk' as cstatus,a.ccode,a.cnbkt,a.dtran,a.deadline,a.cnbkt_order,a.cgroup,a.cproduct,c.cnameProduct,a.nqty,a.nqty_add,a.nqty_total,'PCS' as nunit,a.cket,\n" +
                "b.kode_customer,d.cname as customer,b.dtran as dtranorder\n" +
                "from PWorkOrder a left join PCustomerPO b on a.ccode=b.ccode and a.cnbkt_order=b.cnbkt\n" +
                "join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
                "left join MCustomer d on b.kode_customer=d.kode_customer\n" +
                "where a.cnbkt='"+cnbkt_print+"' and a.ccode='"+tcode.getText()+"' \n";
        
        sql+="union all\n" +
                "\n" +
                "select 1 as urut,'Bahan' as cstatus,a.ccode,a.cnbkt,a.dtran,a.deadline,a.cnbkt_order,e.cgroup,e.cproduct,f.cnameProduct,a.nqty,a.nqty_add,e.nqty,e.nunit,a.cket,\n" +
                "b.kode_customer,d.cname as customer,b.dtran as dtranorder\n" +
                "from PWorkOrder a left join PCustomerPO b on a.ccode=b.ccode and a.cnbkt_order=b.cnbkt\n" +
                "join MProduk c on a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
                "left join MCustomer d on b.kode_customer=d.kode_customer\n" +
                "join PWorkOrderD e on a.ccode=e.ccode and a.cnbkt=e.cnbkt\n" +
                "join MProduk f on e.cgroup=f.cgroup and e.cproduct=f.cproduct\n" +
                "where a.cnbkt='"+cnbkt_print+"' and a.ccode='"+tcode.getText()+"'";
        System.out.println(sql);
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rfa.DateString(keyssDef, rs.getDate("deadline"),"dd/MM/yyyy"),
                    rs.getString("cnbkt_order"),
                    rs.getString("cgroup"),
                    rs.getString("cproduct"),
                    rs.getString("cnameproduct"),
                    rs.getDouble("nqty"),
                    rs.getDouble("nqty_add"),
                    rs.getDouble("nqty_total"),
                    rs.getString("cket"),
                    rs.getString("kode_customer"),
                    rs.getString("customer"),
                    rfa.DateString(keyssDef, rs.getDate("dtranorder"),"dd/MM/yyyy"),
                    rs.getString("nunit"),
                    rs.getString("cstatus"),
                    
                });
                if(rs.getString("cstatus").equals("Produk")) {
                    group=rs.getString("cgroup");
                    product=rs.getString("cproduct");
                }
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        DefaultTableModel model3 = (DefaultTableModel) tablestep.getModel();
        rfa.clear_table(keyssDef, tablestep);
        sql="select * from PWorkOrderStep where ccode='"+tcode.getText()+"' and cnbkt = '"+cnbkt_print+"'";
        rs=con.queryResult(sql);
        int nomorr=1;
        try {
            while(rs.next()){ 
                model3.addRow(new Object[]{
                    nomorr++,
                    rs.getString("cstep_name"),
                    "Tanggal : "+rfa.DateString(keyssDef, rs.getDate("dtran"), "dd MMMM yyyy"),
                });
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        String note_po="";
        sql="select * from PCustomerPOD \n" +
                "where ccode='"+tcode.getText()+"' and cnbkt='"+cnbkt_print+"' and cgroup='"+group+"' and cproduct='"+product+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                note_po = rs.getString("cket");
            }
        } catch (SQLException ex) {
            Logger.getLogger(WorkOrder.class.getName()).log(Level.SEVERE, null, ex);
        }
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/rwo.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "");
            param.put("notepo", note_po);
            param.put("kode",tkode.getText());
            param.put("stepproduksi", new JRTableModelDataSource(tablestep.getModel()));
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

        jScrollPane5wo = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        tsatuanhigh = new javax.swing.JTextField();
        tsatuanlow = new javax.swing.JTextField();
        tprodukgroup = new javax.swing.JTextField();
        tbahan1 = new javax.swing.JTextField();
        buttonGroup1 = new javax.swing.ButtonGroup();
        jButton8 = new javax.swing.JButton();
        tkaryawan1 = new javax.swing.JTextField();
        tkaryawan = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        jScrollPaneStep = new javax.swing.JScrollPane();
        tablestep = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jPanel3 = new javax.swing.JPanel();
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
        jLabel17 = new javax.swing.JLabel();
        tcustomers = new javax.swing.JTextField();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        btPrint = new javax.swing.JButton();
        jLabel11 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        tproduk = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        tproduknama = new javax.swing.JTextField();
        tket = new javax.swing.JTextField();
        jLabel18 = new javax.swing.JLabel();
        jLabel19 = new javax.swing.JLabel();
        tcustomerkode = new javax.swing.JTextField();
        tcustomer = new javax.swing.JTextField();
        jLabel24 = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        jLabel26 = new javax.swing.JLabel();
        tpocustomer = new javax.swing.JTextField();
        jButton16 = new javax.swing.JButton();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jLabel27 = new javax.swing.JLabel();
        tbahan = new javax.swing.JTextField();
        jLabel28 = new javax.swing.JLabel();
        tjumlah = new javax.swing.JTextField();
        listbom = new javax.swing.JComboBox();
        listUnit = new javax.swing.JComboBox();
        tjumlahtotal = new javax.swing.JTextField();
        tjumlahadd = new javax.swing.JTextField();
        jLabel29 = new javax.swing.JLabel();
        jLabel30 = new javax.swing.JLabel();
        jLabel31 = new javax.swing.JLabel();
        tdeadline = new com.toedter.calendar.JDateChooser();
        jButton1 = new javax.swing.JButton();
        jLabel20 = new javax.swing.JLabel();
        tkode = new javax.swing.JTextField();
        jTabbedPane2 = new javax.swing.JTabbedPane();
        jPanel9 = new javax.swing.JPanel();
        jPanel10 = new javax.swing.JPanel();
        tstepkode = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tstep = new javax.swing.JTextField();
        jLabel32 = new javax.swing.JLabel();
        jLabel33 = new javax.swing.JLabel();
        jDateTran1 = new com.toedter.calendar.JDateChooser();
        btAddSteps = new javax.swing.JButton();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jLabel5 = new javax.swing.JLabel();
        jPanel7 = new javax.swing.JPanel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTableMaterial = new javax.swing.JTable();
        jButton2 = new javax.swing.JButton();
        jPanel8 = new javax.swing.JPanel();
        jScrollPane4 = new javax.swing.JScrollPane();
        jTableCost = new javax.swing.JTable();
        jButton3 = new javax.swing.JButton();

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "dtran", "deadline", "cnbkt_order", "cgroup", "cproduct", "cnameproduct", "nqty", "nqty_add", "nqty_total", "cket", "kode_customer", "customer", "dtranorder", "satuan", "status"
            }
        ));
        jScrollPane5wo.setViewportView(jtable_print);

        tsatuanhigh.setText("jTextField1");

        tsatuanlow.setText("jTextField1");

        tprodukgroup.setText("jTextField1");

        tbahan1.setText("jTextField1");

        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });

        tkaryawan1.setEditable(false);
        tkaryawan1.setBackground(new java.awt.Color(153, 153, 255));
        tkaryawan1.setForeground(new java.awt.Color(255, 255, 255));

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

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Operator");

        tablestep.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null},
                {null, null, null},
                {null, null, null},
                {null, null, null}
            },
            new String [] {
                "no", "step", "tgl"
            }
        ));
        jScrollPaneStep.setViewportView(tablestep);

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
            .addGap(0, 0, Short.MAX_VALUE)
        );

        jPanel2.add(jPanel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 500, 5));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Work Order / Perintah Kerja");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

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

        jLabel13.setText("Customer");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));

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
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 30, 280, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(990, 60, 90, -1));

        jLabel15.setText("Keterangan");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, -1, 20));

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

        jLabel17.setText("Periode ");
        jPanel6.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

        tcustomers.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tcustomersKeyPressed(evt);
            }
        });
        jPanel6.add(tcustomers, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 330, -1));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Gudang", "Nomor Bukti", "Kode", "Tgl WO", "Produk", "Customer", "No Order", "Deadline", "Qty", "Keterangan", "Finish Good"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false
            };

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
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1154, Short.MAX_VALUE)
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
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 274, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Gudang");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tanggal");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 50, 130, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel11.setText("Kode*");
        jLabel11.setToolTipText("");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 130, 20));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 20, 90, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 20, 30, -1));

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
        jPanel1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 120, -1));

        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 80, 250, -1));

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
        jPanel1.add(tbukti1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 80, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("PO Customer");
        jPanel1.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        tproduk.setEditable(false);
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
        jPanel1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 80, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 200, 30, -1));

        tproduknama.setEditable(false);
        tproduknama.setBackground(new java.awt.Color(153, 153, 255));
        tproduknama.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tproduknama, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 200, 220, -1));

        tket.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tketFocusLost(evt);
            }
        });
        jPanel1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 350, 330, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Customer");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 72, 20));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Keterangan");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 350, 72, -1));

        tcustomerkode.setEditable(false);
        tcustomerkode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tcustomerkodeKeyTyped(evt);
            }
        });
        jPanel1.add(tcustomerkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 80, -1));

        tcustomer.setEditable(false);
        jPanel1.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 170, 250, -1));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Bill of Material / Cost");
        jPanel1.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 320, 130, 20));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Bahan");
        jPanel1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 260, 110, 20));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("Deadline");
        jPanel1.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 290, 110, 20));

        tpocustomer.setEditable(false);
        jPanel1.add(tpocustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 300, -1));

        jButton16.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton16.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton16ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton16, new org.netbeans.lib.awtextra.AbsoluteConstraints(450, 140, 30, -1));

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete");
        btDelete.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDeleteActionPerformed(evt);
            }
        });
        jPanel1.add(btDelete, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 390, 97, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel1.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 390, 97, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel1.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 390, 97, -1));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("Jenis Produk");
        jPanel1.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, 110, 20));

        tbahan.setEditable(false);
        jPanel1.add(tbahan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 260, 330, -1));

        jLabel28.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel28.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel28.setText("=");
        jPanel1.add(jLabel28, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 230, 20, 20));

        tjumlah.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tjumlah.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tjumlahCaretUpdate(evt);
            }
        });
        jPanel1.add(tjumlah, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 230, 60, -1));

        listbom.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        listbom.addItemListener(new java.awt.event.ItemListener() {
            public void itemStateChanged(java.awt.event.ItemEvent evt) {
                listbomItemStateChanged(evt);
            }
        });
        listbom.addPopupMenuListener(new javax.swing.event.PopupMenuListener() {
            public void popupMenuCanceled(javax.swing.event.PopupMenuEvent evt) {
            }
            public void popupMenuWillBecomeInvisible(javax.swing.event.PopupMenuEvent evt) {
                listbomPopupMenuWillBecomeInvisible(evt);
            }
            public void popupMenuWillBecomeVisible(javax.swing.event.PopupMenuEvent evt) {
            }
        });
        listbom.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                listbomPropertyChange(evt);
            }
        });
        jPanel1.add(listbom, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 320, 330, -1));

        listUnit.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "PCS", "KG", "ROLL", "REAMS" }));
        jPanel1.add(listUnit, new org.netbeans.lib.awtextra.AbsoluteConstraints(430, 230, 50, -1));

        tjumlahtotal.setEditable(false);
        tjumlahtotal.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jPanel1.add(tjumlahtotal, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 230, 90, -1));

        tjumlahadd.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tjumlahadd.setText("2");
        tjumlahadd.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tjumlahaddCaretUpdate(evt);
            }
        });
        jPanel1.add(tjumlahadd, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 230, 50, -1));

        jLabel29.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel29.setText("Jumlah");
        jPanel1.add(jLabel29, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 230, 110, 20));

        jLabel30.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel30.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel30.setText("+");
        jPanel1.add(jLabel30, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 230, 20, 20));

        jLabel31.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel31.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel31.setText("%");
        jPanel1.add(jLabel31, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 230, 20, 20));

        tdeadline.setDateFormatString("dd/MM/yyyy");
        tdeadline.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                tdeadlinePropertyChange(evt);
            }
        });
        tdeadline.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tdeadlineKeyPressed(evt);
            }
        });
        jPanel1.add(tdeadline, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 290, 130, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Close_Window_16px.png"))); // NOI18N
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 140, 30, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Nomor WO");
        jLabel20.setToolTipText("");
        jPanel1.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 130, 20));
        jPanel1.add(tkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 80, -1));

        jPanel10.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        tstepkode.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                tstepkodeActionPerformed(evt);
            }
        });
        jPanel10.add(tstepkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(370, 10, 5, -1));

        jButton12.setText("...");
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel10.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 10, 30, -1));

        tstep.setEditable(false);
        tstep.setBackground(new java.awt.Color(204, 204, 255));
        jPanel10.add(tstep, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 10, 190, -1));

        jLabel32.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel32.setText("Tanggal Produksi");
        jPanel10.add(jLabel32, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 130, 20));

        jLabel33.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel33.setText("Step Produksi");
        jPanel10.add(jLabel33, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 130, 20));

        jDateTran1.setDateFormatString("dd/MM/yyyy");
        jDateTran1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateTran1PropertyChange(evt);
            }
        });
        jDateTran1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateTran1KeyPressed(evt);
            }
        });
        jPanel10.add(jDateTran1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 40, 120, -1));

        btAddSteps.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        btAddSteps.setText("Add");
        btAddSteps.setMargin(new java.awt.Insets(2, 2, 2, 2));
        btAddSteps.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btAddStepsActionPerformed(evt);
            }
        });
        jPanel10.add(btAddSteps, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 10, 80, -1));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null},
                {null, null, null, null},
                {null, null, null, null},
                {null, null, null, null}
            },
            new String [] {
                "No", "Kode Step", "Proses Produksi", "Tanggal"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false
            };

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jScrollPane3.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(80);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(80);
        }

        jLabel5.setText("Klik 2x Untuk hapus data");

        javax.swing.GroupLayout jPanel9Layout = new javax.swing.GroupLayout(jPanel9);
        jPanel9.setLayout(jPanel9Layout);
        jPanel9Layout.setHorizontalGroup(
            jPanel9Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel9Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel9Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane3)
                    .addGroup(jPanel9Layout.createSequentialGroup()
                        .addGroup(jPanel9Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jPanel10, javax.swing.GroupLayout.PREFERRED_SIZE, 544, javax.swing.GroupLayout.PREFERRED_SIZE)
                            .addComponent(jLabel5))
                        .addGap(0, 53, Short.MAX_VALUE)))
                .addContainerGap())
        );
        jPanel9Layout.setVerticalGroup(
            jPanel9Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel9Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel10, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(18, 18, 18)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 264, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jLabel5)
                .addContainerGap())
        );

        jTabbedPane2.addTab("Step Produksi", jPanel9);

        jTableMaterial.setModel(new javax.swing.table.DefaultTableModel(
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
        jTableMaterial.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTableMaterialMouseClicked(evt);
            }
        });
        jScrollPane1.setViewportView(jTableMaterial);
        if (jTableMaterial.getColumnModel().getColumnCount() > 0) {
            jTableMaterial.getColumnModel().getColumn(0).setMinWidth(0);
            jTableMaterial.getColumnModel().getColumn(0).setMaxWidth(0);
            jTableMaterial.getColumnModel().getColumn(6).setMinWidth(0);
            jTableMaterial.getColumnModel().getColumn(6).setMaxWidth(0);
            jTableMaterial.getColumnModel().getColumn(7).setMinWidth(0);
            jTableMaterial.getColumnModel().getColumn(7).setMaxWidth(0);
        }

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancer-20.png"))); // NOI18N
        jButton2.setText("Hitung Ulang");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });

        javax.swing.GroupLayout jPanel7Layout = new javax.swing.GroupLayout(jPanel7);
        jPanel7.setLayout(jPanel7Layout);
        jPanel7Layout.setHorizontalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel7Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 597, Short.MAX_VALUE)
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel7Layout.createSequentialGroup()
                        .addGap(0, 0, Short.MAX_VALUE)
                        .addComponent(jButton2)))
                .addContainerGap())
        );
        jPanel7Layout.setVerticalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel7Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jButton2)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 332, Short.MAX_VALUE)
                .addContainerGap())
        );

        jTabbedPane2.addTab("RAB Material ", jPanel7);

        jTableCost.setModel(new javax.swing.table.DefaultTableModel(
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
        jTableCost.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTableCostMouseClicked(evt);
            }
        });
        jScrollPane4.setViewportView(jTableCost);
        if (jTableCost.getColumnModel().getColumnCount() > 0) {
            jTableCost.getColumnModel().getColumn(0).setMinWidth(0);
            jTableCost.getColumnModel().getColumn(0).setMaxWidth(0);
        }

        jButton3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancer-20.png"))); // NOI18N
        jButton3.setText("Hitung Ulang");
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });

        javax.swing.GroupLayout jPanel8Layout = new javax.swing.GroupLayout(jPanel8);
        jPanel8.setLayout(jPanel8Layout);
        jPanel8Layout.setHorizontalGroup(
            jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel8Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane4, javax.swing.GroupLayout.DEFAULT_SIZE, 597, Short.MAX_VALUE)
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel8Layout.createSequentialGroup()
                        .addGap(0, 0, Short.MAX_VALUE)
                        .addComponent(jButton3)))
                .addContainerGap())
        );
        jPanel8Layout.setVerticalGroup(
            jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel8Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jButton3)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jScrollPane4, javax.swing.GroupLayout.DEFAULT_SIZE, 327, Short.MAX_VALUE)
                .addContainerGap())
        );

        jTabbedPane2.addTab("RAB Cost", jPanel8);

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 536, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane2)
                .addContainerGap())
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addContainerGap()
                        .addComponent(jTabbedPane2)))
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

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 5, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed
         rfa.EnterNextFocus(evt, tproduk);
    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        sql = "select * from mproduk where cproduct = '"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tprodukgroup.setText(rs.getString("cgroup"));
            tproduknama.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
      
    }//GEN-LAST:event_tprodukKeyPressed

    private void tkaryawanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawanFocusLost
        sql = "select * from mkaryawan where nik = '"+tkaryawan.getText()+"'  ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tkaryawan1.setText(rs.getString("cname"));

            }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tkaryawanFocusLost

    private void tkaryawanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawanKeyPressed
        rfa.EnterNextFocus(evt, tket);
    }//GEN-LAST:event_tkaryawanKeyPressed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawan.setText(a.Kode);
            tkaryawan1.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton8ActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(jTable1.getRowCount() == 0) {
                JOptionPane.showMessageDialog(this,"Table Urutan Proses Produksi belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(listbom.getSelectedIndex() == 0) {
                JOptionPane.showMessageDialog(this,"BOM / RAB belum di pilih", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(tket.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"Keterangan belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(tkode.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"KODE belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(btSave.getText().equals("Save")) {
                if(con.akses(akses, rfa.INSERT)) {
                    //LastCode();
                    Timer t = new Timer();
                    t.schedule(new TimerTask() {
                        @Override
                        public void run() {
                            save();
                        }
                    }, 1); 
                }
            } else if(btSave.getText().equals("Update")){
                if(!cnbkt.equals(tbukti1.getText()+tbukti.getText())) {
                    sql="SELECT top 1 * FROM PProdSteps where ccode='"+tcode.getText()+"' and cnbkt = '"+cnbkt+"'";
                    rs = con.queryResult(sql);
                    try {  
                        while(rs.next()){
                            JOptionPane.showMessageDialog(this,"Maaf Nomor SPK tidak dapat di rubah \nkarena sudah dilakukan Proses Produksi pada tanggal "+
                                    rfa.DateString(keyssDef, rs.getDate("dtran"), "dd/MM/yyyy")+" Oleh "+rs.getString("cusername"), "Info", JOptionPane.ERROR_MESSAGE);
                            return;
                        }
                    } catch (SQLException ex) { ex.printStackTrace();   }
                
                }
                
                if(con.akses(akses, rfa.UPDATE)) {
                    con.queryUpdate("ALTER TABLE PWorkOrder NOCHECK CONSTRAINT ALL");
                    con.queryUpdate("ALTER TABLE PWorkOrderCost NOCHECK CONSTRAINT ALL");
                    con.queryUpdate("ALTER TABLE PWorkOrderD NOCHECK CONSTRAINT ALL");
                    con.queryUpdate("ALTER TABLE PWorkOrderStep NOCHECK CONSTRAINT ALL");
                    update();
                    
                    con.queryUpdate("ALTER TABLE PWorkOrderD WITH CHECK CHECK CONSTRAINT ALL");
                    con.queryUpdate("ALTER TABLE PWorkOrderCost WITH CHECK CHECK CONSTRAINT ALL");
                    con.queryUpdate("ALTER TABLE PWorkOrderStep WITH CHECK CHECK CONSTRAINT ALL");
                    con.queryUpdate("ALTER TABLE PWorkOrder WITH CHECK CHECK  CONSTRAINT ALL");
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        sql="SELECT top 1 * FROM PProdSteps where ccode='"+tcode.getText()+"' and cnbkt = '"+cnbkt+"'";
        rs = con.queryResult(sql);
        try {  
            while(rs.next()){
                JOptionPane.showMessageDialog(this,"Maaf Nomor SPK tidak dapat di rubah \nkarena sudah dilakukan Proses Produksi pada tanggal "+
                        rfa.DateString(keyssDef, rs.getDate("dtran"), "dd/MM/yyyy")+" Oleh "+rs.getString("cusername"), "Info", JOptionPane.ERROR_MESSAGE);
                
                JOptionPane.showMessageDialog(this,"Jika ingin menghapus data, silahkan untuk munghubungi pada bagian produksi. ","Info",JOptionPane.INFORMATION_MESSAGE);
                return;
            }
        } catch (SQLException ex) { ex.printStackTrace();   }

        if(con.akses(akses, rfa.DELETE)) {
           int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus semua transaksi ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete();
        }
    }//GEN-LAST:event_btDeleteActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 2).toString();
            cnbkt_print = cnbkt;
            tcode.setText(tcodes.getText());
            loadEdit();
        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        clear();
                deff();
                LastCode();
                loadData();
    }//GEN-LAST:event_btCancelActionPerformed

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tketFocusLost

    private void tcustomerkodeKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tcustomerkodeKeyTyped
        if(evt.getKeyChar() == KeyEvent.VK_BACK_SPACE) {
            rfa.empty(keyssDef, tcustomer,tcustomerkode);
        }
    }//GEN-LAST:event_tcustomerkodeKeyTyped

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

    private void jButton16ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton16ActionPerformed
        DialogOrderCustomer a = new DialogOrderCustomer();
        a.setVisible(true);
        if(a.status){
            loadPOCustomer(a.id);
        }
    }//GEN-LAST:event_jButton16ActionPerformed

    private void jTableMaterialMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTableMaterialMouseClicked
        if(evt.getClickCount()==2){
            int baris = jTableMaterial.getSelectedRow();
            loadEditItem(baris);
        }
    }//GEN-LAST:event_jTableMaterialMouseClicked

    private void jTableCostMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTableCostMouseClicked
       
    }//GEN-LAST:event_jTableCostMouseClicked

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tproduknama.setText(a.namaBarang);
            
            loadBOM(a.groupBarang,a.kodeBarang);
            loadStepWOSebelum(a.groupBarang,a.kodeBarang);
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void listbomPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_listbomPropertyChange
        
    }//GEN-LAST:event_listbomPropertyChange

    private void tjumlahCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tjumlahCaretUpdate
        hitungPenambahan();
    }//GEN-LAST:event_tjumlahCaretUpdate

    private void tjumlahaddCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tjumlahaddCaretUpdate
        hitungPenambahan();
    }//GEN-LAST:event_tjumlahaddCaretUpdate

    private void tdeadlinePropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_tdeadlinePropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_tdeadlinePropertyChange

    private void tdeadlineKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tdeadlineKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tdeadlineKeyPressed

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode() == 10) loadData();
    }//GEN-LAST:event_tbuktisKeyPressed

    private void tcustomersKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tcustomersKeyPressed
        if(evt.getKeyCode() == 10) loadData();
    }//GEN-LAST:event_tcustomersKeyPressed

    private void tketsKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketsKeyPressed
        if(evt.getKeyCode() == 10) loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_tketsKeyPressed

    private void listbomItemStateChanged(java.awt.event.ItemEvent evt) {//GEN-FIRST:event_listbomItemStateChanged
        if(listbom.getSelectedIndex()>0 && btSave.getText().equalsIgnoreCase("Save")) {
            System.out.println(listbom.getSelectedIndex());
            loadBOMMaterialCost();
        }
    }//GEN-LAST:event_listbomItemStateChanged

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void tstepkodeActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_tstepkodeActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_tstepkodeActionPerformed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogPublicTP.DialogSteps a = new DialogSteps("");
        a.setVisible(true);
        if(a.status){
            tstepkode.setText(a.kode);
            tstep.setText(a.name); 
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void jDateTran1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTran1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTran1PropertyChange

    private void jDateTran1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTran1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTran1KeyPressed

    private void btAddStepsActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btAddStepsActionPerformed
        if(tstep.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Step Produksi belum di pilih", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        } 
 
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            model.addRow(new Object[]{
                jTable1.getRowCount()+1,
                tstepkode.getText(),
                tstep.getText(),
                rfa.DateString(keyssDef, jDateTran1.getDate(), "yyyy-MM-dd"),
            });
            
        resetNoUrutStep();
    }//GEN-LAST:event_btAddStepsActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount()== 2) {
                try {
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();

            int baris = jTable1.getSelectedRow();
            model.removeRow(baris); 
            resetNoUrutStep();
        } catch (Exception e) {}
        }
        
    }//GEN-LAST:event_jTable1MouseClicked

    private void listbomPopupMenuWillBecomeInvisible(javax.swing.event.PopupMenuEvent evt) {//GEN-FIRST:event_listbomPopupMenuWillBecomeInvisible
        // TODO add your handling code here:
    }//GEN-LAST:event_listbomPopupMenuWillBecomeInvisible

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        tpocustomer.setText("");
        tcustomerkode.setText("");
        tcustomer.setText(""); 
        tbahan.setText("");
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        loadBOMMaterialCost();
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        loadBOMMaterialCost();
    }//GEN-LAST:event_jButton3ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAddSteps;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.ButtonGroup buttonGroup1;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton16;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private com.toedter.calendar.JDateChooser jDateTran1;
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
    private javax.swing.JLabel jLabel22;
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
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel10;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JPanel jPanel7;
    private javax.swing.JPanel jPanel8;
    private javax.swing.JPanel jPanel9;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5wo;
    private javax.swing.JScrollPane jScrollPaneStep;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTabbedPane jTabbedPane2;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTableCost;
    private javax.swing.JTable jTableMaterial;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTable jtable_print;
    private javax.swing.JComboBox listUnit;
    private javax.swing.JComboBox listbom;
    private javax.swing.JTable tablestep;
    private javax.swing.JTextField tbahan;
    private javax.swing.JTextField tbahan1;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tcustomer;
    private javax.swing.JTextField tcustomerkode;
    private javax.swing.JTextField tcustomers;
    private com.toedter.calendar.JDateChooser tdeadline;
    private javax.swing.JTextField tjumlah;
    private javax.swing.JTextField tjumlahadd;
    private javax.swing.JTextField tjumlahtotal;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawan1;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tkode;
    private javax.swing.JTextField tpocustomer;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tproduknama;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    private javax.swing.JTextField tstep;
    private javax.swing.JTextField tstepkode;
    // End of variables declaration//GEN-END:variables
}
