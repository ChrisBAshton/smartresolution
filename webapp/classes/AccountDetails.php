<?php
require_once __DIR__ . '/autoload.php';

class AccountDetails {

    /**
     * Stores account details in the database.
     * 
     * @param  Array $object An array of registration values, including email and password.
     * @return int           The login ID associated with the newly registered account.
     */
    public static function register($object) {
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
     * @TODO - instantiate specific types
     * 
     * Returns an object that implements the AccountInterface interface, populating it with data retrieved from the database that corresponds to the given email. Calls getDetailsByEmail internally.
     * 
     * @param  String $email The email address.
     * @return Object        Either an Organisation or Individual type object, or one of their subclasses.
     */
    public static function getAccountFromDatabase($email) {
        $account = AccountDetails::getDetailsByEmail($email);

        if (!$account) {
            return false;
        }
        switch($account['type']) {
            case "mediator":
            case "agent":
                return new Individual($account);
            case "law_firm":
            case "mediation_centre":
                return new Organisation($account);
            default:
                var_dump($account);
                throw new Exception("Invalid account type.");
        }
    }

    /**
     * Returns an array of details corresponding to the account's email address. Not to be confused with getAccountFromDatabase, which uses the details to instantiate and return a PHP object.
     * @param  String $email
     * @return Array
     */
    private static function getDetailsByEmail($email) {
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

    /**
     * Given an email, returns the login ID of the account.
     * @param  String $email
     * @return int
     */
    public static function emailToId($email) {
        // it may be tempting to move this to getDetailsByEmail() but we often call emailToId BEFORE
        // adding a corresponding entry to individuals or organisations, so this should be left untouched.
        $login_id = Database::instance()->exec('SELECT login_id FROM account_details WHERE email = :email LIMIT 1', array(
            ':email' => $email
        ));
        if (!$login_id) {
            return false;
        }
        $login_id = (int) $login_id[0]['login_id'];
        return $login_id;
    }

    /**
     * Returns true or false depending on whether or not the provided email and password combination match an account in the database.
     * 
     * @param  String $email    The email address.
     * @param  String $password The unencrypted password.
     * @return boolean          True if the credentials are valid, otherwise false.
     */
    public static function validCredentials($email, $password) {
        $details = AccountDetails::getDetailsByEmail($email);
        if (!$details) {
            return false;
        }
        else {
            return AccountDetails::correctPassword($password, $details['password']);
        }
    }

    /**
     * Returns true or false depending on whether or not the inputted password is a match for the encrypted password we have on file.
     * 
     * @param  String $inputtedPassword  The unencrypted password.
     * @param  String $encryptedPassword The encrypted password we're checking our unencrypted password against.
     * @return boolean                   True if the inputted password is a match.
     */
    public static function correctPassword($inputtedPassword, $encryptedPassword) {
        $crypt = \Bcrypt::instance();
        return $crypt->verify($inputtedPassword, $encryptedPassword);
    }
}