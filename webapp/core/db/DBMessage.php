<?php

class DBMessage {

    public static function retrieveDisputeMessages($disputeID) {
        $messages = array();

        $messageDetails = Database::instance()->exec(
            'SELECT message_id FROM messages WHERE dispute_id = :dispute_id AND recipient_id is null ORDER BY message_id DESC',
            array(':dispute_id' => $disputeID)
        );

        foreach($messageDetails as $message) {
            array_push($messages, new Message((int) $message['message_id']));
        }

        return $messages;
    }

    public static function retrieveMediationMessages($disputeID, $individualA, $individualB) {
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

        foreach($messageDetails as $message) {
            array_push($messages, new Message((int) $message['message_id']));
        }

        return $messages;
    }

}