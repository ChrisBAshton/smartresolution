<?php

interface LifespanInterface {

    public function __construct($disputeID);
    public function status();
    public function isCurrent();
    public function offered();
    public function accepted();
    public function declined();

}

class LifespanMock implements LifespanInterface {

    function __construct($disputeID) {
        $this->disputeID = $disputeID;
    }

    public function status() {
        return '<a href="/disputes/' . $this->disputeID . '/lifespan">No lifespan set yet.</a>';
    }

    public function isCurrent() {
        return false;
    }

    public function offered() {
        return false;
    }

    public function accepted() {
        return false;
    }

    public function declined() {
        return false;
    }

}

class Lifespan implements LifespanInterface {

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

    public function status() {
        if ($this->accepted()) {
            $currentTime = time();
            if ($this->startTime() > $currentTime) {
                $status = 'Dispute starts in ' . secondsToTime($this->startTime() - $currentTime);
            }
            else if ($this->endTime > $currentTime) {
                $status = 'Dispute has started and ends in ' . secondsToTime($this->endTime() - $currentTime);
            }
            else {
                $status = 'Dispute lifespan ended ' . secondsToTime($currentTime - $this->endTime()) . ' ago';
            }
        }
        else if ($this->offered()) {
            $status = 'New lifespan proposal offered.';
        }
        else {
            $status = 'No lifespan set yet.';
        }

        return '<a href="/disputes/' . $this->disputeID . '/lifespan">' . $status . '</a>';
    }

    public function isCurrent() {
        if (!$this->accepted()) {
            return false;
        }
        else {
            $currentTime = time();
            return ($this->startTime() < $currentTime) && ($this->endTime > $currentTime);
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
}