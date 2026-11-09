package Tools;











import Main.Login;



import static Main.MenuAdmin.keyssDef;



import ReportProduction.RProduction;



import java.awt.Color;



import java.awt.Component;



import java.io.File;



import java.io.FileNotFoundException;



import java.io.FileOutputStream;



import java.io.IOException;



import java.sql.Connection;



import java.sql.DriverManager;



import java.sql.ResultSet;



import java.sql.ResultSetMetaData;



import java.sql.SQLException;



import java.sql.Statement;



import java.util.ArrayList;



import java.util.Date;



import java.util.HashMap;



import java.util.LinkedHashMap;



import java.util.List;



import java.util.Map;



import java.util.Properties;



import java.util.concurrent.Executors;



import java.util.logging.Level;



import java.util.logging.Logger;



import javax.swing.JComboBox;



import javax.swing.JFileChooser;



import javax.swing.JOptionPane;



import javax.swing.JTable;



import javax.swing.JTextArea;



import javax.swing.table.DefaultTableCellRenderer;



import javax.swing.table.TableCellRenderer;



import javax.swing.table.TableColumnModel;



import org.apache.poi.hssf.usermodel.HSSFCellStyle;



import org.apache.poi.hssf.usermodel.HSSFFont;



import org.apache.poi.hssf.usermodel.HSSFSheet;



import org.apache.poi.hssf.usermodel.HSSFWorkbook;



import org.apache.poi.hssf.util.HSSFColor;



import org.apache.poi.ss.usermodel.Cell;



import org.apache.poi.ss.usermodel.Row;



import rf4declare.Rf4declare;







/**



 *



 * @author Richard



 */



public class ConnectDB {



    rf4declare.Rf4declare rfa = new Rf4declare();



    private static Connection conn;



    private static Connection conn_notif;



    private static String server;







    public static String getServer() {



        return server;



    }







    public static void setServer(String server) {



        ConnectDB.server = server;



    }



    private static int port;



    private static String dbName;



    private static String username;



    private static String password;



    private static String winAuth;    



    private static String Hasil;



    private static String pesanError;







    private static Executors exx;



    public String sql;



    



    private static int testC=0;



    private static int connectStatus=0;



    public ConnectDB() {



        conn=null;



    }



    



    public static void loadDriver() throws ClassNotFoundException {



        if(port == 3306){



            Class.forName("com.mysql.jdbc.Driver");



        } else {



            Class.forName("net.sourceforge.jtds.jdbc.Driver");



        }



    }







