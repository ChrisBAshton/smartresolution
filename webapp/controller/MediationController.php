<?php

class MediationController {

    private function setUp($f3, $params) {
        $this->account = mustBeLoggedIn();
        $this->dispute = setDisputeFromParams($f3, $params);

        if (!$this->dispute->getState($this->account)->canProposeMediation()) {
            errorPage('You do not have permission to view this page.');
        }

        $this->mediationState = $this->dispute->getMediationState();
    }

    public function view ($f3, $params) {
        $this->setUp($f3, $params);

        if ($this->account instanceof MediationCentre ||
            $this->account instanceof Mediator) {
            $this->viewInContextOfMediators($f3);
        }
        else {
            $this->viewInContextOfAgents($f3);
        }

        echo View::instance()->render('layout.html');
    }

    private function viewInContextOfMediators($f3) {
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

    private function viewInContextOfAgents($f3) {
        if (!$this->mediationState->mediationCentreProposed()) :

            $mediationCentres = Utils::getOrganisations(array(
                'type'   => 'mediation_centre'
            ));

            $f3->set('mediationCentres', $mediationCentres);
            $f3->set('content', 'mediation_new.html');

        elseif (!$this->mediationState->mediationCentreDecided()) :

            $f3->set('proposed_mediation_party', $this->mediationState->getMediationCentre());
            $f3->set('proposed_by',              $this->mediationState->getMediationCentreProposer());
            $f3->set('content', 'mediation_proposed.html');

        elseif (!$this->mediationState->mediatorProposed()) :

            $availableMediators = DBMediation::getAvailableMediators($this->dispute->getDisputeId());
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
     * Actioned by Mediation Centre.
     */
    public function chooseListOfMediators($f3, $params) {
        $this->setUp($f3, $params);
        $availableMediators = $f3->get('POST.available_mediators');
        if (!$availableMediators) {
            $availableMediators = array();
        }
        DBMediation::saveListOfMediators($this->dispute->getDisputeId(), $availableMediators);
        $this->notifyAgentsOfUpdatedList();
        header('Location: ' . $this->dispute->getUrl() . '/mediation');
    }

    public function createMediationOffer ($f3, $params) {
        $account           = mustBeLoggedIn();
        $dispute           = setDisputeFromParams($f3, $params);
        $mediationCentreId = $f3->get('POST.mediation_centre');

        if (!$mediationCentreId || $mediationCentreId === '---') {
            $f3->set('error_message', 'Please choose a Mediation Centre.');
        }
        else {
            try {
                $mediationCentre = new MediationCentre((int) $mediationCentreId);

                DBL::createMediationCentreOffer(array(
                    'dispute'          => $dispute,
                    'proposed_by'      => $account,
                    'mediation_centre' => $mediationCentre
                ));

                $f3->set('success_message', "You have proposed " . $mediationCentre->getName() . " to mediate your dispute.");

            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }

        $this->view($f3, $params);
    }

    public function chooseMediatorFromList($f3, $params) {
        $account    = mustBeLoggedIn();
        $dispute    = setDisputeFromParams($f3, $params);
        $mediatorId = $f3->get('POST.mediator');

        if ($mediatorId) {
            try {
                $mediator = new Mediator((int) $mediatorId);

                DBL::createMediatorOffer(array(
                    'dispute'     => $dispute,
                    'proposed_by' => $account,
                    'mediator'    => $mediator
                ));

            } catch(Exception $e) {
            }
        }

        header('Location: ' . $dispute->getUrl() . '/mediation');
    }

    private function notifyAgentsOfUpdatedList() {
        DBL::createNotification(array(
            'recipient_id' => $this->dispute->getAgentA()->getLoginId(),
            'message'      => $this->account->getName() . ' has selected a list of available mediators for your dispute.',
            'url'          => $this->dispute->getUrl() . '/mediation'
        ));

        DBL::createNotification(array(
            'recipient_id' => $this->dispute->getAgentB()->getLoginId(),
            'message'      => $this->account->getName() . ' has selected a list of available mediators for your dispute.',
            'url'          => $this->dispute->getUrl() . '/mediation'
        ));
    }

    public function viewMessages($f3, $params) {
        $this->setUp($f3, $params);
        $recipientID = (int) $params['recipientID'];
        if (!$this->dispute->canBeViewedBy($recipientID)) {
            errorPage("The account you're trying to send a message to is not involved in this dispute!");
        }
        $this->viewMessagesWith($recipientID);
        echo View::instance()->render('layout.html');
    }

    private function viewMessagesWith($recipientID) {
        global $f3;
        $messages = new Messages($this->dispute->getDisputeId(), $this->account->getLoginId(), $recipientID);
        $f3->set('recipientID', $recipientID);
        $f3->set('messages', $messages->getMessages());
        $f3->set('content', 'messages.html');
    }

    public function newMessage ($f3, $params) {
        $this->setUp($f3, $params);
        $message     = $f3->get('POST.message');
        $recipientID = $f3->get('POST.recipient_id');

        if ($message && $recipientID) {

            DBL::createMessage(array(
                'dispute_id'   => $this->dispute->getDisputeId(),
                'author_id'    => $this->account->getLoginId(),
                'message'      => $message,
                'recipient_id' => (int) $recipientID
            ));

        }
        header('Location: ' . $this->dispute->getUrl() . '/mediation-chat/' . $recipientID);
    }

    public function roundTableCommunication($f3, $params) {
        $this->setUp($f3, $params);
        $enableOrDisable = $f3->get('POST.action');
        if ($enableOrDisable && $this->account instanceof Mediator) {
            Database::instance()->exec(
                'UPDATE disputes SET round_table_communication = :bool WHERE dispute_id = :dispute_id',
                array(
                    ':dispute_id' => $this->dispute->getDisputeId(),
                    ':bool'       => $enableOrDisable === 'enable' ? 'true' : 'false'
                )
            );
            DBL::createNotification(array(
                'recipient_id' => $this->dispute->getAgentA()->getLoginId(),
                'message'      => 'The mediator has ' . $enableOrDisable . 'd round-table-communication.',
                'url'          => $this->dispute->getUrl() . '/chat'
            ));
            DBL::createNotification(array(
                'recipient_id' => $this->dispute->getAgentB()->getLoginId(),
                'message'      => 'The mediator has ' . $enableOrDisable . 'd round-table-communication.',
                'url'          => $this->dispute->getUrl() . '/chat'
            ));
        }
        header('Location: ' . $this->dispute->getUrl() . '/mediation');
    }

}
