/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package Control;

import java.security.Key;
import javax.crypto.Cipher;
import javax.crypto.spec.SecretKeySpec;
import sun.misc.BASE64Decoder;
import sun.misc.BASE64Encoder;

/**
 *
 * @author SAN-SBY-ITPRO4
 */
public class aesEncrypt {
    private static final String ALGORITHM = "AES";
    private static byte[] keyValue = new byte[] { '~','l','o', 'v', 'e', ' ', 'l', 'i', 'f', 'e', ' ', 'l', 'i', 'v','e','~'};
    private static byte[] keyValue2 = new byte[] { '~','l','o', 'v', 'e', ' ', 'l', 'i', 'f', 'e', ' ', 'l', 'i', 'v','e','~'};

    public static void setKeyValue(byte[] keyValue){
        if(keyValue == null)
            aesEncrypt.keyValue = aesEncrypt.keyValue2;
        else
            aesEncrypt.keyValue = keyValue;
    }

    public static String encrypt(String valueToEnc) throws Exception {
        Key key = generateKey();
        Cipher c = Cipher.getInstance(ALGORITHM);
        c.init(Cipher.ENCRYPT_MODE, key);
        byte[] encValue = c.doFinal(valueToEnc.getBytes());
        String encryptedValue = new BASE64Encoder().encode(encValue);
        return encryptedValue;
    }

    public static String decrypt(String encryptedValue) throws Exception {
        Key key = generateKey();
        Cipher c = Cipher.getInstance(ALGORITHM);
        c.init(Cipher.DECRYPT_MODE, key);
        byte[] decordedValue = new BASE64Decoder().decodeBuffer(encryptedValue);
        byte[] decValue = c.doFinal(decordedValue);
        String decryptedValue = new String(decValue);
        return decryptedValue;
    }

    private static Key generateKey() throws Exception {
        Key key = new SecretKeySpec(keyValue, ALGORITHM);
        return key;
    }
}
