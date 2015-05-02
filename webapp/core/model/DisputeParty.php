<?php

/**
 * Every dispute has two parties, each of which contains a law firm, an agent and their summary of the dispute. That is encapsulated here in the DisputeParty class.
 */
class DisputeParty {

    private $partyID;
    private $individualID;
    private $organisationID;
    private $summary;
    private $disputeID;

    /**
     * DisputeParty constructor. Both parameters are optional, since young disputes only have party A defined (no party B) but we still want all of the methods that a DisputeParty object provides.
     * @param array     $partyDetails                    (Optional) Details of the party.
     *        int       $partyDetails['party_id']        ID of the party in the database.
     *        int       $partyDetails['individual_id']   Login ID of the agent in the party.
     *        int       $partyDetails['organisation_id'] Login ID of the organisation in the party.
     *        string    $partyDetails['summary']         Party's description of the dispute.
     * @param int|false $disputeID                       (Optional) ID of the dispute. Sometimes we need to be able to create a dispute party without associating it with a dispute, in which case we don't pass a second parameter.
     * @todo  This is quite a complicated design - consider refactoring.
     */
    function __construct($partyDetails = array(), $disputeID = false) {
        $this->partyID        = isset($partyDetails['party_id'])        ? $partyDetails['party_id'] : false;
        $this->individualID   = isset($partyDetails['individual_id'])   ? $partyDetails['individual_id'] : false;
        $this->organisationID = isset($partyDetails['organisation_id']) ? $partyDetails['organisation_id'] : false;
        $this->summary        = isset($partyDetails['summary'])         ? $partyDetails['summary'] : false;
        $this->disputeID      = $disputeID;
    }

    /**
     * Returns the ID of the party as it is found in the database.
     * @return int Party ID.
     */
    public function getPartyId() {
        return $this->partyID;
    }

    /**
     * Returns the ID of the dispute, or false if no dispute was specified.
     * @return int|false
     */
    public function getDisputeId() {
        return $this->disputeID;
    }

    /**
     * Returns the ID of the party's law firm, or false if none was set.
     * @return int|false
     */
    public function getLawFirmID() {
        return $this->organisationID;
    }

    /**
     * Returns the ID of the party's agent, or false if none was set.
     * @return int|false
     */
    public function getAgentID() {
        return $this->individualID;
    }

    /**
     * Returns an account representing the party's law firm, or false if none was set.
     * @return Account|false
     */
    public function getLawFirm() {
        return DBGet::instance()->account($this->organisationID);
    }


    /**
     * Returns an account representing the party's agent, or false if none was set.
     * @return Account|false
     */
    public function getAgent() {
        return DBGet::instance()->account($this->individualID);
    }

    /**
     * Compares a login ID with the login ID of the party's law firm and agent, returning true if there is a match or false if there is not.
     * @param  int $loginID Login ID to check exists in the dispute party.
     * @return boolean      True if dispute party contains given login ID, false if not.
     */
    public function contains($loginID) {
        return ($loginID === $this->organisationID || $loginID === $this->individualID);
    }

    /**
     * Returns the HTML-escaped summary of the dispute.
     * @return string
     */
    public function getSummary() {
        return htmlspecialchars($this->summary);
    }

    /**
     * Returns the raw summary of the dispute.
     * @return string
     */
    public function getRawSummary() {
        return $this->summary;
    }

    /**
     * Sets the raw summary of the dispute party. Note that this is not made persistent until the DisputeParty object is passed to DBUpdate::instance()->disputeParty().
     * @param string $summary Raw summary.
     */
    public function setSummary($summary) {
        $this->summary = $summary;
    }

    /**
     * Sets the ID of the dispute party. Note that this is not made persistent until the DisputeParty object is passed to DBUpdate::instance()->disputeParty().
     * @param int $partyID The ID of the dispute party.
     */
    public function setPartyId($partyID) {
        $this->partyID = $partyID;
    }

    /**
     * Sets the Law Firm of the dispute party. As this is only called on Law Firm B (rather than Law Firm A), a notification is also created, saying that a dispute has been opened against the law firm.
     * Note that this change is not made persistent until the DisputeParty object is passed to DBUpdate::instance()->disputeParty().
     * @param int $organisationID Login ID of the Law Firm.
     */
    public function setLawFirm($organisationID) {
        $this->organisationID = $organisationID;
        $this->notify($organisationID, 'A dispute has been opened against your company.');
    }

    /**
     * Sets the Agent of the dispute party and notifies them that they have been assigned. Before the agent is assigned, some checks are carried out to ensure that the agent being assigned is a valid agent.
     *
     * Note that this is not made persistent until the DisputeParty object is passed to DBUpdate::instance()->disputeParty().
     * @param int $individualID Login ID of the Agent.
     */
    public function setAgent($individualID) {
        $this->validateBeforeSettingAgent($individualID);
        $this->individualID = $individualID;
        $this->notify($individualID, 'A new dispute has been assigned to you.');
    }

    /**
     * Notifies the given account with the given notification message.
     * @param  int    $loginID Login ID of the account to notify.
     * @param  string $message Contents of the notification.
     */
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

    /**
     * Performs some validation checks before an agent is set in the dispute party to ensure that there are no mismatches, such as a law firm assigning an agent from a different law firm to represent their dispute party. If the agent is invalid, an exception is raised.
     *
     * @param  int $individualID Login ID of the agent to check.
     */
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

}