<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DisputePartyTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testDisputeSimpleGetters() {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals('This is my summary', $dispute->getPartyA()->getSummary());
        $this->assertEquals(DBAccount::instance()->emailToId('agent_a@t.co'), $dispute->getPartyA()->getAgent()->getLoginId());
        $this->assertEquals(DBAccount::instance()->emailToId('law_firm_a@t.co'), $dispute->getPartyA()->getLawFirm()->getLoginId());
    }

    public function testSimpleSetters() {
        $dispute  = TestHelper::createNewDispute();
        $lawFirmB = DBAccount::instance()->emailToId('law_firm_b@t.co');
        $agentB   = DBAccount::instance()->emailToId('agent_b@t.co');

        // new dispute should not have party B set yet.
        $this->assertFalse($dispute->getPartyB()->getLawFirm());
        $this->assertFalse($dispute->getPartyB()->getAgent());
        $this->assertFalse($dispute->getPartyB()->getRawSummary());

        // so we set the party b properties
        $dispute->getPartyB()->setLawFirm($lawFirmB);
        $dispute->getPartyB()->setAgent($agentB);
        $dispute->getPartyB()->setSummary('Test summary');

        // now the getters should retrieve what we've just set
        $this->assertEquals($dispute->getPartyB()->getLawFirm()->getLoginId(), $lawFirmB);
        $this->assertEquals($dispute->getPartyB()->getAgent()->getLoginId(), $agentB);
        $this->assertEquals($dispute->getPartyB()->getSummary(), 'Test summary');
    }

    public function testOverridingAgentWithAnotherFromSameLawFirm() {
        $dispute = TestHelper::createNewDispute();
        $agent1 = DBAccount::instance()->emailToId('agent_a@t.co');
        $agent2 = DBAccount::instance()->emailToId('agent_c@t.co');

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
        $agentInCompanyA = DBAccount::instance()->emailToId('agent_a@t.co');
        $agentInCompanyB = DBAccount::instance()->emailToId('agent_b@t.co');

        $this->assertEquals($agentInCompanyA, $dispute->getPartyA()->getAgent()->getLoginId());
        $dispute->getPartyA()->setAgent($agentInCompanyB);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Tried setting a non-agent type as an agent!
     */
    public function testOverridingAgentWithMediator() {
        $dispute  = TestHelper::createNewDispute();
        $agent    = DBAccount::instance()->emailToId('agent_a@t.co');
        $mediator = DBAccount::instance()->emailToId('john.smith@we-mediate.co.uk');

        $this->assertEquals($agent, $dispute->getPartyA()->getAgent()->getLoginId());
        $dispute->getPartyA()->setAgent($mediator);
    }

    public function testDisputeGettersObjectsMatch() {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals(
            DBAccount::instance()->getAccountByEmail('law_firm_a@t.co')->getLoginId(),
            $dispute->getPartyA()->getLawFirm()->getLoginId()
        );
        $this->assertEquals(
            DBAccount::instance()->getAccountByEmail('agent_a@t.co')->getLoginId(),
            $dispute->getPartyA()->getAgent()->getLoginId()
        );
    }

    public function testDisputeGettersObjectsCorrectType() {
        $dispute = TestHelper::createNewDispute();
        $this->assertTrue($dispute->getPartyA()->getLawFirm() instanceof LawFirm);
        $this->assertTrue($dispute->getPartyA()->getAgent() instanceof Agent);
    }

}