package Tools;

import java.io.IOException;
import java.security.cert.CertificateException;
import java.util.Arrays;
import java.util.concurrent.TimeUnit;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;
import javax.swing.JOptionPane;
import okhttp3.ConnectionSpec;
import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import org.json.simple.JSONValue;

public class Translator {
    public static final MediaType JSON = MediaType.parse("application/json; charset=utf-8");
//    X509TrustManager trustManager;
//    SSLSocketFactory sslSocketFactory;
//    final TrustManager[] trustAllCerts = new TrustManager[] {
//        new X509TrustManager() {
//          @Override
//          public void checkClientTrusted(java.security.cert.X509Certificate[] chain, String authType) throws CertificateException {
//          }
//
//          @Override
//          public void checkServerTrusted(java.security.cert.X509Certificate[] chain, String authType) throws CertificateException {
//          }
//
//          @Override
//          public java.security.cert.X509Certificate[] getAcceptedIssuers() {
//            return new java.security.cert.X509Certificate[]{};
//          }
//        }
//    };
//        

    OkHttpClient client = new OkHttpClient.Builder()
            .readTimeout(600, TimeUnit.SECONDS)
            .writeTimeout(60, TimeUnit.SECONDS)
            .connectTimeout(60, TimeUnit.SECONDS)
            
            .build();
    
    //.connectionSpecs(Arrays.asList(ConnectionSpec.MODERN_TLS,ConnectionSpec.COMPATIBLE_TLS))
    String currToken;
    String tokenBarier ="";

    public Translator() throws IOException {
//        client.setReadTimeout(15, TimeUnit.SECONDS);
        this.currToken = "";
    }

    public String requestNewToken() throws IOException{
        String url = "http://148.66.130.72:33250/api/user/login/phone" ;
        //url = "https://stp.san.co.id:33253/api/user/login/phone" ;
        //url = "http://10.2.18.145:3333/api/user/login/phone" ;
        MediaType mediaType = MediaType.parse("text/plain");
        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
          .addFormDataPart("phone","accounting")
          .addFormDataPart("password","S@nSTP@ssw0rd10x")
          .build();
        //Add your subscription key!!
        Request request = new Request.Builder()
        .url(url)
        .method("POST", body)
        .addHeader("Content-Type", "application/json")
        .addHeader("Accept", "application/jwt")
        .addHeader("Ocp-Apim-Subscription-Key", "SubKeyGoesHere")
        .build();

        Response response = client.newCall(request).execute();
        String respons = response.body().string();
        System.out.println(respons);
        
        JSONObject ar=(JSONObject)JSONValue.parse(respons);
        tokenBarier = ar.get("token").toString();
        System.out.println(ar.get("token"));

        return respons;
    }

    public String translate(String area) throws IOException{
        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
        .addFormDataPart("area",area)
        .build();
        Request request = new Request.Builder()
        .url("http://148.66.130.72:33250/api/report/petani/area?CCODE="+area)
        
        .addHeader("Authorization", "Bearer "+tokenBarier)
        .addHeader("Content-Type", "application/json")
        .addHeader("Accept", "application/jwt")
        .addHeader("Ocp-Apim-Subscription-Key", "SubKeyGoesHere")
        .method("GET", null)
        .build();
        
        System.out.println(request);
        Response response = client.newCall(request).execute();
        System.out.println(response);
        return response.body().string();
    }
    
