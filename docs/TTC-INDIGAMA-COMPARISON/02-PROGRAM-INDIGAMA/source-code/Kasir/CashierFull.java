/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Kasir;

import DialogPublicTP.DialogDelivery;
import DialogPublicTP.DialogProduk;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import RawMaterial.StockOpname;
import Tools.ConnectDB;
import java.awt.Color;
import java.awt.Cursor;
import java.awt.Dimension;
import java.awt.Toolkit;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
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
import javax.swing.event.ChangeEvent;
import javax.swing.table.DefaultTableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JRException;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperPrintManager;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import okhttp3.FormBody;
import okhttp3.RequestBody;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class CashierFull extends javax.swing.JFrame {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="CASHIER";
    String idKas="D1";
    ArrayList<String> deleteRow;
    
    Date tanggal=null;
    Double harga,diskon,diskonMember;
    String unitBarang="";
    DecimalFormat dFormat = new DecimalFormat("#,##0.00");
    String tujuanSJ,sopirSJ,nopolSJ,metodePembayaran,id_karyawan;
    String JmlJT,TglJT;
    String ccode="";
    String ppnnominal="",ongkir="";
    Double[] jmlAwal = new Double[1000];
    int change = 0;
    ArrayList<Integer> satuanNilai;
    String noinv="";
    Boolean ongkosMesin=false;
    ArrayList<String> idSkat;
    
    String bank1="",bank2="";
    public CashierFull() {
        initComponents();
        ccode=deffProgram.get("ccode");
        Dimension screenSize;
        screenSize=Toolkit.getDefaultToolkit().getScreenSize();
        setSize(screenSize.width,screenSize.height-20);
        setLocation(0, -5);
        loadSkat();
        defaultForm();
        clearVariable();
        push_button();
        loadCompany();
    }
    private void loadCompany(){
        sql = "select * from MCompany ";
        rs = con.queryResult(sql); 
        try {
            while(rs.next()){ 
                bank1 = rs.getString("rek1_jenis")+"\nAn. "+rs.getString("rek1_an")+" \nNo. "+rs.getString("rek1_no");
                bank2 = rs.getString("rek2_jenis")+"\nAn. "+rs.getString("rek2_an")+" \nNo. "+rs.getString("rek2_no"); 
                tket.setText(rs.getString("ket_inv"));

            }
        } catch (SQLException ex) {
            ex.printStackTrace();
        }
    } 
    private void loadSkat(){
        idSkat = new ArrayList<>();
        idSkat.clear();
        cbSkat.removeAll();
        cbSkat.removeAllItems();
        idSkat.add("-1");
        cbSkat.addItem("Pilih Skat");
        
        rs = con.queryResult("Select * from mskat -- where ccode='"+deffProgram.get("ccode")+"' ");
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
    
    private void clearVariable(){
        harga = null;
        diskon = null;
        unitBarang = null;
    }
    private void defaultForm() {
        ongkosMesin=false;
        namauser.setText(" Nama User : "+Login.user);
        
        sql = "select getdate() as tgl";
        rs = con.queryResult(sql);
        
        try {
            while(rs.next()){
                tanggal = rs.getDate("tgl");
                jDateTranSales.setDate(rs.getDate("tgl"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(CashierFull.class.getName()).log(Level.SEVERE, null, ex);
        }
        idCreated();
        
    }
    
    private void idCreated(){
        
        int nomor = 0;
        String nobuktiawal = "INV/"+new SimpleDateFormat("yyyyMMdd").format(tanggal)+"/";
        sql =   "select isnull(max(convert(int,right(cnbkt,4))),0) as lastno \n" +
                "from transale \n" +
                "where cnbkt like '%"+nobuktiawal+"%'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                nomor = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(CashierFull.class.getName()).log(Level.SEVERE, null, ex);
        }
        
//        noinv = nobuktiawal+rfa.NumberBukti(MenuAdmin.keyssDef, 4, nomor+1);
//        ctran.setText(noinv);
    }
    
    
    private void push_button(){
        InputMap im = getRootPane().getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = getRootPane().getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F2, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                jButton3.doClick();
        
            }
        });
        
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F9, 0), "go");
        am.put("go", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                btProses.doClick();
            }
        });
    }
    
    private void koreksi() {
        if(jTable1.getRowCount()>0) {
            for(int i=0; i<jTable1.getRowCount(); i++) {
//                    if((Double.valueOf(jTable1.getValueAt(i, 4).toString()))>(cekSisaStock(jTable1.getValueAt(i, 1).toString(),jTable1.getValueAt(i, 10).toString()))) {
//                        JOptionPane.showMessageDialog(this, "Stock Barang Terbatas, Sisa Stock Sebanyak "+
//                                dFormat.format(cekSisaStock(tfKode.getText()))+" Unit", "Information Stock", JOptionPane.ERROR_MESSAGE);
//                        jTable1.setValueAt(jmlAwal[i],i, 4);
//                        return;
//                    }
                    
                    //jTable1.setValueAt(tppn.getText(), i, 6);
                    
                    Double hargaBarang = Double.valueOf(jTable1.getValueAt(i, 3).toString());
                    Double jumlahTotal = Double.valueOf(jTable1.getValueAt(i, 4).toString());
                    Double diskonBarang = Double.valueOf(jTable1.getValueAt(i, 5).toString());
                    //Double ppn = Double.valueOf(jTable1.getValueAt(i, 6).toString());
                    Double HargaSetDiskon = hargaBarang-((hargaBarang*diskonBarang)/100);
                    Double totalHarga = HargaSetDiskon*jumlahTotal;
                      
                    jTable1.setValueAt(Double.valueOf(jTable1.getValueAt(i, 4).toString()), i, 4);
                    jTable1.setValueAt(totalHarga, i, 7);
                    
                
            }
        }
        
        if(jTable1.getRowCount()>0) {
            for(int i=0; i<jTable1.getRowCount(); i++) {
                jmlAwal[i] = Double.valueOf(jTable1.getValueAt(i, 4).toString());
            }
        }
    }
    
    private void beli() {
        
        if(Double.valueOf(tfJml.getText())>(cekSisaStock(tfKode.getText(),tproduk1.getText()))) {
            JOptionPane.showMessageDialog(this, "Stock Barang Terbatas, Sisa Stock Sebanyak "+dFormat.format(cekSisaStock(tfKode.getText(),tproduk1.getText()))+" Unit", "Information Stock", JOptionPane.ERROR_MESSAGE);
          
            tfJml.setText("");
            tfJml.requestFocus();
            return;
        }
        
        Boolean cekTable=false;
        if(jTable1.getRowCount()>0) {
            for(int i=0; i<jTable1.getRowCount(); i++) {
//                if(tfKode.getText().equals(jTable1.getValueAt(i, 1).toString()) && jListSatuan.getSelectedItem().toString().equalsIgnoreCase(jTable1.getValueAt(i,7).toString())) {
//                    
//                    if((Double.valueOf(tfJml.getText())+Double.valueOf(jTable1.getValueAt(i, 4).toString()))>(cekSisaStock(jTable1.getValueAt(i, 1).toString()))) {
//                        JOptionPane.showMessageDialog(this, "Stock Barang Terbatas, Sisa Stock Sebanyak "+
//                                dFormat.format(cekSisaStock(tfKode.getText()))+" Unit", "Information Stock", JOptionPane.ERROR_MESSAGE);
//
//                        tfJml.setText("");
//                        tfJml.requestFocus();
//                        return;
//                    }
//                    
//                    Double hargaBarang = Double.valueOf(jTable1.getValueAt(i, 3).toString());
//                    Double jumlahambil =Double.valueOf(tfJml.getText());
//                    Double jumlahTotal = Double.valueOf(jTable1.getValueAt(i, 4).toString())+jumlahambil;
//                    Double diskonBarang = Double.valueOf(jTable1.getValueAt(i, 5).toString());
//                    Double HargaSetDiskon = hargaBarang-((hargaBarang*diskonBarang)/100);
//                    Double totalHarga = HargaSetDiskon*jumlahTotal;
//                    jTable1.setValueAt(Double.valueOf(jTable1.getValueAt(i, 4).toString())+Double.valueOf(tfJml.getText()), i, 4);
//                    jTable1.setValueAt(totalHarga, i, 6);
//                    cekTable = true;
//                    break;
//                }
            }
        }
        
        if(!cekTable) {
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            harga = harga*satuanNilai.get(jListSatuan.getSelectedIndex());
            Double hargaBarang = harga;
            Double jumlahTotal = Double.valueOf(tfJml.getText());
            Double diskonBarang = diskon;
            Double HargaSetDiskon = hargaBarang-((hargaBarang*diskonBarang)/100);
            Double totalHarga = HargaSetDiskon*jumlahTotal;
            
            model.addRow(new Object[]{jTable1.getRowCount()+1,tfKode.getText(),ketKode.getText(),harga,
                Double.valueOf(tfJml.getText()),diskon,0.0,totalHarga,jListSatuan.getSelectedItem().toString(),harga,tproduk1.getText(),satuanNilai.get(jListSatuan.getSelectedIndex())*jumlahTotal});
            
        }
        
        callculation();
        
        if(jTable1.getRowCount()>0) {
            for(int i=0; i<jTable1.getRowCount(); i++) {
                jmlAwal[i] = Double.valueOf(jTable1.getValueAt(i, 4).toString());
            }
        }
        
        tfKode.setText("");
        ketKode.setText("");
        tfJml.setText("");
        tfKode.requestFocus();
    }
    
    private void callculation() {
        Double nilaiakhir = 0.0;
        Double totalSementara = 0.0,diston=0.0,ongkir=0.0,ppn=0.0;
        for(int i=0; i<jTable1.getRowCount(); i++) {
            totalSementara=Double.valueOf(jTable1.getValueAt(i, 7).toString())+totalSementara;
           
//            Double hargaBarang = Double.valueOf(jTable1.getValueAt(i, 3).toString());
//            Double jumlahTotal = Double.valueOf(jTable1.getValueAt(i, 4).toString());
//            Double diskonBarang = Double.valueOf(jTable1.getValueAt(i, 5).toString());
//            Double HargaSetDiskon = hargaBarang-((hargaBarang*diskonBarang)/100);
//            Double totalHarga = HargaSetDiskon*jumlahTotal;
                    
        }
        
        if(!iDisc.getText().isEmpty()) diston = Double.valueOf(iDisc.getText().replaceAll(",", ""));
        if(!tongkir.getText().isEmpty()) ongkir = Double.valueOf(tongkir.getText().replaceAll(",", ""));
        if(!tppn.getText().isEmpty()) ppn = Double.valueOf(tppn.getText().replaceAll(",", ""))/100;
       
        nilaiakhir = totalSementara;
        
        iSubTotal.setText(dFormat.format(totalSementara));
        Double HargaSetDiskon = totalSementara-diston;
        //HargaSetDiskon = HargaSetDiskon - Double.valueOf(iDisc.getText());
        
        Double ppn_nilai = HargaSetDiskon*ppn;
        
        tppn_nominal.setText(dFormat.format(ppn_nilai));        
        
        nilaiakhir = HargaSetDiskon+ppn_nilai+ongkir;
        iTotalSementara.setText(dFormat.format(nilaiakhir));
        
    }
    
    private void loadBarangF2(){
        harga = 0.0;
        diskon = 0.0;
        unitBarang = "";
        
        DialogProduk a = new DialogProduk(1);
        a.setVisible(true);
        if(a.status) {
            tproduk1.setText(a.groupBarang);
            tfKode.setText(a.kodeBarang);
            ketKode.setText(a.namaBarang);
            harga = a.hargajual;
            diskon = 0.0;
            unitBarang = a.unit;
            tfJml.requestFocus();
            
            jListSatuan.removeAll();
            jListSatuan.removeAllItems();
            satuanNilai = new ArrayList<>();
            satuanNilai.clear();
            sql="select cname,big_value as terkecil from MSatuan where cname= '"+unitBarang+"'\n" +
                "union all\n" +
                "select cname,small_value as terkecil  from MSatuan where id_turunan in (select id from MSatuan where cname= '"+unitBarang+"')";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    jListSatuan.addItem(rs.getString("cname"));
                    satuanNilai.add(rs.getInt("terkecil"));
                }
            } catch (SQLException ex) { ex.printStackTrace();}
            
          
            
                    
        } else {
            tfKode.requestFocus();
        }
    }
    
    
    private double cekSisaStock(String kodeBarang,String group){
        Double sisa = 0.0; //sementara
        
        sql="select cgroup,cproduct,sum(totalpcs) as qty,satuan from ProductStock\n" +
            "where   cproduct = '"+kodeBarang+"'\n"+
            "group by cgroup,cproduct,satuan";
        rs = con.queryResult(sql);
        try {
            while(rs.next()) {
                sisa= rs.getDouble("qty");
            }
//        sql = "select a.id_barang,a.nama,a.harga,a.unit,\n" +
//                "(select ifnull(sum(jumlah),0) from pembelian where id_barang = a.id_barang)  as TotalBarang,\n" +
//                "(select ifnull(sum(jumlah),0) from penjualan where id_barang = a.id_barang and left(id_jual,1) = 'R')  as TotalReturn,\n" +
//                "(select ifnull(sum(jumlah),0) from penjualan where id_barang = a.id_barang and left(id_jual,1) != 'R')  as TotalDijual\n" +
//                "from produkbarang a\n" +
//                "where a.id_barang = '"+kodeBarang+"'";
//        
//        RequestBody formBody = new FormBody.Builder()
//        .add("kodeBarang",kodeBarang)
//        .build();
////        sql = "get/nuzulsteel/kasir/ceksisastok";
////        rs = con.queryResult(sql);
//        
//        rs = con.queryResult(sql);
//        if(rs != null && rs.size()>0) {
//            for(int i=0;i<rs.size();++i) {
//                JSONObject obj=(JSONObject)rs.get(i);
//                sisa = (Double.valueOf(rs.getString("TotalBarang").toString())+Double.valueOf(rs.getString("TotalReturn").toString())) - Double.valueOf(rs.getString("TotalDijual").toString());
//                
//            }
//        }
        } catch (SQLException ex) {
            Logger.getLogger(CashierFull.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        return sisa;
    }
    
    private void saving() {
        
        /*
        if(tfBayar.getText().isEmpty()) {
            tfBayar.setText("0");
        }
        Double Bayar = Double.valueOf(tfBayar.getText());
        Double Kembali =Bayar  - Double.valueOf(iTotal.getText().replaceAll(",", ""));      
        iKembali.setText(dFormat.format(Kembali));
        */
        
        
        jTable1.editingCanceled(null);
        jTable1.editingCanceled(new ChangeEvent (this));
        jTable1.clearSelection();

//        idCreated();
//        ctran.setText("BISMILLAH");
        noinv = ctran.getText();
        CashierBayar a = new CashierBayar(iTotalSementara.getText(),noinv);
        a.setVisible(true);
        if(a.status) {
            this.setCursor(Cursor.getPredefinedCursor(Cursor.WAIT_CURSOR));
            System.out.println("Done");
            String idmarketing = a.idmarketing; 
            tfMember.setText(a.idmember);
            jLabel3.setText(a.namamember);
            jLabel2.setText(a.alamatmember);
            iDisc.setText(dFormat.format(Double.valueOf(a.discmember.replaceAll(",", ""))));
            tppn_nominal.setText(dFormat.format(Double.valueOf(a.ppn_nominal.replaceAll(",", ""))));
            ppnnominal = dFormat.format(Double.valueOf(a.ppn_nominal.replaceAll(",", "")));
            ongkir = dFormat.format(Double.valueOf(a.ongkir.replaceAll(",", "")));
            tongkir.setText(dFormat.format(Double.valueOf(a.ongkir.replaceAll(",", ""))));
            iTotal.setText(dFormat.format(Double.valueOf(a.totalk.replaceAll(",", ""))));
            iBayar.setText(dFormat.format(Double.valueOf(a.dbayar.replaceAll(",", ""))));
            iKembali.setText(dFormat.format(Double.valueOf(a.dkembali.replaceAll(",", ""))));
            JmlJT=TglJT="";
            JmlJT=a.djt;
            TglJT=a.tgljt;
            Date tglJatuhTempo = a.tglJatuhTempo;
            metodePembayaran = a.metodebayardesc;
            
            System.out.println("PPN "+tppn_nominal.getText());
            System.out.println("PPN get "+a.ppn_nominal);
            
            HashMap<String,String> header = new HashMap<String,String>();        
            header.put("ccode",ccode);
            header.put("cnbkt",noinv);
            header.put("cworkorder",tspk.getText());
            header.put("kode",tkode.getText());
            header.put("cnbkt_delivery",tsj.getText());
            header.put("cnbkt_order",tpo.getText());
            header.put("ntotal_sub",iSubTotal.getText().replaceAll(",", ""));
            header.put("ndisc",a.discmember.replaceAll(",", ""));
            header.put("ppn",a.ppn.replaceAll(",", ""));
            header.put("ppn_nominal",a.ppn_nominal.replaceAll(",", ""));
            header.put("nongkir",a.ongkir.replaceAll(",", ""));
            header.put("ntotal",iTotal.getText().replaceAll(",", ""));
            header.put("bayar",iBayar.getText().replaceAll(",", ""));
            header.put("kembali",iKembali.getText().replaceAll(",", ""));
            header.put("kodebayar",a.metodebayar);
            header.put("tket",tket.getText());
            header.put("cusername",Login.user);
            sql=con.insertValue("TranSaleHeader", header);
            if(con.queryUpdate(sql)<=0) {
                JOptionPane.showMessageDialog(this,"Gagal membuat Invoice, No Invoice Sudah Ada", "Info", JOptionPane.ERROR_MESSAGE);
                this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
                return;
            }
            
            
            idCreated();
            for(int i = 0; i<jTable1.getRowCount(); i++) {
                HashMap<String,String> listObj = new HashMap<String,String>();        
                listObj.put("ctran","IV");
                listObj.put("cnbkt",noinv);
                listObj.put("caccnt","103.101");
                listObj.put("ccode",deffProgram.get("ccode"));
                listObj.put("cgroup",jTable1.getValueAt(i, 10).toString());
                listObj.put("cproduct",jTable1.getValueAt(i, 1).toString());
                listObj.put("namaproduk",jTable1.getValueAt(i, 2).toString());
                listObj.put("cunit",jTable1.getValueAt(i, 8).toString());
                listObj.put("id_member",tfMember.getText().trim());
                listObj.put("dtran",new SimpleDateFormat("yyyy/MM/dd").format(jDateTranSales.getDate()));
                listObj.put("jumlah",rfa.formatDecimal3.format(jTable1.getValueAt(i, 4)));
                listObj.put("nqty_small",rfa.formatDecimal3.format(jTable1.getValueAt(i, 11)));
                listObj.put("harga",rfa.formatDecimal3.format(jTable1.getValueAt(i, 3)));
                listObj.put("diskon_barang",rfa.formatDecimal3.format(jTable1.getValueAt(i, 5)));
                listObj.put("diskon_member",iDisc.getText().replaceAll(",", ""));
                listObj.put("total",iTotal.getText().replaceAll(",", ""));
                listObj.put("bayar",iBayar.getText().replaceAll(",", ""));
                listObj.put("kembali",iKembali.getText().replaceAll(",", ""));
                listObj.put("kodebayar",a.metodebayar);
                listObj.put("cusername",Login.user);
                listObj.put("cmarketing",idmarketing);
                listObj.put("cworkorder",a.wo);
                sql=con.insertValue("TranSale", listObj);
                
                con.queryUpdate(sql);
            }
            
            System.out.println("JUMLAH TEMPO : "+JmlJT);
            
            if(Integer.valueOf(JmlJT) > 0) {
                HashMap<String,String> listObj = new HashMap<String,String>();        
                listObj.put("cnbkt",noinv);
                listObj.put("ccode",deffProgram.get("ccode"));
                listObj.put("lamatempo",JmlJT);
                listObj.put("awaltempo",new SimpleDateFormat("yyyy-MM-dd").format(jDateTranSales.getDate()));
                listObj.put("akhirtempo",new SimpleDateFormat("yyyy/MM/dd").format(tglJatuhTempo));
                listObj.put("bayarawal",iBayar.getText().replaceAll(",", ""));
                listObj.put("cicilan","0");
                listObj.put("cusername",Login.user);
                sql=con.insertValue("TranSaleBon", listObj);
                con.queryUpdate(sql);
            }
            

            int noo=1;
            for(int i=0; i<jTable1.getRowCount(); i++) {
                jTable1.setValueAt(noo++, i, 0);
                
                String satuan  = jTable1.getValueAt(i, 8).toString();
                String satuansplit[] = satuan.split(" ");
                jTable1.setValueAt(satuansplit[0], i,8);
            }
            
            //Auto Memorial
            if(!a.caccll.equals("") || !a.caccll.isEmpty()) {
                if(Double.valueOf(iBayar.getText().replaceAll(",", ""))>0) {
//                    HashMap<String,String> listObj = new HashMap<String,String>();               
//                    listObj.put("ctran","M1");
//                    listObj.put("cnbkt",buktimemorial());
//                    listObj.put("ccode",deffProgram.get("ccode"));
//                    listObj.put("cyear",new SimpleDateFormat("yyyy").format(jDateTranSales.getDate()));
//                    listObj.put("dtran",new SimpleDateFormat("yyyy-MM-dd").format(jDateTranSales.getDate()));
//                    listObj.put("cacckas","103.101");
//                    listObj.put("caccll",a.caccll);
//                    listObj.put("namnt",iBayar.getText().replaceAll(",", ""));
//                    listObj.put("cnote","SPLIT SALE ORDER CUSTOMER ("+a.namamember+") INV NO "+noinv);
//                    listObj.put("cusername",Login.user);
//                    sql=con.insertValue("TranMemorial", listObj);
//                    con.queryUpdate(sql);
                }
            }
            
            print();
            
            jTable1.clearSelection();
            jTable1.removeAll();
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            model.getDataVector().removeAllElements();
            jTable1.removeAll();
            
            rfa.empty(keyssDef, tfKode,ketKode,tfJml,tfMember,tfBayar,tsj,tspk,tpo,tcustomer,tcustomerkode,tfakturpajak);
            iTotalSementara.setText("0");
            iSubTotal.setText("0");
            iDisc.setText("0");
            iTotal.setText("0");
            iBayar.setText("0");
            iKembali.setText("0");
            tppn.setText("0");
            tppn_nominal.setText("0");
            tongkir.setText("0");
            
            defaultForm();
            
            tfKode.requestFocus();
            this.setCursor(Cursor.getPredefinedCursor(Cursor.DEFAULT_CURSOR));
        }
        
    }
    
    private String buktimemorial(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranMemorial where  ccode='"+deffProgram.get("ccode")+"' "
                + "and cnbkt like '%"+"MM/"+rfa.DateString(keyssDef, tanggal,"yyMM/")+Login.subdefault+"-"+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        String b1 = ("MM/"+rfa.DateString(keyssDef, tanggal,"yyMM/")+Login.subdefault+"-");
        String b2 = (rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
        
        return b1+b2;
    }
    
    private void autoSaveMemorial(){
        
//        
//        sql="INSERT INTO TranMemorial (ctran,cnbkt,ccode,cyear,dtran,cacckas,caccll\n" +
//                    ",namnt,cnote,cusername,kode_customer,kode_customerll,idmesinll)\n" +
//                    "VALUES\n" +
//                    "('"+idKas+"','"+jTable1.getValueAt(i, 6).toString()+"','"+tcode.getText()+"','"+jTable1.getValueAt(i, 5).toString().substring(0, 4)+"','"+jTable1.getValueAt(i, 5).toString()+"','"+jTable1.getValueAt(i, 1).toString()+"','"+jTable1.getValueAt(i, 2).toString()+"',"
//                        + "'"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"','"+jTable1.getValueAt(i, 3).toString()+"','"+Login.user+"',"
//                        + "'"+jTable1.getValueAt(i,8).toString()+"','"+jTable1.getValueAt(i,10).toString()+"','"+jTable1.getValueAt(i,12).toString()+"')";
    }
    

    
    private void print(){
        String nama=deffProgram.get("pt"),alamat=deffProgram.get("alamat");
        
        
        try {
            JRDataSource dataSource = new JRTableModelDataSource(jTable1.getModel());
            String treport = "report/kwitasi.jrxml";
            HashMap param = new HashMap();
            param.clear();
                param.put("tglinv",new SimpleDateFormat("dd/MMM/yyyy").format(jDateTranSales.getDate()));
                param.put("user", Login.user);
                param.put("subtotal", iSubTotal.getText());
                param.put("pot", iDisc.getText());
                param.put("total", iTotal.getText());
                param.put("toko", nama);
                param.put("alamattoko", alamat);
                param.put("nokwitansi", noinv);
                param.put("member", jLabel3.getText());
                param.put("memberlamat", jLabel2.getText());
                param.put("jt", JmlJT);
                param.put("tgljt", TglJT);
                param.put("sisa", iKembali.getText());
                param.put("uangmuka", iBayar.getText());
                param.put("ppn", ppnnominal);
                param.put("ongkir", ongkir);
                param.put("diskon", iDisc.getText());
                param.put("metodebayar", metodePembayaran);
                param.put("note",tket.getText());
                param.put("fakturpajak",tfakturpajak.getText());
                param.put("nopo",tpo.getText());
                param.put("nosj",tsj.getText());
                param.put("rek1",bank1);
                param.put("rek2",bank2);
                param.put("terbilang", rfa.Pembilang(Double.valueOf(iTotal.getText().replaceAll(",", ""))).toUpperCase()+" RUPIAH" );
                
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,true);
//            JasperViewer.setAlwaysOnTop(true); 
//            JasperViewer.viewReport(JPrint,false);
            
            Dimension screenSize,frameSize;
            int x,y;
            screenSize=Toolkit.getDefaultToolkit().getScreenSize();
            frameSize=getSize();
            x=(screenSize.width)/2;
            y=(screenSize.height-frameSize.height)/2;
        
            int xframe = frameSize.width/2;
            int yframe = frameSize.height;
            
            JasperViewer jasperViewer = new JasperViewer(JPrint, false); 
            jasperViewer.setTitle("Invoice");
            //setModal(false);
            jasperViewer.setAlwaysOnTop(true);  //here
            jasperViewer.setSize(xframe+175, yframe);
            jasperViewer.setLocation(x-175,0);
            jasperViewer.setVisible(true);
            //setModal(true);
        } catch (JRException e) {
            System.out.println(e);
            JOptionPane.showMessageDialog(this, e, "INFO", JOptionPane.ERROR_MESSAGE);
        }
    }
    
    private void callculationEnd(){
//        Double subTotal = Double.valueOf(iSubTotal.getText().replaceAll(",", ""));
//        Double Potongan = Double.valueOf(iDisc.getText().replaceAll(",", ""));
//        Double Total = subTotal - Potongan;
        iTotal.setText(iTotalSementara.getText());
//        iTotalSementara.setText(dFormat.format(Total));
    }
    
    
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        tproduk1 = new javax.swing.JTextField();
        jLabel15 = new javax.swing.JLabel();
        cbSkat = new javax.swing.JComboBox();
        jPanel3 = new javax.swing.JPanel();
        jLabel13 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        tfMember = new javax.swing.JTextField();
        tfBayar = new javax.swing.JTextField();
        jLabel12 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        jLayeredPane1 = new javax.swing.JLayeredPane();
        jLabel4 = new javax.swing.JLabel();
        jLabel9 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        jLabel7 = new javax.swing.JLabel();
        jSeparator1 = new javax.swing.JSeparator();
        jLabel8 = new javax.swing.JLabel();
        iKembali = new javax.swing.JLabel();
        iBayar = new javax.swing.JLabel();
        iTotal = new javax.swing.JLabel();
        jButton2 = new javax.swing.JButton();
        jLabel22 = new javax.swing.JLabel();
        tppn = new javax.swing.JTextField();
        jLayeredPane3 = new javax.swing.JLayeredPane();
        iSubTotal = new javax.swing.JLabel();
        jTextField2 = new javax.swing.JTextField();
        jLayeredPane4 = new javax.swing.JLayeredPane();
        jTextField3 = new javax.swing.JTextField();
        iDisc = new javax.swing.JTextField();
        jLayeredPane5 = new javax.swing.JLayeredPane();
        jTextField4 = new javax.swing.JTextField();
        tppn_nominal = new javax.swing.JTextField();
        jLayeredPane6 = new javax.swing.JLayeredPane();
        jTextField5 = new javax.swing.JTextField();
        tongkir = new javax.swing.JTextField();
        jPanel1 = new javax.swing.JPanel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLayeredPane2 = new javax.swing.JLayeredPane();
        ctran = new javax.swing.JTextField();
        tfKode = new javax.swing.JTextField();
        jButton3 = new javax.swing.JButton();
        jLabel10 = new javax.swing.JLabel();
        ketKode = new javax.swing.JTextField();
        jListSatuan = new javax.swing.JComboBox();
        jLabel11 = new javax.swing.JLabel();
        jLabel14 = new javax.swing.JLabel();
        tfJml = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        btProses = new javax.swing.JButton();
        jLabel16 = new javax.swing.JLabel();
        tsj = new javax.swing.JTextField();
        jButton4 = new javax.swing.JButton();
        jLabel17 = new javax.swing.JLabel();
        jLabel18 = new javax.swing.JLabel();
        tspk = new javax.swing.JTextField();
        tpo = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        tcustomerkode = new javax.swing.JTextField();
        tcustomer = new javax.swing.JTextField();
        jLabel21 = new javax.swing.JLabel();
        jLabel20 = new javax.swing.JLabel();
        tfakturpajak = new javax.swing.JTextField();
        jLabel23 = new javax.swing.JLabel();
        jScrollPane4 = new javax.swing.JScrollPane();
        tket = new javax.swing.JTextArea();
        jLabel24 = new javax.swing.JLabel();
        tkode = new javax.swing.JTextField();
        jLabel25 = new javax.swing.JLabel();
        jDateTranSales = new com.toedter.calendar.JDateChooser();
        namauser = new javax.swing.JTextField();
        iTotalSementara = new javax.swing.JTextField();
        jTextField1 = new javax.swing.JTextField();

        tproduk1.setText("jTextField1");

        jLabel15.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel15.setText("Skat");

        cbSkat.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));

        jPanel3.setBackground(new java.awt.Color(204, 204, 204));
        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel13.setText("Member");
        jPanel3.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, -1, 20));

        jLabel2.setText("Alamat");
        jPanel3.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 70, 170, 20));

        tfMember.setEditable(false);
        tfMember.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusGained(java.awt.event.FocusEvent evt) {
                tfMemberFocusGained(evt);
            }
            public void focusLost(java.awt.event.FocusEvent evt) {
                tfMemberFocusLost(evt);
            }
        });
        tfMember.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tfMemberKeyPressed(evt);
            }
        });
        jPanel3.add(tfMember, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 20, 170, 20));

        tfBayar.setEditable(false);
        tfBayar.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        tfBayar.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tfBayar.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusGained(java.awt.event.FocusEvent evt) {
                tfBayarFocusGained(evt);
            }
            public void focusLost(java.awt.event.FocusEvent evt) {
                tfBayarFocusLost(evt);
            }
        });
        tfBayar.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tfBayarKeyPressed(evt);
            }
        });
        jPanel3.add(tfBayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 100, 170, -1));

        jLabel12.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        jLabel12.setText("Bayar");
        jPanel3.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 100, -1, 20));

        jLabel3.setText("Name");
        jPanel3.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(110, 40, 170, 20));

        jLayeredPane1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel4.setText("Sub Total");
        jLayeredPane1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, -1, -1));

        jLabel9.setText("Disc");
        jLayeredPane1.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 30, -1, -1));

        jLabel6.setText("Total");
        jLayeredPane1.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 50, -1, -1));

        jLabel7.setText("Bayar");
        jLayeredPane1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, -1, -1));
        jLayeredPane1.add(jSeparator1, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 90, 270, 10));

        jLabel8.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        jLabel8.setText("Sisa");
        jLayeredPane1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 100, -1, -1));

        iKembali.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        iKembali.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        iKembali.setText("0");
        jLayeredPane1.add(iKembali, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 100, 120, -1));

        iBayar.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        iBayar.setText("0");
        jLayeredPane1.add(iBayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(160, 70, 120, -1));

        iTotal.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        iTotal.setText("0");
        jLayeredPane1.add(iTotal, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 50, 130, -1));

        jButton2.setText("Reload");
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });

        jLabel22.setText("Nilai PPN (%)");

        tppn.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tppn.setText("0");
        tppn.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tppnCaretUpdate(evt);
            }
        });
        tppn.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tppnKeyPressed(evt);
            }
        });

        jLayeredPane3.setBackground(new java.awt.Color(204, 255, 255));
        jLayeredPane3.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jLayeredPane3.setOpaque(true);

        iSubTotal.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        iSubTotal.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        iSubTotal.setText("0");

        jTextField2.setEditable(false);
        jTextField2.setBackground(new java.awt.Color(153, 153, 153));
        jTextField2.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField2.setForeground(new java.awt.Color(255, 255, 255));
        jTextField2.setText("Sub Total");

        javax.swing.GroupLayout jLayeredPane3Layout = new javax.swing.GroupLayout(jLayeredPane3);
        jLayeredPane3.setLayout(jLayeredPane3Layout);
        jLayeredPane3Layout.setHorizontalGroup(
            jLayeredPane3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jTextField2, javax.swing.GroupLayout.DEFAULT_SIZE, 162, Short.MAX_VALUE)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jLayeredPane3Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(iSubTotal, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                .addContainerGap())
        );
        jLayeredPane3Layout.setVerticalGroup(
            jLayeredPane3Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jLayeredPane3Layout.createSequentialGroup()
                .addComponent(jTextField2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(iSubTotal)
                .addContainerGap(16, Short.MAX_VALUE))
        );
        jLayeredPane3.setLayer(iSubTotal, javax.swing.JLayeredPane.DEFAULT_LAYER);
        jLayeredPane3.setLayer(jTextField2, javax.swing.JLayeredPane.DEFAULT_LAYER);

        jLayeredPane4.setBackground(new java.awt.Color(204, 255, 255));
        jLayeredPane4.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jLayeredPane4.setOpaque(true);

        jTextField3.setEditable(false);
        jTextField3.setBackground(new java.awt.Color(153, 153, 153));
        jTextField3.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField3.setForeground(new java.awt.Color(255, 255, 255));
        jTextField3.setText("Diskon");
        jTextField3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField3ActionPerformed(evt);
            }
        });

        iDisc.setBackground(new java.awt.Color(204, 255, 255));
        iDisc.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        iDisc.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        iDisc.setText("0");
        iDisc.setBorder(null);
        iDisc.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                iDiscCaretUpdate(evt);
            }
        });
        iDisc.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                iDiscFocusLost(evt);
            }
        });

        javax.swing.GroupLayout jLayeredPane4Layout = new javax.swing.GroupLayout(jLayeredPane4);
        jLayeredPane4.setLayout(jLayeredPane4Layout);
        jLayeredPane4Layout.setHorizontalGroup(
            jLayeredPane4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jTextField3, javax.swing.GroupLayout.DEFAULT_SIZE, 162, Short.MAX_VALUE)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jLayeredPane4Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(iDisc)
                .addContainerGap())
        );
        jLayeredPane4Layout.setVerticalGroup(
            jLayeredPane4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jLayeredPane4Layout.createSequentialGroup()
                .addComponent(jTextField3, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(iDisc, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
        );
        jLayeredPane4.setLayer(jTextField3, javax.swing.JLayeredPane.DEFAULT_LAYER);
        jLayeredPane4.setLayer(iDisc, javax.swing.JLayeredPane.DEFAULT_LAYER);

        jLayeredPane5.setBackground(new java.awt.Color(204, 255, 255));
        jLayeredPane5.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jLayeredPane5.setOpaque(true);

        jTextField4.setEditable(false);
        jTextField4.setBackground(new java.awt.Color(153, 153, 153));
        jTextField4.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField4.setForeground(new java.awt.Color(255, 255, 255));
        jTextField4.setText("PPn");
        jTextField4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField4ActionPerformed(evt);
            }
        });

        tppn_nominal.setBackground(new java.awt.Color(204, 255, 255));
        tppn_nominal.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        tppn_nominal.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tppn_nominal.setText("0");
        tppn_nominal.setBorder(null);
        tppn_nominal.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tppn_nominalCaretUpdate(evt);
            }
        });
        tppn_nominal.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tppn_nominalFocusLost(evt);
            }
        });

        javax.swing.GroupLayout jLayeredPane5Layout = new javax.swing.GroupLayout(jLayeredPane5);
        jLayeredPane5.setLayout(jLayeredPane5Layout);
        jLayeredPane5Layout.setHorizontalGroup(
            jLayeredPane5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jTextField4, javax.swing.GroupLayout.DEFAULT_SIZE, 162, Short.MAX_VALUE)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jLayeredPane5Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(tppn_nominal)
                .addContainerGap())
        );
        jLayeredPane5Layout.setVerticalGroup(
            jLayeredPane5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jLayeredPane5Layout.createSequentialGroup()
                .addComponent(jTextField4, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(tppn_nominal, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
        );
        jLayeredPane5.setLayer(jTextField4, javax.swing.JLayeredPane.DEFAULT_LAYER);
        jLayeredPane5.setLayer(tppn_nominal, javax.swing.JLayeredPane.DEFAULT_LAYER);

        jLayeredPane6.setBackground(new java.awt.Color(204, 255, 255));
        jLayeredPane6.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jLayeredPane6.setOpaque(true);

        jTextField5.setEditable(false);
        jTextField5.setBackground(new java.awt.Color(153, 153, 153));
        jTextField5.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField5.setForeground(new java.awt.Color(255, 255, 255));
        jTextField5.setText("Ongkos Kirim");
        jTextField5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField5ActionPerformed(evt);
            }
        });

        tongkir.setBackground(new java.awt.Color(204, 255, 255));
        tongkir.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        tongkir.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tongkir.setText("0");
        tongkir.setBorder(null);
        tongkir.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                tongkirCaretUpdate(evt);
            }
        });
        tongkir.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tongkirFocusLost(evt);
            }
        });

        javax.swing.GroupLayout jLayeredPane6Layout = new javax.swing.GroupLayout(jLayeredPane6);
        jLayeredPane6.setLayout(jLayeredPane6Layout);
        jLayeredPane6Layout.setHorizontalGroup(
            jLayeredPane6Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jTextField5, javax.swing.GroupLayout.DEFAULT_SIZE, 162, Short.MAX_VALUE)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jLayeredPane6Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(tongkir)
                .addContainerGap())
        );
        jLayeredPane6Layout.setVerticalGroup(
            jLayeredPane6Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jLayeredPane6Layout.createSequentialGroup()
                .addComponent(jTextField5, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(tongkir, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
        );
        jLayeredPane6.setLayer(jTextField5, javax.swing.JLayeredPane.DEFAULT_LAYER);
        jLayeredPane6.setLayer(tongkir, javax.swing.JLayeredPane.DEFAULT_LAYER);

        setDefaultCloseOperation(javax.swing.WindowConstants.DISPOSE_ON_CLOSE);
        setTitle("Cashier ");

        jPanel1.setPreferredSize(new java.awt.Dimension(940, 510));

        jTable1.setFont(new java.awt.Font("Tahoma", 0, 13)); // NOI18N
        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "No", "Kode", "Nama", "Harga", "Jumlah", "Diskon", "PPN", "Total", "unit", "hargajualasli", "cgroup", "qty_small"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Integer.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Double.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, true, true, true, false, false, false, false, false, false
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }

            public boolean isCellEditable(int rowIndex, int columnIndex) {
                return canEdit [columnIndex];
            }
        });
        jTable1.setRowHeight(40);
        jTable1.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                jTable1FocusLost(evt);
            }
        });
        jTable1.addMouseWheelListener(new java.awt.event.MouseWheelListener() {
            public void mouseWheelMoved(java.awt.event.MouseWheelEvent evt) {
                jTable1MouseWheelMoved(evt);
            }
        });
        jTable1.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                jTable1MouseClicked(evt);
            }
        });
        jTable1.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jTable1PropertyChange(evt);
            }
        });
        jTable1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jTable1KeyPressed(evt);
            }
            public void keyReleased(java.awt.event.KeyEvent evt) {
                jTable1KeyReleased(evt);
            }
            public void keyTyped(java.awt.event.KeyEvent evt) {
                jTable1KeyTyped(evt);
            }
        });
        jScrollPane1.setViewportView(jTable1);
        if (jTable1.getColumnModel().getColumnCount() > 0) {
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(1).setMinWidth(0);
            jTable1.getColumnModel().getColumn(1).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(3).setMinWidth(150);
            jTable1.getColumnModel().getColumn(3).setMaxWidth(150);
            jTable1.getColumnModel().getColumn(4).setMinWidth(80);
            jTable1.getColumnModel().getColumn(4).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(5).setMinWidth(80);
            jTable1.getColumnModel().getColumn(5).setMaxWidth(80);
            jTable1.getColumnModel().getColumn(6).setMinWidth(0);
            jTable1.getColumnModel().getColumn(6).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(7).setMinWidth(150);
            jTable1.getColumnModel().getColumn(7).setMaxWidth(150);
            jTable1.getColumnModel().getColumn(8).setMaxWidth(150);
            jTable1.getColumnModel().getColumn(9).setMinWidth(0);
            jTable1.getColumnModel().getColumn(9).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(10).setMinWidth(0);
            jTable1.getColumnModel().getColumn(10).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(11).setMinWidth(0);
            jTable1.getColumnModel().getColumn(11).setMaxWidth(0);
        }

        jPanel2.setBackground(new java.awt.Color(204, 255, 255));

        jLayeredPane2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        ctran.setBackground(new java.awt.Color(0, 51, 153));
        ctran.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        ctran.setForeground(new java.awt.Color(255, 255, 255));
        ctran.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        ctran.setBorder(null);
        jLayeredPane2.add(ctran, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 260, 30));

        tfKode.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tfKode.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tfKode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusGained(java.awt.event.FocusEvent evt) {
                tfKodeFocusGained(evt);
            }
            public void focusLost(java.awt.event.FocusEvent evt) {
                tfKodeFocusLost(evt);
            }
        });
        tfKode.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tfKodeKeyPressed(evt);
            }
        });
        jLayeredPane2.add(tfKode, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 270, 140, -1));

        jButton3.setText("F2");
        jButton3.setMargin(new java.awt.Insets(0, 0, 0, 0));
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });
        jLayeredPane2.add(jButton3, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 270, 30, -1));

        jLabel10.setText("Bukti SPK ");
        jLayeredPane2.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 160, -1, 20));

        ketKode.setEditable(false);
        ketKode.setBackground(new java.awt.Color(204, 204, 255));
        ketKode.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jLayeredPane2.add(ketKode, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 300, 170, -1));

        jListSatuan.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        jLayeredPane2.add(jListSatuan, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 330, 170, -1));

        jLabel11.setText("Keterangan");
        jLayeredPane2.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 450, 70, 20));

        jLabel14.setText("Jumlah Barang");
        jLayeredPane2.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 360, -1, 20));

        tfJml.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        tfJml.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tfJml.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusGained(java.awt.event.FocusEvent evt) {
                tfJmlFocusGained(evt);
            }
            public void focusLost(java.awt.event.FocusEvent evt) {
                tfJmlFocusLost(evt);
            }
        });
        tfJml.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                tfJmlKeyPressed(evt);
            }
        });
        jLayeredPane2.add(tfJml, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 360, 110, 22));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Ok_15px.png"))); // NOI18N
        jButton1.setText("Add");
        jButton1.setMargin(new java.awt.Insets(2, 1, 2, 1));
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jButton1.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jButton1KeyPressed(evt);
            }
        });
        jLayeredPane2.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 360, 60, 22));

        btProses.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Ok_15px.png"))); // NOI18N
        btProses.setText("Proses (F9)");
        btProses.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btProsesActionPerformed(evt);
            }
        });
        btProses.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btProsesKeyPressed(evt);
            }
        });
        jLayeredPane2.add(btProses, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 410, 260, 30));

        jLabel16.setText("Kode Barang");
        jLayeredPane2.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 270, -1, 20));

        tsj.setEditable(false);
        jLayeredPane2.add(tsj, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 110, 140, -1));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton4ActionPerformed(evt);
            }
        });
        jLayeredPane2.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(240, 110, 30, -1));

        jLabel17.setText("Tanggal");
        jLayeredPane2.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 50, -1, 20));

        jLabel18.setText("Bukti PO");
        jLayeredPane2.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 140, -1, 20));

        tspk.setEditable(false);
        jLayeredPane2.add(tspk, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 160, 170, -1));

        tpo.setEditable(false);
        jLayeredPane2.add(tpo, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 140, 170, -1));

        jLabel19.setText("Kode Customer");
        jLayeredPane2.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 190, 90, 20));

        tcustomerkode.setEditable(false);
        jLayeredPane2.add(tcustomerkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 190, 170, -1));

        tcustomer.setEditable(false);
        jLayeredPane2.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 210, 170, -1));

        jLabel21.setText("Kode");
        jLayeredPane2.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 240, 90, 20));

        jLabel20.setText("Surat Jalan");
        jLayeredPane2.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 110, -1, 20));

        tfakturpajak.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tfakturpajakFocusLost(evt);
            }
        });
        jLayeredPane2.add(tfakturpajak, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 80, 170, -1));

        jLabel23.setText("Satuan");
        jLayeredPane2.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 330, 70, 20));

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

        jLayeredPane2.add(jScrollPane4, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 480, 250, 130));

        jLabel24.setText("Customer");
        jLayeredPane2.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 210, 90, 20));
        jLayeredPane2.add(tkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 240, 50, -1));

        jLabel25.setText("No Faktur Pajak");
        jLayeredPane2.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 80, -1, 20));

        jDateTranSales.setDateFormatString("dd/MM/yyyy");
        jDateTranSales.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateTranSalesPropertyChange(evt);
            }
        });
        jDateTranSales.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateTranSalesKeyPressed(evt);
            }
        });
        jLayeredPane2.add(jDateTranSales, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 50, 170, -1));

        namauser.setEditable(false);
        namauser.setBackground(new java.awt.Color(204, 204, 255));
        namauser.setFocusable(false);

        javax.swing.GroupLayout jPanel2Layout = new javax.swing.GroupLayout(jPanel2);
        jPanel2.setLayout(jPanel2Layout);
        jPanel2Layout.setHorizontalGroup(
            jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel2Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jLayeredPane2)
                    .addComponent(namauser, javax.swing.GroupLayout.Alignment.TRAILING))
                .addContainerGap())
        );
        jPanel2Layout.setVerticalGroup(
            jPanel2Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel2Layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jLayeredPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 645, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(namauser, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addContainerGap())
        );

        iTotalSementara.setEditable(false);
        iTotalSementara.setBackground(new java.awt.Color(0, 51, 153));
        iTotalSementara.setFont(new java.awt.Font("Tahoma", 1, 36)); // NOI18N
        iTotalSementara.setForeground(new java.awt.Color(255, 255, 255));
        iTotalSementara.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        iTotalSementara.setText("0");

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(153, 153, 153));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField1.setForeground(new java.awt.Color(255, 255, 255));
        jTextField1.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        jTextField1.setText("Klik 3x Untuk Menghapus Data atau Tekan Tombol Delete");
        jTextField1.setFocusable(false);
        jTextField1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField1ActionPerformed(evt);
            }
        });

        javax.swing.GroupLayout jPanel1Layout = new javax.swing.GroupLayout(jPanel1);
        jPanel1.setLayout(jPanel1Layout);
        jPanel1Layout.setHorizontalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel1Layout.createSequentialGroup()
                .addGap(30, 30, 30)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jTextField1)
                    .addComponent(iTotalSementara)
                    .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 826, Short.MAX_VALUE))
                .addGap(30, 30, 30))
        );
        jPanel1Layout.setVerticalGroup(
            jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, jPanel1Layout.createSequentialGroup()
                .addGap(30, 30, 30)
                .addGroup(jPanel1Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.TRAILING)
                    .addGroup(jPanel1Layout.createSequentialGroup()
                        .addComponent(iTotalSementara, javax.swing.GroupLayout.PREFERRED_SIZE, 70, javax.swing.GroupLayout.PREFERRED_SIZE)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                        .addComponent(jScrollPane1)
                        .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                        .addComponent(jTextField1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                    .addComponent(jPanel2, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addGap(20, 20, 20))
        );

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 1186, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 743, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void jTable1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jTable1PropertyChange
//        change++;
//        System.out.println("Chace "+change);
        
        try {
            koreksi();
        } catch (Exception e) {}

        try {
            callculation();
        } catch (Exception e) {}
    }//GEN-LAST:event_jTable1PropertyChange

    private void jTable1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTable1KeyPressed
        switch (evt.getKeyCode()) {
            case 10:
            koreksi();
            callculation();
            break;
            case KeyEvent.VK_DELETE:
            try {
                DefaultTableModel model = (DefaultTableModel) jTable1.getModel();

                int baris = jTable1.getSelectedRow();
                model.removeRow(baris);
                callculation();
            } catch (Exception e) {}
            break;
            default:
            break;
        }

    }//GEN-LAST:event_jTable1KeyPressed

    private void tfKodeFocusGained(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfKodeFocusGained
        tfKode.setBackground(Color.BLUE);
        tfKode.setForeground(Color.WHITE);
    }//GEN-LAST:event_tfKodeFocusGained

    private void tfKodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfKodeFocusLost
        
        tfKode.setBackground(Color.WHITE);
        tfKode.setForeground(Color.BLACK);
        tfKode.setText(tfKode.getText().toUpperCase());
    }//GEN-LAST:event_tfKodeFocusLost

    private void tfKodeKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tfKodeKeyPressed
        if (evt.getKeyCode()==10){

            harga = 0.0;
            diskon = 0.0;
            unitBarang = "";
            if(!tfKode.getText().isEmpty()) {
                sql ="select * from MProduk where cproduct = '"+tfKode.getText()+"'";
                rs = con.queryResult(sql);
                try {
                    while(rs.next()){
                        ketKode.setText(rs.getString("cnameProduct").toString());
                        harga=rs.getDouble("NPrSell"); 
                        diskon=0.0;
                        
                        unitBarang = rs.getString("unit").toString();
                    }
                } catch (SQLException ex) {
                    Logger.getLogger(CashierFull.class.getName()).log(Level.SEVERE, null, ex);
                }
//                System.out.println(harga);
                if(ketKode.getText().isEmpty()) {
                    JOptionPane.showMessageDialog(this, "Kode Barang tidak Terdaftar","Warning",JOptionPane.INFORMATION_MESSAGE);
                    tfKode.requestFocus();
                }
            }

            if(tfKode.getText().isEmpty())  btProses.requestFocus();
            else                            tfJml.requestFocus();
        }
        
    }//GEN-LAST:event_tfKodeKeyPressed

    private void tfJmlFocusGained(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfJmlFocusGained
        tfJml.setBackground(Color.BLUE);
        tfJml.setForeground(Color.WHITE);
        tfJml.setText("1");
    }//GEN-LAST:event_tfJmlFocusGained

    private void tfJmlFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfJmlFocusLost
        tfJml.setBackground(Color.WHITE);
        tfJml.setForeground(Color.BLACK);        // TODO add your handling code here:
    }//GEN-LAST:event_tfJmlFocusLost

    private void tfJmlKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tfJmlKeyPressed
        if (evt.getKeyCode()==10){
            beli();
            jListSatuan.removeAll();
            jListSatuan.removeAllItems();
            satuanNilai = new ArrayList<>();
            satuanNilai.clear();
        }
    }//GEN-LAST:event_tfJmlKeyPressed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        beli();   
        jListSatuan.removeAll();
        jListSatuan.removeAllItems();
        satuanNilai = new ArrayList<>();
        satuanNilai.clear();
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jButton1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jButton1KeyPressed
       
    }//GEN-LAST:event_jButton1KeyPressed

    private void tfBayarFocusGained(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfBayarFocusGained
        tfBayar.setBackground(Color.BLUE);
        tfBayar.setForeground(Color.WHITE);

        //callculationEnd();

    }//GEN-LAST:event_tfBayarFocusGained

    private void tfBayarFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfBayarFocusLost
        tfBayar.setBackground(Color.WHITE);
        tfBayar.setForeground(Color.BLACK);
        if(!tfBayar.getText().isEmpty()) iBayar.setText(dFormat.format(Double.valueOf(tfBayar.getText())));
    }//GEN-LAST:event_tfBayarFocusLost

    private void tfBayarKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tfBayarKeyPressed
        if (evt.getKeyCode()==10) btProses.requestFocus();

        
    }//GEN-LAST:event_tfBayarKeyPressed

    private void btProsesActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btProsesActionPerformed
        if(ongkosMesin) {
            JOptionPane.showMessageDialog(this,"Apabila invoice untuk keperluan ONGKOS MESIN, maka isi WO sesuai dengan invoice yang akan dibuat", "Info", JOptionPane.ERROR_MESSAGE);
            
        }
        if(tkode.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Kode Wajib di-isi", "Info", JOptionPane.ERROR_MESSAGE);
            return;
        }
        //idCreated();
        callculationEnd();
        jTable1.editingCanceled(null);
        jTable1.editingCanceled(new ChangeEvent (this));
        jTable1.clearSelection();
        
        jTable1.requestFocus();
        tfKode.requestFocus();
        if(con.akses(akses, rfa.INSERT)) {
            
            saving();   
            clearVariable();
            
        }
        
    }//GEN-LAST:event_btProsesActionPerformed

    private void btProsesKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btProsesKeyPressed
        if (evt.getKeyCode()==10) saving();

       
    }//GEN-LAST:event_btProsesKeyPressed

    private void tfMemberFocusGained(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfMemberFocusGained
        tfMember.setBackground(Color.BLUE);
        tfMember.setForeground(Color.WHITE);        // TODO add your handling code here:
    }//GEN-LAST:event_tfMemberFocusGained

    private void tfMemberFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfMemberFocusLost
        tfMember.setBackground(Color.WHITE);
        tfMember.setForeground(Color.BLACK);        // TODO add your handling code here:
    }//GEN-LAST:event_tfMemberFocusLost

    private void tfMemberKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tfMemberKeyPressed
        if (evt.getKeyCode()==10) {
            if(tfMember.getText().isEmpty()) {
                tfBayar.requestFocus();
            } else {

            }
        }
        
    }//GEN-LAST:event_tfMemberKeyPressed

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        loadBarangF2();
    }//GEN-LAST:event_jButton3ActionPerformed

    private void jTextField1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField1ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField1ActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        
        if(evt.getClickCount()==3) {
            int pil = JOptionPane.showConfirmDialog(this, "Apakah Anda Yakin Akan Membatalkan Pesanan " + jTable1.getValueAt(jTable1.getSelectedRow(), 2).toString() + "?", "Delete Data", JOptionPane.YES_NO_OPTION, JOptionPane.QUESTION_MESSAGE);
            if (pil == 0) {
                try {
                    DefaultTableModel model = (DefaultTableModel) jTable1.getModel();

                    int baris = jTable1.getSelectedRow();
                    model.removeRow(baris);
                    callculation();
                } catch (Exception e) {}
                
                if(jTable1.getRowCount() == 0) {
                    ongkosMesin = false;
                }
            }
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void jTable1FocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_jTable1FocusLost
//        System.out.println("where");
        Object source = evt.getSource();
        if (!jTable1.equals(source)) {
          System.out.println("Terjadi Perubahan data");
          jTable1.editingCanceled(new ChangeEvent(source));
          jTable1.clearSelection();
        }
    }//GEN-LAST:event_jTable1FocusLost

    private void jTable1MouseWheelMoved(java.awt.event.MouseWheelEvent evt) {//GEN-FIRST:event_jTable1MouseWheelMoved
        
    }//GEN-LAST:event_jTable1MouseWheelMoved

    private void jTable1KeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTable1KeyTyped
        
    }//GEN-LAST:event_jTable1KeyTyped

    private void jTable1KeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jTable1KeyReleased
