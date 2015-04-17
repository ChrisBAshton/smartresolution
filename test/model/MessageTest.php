<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class ChatTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        Database::setEnvironment('test');
        Database::clear();
    }

    protected function setUp() {
        $this->dispute = new Dispute(2);
    }

    public function testSuccessfulMessage()
    {
        $message = DBCreate::instance()->message(array(
            'dispute_id' => $this->dispute->getDisputeId(),
            'author_id'  => $this->dispute->getPartyA()->getAgent()->getLoginId(),
            'message'    => 'This is a test message.'
        ));

        $this->assertEquals(
            'This is a test message.',
            $message->contents()
        );

        $this->assertEquals(
            $this->dispute->getPartyA()->getAgent()->getLoginId(),
            $message->author()->getLoginId()
        );

        $this->assertEquals(
            $this->dispute->getDisputeId(),
            $message->getDisputeId()
        );

        $this->assertTrue(
            (time() - $message->timestamp() < 100) // a few milliseconds margin of error
        );
    }

    public function testCreateMessageWithRecipient() {
        $message = DBCreate::instance()->message(array(
            'dispute_id'   => TestHelper::getDisputeByTitle('Smith versus Jones')->getDisputeId(),
            'author_id'    => DBAccount::instance()->emailToId('agent_a@t.co'),
            'message'      => 'hello, world',
            'recipient_id' => DBAccount::instance()->emailToId('agent_b@t.co')
        ));
        $this->assertTrue($message instanceof Message);
    }

    /**
     * @expectedException Exception
     */
    public function testMessageFailsWithoutDisputeId()
    {
        $message = DBCreate::instance()->message(array(
            'author_id'  => $this->dispute->getPartyA()->getAgent()->getLoginId(),
            'message'    => 'This is a test message.'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testMessageFailsWithoutAuthorId()
    {
        $message = DBCreate::instance()->message(array(
            'dispute_id' => $this->dispute->getDisputeId(),
            'message'    => 'This is a test message.'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testMessageFailsWithoutMessage()
    {
        $message = DBCreate::instance()->message(array(
            'dispute_id' => $this->dispute->getDisputeId(),
            'author_id'  => $this->dispute->getPartyA()->getAgent()->getLoginId()
        ));
    }
}