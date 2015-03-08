<?php

class LifespanFactory {

    /**
     * Lifespan constructor should get the most recent Lifespan that has been attributed to the given dispute.
     * If the lifespan has been accepted, that should give it higher precedence over a lifespan proposal that is
     * more recent but has not yet been accepted by the other party.
     *
     * @param integer $disputeID ID of the dispute.
     */
    public static function getLifespan($disputeID) {
        $lifespan = Database::instance()->exec(
            'SELECT * FROM lifespans WHERE dispute_id = :dispute_id ORDER BY lifespan_id DESC LIMIT 1',
            array(':dispute_id' => $disputeID)
        );

        if (count($lifespan) === 1) {
            return new Lifespan($disputeID);
        }
        else {
            return new LifespanMock($disputeID);
        }
    }

}
