<?php

class LifespanController {

    function view ($f3, $params) {
        $account = mustBeLoggedInAsAn('Agent');
        $dispute = setDisputeFromParams($f3, $params);

        if ($dispute->getLatestLifespan()->accepted()) {
            $f3->set('content', 'lifespan_agreed.html');
            echo View::instance()->render('layout.html');
        }
        else if ($dispute->getLatestLifespan()->offered()) {
            if ($dispute->getLatestLifespan()->getProposer() === $account->getLoginId()) {
                $f3->set('content', 'lifespan_offered--sent.html');
            }
            else {
                $f3->set('content', 'lifespan_offered--received.html');
            }
            echo View::instance()->render('layout.html');
        }
        else {
            header('Location: ' . $dispute->getUrl() . '/lifespan/new');
        }
    }

    private function newLifespanPrechecks($f3, $params) {
        $this->account = mustBeLoggedInAsAn('Agent');
        $this->dispute = setDisputeFromParams($f3, $params);

        if (!$this->dispute->getState($this->account)->canNegotiateLifespan()) {
            errorPage('You cannot negotiate a lifespan.');
        }
    }

    function newLifespanGet ($f3, $params) {
        $this->newLifespanPrechecks($f3, $params);
        $f3->set('content', 'lifespan_new.html');
        echo View::instance()->render('layout.html');
    }

    function newLifespanPost($f3, $params) {
        $this->newLifespanPrechecks($f3, $params);
        $validUntil = strtotime($f3->get('POST.valid_until'));
        $startTime  = strtotime($f3->get('POST.start_time'));
        $endTime    = strtotime($f3->get('POST.end_time'));

        if (!$validUntil || !$startTime || !$endTime) {
            $f3->set('error_message', 'Please fill in all fields!');
        }
        else {
            try {
                $lifespan = DBCreate::instance()->lifespan(array(
                    'dispute_id'  => $this->dispute->getDisputeId(),
                    'proposer'    => $this->account->getLoginId(),
                    'valid_until' => $validUntil,
                    'start_time'  => $startTime,
                    'end_time'    => $endTime
                ));

                header('Location: ' . $this->dispute->getUrl() . '/lifespan');
            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }
        $f3->set('content', 'lifespan_new.html');
        echo View::instance()->render('layout.html');
    }

    function acceptOrDecline ($f3, $params) {
        $account = mustBeLoggedInAsAn('Agent');
        $dispute = setDisputeFromParams($f3, $params);
        $resolution = $f3->get('POST.resolution');
        $lifespan = $dispute->getLatestLifespan();
        if ($resolution === 'accept') {
            $lifespan->accept();
        }
        else if ($resolution === 'decline') {
            $lifespan->decline();
        }

        DBUpdate::instance()->lifespan($lifespan);

        header('Location: ' . $dispute->getUrl() . '/lifespan');
    }
}
