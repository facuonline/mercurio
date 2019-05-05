<?php
/**
 * DB class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var object $DB Medoo instance
 */

namespace Mercurio;
class Database {
    public $DB;

    public function __construct() {
        $this->conn();
    }

    /**
     * To use when Database not instantiated
     */
    public function conn() {
        $this->DB = new \Medoo\Medoo([
            'database_type' => 'mysql',
            'database_name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ]);
        return $this->DB;
    }
    
    /**
     * Get a configuration value by name
     * @param string $name Config name
     * @return array|bool
     */
    public function getConfig(string $name) {
        $result = $this->DB->select('mro_configs', 
            ['value'],
            ['name' => $name]
        );
        if ($result) {
            return $result[0]['value'];
        } else {
            return false;
        }
    }

    /**
     * Set or update a configuration
     * @param string $name Config name
     * @param mixed $value Config value
     * @return object PDOStatement
     */
    public function setConfig(string $name, $value) {
        if ($this->getConfig($name)) {
            return $this->DB->update('mro_configs',
                ['value' => $value],
                ['name' => $name]
            );
        } else {
            return $this->DB->insert('mro_configs',
                ['name' => $name,
                'value' => $value]
            );
        }
    }
}