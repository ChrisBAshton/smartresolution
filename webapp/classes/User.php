<?php

require_once __DIR__ . '/../lib/autoload.php';

abstract class User {

    private $email;

    /**
     * Instantiates User object, populating attributes from database row.
     * Parameters are either passed directly from the POST login screen, or
     * passed via cookies if user is already logged in.
     * 
     * @param [String] $email    User email
     * @param [String] $password User's encrypted password.
     */
    function __construct($email, $password) {
        $db = new \DB\SQL('sqlite:' . __DIR__ . '/../../data/production.db');
        $user = $this->authenticate($db, $email, $password);
        $this->email = $user['email'];
    }

    private function authenticate($db, $email, $password) {
        $crypt = \Bcrypt::instance();

        $users = $db->exec(
            'SELECT * FROM users WHERE email = :email',
            array(
                ':email' => $email
            )
        );

        if (count($users) === 0) {
            throw new Exception("We have no record of that email address.");
        }
        else if (!$crypt->verify($password, $users[0]['password'])) {
            throw new Exception("Incorrect password.");
        }

        return $users[0];
    }

    public function __toString() {

    }

}

class Agent extends User {

}

class Mediator extends User {

}