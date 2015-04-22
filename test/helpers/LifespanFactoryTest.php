<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class LifespanFactoryTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $account        = DBAccount::instance();
        $this->agentA   = $account->emailToId('agent_a@t.co');

        $this->dispute = DBCreate::instance()->dispute(array(
            'law_firm_a' => $account->emailToId('law_firm_a@t.co'),
            'agent_a'    => $this->agentA,
            'type'       => 'other',
            'title'      => 'Smith versus Jones',
            'summary'    => 'This is my summary'
        ));

        $this->dispute->getPartyB()->setLawFirm($account->emailToId('law_firm_b@t.co'));
        $this->dispute->getPartyB()->setAgent($account->emailToId('agent_b@t.co'));
        $this->dispute->getPartyB()->setSummary('Summary for Agent B');
        DBUpdate::instance()->disputeParty($this->dispute->getPartyB());
    }

    public function testCurrentAndLatestLifespans()
    {
        // create and accept a lifespan
        $this->createLifespan();
        $lifespan = $this->dispute->getCurrentLifespan();
        $lifespan->accept();
        DBUpdate::instance()->lifespan($lifespan); // make acceptance persistent

        // now offer a new lifespan
        $this->createLifespan();

        // latest and current lifespans should be different
        $currentLifespanID = $this->dispute->getCurrentLifespan()->getLifespanId();
        $latestLifespanID  = $this->dispute->getLatestLifespan()->getLifespanId();
        $this->assertNotEquals($currentLifespanID, $latestLifespanID);
    }

    private function createLifespan()
    {
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

}