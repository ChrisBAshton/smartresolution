<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DBDisputeTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testEnablingAndDisablingRoundTableCommunication()
    {
        $dispute = TestHelper::getDisputeByTitle('Dispute that is in mediation');
        $this->assertFalse($dispute->inRoundTableCommunication());

        $dispute->enableRoundTableCommunication();
        $this->assertTrue($dispute->inRoundTableCommunication());

        $dispute->disableRoundTableCommunication();
        $this->assertFalse($dispute->inRoundTableCommunication());
    }

    public function testSetDisputeParty()
    {
        $create          = DBCreate::instance();
        $dispute         = TestHelper::getDisputeByTitle('Smith versus Jones');
        $originalPartyID = $dispute->getPartyB()->getPartyId();
        $newLawFirmId    = DBAccount::instance()->emailToId('law_firm_with_only_one_dispute@company.com');

        $party = $create->disputeParty(array(
            'organisation_id' => $newLawFirmId
        ));

        DBDispute::instance()->updateDisputePartyB($party->getPartyId(), $dispute->getDisputeId());

        // as we've called the static method directly, rather than through the Dispute class,
        // the dispute's Party will have been cached. We need to break that cache by re-grabbing the
        // details from the database.
        $dispute = TestHelper::getDisputeByTitle('Smith versus Jones');
        $this->assertNotEquals($originalPartyID, $dispute->getPartyB()->getPartyId());
    }
}
