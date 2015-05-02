<?php

class DisputeParty {

    private $partyID;
    private $individualID;
    private $organisationID;
    private $summary;
    private $disputeID;

    function __construct($partyDetails = array(), $disputeID = false) {
        $this->partyID        = isset($partyDetails['party_id'])        ? $partyDetails['party_id'] : false;
        $this->individualID   = isset($partyDetails['individual_id'])   ? $partyDetails['individual_id'] : false;
        $this->organisationID = isset($partyDetails['organisation_id']) ? $partyDetails['organisation_id'] : false;
        $this->summary        = isset($partyDetails['summary'])         ? $partyDetails['summary'] : false;
        $this->disputeID      = $disputeID;
    }

    public function getPartyId() {
        return $this->partyID;
    }

    public function getDisputeId() {
        return $this->disputeID;
    }

    public function getLawFirmID() {
        return $this->organisationID;
    }

    public function getAgentID() {
        return $this->individualID;
    }

    public function getLawFirm() {
        return DBGet::instance()->account($this->organisationID);
    }

    public function getAgent() {
        return DBGet::instance()->account($this->individualID);
    }

    public function getSummary() {
        return htmlspecialchars($this->summary);
    }

    public function getRawSummary() {
        return $this->summary;
    }

    public function setPartyId($partyID) {
        $this->partyID = $partyID;
    }

    public function setLawFirm($organisationID) {
        $this->organisationID = $organisationID;
        $this->notify($organisationID, 'A dispute has been opened against your company.');
    }

    public function setAgent($individualID) {
        $this->validateBeforeSettingAgent($individualID);
        $this->individualID = $individualID;
        $this->notify($individualID, 'A new dispute has been assigned to you.');
    }

    private function notify($loginID, $message) {
        if ($this->disputeID) {
            $dispute = DBGet::instance()->dispute($this->disputeID);
            DBCreate::instance()->notification(array(
                'recipient_id' => $loginID,
                'message'      => $message,
                'url'          => $dispute->getUrl()
            ));
        }
    }

    private function validateBeforeSettingAgent($individualID) {
        $utils = Utils::instance();
        $agent = DBGet::instance()->account($individualID);

        if (!$this->organisationID) {
            $utils->throwException('Tried setting the agent before setting the law firm!');
        }

        if ( ! ($agent instanceof Agent) ) {
            $utils->throwException('Tried setting a non-agent type as an agent!');
        }

        if ($agent->getOrganisation()->getLoginId() !== $this->organisationID) {
            $utils->throwException("You can only assign agents that are in your law firm! The agent's (whose ID is ". $agent->getLoginId() .") organisation ID is " . $agent->getOrganisation()->getLoginId() . " whereas the ID of the organisation in this party is " . $this->organisationID . "!");
        }
    }

    public function setSummary($summary) {
        $this->summary = $summary;
    }

    public function contains($loginID) {
        return ($loginID === $this->organisationID || $loginID === $this->individualID);
    }

}