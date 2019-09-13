<?php
namespace Mercurio\Exception;
use Exception;
/**
 * @package Mercurio
 * @subpackage Extended Exception classes
 * 
 * Base model exception
 */
class Model extends Exception {

    /**
     * Set and override Exception message
     * @param string $message New exception message
     */
    public function setMessage(string $message) {
        $this->message = $message;
    }

}