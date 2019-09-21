<?php

namespace Mercurio\App;

/**
 * App persistent configurations
 * @package Mercurio
 * @subpackage App classes
 */
class Config extends \Mercurio\App\Model {

    public $data = [
        'id' => NULL, 
        'name' => NULL, 
        'value' => NULL, 
        'stamp' => NULL
    ];

    public $db_table = DB_CONF;

    /**
     * Prepare configuration to be selected by name
     * @param string $name
     */
    public function getByName(string $name) {
        $this->get_by = ['name' => $name];
    }

    /**
     * Prepare configurations to be selected by value
     * @param mixed $value
     */
    public function selectByValue($value) {
        $this->get_by = ['value' => $value];
    }

    /**
     * Set configuration name
     * @param string $name
     * @return object Self instance
     */
    public function setName(string $name) {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Set configuration value
     * @param mixed $value
     * @return object Self instance
     */
    public function setValue($value) {
        $this->data['value'] = $value;
        return $this;
    }

    /**
     * Obtain configuration value
     * @return mixed
     */
    public function getValue() {
        return $this->data['value'];
    }

}
