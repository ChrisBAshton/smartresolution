<?php

class Organisation extends AccountCommonMethods implements AccountInterface {

    function __construct($account) {
        $this->loginId = (int) $account['login_id'];
        $this->email = $account['email'];
        $this->name = $account['name'];
    }

    public function getName() {
        return $this->name;
    }

    public function getIndividuals($type) {
        $individuals = array();

        $individualsDetails = Database::instance()->exec(
            'SELECT * FROM individuals INNER JOIN account_details ON individuals.login_id = account_details.login_id WHERE organisation_id = :organisation_id',
            array(':organisation_id' => $this->getLoginId())
        );

        foreach($individualsDetails as $individual) {
            $individuals[] = new $type($individual);
        }

        return $individuals;
    }
}

class LawFirm extends Organisation {

    public function getAgents() {
        return parent::getIndividuals('Agent');
    }

}

class MediationCentre extends Organisation {

    public function getMediators() {
        return parent::getIndividuals('Mediator');
    }

}
