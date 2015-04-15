<?php

class DBEvidence {

    public static function getEvidence ($evidenceID) {
        return DBQuery::getRowById('evidence', 'evidence_id', $evidenceID);
    }

}