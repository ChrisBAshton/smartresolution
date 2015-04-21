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

    /**
     * Changes the status of round-table communication in the database.
     */
    public function markRoundTableCommunicationAs($enabledOrDisabled, $disputeID) {
        Database::instance()->exec(
            'UPDATE disputes SET round_table_communication = :bool WHERE dispute_id = :dispute_id',
            array(
                ':dispute_id' => $disputeID,
                ':bool'       => $enabledOrDisabled === 'enabled' ? 'true' : 'false'
            )
        );
    }

    /**
     * Updates a given field in the dispute.
     *
     * @param  string  $key         The field to update.
     * @param  Unknown $value       The value to set it as.
     * @param  int     $disputeID   The ID of the dispute.
     */
    public function updateField($key, $value, $disputeID) {
        Database::instance()->exec('UPDATE disputes SET ' . $key . ' = :new_value WHERE dispute_id = :dispute_id',
            array(
                ':new_value' => $value,
                'dispute_id' => $disputeID
            )
        );
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