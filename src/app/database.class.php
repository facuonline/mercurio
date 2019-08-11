<?php
/**
 * Database connection and utils
 * @package Mercurio
 * @subpackage Included classes
 * 
 */

namespace Mercurio\App;
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

    /**
     * Get meta values for entities in database
     * @param int $target Id of target database entity
     * @param string|array $meta Name of meta field or array of, leave blank to bypass
     * @param string $grouping Name of meta group, leave blank to bypass
     * @return array|null Empty arrays for multiple selects, NULL for empty single key select
     */
    public function dbGetMeta(int $target, $meta = '', string $grouping = '') {
        // Get all meta
        if (empty($meta)
        && empty($grouping)) return $this->DB->select(DB_META, '*', [
            'target' => $target
        ]);
        // Get by group
        if (empty ($meta)
        && !empty($grouping)) return $this->DB->select(DB_META, '*', [
            'target' => $target,
            'grouping' => $grouping
        ]);
        // Get by array
        if (is_array($meta)) return $this->DB->select(DB_META, '*', [
            'target' => $target,
            'name' => $meta
        ]);
        // Get by name
        return $this->DB->get(DB_META, '*', [
            'target' => $target,
            'name' => $meta
        ]);
    }

    /**
     * Set meta properties for entities
     * @param int $target Id of target database entity
     * @param array $meta Associative array of meta values
     * @param string $grouping Name of meta group
     */
    public function dbSetMeta(int $target, array $meta, string $grouping = '') {
        foreach ($meta as $key => $value) {
            if (!is_string($key)) throw new \Mercurio\Exception\Usage\StringKeysRequired('setMeta');

            if (!is_null($this->dbGetMeta($target, $key, $grouping))) {
                if (empty($grouping)) $grouping = $this->dbGetMeta($target, $key)['grouping'];
    
                $this->DB->update(DB_META, [
                    'grouping' => $grouping,
                    'value' => $value
                ], [
                    'target' => $target,
                    'name' => $key
                ]);
            } else {
                $this->DB->insert(DB_META, [
                    'id' => \Mercurio\Utils\ID::new(),
                    'name' => $key,
                    'grouping' => $grouping,
                    'value' => $value,
                    'target' => $target,
                    'stamp' => time() 
                ]);
            }
        }
    }

    /**
     * Delete meta properties for objects
     * @param int $target Id of target database entity
     * @param string|array $meta Name of meta field or array of, leave blank to delete all fields
     * @param string $grouping Name of meta group
     */
    public function dbUnsetMeta(int $target, $meta = '', string $grouping = '') {
        // Delete all meta
        if (empty($meta)
        && empty($grouping)) {
            $this->DB->delete(DB_META, [
                'target' => $target
            ]);
        // Delete specific meta
        // Delete by group
        } elseif (!empty($grouping)) {
            $this->DB->delete(DB_META, [
                'target' => $target,
                'grouping' => $grouping
            ]);
        // Delete by name
        } else {
            $this->DB->delete(DB_META, [
                'target' => $target,
                'name' => $meta
            ]);
        }
    }

}
