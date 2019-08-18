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
	 * Timestamp base with custom epoch.\  
	 * 2 digit miliseconds.\
	 * 1 digit server ip based discriminator.\
	 * 1 digit connection port based discriminator.\
	 * 1 digit client ip based discriminator.\
	 * 1 digit random entropic discriminator.
	 * @return int */
	public static function new() : int {
		// Custom epoch from the january 1st 2019
		$time = time() - 1546300800;

		$miliseconds = gettimeofday()['usec'];
		$mili = substr($miliseconds, 0, 2);

		$serveraddress = base_convert(gethostbyname(gethostname()), 10, 10);
		$server = $serveraddress[random_int(0, strlen($serveraddress))-1];
		
		$portaddress = $_SERVER['REMOTE_PORT'];
		$port = $portaddress[random_int(0, strlen($portaddress))-1];
		
		$clientaddress = base_convert($_SERVER['REMOTE_ADDR'], 10, 10);
		$client = $clientaddress[random_int(0, strlen($clientaddress) -1)];
		
		$entropy = random_int(0, 9);
		
		return $time.$mili.$server.$port.$client.$entropy;
	}

	/**
	 * Encrypts and decrypts a ID to prevent CSRF
	 *
	 * Encrypted values are session wise decryptable 
	 *
	 * @param int|string $ID ID to encrypt or decrypt
	 * @return int|string */
	public static function enc($ID = false){
		if (!$ID) $ID = self::new();
		/* aes-192-ctr is chosen because it allows for faster system performance in encryption and this function is supposed to provide an obfuscation layer rather than actually cryptosafe values, that's also the reason for no random IV */
		if (ctype_digit($ID)) {
			$iv = substr(md5(getenv('APP_KEY')), -16);
			return openssl_encrypt($ID, 'aes-192-ctr', session_id(), 0, $iv);
		} else {
			$iv = substr(md5(getenv('APP_KEY')), -16);
			return openssl_decrypt($ID, 'aes-192-ctr', session_id(), 0, $iv);
		}
	}
}