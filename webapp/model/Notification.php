<?php

class Notification {

    private $notificationID;
    private $loginID;
    private $url;
    private $message;
    private $read;

    public function __construct($notificationID) {
        $this->setVariables($notificationID);
    }

    private function setVariables($notificationID) {
        $details = Database::instance()->exec('SELECT * FROM notifications WHERE notification_id = :notification_id', array(':notification_id' => $notificationID))[0];
        
        $this->notificationID = (int) $details['notification_id'];
        $this->loginID        = (int) $details['recipient_id'];
        $this->url            = $details['url'];
        $this->message        = $details['message'];
        $this->read           = !($details['read'] === 'false');
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
        Database::instance()->exec('UPDATE notifications SET read = "true" WHERE notification_id = :notification_id', array(':notification_id' => $this->getNotificationId()));
        $this->setVariables($this->getNotificationId());
    }
}