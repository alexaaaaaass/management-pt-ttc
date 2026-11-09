/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Report;

import DialogPublicTP.DialogGudang;
import static Main.DefaultProgram.deffProgram;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Printer.PrinterUtility;
import Tools.ConnectDB;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.table.DefaultTableModel;
import net.sf.jasperreports.engine.JRDataSource;
import net.sf.jasperreports.engine.JRException;
import net.sf.jasperreports.engine.JasperCompileManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRTableModelDataSource;
import net.sf.jasperreports.view.JasperViewer;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;
import sun.nio.cs.ext.IBM037;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class RAnalisaPenjualan extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String idKas="B1";
    ArrayList<String> deleteRow;
    DecimalFormat dFormat = new DecimalFormat("#,##0.00");
    int nomor = 0;
    Double TotalKWT=0.0, TotalBayar=0.0, TotalSisa=0.0, TotalKembalian=0.0, TotalKurang=0.0;
    public RAnalisaPenjualan() {
        initComponents();
        jScrollPane1.setVisible(false);
        jScrollPane2.setVisible(false);
        jDateChooser1.setDate(Calendar.getInstance().getTime());
        jDateChooser2.setDate(Calendar.getInstance().getTime());
        
        tcode.setText(deffProgram.get("ccode"));
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  
            tcode3.setText(rs.getString("cname"));
        } } catch (SQLException ex) { ex.printStackTrace();   }
    }

    private void dataPenjualan(int mode){
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.getDataVector().removeAllElements();
        jTable1.removeAll();
        
        String wherekode="";
        if(kodeineks.getSelectedIndex() > 0) wherekode+=" and aa.kode='"+kodeineks.getSelectedIndex()+"' ";
        if(kodeppn.getSelectedIndex() > 0) {
            if(kodeppn.getSelectedIndex() == 1) wherekode+=" and isnull(aa.ppn,0) = 0 ";
            if(kodeppn.getSelectedIndex() == 2) wherekode+=" and isnull(aa.ppn,0) > 0  ";
        }
        
        if(mode == 0) {
            sql = "SELECT '1' as urut,a.cworkorder,a.cnbkt,a.cnbkt as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga],a.[diskon_barang],a.diskon_member,\n" +
                "a.total,a.bayar+(select isnull(sum(z.cicilan),0) from transalebonpay z  where z.ccode=a.ccode and z.cnbkt = a.cnbkt) as bayar,\n" +
                "a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "inner join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt not like '%R%' "+wherekode+" and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "union all SELECT '2' as urut,a.cworkorder,a.cnbkt,right(a.cnbkt,13) as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga]*-1,a.[diskon_barang],a.diskon_member,a.total,a.bayar ,a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "inner join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt like '%R-%' "+wherekode+"  and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "order by kodetranurut,urut,dtran";
        } else if(mode == 1) {
            sql = "SELECT '1' as urut,a.cworkorder,a.cnbkt,a.cnbkt as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga],a.[diskon_barang],a.diskon_member,\n" +
                "a.total,a.bayar+(select isnull(sum(z.cicilan),0) from transalebonpay z  where z.ccode=a.ccode and z.cnbkt = a.cnbkt) as bayar,\n" +
                "a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt not like '%R-%' "+wherekode+"  and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "order by kodetranurut,urut,dtran";
        } else if(mode == 2) {
            sql = "SELECT '2' as urut,a.cworkorder,a.cnbkt,right(a.cnbkt,13) as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga]*-1 as harga,a.[diskon_barang],a.diskon_member,a.total,a.bayar ,a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "inner join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt like '%R-%' "+wherekode+"  and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "order by kodetranurut,urut,dtran";
        } else if(mode == 3) {
            sql = "SELECT '1' as urut,a.cworkorder,a.cnbkt,a.cnbkt as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga],a.[diskon_barang],a.diskon_member,\n" +
                "a.total,a.bayar+(select isnull(sum(z.cicilan),0) from transalebonpay z  where z.ccode=a.ccode and z.cnbkt = a.cnbkt) as bayar,\n" +
                "a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "inner join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt not like '%R-%' "+wherekode+" and a.id_member = '"+idMember.getText().replaceAll(" ", "")+"' and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "union all SELECT '2' as urut,a.cworkorder,a.cnbkt,right(a.cnbkt,13) as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga]*-1,a.[diskon_barang],a.diskon_member,a.total,a.bayar ,a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "inner join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt like '%R-%' "+wherekode+"  and a.id_member = '"+idMember.getText().replaceAll(" ", "")+"'  and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "order by kodetranurut,urut,dtran";
        } else if(mode == 4) {
            sql = "SELECT '1' as urut,a.cworkorder,a.cnbkt,a.cnbkt as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga],a.[diskon_barang],a.diskon_member,\n" +
                "a.total,a.bayar+(select isnull(sum(z.cicilan),0) from transalebonpay z  where z.ccode=a.ccode and z.cnbkt = a.cnbkt) as bayar,\n" +
                "a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "inner join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt not like '%R-%' "+wherekode+"  and a.id_member = '"+idMember.getText().replaceAll(" ", "")+"'  and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "order by kodetranurut,urut,dtran";
            
        } else if(mode == 5) {
            sql = "SELECT '2' as urut,a.cworkorder,a.cnbkt,right(a.cnbkt,13) as kodetranurut,a.cproduct as id_barang,b.cnameProduct as nama,a.cusername as username\n" +
                ",a.dtran,a.jumlah,a.[harga]*-1 as harga,a.[diskon_barang],a.diskon_member,a.total,a.bayar ,a.kembali,a.id_member,f.cname as namamember\n" +
                "FROM TranSale a join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt\n" +
                "inner join MProduk b on a.cproduct = b.cproduct \n" +
                "left join MCustomer f on a.id_member = f.kode_customer \n" +
                "where  a.cnbkt like '%R-%' "+wherekode+"  and a.id_member = '"+idMember.getText().replaceAll(" ", "")+"'  and (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"')\n" +
                "\n" +
                "order by kodetranurut,urut,dtran";
    
        }
        
        System.out.println(sql.toLowerCase());
        String CekID = "";
        Double HitungTotalPerKwt = 0.0, HitungTotalKwtFinal=0.0, Bayar=0.0, DiskonMember=0.0;
        TotalKembalian = TotalKurang = TotalKWT=0.0; TotalBayar=0.0; TotalSisa=0.0;
        int ic=0;
        rs = con.queryResult(sql.toLowerCase());
        try {
            while(rs.next()){
                if(ic==0) {
                    CekID = checkString(rs.getString("kodetranurut"));
                }
                
                if(!CekID.equals(checkString(rs.getString("kodetranurut")))) {
                    HitungTotalKwtFinal =  (HitungTotalPerKwt-(HitungTotalPerKwt *(DiskonMember/100)));
                    TotalKWT = TotalKWT+HitungTotalKwtFinal;
                    TotalBayar = TotalBayar+Bayar;
                    TotalSisa = TotalSisa+(HitungTotalKwtFinal-Bayar);
                    
//                    System.out.println(HitungTotalKwtFinal+"_"+Bayar+"_"+(HitungTotalKwtFinal-Bayar));
                    if((HitungTotalKwtFinal-Bayar)<0) {
                        TotalKembalian = TotalKembalian + (HitungTotalKwtFinal-Bayar);
//                        System.out.println(HitungTotalKwtFinal-Bayar);
                    } else {
                        TotalKurang = TotalKurang + (HitungTotalKwtFinal-Bayar);
                    }
                    
                    DiskonMember = HitungTotalPerKwt = Bayar = 0.0;
                    CekID = checkString(rs.getString("kodetranurut"));
                    
                    
                    
                }
                
                Double hithargaitem = (checkDouble(rs.getString("harga"))-(checkDouble(rs.getString("harga")) *(checkDouble(rs.getString("diskon_barang"))/100)))* checkDouble(rs.getString("jumlah"));
                HitungTotalPerKwt = HitungTotalPerKwt + hithargaitem;
                Bayar = Math.max(Bayar,checkDouble(rs.getString("bayar")));
                
//                System.out.println(DiskonMember+"_"+rs.getStringDouble("diskon_member"));
             
                DiskonMember = Math.max(DiskonMember,checkDouble(rs.getString("diskon_member")));
                
                
                
                String status = "Terjual";
                if(checkString(rs.getString("urut")).equals("2")) {
                    status = "Return";
                } 
                
                
                model.addRow(new Object[]{status,checkString(rs.getString("cnbkt")),
                    checkString(rs.getString("kodetranurut")),
                    checkString(rs.getString("id_barang")),
                    checkString(rs.getString("nama")),
                    checkString(rs.getString("username")),
                    checkString(rs.getString("dtran")),
                    checkDouble(rs.getString("jumlah")),
                    checkDouble(rs.getString("harga")),
                    checkDouble(rs.getString("diskon_barang")),DiskonMember,
                    checkDouble(rs.getString("total")),
                    checkDouble(rs.getString("bayar")),
                    checkDouble(rs.getString("kembali")),hithargaitem,
                    checkString(rs.getString("id_member")),
                    checkString(rs.getString("namamember")),
                    checkString(rs.getString("cworkorder")),
                        });

                ic++;
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaPenjualan.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        HitungTotalKwtFinal =  (HitungTotalPerKwt-(HitungTotalPerKwt *(DiskonMember/100)));
        TotalKWT = TotalKWT+HitungTotalKwtFinal;
        TotalBayar = TotalBayar+Bayar;
        TotalSisa = TotalSisa+(HitungTotalKwtFinal-Bayar);
        
        if((HitungTotalKwtFinal-Bayar)<0) {
            TotalKembalian = TotalKembalian + (HitungTotalKwtFinal-Bayar);
        } else {
            TotalKurang = TotalKurang + (HitungTotalKwtFinal-Bayar);
        }

//        System.out.println(HitungTotalKwtFinal+"_"+Bayar+"_"+(HitungTotalKwtFinal-Bayar));
//        System.out.println("============================");
//        System.out.println(TotalKWT+"_"+TotalBayar+"_"+TotalSisa);
        System.out.println("============================");
        System.out.println(TotalKembalian+" ==== "+TotalKurang);
        HitungTotalPerKwt = Bayar = 0.0;
        
        
        print();
    }
    private void print(){
        String nama="",alamat="";
//        rs = con.queryResult("select * from mtoko");
//        if(rs != null && rs.size()>0) {
//            for(int i=0;i<rs.size();++i) {
//                JSONObject obj=(JSONObject)rs.get(i);
//                //System.out.println(rs.getString("username"));
//                nama=(rs.getString("toko").toString());
//                alamat=(rs.getString("alamat").toString());
//                
//            }
//        }
        
        
        try {
            JRDataSource dataSource = new JRTableModelDataSource(jTable1.getModel());
            String treport = "report/rpenjualanfull.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("toko", nama);
            param.put("alamattoko", alamat);
            param.put("periode", "Periode "+new SimpleDateFormat("dd MMMMM yyyy").format(jDateChooser1.getDate())+" s/d "+new SimpleDateFormat("dd MMMMM yyyy").format(jDateChooser2.getDate()));
            
            param.put("TotalKes", TotalKWT);
            param.put("BayarKes", TotalBayar);
            param.put("SisaKes", TotalSisa);
            
            param.put("uangkembalian", TotalKembalian*-1);
            param.put("sisakekurangan", TotalKurang);
            
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false); 
        } catch (JRException e) {
            System.out.println(e);
        }
    }
    
    private void dataBon(){
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        model.getDataVector().removeAllElements();
        jTable2.removeAll();
        
        String wherekode="";
        if(kodeineks.getSelectedIndex() > 0) wherekode+=" and aa.kode='"+kodeineks.getSelectedIndex()+"' ";
        if(kodeppn.getSelectedIndex() > 0) {
            if(kodeppn.getSelectedIndex() == 1) wherekode+=" and isnull(aa.ppn,0) = 0 ";
            if(kodeppn.getSelectedIndex() == 2) wherekode+=" and isnull(aa.ppn,0) > 0  ";
        }
        
        sql = "select distinct a.cnbkt,aa.cnbkt_order, a.id_member,a.dtran,a.total,a.kembali,a.KodeBayar,b.lamatempo,b.akhirtempo,\n" +
            "(a.bayar+(select iSnull(sum(cicilan),0) from TranSaleBonPay where cnbkt = a.cnbkt)) as TotalDibayar,\n" +
            "a.total - (a.bayar+(select iSnull(sum(cicilan),0) from TranSaleBonPay where cnbkt = a.cnbkt)) as SisaCicilan,c.cname as name\n" +
            "from TranSale a  join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt left join TranSaleBon  b on a.cnbkt = b.cnbkt join MCustomer c on a.id_member = c.kode_customer\n" +
            "and a.total - (a.bayar+(select iSnull(sum(cicilan),0) from TranSaleBonPay where cnbkt = a.cnbkt)) > 0\n" +
            "and a.id_member like '%"+idMember.getText()+"%' "+wherekode;
        System.out.println(sql);
        rs = con.queryResult(sql.toLowerCase());
        int i=1;
        try {
            while(rs.next()){
                model.addRow(new Object[]{checkString(rs.getString("cnbkt")).replaceAll("INV/",""),checkString(rs.getString("id_member")),checkString(rs.getString("name")),
                rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                checkString(rs.getString("lamatempo")),
                rfa.DateString(keyssDef, rs.getDate("akhirtempo"),"dd/MM/yyyy"), 
                checkDouble(rs.getString("total")),checkDouble(rs.getString("TotalDibayar")),checkDouble(rs.getString("SisaCicilan")),i++,
                rs.getString("cnbkt_order"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaPenjualan.class.getName()).log(Level.SEVERE, null, ex);
        }
//        try {
//            while(rs.next()) {
//                model.addRow(new Object[]{rs.getString("cnbkt"),rs.getString("id_member"),rs.getString("name"),
//                rs.getString("dtran"),rs.getString("lamatempo"),rs.getString("akhirtempo"),
//                rs.getDouble("total"),rs.getDouble("TotalDibayar"),rs.getDouble("SisaCicilan"),i++});
//            }
//        } catch (SQLException ex) {
//            Logger.getLogger(RAnalisaPenjualan.class.getName()).log(Level.SEVERE, null, ex);
//        }
        
        printBon();
    }
    
    private void printBon(){
        String nama="",alamat="";
        
        try {
            JRDataSource dataSource = new JRTableModelDataSource(jTable2.getModel());
            String treport = "report/rekapbon.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("toko", nama);
            param.put("alamattoko", alamat);
            param.put("periode", "Periode "+new SimpleDateFormat("dd MMMMM yyyy").format(jDateChooser1.getDate())+" s/d "+new SimpleDateFormat("dd MMMMM yyyy").format(jDateChooser2.getDate()));
         
            
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false); 
        } catch (JRException e) {
            System.out.println(e);
        }
    }
    
    private void detailBon(){
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        model.getDataVector().removeAllElements();
        jTable3.removeAll();
        
        sql="select a.*,b.MetodeBayar,bb.cname as customer from (\n" +
            "	select 0 as urut,a.ccode,a.cnbkt,a.id_member,a.dtran,a.total,a.bayar,a.kodebayar\n" +
            "	from (select distinct ccode,cnbkt,id_member,dtran,total,bayar,kodebayar from transale where left(cnbkt,1) != 'R') a\n" +
            "	union all\n" +
            "	select 1 as urut,a.ccode,a.cnbkt,a.id_member,b.dtran,a.total,b.cicilan as bayar,b.kodebayar\n" +
            "	from (select distinct ccode,cnbkt,id_member,dtran,total,bayar from transale where left(cnbkt,1) != 'R') a \n" +
            "	join (\n" +
            "		select * from TranSaleBonPay\n" +
            "	) b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            ") a \n" +
            "join MCustomer bb on a.id_member=bb.kode_customer\n" +
            "join MMetodePembayaran b on a.kodebayar=b.KodeBayar\n" +
            "where a.ccode='"+tcode.getText()+"'\n";
        if(!idMember.getText().isEmpty()) sql+=" and a.id_member='"+idMember.getText()+"' ";
        sql+="order by a.id_member,a.cnbkt,a.urut,a.dtran";
        
        rs = con.queryResult(sql);
        int i=1;
        Double sisa=0.0,bayar=0.0,nilaiinv=0.0;
        try {
            while(rs.next()){
                if(rs.getInt("urut") == 0) {
                    bayar = rs.getDouble("bayar");
                    sisa = rs.getDouble("total")-bayar;
                    nilaiinv =rs.getDouble("total");
                } else {
                    bayar+= rs.getDouble("bayar");
                    sisa = rs.getDouble("total")-bayar;
                    nilaiinv=0.0;
                }
                model.addRow(new Object[]{
                    rs.getString("cnbkt"),rs.getString("id_member"),
                    rfa.DateString(keyssDef, rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getDouble("total"),rs.getDouble("bayar"),sisa,
                    rs.getString("kodebayar"),rs.getString("metodebayar"),rs.getString("customer"),
                    nilaiinv
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaPenjualan.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTable3.getModel());
            String treport = "report/rpenjualanbondetail.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", "");
            param.put("dtran2", "");
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private void rincian(){
        DefaultTableModel model = (DefaultTableModel) jTablerincianjual.getModel();
        rfa.clear_table(keyssDef, jTablerincianjual);
        sql = "select a.dtran,a.cgroup,b.cproduct,b.cnameProduct,a.jumlah,a.harga,a.total,a.cunit \n" +
            "From TranSale a join MProduk b on a.cgroup=b.cgroup and a.cproduct=b.cproduct\n" +
            "where a.ccode = '"+tcode.getText()+"' and  (a.dtran >= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate())+"' and a.dtran <= '"+new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate())+"') \n" +
            "order by a.cgroup,a.cproduct,a.dtran\n" +
                "";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                model.addRow(new Object[]{
                    new SimpleDateFormat("dd/MM/yyyy").format(rs.getDate("dtran")),
                    rs.getString("cproduct"),
                    rs.getString("cnameproduct"),
                    rs.getDouble("jumlah"),
                    rs.getDouble("harga"),
                    rs.getDouble("total"),
                    rs.getString("cunit"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaPenjualan.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(jTablerincianjual.getModel());
            String treport = "report/RPenjualanDetail.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "BUKTI BANK MASUK");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser1.getDate()));
            param.put("dtran2", new SimpleDateFormat("yyyy-MM-dd").format(jDateChooser2.getDate()));
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private String checkString(Object in) {
        String cek = "";
        if(in != null) {
            cek = in.toString();
        }
        return cek;
    }
     private Double checkDouble(Object in) {
        Double cek = 0.0;
        if(in != null) {
            cek = Double.valueOf(in.toString());
        }
        return cek;
    }
    
    private void invoicedelivery(int status){
        String dtran1=rfa.DateString(keyssDef, jDateChooser1.getDate(),"yyyy-MM-dd");
        String dtran2=rfa.DateString(keyssDef, jDateChooser2.getDate(),"yyyy-MM-dd");
        
        sql="select a.id,a.ccode,d.cname as gudang,a.cnbkt as inv,isnull(b.cname,'') as customer,a.dtran,\n" +
            "a.cproduct,a.namaproduk,a.jumlah,a.cunit,\n" +
            "e.cname as logistik,c.cproduct,c.cnameProduct as produklogistik,c.dtran as tglkirim,c.nqty as qtykirim,c.csatuan as satuankirim\n" +
            "from TranSale a  join TranSaleHeader aa on a.ccode=aa.ccode and a.cnbkt=aa.cnbkt --join MProdukJual aa on a.cgroup=aa.cgroup and a.cproduct=aa.cproduct\n" +
            "left join MCustomer b on a.id_member=b.kode_customer\n" +
            "left join (\n" +
            "	select b.id,a.ccode,a.cnbkt,a.dtran,a.cvia,a.csupir,a.cnopol,a.cnote,b.cgroup,b.cproduct,c.cnameProduct,b.nqty,b.csatuan\n" +
            "	from TranDeliveryOrder a join TranDeliveryOrderD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "	join MProduk c on b.cgroup=c.cgroup and b.cproduct=c.cproduct\n" +
            ") c on aa.cnbkt_delivery=c.cnbkt and a.cgroup=c.cgroup and a.cproduct=c.cproduct\n" +
            "join MGudang d on a.ccode=d.ccode \n" +
            "left join MGudang e on c.ccode=e.ccode \n" +
            "where a.ccode='"+tcode.getText()+"' and a.dtran>='"+dtran1+"' and a.dtran<='"+dtran2+"'\n";
        if(status == 0) {
            // Terikirim
            //sql+=" and c.cproduct is not null ";
        }
        if(status == 1) {
            // belum terkirim
            sql+=" and c.cproduct is null ";
        }   
        if(!idMember.getText().isEmpty()) {
            // belum terkirim
            sql+=" and a.id_member='"+idMember.getText()+"' ";
        }         
        sql+="order by a.dtran,a.id";
        System.out.println(sql);
        rfa.clear_table(MenuAdmin.keyssDef, tabledelivery);
        DefaultTableModel model = (DefaultTableModel) tabledelivery.getModel();
        rs = con.queryResult(sql);
        int id=0;
        try {
            while(rs.next()){
                if(id != rs.getInt("id")) {
                    model.addRow(new Object[]{
                        rs.getString("gudang"),rs.getString("inv"),rs.getString("customer"),
                        rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy"),
                        rs.getString("namaproduk"),rs.getDouble("jumlah"),rs.getString("cunit"),
                        rs.getString("logistik"),rfa.DateString(keyssDef, rs.getDate("tglkirim"),"dd MMM yyyy"),
                        rs.getDouble("qtykirim"),rs.getString("satuankirim"),
                    });
                } else {
                    model.addRow(new Object[]{
                        rs.getString("gudang"),rs.getString("inv"),rs.getString("customer"),
                        rfa.DateString(keyssDef, rs.getDate("dtran"),"dd MMM yyyy"),
                        null,null,null,
                        rs.getString("logistik"),rfa.DateString(keyssDef, rs.getDate("tglkirim"),"dd MMM yyyy"),
                        rs.getDouble("qtykirim"),rs.getString("satuankirim"),
                    });
                }
                
                id = rs.getInt("id");
            }
        } catch (SQLException ex) {
            Logger.getLogger(RAnalisaPenjualan.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        try{
            String gudang="";;
            JRDataSource dataSource = new JRTableModelDataSource(tabledelivery.getModel());
            String treport = "report/DeliveryOrder.jrxml";
            HashMap param = new HashMap();
            param.clear();
            param.put("title", "Delivery");
            param.put("pt", deffProgram.get("pt"));
            param.put("alamat", deffProgram.get("alamat"));
            param.put("telp", "Telp. "+deffProgram.get("telp"));
            param.put("mail", "Email : "+deffProgram.get("mail"));
            param.put("gudang", "Indigama");
            param.put("dtran1", dtran1);
            param.put("dtran2", dtran2);
            JasperReport JReport = JasperCompileManager.compileReport(treport);
            JasperPrint JPrint = JasperFillManager.fillReport(JReport, param, dataSource);
            //JasperPrintManager.printReport(JPrint,false);
            JasperViewer.viewReport(JPrint,false);    //With Preview
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
     
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {
        java.awt.GridBagConstraints gridBagConstraints;

        jtabledelivery = new javax.swing.JScrollPane();
        tabledelivery = new javax.swing.JTable();
        jScrollPaneDetailBon = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        rincianpenjualan = new javax.swing.JScrollPane();
        jTablerincianjual = new javax.swing.JTable();
        jPanel1 = new javax.swing.JPanel();
        jPanel2 = new javax.swing.JPanel();
        jTextField1 = new javax.swing.JTextField();
        jLabel1 = new javax.swing.JLabel();
        jDateChooser1 = new com.toedter.calendar.JDateChooser();
        jLabel2 = new javax.swing.JLabel();
        jDateChooser2 = new com.toedter.calendar.JDateChooser();
        idMember = new javax.swing.JTextField();
        jLabel3 = new javax.swing.JLabel();
        jButton4 = new javax.swing.JButton();
        namaMember = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton18 = new javax.swing.JButton();
        tcode3 = new javax.swing.JTextField();
        jLabel12 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        kodeppn = new javax.swing.JComboBox();
        kodeineks = new javax.swing.JComboBox();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel3 = new javax.swing.JPanel();
        jButton2 = new javax.swing.JButton();
        jButton3 = new javax.swing.JButton();
        jButton6 = new javax.swing.JButton();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jButton14 = new javax.swing.JButton();
        jPanel5 = new javax.swing.JPanel();
        jButton5 = new javax.swing.JButton();
        jButton7 = new javax.swing.JButton();
        jButton8 = new javax.swing.JButton();
        jButton9 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jButton13 = new javax.swing.JButton();
        jPanel4 = new javax.swing.JPanel();
        jButton10 = new javax.swing.JButton();
        jButton11 = new javax.swing.JButton();
        jButton12 = new javax.swing.JButton();

        tabledelivery.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "gudang", "inv", "customer", "dtran", "namaproduk", "jumlah", "cunit", "logistik", "tglkirim", "qtykirim", "satuankirim"
            }
        ));
        jtabledelivery.setViewportView(tabledelivery);

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "cnbkt", "id_member", "dtran", "total", "bayar", "sisa", "kodebayar", "metodebayar", "customer", "nilaiinv"
            }
        ));
        jScrollPaneDetailBon.setViewportView(jTable3);

        jTablerincianjual.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null}
            },
            new String [] {
                "tgl", "cproduct", "produk", "jumlah", "harga", "total", "satuan"
            }
        ));
        rincianpenjualan.setViewportView(jTablerincianjual);

        setLayout(new java.awt.GridBagLayout());

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel2.setBorder(javax.swing.BorderFactory.createLineBorder(new java.awt.Color(0, 0, 0)));
        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(102, 102, 102));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField1.setForeground(new java.awt.Color(255, 255, 255));
        jTextField1.setText(" PERIODE : ");
        jPanel2.add(jTextField1, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 160, -1));

        jLabel1.setText("s/d");
        jPanel2.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(340, 70, -1, 20));

        jDateChooser1.setDateFormatString("dd/MM/yyyy");
        jPanel2.add(jDateChooser1, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 70, 140, -1));

        jLabel2.setText("Periode :");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 70, -1, 20));

        jDateChooser2.setDateFormatString("dd/MM/yyyy");
        jPanel2.add(jDateChooser2, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 70, 120, -1));
        jPanel2.add(idMember, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 100, 120, -1));

        jLabel3.setText("Nama Member");
        jPanel2.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 100, -1, 20));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton4ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 100, 30, -1));

        namaMember.setEditable(false);
        namaMember.setBackground(new java.awt.Color(153, 204, 255));
        jPanel2.add(namaMember, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 100, 300, -1));

        jLabel7.setText("Gudang");
        jPanel2.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 40, 72, 20));

        tcode.setEditable(false);
        tcode.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodeFocusLost(evt);
            }
        });
        jPanel2.add(tcode, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 40, 120, -1));

        jButton18.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton18.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton18ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton18, new org.netbeans.lib.awtextra.AbsoluteConstraints(300, 40, 30, -1));

        tcode3.setEditable(false);
        tcode3.setBackground(new java.awt.Color(153, 153, 255));
        tcode3.setForeground(new java.awt.Color(255, 255, 255));
        jPanel2.add(tcode3, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 40, 240, -1));

        jLabel12.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jLabel12.setText("Kode In/Eksternal");
        jPanel2.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 130, 150, 20));

        jLabel6.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jLabel6.setText("PPN/Non PPN");
        jPanel2.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(30, 160, 120, 20));

        kodeppn.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "SEMUA", "PPN", "NON PPN" }));
        kodeppn.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                kodeppnActionPerformed(evt);
            }
        });
        jPanel2.add(kodeppn, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 160, 120, -1));

        kodeineks.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "SEMUA", "INTERNAL", "EXTERNAL" }));
        kodeineks.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                kodeineksActionPerformed(evt);
            }
        });
        jPanel2.add(kodeineks, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 130, 120, -1));

        jPanel1.add(jPanel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, 860, 190));

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton2.setBackground(new java.awt.Color(102, 102, 255));
        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton2.setText("Detail Penjualan + Return");
        jButton2.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 40, 200, 40));

        jButton3.setBackground(new java.awt.Color(102, 102, 255));
        jButton3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton3.setText("Detial Penjualan");
        jButton3.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton3, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 100, 200, 40));

        jButton6.setBackground(new java.awt.Color(102, 102, 255));
        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton6.setText("Detial Return");
        jButton6.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 160, 200, 40));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "urut", "id_jual", "Nomor Kwitansi", "Kode Barang", "Nama Barang", "username", "Tanggal Penjualan", "Jumlah", "Harga", "Disc", "diskon_member", "total", "bayar", "sisa", "Perolehan", "id_member", "nama_member", "wo"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane1.setViewportView(jTable1);

        jPanel3.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(710, 40, 90, 120));

        jButton14.setBackground(new java.awt.Color(102, 102, 255));
        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton14.setText("Detail Item Penjualan");
        jButton14.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 40, 200, 40));

        jTabbedPane1.addTab("Detail Penjualan", jPanel3);

        jPanel5.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton5.setBackground(new java.awt.Color(102, 102, 255));
        jButton5.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton5.setText("Detail Penjualan + Return");
        jButton5.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton5ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton5, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 50, 200, 40));

        jButton7.setBackground(new java.awt.Color(102, 102, 255));
        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton7.setText("Detial Penjualan");
        jButton7.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 120, 200, 40));

        jButton8.setBackground(new java.awt.Color(102, 102, 255));
        jButton8.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton8.setText("Detial Return");
        jButton8.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton8.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton8ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton8, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 190, 200, 40));

        jButton9.setBackground(new java.awt.Color(102, 102, 255));
        jButton9.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton9.setText("Rekap Tagihan PO Customer");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 50, 260, 40));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null},
                {null, null, null, null, null, null, null, null, null, null, null}
            },
            new String [] {
                "id_jual", "id_member", "nama_member", "dtran", "jumlahtempo", "akhirtempo", "total", "bayar", "sisa", "No", "po"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class
            };

            public Class getColumnClass(int columnIndex) {
                return types [columnIndex];
            }
        });
        jScrollPane2.setViewportView(jTable2);

        jPanel5.add(jScrollPane2, new org.netbeans.lib.awtextra.AbsoluteConstraints(382, 210, 390, 70));

        jButton13.setBackground(new java.awt.Color(102, 102, 255));
        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton13.setText("Detail Pembayaran Bon");
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel5.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 120, 260, 40));

        jTabbedPane1.addTab("Detail Penjualan Per Member", jPanel5);

        jPanel4.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jButton10.setBackground(new java.awt.Color(102, 102, 255));
        jButton10.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton10.setText("Invoice Semua Status");
        jButton10.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 170, 200, 40));

        jButton11.setBackground(new java.awt.Color(102, 102, 255));
        jButton11.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton11.setText(" Invoice Terkirim");
        jButton11.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 50, 200, 40));

        jButton12.setBackground(new java.awt.Color(102, 102, 255));
        jButton12.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/report.png"))); // NOI18N
        jButton12.setText("Invoice Belum Terkirim");
        jButton12.setHorizontalAlignment(javax.swing.SwingConstants.LEFT);
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel4.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 110, 200, 40));

        jTabbedPane1.addTab("Delivery Order", jPanel4);

        jPanel1.add(jTabbedPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 240, 860, 320));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.ipadx = 33;
        gridBagConstraints.ipady = 21;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        add(jPanel1, gridBagConstraints);
    }// </editor-fold>//GEN-END:initComponents

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        dataPenjualan(2);
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        dataPenjualan(1);        // TODO add your handling code here:
    }//GEN-LAST:event_jButton3ActionPerformed

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        dataPenjualan(0);
    }//GEN-LAST:event_jButton2ActionPerformed

    private void jButton5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton5ActionPerformed
        dataPenjualan(3);
    }//GEN-LAST:event_jButton5ActionPerformed

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        dataPenjualan(4);
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jButton8ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton8ActionPerformed
        dataPenjualan(5);
    }//GEN-LAST:event_jButton8ActionPerformed

    private void jButton4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton4ActionPerformed
        DialogPublicTP.DialogMembers a = new DialogPublicTP.DialogMembers();
        a.setVisible(true);
        
        if(a.status) {
            idMember.setText(a.Kode);
            namaMember.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton4ActionPerformed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        dataBon();
    }//GEN-LAST:event_jButton9ActionPerformed

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        invoicedelivery(2);
    }//GEN-LAST:event_jButton10ActionPerformed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        invoicedelivery(0);
    }//GEN-LAST:event_jButton11ActionPerformed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        invoicedelivery(1);
    }//GEN-LAST:event_jButton12ActionPerformed

    private void tcodeFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tcodeFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tcodeFocusLost

    private void jButton18ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton18ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode3.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton18ActionPerformed

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        detailBon();
    }//GEN-LAST:event_jButton13ActionPerformed

    private void kodeppnActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_kodeppnActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_kodeppnActionPerformed

    private void kodeineksActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_kodeineksActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_kodeineksActionPerformed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        rincian();
    }//GEN-LAST:event_jButton14ActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField idMember;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton18;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton4;
    private javax.swing.JButton jButton5;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton8;
    private javax.swing.JButton jButton9;
    private com.toedter.calendar.JDateChooser jDateChooser1;
    private com.toedter.calendar.JDateChooser jDateChooser2;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel6;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JPanel jPanel3;
    private javax.swing.JPanel jPanel4;
    private javax.swing.JPanel jPanel5;
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPaneDetailBon;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTable jTablerincianjual;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JScrollPane jtabledelivery;
    private javax.swing.JComboBox kodeineks;
    private javax.swing.JComboBox kodeppn;
    private javax.swing.JTextField namaMember;
    private javax.swing.JScrollPane rincianpenjualan;
    private javax.swing.JTable tabledelivery;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode3;
    // End of variables declaration//GEN-END:variables

    

}
