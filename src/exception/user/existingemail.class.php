<?php
/**
 * @package Mercurio
 * @subpackage Extended Exception classes
 * 
 * These types of exceptions are triggered on user failures
 * e.g When an email to be submitted already exists
 */
namespace Exception\User;
use Exception;
class ExistingEmail extends Exception {
    
}