/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Purchase;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogPayment;
import DialogPublicTP.DialogPurchaseOrder;
import DialogPublicTP.DialogPurchaseOrderBilling;
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
public class PaymentPurchase extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="PURCHASE PAYMENT";
    String idKas="O2";
    ArrayList<String> deleteRow;
    public PaymentPurchase(String mode) {
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

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F9, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                 System.out.println("Print "+jLabel2.getText());
            }
        });
    }

     private void deff(){
        jTable1.getTableHeader().setEnabled(false);
        btDelete.setVisible(false);
        btBatal.setVisible(false);
        btHapus.setVisible(false);
        jLayeredPane2.setVisible(false);
        btSave.setText("Save");
        jTabbedPane1.setTitleAt(1, "Add");
        jDateTran.setEnabled(true);
        id = "";
        jDateTran.setDate(new Date());
        jDate2.setDate(new Date());
        jDate1.setDate(new Date());
        jDatePay.setDate(new Date());
        
        
        tcodes.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        
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
        rfa.empty(MenuAdmin.keyssDef,tket,tmetode,tbank,tbankan,tbankrek,tnominal);
        rfa.empty(keyssDef, tpembelian,tsupplier,ttanggalpo,ttotalall,tduedate,tbukti_po,tbukti_bill,tbukti_inv,ttotaltagihan,ttanggaltagihan,tTotalBayar);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        deleteRow.clear();
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        barisEdit = -1;
        rfa.empty(MenuAdmin.keyssDef,tket,tmetode,tbank,tbankan,tbankrek,tnominal);
        combokurs.setSelectedIndex(0);
        tacc.requestFocus();
        btAdd.setText("Add");
    }
    
    
    private void LastCode(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranPayment where  ccode='"+tcode.getText()+"' and cnbkt like '%"+"PAY/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/"+Login.subdefault+"-")+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        tbukti.setText("PAY/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yyMM/")+Login.subdefault+"-");
        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
    } 
    private void loadData(){
        sql = "select a.id,a.cnbkt,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cdesc,\n" +
            "b.cname as gudang,sum(c.ntotal) as total \n" +
            "from TranPurchaseReq a join MGudang b on a.ccode=b.ccode\n" +
            "join TranPurchaseReq_detail c on a.cnbkt=c.cnbkt\n" +
            "where (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
            "and a.cdesc like '%"+tbuktipos.getText()+"%' "
                + "group by a.id,a.cnbkt,a.ccode,a.ccodeDest,a.cyear,a.dtran,a.cdesc,b.cname\n";
        sql="select a.id,a.cnbkt,a.cnbkt_PO,a.dtran,sum(b.namnt) as totalbayar,c.dtran as dtranpo,c.total,c.cname as supplier,c.cdue_date,\n" +
            "case when getdate()>c.cdue_date then 'LEWAT JATUH TEMPO'\n" +
            "	else '' end as statustempo,\n" +
            "case when sum(b.namnt)>=c.total then 'Lunas'\n" +
            "	else 'Belum Lunas'\n" +
            "	end as statuspay \n" +
            "from TranPayment a join TranPayment_detail b on a.id=b.id_cnbkt and a.cnbkt=b.cnbkt\n" +
            "join (\n" +
            "	select a.cnbkt,a.dtran,a.cdue_date,a.cdesc,sum(b.ntotal) as total,b.kode_supplier,c.cname,c.bank,c.bank_owner,c.bank_number\n" +
            "	from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt = b.cnbkt\n" +
            "	join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
            "	group by a.cnbkt,a.dtran,a.cdue_date,a.cdesc,b.kode_supplier,c.cname,c.bank,c.bank_owner,c.bank_number\n" +
            ") c on a.cnbkt_PO=c.cnbkt\n" +
            "where a.ccode='"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran<='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
            "and a.cnbkt like '%"+tbuktis.getText()+"%' \n" +
            "and a.cnbkt_PO like '%"+tbuktipos.getText()+"%'\n" +
            "group by a.id,a.cnbkt,a.cnbkt_PO,a.dtran,c.dtran,c.total,c.cname,c.cdue_date";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cnbkt"),rs.getString("cnbkt_PO"),
                    rfa.DateString(keyssDef, rs.getDate("dtranpo"),"dd/MM/yyyy"),
                    rs.getString("supplier"),rs.getDouble("total"),rs.getDouble("totalbayar"),
                    rfa.DateString(keyssDef, rs.getDate("cdue_date"),"dd/MM/yyyy"),
                    rs.getString("statustempo"),rs.getString("statuspay"),
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
        sql = "select * from TranPayment where id='"+id+"'  ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                tbukti.setText(rs.getString("cnbkt").substring(0, rs.getString("cnbkt").length()-4));
                tbukti1.setText(rs.getString("cnbkt").substring(rs.getString("cnbkt").length()-4, rs.getString("cnbkt").length()));
                jDateTran.setDate(rs.getDate("dtran"));
                tbukti_po.setText(rs.getString("cnbkt_PO"));
                tbukti_bill.setText(rs.getString("cnbkt_bill"));
                tbukti_inv.setText(rs.getString("cnbkt_inv"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        loadPOData();
        jDateTran.setEnabled(false);
        
        String bkt = cnbkt;
        sql="select a.* \n" +
        "from TranPayment_detail a \n" +
        "where a.cnbkt = '"+bkt+"'";
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                rs.getString("id"),rs.getString("cdesc"),rs.getString("cacckas"),rs.getString("caccll"),
                rfa.DateString(keyssDef,rs.getDate("dtran"),"yyyy-MM-dd"),
                rs.getString("curs"),rs.getString("method"),
                rs.getDouble("namnt"),
                rs.getString("cbank"),rs.getString("cbank_an"),rs.getString("cbank_rekening"),
                rs.getString("cusername"),rs.getString("cusername_update"),rfa.DateString(keyssDef,rs.getDate("dupdat"),"yyyy-MM-dd"),
            });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        hitungTotalTransaksi();
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTable1.getValueAt(baris, 0).toString();
        
        if(jTable1.getValueAt(baris,1)!=null) tket.setText(jTable1.getValueAt(baris, 1).toString());
        if(jTable1.getValueAt(baris,2)!=null) tacc.setText(jTable1.getValueAt(baris, 2).toString());
        if(jTable1.getValueAt(baris,3)!=null) taccll.setText(jTable1.getValueAt(baris, 3).toString());
        if(jTable1.getValueAt(baris,4)!=null) jDatePay.setDate(rfa.StringtoDate(keyssDef,jTable1.getValueAt(baris, 4).toString(),"yyyy-MM-dd"));
        if(jTable1.getValueAt(baris,5)!=null) combokurs.setSelectedItem(jTable1.getValueAt(baris, 5));
        if(jTable1.getValueAt(baris,6)!=null) tmetode.setText(jTable1.getValueAt(baris, 6).toString());
        if(jTable1.getValueAt(baris,7)!=null) tnominal.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris, 7).toString())));
        if(jTable1.getValueAt(baris,8)!=null) tbank.setText(jTable1.getValueAt(baris, 8).toString());
        if(jTable1.getValueAt(baris,9)!=null) tbankan.setText(jTable1.getValueAt(baris, 9).toString());
        if(jTable1.getValueAt(baris,10)!=null) tbankrek.setText(jTable1.getValueAt(baris,10).toString());
    
        tacc.requestFocus();
        taccll.requestFocus();
        tmetode.requestFocus();
        tacc.requestFocus();
        
        btAdd.setText("Ubah");
        btBatal.setVisible(true);
        btHapus.setVisible(true);
    }
    private void loadPOData(){
        rfa.empty(keyssDef, tpembelian,tsupplier,ttanggalpo,ttotalall,tduedate,ttotaltagihan);
        sql="select a.cnbkt,d.cnbkt as cnbktbill,a.dtran,a.cdesc,sum(b.ntotal)+a.nppn_nilai as total,b.kode_supplier,c.cname,c.bank,c.bank_owner,c.bank_number,\n" +
            "d.ntotal as totaltagihan,d.dtran as dtrantagihan,d.cdue_date\n" +
            "from TranPurchaseOrder a join TranPurchaseOrder_detail b on a.cnbkt = b.cnbkt\n" +
            "join MSupplier c on b.kode_supplier=c.kode_supplier\n" +
            "join (\n" +
            "	select a.ccode,a.cnbkt,a.cnbkt_po,a.cnbkt_inv,a.dtran,a.cdue_date,sum(b.ntotalall) as ntotal from TranPurchaseOrderBilling a\n" +
            "	join TranPurchaseOrderBillingD b on a.cnbkt=b.cnbkt and a.ccode = b.ccode\n" +
            "	group by a.ccode,a.cnbkt,a.cnbkt_po,a.cnbkt_inv,a.dtran,a.cdue_date\n" +
            ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt_po\n" +
            "where a.cnbkt = '"+tbukti_po.getText()+"'  and d.cnbkt = '"+tbukti_bill.getText()+"' \n" +
            "group by a.cnbkt,d.cnbkt,a.dtran,d.cdue_date,a.cdesc,b.kode_supplier,c.cname,c.bank,c.bank_owner,c.bank_number,d.dtran,d.ntotal,a.nppn_nilai";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                tpembelian.setText(rs.getString("cdesc"));
                tsupplier.setText(rs.getString("cname"));
                ttanggalpo.setText(rfa.DateString(keyssDef,rs.getDate("dtran"),"dd MMMM yyyy"));
                ttotalall.setText(rfa.formatDecimal2.format(rs.getDouble("total")));
                
                
                ttanggaltagihan.setText(rfa.DateString(keyssDef,rs.getDate("dtrantagihan"),"dd MMMM yyyy"));
                ttotaltagihan.setText(rfa.formatDecimal2.format(rs.getDouble("totaltagihan")));
                
                if(rs.getDate("cdue_date") != null)  tduedate.setText(rfa.DateString(keyssDef,rs.getDate("cdue_date"),"dd MMMM yyyy"));
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(PaymentPurchase.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    private Boolean cekSave(){
        if(tacc1.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Akun Debet wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(taccll1.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Akun Kredit wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tket.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Keterangan wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tmetode.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Produk wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
            return false;
        }
        if(tnominal.getText().equals("")) {
            JOptionPane.showMessageDialog(this,"Quantity wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
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
                -1,tket.getText(),tacc.getText(),taccll.getText(),
                rfa.DateString(keyssDef,jDatePay.getDate(),"yyyy-MM-dd"),
                combokurs.getSelectedItem().toString(),tmetode.getText(),
                Double.valueOf(tnominal.getText()),
                tbank.getText(),tbankan.getText(),tbankrek.getText(),
                Login.user,null,null,
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(iddetail, barisEdit, col++);
            jTable1.setValueAt(tket.getText(), barisEdit, col++);
            jTable1.setValueAt(tacc.getText(), barisEdit, col++);
            jTable1.setValueAt(taccll.getText(), barisEdit, col++);
            jTable1.setValueAt(rfa.DateString(keyssDef,jDatePay.getDate(),"yyyy-MM-dd"), barisEdit, col++);
            jTable1.setValueAt(combokurs.getSelectedItem().toString(), barisEdit, col++);
            jTable1.setValueAt(tmetode.getText(), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tnominal.getText()), barisEdit, col++);
            jTable1.setValueAt(tbank.getText(), barisEdit, col++);
            jTable1.setValueAt(tbankan.getText(), barisEdit, col++);
            jTable1.setValueAt(tbankrek.getText(), barisEdit, col++);
            col++;
            if(!iddetail.equals("-1")) {
                jTable1.setValueAt(Login.user, barisEdit, col++);
                jTable1.setValueAt(rfa.DateString(keyssDef,new Date(),"yyyy-MM-dd"), barisEdit, col++);
            }
           
            
        }
        hitungTotalTransaksi();
        deffBatal();
        
    }
    private void hitungTotalTransaksi(){
        Double totalNilai=0.0;
        for(int i=0; i<jTable1.getRowCount(); i++){
            totalNilai+=Double.valueOf(jTable1.getValueAt(i, 7).toString());
        }
        tTotalBayar.setText(rfa.formatDecimal2.format(totalNilai));
        jLayeredPane2.setVisible(false);
        try{
            if(totalNilai>=Double.valueOf(ttotaltagihan.getText().replaceAll(",", ""))) {
                jLayeredPane2.setVisible(true);
            }
        } catch (Exception e){}
        if(jTable1.getRowCount()>0){
//            jButton12.setEnabled(false);
        }
    }
    private void hitungTotalIn(){
    }
    
    private int insertData(){
        int err= 0;
        for(int i=0; i<jTable1.getRowCount();i++){
            if("-1".equalsIgnoreCase(jTable1.getValueAt(i, 0).toString())) {
                //INSERT
                sql="INSERT INTO TranPayment_detail (id_cnbkt,cnbkt,ccode,cdesc,dtran\n" +
                    ",cacckas,caccll\n" +
                    ",curs,method\n" +
                    ",namnt,cbank,cbank_an,cbank_rekening\n" +
                    ",fstatus,cusername)\n" 
                    + "VALUES\n" 
                    + "('"+id+"','"+tbukti.getText()+tbukti1.getText()+"','"+tcode.getText()+"','"+jTable1.getValueAt(i, 1)+"','"+jTable1.getValueAt(i, 4)+"',"
                        + "'"+jTable1.getValueAt(i, 2)+"','"+jTable1.getValueAt(i,3)+"',"
                        + "'"+jTable1.getValueAt(i, 5)+"','"+jTable1.getValueAt(i,6)+"',"
                        + "'"+jTable1.getValueAt(i, 7)+"','"+jTable1.getValueAt(i, 8)+"','"+jTable1.getValueAt(i,9)+"','"+jTable1.getValueAt(i,10)+"',"
                        + "'1','"+jTable1.getValueAt(i,11)+"')";
            } else {  
                //UPDATE
                sql="update TranPayment_detail set id_cnbkt='"+id+"',cnbkt='"+tbukti.getText()+tbukti1.getText()+"',ccode='"+tcode.getText()+"',cdesc='"+jTable1.getValueAt(i, 1)+"',dtran='"+jTable1.getValueAt(i, 4)+"'\n" +
                    ",cacckas='"+jTable1.getValueAt(i, 2)+"',caccll='"+jTable1.getValueAt(i,3)+"'\n" +
                    ",curs='"+jTable1.getValueAt(i, 5)+"',method='"+jTable1.getValueAt(i,6)+"'\n" +
                    ",namnt='"+jTable1.getValueAt(i, 7)+"',cbank='"+jTable1.getValueAt(i, 8)+"',cbank_an='"+jTable1.getValueAt(i,9)+"',cbank_rekening='"+jTable1.getValueAt(i,10)+"'\n" +
                    ",fstatus=1,cusername_update='"+jTable1.getValueAt(i,12)+"',dupdat='"+jTable1.getValueAt(i,13)+"'\n" 
                    + " where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                
//                sql="update TranPurchaseReq_detail set cnbkt='"+tbukti.getText()+tbukti1.getText()+"',ccode='"+tcode.getText()+"',cproduct='"+jTable1.getValueAt(i,1).toString()+"',cgroup='"+jTable1.getValueAt(i, 2).toString()+"',"
//                        + "nqty='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 3))+"',nprice='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"',ndisc='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 5))+"',ntotal='"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 6))+"',nunit='"+jTable1.getValueAt(i, 7).toString()+"',"
//                        + "fstatus='1',cusername='"+Login.user+"',kode_supplier='"+jTable1.getValueAt(i,10).toString()+"' \n" 
//                        + " where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
            
            }
            
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    private void save(){
        cnbkt_print = tbukti.getText()+tbukti1.getText();
        sql="insert into TranPayment (cnbkt,cnbkt_PO,ccode,cyear,dtran,fstatus,cusername,cnbkt_bill,cnbkt_inv) values "
                + " ('"+tbukti.getText()+tbukti1.getText()+"','"+tbukti_po.getText()+"','"+tcode.getText()+"',"
                + "'"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy")+"',"
                + "'"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"',"
                + "'1','"+Login.user+"',"
                + "'"+tbukti_bill.getText()+"',"
                + "'"+tbukti_inv.getText()+"')";
        if(con.queryUpdate(sql)>0) {
            sql="select id from TranPayment where cnbkt='"+tbukti.getText()+tbukti1.getText()+"' and cnbkt_PO='"+tbukti_po.getText()+"' and cusername='"+Login.user+"' ";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    id = rs.getString("id");
                }
            } catch (SQLException ex) { ex.printStackTrace();}
            
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
        sql="update TranPayment set "
                + "cnbkt='"+tbukti.getText()+tbukti1.getText()+"',"
                + "cnbkt_PO='"+tbukti_po.getText()+"',"
                + "cnbkt_bill='"+tbukti_bill.getText()+"',"
                + "cnbkt_inv='"+tbukti_inv.getText()+"',"
                + "ccode='"+tcode.getText()+"',"
                + "cyear='"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy")+"',"
                + "dtran='"+rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd")+"',"
                + "fstatus='1',"
                + "cusername='"+Login.user+"'  "
                + "   where id='"+id+"' ";
        
        if(con.queryUpdate(sql)>0) {
            for(int i=0; i<deleteRow.size();i++){
                sql="delete from  TranPayment_detail  where id = '"+deleteRow.get(i)+"' ";
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
            sql="delete from  TranPayment_detail  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  TranPayment_detail  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                if(con.queryUpdate(sql)<=0){
                    err++;
                } 
            }
            
        }
        sql="delete from  TranPayment  where id = '"+id+"' ";
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
            JOptionPane.showMessageDialog(this,"Gagal Menghapus.", "Save", JOptionPane.ERROR_MESSAGE);
            
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
        
        sql="select a.cnbkt,a.ccode,cnbkt_PO,b.cnbktbill,b.dtran as tglpo,cdue_date,total,totalbayar,kode_supplier,cname as supplier from TranPayment a \n" +
            "join (\n" +
            "	select a.ccode,a.cnbkt as cnbktbill,bb.cnbkt as cnbkt,a.dtran,a.cdue_date,a.cdesc,sum(b.ntotalall) as total,bb.kode_supplier,c.cname,c.bank,c.bank_owner,c.bank_number \n" +
            "	from TranPurchaseOrderBilling a join TranPurchaseOrderBillingD b on a.cnbkt = b.cnbkt \n" +
            "	join (select distinct a.ccode,a.cnbkt,b.kode_supplier From TranPurchaseOrder a join TranPurchaseOrder_detail b on a.ccode=b.ccode and a.cnbkt=b.cnbkt) bb on a.ccode=bb.ccode and a.cnbkt_po=bb.cnbkt\n" +
            "	join MSupplier c on bb.kode_supplier=c.kode_supplier \n" +
            "	--where a.cnbkt = 'tbukti_po.getText()' \n" +
            "	group by a.ccode,a.cnbkt,a.dtran,a.cdue_date,a.cdesc,bb.kode_supplier,c.cname,c.bank,c.bank_owner,c.bank_number,bb.cnbkt\n" +
            ") b on a.cnbkt_PO=b.cnbkt and a.cnbkt_bill=b.cnbktbill and a.ccode=b.ccode\n" +
            "join (\n" +
            "	select cnbkt,ccode,sum(namnt) as totalbayar from TranPayment_detail group by cnbkt,ccode\n" +
            ") c on a.ccode=c.ccode and a.cnbkt=c.cnbkt\n" +
            "where a.ccode = '"+tcode.getText()+"' and a.cnbkt = '"+cnbkt_print+"' ";
        System.out.println(sql);
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                param.put("pt", deffProgram.get("pt"));
                param.put("alamat", deffProgram.get("alamat"));
                param.put("telp", "Telp. "+deffProgram.get("telp"));
                param.put("mail", "Email : "+deffProgram.get("mail"));
                param.put("bukti",rs.getString("cnbkt"));
                param.put("supplier",rs.getString("kode_supplier")+" - "+rs.getString("supplier"));
                param.put("tanggalpo",rfa.DateString(keyssDef,rs.getDate("tglpo"),"dd/MM/yyyy"));
                param.put("buktipo",rs.getString("cnbkt_PO"));
                param.put("buktitagihan",rs.getString("cnbktbill"));
                param.put("jatuhtempo",rfa.DateString(keyssDef,rs.getDate("cdue_date"),"dd/MM/yyyy"));
                param.put("total",rfa.formatDecimal2.format(rs.getDouble("total")));
                param.put("totalbayar",rfa.formatDecimal2.format(rs.getDouble("totalbayar")));
                param.put("sisa",rfa.formatDecimal2.format(rs.getDouble("total")-rs.getDouble("totalbayar")));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        sql="select * from TranPayment_detail\n" +
            "where ccode='"+tcode.getText()+"' and cnbkt='"+cnbkt_print+"'";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print);
        DefaultTableModel model = (DefaultTableModel) jtable_print.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    nomor++,
                    rs.getString("cdesc"),
                    rs.getString("method"),
                    rs.getString("curs"),
                    rs.getString("cbank"),
                    rs.getString("cbank_an"),
                    rs.getString("cbank_rekening"),
                    rs.getDouble("namnt"),
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        //ITEM TAGIHAN 
        sql = "select a.cnbkt,a.ccode,a.dtran,a.cdesc,a.nik,a.nik_cname,\n" +
                "b.cgroup,b.cproduct,c.cnameProduct,b.nqty,b.nprice,b.ndisc,b.ntotal,b.ppn,b.ntotalall,b.nunit\n" +
                "from TranPurchaseOrderBilling a join TranPurchaseOrderBillingD b on a.cnbkt=b.cnbkt\n" +
                "join MProduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
                "where a.ccode='"+tcode.getText()+"' and a.cnbkt = '"+tbukti_bill.getText()+"'\n" +
                "order by b.cgroup,b.cproduct";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jtable_print_item);
        DefaultTableModel model1 = (DefaultTableModel) jtable_print_item.getModel();
        int nomorr=1;
        try {
            while(rs.next()){
                pengaju=rs.getString("nik_cname")+" ("+rs.getString("nik")+")";
                model1.addRow(new Object[]{
                    nomorr++,
                    "",
                    "",
                    "","",
                    rs.getDouble("nqty"),rs.getDouble("nprice"),
                    rs.getDouble("ndisc"),rs.getDouble("ntotal"),
                    rs.getString("cnameProduct"),
                    rs.getString("nunit"),
                    rs.getDouble("ppn"),rs.getDouble("ntotalall"),
                });
                 
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jtable_print.getModel());
            String treport = "report/TranPaymentPO.jrxml";
             
            param.put("itempo", new JRTableModelDataSource(jtable_print_item.getModel()));
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
        jScrollPane6 = new javax.swing.JScrollPane();
        jtable_print_item = new javax.swing.JTable();
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
        tbuktipos = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jLabel28 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel6 = new javax.swing.JLabel();
        jLabel7 = new javax.swing.JLabel();
        tket = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        tbukti_po = new javax.swing.JTextField();
        jButton9 = new javax.swing.JButton();
        jPanel3 = new javax.swing.JPanel();
        jLabel16 = new javax.swing.JLabel();
        tpembelian = new javax.swing.JTextField();
        jLabel17 = new javax.swing.JLabel();
        ttotalall = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        tduedate = new javax.swing.JTextField();
        jLabel20 = new javax.swing.JLabel();
        tTotalBayar = new javax.swing.JTextField();
        jLabel21 = new javax.swing.JLabel();
        tsupplier = new javax.swing.JTextField();
        jLabel22 = new javax.swing.JLabel();
        ttanggalpo = new javax.swing.JTextField();
        jLabel31 = new javax.swing.JLabel();
        ttotaltagihan = new javax.swing.JTextField();
        jLabel32 = new javax.swing.JLabel();
        ttanggaltagihan = new javax.swing.JTextField();
        jLabel18 = new javax.swing.JLabel();
        tmetode = new javax.swing.JTextField();
        jLabel24 = new javax.swing.JLabel();
        tnominal = new javax.swing.JTextField();
        combokurs = new javax.swing.JComboBox();
        jLabel25 = new javax.swing.JLabel();
        tbank = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        jLabel26 = new javax.swing.JLabel();
        tbankrek = new javax.swing.JTextField();
        tbankan = new javax.swing.JTextField();
        jLabel27 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jLabel4 = new javax.swing.JLabel();
        jDateTran = new com.toedter.calendar.JDateChooser();
        jLabel5 = new javax.swing.JLabel();
        tbukti = new javax.swing.JTextField();
        tbukti1 = new javax.swing.JTextField();
        btPrint = new javax.swing.JButton();
        tacc = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        tacc1 = new javax.swing.JTextField();
        taccll1 = new javax.swing.JTextField();
        jButton6 = new javax.swing.JButton();
        taccll = new javax.swing.JTextField();
        btHapus = new javax.swing.JButton();
        btBatal = new javax.swing.JButton();
        btAdd = new javax.swing.JButton();
        jLabel10 = new javax.swing.JLabel();
        jDatePay = new com.toedter.calendar.JDateChooser();
        jLabel23 = new javax.swing.JLabel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLayeredPane2 = new javax.swing.JLayeredPane();
        jLabel11 = new javax.swing.JLabel();
        jLabel29 = new javax.swing.JLabel();
        tbukti_inv = new javax.swing.JTextField();
        jLabel30 = new javax.swing.JLabel();
        tbukti_bill = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();

        jtable_print.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "no", "desc", "method", "curs", "bank", "bankan", "bankrek", "namnt"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane5.setViewportView(jtable_print);

        jtable_print_item.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "no", "cnbkt", "dtran", "kode_supplier", "nama_supplier", "qty", "harga", "disc", "total", "desc", "satuan", "ppn", "totalall"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane6.setViewportView(jtable_print_item);

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("Payment");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(500, 30, -1, 20));

        jLabel12.setText("No Payment");
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

        tbuktipos.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbuktiposKeyPressed(evt);
            }
        });
        jPanel6.add(tbuktipos, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 60, 140, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("No PO");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));

        jLabel28.setText("Gudang");
        jPanel6.add(jLabel28, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

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
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "No Payment", "No PO", "Tanggal PO", "Supplier", "Total ", "Total Dibayarkan", "Jatuh Tempo", "Status ", "Status Pay"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
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
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1270, Short.MAX_VALUE)
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
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 301, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Account Debit");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 100, 90, 20));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Account Kredit");
        jPanel1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 130, 110, 20));

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
        jPanel1.add(tket, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 190, 270, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("No. Invoice Vendor");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 220, 110, 20));

        tbukti_po.setEditable(false);
        tbukti_po.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jPanel1.add(tbukti_po, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 160, 200, -1));

        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 160, 30, -1));

        jPanel3.setBorder(javax.swing.BorderFactory.createLineBorder(new java.awt.Color(0, 0, 0)));
        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Pembelian");
        jPanel3.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 90, 20));

        tpembelian.setEditable(false);
        tpembelian.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(tpembelian, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 10, 180, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Total Keseluruhan PO");
        jPanel3.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, 140, 20));

        ttotalall.setEditable(false);
        ttotalall.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(ttotalall, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 70, 180, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Jatuh Tempo");
        jPanel3.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 160, 90, 20));

        tduedate.setEditable(false);
        tduedate.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(tduedate, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 160, 180, -1));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Total Ditagihkan");
        jPanel3.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 120, 110, 20));

        tTotalBayar.setEditable(false);
        tTotalBayar.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(tTotalBayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 180, -1));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Tgl PO");
        jPanel3.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 50, 90, 20));

        tsupplier.setEditable(false);
        tsupplier.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(tsupplier, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 30, 180, -1));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Kepada");
        jPanel3.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 30, 90, 20));

        ttanggalpo.setEditable(false);
        ttanggalpo.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(ttanggalpo, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 180, -1));

        jLabel31.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel31.setText("Total Dibayarkan");
        jPanel3.add(jLabel31, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 140, 110, 20));

        ttotaltagihan.setEditable(false);
        ttotaltagihan.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(ttotaltagihan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 120, 180, -1));

        jLabel32.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel32.setText("Tgl Tagihan");
        jPanel3.add(jLabel32, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 100, 100, 20));

        ttanggaltagihan.setEditable(false);
        ttanggaltagihan.setBackground(new java.awt.Color(153, 255, 153));
        jPanel3.add(ttanggaltagihan, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 100, 180, -1));

        jPanel1.add(jPanel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(930, 0, 340, 230));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Metode Bayar");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 40, 100, 20));

        tmetode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tmetodeFocusLost(evt);
            }
        });
        tmetode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tmetodeKeyPressed(evt);
            }
        });
        jPanel1.add(tmetode, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 40, 110, -1));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setText("Nominal dibayarkan");
        jLabel24.setToolTipText("");
        jPanel1.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 160, 120, 20));

        tnominal.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tnominalKeyPressed(evt);
            }
        });
        jPanel1.add(tnominal, new org.netbeans.lib.awtextra.AbsoluteConstraints(700, 160, 130, -1));

        combokurs.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "RP", "USD", "EUR", "YEN", "PAUND", "JPY", "AUD", "NZD" }));
        jPanel1.add(combokurs, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 160, 60, -1));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Bank");
        jLabel25.setToolTipText("");
        jPanel1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 70, 120, 20));

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
        jPanel1.add(tbank, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 70, 140, -1));

        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(750, 40, 30, -1));

        jLabel26.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel26.setText("No Rekening");
        jLabel26.setToolTipText("");
        jPanel1.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 130, 120, 20));

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
        jPanel1.add(tbankrek, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 130, 270, -1));

        tbankan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbankanFocusLost(evt);
            }
        });
        tbankan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbankanKeyPressed(evt);
            }
        });
        jPanel1.add(tbankan, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 100, 270, -1));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setText("A/N Rekening");
        jLabel27.setToolTipText("");
        jPanel1.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 100, 120, 20));

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Log");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 72, 20));

        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel1.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 10, 90, -1));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 10, 30, -1));

        tcode1.setEditable(false);
        tcode1.setBackground(new java.awt.Color(153, 153, 255));
        tcode1.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 10, 200, -1));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Header");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, 100, 20));

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
        jPanel1.add(jDateTran, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 40, 180, -1));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("No. Bukti");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, 72, 20));

        tbukti.setEditable(false);
        tbukti.setBackground(new java.awt.Color(153, 153, 255));
        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 70, 130, -1));

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
        jPanel1.add(tbukti1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 70, 50, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 70, 130, -1));

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
        jPanel1.add(tacc, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 100, 90, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 100, 30, -1));

        tacc1.setEditable(false);
        tacc1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(tacc1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 100, 200, -1));

        taccll1.setEditable(false);
        taccll1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel1.add(taccll1, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 130, 200, -1));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(230, 130, 30, -1));

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
        jPanel1.add(taccll, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 130, 90, -1));

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
        jPanel1.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(720, 220, 30, 25));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jPanel1.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(760, 220, 70, 25));

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
        jPanel1.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(830, 220, 80, 25));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Tgl. Pembayaran");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 10, 120, 20));

        jDatePay.setDateFormatString("dd/MM/yyyy");
        jDatePay.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDatePayPropertyChange(evt);
            }
        });
        jDatePay.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDatePayKeyPressed(evt);
            }
        });
        jPanel1.add(jDatePay, new org.netbeans.lib.awtextra.AbsoluteConstraints(640, 10, 190, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setText("Keterangan");
        jPanel1.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(510, 190, 72, 20));

        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());
        jPanel1.add(jLayeredPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(810, 20, -1, -1));

        jLayeredPane2.setBackground(new java.awt.Color(0, 255, 0));
        jLayeredPane2.setOpaque(true);
        jLayeredPane2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel11.setFont(new java.awt.Font("Tahoma", 1, 24)); // NOI18N
        jLabel11.setForeground(new java.awt.Color(255, 255, 255));
        jLabel11.setHorizontalAlignment(javax.swing.SwingConstants.CENTER);
        jLabel11.setText("LUNAS");
        jLabel11.setToolTipText("");
        jLayeredPane2.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 90, -1));

        jPanel1.add(jLayeredPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(800, 40, 110, 50));

        jLabel29.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel29.setText("No. PO");
        jPanel1.add(jLabel29, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 160, 110, 20));

        tbukti_inv.setEditable(false);
        tbukti_inv.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jPanel1.add(tbukti_inv, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 220, 330, -1));

        jLabel30.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel30.setText("No. Tagihan");
        jPanel1.add(jLabel30, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 190, 110, 20));

        tbukti_bill.setEditable(false);
        tbukti_bill.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jPanel1.add(tbukti_bill, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 190, 330, -1));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Keterangan", "Akun Debet", "Akun Kredit", "Tanggal", "Kurs", "Metode", "Nominal", "Bank", "Bank AN", "Bank Rekening", "Input By", "Update By", "Update Date"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false, false
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
        jTable1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jTable1KeyPressed(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(50);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(50);
            jTable1.getColumnModel().getColumn(1).setMinWidth(250);
            jTable1.getColumnModel().getColumn(1).setMaxWidth(250);
            jTable1.getColumnModel().getColumn(2).setMinWidth(120);
            jTable1.getColumnModel().getColumn(2).setMaxWidth(120);
            jTable1.getColumnModel().getColumn(3).setMinWidth(120);
            jTable1.getColumnModel().getColumn(3).setMaxWidth(120);
            jTable1.getColumnModel().getColumn(4).setMinWidth(120);
            jTable1.getColumnModel().getColumn(4).setMaxWidth(120);
            jTable1.getColumnModel().getColumn(5).setMinWidth(150);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(150);
            jTable1.getColumnModel().getColumn(6).setMinWidth(150);
            jTable1.getColumnModel().getColumn(6).setMaxWidth(150);
            jTable1.getColumnModel().getColumn(7).setMinWidth(150);
            jTable1.getColumnModel().getColumn(7).setMaxWidth(150);
            jTable1.getColumnModel().getColumn(8).setMinWidth(100);
            jTable1.getColumnModel().getColumn(8).setMaxWidth(100);
            jTable1.getColumnModel().getColumn(9).setMinWidth(200);
            jTable1.getColumnModel().getColumn(9).setMaxWidth(200);
            jTable1.getColumnModel().getColumn(10).setMinWidth(150);
            jTable1.getColumnModel().getColumn(10).setMaxWidth(150);
            jTable1.getColumnModel().getColumn(11).setMinWidth(200);
            jTable1.getColumnModel().getColumn(11).setMaxWidth(200);
            jTable1.getColumnModel().getColumn(12).setMinWidth(200);
            jTable1.getColumnModel().getColumn(12).setMaxWidth(200);
            jTable1.getColumnModel().getColumn(13).setMinWidth(200);
            jTable1.getColumnModel().getColumn(13).setMaxWidth(200);
        }

        btDelete.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelete.setText("Delete All");
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

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane1)
                    .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel5Layout.createSequentialGroup()
                        .addGap(0, 0, Short.MAX_VALUE)
                        .addComponent(btDelete, javax.swing.GroupLayout.PREFERRED_SIZE, 106, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(13, 13, 13)
                        .addComponent(btSave, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(13, 13, 13)
                        .addComponent(btCancel, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addContainerGap())
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 244, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 158, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(btDelete)
                    .addComponent(btSave)
                    .addComponent(btCancel))
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

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
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

    private void tbukti1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbukti1FocusLost
        int no = Integer.valueOf(tbukti1.getText());
        tbukti1.setText(rfa.NumberBukti(keyssDef, 4, no));
    }//GEN-LAST:event_tbukti1FocusLost

    private void tbukti1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyPressed
        if(evt.getKeyCode()==10) tket.requestFocus();
    }//GEN-LAST:event_tbukti1KeyPressed

    private void tbukti1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbukti1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tbukti1KeyTyped

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        if(con.akses(akses, rfa.PRINT)) print();
    }//GEN-LAST:event_btPrintActionPerformed

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

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogAccount a = new DialogAccount("'CL_ASET'");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            tacc.setText(a.caccnt);
            tacc1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogAccount a = new DialogAccount("'CL_BEBAN','CL_PROD','CL_HPP'");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            taccll.setText(a.caccnt);
            taccll1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void taccllFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccllFocusLost
        sql = "select * from maccount where caccnt = '"+taccll.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, taccll1);
            while(rs.next()){  taccll1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_taccllFocusLost

    private void taccllKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccllKeyPressed
        rfa.EnterNextFocus(evt, tket);
    }//GEN-LAST:event_taccllKeyPressed

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

    private void jDatePayPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDatePayPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDatePayPropertyChange

    private void jDatePayKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDatePayKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDatePayKeyPressed

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        rfa.EnterNextFocus(evt, tmetode);
    }//GEN-LAST:event_tketKeyPressed

    private void tmetodeKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tmetodeKeyPressed
        rfa.EnterNextFocus(evt, tbank);
    }//GEN-LAST:event_tmetodeKeyPressed

    private void tbankKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbankKeyPressed
        rfa.EnterNextFocus(evt, tbankan);
    }//GEN-LAST:event_tbankKeyPressed

    private void tbankrekKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbankrekKeyPressed
        rfa.EnterNextFocus(evt, tnominal);
    }//GEN-LAST:event_tbankrekKeyPressed

    private void tbankanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbankanKeyPressed
        rfa.EnterNextFocus(evt, tbankrek);
    }//GEN-LAST:event_tbankanKeyPressed

    private void tnominalKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tnominalKeyPressed
        rfa.EnterNextFocus(evt, btAdd);
    }//GEN-LAST:event_tnominalKeyPressed

    private void jTable1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTable1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTable1KeyPressed

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
                    save();
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
        deffBatal();
        clear();
        deff();
        loadData();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        DialogPurchaseOrderBilling a = new DialogPurchaseOrderBilling("");
        a.setVisible(true);
        if(a.status){
            Boolean statuss = false;
            sql="select * From TranPayment where ccode='"+tcode.getText()+"' and cnbkt_PO = '"+a.kode+"' and cnbkt_bill='"+a.kodetagihan+"' ";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    statuss = true;
                    id = rs.getString("id");
                    cnbkt = rs.getString("cnbkt");
                    cnbkt_print = cnbkt;
                    loadEdit();
                }
            } catch (SQLException ex) {
                Logger.getLogger(PaymentPurchase.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            if(!statuss) {
                tbukti_po.setText(a.kode);
                tbukti_bill.setText(a.kodetagihan);
                tbukti_inv.setText(a.kodeinv);
                loadPOData();
                tacc.requestFocus();
            }
            
        }
    }//GEN-LAST:event_jButton9ActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void tbuktisKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktisKeyPressed
        if(evt.getKeyCode()==10) loadData();        // TODO add your handling code here:
    }//GEN-LAST:event_tbuktisKeyPressed

    private void tbuktiposKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbuktiposKeyPressed
        if(evt.getKeyCode()==10) loadData();
    }//GEN-LAST:event_tbuktiposKeyPressed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable2.getSelectedRow();
            id = jTable2.getValueAt(baris, 0).toString();
            cnbkt = jTable2.getValueAt(baris, 1).toString();
            cnbkt_print = cnbkt;
            loadEdit();
        } 
    }//GEN-LAST:event_jTable2MouseClicked

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
       DialogPayment a = new DialogPayment();
       a.setVisible(true);
       if(a.status){
           tmetode.setText(a.payment);
       }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tketFocusLost

    private void tmetodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tmetodeFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tmetodeFocusLost

    private void tbankFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbankFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tbankFocusLost

    private void tbankanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbankanFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tbankanFocusLost

    private void tbankrekFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbankrekFocusLost
        rfa.FieldCapital((JTextField) evt.getComponent());
    }//GEN-LAST:event_tbankrekFocusLost

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
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JComboBox combokurs;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton9;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDatePay;
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
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel5;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JLayeredPane jLayeredPane2;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JPanel jPanel6;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTable jtable_print;
    private javax.swing.JTable jtable_print_item;
    private javax.swing.JTextField tTotalBayar;
    private javax.swing.JTextField tacc;
    private javax.swing.JTextField tacc1;
    private javax.swing.JTextField taccll;
    private javax.swing.JTextField taccll1;
    private javax.swing.JTextField tbank;
    private javax.swing.JTextField tbankan;
    private javax.swing.JTextField tbankrek;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbukti1;
    private javax.swing.JTextField tbukti_bill;
    private javax.swing.JTextField tbukti_inv;
    private javax.swing.JTextField tbukti_po;
    private javax.swing.JTextField tbuktipos;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tduedate;
    private javax.swing.JTextField tket;
    private javax.swing.JTextField tmetode;
    private javax.swing.JTextField tnominal;
    private javax.swing.JTextField tpembelian;
    private javax.swing.JTextField tsupplier;
    private javax.swing.JTextField ttanggalpo;
    private javax.swing.JTextField ttanggaltagihan;
    private javax.swing.JTextField ttotalall;
    private javax.swing.JTextField ttotaltagihan;
    // End of variables declaration//GEN-END:variables
}
