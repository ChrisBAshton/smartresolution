<?php

/**
 * This class defines methods which retrieve database records by ID, according to the type of the object.
 */
class DBGet extends Prefab {

    public function dispute($disputeID) {
        $details = $this->getRowById('disputes', 'dispute_id', $disputeID, "The dispute you are trying to view does not exist.");
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['party_a']);
        $this->convertToInt($details['party_b']);
        $this->convertToBoolean($details['round_table_communication']);
        return $details;
    }

    public function disputeParty($partyID) {
        return $this->getRowById('dispute_parties', 'party_id', $partyID);
    }

    public function evidence($evidenceID) {
        return $this->getRowById('evidence', 'evidence_id', $evidenceID);
    }

    public function lifespan($lifespanID) {
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
        $details = $this->getRowById('messages', 'message_id', $messageID);
        $this->convertToInt($details['dispute_id']);
        $this->convertToInt($details['author_id']);
        $this->convertToInt($details['recipient_id']);
        $this->convertToInt($details['timestamp']);
        return $details;
    }

    public function notification($notificationID) {
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
                ':' . $idName => $id
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