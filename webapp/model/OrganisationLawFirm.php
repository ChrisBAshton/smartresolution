<?php

class LawFirm extends Organisation {

    public function getAgents() {
        return parent::getIndividuals('Agent');
    }

}
