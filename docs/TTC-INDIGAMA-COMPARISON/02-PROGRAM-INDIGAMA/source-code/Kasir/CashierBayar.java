/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Kasir;

import DialogPublicTP.DialogAccount;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogWO;
import Main.Login;
import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import java.awt.Dialog;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.JOptionPane;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import rf4declare.Rf4declare;
/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class CashierBayar extends javax.swing.JDialog {

    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="CASHIER";
    String idKas="D1";
    ArrayList<String> deleteRow;
    
    SimpleDateFormat sdf = new SimpleDateFormat("dd MMMM yyyy");
    public Boolean status = false,suratjalan = false;
    DecimalFormat dFormat = new DecimalFormat("#,##0");
    String[] kodeBayar = new String[100];
    public String idmarketing,idmember,namamember,alamatmember,discmember,djt,tgljt,totalk,dbayar,dkembali,metodebayar,metodebayardesc,wo,caccll,ppn,ppn_nominal,ongkir;
    public Date tglJatuhTempo;
    public CashierBayar(String subTotal,String nokwt) {
        this.setModalityType(Dialog.ModalityType.APPLICATION_MODAL); 
        initComponents();
        setLocationRelativeTo(null);
         
        idmember=namamember=alamatmember=discmember=djt=tgljt=totalk=dbayar=dkembali="";
        
        Calendar a = Calendar.getInstance();
        TglNow.setText(sdf.format(CashierFull.jDateTranSales.getDate()));
        Kasir.setText(Login.user);
        Kwitansi.setText(nokwt);
        
        kMember.setText(CashierFull.tcustomerkode.getText());
        kNama.setText(CashierFull.tcustomer.getText());
        sql="select * from MCustomer where kode_customer = '"+CashierFull.tcustomerkode.getText()+"'";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                kAlamat.setText(rs.getString("addr"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(CashierBayar.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        subtotal.setText(subTotal);
        totalSeleuruh.setText("Total : Rp "+subTotal);
        
        kMember.requestFocus();
        
        listMetodeBayar();
        try {
            JT();
        } catch (Exception e) {}
    }
    
    private void listMetodeBayar(){
        jComboBox1.removeAll();
        jComboBox1.removeAllItems();
        int ii=1;
        rs = con.queryResult("select * from MMetodePembayaran");
        kodeBayar[0] = "";
        jComboBox1.addItem("");
        try {
            while(rs.next()){
                kodeBayar[ii++] = rs.getString("KodeBayar");
                jComboBox1.addItem(rs.getString("MetodeBayar"));
            }
        } catch (SQLException ex) {
            Logger.getLogger(CashierBayar.class.getName()).log(Level.SEVERE, null, ex);
        }
    }
    
    
    private void JT() {
        Date datee = CashierFull.jDateTranSales.getDate();
        Calendar c = Calendar.getInstance();
        int tahun = Integer.valueOf(rfa.DateString(keyssDef, datee, "yyyy"));
        int bulan = Integer.valueOf(rfa.DateString(keyssDef, datee, "MM"));
        int hari = Integer.valueOf(rfa.DateString(keyssDef, datee, "dd"));
        c.set(tahun,bulan-1,hari);
        if(!iJT.getText().isEmpty()) {
            c.add(Calendar.DAY_OF_MONTH, Integer.valueOf(iJT.getText()));  
            String newDate = sdf.format(c.getTime());  
            tglJatuhTempo = c.getTime();
            tgltempo.setText(newDate);
        }
    }
    private void hitung() {
        Double hargaBarang = Double.valueOf(subtotal.getText().replaceAll(",", ""));
        Double diskonBarang = 0.0;
        Double ppnn=0.0,ongkir=0.0;
        if(!tongkir.getText().isEmpty()) ongkir = Double.valueOf(tongkir.getText().replaceAll(",", ""));
        
     
        diskonBarang = Double.valueOf(disc_nominal.getText().replaceAll(",", ""));
        
        Double HargaSetDiskon = hargaBarang-diskonBarang;
        
        if(!tppn.getText().isEmpty()) {
            ppnn = Double.valueOf(tppn.getText().replaceAll(",", ""))/100;
        }
        Double ppn_nilai = HargaSetDiskon*ppnn;
        tppn_nominal.setText(dFormat.format(ppn_nilai));
        
        Double totalakhir = HargaSetDiskon+ppn_nilai+ongkir;
        totalSeleuruh.setText("Total : Rp "+dFormat.format(totalakhir));
    }
    private void hitungKembali() {
        Double totalll  = Double.valueOf(totalSeleuruh.getText().replaceAll("Total : Rp ", "").replace(",", ""));
        Double bayarr  = Double.valueOf(ibayar.getText().replaceAll(",", ""));
        Double kembalii = bayarr - totalll;
        
        iKembali.setText("Kembali : Rp "+dFormat.format(kembalii));
    }
    private Boolean cekTagihanBon(String member) {
        sql =   "select distinct a.cnbkt,a.id_member,a.dtran,a.total,a.kembali,a.KodeBayar,b.lamatempo,b.akhirtempo, \n" +
                "a.total - (a.bayar+(select isnull(sum(cicilan),0) from TranSaleBonPay where cnbkt = a.cnbkt)) as SisaCicilan,c.cname \n" +
                "from TranSale a inner join TranSaleBon b on a.cnbkt = b.cnbkt join MCustomer c on a.id_member = c.kode_customer \n" +
                "and a.id_member = '"+member+"'\n" +
                "and a.total - (a.bayar+(select isnull(sum(cicilan),0) from TranSaleBonPay where cnbkt = a.cnbkt)) > 0";
        sql="select distinct aa.ccode,aa.cname as gudang,a.cnbkt,a.id_member,a.dtran,a.total,a.kembali,b.lamatempo,b.akhirtempo as akhirtempo, \n" +
            "a.total - (a.bayar+isnull(d.cicilan,0)) as SisaCicilan,c.cname \n" +
            "from TranSale a join MGudang aa on a.ccode=aa.ccode\n" +
            "left join TranSaleBon b on a.cnbkt = b.cnbkt and a.ccode=b.ccode\n" +
            "left join (\n" +
            "	select ccode,cnbkt,sum(cicilan) as cicilan from TranSaleBonPay\n" +
            "	group by ccode,cnbkt\n" +
            ") d on a.ccode=d.ccode and a.cnbkt=d.cnbkt\n" +
            "join MCustomer c on a.id_member = c.kode_customer       \n" +
            "where a.id_member = '"+member+"' and a.total - (a.bayar+isnull(d.cicilan,0)) > 0 \n" +
            "order by a.cnbkt desc\n" +
            "\n" +
            "";
        rs = con.queryResult(sql.replaceAll("]", "").replace("[", "").toLowerCase());
        Boolean data = false;
        try {
            while(rs.next()){
                data = true;
            }
        } catch (SQLException ex) {
            Logger.getLogger(CashierBayar.class.getName()).log(Level.SEVERE, null, ex);
        }
        return data;
    }
    
    
    
    /**
     * This method is called from within the constructor to initialize the form.
     * WARNING: Do NOT modify this code. The content of this method is always
     * regenerated by the Form Editor.
     */
    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        tkodemarketing = new javax.swing.JLabel();
        jPanel1 = new javax.swing.JPanel();
        iKembali = new javax.swing.JTextField();
        jLabel7 = new javax.swing.JLabel();
        jButton1 = new javax.swing.JButton();
        jTextField1 = new javax.swing.JTextField();
        kMember = new javax.swing.JTextField();
        jTextField3 = new javax.swing.JTextField();
        ibayar = new javax.swing.JTextField();
        Kasir = new javax.swing.JTextField();
        TglNow = new javax.swing.JTextField();
        Kwitansi = new javax.swing.JTextField();
        kAlamat = new javax.swing.JLabel();
        jButton2 = new javax.swing.JButton();
        kNama = new javax.swing.JLabel();
        jTextField9 = new javax.swing.JTextField();
        jLabel9 = new javax.swing.JLabel();
        jTextField10 = new javax.swing.JTextField();
        subtotal = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jTextField13 = new javax.swing.JTextField();
        jLabel8 = new javax.swing.JLabel();
        tgltempo = new javax.swing.JTextField();
        iJT = new javax.swing.JTextField();
        totalSeleuruh = new javax.swing.JTextField();
        jLabel10 = new javax.swing.JLabel();
        disc = new javax.swing.JTextField();
        jTextField18 = new javax.swing.JTextField();
        jButton3 = new javax.swing.JButton();
        jTextField15 = new javax.swing.JTextField();
        jTextField5 = new javax.swing.JTextField();
        jLabel11 = new javax.swing.JLabel();
        jComboBox1 = new javax.swing.JComboBox();
        jButton6 = new javax.swing.JButton();
        jLabel1 = new javax.swing.JLabel();
        jLabel2 = new javax.swing.JLabel();
        jLabel3 = new javax.swing.JLabel();
        jLabel4 = new javax.swing.JLabel();
        jTextField2 = new javax.swing.JTextField();
        kMarketing = new javax.swing.JTextField();
        jButton4 = new javax.swing.JButton();
        jTextField4 = new javax.swing.JTextField();
        two = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        two1 = new javax.swing.JTextField();
        jTextField6 = new javax.swing.JTextField();
        taccll = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        taccll1 = new javax.swing.JTextField();
        jLabel12 = new javax.swing.JLabel();
        jTextField14 = new javax.swing.JTextField();
        tppn = new javax.swing.JTextField();
        jLabel13 = new javax.swing.JLabel();
        jTextField17 = new javax.swing.JTextField();
        disc_nominal = new javax.swing.JTextField();
        tongkir = new javax.swing.JTextField();
        tppn_nominal = new javax.swing.JTextField();

        setDefaultCloseOperation(javax.swing.WindowConstants.DISPOSE_ON_CLOSE);
        setUndecorated(true);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        iKembali.setEditable(false);
        iKembali.setBackground(new java.awt.Color(102, 153, 255));
        iKembali.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        iKembali.setForeground(new java.awt.Color(255, 255, 255));
        iKembali.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        iKembali.setText("Kembali : Rp 0");
        iKembali.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iKembaliKeyPressed(evt);
            }
        });
        jPanel1.add(iKembali, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 430, 360, 40));

        jLabel7.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel7.setText("Bayar");
        jPanel1.add(jLabel7, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 390, 90, 30));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Print_16px.png"))); // NOI18N
        jButton1.setText("Save and Print Invoice");
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
        jPanel1.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(560, 490, 180, -1));

        jTextField1.setEditable(false);
        jTextField1.setBackground(new java.awt.Color(153, 153, 153));
        jTextField1.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField1.setForeground(new java.awt.Color(204, 255, 255));
        jTextField1.setText("ACCOUNT");
        jTextField1.setFocusable(false);
        jTextField1.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jTextField1.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField1ActionPerformed(evt);
            }
        });
        jPanel1.add(jTextField1, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 370, 100, 30));

        kMember.setEditable(false);
        kMember.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        kMember.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                kMemberKeyPressed(evt);
            }
        });
        jPanel1.add(kMember, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 200, 240, 30));

        jTextField3.setEditable(false);
        jTextField3.setBackground(new java.awt.Color(204, 204, 204));
        jTextField3.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField3.setText("Tanggal");
        jTextField3.setFocusable(false);
        jTextField3.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jTextField3.setName(""); // NOI18N
        jTextField3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField3ActionPerformed(evt);
            }
        });
        jPanel1.add(jTextField3, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 140, 100, 30));

        ibayar.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        ibayar.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        ibayar.setMargin(new java.awt.Insets(5, 5, 5, 5));
        ibayar.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                ibayarCaretUpdate(evt);
            }
        });
        jPanel1.add(ibayar, new org.netbeans.lib.awtextra.AbsoluteConstraints(610, 390, 230, 30));

        Kasir.setEditable(false);
        Kasir.setBackground(new java.awt.Color(204, 255, 255));
        Kasir.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        Kasir.setText(" Kasir");
        Kasir.setFocusable(false);
        jPanel1.add(Kasir, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 110, 270, 30));

        TglNow.setEditable(false);
        TglNow.setBackground(new java.awt.Color(204, 255, 255));
        TglNow.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        TglNow.setText(" Kasir");
        TglNow.setFocusable(false);
        jPanel1.add(TglNow, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 140, 270, 30));

        Kwitansi.setEditable(false);
        Kwitansi.setBackground(new java.awt.Color(204, 255, 255));
        Kwitansi.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        Kwitansi.setText(" Kasir");
        Kwitansi.setFocusable(false);
        jPanel1.add(Kwitansi, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 170, 270, 30));
        jPanel1.add(kAlamat, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 250, 140, 20));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton2ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 200, 30, 30));
        jPanel1.add(kNama, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 230, 140, 20));

        jTextField9.setEditable(false);
        jTextField9.setBackground(new java.awt.Color(51, 102, 255));
        jTextField9.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        jTextField9.setForeground(new java.awt.Color(204, 255, 255));
        jTextField9.setText("      Bayar");
        jTextField9.setFocusable(false);
        jPanel1.add(jTextField9, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 870, 60));

        jLabel9.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel9.setText("Total");
        jPanel1.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 110, 70, 30));

        jTextField10.setEditable(false);
        jTextField10.setBackground(new java.awt.Color(204, 204, 204));
        jTextField10.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField10.setText(" Kasir");
        jTextField10.setFocusable(false);
        jPanel1.add(jTextField10, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 110, 100, 30));

        subtotal.setEditable(false);
        subtotal.setBackground(new java.awt.Color(204, 255, 255));
        subtotal.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        subtotal.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        subtotal.setText("0");
        subtotal.setFocusable(false);
        subtotal.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jPanel1.add(subtotal, new org.netbeans.lib.awtextra.AbsoluteConstraints(620, 110, 220, 30));

        jTextField12.setEditable(false);
        jTextField12.setBackground(new java.awt.Color(204, 204, 204));
        jTextField12.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField12.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField12.setText("%");
        jTextField12.setFocusable(false);
        jPanel1.add(jTextField12, new org.netbeans.lib.awtextra.AbsoluteConstraints(630, 150, 30, 30));

        jTextField13.setEditable(false);
        jTextField13.setBackground(new java.awt.Color(204, 204, 204));
        jTextField13.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField13.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField13.setText("RP");
        jTextField13.setFocusable(false);
        jPanel1.add(jTextField13, new org.netbeans.lib.awtextra.AbsoluteConstraints(580, 390, 30, 30));

        jLabel8.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel8.setText("PPn");
        jPanel1.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 180, 70, 30));

        tgltempo.setEditable(false);
        tgltempo.setBackground(new java.awt.Color(204, 204, 204));
        tgltempo.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        tgltempo.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tgltempo.setText("Tanggal");
        tgltempo.setFocusable(false);
        jPanel1.add(tgltempo, new org.netbeans.lib.awtextra.AbsoluteConstraints(690, 260, 150, 30));

        iJT.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        iJT.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        iJT.setText("0");
        iJT.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                iJTCaretUpdate(evt);
            }
        });
        iJT.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                iJTFocusLost(evt);
            }
        });
        iJT.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                iJTKeyPressed(evt);
            }
        });
        jPanel1.add(iJT, new org.netbeans.lib.awtextra.AbsoluteConstraints(590, 260, 40, 30));

        totalSeleuruh.setEditable(false);
        totalSeleuruh.setBackground(new java.awt.Color(51, 102, 255));
        totalSeleuruh.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        totalSeleuruh.setForeground(new java.awt.Color(204, 255, 255));
        totalSeleuruh.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        totalSeleuruh.setText("Total : Rp 0");
        totalSeleuruh.setFocusable(false);
        jPanel1.add(totalSeleuruh, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 340, 360, 40));

        jLabel10.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel10.setText("Metode Bayar");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 300, 90, 30));

        disc.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        disc.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        disc.setText("0");
        disc.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                discCaretUpdate(evt);
            }
        });
        disc.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                discFocusLost(evt);
            }
        });
        disc.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                discKeyPressed(evt);
            }
        });
        jPanel1.add(disc, new org.netbeans.lib.awtextra.AbsoluteConstraints(590, 150, 40, 30));

        jTextField18.setEditable(false);
        jTextField18.setBackground(new java.awt.Color(204, 204, 204));
        jTextField18.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField18.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField18.setText("Hari");
        jTextField18.setFocusable(false);
        jPanel1.add(jTextField18, new org.netbeans.lib.awtextra.AbsoluteConstraints(630, 260, 50, 30));

        jButton3.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        jButton3.setText("Batal");
        jButton3.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton3ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton3, new org.netbeans.lib.awtextra.AbsoluteConstraints(750, 490, 90, -1));

        jTextField15.setEditable(false);
        jTextField15.setBackground(new java.awt.Color(204, 204, 204));
        jTextField15.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField15.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField15.setText("RP");
        jTextField15.setFocusable(false);
        jPanel1.add(jTextField15, new org.netbeans.lib.awtextra.AbsoluteConstraints(590, 110, 30, 30));

        jTextField5.setEditable(false);
        jTextField5.setBackground(new java.awt.Color(204, 204, 204));
        jTextField5.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField5.setText("No Kwitansi");
        jTextField5.setFocusable(false);
        jTextField5.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jTextField5.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField5ActionPerformed(evt);
            }
        });
        jPanel1.add(jTextField5, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 170, 100, 30));

        jLabel11.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel11.setText("Jatuh Tempo");
        jPanel1.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 260, 90, 30));

        jComboBox1.setFont(new java.awt.Font("Tahoma", 1, 14)); // NOI18N
        jComboBox1.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "Cash", "Transfer", "Bon", "Invest" }));
        jPanel1.add(jComboBox1, new org.netbeans.lib.awtextra.AbsoluteConstraints(590, 300, 250, 30));

        jButton6.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Close_Window_16px.png"))); // NOI18N
        jButton6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton6ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton6, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 230, 30, -1));

        jLabel1.setText("Alamat");
        jPanel1.add(jLabel1, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 250, -1, 20));

        jLabel2.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        jLabel2.setText(":");
        jPanel1.add(jLabel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 250, 10, 20));

        jLabel3.setText("Nama Member");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 230, -1, 20));

        jLabel4.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        jLabel4.setText(":");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(130, 230, 10, 20));

        jTextField2.setEditable(false);
        jTextField2.setBackground(new java.awt.Color(153, 153, 153));
        jTextField2.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField2.setForeground(new java.awt.Color(204, 255, 255));
        jTextField2.setText("Member");
        jTextField2.setFocusable(false);
        jTextField2.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jTextField2.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField2ActionPerformed(evt);
            }
        });
        jPanel1.add(jTextField2, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 200, 100, 30));

        kMarketing.setEditable(false);
        kMarketing.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        kMarketing.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                kMarketingKeyPressed(evt);
            }
        });
        jPanel1.add(kMarketing, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 280, 240, 30));

        jButton4.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton4ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton4, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 280, 30, 30));

        jTextField4.setEditable(false);
        jTextField4.setBackground(new java.awt.Color(153, 153, 153));
        jTextField4.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField4.setForeground(new java.awt.Color(204, 255, 255));
        jTextField4.setText("Marketing");
        jTextField4.setFocusable(false);
        jTextField4.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jTextField4.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField4ActionPerformed(evt);
            }
        });
        jPanel1.add(jTextField4, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 280, 100, 30));

        two.setEditable(false);
        two.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 310, 240, 30));

        jButton13.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton13.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton13ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton13, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 310, 30, 30));

        two1.setEditable(false);
        two1.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(two1, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 340, 270, 30));

        jTextField6.setEditable(false);
        jTextField6.setBackground(new java.awt.Color(153, 153, 153));
        jTextField6.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField6.setForeground(new java.awt.Color(204, 255, 255));
        jTextField6.setText("WO Produksi");
        jTextField6.setFocusable(false);
        jTextField6.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jTextField6.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jTextField6ActionPerformed(evt);
            }
        });
        jPanel1.add(jTextField6, new org.netbeans.lib.awtextra.AbsoluteConstraints(40, 310, 100, 30));

        taccll.setEditable(false);
        taccll.setBackground(new java.awt.Color(204, 204, 255));
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
        jPanel1.add(taccll, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 370, 240, 30));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(380, 370, 30, 30));

        taccll1.setEditable(false);
        taccll1.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(taccll1, new org.netbeans.lib.awtextra.AbsoluteConstraints(140, 400, 270, 30));

        jLabel12.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel12.setText("Disc");
        jPanel1.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 150, 70, 30));

        jTextField14.setEditable(false);
        jTextField14.setBackground(new java.awt.Color(204, 204, 204));
        jTextField14.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField14.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField14.setText("%");
        jTextField14.setFocusable(false);
        jPanel1.add(jTextField14, new org.netbeans.lib.awtextra.AbsoluteConstraints(630, 180, 30, 30));

        tppn.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        tppn.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        tppn.setText("0");
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
        jPanel1.add(tppn, new org.netbeans.lib.awtextra.AbsoluteConstraints(590, 180, 40, 30));

        jLabel13.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel13.setText("Ongkos Kirim");
        jPanel1.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 220, 100, 30));

        jTextField17.setEditable(false);
        jTextField17.setBackground(new java.awt.Color(204, 204, 204));
        jTextField17.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField17.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField17.setText("RP");
        jTextField17.setFocusable(false);
        jPanel1.add(jTextField17, new org.netbeans.lib.awtextra.AbsoluteConstraints(590, 220, 30, 30));

        disc_nominal.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        disc_nominal.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        disc_nominal.setText("0");
        disc_nominal.setMargin(new java.awt.Insets(5, 5, 5, 5));
        disc_nominal.addCaretListener(new javax.swing.event.CaretListener() {
            public void caretUpdate(javax.swing.event.CaretEvent evt) {
                disc_nominalCaretUpdate(evt);
            }
        });
        disc_nominal.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                disc_nominalFocusLost(evt);
            }
        });
        disc_nominal.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyReleased(java.awt.event.KeyEvent evt) {
                disc_nominalKeyReleased(evt);
            }
        });
        jPanel1.add(disc_nominal, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 150, 170, 30));

        tongkir.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        tongkir.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tongkir.setText("0");
        tongkir.setMargin(new java.awt.Insets(5, 5, 5, 5));
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
        jPanel1.add(tongkir, new org.netbeans.lib.awtextra.AbsoluteConstraints(620, 220, 220, 30));

        tppn_nominal.setEditable(false);
        tppn_nominal.setBackground(new java.awt.Color(204, 255, 255));
        tppn_nominal.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        tppn_nominal.setHorizontalAlignment(javax.swing.JTextField.RIGHT);
        tppn_nominal.setText("0");
        tppn_nominal.setFocusable(false);
        tppn_nominal.setMargin(new java.awt.Insets(5, 5, 5, 5));
        jPanel1.add(tppn_nominal, new org.netbeans.lib.awtextra.AbsoluteConstraints(670, 180, 170, 30));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 871, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, 547, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(0, 0, Short.MAX_VALUE))
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    private void iKembaliKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iKembaliKeyPressed
        if(evt.getKeyCode() == 0)
            dispose();// TODO add your handling code here:
    }//GEN-LAST:event_iKembaliKeyPressed

    private void jButton1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton1ActionPerformed
        
        dkembali = iKembali.getText().replace("Kembali : Rp ", "").replaceAll(",", "");
        if(ibayar.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this, "Nilai Bayar tidak Boleh Kosong", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        } else if(jComboBox1.getSelectedIndex() == 0) {
            JOptionPane.showMessageDialog(this, "Metode Pembayaran Harus Diisi", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        } else if(Integer.valueOf(iJT.getText())>0) {
            if(kNama.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this, "Member harus diisi apabila ingin mengajukan jatuh tempo !!!", "Error", JOptionPane.ERROR_MESSAGE);
                return;
            }
            
        } 
        if(Double.valueOf(dkembali)<0) {
            if(iJT.getText().isEmpty() || Integer.valueOf(iJT.getText())==0) {
                JOptionPane.showMessageDialog(this, "Jatuh Tempo belum di atur !!!", "Error", JOptionPane.ERROR_MESSAGE);
                return;
            }
        }
        if(!two.getText().isEmpty()) {
            if(!kMember.getText().trim().equals(two1.getText().trim())) {
                JOptionPane.showMessageDialog(this, "No WO tidak sesuai dengan Customer/Member yang dipilih !!!", "Error", JOptionPane.ERROR_MESSAGE);
                return;
            }
        }
