<?php
/**
 * app.php
 * @package Mercurio
 * @subpackage App loader
 * 
 * Initializes the app components
 */
 
/**
 * Load composer depencies
 * @subpackage Composer
 */
require MROINDEX.'/vendor/autoload.php';

/**
 * Load src index
 */
require MROINDEX.'/app/src/index.php';

/**
 * Run environmental variables
 */
require MROSRC.'/.env.php';

/**
 * Error and warning reporting, developer friendly
 */
function mroError($error_no, $error_string = '', $error_file = '', $error_line = '') {
    if (is_object($error_no)) {
        $error = "<pre><b>EXCEPTION:</b> $error_no</pre>\n";
    } else {
        $error = "<pre><b>ERROR:</b> [$error_no] $error_string\n\n
        In $error_file @ L#<b>$error_line</b></pre>\n";
    }
    error_log($error); print_r($error);
}
set_error_handler('mroError'); set_exception_handler('mroError');

/**
 * Check app key
 */
if (!getenv('APP_KEY')) {
    throw new Exception("APP KEY IS NOT PRESENT. Please configure your .env file at app/src/.env.php", 1);
    exit;
}

/**
 * Load functions
 */
require 'functions.php';

/**
 * Load classes
 */
require 'spl.php';