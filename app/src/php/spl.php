<?php
/**
 * classes.php
 * @package Mercurio
 * @subpackage Included Classes 
 * 
 * Please remember the principles of OOP when working with this code:
 * *Classes should be working pieces by themselves and not depend on other functions or classes (encapsulation)
 * *Classes serve and encapsulate even high level tools with easy approaches (abstraction)
 * *Classes can also be the root for other classes and new methods (inheritance)
 * *Them and their inheritance can and should maintain their basic ideas even if acting differently (polymorphism)
 *
 */
spl_autoload_register(function ($class) {
    include 'classes/'.$class.'.class.php';
});