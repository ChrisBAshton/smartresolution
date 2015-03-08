<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class LifespanTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    protected function setUp() {
        $this->lawFirmA = AccountDetails::emailToId('law_firm_a@t.co');
        $this->agentA   = AccountDetails::emailToId('agent_a@t.co');
        $this->lawFirmB = AccountDetails::emailToId('law_firm_b@t.co');
        $this->agentB   = AccountDetails::emailToId('agent_b@t.co');

        $this->dispute = DBL::createDispute(array(
            'law_firm_a' => $this->lawFirmA,
            'agent_a'    => $this->agentA,
            'type'       => 'other',
            'title'      => 'Smith versus Jones',
            'summary'    => 'This is my summary'
        ));

        $this->dispute->setLawFirmB($this->lawFirmB);
        $this->dispute->setAgentB($this->agentB);
        $this->dispute->setSummaryForPartyB('Summary for Agent B');
    }

    private function createLifespan() {
        $currentTime = time();
        DBL::createLifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime + 12000
        ));
        $this->dispute->refresh();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage All selected dates must be in the future.
     */
    public function testLifespanInvalidWhenValidUntilIsInPast() {
        $currentTime = time();
        DBL::createLifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime - 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime + 12000
        ));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage All selected dates must be in the future.
     */
    public function testLifespanInvalidWhenStartTimeIsInPast() {
        $currentTime = time();
        DBL::createLifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime - 7200,
            'end_time'    => $currentTime + 12000
        ));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage All selected dates must be in the future.
     */
    public function testLifespanInvalidWhenEndTimeIsInPast() {
        $currentTime = time();
        DBL::createLifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime - 12000
        ));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "Valid Until" date must be before the start and end dates.
     */
    public function testLifespanInvalidWhenValidUntilIsAheadOfStartTime() {
        $currentTime = time();
        DBL::createLifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 7200,
            'start_time'  => $currentTime + 3600,
            'end_time'    => $currentTime + 12000
        ));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Start date must be before end date.
     */
    public function testLifespanInvalidWhenEndTimeBeforeStartTime() {
        $currentTime = time();
        DBL::createLifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime + 5000
        ));
    }

    public function testLifespanStatusStartsOffCorrect() {
        $this->assertTrue($this->dispute->getLifespan() instanceof LifespanMock);
        $this->assertFalse($this->dispute->getLifespan()->offered());
        $this->assertFalse($this->dispute->getLifespan()->accepted());
        $this->assertFalse($this->dispute->getLifespan()->declined());
    }

    public function testLifespanOffered() {
        $this->createLifespan();
        $this->assertTrue($this->dispute->getLifespan() instanceof Lifespan);
        $this->assertTrue($this->dispute->getLifespan()->offered());
        $this->assertFalse($this->dispute->getLifespan()->accepted());
        $this->assertFalse($this->dispute->getLifespan()->declined());
    }

    public function testLifespanAcceptAndDecline() {
        $this->createLifespan();
        $this->dispute->getLifespan()->accept();
        $this->assertTrue($this->dispute->getLifespan()->accepted());
        $this->dispute->getLifespan()->decline();
        $this->assertTrue($this->dispute->getLifespan()->declined());
    }
}
