<?php

class DBMediation {

    public static function getMediationCentreOfferForDispute($disputeID) {
        return DBMediation::getOfferOfType('mediation_centre', $disputeID);
    }

    public static function getMediatorOfferForDispute($disputeID) {
        return DBMediation::getOfferOfType('mediator', $disputeID);
    }

    public static function getOfferOfType($type, $disputeID) {
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

    public static function saveListOfMediators($disputeID, $availableMediators) {
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

    public static function getAvailableMediators($disputeID) {
        $availableMediatorsDetails = Database::instance()->exec(
                'SELECT * FROM mediators_available WHERE dispute_id = :dispute_id',
                array(
                    ':dispute_id' => $disputeID
                )
            );

        $availableMediators = array();
        foreach($availableMediatorsDetails as $details) {
            $availableMediators[] = new Mediator((int) $details['mediator_id']);
        }

        return $availableMediators;
    }

    public static function mediatorIsAvailableForDispute($mediatorID, $disputeID) {
        $available = Database::instance()->exec(
            'SELECT * FROM mediators_available WHERE dispute_id = :dispute_id AND mediator_id = :mediator_id LIMIT 1',
            array(
                ':dispute_id'  => $disputeID,
                ':mediator_id' => $mediatorID
            )
        );

        return count($available) === 1;
    }

}