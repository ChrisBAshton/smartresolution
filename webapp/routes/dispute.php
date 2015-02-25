<?php

class RouteDispute {

    function newDisputeGet ($f3) {
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

        $this->newDisputeGet($f3);
    }

    function viewDispute ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = $this->setDisputeFromParams($f3, $params);
        $dashboardActions = array();

        if (!$dispute->canBeViewedBy($account->getLoginId())) {
            errorPage('You do not have permission to view this Dispute!');
        }
        elseif ($dispute->hasNotBeenOpened()) {
            $dashboardActions[] = array(
                'title' => 'Open dispute',
                'href'  => $dispute->getUrl() . '/open'
            );
        }
        else if ($dispute->waitingForLawFirmB()) {
            // if we are law firm b
            if ($dispute->getLawFirmBId() === $account->getLoginId()) {
                $dashboardActions[] = array(
                    'title' => 'Assign dispute to an agent',
                    'href'  => $dispute->getUrl() . '/assign'
                );
            }
            else {
                $f3->set('status', array(
                    'message' => 'You are waiting for ' . $dispute->getLawFirmB()->getName() . ' to assign an agent to the dispute.',
                    'class'   => 'bg-padded bg-info'
                ));
            }
        }
        else {
            $dashboardActions[] = array(
                'title' => 'Take the case to court',
                'href'  => $dispute->getUrl() .'/close',
            );
        }
        $f3->set('dashboardActions', $dashboardActions);
        $f3->set('content', 'dispute_view--single.html');
        echo View::instance()->render('layout.html');
    }

    function assignDisputeGet ($f3, $params) {
        mustBeLoggedInAsAnOrganisation();
        $agents  = $f3->get('account')->getAgents();
        if (count($agents) === 0) {
            errorPage('You must create an Agent account before you can assign a dispute to an Agent!');
        }
        else {
            $f3->set('agents', $agents);
        }
        $this->setDisputeFromParams($f3, $params);
        $f3->set('content', 'dispute_assign.html');
        echo View::instance()->render('layout.html');
    }

    function assignDisputePost ($f3, $params) {
        mustBeLoggedInAsAnOrganisation();
        $dispute = $this->setDisputeFromParams($f3, $params);

        $agent = $f3->get('POST.agent');

        if (!$agent || $agent === '---') {
            $f3->set('error_message', 'You must choose an Agent to assign!');
            $f3->set('content', 'dispute_assign.html');
            echo View::instance()->render('layout.html');
        }
        else {
            $dispute->setAgentB((int) $agent);
            header('Location: ' . $dispute->getUrl());
        }
    }

    function viewDisputes ($f3) {
        mustBeLoggedIn();
        $disputes = Dispute::getAllDisputesConcerning($f3->get('account')->getLoginId());
        $f3->set('disputes', $disputes);
        $f3->set('content', 'dispute_view--list.html');
        echo View::instance()->render('layout.html');
    }

    function setDisputeFromParams($f3, $params) {
        try {
            $disputeID = (int)$params['disputeID'];
            $dispute = new Dispute($disputeID); // if dispute does not exist, throws exception
            $f3->set('dispute', $dispute);
            return $dispute;
        }
        catch(Exception $e) {
            errorPage($e->getMessage());
        }
    }

    function openDisputeGet ($f3, $params) {
        mustBeLoggedInAsAnIndividual();
        $this->setDisputeFromParams($f3, $params);

        $lawFirms = array();
        $lawFirmsDetails = Database::instance()->exec('SELECT * FROM organisations INNER JOIN account_details ON organisations.login_id = account_details.login_id  WHERE type = "law_firm" AND organisations.login_id != :law_firm_a ORDER BY name DESC',
            array(':law_firm_a' => $f3->get('dispute')->getLawFirmAId()));

        foreach($lawFirmsDetails as $details) {
            $lawFirms[] = new LawFirm($details);
        }

        $f3->set('lawFirms', $lawFirms);
        $f3->set('content', 'dispute_open.html');
        echo View::instance()->render('layout.html');
    }

    function openDisputePost ($f3, $params) {
        mustBeLoggedInAsAnIndividual();
        $lawFirmB = $f3->get('POST.law_firm_b');
        
        if (!$lawFirmB || $lawFirmB === '---') {
            $f3->set('error_message', 'You must choose a company from the dropdown list.');
            $this->openDisputeGet($f3, $params);
        }
        else {
            $dispute = $this->setDisputeFromParams($f3, $params);
            $dispute->setLawFirmB($lawFirmB);

            Notification::create(array(
                'recipient_id' => $lawFirmB,
                'message'      => 'A dispute has been opened against your company.',
                'url'          => $dispute->getUrl()
            ));

            header('Location: ' . $dispute->getUrl());
        }
    }

    function closeDisputeGet ($f3, $params) {
        mustBeLoggedInAsAnIndividual();
        $this->setDisputeFromParams($f3, $params);
        $f3->set('content', 'dispute_close.html');
        echo View::instance()->render('layout.html');
    }

    function closeDisputePost ($f3) {
    }
}