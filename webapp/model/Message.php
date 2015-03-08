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

    public function getDispute() {
        return new Dispute($this->getDisputeId());
    }

    public function timestamp() {
        return $this->timestamp;
    }

    public function contents() {
        return htmlspecialchars($this->contents);
    }

    public function author() {
        return AccountDetails::getAccountById($this->authorID);
    }

    // based on http://starikovs.com/2011/11/10/php-new-line-to-paragraph/
    public function __toString() {
        $message = $this->contents();
        $message = preg_replace('/(\r?\n){2,}/', '</p><p>', $message);
        $message = preg_replace('/(\r?\n)+/', '<br />', $message);
        return '<p>' . $message . '</p>';
    }
}
