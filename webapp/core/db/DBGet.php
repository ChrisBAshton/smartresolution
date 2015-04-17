<?php

/**
 * This class defines methods which retrieve database records by ID, according to the type of the object.
 */
class DBGet extends Prefab {

    public function dispute($disputeID) {
        return $this->getRowById('disputes', 'dispute_id', $disputeID, "The dispute you are trying to view does not exist.");
    }

    public function disputeParty($partyID) {
        return $this->getRowById('dispute_parties', 'party_id', $partyID);
    }

    public function evidence($evidenceID) {
        return $this->getRowById('evidence', 'evidence_id', $evidenceID);
    }

    public function lifespan($lifespanID) {
        return $this->getRowById('lifespans', 'lifespan_id', $lifespanID);
    }

    public function message($messageID) {
        return $this->getRowById('messages', 'message_id', $messageID);
    }

    public function notification($notificationID) {
        return $this->getRowById('notifications', 'notification_id', $notificationID);
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

}