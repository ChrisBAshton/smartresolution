<?php
session_start();

/**
 * Creates/clears sessions and retrieves the account object related to the session.
 */
class Session extends Prefab {

    /**
     * Returns the account object linked to the current session, i.e. the account of the logged in user.
     * @return Account
     */
    public function getAccount() {
        $email = Utils::instance()->getValue($_SESSION, 'ODR_Email', false);
        if (!$email) {
            return false;
        }
        $loginID = DBQuery::instance()->emailToId($email);
        return DBGet::instance()->account($loginID);
    }

    /**
     * Creates a session.
     * @param  string $email    Email address.
     * @param  string $password Raw password.
     */
    public function create($email, $password) {
        $_SESSION['ODR_Email']     = $email;
        $_SESSION['ODR_Password']  = $password;
        $_SESSION['ODR_Logged_In'] = true;
    }

    /**
     * Clears the current session.
     */
    public function clear() {
        $_SESSION['ODR_Password']  = false;
        $_SESSION['ODR_Logged_In'] = false;
    }

    /**
     * Retrieves the last known SmartResolution email address of the user's browser by examining the session cookie.
     * @return string|false
     */
    public function lastKnownEmail() {
        return isset($_SESSION['ODR_Email']) ? $_SESSION['ODR_Email'] : false;
    }

    /**
     * Denotes whether or not the user is logged in.
     * @return boolean True if logged in, false if not.
     */
    public function loggedIn() {
        $loggedIn = false;
        if(isset($_SESSION['ODR_Logged_In']))  {
            if ($_SESSION['ODR_Logged_In']) {
                $loggedIn = $_SESSION['ODR_Logged_In'];
            }
        }
        return $loggedIn;
    }
}