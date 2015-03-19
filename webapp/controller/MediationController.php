<?php

class MediationController {

    public function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        $mediationCentres = Utils::getOrganisations(array(
            'type'   => 'mediation_centre'
        ));

        $f3->set('mediationCentres', $mediationCentres);
        $f3->set('content', 'mediation.html');
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

}
