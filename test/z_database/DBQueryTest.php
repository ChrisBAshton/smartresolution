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

    public function testValidCredentials()
    {
        $validCredentials = DBQuery::instance()->validCredentials('law_firm_a@t.co', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = DBQuery::instance()->validCredentials('wrong email', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = DBQuery::instance()->validCredentials('law_firm_a@t.co', 'test');
        $this->assertTrue($validCredentials);
    }

    public function testUserPasswordCheck()
    {
        $this->assertFalse(DBQuery::instance()->correctPassword('test', 'test'));
        $this->assertFalse(DBQuery::instance()->correctPassword('test', 'random string'));
        $this->assertTrue(DBQuery::instance()->correctPassword('test', '$2y$10$md2.JKnCBFH5IGU9MeV50OUtx35VdVcThXeeQG9QUbpm9DwYmBlq.'));
    }

}