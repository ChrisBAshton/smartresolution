<?php

class LifespanNegotiated extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute in progress.';
    }

    public function canOpenDispute() {
        return false;
    }

    public function canAssignDisputeToAgent() {
        return false;
    }

    public function canProposeMediation() {
        return true;
    }

    public function canSendMessage() {
        return $this->dispute->getCurrentLifespan()->isCurrent();
    }
}
