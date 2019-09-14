<?php

namespace Mercurio\App;

/**
 * Database middleware
 * @package Mercurio
 * @subpackage App classes
 */
class Database {

    /**
     * SQL query builder Medoo
     * @see https://github.com/catfan/medoo
     */
    protected $sql;

    /**
     * @param array $parameters Database connection arguments,
     * use `Mercurio\App::getDatabase()` if you have configured the database with `App`
     * @see https://medoo.in/api/new
     */
    public function __construct(array $parameters) {
        $this->sql = new \Medoo\Medoo($parameters);
    }

    /**
     * Obtain SQL builder instance
     * @return object
     */
    public function getSql() {
        return $this->sql;
    }

    /**
     * Insert new record in database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return object PDO Statement
     */
    public function insert(object $object) {
        // These are system properties
        $object->data['id'] = \Mercurio\Utils\ID::new();
        $object->data['stamp'] = time();

        return $this->sql->insert($object->dbTable, $object->data);
    }

    /**
     * Get record from database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return object|null Loaded instance of entry object, NULL on failure
     */
    public function get(object $object) {
        $data = $this->sql->get($object->dbTable, '*', $object->getBy);
        if (!$data) return NULL;

        // Reassign data to object and return it
        $class = get_class($object);
        $object = new $class;
        $object->data = $data;
        $object->id = $object->data['id'];

        return $object;
    }

    /**
     * Update record in database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return object PDO Statement
     */
    public function update(object $object) {
        // Ensure system properties remain untouched
        if (array_key_exists('id', $object->data)) unset($object->data['id']);
        if (array_key_exists('stamp', $object->data)) unset($object->data['stamp']);

        return $this->sql->update($object->dbTable, $object->data, ['id' => $object->id]);
    }

    /**
     * Delete record in database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return object PDO Statement
     */
    public function delete(object $object) {
        return $this->sql->delete($object->dbTable, ['id' => $object->id]);
    }

}
