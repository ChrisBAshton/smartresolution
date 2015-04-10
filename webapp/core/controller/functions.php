<?php

function noSystemMessages() {
    return (
        !Base::instance()->get('success_message') &&
        !Base::instance()->get('error_message')
    );
}

function mustBeLoggedIn() {
    if (Session::loggedIn()) {
        global $f3;
        $account = Session::getAccount();
        $f3->set('account', $account);
    }
    else {
        errorPage('You do not have permission to see this page. Please log in first.');
    }
    return $account;
}

function mustBeLoggedInAsAn($accountType) {
    $account = mustBeLoggedIn();
    if ( !is_a($account, $accountType) ) {
        errorPage('You do not have permission to see this page. You must be logged into an ' . $accountType . ' account.');
    }
    return $account;
}

function errorPage($errorMessage) {
    global $f3;
    $f3->set('error_message', $errorMessage);
    $f3->set('content','error.html');
    echo View::instance()->render('layout.html');
    die();
}

function notificationsList ($f3) {
    mustBeLoggedIn();
    $f3->set('content','notifications.html');
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

function prettyTime($unixTimestamp) {
    return date('d/m/Y H:i:s', $unixTimestamp);
}

/**
 * If we do time() minus a UNIX timestamp on file, we can work out the number of seconds the thing is
 * in the past or in the future. This function converts those seconds into a human-readable time.
 *
 * Based on http://stackoverflow.com/a/19680778
 *
 * @param  int $seconds Number of seconds calculated.
 * @return string       Formatted time.
 */
function secondsToTime($seconds) {
    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");
    $dtF = $dtF->diff($dtT)->format('%a days, %h hours, %i minutes');
    $dtF = str_replace("0 days, ", "", $dtF);
    $dtF = str_replace("0 hours, ", "", $dtF);
    $dtF = str_replace("0 minutes, ", "", $dtF);
    return $dtF;
}
