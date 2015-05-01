<?php
session_start();

class Session extends Prefab {

    public function getAccount() {
        $email = Utils::instance()->getValue($_SESSION, 'ODR_Email', false);
        if (!$email) {
            return false;
        }
        $loginID = DBQuery::instance()->emailToId($email);
        return DBGet::instance()->account($loginID);
    }

    public function create($email, $password) {
        $_SESSION['ODR_Email']     = $email;
        $_SESSION['ODR_Password']  = $password;
        $_SESSION['ODR_Logged_In'] = true;
    }

    public function clear() {
        $_SESSION['ODR_Password']  = false;
        $_SESSION['ODR_Logged_In'] = false;
    }

    public function lastKnownEmail() {
        return isset($_SESSION['ODR_Email']) ? $_SESSION['ODR_Email'] : false;
    }

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