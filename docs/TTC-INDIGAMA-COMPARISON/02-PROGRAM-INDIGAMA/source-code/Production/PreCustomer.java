/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Production;

import Accounting.*;
import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogMembers;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogPurchaseOrder;
import DialogPublicTP.DialogSatuan;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Purchase.PaymentPurchase;
import Purchase.PurchaseOrder;
import Purchase.PurchaseRequest;
import RawMaterial.*;
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
public class PreCustomer extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="PRODUKSI PO CUSTOMER";
    String idKas="R1";
    String satuanterkecil;
    ArrayList<String> deleteRow;
    public PreCustomer(String mode) {
        initComponents();
        push_button();
        deleteRow = new ArrayList<>();
        clear();
        deff();
        deffBatal();
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
        jDateDeadline.setDate(new Date());
        
        
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
        rfa.empty(MenuAdmin.keyssDef,tket,tproduk,tprodukgroup,tproduknama,tqty,tsatuanhigh,tsatuanlow,
                tkaryawankode,tkaryawan,tcustomer,tcustomerkode);
        rfa.clear_table(MenuAdmin.keyssDef, jTable1);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        deleteRow.clear();
        tket1.setText("");
    }
    private void deffBatal(){
        btBatal.setVisible(false); 
        btHapus.setVisible(false);
        btAdd.setText("Add");
        barisEdit = -1;
        rfa.empty(MenuAdmin.keyssDef,tproduk,tprodukgroup,tproduknama,tqty,tsatuanhigh,tsatuanlow,
                tbahan,tbahangroup,tbahannama,tket);
        
    }
    
    
    private void LastCode(){
//        int last = 0;
//        rs = con.queryResult("select max(convert(int,left(cnbkt,3))) as lastno "
//                + " from PCustomerPO "
//                + " where  ccode='"+tcode.getText()+"' "
//                + " and month(dtran)= '"+rfa.DateString(keyssDef, jDateTran.getDate(),"MM")+"' ");
//        
//        try {
//            while(rs.next()){
//                last = rs.getInt("lastno");
//            }
//        } catch (SQLException ex) {
//            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
//        }
//        
//        tbukti.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1)+"/ORDER/UG-RMS/"+con.romawi(rfa.DateString(keyssDef, jDateTran.getDate(),"MM"))+"/"+rfa.DateString(keyssDef, jDateTran.getDate(),"yy"));
//        tbukti1.setText(rfa.NumberBukti(MenuAdmin.keyssDef, 3, last+1));
    } 
    private void loadData(){
        sql="select a.*,aa.cname as gudang,b.cname as customer,c.cname as marketing,d.jml from PCustomerPO a\n" +
                "join MGudang aa on a.ccode=aa.ccode\n" +
                "join MCustomer b on a.kode_customer=b.kode_customer\n" +
                "left join MKaryawan c on a.cmarketing=c.nik\n" +
                "join (\n" +
                "	select count(*) as jml,ccode,cnbkt from PCustomerPOD \n" +
                "	group by ccode,cnbkt\n" +
                ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt "+
                "where a.ccode='"+tcodes.getText()+"' and (a.dtran >='"+rfa.DateString(keyssDef, jDate1.getDate(), "yyyy-MM-dd")+"' and a.dtran <='"+rfa.DateString(keyssDef, jDate2.getDate(), "yyyy-MM-dd")+"')\n" +
                "and a.cnbkt like '%"+tbuktis.getText()+"%'\n" +
                "and b.cname like '%"+tkets.getText()+"%' "
                + " \n"
                + " order by a.cnbkt desc ";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable2);
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("id"),rs.getString("kode"),
                    rs.getString("gudang"),
                    rs.getString("cnbkt"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("customer"),
                    rs.getString("marketing"),
                    rs.getDouble("jml"),
                    
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
        sql = "select * from PCustomerPO where id='"+id+"'  ";
        rs = con.queryResult(sql);
        int no=0;
        try {
            while(rs.next()){
                
                no++;
                
//                tbukti1.setText(rs.getString("cnbkt").substring(0,3));
                tbukti.setText(rs.getString("cnbkt"));
                tcustomerkode.setText(rs.getString("kode_customer"));
                tkaryawankode.setText(rs.getString("cmarketing"));
                tkode.setText(rs.getString("kode"));
                tket1.setText(rs.getString("cnote"));
                jDateTran.setDate(rs.getDate("dtran")); 
                tproduk.requestFocus();
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        
        
        String bkt = cnbkt;
        sql="select a.*,b.cnameproduct \n" +
        "from PCustomerPOD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct  \n" +
        "where a.cnbkt = '"+bkt+"'  and a.ccode = '"+tcode.getText()+"'";
        rfa.clear_table(keyssDef, jTable1);
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                System.out.println(rs.getString("id"));
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameproduct"),
                    rs.getDouble("nqty"),rs.getString("nunit"),
                    rs.getString("cgroup_bahan"),rs.getString("cproduct_bahan"),
                    rfa.DateString(keyssDef, rs.getDate("deadline"), "yyyy-MM-dd"),
                    rs.getString("cket"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(PurchaseRequest.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        loadkaryawan();
        loadcustomer();
        tproduk.requestFocus();
        
        jTabbedPane1.setSelectedIndex(1);
        jTabbedPane1.setTitleAt(1, "Edit");
    }
    private void loadEditItem(int baris){
        barisEdit = baris;
        iddetail = jTable1.getValueAt(baris, 0).toString();
        
        if(jTable1.getValueAt(baris,2)!=null) tproduk.setText(jTable1.getValueAt(baris, 2).toString());
        if(jTable1.getValueAt(baris,4)!=null) tqty.setText(rfa.formatDecimal3.format(Double.valueOf(jTable1.getValueAt(baris, 4).toString())));
        if(jTable1.getValueAt(baris,5)!=null) listSatuan.setSelectedItem(jTable1.getValueAt(baris, 5).toString());
        if(jTable1.getValueAt(baris,7)!=null) tbahan.setText(jTable1.getValueAt(baris, 7).toString());
        if(jTable1.getValueAt(baris,8)!=null) {
            String tgl=(jTable1.getValueAt(baris, 8).toString());
            jDateDeadline.setDate(rfa.StringtoDate(keyssDef, tgl, "yyyy-MM-dd"));
        }
        if(jTable1.getValueAt(baris,9)!=null) tket.setText(jTable1.getValueAt(baris, 9).toString());
        
        tproduk.requestFocus();
        tbahan.requestFocus();
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
//        if(tbahan.getText().equals("")) {
//            JOptionPane.showMessageDialog(this,"Bahan Kertas wajib di isi", "Info", JOptionPane.ERROR_MESSAGE);
//            return false;
//        }
        if(tqty.getText().equals("")) {
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
                -1,
                tprodukgroup.getText(),tproduk.getText(),tproduknama.getText(),
                Double.valueOf(tqty.getText()),
                listSatuan.getSelectedItem().toString(),
                tbahangroup.getText(),tbahan.getText(),
                rfa.DateString(keyssDef,jDateDeadline.getDate(),"yyyy-MM-dd"),
                tket.getText()
            });
        } else { //Ubah
            int col=0;
            jTable1.setValueAt(iddetail, barisEdit, col++);
            jTable1.setValueAt(tprodukgroup.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduk.getText(), barisEdit, col++);
            jTable1.setValueAt(tproduknama.getText(), barisEdit, col++);
            jTable1.setValueAt(Double.valueOf(tqty.getText()), barisEdit, col++);
            jTable1.setValueAt(listSatuan.getSelectedItem().toString(), barisEdit, col++);
            jTable1.setValueAt(tbahangroup.getText(), barisEdit, col++);
            jTable1.setValueAt(tbahan.getText(), barisEdit, col++);
            jTable1.setValueAt(rfa.DateString(keyssDef,jDateDeadline.getDate(),"yyyy-MM-dd"), barisEdit, col++);
            jTable1.setValueAt(tket.getText(), barisEdit, col++);
        }
        deffBatal();
        
    }
    private void hitungTotalTransaksi(){
        Double totalNilai=0.0;
        for(int i=0; i<jTable1.getRowCount(); i++){
            totalNilai+=Double.valueOf(jTable1.getValueAt(i, 6).toString());
        }
//        infoTotal.setText("Total Transaksi : Rp. "+rfa.formatDecimal2.format(totalNilai));
//        if(jTable1.getRowCount()>0){
//            jButton12.setEnabled(false);
//        }
    }
    private void hitungTotalIn(){
        Double qty=0.0,harga=0.0,disc=0.0,totalsub=0.0,total=0.0,totalAll=0.0;
        if(!tqty.getText().isEmpty()) qty = Double.valueOf(tqty.getText());
        total = qty*harga;
        totalAll = total-disc;
    }
    private void loadcustomer(){
        sql = "select * from mcustomer where kode_customer = '"+tcustomerkode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tcustomer.setText(rs.getString("cname"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }
    private void loadkaryawan(){
        sql = "select * from mkaryawan where nik = '"+tkaryawankode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tkaryawan.setText(rs.getString("cname"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }
    
    private int insertData(){
        int err= 0;
        for(int i=0; i<jTable1.getRowCount();i++){
            HashMap<String,String> listObj = new HashMap<String,String>();        
            listObj.put("cnbkt",tbukti.getText());
            listObj.put("ccode",tcode.getText());
            listObj.put("cgroup",jTable1.getValueAt(i,1).toString());
            listObj.put("cproduct",jTable1.getValueAt(i,2).toString());
            listObj.put("nqty",rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
            listObj.put("nunit",jTable1.getValueAt(i,5).toString());
            listObj.put("cgroup_bahan",jTable1.getValueAt(i,6).toString());
            listObj.put("cproduct_bahan",jTable1.getValueAt(i,7).toString());
            listObj.put("deadline",jTable1.getValueAt(i,8).toString());
            listObj.put("cket",jTable1.getValueAt(i,9).toString());
                
            if("-1".equalsIgnoreCase(jTable1.getValueAt(i, 0).toString())) {
                //INSERT
                
                sql=con.insertValue("PCustomerPOD", listObj);
                System.out.println(sql);
                
            } else {  
                sql=con.updateValue("PCustomerPOD", listObj);
                sql+=" where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";                
            }
            
            if(con.queryUpdate(sql)<=0){
                err++;
            } 
        }
        
        return err;
    }
    private void save(){
        cnbkt_print =  tbukti.getText();
        String due_date = "NULL";
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cmarketing",tkaryawankode.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("kode",tkode.getText());
        listObj.put("cnote",tket1.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("PCustomerPO", listObj);
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
        
        con.queryUpdate("ALTER TABLE PCustomerPOD NOCHECK CONSTRAINT ALL");
        
        String due_date = "NULL";
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",tbukti.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef,jDateTran.getDate(),"yyyy-MM-dd"));
        listObj.put("cmarketing",tkaryawankode.getText());
        listObj.put("kode_customer",tcustomerkode.getText());
        listObj.put("kode",tkode.getText());
        listObj.put("cnote",tket1.getText());
        listObj.put("updateby",Login.user);
        listObj.put("dupdat","getdate()");
        sql=con.updateValue("PCustomerPO", listObj);
        sql+=" where id="+id;
        if(con.queryUpdate(sql)>0) {
            for(int i=0; i<deleteRow.size();i++){
                sql="delete from  PCustomerPOD  where id = '"+deleteRow.get(i)+"' ";
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
        
        con.queryUpdate("ALTER TABLE PCustomerPOD  WITH CHECK CHECK  CONSTRAINT ALL");
    }
    private void delete(){
        int err=0;
        for(int i=0; i<deleteRow.size();i++){
            sql="delete from  PCustomerPOD  where id = '"+deleteRow.get(i)+"' ";
            con.queryUpdate(sql);
        }
        for(int i=0; i<jTable1.getRowCount();i++){
            if(!jTable1.getValueAt(i, 0).toString().equals("-1")) {
                sql="delete from  PCustomerPOD  where id = '"+jTable1.getValueAt(i, 0).toString()+"' ";
                if(con.queryUpdate(sql)<=0){
                    err++;
                } 
            }
            
        }
        sql="delete from  PCustomerPO  where id = '"+id+"' ";
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
        
        
        sql="select a.cnbkt,a.dtran,a.kode_customer,b.cname as customer,b.addr,b.ctelp,a.cnote\n" +
                "from PCustomerPO a\n" +
                "join MCustomer b on a.kode_customer=b.kode_customer\n" +
            "where a.ccode = '"+tcode.getText()+"' and a.cnbkt = '"+cnbkt_print+"' ";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                param.put("pt", deffProgram.get("pt"));
                param.put("alamat", deffProgram.get("alamat"));
                param.put("telp", "Telp. "+deffProgram.get("telp"));
                param.put("mail", "Email : "+deffProgram.get("mail"));
                param.put("noorder",rs.getString("cnbkt"));
                param.put("customer",rs.getString("customer"));
                param.put("alamatcustomer",rs.getString("addr"));
                param.put("telpcustomer",rs.getString("ctelp"));
                param.put("ket",rs.getString("cnote"));
                param.put("tglorder",rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"));
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        } 
        sql="select b.cnameProduct as produk,a.nqty,a.nunit,c.cnameProduct as bahan,a.deadline,a.cket from PCustomerPOD  a\n" +
            "left join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "left join MProduk c on a.cgroup_bahan=c.cgroup and a.cproduct_bahan=c.cproduct\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+cnbkt_print+"'";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTableprint);
        DefaultTableModel model = (DefaultTableModel) jTableprint.getModel();
        int nomor=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    rs.getString("produk"),
                    rs.getDouble("nqty"),
                    rs.getString("nunit"),
                    rs.getString("bahan"),
                    rfa.DateString(keyssDef,rs.getDate("deadline"),"dd MMM yyyy"),
                    rs.getString("cket"),
                });
                
            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTableprint.getModel());
            String treport = "report/POCustomer.jrxml";
            
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
        tbahangroup = new javax.swing.JTextField();
        jScrollPanelPrint = new javax.swing.JScrollPane();
        jTableprint = new javax.swing.JTable();
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
        tqty = new javax.swing.JTextField();
        tproduknama = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        btPrint = new javax.swing.JButton();
        jDateDeadline = new com.toedter.calendar.JDateChooser();
        jLabel7 = new javax.swing.JLabel();
        tcustomerkode = new javax.swing.JTextField();
        jButton3 = new javax.swing.JButton();
        tcustomer = new javax.swing.JTextField();
        listSatuan = new javax.swing.JComboBox();
        jLabel10 = new javax.swing.JLabel();
        tbahan = new javax.swing.JTextField();
        jLabel25 = new javax.swing.JLabel();
        jButton11 = new javax.swing.JButton();
        tbahannama = new javax.swing.JTextField();
        jLabel6 = new javax.swing.JLabel();
        tkaryawankode = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();
        tkaryawan = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        tkode = new javax.swing.JTextField();
        jLabel14 = new javax.swing.JLabel();
        jScrollPane6 = new javax.swing.JScrollPane();
        tket1 = new javax.swing.JTextArea();
        btDelete = new javax.swing.JButton();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();

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

        tbahangroup.setText("jTextField1");

        jTableprint.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null},
                {null, null, null, null, null, null}
            },
            new String [] {
                "produk", "nqty", "nunit", "bahan", "deadline", "cket"
            }
        ));
        jScrollPanelPrint.setViewportView(jTableprint);

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Accounting_20px.png"))); // NOI18N
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, 30, 20));

        jLabel2.setFont(new java.awt.Font("Tahoma", 1, 18)); // NOI18N
        jLabel2.setText("PreOrder Customer");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, 340, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 30, -1, 20));

        jLabel12.setText("No PO Customer");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        tbuktis.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbuktisKeyPressed(evt);
            }
        });
        jPanel6.add(tbuktis, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 60, 140, -1));

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
        jPanel6.add(jDate2, new org.netbeans.lib.awtextra.AbsoluteConstraints(580, 30, 150, -1));

        jLabel13.setText("Periode ");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 30, -1, 20));

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
        jPanel6.add(jDate1, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 30, 150, -1));

        tkets.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tketsKeyPressed(evt);
            }
        });
        jPanel6.add(tkets, new org.netbeans.lib.awtextra.AbsoluteConstraints(390, 60, 340, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(780, 30, 90, -1));

        jLabel15.setText("Customer");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 60, -1, 20));

        jLabel21.setText("Gudang");
        jPanel6.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));

        tcodes.setEditable(false);
        tcodes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodesFocusLost(evt);
            }
        });
        jPanel6.add(tcodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 30, 110, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(260, 30, 30, -1));

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
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id", "Kode", "Gudang", "PO Customer", "Tgl Order", "Customer", "Marketing", "Jumlah Produk"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false
            };

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

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null}
            },
            new String [] {
                "Group", "Product", "Item", "Qty", "Unit", "Bahan", "Deadline", "Note"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
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

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel4Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1037, Short.MAX_VALUE)
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
                .addComponent(jScrollPane3, javax.swing.GroupLayout.DEFAULT_SIZE, 154, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jTextField13, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane2, javax.swing.GroupLayout.PREFERRED_SIZE, 128, javax.swing.GroupLayout.PREFERRED_SIZE)
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
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 110, 20));

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Tgl. Order");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("Marketing*");
        jLabel5.setToolTipText("");
        jPanel1.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 72, 20));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("Note Produk");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 140, 72, -1));

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

        jPanel1.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 140, 330, 40));

        tbukti.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tbukti.setToolTipText("");
        tbukti.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbuktiFocusLost(evt);
            }
        });
        jPanel1.add(tbukti, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 330, -1));

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
        jPanel1.add(btHapus, new org.netbeans.lib.awtextra.AbsoluteConstraints(810, 200, 30, 30));

        btBatal.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/close-15.png"))); // NOI18N
        btBatal.setText("Batal");
        btBatal.setMargin(new java.awt.Insets(2, 1, 2, 1));
        btBatal.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btBatalActionPerformed(evt);
            }
        });
        jPanel1.add(btBatal, new org.netbeans.lib.awtextra.AbsoluteConstraints(850, 200, 70, 30));

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
        jPanel1.add(btAdd, new org.netbeans.lib.awtextra.AbsoluteConstraints(920, 200, 80, 30));

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
        jPanel1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 20, 80, -1));

        jLabel17.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel17.setText("Produk");
        jPanel1.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 20, 90, 20));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Qty");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 50, 90, 20));

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
        jPanel1.add(tqty, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 50, 110, -1));

        tproduknama.setEditable(false);
        tproduknama.setBackground(new java.awt.Color(153, 153, 255));
        tproduknama.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tproduknama, new org.netbeans.lib.awtextra.AbsoluteConstraints(790, 20, 210, -1));

        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(750, 20, 30, -1));

        btPrint.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        btPrint.setText("Print");
        btPrint.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btPrintActionPerformed(evt);
            }
        });
        jPanel1.add(btPrint, new org.netbeans.lib.awtextra.AbsoluteConstraints(350, 50, 130, -1));

        jDateDeadline.setDateFormatString("dd/MM/yyyy");
        jDateDeadline.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateDeadlinePropertyChange(evt);
            }
        });
        jDateDeadline.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateDeadlineKeyPressed(evt);
            }
        });
        jPanel1.add(jDateDeadline, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 110, 110, -1));

        jLabel7.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel7.setText("Kode");
        jLabel7.setToolTipText("");
        jPanel1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 120, 20));

        tcustomerkode.setEditable(false);
        tcustomerkode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcustomerkodeFocusLost(evt);
            }
        });
        tcustomerkode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tcustomerkodeKeyTyped(evt);
            }
        });
        jPanel1.add(tcustomerkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 90, -1));

        jButton3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton3, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 140, 30, -1));

        tcustomer.setEditable(false);
        jPanel1.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 140, 200, -1));

        listSatuan.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "PCS", "KG", "ROLL", "REAMS" }));
        jPanel1.add(listSatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(790, 50, -1, -1));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("Tgl. Deadline");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 110, 100, 20));

        tbahan.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tbahanFocusLost(evt);
            }
        });
        tbahan.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tbahanKeyPressed(evt);
            }
        });
        jPanel1.add(tbahan, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 80, 80, -1));

        jLabel25.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel25.setText("Bahan/Type Kertas");
        jPanel1.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(540, 80, 120, 20));

        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(750, 80, 30, -1));

        tbahannama.setEditable(false);
        tbahannama.setBackground(new java.awt.Color(153, 153, 255));
        tbahannama.setForeground(new java.awt.Color(255, 255, 255));
        jPanel1.add(tbahannama, new org.netbeans.lib.awtextra.AbsoluteConstraints(790, 80, 210, -1));

        jLabel6.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel6.setText("Customer*");
        jLabel6.setToolTipText("");
        jPanel1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 72, 20));

        tkaryawankode.setEditable(false);
        tkaryawankode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tkaryawankodeFocusLost(evt);
            }
        });
        tkaryawankode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tkaryawankodeKeyPressed(evt);
            }
        });
        jPanel1.add(tkaryawankode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 90, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 170, 30, -1));

        tkaryawan.setEditable(false);
        jPanel1.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 170, 200, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("No PO Customer");
        jLabel11.setToolTipText("");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 120, 20));
        jPanel1.add(tkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 60, -1));

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("Note PO");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, 90, -1));

        jScrollPane6.setHorizontalScrollBarPolicy(javax.swing.ScrollPaneConstants.HORIZONTAL_SCROLLBAR_NEVER);

        tket1.setColumns(20);
        tket1.setFont(new java.awt.Font("sansserif", 0, 11)); // NOI18N
        tket1.setLineWrap(true);
        tket1.setRows(5);
        tket1.setWrapStyleWord(true);
        tket1.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tket1FocusLost(evt);
            }
        });
        tket1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tket1KeyPressed(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                tket1KeyTyped(evt);
            }
        });
        jScrollPane6.setViewportView(tket1);

        jPanel1.add(jScrollPane6, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 360, 80));

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
                "id", "Group", "Product", "Item", "Qty", "Satuan ", "GroupBahan", "ProductBahan", "Dealine", "Note"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
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
        jTable1.setAutoResizeMode(javax.swing.JTable.AUTO_RESIZE_OFF);
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

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 1017, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 40, Short.MAX_VALUE))
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jScrollPane1)
                    .addGroup(jPanel5Layout.createSequentialGroup()
                        .addGap(0, 0, Short.MAX_VALUE)
                        .addComponent(btDelete, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(13, 13, 13)
                        .addComponent(btSave, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addGap(13, 13, 13)
                        .addComponent(btCancel, javax.swing.GroupLayout.PREFERRED_SIZE, 97, javax.swing.GroupLayout.PREFERRED_SIZE)))
                .addContainerGap())
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 288, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 121, Short.MAX_VALUE)
                .addGap(18, 18, 18)
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

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
        tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        rfa.EnterNextFocus(evt, tproduk);
    }//GEN-LAST:event_tketKeyPressed

    private void tketKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyTyped
        
    }//GEN-LAST:event_tketKeyTyped

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        //if(btSave.getText().equals("Save")) LastCode();
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
            tprodukgroup.setText(rs.getString("cgroup"));
            tproduknama.setText(rs.getString("cnameProduct"));
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
        rfa.EnterNextFocus(evt, tket);
    }//GEN-LAST:event_tqtyKeyPressed

    private void tqtyKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tqtyKeyTyped
        //rfa.filterAngka(keyssDef, evt);
    }//GEN-LAST:event_tqtyKeyTyped

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        if(a.status){
            satuanterkecil="";
            tproduk.setText(a.kodeBarang);
            tprodukgroup.setText(a.groupBarang);
            tproduknama.setText(a.namaBarang);
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

    private void btPrintActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btPrintActionPerformed
        if(con.akses(akses, rfa.PRINT)) print();
    }//GEN-LAST:event_btPrintActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
        if(pil == 0) {
            if(jTable1.getRowCount()<=0) {
                JOptionPane.showMessageDialog(this,"Detail Produk belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(tcustomerkode.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"Customer belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(tbukti.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"NO PO belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(tkode.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"Kode belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }

            if(btSave.getText().equals("Save")) {
                sql = "select * from PCustomerPO where ccode='"+tcode.getText()+"' and cnbkt='"+tbukti.getText()+"'  ";
                rs = con.queryResult(sql);
                int no=0;
                try {
                    while(rs.next()){
                        JOptionPane.showMessageDialog(this,"Nomor PO sudah ada dalam sistem", "Info", JOptionPane.ERROR_MESSAGE);
                        return;
                    }
                } catch (SQLException ex) {
                    ex.printStackTrace();
                }
                
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
                if(!cnbkt.equals(tbukti.getText())) {
                    sql="SELECT * FROM TranDeliveryOrder where ccode='"+tcode.getText()+"' and cnbkt_inv = '"+cnbkt+"'";
                    rs = con.queryResult(sql);
                    try {  
                        while(rs.next()){
                            JOptionPane.showMessageDialog(this,"Maaf Nomor PO tidak dapat di rubah \nkarena sudah dibuatkan Surat Jalan pada tanggal "+
                                    rfa.DateString(keyssDef, rs.getDate("dtran"), "dd/MM/yyyy")+" Oleh "+rs.getString("cusername"), "Info", JOptionPane.ERROR_MESSAGE);
                            return;
                        }
                    } catch (SQLException ex) { ex.printStackTrace();   }
                    
                    HashMap<String,String> listObj = new HashMap<String,String>();        
                    listObj.put("cnbkt_order",tbukti.getText());
                    sql=con.updateValue("PWorkOrder", listObj);
                    sql+=" where ccode='"+tcode.getText()+"' and cnbkt_order = '"+cnbkt+"' ";
                    con.queryUpdate(sql);
                } 
                cnbkt = tbukti.getText();
                cnbkt_print = tbukti.getText();
                if(con.akses(akses, rfa.UPDATE)) {
                    update();
                }
            }
        }
    }//GEN-LAST:event_btSaveActionPerformed

    private void btDeleteActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDeleteActionPerformed
        sql="SELECT * FROM PWorkOrder where ccode='"+tcode.getText()+"' and cnbkt_order = '"+cnbkt+"'";
        rs = con.queryResult(sql);
        try {  
            while(rs.next()){
                JOptionPane.showMessageDialog(this,"Maaf PO CUSTOMER tidak dapat di HAPUS \nkarena sudah dibuatkan SPK pada tanggal "+
                                    rfa.DateString(keyssDef, rs.getDate("dtran"), "dd/MM/yyyy")+" Oleh "+rs.getString("cusername"), "Info", JOptionPane.ERROR_MESSAGE);
                            return;
            }
        } catch (SQLException ex) { ex.printStackTrace();   }
        
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
            cnbkt = jTable2.getValueAt(baris, 3).toString();
            cnbkt_print = cnbkt;
            tcode.setText(tcodes.getText());
            loadEdit();
        } else if(evt.getClickCount() == 1) {
            int baris = jTable2.getSelectedRow();
            String bkt = jTable2.getValueAt(baris, 3).toString();
            sql="select a.*,b.cnameProduct \n" +
            "from PCustomerPOD a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.cnbkt = '"+bkt+"' and a.ccode = '"+tcodes.getText()+"'";
            rfa.clear_table(MenuAdmin.keyssDef, jTable3);
            DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    model.addRow(new Object[]{
                        rs.getString("cgroup"),rs.getString("cproduct"),rs.getString("cnameProduct"),
                        rs.getDouble("nqty"),rs.getString("nunit"),
                        rs.getString("cproduct_bahan"),
                        rs.getString("deadline"),rs.getString("cket")
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

    private void jDateDeadlinePropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateDeadlinePropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateDeadlinePropertyChange

    private void jDateDeadlineKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateDeadlineKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateDeadlineKeyPressed

    private void tcustomerkodeKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tcustomerkodeKeyTyped
        if(evt.getKeyChar() == KeyEvent.VK_BACK_SPACE) {
            rfa.empty(keyssDef, tcustomer,tcustomerkode);
        }
    }//GEN-LAST:event_tcustomerkodeKeyTyped

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        DialogMembers a = new DialogMembers();
        a.setVisible(true);
        rfa.empty(keyssDef, tcustomer,tcustomerkode);
        if(a.status){
            tcustomerkode.setText(a.Kode);
            tcustomer.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton3ActionPerformed

    private void tbahanFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbahanFocusLost
        sql = "select * from mproduk where cproduct = '"+tbahan.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){
            tbahangroup.setText(rs.getString("cgroup"));
            tbahannama.setText(rs.getString("cnameProduct"));
        }
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_tbahanFocusLost

    private void tbahanKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tbahanKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tbahanKeyPressed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogProduk a = new DialogProduk(0);
        a.setVisible(true);
        if(a.status){
            satuanterkecil="";
            tbahan.setText(a.kodeBarang);
            tbahangroup.setText(a.groupBarang);
            tbahannama.setText(a.namaBarang);
            tket.requestFocus();
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void tkaryawankodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tkaryawankodeFocusLost
        loadkaryawan();
    }//GEN-LAST:event_tkaryawankodeFocusLost

    private void tkaryawankodeKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tkaryawankodeKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tkaryawankodeKeyPressed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawankode.setText(a.Kode);
            tkaryawan.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void tcustomerkodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcustomerkodeFocusLost
        loadcustomer();
    }//GEN-LAST:event_tcustomerkodeFocusLost

    private void tbuktiFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tbuktiFocusLost
        tbukti.setText(tbukti.getText().toUpperCase());
    }//GEN-LAST:event_tbuktiFocusLost

    private void tket1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tket1FocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tket1FocusLost

    private void tket1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tket1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tket1KeyPressed

    private void tket1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tket1KeyTyped
        // TODO add your handling code here:
    }//GEN-LAST:event_tket1KeyTyped


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAdd;
    private javax.swing.JButton btBatal;
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btDelete;
    private javax.swing.JButton btHapus;
    private javax.swing.JButton btPrint;
    private javax.swing.JButton btSave;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton7;
    private com.toedter.calendar.JDateChooser jDate1;
    private com.toedter.calendar.JDateChooser jDate2;
    private com.toedter.calendar.JDateChooser jDateDeadline;
    private com.toedter.calendar.JDateChooser jDateTran;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel14;
    private javax.swing.JLabel jLabel15;
    private javax.swing.JLabel jLabel17;
    private javax.swing.JLabel jLabel18;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel21;
    private javax.swing.JLabel jLabel25;
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
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JScrollPane jScrollPane6;
    private javax.swing.JScrollPane jScrollPanelPrint;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTable jTableprint;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField13;
    private javax.swing.JTable jtable_print;
    private javax.swing.JComboBox listSatuan;
    private javax.swing.JTextField tbahan;
    private javax.swing.JTextField tbahangroup;
    private javax.swing.JTextField tbahannama;
    private javax.swing.JTextField tbukti;
    private javax.swing.JTextField tbuktis;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcodes;
    private javax.swing.JTextField tcustomer;
    private javax.swing.JTextField tcustomerkode;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawankode;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextArea tket1;
    private javax.swing.JTextField tkets;
    private javax.swing.JTextField tkode;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tprodukgroup;
    private javax.swing.JTextField tproduknama;
    private javax.swing.JTextField tqty;
    private javax.swing.JTextField tsatuanhigh;
    private javax.swing.JTextField tsatuanlow;
    // End of variables declaration//GEN-END:variables
}
