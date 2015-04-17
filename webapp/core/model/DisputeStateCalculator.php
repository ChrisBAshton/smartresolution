<?php

class DisputeStateCalculator extends Prefab {

    public function getState($dispute, $account = false) {
        if (!$account) {
            $account = Session::instance()->getAccount();
        }

        $stateClass = $this->calculateStateClass($dispute);
        return new $stateClass($dispute, $account);
    }

    private function calculateStateClass($dispute) {
        if ($dispute->getStatus() !== 'ongoing') {
            return "DisputeClosed";
        }

        if ($dispute->getPartyB()->getLawFirm() === false) {
            return "DisputeCreated";
        }
        else if ($dispute->getPartyB()->getAgent() === false) {
            return "DisputeAssignedToLawFirmB";
        }

        if ($dispute->getCurrentLifespan()) {

            if ($dispute->getCurrentLifespan()->isEnded()) {
                return "DisputeClosed";
            }
            if (!$dispute->getCurrentLifespan()->accepted()) {
                return "DisputeOpened";
            }
            else {

                $mediationState = $dispute->getMediationState();

                if (!$mediationState->inMediation()) {
                    return "LifespanNegotiated";
                }
                elseif (!$dispute->inRoundTableCommunication()) {
                    return "InMediation";
                }
                else {
                    return "InRoundTableMediation";
                }
            }
        }
    }

    public function getActions($dispute, $account) {
        global $dashboardActions;
        $this->setDefaultActions($dispute, $account);
        ModuleController::instance()->emit('dispute_dashboard');
        return $dashboardActions;
    }

    public function setDefaultActions($dispute, $account) {
        global $dashboardActions;
        $dashboardActions = array();
        $state = $dispute->getState($account);

        $this->getMediatorSpecificOptions($dispute, $account);

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

    private function getMediatorSpecificOptions($dispute, $account) {
        global $dashboardActions;

        if ($account instanceof Mediator && $dispute->getMediationState()->inMediation()) {

            $agentA = $dispute->getPartyA()->getAgent();
            $agentB = $dispute->getPartyB()->getAgent();

            $dashboardActions[] = array(
                'title' => 'Round-Table Communication',
                'image' => '/core/view/images/message.png',
                'href'  => $dispute->getUrl() . '/chat/'
            );

            $dashboardActions[] = array(
                'title' => 'Communicate with ' . $agentA->getName(),
                'image' => '/core/view/images/message.png',
                'href'  => $dispute->getUrl() . '/mediation-chat/' . $agentA->getLoginId()
            );

            $dashboardActions[] = array(
                'title' => 'Communicate with ' . $agentB->getName(),
                'image' => '/core/view/images/message.png',
                'href'  => $dispute->getUrl() . '/mediation-chat/' . $agentB->getLoginId()
            );
        }
    }
}
