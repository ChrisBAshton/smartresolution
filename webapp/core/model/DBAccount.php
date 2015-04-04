<?php

/**
 * This class is used as the middle layer between the application and the database, in terms of account interaction.
 */
class DBAccount {

    /**
     * Gets all of the disputes associated with an account. For Agents, this would be disputes that they have created or been assigned to. For mediators, this would be disputes that are in mediation and that they are assigned to, and so on.
     * @param  Individual|Organisation $account The account to get the disputes from.
     * @return Array<Dispute>                   The array of associated disputes.
     */
    public static function getAllDisputes ($account) {
        $disputes = array();

        if ($account instanceof LawFirm || $account instanceof Agent) {
            $disputesDetails = Database::instance()->exec(
                'SELECT dispute_id FROM disputes

                INNER JOIN dispute_parties
                ON disputes.party_a     = dispute_parties.party_id
                OR disputes.party_b     = dispute_parties.party_id

                WHERE organisation_id = :login_id OR individual_id = :login_id
                ORDER BY party_id DESC',
                array(':login_id' => $account->getLoginId())
            );
        }
        else {
            $disputesDetails = Database::instance()->exec(
                'SELECT dispute_id FROM mediation_offers
                WHERE proposed_id = :login_id
                AND status = "accepted"
                ORDER BY mediation_offer_id DESC',
                array(':login_id' => $account->getLoginId())
            );
        }

        foreach($disputesDetails as $dispute) {
            $disputes[] = new Dispute($dispute['dispute_id']);
        }
        return $disputes;
    }

    /**
     * Gets an account by its login ID.
     * @param  Int $id                  The login ID.
     * @return Individual|Organisation  The account object.
     */
    public static function getAccountById($id) {
        $account = DBAccount::getDetailsById($id);
        return DBAccount::arrayToObject($account);
    }

    /**
     * Gets an account by its email address.
     * @param  String $email            The account email address.
     * @return Individual|Organisation  The account object.
     */
    public static function getAccountByEmail($email) {
        $account = DBAccount::getDetailsByEmail($email);
        return DBAccount::arrayToObject($account);
    }

    /**
     * @TODO  - this should be privately referenced by getAccountById/Email, not publicly available.
     * Return the database record corresponding to the provided account ID.
     * @param  Int $value    ID of the account.
     * @return Array<Mixed>  Associated database row.
     */
    public static function getDetailsById($value) {
        return DBAccount::getDetailsBy('login_id', $value);
    }

    /**
     * @TODO  - this should be privately referenced by getAccountById/Email, not publicly available.
     * Return the database record corresponding to the provided account email.
     * @param  String $value Email of the account.
     * @return Array<Mixed>  Associated database row.
     */
    public static function getDetailsByEmail($value) {
        return DBAccount::getDetailsBy('email', $value);
    }

    /**
     * @TODO  - this should be privately referenced by getAccountById/Email, not publicly available.
     * Return the database record corresponding to the provided primary key and value.
     * @param  String  $key   Record to use as the primary key when retrieving the corresponding account information.
     * @param  Unknown $value The value of that primary key.
     * @return Array<Mixed>  Associated database row.
     */
    public static function getDetailsBy($key, $value) {
        $individual   = DBAccount::getRowsFromTable('individuals', $key, $value);
        $organisation = DBAccount::getRowsFromTable('organisations', $key, $value);
        $details      = false;

        if (count($individual) === 1) {
            $details = $individual[0];
        }
        else if (count($organisation) === 1) {
            $details = $organisation[0];
        }

        return $details;
    }

    /**
     * @TODO  - this should be privately referenced by getAccountById/Email, not publicly available.
     * Return the database record corresponding to the provided table name, key and value.
     *
     * @param  String  $table The name of the table to search.
     * @param  String  $key   Record to use as the primary key when retrieving the corresponding account information.
     * @param  Unknown $value The value of that primary key.
     * @return Array<Mixed>  Associated database row.
     */
    public static function getRowsFromTable($table, $key, $value) {
        return Database::instance()->exec(
            'SELECT * FROM account_details INNER JOIN ' . $table . ' ON account_details.login_id = ' . $table . '.login_id WHERE account_details.' . $key . ' = :value',
            array(
                ':value' => $value
            )
        );
    }

    /**
     * Converts an account details (name, email, etc) into an account object of the correct type, e.g. Agent, Law Firm, etc.
     * @param  Array<Mixed> $account    The account details
     * @return Individual|Organisation  The account object.
     */
    public static function arrayToObject($account) {
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
            default:
                var_dump($account);
                throw new Exception("Invalid account type.");
        }
    }

    /**
     * Given an email, returns the login ID of the account.
     *
     * NOTE: It may be tempting to move this to getDetailsByEmail() but we often call emailToId BEFORE
     * adding a corresponding entry to individuals or organisations, so this should be left untouched.
     *
     * @param  String $email
     * @return int
     */
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

    /**
     * Returns true or false depending on whether or not the provided email and password combination match an account in the database.
     *
     * @param  String $email    The email address.
     * @param  String $password The unencrypted password.
     * @return boolean          True if the credentials are valid, otherwise false.
     */
    public static function validCredentials($email, $password) {
        $details = DBAccount::getDetailsByEmail($email);
        if (!$details) {
            return false;
        }
        else {
            return DBAccount::correctPassword($password, $details['password']);
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
