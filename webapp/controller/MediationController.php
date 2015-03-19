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

}
