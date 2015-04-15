<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DisputePartyTest extends PHPUnit_Framework_TestCase
{

    public function test() {
        //$this->assertEquals('This is my summary', $dispute->getPartyA()->getSummary());
    }

    public function testDisputeSimpleGetters() {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals(DBAccount::emailToId('agent_a@t.co'), $dispute->getPartyA()->getAgent()->getLoginId());
        $this->assertEquals(DBAccount::emailToId('law_firm_a@t.co'), $dispute->getPartyA()->getLawFirm()->getLoginId());
    }

    public function testOverridingAgentWithAnotherFromSameLawFirm() {
        $dispute = TestHelper::createNewDispute();
        $agent1 = DBAccount::emailToId('agent_a@t.co');
        $agent2 = DBAccount::emailToId('agent_c@t.co');

        $this->assertEquals($agent1, $dispute->getPartyA()->getAgent()->getLoginId());
        $dispute->getPartyA()->setAgent($agent2);
        $this->assertEquals($agent2, $dispute->getPartyA()->getAgent()->getLoginId());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You can only assign agents that are in your law firm!
     */
    public function testOverridingAgentWithAgentFromDifferentLawFirm() {
        $dispute = TestHelper::createNewDispute();
        $agentInCompanyA = DBAccount::emailToId('agent_a@t.co');
        $agentInCompanyB = DBAccount::emailToId('agent_b@t.co');

        $this->assertEquals($agentInCompanyA, $dispute->getPartyA()->getAgent()->getLoginId());
        $dispute->getPartyA()->setAgent($agentInCompanyB);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Tried setting a non-agent type as an agent!
     */
    public function testOverridingAgentWithMediator() {
        $dispute  = TestHelper::createNewDispute();
        $agent    = DBAccount::emailToId('agent_a@t.co');
        $mediator = DBAccount::emailToId('john.smith@we-mediate.co.uk');

        $this->assertEquals($agent, $dispute->getPartyA()->getAgent()->getLoginId());
        $dispute->getPartyA()->setAgent($mediator);
    }

    public function testDisputeGettersObjectsMatch() {
        DisputeTest::setUpBeforeClass();
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals(
            DBAccount::getAccountByEmail('law_firm_a@t.co')->getLoginId(),
            $dispute->getPartyA()->getLawFirm()->getLoginId()
        );
        $this->assertEquals(
            DBAccount::getAccountByEmail('agent_a@t.co')->getLoginId(),
            $dispute->getPartyA()->getAgent()->getLoginId()
        );
    }

    public function testDisputeGettersObjectsCorrectType() {
        DisputeTest::setUpBeforeClass();
        $dispute = TestHelper::createNewDispute();
        $this->assertTrue($dispute->getPartyA()->getLawFirm() instanceof LawFirm);
        $this->assertTrue($dispute->getPartyA()->getAgent() instanceof Agent);
    }

}