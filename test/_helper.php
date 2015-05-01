<?php

// TestHelper is the first file executed by PHPUnit, so we can safely clear the database
// here in order to set up consistent fixture data for use by all of the unit tests.
Database::setEnvironment('test');
Database::clear();

class TestHelper {

    /**
     * Semi-temporary function - used for DisputeStateTest.php. Maybe rethink the use of this function later down the line.
     * This function should NOT be called from within the application itself!
     *
     * @param  string $title The title of the dispute.
     * @return Dispute
     */
    public static function getDisputeByTitle($title) {
        $dispute = Database::instance()->exec(
            'SELECT * FROM disputes WHERE title = :title ORDER BY dispute_id DESC LIMIT 1',
            array('title' => $title)
        );
        if (count($dispute) !== 1) {
            throw new Exception("Dispute not found!!!");
        }
        else {
            return DBGet::instance()->dispute((int) $dispute[0]['dispute_id']);
        }
    }

    public static function createNewDispute() {
        $lawFirm = DBQuery::instance()->emailToId('law_firm_a@t.co');
        $agent = DBQuery::instance()->emailToId('agent_a@t.co');

        return DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones',
            'summary'    => 'This is my summary'
        ));
    }

    public static function getAccountByEmail($email) {
        $loginID = DBQuery::instance()->emailToId($email);
        return DBGet::instance()->account($loginID);
    }
}