<?php
// @TODO - needs a major refurbishment
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
            $this->lifespan  = new Lifespan((int) $dispute['dispute_id']);

            if (!$this->partyA) {
                throw new Exception('A dispute must have at least one organisation associated with it!');
            }
        }
    }

    public function refresh() {
        $this->setVariables($this->getDisputeId());
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

    public function getLifespan() {
        return $this->lifespan;
    }

    private function getPartyDetails($partyID) {
        if ($partyID === 0) {
            return array(
                'agent'    => false,
                'law_firm' => false
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

    // we should never need to set law firm a through a method, so there is no corresponding setLawFirmA.
    public function setLawFirmB($organisationId) {
        $db = Database::instance();
        $db->begin();
        $partyId = Dispute::createParty($organisationId);
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

    public function hasBeenOpened() {
        return $this->getLawFirmB() !== false;
    }

    public function hasNotBeenOpened() {
        return !$this->hasBeenOpened();
    }

    public function waitingForLawFirmB() {
        if ($this->hasBeenOpened()) {
            return $this->getAgentB() === false;
        }
        return true;
    }

    public function canBeViewedBy($loginID) {
        $viewableDisputes = Dispute::getAllDisputesConcerning($loginID);
        foreach($viewableDisputes as $dispute) {
            if ($dispute->getDisputeId() === $this->getDisputeId()) {
                return true;
            }
        }

        return false;
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

    public static function getAllDisputesConcerning($loginID) {
        $disputes = array();
        $disputesDetails = Database::instance()->exec(
            'SELECT dispute_id FROM disputes

            INNER JOIN dispute_parties
            ON disputes.party_a     = dispute_parties.party_id
            OR disputes.party_b     = dispute_parties.party_id
            OR disputes.third_party = dispute_parties.party_id

            WHERE organisation_id = :login_id OR individual_id = :login_id
            ORDER BY party_id DESC',
            array(':login_id' => $loginID)
        );
        foreach($disputesDetails as $dispute) {
            $disputes[] = new Dispute($dispute['dispute_id']);
        }
        return $disputes;
    }

    /**
     * Creates a new Dispute, saving it to the database.
     * 
     * @param  Array $details Array of details to populate the database with.
     * @return Dispute        The Dispute object associated with the new entry.
     */
    public static function create($details) {
        $lawFirmA = (int) Utils::getValue($details, 'law_firm_a');
        $type     = Utils::getValue($details, 'type');
        $title    = Utils::getValue($details, 'title');
        $agentA   = isset($details['agent_a']) ? $details['agent_a'] : NULL;
        $summary  = isset($details['summary']) ? $details['summary'] : NULL;

        Dispute::ensureCorrectAccountTypes(array(
            'law_firm' => $lawFirmA,
            'agent'    => $agentA
        ));

        $db = Database::instance();
        $db->begin();
        $partyID = Dispute::createParty($lawFirmA, $agentA, $summary);
        $db->exec(
            'INSERT INTO disputes (dispute_id, party_a, type, title)
             VALUES (NULL, :party_a, :type, :title)', array(
            ':party_a'    => $partyID,
            ':type'       => $type,
            ':title'      => $title
        ));
        $newDispute = $db->exec(
            'SELECT * FROM disputes ORDER BY dispute_id DESC LIMIT 1'
        )[0];
        
        // sanity check
        if ((int)$newDispute['party_a'] !== $partyID ||
            $newDispute['type']         !== $type    ||
            $newDispute['title']        !== $title) {
            throw new Exception("There was a problem creating your Dispute.");
        }
        else {
            $db->commit();
            return new Dispute((int) $newDispute['dispute_id']);
        }
    }

    public static function createParty($organisationId, $individualId = NULL, $summary = NULL) {
        Database::instance()->exec(
            'INSERT INTO dispute_parties (party_id, organisation_id, individual_id, summary)
             VALUES (NULL, :organisation_id, :individual_id, :summary)', array(
            ':organisation_id' => $organisationId,
            ':individual_id'   => $individualId,
            ':summary'         => $summary
        ));
        $partyID = (int) Database::instance()->exec(
            'SELECT * FROM dispute_parties ORDER BY party_id DESC LIMIT 1'
        )[0]['party_id'];
        return $partyID;
    }

    public static function ensureCorrectAccountTypes($accountTypes) {
        $correctAccountTypes = true;
        if (isset($accountTypes['law_firm'])) {
            if (!AccountDetails::getAccountById($accountTypes['law_firm']) instanceof LawFirm) {
                $correctAccountTypes = false;
            }
        }
        if (isset($accountTypes['agent'])) {
            if (!AccountDetails::getAccountById($accountTypes['agent']) instanceof Agent) {
                $correctAccountTypes = false;
            }
        }

        if (!$correctAccountTypes) {
            throw new Exception('Invalid account types were set.');
        }
    }
}