        public static String translateSql(String sql) {

        if (sql == null || port != 3306) {

            return sql;

        }

        

        // 1. Fix comments without space: '--where' -> '-- where'

        sql = sql.replaceAll("--([^\\s\\r\\n])", "-- $1");

        

        // 2. Fix convert(type, expr) -> CAST(expr AS ...)

        java.util.regex.Pattern pConv = java.util.regex.Pattern.compile("(?i)\\bconvert\\s*\\(\\s*(int|varchar(?:\\([^)]*\\))?|float|numeric(?:\\([^)]*\\))?)\\s*,");

        while (true) {

            java.util.regex.Matcher m = pConv.matcher(sql);

            if (!m.find()) break;

            String typ = m.group(1).toLowerCase();

            int start = m.start();

            int exprStart = m.end();

            int depth = 1;

            int i = exprStart;

            while (i < sql.length()) {

                char ch = sql.charAt(i);

                if (ch == '(') depth++;

                else if (ch == ')') {

                    depth--;

                    if (depth == 0) break;

                }

                i++;

            }

            if (depth != 0) break;

            String expr = sql.substring(exprStart, i).trim();

            int end = i + 1;

            String targetTyp = "SIGNED";

            if (typ.contains("varchar")) targetTyp = "CHAR";

            else if (typ.contains("float") || typ.contains("numeric")) targetTyp = "DECIMAL(18,2)";

            sql = sql.substring(0, start) + "CAST(" + expr + " AS " + targetTyp + ")" + sql.substring(end);

        }

        

        // 3. Fix isnull(a, b) -> ifnull(a, b)

        sql = sql.replaceAll("(?i)\\bisnull\\s*\\(", "ifnull(");

        

        // 4. Fix getdate() -> NOW()

        sql = sql.replaceAll("(?i)\\bgetdate\\s*\\(\\s*\\)", "NOW()");

        

        // 5. Remove [dbo].

        sql = sql.replaceAll("(?i)\\[dbo\\]\\.", "");

        

        // 6. Fix MMenu IF NOT EXISTS

        if (sql.toUpperCase().contains("MMENU") && sql.toUpperCase().contains("IF NOT EXISTS")) {
            java.util.regex.Matcher mMenuName = java.util.regex.Pattern.compile("(?i)name\\s*=\\s*'([^']*)'").matcher(sql);
            if (mMenuName.find()) {
                return "INSERT INTO MMenu (name) VALUES ('" + mMenuName.group(1) + "') ON DUPLICATE KEY UPDATE name=VALUES(name)";
            }
        }
        java.util.regex.Matcher mMenu = java.util.regex.Pattern.compile("(?is)DO_NOT_MATCH_NEVER").matcher(sql);

        if (mMenu.find()) {

            return "INSERT INTO MMenu (name) VALUES ('" + mMenu.group(1) + "') ON DUPLICATE KEY UPDATE name=VALUES(name)";

        }

        

        // 7. Fix SysColumns IF NOT EXISTS

        java.util.regex.Matcher mCol = java.util.regex.Pattern.compile("(?is)IF\\s+NOT\\s+EXISTS\\s*\\(\\s*SELECT\\s+\\*\\s+FROM\\s+SysColumns\\s+WHERE\\s+\\[name\\]\\s*=\\s*N?'([^']+)'\\s+AND\\s+\\[id\\]\\s*=\\s*OBJECT_ID\\(N?'([^']+)'\\)\\s*\\)\\s*BEGIN\\s*ALTER\\s+TABLE\\s+(?:\\[dbo\\]\\.)?\\*?\\[?([^\\]]+)\\]?\\s+ADD\\s+\\[?([^\\]\\s]+)\\]?\\s+([a-zA-Z0-9_()]+)\\s+null\\s*END").matcher(sql);

        if (mCol.find()) {

            String col = mCol.group(1);

            String tbl = mCol.group(2);

            String typ = mCol.group(5).toUpperCase().replace("NVARCHAR", "VARCHAR");

            return "ALTER TABLE `" + tbl + "` ADD COLUMN `" + col + "` " + typ + " NULL";

        }

        

        // 8. Fix SELECT TOP X ... -> SELECT ... LIMIT X

        java.util.regex.Matcher mTop = java.util.regex.Pattern.compile("(?i)\\bselect\\s+top\\s+(\\d+)\\s+(.+)").matcher(sql);

        if (mTop.find()) {

            sql = "select " + mTop.group(2) + " LIMIT " + mTop.group(1);

        }

        

        // 9. Fix [name] -> `name`

        sql = sql.replaceAll("\\[([a-zA-Z0-9_]+)\\]", "`$1`");

        

        return sql;

    }



    public static String getConnectionString() {



        if(port == 3306){



            return "jdbc:mysql://" + server + ":" + port + "/" + dbName + "?useUnicode=true&characterEncoding=UTF-8&zeroDateTimeBehavior=convertToNull";



        } else {



            return "jdbc:jtds:sqlserver://" + server + ":" + port + "/" + dbName + ";loginTimeout=10;socketTimeout=25";



        }



    }







    public static Boolean connectDB(String server,int port,String dbname,String username,String password) throws ClassNotFoundException{     



        ConnectDB.server=server;



        ConnectDB.port=port;



        ConnectDB.dbName=dbname;



        ConnectDB.username=username;



        ConnectDB.password=password;



    



        try {



            loadDriver();



            DriverManager.setLoginTimeout(15);



           



            conn = DriverManager.getConnection(getConnectionString(), username, password);



            System.out.println("Koneksi Sukses");



            return true;



        } catch(ClassNotFoundException eclass){



            System.out.println("Driver Tidak Ada: " + eclass.getMessage());



            return false;



        } catch (SQLException ex) {



            System.out.println(ex.getMessage());



            return false;



        }



    }



    



    public static Connection getCon(){



        System.out.println(getConnectionString() +","+ username +","+ password);



        return ConnectDB.conn;



    }



    



    public static void closeDB(){



        try {



            connectStatus  = 0;



            conn.close();



            conn = null;



        } catch (Exception e) {



            System.out.println(e);



        }



        try {



            connectStatus  = 0;



            conn.isClosed();



            conn = null;



        } catch (Exception e) {



            System.out.println(e);



        }



    }



    



    public static void openDB() throws ClassNotFoundException {



        try {



            connectStatus = 1;



            loadDriver();



            



            DriverManager.setLoginTimeout(10);



            conn = DriverManager.getConnection(getConnectionString(), username, password);



        } catch (SQLException ex) {



            if(connectStatus == 0) {



                JOptionPane.showMessageDialog(null, ex, "Database '"+dbName+"' Tidak Dapat Dibuka", JOptionPane.ERROR_MESSAGE);



            } 



            closeDB();



            testC=1;



        }



    }



