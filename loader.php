<?php

spl_autoload_register(
    function($path) {

        require_once __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', '/', $path) . '.php';

    }
);