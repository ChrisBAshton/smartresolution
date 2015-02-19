<?php

// @TODO - make this a base class and make subclasses LawFirm and MediationCentre inherit from it.
class Organisation implements AccountInterface {

    function __construct($account) {
        $this->email = $account['email'];
        $this->name = $account['name'];
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->name;
    }
}