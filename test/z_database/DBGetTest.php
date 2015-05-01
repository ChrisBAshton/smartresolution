<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DBGetTest extends PHPUnit_Framework_TestCase
{

    public function testGetNotification() {
        $get = DBGet::instance();

        $details = $get->notificationDetails(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['notification_id']));
        $this->assertTrue(isset($details['recipient_id']));
        $this->assertTrue(isset($details['message']));
        $this->assertTrue(isset($details['url']));
        $this->assertTrue(isset($details['read']));

        $this->assertTrue(is_int($details['notification_id']));
        $this->assertTrue(is_int($details['recipient_id']));
        $this->assertTrue(is_bool($details['read']));
    }

    public function testGetters()
    {
        $get = DBGet::instance();

        $details = $get->disputeDetails(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['dispute_id']));

        $details = $get->disputePartyDetails(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['party_id']));

        $details = $get->evidenceDetails(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['evidence_id']));

        $details = $get->lifespanDetails(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['lifespan_id']));

        // we have no message fixture data, so need to add one here:
        DBCreate::instance()->message(array(
            'dispute_id' => 1,
            'author_id'  => 1,
            'message'    => 'message'
        ));

        $details = $get->messageDetails(1);
        $this->assertTrue(is_array($details));
        $this->assertTrue(isset($details['message_id']));
    }
}