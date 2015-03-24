<?php

class InMediation extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute in mediation.';
    }

    public function canProposeMediation() {
        return $this->account instanceof Agent;
    }

}
