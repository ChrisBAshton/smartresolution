<?php

/**
 * The agents have decided to put the dispute into mediation and have negotiated a mediation centre and a mediator. It is important to note that not all disputes will necessarily reach this stage.
 */
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
