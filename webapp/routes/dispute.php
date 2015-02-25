<?php

class RouteDispute {

    function newDisputeForm ($f3) {
        mustBeLoggedInAsAnOrganisation();
        $agents  = $f3->get('account')->getAgents();
        $modules = ModuleController::getModules();
        if (count($agents) === 0) {
            errorPage('You must create an Agent account before you can create a Dispute!');
        }
        else if (count($modules) === 0) {
            errorPage('The system administrator must install at least one dispute module before you can create a Dispute. Please contact the admin.');
        }
        else {
            $f3->set('agents', $agents);
            $f3->set('modules', $modules);
            $f3->set('content', 'dispute_new.html');
            echo View::instance()->render('layout.html');
        }
    }

    function newDisputePost ($f3) {
        mustBeLoggedInAsAnOrganisation();

        $title = $f3->get('POST.title');
        $agent = $f3->get('POST.agent');
        $type  = $f3->get('POST.type');

        if (!$title || !$agent || !$type || $agent === '---' || $type === '---') {
            $f3->set('error_message', 'Please fill in all fields.');
        }
        else {
            try {
                $dispute = Dispute::create(array(
                    'title'      => $title,
                    'law_firm_a' => $f3->get('account')->getLoginId(),
                    'agent_a'    => $agent,
                    'type'       => $type
                ));

                Notification::create(array(
                    'recipient_id' => $agent,
                    'message'      => 'A new dispute has been assigned to you.',
                    'url'          => $dispute->getUrl()
                ));

                header('Location: ' . $dispute->getUrl());
            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }

        $this->newDisputeForm($f3);
    }

    function viewDispute ($f3, $params) {
        mustBeLoggedIn();
        $disputeID = (int)$params['disputeID'];
        $dispute = new Dispute($disputeID);

        if (!$dispute->canBeViewedBy($f3->get('account')->getLoginId())) {
            errorPage('You do not have permission to view this Dispute!');
        }
        else {
            $f3->set('dispute', $dispute);
            $f3->set('content', 'dispute_view--single.html');
            echo View::instance()->render('layout.html');
        }
    }

    function viewDisputes ($f3) {
        mustBeLoggedIn();
        $disputes = Dispute::getAllDisputesConcerning($f3->get('account')->getLoginId());
        $f3->set('disputes', $disputes);
        $f3->set('content', 'dispute_view--list.html');
        echo View::instance()->render('layout.html');
    }

}