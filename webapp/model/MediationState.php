<?php

class MediationState {

    function __construct($data) {
        // if mediation_centre_offer is null, mediation has not even been proposed.
        $this->mediationProposed  = !is_null($data['mediation_centre_offer']);

        // might be in mediation - need to check mediator_offer is accepted.
        $this->mightBeInMediation = !is_null($data['mediator_offer']);

        echo $this->mediationProposed  ? "Mediation has been proposed." : 'Mediation has not yet been proposed.';
        echo $this->mightBeInMediation ? "This is in mediation" : 'This might be in mediation - need to check.';
    }

}
