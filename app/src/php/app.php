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
 * Load static index
 */
require MROINDEX.'/app/static/index.php';

/**
 * Load vistas index
 */
require MROINDEX.'/app/vistas/index.php';

/**
 * Run environmental variables
 */
require MROSRC.'/.env.php';

/**
 * Start HTTP Requests library
 */
Requests::register_autoloader();

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
 * Start CSRFGuard library
 */
if (!file_exists(MROINDEX.'/vendor/owasp/csrf-protector-php/libs/config.php')) {
    $appKey = getenv('APP_KEY');
    $jsUrl = getenv('APP_URL').'vendor/owasp/csrf-protector-php/js/csrfprotector.js';
    $jsMessage = "This site attempts to protect users against Cross-Site Request Forgeries attacks. In order to do so, you must have JavaScript enabled in your web browser otherwise this site will fail to work correctly for you. See details of your web browser for how to enable JavaScript.";
    $csrfConfig[] = "<?php
    return [
        'CSRFP_TOKEN' => 'MercurioCSRF',
        'logDirectory' => '../log',
        'failedAuthAction' => [
            'GET' => 0,
            'POST' => 0
        ],
        'errorRedirectionPage' => '',
        'customErrorMessage' => '',
        'jsUrl' => '$jsUrl',
        'tokenLength' => 12,
        'cookieConfig' => [
            'path' => '',
            'domain' => '',
            'secure' => false,
            'expire' => '',
        ],
        'disabledJavascriptMessage' => '$jsMessage',
        'verifyGetFor' => []
    ];";
    file_put_contents(MROINDEX.'/vendor/owasp/csrf-protector-php/libs/config.php', $csrfConfig);
} else {
    csrfProtector::init();
}

/**
 * Load functions
 */
require 'functions.php';

/**
 * Load classes
 */
require 'spl.php';