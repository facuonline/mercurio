<?php

namespace Mercurio\Utils;

/** 
 * ID generation and obfuscation
 * @package Mercurio
 * @subpackage Included Classes
 * 
 * Inspired by Snowflake by Twitter, this class allows for decentralized, independent, meaningful id generation. 
 * Simpler, not as consuming as other implementations. 
 * _Not collision free_ but extremely unlikely to happen.
 * 
 * Not fixed length, with enough (a lot of years) time IDs will overflow 64 bits
 */
class ID {

	/**
	 * Create new int ID
	 * 
	 * Unix Timestamp base.\  
	 * 2 digit miliseconds.\
	 * 1 digit server ip based discriminator.\
	 * 1 digit connection port based discriminator.\
	 * 1 digit client ip based discriminator.\
	 * 1 digit random entropic discriminator.
	 * @return int 
	 */
	public static function new() : int {
		$time = time();

		$miliseconds = gettimeofday()['usec'];
		$mili = substr($miliseconds, 0, 3);

		$serveraddress = base_convert(gethostbyname(gethostname()), 10, 10);
		$server = $serveraddress[random_int(0, \strlen($serveraddress))-1];
		
		$portaddress = $_SERVER['REMOTE_PORT'];
		$port = $portaddress[random_int(0, \strlen($portaddress))-1];

		$clientaddress = base_convert($_SERVER['REMOTE_ADDR'], 10, 10);
		$client = $clientaddress[random_int(0, \strlen($clientaddress)-1)];

		$entropy = random_int(0, 9);
		
		return $time.$mili.$server.$port.$client.$entropy;
	}

	/**
	 * Encrypts an ID number using AES192-CTR
	 *
	 * Encrypted values are session wise decryptable 
	 *
	 * @param int $ID ID to encrypt or decrypt
	 * Leave int 0 to create a new ID
	 * @return string
	 * @see `\Mercurio\Utils\Encryption` for more advanced encryption
	 */
	public static function encrypt(int $ID = 0) : string {
		if ($ID === 0) $ID = self::new();
		/* aes-192-ctr is chosen because it allows for faster system performance in encryption and this function is supposed to provide an obfuscation layer rather than actually cryptosafe values, that's also the reason for no random IV */
		$iv = substr(md5(getenv('APP_KEY')), -16);
		return openssl_encrypt($ID, 'aes-192-ctr', session_id(), 0, $iv);
	}

	/**
	 * Decrypts an ID encoded by `\Mercurio\Utils\ID::encrypt()`
	 * 
	 * @return int Decrypted original ID
	 * @see `\Mercurio\Utils\Encryption` for more advanced encryption
	 */
	public static function decrypt(string $ID) : int {
		/* aes-192-ctr is chosen because it allows for faster system performance in encryption and this function is supposed to provide an obfuscation layer rather than actually cryptosafe values, that's also the reason for no random IV */
		$iv = substr(md5(getenv('APP_KEY')), -16);
		return openssl_decrypt($ID, 'aes-192-ctr', session_id(), 0, $iv);
	}
}