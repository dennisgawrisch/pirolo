<?php
spl_autoload_register(function($class) {
    if ("Pirolo\\" == substr($class, 0, 7)) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, substr($class, 7)) . ".php";
    }
});