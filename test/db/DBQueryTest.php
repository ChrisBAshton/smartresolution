<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DBQueryTest extends PHPUnit_Framework_TestCase
{

    public function setUp() {
        Database::setEnvironment('test');
        Database::clear();

        $this->disputeID  = 2; // made up, doesn't matter
        $this->agentAId   = DBAccount::instance()->emailToId('agent_a@t.co');
        $this->agentBId   = DBAccount::instance()->emailToId('agent_b@t.co');
        $this->mediatorId = DBAccount::instance()->emailToId('john.smith@we-mediate.co.uk');

        $create = DBCreate::instance();

        $create->message(array(
            'dispute_id' => $this->disputeID,
            'author_id'  => $this->agentAId,
            'message'    => 'Open message to Agent B'
        ));

        $create->message(array(
            'dispute_id' => $this->disputeID,
            'author_id'  => $this->agentBId,
            'message'    => 'Open message to Agent A'
        ));

        $create->message(array(
            'dispute_id'   => $this->disputeID,
            'author_id'    => $this->mediatorId,
            'recipient_id' => $this->agentAId,
            'message'      => 'Direct message from mediator to agent A'
        ));

        $create->message(array(
            'dispute_id'   => $this->disputeID,
            'author_id'    => $this->mediatorId,
            'recipient_id' => $this->agentBId,
            'message'      => 'Direct message from mediator to agent B'
        ));

        $create->message(array(
            'dispute_id'   => $this->disputeID,
            'author_id'    => $this->agentAId,
            'recipient_id' => $this->mediatorId,
            'message'      => 'Direct message from agent A to mediator'
        ));
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
}
