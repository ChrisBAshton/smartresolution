<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class LifespanTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    protected function setUp() {
        $this->lawFirmA = AccountDetails::emailToId('law_firm_email');
        $this->agentA   = AccountDetails::emailToId('agent_email');
        $this->lawFirmB = AccountDetails::emailToId('another_law_firm_email');
        $this->agentB   = AccountDetails::emailToId('agent_b');
        
        $this->dispute = Dispute::create(array(
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
        Lifespan::create(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime + 12000
        ));
        $this->dispute->refresh();
    }

    public function testLifespanStatusStartsOffCorrect() {
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