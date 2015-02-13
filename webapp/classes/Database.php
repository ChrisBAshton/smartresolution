<?php

class Database {

    private static $environment = 'production'; // production by default

    public static function setEnvironment($env) {
        Database::$environment = $env;
    }

    public static function instance() {
        $db = new \DB\SQL('sqlite:' . __DIR__ . '/../../data/' . Database::$environment . '.db');
        return $db;
    }
}