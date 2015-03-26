<?php

abstract class DisputeDefaults {

    public function __construct($dispute, $account) {
        $this->dispute = $dispute;
        $this->account = $account;
        if (!$this->accountIsLinkedToDispute()) {
            throw new Exception($account->getName() . ' is not permitted to view this dispute!');
        }
    }

    public function canOpenDispute() {
        return $this->account instanceof Agent && !$this->dispute->getLawFirmB();
    }

    public function canAssignDisputeToAgent() {
        return false;
    }

    public function canWriteSummary() {
        return $this->account instanceof Agent;
    }

    public function canNegotiateLifespan() {
        return $this->account instanceof Agent;
    }

    public function canSendMessage() {
        return false;
    }

    public function canViewDocuments() {
        return false;
    }

    public function canUploadDocuments() {
        return false;
    }

    public function canEditSummary() {
        return true;
    }

    public function canProposeMediation() {
        return false;
    }

    public function canCloseDispute() {
        return true;
    }

    private function accountIsLinkedToDispute() {
        return (
            $this->accountIs($this->dispute->getLawFirmA()) ||
            $this->accountIs($this->dispute->getLawFirmB()) ||
            $this->accountIs($this->dispute->getAgentA())   ||
            $this->accountIs($this->dispute->getAgentB())   ||
            $this->accountIs($this->dispute->getMediationState()->getMediationCentre()) ||
            $this->accountIs($this->dispute->getMediationState()->getMediator())
        );
    }

    protected function accountIs($accountToCompare) {
        return $accountToCompare && $this->account->getLoginId() === $accountToCompare->getLoginId();
    }
}
