<?php

class DisputeStateCalculator {

    public static function getState($dispute, $account) {
        if ($dispute->getLawFirmB() === false) {
            return new DisputeCreated($dispute, $account);
        }
        else if ($dispute->getAgentB() === false) {
            return new DisputeAssignedToLawFirmB($dispute, $account);
        }

        if ($dispute->getLifespan()) {
            return new LifespanNegotiated($dispute, $account);
        }
    }

    // public function hasBeenOpened() {
    //     return $this->getLawFirmB() !== false;
    // }

    // public function hasNotBeenOpened() {
    //     return !$this->hasBeenOpened();
    // }

    // public function waitingForLawFirmB() {
    //     if ($this->hasBeenOpened()) {
    //         return $this->getAgentB() === false;
    //     }
    //     return true;
    // }

}

interface DisputeState {
    public function __construct($dispute, $account);
    public function canOpenDispute();
    public function canAssignDisputeToAgent();
    public function canWriteSummary();
    public function canNegotiateLifespan();
    public function canSendMessage();
    public function canCloseDispute();
}

class DisputeDefaults implements DisputeState {

    public function __construct($dispute, $account) {
        $this->dispute = $dispute;
        $this->account = $account;
    }

    public function canOpenDispute() {
        return $this->account instanceof Agent && !$this->dispute->getLawFirmB();
    }

    public function canAssignDisputeToAgent() {
        return (
            $this->account instanceof LawFirm &&
            (
                $this->account->getLoginId() === $dispute->getLawFirmB()->getLoginId() ||
                $this->account->getLoginId() === $dispute->getLawFirmA()->getLoginId()
            )
        );
    }

    public function canWriteSummary() {
        return $this->account instanceof Agent;
    }

    public function canNegotiateLifespan() {
        return true;
    }

    public function canSendMessage() {
        return false;
    }

    public function canCloseDispute() {
        return (
            $this->account->getLoginId() === $this->dispute->getAgentA()->getLoginId()   ||
            $this->account->getLoginId() === $this->dispute->getAgentB()->getLoginId()   ||
            $this->account->getLoginId() === $this->dispute->getLawFirmA()->getLoginId() ||
            $this->account->getLoginId() === $this->dispute->getLawFirmB()->getLoginId()
        );
    }    
}

class DisputeCreated extends DisputeDefaults implements DisputeState {

    public function canNegotiateLifespan() {
        return false;
    }

}

class DisputeAssignedToLawFirmB extends DisputeDefaults implements DisputeState {

    public function canNegotiateLifespan() {
        return false;
    }

}

class DisputeOpened extends DisputeDefaults implements DisputeState {


    public function canOpenDispute() {
        return false;
    }

    public function canAssignDisputeToAgent() {
        return false;
    }

}

class LifespanNegotiated extends DisputeDefaults implements DisputeState {

    public function canOpenDispute() {
        return false;
    }

    public function canAssignDisputeToAgent() {
        return false;
    }

    public function canSendMessage() {
        return $this->dispute instanceof Agent;
    }
}

class InMediation extends DisputeDefaults implements DisputeState {

}

class InRoundTableMediation extends DisputeDefaults implements DisputeState {

}