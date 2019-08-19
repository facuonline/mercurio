<?php

namespace Mercurio\App;

/**
 * Base model class for app database based entities
 * 
 * @package Mercurio
 * @subpackage App class
 */
class Model {

    /**
     * Associative array of entity info
     */
    public $info;

    /**
     * Instance of SQL builder via dependency injected database class
     */
    protected $DB;

    /**
     * Name of database table for entities
     */
    protected $DBTABLE = false;

    public function __construct(\Mercurio\App\Database $database) {
        $this->info = false;
        $this->DB = $database->getSQL();

        if (!$this->DBTABLE) throw new \Mercurio\Exception\Usage("You must define a DBTABLE class property.");
    }

    /**
     * Finds entities hint for database selection
     * @return null|int|string NULL on no found hint
     */
    public function getHint() {
        if ($this->info) return $this->info['id'];
        if (\Mercurio\Utils\Router::getTarget()) return \Mercurio\Utils\Router::getTarget();
        
        return NULL;
    }
    
    /**
     * Load entity from database into instance
     * @param int|string $hint Entity identifier
     * @param callable $closure Closure function to access info without loading instance
     * function (array $entity) :
     * @param callable $fallback Closure function to execute in case of failure retrieving user
     * function (array $entity) :
     * @param array|string Properties to be returned
     * Use string '*' to return all properties
     * @return callable|bool
     */
    public function get($hint = false, callable $closure = NULL, callable $fallback = NULL, $properties = '*') {
        if(!$hint) $hint = $this->getHint();

        $entity = $this->DB->get($this->DBTABLE, $properties, [
            'OR' => [
                'id' => $hint,
                'handle' => $hint,
                'email' => $hint
            ]
        ]);

        if ($entity !== NULL
        && $closure !== NULL) return $closure($entity);

        if ($entity === NULL
        && $fallback !== NULL) return $fallback($entity);

        $this->info = $entity;
        return (bool) $this->info;
    }

    /**
     * Update entity properties in database
     * @param array $properties Associative array of properties and values
     */
    public function set(array $properties) {
        $this->get(false, function($entity) use ($properties) {
            $this->DB->update($this->DBTABLE, $properties, ['id' => $entity['id']]);
        });
    }

    /**
     * Delete entity from database
     * @param bool $meta On/off meta deletion
     */
    public function unset(bool $meta = true) {
        if ($meta) $this->unsetMeta();
        $this->get(false, function($entity) {
            $this->DB->delete($this->DBTABLE, ['id' => $entity['id']]);
        });
    }

    /**
     * Load entity meta properties from database into instance
     * @param string|array $meta Name of meta property or array of
     * Leave blank to return all of them
     * @param string $grouping Name of meta group
     */
    public function getMeta($meta = '', string $grouping = '') {
        return $this->get(false, function($entity) use ($meta, $grouping) {
            // Get all meta
            if (empty($meta)
            && empty($grouping)) return $this->DB->select(DB_META, '*', [
                'target' => $entity['id']
            ]);
            // Get by group
            if (empty ($meta)
            && !empty($grouping)) return $this->DB->select(DB_META, '*', [
                'target' => $entity['id'],
                'grouping' => $grouping
            ]);
            // Get by array
            if (is_array($meta)) return $this->DB->select(DB_META, '*', [
                'target' => $entity['id'],
                'name' => $meta
            ]);
            // Get by name
            return $this->DB->get(DB_META, '*', [
                'target' => $entity['id'],
                'name' => $meta
            ]);
        });
    }

    /**
     * Update entity meta properties in database
     * @param string|array $meta Associative array of meta properties 
     * [name => value]
     * @param string $grouping Name of meta group
     */
    public function setMeta(array $meta, string $grouping = '') {
        $this->get(false, function($entity) use ($meta, $grouping) {
            foreach ($meta as $key => $value) {
                if (!is_string($key)) throw new \Mercurio\Exception\Usage\StringKeysRequired('setMeta');
    
                if ($this->getMeta($key, $grouping) !== NULL) {
                    if (empty($grouping)) $grouping = $this->getMeta($key)['grouping'];
        
                    $this->DB->update(DB_META, [
                        'grouping' => $grouping,
                        'value' => $value
                    ], [
                        'target' => $entity['id'],
                        'name' => $key
                    ]);
                } else {
                    /**
                     * Utils\System::property() is not used as meta insertion might be too large
                     * This way we avoid function call overhead, gaining a rather minimal performance time, 
                     * but a time that might be critical sometime
                     */
                    $this->DB->insert(DB_META, [
                        'id' => \Mercurio\Utils\ID::new(),
                        'name' => $key,
                        'grouping' => $grouping,
                        'value' => $value,
                        'target' => $entity['id'],
                        'stamp' => time() 
                    ]);
                }
            }
        });
    }

    /**
     * Delete entity meta properties from database
     * @param string|array $meta Name of meta property or array of
     * Leave blank to delete all of them
     * @param string $grouping Name of meta group
     */
    public function unsetMeta($meta = '', string $grouping = '') {
        $this->get(false, function($entity) use ($meta, $grouping) {
            // Delete all meta
            if (empty($meta)
            && empty($grouping)) {
                $this->DB->delete(DB_META, [
                    'target' => $entity['id']
                ]);
            // Delete specific meta
            // Delete by group
            } elseif (!empty($grouping)) {
                $this->DB->delete(DB_META, [
                    'target' => $entity['id'],
                    'grouping' => $grouping
                ]);
            // Delete by name
            } else {
                $this->DB->delete(DB_META, [
                    'target' => $entity['id'],
                    'name' => $meta
                ]);
            }
        });
    }

    /**
     * Return entity id
     * @param bool $string Set to true to return id as a string
     * @param string|int Entity id
     */
    public function getID(bool $string = false) {
        return $this->get(false, function($entity) use ($string) {
            if ($string) return (string) $entity['id'];
            return (int) $entity['id'];
        });
    }

    /**
     * Return entity timestamp
     * @return int Entity timestamp in UNIX epoch
     */
    public function getTimeStamp() {
        return $this->get(false, function($entity) {
            return (int) $entity['stamp'];
        });
    }

}
