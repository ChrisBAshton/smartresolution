<?php

class Organisation extends Account implements AccountInterface {

    function __construct($account) {
        $this->loginId     = $account['login_id'];
        $this->email       = $account['email'];
        $this->verified    = $account['verified'];
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

    public function getIndividuals() {
        return DBQuery::instance()->getIndividuals($this->getLoginId());
    }
}

class LawFirm extends Organisation {

    public function getAgents() {
        return parent::getIndividuals();
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
        return parent::getIndividuals();
    }
}