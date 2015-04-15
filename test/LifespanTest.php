<?php
require_once __DIR__ . '/../webapp/autoload.php';

class LifespanTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    protected function setUp() {
        $this->lawFirmA = DBAccount::emailToId('law_firm_a@t.co');
        $this->agentA   = DBAccount::emailToId('agent_a@t.co');
        $this->lawFirmB = DBAccount::emailToId('law_firm_b@t.co');
        $this->agentB   = DBAccount::emailToId('agent_b@t.co');

        $this->dispute = DBCreate::dispute(array(
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
        DBCreate::lifespan(array(
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
        DBCreate::lifespan(array(
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
        DBCreate::lifespan(array(
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
        DBCreate::lifespan(array(
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
        DBCreate::lifespan(array(
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
        DBCreate::lifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime + 5000
        ));
    }

    public function testLifespanStatusStartsOffCorrect() {
        $this->assertTrue($this->dispute->getCurrentLifespan() instanceof LifespanMock);
        $this->assertFalse($this->dispute->getCurrentLifespan()->offered());
        $this->assertFalse($this->dispute->getCurrentLifespan()->accepted());
        $this->assertFalse($this->dispute->getCurrentLifespan()->declined());
    }

    public function testLifespanOffered() {
        $this->createLifespan();
        $this->assertTrue($this->dispute->getCurrentLifespan() instanceof Lifespan);
        $this->assertTrue($this->dispute->getCurrentLifespan()->offered());
        $this->assertFalse($this->dispute->getCurrentLifespan()->accepted());
        $this->assertFalse($this->dispute->getCurrentLifespan()->declined());
    }

    public function testLifespanAccept() {
        $this->createLifespan();
        $this->dispute->getCurrentLifespan()->accept();
        $this->assertTrue($this->dispute->getCurrentLifespan()->accepted());
    }

    public function testLifespanDecline() {
        $this->createLifespan();
        $this->dispute->getCurrentLifespan()->decline();
        $this->assertTrue($this->dispute->getCurrentLifespan()->declined());
    }

    public function testCurrentAndLatestLifespans() {
        // create and accept a lifespan
        $this->testLifespanAccept();
        // now offer a new lifespan
        $this->createLifespan();
        // latest and current lifespans should be different
        $currentLifespanID = $this->dispute->getCurrentLifespan()->getLifespanId();
        $latestLifespanID  = $this->dispute->getLatestLifespan()->getLifespanId();
        $this->assertNotEquals($currentLifespanID, $latestLifespanID);
    }
}
