<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class LifespanTest extends PHPUnit_Framework_TestCase
{

    protected function setUp() {
        $this->lawFirmA = DBAccount::instance()->emailToId('law_firm_a@t.co');
        $this->agentA   = DBAccount::instance()->emailToId('agent_a@t.co');
        $this->lawFirmB = DBAccount::instance()->emailToId('law_firm_b@t.co');
        $this->agentB   = DBAccount::instance()->emailToId('agent_b@t.co');

        $this->dispute = DBCreate::instance()->dispute(array(
            'law_firm_a' => $this->lawFirmA,
            'agent_a'    => $this->agentA,
            'type'       => 'other',
            'title'      => 'Smith versus Jones',
            'summary'    => 'This is my summary'
        ));

        $this->dispute->getPartyB()->setLawFirm($this->lawFirmB);
        $this->dispute->getPartyB()->setAgent($this->agentB);
        $this->dispute->getPartyB()->setSummary('Summary for Agent B');
    }

    private function createLifespan() {
        $currentTime = time();
        DBCreate::instance()->lifespan(array(
            'dispute_id'  => $this->dispute->getDisputeId(),
            'proposer'    => $this->agentA,
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime + 12000
        ));
        DBUpdate::instance()->dispute($this->dispute);
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
        $lifespan = $this->dispute->getCurrentLifespan();
        $lifespan->accept();
        $this->assertTrue($lifespan->accepted());
        DBUpdate::instance()->lifespan($lifespan);
    }

    public function testLifespanDecline() {
        $this->createLifespan();
        $lifespan = $this->dispute->getCurrentLifespan();
        $lifespan->decline();
        $this->assertTrue($lifespan->declined());
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
