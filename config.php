<?php
/**
 * Courier. Not CMS.
 * 
 * config.php
 * @package Mercurio
 * @subpackage Main
 * 
 * Welcome to Mercurio.
 * This is the configuration file, here is the only configuration you may need to setup manually
 * 
 * REMEMBER that you need to update your env file at app/src/.env.php 
 */

/**
 * Path to main app folder
 * @package Mercurio
 */
define('MROINDEX', __DIR__);

/**
 * Path to app loader
 * @package Mercurio
 * @subpackage App loader
 */
include MROINDEX.'/app/src/php/app.php';