//        System.out.println("Ngetik");
    }//GEN-LAST:event_jTable1KeyReleased

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        idCreated();
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton4ActionPerformed
        DialogDelivery a = new DialogDelivery();
        a.setVisible(true);
        if(a.status) {
            tsj.setText(a.buktisj);
            tpo.setText(a.buktipo);
            tspk.setText(a.buktispk); 
            tcustomerkode.setText(a.kodecustomer);
            tcustomer.setText(a.customer);
            
            if(!tpo.getText().isEmpty()) {
                noinv = tsj.getText().substring(0, 2)+"."+
                        tpo.getText().subSequence(0, 3)+
                        "/INV/UG-RMS/"+
                        con.romawi(rfa.DateString(keyssDef,new Date(),"MM"))+"/"+
                        rfa.DateString(keyssDef,new Date(),"YY");
            
            } else {
                noinv = tsj.getText().substring(0, 2)+""+
                        "/INV/UG-RMS/"+
                        con.romawi(rfa.DateString(keyssDef,new Date(),"MM"))+"/"+
                        rfa.DateString(keyssDef,new Date(),"YY");
            }
            ctran.setText(noinv);
            
            sql="select a.cgroup,a.cproduct,case when isnull(a.produkalias,'') = '' then b.cnameProduct else a.produkalias end as cnameProduct,"
                    + "sum(a.nqty_small) as nqty,a.csatuan_small From TranDeliveryOrderD  a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct \n" +
                "where a.ccode='"+a.ccode+"' and a.cnbkt='"+a.buktisj+"' \n"+
                    "group by a.cgroup,a.cproduct,b.cnameProduct,a.csatuan_small,a.produkalias ";
            System.out.println(sql);
            rs = con.queryResult(sql);
            rfa.clear_table(MenuAdmin.keyssDef, jTable1); 
            DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
            try {
                while(rs.next()) {
                    model.addRow(new Object[]{jTable1.getRowCount()+1,
                        rs.getString("cproduct"),
                        rs.getString("cnameproduct"),
                        0.0,rs.getDouble("nqty"),0.0,Double.valueOf(tppn.getText()),0.0,rs.getString("csatuan_small"),0.0,
                        rs.getString("cgroup"),
                        rs.getDouble("nqty"),
                    });
                }
            } catch (SQLException ex) {
                Logger.getLogger(CashierFull.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            
        }
    }//GEN-LAST:event_jButton4ActionPerformed

    private void tppnKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tppnKeyPressed
        
    }//GEN-LAST:event_tppnKeyPressed

    private void jTextField3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField3ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField3ActionPerformed

    private void jTextField4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField4ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField4ActionPerformed

    private void jTextField5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField5ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField5ActionPerformed

    private void iDiscFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_iDiscFocusLost
        iDisc.setText(dFormat.format(Double.valueOf(iDisc.getText().replaceAll(",", ""))));
        
    }//GEN-LAST:event_iDiscFocusLost

    private void tongkirFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tongkirFocusLost
        iDisc.setText(dFormat.format(Double.valueOf(iDisc.getText().replaceAll(",", ""))));
        
    }//GEN-LAST:event_tongkirFocusLost

    private void iDiscCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_iDiscCaretUpdate
        try {
            koreksi();
        } catch (Exception e) {}

        try {
            callculation();
        } catch (Exception e) {}
    }//GEN-LAST:event_iDiscCaretUpdate

    private void tongkirCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tongkirCaretUpdate
        try {
            koreksi();
        } catch (Exception e) {}

        try {
            callculation();
        } catch (Exception e) {}
    }//GEN-LAST:event_tongkirCaretUpdate

    private void tppnCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tppnCaretUpdate
