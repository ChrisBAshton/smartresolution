<?php

class DisputeParty {

    function __construct($partyID) {
        if ($partyID === 0) {
            throw new Exception('Party ID must be set before calling DisputeParty constructor');
        }
        $this->setVariables($partyID);
    }

    public function getPartyId() {
        return $this->partyID;
    }

    public function getOrganisation() {
        return $this->organisation;
    }

    public function getIndividual() {
        return $this->individual;
    }

    public function getLawFirm() {
        return $this->getAndEnsureType($this->organisation, 'LawFirm');
    }

    public function getMediationCentre() {
        return $this->getAndEnsureType($this->organisation, 'MediationCentre');
    }

    public function getAgent() {
        return $this->getAndEnsureType($this->individual, 'Agent');
    }

    public function getMediator() {
        return $this->getAndEnsureType($this->individual, 'Mediator');
    }

    private function getAndEnsureType($object, $type) {
        if (!is_a($object, $type)) {
            throw new Exception ("Tried to get an object of type " . $type . ".");
        }
        return $object;
    }

    public function setAgent($agentID) {
        if ($this->getAndEnsureType(AccountDetails::getAccountById($agentID), 'Agent') ) {
            return $this->updateField('individual_id', $agentID);
        }
    }

    private function updateField($key, $value) {
        Database::instance()->exec('UPDATE dispute_parties SET ' . $key . ' = :new_value WHERE party_id = :party_id',
            array(
                ':new_value' => $value,
                ':party_id' => $this->getPartyId()
            )
        );
        $this->setVariables($this->getPartyId());
    }

    private function setVariables($partyID) {
        $partyDetails = Database::instance()->exec(
            'SELECT * FROM dispute_parties WHERE party_id = :party_id LIMIT 1',
             array(
                ':party_id' => $partyID
            )
        )[0];

        $this->partyID      = $partyID;
        $this->organisation = AccountDetails::getAccountById((int) $partyDetails['organisation_id']);
        $this->individual   = is_null($partyDetails['individual_id']) ? false : AccountDetails::getAccountById((int) $partyDetails['individual_id']);
    }

    public static function create($organisationId) {
        Database::instance()->exec(
            'INSERT INTO dispute_parties (party_id, organisation_id, individual_id, summary)
             VALUES (NULL, :organisation_id, :individual_id, NULL)', array(
            ':organisation_id' => $organisationId,
            ':individual_id'   => NULL
        ));
        $partyID = (int) Database::instance()->exec(
            'SELECT * FROM dispute_parties ORDER BY party_id DESC LIMIT 1'
        )[0]['party_id'];
        return $partyID;
    }
}