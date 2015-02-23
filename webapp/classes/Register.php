<?php
require_once __DIR__ . '/autoload.php';

class Register {

    public static function organisation($orgObject) {
        $type        = Register::getValue($orgObject, 'type');
        $name        = Register::getValue($orgObject, 'name', '');
        $description = Register::getValue($orgObject, 'description', '');

        Database::instance()->begin();
        $login_id = Register::accountDetails($orgObject);
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
        $orgId    = Register::getValue($individualObject, 'organisation_id');
        $forename = Register::getValue($individualObject, 'forename', '');
        $surname  = Register::getValue($individualObject, 'surname',  '');

        Database::instance()->begin();
        $login_id = Register::accountDetails($individualObject);
        Database::instance()->exec('INSERT INTO individuals (login_id, organisation_id, type, forename, surname) VALUES (:login_id, :organisation_id, :type, :forename, :surname)', array(
            ':login_id'        => $login_id,
            ':organisation_id' => $orgId,
            ':type'            => $type,
            ':forename'        => $forename,
            ':surname'         => $surname
        ));
        Database::instance()->commit();
    }

    /**
     * Stores account details in the database.
     * 
     * @param  Array $object An array of registration values, including email and password.
     * @return int           The login ID associated with the newly registered account.
     */
    public static function accountDetails($object) {
        if (!isset($object['email']) || !isset($object['password'])) {
            throw new Exception("The minimum required to register is an email and password!");
        }

        if (AccountDetails::getAccountFromDatabase($object['email'])) {
            throw new Exception("An account is already registered to that email address.");
        }
        
        $crypt = \Bcrypt::instance();
        Database::instance()->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
            ':email'    => $object['email'],
            ':password' => $crypt->hash($object['password'])
        ));
        
        $login_id = AccountDetails::emailToId($object['email']);
        if (!$login_id) {
            throw new Exception("Could not retrieve login_id. Abort.");
        }
        return $login_id;
    }

    /**
     * Gets the value of the given key from the given array, defaulting to the given default value if no value exists. If no default is provided and no value exists, an exception is raised.
     *
     * @TODO  - move to a helper module, as this is called from Dispute.php too.
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