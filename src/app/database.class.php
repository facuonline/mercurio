<?php
/**
 * Database connection and utils
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var object $DB Medoo instance
 */

namespace Mercurio\App;
class Database {

    /**
     * Make a database connection, static
     * @return object Medoo instance
     */
    protected static function staticDB() {
        return new \Medoo\Medoo([
            'database_type' => getenv('DB_TYPE'),
            'database_name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ]);
    }

    /**
     * Make a database connection, non static
     * @return object Medoo instance
     */
    protected function db() {
        return new \Medoo\Medoo([
            'database_type' => getenv('DB_TYPE'),
            'database_name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ]);
    }
    
    /**
     * Get a configuration value by name
     * @param string $name Config name
     * @return array|bool
     */
    public static function getConfig(string $name) {
        $result = self::staticDB()->get('mro_conf', '*', ['name' => $name]);
        return ($result ? $result['value'] : false);
    }

    /**
     * Set or update a configuration
     * @param string $name Config name
     * @param mixed $value Config value
     * @return object PDOStatement
     */
    public static function setConfig(string $name, $value) {
        if (self::getConfig($name)) {
            self::staticDB()->update('mro_conf', 
                ['value' => $value],
                ['name' => $name]
            );
        } else {
            self::staticDB()->insert('mro_conf',
                [
                    'name' => $name,
                    'value' => $value
                ]
            );
        }
    }
}