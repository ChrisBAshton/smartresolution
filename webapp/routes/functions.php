<?php

function mustBeLoggedIn() {
    if (Session::loggedIn()) {
        global $f3;
        $f3->set('account', Session::getAccount());
    }
    else {
        errorPage('You do not have permission to see this page. Please log in first.');
    }
}

function mustBeLoggedInAsAnOrganisation() {
    mustBeLoggedIn();
    if ( ! (Session::getAccount() instanceof Organisation) ) {
        errorPage('You do not have permission to see this page. You must be logged into an Organisation account.');
    }
}

function errorPage($errorMessage) {
    global $f3;
    $f3->set('error_message', $errorMessage);
    $f3->set('content','error.html');
    echo View::instance()->render('layout.html');
    die();
}
