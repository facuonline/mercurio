<?php
/**
 * key.php
 * @package Mercurio
 * @subpackage Key generator
 * 
 * App key generator, just run this file via your browser or your terminal to get a strong app key value
 */
$lame[] = microtime();
$lame[] = getenv('DB_HOST');
$lame[] = getenv('DB_USER');
$lame[] = getenv('DB_PASS');
$lame[] = getenv('DB_NAME');
$lame[] = openssl_random_pseudo_bytes(16);

$glue = base64_encode(random_bytes(4));
shuffle($lame);

$key = hash('sha256', implode($glue, $lame));
echo $key;