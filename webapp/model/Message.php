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

    public static function create($params) {
        $disputeID = Utils::getValue($params, 'dispute_id');
        $authorID  = Utils::getValue($params, 'author_id');
        $message   = Utils::getValue($params, 'message');

        Database::instance()->exec('INSERT INTO messages (dispute_id, author_id, message, timestamp) VALUES (:dispute_id, :author_id, :message, :timestamp)',
            array(
                ':dispute_id' => $disputeID,
                ':author_id'  => $authorID,
                ':message'    => $message,
                ':timestamp'  => time()
            )
        );

        $messageID = (int) Database::instance()->exec('SELECT message_id FROM messages ORDER BY message_id DESC LIMIT 1')[0]['message_id'];

        return new Message($messageID);
    }

}