<?php

/**
 * Links lifespan-negotiation-related HTTP requests with the necessary handlers.
 */
class LifespanController {

    /**
     * View the lifespan page. Depending on whether or not a lifespan has been offered, accepted or declined,
     * this page might show the current lifespan status or redirect the logged in account to the 'new lifespan' page.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
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

    /**
     * Loads the 'new lifespan' page, from which the user can propose a new lifespan.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    function newLifespanGet ($f3, $params) {
        $this->newLifespanPrechecks($f3, $params);
        $f3->set('content', 'lifespan_new.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * POST method; creates a new lifespan offer.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
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

    /**
     * Queries the dispute state and triggers an error page if the current user is not allowed to negotiate
     * a new lifespan.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    private function newLifespanPrechecks($f3, $params) {
        $this->account = mustBeLoggedInAsAn('Agent');
        $this->dispute = setDisputeFromParams($f3, $params);

        if (!$this->dispute->getState($this->account)->canNegotiateLifespan()) {
            errorPage('You cannot negotiate a lifespan.');
        }
    }

    /**
     * POST method; accept or decline the other agent's proposed lifespan.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    function acceptOrDecline ($f3, $params) {
        $account    = mustBeLoggedInAsAn('Agent');
        $dispute    = setDisputeFromParams($f3, $params);
        $lifespan   = $dispute->getLatestLifespan();
        $resolution = $f3->get('POST.resolution');

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
