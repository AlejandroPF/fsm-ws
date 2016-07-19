<?php

/**
 * Directory Separator
 */
define("DS", DIRECTORY_SEPARATOR);
/**
 * Document Root
 */
define("ROOT", str_replace(DS . DS, DS, __DIR__ . DS));
/**
 * Common directory
 */
define("COMMON", ROOT . "common" . DS);
/*
 * Register class autoloader from COMMON/class directory.
 * PHP file ext.: .php | .class.php
 */
spl_autoload_register(function($class) {
    $CLASS_DIR = COMMON . "class" . DS;
    $CLASS_EXT = ".class.php";
    $CLASS_EXT_2 = ".php";
    $subdir = str_replace("\\", "/", $class);
    $file = $CLASS_DIR . $subdir . $CLASS_EXT;
    $file2 = $CLASS_DIR . $subdir . $CLASS_EXT_2;
    if (is_readable($file)) {
        include_once $file;
    } elseif (is_readable($file2)) {
        include_once $file2;
    }
});
// Add common file
require_once COMMON . "common.php";
// Add JWT autoload
require_once COMMON . "class/jwt/vendor/autoload.php";
// Add Slim autoload
require_once COMMON . "class/slim/vendor/autoload.php";