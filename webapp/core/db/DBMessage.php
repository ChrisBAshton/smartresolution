<?php

class DBMessage {

    public static function getMessageById($messageID) {
        $message = Database::instance()->exec(
            'SELECT * FROM messages WHERE message_id = :message_id',
            array(':message_id' => $messageID)
        );

        if (count($message) !== 1) {
            throw new Exception('Message not found');
        }

        return $message[0];
    }

}