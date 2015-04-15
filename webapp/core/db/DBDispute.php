<?php

/**
 * This class provides the database middle layer between disputes and the database.
 */
class DBDispute {

    /**
     * Constructor.
     * @param int $disputeID The dispute ID.
     */
    function __construct($disputeID) {
        $this->disputeID = $disputeID;
    }

    /**
     * Gets the dispute data.
     * @return array<mixed> The array of associated dispute data.
     */
    public function getData() {
        $dispute = DBQuery::getRowById('disputes', 'dispute_id', $this->disputeID);

        if (!$dispute) {
            throw new Exception("The dispute you are trying to view does not exist.");
        }

        return $dispute;
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

    // /**
    //  * Retrieves the agent, law firm and summary corresponding to the given party ID.
    //  * @param  int $partyID The ID of the party.
    //  * @return array        The corresponding details.
    //  *         Array['agent']     The agent.
    //  *         Array['law_firm']  The law firm.
    //  *         Array['summary']   The summary.
    //  */
    // public function getPartyDetails($partyID) {
    //     if ($partyID === 0) {
    //         return array(
    //             'agent'    => false,
    //             'law_firm' => false,
    //             'summary'  => false
    //         );
    //     }

    //     $partyDetails = Database::instance()->exec(
    //         'SELECT * FROM dispute_parties WHERE party_id = :party_id LIMIT 1',
    //         array(':party_id' => $partyID)
    //     )[0];

    //     $agent   = isset($partyDetails['individual_id'])   ? DBAccount::getAccountById($partyDetails['individual_id'])   : false;
    //     $lawFirm = isset($partyDetails['organisation_id']) ? DBAccount::getAccountById($partyDetails['organisation_id']) : false;
    //     $summary = isset($partyDetails['summary']) ? htmlspecialchars($partyDetails['summary']) : false;

    //     return array(
    //         'agent'    => $agent,
    //         'law_firm' => $lawFirm,
    //         'summary'  => $summary
    //     );
    // }

    // /**
    //  * Sets a property in the dispute_parties table.
    //  *
    //  * @param string  $party    The role of the party, e.g. 'party_a', 'party_b'
    //  * @param string  $field    The field to update.
    //  * @param Unknown $value    The value to set.
    //  */
    // public function setPartyDatabaseField($party, $field, $value) {
    //     $db = Database::instance();
    //     $db->begin();
    //     $partyID = $db->exec(
    //         'SELECT ' . $party . ' FROM disputes WHERE dispute_id = :dispute_id',
    //         array(':dispute_id' => $this->disputeID)
    //     )[0][$party];
    //     $db->exec(
    //         'UPDATE dispute_parties SET ' . $field . ' = :value WHERE party_id = :party_id',
    //         array(
    //             ':value'    => $value,
    //             ':party_id' => $partyID
    //         )
    //     );
    //     $db->commit();
    // }

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
}
