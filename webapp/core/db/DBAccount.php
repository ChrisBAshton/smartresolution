<?php

class DBAccount extends Prefab {

    // @TODO - move these two functions to Account.php
    /**
     * Returns true or false depending on whether or not the provided email and password combination match an account in the database.
     *
     * @param  string $email    The email address.
     * @param  string $password The unencrypted password.
     * @return boolean          True if the credentials are valid, otherwise false.
     */
    public function validCredentials($email, $password) {
        $details = $this->getDetailsBy('email', $email);
        if (!$details) {
            return false;
        }
        else {
            return $this->correctPassword($password, $details['password']);
        }
    }

    /**
     * Returns true or false depending on whether or not the inputted password is a match for the encrypted password we have on file.
     *
     * @param  string $inputtedPassword  The unencrypted password.
     * @param  string $encryptedPassword The encrypted password we're checking our unencrypted password against.
     * @return boolean                   True if the inputted password is a match.
     */
    public function correctPassword($inputtedPassword, $encryptedPassword) {
        $crypt = \Bcrypt::instance();
        return $crypt->verify($inputtedPassword, $encryptedPassword);
    }










    /**
     * Gets an account by its login ID.
     * @param  int $id                  The login ID.
     * @return Account  The account object.
     */
    public function getAccountById($id) {
        $account = $this->getDetailsBy('login_id', $id);
        return $this->arrayToAccountObject($account);
    }

    /**
     * Gets an account by its email address.
     * @param  string $email            The account email address.
     * @return Account  The account object.
     */
    public function getAccountByEmail($email) {
        $account = $this->getDetailsBy('email', $email);
        return $this->arrayToAccountObject($account);
    }


    /**
     * Converts an account details (name, email, etc) into an account object of the correct type, e.g. Agent, Law Firm, etc.
     * @param  array<Mixed> $account    The account details
     * @return Account  The account object.
     */
    private function arrayToAccountObject($account) {
        if (!$account) {
            return false;
        }

        switch($account['type']) {
            case "mediator":
                return new Mediator($account);
            case "agent":
                return new Agent($account);
            case "law_firm":
                return new LawFirm($account);
            case "mediation_centre":
                return new MediationCentre($account);
            case "administrator":
                return new Admin($account);
            default:
                Utils::instance()->throwException("Invalid account type.");
        }
    }

    /**
     * Given an email, returns the login ID of the account.
     *
     * NOTE: It may be tempting to move this to getDetailsByEmail() but we often call emailToId BEFORE
     * adding a corresponding entry to individuals or organisations, so this should be left untouched.
     *
     * @param  string $email
     * @return int
     */
    public function emailToId($email) {
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
     * Returns the database record corresponding to the provided primary key and value.
     * @param  string  $key   Record to use as the primary key when retrieving the corresponding account information.
     * @param  Unknown $value The value of that primary key.
     * @return array<Mixed>  Associated database row.
     */
    private function getDetailsBy($key, $value) {
        $individual    = $this->getRowsFromTable('individuals', $key, $value);
        $organisation  = $this->getRowsFromTable('organisations', $key, $value);
        $administrator = $this->getRowsFromTable('administrators', $key, $value);
        $details       = false;

        if (count($individual) === 1) {
            $details = $individual[0];
        }
        else if (count($organisation) === 1) {
            $details = $organisation[0];
        }
        else if (count($administrator) === 1) {
            $details = $administrator[0];
            $details['type'] = 'administrator';
        }

        if ($details) {
            $details['login_id'] = (int) $details['login_id'];
        }

        return $details;
    }

    /**
     * Return the database record corresponding to the provided table name, key and value.
     *
     * @param  string  $table The name of the table to search.
     * @param  string  $key   Record to use as the primary key when retrieving the corresponding account information.
     * @param  Unknown $value The value of that primary key.
     * @return array<Mixed>  Associated database row.
     */
    private function getRowsFromTable($table, $key, $value) {
        return Database::instance()->exec(
            'SELECT * FROM account_details INNER JOIN ' . $table . ' ON account_details.login_id = ' . $table . '.login_id WHERE account_details.' . $key . ' = :value',
            array(
                ':value' => $value
            )
        );
    }
}