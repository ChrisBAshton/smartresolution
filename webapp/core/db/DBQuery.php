<?php

class DBQuery extends Prefab {

    public function getIndividuals($organisationID) {
        $individuals = array();

        $individualsDetails = Database::instance()->exec(
            'SELECT individuals.login_id FROM individuals INNER JOIN account_details ON individuals.login_id = account_details.login_id WHERE organisation_id = :organisation_id',
            array(':organisation_id' => $organisationID)
        );

        foreach($individualsDetails as $individual) {
            $individuals[] = DBAccount::instance()->getAccountById($individual['login_id']);
        }

        return $individuals;
    }

    public function getEvidences($disputeID) {
        $evidences = array();
        $evidenceDetails = Database::instance()->exec(
            'SELECT evidence_id FROM evidence WHERE dispute_id = :dispute_id ORDER BY evidence_id DESC',
            array(':dispute_id' => $disputeID)
        );

        foreach($evidenceDetails as $evidence) {
            $evidences[] = DBGet::instance()->evidence((int) $evidence['evidence_id']);
        }

        return $evidences;
    }

    // @TODO - move to DBUpdate
    public function updateDisputePartyB($partyID, $disputeID) {
        Database::instance()->exec(
            'UPDATE disputes SET party_b = :party_id WHERE dispute_id = :dispute_id',
            array(
                ':party_id'   => $partyID,
                ':dispute_id' => $disputeID
            )
        );
    }

    /**
     * Gets organisations as an array.
     * @param  array  $params           Parameters:
     *         string $params['type']   Organisation type ('law_firm' / 'mediation_centre')
     *         int    $params['except'] Integer ID of an account to remove from the results.
     * @return array<Organisation>      An array of matching organisations of the correct subclass type (LawFirm or MediationCentre)
     */
    public function getOrganisations($params) {
        $type   = Utils::instance()->getValue($params, 'type');
        $class  = $type === 'law_firm' ? 'LawFirm' : 'MediationCentre';
        $except = Utils::instance()->getValue($params, 'except', false);

        $organisations = array();
        $orgDetails = Database::instance()->exec(
            'SELECT * FROM organisations INNER JOIN account_details ON organisations.login_id = account_details.login_id  WHERE type = :type AND organisations.login_id != :except ORDER BY name DESC',
            array(
                ':type'   => $type,
                ':except' => $except
            )
        );

        foreach($orgDetails as $details) {
            $organisations[] = new $class($details);
        }

        return $organisations;
    }

    /**
     * Gets all of the disputes associated with an account. For Agents, this would be disputes that they have created or been assigned to. For mediators, this would be disputes that are in mediation and that they are assigned to, and so on.
     * @param  Account $account The account to get the disputes from.
     * @return array<Dispute>                   The array of associated disputes.
     */
    public function getAllDisputes ($account) {
        $disputes = array();

        if ($account instanceof LawFirm || $account instanceof Agent) {
            $disputesDetails = Database::instance()->exec(
                'SELECT dispute_id FROM disputes

                INNER JOIN dispute_parties
                ON disputes.party_a     = dispute_parties.party_id
                OR disputes.party_b     = dispute_parties.party_id

                WHERE organisation_id = :login_id OR individual_id = :login_id
                ORDER BY party_id DESC',
                array(':login_id' => $account->getLoginId())
            );
        }
        else {
            $disputesDetails = Database::instance()->exec(
                'SELECT dispute_id FROM mediation_offers
                WHERE proposed_id = :login_id
                AND status = "accepted"
                ORDER BY mediation_offer_id DESC',
                array(':login_id' => $account->getLoginId())
            );
        }

        foreach($disputesDetails as $dispute) {
            $disputes[] = DBGet::instance()->dispute($dispute['dispute_id']);
        }
        return $disputes;
    }

    public function retrieveDisputeMessages($disputeID) {
        $messages = array();

        $messageDetails = Database::instance()->exec(
            'SELECT message_id FROM messages WHERE dispute_id = :dispute_id AND recipient_id is null ORDER BY message_id DESC',
            array(':dispute_id' => $disputeID)
        );

        foreach($messageDetails as $id) {
            $message = DBGet::instance()->message($id['message_id']);
            array_push($messages, $message);
        }

        return $messages;
    }

    public function retrieveMediationMessages($disputeID, $individualA, $individualB) {
        $messages = array();

        $messageDetails = Database::instance()->exec(
            'SELECT message_id FROM messages
            WHERE
            (author_id = :individual_a AND recipient_id = :individual_b AND dispute_id = :dispute_id)
            OR
            (author_id = :individual_b AND recipient_id = :individual_a AND dispute_id = :dispute_id)
            ORDER BY message_id DESC',
            array(
                ':dispute_id'   => $disputeID,
                ':individual_a' => $individualA,
                ':individual_b' => $individualB,
            )
        );

        foreach($messageDetails as $id) {
            $message = DBGet::instance()->message($id['message_id']);
            array_push($messages, $message);
        }

        return $messages;
    }

    public function getNotificationsForLoginId($loginId) {
        $notifications = array();

        $notificationsDetails = Database::instance()->exec('SELECT notification_id FROM notifications WHERE recipient_id = :login_id AND read = "false" ORDER BY notification_id DESC',
            array(':login_id' => $loginId)
        );

        foreach ($notificationsDetails as $id) {
            $notification = DBGet::instance()->notification($id['notification_id']);
            $notifications[] = $notification;
        }

        return $notifications;
    }

    /**
     * Returns the latest ID in the database from table name $tableName, ordered by primary key $idName (DESC).
     *
     * @param  string $tableName Name of the table.
     * @param  string $idName    Primary key of the table.
     * @return int               The primary key of the latest database entry.
     */
    public function getLatestId($tableName, $idName) {
        $latestRow = $this->getLatestRow($tableName, $idName);
        return $latestRow ? (int) $latestRow[$idName] : false;
    }

    /**
     * Returns the latest row in the database from table name $tableName, ordered by primary key $idName (DESC).
     * @param  string $tableName Name of the table.
     * @param  string $idName    Primary key of the table.
     * @return array             Latest table row.
     */
    public function getLatestRow($tableName, $idName) {
        $rows = Database::instance()->exec(
            'SELECT * FROM ' . $tableName . ' ORDER BY ' . $idName . ' DESC LIMIT 1'
        );
        return (count($rows) === 1) ? $rows[0] : false;
    }

    /**
     * Ensures that the account types set for a dispute's agents/law firms etc do actually correspond to agent/law firm accounts. Essentially, this function raises an exception if the system tries to do something like set Agent A as a Mediation Centre account.
     * @param  array $accountTypes              The accounts to check.
     *         int   $accountTypes['law_firm']  (Optional) The ID of the account that should be a law firm.
     *         int   $accountTypes['agent']     (Optional) The ID of the account that should be an agent.
     */
    public function ensureCorrectAccountTypes($accountTypes) {
        $correctAccountTypes = true;
        if (isset($accountTypes['law_firm'])) {
            if (!DBAccount::instance()->getAccountById($accountTypes['law_firm']) instanceof LawFirm) {
                $correctAccountTypes = false;
            }
        }
        if (isset($accountTypes['agent'])) {
            if (!DBAccount::instance()->getAccountById($accountTypes['agent']) instanceof Agent) {
                $correctAccountTypes = false;
            }
        }

        if (!$correctAccountTypes) {
            Utils::instance()->throwException('Invalid account types were set.');
        }
    }

}