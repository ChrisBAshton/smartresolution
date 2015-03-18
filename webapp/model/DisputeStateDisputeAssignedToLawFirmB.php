<?php

class DisputeAssignedToLawFirmB extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute awaiting action from one of the two parties.';
    }

    public function canAssignDisputeToAgent() {
        return $this->accountIs($this->dispute->getLawFirmB());
    }

    public function canNegotiateLifespan() {
        return false;
    }

    public function canUploadDocuments() {
        return false;
    }

}
