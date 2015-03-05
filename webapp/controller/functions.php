<?php

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