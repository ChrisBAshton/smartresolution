<?php

class DBCreate extends Prefab {

    public function admin($params) {
        Database::instance()->begin();
        $login_id = $this->dbAccount($params);
        Database::instance()->exec('INSERT INTO administrators (login_id) VALUES (:login_id)', array(
            ':login_id' => $login_id,
        ));
        Database::instance()->commit();
        return DBAccount::getAccountById($login_id);
    }

    /**
     * Creates a new Dispute, saving it to the database.
     *
     * @param  array $details array of details to populate the database with.
     * @return Dispute        The Dispute object associated with the new entry.
     */
    public function dispute($details) {
        $lawFirmA = (int) Utils::getValue($details, 'law_firm_a');
        $type     = Utils::getValue($details, 'type');
        $title    = Utils::getValue($details, 'title');
        $agentA   = isset($details['agent_a']) ? $details['agent_a'] : NULL;
        $summary  = isset($details['summary']) ? $details['summary'] : NULL;

        DBQuery::ensureCorrectAccountTypes(array(
            'law_firm' => $lawFirmA,
            'agent'    => $agentA
        ));

        $db = Database::instance();
        $db->begin();
        $party = $this->disputeParty($lawFirmA, $agentA, $summary);
        $partyID = $party->getPartyId();
        $db->exec(
            'INSERT INTO disputes (dispute_id, party_a, type, title)
             VALUES (NULL, :party_a, :type, :title)', array(
            ':party_a'    => $partyID,
            ':type'       => $type,
            ':title'      => $title
        ));
        $newDispute = DBQuery::getLatestRow('disputes', 'dispute_id');

