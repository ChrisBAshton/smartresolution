<?php

/**
 * Database connector class which creates new rows in the database.
 */
class DBCreate extends Prefab {

    /**
     * Creates a new admin in the database.
     * @param  array $params Admin details.
     *         int   $params['login_id'] Login ID of the admin.
     * @return Admin The newly created admin.
     */
    public function admin($params) {
        Database::instance()->begin();
        $login_id = $this->dbAccount($params);
        Database::instance()->exec('INSERT INTO administrators (login_id) VALUES (:login_id)', array(
            ':login_id' => $login_id,
        ));
        Database::instance()->commit();
        return DBGet::instance()->account($login_id);
    }

    /**
     * Creates a new dispute in the database.
     * @param  array  $details Dispute details.
     *         int    $details['law_firm_a'] Login ID of the law firm creating the dispute.
     *         string $details['type']       The dispute type ('Other', 'Maritime Collision', etc).
     *         string $details['title']      The title of the dispute.
     *         int    $details['agent_a']    The login ID of the agent assigned to the dispute.
     *         string $details['summary']    Party A's summary of the dispute.
     * @return Dispute The newly created dispute.
     */
    public function dispute($details) {
        $lawFirmA = (int) Utils::instance()->getValue($details, 'law_firm_a');
        $type     = Utils::instance()->getValue($details, 'type');
        $title    = Utils::instance()->getValue($details, 'title');
        $agentA   = isset($details['agent_a']) ? $details['agent_a'] : NULL;
        $summary  = isset($details['summary']) ? $details['summary'] : NULL;

        DBQuery::instance()->ensureCorrectAccountTypes(array(
            'law_firm' => $lawFirmA,
            'agent'    => $agentA
        ));

        $db = Database::instance();
        $db->begin();
        $party = $this->disputeParty(array(
            'organisation_id' => $lawFirmA,
            'individual_id'   => $agentA,
            'summary'         => $summary
        ));
        $partyID = $party->getPartyId();
        $db->exec(
            'INSERT INTO disputes (dispute_id, party_a, type, title)
             VALUES (NULL, :party_a, :type, :title)', array(
            ':party_a'    => $partyID,
            ':type'       => $type,
            ':title'      => $title
        ));
        $newDisputeID = DBQuery::instance()->getLatestId('disputes', 'dispute_id');

        $db->commit();
        $dispute = DBGet::instance()->dispute((int) $newDisputeID);

        if ($agentA) {
            $this->notification(array(
                'recipient_id' => $agentA,
                'message'      => 'A new dispute has been assigned to you.',
                'url'          => $dispute->getUrl()
            ));
        }

