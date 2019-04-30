<?php
/** 
 * MroGID class
 * @package Mercurio
 * @subpackage Included Classes
 * 
 * GID generation and obfuscation
 * Generated In(cremental)teger Discriminator
 * Inspired by Snowflake by Twitter, GIDs allow for decentralized, independent, meaningful id generation. 
 * Simpler, not as consuming as other implementations. 
 * _Not collision free_ but extremely unlikely to happen.
 * 
 * Not fixed length, with enough (a lot of years) time GIDs will overflow 64 bits
 * 
 * @var int $GID Stores the generated GID
 */

class utils_MroGID {
    private static $GID;

	/**
	 * Create new int GID
	 *
	 * Timestamp base with custom epoch
	 * 1 digit server ip based discriminator
	 * 1 digit connection port based discriminator
	 * 1 digit client ip based discriminator
	 * 1 digit random entropic discriminator */
	public static function new(){
		// Custom epoch from the january 1st 2019
		$time = time() - 1546300800;
		$server = substr(base_convert(gethostbyname(gethostname()), 10, 10), -1);
		$port = substr($_SERVER['REMOTE_PORT'], -1);
		$client = substr(base_convert($_SERVER['REMOTE_ADDR'], 10, 10), -1);
		$entropy = random_int(0, 9);

		self::$GID = $time.$server.$port.$client.$entropy;
		return self::$GID;
	}

	/**
	 * Encrypts and decrypts a GID to prevent session hijacking and csrf
	 *
	 * Encrypted values are session wise decryptable 
	 *
	 * @param int|string $GID GID to encrypt or decrypt
	 * @return int|string */
	public static function enc($GID = false){
		if ($GID) { 
			self::$GID = $GID;
		} else {
			self::new();
		}
		/* aes-192-ctr is chosen because it allows for faster system performance in encryption and this function is supposed to provide an obfuscation layer rather than actually cryptosafe values, that's also the reason for no random IV */
		if (ctype_digit(self::$GID)) {
			$iv = substr(md5(getenv('APP_KEY')), -16);
			return openssl_encrypt(self::$GID, 'aes-192-ctr', session_id(), 0, $iv);
		} else {
			$iv = substr(md5(getenv('APP_KEY')), -16);
			return openssl_decrypt(self::$GID, 'aes-192-ctr', session_id(), 0, $iv);
		}
	}
}