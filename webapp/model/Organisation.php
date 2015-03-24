<?php

class Organisation extends AccountCommonMethods implements AccountInterface {

    function __construct($account) {
        $this->setVariables($account);
    }

    public function setVariables($account) {
        if (is_int($account)) {
            $account = AccountDetails::getDetailsById($account);
        }
        $this->loginId     = (int) $account['login_id'];
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
        $this->setProperty('description', $description);
    }

    private function setProperty($key, $value) {
        Database::instance()->exec(
            'UPDATE organisations SET ' . $key . ' = :value WHERE login_id = :uid',
            array(
                ':value' => $value,
                ':uid'   => $this->getLoginId()
            )
        );
        $this->setVariables($this->getLoginId());
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

    public function getAllDisputes() {
        $disputes = array();
        $disputesDetails = Database::instance()->exec(
            'SELECT  dispute_id  FROM mediation_offers
            WHERE    status      = "accepted"
            AND      proposed_id = :login_id
            ORDER BY mediation_offer_id DESC',
            array(
                ':login_id' => $this->getLoginId()
            )
        );
        foreach($disputesDetails as $dispute) {
            $disputes[] = new Dispute($dispute['dispute_id']);
        }
        return $disputes;
    }

}
