<?php

/**
 * All law firms and agents have been assigned. Now a lifespan must be negotiated.
 */
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
