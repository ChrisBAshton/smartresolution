<?php

/**
 * A dispute lifespan offer, which may or may not have been accepted. If accepted, the lifespan is applied to the dispute.
 */
class Lifespan implements LifespanInterface {

    private $lifespanID;
    private $disputeID;
    private $proposer;
    private $status;
    private $validUntil;
    private $startTime;
    private $endTime;

    /**
     * Lifespan constructor
     * @param array  $lifespan                Array of lifespan details.
     *        int    $lifespan['lifespan_id'] ID of the lifespan in the database.
     *        int    $lifespan['dispute_id']  ID of the associated dispute.
     *        int    $lifespan['proposer']    Login ID of the account proposing the lifespan.
     *        string $lifespan['status']      Represents the state of the lifespan ('offered', 'accepted', 'declined').
     *        int    $lifespan['valid_until'] UNIX timestamp representing time the lifespan offer will be available until. Beyond this time, if the lifespan has still not been accepted, it is automatically invalidated.
     *        int    $lifespan['start_time']  UNIX timestamp representing time the lifespan will begin, assuming the lifespan has been accepted.
     *        int    $lifespan['end_time']    UNIX timestamp representing time the lifespan will end. If the dispute is not resolved by this time, the dispute will automatically close. Agents can always renegotiate a lifespan mid-dispute.
     * @param boolean $justCreated            If true, the lifespan has just been created and we want to run some validity checks on it. If false, the lifespan was created a while ago so might well be invalid now (e.g. start time is in the past) - we don't want to raise an exception for that!
     */
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

    /**
     * This function assumes that the lifespan offer has just been created, and makes some validity checks such as start time being before end time, all times being in the future, and so on. Returns false if valid, or, if it is invalid, a string representing why the lifespan is invalid.
     * @param  int $validUntil UNIX timestamp representing the deadline for accepting the lifespan offer.
     * @param  int $startTime  UNIX timestamp representing the start time of the lifespan.
     * @param  int $endTime    UNIX timestamp representing the end time of the lifespan.
     * @return string|false    false if valid, otherwise a string representing why the lifespan is invalid.
     */
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

    /**
     * Gets the human-readable status of the lifespan according to wether or not it hs been accepted, what the current time is, and so on.
     * @return string Human-readable status.
     */
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
        }
        else if ($this->offered()) {
            $status = 'New lifespan proposal offered.';
        }
        else {
            $status = 'No lifespan set yet.';
        }

        return $status;
    }

    /**
     * Gets the raw status of the lifespan; either 'offered', 'accepted' or 'declined'.
     * @return string Lifespan status.
     */
    public function getRawStatus() {
        return $this->status;
    }

    /**
     * Denotes whether or not the lifespan has been accepted and is current.
     * @return boolean true if accepted and current, otherwise false.
     */
    public function isCurrent() {
        if (!$this->accepted()) {
            return false;
        }
        else {
            $currentTime = time();
            return ($this->startTime() < $currentTime) && ($this->endTime > $currentTime);
        }
    }

    /**
     * Irrespective of whether lifespan has been accepted or offered, denotes whether or not the end time of the lifespan is in the past and therefore the lifespan is no longer valid.
     * @return boolean true if ended, false if could still be valid.
     */
    public function isEnded() {
        return ($this->endTime <= time());
    }

    /**
     * Returns true if the lifespan has been offered, otherwise false.
     * @return boolean
     */
    public function offered() {
        return $this->status === 'offered';
    }

    /**
     * Returns true if the lifespan has been accepted, otherwise false.
     * @return boolean
     */
    public function accepted() {
        return $this->status === 'accepted';
    }

    /**
     * Returns true if the lifespan has been declined, otherwise false.
     * @return boolean
     */
    public function declined() {
        return $this->status === 'declined';
    }

    /**
     * Returns the timestamp representing the start time of the lifespan.
     * @return int UNIX timestamp.
     */
    public function startTime() {
        return $this->startTime;
    }


    /**
     * Returns the timestamp representing the end time of the lifespan.
     * @return int UNIX timestamp.
     */
    public function endTime() {
        return $this->endTime;
    }


    /**
     * Returns the timestamp representing the deadline for accepting the lifespan.
     * @return int UNIX timestamp.
     */
    public function validUntil() {
        return $this->validUntil;
    }

    /**
     * Returns the associated dispute ID.
     * @return int Dispute ID.
     */
    public function getAssociatedDisputeId() {
        return $this->disputeID;
    }

    /**
     * Returns the ID of the lifespan
     * @return int Lifespan ID.
     */
    public function getLifespanId() {
        return $this->lifespanID;
    }

    /**
     * Returns the ID of the lifespan proposer.
     * @todo  Make this consistent by renaming to getProposedId.
     * @return int Login ID of lifespan proposer.
     */
    public function getProposer() {
        return $this->proposer;
    }

    /**
     * Marks the lifespan as accepted and notifies the other party. Note that the status change is not made persistent until the object is passed to DBUpdate::instance()->lifespan().
     */
    public function accept() {
        $this->status = 'accepted';
        $this->notifyOfLifespanStatusChange('The other party has agreed your lifespan offer.');
    }

    /**
     * Marks the lifespan as declined and notifies the other party. Note that the status change is not made persistent until the object is passed to DBUpdate::instance()->lifespan().
     */
    public function decline() {
        $this->status = 'declined';
        $this->notifyOfLifespanStatusChange('The other party has declined your lifespan offer.');
    }

    /**
     * Change the end time of the lifespan to be the current time, and therefore end the lifespan. Used when dispute is closed (successfully or otherwise). Note that the time change is not made persistent until the object is passed to DBUpdate::instance()->lifespan().
     */
    public function endLifespan() {
        $this->endTime = time();
    }

    /**
     * Creates a notification for the other party informing them about the action the agent has taken on their lifespan offer.
     * @param  string $notification Notification message.
     */
    private function notifyOfLifespanStatusChange($notification) {
        $dispute = DBGet::instance()->dispute($this->disputeID);

        DBCreate::instance()->notification(array(
            'recipient_id' => $dispute->getOpposingPartyId(Session::instance()->getAccount()),
            'message'      => $notification,
            'url'          => $dispute->getUrl() . '/lifespan'
        ));
    }
}