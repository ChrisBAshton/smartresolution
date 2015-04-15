<?php

class DBLifespan {

    public static function getLifespanById($lifespanID) {
        $lifespan = Database::instance()->exec(
            'SELECT * FROM lifespans WHERE lifespan_id = :lifespan_id',
            array(':lifespan_id' => $lifespanID)
        );

        return $lifespan[0];
    }

    public static function endLifespan($lifespanID) {
        Database::instance()->exec(
            'UPDATE lifespans SET end_time = :end_time WHERE lifespan_id = :lifespan_id',
            array(
                ':end_time'    => time(),
                ':lifespan_id' => $lifespanID
            )
        );
    }

    public static function updateLifespanStatus($lifespanID, $disputeID, $status) {
        Database::instance()->exec(
            'UPDATE lifespans SET status = :status WHERE lifespan_id = :lifespan_id',
            array(
                ':status'      => $status,
                ':lifespan_id' => $lifespanID
            )
        );

        if ($status === 'accepted') {
            $notification = 'The other party has agreed your lifespan offer.';
        }
        else if ($status === 'declined') {
            $notification = 'The other party has declined your lifespan offer.';
        }

        $dispute = new Dispute($disputeID);

        DBL::createNotification(array(
            'recipient_id' => $dispute->getOpposingPartyId(Session::getAccount()),
            'message'      => $notification,
            'url'          => $dispute->getUrl() . '/lifespan'
        ));
    }

    /**
     * Returns the latest Lifespan attributed to the dispute that matches the given status. If no Lifespan is found, returns false.
     *
     * @param  integer $disputeID ID of the dispute.
     * @param  string  $status    Get latest dispute that matches the given status. Special case: 'any'
     * @return Lifespan|false     Returns the Lifespan if one exists, or false if it doesn't.
     */
    public static function getLatestLifespanWithStatus($disputeID, $status) {
        if ($status === 'notDeclined') {
            $lifespans = Database::instance()->exec(
                'SELECT lifespan_id FROM lifespans WHERE dispute_id = :dispute_id AND status != "declined" ORDER BY lifespan_id DESC LIMIT 1',
                array(
                    ':dispute_id' => $disputeID
                )
            );
        }
        else {
            $lifespans = Database::instance()->exec(
                'SELECT lifespan_id FROM lifespans WHERE dispute_id = :dispute_id AND status = :status ORDER BY lifespan_id DESC LIMIT 1',
                array(
                    ':dispute_id' => $disputeID,
                    ':status'     => $status
                )
            );
        }

        if (count($lifespans) === 1) {
            return new Lifespan((int) $lifespans[0]['lifespan_id']);
        }

        return false;
    }

}