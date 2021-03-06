<?php

namespace Mercurio\App;

/**
 * Meta properties wrapper model
 * @package Mercurio
 * @subpackage App classes
 */
class Meta extends Model {

    public $data = [
        'id' => NULL,
        'name' => NULL,
        'grouping' => NULL,
        'value' => NULL,
        'target' => NULL,
        'stamp' => NULL
    ];

    public $db_table = DB_META;

    /**
     * Select meta properties of parent object
     * @param object $object Loaded instance of class `Mercurio\App\*`
     */
    public function selectByTarget($object) {
        $this->get_by = ['target' => $object->id];
    }

    /**
     * Select meta properties of object by group
     * @param string $group Name of meta group
     * @param object $object Loaded instance of class `Mercurio\App\*`
     */
    public function selectByGroup(string $group, $object) {
        $this->get_by = ['group' => $group, 'target' => $object->id];
    }

    /**
     * Select a meta property of object of parent by name
     * @param string $name Name of meta property
     * @param object $object Instance of class `Mercurio\App\*`
     */
    public function getByName(string $name, $object) {
        $this->get_by = ['name' => $name, 'target' => $object->id];
    }

    /**
     * Return meta property name
     * @return string
     */
    public function getName() {
        return $this->data['name'];
    }

    /**
     * Set meta property name
     * @param string $name
     * @return object Self instance
     */
    public function setName(string $name) {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Return meta property value
     * @return mixed
     */
    public function getValue() {
        return $this->data['value'];
    }

    /**
     * Set meta property value
     * @param mixed $value
     * @return object Self instance
     */
    public function setValue($value) {
        $this->data['value'] = $value;
        return $this;
    }

    /**
     * Return meta property group
     * @return string 
     */
    public function getGroup() {
        return $this->data['group'];
    }

    /**
     * Set meta property group
     * @param string $group
     * @return object Self instance
     */
    public function setGroup(string $group) {
        $this->data['group'] = $group;
        return $this;
    }

    /**
     * Set meta property target
     * @param object $object Loaded instance of class `Mercurio\App\*`
     * @return object Self intance
     */
    public function setTarget($object) {
        $this->data['target'] = $object->id;
        return $this;
    }
    
}
