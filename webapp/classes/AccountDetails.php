<?php
require_once __DIR__ . '/autoload.php';

class AccountDetails {

    public static function register($object, $db) {
        if (!$object['email'] || !$object['password']) {
            throw new Exception("The minimum required to register is an email and password!");
        }

        if (AccountDetails::getAccountFromDatabase($object['email'])) {
            throw new Exception("An account is already registered to that email address.");
        }

        if (!$db) {
            throw new Exception("Programmer error: need to pass db in the subclass.");
        }
        
        $crypt = \Bcrypt::instance();
        $db->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
            ':email'    => $object['email'],
            ':password' => $crypt->hash($object['password'])
        ));
        
        $login_id = $db->exec('SELECT login_id FROM account_details WHERE email = :email LIMIT 1', array(
            ':email' => $object['email']
        ));
        if (!$login_id) {
            throw new Exception("Could not retrieve login_id. Abort.");
        }
        
        $login_id = $login_id[0]['login_id'];
        return $login_id;
    }

    public static function getAccountFromDatabase($email) {
        $db = Database::instance();

        $accounts = $db->exec(
            'SELECT * FROM account_details WHERE email = :email',
            array(
                ':email' => $email
            )
        );

        if (count($accounts) === 0) {
            return false;
        }
        else {
            return $accounts[0];
        }
    }

    public static function correctPassword($inputtedPassword, $encryptedPassword) {
        $crypt = \Bcrypt::instance();
        return $crypt->verify($inputtedPassword, $encryptedPassword);
    }
}