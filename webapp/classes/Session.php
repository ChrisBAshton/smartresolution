<?php
require_once __DIR__ . '/autoload.php';

session_start();

class Session {

    private $loggedIn;
    private $user;

    function __construct() {
        $this->loggedIn = false;

        if($_SESSION['ODR_Logged_In'])  {
            try {
                $username = $_SESSION['ODR_Email']; 
                $password = $_SESSION['ODR_Password'];
                $user = new Agent($username, $password);
                $this->loggedIn = $_SESSION['ODR_Logged_In'];
                $this->user = $user;
            } catch (Exception $e) {
            }
        }
    }

    public function getUser() {
        return $this->user;
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
        return $this->loggedIn;
    }
}