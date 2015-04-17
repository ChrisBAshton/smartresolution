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
        $details = DBGet::instance()->notification($notificationID);
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
        DBNotification::markNotificationAsRead($this->getNotificationId());
        $this->setVariables($this->getNotificationId());
    }
}