<?php

class Dispute {

    function __construct($disputeID) {
        $this->setVariables($disputeID);
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

    public function setLawFirmB($loginID) {
        $this->updateField('law_firm_b', $loginID);
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

    public function setAgentB($loginID) {
        $this->updateField('agent_b', $loginID);
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

    public function waitingForLawFirmB() {
        return $this->agentB === 0 && Session::getAccount() !== $this->getLawFirmBId();
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

    public function hasNotBeenOpened() {
        return !$this->hasBeenOpened();
    }

    public function hasBeenOpened() {
        return $this->lawFirmA > 0 && $this->lawFirmB > 0;
    }

    public function getDisputeId() {
        return $this->disputeId;
    }

    public function getTitle() {
        return $this->title;
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

    public function getUrl() {
        return '/disputes/' . $this->getDisputeId();
    }

    public static function getAllDisputesConcerning($loginID) {
        $disputes = array();
        $disputesDetails = Database::instance()->exec('SELECT dispute_id FROM disputes WHERE law_firm_a = :login_id OR agent_a = :login_id OR law_firm_b = :login_id OR agent_b = :login_id ORDER BY dispute_id DESC', array(':login_id' => $loginID));
        foreach($disputesDetails as $dispute) {
            $disputes[] = new Dispute($dispute['dispute_id']);
        }
        return $disputes;
    }

    public static function create($details) {
        $lawFirmA = (int) Register::getValue($details, 'law_firm_a');
        $agentA   = (int) Register::getValue($details, 'agent_a');
        $type     = Register::getValue($details, 'type');
        $title    = Register::getValue($details, 'title');

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

}