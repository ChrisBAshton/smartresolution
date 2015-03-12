<?php

class MediationController {

    public function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);
        errorPage('Mediation page coming soon.');
    }

}
