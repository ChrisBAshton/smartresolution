<?php

class DBGet extends Prefab {

    public function dispute($disputeID) {
        $dispute = $this->getRowById('disputes', 'dispute_id', $disputeID);

        if (!$dispute) {
            throw new Exception("The dispute you are trying to view does not exist.");
        }

        return $dispute;
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

    private function getRowById($tableName, $idName, $id) {
        $rows = Database::instance()->exec(
            'SELECT * FROM ' . $tableName . ' WHERE ' . $idName . ' = :' . $idName,
            array(
                ':' . $idName => $id
            )
        );
        return (count($rows) === 1) ? $rows[0] : false;
    }

}