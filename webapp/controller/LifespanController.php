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

    function newLifespan ($f3, $params) {
        $account    = mustBeLoggedInAsAn('Agent');
        $dispute    = setDisputeFromParams($f3, $params);

        if (!$dispute->getState($account)->canNegotiateLifespan()) {
            errorPage('You cannot negotiate a lifespan.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $validUntil = strtotime($f3->get('POST.valid_until'));
            $startTime  = strtotime($f3->get('POST.start_time'));
            $endTime    = strtotime($f3->get('POST.end_time'));

            if (!$validUntil || !$startTime || !$endTime) {
                $f3->set('error_message', 'Please fill in all fields!');
            }
            else {
                try {
                    $lifespan = DBL::createLifespan(array(
                        'dispute_id'  => $dispute->getDisputeId(),
                        'proposer'    => $account->getLoginId(),
                        'valid_until' => $validUntil,
                        'start_time'  => $startTime,
                        'end_time'    => $endTime
                    ));

                    DBL::createNotification(array(
                        'recipient_id' => $dispute->getOpposingPartyId($account->getLoginId()),
                        'message'      => 'A lifespan offer has been made. You have until ' . prettyTime($validUntil) . ' to accept or deny the offer.',
                        'url'          => $dispute->getUrl() . '/lifespan'
                    ));

                    header('Location: ' . $dispute->getUrl() . '/lifespan');
                } catch(Exception $e) {
                    $f3->set('error_message', $e->getMessage());
                }
            }
        }

        $f3->set('content', 'lifespan_new.html');
        echo View::instance()->render('layout.html');
    }

    function acceptOrDecline ($f3, $params) {
        $account = mustBeLoggedInAsAn('Agent');
        $dispute = setDisputeFromParams($f3, $params);
        $resolution = $f3->get('POST.resolution');

        if ($resolution === 'accept') {
            $dispute->getLatestLifespan()->accept();
            $notification = 'The other party has agreed your lifespan offer.';
        }
        else if ($resolution === 'decline') {
            $dispute->getLatestLifespan()->decline();
            $notification = 'The other party has declined your lifespan offer.';
        }

        DBL::createNotification(array(
            'recipient_id' => $dispute->getOpposingPartyId($account->getLoginId()),
            'message'      => $notification,
            'url'          => $dispute->getUrl() . '/lifespan'
        ));

        header('Location: ' . $dispute->getUrl() . '/lifespan');
    }
}
