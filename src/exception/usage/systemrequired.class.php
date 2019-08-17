<?php
/**
 * @package Mercurio
 * @subpackage Extended Exception classes
 * 
 * These types of exceptions are triggered on third party developers failure
 * e.g When not providing a necessary array element
 * 
 * @param string $method Method name
 * @param string $property Property name
 * @param string $array Array name
 */
namespace Mercurio\Exception\Usage;
class SystemRequired extends \Mercurio\Exception\Model {
    public function __construct(string $method, string $array, string $property = '') {
        $message = "Class method <strong>$method</strong> expects a <strong>$property</strong> array key in $array";
        return parent::__construct($message);
    }
}