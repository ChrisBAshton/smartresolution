<?php

class DBOrganisation {

    public static function setProperty($loginID, $key, $value) {
        Database::instance()->exec(
            'UPDATE organisations SET ' . $key . ' = :value WHERE login_id = :uid',
            array(
                ':value' => $value,
                ':uid'   => $loginID
            )
        );
    }

    public static function getIndividuals($organisationID, $type) {
        $individuals = array();

        $individualsDetails = Database::instance()->exec(
            'SELECT * FROM individuals INNER JOIN account_details ON individuals.login_id = account_details.login_id WHERE organisation_id = :organisation_id',
            array(':organisation_id' => $organisationID)
        );

        foreach($individualsDetails as $individual) {
            $individuals[] = new $type($individual);
        }

        return $individuals;
    }

}