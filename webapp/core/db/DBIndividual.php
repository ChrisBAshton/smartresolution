<?php

class DBIndividual {

    public static function setProperty($loginID, $key, $value) {
        Database::instance()->exec(
            'UPDATE individuals SET ' . $key . ' = :value WHERE login_id = :uid',
            array(
                ':value' => $value,
                ':uid'   => $loginID
            )
        );
    }

}