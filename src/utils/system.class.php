<?php 
/**
 * @package Mercurio
 * @subpackage Utilitary classes
 * 
 * System tools
 */
namespace Mercurio\Utils;
class System {

    /**
     * Ensures that system defined properties of entities are not in an array
     * @param array $array
     * @return array Input array with system properties added
     * @throws object Exception\Usage\SystemProperty 
     */
    public static function property(array $array) {
        if (array_key_exists('id', $array)
        && array_key_exists('stamp', $array)) throw new \Mercurio\Exception\Usage\SystemProperty('id or stamp');

        $array['id'] = \Mercurio\Utils\ID::new();
        $array['stamp'] = time();
        return $array;
    }

    /**
     * Ensures that system required properties of entities are in an array
     * @param array $keys
     * @param array $array
     * @param string $classmethod
     * @throws object Exception\Usage\SystemRequired
     */
    public static function required(array $keys, array $array, string $classmethod) {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) throw new \Mercurio\Exception\Usage\SystemRequired($classmethod, $$array, $key);
        }
    }

    /**
     * Ensures that properties are not empty
     * @param array $keys
     * @param array $array
     * @throws object Exception\User\EmptyField;
     */
    public static function emptyField(array $keys, array $array) {
        foreach ($keys as $key) {
            if (empty($array[$key])) throw new \Mercurio\Exception\User\EmptyField;
        }
    }

}
