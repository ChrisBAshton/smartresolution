<?php

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
