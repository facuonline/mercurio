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
 * Alternatively you can just open your browser and go to Mercurio, there's a GUI setup page
 */

/**
 * Environmental variables
 */
$variables = [
    // Server address of database
    'DB_HOST' => 'localhost',
    // Username for database
    'DB_USER' => 'root',
    // User password
    'DB_PASS' => '',
    // Database name
    'DB_NAME' => 'mercurio',

    // This key is your site secret password
    // Mercurio can generate a strong safe password for you at app/src/php/key.php
    'APP_KEY' => 'e214ceda8a935dad2bab9fe4d84739e2de75b03501569e2aa29c7c48436e6357',
    // Site URL, don't forget to ALWAYS add a final forward slash (/)
    'APP_URL' => 'http://localhost/mercurio/',
    // Folder name of view model in app/vistas to display
    'APP_VISTA' => 'skeleton'
];
foreach ($variables as $key => $value) {
    putenv("$key=$value");
}

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