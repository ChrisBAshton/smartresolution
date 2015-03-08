<?php

class Message {

    function __construct($messageID) {
        $message = Database::instance()->exec(
            'SELECT * FROM messages WHERE message_id = :message_id',
            array(':message_id' => $messageID)
        );

        if (count($message) !== 1) {
            throw new Exception('Message not found');
        }
        else {
            $message = $message[0];
            $this->disputeID = $message['dispute_id'];
            $this->authorID  = $message['author_id'];
            $this->contents  = $message['message'];
            $this->timestamp = $message['timestamp'];
        }
    }

    public function getDisputeId() {
        return $this->disputeID;
    }

    public function timestamp() {
        return $this->timestamp;
    }

    public function contents() {
        return $this->contents;
    }

    public function author() {
        return AccountDetails::getAccountById($this->authorID);
    }
}