<?php

class MediationController {

    public function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);
        $mediationState = $dispute->getMediationState();

        if (!$mediationState->mediationCentreProposed()) :

            $mediationCentres = Utils::getOrganisations(array(
                'type'   => 'mediation_centre'
            ));

            $f3->set('mediationCentres', $mediationCentres);
            $f3->set('content', 'mediation_new.html');

        elseif (!$mediationState->mediationCentreDecided()) :

            $f3->set('proposed_mediation_party', $mediationState->getMediationCentre());
            $f3->set('proposed_by',              $mediationState->getMediationCentreProposer());
            $f3->set('content', 'mediation_proposed.html');

        elseif (!$mediationState->mediatorProposed()) :

            errorPage('@TODO - offer form for proposing mediator');

        elseif (!$mediationState->mediatorDecided()) :

            $f3->set('proposed_mediation_party', $mediationState->getMediator());
            $f3->set('proposed_by',              $mediationState->getMediatorProposer());
            $f3->set('content', 'mediation_proposed.html');

        else :

            errorPage('@TODO - Mediation is fully underway!');

        endif;

        echo View::instance()->render('layout.html');
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

}
