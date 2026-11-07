/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Kasir;

import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogTunggakanBon;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Master.MGudang;
import Tools.ConnectDB;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import javax.swing.table.DefaultTableModel;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;

/**
 *
 * @author Harits
 */
public class PembayaranBon extends javax.swing.JPanel {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="";
    String akses="PELUNASAN BON";
    String[] kodeBayar = new String[100];
    String buktimemo="";
    public PembayaranBon() {
        initComponents();
        
        con.AddColumnTable("TranSaleBonPay","cnbkt_memorial","nvarchar(50)");
        con.AddColumnTable("TranSaleBonPay","caccll_memorial","nvarchar(50)");
        
        btSave.setEnabled(false);
        listMetodeBayar();
        tglBayar.setDate(new Date());
        
        tcodes.setText(deffProgram.get("ccode"));
    }
    
    
    private void defform(){
        btSave.setEnabled(false);  
        id_jual.setEnabled(true);
        id_jual.setText("");
        total.setText("");
        jatuhtempo.setText("");
        totalbayar.setText("");
        nominalnayar.setText("");
        id_member.setText("");
        taccll.setText("");
        taccll1.setText("");
        
        
        tglBayar.setDate(new Date());
        dtran.setDate(null);
        nama.setText("");
        id_nama.setText("");
        metode_bayar.setSelectedIndex(0);
         
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.getDataVector().removeAllElements();
        jTable1.removeAll();
    }
    
