<?php

class Admin extends Account implements AccountInterface {

    function __construct($account) {
        $this->setVariables($account);
    }

    public function setVariables($account) {
        if (is_int($account)) {
            $account = DBAccount::instance()->getDetailsById($account);
        }

        $this->loginId = (int) $account['login_id'];
        $this->email   = $account['email'];
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
