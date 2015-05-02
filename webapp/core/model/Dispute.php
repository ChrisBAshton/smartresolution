<?php

/**
 * Disputes are the key class in Online Dispute Resolution platforms.
 */
class Dispute {

    private $disputeID;
    private $type;
    private $title;
    private $status;
    private $partyAId;
    private $partyBId;
    private $partyA;
    private $partyB;
    private $rtc;

    /**
     * Dispute constructor.
     * @param array   $data                              Array of data representing the dispute.
     *        int     $data['dispute_id']                ID of the dispute.
     *        string  $data['type']                      Type of dispute, which is linked to a custom dispute type module. The default module is 'Other', which adds nothing to the core system functionality.
     *        string  $data['title']                     The title of the dispute.
     *        string  $data['status']                    The status of the dispute.
     *        int     $data['party_a']                   The dispute party ID corresponding to party A.
     *        int     $data['party_b']                   The dispute party ID corresponding to party B.
     *        boolean $data['round_table_communication'] Denotes whether or not agents can communicate with one another when in mediation.
     */
    function __construct($data) {
        $this->disputeID = $data['dispute_id'];
        $this->type      = $data['type'];
        $this->title     = $data['title'];
        $this->status    = $data['status'];
        $this->partyAId  = $data['party_a'];
        $this->partyBId  = $data['party_b'];
        $this->partyA    = new DisputeParty(DBGet::instance()->disputePartyDetails($data['party_a']), $this->disputeID);
        $this->partyB    = new DisputeParty(DBGet::instance()->disputePartyDetails($data['party_b']), $this->disputeID);
        $this->rtc       = $data['round_table_communication'];
    }

    /**
     * Returns the ID of the dispute.
     * @return int
     */
    public function getDisputeId() {
        return $this->disputeID;
    }

    /**
     * Returns the URL of the dispute.
     * @return string
     */
    public function getUrl() {
        return '/disputes/' . $this->getDisputeId();
    }

    /**
     * Returns the dispute title, as set by Law Firm A.
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Returns Dispute Party A (the party who initiated the dispute).
     * @return DisputeParty
     */
    public function getPartyA() {
        return $this->partyA;
    }

    /**
     * Returns Dispute Party B (the party who the dispute is opened against).
     * @return DisputeParty
     */
    public function getPartyB() {
        return $this->partyB;
    }

    /**
     * Returns the type of the dispute. By default, this is 'other', but could be a custom dispute type linked to a custom dispute type module.
     * @return string Dispute type.
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Returns the status of the dispute; either 'ongoing', 'resolved', or 'failed'
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Returns the state of the dispute in the context of the current account. If an account is passed, returns the state of the dispute in the context of the passed account. For example, dispute party A's Law Firm might be able to edit the summary of the dispute, but they might not be allowed to send a message, whereas their Agent might be able to do both.
     * @param  Account $account (Optional) The account to get the dispute state context for.
     * @return DisputeState
     */
    public function getState($account = false) {
        return DisputeStateCalculator::instance()->getState($this, $account);
    }

    /**
     * Returns the state of the dispute's mediation assignment. This is a 'middle state' class which is useful for monitoring how far along the mediation assignment process a dispute has come, e.g. has a mediation centre been agreed or merely proposed, or not proposed at all?
     *
     * @todo  remove dependencies on this function, work exclusively through the getState function instead for consistency.
     * @return MediationState
     */
    public function getMediationState() {
        return new MediationState($this->disputeID);
    }

    /**
     * Retrieves the most recent accepted Lifespan that has been attributed to this dispute.
     * If no Lifespan has been accepted, retrieves the most recent offered Lifespan.
     * If no Lifespan has even been offered, returns a mock Lifespan object so that method calls still work.
     * @return Lifespan
     */
    public function getCurrentLifespan() {
        return LifespanFactory::instance()->getCurrentLifespan($this->disputeID);
    }

    /**
     * Gets the latest lifespan that has not been declined. This is a useful method for lifespan renegotiation.
     * @return Lifespan
     */
    public function getLatestLifespan() {
        return LifespanFactory::instance()->getLatestLifespan($this->disputeID);
    }

    /**
     * Returns an array of all of the evidence associated with the dispute.
     * @return array<Evidence>
     */
    public function getEvidences() {
        return DBQuery::instance()->getEvidences($this->getDisputeId());
    }

    /**
     * Returns an array of all of the round-table messages associated with the dispute, i.e. the messages between agents A and B, and any round-table communication messages between agent A, agent B and the mediator.
     * @return array<Message>
     */
    public function getMessages() {
        return DBQuery::instance()->retrieveDisputeMessages($this->getDisputeId());
    }

