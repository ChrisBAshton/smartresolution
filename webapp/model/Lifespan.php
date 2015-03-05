<?php

class Lifespan {

    private $status;

    function __construct($disputeID) {
        $this->setVariables($disputeID);
    }

    private function setVariables($disputeID) {
        $lifespan = Database::instance()->exec(
            'SELECT * FROM lifespans WHERE dispute_id = :dispute_id ORDER BY lifespan_id DESC LIMIT 1',
            array(':dispute_id' => $disputeID)
        );
        if (count($lifespan) === 1) {
            $lifespan         = $lifespan[0];
            $this->lifespanID = (int) $lifespan['lifespan_id'];
            $this->disputeID  = (int) $lifespan['dispute_id'];
            $this->proposer   = (int) $lifespan['proposer'];
            $this->status     = $lifespan['status'];
            $this->validUntil = $lifespan['valid_until'];
            $this->startTime  = $lifespan['start_time'];
            $this->endTime    = $lifespan['end_time'];
        }
    }

    public function accept() {
        $this->updateStatus('accepted');
    }

    public function decline() {
        $this->updateStatus('declined');
    }

    public function offered() {
        return $this->status === 'offered';
    }

    public function accepted() {
        return $this->status === 'accepted';
    }

    public function declined() {
        return $this->status === 'declined';
    }

    public function startTime() {
        return $this->startTime;
    }

    public function endTime() {
        return $this->endTime;
    }

    public function validUntil() {
        return $this->validUntil;
    }

    public function getAssociatedDisputeId() {
        return $this->disputeID;
    }

    public function getLifespanId() {
        return $this->lifespanID;
    }

    public function getProposer() {
        return $this->proposer;
    }

    private function updateStatus($status) {
        Database::instance()->exec(
            'UPDATE lifespans SET status = :status WHERE lifespan_id = :lifespan_id',
            array(
                ':status'      => $status,
                ':lifespan_id' => $this->getLifespanId()
            )
        );
        $this->setVariables($this->getAssociatedDisputeId());
    }

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
        $lifespanID = (int) $db->exec(
            'SELECT lifespan_id FROM lifespans ORDER BY lifespan_id DESC LIMIT 1'
        )[0]['lifespan_id'];
        
        $lifespan = new Lifespan($lifespanID);
        // if no exception is raised, safe to commit transaction to database
        $db->commit();
        return $lifespan;
    }
}