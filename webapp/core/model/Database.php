<?php

/**
 * The database class provides a handle on the database. It is the database driver. If the application is ever to switch to another type of database, e.g. MySQL, it is this class that would need to change.
 */
class Database {

    private static $db = false;
    private static $environment = 'test'; // default value, use setEnvironment to override

    /**
     * Sets the database environment. This allows the application to have multiple environments, e.g. int, test, stage, live.
     * @param String $env The environment to use. We've only been working with 'test' and 'production', but this could be expanded quite easily.
     */
    public static function setEnvironment($env) {
        Database::$db = false;
        Database::$environment = $env;
    }

    /**
     * Returns the PDO wrapper on the database instance, allowing the execution of arbitrary SQL from within the application, e.g. Database::instance()->exec('SELECT * FROM ...');
     * @return Object The handle on the database.
     */
    public static function instance() {
        if(!Database::$db) {
            Database::$db = new \DB\SQL('sqlite:' . __DIR__ . '/../../../data/' . Database::$environment . '.db');
            // enable foreign key constraints
            // (to raise errors if corresponding entry in foreign table does not exist)
            Database::$db->exec('PRAGMA foreign_keys = ON;');
            // we want to raise exceptions that we can catch with PHP
            Database::$db->pdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return Database::$db;
    }

    /**
     * Clears the database. Raises an exception if this is called on a non-test database.
     */
    public static function clear() {
        if (Database::$environment !== 'test') {
            throw new Exception('Tried to clear a non-test database! Run Database::setEnvironment("test") before trying to clear.');
        }
        else {
            $pathToDatabases = __DIR__ . "/../../../data";
            shell_exec("rm " . $pathToDatabases . "/test.db && sqlite3 " . $pathToDatabases . "/test.db < " . $pathToDatabases . "/db.sql && php " . $pathToDatabases . "/fixtures/seed.php");
        }
    }
}