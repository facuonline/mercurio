<?php
namespace Mercurio\Exception\Usage;
/**
 * These types of exceptions are triggered on third party developers failure \
 * e.g When not providing a necessary array element
 * @package Mercurio
 * @subpackage Extended Exception classes
 */
class SystemRequired extends \Mercurio\Exception\Model {
    /**
     * @param string $method Method name
     * @param string $property Property name
     * @param string $array Array name
     */
    public function __construct(string $method, array $array, string $property = '') {
        $array = func_get_arg(2);
        $message = "Class method <strong>$method</strong> expects a <strong>$property</strong> array key in $array";
        return parent::__construct($message);
    }
}