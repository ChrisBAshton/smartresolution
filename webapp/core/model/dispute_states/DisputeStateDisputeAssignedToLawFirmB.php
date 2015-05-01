<?php

/**
 * This represents the state of the dispute when it has just been assigned to the other law firm. At this stage, one dispute party is complete, whilst the other only has the law firm. We are still waiting for the law firm to assign an agent.
 */
class DisputeAssignedToLawFirmB extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute awaiting action from one of the two parties.';
    }

    public function canAssignDisputeToAgent() {
        return $this->accountIs($this->dispute->getPartyB()->getLawFirm());
    }

    public function canNegotiateLifespan() {
        return false;
    }

}
