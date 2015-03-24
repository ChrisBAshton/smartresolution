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
        return $this->account instanceof Agent;
    }

    public function canEditSummary() {
        return ($this->account instanceof Agent || $this->account instanceof LawFirm);
    }

    public function canCloseDispute() {
        return ($this->account instanceof Agent || $this->account instanceof LawFirm);
    }

    public function canSendMessage() {
        return $this->dispute->getCurrentLifespan()->isCurrent() && $this->account instanceof Individual;
    }
}
