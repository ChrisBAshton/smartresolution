<?php

/**
 * This class provides the database middle layer between disputes and the database.
 */
class DBDispute extends Prefab {

    /**
     * Constructor.
     * @param int $disputeID The dispute ID.
     */
    function __construct($disputeID) {
        $this->disputeID = $disputeID;
    }

    /**
     * Persistently marks Round-Table Communication as enabled.
     */
    public function enableRoundTableCommunication() {
        $this->markRoundTableCommunicationAs('enabled');
    }

    /**
     * Persistently marks Round-Table Communication as enabled.
     */
    public function disableRoundTableCommunication() {
        $this->markRoundTableCommunicationAs('disabled');
    }

    /**
     * Private function used internally by (enable|disable)RoundTableCommunication.
     * Changes the status of round-table communication in the database.
     */
    private function markRoundTableCommunicationAs($enabledOrDisabled) {
        Database::instance()->exec(
            'UPDATE disputes SET round_table_communication = :bool WHERE dispute_id = :dispute_id',
            array(
                ':dispute_id' => $this->disputeID,
                ':bool'       => $enabledOrDisabled === 'enabled' ? 'true' : 'false'
            )
        );
    }

    /**
     * Updates a given field in the dispute.
     *
     * @param  string  $key   The field to update.
     * @param  Unknown $value The value to set it as.
     */
    public function updateField($key, $value) {
        Database::instance()->exec('UPDATE disputes SET ' . $key . ' = :new_value WHERE dispute_id = :dispute_id',
            array(
                ':new_value' => $value,
                'dispute_id' => $this->disputeID
            )
        );
    }

    public static function updateDisputePartyB($partyID, $disputeID) {
        Database::instance()->exec(
            'UPDATE disputes SET party_b = :party_id WHERE dispute_id = :dispute_id',
            array(
                ':party_id'   => $partyID,
                ':dispute_id' => $disputeID
            )
        );
    }

    public static function updatePartyRecord($partyID, $field, $value) {
        Database::instance()->exec(
            'UPDATE dispute_parties SET ' . $field . ' = :value WHERE party_id = :party_id',
            array(
                ':value'    => $value,
                ':party_id' => $partyID
            )
        );
    }

}