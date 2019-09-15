<?php
namespace Mercurio\Exception\Usage;
/**
 * These types of exceptions are triggered on third party developers failure \
 * e.g When not specifying a string type key for an array index
 * @package Mercurio
 * @subpackage Extended Exceptions classes
 */
class StringKeysRequired extends \Mercurio\Exception\Model{
    /**
     * @param string $method Class method name
     */
    public function __construct(string $method) {
        $message = "Class method <strong>'$method'</strong> requires an associative array of all string keys.";
        return parent::__construct($message);
    }
}