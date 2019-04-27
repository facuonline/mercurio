<?php
/**
 * index.php
 * @package Mercurio
 * @subpackage App loader
 */

require 'config.php';

$vera = new MroUser;
$vera->getUser('verano');

echo $vera->getLink();