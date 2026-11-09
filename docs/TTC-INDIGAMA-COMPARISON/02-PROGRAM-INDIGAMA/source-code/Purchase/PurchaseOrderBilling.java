/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Purchase;

import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogPurchaseOrder;
import DialogPublicTP.DialogReqOrder;
import DialogPublicTP.DialogSatuan;
import DialogPublicTP.DialogSupplier;
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
public class PurchaseOrderBilling extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="PURCHASE BILL";
    String idKas="O2";
    ArrayList<String> deleteRow;
    String satuanterkecil;
    public PurchaseOrderBilling(String mode) {
        initComponents();

        push_button();
        deleteRow = new ArrayList<>();
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
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        btPrint.setVisible(false);
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(new Date());
        
        tcodes.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
        
        
        LastCode();
        hitungTotalTransaksi();
    }
    private void clear(){
        cnbkt_print="";
        rfa.empty(MenuAdmin.keyssDef,tket,tproduk,tprodukgroup,tproduk2,tqty,tharga,ttotal,tsatuan,tsatuanhigh,tsatuanlow,tdisc,tsupplier,tsupplier2,
                tbukti_po);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        jDateTranTempo.setDate(null);
        deleteRow.clear();
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        rfa.empty(MenuAdmin.keyssDef,tproduk,tprodukgroup,tproduk2,tqty,tharga,ttotal,tsatuan,tsatuanhigh,tsatuanlow,tdisc);
        
    }
    
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,left(cnbkt,3))) as lastno from TranPurchaseOrderBilling where ISNUMERIC(left(cnbkt,3)) = 1 and ccode='"+tcode.getText()+"' --and cnbkt like '%"+"PO/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/"+Login.subdefault+"-")+"%' ");
        rs = con.queryResult("select max(convert(int,left(cnbkt,3))) as lastno "
                + " from TranPurchaseOrderBilling "
                + " where  ccode='"+tcode.getText()+"' "
                + " and month(dtran)= '"+rfa.DateString(keyssDef, jDateTran.getDate(),"MM")+"' ");
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1)+"/BILL-IK01/"+con.romawi(rfa.DateString(keyssDef, jDateTran.getDate(),"MM"))+"/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yy"));
//        tbukti1.setText();
    } 
    private void loadData(){
        sql = "select a.id,a.cnbkt,a.cnbkt_po,a.cnbkt_inv,a.cdue_date,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cdesc,\n" +
            "b.cname as gudang,sum(c.ntotalall) as total,isnull(d.nominalpay,0) as pay \n" +
            "from TranPurchaseOrderBilling a join MGudang b on a.ccode=b.ccode\n" +
            "join TranPurchaseOrderBillingD c on a.cnbkt=c.cnbkt and a.ccode=c.ccode\n" +
            "left join (	\n" +
            "	select a.ccode,a.cnbkt_bill,sum(b.namnt) as nominalpay from TranPayment a join TranPayment_detail b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
            "	group by a.ccode,a.cnbkt_bill\n" +
            ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt_bill\n" +
            "where a.ccode='"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
            "and a.cdesc like '%"+tkets.getText()+"%'\n"
                + "group by a.id,a.cnbkt,a.cnbkt_po,a.cnbkt_inv,a.cdue_date,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cdesc,b.cname,isnull(d.nominalpay,0)\n";
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
                    rs.getString("id"),String.valueOf(nomor++),
                    rs.getString("gudang"),
                    rs.getString("cnbkt"),
                    rs.getString("cnbkt_po"),
                    rs.getString("cnbkt_inv"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cdue_date"),
                    rs.getString("cdesc"),
                    rfa.formatDecimal1.format(rs.getDouble("total")), 
                    rfa.formatDecimal1.format(rs.getDouble("pay")), 
                    rfa.formatDecimal1.format(rs.getDouble("total")-rs.getDouble("pay")), 
                });
                
                if((rs.getDouble("total")-rs.getDouble("pay"))<=0) {
                    colorSet.add(roww);
                }
                roww++;
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();
        con.colorTableSet(jTable2, colorSet);
        jTextField12.setText("Total Data : "+rfa.formatDecimal1.format(jTable2.getRowCount()));
    }
    private void loadEdit(){
        btSave.setText("Update");
        btDelete.setVisible(true);
        sql = "select * from TranPurchaseOrderBilling where id='"+id+"'  ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                
                no++;
                tcode.setText(rs.getString("ccode"));
                
                tbukti.setText(rs.getString("cnbkt").substring(0, rs.getString("cnbkt").length()));
                //tbukti.setText(rs.getString("cnbkt").substring(0, rs.getString("cnbkt").length()-4));
                //tbukti1.setText(rs.getString("cnbkt").substring(rs.getString("cnbkt").length()-4, rs.getString("cnbkt").length()));
                tket.setText(rs.getString("cdesc"));
                jDateTran.setDate(rs.getDate("dtran"));
                tbukti_po.setText(rs.getString("cnbkt_po"));
                tbukti_inv.setText(rs.getString("cnbkt_inv"));
                jDateTranTempo.setDate(rs.getDate("cdue_date"));
                
//                tcode.setText(rs.getString("ccode"));
   
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        
        String bkt = cnbkt;
        sql="select a.*,b.cnameproduct\n" +
        "from TranPurchaseOrderBillingD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
        "where a.cnbkt = '"+bkt+"' and a.ccode='"+tcode.getText()+"'";
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                System.out.println(rs.getString("id"));
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cproduct"),rs.getString("cgroup"),rs.getString("cnameproduct"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),
                    rs.getDouble("ndisc"),rs.getDouble("ntotal"),
                    rs.getDouble("ppn"),rs.getDouble("ntotalall"),
                    rs.getString("nunit"),rs.getDouble("nunit_bigvalue"),rs.getDouble("nunit_smallvalue"),
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
        if(jTable1.getValueAt(baris,5)!=null) tharga.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris, 5).toString())));
        if(jTable1.getValueAt(baris,6)!=null) tdisc.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris, 6).toString())));
        if(jTable1.getValueAt(baris,8)!=null) tppn.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris,8).toString())));
        if(jTable1.getValueAt(baris,10)!=null) tsatuan.setText(jTable1.getValueAt(baris, 10).toString());
        if(jTable1.getValueAt(baris,11)!=null) tsatuanhigh.setText(jTable1.getValueAt(baris, 11).toString());
        if(jTable1.getValueAt(baris,12)!=null) tsatuanlow.setText(jTable1.getValueAt(baris, 12).toString());
        
        tsupplier.requestFocus();
        tproduk.requestFocus();
        tqty.requestFocus();
        btAdd.setText("Ubah");
        btBatal.setVisible(true);
        btHapus.setVisible(true);
    }
    private void loadByPO(){
        if(tbukti_po.getText().isEmpty()) {
            return;
        }
//        if(jTable1.getRowCount()>0) {
//            int pil=JOptionPane.showConfirmDialog(this, "Apakah data request order akan di tambahkan? ", "Clear Data", JOptionPane.YES_NO_OPTION);
//            if(pil == 0) {
//                
//            } else {
//                for(int i=0; i<jTable1.getRowCount();i++){
//                    if(!"-1".equals(jTable1.getValueAt(i, 0).toString())) {
//                        deleteRow.add(jTable1.getValueAt(i, 0).toString());
//                    }
//                }
//                rfa.clear_table(keyssDef, jTable1);
//            }
//        }
        
        rfa.clear_table(keyssDef, jTable1);
        
        
        String bkt = cnbkt;
        sql="select a.*,b.cnameProduct,d.big_value,d.small_value,isnull(bb.nppn,0) as ppn \n" +
        "from TranPurchaseOrder_detail a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
        "join TranPurchaseOrder bb on a.ccode=bb.ccode and a.cnbkt=bb.cnbkt\n" +
        "--join msupplier c on a.kode_supplier=c.kode_supplier \n" +
        "join msatuan d on a.nunit=d.cname \n" +
        "where a.cnbkt = '"+tbukti_po.getText()+"' and a.ccode='"+tcode.getText()+"'--and a.kode_supplier = '"+tsupplier.getText()+"' ";
        
        
        
        
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                System.out.println(rs.getString("id"));
                model.addRow(new Object[]{
                    -1,
                    rs.getString("cproduct"),
                    rs.getString("cgroup"),
                    rs.getString("cnameproduct"),
                    rs.getDouble("nqty"),
                    rs.getDouble("nprice"),
                    rs.getDouble("ndisc"),
                    rs.getDouble("ntotal"),
                    rs.getDouble("ppn"),
                    rs.getDouble("ntotal")+(rs.getDouble("ntotal")*(rs.getDouble("ppn")/100)),
                    rs.getString("nunit"),
                    rs.getDouble("nunit_bigvalue"),
                    rs.getDouble("nunit_smallvalue"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        hitungTotalTransaksi();
        
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns();
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
        if(tharga.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Harga wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
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
                tproduk.getText(),
                tprodukgroup.getText(),
                tproduk2.getText(),
                Double.valueOf(tqty.getText()),
                Double.valueOf(tharga.getText()),
                Double.valueOf(tdisc.getText()),
                Double.valueOf(ttotalsub.getText().replaceAll(",", "")),
                Double.valueOf(tppn.getText().replaceAll(",", "")),
                Double.valueOf(ttotal.getText().replaceAll(",", "")),
                tsatuan.getText(),
                tsatuanhigh.getText(),
                tsatuanlow.getText(),
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(iddetail, barisEdit, col++);
            jTable1.setValueAt(tproduk.getText(), barisEdit, col++);
            jTable1.setValueAt(tprodukgroup.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduk2.getText(), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tqty.getText()), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tharga.getText()), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tdisc.getText()), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(ttotalsub.getText().replaceAll(",", "")), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tppn.getText().replaceAll(",", "")), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(ttotal.getText().replaceAll(",", "")), barisEdit, col++);
            jTable1.setValueAt(tsatuan.getText(), barisEdit, col++);
            jTable1.setValueAt(tsatuanhigh.getText(), barisEdit, col++);
            jTable1.setValueAt(tsatuanlow.getText(), barisEdit, col++);
            
        }
        hitungTotalTransaksi();
        deffBatal();
        
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable1);
        tca.adjustColumns();
        
    }
    private void hitungTotalTransaksi(){
        Double totalNilai=0.0;
        for(int i=0; i<jTable1.getRowCount(); i++){
            totalNilai+=Double.valueOf(jTable1.getValueAt(i, 9).toString());
        }
        infoTotal.setText("Total Transaksi : Rp. "+rfa.formatDecimal2.format(totalNilai));
        if(jTable1.getRowCount()>0){
            jButton12.setEnabled(false);
        }
    }
    private void hitungTotalIn(){
        Double qty=0.0,harga=0.0,disc=0.0,totalsub=0.0,total=0.0,ppn=0.0,totalAll=0.0;
        if(!tqty.getText().isEmpty()) qty = Double.valueOf(tqty.getText());
        if(!tharga.getText().isEmpty()) harga = Double.valueOf(tharga.getText());
        if(!tdisc.getText().isEmpty()) disc = Double.valueOf(tdisc.getText());
        if(!tppn.getText().isEmpty()) ppn = Double.valueOf(tppn.getText());
        
        total = qty*harga;
        total = total-disc;
        totalAll = total+(total*(ppn/100));
        
        ttotalsub.setText(rfa.formatDecimal2.format(total));
        ttotal.setText(rfa.formatDecimal2.format(totalAll));
    }
    
    private int insertData(){
        int err= 0;
        for(int i=0; i<jTable1.getRowCount();i++){
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("ccode", tcode.getText());
            listObj.put("cnbkt", tbukti.getText());
            listObj.put("cgroup", jTable1.getValueAt(i, 2).toString());
            listObj.put("cproduct", jTable1.getValueAt(i, 1).toString());
            listObj.put("nqty", rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
            listObj.put("nprice", rfa.formatDecimal3.format(jTable1.getValueAt(i, 5)));
            listObj.put("ndisc", rfa.formatDecimal3.format(jTable1.getValueAt(i, 6)));
            listObj.put("ntotal", rfa.formatDecimal3.format(jTable1.getValueAt(i, 7)));
            listObj.put("ppn", rfa.formatDecimal3.format(jTable1.getValueAt(i, 8)));
            listObj.put("ntotalall", rfa.formatDecimal3.format(jTable1.getValueAt(i, 9)));
            listObj.put("nunit", jTable1.getValueAt(i, 10).toString());
            listObj.put("nunit_bigvalue", jTable1.getValueAt(i,11).toString());
            listObj.put("nunit_smallvalue", jTable1.getValueAt(i,12).toString());
            listObj.put("cusername", Login.user);
            if("-1".equalsIgnoreCase(jTable1.getValueAt(i, 0).toString())) {
                //INSERT
                sql=con.insertValue("TranPurchaseOrderBillingD", listObj);
//                
//                sql="INSERT INTO TranPurchaseOrderBillingD (cnbkt,ccode,cproduct,cgroup,"
//                        + "nqty,nprice,ndisc,ntotal,nunit,"
//                        + "fstatus,cusername,kode_supplier,nunit_bigvalue,nunit_smallvalue)\n" 
//                        + "VALUES\n" 
//                        + "('"+tbukti.getText()+"','"+tcode.getText()+"','"+jTable1.getValueAt(i,1).toString()+"','"+jTable1.getValueAt(i, 2).toString()+"',"
//                        + "'"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 3))+"','"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"','"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 5))+"','"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 6))+"','"+jTable1.getValueAt(i, 7).toString()+"',"
//                        + "'1','"+Login.user+"','"+jTable1.getValueAt(i,10).toString()+"','"+jTable1.getValueAt(i, 8)+"','"+jTable1.getValueAt(i, 9)+"')";
            } else {  
                //UPDATE
                sql=con.updateValue("TranPurchaseOrderBillingD", listObj);
                sql+=" where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
//                sql="update TranPurchaseOrderBillingD set cnbkt='"+tbukti.getText()+"',ccode='"+tcode.getText()+"',cproduct='"+jTable1.getValueAt(i,1).toString()+"',cgroup='"+jTable1.getValueAt(i, 2).toString()+"',"
//                        + "nqty='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 3))+"',nprice='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"',ndisc='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 5))+"',ntotal='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 6))+"',nunit='"+jTable1.getValueAt(i, 7).toString()+"',"
//                        + "fstatus='1',cusername='"+Login.user+"',kode_supplier='"+jTable1.getValueAt(i,10).toString()+"',nunit_bigvalue='"+jTable1.getValueAt(i, 8)+"',nunit_smallvalue='"+jTable1.getValueAt(i, 9)+"' \n" 
//                        + " where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
            
            }
            
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    private void save(){
        cnbkt_print = tbukti.getText();
        String due_date = "NULL";
        if(jDateTranTempo.getDate() != null) {
            due_date = rfa.DateString(keyssDef,jDateTranTempo.getDate(),"yyyy-MM-dd");
        }
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("ccode", tcode.getText());
        listObj.put("cnbkt", tbukti.getText());
        listObj.put("cnbkt_po", tbukti_po.getText());
        listObj.put("cnbkt_inv", tbukti_inv.getText());
        listObj.put("cyear", rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy"));
        listObj.put("dtran", rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cdue_date", due_date);
        listObj.put("cdesc", tket.getText());
        listObj.put("cusername", Login.user);
        sql=con.insertValue("TranPurchaseOrderBilling", listObj);
        
//        sql="insert into TranPurchaseOrderBilling (cnbkt,cnbkt_PR,ccode,cyear,dtran,cdue_date,cdesc,fstatus,cusername,nik,nik_cname) values "
//                + " ('"+tbukti.getText()+"','"+tbukti_po.getText()+"','"+tcode.getText()+"','"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy")+"','"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"',"+due_date+",'"+tket.getText()+"','1','"+Login.user+"','','')";
        if(con.queryUpdate(sql)>0) {
            if(insertData()==0){
                JOptionPane.showMessageDialog(this,"Data Berhasil Di Simpan.", "Info", JOptionPane.INFORMATION_MESSAGE);
//                int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
//                if(pil == 0) btPrint.doClick();
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
        con.queryUpdate("ALTER TABLE TranPurchaseOrderBilling  NOCHECK  CONSTRAINT ALL");
        
        String due_date = "NULL";
        if(jDateTranTempo.getDate() != null) {
            due_date = "'"+rfa.DateString(keyssDef,jDateTranTempo.getDate(),"yyyy-MM-dd")+"'";
        }
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("ccode", tcode.getText());
        listObj.put("cnbkt", tbukti.getText());
        listObj.put("cnbkt_po", tbukti_po.getText());
        listObj.put("cnbkt_inv", tbukti_inv.getText());
        listObj.put("cyear", rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy"));
        listObj.put("dtran", rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cdue_date", due_date);
        listObj.put("cdesc", tket.getText());
        listObj.put("cusername", Login.user);
        sql=con.updateValue("TranPurchaseOrderBilling", listObj);
        sql+=" where id='"+id+"'";
        if(con.queryUpdate(sql)>0) {
            for(int i=0; i<deleteRow.size();i++){
                sql="delete from  TranPurchaseOrderBillingD  where id = '"+deleteRow.get(i)+"' ";
                con.queryUpdate(sql);
            }
            if(insertData()==0){
                JOptionPane.showMessageDialog(this,"Data Berhasil Di Edit.", "Info", JOptionPane.INFORMATION_MESSAGE);
//                int pil=JOptionPane.showConfirmDialog(this, "Apakah anda akan mencetak data tersebut? ", "Print", JOptionPane.YES_NO_OPTION);
//                if(pil == 0) btPrint.doClick();
                clear();
                deff();
                LastCode();
                loadData();
            } else {
                JOptionPane.showMessageDialog(this,"Gagal Mengubah Sebagian Data.", "Save", JOptionPane.ERROR_MESSAGE);

            }
        }
        
        con.queryUpdate("ALTER TABLE TranPurchaseOrderBilling  WITH CHECK CHECK  CONSTRAINT ALL");
    }
    private void delete(){
        int err=0;
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  TranPurchaseOrderBillingD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  TranPurchaseOrderBillingD  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                if(con.queryUpdate(sql)<=0){
                    err++;
                } 
            }
            
        }
        sql="delete from  TranPurchaseOrderBilling  where id = '"+id+"' ";
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
        String pengaju="";
        Double total =0.0;
        sql = "select a.cnbkt,a.ccode,a.dtran,a.cdesc,a.nik,a.nik_cname,\n" +
                "b.kode_supplier,d.cname as nama_supplier,b.cgroup,b.cproduct,c.cnameProduct,b.nqty,b.nprice,b.ndisc,b.ntotal,b.nunit\n" +
                "from TranPurchaseOrderBilling a join TranPurchaseOrderBillingD b on a.cnbkt=b.cnbkt\n" +
                "join MProduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
                "join MSupplier d on b.kode_supplier=d.kode_supplier\n" +
                "where a.cnbkt = '"+cnbkt_print+"'\n" +
                "order by b.kode_supplier,b.cgroup,b.cproduct";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                pengaju=rs.getString("nik_cname")+" ("+rs.getString("nik")+")";
                model.addRow(new Object[]{
                    nomor++,
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("kode_supplier"),rs.getString("nama_supplier"),
                    rs.getDouble("nqty"),rs.getDouble("nprice"),
                    rs.getDouble("ndisc"),rs.getDouble("ntotal"),
                    rs.getString("cnameProduct"),
                    rs.getString("nunit"),
                });
                
                total+=rs.getDouble("ntotal");
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        if(nomor<6){
            for(int i=nomor; i<6 ;i++){
                model.addRow(new Object[]{
                    null,
                });
            }
            
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranPurchaseOrder.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", deffProgram.get("pt"));
            param.put("pengaju", pengaju);
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

        jScrollPane5 = new javax.swing.JScrollPane();
        jtable_print = new javax.swing.JTable();
        tsatuanhigh = new javax.swing.JTextField();
        tsatuanlow = new javax.swing.JTextField();
        tprodukgroup = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
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
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField13 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jLabel5 = new javax.swing.JLabel();
        jScrollPane4 = new javax.swing.JScrollPane();
        tket = new javax.swing.JTextArea();
        btPrint = new javax.swing.JButton();
        jLabel17 = new javax.swing.JLabel();
        tproduk = new javax.swing.JTextField();
        tproduk2 = new javax.swing.JTextField();
        tqty = new javax.swing.JTextField();
        jLabel18 = new javax.swing.JLabel();
        jLabel16 = new javax.swing.JLabel();
        ttotal = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        tharga = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        tsatuan = new javax.swing.JTextField();
        btsatuan = new javax.swing.JButton();
        btHapus = new javax.swing.JButton();
        btBatal = new javax.swing.JButton();
        btAdd = new javax.swing.JButton();
        tcode = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jDateTran = new com.toedter.calendar.JDateChooser();
        tbukti = new javax.swing.JTextField();
        tdisc = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        ttotalsub = new javax.swing.JTextField();
        jLabel23 = new javax.swing.JLabel();
        tsupplier = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        tsupplier2 = new javax.swing.JTextField();
        jLabel10 = new javax.swing.JLabel();
        jLabel11 = new javax.swing.JLabel();
        tbukti_po = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        jLabel14 = new javax.swing.JLabel();
        jDateTranTempo = new com.toedter.calendar.JDateChooser();
        jLabel6 = new javax.swing.JLabel();
        tbukti_inv = new javax.swing.JTextField();
        jLabel24 = new javax.swing.JLabel();
        tppn = new javax.swing.JTextField();
        jLabel25 = new javax.swing.JLabel();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        infoTotal = new javax.swing.JLabel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "no", "cnbkt", "dtran", "kode_supplier", "nama_supplier", "qty", "harga", "disc", "total", "desc", "satuan"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane5.setViewportView(jtable_print);

        tsatuanhigh.setText("jTextField1");

        tsatuanlow.setText("jTextField1");

        tprodukgroup.setText("jTextField1");

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

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Billing of Purchase Order / Penagihan");
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
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, 20));

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
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No", "Gudang", "No Bukti", "No PO", "No Inv Vendor", "Tanggal", "Jatuh Tempo", "Keterangan", "Total", "Pay", "Sisa Tagihan"
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
        jTable2.setAutoscrolls(false);
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
                    .addComponent(jScrollPane2, javax.swing.GroupLayout.Alignment.TRAILING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1270, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 129, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, 227, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jScrollPane3)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, 326, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(0, 0, Short.MAX_VALUE)))
                .addContainerGap())
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel6, javax.swing.GroupLayout.PREFERRED_SIZE, 101, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 213, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.PREFERRED_SIZE, 131, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jLayeredPane1.setBackground(new java.awt.Color(204, 204, 204));
        jLayeredPane1.setOpaque(true);
        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Log");
        jLayeredPane1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 72, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Transaksi");
        jLayeredPane1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Bukti Tagihan");
        jLabel5.setToolTipText("");
        jLayeredPane1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, 140, 20));

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

        jLayeredPane1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 190, 330, 70));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 70, 130, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Produk");
        jLayeredPane1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 10, 90, 20));

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
        jLayeredPane1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 10, 80, -1));

        tproduk2.setEditable(false);
        tproduk2.setBackground(new java.awt.Color(153, 153, 255));
        tproduk2.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tproduk2, new org.netbeans.lib.awtextra.AbsoluteConstraints(770, 10, 210, -1));

        tqty.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tqtyCaretUpdate(evt);
            }
        });
        tqty.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tqtyKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tqtyKeyTyped(evt);
            }
        });
        jLayeredPane1.add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 40, 110, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Qty / Satuan");
        jLayeredPane1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 40, 90, 20));

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Total All");
        jLayeredPane1.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 190, 90, 20));

        ttotal.setEditable(false);
        ttotal.setBackground(new java.awt.Color(153, 153, 255));
        ttotal.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(ttotal, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 190, 180, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Harga Per Qty");
        jLayeredPane1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 70, -1, 20));

        tharga.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                thargaCaretUpdate(evt);
            }
        });
        tharga.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                thargaMouseClicked(evt);
            }
        });
        tharga.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                thargaKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                thargaKeyTyped(evt);
            }
        });
        jLayeredPane1.add(tharga, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 70, 180, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 10, 30, -1));

        tsatuan.setEditable(false);
        tsatuan.setBackground(new java.awt.Color(153, 153, 255));
        tsatuan.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(770, 40, 150, -1));

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
        jLayeredPane1.add(btsatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(920, 40, 30, -1));

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
        jLayeredPane1.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 240, 30, 25));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jLayeredPane1.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(680, 240, 70, 25));

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
        jLayeredPane1.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(750, 240, 80, 25));

        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jLayeredPane1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 10, 90, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 10, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 10, 200, -1));

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
        jLayeredPane1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 40, 180, -1));

        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jLayeredPane1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 70, 180, -1));

        tdisc.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tdiscCaretUpdate(evt);
            }
        });
        tdisc.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tdiscFocusLost(evt);
            }
        });
        tdisc.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                tdiscMouseClicked(evt);
            }
        });
        tdisc.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tdiscKeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tdiscKeyTyped(evt);
            }
        });
        jLayeredPane1.add(tdisc, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 100, 180, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Ppn (%)");
        jLayeredPane1.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 160, 90, 20));

        ttotalsub.setEditable(false);
        ttotalsub.setBackground(new java.awt.Color(153, 153, 255));
        ttotalsub.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(ttotalsub, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 130, 180, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Supplier");
        jLayeredPane1.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(850, 130, 90, 20));

        tsupplier.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tsupplierFocusLost(evt);
            }
        });
        tsupplier.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tsupplierKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tsupplier, new org.netbeans.lib.awtextra.AbsoluteConstraints(920, 130, 80, -1));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(1000, 130, 30, -1));

        tsupplier2.setEditable(false);
        tsupplier2.setBackground(new java.awt.Color(153, 153, 255));
        tsupplier2.setForeground(new java.awt.Color(255, 255, 255));
        jLayeredPane1.add(tsupplier2, new org.netbeans.lib.awtextra.AbsoluteConstraints(1040, 130, 210, -1));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Jatuh Tempo Payment");
        jLayeredPane1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 100, 130, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("No PO");
        jLayeredPane1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 130, 150, 20));

        tbukti_po.setEditable(false);
        tbukti_po.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbukti_poFocusLost(evt);
            }
        });
        tbukti_po.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbukti_poKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tbukti_po, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 130, 300, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jLayeredPane1.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(440, 130, 30, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Keterangan");
        jLayeredPane1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 190, 130, -1));

        jDateTranTempo.setDateFormatString("dd/MM/yyyy");
        jDateTranTempo.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateTranTempoPropertyChange(evt);
            }
        });
        jDateTranTempo.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateTranTempoKeyPressed(evt);
            }
        });
        jLayeredPane1.add(jDateTranTempo, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 100, 180, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("No. Inv/SJ Vendor");
        jLabel6.setToolTipText("");
        jLayeredPane1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 160, 120, 20));

        tbukti_inv.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti_inv.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbukti_invFocusLost(evt);
            }
        });
        jLayeredPane1.add(tbukti_inv, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 160, 330, -1));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Total");
        jLayeredPane1.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 130, 90, 20));

        tppn.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tppnCaretUpdate(evt);
            }
        });
        tppn.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tppnFocusLost(evt);
            }
        });
        tppn.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tppnKeyPressed(evt);
            }
        });
        jLayeredPane1.add(tppn, new org.netbeans.lib.awtextra.AbsoluteConstraints(650, 160, 110, -1));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Disc");
        jLayeredPane1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 100, 90, 20));

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

        infoTotal.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        infoTotal.setText("Total Transaksi : Rp. 1.000.000.000,-");
        infoTotal.setToolTipText("");

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Product", "Group", "Item", "Qty", "Price", "Disc", "Total", "Ppn", "Total All", "Satuan ", "Satuan_nilai", "Satuan_nilaikecil"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false
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
            jTable1.getColumnModel().getColumn(0).setMinWidth(40);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(40);
        }

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jLayeredPane1)
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel5Layout.createSequentialGroup()
                        .addComponent(infoTotal)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                        .addComponent(btDelete, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(btSave, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(btCancel, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jScrollPane1))
                .addContainerGap())
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jLayeredPane1, javax.swing.GroupLayout.PREFERRED_SIZE, 281, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 186, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
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
                .addGap(4, 4, 4)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 44, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode()==10)  loadData();
    }//GEN-LAST:event_tbuktisKeyPressed

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

    private void tketsKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketsKeyPressed
        if(evt.getKeyCode()==10)  loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_tketsKeyPressed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

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
            "from TranPurchaseOrderBillingD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.cnbkt = '"+bkt+"'";
            rfa.clear_table(MenuAdmin.keyssDef, jTable3);
            DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{
                        rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameProduct"),
                        rs.getDouble("nqty"),rs.getDouble("nprice"),rs.getDouble("nqty")*rs.getDouble("nprice"),
                        rs.getDouble("ndisc"),rs.getDouble("ntotal"),rs.getString("nunit")
                    });
                }
            } catch (SQLException ex) {
                Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
            }

        }
    }//GEN-LAST:event_jTable2MouseClicked

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        rfa.EnterNextFocus(evt, tproduk);
    }//GEN-LAST:event_tketKeyPressed

    private void tketKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyTyped

    }//GEN-LAST:event_tketKeyTyped

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        if(con.akses(akses, rfa.PRINT)) print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void tprodukFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tprodukFocusLost
        sql = "select * from mproduk where cproduct = '"+tproduk.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tprodukgroup.setText(rs.getString("cgroup"));
            tproduk2.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tprodukFocusLost

    private void tprodukKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tprodukKeyPressed
        rfa.EnterNextFocus(evt, tqty);
    }//GEN-LAST:event_tprodukKeyPressed

    private void tqtyCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tqtyCaretUpdate
        hitungTotalIn();
    }//GEN-LAST:event_tqtyCaretUpdate

    private void tqtyKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyPressed
        rfa.EnterNextFocus(evt, tharga);
    }//GEN-LAST:event_tqtyKeyPressed

    private void tqtyKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyTyped
        rfa.filterAngka(keyssDef, evt);
    }//GEN-LAST:event_tqtyKeyTyped

    private void thargaCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_thargaCaretUpdate
        hitungTotalIn();
    }//GEN-LAST:event_thargaCaretUpdate

    private void thargaMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_thargaMouseClicked

    }//GEN-LAST:event_thargaMouseClicked

    private void thargaKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_thargaKeyPressed
        rfa.EnterNextFocus(evt, tdisc);
    }//GEN-LAST:event_thargaKeyPressed

    private void thargaKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_thargaKeyTyped
        //rfa.filterAngka(keyssDef, evt);
    }//GEN-LAST:event_thargaKeyTyped

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogProduk a = new DialogProduk(2);
        a.setVisible(true);
        if(a.status){
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tproduk2.setText(a.namaBarang);
            tsatuan.setText(a.unit);
            tsatuanhigh.setText(rfa.formatDecimal3.format(a.unitbig_value));
            tsatuanlow.setText(rfa.formatDecimal3.format(a.unitsmall_value));
            
            tqty.requestFocus();
        }
    }//GEN-LAST:event_jButton10ActionPerformed

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
        
        DialogSatuan a = new DialogSatuan("");
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
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        if(btSave.getText().equals("Save")) LastCode();
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed

    }//GEN-LAST:event_jDateTranKeyPressed

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 3, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed
        if(evt.getKeyCode()==10) tket.requestFocus();
    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void tdiscCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tdiscCaretUpdate
        hitungTotalIn();
    }//GEN-LAST:event_tdiscCaretUpdate

    private void tdiscMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_tdiscMouseClicked
        // TODO add your handling code here:
    }//GEN-LAST:event_tdiscMouseClicked

    private void tdiscKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tdiscKeyPressed
        if(tsatuan.getText().isEmpty()) {
            rfa.EnterNextFocus(evt, btsatuan);
        } else {
            rfa.EnterNextFocus(evt, tppn);
        }
    }//GEN-LAST:event_tdiscKeyPressed

    private void tdiscKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tdiscKeyTyped
        //rfa.filterAngka(keyssDef, evt);
    }//GEN-LAST:event_tdiscKeyTyped

    private void tsupplierFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tsupplierFocusLost
        sql = "select * from msupplier where kode_supplier = '"+tsupplier.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tsupplier2.setText(rs.getString("cname"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tsupplierFocusLost

    private void tsupplierKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tsupplierKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tsupplierKeyPressed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogSupplier a = new DialogSupplier(1);
        a.setVisible(true);
        if(a.status){
            tsupplier.setText(a.kode);
            tsupplier2.setText(a.nama);
            loadByPO();
        }
    }//GEN-LAST:event_jButton11ActionPerformed

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
                cnbkt = tbukti.getText(); 
                cnbkt_print = tbukti.getText(); 
                if(con.akses(akses, rfa.UPDATE)) {
                    update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount()==2){
            deffBatal();
            int baris = jTable1.getSelectedRow();
            loadEditItem(baris);
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void tbukti_poFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti_poFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti_poFocusLost

    private void tbukti_poKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti_poKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti_poKeyPressed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogPurchaseOrder a = new DialogPurchaseOrder();  
        a.setVisible(true);
        
        if(a.status) {
            tbukti_po.setText(a.kode);
            loadByPO();
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        deleteRow = new ArrayList<>();
        push_button();
        clear();
        deff();
        loadData();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jDateTranTempoPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranTempoPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTranTempoPropertyChange

    private void jDateTranTempoKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranTempoKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTranTempoKeyPressed

    private void jTable2MouseEntered(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseEntered
        // TODO add your handling code here:
    }//GEN-LAST:event_jTable2MouseEntered

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

    private void tdiscFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tdiscFocusLost
        if(tdisc.getText().isEmpty()) {
            tdisc.setText("0");
        }
    }//GEN-LAST:event_tdiscFocusLost

    private void tppnCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tppnCaretUpdate
        hitungTotalIn();
    }//GEN-LAST:event_tppnCaretUpdate

    private void tppnFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tppnFocusLost
        if(tppn.getText().isEmpty()) tppn.setText("0");
    }//GEN-LAST:event_tppnFocusLost

    private void tppnKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tppnKeyPressed
        rfa.EnterNextFocus(evt, btAdd);        // TODO add your handling code here:
    }//GEN-LAST:event_tppnKeyPressed

    private void tbukti_invFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti_invFocusLost
        tbukti_inv.setText(rfa.toUpper(keyssDef, tbukti_inv.getText()));
    }//GEN-LAST:event_tbukti_invFocusLost


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAdd;
    private javax.swing.JButton btBatal;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton btsatuan;
    private javax.swing.JLabel infoTotal;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton7;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateTran;
    private com.toedter.calendar.JDateChooser jDateTranTempo;
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
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel22;
    private javax.swing.JLabel jLabel23;
    private javax.swing.JLabel jLabel24;
    private javax.swing.JLabel jLabel25;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JPanel jPanel2;
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
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbukti_inv;
    private javax.swing.JTextField tbukti_po;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tdisc;
    private javax.swing.JTextField tharga;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tppn;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tproduk2;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tqty;
    private javax.swing.JTextField tsatuan;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    private javax.swing.JTextField tsupplier;
    private javax.swing.JTextField tsupplier2;
    private javax.swing.JTextField ttotal;
    private javax.swing.JTextField ttotalsub;
    // End of variables declaration//GEN-END:variables
}
