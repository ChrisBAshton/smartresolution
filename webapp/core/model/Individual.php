<?php

class Individual extends Account implements AccountInterface {

    function __construct($account) {
        $this->setVariables($account);
    }

    public function setVariables($account) {
        if (is_int($account)) {
            $account = DBAccount::instance()->getDetailsById($account);
        }

        $this->loginId      = (int) $account['login_id'];
        $this->email        = $account['email'];
        $this->forename     = $account['forename'];
        $this->surname      = $account['surname'];
        $this->cv           = $account['cv'];
        $this->organisation = DBAccount::instance()->getAccountById($account['organisation_id']);
    }

    public function getRawCV() {
        return $this->cv;
    }

    public function getCV() {
        if (strlen($this->cv) === 0) {
            $cv = '_This individual has not provided a CV._';
        }
        else {
            $cv = $this->cv;
        }

        return Markdown::instance()->convert($cv);
    }

    public function setCV($cv) {
        $this->setProperty('cv', $cv);
    }

    private function setProperty($key, $value) {
        DBAccount::instance()->setAccountProperty($this->getLoginId(), $key, $value);
        $this->setVariables($this->getLoginId());
    }

    public function getName() {
        return $this->forename . ' ' . $this->surname;
    }

    public function getOrganisation() {
        return $this->organisation;
    }
}

class Agent extends Individual {

    public function getRole() {
        return 'Agent';
    }

}

class Mediator extends Individual {

    public function getRole() {
        return 'Mediator';
    }

    public function isAvailableForDispute($disputeID) {
        return DBMediation::instance()->mediatorIsAvailableForDispute($this->getLoginId(), $disputeID);
    }

}
