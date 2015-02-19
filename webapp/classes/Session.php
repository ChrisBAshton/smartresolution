<?php
require_once __DIR__ . '/autoload.php';

session_start();

class Session {

    public static function getAccount() {
        return AccountDetails::getAccountFromDatabase($_SESSION['ODR_Email']);
    }

    public static function create($email, $password) {
        $_SESSION['ODR_Email']     = $email;
        $_SESSION['ODR_Password']  = $password;
        $_SESSION['ODR_Logged_In'] = true;
    }

    public static function clear() {
        $_SESSION['ODR_Password']  = false;
        $_SESSION['ODR_Logged_In'] = false;
    }

    public static function lastKnownEmail() {
        return isset($_SESSION['ODR_Email']) ? $_SESSION['ODR_Email'] : false;
    }

    public static function loggedIn() {
        $loggedIn = false;
        if(isset($_SESSION['ODR_Logged_In']))  {
            if ($_SESSION['ODR_Logged_In']) {
                $loggedIn = $_SESSION['ODR_Logged_In'];
            }
        }
        return $loggedIn;
    }
}