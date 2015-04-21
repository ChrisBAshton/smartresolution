<?php

class Notification {

    private $notificationID;
    private $recipientID;
    private $url;
    private $message;
    private $read;

    public function __construct($details) {
        $this->notificationID = $details['notification_id'];
        $this->recipientID    = $details['recipient_id'];
        $this->url            = $details['url'];
        $this->message        = $details['message'];
        $this->read           = $details['read'];
    }

    public function getNotificationId() {
        return $this->notificationID;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getUrl() {
        return $this->url;
    }

    public function hasBeenRead() {
        return $this->read;
    }

    public function markAsRead() {
        $this->read = true;
    }
}