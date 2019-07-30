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
    public static function staticDB() {
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
    public function db() {
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

    /**
     * Delete a configuration row from database
     * @param string $name Name of configuration to be deleted
     */
    public static function unsetConfig(string $name) {
        self::staticDB()->delete('mro_conf',
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
        && empty($grouping)) return $this->db()->select(DB_META, '*', [
            'target' => $target
        ]);
        // Get by group
        if (empty ($meta)
        && !empty($grouping)) return $this->db()->select(DB_META, '*', [
            'target' => $target,
            'grouping' => $grouping
        ]);
        // Get by array
        if (is_array($meta)) return $this->db()->select(DB_META, '*', [
            'target' => $target,
            'name' => $meta
        ]);
        // Get by name
        return $this->db()->get(DB_META, '*', [
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
    
                $this->db()->update(DB_META, [
                    'grouping' => $grouping,
                    'value' => $value
                ], [
                    'target' => $target,
                    'name' => $key
                ]);
            } else {
                $this->db()->insert(DB_META, [
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
            $this->db()->delete(DB_META, [
                'target' => $target
            ]);
        // Delete specific meta
        // Delete by group
        } elseif (!empty($grouping)) {
            $this->db()->delete(DB_META, [
                'target' => $target,
                'grouping' => $grouping
            ]);
        // Delete by name
        } else {
            $this->db()->delete(DB_META, [
                'target' => $target,
                'name' => $meta
            ]);
        }
    }

}