        return $dispute;
    }

    /**
     * Creates a new dispute party in the database.
     * @param  array  $params Dispute party details.
     *         int    $params['organisation_id'] Login ID of the party's law firm.
     *         int    $params['individual_id']   Login ID of the party's agent.
     *         string $params['summary']         Party's summary of the dispute.
     * @return DisputeParty The newly created dispute party.
     */
    public function disputeParty($params) {
        $params = Utils::instance()->requiredParams(array(
            'organisation_id' => true,
            'individual_id'   => false,
            'summary'         => false
        ), $params);

        $this->insertRow('dispute_parties', $params);

        $partyID = DBQuery::instance()->getLatestId('dispute_parties', 'party_id');

        return DBGet::instance()->disputeParty($partyID);
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
        $params = Utils::instance()->requiredParams(array(
            'dispute_id'  => true,
            'uploader_id' => true,
            'filepath'    => true
        ), $params);
        $this->insertRow('evidence', $params);
        $latestID = DBQuery::instance()->getLatestId('evidence', 'evidence_id');
        return DBGet::instance()->evidence($latestID);
    }

    /**
     * Creates a new individual in the database.
     * @param  array  $params             Individual details.
     *         string $params['type']     Type of individual ('agent', 'mediator').
     *         string $params['forename'] Forename.
     *         string $params['surname']  Surname.
     * @return Account The newly created Individual.
     */
    public function individual($params) {
        Database::instance()->begin();
        $login_id = $this->dbAccount($params);
        $params = Utils::instance()->requiredParams(array(
            'type'            => true,
            'organisation_id' => true,
            'forename'        => false,
            'surname'         => false
        ), $params);

        $params['login_id'] = $login_id;
        $this->insertRow('individuals', $params);
        Database::instance()->commit();
        return DBGet::instance()->account($login_id);
    }

    /**
     * Creates a new lifespan proposal.
     * @param  array $params Parameters outlining start and end dates, etc.
     * @return Lifespan      The newly created lifespan.
     */
    public function lifespan($params, $allowDatesInThePast = false) {
        $params = Utils::instance()->requiredParams(array(
            'dispute_id'  => true,
            'proposer'    => true,
            'valid_until' => true,
            'start_time'  => true,
            'end_time'    => true
        ), $params);

        $db = Database::instance();
        $db->begin();
        $this->insertRow('lifespans', $params);
        $lifespanID = DBQuery::instance()->getLatestId('lifespans', 'lifespan_id');

        try {
            $lifespan = new Lifespan(DBGet::instance()->lifespanDetails($lifespanID), !$allowDatesInThePast);
            // if no exception is raised, safe to commit transaction to database
            $db->commit();

            $dispute = DBGet::instance()->dispute($params['dispute_id']);

            $this->notification(array(
                'recipient_id' => $dispute->getOpposingPartyId($params['proposer']),
                'message'      => 'A lifespan offer has been made. You have until ' . prettyTime($params['valid_until']) . ' to accept or deny the offer.',
                'url'          => $dispute->getUrl() . '/lifespan'
            ));

            return $lifespan;
        }
        catch(Exception $e) {
            Utils::instance()->throwException($e->getMessage());
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
        $params = Utils::instance()->requiredParams(array(
            'dispute_id'   => true,
            'author_id'    => true,
            'message'      => true,
            'recipient_id' => false
        ), $params);
        $params['timestamp'] = time();

        $this->insertRow('messages', $params);

        $messageID = DBQuery::instance()->getLatestId('messages', 'message_id');
        return DBGet::instance()->message($messageID);
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
        $params = Utils::instance()->requiredParams(array(
            'recipient_id' => true,
            'message'      => true,
            'url'          => true
        ), $options);

        $this->insertRow('notifications', $params);
        $notificationID = DBQuery::instance()->getLatestId('notifications', 'notification_id');
        return new Notification($notificationID);
    }

    /**
     * Creates a new organisation in the database.
     * @param  array  $orgObject                Organisation details.
     *         string $orgObject['type']        Type of organisation ('law_firm', 'mediation_centre').
     *         string $orgObject['name']        Organisation name
     *         string $orgObject['description'] Organisation description.
     * @return Account The newly created Organisation.
     */
    public function organisation($orgObject) {
        $params = Utils::instance()->requiredParams(array(
            'type'        => true,
            'name'        => false,
            'description' => false
        ), $orgObject);

        Database::instance()->begin();
        $login_id = $this->dbAccount($orgObject);
        $params['login_id'] = $login_id;
        $this->insertRow('organisations', $params);
        Database::instance()->commit();

        return DBGet::instance()->account($login_id);
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
            Utils::instance()->throwException("The minimum required to register is an email and password!");
        }

        $loginID = DBQuery::instance()->emailToId($object['email']);
        if (DBGet::instance()->account($loginID)) {
            Utils::instance()->throwException("An account is already registered to that email address.");
        }

        $crypt = \Bcrypt::instance();
        Database::instance()->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
            ':email'    => $object['email'],
            ':password' => $crypt->hash($object['password'])
        ));

        $login_id = DBQuery::instance()->emailToId($object['email']);
        if (!$login_id) {
            Utils::instance()->throwException("Could not retrieve login_id. Abort.");
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
        $params = Utils::instance()->requiredParams(array(
            'dispute_id'  => true,
            'proposer_id' => true,
            'proposed_id' => true
        ), $obj);
        $params['type'] = $type;

        if (!is_int($params['dispute_id']) || !is_int($params['proposer_id']) || !is_int($params['proposed_id'])) {
            Utils::instance()->throwException('Incorrect type passed to mediation creation!');
        }

        $this->insertRow('mediation_offers', $params);

        $dispute = DBGet::instance()->dispute($params['dispute_id']);

        $this->notification(array(
            'recipient_id' => $dispute->getOpposingPartyId($params['proposer_id']),
            'message'      => 'Mediation has been proposed.',
            'url'          => $dispute->getUrl() . '/mediation'
        ));
    }

    /**
     * Inserts a row of data into the given table.
     * @param  string $tableName    Name of the table.
     * @param  array  $columnValues Associative array of columns and values.
     */
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