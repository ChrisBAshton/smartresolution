<?php

interface DisputeStateInterface {
    public function __construct($dispute, $account);
    public function getStateDescription();
    public function canOpenDispute();
    public function canAssignDisputeToAgent();
    public function canWriteSummary();
    public function canNegotiateLifespan();
    public function canSendMessage();
    public function canEditSummary();
    public function canCloseDispute();
}

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
        return $this->dispute->getLawFirmB() && $this->account instanceof LawFirm;
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

    public function canEditSummary() {
        return true;
    }

    public function canCloseDispute() {
        // both law firms and agents must be set before a dispute can be closed
        return (
            $this->dispute->getLawFirmA() && $this->dispute->getAgentA() &&
            $this->dispute->getLawFirmB() && $this->dispute->getAgentB()
        );
    }

    private function accountIsLinkedToDispute() {
        return (
            $this->accountIs($this->dispute->getLawFirmA()) ||
            $this->accountIs($this->dispute->getLawFirmB()) ||
            $this->accountIs($this->dispute->getAgentA())   ||
            $this->accountIs($this->dispute->getAgentB())
        );
    }

    private function accountIs($accountToCompare) {
        return $accountToCompare && $this->account->getLoginId() === $accountToCompare->getLoginId();
    }
}

class DisputeCreated extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute newly created.';
    }

    public function canNegotiateLifespan() {
        return false;
    }

}

class DisputeAssignedToLawFirmB extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute awaiting action from one of the two parties.';
    }

    public function canNegotiateLifespan() {
        return false;
    }

}

class DisputeOpened extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Negotiating lifespan.';
    }

    public function canOpenDispute() {
        return false;
    }

    public function canAssignDisputeToAgent() {
        return false;
    }

}

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

    public function canSendMessage() {
        return $this->dispute->getCurrentLifespan()->isCurrent();
    }
}

class InMediation extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute in mediation.';
    }

}

class InRoundTableMediation extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute in mediation.';
    }

}

class DisputeClosed extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute has closed.';
    }

    public function canOpenDispute() {
        return false;
    }

    public function canAssignDisputeToAgent() {
        return false;
    }

    public function canWriteSummary() {
        return false;
    }

    public function canNegotiateLifespan() {
        return false;
    }

    public function canCloseDispute() {
        return false;
    }
}
