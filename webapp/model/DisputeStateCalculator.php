<?php

class DisputeStateCalculator {

    public static function getState($dispute, $account = false) {
        if (!$account) {
            $account = Session::getAccount();
        }

        if ($dispute->getStatus() !== 'ongoing') {
            return new DisputeClosed($dispute, $account);
        }

        if ($dispute->getLawFirmB() === false) {
            return new DisputeCreated($dispute, $account);
        }
        else if ($dispute->getAgentB() === false) {
            return new DisputeAssignedToLawFirmB($dispute, $account);
        }

        if ($dispute->getCurrentLifespan()) {

            if ($dispute->getCurrentLifespan()->isEnded()) {
                return new DisputeClosed($dispute, $account);
            }
            if (!$dispute->getCurrentLifespan()->accepted()) {
                return new DisputeOpened($dispute, $account);
            }
            else {

                $mediationState = $dispute->getMediationState();

                if (!$mediationState->inMediation()) {
                    return new LifespanNegotiated($dispute, $account);
                }
                elseif (!$dispute->inRoundTableCommunication()) {
                    return new InMediation($dispute, $account);
                }
                else {
                    return new InRoundTableMediation($dispute, $account);
                }
            }
        }
    }

    // @TODO - NB, I got the icons from http://www.flaticon.com/packs/web-pictograms
    // Should probably document that somewhere better than here.
    public static function getActions($dispute, $account) {

        $state   = $dispute->getState($account);
        $actions = array();

        ModuleController::emit('dispute_dashboard', $dispute);

        if ($account instanceof Mediator && $dispute->getMediationState()->inMediation()) {

            $actions[] = array(
                'title' => 'Round-Table Communication',
                'image' => '/view/images/message.png',
                'href'  => $dispute->getUrl() . '/chat/'
            );

            $actions[] = array(
                'title' => 'Communicate with ' . $dispute->getAgentA()->getName(),
                'image' => '/view/images/message.png',
                'href'  => $dispute->getUrl() . '/mediation-chat/' . $dispute->getAgentA()->getLoginId()
            );

            $actions[] = array(
                'title' => 'Communicate with ' . $dispute->getAgentB()->getName(),
                'image' => '/view/images/message.png',
                'href'  => $dispute->getUrl() . '/mediation-chat/' . $dispute->getAgentB()->getLoginId()
            );
        }

        if ($state->canOpenDispute()) {
            $actions[] = array(
                'title' => 'Open dispute',
                'image' => '/view/images/dispute.png',
                'href'  => $dispute->getUrl() . '/open'
            );
        }

        if ($state->canAssignDisputeToAgent()) {
            $actions[] = array(
                'title' => 'Assign dispute to an agent',
                'image' => '/view/images/hand.png',
                'href'  => $dispute->getUrl() . '/assign'
            );
        }

        if ($state->canSendMessage() && ! ($account instanceof Mediator) ) {
            $actions[] = array(
                'title' => 'Communicate',
                'image' => '/view/images/message.png',
                'href'  => $dispute->getUrl() .'/chat',
            );
        }

        if ($state->canViewDocuments()) {
            $actions[] = array(
                'title' => 'Evidence',
                'image' => '/view/images/file.png',
                'href'  => $dispute->getUrl() .'/evidence',
            );
        }

        if ($state->canNegotiateLifespan()) {
            $actions[] = array(
                'title' => 'Lifespan',
                'image' => '/view/images/time.png',
                'href'  => $dispute->getUrl() .'/lifespan',
            );
        }

        if ($state->canProposeMediation()) {
            $actions[] = array(
                'title' => 'Mediation',
                'image' => '/view/images/cloud.png',
                'href'  => $dispute->getUrl() .'/mediation',
            );
        }

        if ($state->canEditSummary()) {
            $actions[] = array(
                'title' => 'Edit summary',
                'image' => '/view/images/summary.png',
                'href'  => $dispute->getUrl() . '/summary'
            );
        }

        if ($state->canCloseDispute()) {
            $actions[] = array(
                'title' => 'Close dispute',
                'image' => '/view/images/delete.png',
                'href'  => $dispute->getUrl() . '/close'
            );
        }

        return $actions;
    }
}
