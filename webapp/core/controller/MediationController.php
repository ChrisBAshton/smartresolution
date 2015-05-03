<?php

/**
 * Handles mediation-related HTTP requests. In terms of controllers, this is definitely the weak point of the system and needs some serious refactoring (too few methods doing too many things).
 * @todo  refactor
 */
class MediationController {

    /**
     * View the mediation page. The contents of this page will vary according to the state of mediation.
     * To begin with, it will be a list of mediation centres that either agent can propose. When an agent has
     * proposed a mediation centre, the other agent will have the option to accept that mediation centre.
     * The mediation centre then needs to select a list of available mediators. The above steps then repeat for the mediator. Finally, with a mediator decided, agents will be able to communicate directly with the mediator from this view. With round-table communication enabled, the MEDIATOR can communicate with both agents at the same time through this view (1:1 communication is handled separately).
     *
     * @todo  refactor
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function view ($f3, $params) {
        $this->setUp($f3, $params);

        if ($this->account instanceof MediationCentre ||
            $this->account instanceof Mediator) {
            $this->viewInContextOfMediationEntities($f3);
        }
        else {
            $this->viewInContextOfAgents($f3);
        }

        echo View::instance()->render('layout.html');
    }

    /**
     * Retrieves the mediation state and performs checks such as whether or not the user is allowed to view mediation-related pages.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    private function setUp($f3, $params) {
        $this->account = mustBeLoggedIn();
        $this->dispute = setDisputeFromParams($f3, $params);

        if (!$this->dispute->getState($this->account)->canProposeMediation()) {
            errorPage('You do not have permission to view this page.');
        }

        $this->mediationState = $this->dispute->getMediationState();
    }

    /**
     * Views the mediation page in the context of the mediation centre or mediator viewing the page.
     * From top to bottom:
     *     if mediation centre hasn't been decided yet, nothing to do, so error page triggered.
     *     if mediator hasn't been proposed yet and we are mediation centre, show list of mediators and allow selection of which ones are available.
     *     if mediator hasn't been decided (but HAS been proposed), display message to mediation centre but don't allow them to change list of available mediators.
     *     if mediator has been decided, this is the round-table communication screen for the mediator.
     *
     * @param  F3 $f3         The base F3 object.
     */
    private function viewInContextOfMediationEntities($f3) {
        if (!$this->mediationState->mediationCentreDecided()) :
            errorPage('Trying to view dispute in context of mediator, but mediation centre has not been decided yet!');

        // if we are the mediation centre and neither agent has yet proposed a mediator from our list,
        // we should be allowed to decide which mediators are on the list.
        elseif ($this->account instanceof MediationCentre && !$this->mediationState->mediatorProposed()) :

            $f3->set('mediators', $this->account->getIndividuals('Mediator'));
            $f3->set('content', 'mediation__choose_list.html');

        elseif (!$this->mediationState->mediatorDecided()) :

            $f3->set('proposed_mediation_party', $this->mediationState->getMediator());
            $f3->set('proposed_by',              $this->mediationState->getMediatorProposer());
            $f3->set('content', 'mediation_proposed.html');

        else :

            $f3->set('content', 'mediator__round_table_communications.html');

        endif;
    }

    /**
     * Views the mediation page in the context of the agents viewing the page.
     * From top to bottom:
     *     if mediation centre hasn't been proposed yet, offer form to propose mediation centre.
     *     if mediation centre hasn't been decided yet, view the 'mediation centre proposed' screen.
     *     if mediator hasn't been proposed yet, offer form to propose mediator.
     *     if mediator hasn't been decided yet, view the 'mediator proposed' screen.
     *
     * @param  F3 $f3         The base F3 object.
     */
    private function viewInContextOfAgents($f3) {
        if (!$this->mediationState->mediationCentreProposed()) :

            $mediationCentres = DBQuery::instance()->getOrganisations(array(
                'type'   => 'mediation_centre'
            ));

            $f3->set('mediationCentres', $mediationCentres);
            $f3->set('content', 'mediation_new.html');

        elseif (!$this->mediationState->mediationCentreDecided()) :

            $f3->set('proposed_mediation_party', $this->mediationState->getMediationCentre());
            $f3->set('proposed_by',              $this->mediationState->getMediationCentreProposer());
            $f3->set('content', 'mediation_proposed.html');

        elseif (!$this->mediationState->mediatorProposed()) :

            $availableMediators = DBMediation::instance()->getAvailableMediators($this->dispute->getDisputeId());
            $f3->set('available_mediators', $availableMediators);
            $f3->set('content', 'mediation__choose_mediator_from_list.html');

        elseif (!$this->mediationState->mediatorDecided()) :

            $f3->set('proposed_mediation_party', $this->mediationState->getMediator());
            $f3->set('proposed_by',              $this->mediationState->getMediatorProposer());
            $f3->set('content', 'mediation_proposed.html');

        else :

            $this->viewMessagesWith($this->mediationState->getMediator()->getLoginId());

        endif;
    }

