/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package Production;

import DialogPublicTP.DialogError;
import DialogPublicTP.DialogGudang;
import DialogPublicTP.DialogKaryawan;
import DialogPublicTP.DialogProduksiError;
import DialogPublicTP.DialogProduksiHasil;
import DialogPublicTP.DialogPurchaseOrder;
import DialogPublicTP.DialogSteps;
import DialogPublicTP.DialogWO;
import static Main.DefaultProgram.deffProgram;
import Main.Login;
import Main.MenuAdmin;
import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import Tools.TableColumnAdjuster;
import java.awt.Color;
import java.awt.Component;
import java.awt.event.ActionEvent;
import java.awt.event.KeyEvent;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Timer;
import java.util.TimerTask;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.swing.AbstractAction;
import javax.swing.ActionMap;
import javax.swing.InputMap;
import javax.swing.JComponent;
import javax.swing.JOptionPane;
import javax.swing.JTable;
import javax.swing.KeyStroke;
import javax.swing.table.DefaultTableCellRenderer;
import javax.swing.table.DefaultTableModel;
import javax.swing.table.TableCellRenderer;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class ProduksiInput2 extends javax.swing.JPanel {

    int brs,col;
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="",idstep="",iderr="";
    int barisEdit=-1;
    String akses="PRODUKSI INPUT";
    String idKas="R1";
    String satuanterkecil;
    ArrayList<String> deleteRow;
    List listData;
    ArrayList<String> stepFinish;
    public ProduksiInput2(String mode) {
        initComponents();
        jTable3.setAutoResizeMode(JTable.AUTO_RESIZE_OFF);
        jTable2.setAutoResizeMode(JTable.AUTO_RESIZE_OFF);
        push_button();
        deff();
//        setList();
//        setProses();
//        setKesalahan();
        
        loadData();
    }
    
    private void deff(){
        btDelStep.setVisible(false);
        btCancelStep.setVisible(false);
        jDateTran.setDate(new Date());
        jDateInput.setDate(new Date());
         
        buttonGroup1.add(rbSendiri);
        buttonGroup1.add(rbSubcount);
        rbSendiri.setSelected(true);
        clear_step();
        clear_err();
        //tcodes.setText(deffProgram.get("ccode"));
        tcode.setText(deffProgram.get("ccode"));
        
        sql = "select * from mgudang where ccode = '"+tcode.getText()+"' ";
        rs = con.queryResult(sql);
        try {  while(rs.next()){  tcode1.setText(rs.getString("cname"));}
        } catch (SQLException ex) { ex.printStackTrace();   }
    }
    
    private void loadData(){
        stepFinish = new ArrayList<>();
        stepFinish.clear();
        
        String column="";
        sql="select * from MStepProduction where (cname not like '%PACKING%' and cname not like '%PAKING%'  and cname not like '%PACKAGING%' ) order by urut";
        rs = con.queryResult(sql);
        try {
            while(rs.next()){
                if(rs.getInt("cfinal_process") == 1){
                    stepFinish.add(rs.getString("cname")+"_GOOD");
                } 
                column+="FORMAT(sum(case when b.cstep_kode='"+rs.getString("kode")+"' then case when b.cgood_unit='LEMBAR' then b.cgood*c.cplot else b.cgood end else 0 end), '#,###')    as ["+rs.getString("cname")+"_GOOD],\n" +
                        "FORMAT(sum(case when b.cstep_kode='"+rs.getString("kode")+"' then case when b.cwaste_unit='LEMBAR' then b.cwaste*c.cplot else b.cwaste end else 0 end), '#,###') as ["+rs.getString("cname")+"_WASTE],\n";
            }
        } catch (SQLException ex) {
            Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
        }
        sql="select a.kode as Kode,a.ccode as Gudang,a.cnbkt as SPK,a.dtran as [Tanggal SPK],a.deadline as [Deadline],a.cnbkt_order as NoOrder,a.cproduct as KodeProduk,e.cnameProduct as Produk,FORMAT(a.nqty, '#,###') as Jumlah,\n" +
                column+
                "isnull(format(d.jmlbox,'#,###.##')+' Box ('+format(d.totalpcs,'#,###.##')+' Pcs)','') as PACKAGING_GOOD,format(isnull(d.totalpcs,0),'#,###.##') as TOTALHASILITEM,0 as Percentase\n" +
                "from PWorkOrder a\n" +
                "left join PProdSteps b on a.cnbkt=b.cnbkt and a.ccode=b.ccode\n" +
                "join BOMProduct c on a.cnbkt_bom=c.cnbkt\n" +
                "left join (select a.cworkorder,a.ccode,count(b.id) as jmlbox,sum(b.nqty_small) as totalpcs,b.csatuan_small from PBoxPacking a\n" +
                    "join PBoxPackingD b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
                    "group by a.cworkorder,a.ccode,b.csatuan_small) d on a.ccode=d.ccode and a.cnbkt=d.cworkorder \n"+
                "join MProduk e on a.cgroup=e.cgroup and a.cproduct=e.cproduct\n"+
                "group by a.kode,a.ccode,a.cnbkt,a.dtran,a.deadline,a.cnbkt_order,a.cproduct,a.nqty,d.jmlbox,d.totalpcs,e.cnameProduct\n" +
                "order by a.dtran asc";
        System.out.println(sql);
        listData = con.queryToListMap(this, sql);
        if(listData.size()<=0) {
            return;
        }
        LinkedHashMap mapData = (LinkedHashMap) listData.get(listData.size() - 1);
        
        List listHeader = new ArrayList();
        listHeader.add(listHeader.size(), "No");
        for (int i = 0; i < mapData.size(); i++) {
            listHeader.add(listHeader.size(), mapData.keySet().toArray()[i].toString().replaceAll("'", ""));
        }
        
        DefaultTableModel tabelModel = new DefaultTableModel() {

            @Override
            public boolean isCellEditable(int row, int column) {
                return false;
            }

            @Override
            public Class getColumnClass(int col) {
//                if (getRowCount() > 0) {
//                    try {
//                        return getValueAt(0, col).getClass();
//                    } catch (Exception ex) {
//                        return Object.class;
//                    }
//                } else {
//                    return Object.class;
//                }
                return Object.class;
            }
        };
        for (int i = 0; i < listHeader.size(); i++) {
            tabelModel.addColumn(listHeader.get(i));
        }
        jTable2.setModel(tabelModel);
        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();

        reloadData();
    }
    private void reloadData(){
        DefaultTableModel tabelModel = (DefaultTableModel) jTable2.getModel();
        rfa.clear_table(keyssDef, jTable2);
 
        ArrayList<Integer> colorSet = new ArrayList<>();
        colorSet.clear();
        int count=0;
        int ixRow = 0;
        for (int row = 0; row < listData.size(); row++) { 
            
            LinkedHashMap mapResult = (LinkedHashMap) listData.get(row);
            //System.out.println(mapResult);
            Double target = Double.valueOf(mapResult.get("Jumlah").toString().replaceAll(",", ""));
            Double capai=0.0;
            if(!mapResult.get("TOTALHASILITEM").toString().equals("")){
                capai = Double.valueOf(mapResult.get("TOTALHASILITEM").toString().replaceAll(",", ""));
            }
             
            Double percent = (Double)(capai/target)*100;
            mapResult.put("Percentase", percent);
            
            if(percent>=100.0) {
                colorSet.add(ixRow);
            }
            
            Object[] data = new Object[mapResult.size() + 1];
            data[0] = row + 1;
            System.arraycopy(mapResult.values().toArray(), 0, data, 1, data.length - 1);
            
            tabelModel.insertRow(ixRow, data);
            ixRow++;
        }

        TableColumnAdjuster tca = new TableColumnAdjuster(jTable2);
        tca.adjustColumns();
 
        
        jTable2.setAutoResizeMode(JTable.AUTO_RESIZE_OFF);
        
        System.out.println(colorSet);
        
        jTable2.setDefaultRenderer(Object.class, new TableCellRenderer(){
        private DefaultTableCellRenderer DEFAULT_RENDERER =  new DefaultTableCellRenderer();

            @Override
            public Component getTableCellRendererComponent(JTable table, Object value, boolean isSelected, boolean hasFocus, int row, int column) {
                Component c = DEFAULT_RENDERER.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);
//                if(isSelected){
//                    //c.setBackground(Color.YELLOW);
//                }else{
//                    for(int i=0; i<colorSet.size(); i++){
//                        if(row == colorSet.get(i)) {
//                            System.out.println(colorSet.get(i));
//                            c.setBackground(Color.LIGHT_GRAY);
//                            //break;
//                        } else {
//                            c.setBackground(Color.WHITE);
//                        }
//                    }
//                  
//                }
                c.setBackground(Color.WHITE);
                for(int i=0; i<colorSet.size(); i++){
                    if(row == colorSet.get(i)) {
                        System.out.println(colorSet.get(i));
                        c.setBackground(Color.LIGHT_GRAY);
                        //break;
                    } 
//                    else {
//                        c.setBackground(Color.WHITE);
//                    }
                }
//                if(row > 5)
//        {
//            c.setForeground(Color.black);        
//            c.setBackground(Color.red);            
//        }    
//        else
//        {    
//            c.setBackground(Color.white);    
//            c.setForeground(Color.black);    
//        } 

                
       //Add below code here
                return c;
            }

        });
    }
    
    
    private void setList(){
        DefaultTableModel model = (DefaultTableModel) jTable2.getModel();
        model.addRow(new Object[]{
            "01/01/2022","0001/SPK/2022","001/PO/2022","Bungkus","4",
            "2000","100",
            "2000","100",
            "2000","100",
            "2000","100",
            "2000","100",
            "2000","100",
            "2000","100",
            "2000","100","100",
            "Finish","101%"
        });
        model.addRow(new Object[]{
            "01/01/2022","0002/SPK/2022","001/PO/2022","Bungkus 2","10",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "2000","100","100",
            "Proses","97%"
        });
        model.addRow(new Object[]{
            "01/01/2022","0002/SPK/2022","001/PO/2022","Bungkus 2","10",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200","100",
            "2000","100",
            "Proses","97%"
        });
        model.addRow(new Object[]{
            "01/01/2022","0002/SPK/2022","001/PO/2022","Bungkus 2","10",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200",
            "3000","200","100",
            "3000","200",
            "3000","200",
            "2000","100",
            "Finish","100%"
        });
        jTable2.setAutoResizeMode(JTable.AUTO_RESIZE_OFF);
        
        jTable2.setDefaultRenderer(Object.class, new TableCellRenderer(){
        private DefaultTableCellRenderer DEFAULT_RENDERER =  new DefaultTableCellRenderer();

            @Override
            public Component getTableCellRendererComponent(JTable table, Object value, boolean isSelected, boolean hasFocus, int row, int column) {
                Component c = DEFAULT_RENDERER.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);
                if(isSelected){
                    c.setBackground(Color.YELLOW);
                }else{
                    if (row == 0 || row == 3){
                        c.setBackground(Color.LIGHT_GRAY);

                    }
                    else {
                        c.setBackground(Color.WHITE);
                    }     
                }

                
       //Add below code here
                return c;
            }

        });
    }
    
    private void setProses(){
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        model.addRow(new Object[]{
            "01/01/2022","Operator","1,000 lbr (10,000 pcs)","2,000 lbr (20,000 pcs)"
        });
    }
    private void setKesalahan(){
        DefaultTableModel model = (DefaultTableModel) jTable4.getModel();
        model.addRow(new Object[]{
            "1","Warna Belang"
        });
        model.addRow(new Object[]{
            "2","Warna Muda"
        });
    }
    
    private void push_button(){
        InputMap im = this.getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW);
        ActionMap am = this.getActionMap();

            
        im.put(KeyStroke.getKeyStroke(KeyEvent.VK_F9, 0), "payment");
        am.put("payment", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) { 
            }
        });
    }

    
    private void load_stepinput(){
        sql="select a.*,c.cplot,d.cname as operator,\n" +
            "case when a.cgood_unit='LEMBAR' then convert(varchar,a.cgood)+ ' Lembar ('+convert(varchar,a.cgood*c.cplot)+' Pcs)' else convert(varchar,a.cgood)+' Pcs' end as good,\n" +
            "case when a.cwaste_unit='LEMBAR' then convert(varchar,a.cwaste)+ ' Lembar ('+convert(varchar,a.cwaste*c.cplot)+' Pcs)' else convert(varchar,a.cwaste)+' Pcs' end as waste\n" +
            "from PProdSteps a\n" +
            "join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join BOMProduct c on b.cnbkt_bom=c.cnbkt\n" +
            "join MKaryawan d on a.cnik=d.nik\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+two.getText()+"' and a.cstep_kode = '"+tstepkode.getText()+"' \n"+
            "order by a.dtran desc";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable3);
        DefaultTableModel model = (DefaultTableModel) jTable3.getModel();
        try {
            while(rs.next()) {
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("operator"),
                    rs.getString("good"),
                    rs.getString("waste"),
                    rs.getString("ctype"),
                    rs.getString("cnbkt_po"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
        }
        con.resizeColumnWidth(jTable3);
    } 
    private void clear_step(){
        rfa.empty(keyssDef,  tgood,twaste,tnotestep,tbukti_po);
        btAddSteps.setText("Add");
        btDelStep.setVisible(false);
        btCancelStep.setVisible(false);
        rbSendiri.setSelected(true);
        
    }
    private void save_step(int mode){
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",two.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef,jDateInput.getDate(),"yyyy-MM-dd"));
        listObj.put("cstep_kode",tstepkode.getText());
        listObj.put("cstep_name",tstep.getText());
        listObj.put("cnik",tkaryawankode.getText());
        listObj.put("cgood",tgood.getText());
        listObj.put("cgood_unit",listgoodunit.getSelectedItem().toString());
        listObj.put("cwaste",twaste.getText());
        listObj.put("cwaste_unit",listwasteunit.getSelectedItem().toString());
        listObj.put("cnote",tnotestep.getText());
        
        if(rbSubcount.isSelected()) listObj.put("ctype","1");
        if(rbSendiri.isSelected()) listObj.put("ctype","0");
        
        listObj.put("cnbkt_po",tbukti_po.getText());
        listObj.put("cusername",Login.user);
        
        if(mode == 0) {
            sql=con.insertValue("PProdSteps", listObj);
        } else {
            sql=con.updateValue("PProdSteps", listObj);
            sql+=" where id="+idstep;
        }
        
        if(con.queryUpdate(sql)>0) {
            if(mode == 0) JOptionPane.showMessageDialog(this,"Input Success", "Save", JOptionPane.INFORMATION_MESSAGE);
            else  JOptionPane.showMessageDialog(this,"Update Success", "Save", JOptionPane.INFORMATION_MESSAGE);
            
            load_stepinput();
            clear_step();
            
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menambahkan", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void delete_step(){
        sql="delete from PProdSteps where id="+idstep;
        if(con.queryUpdate(sql)>0) {
            JOptionPane.showMessageDialog(this,"Delete Success", "Save", JOptionPane.INFORMATION_MESSAGE);
            load_stepinput();
            clear_step();
            btAddSteps.setText("Add");
            btDelStep.setVisible(false);
        } else {
            JOptionPane.showMessageDialog(this,"Delete Gagal", "Save", JOptionPane.ERROR);
            
        }
    }
    
    private void clear_err(){
        rfa.empty(keyssDef, terror,terrorkode,tqty_fail);
        btAddErr.setText("Add");
        btDelErr.setVisible(false);
        btCancelErr.setVisible(false);
    }
    private void load_errinput(){
        sql="select a.*,c.cplot\n" +
            "from PProdErr a\n" +
            "join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join BOMProduct c on b.cnbkt_bom=c.cnbkt\n" +
            "where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+two.getText()+"' and a.cstep_kode = '"+tstepkode.getText()+"' \n"+
            "order by a.dtran desc";
        rs = con.queryResult(sql);
        rfa.clear_table(MenuAdmin.keyssDef, jTable4);
        DefaultTableModel model = (DefaultTableModel) jTable4.getModel();
        try {
            while(rs.next()) {
                model.addRow(new Object[]{
                    rs.getString("id"),
                    rfa.DateString(keyssDef,rs.getDate("dtran"),"dd/MM/yyyy"),
                    rs.getString("cerr_name"),
                    rs.getInt("nqty_fail"),
                });
            }
        } catch (SQLException ex) {
            Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
        }
        con.resizeColumnWidth(jTable3);
    }
    private void save_error(int mode){
        HashMap<String,String> listObj = new HashMap<String,String>();        
        listObj.put("cnbkt",two.getText());
        listObj.put("ccode",tcode.getText());
        listObj.put("dtran",rfa.DateString(keyssDef,jDateInput.getDate(),"yyyy-MM-dd"));
        listObj.put("cstep_kode",tstepkode.getText());
        listObj.put("cstep_name",tstep.getText());
        listObj.put("cerr_kode",terrorkode.getText());
        listObj.put("cerr_name",terror.getText());
        listObj.put("cnik",tkaryawankode.getText());
        listObj.put("nqty_fail",tqty_fail.getText());
        listObj.put("cusername",Login.user);
        
        if(mode == 0) {
            sql=con.insertValue("PProdErr", listObj);
        } else {
            sql=con.updateValue("PProdErr", listObj);
            sql+=" where id="+iderr;
        }
        
        if(con.queryUpdate(sql)>0) {
            if(mode == 0) JOptionPane.showMessageDialog(this,"Input Success", "Save", JOptionPane.INFORMATION_MESSAGE);
            else  JOptionPane.showMessageDialog(this,"Update Success", "Save", JOptionPane.INFORMATION_MESSAGE);
            
            load_errinput();
            clear_err();
            
        } else {
            JOptionPane.showMessageDialog(this,"Gagal Menambahkan", "Save", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void delete_error(){
        sql="delete from PProdErr where id="+iderr;
        if(con.queryUpdate(sql)>0) {
            JOptionPane.showMessageDialog(this,"Delete Success", "Save", JOptionPane.INFORMATION_MESSAGE);
            load_errinput();
            clear_err();
        } else {
            JOptionPane.showMessageDialog(this,"Delete Gagal", "Save", JOptionPane.ERROR);
            
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

        buttonGroup1 = new javax.swing.ButtonGroup();
        jPanel9 = new javax.swing.JPanel();
        jLabel17 = new javax.swing.JLabel();
        jLabel23 = new javax.swing.JLabel();
        jLabel24 = new javax.swing.JLabel();
        jLabel25 = new javax.swing.JLabel();
        jLabel26 = new javax.swing.JLabel();
        jLabel27 = new javax.swing.JLabel();
        jLabel28 = new javax.swing.JLabel();
        jLabel29 = new javax.swing.JLabel();
        jLabel31 = new javax.swing.JLabel();
        jLabel32 = new javax.swing.JLabel();
        jLabel33 = new javax.swing.JLabel();
        jLabel34 = new javax.swing.JLabel();
        jTabbedPane1 = new javax.swing.JTabbedPane();
        jPanel4 = new javax.swing.JPanel();
        jPanel6 = new javax.swing.JPanel();
        jLabel9 = new javax.swing.JLabel();
        jLabel12 = new javax.swing.JLabel();
        jTextField9 = new javax.swing.JTextField();
        jDateTran1 = new com.toedter.calendar.JDateChooser();
        jLabel13 = new javax.swing.JLabel();
        jDateTran2 = new com.toedter.calendar.JDateChooser();
        jTextField10 = new javax.swing.JTextField();
        jButton7 = new javax.swing.JButton();
        jLabel15 = new javax.swing.JLabel();
        jButton1 = new javax.swing.JButton();
        jLabel35 = new javax.swing.JLabel();
        jTextField21 = new javax.swing.JTextField();
        jButton2 = new javax.swing.JButton();
        jScrollPane2 = new javax.swing.JScrollPane();
        jTable2 = new javax.swing.JTable();
        jTextField11 = new javax.swing.JTextField();
        jTextField12 = new javax.swing.JTextField();
        jPanel5 = new javax.swing.JPanel();
        jPanel1 = new javax.swing.JPanel();
        jLabel4 = new javax.swing.JLabel();
        jDateTran = new com.toedter.calendar.JDateChooser();
        jLabel14 = new javax.swing.JLabel();
        two = new javax.swing.JTextField();
        jButton9 = new javax.swing.JButton();
        jLabel18 = new javax.swing.JLabel();
        ttarget = new javax.swing.JTextField();
        jLabel19 = new javax.swing.JLabel();
        jLabel10 = new javax.swing.JLabel();
        tproduk = new javax.swing.JTextField();
        tpo = new javax.swing.JTextField();
        jLabel37 = new javax.swing.JLabel();
        tcode = new javax.swing.JTextField();
        jButton13 = new javax.swing.JButton();
        tcode1 = new javax.swing.JTextField();
        jLabel3 = new javax.swing.JLabel();
        jLabel22 = new javax.swing.JLabel();
        tcustomer = new javax.swing.JTextField();
        jScrollPane1 = new javax.swing.JScrollPane();
        tnote = new javax.swing.JTextArea();
        jLabel39 = new javax.swing.JLabel();
        jScrollPane5 = new javax.swing.JScrollPane();
        tnotespk = new javax.swing.JTextArea();
        jLabel40 = new javax.swing.JLabel();
        tdeadline = new javax.swing.JTextField();
        jPanel8 = new javax.swing.JPanel();
        jTextField30 = new javax.swing.JTextField();
        jScrollPane4 = new javax.swing.JScrollPane();
        jTable4 = new javax.swing.JTable();
        jPanel2 = new javax.swing.JPanel();
        jLabel16 = new javax.swing.JLabel();
        terror = new javax.swing.JTextField();
        jButton10 = new javax.swing.JButton();
        terrorkode = new javax.swing.JTextField();
        btAddErr = new javax.swing.JButton();
        btCancelErr = new javax.swing.JButton();
        btDelErr = new javax.swing.JButton();
        jLabel38 = new javax.swing.JLabel();
        tqty_fail = new javax.swing.JTextField();
        jPanel7 = new javax.swing.JPanel();
        jScrollPane3 = new javax.swing.JScrollPane();
        jTable3 = new javax.swing.JTable();
        jTextField25 = new javax.swing.JTextField();
        jPanel3 = new javax.swing.JPanel();
        jLabel5 = new javax.swing.JLabel();
        jDateInput = new com.toedter.calendar.JDateChooser();
        jLabel30 = new javax.swing.JLabel();
        tstep = new javax.swing.JTextField();
        jButton12 = new javax.swing.JButton();
        tstepkode = new javax.swing.JTextField();
        tkaryawankode = new javax.swing.JTextField();
        jButton11 = new javax.swing.JButton();
        tkaryawan = new javax.swing.JTextField();
        jLabel8 = new javax.swing.JLabel();
        jLabel20 = new javax.swing.JLabel();
        tgood = new javax.swing.JTextField();
        listgoodunit = new javax.swing.JComboBox();
        jLabel36 = new javax.swing.JLabel();
        jLabel21 = new javax.swing.JLabel();
        tnotestep = new javax.swing.JTextField();
        twaste = new javax.swing.JTextField();
        listwasteunit = new javax.swing.JComboBox();
        btAddSteps = new javax.swing.JButton();
        btDelStep = new javax.swing.JButton();
        btCancelStep = new javax.swing.JButton();
        rbSubcount = new javax.swing.JRadioButton();
        rbSendiri = new javax.swing.JRadioButton();
        jLabel11 = new javax.swing.JLabel();
        tbukti_po = new javax.swing.JTextField();
        jButton14 = new javax.swing.JButton();

        jPanel9.setBackground(new java.awt.Color(102, 102, 255));
        jPanel9.setBorder(javax.swing.BorderFactory.createEtchedBorder());
        jPanel9.setForeground(new java.awt.Color(102, 102, 255));
        jPanel9.setToolTipText("");
        jPanel9.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel17.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel17.setText("1,000,000 (pcs)");
        jPanel9.add(jLabel17, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 10, 130, -1));

        jLabel23.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel23.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        jLabel23.setText("Target :");
        jPanel9.add(jLabel23, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 10, 72, -1));

        jLabel24.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel24.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        jLabel24.setText("Good :");
        jPanel9.add(jLabel24, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 30, 72, -1));

        jLabel25.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel25.setText("1,000,000 (Pcs)");
        jPanel9.add(jLabel25, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 50, 130, -1));

        jLabel26.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel26.setText("1,000,000 (Lbr)");
        jPanel9.add(jLabel26, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 30, 130, -1));

        jLabel27.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel27.setHorizontalAlignment(javax.swing.SwingConstants.RIGHT);
        jLabel27.setText("Waste :");
        jPanel9.add(jLabel27, new org.netbeans.lib.awtextra.AbsoluteConstraints(10, 70, 72, -1));

        jLabel28.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel28.setText("1,000 (Lbr)");
        jPanel9.add(jLabel28, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 70, 130, -1));

        jLabel29.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel29.setText("1,000 (Pcs)");
        jPanel9.add(jLabel29, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 90, 130, -1));

        jLabel31.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel31.setText("0.4%");
        jPanel9.add(jLabel31, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 50, 130, -1));

        jLabel32.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel32.setText("0.4%");
        jPanel9.add(jLabel32, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 30, 130, -1));

        jLabel33.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel33.setText("0.4%");
        jPanel9.add(jLabel33, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 90, 130, -1));

        jLabel34.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jLabel34.setText("0.4%");
        jPanel9.add(jLabel34, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 70, 130, -1));

        jPanel6.setBorder(javax.swing.BorderFactory.createTitledBorder("Filter"));
        jPanel6.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel9.setText("s/d");
        jPanel6.add(jLabel9, new org.netbeans.lib.awtextra.AbsoluteConstraints(520, 30, 20, 20));

        jLabel12.setText("No SPK");
        jPanel6.add(jLabel12, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 30, -1, 20));
        jPanel6.add(jTextField9, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 30, 140, -1));

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
        jPanel6.add(jDateTran1, new org.netbeans.lib.awtextra.AbsoluteConstraints(550, 30, 150, -1));

        jLabel13.setText("Periode ");
        jPanel6.add(jLabel13, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 30, -1, 20));

        jDateTran2.setDateFormatString("dd/MM/yyyy");
        jDateTran2.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateTran2PropertyChange(evt);
            }
        });
        jDateTran2.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateTran2KeyPressed(evt);
            }
        });
        jPanel6.add(jDateTran2, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 30, 150, -1));
        jPanel6.add(jTextField10, new org.netbeans.lib.awtextra.AbsoluteConstraints(100, 60, 110, -1));

        jButton7.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton7.setText("Cari");
        jButton7.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton7ActionPerformed(evt);
            }
        });
        jPanel6.add(jButton7, new org.netbeans.lib.awtextra.AbsoluteConstraints(730, 30, 90, -1));

        jLabel15.setText("PO Customer");
        jPanel6.add(jLabel15, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 60, -1, 20));

        jButton1.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jPanel6.add(jButton1, new org.netbeans.lib.awtextra.AbsoluteConstraints(210, 60, 30, -1));

        jLabel35.setText("Customer");
        jPanel6.add(jLabel35, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 60, -1, 20));
        jPanel6.add(jTextField21, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 60, 120, -1));

        jButton2.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jPanel6.add(jButton2, new org.netbeans.lib.awtextra.AbsoluteConstraints(480, 60, 30, -1));

        jTable2.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "Tanggal", "No SPK", "No PO", "Nama Produk", "Up", "Cetak (OK)", "Cetak (Waste)", "Spot UV (OK)", "Spot UV (Waste)", "Hotprint (OK)", "Hotprint (Wsate)", "Plong (OK)", "Plong (Waste)", "Protol (OK)", "Protol (Waste)", "Lem (OK)", "Lem (Waste)", "Sortir (OK)", "Sortir (Waste)", "Packing (OK)", "Packing (Waste)", "Waste Lainnya", "Status", "Persentase"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, false
            };

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
            jTable2.getColumnModel().getColumn(0).setMinWidth(100);
            jTable2.getColumnModel().getColumn(1).setMinWidth(100);
            jTable2.getColumnModel().getColumn(2).setMinWidth(100);
            jTable2.getColumnModel().getColumn(3).setMinWidth(100);
            jTable2.getColumnModel().getColumn(4).setMinWidth(80);
            jTable2.getColumnModel().getColumn(5).setMinWidth(100);
            jTable2.getColumnModel().getColumn(6).setMinWidth(100);
            jTable2.getColumnModel().getColumn(7).setMinWidth(100);
            jTable2.getColumnModel().getColumn(8).setMinWidth(100);
            jTable2.getColumnModel().getColumn(9).setMinWidth(100);
            jTable2.getColumnModel().getColumn(10).setMinWidth(100);
            jTable2.getColumnModel().getColumn(11).setMinWidth(100);
            jTable2.getColumnModel().getColumn(12).setMinWidth(100);
            jTable2.getColumnModel().getColumn(13).setMinWidth(100);
            jTable2.getColumnModel().getColumn(14).setMinWidth(100);
            jTable2.getColumnModel().getColumn(15).setMinWidth(100);
            jTable2.getColumnModel().getColumn(16).setMinWidth(100);
            jTable2.getColumnModel().getColumn(17).setMinWidth(100);
            jTable2.getColumnModel().getColumn(18).setMinWidth(100);
            jTable2.getColumnModel().getColumn(19).setMinWidth(100);
            jTable2.getColumnModel().getColumn(20).setMinWidth(100);
            jTable2.getColumnModel().getColumn(21).setMinWidth(100);
            jTable2.getColumnModel().getColumn(22).setMinWidth(70);
            jTable2.getColumnModel().getColumn(23).setMinWidth(50);
        }

        jTextField11.setEditable(false);
        jTextField11.setBackground(new java.awt.Color(255, 255, 255));
        jTextField11.setFont(new java.awt.Font("Tahoma", 3, 11)); // NOI18N
        jTextField11.setHorizontalAlignment(javax.swing.JTextField.CENTER);
        jTextField11.setText("Double Click to View Kesalahan");

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
                    .addComponent(jPanel6, javax.swing.GroupLayout.Alignment.TRAILING, javax.swing.GroupLayout.DEFAULT_SIZE, 1280, Short.MAX_VALUE)
                    .addGroup(jPanel4Layout.createSequentialGroup()
                        .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, 277, javax.swing.GroupLayout.PREFERRED_SIZE)
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
                .addComponent(jScrollPane2, javax.swing.GroupLayout.DEFAULT_SIZE, 373, Short.MAX_VALUE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addGroup(jPanel4Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.BASELINE)
                    .addComponent(jTextField11, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                    .addComponent(jTextField12, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("List", jPanel4);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel4.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel4.setText("Periode Mulai");
        jPanel1.add(jLabel4, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 50, 100, 20));

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

        jLabel14.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel14.setText("No SPK");
        jPanel1.add(jLabel14, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 80, 130, 20));

        two.setEditable(false);
        jPanel1.add(two, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 80, 260, -1));

        jButton9.setText("...");
        jButton9.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton9ActionPerformed(evt);
            }
        });
        jPanel1.add(jButton9, new org.netbeans.lib.awtextra.AbsoluteConstraints(410, 80, 30, -1));

        jLabel18.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel18.setText("Deadline Produksi");
        jPanel1.add(jLabel18, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 390, 130, 20));

        ttarget.setEditable(false);
        ttarget.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(ttarget, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 360, 290, -1));

        jLabel19.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel19.setText("Customer");
        jPanel1.add(jLabel19, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 140, 110, 20));

        jLabel10.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel10.setText("No PO Customer");
        jPanel1.add(jLabel10, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 110, 110, 20));

        tproduk.setEditable(false);
        tproduk.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tproduk, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 170, 290, -1));

        tpo.setEditable(false);
        tpo.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tpo, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 110, 290, -1));

        jLabel37.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel37.setText("Keterangan SPK");
        jPanel1.add(jLabel37, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 200, 110, 20));
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
        jPanel1.add(tcode1, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 20, 160, -1));

        jLabel3.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel3.setText("Kode Gudang");
        jPanel1.add(jLabel3, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 20, 110, 20));

        jLabel22.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel22.setText("Produk");
        jPanel1.add(jLabel22, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 170, 110, 20));

        tcustomer.setEditable(false);
        tcustomer.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tcustomer, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 140, 290, -1));

        jScrollPane1.setEnabled(false);

        tnote.setEditable(false);
        tnote.setBackground(new java.awt.Color(204, 204, 255));
        tnote.setColumns(20);
        tnote.setRows(5);
        jScrollPane1.setViewportView(tnote);

        jPanel1.add(jScrollPane1, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 200, 290, 70));

        jLabel39.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel39.setText("Keterangan PO");
        jPanel1.add(jLabel39, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 280, 110, 20));

        jScrollPane5.setEnabled(false);

        tnotespk.setEditable(false);
        tnotespk.setBackground(new java.awt.Color(204, 204, 255));
        tnotespk.setColumns(20);
        tnotespk.setRows(5);
        jScrollPane5.setViewportView(tnotespk);

        jPanel1.add(jScrollPane5, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 280, 290, 70));

        jLabel40.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel40.setText("Target Produksi");
        jPanel1.add(jLabel40, new org.netbeans.lib.awtextra.AbsoluteConstraints(20, 360, 130, 20));

        tdeadline.setEditable(false);
        tdeadline.setBackground(new java.awt.Color(204, 204, 255));
        jPanel1.add(tdeadline, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 390, 290, -1));

        jPanel8.setBorder(javax.swing.BorderFactory.createEtchedBorder());

        jTextField30.setEditable(false);
        jTextField30.setBackground(new java.awt.Color(102, 102, 255));
        jTextField30.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField30.setForeground(new java.awt.Color(255, 255, 255));
        jTextField30.setText("Kesalahan Produksi");

        jTable4.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "Tanggal", "Kesalahan", "Qty"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                false, false, false, false
            };

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
            jTable4.getColumnModel().getColumn(0).setMinWidth(0);
            jTable4.getColumnModel().getColumn(0).setMaxWidth(0);
        }

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel16.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel16.setText("Qty ");
        jPanel2.add(jLabel16, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 40, 60, 20));
        jPanel2.add(terror, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, 230, -1));

        jButton10.setText("...");
        jButton10.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton10ActionPerformed(evt);
            }
        });
        jPanel2.add(jButton10, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 10, 30, -1));
        jPanel2.add(terrorkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 10, -1, -1));

        btAddErr.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        btAddErr.setText("Add");
        btAddErr.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btAddErrActionPerformed(evt);
            }
        });
        jPanel2.add(btAddErr, new org.netbeans.lib.awtextra.AbsoluteConstraints(150, 60, 90, -1));

        btCancelErr.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancelErr.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelErrActionPerformed(evt);
            }
        });
        jPanel2.add(btCancelErr, new org.netbeans.lib.awtextra.AbsoluteConstraints(290, 60, 30, -1));

        btDelErr.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelErr.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDelErrActionPerformed(evt);
            }
        });
        btDelErr.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btDelErrKeyPressed(evt);
            }
        });
        jPanel2.add(btDelErr, new org.netbeans.lib.awtextra.AbsoluteConstraints(250, 60, 30, -1));

        jLabel38.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel38.setText("Fail");
        jPanel2.add(jLabel38, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 10, 60, 20));
        jPanel2.add(tqty_fail, new org.netbeans.lib.awtextra.AbsoluteConstraints(50, 40, 70, -1));

        javax.swing.GroupLayout jPanel8Layout = new javax.swing.GroupLayout(jPanel8);
        jPanel8.setLayout(jPanel8Layout);
        jPanel8Layout.setHorizontalGroup(
            jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jTextField30, javax.swing.GroupLayout.DEFAULT_SIZE, 355, Short.MAX_VALUE)
            .addGroup(jPanel8Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel2, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addComponent(jScrollPane4, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE))
                .addContainerGap())
        );
        jPanel8Layout.setVerticalGroup(
            jPanel8Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel8Layout.createSequentialGroup()
                .addComponent(jTextField30, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.UNRELATED)
                .addComponent(jPanel2, javax.swing.GroupLayout.PREFERRED_SIZE, 107, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane4, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE)
                .addGap(19, 19, 19))
        );

        jPanel7.setBorder(javax.swing.BorderFactory.createEtchedBorder());

        jTable3.setModel(new javax.swing.table.DefaultTableModel(
            new Object [][] {

            },
            new String [] {
                "id", "Tgl", "Operator", "Good", "Waste", "Type", "No PO"
            }
        ) {
            boolean[] canEdit = new boolean [] {
                true, false, false, false, false, false, false
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
        }

        jTextField25.setEditable(false);
        jTextField25.setBackground(new java.awt.Color(102, 102, 255));
        jTextField25.setFont(new java.awt.Font("Tahoma", 1, 12)); // NOI18N
        jTextField25.setForeground(new java.awt.Color(255, 255, 255));
        jTextField25.setText("Hasil Produksi");

        jPanel3.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jLabel5.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel5.setText("Tgl Produksi");
        jPanel3.add(jLabel5, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 0, 100, 20));

        jDateInput.setDateFormatString("dd/MM/yyyy");
        jDateInput.addPropertyChangeListener(new java.beans.PropertyChangeListener() {
            public void propertyChange(java.beans.PropertyChangeEvent evt) {
                jDateInputPropertyChange(evt);
            }
        });
        jDateInput.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                jDateInputKeyPressed(evt);
            }
        });
        jPanel3.add(jDateInput, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 0, 120, -1));

        jLabel30.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel30.setText("Step Produksi");
        jPanel3.add(jLabel30, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 25, 130, 20));

        tstep.setEditable(false);
        tstep.setBackground(new java.awt.Color(204, 204, 255));
        jPanel3.add(tstep, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 25, 190, -1));

        jButton12.setText("...");
        jButton12.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton12ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton12, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 25, 30, -1));

        tstepkode.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                tstepkodeActionPerformed(evt);
            }
        });
        jPanel3.add(tstepkode, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 25, 5, -1));
        jPanel3.add(tkaryawankode, new org.netbeans.lib.awtextra.AbsoluteConstraints(310, 50, 5, -1));

        jButton11.setText("...");
        jButton11.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton11ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton11, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 50, 30, -1));

        tkaryawan.setEditable(false);
        jPanel3.add(tkaryawan, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 50, 190, -1));

        jLabel8.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel8.setText("No PO");
        jPanel3.add(jLabel8, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 120, 130, 20));

        jLabel20.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel20.setText("Good");
        jPanel3.add(jLabel20, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 150, 80, 20));
        jPanel3.add(tgood, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 150, 80, -1));

        listgoodunit.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "PCS", "LEMBAR" }));
        jPanel3.add(listgoodunit, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 150, 90, -1));

        jLabel36.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel36.setText("Waste");
        jPanel3.add(jLabel36, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 175, 80, 20));

        jLabel21.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel21.setText("Note");
        jPanel3.add(jLabel21, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 200, 80, 20));

        tnotestep.addFocusListener(new java.awt.event.FocusAdapter() {
            public void focusLost(java.awt.event.FocusEvent evt) {
                tnotestepFocusLost(evt);
            }
        });
        jPanel3.add(tnotestep, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 200, 290, -1));
        jPanel3.add(twaste, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 175, 80, -1));

        listwasteunit.setModel(new javax.swing.DefaultComboBoxModel(new String[] { "PCS", "LEMBAR" }));
        jPanel3.add(listwasteunit, new org.netbeans.lib.awtextra.AbsoluteConstraints(170, 175, 90, -1));

        btAddSteps.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Add_16px.png"))); // NOI18N
        btAddSteps.setText("Add");
        btAddSteps.setMargin(new java.awt.Insets(2, 2, 2, 2));
        btAddSteps.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btAddStepsActionPerformed(evt);
            }
        });
        jPanel3.add(btAddSteps, new org.netbeans.lib.awtextra.AbsoluteConstraints(270, 170, 80, -1));

        btDelStep.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Delete_Bin_16px.png"))); // NOI18N
        btDelStep.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btDelStepActionPerformed(evt);
            }
        });
        btDelStep.addKeyListener(new java.awt.event.KeyAdapter() {
            public void keyPressed(java.awt.event.KeyEvent evt) {
                btDelStepKeyPressed(evt);
            }
        });
        jPanel3.add(btDelStep, new org.netbeans.lib.awtextra.AbsoluteConstraints(360, 170, 30, -1));

        btCancelStep.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/cancel.png"))); // NOI18N
        btCancelStep.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                btCancelStepActionPerformed(evt);
            }
        });
        jPanel3.add(btCancelStep, new org.netbeans.lib.awtextra.AbsoluteConstraints(400, 170, 30, -1));

        rbSubcount.setText("Subcount");
        rbSubcount.addItemListener(new java.awt.event.ItemListener() {
            public void itemStateChanged(java.awt.event.ItemEvent evt) {
                rbSubcountItemStateChanged(evt);
            }
        });
        rbSubcount.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                rbSubcountMouseClicked(evt);
            }
        });
        jPanel3.add(rbSubcount, new org.netbeans.lib.awtextra.AbsoluteConstraints(180, 90, -1, -1));

        rbSendiri.setText("Prod Sendiri");
        rbSendiri.addItemListener(new java.awt.event.ItemListener() {
            public void itemStateChanged(java.awt.event.ItemEvent evt) {
                rbSendiriItemStateChanged(evt);
            }
        });
        rbSendiri.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseClicked(java.awt.event.MouseEvent evt) {
                rbSendiriMouseClicked(evt);
            }
        });
        jPanel3.add(rbSendiri, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 90, -1, -1));

        jLabel11.setFont(new java.awt.Font("Tahoma", 0, 12)); // NOI18N
        jLabel11.setText("Operator");
        jPanel3.add(jLabel11, new org.netbeans.lib.awtextra.AbsoluteConstraints(0, 50, 130, 20));

        tbukti_po.setEditable(false);
        jPanel3.add(tbukti_po, new org.netbeans.lib.awtextra.AbsoluteConstraints(90, 120, 190, -1));

        jButton14.setIcon(new javax.swing.ImageIcon(getClass().getResource("/icon/Search_More_15px.png"))); // NOI18N
        jButton14.addActionListener(new java.awt.event.ActionListener() {
            public void actionPerformed(java.awt.event.ActionEvent evt) {
                jButton14ActionPerformed(evt);
            }
        });
        jPanel3.add(jButton14, new org.netbeans.lib.awtextra.AbsoluteConstraints(280, 120, 30, -1));

        javax.swing.GroupLayout jPanel7Layout = new javax.swing.GroupLayout(jPanel7);
        jPanel7.setLayout(jPanel7Layout);
        jPanel7Layout.setHorizontalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel7Layout.createSequentialGroup()
                .addGroup(jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jTextField25)
                    .addGroup(jPanel7Layout.createSequentialGroup()
                        .addContainerGap()
                        .addGroup(jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                            .addComponent(jPanel3, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                            .addComponent(jScrollPane3, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE))))
                .addContainerGap())
        );
        jPanel7Layout.setVerticalGroup(
            jPanel7Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel7Layout.createSequentialGroup()
                .addComponent(jTextField25, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jPanel3, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addPreferredGap(javax.swing.LayoutStyle.ComponentPlacement.RELATED)
                .addComponent(jScrollPane3, javax.swing.GroupLayout.PREFERRED_SIZE, 0, Short.MAX_VALUE)
                .addContainerGap())
        );

        javax.swing.GroupLayout jPanel5Layout = new javax.swing.GroupLayout(jPanel5);
        jPanel5.setLayout(jPanel5Layout);
        jPanel5Layout.setHorizontalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addComponent(jPanel1, javax.swing.GroupLayout.PREFERRED_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.PREFERRED_SIZE)
                .addGap(18, 18, 18)
                .addComponent(jPanel7, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                .addGap(18, 18, 18)
                .addComponent(jPanel8, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                .addContainerGap())
        );
        jPanel5Layout.setVerticalGroup(
            jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 538, Short.MAX_VALUE)
            .addGroup(jPanel5Layout.createSequentialGroup()
                .addContainerGap()
                .addGroup(jPanel5Layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
                    .addComponent(jPanel8, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
                    .addComponent(jPanel7, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE))
                .addContainerGap())
        );

        jTabbedPane1.addTab("Add/Edit", jPanel5);

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(this);
        this.setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addGroup(javax.swing.GroupLayout.Alignment.TRAILING, layout.createSequentialGroup()
                .addContainerGap()
                .addComponent(jTabbedPane1)
                .addContainerGap())
        );
    }// </editor-fold>//GEN-END:initComponents

    private void jDateTranPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTranPropertyChange
        
    }//GEN-LAST:event_jDateTranPropertyChange

    private void jDateTranKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTranKeyPressed
       
    }//GEN-LAST:event_jDateTranKeyPressed

    private void jDateTran1PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTran1PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTran1PropertyChange

    private void jDateTran1KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTran1KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTran1KeyPressed

    private void jDateTran2PropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateTran2PropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTran2PropertyChange

    private void jDateTran2KeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateTran2KeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateTran2KeyPressed

    private void tstepkodeActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_tstepkodeActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_tstepkodeActionPerformed

    private void jTable2MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable2MouseClicked
       if(evt.getClickCount() == 2){
            brs = jTable2.getSelectedRow();
            col = jTable2.getSelectedColumn();
            if(col<8) {
                return;
            }
            
            String[] name = jTable2.getColumnName(col).split("_");
            if(name.length >1) {
                if(name[1].equals("WASTE")) {
                    DialogProduksiError prod = new DialogProduksiError(jTable2.getValueAt(brs,2).toString(),jTable2.getValueAt(brs,3).toString(),name[0]);
                    prod.setVisible(true);
                } else if(name[1].equals("GOOD")) {
                    DialogProduksiHasil prod = new DialogProduksiHasil(jTable2.getValueAt(brs,2).toString(),jTable2.getValueAt(brs,3).toString(),name[0]);
                    prod.setVisible(true);
                }
            }
       }
    }//GEN-LAST:event_jTable2MouseClicked

    private void jDateInputPropertyChange(java.beans.PropertyChangeEvent evt) {//GEN-FIRST:event_jDateInputPropertyChange
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateInputPropertyChange

    private void jDateInputKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_jDateInputKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_jDateInputKeyPressed

    private void jButton13ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton13ActionPerformed
        DialogGudang a = new DialogGudang();
        a.setVisible(true);
        if(!"".equals(a.ccode)){
            tcode.setText(a.ccode);
            tcode1.setText(a.cdesc);
        }
    }//GEN-LAST:event_jButton13ActionPerformed

    private void jButton9ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton9ActionPerformed
        DialogPublicTP.DialogWO a = new DialogWO();
        a.setVisible(true);
        if(a.status){
            two.setText(a.cnbkt);
            tpo.setText(a.cnbktpo);
            tcustomer.setText(a.customer);
            tproduk.setText(a.produk);
            tnote.setText(a.cnote);
            
            sql="select a.*,b.cket as ketpo from PWorkOrder a \n" +
                    "left join PCustomerPOD b on a.ccode=b.ccode and a.cnbkt_order=b.cnbkt and a.cgroup=b.cgroup and a.cproduct=b.cproduct"
                    + " where a.ccode='"+tcode.getText()+"' and a.cnbkt='"+a.cnbkt+"'";
            rs = con.queryResult(sql);
            try {
                while(rs.next()){
                    tnotespk.setText(rs.getString("ketpo"));
                    tnote.setText(rs.getString("cket"));
                    ttarget.setText(rfa.formatDecimal2.format(rs.getDouble("nqty_total")) +" PCS");
                    tdeadline.setText(rfa.DateString(keyssDef, rs.getDate("deadline"), "dd MMMM yyyy"));
                }
            } catch (SQLException ex) {
                Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
            }
            
        }
    }//GEN-LAST:event_jButton9ActionPerformed

    private void jButton12ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton12ActionPerformed
        String wo= two.getText();
        if(wo.isEmpty()) {
            wo="AA";
        }
        
        DialogPublicTP.DialogSteps a = new DialogSteps(wo);
        a.setVisible(true);
        if(a.status){
            tstepkode.setText(a.kode);
            tstep.setText(a.name);
            load_stepinput();
            load_errinput();
        }
    }//GEN-LAST:event_jButton12ActionPerformed

    private void jButton11ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton11ActionPerformed
        DialogKaryawan a = new DialogKaryawan();
        a.setVisible(true);
        if(a.status){
            tkaryawankode.setText(a.Kode);
            tkaryawan.setText(a.Nama);
        }
    }//GEN-LAST:event_jButton11ActionPerformed

    private void jButton10ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton10ActionPerformed
        DialogError a = new DialogError("");
        a.setVisible(true);
        if(a.status){
            terrorkode.setText(a.kode);
            terror.setText(a.name);
        }
    }//GEN-LAST:event_jButton10ActionPerformed

    private void btDelStepActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDelStepActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete_step();
        }
    }//GEN-LAST:event_btDelStepActionPerformed

    private void btDelStepKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btDelStepKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_btDelStepKeyPressed

    private void btAddStepsActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btAddStepsActionPerformed
        if(tstep.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Step Produksi belum di pilih", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        if(tkaryawankode.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Operator belum di pilih", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        if(tgood.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Jumlah Good belum diisi", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        if(twaste.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Jumlah Waste belum diisi", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        if(rbSubcount.isSelected()) {
            if(tbukti_po.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"Nomor PO untuk Subcount belum diisi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
        }
        
        if(btAddSteps.getText().equals("Add")) {
            if(con.akses(akses, rfa.INSERT)) {
                save_step(0);
            }
        } else if(btAddSteps.getText().equals("Update")){
            if(con.akses(akses, rfa.UPDATE)) {
                save_step(1);
            }
        }
    }//GEN-LAST:event_btAddStepsActionPerformed

    private void tnotestepFocusLost(java.awt.event.FocusEvent evt) {//GEN-FIRST:event_tnotestepFocusLost
        tnotestep.setText(tnotestep.getText().toUpperCase());
    }//GEN-LAST:event_tnotestepFocusLost

    private void jTable3MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable3MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable3.getSelectedRow();
            
            if(jTable3.getValueAt(baris, 0) != null) idstep = jTable3.getValueAt(baris, 0).toString();
            
            sql="select a.*,c.cplot,d.cname as operator,\n" +
            "case when a.cgood_unit='LEMBAR' then convert(varchar,a.cgood)+ ' Lembar ('+convert(varchar,a.cgood*c.cplot)+' Pcs)' else convert(varchar,a.cgood)+' Pcs' end as good,\n" +
            "case when a.cwaste_unit='LEMBAR' then convert(varchar,a.cwaste)+ ' Lembar ('+convert(varchar,a.cwaste*c.cplot)+' Pcs)' else convert(varchar,a.cwaste)+' Pcs' end as waste\n" +
            "from PProdSteps a\n" +
            "join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join BOMProduct c on b.cnbkt_bom=c.cnbkt\n" +
            "join MKaryawan d on a.cnik=d.nik\n" +
            "where a.id='"+idstep+"'  \n";
            
            rs = con.queryResult(sql);
            try {
                while(rs.next()) {
                    tkaryawankode.setText(rs.getString("cnik"));
                    tkaryawan.setText(rs.getString("operator"));
                    tgood.setText(rs.getString("cgood"));
                    listgoodunit.setSelectedItem(rs.getString("cgood_unit"));
                    twaste.setText(rs.getString("cwaste"));
                    listwasteunit.setSelectedItem(rs.getString("cwaste_unit"));
                    tnotestep.setText(rs.getString("cnote"));
                    
                    if(rs.getInt("ctype") == 1) rbSubcount.setSelected(true);
                    tbukti_po.setText(rs.getString("cnbkt_po"));
                    
                }
            } catch (SQLException ex) {
                Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            
            btAddSteps.setText("Update");
            btDelStep.setVisible(true);
            btCancelStep.setVisible(true);
        }
    }//GEN-LAST:event_jTable3MouseClicked

    private void btAddErrActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btAddErrActionPerformed
        if(terrorkode.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Kesalahan Produksi belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        if(tqty_fail.getText().isEmpty()) {
            JOptionPane.showMessageDialog(this,"Jumlah Qty belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        if(btAddErr.getText().equals("Add")) {
            if(tstep.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"Step Produksi belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(tkaryawan.getText().isEmpty()) {
                JOptionPane.showMessageDialog(this,"Operator belum di isi", "Save", JOptionPane.ERROR_MESSAGE);
                return;
            }
            if(con.akses(akses, rfa.INSERT)) {
                save_error(0);
            }
        } else if(btAddErr.getText().equals("Update")){
            if(con.akses(akses, rfa.UPDATE)) {
                save_error(1);
            }
        }
    }//GEN-LAST:event_btAddErrActionPerformed

    private void btDelErrActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btDelErrActionPerformed
        if(con.akses(akses, rfa.DELETE)) {
            int pil=JOptionPane.showConfirmDialog(this, "Apakah anda yakin akan menghapus data ini? ", "Hapus Data", JOptionPane.YES_NO_OPTION);
            if(pil == 0) delete_error();
        }
    }//GEN-LAST:event_btDelErrActionPerformed

    private void btDelErrKeyPressed(java.awt.event.KeyEvent evt) {//GEN-FIRST:event_btDelErrKeyPressed
        // TODO add your handling code here:
    }//GEN-LAST:event_btDelErrKeyPressed

    private void btCancelStepActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelStepActionPerformed
        clear_step();
    }//GEN-LAST:event_btCancelStepActionPerformed

    private void jTable4MouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_jTable4MouseClicked
        if(evt.getClickCount() == 2) {
            int baris = jTable4.getSelectedRow();
            
            if(jTable4.getValueAt(baris, 0) != null) iderr = jTable4.getValueAt(baris, 0).toString();
            
            sql="select a.*,c.cplot,isnull(a.nqty_fail,0) as qty,d.cname as karyawan \n" +
            "from PProdErr a\n" +
            "join PWorkOrder b on a.ccode=b.ccode and a.cnbkt=b.cnbkt\n" +
            "join BOMProduct c on b.cnbkt_bom=c.cnbkt\n" +
            "left join MKaryawan d on a.cnik=d.nik \n"+
            "where a.id='"+iderr+"' "; 
            
            rs = con.queryResult(sql);
            try {
                while(rs.next()) {
                    terrorkode.setText(rs.getString("cerr_kode"));
                    terror.setText(rs.getString("cerr_name"));
                    tqty_fail.setText(rfa.formatDecimal3.format(rs.getInt("qty")));
                    jDateInput.setDate(rs.getDate("dtran"));
                    tkaryawankode.setText(rs.getString("cnik"));
                    tkaryawan.setText(rs.getString("karyawan"));
                }
            } catch (SQLException ex) {
                Logger.getLogger(ProduksiInput2.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            
            btAddErr.setText("Update");
            btDelErr.setVisible(true);
            btCancelErr.setVisible(true);
        }
    }//GEN-LAST:event_jTable4MouseClicked

    private void jButton7ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton7ActionPerformed
        loadData();
    }//GEN-LAST:event_jButton7ActionPerformed

    private void jButton14ActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_jButton14ActionPerformed
        DialogPurchaseOrder a = new DialogPurchaseOrder();
        a.setVisible(true);
        if(a.status){
            tbukti_po.setText(a.kode); 
        }
    }//GEN-LAST:event_jButton14ActionPerformed

    private void rbSubcountMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_rbSubcountMouseClicked
        if(rbSubcount.isSelected()) {
            jButton14.setEnabled(true);
        } else {
            jButton14.setEnabled(false);
            tbukti_po.setText("");
        }
    }//GEN-LAST:event_rbSubcountMouseClicked

    private void rbSendiriMouseClicked(java.awt.event.MouseEvent evt) {//GEN-FIRST:event_rbSendiriMouseClicked
        if(rbSubcount.isSelected()) {
            jButton14.setEnabled(true);
        } else {
            jButton14.setEnabled(false);
            tbukti_po.setText("");
        }
    }//GEN-LAST:event_rbSendiriMouseClicked

    private void rbSendiriItemStateChanged(java.awt.event.ItemEvent evt) {//GEN-FIRST:event_rbSendiriItemStateChanged
        if(rbSubcount.isSelected()) {
            jButton14.setEnabled(true);
        } else {
            jButton14.setEnabled(false);
            tbukti_po.setText("");
        }
    }//GEN-LAST:event_rbSendiriItemStateChanged

    private void rbSubcountItemStateChanged(java.awt.event.ItemEvent evt) {//GEN-FIRST:event_rbSubcountItemStateChanged
        if(rbSubcount.isSelected()) {
            jButton14.setEnabled(true);
        } else {
            jButton14.setEnabled(false);
            tbukti_po.setText("");
        }
    }//GEN-LAST:event_rbSubcountItemStateChanged

    private void btCancelErrActionPerformed(java.awt.event.ActionEvent evt) {//GEN-FIRST:event_btCancelErrActionPerformed
        // TODO add your handling code here:
    }//GEN-LAST:event_btCancelErrActionPerformed


    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JButton btAddErr;
    private javax.swing.JButton btAddSteps;
    private javax.swing.JButton btCancelErr;
    private javax.swing.JButton btCancelStep;
    private javax.swing.JButton btDelErr;
    private javax.swing.JButton btDelStep;
    private javax.swing.ButtonGroup buttonGroup1;
    private javax.swing.JButton jButton1;
    private javax.swing.JButton jButton10;
    private javax.swing.JButton jButton11;
    private javax.swing.JButton jButton12;
    private javax.swing.JButton jButton13;
    private javax.swing.JButton jButton14;
    private javax.swing.JButton jButton2;
    private javax.swing.JButton jButton7;
    private javax.swing.JButton jButton9;
    private com.toedter.calendar.JDateChooser jDateInput;
    private com.toedter.calendar.JDateChooser jDateTran;
    private com.toedter.calendar.JDateChooser jDateTran1;
    private com.toedter.calendar.JDateChooser jDateTran2;
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
    private javax.swing.JLabel jLabel33;
    private javax.swing.JLabel jLabel34;
    private javax.swing.JLabel jLabel35;
    private javax.swing.JLabel jLabel36;
    private javax.swing.JLabel jLabel37;
    private javax.swing.JLabel jLabel38;
    private javax.swing.JLabel jLabel39;
    private javax.swing.JLabel jLabel4;
    private javax.swing.JLabel jLabel40;
    private javax.swing.JLabel jLabel5;
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
    private javax.swing.JScrollPane jScrollPane2;
    private javax.swing.JScrollPane jScrollPane3;
    private javax.swing.JScrollPane jScrollPane4;
    private javax.swing.JScrollPane jScrollPane5;
    private javax.swing.JTabbedPane jTabbedPane1;
    private javax.swing.JTable jTable2;
    private javax.swing.JTable jTable3;
    private javax.swing.JTable jTable4;
    private javax.swing.JTextField jTextField10;
    private javax.swing.JTextField jTextField11;
    private javax.swing.JTextField jTextField12;
    private javax.swing.JTextField jTextField21;
    private javax.swing.JTextField jTextField25;
    private javax.swing.JTextField jTextField30;
    private javax.swing.JTextField jTextField9;
    private javax.swing.JComboBox listgoodunit;
    private javax.swing.JComboBox listwasteunit;
    private javax.swing.JRadioButton rbSendiri;
    private javax.swing.JRadioButton rbSubcount;
    private javax.swing.JTextField tbukti_po;
    private javax.swing.JTextField tcode;
    private javax.swing.JTextField tcode1;
    private javax.swing.JTextField tcustomer;
    private javax.swing.JTextField tdeadline;
    private javax.swing.JTextField terror;
    private javax.swing.JTextField terrorkode;
    private javax.swing.JTextField tgood;
    private javax.swing.JTextField tkaryawan;
    private javax.swing.JTextField tkaryawankode;
    private javax.swing.JTextArea tnote;
    private javax.swing.JTextArea tnotespk;
    private javax.swing.JTextField tnotestep;
    private javax.swing.JTextField tpo;
    private javax.swing.JTextField tproduk;
    private javax.swing.JTextField tqty_fail;
    private javax.swing.JTextField tstep;
    private javax.swing.JTextField tstepkode;
    private javax.swing.JTextField ttarget;
    private javax.swing.JTextField twaste;
    private javax.swing.JTextField two;
    // End of variables declaration//GEN-END:variables
}
