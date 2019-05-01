<?php
/**
 * key.php
 * @package Mercurio
 * @subpackage Key generator
 */
require '../../../config.php';

$key = mroKeyGen();

echo $key;