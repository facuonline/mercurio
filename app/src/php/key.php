<?php
/**
 * key.php
 * @package Mercurio
 * @subpackage Key generator
 * 
 * App key generator, just run this file via your browser or your terminal to get a strong app key value
 */
require '../../../config.php';

$lame[] = microtime();
$lame[] = mt_rand(1111, 9999);
$lame[] = getenv('APP_URL');
$lame[] = openssl_random_pseudo_bytes(16);

$glue = base64_encode(random_bytes(4));
shuffle($lame);

$key = hash('sha256', implode($glue, $lame));
echo $key;