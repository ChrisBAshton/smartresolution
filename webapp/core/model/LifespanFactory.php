<?php

class LifespanFactory extends Prefab {

    private $db;

    function __construct() {
        $this->db = DBLifespan::instance();
    }

    /**
     * Retrieves the most recent accepted Lifespan that has been attributed to the given dispute.
     * If no Lifespan has been accepted, retrieves the most recent offered Lifespan.
     * If no Lifespan has even been offered, returns a mock Lifespan object so that method calls still work.
     *
     * @param integer $disputeID ID of the dispute.
     * @return Lifespan
     */
    public function getCurrentLifespan($disputeID) {
        $acceptedLifespan = $this->db->getLatestLifespanWithStatus($disputeID, 'accepted');
        $proposedLifespan = $this->db->getLatestLifespanWithStatus($disputeID, 'offered');

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

    public function getLatestLifespan($disputeID) {
        $lifespan = $this->db->getLatestLifespanWithStatus($disputeID, 'notDeclined');
        if ($lifespan) {
            return $lifespan;
        }
        else {
            return new LifespanMock();
        }
    }
}
