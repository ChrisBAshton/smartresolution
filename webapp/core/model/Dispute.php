<?php

class Dispute {

    function __construct($disputeID) {
        $this->disputeID = $disputeID;
        $this->refresh();
    }

    public function refresh() {
        $data                  = DBGet::instance()->dispute($this->disputeID);
        $this->type            = $data['type'];
        $this->title           = $data['title'];
        $this->status          = $data['status'];
        $this->partyA          = new DisputeParty((int) $data['party_a'], $this->disputeID);
        $this->partyB          = new DisputeParty((int) $data['party_b'], $this->disputeID);
        $this->currentLifespan = LifespanFactory::instance()->getCurrentLifespan($this->disputeID);
        $this->latestLifespan  = LifespanFactory::instance()->getLatestLifespan($this->disputeID);
        $this->mediationState  = new MediationState($this->disputeID);
        $this->inRoundTableCommunication = $data['round_table_communication'] === 'true';
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

    public function getMediationState($account = false) {
        return $this->mediationState;
    }

    public function getCurrentLifespan() {
        return $this->currentLifespan;
    }

    public function getLatestLifespan() {
        return $this->latestLifespan;
    }

    public function getdisputeID() {
        return $this->disputeID;
    }

    public function getUrl() {
        return '/disputes/' . $this->getdisputeID();
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
        return $this->inRoundTableCommunication;
    }

    public function enableRoundTableCommunication() {
        DBDispute::instance()->markRoundTableCommunicationAs('enabled', $this->getDisputeId());
        $this->notifyAgentsOfRTC('enabled');
        $this->refresh();
    }

    public function disableRoundTableCommunication() {
        DBDispute::instance()->markRoundTableCommunicationAs('disabled', $this->getDisputeId());
        $this->notifyAgentsOfRTC('disabled');
        $this->refresh();
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
        DBDispute::instance()->updateField('status', 'resolved', $this->getDisputeId());
        $this->getCurrentLifespan()->disputeClosed();
        $this->refresh();
    }

    public function closeUnsuccessfully() {
        DBDispute::instance()->updateField('status', 'failed', $this->getDisputeId());
        $this->getCurrentLifespan()->disputeClosed();
        $this->refresh();
    }

    public function setType($type) {
        DBDispute::instance()->updateField('type', $type, $this->getDisputeId());
        $this->refresh();
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
        if ($this->getMediationState()->inMediation()) {
            return (
                $partyID === $this->getMediationState()->getMediator()->getLoginId() ||
                $partyID === $this->getMediationState()->getMediationCentre()->getLoginId()
            );
        }
        return false;
    }

    public function getMessages() {
        return DBQuery::instance()->retrieveDisputeMessages($this->getdisputeID());
    }

    public function getMessagesBetween($individualA, $individualB) {
        return DBQuery::instance()->retrieveMediationMessages($this->getdisputeID(), $individualA, $individualB);
    }

}
