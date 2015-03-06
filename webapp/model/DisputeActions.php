<?php

class DisputeActions {

    public static function getActions($dispute, $account, $f3) {

        $state   = DisputeStateCalculator::getState($dispute, $account);
        $actions = array();

        if ($state->canOpenDispute()) {
            $actions[] = array(
                'title' => 'Open dispute',
                'href'  => $dispute->getUrl() . '/open'
            );
        }

        if ($state->canAssignDisputeToAgent()) {
            $actions[] = array(
                'title' => 'Assign dispute to an agent',
                'href'  => $dispute->getUrl() . '/assign'
            );
        }

        if ($state->canSendMessage()) {
            $actions[] = array(
                'title' => 'Communicate',
                'href'  => $dispute->getUrl() .'/chat',
            );
        }

        if ($state->canNegotiateLifespan()) {
            $actions[] = array(
                'title' => 'Negotiate dispute lifespan',
                'href'  => $dispute->getUrl() .'/lifespan',
            );
        }

        if ($state->canCloseDispute()) {
            $actions[] = array(
                'title' => 'Close dispute',
                'href'  => $dispute->getUrl() . '/close'
            );
        }

        // if ($state instanceof DisputeCreated && $account instanceof LawFirm) {
        //     $f3->set('status', array(
        //         'message' => 'You are waiting for ' . $dispute->getAgentA()->getName() . ' to open the dispute against another law firm.',
        //         'class'   => 'bg-padded bg-info'
        //     ));
        // }

        // elseif ($state instanceof DisputeAssignedToLawFirmB) {
        //     $f3->set('status', array(
        //         'message' => 'You are waiting for ' . $dispute->getLawFirmB()->getName() . ' to assign an agent to the dispute.',
        //         'class'   => 'bg-padded bg-info'
        //     ));        
        // }
        
        return $actions;
    }
}