    private void loadData(){
        Boolean trueData =false;
        dtran.setDate(null);
        Double bayarAwal = 0.0;
        sql =   "select distinct a.cnbkt,a.id_member,b.cname,a.dtran,a.total,c.lamatempo,a.bayar, \n" +
                "c.awaltempo,c.akhirtempo  \n" +
                "from TranSale a left join MCustomer b on a.id_member = b.kode_customer \n" +
                "left join TranSaleBon c on a.cnbkt = c.cnbkt and a.ccode=c.ccode \n" +
                "where a.cnbkt = '"+id_jual.getText()+"' and a.ccode='"+tcodes.getText()+"'\n" +
                "";
        
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                if(rs.getString("bayar") != null) {
                    bayarAwal =  rs.getDouble("bayar");
                }
                Double totall = 0.0;
                if(rs.getString("total") != null) {
                    totall =  rs.getDouble("total");
                }
                
                id_member.setText(rs.getString("cname").toString());
                dtran.setDate(rs.getDate("dtran"));
                total.setText(rfa.formatDecimal1.format(totall));
                //jatuhtempo.setText(rs.getString("lamatempo").toString());
                trueData = true;
            }
        } catch (SQLException ex) {
            Logger.getLogger(PembayaranBon.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        //if(trueData && Integer.valueOf(jatuhtempo.getText()) >0) {
            btSave.setEnabled(true);
            id_jual.setEnabled(false);
        //}
        
        
        DefaultTableModel model = (DefaultTableModel) jTable1.getModel();
        model.getDataVector().removeAllElements();
        jTable1.removeAll();
        sql =   "select distinct a.cnbkt, a.id_member, a.total, a.bayar,c.id,c.cicilan,c.dtran,c.ServerDate,d.MetodeBayar,e.cname,c.keterangan,c.cnbkt_memorial,c.caccll_memorial \n" +
                    "from TranSale a left join TranSaleBon b on a.cnbkt = b.cnbkt and a.ccode=b.ccode \n" +
                    "join TranSaleBonPay c on a.cnbkt = c.cnbkt and a.ccode=c.ccode\n" +
                    "left join MMetodePembayaran d on c.KodeBayar = d.KodeBayar \n" +
                    "left join MKaryawan e on c.nik = e.nik  "+
                "where c.cnbkt = '"+id_jual.getText()+"'\n" +
                "";
        rs = con.queryResult(sql.toLowerCase());
        int nomor = 1; Double total = 0.0;
        try {
            while(rs.next()){
                Double cicilan = 0.0;
                if(rs.getString("cicilan") != null) {
                    cicilan = Double.valueOf(rs.getString("cicilan").toString());
                }
                bayarAwal =  Double.valueOf(rs.getString("bayar").toString());
                model.addRow(new Object[]{rs.getString("id"),
                    nomor++,
                    cicilan,
                    rs.getString("dtran"),
                    rs.getString("ServerDate".toLowerCase()),
                    rs.getString("MetodeBayar".toLowerCase()),
                    rs.getString("keterangan"),
                    rs.getString("cname"),
                    rs.getString("caccll_memorial"),
                    rs.getString("cnbkt_memorial"),
                });
                
                total = total + cicilan;
            }
        } catch (SQLException ex) {
            Logger.getLogger(PembayaranBon.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        
        
        totalbayar.setText(rfa.formatDecimal1.format(total+bayarAwal));
        
    }
    private void loadDataKwitansi(){
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        model.getDataVector().removeAllElements();
        jTable2.removeAll();
        sql="select a.cworkorder,a.id,a.dtran, a.cnbkt,a.cgroup,a.cproduct as id_barang,a.kodebayar,a.cmarketing,a.id_member,c.cname as name,c.addr as alamat,b.cnameProduct as nama,a.jumlah,a.harga,a.diskon_barang,a.diskon_member,a.total,a.bayar,a.kembali,a.cunit as unit,b.cunit as unitasli \n" +
        "from TranSale a left join MProduk b on a.cproduct = b.cproduct left join MCustomer c on a.id_member = c.kode_customer \n" +
        "where a.ccode='"+tcodes.getText().trim()+"' and a.cnbkt = '"+id_jual.getText().trim()+"' or cnbkt like '%R-"+id_jual.getText().trim()+"%';";
        System.out.println(sql);
        int i=1;
        rs = con.queryResult(sql.toLowerCase());
        try {
            while(rs.next()){
                Double hargaBarang = 0.0; if(rs.getString("harga") != null) { hargaBarang =  Double.valueOf(rs.getString("harga").toString());  }
                Double jumlahBarang = 0.0; if(rs.getString("jumlah") != null) { jumlahBarang =  Double.valueOf(rs.getString("jumlah").toString());  }
                Double diskon_barang = 0.0; if(rs.getString("diskon_barang") != null) { diskon_barang =  Double.valueOf(rs.getString("diskon_barang").toString());  }
                Double diskon_member = 0.0; if(rs.getString("diskon_member") != null) { diskon_member =  Double.valueOf(rs.getString("diskon_member").toString());  }
                Double bayar = rs.getDouble("bayar");
                Double kembali = 0.0; if(rs.getString("kembali") != null) { kembali =  Double.valueOf(rs.getString("kembali").toString());  }
                
                
                String NamaBarang = (rs.getString("nama"));
                if("R".equals(rs.getString("cnbkt").toString().substring(0, 1))) {
                    NamaBarang = "(RETURN) "+NamaBarang;
                    hargaBarang = hargaBarang;
                    jumlahBarang = jumlahBarang*-1;
                }
                model.addRow(new Object[]{rs.getString("cnbkt"),rs.getString("id_barang"),NamaBarang,jumlahBarang,
                hargaBarang,diskon_barang,jumlahBarang*(hargaBarang-((hargaBarang*diskon_barang)/100)),rs.getString("unit"),i++,rs.getString("cgroup"),rs.getString("id"),rs.getString("unitasli")});
                
            }
        } catch (SQLException ex) {
            Logger.getLogger(PenjualanUtility.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    private void saveBayar(){
        
        if(Double.valueOf(totalbayar.getText().replaceAll(",", ""))+Double.valueOf(nominalnayar.getText().replaceAll(",", ""))>Double.valueOf(total.getText().replaceAll(",", ""))) {
            JOptionPane.showMessageDialog(this, "Nilai yang dibayarkan Melebihi Total Pembelian", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        } else if(taccll.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Isi Akun Kas/Bank terlebih dahulu", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        buktimemo=buktimemorial();
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",id_jual.getText());
        listObj.put("ccode",tcodes.getText());
        listObj.put("dtran",new SimpleDateFormat("yyyy-MM-dd").format(tglBayar.getDate()));
        listObj.put("cicilan",nominalnayar.getText());
        listObj.put("kodebayar",kodeBayar[metode_bayar.getSelectedIndex()]);
        listObj.put("keterangan",nama.getText());
        listObj.put("nik",id_nama.getText());
        listObj.put("cusername",Login.user);
        listObj.put("cnbkt_memorial",buktimemo);
        listObj.put("caccll_memorial",taccll.getText());
        
        sql=con.insertValue("TranSaleBonPay", listObj);
        if(con.queryUpdate(sql)>0){
            //autoSaveMemorial();
            JOptionPane.showMessageDialog(this, "Data Berhasil Disimpan", "Informastion", JOptionPane.INFORMATION_MESSAGE);
            nominalnayar.setText("");
            tglBayar.setDate(null);
            nama.setText("");
            id_nama.setText("");
            taccll.setText("");
            taccll1.setText("");
            metode_bayar.setSelectedIndex(0);
            loadData();
        }
        
    }
    
    private String buktimemorial(){
        int last = 0;
        rs = con.queryResult("select max(convert(int,right(cnbkt,4))) as lastno from TranMemorial where  ccode='"+tcodes.getText()+"' "
                + "and cnbkt like '%"+"MM/"+rfa.DateString(keyssDef, tglBayar.getDate(),"yyMM/")+Login.subdefault+"-"+"%' ");
        
        try {
            while(rs.next()){
                last = rs.getInt("lastno");
            }
        } catch (SQLException ex) {
            Logger.getLogger(MGudang.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        String b1 = ("MM/"+rfa.DateString(keyssDef, tglBayar.getDate(),"yyMM/")+Login.subdefault+"-");
        String b2 = (rfa.NumberBukti(MenuAdmin.keyssDef, 4, last+1));
        
        return b1+b2;
    }
    
    private void autoSaveMemorial(){
        HashMap<String,String> listObj = new HashMap<String,String>();               
        listObj.put("ctran","M1");
        listObj.put("cnbkt",buktimemo);
        listObj.put("ccode",tcodes.getText());
        listObj.put("cyear",new SimpleDateFormat("yyyy").format(tglBayar.getDate()));
        listObj.put("dtran",new SimpleDateFormat("yyyy-MM-dd").format(tglBayar.getDate()));
        listObj.put("cacckas","103.101");
        listObj.put("caccll",taccll.getText());
        listObj.put("namnt",nominalnayar.getText());
        listObj.put("cnote","SPLIT SALE ORDER PELUNASAN CUSTOMER ("+id_member.getText()+") INV NO "+id_jual.getText());
        listObj.put("cusername",Login.user);
        sql=con.insertValue("TranMemorial", listObj);
        con.queryUpdate(sql);
//        
//        sql="INSERT INTO TranMemorial (ctran,cnbkt,ccode,cyear,dtran,cacckas,caccll\n" +
//                    ",namnt,cnote,cusername,kode_customer,kode_customerll,idmesinll)\n" +
//                    "VALUES\n" +
//                    "('"+idKas+"','"+jTable1.getValueAt(i, 6).toString()+"','"+tcode.getText()+"','"+jTable1.getValueAt(i, 5).toString().substring(0, 4)+"','"+jTable1.getValueAt(i, 5).toString()+"','"+jTable1.getValueAt(i, 1).toString()+"','"+jTable1.getValueAt(i, 2).toString()+"',"
//                        + "'"+rfa.formatDecimal3.format(jTable1.getValueAt(i, 4))+"','"+jTable1.getValueAt(i, 3).toString()+"','"+Login.user+"',"
//                        + "'"+jTable1.getValueAt(i,8).toString()+"','"+jTable1.getValueAt(i,10).toString()+"','"+jTable1.getValueAt(i,12).toString()+"')";
    }
    
    private void delete(String id){
        sql = "delete from TranSaleBonPay where id = '"+id+"'";
        System.out.println(sql);
        if(con.queryUpdate(sql)>0){
            JOptionPane.showMessageDialog(this, "Data Berhasil dibatalkan", "Informastion", JOptionPane.INFORMATION_MESSAGE);
            nominalnayar.setText("");
            tglBayar.setDate(null);
            loadData();
        }
    }
    private void deleteMemorial(String cnbktmemo, String tgl,String accll){
        sql = "delete from TranMemorial where ccode = '"+tcodes.getText()+"' and cacckas='103.101' and caccll='"+accll+"' and dtran='"+tgl+"'  and cnbkt='"+cnbktmemo+"'";
        System.out.println(sql);
        if(con.queryUpdate(sql)>0){
        }
    }
    
    private void listMetodeBayar(){
        metode_bayar.removeAll();
        metode_bayar.removeAllItems();
        int ii=1;
        rs = con.queryResult("select * from MMetodePembayaran");
        kodeBayar[0] = "";
        metode_bayar.addItem("");
        try {
            while(rs.next()){
                kodeBayar[ii++] = rs.getString("KodeBayar");
                metode_bayar.addItem(rs.getString("MetodeBayar"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(CashierBayar.class.getName()).log(Level.SEVERE, null, ex);
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
        java.awt.GridBagConstraints gridBagConstraints;

        jPanel1 = new javax.swing.JPanel();
        jPanel2 = new javax.swing.JPanel();
        jLabel2 = new javax.swing.JLabel();
        total = new javax.swing.JTextField();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        id_jual = new javax.swing.JTextField();
        jatuhtempo = new javax.swing.JTextField();
        jLabel5 = new javax.swing.JLabel();
        jLabel6 = new javax.swing.JLabel();
        jLabel7 = new javax.swing.JLabel();
        id_member = new javax.swing.JTextField();
        jButton1 = new javax.swing.JButton();
        totalbayar = new javax.swing.JTextField();
        dtran = new com.toedter.calendar.JDateChooser();
        jLabel20 = new javax.swing.JLabel();
        tcodes = new javax.swing.JTextField();
        jButton15 = new javax.swing.JButton();
        jPanel3 = new javax.swing.JPanel();
        jLabel1 = new javax.swing.JLabel();
        jLabel8 = new javax.swing.JLabel();
        nominalnayar = new javax.swing.JTextField();
        jTextField5 = new javax.swing.JTextField();
        btSave = new javax.swing.JButton();
        btCancel = new javax.swing.JButton();
        tglBayar = new com.toedter.calendar.JDateChooser();
        jLabel9 = new javax.swing.JLabel();
        jLabel10 = new javax.swing.JLabel();
        jButton2 = new javax.swing.JButton();
        id_nama = new javax.swing.JTextField();
        nama = new javax.swing.JTextField();
        metode_bayar = new javax.swing.JComboBox();
        jLabel11 = new javax.swing.JLabel();
        taccll = new javax.swing.JTextField();
        jButton6 = new javax.swing.JButton();
        taccll1 = new javax.swing.JTextField();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jScrollPane1 = new javax.swing.JScrollPane();
        jTable1 = new javax.swing.JTable();
        jPanel5 = new javax.swing.JPanel();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();

        setLayout(new java.awt.GridBagLayout());

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel2.setBorder(javax.swing.BorderFactory.createLineBorder(new java.awt.Color(0, 0, 0)));
        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel2.setText("Total Pembelian");
        jPanel2.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 10, -1, 20));

        total.setEditable(false);
        total.setBackground(new java.awt.Color(204, 204, 255));
        jPanel2.add(total, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 10, 170, -1));

        jLabel3.setText("No Kwitansi ");
        jPanel2.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 10, -1, 20));

        jLabel4.setText("Tgl Pembelian");
        jPanel2.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 70, -1, 20));
        jPanel2.add(id_jual, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 10, 170, -1));

        jatuhtempo.setEditable(false);
        jatuhtempo.setBackground(new java.awt.Color(204, 204, 255));
        jPanel2.add(jatuhtempo, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 40, 170, -1));

        jLabel5.setText("Jatuh Tempo");
        jPanel2.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 40, -1, 20));

        jLabel6.setText("Total Pembayaran (Rp)");
        jPanel2.add(jLabel6, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 70, -1, 20));

        jLabel7.setText("Customer");
        jPanel2.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 40, -1, 20));

        id_member.setEditable(false);
        id_member.setBackground(new java.awt.Color(204, 204, 255));
        jPanel2.add(id_member, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 40, 170, -1));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton1ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 10, 40, -1));

        totalbayar.setEditable(false);
        totalbayar.setBackground(new java.awt.Color(204, 204, 255));
        jPanel2.add(totalbayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 70, 170, -1));

        dtran.setDateFormatString("dd MMMM yyyy");
        dtran.setEnabled(false);
        jPanel2.add(dtran, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 70, 170, 30));

        jLabel20.setText("Gudang");
        jPanel2.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(770, 10, -1, 20));

        tcodes.setEditable(false);
        tcodes.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tcodesFocusLost(evt);
            }
        });
        jPanel2.add(tcodes, new org.netbeans.lib.awtextra.AbsoluteConstraints(850, 10, 110, -1));

        jButton15.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton15.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton15ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton15, new org.netbeans.lib.awtextra.AbsoluteConstraints(960, 10, 30, -1));

        jPanel1.add(jPanel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 1070, 110));

        jPanel3.setBorder(javax.swing.BorderFactory.createLineBorder(new java.awt.Color(0, 0, 0)));
        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel1.setText("- Tanggal Pembayaran");
        jPanel3.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 40, -1, 20));

        jLabel8.setText("- Melalui");
        jPanel3.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(330, 70, -1, 20));
        jPanel3.add(nominalnayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 40, 150, -1));

        jTextField5.setEditable(false);
        jTextField5.setBackground(new java.awt.Color(51, 102, 255));
        jTextField5.setFont(new java.awt.Font("Tahoma", 1, 11)); // NOI18N
        jTextField5.setForeground(new java.awt.Color(255, 255, 255));
        jTextField5.setText(" INPUT PEMBAYARAN");
        jPanel3.add(jTextField5, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 220, -1));

        btSave.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/save.png"))); // NOI18N
        btSave.setText("Save");
        btSave.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btSaveActionPerformed(evt);
            }
        });
        jPanel3.add(btSave, new org.netbeans.lib.awtextra.AbsoluteConstraints(930, 40, 90, -1));

        btCancel.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancel.setText("Cancel");
        btCancel.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelActionPerformed(evt);
            }
        });
        jPanel3.add(btCancel, new org.netbeans.lib.awtextra.AbsoluteConstraints(930, 70, 90, -1));

        tglBayar.setDateFormatString("dd/MM/yyyy");
        jPanel3.add(tglBayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(470, 40, 130, -1));

        jLabel9.setText("- Account");
        jPanel3.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 40, -1, 20));

        jLabel10.setText("- Metode Bayar");
        jPanel3.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, -1, 20));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(600, 70, 30, -1));

        id_nama.setEditable(false);
        id_nama.setBackground(new java.awt.Color(204, 204, 255));
        jPanel3.add(id_nama, new org.netbeans.lib.awtextra.AbsoluteConstraints(460, 70, -1, -1));

        nama.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                namaFocusLost(evt);
            }
        });
        nama.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyTyped(java.awt.event.KeyEvent evt) {
                namaKeyTyped(evt);
            }
        });
        jPanel3.add(nama, new org.netbeans.lib.awtextra.AbsoluteConstraints(470, 70, 130, -1));

        metode_bayar.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Item 1", "Item 2", "Item 3", "Item 4" }));
        metode_bayar.addItemListener(new java.awt.event.ItemListener() {
            public void itemStateChanged(java.awt.event.ItemEvent evt) {
                metode_bayarItemStateChanged(evt);
            }
        });
        jPanel3.add(metode_bayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 70, 150, -1));

        jLabel11.setText("- Nominal Pembayaran (RP)");
        jPanel3.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 40, -1, 20));

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
        jPanel3.add(taccll, new org.netbeans.lib.awtextra.AbsoluteConstraints(740, 40, 90, -1));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(830, 40, 30, -1));

        taccll1.setEditable(false);
        taccll1.setBackground(new java.awt.Color(153, 153, 255));
        jPanel3.add(taccll1, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 70, 190, -1));

        jPanel1.add(jPanel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 420, 1070, 120));

        jTabbedPane1.setBackground(new java.awt.Color(51, 51, 255));

        jTable1.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "ID", "No", "Nominal Pembayaran", "Tanggal Pembayaran", "ComputerDate", "Metode", "Keterangan", "Oleh", "Account", "No Bukti"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, true, false, false, false
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
            jTable1.getColumnModel().getColumn(0).setMinWidth(0);
            jTable1.getColumnModel().getColumn(0).setMaxWidth(0);
            jTable1.getColumnModel().getColumn(1).setMinWidth(50);
            jTable1.getColumnModel().getColumn(1).setMaxWidth(50);
        }

        javax.swing.GroupLayout jPanel4Layout = new javax.swing.GroupLayout(jPanel4);
        jPanel4.setLayout(jPanel4Layout);
        jPanel4Layout.setHorizontalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 1065, Short.MAX_VALUE)
        );
        jPanel4Layout.setVerticalGroup(
            jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jScrollPane1, javax.swing.GroupLayout.DEFAULT_SIZE, 242, Short.MAX_VALUE)
        );

        jTabbedPane1.addTab("Pembayaran", jPanel4);

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "No Kwitansi", "Kode", "Nama", "Jumlah", "Harga", "Diskon", "Total", "Satuan", "No", "group", "id", "satuanasli"
            }
        ) {
            Class[] types = new Class [] {
                java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Double.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class, java.lang.Object.class
            };
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false
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
            jTable2.getColumnModel().getColumn(3).setMinWidth(90);
            jTable2.getColumnModel().getColumn(3).setMaxWidth(90);
            jTable2.getColumnModel().getColumn(4).setMinWidth(120);
            jTable2.getColumnModel().getColumn(4).setMaxWidth(120);
            jTable2.getColumnModel().getColumn(8).setMinWidth(0);
            jTable2.getColumnModel().getColumn(8).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(9).setMinWidth(0);
            jTable2.getColumnModel().getColumn(9).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(10).setMinWidth(0);
            jTable2.getColumnModel().getColumn(10).setMaxWidth(0);
            jTable2.getColumnModel().getColumn(11).setMinWidth(0);
            jTable2.getColumnModel().getColumn(11).setMaxWidth(0);
        }

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 1065, Short.MAX_VALUE)
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 242, Short.MAX_VALUE)
        );

        jTabbedPane1.addTab("List Invoice", jPanel5);

        jPanel1.add(jTabbedPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 1070, 270));

        gridBagConstraints = new java.awt.GridBagConstraints();
        gridBagConstraints.gridx = 0;
        gridBagConstraints.gridy = 0;
        gridBagConstraints.ipadx = 20;
        gridBagConstraints.ipady = 9;
        gridBagConstraints.anchor = java.awt.GridBagConstraints.NORTHWEST;
        add(jPanel1, gridBagConstraints);
    }// </editor-fold>//GEN-END:initComponents

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        DialogTunggakanBon a = new DialogTunggakanBon();
        a.setVisible(true);
        if(a.status) {
            if(!tcodes.getText().equals(a.ccode)) {
                JOptionPane.showMessageDialog(this, "Nomor invoice tidak sesuai dengan kode gudang yang dipilih", "Informastion", JOptionPane.ERROR_MESSAGE);
                return;
            }
            id_jual.setText(a.kwtt);
            loadData();     
            loadDataKwitansi();
        }
        
           // TODO add your handling code here:
    }//GEN-LAST:event_jButton1ActionPerformed

    private void btSaveActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btSaveActionPerformed
