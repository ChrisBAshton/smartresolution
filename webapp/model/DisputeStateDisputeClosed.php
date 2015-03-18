<?php

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

    public function canUploadDocuments() {
        return false;
    }

    public function canCloseDispute() {
        return false;
    }
}
