<?php

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
            return new Dispute((int) $dispute[0]['dispute_id']);
        }
    }

    public static function createNewDispute() {
        $lawFirm = DBAccount::instance()->emailToId('law_firm_a@t.co');
        $agent = DBAccount::instance()->emailToId('agent_a@t.co');

        return DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones',
            'summary'    => 'This is my summary'
        ));
    }
}