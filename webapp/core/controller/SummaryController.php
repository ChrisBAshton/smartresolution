<?php
// @TODO move to DisputeController, now that we're editing the Dispute as a whole rather than just the summary.







class SummaryController {

    private function commonSummaryActions($f3, $params, $callback) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        $modules = ModuleController::getModules();
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
            $summary = $dispute->isInPartyA($account->getLoginId()) ? $dispute->getSummaryFromPartyA() : $dispute->getSummaryFromPartyB();
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
                if ($dispute->isInPartyA($account->getLoginId())) {
                    $dispute->setSummaryForPartyA($summary);
                }
                elseif($dispute->isInPartyB($account->getLoginId())) {
                    $dispute->setSummaryForPartyB($summary);
                }

                $dispute->setType($type);

                $f3->set('summary', $summary);
                $f3->set('success_message', 'You have updated the dispute details.');
            }
        });
    }
}
