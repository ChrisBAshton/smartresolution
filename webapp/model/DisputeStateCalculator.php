<?php

class DisputeStateCalculator {

    public static function getState($dispute, $account = false) {
        if (!$account) {
            $account = Session::getAccount();
        }
        
        if ($dispute->getLawFirmB() === false) {
            return new DisputeCreated($dispute, $account);
        }
        else if ($dispute->getAgentB() === false) {
            return new DisputeAssignedToLawFirmB($dispute, $account);
        }

        if ($dispute->getLifespan()) {

            if ($dispute->getLifespan()->accepted()) {
                return new LifespanNegotiated($dispute, $account);
            }
            else {
                return new DisputeOpened($dispute, $account);
            }
        }
    }

    public static function getActions($dispute, $account) {

        $state   = $dispute->getState($account);
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

        if ($state->canEditSummary()) {
            $actions[] = array(
                'title' => 'Edit summary',
                'href'  => $dispute->getUrl() . '/summary'
            );
        }

        if ($state->canCloseDispute()) {
            $actions[] = array(
                'title' => 'Close dispute',
                'href'  => $dispute->getUrl() . '/close'
            );
        }
        
        return $actions;
    }
}