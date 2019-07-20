<?php
/**
 * App *Mercurio main class*
 * @package Mercurio
 * @subpackage App class
 */
namespace Mercurio;
class App {

    /**
     * Set necessary parameters for database connection
     * @param array $connection 
     * @throws object Exception if array does not contain expected values
     */
    public static function setDatabase(array $connection = []) {
        // check that $connection array has needed indexes
        if (!array_key_exists('HOST', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'HOST' index in given array.", 1);
        if (!array_key_exists('USER', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'USER' index in given array.", 1);
        if (!array_key_exists('PASS', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'PASS' index in given array.", 1);
        if (!array_key_exists('NAME', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'NAME' index in given array.", 1);

        foreach ($connection as $key => $value) {
            putenv("DB_$key=$value");
        }
    }

    /**
     * Set App settings
     * @param array $settings App generic settings
     * @param array $connection Database connection arguments
     * @throws object Usage exception if required setting not present
     */
    public static function setApp(array $settings = [], array $connection = []) {
        // check for minimum required app settings
        if (!getenv('APP_KEY') && !array_key_exists('KEY', $settings)) throw new \Mercurio\Exception\Usage("setApp expects a 'KEY' index in given array. Use \Mercurio\App::randomKey to generate a safe hash value.", 1);
        if (!getenv('APP_URL') && !array_key_exists('URL', $settings)) throw new \Mercurio\Exception\Usage("setApp expects an 'URL' index in given array.", 1);

        foreach ($settings as $key => $value) {
            putenv("APP_$key=$value");
        }
        if (!empty($connection)) self::setDatabase($connection);

        // Define system constants
        self::constants();

        // Init Session accross app
        \Mercurio\Utils\Session::start();
    }

    /**
     * Defines system constants
     */
    private function constants() {
        define('APP_STATIC', 
            $_SERVER['DOCUMENT_ROOT']
            .DIRECTORY_SEPARATOR
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
        );

        define('APP_STATIC_ABS', 
            $_SERVER['DOCUMENT_ROOT']
            .DIRECTORY_SEPARATOR
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
        );

        define('APP_USERSTATIC', 
            $_SERVER['DOCUMENT_ROOT']
            .DIRECTORY_SEPARATOR
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
            .'user'
            .DIRECTORY_SEPARATOR
        );

        define('APP_USERSTATIC_ABS', 
            dirname(getenv('APP_URL'))
            .DIRECTORY_SEPARATOR
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
            .'user'
            .DIRECTORY_SEPARATOR
        );
    }

    /**
     * Returns an App setting set by setApp
     * @param string $key
     * @return mixed
     * @throws object Runtime exception if setting not found
     */
    public static function getApp(string $key) {
        if (getenv('APP_'.$key)) {
            if ($key == 'URL') return rtrim(getenv('APP_URL'), '/').'/';
            return getenv('APP_'.$key);
        } else {
            throw new \Mercurio\Exception\Runtime("getApp could not find '$key' setting.", 400);
        }
    }

    /**
     * Returns a very random sha256 hash
     * @param mixed $entropy Optional additional entropy
     * @return string
     */
    public static function randomKey($entropy = 'EUreka') {
        $lame[] = microtime();
        $lame[] = mt_rand(1111, 9999);
        $lame[] = $_SERVER['PHP_SELF'];
        $lame[] = openssl_random_pseudo_bytes(16);
        $lame[] = $entropy;
        $glue = base64_encode(random_bytes(4));
        shuffle($lame);
        return hash('sha256', implode($glue, $lame));
    }

}