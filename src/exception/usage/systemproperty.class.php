<?php
/**
 * @package Mercurio
 * @subpackage Extended Exception classes
 * 
 * These types of exceptions are triggered on third party developers failure
 * e.g When trying to modify a system set property in the database
 * 
 * @param string $property Property name
 */
namespace Exception\Usage;
use Exception;
class SystemProperty extends Exception {
    public function __construct(string $property) {
        $message = "Property <strong>'$property'</strong> can't be manipulated by other than the system.";
        return parent::__construct($message);
    }
}