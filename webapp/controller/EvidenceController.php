<?php

class EvidenceController {

    public function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);
        errorPage('Evidence page coming soon.');
    }

}