//         try {
//            koreksi();
//        } catch (Exception e) {}
//
//        try {
//            callculation();
//        } catch (Exception e) {}
    }//GEN-LAST:event_tppnCaretUpdate

    private void tfakturpajakFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tfakturpajakFocusLost
        tfakturpajak.setText(tfakturpajak.getText().toUpperCase());
    }//GEN-LAST:event_tfakturpajakFocusLost

    private void tppn_nominalCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tppn_nominalCaretUpdate
        // TODO add your handling code here:
    }//GEN-LAST:event_tppn_nominalCaretUpdate

    private void tppn_nominalFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tppn_nominalFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tppn_nominalFocusLost

    private void tketFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tketFocusLost
      //  tket.setText(tket.getText().toUpperCase());
    }//GEN-LAST:event_tketFocusLost

    private void tketKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyPressed
        
    }//GEN-LAST:event_tketKeyPressed

    private void tketKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tketKeyTyped

    }//GEN-LAST:event_tketKeyTyped

    private void jDateTranSalesPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranSalesPropertyChange
  
    }//GEN-LAST:event_jDateTranSalesPropertyChange

    private void jDateTranSalesKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranSalesKeyPressed

    }//GEN-LAST:event_jDateTranSalesKeyPressed

    /**
     * @param args the command line arguments
     */
    public static void main(String args[]) {
        /* Set the Nimbus look and feel */
        //<editor-fold defaultstate="collapsed" desc=" Look and feel setting code (optional) ">
        /* If Nimbus (introduced in Java SE 6) is not available, stay with the default look and feel.
         * For details see http://download.oracle.com/javase/tutorial/uiswing/lookandfeel/plaf.html 
         */
        try {
            for (javax.swing.UIManager.LookAndFeelInfo info : javax.swing.UIManager.getInstalledLookAndFeels()) {
                if ("Nimbus".equals(info.getName())) {
                    javax.swing.UIManager.setLookAndFeel(info.getClassName());
                    break;
                }
            }
        } catch (ClassNotFoundException ex) {
            java.util.logging.Logger.getLogger(CashierFull.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        } catch (InstantiationException ex) {
            java.util.logging.Logger.getLogger(CashierFull.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        } catch (IllegalAccessException ex) {
            java.util.logging.Logger.getLogger(CashierFull.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        } catch (javax.swing.UnsupportedLookAndFeelException ex) {
            java.util.logging.Logger.getLogger(CashierFull.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        }
        //</editor-fold>

        /* Create and display the form */
        java.awt.EventQueue.invokeLater(new Runnable() {
            public void run() {
                new CashierFull().setVisible(true);
            }
        });
    }

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btProses;
    private javax.swing.JComboBox cbSkat;
    private javax.swing.JTextField ctran;
    private javax.swing.JLabel iBayar;
    private javax.swing.JTextField iDisc;
    private javax.swing.JLabel iKembali;
    private javax.swing.JLabel iSubTotal;
    private javax.swing.JLabel iTotal;
    private javax.swing.JTextField iTotalSementara;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton4;
    public static com.toedter.calendar.JDateChooser jDateTranSales;
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
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JLayeredPane jLayeredPane1;
    private javax.swing.JLayeredPane jLayeredPane2;
    private javax.swing.JLayeredPane jLayeredPane3;
    private javax.swing.JLayeredPane jLayeredPane4;
    private javax.swing.JLayeredPane jLayeredPane5;
    private javax.swing.JLayeredPane jLayeredPane6;
    private javax.swing.JComboBox jListSatuan;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JSeparator jSeparator1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JTextField jTextField2;
    private javax.swing.JTextField jTextField3;
    private javax.swing.JTextField jTextField4;
    private javax.swing.JTextField jTextField5;
    private javax.swing.JTextField ketKode;
    private javax.swing.JTextField namauser;
    public static javax.swing.JTextField tcustomer;
    public static javax.swing.JTextField tcustomerkode;
    private javax.swing.JTextField tfBayar;
    private javax.swing.JTextField tfJml;
    private javax.swing.JTextField tfKode;
    private javax.swing.JTextField tfMember;
    private javax.swing.JTextField tfakturpajak;
    private javax.swing.JTextArea tket;
    private javax.swing.JTextField tkode;
    private javax.swing.JTextField tongkir;
    private javax.swing.JTextField tpo;
    private javax.swing.JTextField tppn;
    private javax.swing.JTextField tppn_nominal;
    private javax.swing.JTextField tproduk1;
    private javax.swing.JTextField tsj;
    private javax.swing.JTextField tspk;
    // End of variables declaration//GEN-END:variables
}