//        else if(Integer.valueOf(iKembali.getText().replace("Kembali : Rp ", "").replace(",", ""))<0) {
//                JOptionPane.showMessageDialog(this, "Apabila Uang yang dibayarkan Kurang, harap mengisi hari Jatuh Tempo !", "Error", JOptionPane.ERROR_MESSAGE);
//                return;
//            }
        
        status = true; 
        idmarketing = tkodemarketing.getText();
        idmember = kMember.getText();
        namamember = kNama.getText();
        alamatmember = kAlamat.getText();
        discmember = disc_nominal.getText().replaceAll(",", "");
        ppn = tppn.getText().replaceAll(",", "");
        ppn_nominal = tppn_nominal.getText().replaceAll(",", "");
        ongkir = tongkir.getText().replaceAll(",", "");
        wo = two.getText();
        djt = iJT.getText();
        tgljt = tgltempo.getText();
        totalk = totalSeleuruh.getText().replaceAll("Total : Rp ", "").replace(",", "");
        dbayar = ibayar.getText();
        dkembali = iKembali.getText().replace("Kembali : Rp ", "");
        metodebayar = kodeBayar[jComboBox1.getSelectedIndex()];
        metodebayardesc = jComboBox1.getSelectedItem().toString();
        caccll = taccll.getText();
        dispose();        // TODO add your handling code here:
    }//GEN-LAST:event_jButton1ActionPerformed

    private void jButton1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jButton1KeyPressed
        if (evt.getKeyCode()==10) {
            status = true; 
            dispose();    
        }// TODO add your handling code here:
    }//GEN-LAST:event_jButton1KeyPressed

    private void jTextField1ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField1ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField1ActionPerformed

    private void jTextField3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField3ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField3ActionPerformed

    private void iJTCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_iJTCaretUpdate
        try {
            JT();
        } catch (Exception e) {}
    }//GEN-LAST:event_iJTCaretUpdate

    private void jButton2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton2ActionPerformed
        DialogPublicTP.DialogMembers a = new DialogPublicTP.DialogMembers();
        a.setVisible(true);
        
        if(a.status) {
            kMember.setText(a.Kode);
            kNama.setText(a.Nama);
            kAlamat.setText(a.Alamat);
            disc.setText(String.valueOf(a.discc));
            disc.requestFocus();
        }
        
        if(cekTagihanBon(a.Kode)) {
            DialogPublicTP.WarningTunggakanBon b = new DialogPublicTP.WarningTunggakanBon(a.Kode);
            b.setVisible(true);
        }
        
    }//GEN-LAST:event_jButton2ActionPerformed

    private void kMemberKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_kMemberKeyPressed
        if(evt.getKeyCode() == 10 ) {
            disc.requestFocus();
        }// TODO add your handling code here:
    }//GEN-LAST:event_kMemberKeyPressed

    private void discKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_discKeyPressed
        if(evt.getKeyCode() == 10 )
            iJT.requestFocus();// TODO add your handling code here:
    }//GEN-LAST:event_discKeyPressed

    private void iJTKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_iJTKeyPressed
        if(evt.getKeyCode() == 10 )
            ibayar.requestFocus();
    }//GEN-LAST:event_iJTKeyPressed

    private void discFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_discFocusLost
        if(disc.getText().isEmpty()) {
            disc.setText("0");
        }// TODO add your handling code here:
    }//GEN-LAST:event_discFocusLost

    private void iJTFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_iJTFocusLost
        if(iJT.getText().isEmpty()) {
            iJT.setText("0");
        }
    }//GEN-LAST:event_iJTFocusLost

    private void discCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_discCaretUpdate
        try {
            Double hargaBarang = Double.valueOf(subtotal.getText().replaceAll(",", ""));
            Double diskonBarang = 0.0;
            Double ppnn=0.0,ongkir=0.0;
            if(!disc.getText().isEmpty()) {
                diskonBarang = Double.valueOf(disc.getText().replaceAll(",", ""));
                diskonBarang = (hargaBarang*diskonBarang)/100;
                disc_nominal.setText(dFormat.format(diskonBarang));
            }
            //hitung();
        }catch (Exception e) {}        // TODO add your handling code here:
    }//GEN-LAST:event_discCaretUpdate

    private void ibayarCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_ibayarCaretUpdate
       
        try {
            hitungKembali();
        } catch (Exception e ) {}
    }//GEN-LAST:event_ibayarCaretUpdate

    private void jButton3ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton3ActionPerformed
        dispose();        // TODO add your handling code here:
    }//GEN-LAST:event_jButton3ActionPerformed

    private void jTextField5ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField5ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField5ActionPerformed

    private void jButton6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton6ActionPerformed
        kMember.setText(""); 
        kNama.setText(""); 
        kAlamat.setText(""); 
    }//GEN-LAST:event_jButton6ActionPerformed

    private void jTextField2ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField2ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField2ActionPerformed

    private void kMarketingKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_kMarketingKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_kMarketingKeyPressed

    private void jButton4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton4ActionPerformed
        kMarketing.setText("");
        tkodemarketing.setText("");
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkodemarketing.setText(a.Kode);
            kMarketing.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton4ActionPerformed

    private void jTextField4ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField4ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField4ActionPerformed

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogWO a = new DialogWO();
        a.setVisible(true);
        if(a.status){
//            two.setText(a.kode);
//            two1.setText(a.customerid);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jTextField6ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jTextField6ActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_jTextField6ActionPerformed

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

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        DialogAccount a = new DialogAccount("");
        a.setVisible(true);
        if(!"".equals(a.caccnt)) {
            taccll.setText(a.caccnt);
            taccll1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton7ActionPerformed

    private void tppnCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tppnCaretUpdate
        try {
            hitung();
        }catch (Exception e) {}  
    }//GEN-LAST:event_tppnCaretUpdate

    private void tppnFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tppnFocusLost
        // TODO add your handling code here:
    }//GEN-LAST:event_tppnFocusLost

    private void tppnKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_tppnKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_tppnKeyPressed

    private void disc_nominalCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_disc_nominalCaretUpdate
        
    }//GEN-LAST:event_disc_nominalCaretUpdate

    private void tongkirCaretUpdate(javax.swing.event.CaretEvent evt) {//GEN-FIRST:event_tongkirCaretUpdate
        try {
            hitung();
        }catch (Exception e) {}  
    }//GEN-LAST:event_tongkirCaretUpdate

    private void disc_nominalKeyReleased(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_disc_nominalKeyReleased
        try {
            hitung();
        }catch (Exception e) {}  
    }//GEN-LAST:event_disc_nominalKeyReleased

    private void disc_nominalFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_disc_nominalFocusLost
        disc_nominal.setText(dFormat.format(Double.valueOf(disc_nominal.getText().replaceAll(",", ""))));
    }//GEN-LAST:event_disc_nominalFocusLost

    private void tongkirFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tongkirFocusLost
        tongkir.setText(dFormat.format(Double.valueOf(tongkir.getText().replaceAll(",", ""))));
    }//GEN-LAST:event_tongkirFocusLost

 

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JTextField Kasir;
    private javax.swing.JTextField Kwitansi;
    private javax.swing.JTextField TglNow;
    private javax.swing.JTextField disc;
    private javax.swing.JTextField disc_nominal;
    private javax.swing.JTextField iJT;
    private javax.swing.JTextField iKembali;
    private javax.swing.JTextField ibayar;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton3;
    private javax.swing.JButton jButton4;
    private javax.swing.JButton jButton6;
    private javax.swing.JButton jButton7;
    private javax.swing.JComboBox jComboBox1;
    private javax.swing.JLabel jLabel1;
    private javax.swing.JLabel jLabel10;
    private javax.swing.JLabel jLabel11;
    private javax.swing.JLabel jLabel12;
    private javax.swing.JLabel jLabel13;
    private javax.swing.JLabel jLabel2;
    private javax.swing.JLabel jLabel3;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel7;
    private javax.swing.JLabel jLabel8;
    private javax.swing.JLabel jLabel9;
    private javax.swing.JPanel jPanel1;
    private javax.swing.JTextField jTextField1;
    private javax.swing.JTextField jTextField10;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField13;
    private javax.swing.JTextField jTextField14;
    private javax.swing.JTextField jTextField15;
    private javax.swing.JTextField jTextField17;
    private javax.swing.JTextField jTextField18;
    private javax.swing.JTextField jTextField2;
    private javax.swing.JTextField jTextField3;
    private javax.swing.JTextField jTextField4;
    private javax.swing.JTextField jTextField5;
    private javax.swing.JTextField jTextField6;
    private javax.swing.JTextField jTextField9;
    private javax.swing.JLabel kAlamat;
    private javax.swing.JTextField kMarketing;
    private javax.swing.JTextField kMember;
    private javax.swing.JLabel kNama;
    private javax.swing.JTextField subtotal;
    private javax.swing.JTextField taccll;
    private javax.swing.JTextField taccll1;
    private javax.swing.JTextField tgltempo;
    private javax.swing.JLabel tkodemarketing;
    private javax.swing.JTextField tongkir;
    private javax.swing.JTextField totalSeleuruh;
    private javax.swing.JTextField tppn;
    private javax.swing.JTextField tppn_nominal;
    private javax.swing.JTextField two;
    private javax.swing.JTextField two1;
    // End of variables declaration//GEN-END:variables
}
