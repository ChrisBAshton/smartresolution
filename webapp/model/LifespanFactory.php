<?php

class LifespanFactory {

    /**
     * Lifespan constructor should get the most recent Lifespan that has been attributed to the given dispute.
     * If the lifespan has been accepted, that should give it higher precedence over a lifespan proposal that is
     * more recent but has not yet been accepted by the other party.
     *
     * @param integer $disputeID ID of the dispute.
     */
    public static function getCurrentLifespan($disputeID) {
        $lifespan = LifespanFactory::getLifespanWhoseStatusIs('accepted', $disputeID);
        if (!$lifespan) {
            return new LifespanMock();
        }
        return $lifespan;
    }

    public static function getOfferedLifespan($disputeID) {
        return LifespanFactory::getLifespanWhoseStatusIs('offered', $disputeID);
    }


    public static function getLifespanWhoseStatusIs($status, $disputeID) {
        $lifespans = Database::instance()->exec(
            'SELECT lifespan_id FROM lifespans WHERE dispute_id = :dispute_id AND status = :status ORDER BY lifespan_id DESC LIMIT 1',
            array(
                ':status'     => $status,
                ':dispute_id' => $disputeID
            )
        );

        if (count($lifespans) === 1) {
            return new Lifespan((int) $lifespans[0]['lifespan_id']);
        }
        else {
            return false;
        }
    }
}
