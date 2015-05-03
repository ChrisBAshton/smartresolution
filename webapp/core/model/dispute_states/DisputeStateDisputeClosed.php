<?php

/**
 * The dispute is now closed, either because an agent closed it or because the lifespan of the dispute came to an end. It may have been closed successfully (the dispute was resolved) or unsuccessfully (the dispute had to be resolved by other means, e.g. court).
 */
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

    public function canEditSummary() {
        return false;
    }

    public function canNegotiateLifespan() {
        return false;
    }

    public function canCloseDispute() {
        return false;
    }
}
