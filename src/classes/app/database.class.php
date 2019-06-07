<?php
/**
 * DB class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var object $DB Medoo instance
 */

namespace Mercurio\App;
class Database {

    protected static $DB;

    /**
     * Make a database connection
     * @return object Medoo instance
     */
    protected static function db() {
        self::$DB = new \Medoo\Medoo([
            'database_type' => 'mysql',
            'database_name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ]);
        return self::$DB;
    }
    
    /**
     * Get a configuration value by name
     * @param string $name Config name
     * @return array|bool
     */
    public static function getConfig(string $name) {
        $result = self::db()->select(
            'mro_configs', 
            ['value'],
            ['name' => $name]
        );
        return ($result ? $result[0]['value'] : false);
    }

    /**
     * Set or update a configuration
     * @param string $name Config name
     * @param mixed $value Config value
     * @return object PDOStatement
     */
    public static function setConfig(string $name, $value) {
        if (self::getConfig($name)) {
            return self::db()->update(
                'mro_configs',
                ['value' => $value],
                ['name' => $name]
            );
        } else {
            return self::db()->insert(
                'mro_configs',
                [
                    'name' => $name,
                    'value' => $value
                ]
            );
        }
    }
}