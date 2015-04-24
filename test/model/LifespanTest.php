<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class LifespanTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $this->timeInitiated = time();
        $this->lifespan = new Lifespan(array(
            'lifespan_id' => 10,
            'dispute_id'  => 2,
            'proposer'    => 8,
            'status'      => 'offered',
            'valid_until' => $this->timeInitiated + 1000,
            'start_time'  => $this->timeInitiated + 2000,
            'end_time'    => $this->timeInitiated + 3000
        ));
    }

    public function testGetters()
    {
        $this->assertEquals('offered', $this->lifespan->getRawStatus());
        $this->assertEquals('New lifespan proposal offered.', $this->lifespan->status());
        $this->assertEquals(2, $this->lifespan->getAssociatedDisputeId());
        $this->assertEquals(10, $this->lifespan->getLifespanId());
        $this->assertEquals(8, $this->lifespan->getProposer());
        $this->assertEquals($this->timeInitiated + 1000, $this->lifespan->validUntil());
        $this->assertEquals($this->timeInitiated + 2000, $this->lifespan->startTime());
        $this->assertEquals($this->timeInitiated + 3000, $this->lifespan->endTime());
        $this->assertTrue($this->lifespan->offered());
        $this->assertFalse($this->lifespan->accepted());
        $this->assertFalse($this->lifespan->declined());
        $this->assertFalse($this->lifespan->isCurrent()); // lifespan hasn't started yet
        $this->assertFalse($this->lifespan->isEnded());
    }

    public function testEndLifespan()
    {
        $this->lifespan->endLifespan();
        $this->assertEquals(time(), $this->lifespan->endTime());
        $this->assertTrue($this->lifespan->isEnded());
        $this->assertEquals('offered', $this->lifespan->getRawStatus());
        $this->assertEquals('Dispute ended.', $this->lifespan->status());
        $this->assertFalse($this->lifespan->isCurrent());
    }

    public function testAcceptLifespan()
    {
        $this->lifespan->accept();
        $this->assertFalse($this->lifespan->offered());
        $this->assertTrue($this->lifespan->accepted());
        $this->assertEquals('accepted', $this->lifespan->getRawStatus());
        $this->assertEquals('Dispute starts in 33 minutes', $this->lifespan->status());
        $this->assertFalse($this->lifespan->isCurrent());
    }

    public function testCurrentLifespan()
    {
        $lifespan = new Lifespan(array(
            'lifespan_id' => 10,
            'dispute_id'  => 2,
            'proposer'    => 8,
            'status'      => 'accepted',
            'valid_until' => time() - 1000,
            'start_time'  => time() - 100,
            'end_time'    => time() + 3000
        ));

        $this->assertEquals('Dispute has started and ends in 50 minutes', $lifespan->status());
        $this->assertTrue($lifespan->isCurrent());
    }

    public function testEndedLifespan()
    {
        $lifespan = new Lifespan(array(
            'lifespan_id' => 10,
            'dispute_id'  => 2,
            'proposer'    => 8,
            'status'      => 'accepted',
            'valid_until' => $this->timeInitiated - 2000,
            'start_time'  => $this->timeInitiated - 1000,
            'end_time'    => $this->timeInitiated - 400
        ));

        $this->assertEquals('Dispute ended.', $lifespan->status());
        $this->assertFalse($lifespan->isCurrent());
    }

    public function testDeclineLifespan()
    {
        $this->lifespan->decline();
        $this->assertFalse($this->lifespan->offered());
        $this->assertTrue($this->lifespan->declined());
        $this->assertEquals('declined', $this->lifespan->getRawStatus());
        $this->assertEquals('No lifespan set yet.', $this->lifespan->status());
        $this->assertFalse($this->lifespan->isCurrent());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "Valid Until" date must be before the start and end dates.
     */
    public function testInvalidWhenValidUntilIsAheadOFStartTime()
    {
        new Lifespan(array(
            'lifespan_id' => 1,
            'dispute_id'  => 1,
            'proposer'    => 1,
            'status'      => 'offered',
            'valid_until' => time() + 1000,
            'start_time'  => time() + 500,
            'end_time'    => time() + 3000
        ), true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Start date must be before end date.
     */
    public function testInvalidWhenStartTimeIsAheadOfEndTime()
    {
        new Lifespan(array(
            'lifespan_id' => 1,
            'dispute_id'  => 1,
            'proposer'    => 1,
            'status'      => 'offered',
            'valid_until' => time(),
            'start_time'  => time() + 3000,
            'end_time'    => time() + 500
        ), true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage All selected dates must be in the future.
     */
    public function testInvalidWhenValidUntilIsInPast()
    {
        new Lifespan(array(
            'lifespan_id' => 1,
            'dispute_id'  => 1,
            'proposer'    => 1,
            'status'      => 'offered',
            'valid_until' => time() - 100,
            'start_time'  => time() + 100,
            'end_time'    => time() + 100
        ), true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage All selected dates must be in the future.
     */
    public function testInvalidWhenStartTimeIsInPast()
    {
        new Lifespan(array(
            'lifespan_id' => 1,
            'dispute_id'  => 1,
            'proposer'    => 1,
            'status'      => 'offered',
            'valid_until' => time() + 100,
            'start_time'  => time() - 100,
            'end_time'    => time() + 100
        ), true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage All selected dates must be in the future.
     */
    public function testInvalidWhenEndTimeIsInPast()
    {
        new Lifespan(array(
            'lifespan_id' => 1,
            'dispute_id'  => 1,
            'proposer'    => 1,
            'status'      => 'offered',
            'valid_until' => time() + 100,
            'start_time'  => time() + 100,
            'end_time'    => time() - 100
        ), true);
    }

}
