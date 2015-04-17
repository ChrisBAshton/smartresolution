<?php

class DisputeParty {

    private $partyID;
    private $disputeID;
    private $organisationID;
    private $individualID;
    private $summary;

    function __construct($partyID, $disputeID = false) {
        $this->setVariables($partyID, $disputeID);
    }

    private function setVariables($partyID, $disputeID) {
        if ($partyID === 0) {
            $partyDetails = array();
        }
        else {
            $partyDetails = DBGet::instance()->disputeParty($partyID);
        }

        $this->partyID        = $partyID;
        $this->disputeID      = $disputeID;
        $this->individualID   = isset($partyDetails['individual_id'])   ? (int) $partyDetails['individual_id'] : false;
        $this->organisationID = isset($partyDetails['organisation_id']) ? (int) $partyDetails['organisation_id'] : false;
        $this->summary        = isset($partyDetails['summary']) ? htmlspecialchars($partyDetails['summary']) : false;
    }

    public function getPartyId() {
        return $this->partyID;
    }

    public function getLawFirm() {
        return DBAccount::instance()->getAccountById($this->organisationID);
    }

    public function getAgent() {
        return DBAccount::instance()->getAccountById($this->individualID);
    }

    public function getSummary() {
        return $this->summary;
    }

    public function setLawFirm($organisationId) {
        $this->setPartyDatabaseField('organisation_id', $organisationId);
        $this->notify($organisationId, 'A dispute has been opened against your company.');
    }

    public function setAgent($individualID) {
        $this->validateBeforeSettingAgent($individualID);
        $this->setPartyDatabaseField('individual_id', $individualID);
        $this->notify($individualID, 'A new dispute has been assigned to you.');
    }

    private function notify($loginID, $message) {
        if ($this->disputeID) {
            $dispute = new Dispute($this->disputeID);
            DBCreate::instance()->notification(array(
                'recipient_id' => $loginID,
                'message'      => $message,
                'url'          => $dispute->getUrl()
            ));
        }
    }

    // @TODO - write tests for this
    private function validateBeforeSettingAgent($individualID) {
        $agent = DBAccount::instance()->getAccountById($individualID);

        if (!$this->organisationID) {
            throw new Exception('Tried setting the agent before setting the law firm!');
        }

        if ( ! ($agent instanceof Agent) ) {
            throw new Exception('Tried setting a non-agent type as an agent!');
        }

        if ($agent->getOrganisation()->getLoginId() !== $this->organisationID) {
            throw new Exception('You can only assign agents that are in your law firm!');
        }
    }

    public function setSummary($summary) {
        $this->setPartyDatabaseField('summary', $summary);
    }

    // @TODO - write tests for this
    public function setPartyDatabaseField($field, $value) {
        if ($this->partyID === 0 && $field === 'organisation_id') {
            $createdParty = DBCreate::instance()->disputeParty($value);
            $this->partyID = $createdParty->getPartyId();
            DBDispute::updateDisputePartyB($this->partyID, $this->disputeID);
        }
        elseif ($this->getPartyId() !== 0) {
            DBDispute::updatePartyRecord($this->getPartyId(), $field, $value);
        }
        else {
            throw new Exception("Tried setting something other than Law Firm when the record for the party has not been created yet.");
        }

        $this->setVariables($this->partyID, $this->disputeID);
    }

    public function contains($loginID) {
        return ($loginID === $this->organisationID || $loginID === $this->individualID);
    }

}