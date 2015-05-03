<?php

/**
 * The dispute is in mediation, but all parties are free to communicate openly. By default, a dispute in mediation disables direct communication between the two agents. The mediator can enable round-table communication to put the dispute into this state.
 */
class InRoundTableMediation extends InMediation implements DisputeStateInterface {

    public function getStateDescription() {
        return 'Dispute in round-table mediation.';
    }

    public function canSendMessage() {
        return $this->dispute->getCurrentLifespan()->isCurrent() && $this->account instanceof Individual;
    }

}