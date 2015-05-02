<?php

class Admin extends Account implements AccountInterface {

    private $loginId;
    private $email;
    private $verified;

    function __construct($account) {
        $this->loginId  = $account['login_id'];
        $this->email    = $account['email'];
        $this->verified = $account['verified'];
    }

    public function getName() {
        return 'Administrator';
    }

    public function getRole() {
        return 'Administrator';
    }

    public function __toString() {
        return 'Administrator';
    }

    public function getUrl() {
        return '';
    }

    public function getAllDisputes() {
        return array();
    }
}
