<?php

class Message {

    function __construct($message) {
        $this->disputeID    = $message['dispute_id'];
        $this->authorID     = $message['author_id'];
        $this->recipientID  = $message['recipient_id'];
        $this->contents     = $message['message'];
        $this->timestamp    = $message['timestamp'];
    }

    public function getDisputeId() {
        return $this->disputeID;
    }

    public function getDispute() {
        return DBGet::instance()->dispute($this->getDisputeId());
    }

    public function timestamp() {
        return $this->timestamp;
    }

    public function contents() {
        return htmlspecialchars($this->contents);
    }

    public function getAuthorId() {
        return $this->authorID;
    }

    public function getRecipientId() {
        return $this->recipientID;
    }

    public function author() {
        return DBGet::instance()->account($this->authorID);
    }

    // based on http://starikovs.com/2011/11/10/php-new-line-to-paragraph/
    public function __toString() {
        $message = $this->contents();
        $message = preg_replace('/(\r?\n){2,}/', '</p><p>', $message);
        $message = preg_replace('/(\r?\n)+/', '<br />', $message);
        return '<p>' . $message . '</p>';
    }
}