    public String responFromURL(String urll) throws IOException{
        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
        .addFormDataPart("area","Bismillah")
        .build();
        Request request = new Request.Builder()
        .url(urll)
        
        .addHeader("Content-Type", "application/json")
        .addHeader("Accept", "application/jwt")
        .addHeader("Ocp-Apim-Subscription-Key", "SubKeyGoesHere")
        .method("GET", null)
        .build();
        
        System.out.println(request);
        Response response = null;
//        try {
            response = client.newCall(request).execute();
//        } catch (Exception e ){
//            JOptionPane.showMessageDialog(this, "Koneksi terputus. ", "Error to Import", JOptionPane.WARNING_MESSAGE);
//                    
//        }
        System.out.println(response);
        return response.body().string();
    }
    public String responFromURL1(String urll) throws IOException{
        System.out.println(urll);
        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
        .addFormDataPart("area","Bismillah")
        .build();
        Request request = new Request.Builder()
        .url(urll)
        
        .addHeader("Content-Type", "application/json")
        .addHeader("Accept", "application/jwt")
        .addHeader("Ocp-Apim-Subscription-Key", "SubKeyGoesHere")
        .method("GET", null)
        .build();
        
        System.out.println(request);
        Response response = null;
//        try {
            response = client.newCall(request).execute();
//        } catch (Exception e ){
//            JOptionPane.showMessageDialog(this, "Koneksi terputus. ", "Error to Import", JOptionPane.WARNING_MESSAGE);
//                    
//        }
        System.out.println(response);
        return response.body().string();
    }
    public String responFromURL2(String urll) throws IOException{
        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
        .addFormDataPart("area","Bismillah")
        .build();
        Request request = new Request.Builder()
        .url(urll)
        
        .addHeader("Content-Type", "application/json")
        .addHeader("Accept", "application/jwt")
        .addHeader("Ocp-Apim-Subscription-Key", "SubKeyGoesHere")
        .method("GET", null)
        .build();
        
        System.out.println(request);
        Response response = null;
//        try {
            response = client.newCall(request).execute();
//        } catch (Exception e ){
//            JOptionPane.showMessageDialog(this, "Koneksi terputus. ", "Error to Import", JOptionPane.WARNING_MESSAGE);
//                    
//        }
        System.out.println(response);
        return response.body().string();
    }
    
    public String requestData(String urll,RequestBody body) throws IOException{
//        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
//        .addFormDataPart("area","Bismillah")
//        .build();
        Request request = new Request.Builder()
        .url(urll)
                .get()
//        .method("GET", null)
         .post(body)
        .build();
        
        System.out.println(request);
        Response response = null;
//        try {
            response = client.newCall(request).execute();
//        } catch (Exception e ){
//            JOptionPane.showMessageDialog(this, "Koneksi terputus. ", "Error to Import", JOptionPane.WARNING_MESSAGE);
//                    
//        }
        System.out.println(response);
        return response.body().string();
    }
    
    public String requestDataSelectAll(String urll,RequestBody body) throws IOException{
//        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
//        .addFormDataPart("area","Bismillah")
//        .build();
        System.out.println(urll);
        Request request = new Request.Builder()
        .url(urll)
//        .method("GET", null)
         .post(body)
        .build();
        
        System.out.println(request);
        Response response = null;
//        try {
            response = client.newCall(request).execute();
//        } catch (Exception e ){
//            JOptionPane.showMessageDialog(this, "Koneksi terputus. ", "Error to Import", JOptionPane.WARNING_MESSAGE);
//                    
//        }
        System.out.println(response);
        return response.body().string();
    }
    
    public String uploadBKBPSaprodi(String ccode, String data, String tgl) throws IOException{
        String url = "http://148.66.130.72:33250/api/sync/bahan/pembantu" ;
        //url = "http://10.2.18.145:3333/api/sync/bahan/pembantu" ;
        MediaType mediaType = MediaType.parse("text/plain");
        RequestBody body = new MultipartBody.Builder().setType(MultipartBody.FORM)
          .addFormDataPart("CCODE",ccode)
          .addFormDataPart("TGL",tgl)
          .addFormDataPart("DATA",data)
          .build();
        
        
        //Add your subscription key!!
        Request request = new Request.Builder()
        .url(url)
        .method("POST", body)
        .addHeader("Authorization", "Bearer "+tokenBarier)
        .addHeader("Content-Type", "multipart/form-data")
        .addHeader("Accept", "application/jwt")
        .addHeader("Ocp-Apim-Subscription-Key", "SubKeyGoesHere")
        .build();

        //System.out.println(request);
        
        Response response = client.newCall(request).execute();
        //System.out.println(respons);
        
//        JSONObject ar=(JSONObject)JSONValue.parse(respons);
//        tokenBarier = ar.get("token").toString();
//        System.out.println(ar.get("token"));

        return response.body().string();
    }

}
