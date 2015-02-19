<?php
require_once __DIR__ . '/autoload.php';

class Register {

    public static function organisation($orgObject) {
        $type        = Register::getValue($orgObject, 'type');
        $name        = Register::getValue($orgObject, 'name', '');
        $description = Register::getValue($orgObject, 'description', '');

        Database::instance()->begin();
        $login_id = AccountDetails::register($orgObject);
        Database::instance()->exec('INSERT INTO organisations (login_id, type, name, description) VALUES (:login_id, :type, :name, :description)', array(
            ':login_id'    => $login_id,
            ':type'        => $type,
            ':name'        => $name,
            ':description' => $description
        ));
        Database::instance()->commit();
    }

    public static function individual($individualObject) {
        $type     = Register::getValue($individualObject, 'type');
        $forename = Register::getValue($individualObject, 'forename', 'Forename');
        $surname  = Register::getValue($individualObject, 'surname',  'Surname');

        Database::instance()->begin();
        $login_id = AccountDetails::register($individualObject);
        Database::instance()->exec('INSERT INTO individuals (login_id, type, forename, surname) VALUES (:login_id, :type, :forename, :surname)', array(
            ':login_id' => $login_id,
            ':type'     => $type,
            ':forename' => $forename,
            ':surname'  => $surname
        ));
        Database::instance()->commit();
    }

    /**
     * Gets the value of the given key from the given array, defaulting to the given default value if no value exists. If no default is provided and no value exists, an exception is raised.
     *
     * Example:
     *     $arr = array('foo' => 'bar');
     *     $val = getValue($arr, 'foo');        // $val === 'bar'
     *     $val = getValue($arr, 'abc', 'def'); // $val === 'def'
     *     $val = getValue($arr, 'abc');        // Exception raised
     * 
     * @param  Array  $array   The array to search in.
     * @param  String $key     The key whose value we want to find.
     * @param  String $default (Optional) - the default value if no value is found.
     * @return Object          Returns the found value, the default value, or raises an exception.
     */
    public static function getValue($array, $key, $default = NULL) {
        if (!isset($default)) {
            if (!isset($array[$key])) {
                throw new Exception ($key . ' is a required index!');
            }
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }
}