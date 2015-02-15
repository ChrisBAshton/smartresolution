<?php

class Database {

    private static $db = false;
    private static $environment = 'test'; // default value, use setEnvironment to override

    public static function setEnvironment($env) {
        Database::$db = false;
        Database::$environment = $env;
    }

    public static function instance() {
        if(!Database::$db) {
            Database::$db = new \DB\SQL('sqlite:' . __DIR__ . '/../../data/' . Database::$environment . '.db');
            // enable foreign key constraints (to raise errors if corresponding entry in foreign table does not exist)
            Database::$db->exec('PRAGMA foreign_keys = ON;');
            // we want to raise exceptions that we can catch with PHP
            Database::$db->pdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return Database::$db;
    }

    public static function clear() {
        if (Database::$environment !== 'test') {
            throw new Exception('Tried to clear a non-test database! Run Database::setEnvironment("test") before trying to clear.');
        }
        else {
            $pathToDatabases = __DIR__ . "/../../data";
            shell_exec("rm " . $pathToDatabases . "/test.db && sqlite3 " . $pathToDatabases . "/test.db < " . $pathToDatabases . "/db.sql && php " . $pathToDatabases . "/fixtures/seed.php");
        }
    }
}