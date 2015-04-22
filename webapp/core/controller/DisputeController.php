<?php

class DisputeController {

    function viewDispute ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        if (!$dispute->canBeViewedBy($account->getLoginId())) {
            errorPage('You do not have permission to view this Dispute!');
        }
        else {
            $dashboardActions = DisputeStateCalculator::instance()->getActions($dispute, $account);
            $f3->set('dashboardActions', $dashboardActions);
            $f3->set('disputeDashboard', true);
            $f3->set('content', 'dispute_view--single.html');
            echo View::instance()->render('layout.html');
        }
    }

    function viewDisputes ($f3) {
        $account  = mustBeLoggedIn();
        $f3->set('disputes', $account->getAllDisputes());
        $f3->set('content', 'dispute_view--list.html');
        echo View::instance()->render('layout.html');
    }

    function newDisputeGet ($f3) {
        mustBeLoggedInAsAn('Organisation');
        $agents  = $f3->get('account')->getAgents();
        $modules = ModuleController::instance()->getActiveModules();
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
                $dispute = DBCreate::instance()->dispute(array(
                    'title'      => $title,
                    'law_firm_a' => $f3->get('account')->getLoginId(),
                    'agent_a'    => $agent,
                    'summary'    => $summary,
                    'type'       => $type
                ));

                header('Location: ' . $dispute->getUrl());
            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }

        $this->newDisputeGet($f3);
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
            $disputeParty = $dispute->getPartyB();
            $disputeParty->setAgent((int) $agent);
            $disputeParty->setSummary($summary);
            DBUpdate::instance()->disputeParty($disputeParty);

            header('Location: ' . $dispute->getUrl());
        }
    }

    function openDisputeGet ($f3, $params) {
        $account = mustBeLoggedInAsAn('Agent');
        $dispute = setDisputeFromParams($f3, $params);

        if (!$dispute->getState($account)->canOpenDispute()) {
            errorPage('You have already opened this dispute against ' . $dispute->getPartyB()->getLawFirm()->getName() . '!');
        }

        $lawFirms = DBQuery::instance()->getOrganisations(array(
            'type'   => 'law_firm',
            'except' => $f3->get('dispute')->getPartyA()->getLawFirm()->getLoginId()
        ));

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
            $party   = $dispute->getPartyB();
            $party->setLawFirm($lawFirmB);
            DBUpdate::instance()->disputeParty($party);

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
        $account = mustBeLoggedInAsAn('Agent');
        $dispute = setDisputeFromParams($f3, $params);

        $verdict = $f3->get('POST.verdict');
        if (!$verdict || ($verdict !== 'resolved' && $verdict !== 'failed')) {
            $f3->set('error_message', 'You need to select a valid option.');
        }
        else {
            if ($verdict === 'failed') {
                $dispute->closeUnsuccessfully();
            }
            else {
                $dispute->closeSuccessfully();
            }

            $lifespan = $dispute->getCurrentLifespan();
            $lifespan->endLifespan();

            // make changes persistent
            DBUpdate::instance()->lifespan($lifespan);
            DBUpdate::instance()->dispute($dispute);

            $f3->set('success_message', 'You have successfully closed the dispute.');
        }

        $f3->set('content', 'dispute_close.html');
        echo View::instance()->render('layout.html');
    }

    private function commonSummaryActions($f3, $params, $callback) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        $modules = ModuleController::instance()->getActiveModules();
        if (count($modules) === 0) {
            errorPage('The system administrator must install at least one dispute module before you can create a Dispute. Please contact the admin.');
        }

        if (!$dispute->getState($account)->canEditSummary()) {
            errorPage('You cannot edit this dispute!');
        }

        $callback($f3, $account, $dispute);

        $f3->set('modules', $modules);
        $f3->set('content', 'dispute_edit.html');
        echo View::instance()->render('layout.html');
    }

    function editGet ($f3, $params) {
        $this->commonSummaryActions($f3, $params, function ($f3, $account, $dispute) {
            if ($dispute->getPartyA()->contains($account->getLoginId())) {
                $party = $dispute->getPartyA();
            }
            else {
                $party = $dispute->getPartyB();
            }
            $f3->set('summary', $party->getSummary());
        });
    }

    function editPost($f3, $params) {
        $this->commonSummaryActions($f3, $params, function ($f3, $account, $dispute) {

            $summary = $f3->get('POST.dispute_summary');
            $type    = $f3->get('POST.type');

            if (!$summary) {
                $f3->set('error_message', 'You must fill in a summary.');
                $f3->set('summary', '');
            }
            elseif (!$type) {
                $f3->set('error_message', 'You must select a dispute type.');
            }
            else {
                if ($dispute->getPartyA()->contains($account->getLoginId())) {
                    $dispute->getPartyA()->setSummary($summary);
                    DBUpdate::instance()->disputeParty($dispute->getPartyA());
                }
                elseif($dispute->getPartyB()->contains($account->getLoginId())) {
                    $dispute->getPartyB()->setSummary($summary);
                    DBUpdate::instance()->disputeParty($dispute->getPartyB());
                }

                $dispute->setType($type);

                DBUpdate::instance()->dispute($dispute);

                $f3->set('summary', $summary);
                $f3->set('success_message', 'You have updated the dispute details.');
            }
        });
    }
}