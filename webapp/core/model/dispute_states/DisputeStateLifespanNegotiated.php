<?php

/**
 * The agents have managed to negotiate a lifespan and there is nothing more to do to initiate the dispute. When the start date is surpassed, the dispute is underway and the agents are free to perform all dispute-related actions. When the end date passes, the dispute is automatically closed.
 */
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
        return $this->account instanceof Agent || $this->account instanceof MediationCentre;
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

    public function canViewDocuments() {
        return $this->account instanceof Agent;
    }

    public function canUploadDocuments() {
        return $this->account instanceof Agent;
    }
}
