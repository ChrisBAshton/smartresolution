<?php

/**
 * Defines methods which retrieve database records by ID, according to the type of the object.
 */
class DBGet extends Prefab {

    /**
     * Gets an account by its login ID.
     * @param  int $loginID  The login ID.
     * @return Account       The account object.
     */
    public function account($loginID) {
        $account = $this->accountDetails($loginID);
        return $this->arrayToAccountObject($account);
    }

    /**
     * Gets a dispute by its ID.
     * @param  int $disputeID
     * @return Dispute
     */
    public function dispute($disputeID) {
        return new Dispute($this->disputeDetails($disputeID));
    }

    /**
     * Gets a dispute party by its ID.
     * @param  int $partyID
     * @return DisputeParty
     */
    public function disputeParty($partyID) {
        return new DisputeParty($this->disputePartyDetails($partyID));
    }

    /**
     * Gets an Evidence object by its ID.
     * @param  int $evidenceID
     * @return Evidence
     */
    public function evidence($evidenceID) {
        return new Evidence($this->evidenceDetails($evidenceID));
    }

    /**
     * Gets a Lifespan object by its ID.
     * @param  int $lifespanID
     * @return Lifespan
     */
    public function lifespan($lifespanID) {
        return new Lifespan($this->lifespanDetails($lifespanID));
    }

    /**
     * Gets a Message object by its ID.
     * @param  int $messageID
     * @return Message
     */
    public function message($messageID) {
        return new Message($this->messageDetails($messageID));
    }

    /**
     * Gets a Notification object by its ID.
     * @param  int $notificationID
     * @return Notification
     */
    public function notification($notificationID) {
        return new Notification($this->notificationDetails($notificationID));
    }

    /**
     * Gets an array of account details from the database, according to the login ID.
     * @param  int $loginID
     * @return array
     */
    public function accountDetails($loginID) {
        $individual    = $this->getAccountRowByLoginId('individuals',    $loginID);
        $organisation  = $this->getAccountRowByLoginId('organisations',  $loginID);
        $administrator = $this->getAccountRowByLoginId('administrators', $loginID);
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
            $this->convertToInt($details['login_id']);
            $this->convertToBoolean($details['verified']);
        }

        return $details;
    }

    /**
     * Gets an array of dispute details from the database, according to its ID.
     * @param  int $disputeID
     * @return array
     */
    public function disputeDetails($disputeID) {
        $details = $this->getRowById('disputes', 'dispute_id', $disputeID, "The dispute you are trying to view does not exist.");
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['party_a']);
        $this->convertToInt($details['party_b']);
        $this->convertToBoolean($details['round_table_communication']);
        return $details;
    }

    /**
     * Gets an array of dispute party details from the database, according to its ID.
     * @param  int $partyID
     * @return array
     */
    public function disputePartyDetails($partyID) {
        $details = $this->getRowById('dispute_parties', 'party_id', $partyID);

        if ($details) {
            $this->convertToInt($details['party_id']);
            $this->convertToInt($details['organisation_id']);
            $this->convertToInt($details['individual_id']);
        }
        else {
            $details = array();
        }

        return $details;
    }

    /**
     * Gets an array of evidence details from the database, according to its ID.
     * @param  int $evidenceID
     * @return array
     */
    public function evidenceDetails($evidenceID) {
        $details = $this->getRowById('evidence', 'evidence_id', $evidenceID);
        $this->convertToInt($details['evidence_id']);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['uploader_id']);
        return $details;
    }

    /**
     * Gets an array of lifespan details from the database, according to its ID.
     * @param  int $lifespanID
     * @return array
     */
    public function lifespanDetails($lifespanID) {
        $details = $this->getRowById('lifespans', 'lifespan_id', $lifespanID);
        $this->convertToInt($details['lifespan_id']);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['proposer']);
        $this->convertToInt($details['valid_until']);
        $this->convertToInt($details['start_time']);
        $this->convertToInt($details['end_time']);
        return $details;
    }

    /**
     * Gets an array of message details from the database, according to its ID.
     * @param  int $messageID
     * @return array
     */
    public function messageDetails($messageID) {
        $details = $this->getRowById('messages', 'message_id', $messageID);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['author_id']);
        $this->convertToInt($details['recipient_id']);
        $this->convertToInt($details['timestamp']);
        return $details;
    }

    /**
     * Gets an array of notification details from the database, according to its ID.
     * @param  int $notificationID
     * @return array
     */
    public function notificationDetails($notificationID) {
        $details = $this->getRowById('notifications', 'notification_id', $notificationID);
        $this->convertToInt($details['notification_id']);
        $this->convertToInt($details['recipient_id']);
        $this->convertToBoolean($details['read']);
        return $details;
    }

    /**
     * Converts the given element to an integer. This is done by reference, so the passed variable is directly modified.
     * @param  mixed &$element Variable to cast.
     */
    private function convertToInt(&$element) {
        $element = (int) $element;
    }

    /**
     * Converts the given element to a boolean. This is done by reference, so the passed variable is directly modified.
     * @param  mixed &$element Variable to cast.
     */
    private function convertToBoolean(&$element) {
        $element = !($element === 'false' || $element === '0');
    }

    /**
     * Retrieves a row of data from the database according to the table and column name and the provided primary key.
     * @param  string  $tableName        Name of the table.
     * @param  string  $idName           Name of the primary key column.
     * @param  id      $id               ID of the primary key.
     * @param  string  $exceptionMessage (Optional) An exception message to be raised if the row cannot be found.
     * @return array   The row of data.
     */
    private function getRowById($tableName, $idName, $id, $exceptionMessage = false) {
        $rows = Database::instance()->exec(
            'SELECT * FROM ' . $tableName . ' WHERE ' . $idName . ' = :' . $idName,
            array(
                ':' . $idName => (int) $id
            )
        );

        if (count($rows) !== 1) {
            if ($exceptionMessage) {
                Utils::instance()->throwException($exceptionMessage);
            }
            return false;
        }

        return $rows[0];
    }

    /**
     * More specialised than getRowById in that it joins the account_details table with the individuals or organisations table.
     * @param  string $table   Table to retrieve account details from.
     * @param  int $loginID    Login ID of the account.
     * @return array           The row of data.
     */
    private function getAccountRowByLoginId($table, $loginID) {
        return Database::instance()->exec(
            'SELECT * FROM account_details INNER JOIN ' . $table . ' ON account_details.login_id = ' . $table . '.login_id WHERE account_details.login_id = :loginID',
            array(
                ':loginID' => $loginID
            )
        );
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
}