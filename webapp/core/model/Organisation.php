<?php

class Organisation extends Account implements AccountInterface {

    function __construct($account) {
        $this->loginId     = $account['login_id'];
        $this->email       = $account['email'];
        $this->name        = $account['name'];
        $this->description = $account['description'];
    }

    public function getName() {
        return $this->name;
    }

    public function getRawDescription() {
        return $this->description;
    }

    public function getDescription() {
        if (strlen($this->description) === 0) {
            $description = '_This organisation has not provided a description._';
        }
        else {
            $description = $this->description;
        }

        return Markdown::instance()->convert($description);
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getIndividuals($type) {
        return DBOrganisation::instance()->getIndividuals($this->getLoginId(), $type);
    }
}

class LawFirm extends Organisation {

    public function getAgents() {
        return parent::getIndividuals('Agent');
    }

    public function getRole() {
        return 'Law Firm';
    }

}

class MediationCentre extends Organisation {

    public function getRole() {
        return 'Mediation Centre';
    }

    public function getMediators() {
        return parent::getIndividuals('Mediator');
    }
}