        // sanity check
        if ((int)$newDispute['party_a'] !== $partyID ||
            $newDispute['type']         !== $type    ||
            $newDispute['title']        !== $title) {
            throw new Exception("There was a problem creating your Dispute.");
        }
        else {
            $db->commit();
            $dispute = new Dispute((int) $newDispute['dispute_id']);

            if ($agentA) {
                $this->notification(array(
                    'recipient_id' => $agentA,
                    'message'      => 'A new dispute has been assigned to you.',
                    'url'          => $dispute->getUrl()
                ));
            }

            return $dispute;
        }
    }

    /**
     * Creates a dispute party entry.
     *
     * @param  int $organisationId  The ID of the party's organisation.
     * @param  int $individualId    (Optional) The ID of the party's individual.
     * @param  string $summary      The party's summary of the dispute.
     * @return DisputeParty         The newly created Dispute Party.
     */
    public function disputeParty($organisationId, $individualId = NULL, $summary = NULL) {

        Database::instance()->exec(
            'INSERT INTO dispute_parties (party_id, organisation_id, individual_id, summary)
             VALUES (NULL, :organisation_id, :individual_id, :summary)', array(
            ':organisation_id' => $organisationId,
            ':individual_id'   => $individualId,
            ':summary'         => $summary
        ));

        $partyID = DBQuery::getLatestId('dispute_parties', 'party_id');

        return new DisputeParty($partyID);
    }

    /**
     * Creates an entry in the database representing a piece of uploaded evidence.
     * @param  array  $params                 The evidence parameters.
     *         int    $params['dispute_id']   The ID of the dispute to make the proposal against.
     *         int    $params['uploader_id']  The ID of the account who uploaded the evidence.
     *         string $params['filepath']     The filepath of the uploaded evidence.
     * @return Evidence                       The object representing the piece of uploaded evidence.
     */
    public function evidence($params) {
        $params = Utils::requiredParams(array(
            'dispute_id'  => true,
            'uploader_id' => true,
            'filepath'    => true
        ), $params);
        $this->insertRow('evidence', $params);
        $latestID = DBQuery::getLatestId('evidence', 'evidence_id');
        return new Evidence($latestID);
    }

    public function individual($individualObject) {
        Database::instance()->begin();
        $login_id = $this->dbAccount($individualObject);
        $params = Utils::requiredParams(array(
            'type'            => true,
            'organisation_id' => true,
            'forename'        => false,
            'surname'         => false
        ), $individualObject);

        $params['login_id'] = $login_id;
        $this->insertRow('individuals', $params);
        Database::instance()->commit();
        return DBAccount::getAccountById($login_id);
    }

    /**
     * Creates a new lifespan proposal.
     * @param  array $params Parameters outlining start and end dates, etc.
     * @return Lifespan      The newly created lifespan.
     */
    public function lifespan($params, $allowDatesInThePast = false) {
        $params = Utils::requiredParams(array(
            'dispute_id'  => true,
            'proposer'    => true,
            'valid_until' => true,
            'start_time'  => true,
            'end_time'    => true
        ), $params);

        $db = Database::instance();
        $db->begin();
        $this->insertRow('lifespans', $params);
        $lifespanID = DBQuery::getLatestId('lifespans', 'lifespan_id');

        try {
            $lifespan = new Lifespan($lifespanID, !$allowDatesInThePast);
            // if no exception is raised, safe to commit transaction to database
            $db->commit();

            $dispute = new Dispute($params['dispute_id']);

            $this->notification(array(
                'recipient_id' => $dispute->getOpposingPartyId($params['proposer']),
                'message'      => 'A lifespan offer has been made. You have until ' . prettyTime($params['valid_until']) . ' to accept or deny the offer.',
                'url'          => $dispute->getUrl() . '/lifespan'
            ));

            return $lifespan;
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Creates a message.
     * @param  array  $params                  The message options.
     *         int    $params['dispute_id']    The ID of the dispute that the message is connected to.
     *         int    $params['author_id']     The ID of the author of the message.
     *         int    $params['recipient_id']  (Optional) The ID of the recipient of the message. This is only set when the message is strictly private between two parties, e.g. Agent A and Mediator M. It is not set for normal dispute communication between agents A and B.
     *         string $params['message']       The message content.
     * @return Message                         The newly-created message.
     */
    public function message($params) {
        $params = Utils::requiredParams(array(
            'dispute_id'   => true,
            'author_id'    => true,
            'message'      => true,
            'recipient_id' => false
        ), $params);
        $params['timestamp'] = time();

        $this->insertRow('messages', $params);

        $messageID = DBQuery::getLatestId('messages', 'message_id');
        return new Message($messageID);
    }

    /**
     * Creates a notification.
     * @param  array  $options                  The notification options.
     *         int    $options['recipient_id']  The ID of the recipient of the notification.
     *         string $option['message']        The notification message.
     *         string $option['url']            The notification's associated URL.
     * @return Notification                     The newly-created notification.
     */
    public function notification($options) {
        $params = Utils::requiredParams(array(
            'recipient_id' => true,
            'message'      => true,
            'url'          => true
        ), $options);

        $this->insertRow('notifications', $params);
        $notificationID = DBQuery::getLatestId('notifications', 'notification_id');
        return new Notification($notificationID);
    }


    public function organisation($orgObject) {
        $params = Utils::requiredParams(array(
            'type'        => true,
            'name'        => false,
            'description' => false
        ), $orgObject);

        Database::instance()->begin();
        $login_id = $this->dbAccount($orgObject);
        $params['login_id'] = $login_id;
        $this->insertRow('organisations', $params);
        Database::instance()->commit();

        return DBAccount::getAccountById($login_id);
    }

// --------------------------------------------------------------------------- the functions from this point onwards do not return an object like the rest of the createX API. Maybe these should be extracted?? Or made private?? (Whereas the above are public.)


    /**
     * Stores account details in the database.
     *
     * @param  array $object An array of registration values, including email and password.
     * @return int           The login ID associated with the newly registered account.
     */
    public function dbAccount($object) {
        if (!isset($object['email']) || !isset($object['password'])) {
            throw new Exception("The minimum required to register is an email and password!");
        }

        if (DBAccount::getAccountByEmail($object['email'])) {
            throw new Exception("An account is already registered to that email address.");
        }

        $crypt = \Bcrypt::instance();
        Database::instance()->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
            ':email'    => $object['email'],
            ':password' => $crypt->hash($object['password'])
        ));

        $login_id = DBAccount::emailToId($object['email']);
        if (!$login_id) {
            throw new Exception("Could not retrieve login_id. Abort.");
        }
        return $login_id;
    }

    /**
     * Creates an entry in the database representing a proposal of using a Mediation Centre to mediate the dispute.
     * @param  array    $params                The details of the offer.
     *         Dispute  $params['dispute']     The Dispute object to make the proposal against.
     *         Agent    $params['proposed_by'] The Agent object representing the Agent who made the proposal.
     */
    public function mediationCentreOffer($params) {
        $this->_mediationEntityOffer($params, 'mediation_centre');
    }

    /**
     * Creates an entry in the database representing a proposal of using a Mediator to mediate the dispute.
     * @param  array    $params                The details of the offer.
     *         Dispute  $params['dispute']     The Dispute object to make the proposal against.
     *         Agent    $params['proposed_by'] The Agent object representing the Agent who made the proposal.
     */
    public function mediatorOffer($params) {
        $this->_mediationEntityOffer($params, 'mediator');
    }

    /**
     * Creates an entry in the database representing a proposal of using a Mediation Centre to mediate the dispute.
     * Also creates a notification for the opposing party.
     *
     * @param  array $params The details of the offer.
     * @see $this->mediationCentreOffer for a detailed description of parameters.
     * @param  string $type The type of offer: either 'mediation_centre' or 'mediator'
     */
    private function _mediationEntityOffer($obj, $type) {
        $params = Utils::requiredParams(array(
            'dispute_id'  => true,
            'proposer_id' => true,
            'proposed_id' => true
        ), $obj);
        $params['type'] = $type;

        $this->insertRow('mediation_offers', $params);

        $dispute = new Dispute($params['dispute_id']);

        $this->notification(array(
            'recipient_id' => $dispute->getOpposingPartyId($params['proposer_id']),
            'message'      => 'Mediation has been proposed.',
            'url'          => $dispute->getUrl() . '/mediation'
        ));
    }

    private function insertRow($tableName, $columnValues) {
        $columns = array();
        $values  = array();
        foreach($columnValues as $columnName => $value) {
            $columns[] = $columnName;
            $values[':' . $columnName] = $value;
        }

        $query = 'INSERT INTO ' . $tableName . ' (' . implode(', ', $columns) . ')
                  VALUES (' . implode(', ', array_keys($values)) . ')';

        Database::instance()->exec($query, $values);
    }
}