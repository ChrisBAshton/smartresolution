<?php
// @TODO move to DisputeController, now that we're editing the Dispute as a whole rather than just the summary.







class SummaryController {

    private function commonSummaryActions($f3, $params, $callback) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        $modules = ModuleController::getActiveModules();
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

    function view ($f3, $params) {
        $this->commonSummaryActions($f3, $params, function ($f3, $account, $dispute) {
            if ($dispute->getPartyA()->contains($account->getLoginId())) {
                $summary = $dispute->getPartyA()->getSummary();
            }
            else {
                $summary = $dispute->getPartyB()->getSummary();
            }
            $f3->set('summary', $summary);
        });
    }

    function edit($f3, $params) {
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
                }
                elseif($dispute->getPartyB()->contains($account->getLoginId())) {
                    $dispute->getPartyB()->setSummary($summary);
                }

                $dispute->setType($type);

                $f3->set('summary', $summary);
                $f3->set('success_message', 'You have updated the dispute details.');
            }
        });
    }
}
