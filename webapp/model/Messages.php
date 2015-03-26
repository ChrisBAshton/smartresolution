<?php

class Messages {

    private $messages = array();

    function __construct($disputeID, $individualA = false, $individualB = false) {
        if (!$individualA || !$individualB) {
            $this->retrieveDisputeMessages($disputeID);
        }
        else {
            $this->retrieveMediationMessages($disputeID, $individualA, $individualB);
        }
    }

    private function retrieveDisputeMessages($disputeID) {
        $messages = Database::instance()->exec(
            'SELECT message_id FROM messages WHERE dispute_id = :dispute_id ORDER BY message_id DESC',
            array(':dispute_id' => $disputeID)
        );

        foreach($messages as $message) {
            array_push($this->messages, new Message((int) $message['message_id']));
        }
    }

    private function retrieveMediationMessages($disputeID, $individualA, $individualB) {
        $messages = Database::instance()->exec(
            'SELECT message_id FROM messages
            WHERE
            (author_id = :individual_a AND recipient_id = :individual_b AND dispute_id = :dispute_id)
            OR
            (author_id = :individual_b AND recipient_id = :individual_a AND dispute_id = :dispute_id)
            ORDER BY message_id DESC',
            array(
                ':dispute_id'   => $disputeID,
                ':individual_a' => $individualA->getLoginId(),
                ':individual_b' => $individualB->getLoginId(),
            )
        );

        foreach($messages as $message) {
            array_push($this->messages, new Message((int) $message['message_id']));
        }
    }

    public function getMessages() {
        return $this->messages;
    }

}