//        if(!con.akses(akses, rfa.INSERT)) {return;}
        int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan melakukan menyimpan data ini? ", "Save Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) saveBayar();        // TODO add your handling code here:
    }//GEN-LAST:event_btSaveActionPerformed

    private void btCancelActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelActionPerformed
        defform();
    }//GEN-LAST:event_btCancelActionPerformed

    private void jTable1MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable1MouseClicked
        if(evt.getClickCount() == 2) {
            
//            if(db.Akses("PELUNASAN BON", "DELETE") == 0) {return;}
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan melakukan menghapus data ini? ", "Delete Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) {
                if(!con.akses(akses, rfa.DELETE)) return;
                
                int row = jTable1.getSelectedRow();
                
                if(jTable1.getValueAt(row,8) != null) {
                    deleteMemorial(jTable1.getValueAt(row,9).toString(),
                        jTable1.getValueAt(row,3).toString(),
                        jTable1.getValueAt(row,8).toString());
                }
                
                delete(jTable1.getValueAt(jTable1.getSelectedRow(), 0).toString());
                
            }
        }
    }//GEN-LAST:event_jTable1MouseClicked

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        id_nama.setText("");
            nama.setText("");
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status) {
            id_nama.setText(a.Kode);
            nama.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton2ActionPerformed

    private void metode_bayarItemStateChanged(java.awt.event.ItemEvent evt) {//GEN-FIRST:event_metode_bayarItemStateChanged
        if(metode_bayar.getSelectedIndex() >0) {
            if(metode_bayar.getSelectedItem().toString().equalsIgnoreCase("TRANSFER")) {
                jButton2.setEnabled(false);
                jLabel8.setText("- Keterangan");
            } else {
                jButton2.setEnabled(true);
                jLabel8.setText("- Melalui");
            }
        }
    }//GEN-LAST:event_metode_bayarItemStateChanged

    private void namaFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_namaFocusLost
        nama.setText(nama.getText().toUpperCase());
    }//GEN-LAST:event_namaFocusLost

    private void namaKeyTyped(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_namaKeyTyped
        if(evt.getKeyCode()==KeyEvent.VK_BACK_SPACE)  {
            id_nama.setText("");
        }
    }//GEN-LAST:event_namaKeyTyped

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

    private void taccllFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_taccllFocusLost
        sql = "select * from maccount where caccnt = '"+taccll.getText()+"' ";
        rs = con.queryResult(sql);
        try {
            rfa.empty(keyssDef, taccll1);
            while(rs.next()){  taccll1.setText(rs.getString("cdesc"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }//GEN-LAST:event_taccllFocusLost

    private void taccllKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_taccllKeyPressed
        
    }//GEN-LAST:event_taccllKeyPressed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            taccll.setText(a.caccnt);
            taccll1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
        
    }//GEN-LAST:event_jTable2MouseClicked


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btCancel;
    private javax.swing.JButton btSave;
    private com.toedter.calendar.JDateChooser dtran;
    private javax.swing.JTextField id_jual;
    private javax.swing.JTextField id_member;
    private javax.swing.JTextField id_nama;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton15;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton6;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel20;
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
    private javax.swing.JScrollPane jScrollPane1;
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTextField jTextField5;
    private javax.swing.JTextField jatuhtempo;
    private javax.swing.JComboBox metode_bayar;
    private javax.swing.JTextField nama;
    private javax.swing.JTextField nominalnayar;
    private javax.swing.JTextField taccll;
    private javax.swing.JTextField taccll1;
    private javax.swing.JTextField tcodes;
    private com.toedter.calendar.JDateChooser tglBayar;
    private javax.swing.JTextField total;
    private javax.swing.JTextField totalbayar;
    // End of variables declaration//GEN-END:variables
}
