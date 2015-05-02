<?php

/**
 * Represents a notification, which is sent to a specific user after an important action such as
 * a dispute being assigned to them, a message being sent, etc.
 */
class Notification {

    private $notificationID;
    private $recipientID;
    private $url;
    private $message;
    private $read;

    /**
     * Notification constructor.
     * @param array   $details                     Array of notification data, probably retrieved from the database.
     *        int     $details['notification_id']  Unique ID of the notification.
     *        int     $details['recipient_id']     ID corresponding to the login ID of the intended recipient.
     *        string  $details['url']              URL that the notification links to.
     *        string  $details['message']          The content of the notification.
     *        boolean $details['read']             True if the notification has been read by the recipient, otherwise false.
     */
    public function __construct($details) {
        $this->notificationID = $details['notification_id'];
        $this->recipientID    = $details['recipient_id'];
        $this->url            = $details['url'];
        $this->message        = $details['message'];
        $this->read           = $details['read'];
    }

    /**
     * Returns the notification's unique ID.
     * @return int Notification ID.
     */
    public function getNotificationId() {
        return $this->notificationID;
    }

    /**
     * Returns the content of the notification, e.g. 'Webdapper Ltd has opened a dispute against you.'
     * @return string Notification content.
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Returns the URL that the notification links to, e.g. '/disputes/1337'
     * @return string URL that the notification links to.
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Returns whether or not the message has been read.
     * @return boolean True if message has been read, otherwise false.
     */
    public function hasBeenRead() {
        return $this->read;
    }

    /**
     * Marks the notification object as 'read', changing the return value of hasBeenRead(). Note that
     * the change is not persistent until the Notification object has been passed to DBUpdate::instance()->notification()
     */
    public function markAsRead() {
        $this->read = true;
    }
}