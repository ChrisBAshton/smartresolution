<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DBUpdateTest extends PHPUnit_Framework_TestCase
{
    public function testSetDisputeParty()
    {
        $create          = DBCreate::instance();
        $dispute         = TestHelper::getDisputeByTitle('Smith versus Jones');
        $originalPartyID = $dispute->getPartyB()->getPartyId();
        $newLawFirmId    = DBQuery::instance()->emailToId('law_firm_with_only_one_dispute@company.com');

        $party = $create->disputeParty(array(
            'organisation_id' => $newLawFirmId
        ));

        DBUpdate::instance()->updateDisputePartyB($party->getPartyId(), $dispute->getDisputeId());

        // as we've called the static method directly, rather than through the Dispute class,
        // the dispute's Party will have been cached. We need to break that cache by re-grabbing the
        // details from the database.
        $dispute = TestHelper::getDisputeByTitle('Smith versus Jones');
        $this->assertNotEquals($originalPartyID, $dispute->getPartyB()->getPartyId());
    }

    public function testUpdateNotification()
    {
        $get = DBGet::instance();

        // get a notification, make sure it hasn't been read yet
        $notification = $get->notification(1);
        $this->assertFalse($notification->hasBeenRead());

        // mark it as read. Make sure the object has been updated.
        $notification->markAsRead();
        $this->assertTrue($notification->hasBeenRead());

        // make the 'mark as read' change persistent
        DBUpdate::instance()->notification($notification);

        // retrieve the object 'freshly' and make sure the 'mark as read' change has been committed
        $notificationAfterPersistence = $get->notification(1);
        $this->assertTrue($notificationAfterPersistence->hasBeenRead());
    }
}