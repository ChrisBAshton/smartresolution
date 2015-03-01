<?php

class Register {

    public static function organisation($orgObject) {
        $type        = Utils::getValue($orgObject, 'type');
        $name        = Utils::getValue($orgObject, 'name', '');
        $description = Utils::getValue($orgObject, 'description', '');

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
        $type     = Utils::getValue($individualObject, 'type');
        $orgId    = Utils::getValue($individualObject, 'organisation_id');
        $forename = Utils::getValue($individualObject, 'forename', '');
        $surname  = Utils::getValue($individualObject, 'surname',  '');

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

        if (AccountDetails::getAccountByEmail($object['email'])) {
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
}