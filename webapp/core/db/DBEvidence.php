<?php

class DBEvidence {

    public static function getEvidence ($evidenceID) {
        $evidence = Database::instance()->exec(
            'SELECT * FROM evidence WHERE evidence_id = :evidence_id LIMIT 1',
            array(':evidence_id' => $evidenceID)
        );

        if (count($evidence) !== 1) {
            throw new Exception('Evidence does not exist.');
        }

        return $evidence[0];
    }

}