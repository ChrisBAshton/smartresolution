<?php

class Dispute {

    function __construct($disputeID) {
        $this->db = new DBDispute($disputeID);
        $this->refresh();
    }

    public function refresh() {
        $data = $this->db->getData();
        $this->disputeId       = $data['dispute_id'];
        $this->title           = $data['title'];
        $this->status          = $data['status'];
        $this->partyA          = $this->db->getPartyDetails($data['party_a']);
        $this->partyB          = $this->db->getPartyDetails($data['party_b']);
        $this->currentLifespan = LifespanFactory::getCurrentLifespan($data['dispute_id']);
        $this->latestLifespan  = LifespanFactory::getLatestLifespan($data['dispute_id']);

        if (!$this->partyA) {
            throw new Exception('A dispute must have at least one organisation associated with it!');
        }
    }

    public function getStatus() {
        return $this->status;
    }

    public function getState($account = false) {
        return DisputeStateCalculator::getState($this, $account);
    }

    public function getCurrentLifespan() {
        return $this->currentLifespan;
    }

    public function getLatestLifespan() {
        return $this->latestLifespan;
    }

    public function getDisputeId() {
        return $this->disputeId;
    }

    public function getUrl() {
        return '/disputes/' . $this->getDisputeId();
    }

    public function getTitle() {
        return $this->title;
    }

    public function getLawFirmA() {
        return $this->partyA['law_firm'];
    }

    public function getLawFirmB() {
        return $this->partyB['law_firm'];
    }

    public function getAgentA() {
        return $this->partyA['agent'];
    }

    public function getAgentB() {
        return $this->partyB['agent'];
    }

    public function getSummaryFromPartyA() {
        return $this->partyA['summary'];
    }

    public function getSummaryFromPartyB() {
        return $this->partyB['summary'];
    }

    public function closeSuccessfully() {
        $this->db->updateField('status', 'resolved');
        $this->getCurrentLifespan()->disputeClosed();
        $this->refresh();
    }

    public function closeUnsuccessfully() {
        $this->db->updateField('status', 'failed');
        $this->getCurrentLifespan()->disputeClosed();
        $this->refresh();
    }

    // we should never need to set Law Firm A through a method, so there is no corresponding setLawFirmA.
    public function setLawFirmB($organisationId) {
        $partyId = DBL::createDisputeParty($organisationId);
        $this->db->updateField('party_b', $partyId);
        $this->refresh();

        DBL::createNotification(array(
            'recipient_id' => $organisationId,
            'message'      => 'A dispute has been opened against your company.',
            'url'          => $this->getUrl()
        ));
    }

    public function setSummaryForPartyA($summary) {
        $this->db->setPartyDatabaseField('party_a', 'summary', $summary);
        $this->refresh();
    }

    public function setSummaryForPartyB($summary) {
        $this->db->setPartyDatabaseField('party_b', 'summary', $summary);
        $this->refresh();
    }

    public function setAgentA($loginID) {
        $this->setAgent('party_a', $loginID);
    }

    public function setAgentB($loginID) {
        $this->setAgent('party_b', $loginID);
    }

    public function setAgent($party, $loginID) {
        $agent = AccountDetails::getAccountById($loginID);
        $this->validateBeforeSettingAgent($agent, $party);
        $this->db->setPartyDatabaseField($party, 'individual_id', $loginID);
        $this->refresh();

        DBL::createNotification(array(
            'recipient_id' => $loginID,
            'message'      => 'A new dispute has been assigned to you.',
            'url'          => $this->getUrl()
        ));

        if ($this->getOpposingPartyId($loginID)) {
            DBL::createNotification(array(
                'recipient_id' => $this->getOpposingPartyId($loginID),
                'message'      => 'The other party has assigned an agent to the case.',
                'url'          => $this->getUrl()
            ));
        }
    }

    private function validateBeforeSettingAgent($agent, $party) {
        if ( ! ($agent instanceof Agent) ) {
            throw new Exception('Tried setting a non-agent type as an agent!');
        }
        if ($this->agentIsNotInParty($agent, $party)) {
            throw new Exception('You can only assign agents that are in your law firm!');
        }
    }

    private function agentIsNotInParty($agent, $party) {
        if ($party === 'party_a') {
            return $agent->getOrganisation()->getLoginId() !== $this->getLawFirmA()->getLoginId();
        }
        else if ($party === 'party_b') {
            return $agent->getOrganisation()->getLoginId() !== $this->getLawFirmB()->getLoginId();
        }
    }

    public function canBeViewedBy($loginID) {
        $account = AccountDetails::getAccountById($loginID);
        $viewableDisputes = $account->getAllDisputes();
        foreach($viewableDisputes as $dispute) {
            if ($dispute->getDisputeId() === $this->getDisputeId()) {
                return true;
            }
        }

        return false;
    }

    public function getOpposingPartyId($partyID) {

        $this->mockAgentAndOrganisationAccountsIfNecessary();

        if ($this->isInPartyA($partyID)) {
            $opposingParty = $this->agentBIsSet() ? $this->partyB['agent'] : $this->partyB['law_firm'];
        }
        else {
            $opposingParty = $this->agentAIsSet() ? $this->partyA['agent'] : $this->partyA['law_firm'];
        }

        return $opposingParty ? $opposingParty->getLoginId() : false;
    }

    public function isInPartyA($partyID) {
        return (
            $partyID === $this->partyA['law_firm']->getLoginId() ||
            $partyID === $this->partyA['agent']->getLoginId()
        );
    }

    public function isInPartyB($partyID) {
        return (
            $partyID === $this->partyB['law_firm']->getLoginId() ||
            $partyID === $this->partyB['agent']->getLoginId()
        );
    }

    private function agentAIsSet() {
        return $this->partyA['agent']->getLoginId();
    }

    private function agentBIsSet() {
        return $this->partyB['agent'] ? $this->partyB['agent']->getLoginId() : false;
    }

    private function mockAgentAndOrganisationAccountsIfNecessary() {
        $mockIfNecessary = array(
            'lawFirmA' => $this->partyA['law_firm'],
            'lawFirmB' => $this->partyB['law_firm'],
            'agentA'   => $this->partyA['agent'],
            'agentB'   => $this->partyB['agent'],
        );

        foreach($mockIfNecessary as $key => $object) {
            if (!$mockIfNecessary[$key]) {
                $mockIfNecessary[$key] = new MockAccount();
            }
        }
    }

}
