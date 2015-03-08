<?php

class Messages {

    private $messages = array();

    function __construct($disputeID) {

        $messages = Database::instance()->exec(
            'SELECT message_id FROM messages WHERE dispute_id = :dispute_id ORDER BY message_id DESC',
            array(':dispute_id' => $disputeID)
        );

        foreach($messages as $message) {
            array_push($this->messages, new Message((int) $message['message_id']));
        }
    }

    public function getMessages() {
        return $this->messages;
    }

}
