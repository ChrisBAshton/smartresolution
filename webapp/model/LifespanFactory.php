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

    public static function getLifespanProposals($disputeID) {
        // @TODO
    }

    /**
     * Creates a new lifespan proposal.
     * @param  Array $params Parameters outlining start and end dates, etc.
     * @return Lifespan      The newly created lifespan.
     */
    public static function create($params) {
        $disputeID  = Utils::getValue($params, 'dispute_id');
        $proposer   = Utils::getValue($params, 'proposer');
        $validUntil = Utils::getValue($params, 'valid_until');
        $startTime  = Utils::getValue($params, 'start_time');
        $endTime    = Utils::getValue($params, 'end_time');

        $db = Database::instance();
        $db->begin();
        $db->exec(
            'INSERT INTO lifespans (dispute_id, proposer, valid_until, start_time, end_time)
             VALUES (:dispute_id, :proposer, :valid_until, :start_time, :end_time)', array(
            ':dispute_id'  => $disputeID,
            ':proposer'    => $proposer,
            ':valid_until' => $validUntil,
            ':start_time'  => $startTime,
            ':end_time'    => $endTime
        ));
        
        $lifespan = new Lifespan($disputeID);
        // if no exception is raised, safe to commit transaction to database
        $db->commit();
        return $lifespan;
    }

}