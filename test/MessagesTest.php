<?php
require_once __DIR__ . '/../webapp/autoload.php';

class MessagesTest extends PHPUnit_Framework_TestCase
{

    public function setUp() {
        Database::setEnvironment('test');
        Database::clear();

        $this->disputeID  = 2; // made up, doesn't matter
        $this->agentAId   = AccountDetails::emailToId('agent_a@t.co');
        $this->agentBId   = AccountDetails::emailToId('agent_b@t.co');
        $this->mediatorId = AccountDetails::emailToId('john.smith@we-mediate.co.uk');

        DBL::createMessage(array(
            'dispute_id' => $this->disputeID,
            'author_id'  => $this->agentAId,
            'message'    => 'Open message to Agent B'
        ));

        DBL::createMessage(array(
            'dispute_id' => $this->disputeID,
            'author_id'  => $this->agentBId,
            'message'    => 'Open message to Agent A'
        ));

        DBL::createMessage(array(
            'dispute_id'   => $this->disputeID,
            'author_id'    => $this->mediatorId,
            'recipient_id' => $this->agentAId,
            'message'      => 'Direct message from mediator to agent A'
        ));

        DBL::createMessage(array(
            'dispute_id'   => $this->disputeID,
            'author_id'    => $this->mediatorId,
            'recipient_id' => $this->agentBId,
            'message'      => 'Direct message from mediator to agent B'
        ));

        DBL::createMessage(array(
            'dispute_id'   => $this->disputeID,
            'author_id'    => $this->agentAId,
            'recipient_id' => $this->mediatorId,
            'message'      => 'Direct message from agent A to mediator'
        ));
    }

    public function testGetDisputeMessages()
    {
        $messages = new Messages($this->disputeID);
        $messages = $messages->getMessages();

        foreach($messages as $message) {
            $this->assertTrue(
                strpos($message->contents(), 'Open message to Agent') !== false
            );
        }
    }

    public function testGetDirectMessagesBetweenMediatorAndAgent() {
        $messages = new Messages($this->disputeID, $this->mediatorId, $this->agentBId);
        $messages = $messages->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertEquals('Direct message from mediator to agent B', $messages[0]->contents());

        // same as above, parameters reversed = should give same results
        $messages = new Messages($this->disputeID, $this->agentBId, $this->mediatorId);
        $messages = $messages->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertEquals('Direct message from mediator to agent B', $messages[0]->contents());
    }

    public function testMoreMediatorAgentMessages() {
        $messages = new Messages($this->disputeID, $this->mediatorId, $this->agentAId);
        $messages = $messages->getMessages();
        $this->assertEquals(2, count($messages));
        // messages are in chronological order
        $this->assertEquals('Direct message from agent A to mediator', $messages[0]->contents());
        $this->assertEquals('Direct message from mediator to agent A', $messages[1]->contents());
    }

    public function testNoMessages() {
        $messages = new Messages($this->disputeID, $this->mediatorId, 1337);
        $this->assertEquals(array(), $messages->getMessages());
    }
}
