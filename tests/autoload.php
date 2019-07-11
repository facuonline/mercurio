<?php

spl_autoload_register(function ($class) {
    $classname = strtolower(str_replace('Mercurio', '', $class));
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
    require '../src'.DIRECTORY_SEPARATOR.$file.'.class.php';
});