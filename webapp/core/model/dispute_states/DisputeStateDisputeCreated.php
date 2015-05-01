<?php

/**
 * This is the very first state of the dispute and represents a dispute that has just been created. At this stage, the first dispute party is complete (it will have a law firm and an agent associated with it), but it has not been opened against another law firm.
 */
class DisputeCreated extends DisputeDefaults implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute newly created.';
    }

    public function canNegotiateLifespan() {
        return false;
    }

}
