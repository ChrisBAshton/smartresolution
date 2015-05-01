<?php

/**
 * This class defines methods which retrieve database records by ID, according to the type of the object.
 */
class DBGet extends Prefab {

    public function dispute($disputeID) {
        return new Dispute($this->disputeDetails($disputeID));
    }

    public function disputeDetails($disputeID) {
        $details = $this->getRowById('disputes', 'dispute_id', $disputeID, "The dispute you are trying to view does not exist.");
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['party_a']);
        $this->convertToInt($details['party_b']);
        $this->convertToBoolean($details['round_table_communication']);
        return $details;
    }

    public function disputeParty($partyID) {
        return new DisputeParty($this->disputePartyDetails($partyID));
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

    public function evidence($evidenceID) {
        return new Evidence($this->evidenceDetails($evidenceID));
    }

    public function evidenceDetails($evidenceID) {
        $details = $this->getRowById('evidence', 'evidence_id', $evidenceID);
        $this->convertToInt($details['evidence_id']);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['uploader_id']);
        return $details;
    }

    public function lifespan($lifespanID) {
        return new Lifespan($this->lifespanDetails($lifespanID));
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

    public function message($messageID) {
        return new Message($this->messageDetails($messageID));
    }

    public function messageDetails($messageID) {
        $details = $this->getRowById('messages', 'message_id', $messageID);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['author_id']);
        $this->convertToInt($details['recipient_id']);
        $this->convertToInt($details['timestamp']);
        return $details;
    }

    public function notification($notificationID) {
        return new Notification($this->notificationDetails($notificationID));
    }

    public function notificationDetails($notificationID) {
        $details = $this->getRowById('notifications', 'notification_id', $notificationID);
        $this->convertToInt($details['notification_id']);
        $this->convertToInt($details['recipient_id']);
        $this->convertToBoolean($details['read']);
        return $details;
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

    private function convertToInt(&$element) {
        $element = (int) $element;
    }

    private function convertToBoolean(&$element) {
        $element = !($element === 'false' || $element === '0');
    }

}