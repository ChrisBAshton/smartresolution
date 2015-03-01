<?php

class Dispute {

    function __construct($disputeID) {
        $this->setVariables($disputeID);
    }

    public function partyA() {
        return $this->partyA;
    }

    public function partyB() {
        return $this->partyB;
    }

    public function setPartyB($organisationId) {
        $db = Database::instance();
        $db->begin();
        $partyId = DisputeParty::create($organisationId);
        if ($partyId) {
            $this->updateField('party_b', $partyId);
            $db->commit();
        }
        else {
            throw new Exception("Couldn't set party b!");
        }
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

    public function hasBeenOpened() {
        return $this->partyB() !== false;
    }

    public function hasNotBeenOpened() {
        return !$this->hasBeenOpened();
    }

    public function waitingForLawFirmB() {
        if ($this->hasBeenOpened()) {
            return $this->partyB()->getIndividual() === false;
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

    private function setVariables($disputeID) {
        $dispute = Database::instance()->exec('SELECT * FROM disputes WHERE dispute_id = :dispute_id', array(':dispute_id' => $disputeID));

        if (count($dispute) !== 1) {
            throw new Exception("The dispute you are trying to view does not exist.");
        }
        else {
            $dispute         = $dispute[0];
            $this->disputeId = (int) $dispute['dispute_id'];
            $partyAId        = (int) $dispute['party_a'];
            $partyBId        = (int) $dispute['party_b'];
            if ($partyAId === 0) {
                throw new Exception('A dispute must have at least one organisation associated with it!');
            }
            $this->partyA    = new DisputeParty($partyAId);
            $this->partyB    = $partyBId === 0 ? false : new DisputeParty($partyBId);
            $this->title     = $dispute['title'];
        }
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

        Dispute::ensureCorrectAccountTypes(array(
            'law_firm' => $lawFirmA
        ));

        $db = Database::instance();
        $db->begin();
        $partyID = DisputeParty::create($lawFirmA);
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

    public static function ensureCorrectAccountTypes($accountTypes) {
        $correctAccountTypes = 
            (
                isset($accountTypes['law_firm']) &&
                AccountDetails::getAccountById($accountTypes['law_firm']) instanceof LawFirm
            );

        if (!$correctAccountTypes) {
            throw new Exception('Invalid account types were set.');
        }
    }
}