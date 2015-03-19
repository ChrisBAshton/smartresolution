<?php

class MediationController {

    public function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        $mediationCentres = array();

        // @TODO - this is duplicating line 118 of DisputeController.php
        $mediationCentreDetails = Database::instance()->exec(
            'SELECT * FROM organisations INNER JOIN account_details ON organisations.login_id = account_details.login_id  WHERE type = "mediation_centre" ORDER BY name DESC'
        );

        foreach($mediationCentreDetails as $details) {
            $mediationCentres[] = new MediationCentre($details);
        }

        $f3->set('mediationCentres', $mediationCentres);
        $f3->set('content', 'mediation.html');
        echo View::instance()->render('layout.html');
    }

}
