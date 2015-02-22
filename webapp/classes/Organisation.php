<?php

class Organisation implements AccountInterface {

    function __construct($account) {
        $this->loginId = (int) $account['login_id'];
        $this->email = $account['email'];
        $this->name = $account['name'];
    }

    public function getLoginId() {
        return $this->loginId;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->name;
    }
}

class LawFirm extends Organisation {

}

class MediationCentre extends Organisation {

}