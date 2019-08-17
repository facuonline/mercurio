<?php
/**
 * @package Mercurio
 * @subpackage Extended Exceptions classes
 * 
 * These types of exceptions are triggered on third party developers failure
 * e.g When not specifying a string type key for an array index
 */
namespace Mercurio\Exception\Usage;
class StringKeysRequired extends \Mercurio\Exception\Model{
    public function __construct(string $method) {
        $message = "Class method <strong>'$method'</strong> requires an associative array of all string keys.";
        return parent::__construct($message);
    }
}