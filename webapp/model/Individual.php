<?php

class Individual extends AccountCommonMethods implements AccountInterface {

    function __construct($account) {
        if (is_int($account)) {
            $account = AccountDetails::getDetailsById($account);
        }

        $this->loginId      = (int) $account['login_id'];
        $this->email        = $account['email'];
        $this->forename     = $account['forename'];
        $this->surname      = $account['surname'];
        $this->organisation = AccountDetails::getAccountById($account['organisation_id']);
    }

    public function getName() {
        return $this->forename . ' ' . $this->surname;
    }

    public function getOrganisation() {
        return $this->organisation;
    }
}
