<?php

/**
 * Messages are linked to Disputes and can be read by everyone involved in the dispute, unless a recipient is specified. If a recipient is specified, the message can only be read by the author and the recipient.
 * The former is the default messaging behaviour of the application. The latter is how the application handles messaging within mediation where private messages are sent between the mediator and an agent.
 */
class Message {

    private $disputeID;
    private $authorID;
    private $contents;
    private $timestamp;
    private $recipientID;

    /**
     * Message constructor.
     * @param array  $message                 Array of details retrieved from database.
     *        int    $message['dispute_id']   ID of the associated dispute.
     *        int    $message['author_id']    Login ID of the author of the message.
     *        string $message['message']      Contents of the message.
     *        int    $message['timestamp']    UNIX timestamp representing when message was sent.
     *        int    $message['recipient_id'] ID of the message recipient (defaults to 0).
     */
    function __construct($message) {
        $this->disputeID    = $message['dispute_id'];
        $this->authorID     = $message['author_id'];
        $this->contents     = $message['message'];
        $this->timestamp    = $message['timestamp'];
        $this->recipientID  = $message['recipient_id'];
    }

    /**
     * Returns the ID of the associated dispute.
     * @return int ID of the associated dispute.
     */
    public function getDisputeId() {
        return $this->disputeID;
    }

    /**
     * Returns the associated dispute.
     * @return Dispute The associated dispute.
     */
    public function getDispute() {
        return DBGet::instance()->dispute($this->getDisputeId());
    }

    /**
     * Returns the timestamp representing when message was sent.
     * @return int UNIX timestamp.
     */
    public function timestamp() {
        return $this->timestamp;
    }

    /**
     * Returns the contents of the message, HTML-character-encoded to prevent harmful scripts.
     * @return [type] [description]
     */
    public function contents() {
        return htmlspecialchars($this->contents);
    }

    /**
     * Returns the ID of the author of the message.
     * @return int Author's login ID.
     */
    public function getAuthorId() {
        return $this->authorID;
    }

    /**
     * Returns the author of the message.
     * @return Account Author of the message.
     */
    public function author() {
        return DBGet::instance()->account($this->authorID);
    }

    /**
     * Returns the ID of the recipient of the message. If no recipient was set, this defaults to 0.
     * @return int ID of the recipient.
     */
    public function getRecipientId() {
        return $this->recipientID;
    }

    //
    /**
     * Defines how message should be rendered when the object is directly rendered in HTML through 'echo'.
     *
     * Whitespace is replaced with HTML paragraphs and line breaks, based on a technique outlined here:
     *
     *     http://starikovs.com/2011/11/10/php-new-line-to-paragraph/
     *
     * @return string HTML representation of the message.
     */
    public function __toString() {
        $message = $this->contents();
        $message = preg_replace('/(\r?\n){2,}/', '</p><p>', $message);
        $message = preg_replace('/(\r?\n)+/', '<br />', $message);
        return '<p>' . $message . '</p>';
    }
}
