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
     * Returns an App setting set by setApp
     * @param string $key
     * @return mixed
     * @throws \Mercurio\Exception\Runtime
     */
    public static function getApp(string $key) {
        $key = strtoupper($key);

        if (getenv('APP_'.$key)) {
            if ($key == 'URL') return rtrim(getenv('APP_URL'), '/').'/';
            return getenv('APP_'.$key);
        } else {
            throw new \Mercurio\Exception\Runtime("getApp could not find '$key' setting.", 400);
        }
    }

    /**
     * Set App settings and start Mercurio 
     * @param array $settings App generic settings
     * @param array $database Database connection arguments
     * @throws \Mercurio\Exception\Usage
     */
    public static function setApp(array $settings, array $database = []) {
        // check for minimum required app settings
        if (!getenv('APP_KEY') && !array_key_exists('KEY', $settings)) throw new \Mercurio\Exception\Usage("setApp expects a 'KEY' index in given array. Use \Mercurio\App::randomKey to generate a safe hash value.", 1);
        if (!getenv('APP_URL') && !array_key_exists('URL', $settings)) throw new \Mercurio\Exception\Usage("setApp expects an 'URL' index in given array.", 1);

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
        $APP_URL = parse_url(self::getApp('URL'));
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
            $_SERVER['DOCUMENT_ROOT']
            .DIRECTORY_SEPARATOR
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
            $_SERVER['DOCUMENT_ROOT']
            .DIRECTORY_SEPARATOR
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
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

    /**
     * Set database tables and columns via SQL \
     * Run only if your database hasn't been set beforehand
     * @param object $DB Instance of dependency injection `\Mercurio\App\Database`
     * @throws \Mercurio\Exception\Usage 
     */
    public static function setDatabase(\Mercurio\App\Database $DB) {
        // Check that database connection parameters have been set
        if (!getenv('DB_NAME')) throw new \Mercurio\Exception\Usage("You must define your database connection using \Mercurio\App::setApp() before setting up the Database.");

        // Set up db
        $dbName = getenv('DB_NAME');
        $DB = $DB->getSQL();
        $DB->query("CREATE DATABASE $dbName");

        // Set up tables
        $DB->create(DB_CONF, [
            'id' => [
                'BIGINT',
                'NOT NULL'
            ],
            'name' => [
                'VARCHAR(30)',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'value' => [
                'VARCHAR(255)',
                'NOT NULL'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ]);

        $DB->create(DB_META, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'name' => [
                'VARCHAR(30)',
                'NOT NULL'
            ],
            'grouping' => [
                'VARCHAR(30)',
            ],
            'value' => [
                'VARCHAR(255)',
                'NOT NULL'
            ],
            'target' => [
                'BIGINT',
                'NOT NULL'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ]);

        $DB->create(DB_USERS, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'handle' => [
                'VARCHAR(26)',
            ],
            'email' => [
                'VARCHAR(255)'
            ],
            'nickname' => [
                'VARCHAR(255)'
            ],
            'password' => [
                'VARCHAR(255)',
                'NOT NULL'
            ],
            'img' => [
                'VARCHAR(255)'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ]);

        $DB->create(DB_CHANNELS, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'handle' => [
                'VARCHAR(26)',
            ],
            'author' => [
                'BIGINT',
            ],
            'channel' => [
                'BIGINT'
            ],
            'body' => [
                'VARCHAR(4000)',
                'FULL TEXT'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ]);

        $DB->create(DB_MEDIA, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'author' => [
                'BIGINT',
            ],
            'channel' => [
                'BIGINT',
            ],
            'body' => [
                'TEXT',
                'FULL TEXT'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ]);
        
    }

    /**
     * Returns a very random sha256 hash
     * @param mixed $entropy Optional additional entropy
     * @return string SHA256 hash
     */
    public static function randomKey($entropy = 'EUreka') {
        $lame[] = microtime();
        $lame[] = mt_rand(1111, 9999);
        $lame[] = openssl_random_pseudo_bytes(16);
        $lame[] = $entropy;
        $glue = base64_encode(random_bytes(4));
        shuffle($lame);
        return hash('sha256', implode($glue, $lame));
    }

}
