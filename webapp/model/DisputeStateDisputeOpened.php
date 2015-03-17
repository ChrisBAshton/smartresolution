<?php

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
