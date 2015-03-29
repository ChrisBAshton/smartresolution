<?php

class InRoundTableMediation extends InMediation implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute in round-table mediation.';
    }

    public function canSendMessage() {
        return $this->dispute->getCurrentLifespan()->isCurrent() && $this->account instanceof Individual;
    }

}