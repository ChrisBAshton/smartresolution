<?php
require_once __DIR__ . '/autoload.php';

class Session {

    private $loggedIn;

    function __construct() {
        $this->loggedIn = false;

        if(isset($_COOKIE['ODR_Email']))  {
            try {
                $username = $_COOKIE['ODR_Email']; 
                $password = $_COOKIE['ODR_Password'];
                $user = new Agent($username, $password);
                $this->loggedIn = true;
            } catch (Exception $e) {
            }
        }
    }

    public function loggedIn() {
        return $this->loggedIn;
    }

    public function initSession($email, $password) {
        // now set a cookie to log the user in
        $cookieLength = 0; // unlimited login
        setcookie('ODR_Email',     $email,    $cookieLength, '/');
        setcookie('ODR_Password',  $password, $cookieLength, '/');
    }

    public function clearSession() {
        // make the time in the past to destroy the cookie
        $past = time() - 100000;
        setcookie('ODR_Email',    'delete', $past, '/');
        setcookie('ODR_Password', 'delete', $past, '/');
    }

}