<?php

class DisputeController {

    function newDisputeGet ($f3) {
        mustBeLoggedInAsAn('Organisation');
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
        mustBeLoggedInAsAn('Organisation');

        $title   = $f3->get('POST.title');
        $agent   = $f3->get('POST.agent');
        $type    = $f3->get('POST.type');
        $summary = $f3->get('POST.summary');

        if (!$title || !$agent || !$type  || !$summary || $agent === '---' || $type === '---') {
            $f3->set('error_message', 'Please fill in all fields.');
        }
        else {
            try {
                $dispute = DisputeDB::create(array(
                    'title'      => $title,
                    'law_firm_a' => $f3->get('account')->getLoginId(),
                    'agent_a'    => $agent,
                    'summary'    => $summary,
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
        $dispute = setDisputeFromParams($f3, $params);
        $dashboardActions = array();

        if (!$dispute->canBeViewedBy($account->getLoginId())) {
            errorPage('You do not have permission to view this Dispute!');
        }
        else {
            $dashboardActions = DisputeActions::getActions($dispute, $account, $f3);
        }
        $f3->set('dashboardActions', $dashboardActions);
        $f3->set('content', 'dispute_view--single.html');
        echo View::instance()->render('layout.html');
    }

    function assignDisputeGet ($f3, $params) {
        mustBeLoggedInAsAn('Organisation');
        $agents  = $f3->get('account')->getAgents();
        if (count($agents) === 0) {
            errorPage('You must create an Agent account before you can assign a dispute to an Agent!');
        }
        else {
            $f3->set('agents', $agents);
        }
        setDisputeFromParams($f3, $params);
        $f3->set('content', 'dispute_assign.html');
        echo View::instance()->render('layout.html');
    }

    function assignDisputePost ($f3, $params) {
        mustBeLoggedInAsAn('Organisation');
        $dispute = setDisputeFromParams($f3, $params);

        $agent = $f3->get('POST.agent');
        $summary = $f3->get('POST.summary');

        if (!$summary || !$agent || $agent === '---') {
            $f3->set('error_message', 'You must fill in all fields!');
            $this->assignDisputeGet($f3, $params);
        }
        else {
            $dispute->setAgentB((int) $agent);
            $dispute->setSummaryForPartyB($summary);

            Notification::create(array(
                'recipient_id' => $agent,
                'message'      => 'A new dispute has been assigned to you.',
                'url'          => $dispute->getUrl()
            ));

            Notification::create(array(
                'recipient_id' => $dispute->getOpposingPartyId($agent),
                'message'      => 'The other party has assigned an agent to the case.',
                'url'          => $dispute->getUrl()
            ));

            header('Location: ' . $dispute->getUrl());
        }
    }

    function viewDisputes ($f3) {
        $account  = mustBeLoggedIn();
        $disputes = DisputeDB::getAllDisputesConcerning($account->getLoginId());
        var_dump($disputes);
        //$f3->set('disputes', $disputes);
        $f3->set('content', 'dispute_view--list.html');
        echo View::instance()->render('layout.html');
    }

    function openDisputeGet ($f3, $params) {
        mustBeLoggedInAsAn('Individual');
        $dispute = setDisputeFromParams($f3, $params);

        if ($dispute->hasBeenOpened()) {
            errorPage('You have already opened this dispute against ' . $dispute->getLawFirmB()->getName() . '!');
        }

        $lawFirms = array();
        $lawFirmsDetails = Database::instance()->exec('SELECT * FROM organisations INNER JOIN account_details ON organisations.login_id = account_details.login_id  WHERE type = "law_firm" AND organisations.login_id != :law_firm_a ORDER BY name DESC',
            array(':law_firm_a' => $f3->get('dispute')->getLawFirmA()->getLoginId()));

        foreach($lawFirmsDetails as $details) {
            $lawFirms[] = new LawFirm($details);
        }

        $f3->set('lawFirms', $lawFirms);
        $f3->set('content', 'dispute_open.html');
        echo View::instance()->render('layout.html');
    }

    function openDisputePost ($f3, $params) {
        mustBeLoggedInAsAn('Agent');
        $lawFirmB = $f3->get('POST.law_firm_b');
        
        if (!$lawFirmB || $lawFirmB === '---') {
            $f3->set('error_message', 'You must choose a company from the dropdown list.');
            $this->openDisputeGet($f3, $params);
        }
        else {
            $dispute = setDisputeFromParams($f3, $params);
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
        mustBeLoggedInAsAn('Agent');
        setDisputeFromParams($f3, $params);
        $f3->set('content', 'dispute_close.html');
        echo View::instance()->render('layout.html');
    }

    function closeDisputePost ($f3, $params) {
        $this->closeDisputeGet($f3, $params); // @TODO
    }
}