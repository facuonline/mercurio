<?php
/**
 * @package Mercurio
 * @subpackage Extended Exception classes
 * 
 * These types of exceptions are triggered on user failures
 * e.g When an important form field is missing
 */
namespace Exception\User;
use Exception;
class EmptyField extends Exception {
    
}