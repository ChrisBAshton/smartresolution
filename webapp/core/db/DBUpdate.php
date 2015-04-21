<?php

class DBUpdate extends Prefab {

    public function lifespan($lifespan) {
        Database::instance()->exec(
            'UPDATE lifespans SET status = :status, end_time = :end_time WHERE lifespan_id = :lifespan_id',
            array(
                ':lifespan_id' => $lifespan->getLifespanId(),
                ':end_time'    => $lifespan->endTime(),
                ':status'      => $lifespan->getRawStatus()
            )
        );
    }

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

    public function dispute($dispute) {
        Database::instance()->exec(
            'UPDATE disputes SET round_table_communication = :rtc, status = :status, type = :type WHERE dispute_id = :dispute_id',
            array(
                ':dispute_id' => $dispute->getDisputeId(),
                ':rtc'        => $dispute->inRoundTableCommunication(),
                ':status'     => $dispute->getStatus(),
                ':type'       => $dispute->getType()
            )
        );
    }

}