<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class MessageTest extends PHPUnit_Framework_TestCase
{

    public function testSuccessfulMessage()
    {
        $message = new Message(array(
            'dispute_id'   => 1,
            'author_id'    => 1,
            'recipient_id' => false,
            'message'      => 'This is a test message.',
            'timestamp'    => time()
        ));

        $this->assertEquals(
            'This is a test message.',
            $message->contents()
        );

        $this->assertEquals(1, $message->getAuthorId());
        $this->assertEquals(false, $message->getRecipientId());
        $this->assertEquals(1, $message->getDisputeId());
        $this->assertTrue((time() - $message->timestamp() < 100)); // a few milliseconds margin of error
    }

    public function testCreateMessageWithRecipient() {
        $message = new Message(array(
            'dispute_id'   => 1,
            'author_id'    => 1,
            'recipient_id' => 2,
            'message'      => '<script>hello, world',
            'timestamp'    => time()
        ));
        $this->assertTrue($message instanceof Message);

        $this->assertEquals(
            '&lt;script&gt;hello, world',
            $message->contents()
        );

        $this->assertEquals(1, $message->getAuthorId());
        $this->assertEquals(2, $message->getRecipientId());
        $this->assertEquals(1, $message->getDisputeId());
        $this->assertTrue((time() - $message->timestamp() < 100)); // a few milliseconds margin of error
    }
}