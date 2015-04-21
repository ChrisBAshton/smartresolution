<?php

class DBQuery extends Prefab {

    public function retrieveDisputeMessages($disputeID) {
        $messages = array();

        $messageDetails = Database::instance()->exec(
            'SELECT message_id FROM messages WHERE dispute_id = :dispute_id AND recipient_id is null ORDER BY message_id DESC',
            array(':dispute_id' => $disputeID)
        );

        foreach($messageDetails as $id) {
            $details = DBGet::instance()->message($id['message_id']);
            array_push($messages, new Message($details));
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
            $details = DBGet::instance()->message($id['message_id']);
            array_push($messages, new Message($details));
        }

        return $messages;
    }

    public function getNotificationsForLoginId($loginId) {
        $notifications = array();

        $notificationsDetails = Database::instance()->exec('SELECT notification_id FROM notifications WHERE recipient_id = :login_id AND read = "false" ORDER BY notification_id DESC',
            array(':login_id' => $loginId)
        );

        foreach ($notificationsDetails as $id) {
            $details = DBGet::instance()->notification($id['notification_id']);
            $notifications[] = new Notification($details);
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