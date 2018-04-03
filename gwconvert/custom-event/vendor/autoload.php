<?php

spl_autoload_register(function ($class_name) {
    require __dir__ . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $class_name)
        . '.php';
});

?>
