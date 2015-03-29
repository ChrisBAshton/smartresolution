<?php
// @TODO - some create methods return the ID, others return the object, others return nothing!
// Let's standardise the create() API.






/**
 * This is the Database Layer class - it acts as middleware between the application and the database,
 * and is mainly used for defining methods that create new rows in the database.
 */
class DBL {

    public static function createMediationCentreOffer($params) {
        DBL::createMediationEntityOffer($params, 'mediation_centre');
    }

    public static function createMediatorOffer($params) {
        DBL::createMediationEntityOffer($params, 'mediator');
    }

    public static function createMediationEntityOffer($params, $type) {
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

        DBL::createNotification(array(
            'recipient_id' => $dispute->getOpposingPartyId($proposedBy->getLoginId()),
            'message'      => 'Mediation has been proposed.',
            'url'          => $dispute->getUrl() . '/mediation'
        ));
    }

    public static function createEvidence($params) {
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

        return DBL::getLatestId('evidence', 'evidence_id');
    }

    /**
     * Creates a new Dispute, saving it to the database.
     *
     * @param  Array $details Array of details to populate the database with.
     * @return Dispute        The Dispute object associated with the new entry.
     */
    public static function createDispute($details) {
        $lawFirmA = (int) Utils::getValue($details, 'law_firm_a');
        $type     = Utils::getValue($details, 'type');
        $title    = Utils::getValue($details, 'title');
        $agentA   = isset($details['agent_a']) ? $details['agent_a'] : NULL;
        $summary  = isset($details['summary']) ? $details['summary'] : NULL;

        DBL::ensureCorrectAccountTypes(array(
            'law_firm' => $lawFirmA,
            'agent'    => $agentA
        ));

        $db = Database::instance();
        $db->begin();
        $partyID = DBL::createDisputeParty($lawFirmA, $agentA, $summary);
        $db->exec(
            'INSERT INTO disputes (dispute_id, party_a, type, title)
             VALUES (NULL, :party_a, :type, :title)', array(
            ':party_a'    => $partyID,
            ':type'       => $type,
            ':title'      => $title
        ));
        $newDispute = DBL::getLatestRow('disputes', 'dispute_id');

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
                DBL::createNotification(array(
                    'recipient_id' => $agentA,
                    'message'      => 'A new dispute has been assigned to you.',
                    'url'          => $dispute->getUrl()
                ));
            }

            return $dispute;
        }
    }

    public static function createDisputeParty($organisationId, $individualId = NULL, $summary = NULL) {
        Database::instance()->exec(
            'INSERT INTO dispute_parties (party_id, organisation_id, individual_id, summary)
             VALUES (NULL, :organisation_id, :individual_id, :summary)', array(
            ':organisation_id' => $organisationId,
            ':individual_id'   => $individualId,
            ':summary'         => $summary
        ));

        return DBL::getLatestId('dispute_parties', 'party_id');
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

    /**
     * Creates a new lifespan proposal.
     * @param  Array $params Parameters outlining start and end dates, etc.
     * @return Lifespan      The newly created lifespan.
     */
    public static function createLifespan($params, $allowDatesInThePast = false) {
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

        $lifespanID = DBL::getLatestId('lifespans', 'lifespan_id');

        try {
            $lifespan = new Lifespan($lifespanID, !$allowDatesInThePast);
            // if no exception is raised, safe to commit transaction to database
            $db->commit();

            $dispute = new Dispute($disputeID);
            DBL::createNotification(array(
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

    public static function createNotification($options) {

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

        $notificationID = DBL::getLatestId('notifications', 'notification_id');
        return new Notification($notificationID);
    }

    public static function createMessage($params) {
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

        $messageID = DBL::getLatestId('messages', 'message_id');
        return new Message($messageID);
    }


    public static function createOrganisation($orgObject) {
        $type        = Utils::getValue($orgObject, 'type');
        $name        = Utils::getValue($orgObject, 'name', '');
        $description = Utils::getValue($orgObject, 'description', '');

        Database::instance()->begin();
        $login_id = DBL::createAccountDetails($orgObject);
        Database::instance()->exec('INSERT INTO organisations (login_id, type, name, description) VALUES (:login_id, :type, :name, :description)', array(
            ':login_id'    => $login_id,
            ':type'        => $type,
            ':name'        => $name,
            ':description' => $description
        ));
        Database::instance()->commit();

        return ($type === 'law_firm') ? new LawFirm($login_id) : new MediationCentre($login_id);
    }

    public static function createIndividual($individualObject) {
        $type     = Utils::getValue($individualObject, 'type');
        $orgId    = Utils::getValue($individualObject, 'organisation_id');
        $forename = Utils::getValue($individualObject, 'forename', '');
        $surname  = Utils::getValue($individualObject, 'surname',  '');

        Database::instance()->begin();
        $login_id = DBL::createAccountDetails($individualObject);
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

    /**
     * Stores account details in the database.
     *
     * @param  Array $object An array of registration values, including email and password.
     * @return int           The login ID associated with the newly registered account.
     */
    public static function createAccountDetails($object) {
        if (!isset($object['email']) || !isset($object['password'])) {
            throw new Exception("The minimum required to register is an email and password!");
        }

        if (AccountDetails::getAccountByEmail($object['email'])) {
            throw new Exception("An account is already registered to that email address.");
        }

        $crypt = \Bcrypt::instance();
        Database::instance()->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
            ':email'    => $object['email'],
            ':password' => $crypt->hash($object['password'])
        ));

        $login_id = AccountDetails::emailToId($object['email']);
        if (!$login_id) {
            throw new Exception("Could not retrieve login_id. Abort.");
        }
        return $login_id;
    }

    /**
     * Returns the latest ID in the database from table name $tableName, ordered by primary key $idName (DESC).
     * Calls DBL::getLatestRow internally.
     *
     * @param  String $tableName Name of the table.
     * @param  String $idName    Primary key of the table.
     * @return Int               The primary key of the latest database entry.
     */
    public static function getLatestId($tableName, $idName) {
        $latestRow = DBL::getLatestRow($tableName, $idName);
        return (int) $latestRow[$idName];
    }

    /**
     * Returns the latest row in the database from table name $tableName, ordered by primary key $idName (DESC).
     * @param  String $tableName Name of the table.
     * @param  String $idName    Primary key of the table.
     * @return Array             Latest table row.
     */
    public static function getLatestRow($tableName, $idName) {
        $rows = Database::instance()->exec(
            'SELECT * FROM ' . $tableName . ' ORDER BY ' . $idName . ' DESC LIMIT 1'
        );
        return $rows[0];
    }
}
