<?php

class DBOrganisation {

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