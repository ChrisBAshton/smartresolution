<?php

/**
 * This class defines methods which retrieve database records by ID, according to the type of the object.
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

    public function dispute($disputeID) {
        return new Dispute($this->disputeDetails($disputeID));
    }

    public function disputeParty($partyID) {
        return new DisputeParty($this->disputePartyDetails($partyID));
    }

    public function evidence($evidenceID) {
        return new Evidence($this->evidenceDetails($evidenceID));
    }

    public function lifespan($lifespanID) {
        return new Lifespan($this->lifespanDetails($lifespanID));
    }

    public function message($messageID) {
        return new Message($this->messageDetails($messageID));
    }

    public function notification($notificationID) {
        return new Notification($this->notificationDetails($notificationID));
    }

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

    public function disputeDetails($disputeID) {
        $details = $this->getRowById('disputes', 'dispute_id', $disputeID, "The dispute you are trying to view does not exist.");
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['party_a']);
        $this->convertToInt($details['party_b']);
        $this->convertToBoolean($details['round_table_communication']);
        return $details;
    }

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

    public function evidenceDetails($evidenceID) {
        $details = $this->getRowById('evidence', 'evidence_id', $evidenceID);
        $this->convertToInt($details['evidence_id']);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['uploader_id']);
        return $details;
    }

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

    public function messageDetails($messageID) {
        $details = $this->getRowById('messages', 'message_id', $messageID);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['author_id']);
        $this->convertToInt($details['recipient_id']);
        $this->convertToInt($details['timestamp']);
        return $details;
    }

    public function notificationDetails($notificationID) {
        $details = $this->getRowById('notifications', 'notification_id', $notificationID);
        $this->convertToInt($details['notification_id']);
        $this->convertToInt($details['recipient_id']);
        $this->convertToBoolean($details['read']);
        return $details;
    }

    private function convertToInt(&$element) {
        $element = (int) $element;
    }

    private function convertToBoolean(&$element) {
        $element = !($element === 'false' || $element === '0');
    }

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