<?php

class Individual extends AccountCommonMethods implements AccountInterface {

    function __construct($account) {
        if (is_int($account)) {
            $account = AccountDetails::getDetailsById($account);
        }

        $this->loginId = (int) $account['login_id'];
        $this->email = $account['email'];
        $this->forename = $account['forename'];
        $this->surname = $account['surname'];
    }

    public function getName() {
        return $this->forename . ' ' . $this->surname;
    }
}

class Agent extends Individual {

}

class Mediator extends Individual {

}