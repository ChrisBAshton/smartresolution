<?php

/**
 * Handles the database queries concerning mediation.
 * @todo  - remove this file. Extract the methods out to another class.
 * @deprecated
 */
class DBMediation extends Prefab {

    /**
     * Marks the given mediation proposal as accepted or declined.
     * @param  int    $proposalID The ID of the mediation proposal.
     * @param  string $response   Status of the proposal; 'accepted' or 'declined' (or 'offered', technically, would be allowed)
     */
    public function respondToMediationProposal($proposalID, $response) {
        Database::instance()->exec(
            'UPDATE mediation_offers SET status = :response WHERE mediation_offer_id = :offer_id',
            array(
                ':offer_id' => $proposalID,
                ':response' => $response
            )
        );
    }

    /**
     * Retrieves the latest offer for a mediation centre for the given dispute.
     * @param  int $disputeID ID of the dispute.
     * @return array          Row of data corresponding to the offer.
     */
    public function getMediationCentreOfferForDispute($disputeID) {
        return $this->getOfferOfType('mediation_centre', $disputeID);
    }

    /**
     * Retrieves the latest offer for a mediator for the given dispute.
     * @param  int $disputeID ID of the dispute.
     * @return array          Row of data corresponding to the offer.
     */
    public function getMediatorOfferForDispute($disputeID) {
        return $this->getOfferOfType('mediator', $disputeID);
    }

    /**
     * Retrieves the latest offer for the given type, for the given dispute.
     * @param  string $type      'mediation_centre' or 'mediator'.
     * @param  int    $disputeID ID of the dispute.
     * @return array             Row of data corresponding to the offer.
     */
    public function getOfferOfType($type, $disputeID) {
        $offers = Database::instance()->exec(
            'SELECT * FROM mediation_offers
            WHERE type     = :type
            AND dispute_id = :dispute_id
            AND status    != "declined"
            ORDER BY mediation_offer_id DESC',
            array(
                ':type'       => $type,
                ':dispute_id' => $disputeID
            )
        );

        if (count($offers) !== 0) {
            return $offers[0];
        }

        return false;
    }

    /**
     * Updates the list of mediators marked as available for the given dispute.
     * @param  int   $disputeID          ID of the dispute.
     * @param  array $availableMediators One-dimensional array of mediator login IDs representing each mediator which is available for the dispute.
     */
    public function saveListOfMediators($disputeID, $availableMediators) {
        $db = Database::instance();
        $db->begin();
        $db->exec(
            'DELETE FROM mediators_available WHERE dispute_id = :dispute_id',
            array(
                ':dispute_id' => $disputeID
            )
        );
        foreach($availableMediators as $mediatorId) {
            $db->exec(
                'INSERT INTO mediators_available (mediator_id, dispute_id) VALUES (:mediator_id, :dispute_id)',
                array(
                    ':mediator_id' => (int) $mediatorId,
                    ':dispute_id'  => $disputeID
                )
            );
        }
        $db->commit();
    }

    /**
     * Retrieves all of the available mediators for the given dispute.
     * @param  int $disputeID  The ID of the dispute.
     * @return array<Mediator> The array of available mediators.
     */
    public function getAvailableMediators($disputeID) {
        $availableMediatorsDetails = Database::instance()->exec(
                'SELECT * FROM mediators_available WHERE dispute_id = :dispute_id',
                array(
                    ':dispute_id' => $disputeID
                )
            );

        $availableMediators = array();
        foreach($availableMediatorsDetails as $details) {
            $availableMediators[] = DBGet::instance()->account((int) $details['mediator_id']);
        }

        return $availableMediators;
    }

}