    /**
     * Returns an array of all of the private messages between two given individuals, in terms of the current dispute. These are usually between an agent and a mediator, but could in theory be between any account types.
     * @param  int $individualA Login ID of the first individual.
     * @param  int $individualB Login ID of the second individual.
     * @return array<Message>
     */
    public function getMessagesBetween($individualA, $individualB) {
        return DBQuery::instance()->retrieveMediationMessages($this->getDisputeId(), $individualA, $individualB);
    }

    /**
     * Denotes whether or not the mediator has enabled round-table communication, which in turn denotes whether or not the agents in the dispute can continue to message one another.
     * @return boolean True if round-table communication is enabled, false if not.
     */
    public function inRoundTableCommunication() {
        return $this->rtc;
    }

    /**
     * Describes whether the given login ID is allowed to view the dispute.
     * @param  int $loginID Login ID of account to check.
     * @return boolean      True if account is allowed to view the dispute, otherwise false.
     */
    public function canBeViewedBy($loginID) {
        return (
            $this->getPartyA()->contains($loginID) ||
            $this->getPartyB()->contains($loginID) ||
            $this->isAMediationParty($loginID)
        );
    }

    /**
     * Denotes whether or not the given login ID is a mediation centre or mediator involved in this dispute.
     * @param  int      $partyID Login ID of account to check.
     * @return boolean           True if account is a mediation party, otherwise false.
     */
    public function isAMediationParty($partyID) {
        $state = $this->getMediationState();
        return (
            ($state->mediationCentreDecided() && $partyID === $state->getMediationCentre()->getLoginId()) ||
            ($state->mediatorDecided()        && $partyID === $state->getMediator()->getLoginId())
        );
    }

    /**
     * Returns the ID of the opposing party - this is useful for calculating who to send notifications to.
     * It calculates which dispute party is the opposing party, then returns the ID of the agent if it is set or the law firm if it is set, or false if neither is set.
     * @param  int $loginID Login ID of the account whose 'opposite' we want to find.
     * @return int|false    Login ID of the opposing account.
     */
    public function getOpposingPartyId($loginID) {
        $lawFirmA = $this->getPartyA()->getLawFirm();
        $lawFirmB = $this->getPartyB()->getLawFirm();
        $agentA   = $this->getPartyA()->getAgent();
        $agentB   = $this->getPartyB()->getAgent();

        if ($this->getPartyA()->contains($loginID)) {
            $opposingParty = $agentB ? $agentB : $lawFirmB;
        }
        else {
            $opposingParty = $agentA ? $agentA : $lawFirmA;
        }

        return $opposingParty ? $opposingParty->getLoginId() : false;
    }

    /**
     * Sets the type of the dispute. Note that this is not persistent until the Dispute is passed to DBUpdate::instance()->dispute().
     * @param string $type The type of dispute.
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Closes the dispute, marking it as 'resolved'. Note that this is not persistent until the Dispute is passed to DBUpdate::instance()->dispute().
     */
    public function closeSuccessfully() {
        $this->status = 'resolved';
    }

    /**
     * Closes the dispute, marking it as 'failed'. Note that this is not persistent until the Dispute is passed to DBUpdate::instance()->dispute().
     */
    public function closeUnsuccessfully() {
        $this->status = 'failed';
    }

    /**
     * Enables round-table communication and notifies the agents of this. Note that this is not persistent until the Dispute is passed to DBUpdate::instance()->dispute().
     */
    public function enableRoundTableCommunication() {
        $this->rtc = true;
        $this->notifyAgentsOfRTC('enabled');
    }


    /**
     * Disabled round-table communication and notifies the agents of this. Note that this is not persistent until the Dispute is passed to DBUpdate::instance()->dispute().
     */
    public function disableRoundTableCommunication() {
        $this->rtc = false;
        $this->notifyAgentsOfRTC('disabled');
    }

    /**
     * Creates notifications for all of the agents involved in the dispute, informing them about a change in round-table communication status.
     * @param  string $enabledOrDisabled Status of the round-table communication; either 'enabled' or 'disabled'.
     */
    private function notifyAgentsOfRTC($enabledOrDisabled) {
        $notifyAgents = array($this->getPartyA()->getAgent(), $this->getPartyB()->getAgent());

        foreach($notifyAgents as $agent) {
            if ($agent) {
                DBCreate::instance()->notification(array(
                    'recipient_id' => $agent->getLoginId(),
                    'message'      => 'The mediator has ' . $enabledOrDisabled . ' round-table-communication.',
                    'url'          => $this->getUrl() . '/chat'
                ));
            }
        }
    }
}