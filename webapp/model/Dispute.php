<?php

class Dispute {

    function __construct($disputeID) {
        $this->setVariables($disputeID);
    }

    private function setVariables($disputeID) {
        $dispute = Database::instance()->exec(
            'SELECT * FROM disputes WHERE dispute_id = :dispute_id LIMIT 1',
            array(':dispute_id' => $disputeID)
        );

        if (count($dispute) !== 1) {
            throw new Exception("The dispute you are trying to view does not exist.");
        }
        else {
            $dispute         = $dispute[0];
            $this->disputeId = (int) $dispute['dispute_id'];
            $this->title     = $dispute['title'];
            $this->partyA    = $this->getPartyDetails((int) $dispute['party_a']);
            $this->partyB    = $this->getPartyDetails((int) $dispute['party_b']);
            $this->lifespan  = LifespanFactory::getCurrentLifespan((int) $dispute['dispute_id']);
            $this->offeredLifespan = LifespanFactory::getOfferedLifespan((int) $dispute['dispute_id']);

            if (!$this->partyA) {
                throw new Exception('A dispute must have at least one organisation associated with it!');
            }
        }
    }

    public function getState($account) {
        return DisputeStateCalculator::getState($this, $account);
    }

    public function refresh() {
        $this->setVariables($this->getDisputeId());
    }

    public function getLifespan() {
        return $this->lifespan;
    }

    public function getOfferedLifespan() {
        return $this->offeredLifespan;
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

    private function getPartyDetails($partyID) {
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

    // we should never need to set Law Firm A through a method, so there is no corresponding setLawFirmA.
    public function setLawFirmB($organisationId) {
        $db = Database::instance();
        $db->begin();
        $partyId = DBL::createDisputeParty($organisationId);
        if ($partyId) {
            $this->updateField('party_b', $partyId);
            $db->commit();
        }
        else {
            throw new Exception("Couldn't set second law firm!");
        }
    }

    public function setSummaryForPartyA($organisationId) {
        $this->setPartyDatabaseField('party_a', 'summary', $organisationId);
    }

    public function setSummaryForPartyB($organisationId) {
        $this->setPartyDatabaseField('party_b', 'summary', $organisationId);
    }

    public function setAgentA($loginID) {
        $this->setAgent('party_a', $loginID);
    }

    public function setAgentB($loginID) {
        $this->setAgent('party_b', $loginID);
    }

    public function setAgent($party, $loginID) {
        $agent = AccountDetails::getAccountById($loginID);

        if ( ! ($agent instanceof Agent) ) {
            throw new Exception('Tried setting a non-agent type as an agent!');
        }
        else if (
            ($party === 'party_a' && $agent->getOrganisation()->getLoginId() !== $this->getLawFirmA()->getLoginId()) ||
            ($party === 'party_b' && $agent->getOrganisation()->getLoginId() !== $this->getLawFirmB()->getLoginId())
        ) {
            throw new Exception('You can only assign agents that are in your law firm!');
        }

        $this->setPartyDatabaseField($party, 'individual_id', $loginID);
    }

    public function setPartyDatabaseField($party, $field, $value) {
        $db = Database::instance();
        $db->begin();
        $partyID = $db->exec(
            'SELECT ' . $party . ' FROM disputes WHERE dispute_id = :dispute_id',
            array(':dispute_id' => $this->getDisputeId())
        )[0][$party];
        $db->exec(
            'UPDATE dispute_parties SET ' . $field . ' = :value WHERE party_id = :party_id',
            array(
                ':value'    => $value,
                ':party_id' => $partyID
            )
        );
        $db->commit();
        $this->setVariables($this->getDisputeId());
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

        if ($partyID === $this->partyA['law_firm']->getLoginId() ||
            $partyID === $this->partyA['agent']->getLoginId())
        {
            if ($this->partyB['agent']->getLoginId()) {
                return $this->partyB['agent']->getLoginId();
            }
            else {
                $this->partyB['law_firm']->getLoginId();
            }
        }
        else {
            if ($this->partyA['agent']->getLoginId()) {
                return $this->partyA['agent']->getLoginId();
            }
            else {
                $this->partyA['law_firm']->getLoginId();
            }
        }
    }

    private function updateField($key, $value) {
        Database::instance()->exec('UPDATE disputes SET ' . $key . ' = :new_value WHERE dispute_id = :dispute_id',
            array(
                ':new_value' => $value,
                'dispute_id' => $this->getDisputeId()
            )
        );
        $this->setVariables($this->getDisputeId());
    }

}
