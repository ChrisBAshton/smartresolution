<?php

class LifespanFactory extends Prefab {

    /**
     * Retrieves the most recent accepted Lifespan that has been attributed to the given dispute.
     * If no Lifespan has been accepted, retrieves the most recent offered Lifespan.
     * If no Lifespan has even been offered, returns a mock Lifespan object so that method calls still work.
     *
     * @param integer $disputeID ID of the dispute.
     * @return Lifespan
     */
    public function getCurrentLifespan($disputeID) {
        if (!is_int($disputeID)) {
            var_dump($disputeID);
            throw new Exception('Dispute ID was not an integer! ');
        }
        $acceptedLifespan = $this->getLatestLifespanWithStatus($disputeID, 'accepted');
        $proposedLifespan = $this->getLatestLifespanWithStatus($disputeID, 'offered');

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
        $lifespan = $this->getLatestLifespanWithStatus($disputeID, 'notDeclined');
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
     * @param  string  $status    Get latest dispute that matches the given status. Special case: 'notDeclined'
     * @return Lifespan|false     Returns the Lifespan if one exists, or false if it doesn't.
     */
    private function getLatestLifespanWithStatus($disputeID, $status) {
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
            $details = DBGet::instance()->lifespan((int) $lifespans[0]['lifespan_id']);
            return new Lifespan($details);
        }

        return false;
    }
}
