<?php
/**
 * @package Mercurio
 * @subpackage Extended Exceptions classes
 * 
 * These types of exceptions are triggered on third party developers failure
 * e.g When not calling User->get before using another method
 */
namespace Mercurio\Exception\Usage;
use Exception;
class UserNotLoaded extends Exception{
    public function __construct() {
        $message = "Class method can only be called on instances loaded with an existing user data. Use method <strong>get</strong> to load an user into instance.";
        return parent::__construct($message);
    }
}