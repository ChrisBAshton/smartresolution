<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DBGetTest extends PHPUnit_Framework_TestCase
{

    public function testGetters()
    {
        $get = DBGet::instance();

        $details = $get->dispute(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['dispute_id']));

        $details = $get->disputeParty(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['party_id']));

        $details = $get->evidence(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['evidence_id']));

        $details = $get->lifespan(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['lifespan_id']));

        $details = $get->notification(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['notification_id']));

        // we have no message fixture data, so need to add one here:
        DBCreate::instance()->message(array(
            'dispute_id' => 1,
            'author_id'  => 1,
            'message'    => 'message'
        ));

        $details = $get->message(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['message_id']));
    }
}
