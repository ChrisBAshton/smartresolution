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

            errorPage('@TODO - Mediation is fully underway!');

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

            errorPage('@TODO - Mediation is fully underway!');

        endif;
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

                DBL::createMediationOffer(array(
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

}
