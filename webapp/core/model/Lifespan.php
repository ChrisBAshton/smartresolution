<?php

class Lifespan implements LifespanInterface {

    private $lifespanID;
    private $disputeID;
    private $proposer;
    private $status;
    private $validUntil;
    private $startTime;
    private $endTime;

    function __construct($lifespan, $justCreated = false) {
        $this->lifespanID = $lifespan['lifespan_id'];
        $this->disputeID  = $lifespan['dispute_id'];
        $this->proposer   = $lifespan['proposer'];
        $this->status     = $lifespan['status'];
        $this->validUntil = $lifespan['valid_until'];
        $this->startTime  = $lifespan['start_time'];
        $this->endTime    = $lifespan['end_time'];

        // this is used to check if lifespan proposal is a valid one
        if ($justCreated) {
            $invalid = $this->invalid($this->validUntil, $this->startTime, $this->endTime);
            if ($invalid) {
                Utils::instance()->throwException($invalid);
            }
        }
    }

    public function invalid($validUntil, $startTime, $endTime) {
        $currentTime = time();
        $invalidBecause = false;

        if ($validUntil < $currentTime || $startTime < $currentTime || $endTime < $currentTime) {
            $invalidBecause = 'All selected dates must be in the future.';
        }
        else if ($startTime >= $endTime) {
            $invalidBecause = 'Start date must be before end date.';
        }
        else if ($validUntil > $startTime) {
            $invalidBecause = 'The "Valid Until" date must be before the start and end dates.';
        }

        return $invalidBecause;
    }

    public function getRawStatus() {
        return $this->status;
    }

    public function status() {
        if ($this->isEnded()) {
            $status = 'Dispute ended.';
        }
        else if ($this->accepted()) {
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

        return $status;
    }

    public function disputeClosed() {
        $this->endTime = time();
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

    public function isEnded() {
        return ($this->endTime < time());
    }

    public function accept() {
        $this->status = 'accepted';
        $this->notifyOfLifespanStatusChange('The other party has agreed your lifespan offer.');
    }

    public function decline() {
        $this->status = 'declined';
        $this->notifyOfLifespanStatusChange('The other party has declined your lifespan offer.');
    }

    private function notifyOfLifespanStatusChange($notification) {
        $dispute = new Dispute(DBGet::instance()->dispute($this->disputeID));

        DBCreate::instance()->notification(array(
            'recipient_id' => $dispute->getOpposingPartyId(Session::instance()->getAccount()),
            'message'      => $notification,
            'url'          => $dispute->getUrl() . '/lifespan'
        ));
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
}