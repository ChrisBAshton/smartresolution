<?php

class DisputeCreated extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute newly created.';
    }

    public function canNegotiateLifespan() {
        return false;
    }

    public function canUploadDocuments() {
        return false;
    }

}
