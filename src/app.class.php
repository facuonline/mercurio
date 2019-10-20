<?php

namespace Mercurio;

/**
 * Mercurio main class \
 * **Must** call `App::setApp()` to be able to use Mercurio
 * @package Mercurio
 * @subpackage App class
 */
class App {

    /**
     * Set App settings and start Mercurio 
     * @param array $settings App generic settings
     * @param array $database Database connection arguments
     * @throws \Mercurio\Exception\Usage
     */
    public static function setApp(array $settings, array $database = []) {
        // check for minimum required app settings
        if (!array_key_exists('KEY', $settings)) throw new \Mercurio\Exception\Usage("setApp expects a 'KEY' index in given array.", 1);

        if (!array_key_exists('URL', $settings)) {
            throw new \Mercurio\Exception\Usage("setApp expects an 'URL' index in given array.", 1);
        } else {
            $settings['URL'] = rtrim($settings['URL'], '/') . '/';
        }

        foreach ($settings as $key => $value) {
            putenv("APP_$key=$value");
        }

        // Define app constants
        self::constants();
        
        // Configure database connection
        if (!empty($database)) self::prepareDatabase($database);

    }

    /**
     * Defines app constants
     */
    private static function constants() {

        /**
         * `APP_URL` must equals $_SERVER['DOCUMENT_ROOT] in terms of route
         */
        $APP_URL = parse_url(getenv('APP_URL'));
        $APP_ROOT = $APP_URL['scheme']
            .'://'
            .$APP_URL['host']
            .$APP_URL['path'];

         /**
         * Link to app root
         */
        define('APP_URL', $APP_ROOT);

        /**
         * App http scheme, stripped from `APP_URL`
         */
        define('APP_SCHEME', $APP_URL['scheme']);

        /**
         * App host, stripped from `APP_URL`
         */
        define('APP_HOST', $APP_URL['host']);

        /**
         * App path, stripped from `APP_URL`
         */
        define('APP_PATH', $APP_URL['path']);

        /**
         * Path to mercurio statics
         */
        define('APP_STATIC', 
            preg_replace('/\//', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'])
            .preg_replace('/\//', DIRECTORY_SEPARATOR, APP_PATH)
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
        );

        /**
         * Link to mercurio statics
         */
        define('APP_STATIC_LINK', 
            APP_URL
            .'mercurio/'
            .'static/'
        );

        /**
         * Path to mercurio users statics
         */
        define('APP_USERSTATIC', 
            APP_STATIC
            .'user'
            .DIRECTORY_SEPARATOR
        );

        /**
         * Link to mercurio user statics
         */
        define('APP_USERSTATIC_LINK', 
            APP_URL
            .'mercurio/'
            .'static/'
            .'user/'
        );

    }

    /**
     * Set necessary parameters for database connection
     * @param array $connection Database connection arguments
     */
    private static function prepareDatabase(array $connection) {
        // check that $connection array has needed indexes
        if (!array_key_exists('HOST', $connection)) throw new \Mercurio\Exception\Usage("setApp expects a 'HOST' index in database array.");
        if (!array_key_exists('USER', $connection)) throw new \Mercurio\Exception\Usage("setApp expects a 'USER' index in database array.");
        if (!array_key_exists('PASS', $connection)) throw new \Mercurio\Exception\Usage("setApp expects a 'PASS' index in database array.");
        if (!array_key_exists('NAME', $connection)) throw new \Mercurio\Exception\Usage("setApp expects a 'NAME' index in database array.");
        if (!array_key_exists('TYPE', $connection)) throw new \Mercurio\Exception\Usage("setApp expects a 'TYPE' index in database array.");

        foreach ($connection as $key => $value) {
            putenv("DB_$key=$value");
        }

        if (!array_key_exists('PREFIX', $connection)) $connection['PREFIX'] = 'mro';
        /**
         * App database table prefix
         */
        define('DB_PREFIX', $connection['PREFIX'].'_');

        /**
         * App database configuration table
         */
        define('DB_CONF', DB_PREFIX.'conf');

        /**
         * App database meta table
         */
        define('DB_META', DB_PREFIX.'meta');

        /**
         * App database users table
         */
        define('DB_USERS', DB_PREFIX.'users');

        /**
         * App database channels table
         */
        define('DB_CHANNELS', DB_PREFIX.'channels');

        /**
         * App database media table
         */
        define('DB_MEDIA', DB_PREFIX.'media');
    }

    /**
     * Get database connection parameters
     * @return array
     */
    public static function getDatabase() {
        return [
            'database_type' => getenv('DB_TYPE'),
            'database_name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ];
    }

}
