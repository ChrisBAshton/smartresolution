<?php

class Dispute {

    function __construct($data) {
        $this->disputeID = $data['dispute_id'];
        $this->type      = $data['type'];
        $this->title     = $data['title'];
        $this->status    = $data['status'];
        $this->partyA    = new DisputeParty($data['party_a'], $this->disputeID);
        $this->partyB    = new DisputeParty($data['party_b'], $this->disputeID);
        $this->rtc       = $data['round_table_communication'];
    }

    public function getType() {
        return $this->type;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getState($account = false) {
        return DisputeStateCalculator::instance()->getState($this, $account);
    }

    public function getMediationState() {
        return new MediationState($this->disputeID);
    }

    public function getCurrentLifespan() {
        return LifespanFactory::instance()->getCurrentLifespan($this->disputeID);
    }

    public function getLatestLifespan() {
        return LifespanFactory::instance()->getLatestLifespan($this->disputeID);
    }

    public function getDisputeId() {
        return $this->disputeID;
    }

    public function getUrl() {
        return '/disputes/' . $this->getDisputeId();
    }

    public function getTitle() {
        return $this->title;
    }

    public function getPartyA() {
        return $this->partyA;
    }

    public function getPartyB() {
        return $this->partyB;
    }

    public function getEvidences() {
        return DBDispute::instance()->getEvidences($this->getDisputeId());
    }

    public function inRoundTableCommunication() {
        return $this->rtc;
    }

    public function enableRoundTableCommunication() {
        $this->rtc = true;
        $this->notifyAgentsOfRTC('enabled');
    }

    public function disableRoundTableCommunication() {
        $this->rtc = false;
        $this->notifyAgentsOfRTC('disabled');
    }

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

    public function closeSuccessfully() {
        $this->status = 'resolved';
    }

    public function closeUnsuccessfully() {
        $this->status = 'failed';
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function canBeViewedBy($loginID) {
        return (
            $this->getPartyA()->contains($loginID) ||
            $this->getPartyB()->contains($loginID) ||
            $this->isAMediationParty($loginID)
        );
    }

    public function getOpposingPartyId($loginID) {
        // mock Agent And Organisation Accounts If Necessary
        $mockIfNecessary = array(
            'lawFirmA' => $this->getPartyA()->getLawFirm(),
            'lawFirmB' => $this->getPartyB()->getLawFirm(),
            'agentA'   => $this->getPartyA()->getAgent(),
            'agentB'   => $this->getPartyB()->getAgent(),
        );
        foreach($mockIfNecessary as $key => $object) {
            if (!$mockIfNecessary[$key]) {
                $mockIfNecessary[$key] = new AccountMock();
            }
        }

        if ($this->getPartyA()->contains($loginID)) {
            $agentB = $this->getPartyB()->getAgent();
            $opposingParty = $agentB ? $agentB : $this->getPartyB()->getLawFirm();
        }
        else {
            $agentA = $this->getPartyA()->getAgent();
            $opposingParty = $agentA ? $agentA : $this->getPartyA()->getLawFirm();
        }

        return $opposingParty ? $opposingParty->getLoginId() : false;
    }

    public function isAMediationParty($partyID) {
        $state = $this->getMediationState();
        return (
            ($state->mediationCentreDecided() && $partyID === $state->getMediationCentre()->getLoginId()) ||
            ($state->mediatorDecided()        && $partyID === $state->getMediator()->getLoginId())
        );
    }

    public function getMessages() {
        return DBQuery::instance()->retrieveDisputeMessages($this->getDisputeId());
    }

    public function getMessagesBetween($individualA, $individualB) {
        return DBQuery::instance()->retrieveMediationMessages($this->getDisputeId(), $individualA, $individualB);
    }

}
