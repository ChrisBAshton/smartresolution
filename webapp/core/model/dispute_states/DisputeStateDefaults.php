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
        return $this->account instanceof Agent && !$this->dispute->getPartyB()->getLawFirm();
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
        return $this->account instanceof Agent || $this->account instanceof LawFirm;
    }

    public function canProposeMediation() {
        return false;
    }

    public function canCloseDispute() {
        return $this->account instanceof Agent || $this->account instanceof LawFirm;
    }

    private function accountIsLinkedToDispute() {
        return (
            $this->accountIs($this->dispute->getPartyA()->getLawFirm()) ||
            $this->accountIs($this->dispute->getPartyB()->getLawFirm()) ||
            $this->accountIs($this->dispute->getPartyA()->getAgent())   ||
            $this->accountIs($this->dispute->getPartyB()->getAgent())   ||
            $this->accountIs($this->dispute->getMediationState()->getMediationCentre()) ||
            $this->accountIs($this->dispute->getMediationState()->getMediator())
        );
    }

    protected function accountIs($accountToCompare) {
        return $accountToCompare && $this->account->getLoginId() === $accountToCompare->getLoginId();
    }
}
