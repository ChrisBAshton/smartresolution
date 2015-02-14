<?php
require_once __DIR__ . '/autoload.php';

abstract class User {

    public $email;

    /**
     * Instantiates User object, populating attributes from database row.
     * Parameters are either passed directly from the POST login screen, or
     * passed via cookies if user is already logged in.
     * 
     * @param [String]  $email    User email
     * @param [String]  $password User's encrypted password.
     * @param [boolean] $newUser  If true, a new User record is created.
     */
    function __construct($email, $password) {

        $user = $this->getUserFromDatabase($email);
        if (!$user) {
            throw new Exception("We have no record of that email address.");
        }

        if(!$this->correctPassword($password, $user['password'])) {
            throw new Exception("Incorrect password.");
        }
        
        $this->setAttributes($user);
    }

    private function setAttributes($user) {
        $this->email = $user['email'];
        // etc
    }

    private function getUserFromDatabase($email) {
        $db = Database::instance();

        $users = $db->exec(
            'SELECT * FROM account_details WHERE email = :email',
            array(
                ':email' => $email
            )
        );

        if (count($users) === 0) {
            return false;
        }
        else {
            return $users[0];
        }
    }

    public function correctPassword($inputtedPassword, $encryptedPassword) {
        $crypt = \Bcrypt::instance();
        return $crypt->verify($inputtedPassword, $encryptedPassword);
    }

    public function __toString() {
    }
}

class Agent extends User {

}

class Mediator extends User {

}