/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package RawMaterial;

import Main.Login;
import static Main.MenuAdmin.keyssDef;
import Tools.ConnectDB;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import rf4declare.Rf4declare;

/**
 *
 * @author HP
 */
public class RunHPP {
    ConnectDB con = new ConnectDB();
    rf4declare.Rf4declare rfa = new Rf4declare();
    ResultSet rs;
    String sql;
    String id="",iddetail="",cnbkt="",cnbkt_print="";
    int barisEdit=-1;
    String akses="PRODUKSI BLANDING";
    String idKas="R1";
    ArrayList<String> deleteRow;
    
    public void saveHPP(String dtran, String cgroup, String cproduct, String unit){
        sql="";
        HashMap<String,String> listObj = new HashMap<String,String>();
//        listObj.put("dtran",);
//        listObj.put("cgroup",);
//        listObj.put("cproduct",);
//        listObj.put("qty",);
//        listObj.put("unit",);
//        listObj.put("small_value",);
//        listObj.put("cogs",);
        sql=con.updateValue("PBland", listObj);
    }
}
