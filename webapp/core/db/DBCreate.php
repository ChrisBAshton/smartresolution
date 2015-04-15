<?php
// @TODO - some create methods return the ID, others return the object, others return nothing!
// Let's standardise the create() API.





class DBCreate {

    /**
     * Creates an entry in the database representing a proposal of using a Mediation Centre to mediate the dispute.
     * @param  array    $params                The details of the offer.
     *         Dispute  $params['dispute']     The Dispute object to make the proposal against.
     *         Agent    $params['proposed_by'] The Agent object representing the Agent who made the proposal.
     */
    public static function mediationCentreOffer($params) {
        DBCreate::mediationEntityOffer($params, 'mediation_centre');
    }

    /**
     * Creates an entry in the database representing a proposal of using a Mediator to mediate the dispute.
     * @param  array    $params                The details of the offer.
     *         Dispute  $params['dispute']     The Dispute object to make the proposal against.
     *         Agent    $params['proposed_by'] The Agent object representing the Agent who made the proposal.
     */
    public static function mediatorOffer($params) {
        DBCreate::mediationEntityOffer($params, 'mediator');
    }

    /**
     * Creates an entry in the database representing a proposal of using a Mediation Centre to mediate the dispute.
     * Also creates a notification for the opposing party.
     *
     * @param  array $params The details of the offer.
     * @see DBCreate::mediationCentreOffer for a detailed description of parameters.
     * @param  string $type The type of offer: either 'mediation_centre' or 'mediator'
     */
    public static function mediationEntityOffer($params, $type) {
        $dispute         = $params['dispute'];
        $proposedBy      = $params['proposed_by'];
        $mediationEntity = $params[$type];

        if (!$dispute || !$proposedBy || !$mediationEntity) {
            throw new Exception('Missing key fields.');
        }

        Database::instance()->exec(
            'INSERT INTO mediation_offers (dispute_id, type, proposer, proposed_id)
            VALUES (:dispute_id, :type, :proposer, :proposed_id)',
            array(
                ':dispute_id'  => $dispute->getDisputeId(),
                ':type'        => $type,
                ':proposer'    => $proposedBy->getLoginId(),
                ':proposed_id' => $mediationEntity->getLoginId()
            )
        );

        DBCreate::notification(array(
            'recipient_id' => $dispute->getOpposingPartyId($proposedBy->getLoginId()),
            'message'      => 'Mediation has been proposed.',
            'url'          => $dispute->getUrl() . '/mediation'
        ));
    }

    /**
     * Creates an entry in the database representing a piece of uploaded evidence.
     * @param  array   $params              The evidence parameters.
     *         Dispute $params['dispute']   The Dispute object to make the proposal against.
     *         Agent   $params['uploader']  The account who uploaded the evidence.
     *         string  $params['filepath']  The filepath of the uploaded evidence.
     * @return int  The ID of the piece of uploaded evidence.
     */
    public static function evidence($params) {
        $disputeID  = $params['dispute']->getDisputeId();
        $uploaderID = $params['uploader']->getLoginId();
        $filepath   = $params['filepath'];

        if (!$disputeID || !$uploaderID || !$filepath) {
            throw new Exception('Missing key evidence information.');
        }

        Database::instance()->exec(
            'INSERT INTO evidence (evidence_id, dispute_id, uploader_id, filepath) VALUES (NULL, :dispute_id, :uploader_id, :filepath)',
            array(
                ':dispute_id'  => $disputeID,
                ':uploader_id' => $uploaderID,
                ':filepath'    => $filepath,
            )
        );

        return DBQuery::getLatestId('evidence', 'evidence_id');
    }

