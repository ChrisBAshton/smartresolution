<?php

class MediationState {

    private $mediationProposed;
    private $mightBeInMediation; // used privately for efficiency, should not need to be exposed publicly
    private $isInMediation;

    function __construct($data) {
        $this->mediationProposed  = !is_null($data['mediation_centre_offer']);
        $this->mightBeInMediation = !is_null($data['mediator_offer']);
    }

    public function mediationProposed() {
        return $this->mediationProposed;
    }

    public function isInMediation() {
        $this->isInMediation = false;

        if ($this->mightBeInMediation) {
            // @TODO - check database - need to check mediator_offer is accepted.
            $this->isInMediation = true;
        }

        return $this->isInMediation;
    }

}
