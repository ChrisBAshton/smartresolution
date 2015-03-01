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

function mustBeLoggedInAsAnOrganisation() {
    $account = mustBeLoggedIn();
    if ( ! (Session::getAccount() instanceof Organisation) ) {
        errorPage('You do not have permission to see this page. You must be logged into an Organisation account.');
    }
    return $account;
}

function mustBeLoggedInAsAnIndividual() {
    $account = mustBeLoggedIn();
    if ( ! (Session::getAccount() instanceof Individual) ) {
        errorPage('You do not have permission to see this page. You must be logged into an Individual account.');
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