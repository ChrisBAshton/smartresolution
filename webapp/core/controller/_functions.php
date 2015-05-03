<?php

/**
 * Are there any success or error messages to display? Returns a boolean.
 * @return boolean True if there is a message to display, otherwise false.
 */
function noSystemMessages() {
    return (
        !Base::instance()->get('success_message') &&
        !Base::instance()->get('error_message')
    );
}

/**
 * Requires that the user accessing this page is logged into an account. If not, raises an error page. If logged in, the account representing the logged in user is returned.
 * @return Account The logged in user.
 */
function mustBeLoggedIn() {
    $session = Session::instance();
    if ($session->loggedIn()) {
        global $f3;
        $account = $session->getAccount();
        $f3->set('account', $account);
    }
    else {
        errorPage('You do not have permission to see this page. Please log in first.');
    }
    return $account;
}

/**
 * Requires that the logged in user is a specific account type, such as an Agent. Raises an error if the logged in user is NOT that account type.
 * Sometimes, you may want the logged in user to be an Agent or a Mediator (i.e. an Individual). In this case, 'Individual' can be passed to the function, it will still work. Go polymorphism!
 * @param  string $accountType Account type required, e.g. 'Agent', 'Individual', 'MediationCentre' etc
 * @return Account             Returns the account representing the logged in user.
 */
function mustBeLoggedInAsAn($accountType) {
    $account = mustBeLoggedIn();
    if ( !is_a($account, $accountType) ) {
        errorPage('You do not have permission to see this page. You must be logged into an ' . $accountType . ' account.');
    }
    return $account;
}

/**
 * Displays the given error message within the standard SmartResolution layout and ceases all further PHP processing.
 * Conceptually, this function is the 'catch' part of a try-catch block: it should only be displayed when the user has done something unexpected or something has otherwise gone wrong.
 * @param  string $errorMessage Error message to display.
 */
function errorPage($errorMessage) {
    global $f3;
    $f3->set('error_message', $errorMessage);
    $f3->set('content','error.html');
    echo View::instance()->render('layout.html');
    die();
}

/**
 * Returns the dispute object corresponding to the dispute ID of the URL, e.g.
 * /disputes/@disputeID => $params['disputeID'] => 1337
 * => returns the Dispute object corresponding to ID 1337.
 *
 * Triggers error page if dispute cannot be found.
 *
 * @param  F3 $f3         The base F3 object.
 * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
 */
function setDisputeFromParams($f3, $params) {
    try {
        $disputeID = (int)$params['disputeID'];
        $dispute = DBGet::instance()->dispute($disputeID); // if dispute does not exist, throws exception
        $f3->set('dispute', $dispute);
        return $dispute;
    }
    catch(Exception $e) {
        errorPage($e->getMessage());
    }
}

/**
 * Returns a human-readable date and time from a UNIX timestamp.
 * @param  int $unixTimestamp UNIX timestamp to convert.
 * @return string             Human-readable date and time.
 * @todo  move to Utils?
 */
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
 * @todo  move to Utils?
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