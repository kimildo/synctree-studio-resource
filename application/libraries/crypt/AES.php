<?php
namespace libraries\crypt;

use libraries\{
	util\CommonUtil,
	constant\CommandConst
};

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class AES
{
    public static function encrypt($plaintext, $key, $mode = 'cbc')
    {
        if (empty(trim($plaintext))) {
            throw new FailedCryptException("empty plain text");
        }
		
        $key = hash('sha256', $key, true);

        $ivSource = defined('MCRYPT_DEV_URANDOM') ? MCRYPT_DEV_URANDOM : MCRYPT_RAND;
        $iv = mcrypt_create_iv(32, $ivSource);

        $ciphertext = mcrypt_encrypt('rijndael-256', $key, $plaintext, $mode, $iv);
        $hmac = hash_hmac('sha256', $ciphertext, $key, true);

        return CommonUtil::base64UrlEncode($ciphertext . $iv . $hmac);    
    } 
    
    public static function decrypt($ciphertext, $key, $mode = 'cbc')
    {
        if (empty(trim($ciphertext))) {
            throw new \Exception("empty cipher text");
        }

        $ciphertext = @CommonUtil::base64UrlDecode($ciphertext, true);
        if ($ciphertext === false) {
            throw new \Exception("failed to base64_decode");
        }

        $len = strlen($ciphertext);
        if ($len < 64) {
            throw new \Exception("not valid cipher length");
        }

        $iv = substr($ciphertext, $len - 64, 32);
        $hmac = substr($ciphertext, $len - 32, 32);
        $ciphertext = substr($ciphertext, 0, $len - 64);

        $key = hash('sha256', trim($key), true);

        $hmacCheck = hash_hmac('sha256', $ciphertext, $key, true);
        if ($hmac !== $hmacCheck) {
            throw new \Exception("failed to check hmac");
        }

        $plaintext = @mcrypt_decrypt('rijndael-256', $key, $ciphertext, $mode, $iv);
        if ($plaintext === false) {
            throw new \Exception("failed to mcrypt_decrypt");
        }

        return trim($plaintext);      
    } 

    private static function getkeyForMysql($key)
    {
	$newKey = str_repeat(chr(0), 16);

        for ($i=0,$len=strlen($key); $i<$len; $i++) {
		$newKey[$i%16] = $newKey[$i%16] ^ $key[$i];
	}

        return $newKey;
    }

    public static function encryptForMysql($plaintext, $key)
    {
        if (empty(trim($plaintext))) {
            throw new \Exception("empty plain text");
        }

	$key = self::getkeyForMysql($key);
	$padValue = 16-(strlen($plaintext) % 16);
	$plaintext = str_pad($plaintext, (16*(floor(strlen($plaintext) / 16)+1)), chr($padValue));
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));

        return bin2hex($ciphertext);
    }

    public static function decryptForMysql($ciphertext, $key)
    {
        if (empty(trim($ciphertext))) {
            throw new \Exception("empty cipher text");
        }
        
        $ciphertext = hex2bin($ciphertext);

        $key = self::getkeyForMysql($key);
	$plaintext = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));
        if ($plaintext === false) {
            throw new \Exception("failed to mcrypt_decrypt");
        }

        return trim($plaintext);
    }
}