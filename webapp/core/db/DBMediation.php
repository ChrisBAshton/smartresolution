<?php

/**
 * @todo  - remove this file. Extract the methods out to another class.
 * @deprecated
 */
class DBMediation extends Prefab {

    public function respondToMediationProposal($proposalID, $response) {
        Database::instance()->exec(
            'UPDATE mediation_offers SET status = :response WHERE mediation_offer_id = :offer_id',
            array(
                ':offer_id' => $proposalID,
                ':response' => $response
            )
        );
    }

    public function getMediationCentreOfferForDispute($disputeID) {
        return $this->getOfferOfType('mediation_centre', $disputeID);
    }

    public function getMediatorOfferForDispute($disputeID) {
        return $this->getOfferOfType('mediator', $disputeID);
    }

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

    public function getAvailableMediators($disputeID) {
        $availableMediatorsDetails = Database::instance()->exec(
                'SELECT * FROM mediators_available WHERE dispute_id = :dispute_id',
                array(
                    ':dispute_id' => $disputeID
                )
            );

        $availableMediators = array();
        foreach($availableMediatorsDetails as $details) {
            $availableMediators[] = DBAccount::instance()->getAccountById((int) $details['mediator_id']);
        }

        return $availableMediators;
    }

}