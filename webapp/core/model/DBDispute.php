<?php

class DBDispute {

    function __construct($disputeID) {
        $this->disputeID = $disputeID;
    }

    public function getData() {
        $dispute = Database::instance()->exec(
            'SELECT * FROM disputes WHERE dispute_id = :dispute_id LIMIT 1',
            array(':dispute_id' => $this->disputeID)
        );

        if (count($dispute) !== 1) {
            throw new Exception("The dispute you are trying to view does not exist.");
        }

        return $dispute[0];
    }

    public function enableRoundTableCommunication() {
        $this->markRoundTableCommunicationAs('enabled');
    }

    public function disableRoundTableCommunication() {
        $this->markRoundTableCommunicationAs('disabled');
    }

    private function markRoundTableCommunicationAs($enabledOrDisabled) {
        Database::instance()->exec(
            'UPDATE disputes SET round_table_communication = :bool WHERE dispute_id = :dispute_id',
            array(
                ':dispute_id' => $this->disputeID,
                ':bool'       => $enabledOrDisabled === 'enabled' ? 'true' : 'false'
            )
        );
    }

    public function getPartyDetails($partyID) {
        if ($partyID === 0) {
            return array(
                'agent'    => false,
                'law_firm' => false,
                'summary'  => false
            );
        }

        $partyDetails = Database::instance()->exec(
            'SELECT * FROM dispute_parties WHERE party_id = :party_id LIMIT 1',
            array(':party_id' => $partyID)
        )[0];

        $agent   = isset($partyDetails['individual_id'])   ? AccountDetails::getAccountById($partyDetails['individual_id'])   : false;
        $lawFirm = isset($partyDetails['organisation_id']) ? AccountDetails::getAccountById($partyDetails['organisation_id']) : false;
        $summary = isset($partyDetails['summary']) ? htmlspecialchars($partyDetails['summary']) : false;

        return array(
            'agent'    => $agent,
            'law_firm' => $lawFirm,
            'summary'  => $summary
        );
    }

    public function setPartyDatabaseField($party, $field, $value) {
        $db = Database::instance();
        $db->begin();
        $partyID = $db->exec(
            'SELECT ' . $party . ' FROM disputes WHERE dispute_id = :dispute_id',
            array(':dispute_id' => $this->disputeID)
        )[0][$party];
        $db->exec(
            'UPDATE dispute_parties SET ' . $field . ' = :value WHERE party_id = :party_id',
            array(
                ':value'    => $value,
                ':party_id' => $partyID
            )
        );
        $db->commit();
    }

    public function updateField($key, $value) {
        Database::instance()->exec('UPDATE disputes SET ' . $key . ' = :new_value WHERE dispute_id = :dispute_id',
            array(
                ':new_value' => $value,
                'dispute_id' => $this->disputeID
            )
        );
    }
}
