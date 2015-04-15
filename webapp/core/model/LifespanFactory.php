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
        $acceptedLifespan = DBLifespan::getLatestLifespanWithStatus($disputeID, 'accepted');
        $proposedLifespan = DBLifespan::getLatestLifespanWithStatus($disputeID, 'offered');

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
        $lifespan = DBLifespan::getLatestLifespanWithStatus($disputeID, 'notDeclined');
        if ($lifespan) {
            return $lifespan;
        }
        else {
            return new LifespanMock();
        }
    }
}
