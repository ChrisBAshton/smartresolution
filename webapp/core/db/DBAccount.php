<?php

/**
 * This class is used as the middle layer between the application and the database, in terms of account interaction.
 */
class DBAccount extends Prefab {

    public function setAccountProperty($loginID, $key, $value) {
        $account = $this->getAccountById($loginID);
        $table   = ($account instanceof Individual) ? 'individuals' : 'organisations';
        Database::instance()->exec(
            'UPDATE ' . $table . ' SET ' . $key . ' = :value WHERE login_id = :uid',
            array(
                ':value' => $value,
                ':uid'   => $loginID
            )
        );
    }

    /**
     * Gets organisations as an array.
     * @param  array  $params           Parameters:
     *         string $params['type']   Organisation type ('law_firm' / 'mediation_centre')
     *         int    $params['except'] Integer ID of an account to remove from the results.
     * @return array<Organisation>      An array of matching organisations of the correct subclass type (LawFirm or MediationCentre)
     */
    public function getOrganisations($params) {
        $type   = Utils::instance()->getValue($params, 'type');
        $class  = $type === 'law_firm' ? 'LawFirm' : 'MediationCentre';
        $except = Utils::instance()->getValue($params, 'except', false);

        $organisations = array();
        $orgDetails = Database::instance()->exec(
            'SELECT * FROM organisations INNER JOIN account_details ON organisations.login_id = account_details.login_id  WHERE type = :type AND organisations.login_id != :except ORDER BY name DESC',
            array(
                ':type'   => $type,
                ':except' => $except
            )
        );

        foreach($orgDetails as $details) {
            $organisations[] = new $class($details);
        }

        return $organisations;
    }

    /**
     * Gets all of the disputes associated with an account. For Agents, this would be disputes that they have created or been assigned to. For mediators, this would be disputes that are in mediation and that they are assigned to, and so on.
     * @param  Account $account The account to get the disputes from.
     * @return array<Dispute>                   The array of associated disputes.
     */
    public function getAllDisputes ($account) {
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
     * @param  int $id                  The login ID.
     * @return Account  The account object.
     */
    public function getAccountById($id) {
        $account = $this->getDetailsById($id);
        return $this->arrayToAccountObject($account);
    }

    /**
     * Gets an account by its email address.
     * @param  string $email            The account email address.
     * @return Account  The account object.
     */
    public function getAccountByEmail($email) {
        $account = $this->getDetailsByEmail($email);
        return $this->arrayToAccountObject($account);
    }


    /**
     * Converts an account details (name, email, etc) into an account object of the correct type, e.g. Agent, Law Firm, etc.
     * @param  array<Mixed> $account    The account details
     * @return Account  The account object.
     */
    public function arrayToAccountObject($account) {
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
     * Returns true or false depending on whether or not the provided email and password combination match an account in the database.
     *
     * @param  string $email    The email address.
     * @param  string $password The unencrypted password.
     * @return boolean          True if the credentials are valid, otherwise false.
     */
    public function validCredentials($email, $password) {
        $details = $this->getDetailsByEmail($email);
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
     * Returns the database record corresponding to the provided account ID.
     * @param  int $value    ID of the account.
     * @return array<Mixed>  Associated database row.
     */
    public function getDetailsById($value) {
        return $this->getDetailsBy('login_id', $value);
    }

    /**
     * Returns the database record corresponding to the provided account email.
     * @param  string $value Email of the account.
     * @return array<Mixed>  Associated database row.
     */
    private function getDetailsByEmail($value) {
        return $this->getDetailsBy('email', $value);
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