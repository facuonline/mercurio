<?php

namespace Mercurio\App;

/**
 * Database connection \
 * To configure your database use `\Mercurio\App::setDatabase()`
 * @package Mercurio
 * @subpackage App classes
 */
class Database {

    /**
     * Instance of SQL middleware Medoo
     */
    public $DB;

    public function __construct() {
        $this->DB = new \Medoo\Medoo($this->getDatabaseParams());
    }

    /**
     * Get SQL builder instance 
     * @return object instance of SQL builder Medoo
     */
    public function getSQL() {
        return $this->DB;
    }

    /**
     * Get database connection parameters
     * @return array
     */
    public function getDatabaseParams() {
        return [
            'database_type' => getenv('DB_TYPE'),
            'database_name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ];
    }
    
    /**
     * Get a configuration value by name
     * @param string $name Config name
     * @return array|bool
     */
    public function getConfig(string $name) {
        $result = $this->DB->get('mro_conf', '*', ['name' => $name]);
        return ($result ? $result['value'] : false);
    }

    /**
     * Set or update a configuration
     * @param string $name Config name
     * @param mixed $value Config value
     */
    public function setConfig(string $name, $value) {
        if ($this->getConfig($name)) {
            $this->DB->update('mro_conf', 
                ['value' => $value],
                ['name' => $name]
            );
        } else {
            $this->DB->insert('mro_conf',
                [
                    'name' => $name,
                    'value' => $value
                ]
            );
        }
    }

    /**
     * Delete a configuration row from database
     * @param string $name Name of configuration to be deleted
     */
    public function unsetConfig(string $name) {
        $this->DB->delete('mro_conf',
            ['name' => $name]
        );
    }

}
