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
        $message = DBCreate::message(array(
            'dispute_id' => $this->dispute->getDisputeId(),
            'author_id'  => $this->dispute->getAgentA()->getLoginId(),
            'message'    => 'This is a test message.'
        ));

        $this->assertEquals(
            'This is a test message.',
            $message->contents()
        );

        $this->assertEquals(
            $this->dispute->getAgentA()->getLoginId(),
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

    /**
     * @expectedException Exception
     */
    public function testMessageFailsWithoutDisputeId()
    {
        $message = DBCreate::message(array(
            'author_id'  => $this->dispute->getAgentA()->getLoginId(),
            'message'    => 'This is a test message.'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testMessageFailsWithoutAuthorId()
    {
        $message = DBCreate::message(array(
            'dispute_id' => $this->dispute->getDisputeId(),
            'message'    => 'This is a test message.'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testMessageFailsWithoutMessage()
    {
        $message = DBCreate::message(array(
            'dispute_id' => $this->dispute->getDisputeId(),
            'author_id'  => $this->dispute->getAgentA()->getLoginId()
        ));
    }
}
?>
