<?php

class Dispute {

    function __construct($disputeID) {
        $this->setVariables($disputeID);
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

    public function getAgentA() {
        return new Agent($this->getAgentAId());
    }

    public function getAgentB() {
        return new Agent($this->getAgentBId());
    }

    public function getAgentAId() {
        return $this->agentA;
    }

    public function getAgentBId() {
        return $this->agentB;
    }

    public function getLawFirmA() {
        return new LawFirm($this->getLawFirmAId());
    }

    public function getLawFirmB() {
        return new LawFirm($this->getLawFirmBId());
    }

    public function getLawFirmAId() {
        return $this->lawFirmA;
    }

    public function getLawFirmBId() {
        return $this->lawFirmB;
    }

    public function setLawFirmB($loginID) {
        $this->updateField('law_firm_b', $loginID);
    }

    public function setAgentB($loginID) {
        $this->updateField('agent_b', $loginID);
    }

    public function waitingForLawFirmB() {
        return $this->agentB === 0;// && Session::getAccount() !== $this->getLawFirmBId();
    }

    public function hasBeenOpened() {
        return $this->lawFirmA > 0 && $this->lawFirmB > 0;
    }

    public function hasNotBeenOpened() {
        return !$this->hasBeenOpened();
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
            $this->lawFirmA  = (int) $dispute['law_firm_a'];
            $this->lawFirmB  = (int) $dispute['law_firm_b'];
            $this->agentA    = (int) $dispute['agent_a'];
            $this->agentB    = (int) $dispute['agent_b'];
            $this->title     = $dispute['title'];
        }
    }

    public static function getAllDisputesConcerning($loginID) {
        $disputes = array();
        $disputesDetails = Database::instance()->exec('SELECT dispute_id FROM disputes WHERE law_firm_a = :login_id OR agent_a = :login_id OR law_firm_b = :login_id OR agent_b = :login_id ORDER BY dispute_id DESC', array(':login_id' => $loginID));
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
        // required fields
        $lawFirmA = (int) Utils::getValue($details, 'law_firm_a');
        $agentA   = (int) Utils::getValue($details, 'agent_a');
        $type     = Utils::getValue($details, 'type');
        $title    = Utils::getValue($details, 'title');

        Dispute::ensureCorrectAccountTypes(array(
            'law_firm_a' => $lawFirmA,
            'agent_a'    => $agentA
        ));

        $db = Database::instance();
        $db->begin();
        $db->exec(
            'INSERT INTO disputes (dispute_id, law_firm_a, agent_a, type, title)
             VALUES (NULL, :law_firm_a, :agent_a, :type, :title)', array(
            ':law_firm_a' => $lawFirmA,
            ':agent_a'    => $agentA,
            ':type'       => $type,
            ':title'      => $title
        ));
        $newDispute = $db->exec(
            'SELECT * FROM disputes ORDER BY dispute_id DESC LIMIT 1'
        )[0];
        
        // sanity check
        if ((int)$newDispute['law_firm_a'] !== $lawFirmA ||
            (int)$newDispute['agent_a']    !== $agentA   ||
            $newDispute['type']            !== $type     ||
            $newDispute['title']           !== $title) {
            throw new Exception("There was a problem creating your Dispute.");
        }
        else {
            $db->commit();
            return new Dispute((int) $newDispute['dispute_id']);
        }
    }

    public static function ensureCorrectAccountTypes($accountTypes) {
        $correctAccountTypes = 
            AccountDetails::getAccountById($accountTypes['law_firm_a']) instanceof LawFirm && 
            AccountDetails::getAccountById($accountTypes['agent_a'])    instanceof Agent;

        if (!$correctAccountTypes) {
            throw new Exception('Invalid account types were set.');
        }
    }
}