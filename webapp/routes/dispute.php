<?php

class RouteDispute {

    function newDisputeForm ($f3) {
        mustBeLoggedInAsAnOrganisation();
        $f3->set('content','dispute_new.html');
        echo View::instance()->render('layout.html');
    }

    function newDisputePost ($f3) {
        mustBeLoggedInAsAnOrganisation();
        $f3->set('content','dispute_new.html');
        echo View::instance()->render('layout.html');
    }

}