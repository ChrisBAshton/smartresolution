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

class Agent extends Individual {

}

class Mediator extends Individual {

    public function isAvailableForDispute($disputeID) {

        $available = Database::instance()->exec(
            'SELECT * FROM mediators_available WHERE dispute_id = :dispute_id AND mediator_id = :mediator_id LIMIT 1',
            array(
                ':dispute_id'  => $disputeID,
                ':mediator_id' => $this->getLoginId()
            )
        );

        return count($available) === 1;
    }

}
