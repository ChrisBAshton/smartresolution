<?php

class DisputeDB {

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

        DisputeDB::ensureCorrectAccountTypes(array(
            'law_firm' => $lawFirmA,
            'agent'    => $agentA
        ));

        $db = Database::instance();
        $db->begin();
        $partyID = DisputeDB::createParty($lawFirmA, $agentA, $summary);
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