<?php

class InMediation extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute in mediation.';
    }

    public function canProposeMediation() {
        return true;
    }

    public function canViewDocuments() {
        return $this->account instanceof Agent || $this->account instanceof Mediator;
    }

    public function canUploadDocuments() {
        return $this->account instanceof Agent;
    }

}
