<?php

class RouteDispute {

    function newDisputeForm ($f3) {
        mustBeLoggedInAsAnOrganisation();
        $agents = $f3->get('account')->getAgents();
        if (count($agents) === 0) {
            errorPage('You must create an Agent account before you can create a Dispute!');
        }
        else {
            $f3->set('agents', $agents);
            $f3->set('content', 'dispute_new.html');
            echo View::instance()->render('layout.html');
        }
    }

    function newDisputePost ($f3) {
        mustBeLoggedInAsAnOrganisation();
        $f3->set('content','dispute_new.html');
        echo View::instance()->render('layout.html');
    }

}