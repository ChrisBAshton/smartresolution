<?php

class LifespanFactory {

    /**
     * Retrieves the most recent accepted Lifespan that has been attributed to the given dispute.
     * If no Lifespan has been accepted, retrieves the most recent offered Lifespan.
     * If no Lifespan has even been offered, returns a mock Lifespan object so that method calls still work.
     *
     * @param integer $disputeID ID of the dispute.
     * @return Lifespan
     */
    public static function getCurrentLifespan($disputeID) {
        $acceptedLifespan = LifespanFactory::getLatestLifespanWithStatus($disputeID, 'accepted');
        $proposedLifespan = LifespanFactory::getLatestLifespanWithStatus($disputeID, 'offered');

        if ($acceptedLifespan) {
            return $acceptedLifespan;
        }
        else if ($proposedLifespan) {
            return $proposedLifespan;
        }
        else {
            return new LifespanMock();
        }
    }

    public static function getLatestLifespan($disputeID) {
        $lifespan = LifespanFactory::getLatestLifespanWithStatus($disputeID, 'notDeclined');
        if ($lifespan) {
            return $lifespan;
        }
        else {
            return new LifespanMock();
        }
    }

    /**
     * Returns the latest Lifespan attributed to the dispute that matches the given status. If no Lifespan is found, returns false.
     *
     * @param  integer $disputeID ID of the dispute.
     * @param  String  $status    Get latest dispute that matches the given status. Special case: 'any'
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
