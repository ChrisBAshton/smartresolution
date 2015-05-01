<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

// global variables for this class
$disputeID  = 2; // made up, doesn't matter
$agentAId   = DBQuery::instance()->emailToId('agent_a@t.co');
$agentBId   = DBQuery::instance()->emailToId('agent_b@t.co');
$mediatorId = DBQuery::instance()->emailToId('john.smith@we-mediate.co.uk');

class DBQueryTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        global $disputeID, $agentAId, $agentBId, $mediatorId;

        $create = DBCreate::instance();

        $create->message(array(
            'dispute_id' => $disputeID,
            'author_id'  => $agentAId,
            'message'    => 'Open message to Agent B'
        ));

        $create->message(array(
            'dispute_id' => $disputeID,
            'author_id'  => $agentBId,
            'message'    => 'Open message to Agent A'
        ));

        $create->message(array(
            'dispute_id'   => $disputeID,
            'author_id'    => $mediatorId,
            'recipient_id' => $agentAId,
            'message'      => 'Direct message from mediator to agent A'
        ));

        $create->message(array(
            'dispute_id'   => $disputeID,
            'author_id'    => $mediatorId,
            'recipient_id' => $agentBId,
            'message'      => 'Direct message from mediator to agent B'
        ));

        $create->message(array(
            'dispute_id'   => $disputeID,
            'author_id'    => $agentAId,
            'recipient_id' => $mediatorId,
            'message'      => 'Direct message from agent A to mediator'
        ));
    }

    public function setUp()
    {
        global $disputeID, $agentAId, $agentBId, $mediatorId;
        $this->disputeID  = $disputeID; // made up, doesn't matter
        $this->agentAId   = $agentAId;
        $this->agentBId   = $agentBId;
        $this->mediatorId = $mediatorId;
    }

    public function testGetIdFromEmail()
    {
        $testUser = DBQuery::instance()->emailToId('law_firm_a@t.co');
        $this->assertEquals(2, $testUser);
    }

    public function testGetDisputeMessages()
    {
        $messages = DBQuery::instance()->retrieveDisputeMessages($this->disputeID);

        foreach($messages as $message) {
            $this->assertTrue(
                strpos($message->contents(), 'Open message to Agent') !== false
            );
        }
    }

    public function testGetDirectMessagesBetweenMediatorAndAgent() {
        $messages = DBQuery::instance()->retrieveMediationMessages($this->disputeID, $this->mediatorId, $this->agentBId);
        $this->assertEquals(1, count($messages));
        $this->assertEquals('Direct message from mediator to agent B', $messages[0]->contents());

        // same as above, parameters reversed = should give same results
        $messages = DBQuery::instance()->retrieveMediationMessages($this->disputeID, $this->agentBId, $this->mediatorId);
        $this->assertEquals(1, count($messages));
        $this->assertEquals('Direct message from mediator to agent B', $messages[0]->contents());
    }

    public function testMoreMediatorAgentMessages() {
        $messages = DBQuery::instance()->retrieveMediationMessages($this->disputeID, $this->mediatorId, $this->agentAId);
        $this->assertEquals(2, count($messages));
        // messages are in chronological order
        $this->assertEquals('Direct message from agent A to mediator', $messages[0]->contents());
        $this->assertEquals('Direct message from mediator to agent A', $messages[1]->contents());
    }

    public function testNoMessages() {
        $messages = DBQuery::instance()->retrieveMediationMessages($this->disputeID, $this->mediatorId, 1337);
        $this->assertEquals(array(), $messages);
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
        $newLawFirmId    = DBQuery::instance()->emailToId('law_firm_with_only_one_dispute@company.com');

        $party = $create->disputeParty(array(
            'organisation_id' => $newLawFirmId
        ));

        DBQuery::instance()->updateDisputePartyB($party->getPartyId(), $dispute->getDisputeId());

        // as we've called the static method directly, rather than through the Dispute class,
        // the dispute's Party will have been cached. We need to break that cache by re-grabbing the
        // details from the database.
        $dispute = TestHelper::getDisputeByTitle('Smith versus Jones');
        $this->assertNotEquals($originalPartyID, $dispute->getPartyB()->getPartyId());
    }

}