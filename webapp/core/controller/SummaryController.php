<?php

class SummaryController {

    private function commonSummaryActions($f3, $params, $callback) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        if (!$dispute->getState($account)->canEditSummary()) {
            errorPage('You cannot edit this summary!');
        }

        $callback($f3, $account, $dispute);

        $f3->set('content', 'summary.html');
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

            if (!$summary) {
                $f3->set('error_message', 'You must fill in a summary.');
                $f3->set('summary', '');
            }
            else {
                if ($dispute->isInPartyA($account->getLoginId())) {
                    $dispute->setSummaryForPartyA($summary);
                }
                elseif($dispute->isInPartyB($account->getLoginId())) {
                    $dispute->setSummaryForPartyB($summary);
                }

                $f3->set('summary', $summary);
                $f3->set('success_message', 'You have updated your summary.');
            }
        });
    }
}
