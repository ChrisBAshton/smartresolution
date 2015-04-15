<?php

class DisputeStateCalculator {

    public static function getState($dispute, $account = false) {
        if (!$account) {
            $account = Session::getAccount();
        }

        if ($dispute->getStatus() !== 'ongoing') {
            return new DisputeClosed($dispute, $account);
        }

        if ($dispute->getPartyB()->getLawFirm() === false) {
            return new DisputeCreated($dispute, $account);
        }
        else if ($dispute->getPartyB()->getAgent() === false) {
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
        global $dashboardActions;
        DisputeStateCalculator::setDefaultActions($dispute, $account);
        ModuleController::emit('dispute_dashboard', $dispute);
        return $dashboardActions;
    }

    public static function setDefaultActions($dispute, $account) {
        $state = $dispute->getState($account);
        global $dashboardActions;
        $dashboardActions = array();

        if ($account instanceof Mediator && $dispute->getMediationState()->inMediation()) {

            $dashboardActions[] = array(
                'title' => 'Round-Table Communication',
                'image' => '/core/view/images/message.png',
                'href'  => $dispute->getUrl() . '/chat/'
            );

            $dashboardActions[] = array(
                'title' => 'Communicate with ' . $dispute->getPartyA()->getAgent()->getName(),
                'image' => '/core/view/images/message.png',
                'href'  => $dispute->getUrl() . '/mediation-chat/' . $dispute->getPartyA()->getAgent()->getLoginId()
            );

            $dashboardActions[] = array(
                'title' => 'Communicate with ' . $dispute->getPartyB()->getAgent()->getName(),
                'image' => '/core/view/images/message.png',
                'href'  => $dispute->getUrl() . '/mediation-chat/' . $dispute->getPartyB()->getAgent()->getLoginId()
            );
        }

        if ($state->canOpenDispute()) {
            $dashboardActions[] = array(
                'title' => 'Open dispute',
                'image' => '/core/view/images/dispute.png',
                'href'  => $dispute->getUrl() . '/open'
            );
        }

        if ($state->canAssignDisputeToAgent()) {
            $dashboardActions[] = array(
                'title' => 'Assign dispute to an agent',
                'image' => '/core/view/images/hand.png',
                'href'  => $dispute->getUrl() . '/assign'
            );
        }

        if ($state->canSendMessage() && ! ($account instanceof Mediator) ) {
            $dashboardActions[] = array(
                'title' => 'Communicate',
                'image' => '/core/view/images/message.png',
                'href'  => $dispute->getUrl() .'/chat',
            );
        }

        if ($state->canViewDocuments()) {
            $dashboardActions[] = array(
                'title' => 'Evidence',
                'image' => '/core/view/images/file.png',
                'href'  => $dispute->getUrl() .'/evidence',
            );
        }

        if ($state->canNegotiateLifespan()) {
            $dashboardActions[] = array(
                'title' => 'Lifespan',
                'image' => '/core/view/images/time.png',
                'href'  => $dispute->getUrl() .'/lifespan',
            );
        }

        if ($state->canProposeMediation()) {
            $dashboardActions[] = array(
                'title' => 'Mediation',
                'image' => '/core/view/images/cloud.png',
                'href'  => $dispute->getUrl() .'/mediation',
            );
        }

        if ($state->canEditSummary()) {
            $dashboardActions[] = array(
                'title' => 'Edit',
                'image' => '/core/view/images/summary.png',
                'href'  => $dispute->getUrl() . '/summary'
            );
        }

        if ($state->canCloseDispute()) {
            $dashboardActions[] = array(
                'title' => 'Close dispute',
                'image' => '/core/view/images/delete.png',
                'href'  => $dispute->getUrl() . '/close'
            );
        }
    }
}