    /**
     * POST method; accept or decline a mediation centre or mediator proposal.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function respondToProposal($f3, $params) {
        $account    = mustBeLoggedInAsAn('Agent');
        $dispute    = setDisputeFromParams($f3, $params);
        $resolution = $f3->get('POST.resolution');

        if ($resolution === 'accept') {
            $dispute->getMediationState()->acceptLatestProposal();
        }
        else if ($resolution === 'decline') {
            $dispute->getMediationState()->declineLatestProposal();
        }

        header('Location: ' . $dispute->getUrl() . '/mediation');
    }

    /**
     * POST method; submit a list of available mediators. This was actioned by the Mediation Centre.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function chooseListOfMediators($f3, $params) {
        $this->setUp($f3, $params);
        $availableMediators = $f3->get('POST.available_mediators');
        if (!$availableMediators) {
            $availableMediators = array();
        }
        DBMediation::instance()->saveListOfMediators($this->dispute->getDisputeId(), $availableMediators);
        $this->notifyAgentsOfUpdatedList();
        header('Location: ' . $this->dispute->getUrl() . '/mediation');
    }

    /**
     * POST method; submit a list of available mediators. This was actioned by the Mediation Centre.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function createMediationOffer ($f3, $params) {
        $account           = mustBeLoggedIn();
        $dispute           = setDisputeFromParams($f3, $params);
        $mediationCentreId = $f3->get('POST.mediation_centre');

        if (!$mediationCentreId || $mediationCentreId === '---') {
            $f3->set('error_message', 'Please choose a Mediation Centre.');
        }
        else {
            try {
                $mediationCentre = DBGet::instance()->account((int) $mediationCentreId);

                DBCreate::instance()->mediationCentreOffer(array(
                    'dispute_id'  => $dispute->getDisputeId(),
                    'proposer_id' => $account->getLoginId(),
                    'proposed_id' => $mediationCentre->getLoginId()
                ));

                $f3->set('success_message', "You have proposed " . $mediationCentre->getName() . " to mediate your dispute.");

            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }

        $this->view($f3, $params);
    }

    /**
     * POST method; agent selects a mediator they wish to use to mediate the dispute. The other agent still needs to confirm that proposal before the mediator is finalised.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function chooseMediatorFromList($f3, $params) {
        $account    = mustBeLoggedIn();
        $dispute    = setDisputeFromParams($f3, $params);
        $mediatorId = $f3->get('POST.mediator');

        if ($mediatorId) {
            try {
                $mediator = DBGet::instance()->account((int) $mediatorId);

                DBCreate::instance()->mediatorOffer(array(
                    'dispute_id'  => $dispute->getDisputeId(),
                    'proposer_id' => $account->getLoginId(),
                    'proposed_id' => $mediator->getLoginId()
                ));

            } catch(Exception $e) {
            }
        }

        header('Location: ' . $dispute->getUrl() . '/mediation');
    }

    /**
     * Notifies both agents that the mediation centre has updated the list of available mediators.
     */
    private function notifyAgentsOfUpdatedList() {
        DBCreate::instance()->notification(array(
            'recipient_id' => $this->dispute->getPartyA()->getAgent()->getLoginId(),
            'message'      => $this->account->getName() . ' has selected a list of available mediators for your dispute.',
            'url'          => $this->dispute->getUrl() . '/mediation'
        ));

        DBCreate::instance()->notification(array(
            'recipient_id' => $this->dispute->getPartyB()->getAgent()->getLoginId(),
            'message'      => $this->account->getName() . ' has selected a list of available mediators for your dispute.',
            'url'          => $this->dispute->getUrl() . '/mediation'
        ));
    }

    /**
     * Triggered by HTTP request: /disputes/@disputeID/mediation-chat/@recipientID
     * Mediator can have a private conversation between either of the agents through this method.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function viewMessages($f3, $params) {
        $this->setUp($f3, $params);
        $recipientID = (int) $params['recipientID'];
        if (!$this->dispute->canBeViewedBy($recipientID) || DBGet::instance()->account($recipientID) instanceof Organisation) {
            errorPage("The account you're trying to send a message to is not involved in this dispute!");
        }
        $this->viewMessagesWith($recipientID);
        echo View::instance()->render('layout.html');
    }

    /**
     * Loads the messages between the current account and the given account.
     * Used by the mediator in mediator:agent communication:
     *     /disputes/@disputeID/mediation-chat/@recipientID
     * Also used by the agents in agent:mediator communication:
     *     /disputes/@disputeID/mediation
     * @param  int $recipientID Login ID of the recipient whose message stream we want to load.
     */
    private function viewMessagesWith($recipientID) {
        global $f3;
        $f3->set('recipientID', $recipientID);
        $f3->set('messages', $this->dispute->getMessagesBetween($this->account->getLoginId(), $recipientID));
        $f3->set('content', 'messages.html');
    }

    /**
     * POST method; creates a new agent-mediator message and redirects back to the specific 1:1 chat stream URL.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function newMessage ($f3, $params) {
        $this->setUp($f3, $params);
        $message     = $f3->get('POST.message');
        $recipientID = $f3->get('POST.recipient_id');

        if ($message && $recipientID) {
            DBCreate::instance()->message(array(
                'dispute_id'   => $this->dispute->getDisputeId(),
                'author_id'    => $this->account->getLoginId(),
                'message'      => $message,
                'recipient_id' => (int) $recipientID
            ));
        }

        header('Location: ' . $this->dispute->getUrl() . '/mediation-chat/' . $recipientID);
    }

    /**
     * POST method; enables or disables round-table communication.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     *
     * @todo  refactor. This should do dispute->state->canEnableRoundTableCommunication($account). Delegate the business logic to the state pattern, don't encode it in the controller. This principle needs to be applied elsewhere too.
     */
    public function roundTableCommunication($f3, $params) {
        $this->setUp($f3, $params);
        $enableOrDisable = $f3->get('POST.action');
        if ($enableOrDisable && $this->account instanceof Mediator) {
            if ($enableOrDisable === 'enable') {
                $this->dispute->enableRoundTableCommunication();
            }
            else {
                $this->dispute->disableRoundTableCommunication();
            }
            DBUpdate::instance()->dispute($this->dispute);
        }
        header('Location: ' . $this->dispute->getUrl() . '/mediation');
    }

}