    /**
     * Creates a new Dispute, saving it to the database.
     *
     * @param  array $details array of details to populate the database with.
     * @return Dispute        The Dispute object associated with the new entry.
     */
    public static function dispute($details) {
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
        $partyID = DBCreate::disputeParty($lawFirmA, $agentA, $summary);
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
                DBCreate::notification(array(
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
     * @return int                  The ID of the created party.
     */
    public static function disputeParty($organisationId, $individualId = NULL, $summary = NULL) {
        Database::instance()->exec(
            'INSERT INTO dispute_parties (party_id, organisation_id, individual_id, summary)
             VALUES (NULL, :organisation_id, :individual_id, :summary)', array(
            ':organisation_id' => $organisationId,
            ':individual_id'   => $individualId,
            ':summary'         => $summary
        ));

        return DBQuery::getLatestId('dispute_parties', 'party_id');
    }

    /**
     * Creates a new lifespan proposal.
     * @param  array $params Parameters outlining start and end dates, etc.
     * @return Lifespan      The newly created lifespan.
     */
    public static function lifespan($params, $allowDatesInThePast = false) {
        $disputeID  = Utils::getValue($params, 'dispute_id');
        $proposer   = Utils::getValue($params, 'proposer');
        $validUntil = Utils::getValue($params, 'valid_until');
        $startTime  = Utils::getValue($params, 'start_time');
        $endTime    = Utils::getValue($params, 'end_time');

        $db = Database::instance();
        $db->begin();
        $db->exec(
            'INSERT INTO lifespans (dispute_id, proposer, valid_until, start_time, end_time)
             VALUES (:dispute_id, :proposer, :valid_until, :start_time, :end_time)', array(
            ':dispute_id'  => $disputeID,
            ':proposer'    => $proposer,
            ':valid_until' => $validUntil,
            ':start_time'  => $startTime,
            ':end_time'    => $endTime
        ));

        $lifespanID = DBQuery::getLatestId('lifespans', 'lifespan_id');

        try {
            $lifespan = new Lifespan($lifespanID, !$allowDatesInThePast);
            // if no exception is raised, safe to commit transaction to database
            $db->commit();

            $dispute = new Dispute($disputeID);
            DBCreate::notification(array(
                'recipient_id' => $dispute->getOpposingPartyId($proposer),
                'message'      => 'A lifespan offer has been made. You have until ' . prettyTime($validUntil) . ' to accept or deny the offer.',
                'url'          => $dispute->getUrl() . '/lifespan'
            ));

            return $lifespan;
        }
        catch(Exception $e) {
            $db->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Creates a notification.
     * @param  array  $options                  The notification options.
     *         int    $options['recipient_id']  The ID of the recipient of the notification.
     *         string $option['message']        The notification message.
     *         string $option['url']            The notification's associated URL.
     * @return Notification                     The newly-created notification.
     */
    public static function notification($options) {

        $recipientId = Utils::getValue($options, 'recipient_id');
        $message     = Utils::getValue($options, 'message');
        $url         = Utils::getValue($options, 'url');

        Database::instance()->exec('INSERT INTO notifications (recipient_id, message, url) VALUES (:recipient_id, :message, :url)',
            array(
                ':recipient_id' => $recipientId,
                ':message'      => $message,
                ':url'          => $url,
            )
        );

        $notificationID = DBQuery::getLatestId('notifications', 'notification_id');
        return new Notification($notificationID);
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
    public static function message($params) {
        $disputeID   = Utils::getValue($params, 'dispute_id');
        $authorID    = Utils::getValue($params, 'author_id');
        $message     = Utils::getValue($params, 'message');
        $recipientID = Utils::getValue($params, 'recipient_id', false);

        Database::instance()->exec('INSERT INTO messages (dispute_id, author_id, recipient_id, message, timestamp) VALUES (:dispute_id, :author_id, :recipient_id, :message, :timestamp)',
            array(
                ':dispute_id'   => $disputeID,
                ':author_id'    => $authorID,
                ':recipient_id' => $recipientID ? $recipientID : NULL,
                ':message'      => $message,
                ':timestamp'    => time()
            )
        );

        $messageID = DBQuery::getLatestId('messages', 'message_id');
        return new Message($messageID);
    }


    public static function organisation($orgObject) {
        $type        = Utils::getValue($orgObject, 'type');
        $name        = Utils::getValue($orgObject, 'name', '');
        $description = Utils::getValue($orgObject, 'description', '');

        Database::instance()->begin();
        $login_id = DBCreate::dbAccount($orgObject);
        Database::instance()->exec('INSERT INTO organisations (login_id, type, name, description) VALUES (:login_id, :type, :name, :description)', array(
            ':login_id'    => $login_id,
            ':type'        => $type,
            ':name'        => $name,
            ':description' => $description
        ));
        Database::instance()->commit();

        return ($type === 'law_firm') ? new LawFirm($login_id) : new MediationCentre($login_id);
    }

    public static function individual($individualObject) {
        $type     = Utils::getValue($individualObject, 'type');
        $orgId    = Utils::getValue($individualObject, 'organisation_id');
        $forename = Utils::getValue($individualObject, 'forename', '');
        $surname  = Utils::getValue($individualObject, 'surname',  '');

        Database::instance()->begin();
        $login_id = DBCreate::dbAccount($individualObject);
        Database::instance()->exec('INSERT INTO individuals (login_id, organisation_id, type, forename, surname) VALUES (:login_id, :organisation_id, :type, :forename, :surname)', array(
            ':login_id'        => $login_id,
            ':organisation_id' => $orgId,
            ':type'            => $type,
            ':forename'        => $forename,
            ':surname'         => $surname
        ));
        Database::instance()->commit();

        return ($type === 'agent') ? new Agent($login_id) : new Mediator($login_id);
    }

    public static function admin($params) {
        Database::instance()->begin();
        $login_id = DBCreate::dbAccount($params);
        Database::instance()->exec('INSERT INTO administrators (login_id) VALUES (:login_id)', array(
            ':login_id' => $login_id,
        ));
        Database::instance()->commit();
    }

    /**
     * Stores account details in the database.
     *
     * @param  array $object An array of registration values, including email and password.
     * @return int           The login ID associated with the newly registered account.
     */
    public static function dbAccount($object) {
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
}