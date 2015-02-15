<?php
require_once __DIR__ . '/autoload.php';

class AccountDetails {

    public static function register($object) {
        if (!$object['email'] || !$object['password']) {
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

    public static function validCredentials($email, $password) {
        $account = AccountDetails::getAccountFromDatabase($email);
        if (!$account) {
            return false;
        }
        else {
            return AccountDetails::correctPassword($password, $account['password']);
        }
    }

    public static function emailToId($email) {
        $login_id = Database::instance()->exec('SELECT login_id FROM account_details WHERE email = :email LIMIT 1', array(
            ':email' => $email
        ));
        if (!$login_id) {
            return false;
        }
        $login_id = (int) $login_id[0]['login_id'];
        return $login_id;
    }

    // @TODO - test getting different account types
    public static function getAccountFromDatabase($email) {
        $individual = Database::instance()->exec(
            'SELECT * FROM account_details INNER JOIN individuals ON account_details.login_id = individuals.login_id WHERE email = :email',
            array(
                ':email' => $email
            )
        );
        $organisation = Database::instance()->exec(
            'SELECT * FROM account_details INNER JOIN organisations ON account_details.login_id = organisations.login_id WHERE email = :email',
            array(
                ':email' => $email
            )
        );

        if (count($individual) === 1) {
            return $individual[0];
        }
        else if (count($organisation) === 1) {
            return $organisation[0];
        }
        else {
            return false;
        }
    }

    public static function correctPassword($inputtedPassword, $encryptedPassword) {
        $crypt = \Bcrypt::instance();
        return $crypt->verify($inputtedPassword, $encryptedPassword);
    }
}