    public static void openDB_notif() throws ClassNotFoundException {



        try {



            loadDriver();



            DriverManager.setLoginTimeout(10);



            conn_notif = DriverManager.getConnection(getConnectionString(), username, password);



        } catch (SQLException ex) {



        }



    }



    



    public static void openDBCon() throws ClassNotFoundException {



        try {



            loadDriver();



            DriverManager.setLoginTimeout(10);



            conn = DriverManager.getConnection(getConnectionString(), username, password);



        } catch (SQLException ex) {



        }



    }



    



    public static ResultSet queryResult(String sqlQuery){
        sqlQuery = translateSql(sqlQuery);



        Statement st = null;



        ResultSet rs = null;



        int status = 0;



//        System.out.println(sqlQuery);



        try {  if(conn == null) openDB(); } catch (ClassNotFoundException ex) { System.out.println(ex.getMessage());  }



            



        try {



            st = conn.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE, ResultSet.CONCUR_READ_ONLY);



            st.setQueryTimeout(25);



            rs = st.executeQuery(sqlQuery);



            status = 1;



            //st.close();



        } catch (Exception ex) {



            System.out.println(ex.getMessage());



        }



        



        if(status == 0) {



            try {  openDB(); } catch (ClassNotFoundException ex) { System.out.println(ex.getMessage());  }



            try {



                st = conn.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE, ResultSet.CONCUR_READ_ONLY);



                st.setQueryTimeout(25);



                rs = st.executeQuery(sqlQuery);



                //st.close();



            } catch (Exception ex) {



                JOptionPane.showMessageDialog(null, ex, "Error Query", JOptionPane.ERROR_MESSAGE);



                System.out.println(ex.getMessage());



            }



        }
        if (rs == null && conn != null) {
            try {
                st = conn.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE, ResultSet.CONCUR_READ_ONLY);
                rs = st.executeQuery("SELECT * FROM (SELECT 1 as dummy) as d WHERE 1=0");
            } catch (Exception ex) {}
        }
        return rs;




    }



    public static ResultSet queryResultNotif(String sqlQuery){
        sqlQuery = translateSql(sqlQuery);



        Statement st = null;



        ResultSet rs = null;



        int status = 0;



//        System.out.println(sqlQuery);



        try {  if(conn_notif == null) openDB_notif(); } catch (ClassNotFoundException ex) { System.out.println(ex.getMessage());  }



            



        try {



            st = conn_notif.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE, ResultSet.CONCUR_READ_ONLY);



            st.setQueryTimeout(25);



            rs = st.executeQuery(sqlQuery);



            status = 1;



            //st.close();



        } catch (Exception ex) {



            System.out.println(ex.getMessage());



        }



        



        if(status == 0) {



            try {  openDB_notif(); } catch (ClassNotFoundException ex) { System.out.println(ex.getMessage());  }



            try {



                st = conn_notif.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE, ResultSet.CONCUR_READ_ONLY);



                st.setQueryTimeout(25);



                rs = st.executeQuery(sqlQuery);



                //st.close();



            } catch (Exception ex) {



                JOptionPane.showMessageDialog(null, ex, "Error Query", JOptionPane.ERROR_MESSAGE);



                System.out.println(ex.getMessage());



            }



        }



        return rs;



    }



    



    public static ResultSet queryResultTask(String sqlQuery){
        sqlQuery = translateSql(sqlQuery);



        Statement st = null;



        ResultSet rs = null;



        



        try {



            openDB();



            if(connectStatus == 0) openDB();



            st = conn.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE, ResultSet.CONCUR_READ_ONLY);



            rs = st.executeQuery(sqlQuery);



            //st.close();



        } catch (Exception ex) {



            System.out.println(ex.getMessage());



        }



        return rs;



    }



    



    public static ResultSet queryResultCon(String sqlQuery){
        sqlQuery = translateSql(sqlQuery);



        Statement st = null;



        ResultSet rs = null;



        



        try {



            openDBCon();



            st = conn.createStatement(ResultSet.TYPE_SCROLL_INSENSITIVE, ResultSet.CONCUR_READ_ONLY);



            rs = st.executeQuery(sqlQuery);



            //st.close();



        } catch (Exception ex) {



            System.out.println(ex.getMessage());



        }



        return rs;



    }



    



    public static int queryUpdate(String sqlquery){
        sqlquery = translateSql(sqlquery);



        int retn=0;



        Statement st1=null;



        int status=0;



        



        try {  if(conn == null)  openDB(); } catch (ClassNotFoundException ex) {  JOptionPane.showMessageDialog(null, ex, "Kesalahan", JOptionPane.ERROR_MESSAGE); }



        



        try {



            st1=conn.createStatement();



            st1.setQueryTimeout(25);



            retn=st1.executeUpdate(sqlquery);



            status = 1;



        } catch (SQLException ex) {



            JOptionPane.showMessageDialog(null, ex, "Kesalahan", JOptionPane.ERROR_MESSAGE);



            System.out.println(sqlquery);



            retn=0;



        }



        



        if(status == 0) {



            try {   openDB(); } catch (ClassNotFoundException ex) {  JOptionPane.showMessageDialog(null, ex, "Kesalahan", JOptionPane.ERROR_MESSAGE); }



        



            try {



                st1=conn.createStatement();



                st1.setQueryTimeout(25);



                retn=st1.executeUpdate(sqlquery);



                status = 1;



            } catch (SQLException ex) {



                JOptionPane.showMessageDialog(null, ex, "Kesalahan", JOptionPane.ERROR_MESSAGE);



                System.out.println(sqlquery);



                retn=0;



            }



        }



        return retn;



    }



    



    public static int queryUpdateConn(String sqlquery){
        sqlquery = translateSql(sqlquery);



        int retn=0;



        Statement st1=null;



        



        try {       



            openDBCon();



        } catch (ClassNotFoundException ex) {



            //JOptionPane.showMessageDialog(null, ex, "Kesalahan", JOptionPane.ERROR_MESSAGE);



        }



        



        try {



            st1=conn.createStatement();



            retn=st1.executeUpdate(sqlquery);



        } catch (SQLException ex) {



            JOptionPane.showMessageDialog(null, ex, "Kesalahan", JOptionPane.ERROR_MESSAGE);



            retn=0;



        }



        return retn;



    }



    



    public String insertValue(String table,HashMap<String,String> listObj) {



        String sqll=" INSERT INTO "+table+" ";



        String colom="",value="";



        for ( Map.Entry<String, String> e : listObj.entrySet() ) {



            String key = e.getKey();



            String val = e.getValue();



            colom+=""+key+",";



            



            if(val.toLowerCase().equalsIgnoreCase("getdate()")) {



                value+=""+val+",";



            } else if(val.toLowerCase().equalsIgnoreCase("null")) {



                value+="NULL,";



            } else {



                value+="'"+val.toString().replaceAll("'", "''")+"',";



            }



            



        }



        return sqll+" ("+colom.substring(0, colom.length()-1)+") values ("+value.substring(0, value.length()-1)+")";



    }



    



    public String updateValue(String table,HashMap<String,String> listObj) {



        String sqll=" UPDATE "+table+" set ";



        String colom="",value="";



        for ( Map.Entry<String, String> e : listObj.entrySet() ) {



            String key = e.getKey();



            String val = e.getValue();



            if(val.toLowerCase().equalsIgnoreCase("getdate()")) {



                value+=key+"="+val+",";



            } else if(val.toLowerCase().equalsIgnoreCase("null")) {



                value+=key+"=NULL,";



            } else {



                value+=key+"='"+val.toString().replaceAll("'", "''")+"',";



            }



            



        }



        return sqll+" "+value.substring(0, value.length()-1);



    }



    



    public static String getPesanError() {



        return pesanError;



    }







    public static void setPesanError(String pesanError) {



        ConnectDB.pesanError = pesanError;



    }



    



    public static int queryUpdateWithoutNote(String sqlquery){
        sqlquery = translateSql(sqlquery);



        int retn=0;



        Statement st1=null;



        System.out.println(sqlquery);



        try {       



            openDB();



            if(connectStatus == 0) openDB();



        } catch (ClassNotFoundException ex) {



            JOptionPane.showMessageDialog(null, ex, "Kesalahan", JOptionPane.ERROR_MESSAGE);



        }



        



        try {



            st1=conn.createStatement();



            retn=st1.executeUpdate(sqlquery);



        } catch (SQLException ex) {



            ex.printStackTrace();



//            System.out.println("=== "+sqlquery);



            pesanError = "";



            pesanError = ex.toString();



            retn=0;



        }



        return retn;



    }



    



    public void load_periode_acc(JComboBox cb, String sql){



        String comboBox;



        ResultSet rs = null;



        rs=queryResult(sql);



        cb.removeAllItems();



        try {



            comboBox="";



            while (rs.next()) {



                if(!comboBox.equals(rs.getString("cyear"))){



                    cb.addItem(rs.getString("cyear"));



                    comboBox=rs.getString("cyear");



                }



                cb.addItem(rs.getString("cyear") + "/" + rs.getString("cnbkt"));



                cb.setSelectedItem(rs.getString("cyear") + "/" + rs.getString("cnbkt"));



            }



        } catch (SQLException ex) {



            System.out.println(ex);



        }



        closeDB();



    }



    



    public void load_periode_umum(JComboBox cb, String sql){



        String comboBox;



        ResultSet rs = null;



        rs=queryResult(sql);



        cb.removeAllItems();



        try {



            comboBox="";



            while (rs.next()) {



                if(!comboBox.equals(rs.getString("thn"))){



                    cb.addItem(rs.getString("thn"));



                    comboBox=rs.getString("thn");



                }



                cb.addItem(rs.getString("bln") );



                cb.setSelectedItem(rs.getString("bln"));



            }



        } catch (SQLException ex) {



            System.out.println(ex);



        }



        closeDB();



    }



    



    public boolean akses(String nm, int stat){



        if (port == 3306) {
            queryUpdateWithoutNote("INSERT INTO MMenu (name) VALUES ('" + nm + "') ON DUPLICATE KEY UPDATE name=VALUES(name)");
        } else queryUpdate("BEGIN\n" +



                    "   IF NOT EXISTS (SELECT * FROM MMenu \n" +



                    "                   WHERE name = '"+nm+"' )\n" +



                    "   BEGIN\n" +



                    "       INSERT INTO MMenu (name)\n" +



                    "       VALUES ( '"+nm+"' )\n" +



                    "   END\n" +



                    "END");



        if(Login.usercode.equals("MIT")) return true;



        if(Login.usercode.equals("USRKEY")) return true;



       



        



        String id = Login.idd;



        boolean ok=false;



        ResultSet rs = null;



        rs=queryResult("select * from UserLoginKeys a join MMenu b on a.idform=b.id\n" +



                "where id_user= "+id+" and name='"+nm+"'");







        try {



            while(rs.next()){



                String akses = null;



                try {



                    akses = rfa.decrypt_rfa(1, rs.getString("keys"));



                } catch (Exception ex) {



                    ex.printStackTrace();



                }



                String[] parts = akses.split(";");



                



                if (stat==rfa.INSERT || stat == rfa.PRINT || stat == rfa.EXCEL){



                    if(parts[1].equals("1")){



                        ok=true;



                    }



                }else if (stat==rfa.UPDATE){



                    if(parts[2].equals("1")){



                        ok=true;



                    }



                }else if (stat==rfa.DELETE){



                    if(parts[3].equals("1")){



                        ok=true;



                    }



                }



            }



        } catch (SQLException ex) {



            System.out.println(ex);



        }



        System.out.println(ok);



        if (ok==false){



            if (stat==rfa.INSERT){



                JOptionPane.showMessageDialog(null, "Anda Tidak Mempunyai Akses Untuk Menambah Data", nm, JOptionPane.ERROR_MESSAGE);



            }else if (stat==rfa.UPDATE){



                JOptionPane.showMessageDialog(null, "Anda Tidak Mempunyai Akses Untuk Mengedit Data", nm, JOptionPane.ERROR_MESSAGE);



            }else if (stat==rfa.DELETE){



                JOptionPane.showMessageDialog(null, "Anda Tidak Mempunyai Akses Untuk Menghapus Data", nm, JOptionPane.ERROR_MESSAGE);



            }



        }



        closeDB();



        return ok;



    }



    



    public void exportExcel(JTable table,ArrayList kolom,String namafile,String dawal,String dakhir){



        final JFileChooser chooser = new JFileChooser();



        chooser.setDialogTitle("Specify a Folder to Save");



        chooser.setFileSelectionMode(JFileChooser.DIRECTORIES_ONLY);



        chooser.setCurrentDirectory(new File("C:\\Users\\Administrator\\Documents"));



        



        HSSFWorkbook workbook = new HSSFWorkbook();



        HSSFSheet sheet = workbook.createSheet("Sheet 1");







        HSSFFont font = workbook.createFont();



        HSSFCellStyle styleHeader = workbook.createCellStyle();



        font.setBoldweight(HSSFFont.BOLDWEIGHT_BOLD);



        styleHeader.setAlignment(styleHeader.ALIGN_CENTER);            



        styleHeader.setFillForegroundColor(HSSFColor.LIME.index);



        styleHeader.setFillPattern(HSSFCellStyle.SOLID_FOREGROUND);



        styleHeader.setBorderBottom(HSSFCellStyle.BORDER_THIN);



        styleHeader.setBorderTop(HSSFCellStyle.BORDER_THIN);



        styleHeader.setBorderLeft(HSSFCellStyle.BORDER_THIN);



        styleHeader.setBorderRight(HSSFCellStyle.BORDER_THIN);



        styleHeader.setFont(font);







        HSSFCellStyle styleHeader2 = workbook.createCellStyle();



        styleHeader2.setAlignment(styleHeader2.ALIGN_LEFT);



        styleHeader2.setFont(font);







        HSSFCellStyle styleBorder = workbook.createCellStyle();



        styleBorder.setBorderBottom(HSSFCellStyle.BORDER_THIN);



        styleBorder.setBorderTop(HSSFCellStyle.BORDER_THIN);



        styleBorder.setBorderLeft(HSSFCellStyle.BORDER_THIN);



        styleBorder.setBorderRight(HSSFCellStyle.BORDER_THIN);



        



        int retrival = chooser.showSaveDialog(null);



        if (retrival == JFileChooser.APPROVE_OPTION) {



            try {



                Row row = sheet.createRow(0);



                Cell cell = row.createCell(0); cell.setCellValue((String)namafile); cell.setCellStyle(styleHeader2);



                row = sheet.createRow(1);



                cell = row.createCell(0); cell.setCellValue((String)"Periode "+dawal+" s/d "+dakhir); cell.setCellStyle(styleHeader2);







                row = sheet.createRow(3);



                for(int i=0; i<kolom.size();i++){



                    cell = row.createCell(i); cell.setCellValue((String)kolom.get(i)); cell.setCellStyle(styleHeader);



                }







                for (int i = 0; i < table.getRowCount(); i++) {



                    row = sheet.createRow(i+4);



                    for (int j = 0; j < kolom.size(); j++) {



                        cell = row.createCell(j);



                        if(table.getValueAt(i, j) instanceof String)



                            cell.setCellValue((String)table.getValueAt(i, j));



                        else if(table.getValueAt(i, j) instanceof Integer)



                            cell.setCellValue((Integer)table.getValueAt(i, j));



                        else if(table.getValueAt(i, j) instanceof Double)



                            cell.setCellValue((Double)table.getValueAt(i, j));



                        cell.setCellStyle(styleBorder);



                    }



                }







                



            



                String f = chooser.getSelectedFile() + "\\Export "+namafile+" "+dawal+" sd "+dakhir+" "+rfa.DateString(keyssDef,new Date(),"yyyyMMdd hhmmss")+".xls";



                FileOutputStream excel= new FileOutputStream(new File(f));



                workbook.write(excel);



                excel.close();



                JOptionPane.showMessageDialog(null, "File Excel Disimpan Di "+f, "File Created", JOptionPane.INFORMATION_MESSAGE);



            } catch (FileNotFoundException ex) {



                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);



            } catch (IOException ex) {



                Logger.getLogger(RProduction.class.getName()).log(Level.SEVERE, null, ex);



            }



                



         



        }



    }



 



    public double loadNilaiSatuanTerkecil(String satuan_froom,String satuan_to){



        Double nilaisatuan = 0.0;



        String satuanterkecil = satuan_to;



        ResultSet rs1 = queryResult("select a.*,\n" +



                    "isnull(a.small_value,1) as n1,isnull(b.small_value,1) as n2,isnull(c.small_value,1) as n3,isnull(d.small_value,1) as n4,isnull(e.small_value,1) as n5,isnull(f.small_value,1) as n6,isnull(g.small_value,1) as n7,\n" +



                    "a.cname as t1,b.cname as t2,c.cname as t3,d.cname as t4,e.cname as t5,f.cname as t6,g.cname as t7\n" +



                    "from MSatuan a\n" +



                    "left join MSatuan b on a.id_turunan=b.id\n" +



                    "left join MSatuan c on b.id_turunan=c.id\n" +



                    "left join MSatuan d on c.id_turunan=d.id\n" +



                    "left join MSatuan e on d.id_turunan=e.id\n" +



                    "left join MSatuan f on e.id_turunan=f.id\n" +



                    "left join MSatuan g on f.id_turunan=g.id\n" +



                    "where a.cname = '"+satuan_froom+"'");



      



            try {



                while(rs1.next()){   



                    if(satuanterkecil.equals(rs1.getString("t1")))  nilaisatuan=1.0;



                    if(satuanterkecil.equals(rs1.getString("t2")))  nilaisatuan=rs1.getDouble("n1");



                    if(satuanterkecil.equals(rs1.getString("t3")))  nilaisatuan=rs1.getDouble("n1")*rs1.getDouble("n2");



                    if(satuanterkecil.equals(rs1.getString("t4")))  nilaisatuan=rs1.getDouble("n1")*rs1.getDouble("n2")*rs1.getDouble("n3");



                    if(satuanterkecil.equals(rs1.getString("t5")))  nilaisatuan=rs1.getDouble("n1")*rs1.getDouble("n2")*rs1.getDouble("n3")*rs1.getDouble("n4");



                    if(satuanterkecil.equals(rs1.getString("t6")))  nilaisatuan=rs1.getDouble("n1")*rs1.getDouble("n2")*rs1.getDouble("n3")*rs1.getDouble("n4")*rs1.getDouble("n5");



                    if(satuanterkecil.equals(rs1.getString("t7")))  nilaisatuan=rs1.getDouble("n1")*rs1.getDouble("n2")*rs1.getDouble("n3")*rs1.getDouble("n4")*rs1.getDouble("n5")*rs1.getDouble("n6");



                }



            } catch (SQLException ex) {  }



            



        return nilaisatuan;



    }



    



    public void resizeColumnWidth(JTable table) {



        final TableColumnModel columnModel = table.getColumnModel();



        for (int column = 0; column < table.getColumnCount(); column++) {



            int width = 55; // Min width



            for (int row = 0; row < table.getRowCount(); row++) {



                TableCellRenderer renderer = table.getCellRenderer(row, column);



                Component comp = table.prepareRenderer(renderer, row, column);



                width = Math.max(comp.getPreferredSize().width +1 , width);



            }



            if(width > 300)



                width=300;



            columnModel.getColumn(column).setPreferredWidth(width);



        }



    }



    



    public void colorTableSet(JTable table,ArrayList<Integer> colorSet) {



        table.setDefaultRenderer(Object.class, new TableCellRenderer(){



        private DefaultTableCellRenderer DEFAULT_RENDERER =  new DefaultTableCellRenderer();







            @Override



            public Component getTableCellRendererComponent(JTable table, Object value, boolean isSelected, boolean hasFocus, int row, int column) {



                Component c = DEFAULT_RENDERER.getTableCellRendererComponent(table, value, isSelected, hasFocus, row, column);



 



                if (isSelected) {



                    c.setBackground(Color.CYAN);



                } else {



                    c.setBackground(Color.WHITE);



                    



                    for(int i=0; i<colorSet.size(); i++){



                        if(row == colorSet.get(i)) { 



                            c.setBackground(Color.LIGHT_GRAY); 



                        }  



                    } 



                }



                return c;



            }







        });



    }



    



        public void AddColumnTable(String tablename, String column, String type){
        if (port == 3306) {
            try {
                String myType = type.toUpperCase().replace("NVARCHAR", "VARCHAR");
                queryUpdateWithoutNote("ALTER TABLE `" + tablename + "` ADD COLUMN `" + column + "` " + myType + " NULL");
            } catch (Exception e) {}
            return;
        }
        queryUpdate("IF NOT EXISTS(SELECT * FROM SysColumns WHERE [name] = N'"+column+"' AND [id] = OBJECT_ID(N'"+tablename+"')) " +
            "BEGIN " +
            "ALTER TABLE [dbo].["+tablename+"] " +
            "	ADD ["+column+"] "+type+" null " +
            "END");
    }







    public static List queryToListMap(Component backForm, String sqlQuery) {
        sqlQuery = translateSql(sqlQuery);



        List listData = new ArrayList();



        LinkedHashMap mapData = new LinkedHashMap();







        Statement st = null;



        ResultSet rs = null;



        try {



            openDB();



        } catch (ClassNotFoundException ex) {



            Logger.getLogger(ConnectDB.class.getName()).log(Level.SEVERE, null, ex);



        }



        try {



            st = conn.createStatement();



            rs = st.executeQuery(sqlQuery);



            //st.close();



        } catch (Exception ex) {



            //dLoading.close();



           



        }







        int ix = 0;



        try {



            ResultSetMetaData rsm = rs.getMetaData();







            while (rs.next()) {



                mapData = new LinkedHashMap();



                for (int i = 0; i < rsm.getColumnCount(); i++) {



                    mapData.put(rsm.getColumnName(i + 1), rs.getObject(i + 1));



                }







                listData.add(ix, mapData);



                ix++;



            }



        } catch (Exception ex) {



            //dLoading.close();



            //ex.printStackTrace();



            JOptionPane.showMessageDialog(backForm, ex.getMessage());



        }







        closeDB();







        return listData;



    }







    public static String romawi(String bulan){



        if("01".equalsIgnoreCase(bulan))        return "I";



        else if("02".equalsIgnoreCase(bulan))   return "II";



        else if("03".equalsIgnoreCase(bulan))   return "III";



        else if("04".equalsIgnoreCase(bulan))   return "IV";



        else if("05".equalsIgnoreCase(bulan))   return "V";



        else if("06".equalsIgnoreCase(bulan))   return "VI";



        else if("07".equalsIgnoreCase(bulan))   return "VII";



        else if("08".equalsIgnoreCase(bulan))   return "VIII";



        else if("09".equalsIgnoreCase(bulan))   return "IX";



        else if("10".equalsIgnoreCase(bulan))   return "X";



        else if("11".equalsIgnoreCase(bulan))   return "XI";



        else if("12".equalsIgnoreCase(bulan))   return "XII";



        else return "";



    }



    



    public Double saldoakhir(String ccode,String caccnt, String sub) {



        String sqll="declare\n" +



                "@dawal datetime,\n" +



                "@dakhir datetime,\n" +



                "@ccode nvarchar(10),\n" +



                "@caccnt nvarchar(10),\n" +



                "@sub nvarchar(10)\n" +



                "\n" +



                "set @dawal = '2023-12-28'\n" +



                "set @dakhir = '2023-12-28'\n" +



                "set @ccode = '"+ccode+"'\n" +



                "set @caccnt = '"+caccnt+"'\n" +



                "set @sub = '"+sub+"'\n" +



                "\n" +



                "Select a.ccode, upper(faccount) as GroupAcc, a.caccnt , \n" +



                "b.cdesc as cdesc,\n" +



                "case sign( Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end)) when 1 then\n" +



                "Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end)\n" +



                "else 0 end  as Sadbt,\n" +



                "case sign( Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end)) when -1 then\n" +



                "abs(Sum( case sign(datediff(d, a.dtran, @dawal)) when 1 then a.nilai else 0 end))\n" +



                "else 0 end  as SaCrd,\n" +



                "Sum( case Sign(datediff(d, @dawal, a.dtran)) when -1 then 0 else case sign(a.nilai) when -1 then 0 else a.nilai end  end) as MtDbt,\n" +



                "Sum( case Sign(datediff(d, @dawal, a.dtran)) when -1 then 0 else case sign(a.nilai) when 1 then 0 else abs(a.nilai) end  end) as MtCrd,\n" +



                "case sign( sum(a.nilai)) when 1 then sum(a.nilai) else 0 end as SkDbt, \n" +



                "case sign(sum(a.nilai)) when -1 then abs(sum(a.nilai)) else 0 end as SkCrd,\n" +



                "0 as saldo\n" +



                "from AccAll a \n" +



                "join MAccount b on a.caccnt=b.caccnt\n" + 



                "where a.ccode= @ccode and a.caccnt=@caccnt  \n" +



                "group by a.ccode, a.caccnt, faccount, b.cdesc \n" +



                "order by a.caccnt";



        System.out.println(sqll);



        ResultSet rsss = queryResult(sqll);



        Double saldoakhir=0.0;



        try {



            while(rsss.next()){



                saldoakhir = rsss.getDouble("SkDbt") - rsss.getDouble("SkCrd") ;



            }



        } catch (SQLException ex) {



            Logger.getLogger(ConnectDB.class.getName()).log(Level.SEVERE, null, ex);



        }



        return saldoakhir;



    }







    public Double saldoakhir_produok(String ccode,String cgroup, String cproduct, String skat) {



        String sqll="select sum(qty_in) as saldo from ProductStock\n" +



                "where ccode='"+ccode+"' and idskat = '"+skat+"' and cgroup='"+cgroup+"' and cproduct = '"+cproduct+"'";



        



        ResultSet rsss = queryResult(sqll);



        Double saldoakhir=0.0;



        try {



            while(rsss.next()){



                saldoakhir = rsss.getDouble("saldo") ;



            }



        } catch (SQLException ex) {



            Logger.getLogger(ConnectDB.class.getName()).log(Level.SEVERE, null, ex);



        }



        return saldoakhir;



    }







}



