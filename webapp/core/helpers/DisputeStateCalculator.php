<?php

/**
 * Calculates the current state of the dispute, instantiating a DisputeState object which can then be queried, making business logic easy to query in the controllers/models.
 */
class DisputeStateCalculator extends Prefab {

    /**
     * Retrieves the dispute's calculated state.
     * @param  Dispute         $dispute Dispute whose state we need to calculate.
     * @param  Account|boolean $account (Optional) The account whose context we want to pass to the querying of the state. For example, a state->canUploadEvidence() call may return true or false depending on the user querying the state. Defaults to the currently logged in account.
     * @return DisputeState
     */
    public function getState($dispute, $account = false) {
        if (!$account) {
            $account = Session::instance()->getAccount();
        }

        $stateClass = $this->calculateStateClass($dispute);
        return new $stateClass($dispute, $account);
    }

    /**
     * Calculates the state of the dispute.
     * @param  Dispute $dispute
     * @return DisputeState
     */
    public function calculateStateClass($dispute) {
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

    /**
     * Retrieves an array of actions to be rendered to the dispute dashboard, according to the state of the dispute.
     * @param  Dispute $dispute The dispute whose dashboard we're rendering.
     * @param  Account $account The account whose context will affect which options are on the dashboard.
     * @return array            Array of dashboard items.
     *         string array['title']
     *         string array['image']
     *         string array['href']
     */
    public function getActions($dispute, $account) {
        global $dashboardActions;
        $this->setDefaultActions($dispute, $account);
        ModuleController::instance()->emit('dispute_dashboard', $dispute);
        return $dashboardActions;
    }

    /**
     * Sets the default dispute dashboard actions (according to the dispute state), BEFORE any module modifications.
     * @param Dispute $dispute
     * @param Account $account
     */
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

    /**
     * Calculates the mediator-specific options for the dispute dashboard. Only applies if the given account is a Mediator and the dispute is in mediation.
     * @param  Dispute $dispute
     * @param  Account $account
     * @return array     Dashboard actions.
     */
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
