<?php

class Individual implements AccountInterface {

    function __construct($account) {
        $this->loginId = (int) $account['login_id'];
        $this->email = $account['email'];
        $this->forename = $account['forename'];
        $this->surname = $account['surname'];
    }

    public function getLoginId() {
        return $this->loginId;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->forename . ' ' . $this->surname;
    }
}

class Agent extends Individual {

}

class Mediator extends Individual {

}