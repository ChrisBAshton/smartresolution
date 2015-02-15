<?php

// @TODO - make this a base class and make subclasses Mediator and Agent inherit from it.
class Individual {

    function __construct($account) {
        $this->email = $account['email'];
        $this->forename = $account['forename'];
        $this->surname = $account['surname'];
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->forename . ' ' . $this->surname;
    }
}