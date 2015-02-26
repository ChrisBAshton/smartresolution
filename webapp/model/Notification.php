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

    public static function create($options) {

        $recipientId = Register::getValue($options, 'recipient_id');
        $message     = Register::getValue($options, 'message');
        $url         = Register::getValue($options, 'url');

        Database::instance()->exec('INSERT INTO notifications (recipient_id, message, url) VALUES (:recipient_id, :message, :url)',
            array(
                ':recipient_id' => $recipientId,
                ':message'      => $message,
                ':url'          => $url,
            )
        );

        $notificationID = (int) Database::instance()->exec('SELECT notification_id FROM notifications ORDER BY notification_id DESC LIMIT 1')[0]['notification_id'];

        return new Notification($notificationID);
    }

}