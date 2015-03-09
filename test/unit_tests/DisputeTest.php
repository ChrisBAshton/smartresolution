<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DisputeTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    private function createNewDispute() {
        $lawFirm = AccountDetails::emailToId('law_firm_a@t.co');
        $agent = AccountDetails::emailToId('agent_a@t.co');

        return DBL::createDispute(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones',
            'summary'    => 'This is my summary'
        ));
    }

    public function testCreateDisputeSuccessfully() {
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute instanceof Dispute);
    }

    public function testOverridingAgentWithAnotherFromSameLawFirm() {
        $dispute = $this->createNewDispute();
        $agent1 = AccountDetails::emailToId('agent_a@t.co');
        $agent2 = AccountDetails::emailToId('agent_c@t.co');

        $this->assertEquals($agent1, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($agent2);
        $this->assertEquals($agent2, $dispute->getAgentA()->getLoginId());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Tried setting a non-agent type as an agent!
     */
    public function testOverridingAgentWithMediator() {
        $dispute  = $this->createNewDispute();
        $agent    = AccountDetails::emailToId('agent_a@t.co');
        $mediator = AccountDetails::emailToId('john.smith@we-mediate.co.uk');

        $this->assertEquals($agent, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($mediator);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You can only assign agents that are in your law firm!
     */
    public function testOverridingAgentWithAgentFromDifferentLawFirm() {
        $dispute = $this->createNewDispute();
        $agentInCompanyA = AccountDetails::emailToId('agent_a@t.co');
        $agentInCompanyB = AccountDetails::emailToId('agent_b@t.co');

        $this->assertEquals($agentInCompanyA, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($agentInCompanyB);
    }

    public function testDisputeSimpleGetters() {
        $dispute = $this->createNewDispute();
        $this->assertEquals(AccountDetails::emailToId('agent_a@t.co'), $dispute->getAgentA()->getLoginId());
        $this->assertEquals(AccountDetails::emailToId('law_firm_a@t.co'), $dispute->getLawFirmA()->getLoginId());
        $this->assertEquals('Smith versus Jones', $dispute->getTitle());
        $this->assertEquals('This is my summary', $dispute->getSummaryFromPartyA());
    }

    public function testDisputeGettersObjectsMatch() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertEquals(
            AccountDetails::getAccountByEmail('law_firm_a@t.co')->getLoginId(),
            $dispute->getLawFirmA()->getLoginId()
        );
        $this->assertEquals(
            AccountDetails::getAccountByEmail('agent_a@t.co')->getLoginId(),
            $dispute->getAgentA()->getLoginId()
        );
    }

    public function testDisputeGettersObjectsCorrectType() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute->getLawFirmA() instanceof LawFirm);
        $this->assertTrue($dispute->getAgentA() instanceof Agent);
    }

    public function testAuthorisationLogicIsCorrect() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute->canBeViewedBy(AccountDetails::emailToId('law_firm_a@t.co')));
        $this->assertTrue($dispute->canBeViewedBy(AccountDetails::emailToId('agent_a@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(AccountDetails::emailToId('law_firm_b@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(AccountDetails::emailToId('john.smith@we-mediate.co.uk')));
    }

    public function testDisputeWorkflowCorrect() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $state   = $dispute->getState(AccountDetails::getAccountByEmail('agent_a@t.co'));
        $this->assertTrue($state->canOpenDispute());

        $lawFirmB = AccountDetails::emailToId('law_firm_b@t.co');
        $dispute->setLawFirmB($lawFirmB);
        $this->assertEquals($lawFirmB, $dispute->getLawFirmB()->getLoginId());

        $agentB = AccountDetails::emailToId('agent_b@t.co');
        $dispute->setAgentB($agentB);
        $this->assertEquals($agentB, $dispute->getAgentB()->getLoginId());
        $this->assertFalse($state->canOpenDispute());

        $this->assertFalse($dispute->getSummaryFromPartyB());
        $dispute->setSummaryForPartyB('Test summary');
        $this->assertEquals('Test summary', $dispute->getSummaryFromPartyB());
    }

    public function testGetOpposingPartyId() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $lawFirmA = AccountDetails::emailToId('law_firm_a@t.co');
        $agentA   = AccountDetails::emailToId('agent_a@t.co');
        $lawFirmB = AccountDetails::emailToId('law_firm_b@t.co');
        $agentB   = AccountDetails::emailToId('agent_b@t.co');
        $dispute->setLawFirmB($lawFirmB);
        $dispute->setAgentB($agentB);

        $this->assertEquals($agentB, $dispute->getOpposingPartyId($agentA));
        $this->assertEquals($agentA, $dispute->getOpposingPartyId($agentB));
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeAgentToLawFirm() {
        $agentA   = AccountDetails::emailToId('agent_a@t.co');

        return DBL::createDispute(array(
            'law_firm_a' => $agentA, // shouldn't be able to set law firm as an agent
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeLawFirmToAgent() {
        $lawFirmA = AccountDetails::emailToId('law_firm_a@t.co');
        $lawFirmB = AccountDetails::emailToId('law_firm_b@t.co');

        $dispute = DBL::createDispute(array(
            'law_firm_a' => $lawFirmA,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));

        $dispute->setAgentB($lawFirmB); // shouldn't be able to set agent as a law firm
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullLawFirm() {
        DBL::createDispute(array(
            'law_firm_a' => NULL,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullType() {

        $lawFirm = AccountDetails::emailToId('law_firm_a@t.co');

        DBL::createDispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => NULL,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullTitle() {

        $lawFirm = AccountDetails::emailToId('law_firm_a@t.co');

        DBL::createDispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other',
            'title'      => NULL
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoLawFirm() {
        DBL::createDispute(array(
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoType() {
        $lawFirm = AccountDetails::emailToId('law_firm_a@t.co');
        DBL::createDispute(array(
            'law_firm_a' => $lawFirm,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoTitle() {
        $lawFirm = AccountDetails::emailToId('law_firm_a@t.co');
        DBL::createDispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other'
        ));
    }

    private function getDisputeStatus($dispute) {
        return Database::instance()->exec(
            'SELECT status FROM disputes WHERE dispute_id = :dispute_id',
            array(':dispute_id' => $dispute->getDisputeId())
        )[0]['status'];
    }

    public function testCloseUnsuccessfully() {
        $dispute = $this->createNewDispute();
        $this->assertEquals("ongoing", $this->getDisputeStatus($dispute));
        $dispute->closeUnsuccessfully();
        $this->assertEquals("failed", $this->getDisputeStatus($dispute));
    }
}
