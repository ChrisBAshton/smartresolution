<?php

class MediationCentre extends Organisation {

    public function getMediators() {
        return parent::getIndividuals('Mediator');
    }

}
