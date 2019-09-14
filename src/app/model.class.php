<?php

namespace Mercurio\App;

/**
 * App components model class, use this if you want to create new components for your app
 * @package Mercurio
 * @subpackage App classes
 */
class Model {

    /**
     * Associative array of component data \
     * `*` Is used to define all database columns 
     */
    public $data = '*';

    /**
     * Database ID of object, also in $data
     */
    public $id = NULL;

    /**
     * Array to prepare component selection criteria
     */
    public $get_by;

    /**
     * Table where this class objects are stored
     * Must be defined when extending a new component
     */
    public $db_table = NULL;

    public function __construct() {
        if ($this->db_table === NULL) throw new \Mercurio\Exception\Usage("App components must define a 'db_table' class variable with public scope.");
    }

    /**
     * Prepare component to be selected by given arguments
     * @param array $getBy Medoo SELECT $where
     * @see http://medoo.in/api/where
     */
    public function getBy(array $getBy) {
        $this->get_by = $getBy;
    }

    /**
     * Prepare component to be selected by ID
     * @param int $id Component numeric ID
     */
    public function getById(int $id) {
        $this->get_by = ['id' => $id];
    }

    /**
     * Obtain component numeric ID
     * @param bool $string Return id as a string
     * @return int|string
     */
    public function getId(bool $string = false) {
        if ($string) return (string) $this->id;
        return (int) $this->id;
    }

    /**
     * Obtain component UNIX timestamp of database insertion
     * @param int UNIX timestamp
     */
    public function getTimestamp() {
        return (int) $this->data['stamp'];
    }

}
