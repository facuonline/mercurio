<?php
/**
 * Encryption class
 * @package Mercurio
 * @subpackage Utilitary classes
 * 
 * Safe and secure encryption made easy
 */
namespace Mercurio\Utils;
class Encryption {

    /**
     * Encrypts a string using OpenSSL AES-256-CBC
     * @param string $string String to be encrypted
     * @return string Encrypted string as base 64
     */
    public static function encrypt(string $string) {
        // App key added to avoid decryption trough different Mercurio instances
        $key = password_hash(getenv('APP_KEY'), PASSWORD_DEFAULT);
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		// AES-256-CBC allows for the best encryption and decryption
		$encrypted = openssl_encrypt($string, 'aes-256-cbc', $key, 0, $iv);
		return base64_encode($fingerprint.'::'.$encrypted.'::'.$salt.'::'.$iv);
    }

    /**
     * Decrypts a string generated with ::encrypt
     * @param string $string String to be decrpyted
     * @return string Decrypted string
     * @throws Exception
     */
    public static function decrypt(string $string) {
        list($encrypted, $key, $iv) = explode('::', base64_decode($string), 3);
        if (!password_verify(getenv('APP_KEY'), $key)) throw new \Mercurio\Exception\Runtime("APP_KEY in string does not match APP_KEY from running environment.", 1);
        
        return openssl_decrypt($encrypted, 'aes-256-cbc', $fingerprint, 0, $iv);
    }

}