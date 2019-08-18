<?php

namespace Mercurio;

/**
 * Mercurio main class \
 * **Must** instantiate to be able to use Mercurio
 * @package Mercurio
 * @subpackage App class
 */
class App {

    /**
     * Set necessary parameters for database connection
     * @param array $connection 
     * @throws object Exception if array does not contain expected values
     */
    public static function setDatabase(array $connection) {
        // check that $connection array has needed indexes
        if (!array_key_exists('HOST', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'HOST' index in given array.");
        if (!array_key_exists('USER', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'USER' index in given array.");
        if (!array_key_exists('PASS', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'PASS' index in given array.");
        if (!array_key_exists('NAME', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'NAME' index in given array.");
        if (!array_key_exists('TYPE', $connection)) throw new \Mercurio\Exception\Usage("setDatabase expects a 'TYPE' index in given array.");


        foreach ($connection as $key => $value) {
            putenv("DB_$key=$value");
        }

        // Set up db
        $dbName = getenv('DB_NAME');
        $DB = new \Mercurio\App\Database;
        $DB = $DB->getSQL();
        $DB->query("CREATE DATABASE $dbName");

        /**
         * App database name
         */
        define('DB_NAME', $dbName);


        $dbPrefix = 'mro_';
        if (array_key_exists('PREFIX', $connection)) $dbPrefix = $connection['PREFIX'].'_';

        /**
         * App database table prefix
         */
        define('DB_PREFIX', $dbPrefix);

        // Set up tables
        $DB->create(DB_PREFIX.'conf', [
            'name' => [
                'VARCHAR(30)',
                'NOT NULL'
            ],
            'value' => [
                'VARCHAR(255)',
                'NOT NULL'
            ]
        ]);

        /**
         * App database configuration table
         */
        define('DB_CONF', $dbPrefix.'conf');

        $DB->create(DB_PREFIX.'meta', [
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

        /**
         * App database meta table
         */
        define('DB_META', $dbPrefix.'meta');

        $DB->create(DB_PREFIX.'users', [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'handle' => [
                'VARCHAR(26)',
                'NOT NULL'
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
            'token' => [
                'VARCHAR(255)'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ]);

        /**
         * App database users table
         */
        define('DB_USERS', $dbPrefix.'users');

        $DB->create(DB_PREFIX.'channels', [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'handle' => [
                'VARCHAR(26)',
                'NOT NULL'
            ],
            'author' => [
                'BIGINT',
                'NOT NULL'
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

        /**
         * App database channels table
         */
        define('DB_CHANNELS', $dbPrefix.'channels');

        $DB->create(DB_PREFIX.'media', [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY_KEY'
            ],
            'author' => [
                'BIGINT',
                'NOT NULL'
            ],
            'channel' => [
                'BIGINT',
                'NOT NULL'
            ],
            'content' => [
                'TEXT',
                'FULL TEXT'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ]);

        /**
         * App database media table
         */
        define('DB_MEDIA', $dbPrefix.'media');
    }

    /**
     * Set necessary parameters for CSRF protection
     * @param array $csrf
     * @see https://github.com/mebjas/CSRF-Protector-PHP/wiki/Configurations
     */
    public static function setOwasp(array $csrf = []) {
        if (empty($csrf)) $csrf = [
            "CSRFP_TOKEN" => 'MercurioCSRF',
            "logDirectory" => "../log",
            "failedAuthAction" => array(
                "GET" => 0,
                "POST" => 0),
            "errorRedirectionPage" => "",
            "customErrorMessage" => "",
            "jsUrl" => APP_CSRFJS,
            "tokenLength" => 10,
            "cookieConfig" => array(
                "path" => '',
                "domain" => '',
                "secure" => false,
                "expire" => '',
            ),
            "disabledJavascriptMessage" => "This site attempts to protect users against <a href=\"https://www.owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29\">
            Cross-Site Request Forgeries </a> attacks. In order to do so, you must have JavaScript enabled in your web browser otherwise this site will fail to work correctly for you.
            See details of your web browser for how to enable JavaScript.",
            "verifyGetFor" => array()
        ];

        file_put_contents(APP_CSRFPHP, $csrf);
    }

    /**
     * Set App settings
     * @param array $settings App generic settings
     * @param array $connection Database connection arguments
     * @throws object Usage exception if required setting not present
     */
    public static function setApp(array $settings, array $connection = []) {
        // check for minimum required app settings
        if (!getenv('APP_KEY') && !array_key_exists('KEY', $settings)) throw new \Mercurio\Exception\Usage("setApp expects a 'KEY' index in given array. Use \Mercurio\App::randomKey to generate a safe hash value.", 1);
        if (!getenv('APP_URL') && !array_key_exists('URL', $settings)) throw new \Mercurio\Exception\Usage("setApp expects an 'URL' index in given array.", 1);

        foreach ($settings as $key => $value) {
            putenv("APP_$key=$value");
        }
        if (!empty($connection)) self::setDatabase($connection);

        // Define system constants
        self::constants();

    }

    /**
     * Defines system constants
     */
    private function constants() {

        $APP_URL = parse_url(self::getApp('URL'));
        $APP_ROOT = $APP_URL['scheme']
        .'://'
        .$APP_URL['host'];

        $APP_VENDOR = dirname(__FILE__, 4);

        /**
         * Link to app root
         */
        define('APP_ROOT', $APP_ROOT);

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
        define('APP_STATIC_ABS', 
            $APP_ROOT
            .DIRECTORY_SEPARATOR
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
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
        define('APP_USERSTATIC_ABS', 
            $APP_ROOT
            .DIRECTORY_SEPARATOR
            .'mercurio'
            .DIRECTORY_SEPARATOR
            .'static'
            .DIRECTORY_SEPARATOR
            .'user'
            .DIRECTORY_SEPARATOR
        );

        /**
         * Link to Owasp CSRF js file
         */
        define('APP_CSRFJS', 
            $APP_ROOT
            .'vendor'
            .DIRECTORY_SEPARATOR
            .'owasp'
            .DIRECTORY_SEPARATOR
            .'csrf-protector-php'
            .DIRECTORY_SEPARATOR
            .'js'
            .DIRECTORY_SEPARATOR
            .'csrsprotector.js'
        );

        /**
         * Path to Owasp CSRF config file
         */
        define('APP_CSRFPHP', 
            $APP_VENDOR
            .DIRECTORY_SEPARATOR
            .'owasp'
            .DIRECTORY_SEPARATOR
            .'csrf-protector-php'
            .DIRECTORY_SEPARATOR
            .'libs'
            .DIRECTORY_SEPARATOR
            .'config.php'
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
