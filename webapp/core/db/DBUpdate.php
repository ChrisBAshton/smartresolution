<?php

class DBUpdate extends Prefab {

    public function notification($notification) {
        Database::instance()->exec('UPDATE notifications
            SET read = :read
            WHERE notification_id = :notification_id',
            array(
                ':notification_id' => $notification->getNotificationId(),
                ':read' => true
            )
        );
    }

    public function organisation($organisation) {
        Database::instance()->exec(
            'UPDATE organisations SET description = :description WHERE login_id = :uid',
            array(
                ':description' => $organisation->getRawDescription(),
                ':uid'         => $organisation->getLoginId()
            )
        );
    }

    public function individual($individual) {
        Database::instance()->exec(
            'UPDATE individuals SET cv = :cv WHERE login_id = :uid',
            array(
                ':cv'  => $individual->getRawCV(),
                ':uid' => $individual->getLoginId()
            )
        );
    }

}