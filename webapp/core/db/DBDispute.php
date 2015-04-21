<?php

/**
 * This class provides the database middle layer between disputes and the database.
 */
class DBDispute extends Prefab {

    public function getEvidences($disputeID) {
        $evidences = array();
        $evidenceDetails = Database::instance()->exec(
            'SELECT evidence_id FROM evidence WHERE dispute_id = :dispute_id ORDER BY evidence_id DESC',
            array(':dispute_id' => $disputeID)
        );

        foreach($evidenceDetails as $evidence) {
            $evidences[] = new Evidence((int) $evidence['evidence_id']);
        }

        return $evidences;
    }

    public function updateDisputePartyB($partyID, $disputeID) {
        Database::instance()->exec(
            'UPDATE disputes SET party_b = :party_id WHERE dispute_id = :dispute_id',
            array(
                ':party_id'   => $partyID,
                ':dispute_id' => $disputeID
            )
        );
    }

    public function updatePartyRecord($partyID, $field, $value) {
        Database::instance()->exec(
            'UPDATE dispute_parties SET ' . $field . ' = :value WHERE party_id = :party_id',
            array(
                ':value'    => $value,
                ':party_id' => $partyID
            )
        );
    }

}