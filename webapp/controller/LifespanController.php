<?php

class LifespanController {

    function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        if ($dispute->getLifespan()->accepted()) {
            $f3->set('content', 'lifespan_agreed.html');
        }
        else if ($dispute->getLifespan()->offered()) {
            if ($dispute->getLifespan()->getProposer() === $account->getLoginId()) {
                $f3->set('content', 'lifespan_offered--sent.html');
            }
            else {
                $f3->set('content', 'lifespan_offered--received.html');
            }
        }
        else { 
            mustBeLoggedInAsAn('Agent');
            $f3->set('content', 'lifespan_new.html');
        }

        echo View::instance()->render('layout.html');
    }

    function newLifespan ($f3, $params) {
        $account    = mustBeLoggedInAsAn('Agent');
        $dispute    = setDisputeFromParams($f3, $params);
        $validUntil = strtotime($f3->get('POST.valid_until'));
        $startTime  = strtotime($f3->get('POST.start_time'));
        $endTime    = strtotime($f3->get('POST.end_time'));

        if (!$validUntil || !$startTime || !$endTime) {
            $f3->set('error_message', 'Please fill in all fields!');
        }
        else {
            try {

                $lifespan = Lifespan::create(array(
                    'dispute_id'  => $dispute->getDisputeId(),
                    'proposer'    => $account->getLoginId(),
                    'valid_until' => $validUntil,
                    'start_time'  => $startTime,
                    'end_time'    => $endTime
                ));

                Notification::create(array(
                    'recipient_id' => $dispute->getOpposingPartyId($account->getLoginId()),
                    'message'      => 'A lifespan offer has been made. You have until ' . prettyTime($validUntil) . ' to accept or deny the offer.',
                    'url'          => $dispute->getUrl() . '/lifespan'
                ));

                header('Location: ' . $dispute->getUrl() . '/lifespan');
            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }

        $this->view($f3, $params);
    }

    function acceptOrDeclime ($f3) {
        $f3->set('status', '@TODO!');
        echo View::instance()->render('layout.html');
    }
}