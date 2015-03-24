<?php

class DBMediation {

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
