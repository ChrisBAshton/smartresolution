<?php

class Dispute {

    function __construct($disputeID) {
        $dispute = Database::instance()->exec('SELECT * FROM disputes WHERE dispute_id = :dispute_id', array(':dispute_id' => $disputeID));
        if (count($dispute) !== 1) {
            throw new Exception("Something went wrong. Please contact an admin.");
        }
        else {
            $dispute = $dispute[0];
            $this->title = $dispute['title'];
        }
    }

    public function getTitle() {
        return $this->title;
    }

    public function canBeViewedBy($loginID) {
        return true; // @TODO
    }

    public static function getAllDisputesConcerning($loginID) {
        $disputes = array();
        $disputesDetails = Database::instance()->exec('SELECT dispute_id FROM disputes WHERE law_firm_a = :login_id OR agent_a = :login_id OR law_firm_b = :login_id OR agent_b = :login_id', array(':login_id' => $loginID));
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
            return (int) $newDispute['dispute_id'];
        }
    }

}